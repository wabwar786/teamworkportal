<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My day — {{ $emp->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--paper:#E7E8E3;--card:#FCFCFA;--card-2:#F4F5F1;--ink:#191C19;--ink-2:#5D625C;--ink-3:#8E938C;--line:#D8DAD3;--line-2:#C4C7BE;
--green:#14614E;--green-soft:#E1EEE8;--red:#9E3229;--red-soft:#F7E4E1;--red-line:#E4C7C2;--amber:#8A5B14;--amber-soft:#F7EBD7;--r:6px;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'IBM Plex Sans',system-ui,sans-serif;background:var(--paper);color:var(--ink);font-size:14px}
button{cursor:pointer;font:inherit;color:inherit;border:none;background:none}
.mono{font-family:'IBM Plex Mono',monospace}.hide{display:none!important}
.topbar{height:54px;display:flex;align-items:center;gap:12px;padding:0 18px;background:var(--card);border-bottom:1px solid var(--line-2);position:sticky;top:0;z-index:20}
.av{width:32px;height:32px;border-radius:7px;background:var(--green);color:var(--card);font-family:'IBM Plex Mono',monospace;font-size:12px;display:flex;align-items:center;justify-content:center}
.who b{font-size:14px;font-weight:600;display:block;line-height:1.2}.who span{font-size:11px;color:var(--ink-3);font-family:'IBM Plex Mono',monospace}
.btn{border:1px solid var(--line-2);background:var(--card);padding:8px 13px;border-radius:var(--r);font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px}
.btn:hover{background:var(--card-2)}
.btn-primary{background:var(--green);border-color:var(--green);color:#F3F7F5}.btn-primary:hover{background:#0F4E3E}
.tag{display:inline-block;font-size:10px;font-weight:500;letter-spacing:.05em;text-transform:uppercase;padding:3px 7px;border-radius:3px;font-family:'IBM Plex Mono',monospace}
.tag-green{background:var(--green-soft);color:var(--green)}.tag-amber{background:var(--amber-soft);color:var(--amber)}.tag-red{background:var(--red-soft);color:var(--red)}
.wrap{max-width:1180px;margin:0 auto;padding:20px 18px 70px}
.daymeta{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.daymeta h1{font-size:20px;font-weight:600}
.workgrid{display:grid;grid-template-columns:minmax(0,1fr) 316px;gap:16px;align-items:start}
.block{background:var(--card);border:1px solid var(--line-2);border-radius:8px;padding:13px 15px;margin-bottom:13px}
.block.hero{border-color:var(--green);border-top:3px solid var(--green)}
.block-head{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.block-num{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink-3);border:1px solid var(--line-2);border-radius:3px;padding:1px 5px}
.block-head h3{font-size:13px;font-weight:600;flex:1}
textarea.box{width:100%;border:1px solid var(--line-2);border-radius:var(--r);background:#fff;padding:9px 11px;font-family:'IBM Plex Mono',monospace;font-size:12.5px;line-height:1.7;resize:vertical;min-height:92px}
.block.hero textarea.box{min-height:120px}
.two{display:grid;grid-template-columns:1fr 1fr;gap:13px}.two .block{margin-bottom:0}
.rail{display:flex;flex-direction:column;gap:13px;position:sticky;top:70px}
.rail-card{border-radius:0 8px 8px 0;padding:12px 14px}
.rail-crit{background:#FBEEEC;border:1px solid var(--red-line);border-left:4px solid var(--red)}
.rail-pend{background:var(--card);border:1px solid var(--red-line);border-left:4px solid var(--red)}
.rail-head{display:flex;align-items:center;gap:8px;margin-bottom:9px}
.rail-head h3{font-size:13px;font-weight:600;color:var(--red);flex:1}
.rail-head .n{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--red);background:#F2D9D5;padding:2px 7px;border-radius:10px}
.ritem{background:var(--card);border:1px solid var(--red-line);border-radius:var(--r);padding:9px 11px;margin-bottom:7px;font-size:13px}
.ritem.done{opacity:.5;text-decoration:line-through}
.ritem .due{font-family:'IBM Plex Mono',monospace;font-size:10.5px;color:var(--red);margin-top:5px;display:block}
.ritem-top{display:flex;gap:8px;align-items:flex-start}
.savebar{position:fixed;bottom:0;left:0;right:0;background:var(--card);border-top:1px solid var(--line-2);padding:11px 18px;display:flex;align-items:center;gap:12px;z-index:15}
.savebar .status{font-size:12px;color:var(--ink-3);font-family:'IBM Plex Mono',monospace;margin-left:auto}
.offbanner{background:var(--amber-soft);border:1px solid #E4CFA6;border-radius:8px;padding:11px 14px;font-size:13px;color:var(--amber);margin-bottom:14px;display:flex;gap:9px}
.missed{background:var(--red-soft);border:1px solid var(--red-line);border-radius:8px;padding:11px 14px;font-size:13px;color:#6E241C;margin-bottom:14px}
.missed b{color:var(--red)}
.note-pop{position:fixed;inset:0;background:rgba(25,28,25,.42);display:flex;align-items:center;justify-content:center;padding:24px;z-index:40}
.note-card{background:var(--card);border:1px solid var(--line-2);border-top:4px solid var(--green);border-radius:10px;max-width:420px;padding:22px 24px}
.note-card h3{font-size:16px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px}
.note-card p{font-size:14px;color:var(--ink-2);line-height:1.6;margin-bottom:16px}
@media (max-width:900px){.workgrid{grid-template-columns:1fr}.two{grid-template-columns:1fr}.rail{position:static}}
</style>
</head>
<body>
<div class="topbar">
  <div class="av">{{ $emp->initials() }}</div>
  <div class="who"><b>{{ $emp->name }}</b><span>{{ $emp->role }}</span></div>
  <span class="tag tag-green" id="savedTag">Saved {{ optional($log->saved_at)->format('g:i A') }}</span>
  <form method="POST" action="{{ route('member.leave') }}" style="margin-left:auto">@csrf<button class="btn">Not me / switch</button></form>
</div>

<div class="wrap">
  @if($isOff)
    <div class="offbanner"><span>◐</span><span>Today is an <b>off day</b>. You don't have to log anything — but you can if you worked.</span></div>
  @endif
  @if(count($missed))
    <div class="missed"><b>Heads up:</b> you didn't save a log on {{ collect($missed)->map(fn($d)=>\Carbon\Carbon::parse($d)->format('D d M'))->join(', ') }}.</div>
  @endif

  <div class="daymeta"><h1>My day</h1><span class="tag tag-green">{{ now()->format('l, d M Y') }}</span></div>

  <form method="POST" action="{{ route('member.save') }}" id="dayForm">@csrf
  <div class="workgrid">
    <div>
      @php $blocks = $emp->blocks ?? ['done','up','crit','saved']; @endphp
      {{-- 1. Completed (always, hero) --}}
      <div class="block hero"><div class="block-head"><span class="block-num">1</span><h3>What I completed today</h3>
        <span class="tag tag-green">most important</span></div>
        <textarea class="box" name="done" placeholder="One line per finished task…">{{ $log->done }}</textarea></div>

      <div class="two">
        @if(in_array('up',$blocks))
        <div class="block"><div class="block-head"><span class="block-num">2</span><h3>Delivered / uploaded</h3></div>
          <textarea class="box" name="up" placeholder="Builds, files, links delivered…">{{ $log->upload }}</textarea></div>
        @endif
        @if(in_array('crit',$blocks))
        <div class="block"><div class="block-head"><span class="block-num">3</span><h3>Critical work</h3></div>
          <textarea class="box" name="crit" placeholder="Anything critical you handled…">{{ $log->critical }}</textarea></div>
        @endif
      </div>

      @if(in_array('saved',$blocks))
      <div class="block"><div class="block-head"><span class="block-num">4</span><h3>Saved — passwords, links, notes</h3>
        <span class="tag tag-amber">kept for you</span></div>
        <textarea class="box" name="saved" placeholder="site — user / pass&#10;https://…" style="min-height:110px">{{ $log->saved }}</textarea></div>
      @endif
    </div>

    <div class="rail">
      <div class="rail-card rail-crit"><div class="rail-head"><h3>Owner gave you</h3><span class="n">{{ $tasks->count() }}</span></div>
        @forelse($tasks as $t)
          <div class="ritem" id="task-{{ $t->id }}"><div class="ritem-top">
            <span style="flex:1">{{ $t->body }}</span>
            <button type="button" class="btn" style="padding:3px 8px;font-size:11px" onclick="doneTask({{ $t->id }})">Done</button></div>
            <span class="due">{{ $t->due }}</span></div>
        @empty
          <div style="font-size:12.5px;color:var(--ink-2)">Nothing assigned right now.</div>
        @endforelse
      </div>
      <div class="rail-card rail-pend"><div class="rail-head"><h3>My pending</h3></div>
        <textarea class="box" name="pending" placeholder="What's still open on your side…" style="min-height:120px;border-color:var(--red-line)">{{ $log->pending }}</textarea>
        <div style="font-size:11px;color:var(--ink-3);margin-top:6px">This shows on the owner's board so they know what's blocking you.</div>
      </div>
    </div>
  </div>
  </form>
</div>

<div class="savebar">
  <span style="font-size:13px;color:var(--ink-2)">Save whenever — it keeps your whole day.</span>
  <span class="status" id="statusText">Auto-saves on button</span>
  <button class="btn btn-primary" onclick="document.getElementById('dayForm').submit()">Save my day</button>
</div>

@if($emp->shared_note)
<div class="note-pop" id="notePop">
  <div class="note-card"><h3><span>✉</span> A note from your owner</h3>
    <p>{{ $emp->shared_note }}</p>
    <button class="btn btn-primary" onclick="document.getElementById('notePop').remove()">Got it</button></div>
</div>
@endif

<script>
const CSRF=document.querySelector('meta[name=csrf-token]').content;
function doneTask(id){
  fetch('{{ url('my-day/task') }}/'+id+'/done',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF}})
    .then(()=>{const el=document.getElementById('task-'+id);el.classList.add('done');
      el.querySelector('button').remove();});
}
// light autosave hint
let dirty=false;
document.querySelectorAll('textarea').forEach(t=>t.addEventListener('input',()=>{
  dirty=true;document.getElementById('statusText').textContent='Unsaved changes';
}));
window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue=''}});
document.getElementById('dayForm').addEventListener('submit',()=>{dirty=false});
</script>
</body>
</html>
