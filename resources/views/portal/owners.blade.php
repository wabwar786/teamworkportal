@extends('layouts.portal')
@section('title','Owners & heads')
@section('content')
@php $allScopes=['Dev team','Design team','Marketing','Sales','Support','Devices','Everything']; @endphp
<div class="sechead"><h2>Owners & heads</h2>
  <button class="btn btn-primary" style="margin-left:auto" onclick="document.getElementById('addHead').classList.remove('hide')">+ Add head</button></div>
<div class="orgchart">
  <div class="tier"><h4>Super owner</h4>
    @foreach($super as $o)
      <div class="person-row"><span class="avatar">{{ strtoupper(substr($o->name,0,2)) }}</span>
        <div class="pd"><b>{{ $o->name }}</b><span>{{ $o->email }}</span></div>
        <span class="tag tag-green">Full access</span></div>
    @endforeach</div>
  <div class="tier"><h4>Heads ({{ $heads->count() }})</h4>
    @forelse($heads as $o)
    @forelse($heads as $o)
      @php $teamCount = $employees->where('head_id', $o->id)->count(); @endphp
      <div class="person-row"><span class="avatar">{{ strtoupper(substr($o->name,0,2)) }}</span>
        <div class="pd"><b>{{ $o->name }}</b><span>{{ $o->email }}</span>
          <div class="scopes" style="margin-top:5px">@foreach($o->scopes ?? [] as $s)<span class="scope">{{ $s }}</span>@endforeach</div></div>
        <div style="text-align:right">
          <span class="tag tag-gray">{{ $teamCount }} team</span>
          <button class="btn btn-sm btn-ghost-red" style="margin-top:6px"
            onclick='delHead(@json(["id"=>$o->id,"name"=>$o->name,"team"=>$teamCount]))'>Delete</button>
        </div></div>
    @empty
      <div style="color:var(--ink-3);padding:8px">No heads yet.</div>
    @endforelse</div>
</div>

<div class="modal-bg hide" id="addHead">
  <form class="modal" method="POST" action="{{ route('portal.owners.store') }}">@csrf
    <h3>Add a head</h3><p class="msub">Heads see and manage only their scope.</p>
    <div class="field"><label>Name</label><input class="input" name="name" required></div>
    <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
    <div class="field"><label>Password</label><input class="input" type="text" name="password" required value="password"></div>
    <div class="field"><label>Scopes</label>
      @foreach($allScopes as $s)<label class="togrow"><input class="cx" type="checkbox" name="scopes[]" value="{{ $s }}"> {{ $s }}</label>@endforeach</div>
    <div class="modal-foot"><button type="button" class="btn" onclick="this.closest('.modal-bg').classList.add('hide')">Cancel</button>
      <button class="btn btn-primary" type="submit">Add head</button></div>
  </form></div>

<div class="modal-bg hide" id="delHeadModal">
  <form class="modal" method="POST" id="delHeadForm">@csrf @method('DELETE')
    <h3>Delete head?</h3>
    <div class="del-warn"><b id="dh_name"></b> will be removed.</div>
    <div id="dh_move" class="field hide"><label>Move their team to</label><select class="input" name="move_to">
      @foreach($heads as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach
      @foreach($super as $o)<option value="{{ $o->id }}">{{ $o->name }} (super)</option>@endforeach</select></div>
    <div class="modal-foot"><button type="button" class="btn" onclick="hide('delHeadModal')">Cancel</button>
      <button class="btn btn-red" type="submit">Delete</button></div>
  </form></div>
@endsection
@section('scripts')
function hide(id){document.getElementById(id).classList.add('hide')}
function delHead(o){
  document.getElementById('delHeadForm').action='{{ url('portal/owners') }}/'+o.id;
  document.getElementById('dh_name').textContent=o.name;
  document.getElementById('dh_move').classList.toggle('hide', o.team===0);
  document.getElementById('delHeadModal').classList.remove('hide');
}
@endsection
