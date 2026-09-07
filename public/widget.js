/* ============================================================
   Wabwar Support Chat — embeddable widget
   Usage:
     <script src="https://chat.wabwar.com/widget.js"
             data-key="alnoor-pos-7f3a"></script>

   This file is self-contained: it injects its own styles + DOM,
   captures visitor context, and talks to the server over REST +
   (optional) SignalR. For the preview build it runs against a
   built-in mock so you can see the full flow with no backend.
   ============================================================ */
(function () {
  "use strict";

  // ---- config from the <script> tag ------------------------------------
  var thisScript =
    document.currentScript ||
    (function () {
      var s = document.getElementsByTagName("script");
      return s[s.length - 1];
    })();

  var CFG = {
    key: (thisScript && thisScript.getAttribute("data-key")) || "demo",
    api:
      (thisScript && thisScript.getAttribute("data-api")) ||
      "https://chat.wabwar.com",
    title: (thisScript && thisScript.getAttribute("data-title")) || "Support",
    brand:
      (thisScript && thisScript.getAttribute("data-brand")) ||
      "Wabwar Support",
    accent:
      (thisScript && thisScript.getAttribute("data-accent")) || "#14614E",
    // set data-mock="1" to run without a backend (preview)
    mock: (thisScript && thisScript.getAttribute("data-mock")) === "1",
  };

  // don't double-inject
  if (window.__wabwarChat) return;
  window.__wabwarChat = true;

  // ---- persistent visitor id (survives reloads) ------------------------
  function uid() {
    var k = "wabwar_visitor_" + CFG.key;
    try {
      var v = localStorage.getItem(k);
      if (!v) {
        v =
          "v_" +
          Date.now().toString(36) +
          Math.random().toString(36).slice(2, 8);
        localStorage.setItem(k, v);
      }
      return v;
    } catch (e) {
      return "v_" + Math.random().toString(36).slice(2);
    }
  }
  var VISITOR = uid();

  // ---- context we capture automatically --------------------------------
  function collectContext() {
    var ua = navigator.userAgent;
    function pick(re, d) {
      var m = ua.match(re);
      return m ? m[1] + (m[2] ? " " + m[2] : "") : d;
    }
    var browser = "Unknown";
    if (/Edg\//.test(ua)) browser = "Edge " + pick(/Edg\/(\d+)/, "");
    else if (/Chrome\//.test(ua)) browser = "Chrome " + pick(/Chrome\/(\d+)/, "");
    else if (/Firefox\//.test(ua)) browser = "Firefox " + pick(/Firefox\/(\d+)/, "");
    else if (/Safari\//.test(ua)) browser = "Safari";
    var os = "Unknown";
    if (/Windows NT 10/.test(ua)) os = "Windows 10/11";
    else if (/Windows/.test(ua)) os = "Windows";
    else if (/Mac OS X/.test(ua)) os = "macOS";
    else if (/Android/.test(ua)) os = "Android";
    else if (/iPhone|iPad/.test(ua)) os = "iOS";

    return {
      key: CFG.key,
      visitor: VISITOR,
      name: null, // filled after the pre-chat form
      page: document.title || location.pathname,
      url: location.href,
      referrer: document.referrer || "",
      browser: browser,
      os: os,
      screen: window.screen.width + "×" + window.screen.height,
      lang: navigator.language || "",
      tz: (Intl.DateTimeFormat().resolvedOptions().timeZone) || "",
      at: new Date().toISOString(),
    };
  }
  var CTX = collectContext();

  // ---- styles (scoped by prefix, no external CSS needed) ---------------
  var css =
    "" +
    ".wbw-launch{position:fixed;right:22px;bottom:22px;z-index:2147483000;display:flex;flex-direction:column;align-items:flex-end;gap:10px;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}" +
    ".wbw-tip{background:#fff;border:1px solid #C4C7BE;border-radius:12px;padding:9px 13px;font-size:13px;color:#191C19;box-shadow:0 6px 16px rgba(25,28,25,.1);max-width:210px;cursor:pointer}" +
    ".wbw-bubble{width:56px;height:56px;border-radius:50%;background:var(--wbw-accent);color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 20px rgba(20,97,78,.35);position:relative}" +
    ".wbw-bubble svg{width:26px;height:26px}" +
    ".wbw-badge{position:absolute;top:-2px;right:-2px;min-width:19px;height:19px;border-radius:10px;background:#9E3229;color:#fff;font:600 10px system-ui;display:flex;align-items:center;justify-content:center;border:2px solid #fff;padding:0 4px}" +
    ".wbw-panel{position:fixed;right:22px;bottom:22px;width:360px;max-width:calc(100vw - 44px);height:520px;max-height:calc(100vh - 44px);background:#FCFCFA;border:1px solid #C4C7BE;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 16px 40px rgba(25,28,25,.2);z-index:2147483000;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}" +
    ".wbw-head{background:var(--wbw-accent);color:#EAF3EF;padding:14px 16px;flex:none}" +
    ".wbw-head .r1{display:flex;align-items:center;gap:10px}" +
    ".wbw-ava{width:30px;height:30px;border-radius:6px;background:rgba(0,0,0,.25);color:#fff;font:600 11px system-ui;display:flex;align-items:center;justify-content:center;flex:none}" +
    ".wbw-head h3{font-size:15px;font-weight:600;flex:1;margin:0}" +
    ".wbw-x{background:none;border:none;color:#B9D6CB;font-size:18px;cursor:pointer}" +
    ".wbw-head p{font-size:12px;color:#B9D6CB;margin:3px 0 0;display:flex;align-items:center;gap:6px}" +
    ".wbw-live{width:7px;height:7px;border-radius:50%;background:#5DCAA5}" +
    ".wbw-ctx{background:#F4F5F1;border-bottom:1px solid #D8DAD3;padding:7px 12px;font:400 10.5px ui-monospace,monospace;color:#8E938C;display:flex;gap:12px;flex-wrap:wrap}" +
    ".wbw-ctx b{color:#5D625C;font-weight:500}" +
    ".wbw-body{flex:1;overflow-y:auto;padding:14px;background:#E7E8E3;display:flex;flex-direction:column;gap:9px}" +
    ".wbw-intro{background:#fff;border:1px solid #D8DAD3;border-radius:10px;padding:12px 14px;font-size:13px;color:#5D625C;line-height:1.55}" +
    ".wbw-b{max-width:82%;padding:8px 11px;border-radius:10px;font-size:13px;line-height:1.5;word-break:break-word}" +
    ".wbw-b.me{align-self:flex-end;background:var(--wbw-accent);color:#F3F7F5;border-bottom-right-radius:3px}" +
    ".wbw-b.you{align-self:flex-start;background:#fff;border:1px solid #C4C7BE;border-bottom-left-radius:3px}" +
    ".wbw-b .t{font:400 10px ui-monospace,monospace;opacity:.65;margin-top:4px;display:block}" +
    ".wbw-b img{max-width:100%;border-radius:7px;margin-bottom:5px;display:block}" +
    ".wbw-sys{align-self:stretch;background:#E6F1FB;border:1px solid #B5D4F4;border-radius:9px;padding:10px 12px;font-size:12px;color:#0C447C}" +
    ".wbw-sys b{display:block;margin-bottom:3px}" +
    ".wbw-ok{background:#E1EEE8;border-color:#9FE1CB;color:#14614E}" +
    ".wbw-ss{align-self:stretch;background:#F7EBD7;border:1px solid #E8D3AE;border-radius:9px;padding:12px}" +
    ".wbw-ss h4{font-size:13px;font-weight:600;color:#4A3007;margin:0 0 5px}" +
    ".wbw-ss p{font-size:12px;color:#4A3007;margin:0 0 10px;line-height:1.5}" +
    ".wbw-foot{flex:none;border-top:1px solid #D8DAD3;background:#FCFCFA;padding:10px 12px}" +
    ".wbw-field{margin-bottom:9px}" +
    ".wbw-field input{width:100%;border:1px solid #C4C7BE;border-radius:6px;padding:9px 11px;font-size:13px;font-family:inherit}" +
    ".wbw-row{display:flex;gap:8px;align-items:flex-end}" +
    ".wbw-row textarea{flex:1;border:1px solid #C4C7BE;border-radius:6px;padding:9px 11px;font-size:13px;font-family:inherit;resize:none;min-height:38px;max-height:90px;background:#fff}" +
    ".wbw-tools{display:flex;gap:6px;margin-bottom:8px}" +
    ".wbw-tool{border:1px solid #C4C7BE;background:#fff;border-radius:14px;padding:4px 10px;font-size:11.5px;color:#5D625C;cursor:pointer;display:flex;align-items:center;gap:5px}" +
    ".wbw-tool:hover{border-color:var(--wbw-accent);color:var(--wbw-accent)}" +
    ".wbw-btn{border:1px solid var(--wbw-accent);background:var(--wbw-accent);color:#F3F7F5;padding:9px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit}" +
    ".wbw-btn.ghost{background:#fff;color:#5D625C;border-color:#C4C7BE}" +
    ".wbw-btn.sm{padding:6px 11px;font-size:12px}" +
    ".wbw-brand{text-align:center;font:400 10.5px ui-monospace,monospace;color:#8E938C;padding:6px 0 0}" +
    ".wbw-hide{display:none!important}";

  var styleEl = document.createElement("style");
  styleEl.textContent = css;
  document.head.appendChild(styleEl);

  // ---- root element ----------------------------------------------------
  var root = document.createElement("div");
  root.style.setProperty("--wbw-accent", CFG.accent);
  document.body.appendChild(root);

  // ---- state -----------------------------------------------------------
  var state = {
    open: false,
    stage: "start", // start | chat | askscreen | sharing
    unread: 1,
    convId: null,
    msgs: [],
    sharing: false,
    stream: null,
  };

  function esc(s) {
    return (s || "").replace(/[&<>"]/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c];
    });
  }
  function now() {
    return new Date().toLocaleTimeString([], {
      hour: "numeric",
      minute: "2-digit",
    });
  }

  // ---- transport: real or mock ----------------------------------------
  var api = CFG.mock ? mockTransport() : realTransport();

  function realTransport() {
    // REST fallback; a SignalR hub can be layered on top for realtime.
    function post(path, payload) {
      return fetch(CFG.api + path, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      }).then(function (r) {
        return r.json();
      });
    }
    return {
      openConversation: function (ctx) {
        return post("/api/chat/open", ctx);
      },
      sendMessage: function (convId, text, image) {
        return post("/api/chat/send", {
          convId: convId,
          visitor: VISITOR,
          text: text,
          image: image || null,
          at: new Date().toISOString(),
        });
      },
      // long-poll for incoming agent messages; SignalR replaces this
      poll: function (convId, sinceId) {
        return fetch(
          CFG.api +
            "/api/chat/poll?convId=" +
            encodeURIComponent(convId) +
            "&since=" +
            (sinceId || 0)
        ).then(function (r) {
          return r.json();
        });
      },
      pushScreenshot: function (convId, dataUrl) {
        return post("/api/chat/screenshot", {
          convId: convId,
          visitor: VISITOR,
          image: dataUrl,
        });
      },
    };
  }

  function mockTransport() {
    var seq = 0;
    var pending = []; // agent replies scheduled to arrive
    function agentSay(text, delay) {
      setTimeout(function () {
        state.msgs.push({ id: ++seq, from: "agent", text: text, at: now() });
        if (!state.open) {
          state.unread++;
        }
        render();
      }, delay);
    }
    return {
      openConversation: function () {
        return Promise.resolve({ convId: "mock_" + Date.now() });
      },
      sendMessage: function (convId, text) {
        // canned auto-reply so the demo feels alive
        if (/salam|hello|hi\b/i.test(text))
          agentSay("Wa alaikum assalam! Kaise madad karun?", 900);
        else if (/error|masla|issue|problem/i.test(text))
          agentSay(
            "Samajh gaya. Behtar hoga agar aap apni screen dikha dein — neeche button se ek click par ho jayega.",
            1100
          );
        else agentSay("Theek hai, dekh leta hoon. Thora intezaar karein.", 1000);
        return Promise.resolve({ ok: true });
      },
      poll: function () {
        return Promise.resolve({ msgs: [] });
      },
      pushScreenshot: function () {
        agentSay("Screenshot mil gaya, shukriya — dekh raha hoon.", 1200);
        return Promise.resolve({ ok: true });
      },
    };
  }

  // ---- rendering -------------------------------------------------------
  var ICON =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

  function render() {
    if (!state.open) {
      root.innerHTML =
        '<div class="wbw-launch">' +
        '<div class="wbw-tip" data-act="open">Koi masla? Humein batayein — hum dekh lete hain.</div>' +
        '<button class="wbw-bubble" data-act="open">' +
        ICON +
        (state.unread
          ? '<span class="wbw-badge">' + state.unread + "</span>"
          : "") +
        "</button></div>";
      wire();
      return;
    }

    var ctxStrip =
      state.stage === "start"
        ? ""
        : '<div class="wbw-ctx"><span><b>Page:</b> ' +
          esc(CTX.page) +
          "</span><span>" +
          esc(CTX.browser) +
          " · " +
          esc(CTX.os) +
          "</span></div>";

    var body;
    if (state.stage === "start") {
      body =
        '<div class="wbw-intro">Assalam o alaikum! Masla batane se pehle bas apna naam likh dein.</div>';
    } else {
      body =
        '<div class="wbw-sys"><b>Aap ki maloomat bhej di gayi</b>' +
        esc(CTX.name || "Customer") +
        " · " +
        esc(CTX.browser) +
        " · " +
        esc(CTX.os) +
        "</div>" +
        state.msgs
          .map(function (m) {
            return (
              '<div class="wbw-b ' +
              (m.from === "me" ? "me" : "you") +
              '">' +
              (m.image ? '<img src="' + m.image + '">' : "") +
              esc(m.text || "") +
              '<span class="t">' +
              (m.at || "") +
              "</span></div>"
            );
          })
          .join("");
      if (state.stage === "askscreen") {
        body +=
          '<div class="wbw-ss"><h4>Support aap ki screen dekhna chahta hai</h4>' +
          "<p>Taake masla foran samajh aa jaye. Aap khud choose karenge kaunsi window dikhani hai, aur jab chahein rok sakte hain.</p>" +
          '<div><button class="wbw-btn sm" data-act="share">Apni screen dikhayein</button> ' +
          '<button class="wbw-btn ghost sm" data-act="skipshare">Abhi nahi</button></div></div>';
      }
      if (state.stage === "sharing") {
        body +=
          '<div class="wbw-sys wbw-ok"><b>Screen share ho rahi hai</b>Support aap ki window dekh raha hai. Rokne ke liye neeche button.</div>';
      }
    }

    var foot;
    if (state.stage === "start") {
      foot =
        '<div class="wbw-field"><input id="wbwName" placeholder="Aap ka naam" autocomplete="name"></div>' +
        '<button class="wbw-btn" style="width:100%" data-act="startchat">Chat shuru karein</button>' +
        '<div class="wbw-brand">Aap ka page aur browser khud pata chal jate hain</div>';
    } else {
      foot =
        (state.stage === "sharing"
          ? '<div class="wbw-tools"><button class="wbw-tool" style="border-color:#F09595;color:#9E3229" data-act="stopshare">■ Screen share rokein</button></div>'
          : "") +
        '<div class="wbw-row"><textarea id="wbwText" placeholder="Message likhein…"></textarea>' +
        '<button class="wbw-btn" data-act="send">Bhejein</button></div>';
    }

    root.innerHTML =
      '<div class="wbw-panel"><div class="wbw-head"><div class="r1">' +
      '<div class="wbw-ava">' +
      initials(CFG.brand) +
      "</div>" +
      "<h3>" +
      esc(CFG.brand) +
      "</h3>" +
      '<button class="wbw-x" data-act="close">✕</button></div>' +
      '<p><span class="wbw-live"></span> Aam tor par 5 minute mein jawab</p></div>' +
      ctxStrip +
      '<div class="wbw-body" id="wbwBody">' +
      body +
      "</div>" +
      '<div class="wbw-foot">' +
      foot +
      "</div>" +
      '<div class="wbw-brand" style="padding-bottom:8px">Powered by Wabwar Vault</div>' +
      "</div>";
    wire();
    var b = document.getElementById("wbwBody");
    if (b) b.scrollTop = b.scrollHeight;
    var ta = document.getElementById("wbwText");
    if (ta) {
      ta.focus();
      ta.addEventListener("keydown", function (e) {
        if (e.key === "Enter" && !e.shiftKey) {
          e.preventDefault();
          doSend();
        }
      });
      ta.addEventListener("paste", onPaste);
    }
  }

  function initials(s) {
    return s
      .split(" ")
      .map(function (w) {
        return w[0];
      })
      .slice(0, 2)
      .join("")
      .toUpperCase();
  }

  function wire() {
    root.querySelectorAll("[data-act]").forEach(function (el) {
      el.onclick = function () {
        var a = el.getAttribute("data-act");
        if (a === "open") openPanel();
        else if (a === "close") closePanel();
        else if (a === "startchat") startChat();
        else if (a === "send") doSend();
        else if (a === "share") shareScreen();
        else if (a === "skipshare") {
          state.stage = "chat";
          render();
        } else if (a === "stopshare") stopShare();
      };
    });
  }

  // ---- actions ---------------------------------------------------------
  function openPanel() {
    state.open = true;
    state.unread = 0;
    render();
  }
  function closePanel() {
    state.open = false;
    render();
  }

  function startChat() {
    var nm = (document.getElementById("wbwName") || {}).value || "";
    CTX.name = nm.trim() || "Customer";
    api.openConversation(CTX).then(function (res) {
      state.convId = res.convId;
      state.stage = "chat";
      render();
      // offer screen share shortly after first contact
      setTimeout(function () {
        if (state.stage === "chat") {
          state.stage = "askscreen";
          render();
        }
      }, 800);
    });
  }

  function doSend() {
    var ta = document.getElementById("wbwText");
    if (!ta) return;
    var text = ta.value.trim();
    if (!text) return;
    state.msgs.push({ from: "me", text: text, at: now() });
    ta.value = "";
    render();
    api.sendMessage(state.convId, text);
  }

  function onPaste(e) {
    var items = (e.clipboardData && e.clipboardData.items) || [];
    for (var i = 0; i < items.length; i++) {
      if (items[i].type.indexOf("image") === 0) {
        e.preventDefault();
        var file = items[i].getAsFile();
        var r = new FileReader();
        r.onload = function () {
          state.msgs.push({ from: "me", image: r.result, at: now() });
          render();
          api.sendMessage(state.convId, "", r.result);
        };
        r.readAsDataURL(file);
        return;
      }
    }
  }

  // ---- screen share (getDisplayMedia) ---------------------------------
  function shareScreen() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
      alert("Aap ka browser screen share support nahi karta.");
      return;
    }
    navigator.mediaDevices
      .getDisplayMedia({ video: { frameRate: 5 }, audio: false })
      .then(function (stream) {
        state.stream = stream;
        state.sharing = true;
        state.stage = "sharing";
        render();
        // when the user hits the browser's native "Stop sharing"
        stream.getVideoTracks()[0].addEventListener("ended", stopShare);
        // in the real build we'd stream frames / capture on demand to the agent
      })
      .catch(function () {
        // user cancelled the picker
        state.stage = "chat";
        render();
      });
  }

  function stopShare() {
    if (state.stream) {
      state.stream.getTracks().forEach(function (t) {
        t.stop();
      });
      state.stream = null;
    }
    state.sharing = false;
    state.stage = "chat";
    render();
  }

  // capture a single frame from the shared stream (agent asks for it)
  function captureFrame() {
    if (!state.stream) return null;
    var track = state.stream.getVideoTracks()[0];
    var video = document.createElement("video");
    video.srcObject = state.stream;
    return video.play().then(function () {
      var c = document.createElement("canvas");
      c.width = video.videoWidth;
      c.height = video.videoHeight;
      c.getContext("2d").drawImage(video, 0, 0);
      var url = c.toDataURL("image/jpeg", 0.7);
      api.pushScreenshot(state.convId, url);
      return url;
    });
  }

  // expose a tiny API for host software to trigger things
  window.WabwarChat = {
    open: openPanel,
    close: closePanel,
    identify: function (o) {
      if (o && o.name) CTX.name = o.name;
      if (o && o.company) CTX.company = o.company;
    },
    capture: captureFrame,
    context: function () {
      return CTX;
    },
  };

  render();
})();
