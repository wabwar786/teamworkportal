<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\InternalMessage;
use App\Models\Owner;
use Illuminate\Http\Request;

/**
 * Team chat for owners/heads. The owner is the signed-in user; ref is
 * "owner:{id}". They can message any active member and any other owner.
 * Mirrors ChatController but for the authenticated portal side.
 */
class MessageController extends Controller
{
    private function ref(Request $request): string
    {
        return 'owner:'.$request->user()->id;
    }

    /** Full-page messages screen. */
    public function index()
    {
        return view('portal.messages');
    }

    public function contacts(Request $request)
    {
        $meRef = $this->ref($request);
        $meId = $request->user()->id;

        $members = Employee::active()->orderBy('name')->get()->map(fn ($e) => [
            'ref' => 'emp:'.$e->id,
            'name' => $e->name,
            'role' => $e->role,
            'kind' => 'emp',
        ]);

        $owners = Owner::where('active', true)->where('id', '!=', $meId)->orderBy('name')->get()->map(fn ($o) => [
            'ref' => 'owner:'.$o->id,
            'name' => $o->name,
            'role' => $o->isSuper() ? 'Owner' : 'Head',
            'kind' => 'owner',
        ]);

        $unread = InternalMessage::where('to_ref', $meRef)->whereNull('read_at')
            ->selectRaw('from_ref, count(*) as c')->groupBy('from_ref')->pluck('c', 'from_ref');

        $contacts = $members->concat($owners)->map(function ($c) use ($unread) {
            $c['unread'] = (int) ($unread[$c['ref']] ?? 0);
            return $c;
        });

        return response()->json(['me' => $meRef, 'contacts' => $contacts->values()]);
    }

    public function thread(Request $request, string $kind, int $id)
    {
        if (! in_array($kind, ['owner', 'emp'], true)) {
            abort(404);
        }
        $meRef = $this->ref($request);
        $other = $kind.':'.$id;

        $msgs = InternalMessage::where(function ($q) use ($meRef, $other) {
            $q->where('from_ref', $meRef)->where('to_ref', $other);
        })->orWhere(function ($q) use ($meRef, $other) {
            $q->where('from_ref', $other)->where('to_ref', $meRef);
        })->orderBy('id')->get();

        InternalMessage::where('from_ref', $other)->where('to_ref', $meRef)
            ->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'messages' => $msgs->map(fn ($m) => [
                'id' => $m->id,
                'mine' => $m->from_ref === $meRef,
                'body' => $m->body,
                'image' => $m->image,
                'at' => $m->created_at->format('g:i A'),
            ]),
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|string',
            'body' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        [$kind, $id] = array_pad(explode(':', $data['to']), 2, null);
        if (! in_array($kind, ['owner', 'emp'], true) || ! $id) {
            return response()->json(['error' => 'bad recipient'], 422);
        }
        if (empty($data['body']) && empty($data['image'])) {
            return response()->json(['error' => 'empty message'], 422);
        }

        $m = InternalMessage::create([
            'from_ref' => $this->ref($request),
            'to_ref' => $data['to'],
            'body' => $data['body'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        return response()->json(['id' => $m->id, 'at' => $m->created_at->format('g:i A')]);
    }

    public function poll(Request $request)
    {
        $meRef = $this->ref($request);

        $unread = InternalMessage::where('to_ref', $meRef)->whereNull('read_at')
            ->selectRaw('from_ref, count(*) as c')->groupBy('from_ref')->pluck('c', 'from_ref');

        $lastId = (int) (InternalMessage::where('to_ref', $meRef)->orWhere('from_ref', $meRef)->max('id') ?? 0);

        return response()->json([
            'unread' => $unread,
            'total' => (int) $unread->sum(),
            'lastId' => $lastId,
        ]);
    }
}
