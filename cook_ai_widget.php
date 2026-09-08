<?php
// ============================================================
//  Spice Garden — Cook AI Widget
//  Include in cook_header.php before </header>
// ============================================================
?>

<!-- ══════════ COOK AI WIDGET ══════════ -->
<style>
#cook-ai-fab {
   position: fixed; bottom: 2.8rem; right: 2.8rem;
   width: 6rem; height: 6rem; border-radius: 50%;
   background: linear-gradient(135deg, #10b981, #059669);
   color: #fff; border: none; cursor: pointer; z-index: 9998;
   box-shadow: 0 6px 24px rgba(16,185,129,0.5);
   display: flex; align-items: center; justify-content: center;
   font-size: 2.4rem;
   transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
   animation: cook-pulse 2.5s infinite;
}
#cook-ai-fab:hover { transform: scale(1.12); box-shadow: 0 10px 32px rgba(16,185,129,0.7); }
#cook-ai-fab .cook-badge {
   position: absolute; top: -.3rem; right: -.3rem;
   background: #ef4444; color: #fff; font-size: .85rem; font-weight: 700;
   padding: .15rem .45rem; border-radius: 2rem;
   font-family: 'Nunito Sans', sans-serif; pointer-events: none;
}
@keyframes cook-pulse {
   0%,100% { box-shadow: 0 6px 24px rgba(16,185,129,0.5); }
   50%      { box-shadow: 0 6px 32px rgba(16,185,129,0.8), 0 0 0 8px rgba(16,185,129,0.12); }
}
#cook-ai-window {
   position: fixed; bottom: 10rem; right: 2.8rem;
   width: 38rem; max-height: 60rem; background: #fff;
   border-radius: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.18);
   z-index: 9999; display: flex; flex-direction: column; overflow: hidden;
   transform: scale(0.85) translateY(20px); opacity: 0; pointer-events: none;
   transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
   font-family: 'Nunito Sans', sans-serif;
}
#cook-ai-window.open { transform: scale(1) translateY(0); opacity: 1; pointer-events: all; }
#cook-ai-header {
   background: linear-gradient(135deg, #10b981, #059669); color: #fff;
   padding: 1.4rem 1.8rem; display: flex; align-items: center; gap: 1.2rem; flex-shrink: 0;
}
.cook-ai-avatar {
   width: 4.2rem; height: 4.2rem; background: rgba(255,255,255,0.2);
   border-radius: 50%; display: flex; align-items: center; justify-content: center;
   font-size: 2rem; flex-shrink: 0;
}
.cook-ai-info h4 { font-size: 1.5rem; font-weight: 700; margin: 0 0 .2rem; }
.cook-ai-info p  { font-size: 1.1rem; opacity: .85; margin: 0; }
.cook-online-dot {
   width: .8rem; height: .8rem; background: #fde68a; border-radius: 50%;
   display: inline-block; margin-right: .4rem; animation: cook-blink 1.5s infinite;
}
@keyframes cook-blink { 0%,100%{opacity:1} 50%{opacity:.3} }
#cook-close-btn {
   margin-left: auto; background: rgba(255,255,255,0.15); border: none; color: #fff;
   width: 3rem; height: 3rem; border-radius: 50%; cursor: pointer; font-size: 1.4rem;
   display: flex; align-items: center; justify-content: center; transition: background .2s; flex-shrink: 0;
}
#cook-close-btn:hover { background: rgba(255,255,255,0.3); }
#cook-ai-chips {
   padding: 1rem 1.4rem .6rem; display: flex; flex-wrap: wrap; gap: .6rem;
   background: #ecfdf5; border-bottom: 1px solid #a7f3d0; flex-shrink: 0;
}
.cook-chip {
   background: #d1fae5; color: #065f46; border: 1.5px solid #6ee7b7;
   border-radius: 2rem; padding: .4rem 1rem; font-size: 1.1rem; font-weight: 600;
   cursor: pointer; transition: all .2s; font-family: 'Nunito Sans', sans-serif;
   white-space: nowrap;
}
.cook-chip:hover { background: #10b981; color: #fff; border-color: #10b981; }
#cook-ai-messages {
   flex: 1; overflow-y: auto; padding: 1.5rem;
   display: flex; flex-direction: column; gap: 1.2rem;
   background: #ecfdf5; min-height: 24rem; max-height: 34rem;
}
#cook-ai-messages::-webkit-scrollbar { width: .4rem; }
#cook-ai-messages::-webkit-scrollbar-thumb { background: #6ee7b7; border-radius: 1rem; }
.cook-msg { display: flex; flex-direction: column; max-width: 88%; }
.cook-msg.bot  { align-self: flex-start; }
.cook-msg.user { align-self: flex-end; align-items: flex-end; }
.cook-msg-bubble {
   padding: 1rem 1.4rem; border-radius: 1.4rem;
   font-size: 1.3rem; line-height: 1.7; word-break: break-word; white-space: pre-wrap;
}
.cook-msg.bot  .cook-msg-bubble {
   background: #fff; color: #1a1a2e; border: 1px solid #a7f3d0;
   border-bottom-left-radius: .3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.cook-msg.user .cook-msg-bubble {
   background: linear-gradient(135deg, #10b981, #059669);
   color: #fff; border-bottom-right-radius: .3rem;
}
.cook-msg-time { font-size: 1rem; color: #9ca3af; margin-top: .3rem; padding: 0 .4rem; }
#cook-typing { display: none; align-self: flex-start; }
#cook-typing .cook-msg-bubble {
   background: #fff; border: 1px solid #a7f3d0;
   border-bottom-left-radius: .3rem; padding: 1.1rem 1.6rem;
}
.cook-dots { display: flex; gap: .4rem; align-items: center; }
.cook-dots span {
   width: .7rem; height: .7rem; background: #10b981;
   border-radius: 50%; animation: cook-bounce 1.2s infinite;
}
.cook-dots span:nth-child(2) { animation-delay: .2s; }
.cook-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes cook-bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-.6rem)} }
#cook-input-area {
   padding: 1.2rem 1.4rem; border-top: 1px solid #a7f3d0;
   display: flex; gap: .8rem; align-items: center; background: #fff; flex-shrink: 0;
}
#cook-input {
   flex: 1; padding: .9rem 1.3rem; border: 1.5px solid #a7f3d0; border-radius: 1rem;
   font-size: 1.3rem; font-family: 'Nunito Sans', sans-serif;
   color: #1a1a2e; background: #ecfdf5; outline: none; transition: border .2s;
}
#cook-input:focus { border-color: #10b981; background: #d1fae5; }
#cook-input::placeholder { color: #9ca3af; }
#cook-send-btn {
   width: 4rem; height: 4rem;
   background: linear-gradient(135deg, #10b981, #059669);
   color: #fff; border: none; border-radius: 1rem; font-size: 1.5rem; cursor: pointer;
   display: flex; align-items: center; justify-content: center;
   transition: transform .2s, box-shadow .2s; flex-shrink: 0;
}
#cook-send-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.4); }
#cook-send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
@media(max-width:480px){
   #cook-ai-window { width: calc(100vw - 2rem); right: 1rem; bottom: 9rem; }
   #cook-ai-fab { bottom: 2rem; right: 2rem; }
}
</style>

