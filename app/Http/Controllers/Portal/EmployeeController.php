<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /** List active + archived employees (heads see only their own team). */
    public function index(Request $request)
    {
        $owner = $request->user();

        $activeQuery = Employee::active()->with('head');
        $archivedQuery = Employee::archived()->with('head');

        if ($owner->isHead()) {
            $activeQuery->where('head_id', $owner->id);
            $archivedQuery->where('head_id', $owner->id);
        }

        return view('portal.employees', [
            'active' => $activeQuery->orderBy('name')->get(),
            'archived' => $archivedQuery->orderBy('name')->get(),
            'heads' => Owner::where('tier', 'head')->orderBy('name')->get(),
            'roles' => ['Developer', 'Designer', 'Marketing', 'Sales', 'Other'],
        ]);
    }

    /** Create a new employee. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'required|string|max:40',
            'head_id' => 'nullable|exists:owners,id',
            'blocks' => 'array',
            'shared_note' => 'nullable|string',
        ]);

        $owner = $request->user();

        Employee::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'role' => $data['role'],
            'head_id' => $owner->isHead() ? $owner->id : ($data['head_id'] ?? null),
            'blocks' => $this->normalizeBlocks($data['blocks'] ?? []),
            'shared_note' => $data['shared_note'] ?? null,
            'joined_at' => now(),
            'active' => true,
            'archived' => false,
        ]);

        return back()->with('ok', $data['name'].' added.');
    }

    /** Edit an existing employee. */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'required|string|max:40',
            'blocks' => 'array',
            'shared_note' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $employee->update([
            'name' => $data['name'],
            'role' => $data['role'],
            'blocks' => $this->normalizeBlocks($data['blocks'] ?? []),
            'shared_note' => $data['shared_note'] ?? $employee->shared_note,
            'active' => (bool) ($data['active'] ?? false),
        ]);

        return back()->with('ok', 'Saved.');
    }

    /**
     * "Delete" an employee. This ARCHIVES them: they are removed from the
     * active portal and can no longer log in, but the row and ALL their
     * work logs are preserved. Nothing is erased.
     */
    public function destroy(Employee $employee)
    {
        $employee->update([
            'archived' => true,
            'active' => false,
            'left_at' => now(),
        ]);

        return back()->with('warn', $employee->name.' archived — log preserved.');
    }

    /** Bring an archived employee back. */
    public function restore(Employee $employee)
    {
        $employee->update([
            'archived' => false,
            'active' => true,
            'left_at' => null,
        ]);

        return back()->with('ok', $employee->name.' is active again.');
    }

    /** View the preserved log of an archived employee. */
    public function archivedLog(Employee $employee)
    {
        return view('portal.archived-log', [
            'employee' => $employee,
            'logs' => $employee->logs()->orderByDesc('log_date')->get(),
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'emp';
        $slug = $base;
        $i = 2;
        while (Employee::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    /** 'done' is always included; keep only known block keys. */
    private function normalizeBlocks(array $blocks): array
    {
        $allowed = ['done', 'up', 'crit', 'saved'];
        $blocks = array_values(array_intersect($allowed, $blocks));
        if (! in_array('done', $blocks)) {
            array_unshift($blocks, 'done');
        }
        return $blocks;
    }
}
