<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Device;
use App\Models\Employee;
use App\Models\InternalMessage;
use App\Models\Owner;
use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Http\Request;

/**
 * Global search across the whole software. Runs LIKE queries over the main
 * tables and returns grouped, linked results. Heads see only their own team
 * for team-scoped data; the super owner sees everything.
 */
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $owner = $request->user();
        $groups = [];

        if ($q !== '') {
            $like = '%'.$q.'%';

            // team scope for heads
            $teamIds = null;
            if ($owner->isHead()) {
                $teamIds = Employee::where('head_id', $owner->id)->pluck('id');
            }

            // --- Employees ---
            $emp = Employee::query()
                ->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('role', 'like', $like)
                      ->orWhere('slug', 'like', $like)
                      ->orWhere('shared_note', 'like', $like);
                });
            if ($teamIds !== null) {
                $emp->whereIn('id', $teamIds);
            }
            $groups['Employees'] = $emp->limit(12)->get()->map(fn ($e) => [
                'title' => $e->name,
                'sub' => $e->role.($e->archived ? ' · archived' : ''),
                'url' => $e->archived ? route('portal.employees.log', $e) : route('portal.employees'),
            ]);

            // --- Work logs (text inside daily entries) ---
            $logs = WorkLog::query()->with('employee')
                ->where(function ($w) use ($like) {
                    $w->where('done', 'like', $like)->orWhere('upload', 'like', $like)
                      ->orWhere('pending', 'like', $like)->orWhere('critical', 'like', $like)
                      ->orWhere('saved', 'like', $like);
                });
            if ($teamIds !== null) {
                $logs->whereIn('employee_id', $teamIds);
            }
            $groups['Work logs'] = $logs->latest('log_date')->limit(12)->get()->map(fn ($l) => [
                'title' => optional($l->employee)->name.' · '.$l->log_date->format('d M Y'),
                'sub' => $this->snippet($l, $q),
                'url' => route('portal.log'),
            ]);

            // --- Tasks ---
            $tasks = Task::query()->with('employee')->where('body', 'like', $like);
            if ($teamIds !== null) {
                $tasks->whereIn('employee_id', $teamIds);
            }
            $groups['Tasks'] = $tasks->limit(12)->get()->map(fn ($t) => [
                'title' => \Illuminate\Support\Str::limit($t->body, 60),
                'sub' => (optional($t->employee)->name ?? '—').' · '.$t->status,
                'url' => route('portal.tasks'),
            ]);

            // --- Owners & heads (super only) ---
            if ($owner->isSuper()) {
                $groups['Owners & heads'] = Owner::where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)->orWhere('email', 'like', $like);
                })->limit(12)->get()->map(fn ($o) => [
                    'title' => $o->name,
                    'sub' => $o->email.' · '.($o->isSuper() ? 'super' : 'head'),
                    'url' => route('portal.owners'),
                ]);
            }

            // --- Devices ---
            $groups['Devices'] = Device::with('employee')
                ->where(function ($w) use ($like) {
                    $w->where('pc_name', 'like', $like)->orWhere('os', 'like', $like)->orWhere('ip', 'like', $like);
                })->limit(12)->get()->map(fn ($d) => [
                    'title' => $d->pc_name ?? 'Unknown PC',
                    'sub' => (optional($d->employee)->name ?? 'Unassigned').' · '.($d->os ?? ''),
                    'url' => route('portal.devices'),
                ]);

            // --- Support customers ---
            $groups['Support customers'] = Client::where('name', 'like', $like)->orWhere('key', 'like', $like)
                ->limit(12)->get()->map(fn ($c) => [
                    'title' => $c->name,
                    'sub' => $c->key.' · '.$c->channel,
                    'url' => route('portal.scripts'),
                ]);

            // --- Support conversations ---
            $groups['Support chats'] = Conversation::with('client')
                ->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)->orWhere('company', 'like', $like)->orWhere('visitor', 'like', $like);
                })->limit(12)->get()->map(fn ($c) => [
                    'title' => $c->name ?? $c->visitor,
                    'sub' => ($c->company ?? '').' · '.$c->channel,
                    'url' => route('portal.support', ['c' => $c->id]),
                ]);

            // --- Team messages involving this owner ---
            $meRef = 'owner:'.$owner->id;
            $groups['Messages'] = InternalMessage::where('body', 'like', $like)
                ->where(function ($w) use ($meRef) {
                    $w->where('from_ref', $meRef)->orWhere('to_ref', $meRef);
                })->latest('id')->limit(12)->get()->map(fn ($m) => [
                    'title' => \Illuminate\Support\Str::limit($m->body, 60),
                    'sub' => $m->created_at->format('d M Y g:i A'),
                    'url' => route('portal.messages'),
                ]);

            // drop empty groups
            $groups = array_filter($groups, fn ($g) => $g->isNotEmpty());
        }

        $total = collect($groups)->sum(fn ($g) => $g->count());

        return view('portal.search', compact('q', 'groups', 'total'));
    }

    /** Return the matching line from a work log for context. */
    private function snippet(WorkLog $l, string $q): string
    {
        foreach (['done', 'upload', 'pending', 'critical', 'saved'] as $f) {
            foreach ($l->lines($f) as $line) {
                if (stripos($line, $q) !== false) {
                    return \Illuminate\Support\Str::limit($line, 70);
                }
            }
        }
        return '';
    }
}
