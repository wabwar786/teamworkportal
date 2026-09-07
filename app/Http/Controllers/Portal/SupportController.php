<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\RemoteCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /** Inbox: conversations from web + desktop customers in one place. */
    public function inbox(Request $request)
    {
        $conversations = Conversation::with('client')
            ->orderByDesc('last_at')->get();

        $selectedId = $request->input('c', optional($conversations->first())->id);
        $selected = $selectedId ? Conversation::with(['messages', 'client'])->find($selectedId) : null;

        return view('portal.inbox', compact('conversations', 'selected'));
    }

    /** Agent replies to a customer. */
    public function reply(Request $request, Conversation $conversation)
    {
        $data = $request->validate(['text' => 'required|string']);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'agent',
            'text' => $data['text'],
        ]);
        $conversation->update(['last_at' => now()]);
        return back();
    }

    /** Script generator + customer keys. */
    public function scripts()
    {
        return view('portal.scripts', [
            'clients' => Client::orderBy('name')->get(),
            'chatBase' => config('app.chat_base', config('app.url')),
        ]);
    }

    /** Add a support-chat customer (generates a public key). */
    public function addClient(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'channel' => 'nullable|in:web,desktop,both',
        ]);

        Client::create([
            'name' => $data['name'],
            'key' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'channel' => $data['channel'] ?? 'web',
        ]);

        return back()->with('ok', 'Customer added — script ready.');
    }
}
