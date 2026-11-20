@extends('delivery.layouts.app')
@section('title', 'Chat — Order #' . $order->id)
@section('page-title', 'Chat')

@section('content')
<style>
    .chat-wrap { --chat-accent: #2475dc; --chat-accent-dark: #1558b4; max-width: 780px; margin: 0 auto; padding: 8px 0 24px; }
    .chat-card { overflow: hidden; border: 1px solid #e8edf4; border-radius: 22px; background: #fff; box-shadow: 0 18px 50px rgba(30, 48, 73, .09); }
    .chat-header { display: flex; align-items: center; gap: 14px; padding: 20px 24px; background: linear-gradient(115deg, #fff 52%, #f1f7ff); }
    .chat-back { display: grid; place-items: center; width: 38px; height: 38px; border: 1px solid #e4eaf2; border-radius: 12px; color: #5d6775; text-decoration: none; transition: .2s ease; }
    .chat-back:hover { color: var(--chat-accent); border-color: #b8d2f3; background: #f5f9ff; }
    .chat-avatar { position: relative; display: grid; place-items: center; width: 50px; height: 50px; flex: 0 0 50px; border: 3px solid #fff; border-radius: 17px; background: linear-gradient(145deg, #64a7f4, var(--chat-accent)); color: #fff; font-size: 19px; font-weight: 800; box-shadow: 0 5px 14px rgba(36, 117, 220, .2); }
    .chat-person { min-width: 0; flex: 1; }
    .chat-person-name { overflow: hidden; color: #252c35; font-size: 16px; font-weight: 750; text-overflow: ellipsis; white-space: nowrap; }
    .chat-subtitle { margin-top: 3px; color: #788493; font-size: 12px; }
    .chat-order-pill { display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border: 1px solid #e5ebf3; border-radius: 999px; background: rgba(255,255,255,.82); color: #5d6978; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .chat-order-pill i { color: var(--chat-accent); }
    .chat-body { display: flex; flex-direction: column; gap: 12px; height: min(58vh, 560px); min-height: 360px; overflow-y: auto; padding: 24px clamp(16px, 5vw, 42px); background-color: #f7f9fc; background-image: radial-gradient(#e2e8f0 0.7px, transparent 0.7px); background-size: 18px 18px; scroll-behavior: smooth; }
    .chat-body::-webkit-scrollbar { width: 6px; }
    .chat-body::-webkit-scrollbar-thumb { border-radius: 10px; background: #cbd5e1; }
    .chat-bubble { max-width: min(76%, 470px); padding: 11px 15px 9px; border: 1px solid rgba(30, 45, 65, .04); border-radius: 18px; font-size: 13.5px; line-height: 1.55; overflow-wrap: anywhere; box-shadow: 0 3px 10px rgba(33, 48, 68, .045); white-space: pre-wrap; }
    .chat-bubble.mine { align-self: flex-end; border-color: transparent; border-bottom-right-radius: 5px; background: linear-gradient(135deg, #438ce8, var(--chat-accent-dark)); color: #fff; }
    .chat-bubble.theirs { align-self: flex-start; border-bottom-left-radius: 5px; background: #fff; color: #2f3742; }
    .chat-time { display: inline-block; margin: 4px 0 0 10px; color: inherit; font-size: 10px; opacity: .7; white-space: nowrap; }
    .chat-tick { margin-left: 3px; font-size: 12px; opacity: .85; }
    .chat-tick.seen { color: #a9eaff; }
    .chat-empty { width: min(100%, 340px); margin: auto; padding: 25px; border: 1px solid #e8edf4; border-radius: 20px; background: rgba(255,255,255,.9); text-align: center; box-shadow: 0 8px 24px rgba(33, 48, 68, .05); }
    .chat-empty-icon { display: grid; place-items: center; width: 52px; height: 52px; margin: 0 auto 12px; border-radius: 17px; background: #edf5ff; color: var(--chat-accent); font-size: 23px; }
    .chat-empty strong { display: block; color: #2f3742; font-size: 15px; }
    .chat-empty p { margin: 5px 0 0; color: #788493; font-size: 12px; }
    .chat-composer { padding: 12px 20px 14px; border-top: 1px solid #e9edf3; background: #fff; }
    .chat-compose-row { display: flex; align-items: center; gap: 10px; padding: 5px 6px 5px 16px; border: 1px solid #e3e9f0; border-radius: 17px; background: #f9fafc; transition: border-color .2s, box-shadow .2s, background .2s; }
    .chat-compose-row:focus-within { border-color: #9dc2f0; background: #fff; box-shadow: 0 0 0 4px rgba(36, 117, 220, .08); }
    .chat-input { min-width: 0; flex: 1; padding: 10px 0; border: 0; outline: 0; background: transparent; color: #2f3742; font-size: 14px; }
    .chat-input::placeholder { color: #9aa5b2; }
    .chat-send-btn { display: grid; place-items: center; width: 42px; height: 42px; flex: 0 0 42px; border: 0; border-radius: 13px; background: linear-gradient(145deg, #438ce8, var(--chat-accent-dark)); color: #fff; box-shadow: 0 5px 12px rgba(36, 117, 220, .22); transition: transform .18s, opacity .18s; }
    .chat-send-btn:hover:not(:disabled) { transform: translateY(-1px); }
    .chat-send-btn:disabled { cursor: not-allowed; opacity: .48; box-shadow: none; }
    .chat-composer-meta { display: flex; justify-content: space-between; gap: 12px; padding: 8px 3px 0; color: #8994a1; font-size: 10px; }
    .chat-error { margin: 0; padding: 0 20px 12px; background: #fff; font-size: 12px; }
    @media (max-width: 575.98px) { .chat-wrap { margin: 0 -8px; padding-top: 0; } .chat-card { border-radius: 18px; } .chat-header { gap: 10px; padding: 15px 14px; } .chat-avatar { width: 44px; height: 44px; flex-basis: 44px; border-radius: 15px; } .chat-back { width: 34px; height: 34px; flex: 0 0 34px; } .chat-order-pill { align-self: flex-start; padding: 7px 9px; font-size: 10px; } .chat-body { height: calc(100dvh - 270px); min-height: 320px; padding: 18px 14px; } .chat-bubble { max-width: 88%; } .chat-composer { padding: 10px 12px 12px; } .chat-composer-meta { font-size: 9px; } }
</style>

<div class="chat-wrap">
    <div class="chat-card">
        <div class="chat-header">
            <a href="{{ route('delivery.dashboard') }}" class="chat-back" aria-label="Back to dashboard"><i class="bi bi-arrow-left"></i></a>
            <div class="chat-avatar">{{ strtoupper(substr($order->user->name ?? 'C', 0, 1)) }}</div>
            <div class="chat-person">
                <div class="chat-person-name">{{ $order->user->name ?? 'Customer' }}</div>
                <div class="chat-subtitle">Customer · order updates</div>
            </div>
            <div class="chat-order-pill"><i class="bi bi-receipt"></i> Order #{{ $order->id }}</div>
        </div>

        <div class="chat-body" id="chatBody" role="log" aria-live="polite" aria-label="Conversation">
            @forelse($messages as $m)
            <div class="chat-bubble {{ $m->sender_id === auth()->id() ? 'mine' : 'theirs' }}" data-msg-id="{{ $m->id }}">
                {{ $m->message }}
                <span class="chat-time">{{ $m->created_at->format('h:i A') }}</span>
                @if($m->sender_id === auth()->id())
                    <i class="bi {{ $m->read_at ? 'bi-check2-all chat-tick seen' : 'bi-check2 chat-tick' }}"></i>
                @endif
            </div>
            @empty
            <div class="chat-empty">
                <div class="chat-empty-icon"><i class="bi bi-chat-heart"></i></div>
                <strong>Start the conversation</strong>
                <p>Send the customer an update or answer a question about this delivery.</p>
            </div>
            @endforelse
        </div>

        <form class="chat-composer" id="chatForm" action="{{ route('delivery.chat.send', $order) }}" method="POST">
            @csrf
            <div class="chat-compose-row">
                <input type="text" name="message" id="chatInput" class="chat-input" placeholder="Write a message..." maxlength="500" autocomplete="off" aria-label="Write a message" required>
                <button type="submit" class="chat-send-btn" id="chatSendBtn" aria-label="Send message"><i class="bi bi-send-fill"></i></button>
            </div>
            <div class="chat-composer-meta"><span><i class="bi bi-lock-fill me-1"></i>Private order conversation</span><span id="chatCharCount">0 / 500</span></div>
        </form>
        <div id="chatErrorBanner" class="chat-error d-none text-center text-danger" role="alert">
            Message didn't send — check your connection and try again.
        </div>
    </div>
</div>

<script>
(function () {
    const chatBody = document.getElementById('chatBody');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatErrorBanner = document.getElementById('chatErrorBanner');
    const chatSendBtn = document.getElementById('chatSendBtn');
    const chatCharCount = document.getElementById('chatCharCount');
    let lastId = {{ $messages->last()->id ?? 0 }};
    let tempIdCounter = -1;

    chatSendBtn.disabled = !chatInput.value.trim();

    function scrollToBottom(force) {
        if (force || chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 120) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }
    scrollToBottom(true);

    chatInput.addEventListener('input', function () {
        chatSendBtn.disabled = !chatInput.value.trim();
        chatCharCount.textContent = chatInput.value.length + ' / 500';
    });

    function appendMessage(m) {
        if (chatBody.querySelector('[data-msg-id="' + m.id + '"]')) return;

        const emptyState = chatBody.querySelector('.chat-empty');
        if (emptyState) emptyState.remove();

        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (m.is_mine ? 'mine' : 'theirs');
        div.dataset.msgId = m.id;
        div.innerHTML = escapeHtmlDelivery(m.message) + '<span class="chat-time">' + m.time + '</span>';
        if (m.is_mine) {
            div.innerHTML += '<i class="bi bi-check2 chat-tick"></i>';
        }
        chatBody.appendChild(div);
    }

    // Marks every one of my own bubbles up to lastSeenId as "seen" (double
    // blue tick) — called after every poll since the other person may have
    // just read messages I sent a while ago, not only brand-new ones.
    function updateSeenTicks(lastSeenId) {
        if (!lastSeenId) return;
        chatBody.querySelectorAll('.chat-bubble.mine').forEach(function (bubble) {
            const id = parseInt(bubble.dataset.msgId, 10);
            if (id > 0 && id <= lastSeenId) {
                const tick = bubble.querySelector('.chat-tick');
                if (tick) {
                    tick.classList.remove('bi-check2');
                    tick.classList.add('bi-check2-all', 'seen');
                }
            }
        });
    }

    function escapeHtmlDelivery(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message || chatSendBtn.disabled) return;

        chatErrorBanner.classList.add('d-none');

        const tempId = tempIdCounter--;
        appendMessage({
            id: tempId,
            message: message,
            is_mine: true,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        });
        const tempBubble = chatBody.querySelector('[data-msg-id="' + tempId + '"]');
        scrollToBottom(true);
        chatInput.value = '';
        chatSendBtn.disabled = true;
        chatCharCount.textContent = '0 / 500';

        fetch(chatForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message }),
        }).then(res => {
            if (!res.ok) throw new Error('Send failed: ' + res.status);
            return res.json();
        }).then(data => {
            // Swap the temporary bubble's id for the real saved one, so the
            // next poll recognizes it as already-shown instead of appending
            // a second copy of the same message.
            if (tempBubble && data.id) {
                tempBubble.dataset.msgId = data.id;
                lastId = Math.max(lastId, data.id);
            }
        }).catch(() => {
            if (tempBubble) tempBubble.remove();
            chatInput.value = message;
            chatSendBtn.disabled = false;
            chatCharCount.textContent = message.length + ' / 500';
            chatErrorBanner.classList.remove('d-none');
            chatInput.focus();
        });
    });

    function pollDeliveryChat() {
        fetch(@json(route('delivery.chat.poll', $order)) + '?after_id=' + lastId)
            .then(res => res.json())
            .then(data => {
                if (data.messages.length) {
                    data.messages.forEach(m => {
                        appendMessage(m);
                        lastId = Math.max(lastId, m.id);
                    });
                    scrollToBottom();
                }
                updateSeenTicks(data.last_seen_id);
            })
            .catch(() => {});
    }

    setInterval(pollDeliveryChat, 4000);
})();
</script>
@endsection
