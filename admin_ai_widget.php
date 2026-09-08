<?php
// ============================================================
//  Spice Garden — Admin AI Bot Widget
//  Include in admin_header.php before </header>
//  Admin can ask anything about restaurant data!
// ============================================================
?>

<!-- ══════════ ADMIN AI BOT WIDGET ══════════ -->
<style>
#adm-ai-fab {
   position: fixed; bottom: 2.8rem; right: 2.8rem;
   width: 6rem; height: 6rem; border-radius: 50%;
   background: linear-gradient(135deg, #f59e0b, #d97706);
   color: #fff; border: none; cursor: pointer; z-index: 9998;
   box-shadow: 0 6px 24px rgba(245,158,11,0.5);
   display: flex; align-items: center; justify-content: center;
   font-size: 2.4rem;
   transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
   animation: adm-pulse 2.5s infinite;
}
#adm-ai-fab:hover { transform: scale(1.12); box-shadow: 0 10px 32px rgba(245,158,11,0.7); }
#adm-ai-fab .adm-badge {
   position: absolute; top: -.3rem; right: -.3rem;
   background: #B54A2A; color: #fff; font-size: .85rem; font-weight: 700;
   padding: .15rem .45rem; border-radius: 2rem;
   font-family: 'Nunito Sans', sans-serif; pointer-events: none;
   white-space: nowrap;
}
@keyframes adm-pulse {
   0%,100% { box-shadow: 0 6px 24px rgba(245,158,11,0.5); }
   50%      { box-shadow: 0 6px 32px rgba(245,158,11,0.8), 0 0 0 8px rgba(245,158,11,0.12); }
}
#adm-ai-window {
   position: fixed; bottom: 10rem; right: 2.8rem;
   width: 38rem; max-height: 60rem; background: #fff;
   border-radius: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.18);
   z-index: 9999; display: flex; flex-direction: column; overflow: hidden;
   transform: scale(0.85) translateY(20px); opacity: 0; pointer-events: none;
   transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
   font-family: 'Nunito Sans', sans-serif;
}
#adm-ai-window.open { transform: scale(1) translateY(0); opacity: 1; pointer-events: all; }
#adm-ai-header {
   background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;
   padding: 1.4rem 1.8rem; display: flex; align-items: center; gap: 1.2rem; flex-shrink: 0;
}
.adm-ai-avatar {
   width: 4.2rem; height: 4.2rem; background: rgba(255,255,255,0.2);
   border-radius: 50%; display: flex; align-items: center; justify-content: center;
   font-size: 2rem; flex-shrink: 0;
}
.adm-ai-info h4 { font-size: 1.5rem; font-weight: 700; margin: 0 0 .2rem; }
.adm-ai-info p  { font-size: 1.1rem; opacity: .85; margin: 0; }
.adm-online-dot {
   width: .8rem; height: .8rem; background: #4ade80; border-radius: 50%;
   display: inline-block; margin-right: .4rem; animation: adm-blink 1.5s infinite;
}
@keyframes adm-blink { 0%,100%{opacity:1} 50%{opacity:.3} }
#adm-close-btn {
   margin-left: auto; background: rgba(255,255,255,0.15); border: none; color: #fff;
   width: 3rem; height: 3rem; border-radius: 50%; cursor: pointer; font-size: 1.4rem;
   display: flex; align-items: center; justify-content: center; transition: background .2s; flex-shrink: 0;
}
#adm-close-btn:hover { background: rgba(255,255,255,0.3); }

/* Quick chips */
#adm-ai-chips {
   padding: 1rem 1.4rem .6rem; display: flex; flex-wrap: wrap; gap: .6rem;
   background: #fffbeb; border-bottom: 1px solid #fde68a; flex-shrink: 0;
}
.adm-chip {
   background: #fef3c7; color: #92400e; border: 1.5px solid #fcd34d;
   border-radius: 2rem; padding: .4rem 1rem; font-size: 1.1rem; font-weight: 600;
   cursor: pointer; transition: all .2s; font-family: 'Nunito Sans', sans-serif;
   white-space: nowrap;
}
.adm-chip:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }

