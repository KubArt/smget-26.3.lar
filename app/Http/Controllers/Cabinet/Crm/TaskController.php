<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadNote;
use App\Models\Crm\LeadTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends BaseCabinetController
{
    public function tasks(Request $request)
    {
        $query = LeadTask::where('assigned_to', auth()->id())
            ->with(['lead.client', 'lead.site'])
            ->latest('due_date');

        // Фильтр по статусу (по умолчанию показываем только активные)
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tasks = $query->paginate(20);

        return view('cabinet.crm.tasks.index', compact('tasks'));
    }

    public function toggleTask(LeadTask $task)
    {
        // Проверка доступа
        abort_if($task->assigned_to !== auth()->id(), 403);

        $newStatus = $task->status === 'pending' ? 'completed' : 'pending';

        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null
        ]);

        return back()->with('success', 'Статус задачи обновлен');
    }
}
