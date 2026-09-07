@extends('layouts.portal')
@section('title','Messages')

@section('content')
<div class="sechead"><h2>Team messages</h2><p>Chat with members and other owners — text and images, all saved</p>
  @if($u->isSuper())<a class="btn btn-sm" style="margin-left:auto" href="{{ route('portal.messages.all') }}">See all team messages →</a>@endif</div>

<div style="max-width:520px">
  <div class="panel"><div class="panel-body" style="text-align:center;padding:26px">
    <div style="font-size:34px;margin-bottom:8px">💬</div>
    <p style="font-size:14px;color:var(--ink-2);margin-bottom:14px">Open the chat panel to message your team. Unread messages show a red badge here and update live.</p>
    <button class="btn btn-primary btn-lg" onclick="Chat.open()">
      Open messages <span class="chat-badge hide" id="chatBadge" style="position:static;margin-left:6px">0</span></button>
  </div></div>
</div>

@include('portal.partials.chat', [
  'base' => url('portal/messages'),
  'sendUrl' => route('portal.messages.send'),
  'contactsUrl' => route('portal.messages.contacts'),
  'pollUrl' => route('portal.messages.poll'),
])
@endsection
