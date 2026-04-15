<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadNote;
use App\Models\Crm\LeadStageHistory;
use App\Models\Crm\LeadTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FunnelController extends BaseCabinetController
{
    public function updateStage(Request $request, Lead $lead)
    {
        // Определяем коды стадий, которые считаются отказом
        $rejectedCodes = ['rejected', 'lost', 'refusal'];

        $request->validate([
            'stage_code' => 'required|string',
            'comment' => [
                in_array($request->stage_code, $rejectedCodes) ? 'required' : 'nullable',
                'string',
                'max:500'
            ],
        ], [
            'comment.required' => 'При переходе в этот статус необходимо указать причину отказа.'
        ]);

        $oldStage = $lead->status;
        $newStage = $request->stage_code;

        if ($oldStage !== $newStage) {
            $lead->update(['status' => $newStage]);

            LeadStageHistory::create([
                'lead_id'    => $lead->id,
                'from_stage' => $oldStage,
                'to_stage'   => $newStage,
                'changed_by' => auth()->id(),
                'comment'    => $request->comment // Здесь будет причина
            ]);

            return back()->with('success', 'Статус обновлен');
        }

        return back();
    }

}
