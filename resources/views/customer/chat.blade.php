@extends('customer.layouts.app')
@section('title', 'Chat — Order #' . $order->id)

@section('content')
<style>
    .chat-wrap { max-width: 600px; margin: 0 auto; }
    .chat-header {
        background: white; border-radius: 16px 16px 0 0; padding: 16px 20px;
        display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .chat-avatar {
        width: 42px; height: 42px; border-radius: 50%; background: #e8342a; color: white;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0;
    }
    .chat-body {
        background: #F3EDE7; height: 420px; overflow-y: auto; padding: 16px 20px;
        display: flex; flex-direction: column; gap: 8px;
    }
    .chat-bubble {
        max-width: 75%; padding: 8px 14px; border-radius: 14px; font-size: 13.5px; line-height: 1.4;
        word-wrap: break-word;
    }
    .chat-bubble.mine { align-self: flex-end; background: #e8342a; color: white; border-bottom-right-radius: 4px; }
    .chat-bubble.theirs { align-self: flex-start; background: white; color: #222; border-bottom-left-radius: 4px; }
    .chat-time { font-size: 10px; opacity: 0.7; margin-top: 2px; display: inline-block; }
    .chat-tick { font-size: 12px; margin-left: 3px; opacity: 0.85; }
    .chat-tick.seen { color: #7fd7ff; }
    .chat-footer {
        background: white; border-radius: 0 0 16px 16px; padding: 12px 16px;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.05); display: flex; gap: 8px;
    }
    .chat-input { flex-grow: 1; border: 1.5px solid #eee; border-radius: 24px; padding: 8px 16px; font-size: 13.5px; }
    .chat-send-btn {
        background: #e8342a; color: white; border: none; width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
</style>

<div class="chat-wrap">
    <div class="chat-header">
        <a href="{{ route('customer.orders.show', $order) }}" class="text-muted" style="font-size:20px"><i class="bi bi-arrow-left"></i></a>
        <div class="chat-avatar">{{ strtoupper(substr($deliveryAgent->name ?? 'D', 0, 1)) }}</div>
        <div>
            <div class="fw-bold" style="font-size:14.5px">{{ $deliveryAgent->name ?? 'Delivery Partner' }}</div>
            <div class="text-muted" style="font-size:11.5px">Order #{{ $order->id }} · {{ ucfirst(str_replace('_',' ',$order->status)) }}</div>
        </div>
    </div>

    <div class="chat-body" id="chatBody">
        @forelse($messages as $m)
        <div class="chat-bubble {{ $m->sender_id === auth()->id() ? 'mine' : 'theirs' }}" data-msg-id="{{ $m->id }}">
            {{ $m->message }}
            <span class="chat-time">{{ $m->created_at->format('h:i A') }}</span>
            @if($m->sender_id === auth()->id())
                <i class="bi {{ $m->read_at ? 'bi-check2-all chat-tick seen' : 'bi-check2 chat-tick' }}"></i>
            @endif
        </div>
        @empty
        <div class="text-center text-muted mt-4" style="font-size:13px">
            Say hi to your delivery partner! 👋
        </div>
        @endforelse
    </div>

    <!-- action + method are a fallback: if the JS below fails to run for any
         reason, this still submits correctly as a real POST instead of
         silently falling back to a GET request (which would leak the
         message into the URL and never actually save it). -->
    <form class="chat-footer" id="chatForm" action="{{ route('customer.chat.send', $order) }}" method="POST">
        @csrf
        <input type="text" name="message" id="chatInput" class="chat-input" placeholder="Type a message..." maxlength="500" autocomplete="off" required>
        <button type="submit" class="chat-send-btn"><i class="bi bi-send-fill"></i></button>
    </form>
    <div id="chatErrorBanner" class="d-none text-center text-danger" style="font-size:12px;padding:6px">
        Message didn't send — check your connection and try again.
    </div>
</div>
@endsection

@section('scripts')
<script>
const chatBody = document.getElementById('chatBody');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const chatErrorBanner = document.getElementById('chatErrorBanner');
let lastId = {{ $messages->last()->id ?? 0 }};
let tempIdCounter = -1;

function scrollToBottom() {
    chatBody.scrollTop = chatBody.scrollHeight;
}
scrollToBottom();

function appendMessage(m) {
    if (chatBody.querySelector('[data-msg-id="' + m.id + '"]')) return;

    const div = document.createElement('div');
    div.className = 'chat-bubble ' + (m.is_mine ? 'mine' : 'theirs');
    div.dataset.msgId = m.id;
    div.innerHTML = escapeHtml(m.message) + '<span class="chat-time">' + m.time + '</span>';
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

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

chatForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const message = chatInput.value.trim();
    if (!message) return;

    chatErrorBanner.classList.add('d-none');

    const tempId = tempIdCounter--;
    appendMessage({
        id: tempId,
        message: message,
        is_mine: true,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    });
    const tempBubble = chatBody.querySelector('[data-msg-id="' + tempId + '"]');
    scrollToBottom();
    chatInput.value = '';

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
        chatErrorBanner.classList.remove('d-none');
    });
});

function poll() {
    fetch(@json(route('customer.chat.poll', $order)) + '?after_id=' + lastId)
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

setInterval(poll, 4000);
</script>
@endsection
