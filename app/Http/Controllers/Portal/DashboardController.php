<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Owner/head "Today": each employee's log + pending + assigned tasks. */
    public function today(Request $request)
    {
        $team = $this->team($request)->with(['logs' => function ($q) {
            $q->where('log_date', now()->toDateString());
        }])->get();

        $pending = [];
        foreach ($team as $emp) {
            $log = $emp->logs->first();
            if ($log) {
                foreach ($log->lines('pending') as $p) {
                    $pending[] = ['emp' => $emp, 'text' => $p];
                }
            }
        }

        $openTasks = Task::where('status', 'open')
            ->whereIn('employee_id', $team->pluck('id'))
            ->with('employee')->get();

        return view('portal.today', compact('team', 'pending', 'openTasks'));
    }

    /** Analyze: productivity points + breakdown for the day. */
    public function analyze(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $team = $this->team($request)->get();

        $rows = $team->map(function ($emp) use ($date) {
            $log = $emp->logs()->where('log_date', $date)->first();
            return [
                'emp' => $emp,
                'done' => $log ? count($log->lines('done')) : 0,
                'upload' => $log ? count($log->lines('upload')) : 0,
                'critical' => $log ? count($log->lines('critical')) : 0,
                'pending' => $log ? count($log->lines('pending')) : 0,
                'score' => $log ? $log->score() : 0,
            ];
        })->sortByDesc('score')->values();

        return view('portal.analyze', compact('rows', 'date'));
    }

    /** Full log, most recent first. */
    public function fullLog(Request $request)
    {
        $includeArchived = $request->boolean('archived');

        $q = Employee::query()->with(['logs' => fn ($q) => $q->orderByDesc('log_date')]);
        if ($request->user()->isHead()) {
            $q->where('head_id', $request->user()->id);
        }
        if (! $includeArchived) {
            $q->where('archived', false);
        }

        return view('portal.full-log', [
            'employees' => $q->orderBy('name')->get(),
            'includeArchived' => $includeArchived,
        ]);
    }

    /** Base team query scoped to the signed-in owner. */
    private function team(Request $request)
    {
        $q = Employee::active();
        if ($request->user()->isHead()) {
            $q->where('head_id', $request->user()->id);
        }
        return $q->orderBy('name');
    }
}
