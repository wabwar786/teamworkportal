<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\InternalMessage;
use App\Models\Owner;
use Illuminate\Http\Request;

/**
 * Team chat for the member (no-login) side. The member is identified by the
 * session `member_id`. They can message any owner/head and any other active
 * member. Messages (text + optional image) are stored in internal_messages,
 * keyed by refs like "emp:3" / "owner:1".
 */
class ChatController extends Controller
{
    private function me(): ?Employee
    {
        $id = session('member_id');
        return $id ? Employee::active()->find($id) : null;
    }

    private function ref(Employee $e): string
    {
        return 'emp:'.$e->id;
    }

    /** Everyone this member can talk to, with unread counts. */
    public function contacts(Request $request)
    {
        $me = $this->me();
        if (! $me) {
            return response()->json(['error' => 'no session'], 401);
        }
        $meRef = $this->ref($me);

        $owners = Owner::where('active', true)->orderBy('name')->get()->map(fn ($o) => [
            'ref' => 'owner:'.$o->id,
            'name' => $o->name,
            'role' => $o->isSuper() ? 'Owner' : 'Head',
            'kind' => 'owner',
        ]);

        $members = Employee::active()->where('id', '!=', $me->id)->orderBy('name')->get()->map(fn ($e) => [
            'ref' => 'emp:'.$e->id,
            'name' => $e->name,
            'role' => $e->role,
            'kind' => 'emp',
        ]);

        $unread = InternalMessage::where('to_ref', $meRef)->whereNull('read_at')
            ->selectRaw('from_ref, count(*) as c')->groupBy('from_ref')->pluck('c', 'from_ref');

        $contacts = $owners->concat($members)->map(function ($c) use ($unread) {
            $c['unread'] = (int) ($unread[$c['ref']] ?? 0);
            return $c;
        });

        return response()->json(['me' => $meRef, 'contacts' => $contacts->values()]);
    }

    /** Full thread with one contact; marks their messages read. */
    public function thread(Request $request, string $kind, int $id)
    {
        $me = $this->me();
        if (! $me) {
            return response()->json(['error' => 'no session'], 401);
        }
        if (! in_array($kind, ['owner', 'emp'], true)) {
            abort(404);
        }
        $meRef = $this->ref($me);
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

    /** Send a text and/or image to a contact. */
    public function send(Request $request)
    {
        $me = $this->me();
        if (! $me) {
            return response()->json(['error' => 'no session'], 401);
        }

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
            'from_ref' => $this->ref($me),
            'to_ref' => $data['to'],
            'body' => $data['body'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        return response()->json(['id' => $m->id, 'at' => $m->created_at->format('g:i A')]);
    }

    /** Poll: unread-per-contact + a change marker for the open thread. */
    public function poll(Request $request)
    {
        $me = $this->me();
        if (! $me) {
            return response()->json(['error' => 'no session'], 401);
        }
        $meRef = $this->ref($me);

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
