@extends('layouts.portal')
@section('title','Widgets & scripts')
@section('content')
<div class="sechead"><h2>Widgets & scripts</h2><p>Give each customer a snippet — web or desktop — that lands in this inbox</p></div>
<div class="gen">
  <div>
    <div class="panel"><div class="panel-head"><h3>Customers</h3></div><div class="panel-body">
      <form method="POST" action="{{ route('portal.scripts.client') }}" style="display:flex;gap:8px;margin-bottom:12px">@csrf
        <input class="input" name="name" placeholder="Customer name" required>
        <select class="input" name="channel" style="width:auto"><option value="web">Web</option><option value="desktop">Desktop</option><option value="both">Both</option></select>
        <button class="btn btn-primary" type="submit">Add</button></form>
      @foreach($clients as $c)
        <button class="togrow" style="width:100%;text-align:left;border:none;background:none" onclick="pick('{{ $c->key }}','{{ $c->name }}','{{ $c->channel }}')">
          <span style="flex:1"><b style="font-size:13px">{{ $c->name }}</b>
            <div class="mono" style="font-size:10px;color:var(--ink-3)">{{ $c->key }}</div></span>
          <span class="tag {{ $c->channel==='web'?'tag-blue':($c->channel==='desktop'?'tag-purple':'tag-green') }}">{{ $c->channel }}</span></button>
      @endforeach
    </div></div>
  </div>
  <div>
    <div class="chan-pick">
      <button class="chan-btn on" id="tabWeb" onclick="tab('web')"><b>Web</b><span>JavaScript widget</span></button>
      <button class="chan-btn" id="tabDesk" onclick="tab('desktop')"><b>Desktop</b><span>C# WinForms</span></button>
    </div>
    <div class="panel" id="webPane"><div class="panel-head"><h3>Web snippet — <span id="webName">select a customer</span></h3></div>
      <div class="panel-body"><div class="codebox" id="webCode"><button class="copybtn" onclick="cp('webCode')">Copy</button><span class="cm">// pick a customer on the left</span></div>
        <p style="font-size:12px;color:var(--ink-2);margin-top:10px">Paste before <span class="mono">&lt;/body&gt;</span> on the customer's site. Captures page, browser and OS automatically; screen share only on customer click.</p></div></div>
    <div class="panel hide" id="deskPane"><div class="panel-head"><h3>WinForms snippet — <span id="deskName">select a customer</span></h3></div>
      <div class="panel-body"><div class="codebox" id="deskCode"><button class="copybtn" onclick="cp('deskCode')">Copy</button><span class="cm">// pick a customer on the left</span></div>
        <p style="font-size:12px;color:var(--ink-2);margin-top:10px">Add <span class="mono">SupportChatPanel.cs</span> to the customer's WinForms app and drop the panel on a form. Same inbox.</p></div></div>
  </div>
</div>
@endsection
@section('scripts')
const CHAT_BASE='{{ $chatBase }}';
let curKey='',curName='';
function tab(t){
  document.getElementById('tabWeb').classList.toggle('on',t==='web');
  document.getElementById('tabDesk').classList.toggle('on',t==='desktop');
  document.getElementById('webPane').classList.toggle('hide',t!=='web');
  document.getElementById('deskPane').classList.toggle('hide',t!=='desktop');
}
function esc(s){return s.replace(/</g,'&lt;')}
function pick(key,name){
  curKey=key;curName=name;
  document.getElementById('webName').textContent=name;
  document.getElementById('deskName').textContent=name;
  document.getElementById('webCode').innerHTML='<button class="copybtn" onclick="cp(\'webCode\')">Copy</button>'+
    '<span class="cm">&lt;!-- Wabwar Support — '+esc(name)+' --&gt;</span>\n'+
    '&lt;<span class="kw">script</span> <span class="at">src</span>=<span class="st">"'+CHAT_BASE+'/widget.js"</span>\n'+
    '        <span class="at">data-key</span>=<span class="st">"'+key+'"</span>\n'+
    '        <span class="at">data-base</span>=<span class="st">"'+CHAT_BASE+'"</span> <span class="at">defer</span>&gt;&lt;/<span class="kw">script</span>&gt;';
  document.getElementById('deskCode').innerHTML='<button class="copybtn" onclick="cp(\'deskCode\')">Copy</button>'+
    '<span class="cm">// '+esc(name)+' — in your Form load</span>\n'+
    '<span class="kw">var</span> chat = <span class="kw">new</span> SupportChatPanel {\n'+
    '    BaseUrl = <span class="st">"'+CHAT_BASE+'"</span>,\n'+
    '    ClientKey = <span class="st">"'+key+'"</span>,\n'+
    '    AppLabel = <span class="st">"'+esc(name)+'"</span>,\n'+
    '    Dock = DockStyle.Right\n'+
    '};\n'+
    'this.Controls.Add(chat);';
}
function cp(id){
  const el=document.getElementById(id).cloneNode(true);
  el.querySelector('.copybtn')?.remove();
  navigator.clipboard.writeText(el.textContent.trim());
  const b=document.getElementById(id).querySelector('.copybtn');b.textContent='Copied';setTimeout(()=>b.textContent='Copy',1400);
}
@endsection
