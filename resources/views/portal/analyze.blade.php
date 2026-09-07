@extends('layouts.portal')
@section('title','Analyze')
@section('content')
@php
  $totDone=$rows->sum('done');$totUp=$rows->sum('upload');$totCrit=$rows->sum('critical');$totPend=$rows->sum('pending');
  $max=max(1,$rows->max('score') ?? 1);
@endphp
<div class="sechead"><h2>Analyze</h2>
  <form method="GET" style="margin-left:auto;display:flex;gap:8px;align-items:center">
    <input class="input" type="date" name="date" value="{{ $date }}" style="width:auto" onchange="this.form.submit()"></form></div>
<div class="stats">
  <div class="stat"><div class="k">Completed</div><div class="v">{{ $totDone }}</div><div class="s">items today</div></div>
  <div class="stat"><div class="k">Delivered</div><div class="v">{{ $totUp }}</div><div class="s">uploads/builds</div></div>
  <div class="stat"><div class="k">Critical</div><div class="v">{{ $totCrit }}</div><div class="s">weighted ×2</div></div>
  <div class="stat"><div class="k">Pending</div><div class="v" style="color:var(--red)">{{ $totPend }}</div><div class="s">still open</div></div>
</div>
<div class="panel"><div class="panel-head"><h3>Productivity by person</h3><span class="tag tag-gray">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span></div>
<div class="panel-body">
  @forelse($rows as $r)
    @php $seg=fn($n)=>$r['score']?($n/max(1,$r['score']))*100:0; @endphp
    <div class="barrow">
      <div class="who-cell"><span class="avatar" style="width:24px;height:24px;font-size:10px">{{ $r['emp']->initials() }}</span>
        <b style="font-size:13px">{{ $r['emp']->name }}</b></div>
      <div class="bwrap">
        <div class="bseg b-done" style="width:{{ $seg($r['done']) }}%"></div>
        <div class="bseg b-up" style="width:{{ $seg($r['upload']) }}%"></div>
        <div class="bseg b-crit" style="width:{{ $seg($r['critical']*2) }}%"></div>
      </div>
      <div class="bscore">{{ $r['score'] }}</div>
    </div>
  @empty
    <div style="color:var(--ink-3);text-align:center;padding:20px">No data for this day.</div>
  @endforelse
  <div class="legend"><span><i class="b-done"></i>Completed</span><span><i class="b-up"></i>Delivered</span>
    <span><i class="b-crit"></i>Critical ×2</span></div>
</div></div>
@endsection
