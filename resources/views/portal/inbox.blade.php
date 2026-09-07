@extends('layouts.portal')
@section('title','Support inbox')
@section('content')
<div class="sechead"><h2>Support inbox</h2><p>Web + desktop customers in one place</p></div>
<div class="inbox">
  <div class="ib-list">
    <div class="ib-list-head">Conversations <span class="tag tag-gray" style="margin-left:auto">{{ $conversations->where('status','open')->count() }} open</span></div>
    @forelse($conversations as $c)
      <a class="conv {{ $selected && $selected->id===$c->id ? 'on':'' }}" href="{{ route('portal.support',['c'=>$c->id]) }}">
        <div class="cr1"><b>{{ $c->name ?? $c->visitor }}</b>
          <span class="tag {{ $c->channel==='web'?'tag-blue':'tag-purple' }}">{{ $c->channel }}</span></div>
        <div class="prev">{{ optional($c->messages()->latest()->first())->text ?? 'New conversation' }}</div>
        <div class="cr2"><span class="tag tag-gray">{{ $c->company }}</span>
          <span class="mono" style="font-size:10px;color:var(--ink-3);margin-left:auto">{{ optional($c->last_at)->diffForHumans(null,true) }}</span></div>
      </a>
    @empty
      <div style="padding:20px;color:var(--ink-3);font-size:13px;text-align:center">No conversations yet.</div>
    @endforelse
  </div>
  <div class="ib-thread">
    @if($selected)
      <div class="ib-th-head"><span class="avatar">{{ strtoupper(substr($selected->name ?? 'C',0,2)) }}</span>
        <div style="flex:1"><b style="font-size:14px">{{ $selected->name ?? $selected->visitor }}</b>
          <div class="mono" style="font-size:11px;color:var(--ink-3)">{{ $selected->company }}</div></div>
        <span class="tag {{ $selected->status==='open'?'tag-green':'tag-gray' }}">{{ $selected->status }}</span></div>
      <div class="ib-msgs">
        @foreach($selected->messages as $m)
          <div class="bub {{ $m->sender==='agent'?'me':'you' }}">
            @if($m->text){{ $m->text }}@endif
            @if($m->image)<div style="margin-top:6px"><img src="{{ $m->image }}" style="max-width:200px;border-radius:6px"></div>@endif
            <span class="t">{{ $m->created_at->format('g:i A') }}</span></div>
        @endforeach
      </div>
      <div class="ib-foot"><form method="POST" action="{{ route('portal.support.reply',$selected) }}" class="crow">@csrf
        <textarea name="text" placeholder="Type a reply…" required></textarea>
        <button class="btn btn-primary" type="submit">Send</button></form></div>
    @else
      <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--ink-3);font-size:13px">Select a conversation</div>
    @endif
  </div>
  <div class="ib-info">
    <div class="ib-info-head">Customer context</div>
    @if($selected)
      <div class="info-sec"><h5>Identity</h5>
        <div class="info-row"><span class="k">Name</span><span class="v">{{ $selected->name ?? '—' }}</span></div>
        <div class="info-row"><span class="k">Company</span><span class="v">{{ $selected->company ?? '—' }}</span></div>
        <div class="info-row"><span class="k">Visitor</span><span class="v">{{ $selected->visitor }}</span></div></div>
      <div class="info-sec"><h5>Environment</h5>
        <div class="info-row"><span class="k">Channel</span><span class="v">{{ $selected->channel }}</span></div>
        <div class="info-row"><span class="k">Browser</span><span class="v">{{ $selected->browser ?? '—' }}</span></div>
        <div class="info-row"><span class="k">OS</span><span class="v">{{ $selected->os ?? '—' }}</span></div>
        <div class="info-row"><span class="k">City</span><span class="v">{{ $selected->city ?? '—' }}</span></div>
        <div class="info-row"><span class="k">IP</span><span class="v">{{ $selected->ip ?? '—' }}</span></div></div>
      <div class="info-sec"><h5>Context</h5>
        <div class="info-row"><span class="k">Page/App</span><span class="v">{{ $selected->page ?? $selected->app_label ?? '—' }}</span></div></div>
    @endif
  </div>
</div>
@endsection