<button id="cook-ai-fab" onclick="toggleCookAi()" title="Kitchen AI Assistant">
   👨‍🍳<span class="cook-badge">AI</span>
</button>

<div id="cook-ai-window">
   <div id="cook-ai-header">
      <div class="cook-ai-avatar">🍳</div>
      <div class="cook-ai-info">
         <h4>Kitchen AI Assistant</h4>
         <p><span class="cook-online-dot"></span>Live Kitchen Data · Always Ready</p>
      </div>
      <button id="cook-close-btn" onclick="toggleCookAi()"><i class="fas fa-times"></i></button>
   </div>
   <div id="cook-ai-chips">
      <span class="cook-chip" onclick="cookSendChip('What orders are waiting right now?')">⏳ Waiting Orders</span>
      <span class="cook-chip" onclick="cookSendChip('What is currently cooking?')">🔥 Cooking Now</span>
      <span class="cook-chip" onclick="cookSendChip('Which order is waiting longest?')">⚠️ Oldest Order</span>
      <span class="cook-chip" onclick="cookSendChip('How many orders completed today?')">✅ Completed Today</span>
      <span class="cook-chip" onclick="cookSendChip('What are top ordered items today?')">📊 Top Items</span>
      <span class="cook-chip" onclick="cookSendChip('Give me full kitchen summary right now')">📋 Kitchen Summary</span>
      <span class="cook-chip" onclick="cookSendChip('Which order should I prioritize first?')">💡 What To Cook First</span>
   </div>
   <div id="cook-ai-messages">
      <div class="cook-msg bot">
         <div class="cook-msg-bubble">
            👨‍🍳 Hello Chef! I'm your Kitchen AI Assistant!<br><br>
            I have access to your <b>live kitchen orders</b>. Ask me:<br>
            ⏳ Waiting &amp; cooking orders<br>
            ⚠️ Oldest pending order<br>
            ✅ Completed today<br>
            💡 What to cook first<br><br>
            Click a quick button or type anything! 🍳
         </div>
         <div class="cook-msg-time">Just now</div>
      </div>
      <div class="cook-msg bot" id="cook-typing">
         <div class="cook-msg-bubble">
            <div class="cook-dots"><span></span><span></span><span></span></div>
         </div>
      </div>
   </div>
   <div id="cook-input-area">
      <input type="text" id="cook-input" placeholder="Ask about kitchen orders..." maxlength="300"
         onkeypress="if(event.key==='Enter') cookSendMsg()">
      <button id="cook-send-btn" onclick="cookSendMsg()">
         <i class="fas fa-paper-plane"></i>
      </button>
   </div>
