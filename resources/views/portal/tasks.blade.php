@extends('layouts.portal')
@section('title','Critical tasks')
@section('content')
<div class="sechead"><h2>Critical tasks & messages</h2></div>
<div class="grid2">
  <div class="panel"><div class="panel-head"><h3>Assign a task</h3></div><div class="panel-body">
    <form method="POST" action="{{ route('portal.tasks.store') }}">@csrf
      <div class="field"><label>To whom</label><select class="input" name="employee_id" required>
        @foreach($team as $e)<option value="{{ $e->id }}">{{ $e->name }} — {{ $e->role }}</option>@endforeach</select></div>
      <div class="field"><label>Task</label><textarea class="input" name="body" rows="3" required placeholder="What needs doing…"></textarea></div>
      <div class="field"><label>Due (optional)</label><input class="input" name="due" placeholder="e.g. Tomorrow 12 PM"></div>
      <button class="btn btn-red" type="submit">Assign task</button>
    </form></div></div>
  <div class="panel"><div class="panel-head"><h3>Direct message (pops up on their screen)</h3></div><div class="panel-body">
    <form method="POST" action="{{ route('portal.tasks.message') }}">@csrf
      <div class="field"><label>To whom</label><select class="input" name="employee_id" required>
        @foreach($team as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select></div>
      <div class="field"><label>Message</label><textarea class="input" name="body" rows="3" required></textarea></div>
      <button class="btn btn-primary" type="submit">Send message</button>
    </form></div></div>
</div>
<div class="panel" style="margin-top:14px"><div class="panel-head"><h3>All tasks</h3></div>
  <table><thead><tr><th>Task</th><th>Assigned to</th><th>Due</th><th>Status</th></tr></thead><tbody>
    @forelse($tasks as $t)
      <tr id="task-{{ $t->id }}"><td>{{ $t->body }}</td><td>{{ $t->employee->name ?? '—' }}</td><td class="mono" style="font-size:11px">{{ $t->due }}</td>
        <td><span class="tag {{ $t->status==='done'?'tag-green':'tag-red' }}">{{ ucfirst($t->status) }}</span></td></tr>
    @empty
      <tr><td colspan="4" style="color:var(--ink-3);text-align:center;padding:20px">No tasks yet.</td></tr>
    @endforelse
  </tbody></table></div>
@endsection
