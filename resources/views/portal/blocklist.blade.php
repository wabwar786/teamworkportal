@extends('layouts.portal')
@section('title','Blocklist')
@section('content')
<div class="sechead"><h2>Blocklist</h2><p>Block sites and apps on all devices or one</p></div>
<div class="grid2">
  <div class="panel"><div class="panel-head"><h3>Blocked sites</h3></div><div class="panel-body">
    <form method="POST" action="{{ route('portal.devices.block.add') }}" style="display:flex;gap:8px;margin-bottom:12px">@csrf
      <input type="hidden" name="scope" value="site">
      <input class="input" name="value" placeholder="facebook.com" required>
      <select class="input" name="target" style="width:auto"><option value="all">All</option>
        @foreach($devices as $d)<option value="{{ $d->id }}">{{ $d->pc_name }}</option>@endforeach</select>
      <button class="btn btn-red" type="submit">Block</button></form>
    @foreach($sites as $s)
      <div class="togrow"><span class="mono" style="flex:1;font-size:12px">{{ $s->value }}</span>
        <span class="tag tag-gray">{{ $s->target==='all'?'All':'1 device' }}</span>
        <form method="POST" action="{{ route('portal.devices.block.remove',$s) }}">@csrf @method('DELETE')
          <button class="btn btn-sm">Remove</button></form></div>
    @endforeach</div></div>
  <div class="panel"><div class="panel-head"><h3>Blocked apps</h3></div><div class="panel-body">
    <form method="POST" action="{{ route('portal.devices.block.add') }}" style="display:flex;gap:8px;margin-bottom:12px">@csrf
      <input type="hidden" name="scope" value="app">
      <input class="input" name="value" placeholder="steam.exe" required>
      <select class="input" name="target" style="width:auto"><option value="all">All</option>
        @foreach($devices as $d)<option value="{{ $d->id }}">{{ $d->pc_name }}</option>@endforeach</select>
      <button class="btn btn-red" type="submit">Block</button></form>
    @foreach($apps as $a)
      <div class="togrow"><span class="mono" style="flex:1;font-size:12px">{{ $a->value }}</span>
        <span class="tag tag-gray">{{ $a->target==='all'?'All':'1 device' }}</span>
        <form method="POST" action="{{ route('portal.devices.block.remove',$a) }}">@csrf @method('DELETE')
          <button class="btn btn-sm">Remove</button></form></div>
    @endforeach</div></div>
</div>
@endsection
