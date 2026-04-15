<?php

namespace App\Http\Controllers\Cabinet\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::whereIn('site_id', auth()->user()->sites->pluck('id'))
            ->withCount('leads');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('phone', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%");
            });
        }

        $clients = $query->latest()->paginate(20);
        return view('cabinet.crm.clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        // Проверка доступа
        abort_if(!auth()->user()->sites->contains($client->site_id), 403);

        // Загружаем всю историю: лиды, задачи по всем лидам и заметки
        $client->load([
            'leads.funnelStage',
            'leads.widget',
            'notes.user',
            'leads.tasks' => function($q) {
                $q->latest();
            }
        ]);

        return view('cabinet.crm.clients.show', compact('client'));
    }

    public function storeNote(Request $request, Client $client)
    {
        $request->validate(['note' => 'required|string']);

        $client->notes()->create([
            'user_id' => auth()->id(),
            'note' => $request->note
        ]);

        return back()->with('success', 'Глобальная заметка добавлена');
    }
}
