@extends('layouts.portal')
@section('title','Employees')

@section('content')
@php $blockLabels = ['done'=>'Completed','up'=>'Delivered/upload','crit'=>'Critical','saved'=>'Saved']; @endphp

<div class="sechead"><h2>{{ $u->isHead() ? 'My team' : 'Employees' }}</h2>
  <span class="tag tag-gray">{{ $active->count() }} active</span>
  <button class="btn btn-primary" style="margin-left:auto" onclick="showAdd()">+ New employee</button></div>

<div class="panel"><div class="panel-head"><h3>Active employees</h3></div>
  <table><thead><tr><th>Name</th><th>Role</th><th>Head</th><th>Blocks</th><th>Joined</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($active as $e)
      <tr id="emp-{{ $e->id }}">
        <td><div class="who-cell"><span class="avatar" style="width:28px;height:28px;font-size:10px">{{ $e->initials() }}</span>
          <span><b>{{ $e->name }}</b><span>{{ $e->slug }}</span></span></div></td>
        <td>{{ $e->role }}</td>
        <td>{{ optional($e->head)->name ? explode(' ',$e->head->name)[0] : '—' }}</td>
        <td class="mono" style="font-size:11px">{{ implode('·', array_map(fn($b)=>['done'=>1,'up'=>2,'crit'=>3,'saved'=>4][$b] ?? '', $e->blocks ?? [])) }}</td>
        <td class="mono" style="font-size:11px;color:var(--ink-3)">{{ optional($e->joined_at)->format('d M Y') }}</td>
        <td><span class="tag {{ $e->active ? 'tag-green' : 'tag-amber' }}">{{ $e->active ? 'Active' : 'Suspended' }}</span></td>
        <td style="text-align:right;white-space:nowrap">
          <button class="btn btn-sm" onclick='showEdit(@json($e))'>Edit</button>
          <button class="btn btn-sm btn-ghost-red" onclick='showDelete(@json(["id"=>$e->id,"name"=>$e->name]))'>Delete</button>
        </td>
      </tr>
    @empty
      <tr><td colspan="7" style="color:var(--ink-3);text-align:center;padding:24px">No employees yet.</td></tr>
    @endforelse
    </tbody></table></div>

@if($archived->count())
<div class="sechead" style="margin-top:20px"><h2 style="font-size:15px">Archived employees</h2>
  <p>Removed from the team — their full log is preserved, only hidden from the portal</p></div>
<div class="panel"><table><thead><tr><th>Name</th><th>Role</th><th>Left</th><th>Log</th><th></th></tr></thead>
  <tbody>
  @foreach($archived as $e)
    <tr class="archived">
      <td><div class="who-cell"><span class="avatar" style="width:28px;height:28px;font-size:10px">{{ $e->initials() }}</span>
        <span><b>{{ $e->name }}</b><span>archived</span></span></div></td>
      <td>{{ $e->role }}</td>
      <td class="mono" style="font-size:11px">{{ optional($e->left_at)->format('d M Y') ?? '—' }}</td>
      <td class="mono" style="font-size:11px">{{ $e->logs()->count() }} log days</td>
      <td style="text-align:right;white-space:nowrap">
        <a class="btn btn-sm" href="{{ route('portal.employees.log',$e) }}">View log</a>
        <form method="POST" action="{{ route('portal.employees.restore',$e) }}" style="display:inline">@csrf
          <button class="btn btn-sm">Restore</button></form>
      </td>
    </tr>
  @endforeach
  </tbody></table></div>
@endif

