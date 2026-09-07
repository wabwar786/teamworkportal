@extends('layouts.portal')
@section('title','Activity')
@section('content')
<div class="sechead"><h2>App activity — today</h2></div>
@forelse($devices as $d)
  <div class="panel" style="margin-bottom:13px"><div class="panel-head">
    <span class="dotlive {{ $d->online?'dot-on':'dot-off' }}"></span><h3>{{ $d->pc_name }}</h3>
    <span class="tag tag-gray">{{ optional($d->employee)->name }}</span></div>
    <div class="panel-body"><div class="usebars">
      @php $mx=max(1,$d->appUsages->max('minutes') ?? 1); @endphp
      @forelse($d->appUsages as $a)
        <div class="userow"><span class="mono" style="font-size:12px">{{ $a->app_name }}</span>
          <div class="usewrap"><div class="useseg" style="width:{{ ($a->minutes/$mx)*100 }}%;background:var(--green)"></div></div>
          <span class="mono" style="font-size:11px;text-align:right">{{ $a->minutes }}m</span></div>
      @empty
        <div style="color:var(--ink-3);font-size:12.5px">No activity reported today.</div>
      @endforelse
    </div></div></div>
@empty
  <div class="panel"><div class="panel-body" style="text-align:center;color:var(--ink-3);padding:24px">No devices.</div></div>
@endforelse
@endsection
