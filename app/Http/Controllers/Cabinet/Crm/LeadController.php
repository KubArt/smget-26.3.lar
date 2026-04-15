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
        $query = Lead::whereIn('site_id', auth()->user()->sites->pluck('id'))
            ->with(['site', 'client', 'funnelStage']);

        // Фильтр по сайту
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        // Фильтр по дате (как в статистике)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ]);
        }

        $leads = $query->latest()->paginate(15);
        $sites = auth()->user()->sites;

        return view('cabinet.crm.leads.index', compact('leads', 'sites'));
    }

    public function show(Lead $lead)
    {
        // Проверка доступа (через Policy или напрямую)
        abort_if(!auth()->user()->sites->contains($lead->site_id), 403);

        $lead->load(['client.notes', 'notes.user', 'tasks.assignedTo', 'site']);

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
