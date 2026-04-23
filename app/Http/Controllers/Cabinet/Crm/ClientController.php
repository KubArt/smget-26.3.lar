<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Controller;
use App\Models\Crm\Client;
use App\Models\Crm\LeadNote;
use App\Models\Crm\LeadStageHistory;
use App\Models\Crm\LeadTask;
use App\Models\Crm\Prize;
use App\Models\Crm\PrizeAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends BaseCabinetController
{
    public function index(Request $request)
    {
        // 1. Получаем текущий кабинет
        $workspace = auth()->user()->currentWorkspace();

        // 2. Получаем ID всех сайтов этого кабинета
        $siteIds = $workspace->sites()->pluck('id');


        $query = Client::whereIn('site_id', $siteIds)
            ->withCount(['leads', 'prizes as active_prizes_count' => function($q) {
                $q->where('is_used', false)->where(function($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            }]);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('phone', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%");
            });
        }

        $clients = $query->latest()->paginate(20);

        return view('cabinet.crm.clients.index', compact('clients', 'workspace'));
    }

    public function show(Client $client)
    {
        $workspace = auth()->user()->currentWorkspace();

        // 3. Проверка доступа через принадлежность сайта клиента к кабинету
        abort_if(!$workspace->sites->contains('id', $client->site_id), 403, 'Доступ к клиенту запрещен');

        // Загружаем всю историю: лиды, задачи по всем лидам и заметки
        $client->load([
            'leads.funnelStage',
            'leads.widget',
            'notes.user',
            'leads.tasks' => function($q) {
                $q->latest();
            },
            'prizes' => function($q) {
                $q->latest();
            }
        ]);

        return view('cabinet.crm.clients.show', compact('client', 'workspace'));
    }

    public function storeNote(Request $request, Client $client)
    {
        $workspace = auth()->user()->currentWorkspace();

        // Проверка доступа перед сохранением заметки
        abort_if(!$workspace->sites->contains('id', $client->site_id), 403);

        $request->validate(['note' => 'required|string']);

        $client->notes()->create([
            'user_id' => auth()->id(),
            'note' => $request->note
        ]);

        return back()->with('success', 'Заметка добавлена');
    }

    public function destroy(Client $client)
    {
        try {
            DB::beginTransaction();

            // 1. Получаем ID всех лидов клиента для массового удаления связанных сущностей
            $leadIds = $client->leads()->pluck('id');

            // 2. Удаляем задачи, заметки и историю стадий, привязанные к лидам
            LeadTask::whereIn('lead_id', $leadIds)->delete();
            LeadNote::whereIn('lead_id', $leadIds)->delete();
            LeadStageHistory::whereIn('lead_id', $leadIds)->delete();

            // 3. Удаляем призы и попытки (PrizeAttempt не имеет прямой связи с Client, но может быть связан через Prize)
            $prizeIds = $client->prizes()->pluck('id');
            PrizeAttempt::whereIn('prize_id', $prizeIds)->delete();
            $client->prizes()->delete();

            // 4. Удаляем заметки самого клиента
            $client->notes()->delete();

            // 5. Удаляем лиды
            $client->leads()->delete();

            // 6. Удаляем самого клиента
            $client->delete();

            DB::commit();

            return redirect()->route('crm.clients.index')
                ->with('success', 'Клиент и все связанные данные успешно удалены.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }

    /**
     * Полное техническое удаление клиента и всех связанных данных (Force Delete)
     */
    public function forceDestroy(Client $client)
    {
        // ТЕХНИЧЕСКАЯ ПРОВЕРКА ДОСТУПА (чтобы нельзя было удалить чужого клиента через ID в URL)
        $workspace = auth()->user()->currentWorkspace();
        abort_if(!$workspace->sites->contains('id', $client->site_id), 403);

        try {
            DB::beginTransaction();

            // 1. Собираем ID лидов для массового удаления зависимостей
            $leadIds = $client->leads()->pluck('id');

            // 2. Очистка данных, связанных с лидами
            if ($leadIds->isNotEmpty()) {
                LeadTask::whereIn('lead_id', $leadIds)->delete();
                LeadNote::whereIn('lead_id', $leadIds)->delete();
                LeadStageHistory::whereIn('lead_id', $leadIds)->delete();
            }

            // 3. Очистка призов и попыток их активации
            $prizeIds = $client->prizes()->pluck('id');
            if ($prizeIds->isNotEmpty()) {
                PrizeAttempt::whereIn('prize_id', $prizeIds)->delete();
                Prize::whereIn('id', $prizeIds)->delete();
            }

            // 4. Очистка данных самого клиента
            $client->notes()->delete();
            $client->leads()->delete();

            // 5. Финальное удаление записи клиента
            $client->delete();

            DB::commit();

            return redirect()->route('cabinet.crm.clients.index')
                ->with('success', 'Техническая очистка завершена: клиент и все связи удалены навсегда.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при очистке: ' . $e->getMessage());
        }
    }


}
