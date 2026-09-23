<div id="mk-live-chat" class="mk-live-chat">
    <button type="button" id="mk-chat-launcher" class="mk-chat-launcher" aria-expanded="false">💬 <span>Hỗ trợ</span></button>

    <section id="mk-chat-panel" class="mk-chat-panel" hidden>
        <header class="mk-chat-header">
            <div><strong>MommyKids hỗ trợ</strong><small id="mk-chat-status">Bot đang hỗ trợ</small></div>
            <button type="button" id="mk-chat-close">×</button>
        </header>

        <div id="mk-chat-staff" class="mk-chat-staff" hidden></div>
        <div id="mk-chat-messages" class="mk-chat-messages" aria-live="polite"></div>

        <div id="mk-chat-quick" class="mk-chat-quick">
            <button type="button" data-chat-quick="Tư vấn sản phẩm">🍼 Sản phẩm</button>
            <button type="button" data-chat-quick="Kiểm tra đơn hàng">📦 Đơn hàng</button>
            <button type="button" data-chat-quick="Voucher và khuyến mãi">🎟 Voucher</button>
            <button type="button" data-chat-quick="Phí vận chuyển GHN">🚚 Vận chuyển</button>
            <button type="button" id="mk-chat-request-staff" class="is-staff">👩‍💼 Gặp nhân viên</button>
        </div>

        <div id="mk-chat-waiting" class="mk-chat-waiting" hidden>
            <span class="mk-chat-spinner"></span>
            <div><strong>Đang kết nối nhân viên...</strong><small>MommyKids đã nhận yêu cầu của bạn.</small></div>
        </div>

        <form id="mk-chat-form" class="mk-chat-form">
            <textarea id="mk-chat-input" rows="1" maxlength="2000" placeholder="Nhập tin nhắn..."></textarea>
            <button type="submit" id="mk-chat-send">Gửi</button>
        </form>

        <div id="mk-chat-closed" class="mk-chat-closed" hidden>
            <strong>Cuộc trò chuyện đã kết thúc</strong>
            <span>Cảm ơn bạn đã liên hệ MommyKids.</span>
            <button type="button" id="mk-chat-new-session">Bắt đầu phiên trò chuyện mới</button>
        </div>
    </section>
</div>

