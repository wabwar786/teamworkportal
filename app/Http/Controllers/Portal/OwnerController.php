<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Owners & heads (super owner only). Create heads with a limited scope,
 * delete a head (moving their team to another head first).
 */
class OwnerController extends Controller
{
    public function index()
    {
        return view('portal.owners', [
            'super' => Owner::where('tier', 'super')->get(),
            'heads' => Owner::where('tier', 'head')->get(),
            'employees' => Employee::active()->with('head')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:owners,email',
            'password' => 'required|string|min:6',
            'scopes' => 'array',
        ]);

        Owner::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tier' => 'head',
            'scopes' => $data['scopes'] ?? ['Support'],
            'active' => true,
        ]);

        return back()->with('ok', $data['name'].' is now a head.');
    }

    public function update(Request $request, Owner $owner)
    {
        abort_if($owner->isSuper(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'scopes' => 'array',
            'active' => 'nullable|boolean',
        ]);

        $owner->update([
            'name' => $data['name'],
            'scopes' => $data['scopes'] ?? [],
            'active' => (bool) ($data['active'] ?? true),
        ]);

        return back()->with('ok', 'Saved.');
    }

    /** Delete a head; reassign their team to another head first. */
    public function destroy(Request $request, Owner $owner)
    {
        abort_if($owner->isSuper(), 403);

        $team = Employee::where('head_id', $owner->id)->where('archived', false)->get();

        if ($team->isNotEmpty()) {
            $data = $request->validate(['move_to' => 'required|exists:owners,id']);
            Employee::where('head_id', $owner->id)->update(['head_id' => $data['move_to']]);
        }

        $owner->delete();

        return back()->with('warn', $owner->name.' deleted.');
    }
}
