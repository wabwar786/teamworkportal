<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

/**
 * Public support-chat API. Used by both the JavaScript widget (web) and
 * the C# WinForms control (desktop). Both share one inbox.
 */
class ChatApiController extends Controller
{
    /** Open (or reuse) a conversation for a visitor. */
    public function open(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string',
            'visitor' => 'required|string|max:80',
            'name' => 'nullable|string|max:120',
            'company' => 'nullable|string|max:120',
            'channel' => 'nullable|in:web,desktop',
            'page' => 'nullable|string|max:200',
            'browser' => 'nullable|string|max:80',
            'os' => 'nullable|string|max:80',
            'app' => 'nullable|string|max:120',
        ]);

        $client = Client::where('key', $data['key'])->where('active', true)->firstOrFail();

        $conversation = Conversation::firstOrCreate(
            ['client_id' => $client->id, 'visitor' => $data['visitor'], 'status' => 'open'],
            [
                'name' => $data['name'] ?? 'Customer',
                'company' => $data['company'] ?? $client->name,
                'channel' => $data['channel'] ?? 'web',
                'page' => $data['page'] ?? null,
                'browser' => $data['browser'] ?? null,
                'os' => $data['os'] ?? null,
                'app_label' => $data['app'] ?? null,
                'city' => $this->cityFromIp($request->ip()),
                'ip' => $request->ip(),
                'last_at' => now(),
            ]
        );

        return response()->json(['convId' => $conversation->id]);
    }

    /** Customer sends a message. */
    public function send(Request $request)
    {
        $data = $request->validate([
            'convId' => 'required|exists:conversations,id',
            'text' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $msg = Message::create([
            'conversation_id' => $data['convId'],
            'sender' => 'customer',
            'text' => $data['text'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        Conversation::where('id', $data['convId'])->update(['last_at' => now()]);

        return response()->json(['id' => $msg->id]);
    }

    /** Customer polls for new agent replies (SignalR replaces this later). */
    public function poll(Request $request)
    {
        $data = $request->validate([
            'convId' => 'required|exists:conversations,id',
            'since' => 'nullable|integer',
        ]);

        $msgs = Message::where('conversation_id', $data['convId'])
            ->where('sender', 'agent')
            ->where('id', '>', $data['since'] ?? 0)
            ->orderBy('id')->get();

        return response()->json([
            'msgs' => $msgs->map(fn ($m) => [
                'id' => $m->id, 'from' => 'agent',
                'text' => $m->text, 'image' => $m->image,
                'at' => $m->created_at->format('g:i A'),
            ]),
        ]);
    }

    /** Customer shares a screenshot. */
    public function screenshot(Request $request)
    {
        $data = $request->validate([
            'convId' => 'required|exists:conversations,id',
            'image' => 'required|string',
        ]);

        Message::create([
            'conversation_id' => $data['convId'],
            'sender' => 'customer',
            'image' => $data['image'],
        ]);

        return response()->json(['ok' => true]);
    }

    /** Placeholder geo lookup; wire a real IP->city service in production. */
    private function cityFromIp(?string $ip): ?string
    {
        return null;
    }
}