/* Messages */
#adm-ai-messages {
   flex: 1; overflow-y: auto; padding: 1.5rem;
   display: flex; flex-direction: column; gap: 1.2rem;
   background: #fffbeb; min-height: 24rem; max-height: 34rem;
}
#adm-ai-messages::-webkit-scrollbar { width: .4rem; }
#adm-ai-messages::-webkit-scrollbar-thumb { background: #fcd34d; border-radius: 1rem; }
.adm-msg { display: flex; flex-direction: column; max-width: 88%; }
.adm-msg.bot  { align-self: flex-start; }
.adm-msg.user { align-self: flex-end; align-items: flex-end; }
.adm-msg-bubble {
   padding: 1rem 1.4rem; border-radius: 1.4rem;
   font-size: 1.3rem; line-height: 1.7; word-break: break-word; white-space: pre-wrap;
}
.adm-msg.bot  .adm-msg-bubble {
   background: #fff; color: #1a1a2e; border: 1px solid #fde68a;
   border-bottom-left-radius: .3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.adm-msg.user .adm-msg-bubble {
   background: linear-gradient(135deg, #f59e0b, #d97706);
   color: #fff; border-bottom-right-radius: .3rem;
}
.adm-msg-time { font-size: 1rem; color: #9ca3af; margin-top: .3rem; padding: 0 .4rem; }
#adm-typing { display: none; align-self: flex-start; }
#adm-typing .adm-msg-bubble {
   background: #fff; border: 1px solid #fde68a;
   border-bottom-left-radius: .3rem; padding: 1.1rem 1.6rem;
}
.adm-dots { display: flex; gap: .4rem; align-items: center; }
.adm-dots span {
   width: .7rem; height: .7rem; background: #f59e0b;
   border-radius: 50%; animation: adm-bounce 1.2s infinite;
}
.adm-dots span:nth-child(2) { animation-delay: .2s; }
.adm-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes adm-bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-.6rem)} }

/* Input */
#adm-input-area {
   padding: 1.2rem 1.4rem; border-top: 1px solid #fde68a;
   display: flex; gap: .8rem; align-items: center; background: #fff; flex-shrink: 0;
}
#adm-input {
   flex: 1; padding: .9rem 1.3rem; border: 1.5px solid #fde68a; border-radius: 1rem;
   font-size: 1.3rem; font-family: 'Nunito Sans', sans-serif;
   color: #1a1a2e; background: #fffbeb; outline: none; transition: border .2s;
}
#adm-input:focus { border-color: #f59e0b; background: #fef3c7; }
#adm-input::placeholder { color: #9ca3af; }
#adm-send-btn {
   width: 4rem; height: 4rem;
   background: linear-gradient(135deg, #f59e0b, #d97706);
   color: #fff; border: none; border-radius: 1rem; font-size: 1.5rem; cursor: pointer;
   display: flex; align-items: center; justify-content: center;
   transition: transform .2s, box-shadow .2s; flex-shrink: 0;
}
#adm-send-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245,158,11,0.4); }
#adm-send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
@media(max-width:480px){
   #adm-ai-window { width: calc(100vw - 2rem); right: 1rem; bottom: 9rem; }
   #adm-ai-fab { bottom: 2rem; right: 2rem; }
}
</style>

<!-- Floating Button -->
<button id="adm-ai-fab" onclick="toggleAdmAi()" title="Admin AI Assistant">
   🧠<span class="adm-badge">ADMIN AI</span>
</button>

<!-- Chat Window -->
<div id="adm-ai-window">
   <div id="adm-ai-header">
      <div class="adm-ai-avatar">📊</div>
      <div class="adm-ai-info">
         <h4>Admin AI Assistant</h4>
         <p><span class="adm-online-dot"></span>Live Restaurant Data · Always Ready</p>
      </div>
      <button id="adm-close-btn" onclick="toggleAdmAi()"><i class="fas fa-times"></i></button>
   </div>

   <!-- Quick Chips -->
   <div id="adm-ai-chips">
      <span class="adm-chip" onclick="admSendChip('Give me dashboard summary for today')">📊 Dashboard Summary</span>
      <span class="adm-chip" onclick="admSendChip('Show me this week sales report')">📈 Sales Report</span>
      <span class="adm-chip" onclick="admSendChip('What are the most popular items?')">🔥 Popular Items</span>
      <span class="adm-chip" onclick="admSendChip('What are the least ordered items?')">📉 Unpopular Items</span>
      <span class="adm-chip" onclick="admSendChip('What is todays total revenue?')">💰 Today Revenue</span>
      <span class="adm-chip" onclick="admSendChip('How many orders today and what is pending?')">📦 Orders Status</span>
      <span class="adm-chip" onclick="admSendChip('Give me staff status and top waiter today')">👨‍💼 Staff Status</span>
      <span class="adm-chip" onclick="admSendChip('Any suggestions to improve restaurant performance?')">💡 Suggestions</span>
   </div>

   <!-- Messages -->
   <div id="adm-ai-messages">
      <div class="adm-msg bot">
         <div class="adm-msg-bubble">
            👋 Hello Admin! I'm your AI Restaurant Assistant!<br><br>
            I have access to your <b>live restaurant data</b>. Ask me anything:<br>
            📊 Dashboard summary<br>
            📈 Sales reports<br>
            🔥 Popular &amp; unpopular items<br>
            💰 Revenue &amp; orders<br>
            👨‍💼 Staff performance<br><br>
            Or click any quick button above! 😊
         </div>
         <div class="adm-msg-time">Just now</div>
      </div>
      <div class="adm-msg bot" id="adm-typing">
         <div class="adm-msg-bubble">
            <div class="adm-dots"><span></span><span></span><span></span></div>
         </div>
      </div>
   </div>

   <!-- Input -->
   <div id="adm-input-area">
      <input type="text" id="adm-input" placeholder="Ask anything about your restaurant..." maxlength="300"
         onkeypress="if(event.key==='Enter') admSendMsg()">
      <button id="adm-send-btn" onclick="admSendMsg()">
         <i class="fas fa-paper-plane"></i>
      </button>
   </div>
