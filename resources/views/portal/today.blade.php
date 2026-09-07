@extends('layouts.portal')
@section('title','Today')
@section('content')
@php
  $sec = function($h,$lines,$red=false){
    if(!count($lines)) return '';
    $out = '<div class="log-sec"><h5'.($red?' style="color:var(--red)"':'').'>'.$h.'</h5>';
    foreach($lines as $l) $out .= '<div class="log-line"'.($red?' style="color:var(--red)"':'').'>'.e($l).'</div>';
    return $out.'</div>';
  };
@endphp
<div class="workgrid">
  <div>
    <div class="sechead"><h2>What everyone did today</h2>
      <span class="tag tag-gray">{{ $u->isHead()?'My team':'Whole team' }} · {{ now()->format('d M') }}</span></div>
    @forelse($team as $emp)
      @php $log = $emp->logs->first(); @endphp
      @if($log)
        <div class="panel" style="margin-bottom:13px">
          <div class="panel-head"><span class="avatar" style="width:24px;height:24px;font-size:10px">{{ $emp->initials() }}</span>
            <h3>{{ $emp->name }}</h3><span class="tag tag-gray">{{ $emp->role }}</span>
            <span class="tag tag-green">{{ $log->score() }} pts</span></div>
          <div class="panel-body">
            {!! $sec('Completed', $log->lines('done')) !!}
            {!! $sec('Delivered', $log->lines('upload')) !!}
            {!! $sec('Critical', $log->lines('critical')) !!}
            {!! $sec('Pending', $log->lines('pending'), true) !!}
            @if($log->saved)<div class="log-sec"><h5>Saved</h5><div class="log-line" style="white-space:pre-wrap">{{ $log->saved }}</div></div>@endif
          </div>
        </div>
      @else
        <div class="panel" style="margin-bottom:13px"><div class="panel-head">
          <span class="avatar" style="width:24px;height:24px;font-size:10px">{{ $emp->initials() }}</span>
          <h3>{{ $emp->name }}</h3><span class="tag tag-gray">{{ $emp->role }}</span>
          <span class="tag tag-red">Nothing logged today</span></div></div>
      @endif
    @empty
      <div class="panel"><div class="panel-body" style="color:var(--ink-3);text-align:center;padding:24px">No team members yet.</div></div>
    @endforelse
  </div>
  <div class="rail">
    <div class="rail-card rail-crit"><div class="rail-head"><h3>Assigned tasks</h3><span class="n">{{ $openTasks->count() }}</span></div>
      @foreach($openTasks as $t)
        <div class="ritem"><div class="top"><span style="flex:1">{{ $t->body }}</span></div>
          <span class="due">{{ $t->employee->name }} · {{ $t->due }}</span></div>
      @endforeach
      <a class="btn btn-sm btn-red" style="width:100%;margin-top:8px;justify-content:center" href="{{ route('portal.tasks') }}">Assign a task</a></div>
    <div class="rail-card rail-pend"><div class="rail-head"><h3>Team's pending</h3><span class="n">{{ count($pending) }}</span></div>
      @foreach($pending as $p)
        <div class="ritem"><div class="top"><span style="flex:1">{{ $p['text'] }}</span></div>
          <span class="due">{{ $p['emp']->name }}</span></div>
      @endforeach
      @if(!count($pending))<div style="font-size:12.5px;color:var(--ink-2)">All clear.</div>@endif
    </div>
  </div>
</div>
@endsection
