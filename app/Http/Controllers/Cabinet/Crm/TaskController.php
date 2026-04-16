<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Models\Crm\LeadTask;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends BaseCabinetController
{
    /**
     * Список задач текущего кабинета
     */
    public function tasks(Request $request)
    {
        $workspace = auth()->user()->currentWorkspace();
        $siteIds = $workspace->sites()->pluck('id');

        $query = LeadTask::whereHas('lead', function($q) use ($siteIds) {
            $q->whereIn('site_id', $siteIds);
        })->with(['lead.client', 'lead.site', 'assignedTo']);

        // 1. По умолчанию отображаем ВСЕ задачи (assigned_to = all)
        $assignedTo = $request->get('assigned_to', 'all');
        if ($assignedTo !== 'all') {
            $query->where('assigned_to', $assignedTo);
        }

        // 2. Фильтр по статусу
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // 3. Фильтр по дате (Новая логика)
        $dateFilter = $request->get('date_range', 'all');

        if ($request->filled('date_custom')) {
            $query->whereDate('due_date', $request->date_custom);
        } else {
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('due_date', Carbon::today());
                    break;
                case 'tomorrow':
                    $query->whereDate('due_date', Carbon::tomorrow());
                    break;
                case '7_days':
                    $query->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(7)]);
                    break;
                case '30_days':
                    $query->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(30)]);
                    break;
            }
        }

        $tasks = $query->latest('due_date')->paginate(20);
        $members = $workspace->users;

        return view('cabinet.crm.tasks.index', compact('tasks', 'workspace', 'members', 'assignedTo', 'dateFilter'));
    }

    public function toggleTask(LeadTask $task)
    {
        // 1. Получаем текущий активный кабинет пользователя
        $workspace = auth()->user()->currentWorkspace();

        // 2. Проверяем только одно: принадлежит ли задача (через лид и сайт) к текущему кабинету
        // Мы убираем проверку на конкретного исполнителя (isAssigned) и на владельца (isOwner)
        $belongsToWorkspace = $workspace->sites->contains('id', $task->lead->site_id);

        // Если сайт лида входит в текущий кабинет — разрешаем любое действие
        abort_if(!$belongsToWorkspace, 403, 'Эта задача принадлежит другому кабинету');

        // 3. Переключаем статус
        $newStatus = $task->status === 'pending' ? 'completed' : 'pending';

        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null
        ]);

        // В будущем здесь добавим логирование: кто именно нажал кнопку (auth()->id())

        return back()->with('success', 'Статус задачи обновлен');
    }
}
