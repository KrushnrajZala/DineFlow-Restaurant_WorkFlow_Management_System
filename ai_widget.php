<?php
// ============================================================
//  Spice Garden — AI Food Assistant Widget
//  Browser calls Groq directly — no PHP curl needed!
// ============================================================
?>

<!-- ══════════ AI FOOD ASSISTANT WIDGET ══════════ -->
<style>
#ai-fab {
   position: fixed; bottom: 2.8rem; right: 2.8rem;
   width: 6rem; height: 6rem; border-radius: 50%;
   background: linear-gradient(135deg, #B54A2A, #8E361C);
   color: #fff; border: none; cursor: pointer; z-index: 9998;
   box-shadow: 0 6px 24px rgba(181,74,42,0.5);
   display: flex; align-items: center; justify-content: center;
   font-size: 2.4rem;
   transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
   animation: ai-pulse 2.5s infinite;
}
#ai-fab:hover { transform: scale(1.12); box-shadow: 0 10px 32px rgba(181,74,42,0.65); }
#ai-fab .ai-badge {
   position: absolute; top: -.3rem; right: -.3rem;
   background: #f59e0b; color: #fff; font-size: .85rem; font-weight: 700;
   padding: .15rem .45rem; border-radius: 2rem;
   font-family: 'Nunito Sans', sans-serif; pointer-events: none;
}
@keyframes ai-pulse {
   0%,100% { box-shadow: 0 6px 24px rgba(181,74,42,0.5); }
   50%      { box-shadow: 0 6px 32px rgba(181,74,42,0.8), 0 0 0 8px rgba(181,74,42,0.12); }
}
#ai-chat-window {
   position: fixed; bottom: 10rem; right: 2.8rem;
   width: 36rem; max-height: 56rem; background: #fff;
   border-radius: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.18);
   z-index: 9999; display: flex; flex-direction: column; overflow: hidden;
   transform: scale(0.85) translateY(20px); opacity: 0; pointer-events: none;
   transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
   font-family: 'Nunito Sans', sans-serif;
}
#ai-chat-window.open { transform: scale(1) translateY(0); opacity: 1; pointer-events: all; }
#ai-chat-header {
   background: linear-gradient(135deg, #B54A2A, #8E361C); color: #fff;
   padding: 1.4rem 1.8rem; display: flex; align-items: center; gap: 1.2rem; flex-shrink: 0;
}
.ai-avatar {
   width: 4.2rem; height: 4.2rem; background: rgba(255,255,255,0.2);
   border-radius: 50%; display: flex; align-items: center; justify-content: center;
   font-size: 2rem; flex-shrink: 0;
}
.ai-header-info h4 { font-size: 1.5rem; font-weight: 700; margin: 0 0 .2rem; }
.ai-header-info p  { font-size: 1.1rem; opacity: .8; margin: 0; }
.ai-online-dot {
   width: .8rem; height: .8rem; background: #4ade80; border-radius: 50%;
   display: inline-block; margin-right: .4rem; animation: blink 1.5s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
#ai-close-btn {
   margin-left: auto; background: rgba(255,255,255,0.15); border: none; color: #fff;
   width: 3rem; height: 3rem; border-radius: 50%; cursor: pointer; font-size: 1.4rem;
   display: flex; align-items: center; justify-content: center; transition: background .2s; flex-shrink: 0;
}
#ai-close-btn:hover { background: rgba(255,255,255,0.3); }
#ai-chips {
   padding: 1rem 1.4rem .4rem; display: flex; flex-wrap: wrap; gap: .6rem;
   background: #f8f9ff; border-bottom: 1px solid #e5e7eb; flex-shrink: 0;
}
.ai-chip {
   background: #eef2ff; color: #B54A2A; border: 1.5px solid #c7d2fe;
   border-radius: 2rem; padding: .4rem 1rem; font-size: 1.1rem; font-weight: 600;
   cursor: pointer; transition: all .2s; font-family: 'Nunito Sans', sans-serif;
}
.ai-chip:hover { background: #B54A2A; color: #fff; border-color: #B54A2A; }
#ai-messages {
   flex: 1; overflow-y: auto; padding: 1.5rem;
   display: flex; flex-direction: column; gap: 1.2rem;
   background: #f8f9ff; min-height: 24rem; max-height: 32rem;
}
#ai-messages::-webkit-scrollbar { width: .4rem; }
#ai-messages::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 1rem; }
.ai-msg { display: flex; flex-direction: column; max-width: 85%; }
.ai-msg.bot  { align-self: flex-start; }
.ai-msg.user { align-self: flex-end; align-items: flex-end; }
.ai-msg-bubble {
   padding: 1rem 1.4rem; border-radius: 1.4rem;
   font-size: 1.3rem; line-height: 1.6; word-break: break-word; white-space: pre-wrap;
}
.ai-msg.bot  .ai-msg-bubble {
   background: #fff; color: #1a1a2e; border: 1px solid #e5e7eb;
   border-bottom-left-radius: .3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.ai-msg.user .ai-msg-bubble {
   background: linear-gradient(135deg, #B54A2A, #8E361C);
   color: #fff; border-bottom-right-radius: .3rem;
}
.ai-msg-time { font-size: 1rem; color: #9ca3af; margin-top: .3rem; padding: 0 .4rem; }
#ai-typing { display: none; align-self: flex-start; }
#ai-typing .ai-msg-bubble {
   background: #fff; border: 1px solid #e5e7eb;
   border-bottom-left-radius: .3rem; padding: 1.1rem 1.6rem;
}
.typing-dots { display: flex; gap: .4rem; align-items: center; }
.typing-dots span {
   width: .7rem; height: .7rem; background: #B54A2A;
   border-radius: 50%; animation: bounce 1.2s infinite;
}
.typing-dots span:nth-child(2) { animation-delay: .2s; }
.typing-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-.6rem)} }
#ai-input-area {
   padding: 1.2rem 1.4rem; border-top: 1px solid #e5e7eb;
   display: flex; gap: .8rem; align-items: center; background: #fff; flex-shrink: 0;
}
#ai-input {
   flex: 1; padding: .9rem 1.3rem; border: 1.5px solid #e5e7eb; border-radius: 1rem;
   font-size: 1.3rem; font-family: 'Nunito Sans', sans-serif;
   color: #1a1a2e; background: #f8f9ff; outline: none; transition: border .2s;
}
#ai-input:focus { border-color: #B54A2A; background: #eef2ff; }
#ai-input::placeholder { color: #9ca3af; }
#ai-send-btn {
   width: 4rem; height: 4rem;
   background: linear-gradient(135deg, #B54A2A, #8E361C);
   color: #fff; border: none; border-radius: 1rem; font-size: 1.5rem; cursor: pointer;
   display: flex; align-items: center; justify-content: center;
   transition: transform .2s, box-shadow .2s; flex-shrink: 0;
}
#ai-send-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(181,74,42,0.4); }
#ai-send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
@media(max-width:480px){
   #ai-chat-window { width: calc(100vw - 2rem); right: 1rem; bottom: 9rem; }
   #ai-fab { bottom: 2rem; right: 2rem; }
}
</style>