{{-- ADD modal --}}
<div class="modal-bg hide" id="addModal">
  <form class="modal" method="POST" action="{{ route('portal.employees.store') }}">@csrf
    <h3>New employee</h3><p class="msub">They'll appear on the start screen, then pick their name to begin.</p>
    <div class="field"><label>Full name</label><input class="input" name="name" required placeholder="e.g. Imran Sheikh"></div>
    <div class="field"><label>Role</label><select class="input" name="role">
      @foreach($roles as $r)<option>{{ $r }}</option>@endforeach</select></div>
    @if($u->isSuper())
    <div class="field"><label>Under which head</label><select class="input" name="head_id">
      @foreach($heads as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach</select></div>
    @endif
    <div class="field"><label>Which blocks they see</label>
      @foreach($blockLabels as $k=>$lbl)
        <label class="togrow"><input type="checkbox" class="cx" name="blocks[]" value="{{ $k }}" {{ $k!=='saved'||true ? 'checked':'' }} {{ $k==='done'?'disabled checked':'' }}>
          <span>{{ ['done'=>1,'up'=>2,'crit'=>3,'saved'=>4][$k] }}. {{ $lbl }}{{ $k==='done'?' (always)':'' }}</span></label>
      @endforeach
    </div>
    <div class="modal-foot"><button type="button" class="btn" onclick="hide('addModal')">Cancel</button>
      <button class="btn btn-primary" type="submit">Add</button></div>
  </form>
</div>

{{-- EDIT modal --}}
<div class="modal-bg hide" id="editModal">
  <form class="modal" method="POST" id="editForm">@csrf @method('PUT')
    <h3>Edit employee</h3>
    <div class="field"><label>Name</label><input class="input" name="name" id="ed_name"></div>
    <div class="field"><label>Role</label><select class="input" name="role" id="ed_role">
      @foreach($roles as $r)<option>{{ $r }}</option>@endforeach</select></div>
    <div class="field"><label>Which blocks</label>
      @foreach($blockLabels as $k=>$lbl)
        <label class="togrow"><input type="checkbox" class="cx ed_block" data-b="{{ $k }}" name="blocks[]" value="{{ $k }}" {{ $k==='done'?'disabled checked':'' }}>
          <span>{{ ['done'=>1,'up'=>2,'crit'=>3,'saved'=>4][$k] }}. {{ $lbl }}{{ $k==='done'?' (always)':'' }}</span></label>
      @endforeach
    </div>
    <label class="check"><input type="checkbox" class="cx" name="active" id="ed_active" value="1"> Active (uncheck = suspend — login blocked, log stays)</label>
    <div class="modal-foot"><button type="button" class="btn" onclick="hide('editModal')">Cancel</button>
      <button class="btn btn-primary" type="submit">Save</button></div>
  </form>
</div>

{{-- DELETE modal --}}
<div class="modal-bg hide" id="delModal">
  <form class="modal" method="POST" id="delForm">@csrf @method('DELETE')
    <h3>Delete this employee?</h3>
    <div class="del-warn"><b id="del_name"></b> will be removed from the team. They can no longer log in and drop off the active list.</div>
    <div class="keep-note"><span>✓</span><span><b>The log will NOT be deleted.</b> All of this employee's past entries and saved passwords/links stay preserved under "Archived". You can view them any time or restore the person.</span></div>
    <div class="modal-foot"><button type="button" class="btn" onclick="hide('delModal')">Cancel</button>
      <button class="btn btn-red" type="submit">Yes, delete</button></div>
  </form>
</div>
@endsection

@section('scripts')
function show(id){document.getElementById(id).classList.remove('hide')}
function hide(id){document.getElementById(id).classList.add('hide')}
function showAdd(){show('addModal')}
function showEdit(e){
  const f=document.getElementById('editForm');
  f.action='{{ url('portal/employees') }}/'+e.id;
  document.getElementById('ed_name').value=e.name;
  document.getElementById('ed_role').value=e.role;
  document.getElementById('ed_active').checked=!!e.active;
  document.querySelectorAll('.ed_block').forEach(c=>{ if(c.dataset.b!=='done') c.checked=(e.blocks||[]).includes(c.dataset.b); });
  show('editModal');
}
function showDelete(e){
  document.getElementById('delForm').action='{{ url('portal/employees') }}/'+e.id;
  document.getElementById('del_name').textContent=e.name;
  show('delModal');
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.modal-bg').forEach(m=>m.classList.add('hide'))});
@endsection
