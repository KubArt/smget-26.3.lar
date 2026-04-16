<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Controller;
use App\Models\Crm\Client;
use Illuminate\Http\Request;

class ClientController extends BaseCabinetController
{
    public function index(Request $request)
    {
        // 1. Получаем текущий кабинет
        $workspace = auth()->user()->currentWorkspace();

        // 2. Получаем ID всех сайтов этого кабинета
        $siteIds = $workspace->sites()->pluck('id');

        $query = Client::whereIn('site_id', $siteIds)
            ->withCount('leads');

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
}