<button id="ai-fab" onclick="toggleAiChat()" title="AI Food Assistant">
   🤖<span class="ai-badge">AI</span>
</button>

<div id="ai-chat-window">
   <div id="ai-chat-header">
      <div class="ai-avatar">🍽️</div>
      <div class="ai-header-info">
         <h4>Spice Garden AI</h4>
         <p><span class="ai-online-dot"></span>Food Assistant · Always Online</p>
      </div>
      <button id="ai-close-btn" onclick="toggleAiChat()"><i class="fas fa-times"></i></button>
   </div>
   <div id="ai-chips">
      <span class="ai-chip" onclick="sendChip('Not spicy food')">Not Spicy</span>
      <span class="ai-chip" onclick="sendChip('Veg only')">Veg Only</span>
      <span class="ai-chip" onclick="sendChip('Non veg')">Non Veg</span>
      <span class="ai-chip" onclick="sendChip('Something sweet')">Sweet</span>
      <span class="ai-chip" onclick="sendChip('Cheap under 100 rupees')">Budget</span>
      <span class="ai-chip" onclick="sendChip('Starter suggestions')">Starters</span>
      <span class="ai-chip" onclick="sendChip('Something to drink')">Drinks</span>
   </div>
   <div id="ai-messages">
      <div class="ai-msg bot">
         <div class="ai-msg-bubble">
            👋 Hello! I'm your Spice Garden food assistant!<br><br>
            Tell me what you're in the mood for — spicy, veg, sweet, budget-friendly — and I'll suggest the best items from our menu! 😊
         </div>
         <div class="ai-msg-time">Just now</div>
      </div>
      <div class="ai-msg bot" id="ai-typing">
         <div class="ai-msg-bubble">
            <div class="typing-dots"><span></span><span></span><span></span></div>
         </div>
      </div>
   </div>
   <div id="ai-input-area">
      <input type="text" id="ai-input" placeholder="e.g. not spicy, veg, cheap..." maxlength="300"
         onkeypress="if(event.key==='Enter') sendAiMsg()">
      <button id="ai-send-btn" onclick="sendAiMsg()">
         <i class="fas fa-paper-plane"></i>
      </button>
   </div>
</div>

<script>
const GROQ_KEY = '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';

