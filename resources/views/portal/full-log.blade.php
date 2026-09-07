@extends('layouts.portal')
@section('title','Full log')
@section('content')
<div class="sechead"><h2>Full log</h2>
  <form method="GET" style="margin-left:auto"><label class="check">
    <input class="cx" type="checkbox" name="archived" value="1" {{ $includeArchived?'checked':'' }} onchange="this.form.submit()">
    Include archived employees</label></form></div>
@forelse($employees as $emp)
  <div class="panel" id="emp-{{ $emp->id }}" style="margin-bottom:13px">
    <div class="panel-head"><span class="avatar" style="width:24px;height:24px;font-size:10px">{{ $emp->initials() }}</span>
      <h3>{{ $emp->name }}</h3><span class="tag tag-gray">{{ $emp->role }}</span>
      @if($emp->archived)<span class="tag tag-amber">Archived</span>@endif
      <span class="tag tag-gray" style="margin-left:auto">{{ $emp->logs->count() }} days</span></div>
    <table><thead><tr><th>Date</th><th>Completed</th><th>Delivered</th><th>Pending</th><th>Critical</th></tr></thead><tbody>
      @foreach($emp->logs->take(14) as $log)
        <tr><td class="mono">{{ $log->log_date->format('d M') }}</td>
          <td class="mono" style="font-size:11px">{{ count($log->lines('done')) }}</td>
          <td class="mono" style="font-size:11px">{{ count($log->lines('upload')) }}</td>
          <td class="mono" style="font-size:11px;color:var(--red)">{{ count($log->lines('pending')) }}</td>
          <td class="mono" style="font-size:11px">{{ count($log->lines('critical')) }}</td></tr>
      @endforeach
      @if(!$emp->logs->count())<tr><td colspan="5" style="color:var(--ink-3)">No logs.</td></tr>@endif
    </tbody></table>
  </div>
@empty
  <div class="panel"><div class="panel-body" style="text-align:center;color:var(--ink-3);padding:24px">No employees.</div></div>
@endforelse
@endsection
