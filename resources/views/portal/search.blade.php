@extends('layouts.portal')
@section('title','Search')

@section('content')
<div class="sechead"><h2>Search</h2>
  @if($q !== '')<p>{{ $total }} result{{ $total===1?'':'s' }} for "<b>{{ $q }}</b>"</p>@endif</div>

<form method="GET" action="{{ route('portal.search') }}" style="max-width:560px;margin-bottom:18px;display:flex;gap:8px">
  <input class="input" name="q" value="{{ $q }}" placeholder="Search employees, logs, tasks, devices, chats…" autofocus>
  <button class="btn btn-primary" type="submit">Search</button>
</form>

@if($q === '')
  <div class="panel"><div class="panel-body" style="color:var(--ink-3);padding:22px">
    Type anything above — it searches across employees, work logs, tasks, owners, devices, support customers, support chats and team messages.</div></div>
@elseif($total === 0)
  <div class="panel"><div class="panel-body" style="text-align:center;color:var(--ink-3);padding:28px">
    Nothing found for "<b>{{ $q }}</b>". Try a different word.</div></div>
@else
  @foreach($groups as $label => $items)
    <div class="panel" style="margin-bottom:13px">
      <div class="panel-head"><h3>{{ $label }}</h3><span class="tag tag-gray">{{ $items->count() }}</span></div>
      <div>
        @foreach($items as $r)
          <a href="{{ $r['url'] }}" class="sresult">
            <span class="sr-main"><b>{{ $r['title'] }}</b>@if(!empty($r['sub']))<span>{{ $r['sub'] }}</span>@endif</span>
            <span class="sr-go">→</span>
          </a>
        @endforeach
      </div>
    </div>
  @endforeach
@endif

<style>
.sresult{display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--line)}
.sresult:last-child{border-bottom:none}
.sresult:hover{background:var(--card-2)}
.sr-main{flex:1;min-width:0}
.sr-main b{font-size:13.5px;font-weight:500;display:block}
.sr-main span{font-size:12px;color:var(--ink-3);font-family:var(--mono);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sr-go{color:var(--ink-3);font-size:15px}
</style>
@endsection
