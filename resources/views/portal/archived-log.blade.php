@extends('layouts.portal')
@section('title','Archived log')
@section('content')
<div class="sechead"><a class="btn btn-sm" href="{{ route('portal.employees') }}">← Back</a>
  <h2 style="margin-left:6px">{{ $employee->name }}</h2><span class="tag tag-amber">Archived</span>
  <span class="tag tag-gray">log preserved</span></div>
<div class="keep-note" style="margin-bottom:14px"><span>✓</span><span>This person was archived on {{ optional($employee->left_at)->format('d M Y') }}. Nothing below was deleted.</span></div>
@forelse($logs as $log)
  <div class="panel" style="margin-bottom:11px"><div class="panel-head"><h3>{{ $log->log_date->format('l, d M Y') }}</h3>
    <span class="tag tag-gray">{{ $log->score() }} pts</span></div>
    <div class="panel-body">
      @foreach(['done'=>'Completed','upload'=>'Delivered','critical'=>'Critical','pending'=>'Pending'] as $f=>$h)
        @if(count($log->lines($f)))<div class="log-sec"><h5>{{ $h }}</h5>
          @foreach($log->lines($f) as $l)<div class="log-line">{{ $l }}</div>@endforeach</div>@endif
      @endforeach
      @if($log->saved)<div class="log-sec"><h5>Saved</h5><div class="log-line" style="white-space:pre-wrap">{{ $log->saved }}</div></div>@endif
    </div></div>
@empty
  <div class="panel"><div class="panel-body" style="text-align:center;color:var(--ink-3);padding:24px">No log entries.</div></div>
@endforelse
@endsection
