@extends('layouts.portal')
@section('title','Devices')
@section('content')
<div class="legalnote"><span>ⓘ</span><span>Monitoring runs with a <b>visible tray icon</b> and a first-run consent screen on each PC. This is agreed workplace monitoring — not hidden surveillance.</span></div>
<div class="sechead"><h2>Devices</h2><span class="tag tag-gray">{{ $devices->count() }} enrolled</span></div>
<div class="devgrid">
  @forelse($devices as $d)
    <div class="devcard {{ $d->online?'on':'' }}" id="device-{{ $d->id }}">
      <div class="dh"><span class="dotlive {{ $d->online?'dot-on':'dot-off' }}"></span>
        <div style="flex:1"><b>{{ $d->pc_name ?? 'Unknown PC' }}</b><span>{{ optional($d->employee)->name ?? 'Unassigned' }}</span></div>
        <span class="tag {{ $d->online?'tag-green':'tag-gray' }}">{{ $d->online?'Online':'Offline' }}</span></div>
      <div class="devmeta">
        <div>OS <b>{{ $d->os ?? '—' }}</b></div><div>IP <b>{{ $d->ip ?? '—' }}</b></div>
        <div>Agent <b>{{ $d->agent_version ?? '—' }}</b></div><div>Seen <b>{{ optional($d->last_seen)->diffForHumans() ?? 'never' }}</b></div>
      </div>
      <div class="devacts">
        <a class="btn btn-sm" href="{{ route('portal.devices.activity') }}">Activity</a>
        <a class="btn btn-sm" href="{{ route('portal.devices.files') }}">Files</a>
        <a class="btn btn-sm" href="{{ route('portal.devices.remote') }}">Remote</a>
      </div>
    </div>
  @empty
    <div class="panel" style="grid-column:1/-1"><div class="panel-body" style="text-align:center;color:var(--ink-3);padding:24px">
      No devices enrolled yet. Install the agent on a PC to see it here.</div></div>
  @endforelse
</div>
@endsection