</div>

<script>
const COOK_GROQ_KEY = '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';
let cookOpen    = false;
let cookHistory = [];
let cookTyping  = false;
let cookDbData  = null;

function toggleCookAi() {
   cookOpen = !cookOpen;
   const win = document.getElementById('cook-ai-window');
   const fab = document.getElementById('cook-ai-fab');
   if (cookOpen) {
      win.classList.add('open');
      fab.style.transform = 'scale(0.9) rotate(10deg)';
      setTimeout(() => document.getElementById('cook-input').focus(), 300);
      if (!cookDbData) loadCookData();
   } else {
      win.classList.remove('open');
      fab.style.transform = '';
   }
}

async function loadCookData() {
   try {
      const res  = await fetch('cook_ai_api.php');
      const data = await res.json();
      cookDbData = data.data || 'No data available';
   } catch(e) {
      cookDbData = 'Could not load kitchen data.';
   }
}

function cookSendChip(text) {
   document.getElementById('cook-input').value = text;
   cookSendMsg();
}

async function cookSendMsg() {
   if (cookTyping) return;
   const input = document.getElementById('cook-input');
   const msg   = input.value.trim();
   if (!msg) return;

   input.value = '';
   cookAppendMsg('user', msg);
   cookHistory.push({ role: 'user', content: msg });
   cookShowTyping(true);
   document.getElementById('cook-send-btn').disabled = true;

   if (!cookDbData) await loadCookData();

   const systemPrompt = `You are a smart Kitchen AI assistant for Spice Garden restaurant cook.
You have access to LIVE kitchen order data:

${cookDbData}

YOUR JOB:
- Help cook manage kitchen orders efficiently
- Be clear, direct and quick (cook is busy!)
- Keep replies short (max 6 lines)
- When asked about waiting orders: list them with wait time, oldest first
- When asked what to cook first: suggest based on longest wait time and cooking time
- When asked about cooking now: list items currently cooking
- When asked for summary: give quick overview of all pending + cooking + completed
- Use emojis to make it easy to scan quickly
- Always end with a quick tip if relevant
- Do not make up data — only use what is provided above`;

   try {
      const messages = [
         { role: 'system', content: systemPrompt },
         ...cookHistory.slice(-8)
      ];

      const res = await fetch('https://api.groq.com/openai/v1/chat/completions', {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + COOK_GROQ_KEY
         },
         body: JSON.stringify({
            model: 'llama-3.3-70b-versatile',
            max_tokens: 350,
            temperature: 0.4,
            messages: messages
         })
      });

      const data  = await res.json();
      if (!res.ok) throw new Error(data?.error?.message || 'API Error');
      const reply = data?.choices?.[0]?.message?.content?.trim() || 'Sorry, try again!';

      cookShowTyping(false);
      document.getElementById('cook-send-btn').disabled = false;
      cookAppendMsg('bot', reply);
      cookHistory.push({ role: 'assistant', content: reply });
      if (cookHistory.length > 12) cookHistory = cookHistory.slice(-12);

   } catch(err) {
      cookShowTyping(false);
      document.getElementById('cook-send-btn').disabled = false;
      cookAppendMsg('bot', '⚠️ Error: ' + err.message);
   }
}

function cookAppendMsg(role, text) {
   const box    = document.getElementById('cook-ai-messages');
   const typing = document.getElementById('cook-typing');
   const now    = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
   const wrap   = document.createElement('div');
   wrap.className = 'cook-msg ' + role;
   wrap.innerHTML = '<div class="cook-msg-bubble">' + cookEscHtml(text) + '</div><div class="cook-msg-time">' + now + '</div>';
   box.insertBefore(wrap, typing);
   box.scrollTop = box.scrollHeight;
}

function cookShowTyping(show) {
   cookTyping = show;
   const t = document.getElementById('cook-typing');
   t.style.display = show ? 'flex' : 'none';
   if (show) document.getElementById('cook-ai-messages').scrollTop = 9999;
}

function cookEscHtml(t) {
   return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}
</script>
<!-- ══════════ END COOK AI WIDGET ══════════ -->