</div>

<script>
const ADM_GROQ_KEY = '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';
let admOpen    = false;
let admHistory = [];
let admTyping  = false;
let admDbData  = null; // cached DB data

function toggleAdmAi() {
   admOpen = !admOpen;
   const win = document.getElementById('adm-ai-window');
   const fab = document.getElementById('adm-ai-fab');
   if (admOpen) {
      win.classList.add('open');
      fab.style.transform = 'scale(0.9) rotate(10deg)';
      setTimeout(() => document.getElementById('adm-input').focus(), 300);
      // Load DB data when first opened
      if (!admDbData) loadAdmData();
   } else {
      win.classList.remove('open');
      fab.style.transform = '';
   }
}

// Fetch real data from PHP
async function loadAdmData() {
   try {
      const res  = await fetch('admin_ai_api.php');
      const data = await res.json();
      admDbData  = data.data || 'No data available';
   } catch(e) {
      admDbData = 'Could not load restaurant data.';
   }
}

function admSendChip(text) {
   document.getElementById('adm-input').value = text;
   admSendMsg();
}

async function admSendMsg() {
   if (admTyping) return;
   const input = document.getElementById('adm-input');
   const msg   = input.value.trim();
   if (!msg) return;

   input.value = '';
   admAppendMsg('user', msg);
   admHistory.push({ role: 'user', content: msg });
   admShowTyping(true);
   document.getElementById('adm-send-btn').disabled = true;

   // Make sure data is loaded
   if (!admDbData) await loadAdmData();

   const systemPrompt = `You are a smart AI assistant for the admin of Spice Garden restaurant.
You have access to the following LIVE restaurant data:

${admDbData}

YOUR JOB:
- Answer admin questions about restaurant performance using the data above
- Be helpful, clear, and professional
- Use bullet points for lists
- Keep replies concise but informative (max 8-10 lines)
- When giving suggestions, be specific and practical
- If asked about dashboard summary: mention revenue, orders, pending items, staff status
- If asked about sales report: mention weekly data, best day, total revenue
- If asked about popular items: list top items with order counts
- If asked about unpopular items: suggest promotions or removal
- If asked for suggestions: give 2-3 practical improvement tips based on the data
- Always end with "Anything else you want to know?"
- Do not make up data — only use what is provided above`;

   try {
      const messages = [
         { role: 'system', content: systemPrompt },
         ...admHistory.slice(-8)
      ];

      const res = await fetch('https://api.groq.com/openai/v1/chat/completions', {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + ADM_GROQ_KEY
         },
         body: JSON.stringify({
            model: 'llama-3.3-70b-versatile',
            max_tokens: 400,
            temperature: 0.5,
            messages: messages
         })
      });

      const data  = await res.json();
      if (!res.ok) throw new Error(data?.error?.message || 'API Error');
      const reply = data?.choices?.[0]?.message?.content?.trim() || 'Sorry, try again!';

      admShowTyping(false);
      document.getElementById('adm-send-btn').disabled = false;
      admAppendMsg('bot', reply);
      admHistory.push({ role: 'assistant', content: reply });
      if (admHistory.length > 12) admHistory = admHistory.slice(-12);

   } catch(err) {
      admShowTyping(false);
      document.getElementById('adm-send-btn').disabled = false;
      admAppendMsg('bot', '⚠️ Error: ' + err.message);
   }
}

function admAppendMsg(role, text) {
   const box    = document.getElementById('adm-ai-messages');
   const typing = document.getElementById('adm-typing');
   const now    = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
   const wrap   = document.createElement('div');
   wrap.className = 'adm-msg ' + role;
   wrap.innerHTML = '<div class="adm-msg-bubble">' + admEscHtml(text) + '</div><div class="adm-msg-time">' + now + '</div>';
   box.insertBefore(wrap, typing);
   box.scrollTop = box.scrollHeight;
}

function admShowTyping(show) {
   admTyping = show;
   const t = document.getElementById('adm-typing');
   t.style.display = show ? 'flex' : 'none';
   if (show) document.getElementById('adm-ai-messages').scrollTop = 9999;
}

function admEscHtml(t) {
   return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}
</script>
<!-- ══════════ END ADMIN AI WIDGET ══════════ -->
