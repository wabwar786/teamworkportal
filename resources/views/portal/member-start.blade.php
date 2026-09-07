<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Start your day — Wabwar Vault</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--paper:#E7E8E3;--card:#FCFCFA;--ink:#191C19;--ink-2:#5D625C;--ink-3:#8E938C;--line-2:#C4C7BE;--green:#14614E;--green-soft:#E1EEE8;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'IBM Plex Sans',system-ui,sans-serif;background:repeating-linear-gradient(180deg,transparent 0 27px,#DDDFD8 27px 28px),var(--paper);color:var(--ink);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.wrap{width:100%;max-width:560px}
.brand{display:flex;align-items:center;gap:11px;justify-content:center;margin-bottom:22px}
.mark{width:28px;height:28px;border:1.5px solid var(--ink);border-radius:5px;position:relative}
.mark::before{content:"";position:absolute;left:50%;top:6px;transform:translateX(-50%);width:8px;height:8px;border:1.5px solid var(--ink);border-bottom:none;border-radius:5px 5px 0 0}
.mark::after{content:"";position:absolute;left:50%;top:13px;transform:translateX(-50%);width:5px;height:6px;background:var(--ink);border-radius:1px}
h1{font-size:23px;font-weight:600;text-align:center;letter-spacing:-.01em}
p.sub{text-align:center;color:var(--ink-2);font-size:14px;margin:6px 0 24px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:11px}
.pick{background:var(--card);border:1px solid var(--line-2);border-radius:10px;padding:16px 14px;text-align:center;cursor:pointer;transition:.12s}
.pick:hover{border-color:var(--green);background:var(--green-soft);transform:translateY(-1px)}
.pick .av{width:42px;height:42px;border-radius:9px;background:var(--ink);color:var(--card);font-family:'IBM Plex Mono',monospace;font-size:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 9px}
.pick.remembered .av{background:var(--green)}
.pick b{font-size:14px;font-weight:600;display:block}.pick span{font-size:11px;color:var(--ink-3);font-family:'IBM Plex Mono',monospace}
.owner-link{display:block;text-align:center;margin-top:24px;font-size:13px;color:var(--ink-3)}
.rem{text-align:center;font-size:12px;color:var(--green);margin-bottom:14px;font-family:'IBM Plex Mono',monospace}
</style>
</head>
<body>
<div class="wrap">
  <div class="brand"><div class="mark"></div><b style="font-size:18px">Wabwar Vault</b></div>
  <h1>Who's starting their day?</h1>
  <p class="sub">Tap your name — no password needed.</p>
  @if($remembered)<div class="rem">↩ last time you were here as {{ optional($employees->firstWhere('id',$remembered))->name }}</div>@endif
  <div class="grid">
    @foreach($employees as $e)
      <form method="POST" action="{{ route('member.pick') }}">@csrf
        <input type="hidden" name="employee_id" value="{{ $e->id }}">
        <button class="pick {{ $remembered==$e->id?'remembered':'' }}" type="submit">
          <div class="av">{{ $e->initials() }}</div><b>{{ $e->name }}</b><span>{{ $e->role }}</span></button>
      </form>
    @endforeach
  </div>
  <a class="owner-link" href="{{ route('login') }}">I'm an owner or head →</a>
</div>
</body>
</html>