const MENU_TEXT = `[Main Course]
Paneer Butter Masala (Rs.220), Dal Makhani (Rs.180), Shahi Paneer (Rs.240)

[Rice]
Veg Biryani (Rs.200), Chicken Biryani (Rs.280)

[Starter]
Chicken Tikka (Rs.280), Veg Manchurian (Rs.160), Samosa 2pcs (Rs.40), Spring Roll (Rs.80)

[Bread]
Butter Naan (Rs.40), Garlic Naan (Rs.50), Roti (Rs.20), Paratha (Rs.35)

[Drinks]
Mango Lassi (Rs.80), Masala Chai (Rs.30), Cold Coffee (Rs.90), Fresh Lime Soda (Rs.60)

[Dessert]
Gulab Jamun (Rs.60), Ice Cream 2 scoops (Rs.80), Rasmalai (Rs.70)`;

const SYSTEM_PROMPT = `You are a friendly AI food assistant for Spice Garden restaurant. Help customers pick food from the menu based on their preferences.

OUR MENU:
${MENU_TEXT}

RULES:
- Only recommend items from the menu above
- Be friendly and short (max 4-5 lines)
- Not spicy: recommend Bread, Drinks, Desserts, Dal Makhani, Paneer dishes
- Spicy: recommend Chicken Tikka, Veg Manchurian, Biryani
- Veg: no chicken items
- Non veg: include Chicken Biryani, Chicken Tikka
- Sweet: Gulab Jamun, Rasmalai, Ice Cream, Mango Lassi
- Budget/cheap: items under Rs.100
- Starter: Samosa, Spring Roll, Veg Manchurian, Chicken Tikka
- Drinks: Mango Lassi, Masala Chai, Cold Coffee, Fresh Lime Soda
- Always show item name and price
- End with a friendly question like Want something else?`;

let aiOpen = false;
let aiHistory = [];
let aiTyping = false;

function toggleAiChat() {
   aiOpen = !aiOpen;
   const win = document.getElementById('ai-chat-window');
   const fab = document.getElementById('ai-fab');
   if (aiOpen) {
      win.classList.add('open');
      fab.style.transform = 'scale(0.9) rotate(10deg)';
      setTimeout(() => document.getElementById('ai-input').focus(), 300);
   } else {
      win.classList.remove('open');
      fab.style.transform = '';
   }
}

function sendChip(text) {
   document.getElementById('ai-input').value = text;
   sendAiMsg();
}

async function sendAiMsg() {
   if (aiTyping) return;
   const input = document.getElementById('ai-input');
   const msg = input.value.trim();
   if (!msg) return;

   input.value = '';
   appendMsg('user', msg);
   aiHistory.push({ role: 'user', content: msg });
   showTyping(true);
   document.getElementById('ai-send-btn').disabled = true;

   try {
      // Build messages array — must start with user or system
      const messages = [{ role: 'system', content: SYSTEM_PROMPT }];

      // Add last 6 history messages
      const recent = aiHistory.slice(-6);
      recent.forEach(h => messages.push({ role: h.role, content: h.content }));

      const res = await fetch('https://api.groq.com/openai/v1/chat/completions', {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + GROQ_KEY
         },
         body: JSON.stringify({
            model: 'llama-3.1-8b-instant',
            max_tokens: 300,
            temperature: 0.7,
            messages: messages
         })
      });

      const data = await res.json();

      if (!res.ok) {
         console.error('Groq Error:', data);
         throw new Error(data?.error?.message || 'API Error ' + res.status);
      }

      const reply = data?.choices?.[0]?.message?.content?.trim() || 'Sorry, no response. Try again!';

      showTyping(false);
      document.getElementById('ai-send-btn').disabled = false;
      appendMsg('bot', reply);
      aiHistory.push({ role: 'assistant', content: reply });
      if (aiHistory.length > 12) aiHistory = aiHistory.slice(-12);

   } catch (err) {
      showTyping(false);
      document.getElementById('ai-send-btn').disabled = false;
      console.error('AI Error:', err);
      appendMsg('bot', '⚠️ Error: ' + err.message);
   }
}

function appendMsg(role, text) {
   const box = document.getElementById('ai-messages');
   const typing = document.getElementById('ai-typing');
   const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
   const wrap = document.createElement('div');
   wrap.className = 'ai-msg ' + role;
   wrap.innerHTML = '<div class="ai-msg-bubble">' + escHtml(text) + '</div><div class="ai-msg-time">' + now + '</div>';
   box.insertBefore(wrap, typing);
   box.scrollTop = box.scrollHeight;
}

function showTyping(show) {
   aiTyping = show;
   const t = document.getElementById('ai-typing');
   t.style.display = show ? 'flex' : 'none';
   if (show) document.getElementById('ai-messages').scrollTop = 9999;
}

function escHtml(t) {
   return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}
</script>
<!-- ══════════ END AI WIDGET ══════════ -->
