@extends('layouts.portal')
@section('title','File & USB')
@section('content')
<div class="sechead"><h2>File & USB events</h2><p>Copy / move / delete and USB insertions reported by agents</p></div>
<div class="panel"><table><thead><tr><th>When</th><th>Device</th><th>Type</th><th>Path</th><th>Destination</th></tr></thead><tbody>
  @forelse($events as $e)
    <tr><td class="mono" style="font-size:11px">{{ optional($e->happened_at)->format('d M H:i') }}</td>
      <td>{{ optional($e->device)->pc_name }}</td>
      <td><span class="tag {{ ['copy'=>'tag-blue','move'=>'tag-amber','delete'=>'tag-red','usb'=>'tag-purple','app'=>'tag-gray'][$e->type] }}">{{ strtoupper($e->type) }}</span></td>
      <td class="mono" style="font-size:11px">{{ \Illuminate\Support\Str::limit($e->path,50) }}</td>
      <td class="mono" style="font-size:11px;color:var(--ink-3)">{{ \Illuminate\Support\Str::limit($e->destination,40) }}</td></tr>
  @empty
    <tr><td colspan="5" style="color:var(--ink-3);text-align:center;padding:24px">No events yet.</td></tr>
  @endforelse
</tbody></table></div>
@endsection
