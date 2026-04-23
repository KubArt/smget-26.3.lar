<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadNote;
use App\Models\Crm\LeadTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadController extends BaseCabinetController
{
    public function index(Request $request)
    {
        // 1. Получаем текущий кабинет через метод, определенный в модели User
        $workspace = auth()->user()->currentWorkspace();

        // 2. Получаем ID всех сайтов, которые принадлежат этому кабинету
        // Это позволяет сотрудникам видеть лиды всех проектов компании
        $siteIds = $workspace->sites()->pluck('id');

        $query = Lead::whereIn('site_id', $siteIds)
            ->with(['site', 'client', 'funnelStage', 'prize']);

        // Фильтр по конкретному сайту (если выбран в интерфейсе)
        if ($request->filled('site_id')) {
            // Проверяем, что выбранный сайт действительно принадлежит этому кабинету
            $query->where('site_id', $request->site_id);
        }

        // Фильтрация по датам
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ]);
        }

        $leads = $query->latest()->paginate(15);

        // Список сайтов для выпадающего списка фильтра
        $sites = $workspace->sites;

        return view('cabinet.crm.leads.index', compact('leads', 'sites', 'workspace'));
    }

    public function show(Lead $lead)
    {
        $workspace = auth()->user()->currentWorkspace();

        // 3. Проверка доступа: принадлежит ли сайт лида текущему кабинету
        abort_if(!$workspace->sites->contains('id', $lead->site_id), 403, 'Доступ к лиду запрещен');

        $lead->load(['client.notes', 'notes.user', 'tasks.assignedTo', 'site', 'prize']);

        return view('cabinet.crm.leads.show', compact('lead'));
    }

    public function storeNote(Request $request, Lead $lead)
    {
        $request->validate(['note' => 'required|string']);

        $lead->notes()->create([
            'user_id' => auth()->id(),
            'note' => $request->note
        ]);

        return back()->with('success', 'Заметка добавлена');
    }

    public function storeTask(Request $request, Lead $lead)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date'
        ]);

        $lead->tasks()->create([
            'title' => $request->title,
            'due_date' => $request->due_date,
            'assigned_to' => auth()->id(),
            'created_by' => auth()->id(),
            'status' => 'pending'
        ]);

        return back()->with('success', 'Напоминание создано');
    }
}
