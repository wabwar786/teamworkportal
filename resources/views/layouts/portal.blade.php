<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Wabwar Vault — @yield('title', 'Portal')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--paper:#E7E8E3;--card:#FCFCFA;--card-2:#F4F5F1;--ink:#191C19;--ink-2:#5D625C;--ink-3:#8E938C;
--line:#D8DAD3;--line-2:#C4C7BE;--green:#14614E;--green-soft:#E1EEE8;--amber:#8A5B14;--amber-soft:#F7EBD7;
--red:#9E3229;--red-soft:#F7E4E1;--red-line:#E4C7C2;--blue:#185FA5;--blue-soft:#E6F1FB;--purple:#534AB7;--purple-soft:#EEEDFE;
--r:6px;--sans:'IBM Plex Sans',system-ui,sans-serif;--mono:'IBM Plex Mono',ui-monospace,monospace;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--sans);background:var(--paper);color:var(--ink);font-size:14px;line-height:1.5;-webkit-font-smoothing:antialiased}
button{cursor:pointer;background:none;border:none;font:inherit;color:inherit}
input,textarea,select{font:inherit;color:inherit}
a{color:inherit;text-decoration:none}
.hide{display:none!important}.mono{font-family:var(--mono)}
:focus-visible{outline:2px solid var(--green);outline-offset:2px}
.btn{border:1px solid var(--line-2);background:var(--card);padding:8px 13px;border-radius:var(--r);font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px}
.btn:hover{background:var(--card-2);border-color:var(--ink-3)}
.btn-primary{background:var(--green);border-color:var(--green);color:#F3F7F5}.btn-primary:hover{background:#0F4E3E}
.btn-red{background:var(--red);border-color:var(--red);color:#FBF3F2}.btn-red:hover{background:#84291F}
.btn-ghost-red{color:var(--red);border-color:var(--red-line)}.btn-ghost-red:hover{background:var(--red-soft)}
.btn-blue{background:var(--blue);border-color:var(--blue);color:#F1F7FD}
.btn-sm{padding:4px 9px;font-size:12px}.btn-lg{padding:11px 18px;font-size:14px}
.tag{display:inline-block;font-size:10px;font-weight:500;letter-spacing:.05em;text-transform:uppercase;padding:3px 7px;border-radius:3px;font-family:var(--mono)}
.tag-green{background:var(--green-soft);color:var(--green)}.tag-amber{background:var(--amber-soft);color:var(--amber)}
.tag-red{background:var(--red-soft);color:var(--red)}.tag-blue{background:var(--blue-soft);color:var(--blue)}
.tag-purple{background:var(--purple-soft);color:var(--purple)}.tag-gray{background:var(--card-2);color:var(--ink-2);border:1px solid var(--line)}
.field{margin-bottom:13px}.field label{display:block;font-size:12px;font-weight:500;color:var(--ink-2);margin-bottom:5px}
.input{width:100%;border:1px solid var(--line-2);background:#fff;border-radius:var(--r);padding:9px 11px;font-size:14px}
.input:focus{border-color:var(--green);outline:none;box-shadow:0 0 0 3px rgba(20,97,78,.09)}
.cx{width:15px;height:15px;accent-color:var(--green);flex:none}
.check{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-2)}
.avatar{width:30px;height:30px;border-radius:6px;background:var(--ink);color:var(--card);font-family:var(--mono);font-size:11px;display:flex;align-items:center;justify-content:center;flex:none}
.dotlive{width:7px;height:7px;border-radius:50%;flex:none}.dot-on{background:#1D9E75}.dot-off{background:var(--ink-3)}
.topbar{height:52px;flex:none;display:flex;align-items:center;gap:13px;padding:0 16px;background:var(--card);border-bottom:1px solid var(--line-2);position:sticky;top:0;z-index:14}
.brand-mini{display:flex;align-items:center;gap:9px}
.brand-mark{width:22px;height:22px;border:1.5px solid var(--ink);border-radius:5px;position:relative;flex:none}
.brand-mark::before{content:"";position:absolute;left:50%;top:5px;transform:translateX(-50%);width:7px;height:7px;border:1.5px solid var(--ink);border-bottom:none;border-radius:4px 4px 0 0}
.brand-mark::after{content:"";position:absolute;left:50%;top:11px;transform:translateX(-50%);width:4px;height:5px;background:var(--ink);border-radius:1px}
.brand-mini b{font-size:15px;font-weight:600}
.search{flex:1;max-width:440px;position:relative}
.search input{width:100%;border:1px solid var(--line-2);background:var(--card-2);border-radius:var(--r);padding:8px 12px 8px 30px;font-size:13px}
.search::before{content:"⌕";position:absolute;left:10px;top:5px;font-size:16px;color:var(--ink-3)}
.search-dd{position:absolute;top:calc(100% + 6px);left:0;right:0;background:var(--card);border:1px solid var(--line-2);border-radius:8px;box-shadow:0 10px 28px rgba(0,0,0,.14);max-height:440px;overflow-y:auto;z-index:30;display:none}
.search-dd.on{display:block}
.sdd-sec{font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);padding:8px 12px 3px;background:var(--card-2);position:sticky;top:0}
.sdd-row{display:block;padding:8px 12px;border-bottom:1px solid var(--line)}
.sdd-row:hover{background:var(--card-2)}
.sdd-row b{font-size:13px;font-weight:500;display:block}
.sdd-row span{font-size:11px;color:var(--ink-3);font-family:var(--mono);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sdd-all{display:block;text-align:center;padding:9px;font-size:12.5px;color:var(--green);font-weight:500;border-top:1px solid var(--line)}
.sdd-all:hover{background:var(--green-soft)}
.sdd-empty{padding:14px 12px;color:var(--ink-3);font-size:13px;text-align:center}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:9px}
.who{text-align:right;line-height:1.2}.who b{font-size:13px;font-weight:600;display:block}.who span{font-size:11px;color:var(--ink-3);font-family:var(--mono)}
.shell{min-height:calc(100vh - 52px);display:grid;grid-template-columns:216px 1fr}
.sidebar{background:var(--card-2);border-right:1px solid var(--line-2);padding:14px 0;overflow-y:auto}
.side-group{padding:0 10px;margin-bottom:18px}
.side-group h4{font-family:var(--mono);font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-3);padding:0 8px;margin-bottom:6px}
.nav-item{display:flex;align-items:center;gap:9px;width:100%;text-align:left;padding:7px 9px;border-radius:var(--r);font-size:13px;color:var(--ink-2)}
.nav-item:hover{background:#E4E6E0}
.nav-item.on{background:var(--card);color:var(--ink);font-weight:500;box-shadow:inset 0 0 0 1px var(--line-2)}
.nav-item .ico{width:16px;text-align:center;flex:none;font-size:13px;opacity:.75}
.nav-badge{margin-left:auto;background:#9E3229;color:#fff;font-family:var(--mono);font-size:10px;border-radius:9px;padding:1px 6px}
@keyframes hlflash{0%,60%{background:var(--amber-soft)}100%{background:transparent}}
.hl-flash{animation:hlflash 2.8s ease-out}
.hl-ring{box-shadow:0 0 0 2px var(--amber),0 0 0 6px var(--amber-soft)!important;border-radius:8px;transition:box-shadow .3s}
.page{padding:20px 22px 60px}
.sechead{display:flex;align-items:center;gap:10px;margin:4px 0 14px;flex-wrap:wrap}
.sechead h2{font-size:18px;font-weight:600;letter-spacing:-.01em}.sechead p{font-size:12.5px;color:var(--ink-2)}
.panel{background:var(--card);border:1px solid var(--line-2);border-radius:8px;overflow:hidden}
.panel-head{padding:11px 14px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:9px;background:var(--card-2)}
.panel-head h3{font-size:13px;font-weight:600;flex:1}.panel-body{padding:14px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
table{width:100%;border-collapse:collapse}
th{text-align:left;font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);font-weight:500;padding:9px 14px;border-bottom:1px solid var(--line-2);background:var(--card-2);white-space:nowrap}
td{padding:10px 14px;border-bottom:1px solid var(--line);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
td.mono{font-family:var(--mono);font-size:12px}tr.archived td{opacity:.6}
.who-cell{display:flex;align-items:center;gap:9px}.who-cell b{font-weight:500;display:block}.who-cell span{font-size:11px;color:var(--ink-3);font-family:var(--mono)}
.log-line{font-family:var(--mono);font-size:12.5px;line-height:1.7;padding-left:15px;position:relative;color:var(--ink-2)}
.log-line::before{content:"·";position:absolute;left:3px;color:var(--ink-3)}
.log-sec{margin-bottom:11px}.log-sec:last-child{margin-bottom:0}
.log-sec h5{font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px}
.block{background:var(--card);border:1px solid var(--line-2);border-radius:8px;padding:13px 15px;margin-bottom:13px}
.block.hero{border-color:var(--green);border-top:3px solid var(--green)}
.block-head{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.block-num{font-family:var(--mono);font-size:11px;color:var(--ink-3);border:1px solid var(--line-2);border-radius:3px;padding:1px 5px;flex:none}
.block-head h3{font-size:13px;font-weight:600;flex:1}
textarea.box{width:100%;border:1px solid var(--line-2);border-radius:var(--r);background:#fff;padding:9px 11px;font-family:var(--mono);font-size:12.5px;line-height:1.7;resize:vertical;min-height:92px}
.block.hero textarea.box{min-height:130px}
.two{display:grid;grid-template-columns:1fr 1fr;gap:13px}.two .block{margin-bottom:0}
.workgrid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px;align-items:start}
.rail{display:flex;flex-direction:column;gap:13px}
.rail-card{border-radius:0 8px 8px 0;padding:12px 14px}
.rail-crit{background:#FBEEEC;border:1px solid var(--red-line);border-left:4px solid var(--red)}
.rail-pend{background:var(--card);border:1px solid var(--red-line);border-left:4px solid var(--red)}
.rail-head{display:flex;align-items:center;gap:8px;margin-bottom:9px}
.rail-head h3{font-size:13px;font-weight:600;color:var(--red);flex:1}
.rail-head .n{font-family:var(--mono);font-size:11px;color:var(--red);background:#F2D9D5;padding:2px 7px;border-radius:10px}
.ritem{background:var(--card);border:1px solid var(--red-line);border-radius:var(--r);padding:9px 11px;margin-bottom:7px;font-size:13px}
.ritem .top{display:flex;gap:9px;align-items:flex-start}.ritem .due{font-family:var(--mono);font-size:10.5px;color:var(--red);margin-top:5px;display:block}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.stat{background:var(--card);border:1px solid var(--line-2);border-radius:8px;padding:13px}
.stat .k{font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:5px}
.stat .v{font-size:22px;font-weight:600}.stat .s{font-size:12px;color:var(--ink-2);margin-top:2px}
.barrow{display:grid;grid-template-columns:140px 1fr 44px;gap:12px;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)}
.bwrap{display:flex;height:20px;border-radius:4px;overflow:hidden;background:var(--card-2)}
.bseg{height:100%}.b-done{background:var(--green)}.b-up{background:#4E9E86}.b-crit{background:var(--amber)}.b-pend{background:#D9A9A3}
.bscore{font-family:var(--mono);font-size:13px;text-align:right;font-weight:500}
.legend{display:flex;gap:14px;flex-wrap:wrap;font-size:11px;color:var(--ink-2);font-family:var(--mono);margin-top:10px}
.legend i{width:9px;height:9px;border-radius:2px;display:inline-block;margin-right:5px}
.devgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:13px}
.devcard{background:var(--card);border:1px solid var(--line-2);border-radius:9px;padding:13px}
.devcard.on{border-color:var(--green);box-shadow:0 0 0 1px var(--green)}
.devcard .dh{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.devcard .dh b{font-size:14px;font-weight:600;display:block}.devcard .dh span{font-family:var(--mono);font-size:11px;color:var(--ink-3)}
.devmeta{display:grid;grid-template-columns:1fr 1fr;gap:6px;font-family:var(--mono);font-size:11px;color:var(--ink-2)}
.devmeta div{background:var(--card-2);border-radius:5px;padding:5px 7px}.devmeta b{color:var(--ink);font-weight:500}
.devacts{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.actgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:11px}
.actbtn{border:1px solid var(--line-2);background:var(--card);border-radius:9px;padding:14px 13px;text-align:left;width:100%}
.actbtn:hover{border-color:var(--green);background:var(--green-soft)}
.actbtn.danger:hover{border-color:var(--red);background:var(--red-soft)}
.actbtn b{font-size:13.5px;font-weight:600;display:block;margin-bottom:2px}.actbtn span{font-size:11.5px;color:var(--ink-2)}
.usebars{display:flex;flex-direction:column;gap:7px}
.userow{display:grid;grid-template-columns:130px 1fr 50px;gap:10px;align-items:center}
.usewrap{height:18px;border-radius:4px;background:var(--card-2);overflow:hidden;display:flex}.useseg{height:100%}
.inbox{display:grid;grid-template-columns:270px 1fr 250px;border:1px solid var(--line-2);border-radius:10px;overflow:hidden;background:var(--card);height:560px}
.ib-list{border-right:1px solid var(--line-2);overflow-y:auto;background:var(--card-2)}
.ib-list-head{padding:10px 14px;border-bottom:1px solid var(--line);background:var(--card);font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.conv{display:block;width:100%;text-align:left;padding:10px 14px;border-bottom:1px solid var(--line);border-left:2px solid transparent}
.conv:hover{background:var(--card)}.conv.on{background:var(--card);border-left-color:var(--green)}
.conv .cr1{display:flex;align-items:center;gap:8px;margin-bottom:3px}.conv .cr1 b{font-size:13px;font-weight:600;flex:1}
.conv .prev{font-size:12px;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.conv .cr2{display:flex;gap:6px;margin-top:5px}
.ib-thread{display:flex;flex-direction:column;min-width:0}
.ib-th-head{padding:10px 14px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px;background:var(--card-2)}
.ib-msgs{flex:1;overflow-y:auto;padding:14px;background:var(--paper);display:flex;flex-direction:column;gap:8px}
.bub{max-width:80%;padding:8px 11px;border-radius:10px;font-size:13px;line-height:1.5}
.bub.me{align-self:flex-end;background:var(--green);color:#F3F7F5;border-bottom-right-radius:3px}
.bub.you{align-self:flex-start;background:var(--card);border:1px solid var(--line-2);border-bottom-left-radius:3px}
.bub .t{font-family:var(--mono);font-size:10px;opacity:.65;margin-top:4px;display:block}
.ib-foot{border-top:1px solid var(--line);padding:10px 12px;background:var(--card)}
.crow{display:flex;gap:8px;align-items:flex-end}.crow textarea{flex:1;border:1px solid var(--line-2);border-radius:var(--r);padding:9px 11px;font-size:13px;resize:none;min-height:38px;background:#fff}
.ib-info{border-left:1px solid var(--line-2);overflow-y:auto;background:var(--card)}
.ib-info-head{padding:10px 14px;border-bottom:1px solid var(--line);background:var(--card-2);font-size:13px;font-weight:600}
.info-sec{padding:11px 14px;border-bottom:1px solid var(--line)}
.info-sec h5{font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:7px}
.info-row{display:flex;justify-content:space-between;gap:10px;font-size:12.5px;padding:3px 0}
.info-row .k{color:var(--ink-3)}.info-row .v{color:var(--ink);font-family:var(--mono);font-size:11px;text-align:right}
.gen{display:grid;grid-template-columns:340px 1fr;gap:16px;align-items:start}
.chan-pick{display:flex;gap:8px;margin-bottom:14px}
.chan-btn{flex:1;border:1px solid var(--line-2);background:var(--card);border-radius:9px;padding:13px;text-align:center}
.chan-btn.on{border-color:var(--green);background:var(--green-soft)}
.chan-btn b{font-size:14px;font-weight:600;display:block}.chan-btn span{font-size:11px;color:var(--ink-3)}
.codebox{background:#20241F;border-radius:9px;padding:15px 16px;font-family:var(--mono);font-size:12.5px;color:#C0DD97;line-height:1.7;overflow-x:auto;white-space:pre;position:relative}
.codebox .cm{color:#7A8570}.codebox .at{color:#EF9F27}.codebox .st{color:#9FE1CB}.codebox .kw{color:#85B7EB}
.copybtn{position:absolute;top:10px;right:10px;background:#2E3129;border:1px solid #444441;color:#C0DD97;border-radius:5px;padding:4px 9px;font-size:11px;font-family:var(--mono)}
.legalnote{background:var(--blue-soft);border:1px solid #B5D4F4;border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:12.5px;color:#0C447C;display:flex;gap:9px}
.orgchart{display:flex;flex-direction:column;gap:14px}
.tier{background:var(--card);border:1px solid var(--line-2);border-radius:9px;padding:14px}
.tier h4{font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:10px}
.person-row{display:flex;align-items:center;gap:11px;padding:9px 0;border-bottom:1px solid var(--line)}
.person-row .pd{flex:1}.person-row .pd b{font-size:13.5px;font-weight:500}.person-row .pd span{font-size:11.5px;color:var(--ink-3);font-family:var(--mono)}
.scopes{display:flex;gap:5px;flex-wrap:wrap}.scope{font-family:var(--mono);font-size:10px;background:var(--card-2);border:1px solid var(--line);border-radius:3px;padding:2px 6px;color:var(--ink-2)}
.del-warn{background:var(--red-soft);border:1px solid var(--red-line);border-radius:8px;padding:12px 14px;font-size:13px;color:#6E241C;line-height:1.6;margin-bottom:14px}
.del-warn b{color:var(--red)}
.keep-note{background:var(--green-soft);border:1px solid #B9D6CB;border-radius:8px;padding:10px 13px;font-size:12.5px;color:var(--green);margin-top:12px;display:flex;gap:8px}
.toast{position:fixed;bottom:22px;left:50%;transform:translateX(-50%);color:#fff;padding:9px 16px;border-radius:var(--r);font-size:13px;z-index:60}
.toast.ok{background:#0F4E3E}.toast.warn{background:#84291F}
.modal-bg{position:fixed;inset:0;background:rgba(25,28,25,.42);display:flex;align-items:center;justify-content:center;padding:24px;z-index:50}
.modal{background:var(--card);border:1px solid var(--line-2);border-radius:10px;width:100%;max-width:470px;padding:22px 24px;max-height:90vh;overflow-y:auto}
.modal h3{font-size:16px;font-weight:600;margin-bottom:4px}.modal p.msub{font-size:12.5px;color:var(--ink-2);margin-bottom:16px}
.modal-foot{display:flex;gap:8px;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid var(--line)}
.togrow{display:flex;align-items:center;gap:9px;padding:6px 0;font-size:13px;border-bottom:1px solid var(--line)}
@media (max-width:1000px){.shell{grid-template-columns:1fr}.sidebar{display:none}.workgrid,.gen{grid-template-columns:1fr}.inbox{grid-template-columns:1fr;height:auto}.stats{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
@php $u = auth()->user(); @endphp
<div class="topbar">
  <div class="brand-mini"><div class="brand-mark"></div><b>Wabwar Vault</b></div>
  <form class="search" method="GET" action="{{ route('portal.search') }}">
    <input name="q" id="globalSearchInput" value="{{ request('q') }}" placeholder="Search everything…" autocomplete="off">
    <div class="search-dd" id="searchDD"></div>
  </form>
  <div class="topbar-right">
    <span class="tag {{ $u->isSuper() ? 'tag-green' : 'tag-purple' }}">{{ $u->isSuper() ? 'Super owner' : 'Head' }}</span>
    <div class="who"><b>{{ $u->name }}</b><span>{{ $u->email }}</span></div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm">Exit</button></form>
  </div>
</div>

<div class="shell">
  <aside class="sidebar">
    @php
      $nav = [
        'WORKSPACE' => [
          ['portal.today','Today','▦'], ['portal.analyze','Analyze','▧'], ['portal.log','Full log','▤'],
        ],
        'TASKS' => [
          ['portal.tasks','Critical tasks','◈'], ['portal.vault.view','Vault','⬡'],
        ],
        'DEVICES' => [
          ['portal.devices','Devices','▣'], ['portal.devices.activity','Activity','◴'],
          ['portal.devices.files','File & USB','⧉'], ['portal.devices.remote','Remote actions','◉'],
          ['portal.devices.blocklist','Blocklist','⊘'],
        ],
        'SUPPORT' => [
          ['portal.support','Inbox','✉'], ['portal.scripts','Widgets & scripts','⟨⟩'],
        ],
        'TEAM' => [
          ['portal.messages','Messages','❝','msgNav'],
        ],
        'ADMIN' => [
          ['portal.employees','Employees','⚇'], ['portal.owners','Owners & heads','♦'],
        ],
      ];
      $current = request()->route()->getName();
    @endphp
    @foreach($nav as $group => $items)
      <div class="side-group"><h4>{{ $group }}</h4>
        @foreach($items as $item)
          @php [$route,$label,$icon] = $item; $badgeId = $item[3] ?? null; @endphp
          @if($route === 'portal.owners' && ! $u->isSuper()) @continue @endif
          <a class="nav-item {{ $current === $route ? 'on' : '' }}" href="{{ route($route) }}" style="position:relative">
            <span class="ico">{{ $icon }}</span>{{ $label }}
            @if($badgeId)<span id="{{ $badgeId }}" class="nav-badge hide">0</span>@endif</a>
        @endforeach
      </div>
    @endforeach
  </aside>

  <main class="page">
    @yield('content')
  </main>
</div>

@if(session('ok'))<div class="toast ok" id="toast">{{ session('ok') }}</div>@endif
@if(session('warn'))<div class="toast warn" id="toast">{{ session('warn') }}</div>@endif
<script>
  const t=document.getElementById('toast'); if(t) setTimeout(()=>t.remove(),2600);
  // attach CSRF to fetch if needed later
  window.CSRF = document.querySelector('meta[name=csrf-token]').content;

  // Global unread badge on the Messages nav item — polls from every portal page
  (function(){
    const nav = document.getElementById('msgNav');
    if(!nav) return;
    @php $msgPollUrl = route('portal.messages.poll'); @endphp
    const POLL_URL = @json($msgPollUrl);
    async function tick(){
      try{
        const r = await fetch(POLL_URL, {headers:{'Accept':'application/json'}});
        if(!r.ok) return;
        const d = await r.json();
        if(d.total>0){ nav.textContent=d.total; nav.classList.remove('hide'); }
        else nav.classList.add('hide');
      }catch(e){}
    }
    tick(); setInterval(tick, 6000);
  })();

  // Live type-ahead search — results appear as you type
  @php $liveUrl = route('portal.search.live'); $fullUrl = route('portal.search'); @endphp
  (function(){
    const si = document.getElementById('globalSearchInput');
    const dd = document.getElementById('searchDD');
    if(!si || !dd) return;
    const LIVE = @json($liveUrl);
    const FULL = @json($fullUrl);
    let timer=null, lastQ='';
    function esc(s){const d=document.createElement('div');d.textContent=(s??'');return d.innerHTML;}
    async function run(q){
      try{
        const r = await fetch(LIVE + '?q=' + encodeURIComponent(q), {headers:{'Accept':'application/json'}});
        if(!r.ok) return;
        const d = await r.json();
        if(si.value.trim() !== d.q) return; // stale response
        render(d);
      }catch(e){}
    }
    function render(d){
      if(!d.groups || !d.groups.length){
        dd.innerHTML='<div class="sdd-empty">No matches for "'+esc(d.q)+'"</div>';
        dd.classList.add('on'); return;
      }
      let h='';
      d.groups.forEach(g=>{
        h += '<div class="sdd-sec">'+esc(g.label)+'</div>';
        g.items.forEach(it=>{
          h += '<a class="sdd-row" href="'+it.url+'"><b>'+esc(it.title)+'</b>'
            + (it.sub ? '<span>'+esc(it.sub)+'</span>' : '') + '</a>';
        });
      });
      h += '<a class="sdd-all" href="'+FULL+'?q='+encodeURIComponent(d.q)+'">See all results →</a>';
      dd.innerHTML=h; dd.classList.add('on');
    }
    si.addEventListener('input', ()=>{
      const q = si.value.trim();
      clearTimeout(timer);
      if(q.length < 1){ dd.classList.remove('on'); dd.innerHTML=''; return; }
      if(q === lastQ){ dd.classList.add('on'); return; }
      lastQ = q;
      timer = setTimeout(()=>run(q), 200);
    });
    si.addEventListener('focus', ()=>{ if(dd.innerHTML.trim()) dd.classList.add('on'); });
    si.addEventListener('keydown', e=>{ if(e.key==='Escape') dd.classList.remove('on'); });
    document.addEventListener('click', e=>{ if(!e.target.closest('.search')) dd.classList.remove('on'); });
  })();
  // Highlight the item that a search result pointed to (?hl=elementId)
  (function(){
    const hl = new URLSearchParams(location.search).get('hl');
    if(!hl) return;
    const el = document.getElementById(hl);
    if(!el) return;
    setTimeout(function(){
      el.scrollIntoView({behavior:'smooth', block:'center'});
      const isRow = el.tagName === 'TR';
      el.classList.add(isRow ? 'hl-flash' : 'hl-ring');
      if(!isRow) el.classList.add('hl-flash');
      setTimeout(function(){ el.classList.remove('hl-ring'); }, 3200);
      setTimeout(function(){ el.classList.remove('hl-flash'); }, 3000);
    }, 300);
  })();
  @yield('scripts')
</script>
</body>
</html>
