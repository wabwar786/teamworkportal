{{--
  Reusable team-chat panel (slide-in). Pass:
    $base        base url for thread fetch: <base>/thread/{kind}/{id}
    $sendUrl     POST endpoint to send a message
    $contactsUrl GET endpoint for contacts + unread
    $pollUrl     GET endpoint for polling unread + lastId
  Requires a <meta name="csrf-token"> in the page (present on both screens).
--}}
<style>
.chat-badge{background:#9E3229;color:#fff;font-family:'IBM Plex Mono',monospace;font-size:10px;border-radius:9px;padding:1px 6px;position:absolute;top:-6px;right:-6px}
.chat-scrim{position:fixed;inset:0;background:rgba(25,28,25,.42);z-index:60;opacity:0;pointer-events:none;transition:.18s}
.chat-scrim.on{opacity:1;pointer-events:auto}
.chat-panel{position:fixed;top:0;right:0;height:100vh;width:390px;max-width:94vw;background:#FCFCFA;border-left:1px solid #C4C7BE;z-index:61;display:flex;flex-direction:column;transform:translateX(100%);transition:.2s;box-shadow:-8px 0 24px rgba(0,0,0,.10)}
.chat-panel.on{transform:translateX(0)}
.chat-h{height:52px;flex:none;display:flex;align-items:center;gap:10px;padding:0 14px;border-bottom:1px solid #D8DAD3;background:#F4F5F1}
.chat-h b{font-size:14px;font-weight:600;flex:1}
.chat-x{width:30px;height:30px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#5D625C}
.chat-x:hover{background:#E4E6E0}
.chat-back{font-size:13px;color:#14614E;font-weight:500;display:none}
.chat-body{flex:1;overflow-y:auto;min-height:0}
/* contacts */
.cc{display:flex;align-items:center;gap:10px;width:100%;text-align:left;padding:11px 14px;border-bottom:1px solid #EAEBE6}
.cc:hover{background:#F4F5F1}
.cc .cav{width:34px;height:34px;border-radius:8px;background:#191C19;color:#FCFCFA;font-family:'IBM Plex Mono',monospace;font-size:12px;display:flex;align-items:center;justify-content:center;flex:none}
.cc.owner .cav{background:#534AB7}
.cc .cn{flex:1;min-width:0}.cc .cn b{font-size:13.5px;font-weight:600;display:block}.cc .cn span{font-size:11px;color:#8E938C;font-family:'IBM Plex Mono',monospace}
.cc .cu{background:#9E3229;color:#fff;font-family:'IBM Plex Mono',monospace;font-size:10px;border-radius:9px;padding:1px 6px}
.chat-sec{font-family:'IBM Plex Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#8E938C;padding:9px 14px 4px;background:#F4F5F1}
/* thread */
.thread{display:none;flex-direction:column;height:100%}
.thread.on{display:flex}
.th-msgs{flex:1;overflow-y:auto;padding:14px;background:#E7E8E3;display:flex;flex-direction:column;gap:7px}
.tb{max-width:80%;padding:8px 11px;border-radius:10px;font-size:13px;line-height:1.5;word-break:break-word}
.tb.me{align-self:flex-end;background:#14614E;color:#F3F7F5;border-bottom-right-radius:3px}
.tb.you{align-self:flex-start;background:#FCFCFA;border:1px solid #C4C7BE;border-bottom-left-radius:3px}
.tb img{max-width:100%;border-radius:7px;margin-top:4px;display:block;cursor:pointer}
.tb .tt{font-family:'IBM Plex Mono',monospace;font-size:9.5px;opacity:.6;margin-top:3px;display:block}
.th-foot{border-top:1px solid #D8DAD3;padding:9px 11px;background:#FCFCFA;display:flex;gap:7px;align-items:flex-end}
.th-foot textarea{flex:1;border:1px solid #C4C7BE;border-radius:7px;padding:8px 10px;font-size:13px;resize:none;min-height:38px;max-height:110px;font-family:inherit}
.th-attach{width:38px;height:38px;border:1px solid #C4C7BE;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:16px;color:#5D625C;flex:none}
.th-attach:hover{background:#F4F5F1}
.th-send{background:#14614E;color:#F3F7F5;border-radius:7px;padding:0 14px;height:38px;font-size:13px;font-weight:500;flex:none}
.th-preview{padding:8px 11px;border-top:1px solid #EAEBE6;background:#F7EBD7;display:none;align-items:center;gap:9px;font-size:12px;color:#8A5B14}
.th-preview.on{display:flex}
.th-preview img{height:34px;border-radius:5px}
.chat-empty{padding:30px 14px;text-align:center;color:#8E938C;font-size:13px}
.img-view{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:70;display:none;align-items:center;justify-content:center;padding:20px}
.img-view.on{display:flex}.img-view img{max-width:100%;max-height:100%;border-radius:8px}
@media (max-width:480px){.chat-panel{width:100vw}}
</style>

<div class="chat-scrim" id="chatScrim" onclick="Chat.close()"></div>
<div class="chat-panel" id="chatPanel">
  <div class="chat-h">
    <button class="chat-back" id="chatBack" onclick="Chat.showContacts()">← Back</button>
    <b id="chatTitle">Messages</b>
    <button class="chat-x" onclick="Chat.close()">×</button>
  </div>
  <div class="chat-body" id="chatContacts"></div>
  <div class="thread" id="chatThread">
    <div class="th-msgs" id="thMsgs"></div>
    <div class="th-preview" id="thPreview"><img id="thPreviewImg"><span style="flex:1">Image ready to send</span>
      <button onclick="Chat.clearImage()" style="color:#9E3229;font-weight:600">remove</button></div>
    <div class="th-foot">
      <button class="th-attach" onclick="document.getElementById('chatFile').click()">📎</button>
      <input type="file" id="chatFile" accept="image/*" class="hide" onchange="Chat.pickImage(this)">
      <textarea id="thInput" placeholder="Type a message…" rows="1"
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();Chat.send()}"></textarea>
      <button class="th-send" onclick="Chat.send()">Send</button>
    </div>
  </div>
</div>
<div class="img-view" id="imgView" onclick="this.classList.remove('on')"><img id="imgViewImg"></div>

<script>
const Chat = (function(){
  const CSRF = document.querySelector('meta[name=csrf-token]').content;
  const BASE = @json($base);
  const SEND = @json($sendUrl);
  const CONTACTS = @json($contactsUrl);
  const POLL = @json($pollUrl);
  let me='', current=null, contacts=[], pendingImg=null, lastId=0, pollTimer=null;

  function initials(n){return (n||'?').split(/\s+/).map(w=>w[0]).slice(0,2).join('').toUpperCase();}
  function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

  async function loadContacts(){
    const r = await fetch(CONTACTS,{headers:{'Accept':'application/json'}});
    if(!r.ok) return;
    const d = await r.json(); me=d.me; contacts=d.contacts;
    renderContacts();
  }
  function renderContacts(){
    const owners = contacts.filter(c=>c.kind==='owner');
    const emps = contacts.filter(c=>c.kind==='emp');
    let h='';
    if(owners.length){h+='<div class="chat-sec">Owners & heads</div>';
      owners.forEach(c=>h+=ccRow(c));}
    if(emps.length){h+='<div class="chat-sec">Team</div>';
      emps.forEach(c=>h+=ccRow(c));}
    if(!contacts.length) h='<div class="chat-empty">No one to chat with yet.</div>';
    document.getElementById('chatContacts').innerHTML=h;
  }
  function ccRow(c){
    const [kind,id]=c.ref.split(':');
    return `<button class="cc ${c.kind}" onclick="Chat.openThread('${kind}',${id},'${esc(c.name)}')">
      <span class="cav">${initials(c.name)}</span>
      <span class="cn"><b>${esc(c.name)}</b><span>${esc(c.role)}</span></span>
      ${c.unread?`<span class="cu">${c.unread}</span>`:''}</button>`;
  }

  async function openThread(kind,id,name){
    current={kind,id,name};
    document.getElementById('chatTitle').textContent=name;
    document.getElementById('chatBack').style.display='block';
    document.getElementById('chatContacts').style.display='none';
    document.getElementById('chatThread').classList.add('on');
    await loadThread();
    document.getElementById('thInput').focus();
  }
  async function loadThread(){
    if(!current) return;
    const r=await fetch(`${BASE}/thread/${current.kind}/${current.id}`,{headers:{'Accept':'application/json'}});
    if(!r.ok) return;
    const d=await r.json();
    const box=document.getElementById('thMsgs');
    box.innerHTML=d.messages.map(m=>`
      <div class="tb ${m.mine?'me':'you'}">
        ${m.body?esc(m.body):''}
        ${m.image?`<img src="${m.image}" onclick="Chat.viewImage('${m.image}')">`:''}
        <span class="tt">${m.at}</span></div>`).join('');
    box.scrollTop=box.scrollHeight;
  }
  function showContacts(){
    current=null;
    document.getElementById('chatBack').style.display='none';
    document.getElementById('chatTitle').textContent='Messages';
    document.getElementById('chatThread').classList.remove('on');
    document.getElementById('chatContacts').style.display='block';
    loadContacts();
  }

  function pickImage(input){
    const f=input.files[0]; if(!f) return;
    const img=new Image(); const rd=new FileReader();
    rd.onload=e=>{img.onload=()=>{
      const max=1000; let{width:w,height:h}=img;
      if(w>max){h=h*max/w;w=max;}
      const cv=document.createElement('canvas');cv.width=w;cv.height=h;
      cv.getContext('2d').drawImage(img,0,0,w,h);
      pendingImg=cv.toDataURL('image/jpeg',0.7);
      document.getElementById('thPreviewImg').src=pendingImg;
      document.getElementById('thPreview').classList.add('on');
    };img.src=e.target.result;};
    rd.readAsDataURL(f); input.value='';
  }
  function clearImage(){pendingImg=null;document.getElementById('thPreview').classList.remove('on');}

  async function send(){
    if(!current) return;
    const ta=document.getElementById('thInput'); const body=ta.value.trim();
    if(!body && !pendingImg) return;
    const payload={to:`${current.kind}:${current.id}`,body,image:pendingImg};
    ta.value=''; const img=pendingImg; clearImage();
    // optimistic
    const box=document.getElementById('thMsgs');
    box.insertAdjacentHTML('beforeend',`<div class="tb me">${body?esc(body):''}${img?`<img src="${img}">`:''}<span class="tt">now</span></div>`);
    box.scrollTop=box.scrollHeight;
    await fetch(SEND,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify(payload)});
    loadThread();
  }

  async function poll(){
    try{
      const r=await fetch(POLL,{headers:{'Accept':'application/json'}}); if(!r.ok) return;
      const d=await r.json();
      const badge=document.getElementById('chatBadge');
      if(d.total>0){badge.textContent=d.total;badge.classList.remove('hide');}
      else badge.classList.add('hide');
      // update contact unread pills if list visible
      if(!current && document.getElementById('chatPanel').classList.contains('on')) loadContacts();
      // refresh open thread if something changed
      if(current && d.lastId!==lastId){lastId=d.lastId;loadThread();}
      else lastId=d.lastId;
    }catch(e){}
  }

  function open(){
    document.getElementById('chatScrim').classList.add('on');
    document.getElementById('chatPanel').classList.add('on');
    showContacts();
  }
  function close(){
    document.getElementById('chatScrim').classList.remove('on');
    document.getElementById('chatPanel').classList.remove('on');
  }
  function viewImage(src){document.getElementById('imgViewImg').src=src;document.getElementById('imgView').classList.add('on');}

  // start background polling
  poll(); pollTimer=setInterval(poll,5000);

  return {open,close,openThread,showContacts,send,pickImage,clearImage,viewImage};
})();
</script>
