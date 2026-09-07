<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — Wabwar Vault</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--paper:#E7E8E3;--card:#FCFCFA;--ink:#191C19;--ink-2:#5D625C;--ink-3:#8E938C;--line-2:#C4C7BE;--green:#14614E;--red:#9E3229;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'IBM Plex Sans',system-ui,sans-serif;background:repeating-linear-gradient(180deg,transparent 0 27px,#DDDFD8 27px 28px),var(--paper);color:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{width:100%;max-width:400px;background:var(--card);border:1px solid var(--line-2);border-radius:12px;padding:32px 30px 26px}
.brand{display:flex;align-items:center;gap:11px;margin-bottom:6px}
.mark{width:30px;height:30px;border:1.5px solid var(--ink);border-radius:5px;position:relative;flex:none}
.mark::before{content:"";position:absolute;left:50%;top:7px;transform:translateX(-50%);width:9px;height:9px;border:1.5px solid var(--ink);border-bottom:none;border-radius:5px 5px 0 0}
.mark::after{content:"";position:absolute;left:50%;top:15px;transform:translateX(-50%);width:5px;height:6px;background:var(--ink);border-radius:1px}
h1{font-size:20px;font-weight:600;margin:16px 0 4px}p.sub{font-size:13px;color:var(--ink-2);margin-bottom:18px}
label{display:block;font-size:12px;font-weight:500;color:var(--ink-2);margin-bottom:5px;margin-top:12px}
input[type=email],input[type=password]{width:100%;border:1px solid var(--line-2);border-radius:6px;padding:10px 12px;font-size:14px;font-family:'IBM Plex Mono',monospace}
input:focus{border-color:var(--green);outline:none;box-shadow:0 0 0 3px rgba(20,97,78,.09)}
.err{color:var(--red);font-size:12px;margin-top:6px}
.chk{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-2);margin:14px 0}
button{width:100%;background:var(--green);color:#F3F7F5;border:none;border-radius:6px;padding:11px;font-size:14px;font-weight:500;cursor:pointer;font-family:inherit}
button:hover{background:#0F4E3E}
.foot{margin-top:18px;padding-top:14px;border-top:1px solid #eee;font-size:11.5px;color:var(--ink-3);font-family:'IBM Plex Mono',monospace;line-height:1.7}
.member-link{display:block;text-align:center;margin-top:14px;font-size:13px;color:var(--green);text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="brand"><div class="mark"></div><div><div style="font-size:17px;font-weight:600">Wabwar Vault</div>
    <div style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink-3)">owner / head sign in</div></div></div>
  <h1>Sign in</h1>
  <p class="sub">Owners and heads log in here. Team members don't need a password.</p>
  <form method="POST" action="{{ route('login') }}">
    @csrf
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
    @error('email')<div class="err">{{ $message }}</div>@enderror
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <label class="chk"><input type="checkbox" name="remember"> Remember me</label>
    <button type="submit">Sign in</button>
  </form>
  <a class="member-link" href="{{ route('member.start') }}">I'm a team member →</a>
  <div class="foot">Demo: admin@wabwar.com / password<br>Head: tariq@wabwar.com / password</div>
</div>
</body>
</html>
