<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\InternalMessage;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $q = Employee::active();
        if ($request->user()->isHead()) {
            $q->where('head_id', $request->user()->id);
        }

        return view('portal.tasks', [
            'team' => $q->orderBy('name')->get(),
            'tasks' => Task::with('employee')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'body' => 'required|string',
            'due' => 'nullable|string|max:60',
        ]);

        Task::create([
            'employee_id' => $data['employee_id'],
            'assigned_by' => $request->user()->id,
            'body' => $data['body'],
            'due' => $data['due'] ?? 'Today',
            'status' => 'open',
        ]);

        return back()->with('ok', 'Assigned.');
    }

    /** Send a direct message to an employee (pops up on their screen). */
    public function message(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'body' => 'required|string',
        ]);

        InternalMessage::create([
            'from_ref' => 'owner:'.$request->user()->id,
            'to_ref' => 'emp:'.$data['employee_id'],
            'body' => $data['body'],
        ]);

        return back()->with('ok', 'Sent.');
    }
}
