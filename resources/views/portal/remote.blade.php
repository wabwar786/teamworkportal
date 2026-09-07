@extends('layouts.portal')
@section('title','Remote actions')
@section('content')
<div class="legalnote"><span>ⓘ</span><span>Only these <b>safe, named actions</b> are available. There is deliberately no free-text command box — the agent can't run arbitrary commands.</span></div>
<div class="sechead"><h2>Remote actions</h2></div>
<div class="panel" style="margin-bottom:14px"><div class="panel-head"><h3>Run an action</h3></div><div class="panel-body">
  <form method="POST" action="{{ route('portal.devices.remote.run') }}">@csrf
    <div class="field"><label>Device</label><select class="input" name="device_id" required>
      @foreach($devices as $d)<option value="{{ $d->id }}">{{ $d->pc_name }} — {{ optional($d->employee)->name }}</option>@endforeach</select></div>
    <div class="field"><label>Action</label><select class="input" name="action" id="actSel" required>
      @foreach($actions as $k=>$v)<option value="{{ $k }}">{{ ucfirst(str_replace('_',' ',$k)) }} — {{ $v }}</option>@endforeach</select></div>
    <div class="field" id="textWrap"><label>Message / detail (for message, app_close, folder_sync)</label>
      <input class="input" name="text" placeholder="Text to show / app name / folder path"></div>
    <button class="btn btn-primary" type="submit">Queue action</button>
  </form></div></div>
<div class="panel"><div class="panel-head"><h3>Recent actions</h3></div>
  <table><thead><tr><th>When</th><th>Device</th><th>Action</th><th>Status</th></tr></thead><tbody>
    @forelse($commands as $c)
      <tr><td class="mono" style="font-size:11px">{{ $c->created_at->format('d M H:i') }}</td>
        <td>{{ optional($c->device)->pc_name }}</td>
        <td class="mono" style="font-size:12px">{{ $c->action }}</td>
        <td><span class="tag {{ ['queued'=>'tag-amber','sent'=>'tag-blue','done'=>'tag-green','failed'=>'tag-red'][$c->status] }}">{{ $c->status }}</span></td></tr>
    @empty
      <tr><td colspan="4" style="color:var(--ink-3);text-align:center;padding:20px">No actions yet.</td></tr>
    @endforelse
  </tbody></table></div>
@endsection
