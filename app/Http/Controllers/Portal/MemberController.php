<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OffDay;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * Member side: no password. The employee picks their name on the start
 * screen; we remember them in the session. They only ever see their own
 * day and the tasks/notes the owner shared with them.
 */
class MemberController extends Controller
{
    /** Start screen — list active employees to pick from. */
    public function start()
    {
        return view('portal.member-start', [
            'employees' => Employee::active()->orderBy('name')->get(),
            'remembered' => session('member_id'),
        ]);
    }

    /** Pick a name -> remember and go to the day view. */
    public function pick(Request $request)
    {
        $data = $request->validate(['employee_id' => 'required|exists:employees,id']);
        $emp = Employee::active()->findOrFail($data['employee_id']);
        session(['member_id' => $emp->id]);
        return redirect()->route('member.day');
    }

    /** The single "My day" screen. */
    public function day(Request $request)
    {
        $emp = $this->current();
        if (! $emp) {
            return redirect()->route('member.start');
        }

        $log = $emp->todayLog();
        $tasks = Task::where('employee_id', $emp->id)->where('status', 'open')->get();
        $isOff = OffDay::isOff(now()->toDateString());

        // missed days in the last week (not off, no saved log)
        $missed = [];
        for ($i = 1; $i <= 6; $i++) {
            $d = now()->subDays($i)->toDateString();
            if (! OffDay::isOff($d)) {
                $has = $emp->logs()->where('log_date', $d)->whereNotNull('saved_at')->exists();
                if (! $has) {
                    $missed[] = $d;
                }
            }
        }

        return view('portal.member-day', compact('emp', 'log', 'tasks', 'isOff', 'missed'));
    }

    /** Save today's blocks. */
    public function save(Request $request)
    {
        $emp = $this->current();
        if (! $emp) {
            return redirect()->route('member.start');
        }

        $data = $request->validate([
            'done' => 'nullable|string',
            'up' => 'nullable|string',
            'crit' => 'nullable|string',
            'saved' => 'nullable|string',
            'pending' => 'nullable|string',
        ]);

        $log = $emp->todayLog();
        $log->update([
            'done' => $data['done'] ?? $log->done,
            'upload' => $data['up'] ?? $log->upload,
            'critical' => $data['crit'] ?? $log->critical,
            'saved' => $data['saved'] ?? $log->saved,
            'pending' => $data['pending'] ?? $log->pending,
            'saved_at' => now(),
        ]);

        return back()->with('ok', 'Saved.');
    }

    /** Mark an assigned task done. */
    public function completeTask(Request $request, Task $task)
    {
        $emp = $this->current();
        if ($emp && $task->employee_id === $emp->id) {
            $task->update(['status' => 'done']);
        }
        return back();
    }

    public function leave()
    {
        session()->forget('member_id');
        return redirect()->route('member.start');
    }

    private function current(): ?Employee
    {
        $id = session('member_id');
        return $id ? Employee::active()->find($id) : null;
    }
}
