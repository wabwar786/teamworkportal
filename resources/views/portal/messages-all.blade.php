@extends('layouts.portal')
@section('title','All team messages')

@section('content')
<div class="sechead"><a class="btn btn-sm" href="{{ route('portal.messages') }}">← Messages</a>
  <h2 style="margin-left:6px">All team messages</h2>
  <span class="tag tag-gray">{{ $total }} shown</span>
  <span class="tag tag-purple">super view</span></div>

<div class="legalnote" style="background:var(--purple-soft);border-color:#C9C5F5;color:#3B348F">
  <span>ⓘ</span><span>As the super owner you can see every message exchanged in the team — owner↔member and member↔member. Newest first.</span></div>

<div class="panel">
  <table><thead><tr><th>When</th><th>From</th><th>To</th><th>Message</th><th>Read</th></tr></thead><tbody>
    @forelse($rows as $r)
      <tr>
        <td class="mono" style="font-size:11px;white-space:nowrap">{{ $r['at'] }}</td>
        <td style="white-space:nowrap">{{ $r['from'] }}</td>
        <td style="white-space:nowrap">{{ $r['to'] }}</td>
        <td>
          @if($r['body']){{ $r['body'] }}@endif
          @if($r['image'])<div style="margin-top:4px"><img src="{{ $r['image'] }}" style="max-width:140px;border-radius:6px;cursor:pointer" onclick="window.open(this.src)"></div>@endif
        </td>
        <td><span class="tag {{ $r['read'] ? 'tag-green' : 'tag-amber' }}">{{ $r['read'] ? 'read' : 'unread' }}</span></td>
      </tr>
    @empty
      <tr><td colspan="5" style="text-align:center;color:var(--ink-3);padding:26px">No messages yet.</td></tr>
    @endforelse
  </tbody></table>
</div>
@endsection