<style>
.mk-live-chat{position:fixed;right:24px;bottom:24px;z-index:9999;font-family:inherit}.mk-chat-launcher{border:0;border-radius:999px;background:#ff5f76;color:#fff;padding:13px 18px;font-weight:800;box-shadow:0 14px 34px rgba(255,95,118,.34);cursor:pointer}.mk-chat-panel{position:absolute;right:0;bottom:62px;width:370px;height:560px;max-height:calc(100vh - 105px);background:#fff;border:1px solid #f0dfe3;border-radius:22px;box-shadow:0 24px 70px rgba(29,21,24,.2);overflow:hidden;grid-template-rows:auto auto 1fr auto auto auto}.mk-chat-panel:not([hidden]){display:grid}.mk-chat-header{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;background:linear-gradient(135deg,#ff5f76,#ff7d91);color:#fff}.mk-chat-header strong,.mk-chat-header small{display:block}.mk-chat-header small{margin-top:3px;opacity:.9}.mk-chat-header button{border:0;background:rgba(255,255,255,.18);color:#fff;width:32px;height:32px;border-radius:50%;font-size:22px;cursor:pointer}.mk-chat-staff{padding:10px 16px;background:#fff5f7;border-bottom:1px solid #f7e1e6;font-size:13px}.mk-chat-messages{padding:16px;overflow-y:auto;background:#fffafb}.mk-chat-row{display:flex;margin-bottom:12px}.mk-chat-row.customer{justify-content:flex-end}.mk-chat-row.system{justify-content:center}.mk-chat-bubble{max-width:82%;padding:10px 12px;border-radius:15px;background:#fff;border:1px solid #f0e4e7;color:#28232a}.mk-chat-row.customer .mk-chat-bubble{background:#ff5f76;border-color:#ff5f76;color:#fff;border-bottom-right-radius:5px}.mk-chat-row.system .mk-chat-bubble{background:transparent;border:0;color:#8b7c82;text-align:center;font-size:12px}.mk-chat-meta{display:block;margin-bottom:4px;font-size:11px;font-weight:800;opacity:.72}.mk-chat-time{display:block;margin-top:5px;font-size:10px;opacity:.65;text-align:right}.mk-chat-quick{display:flex;gap:7px;padding:8px 12px 12px;overflow-x:auto;border-top:1px solid #f4e8eb;background:#fff}.mk-chat-quick button{flex:0 0 auto;border:1px solid #ffd0d8;background:#fff7f8;color:#d9465d;border-radius:999px;padding:8px 10px;font-size:12px;font-weight:700;cursor:pointer}.mk-chat-quick .is-staff{background:#ff5f76;color:#fff}.mk-chat-waiting{display:flex;align-items:center;gap:10px;padding:11px 14px;background:#fff8eb;border-top:1px solid #f8e1b4}.mk-chat-waiting strong,.mk-chat-waiting small{display:block}.mk-chat-spinner{width:16px;height:16px;border:2px solid #f3d18b;border-top-color:#b7791f;border-radius:50%;animation:mkChatSpin .8s linear infinite}@keyframes mkChatSpin{to{transform:rotate(360deg)}}.mk-chat-form{display:grid;grid-template-columns:1fr auto;gap:8px;padding:12px;border-top:1px solid #f0e4e7}.mk-chat-form textarea{resize:none;border:1px solid #e8dce0;border-radius:15px;padding:10px 12px;font:inherit}.mk-chat-form button{border:0;border-radius:14px;background:#ff5f76;color:#fff;padding:0 16px;font-weight:800}.mk-chat-closed{padding:14px;border-top:1px solid #f0e4e7;text-align:center}.mk-chat-closed strong,.mk-chat-closed span{display:block}.mk-chat-closed span{margin:4px 0 10px;color:#81757a;font-size:12px}.mk-chat-closed button{border:0;border-radius:999px;background:#ff5f76;color:#fff;padding:10px 14px;font-weight:800}.mk-chat-form button:disabled,.mk-chat-form textarea:disabled{opacity:.5}@media(max-width:520px){.mk-live-chat{right:12px;bottom:12px}.mk-chat-launcher span{display:none}.mk-chat-panel{position:fixed;inset:0;width:auto;height:auto;max-height:none;border-radius:0}}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const apiBase = @json(url('/chat'));
    const csrf = @json(csrf_token());
    const launcher=document.getElementById('mk-chat-launcher'),panel=document.getElementById('mk-chat-panel'),closeBtn=document.getElementById('mk-chat-close'),statusText=document.getElementById('mk-chat-status'),staffBox=document.getElementById('mk-chat-staff'),messagesBox=document.getElementById('mk-chat-messages'),quickBox=document.getElementById('mk-chat-quick'),waitingBox=document.getElementById('mk-chat-waiting'),form=document.getElementById('mk-chat-form'),input=document.getElementById('mk-chat-input'),sendBtn=document.getElementById('mk-chat-send'),closedBox=document.getElementById('mk-chat-closed'),newBtn=document.getElementById('mk-chat-new-session'),staffBtn=document.getElementById('mk-chat-request-staff');
    let conversation=null,timer=null,lastKey='';

    async function req(url, options={}){const r=await fetch(url,{...options,headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf,...(options.headers||{})}});const d=await r.json().catch(()=>({}));if(!r.ok)throw new Error(d.message||'Không thể thực hiện yêu cầu.');return d}
    const esc=v=>{const d=document.createElement('div');d.textContent=String(v??'');return d.innerHTML};

    function render(data){conversation=data.conversation;const key=JSON.stringify([conversation.status,conversation.staff?.name||'',data.messages.map(m=>m.id)]);if(key!==lastKey){messagesBox.innerHTML=data.messages.map(m=>`<div class="mk-chat-row ${esc(m.sender_type)}"><div class="mk-chat-bubble"><span class="mk-chat-meta">${esc(m.sender_name)}</span><div>${esc(m.message).replace(/\n/g,'<br>')}</div><span class="mk-chat-time">${esc(m.time||'')}</span></div></div>`).join('');messagesBox.scrollTop=messagesBox.scrollHeight;lastKey=key}const s=conversation.status;quickBox.hidden=s!=='bot';waitingBox.hidden=s!=='waiting_staff';closedBox.hidden=s!=='closed';form.hidden=s==='closed';input.disabled=s==='closed';sendBtn.disabled=s==='closed';staffBox.hidden=true;if(s==='bot')statusText.textContent='Bot đang hỗ trợ';if(s==='waiting_staff')statusText.textContent='Đang chờ nhân viên';if(s==='staff_connected'){statusText.textContent='Đã kết nối nhân viên';staffBox.hidden=false;staffBox.textContent='👩‍💼 Chuyên viên: '+(conversation.staff?.name||'MommyKids')}if(s==='closed')statusText.textContent='Phiên đã kết thúc'}
    async function openChat(){panel.hidden=false;launcher.setAttribute('aria-expanded','true');render(await req(`${apiBase}/session`,{method:'POST',body:'{}'}));start();input.focus()}
    function closeChat(){panel.hidden=true;launcher.setAttribute('aria-expanded','false');stop()}
    async function poll(){if(!conversation||panel.hidden)return;try{render(await req(`${apiBase}/${conversation.id}/messages`))}catch(e){console.error(e)}}
    function start(){stop();timer=setInterval(poll,2000)}function stop(){if(timer){clearInterval(timer);timer=null}}
    async function send(message){const text=String(message||'').trim();if(!text||!conversation||conversation.status==='closed')return;input.disabled=true;sendBtn.disabled=true;try{const data=await req(`${apiBase}/${conversation.id}/messages`,{method:'POST',body:JSON.stringify({message:text})});input.value='';render(data)}finally{if(conversation?.status!=='closed'){input.disabled=false;sendBtn.disabled=false;input.focus()}}}

    launcher.addEventListener('click',async()=>{if(panel.hidden){try{await openChat()}catch(e){alert(e.message)}}else closeChat()});closeBtn.addEventListener('click',closeChat);
    form.addEventListener('submit',async e=>{e.preventDefault();try{await send(input.value)}catch(err){alert(err.message)}});
    input.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();form.requestSubmit()}});
    document.querySelectorAll('[data-chat-quick]').forEach(b=>b.addEventListener('click',()=>send(b.dataset.chatQuick).catch(e=>alert(e.message))));
    staffBtn.addEventListener('click',async()=>{if(!conversation)return;try{render(await req(`${apiBase}/${conversation.id}/request-staff`,{method:'POST',body:'{}'}))}catch(e){alert(e.message)}});
    newBtn.addEventListener('click',async()=>{try{lastKey='';render(await req(`${apiBase}/session/new`,{method:'POST',body:'{}'}));input.value='';input.focus()}catch(e){alert(e.message)}});
    window.addEventListener('beforeunload',stop);
});
</script>
