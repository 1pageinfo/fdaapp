@extends('layouts.app')

@section('content')
<style>
  /* --- Chat Layout Tokens --- */
  :root {
    --tabs-h: 52px;
    /* top tabs rail height */
    --header-h: 60px;
    /* group header */
    --composer-h: 160px;
    /* message box area */
  }

  @media (min-width: 992px) {
    :root {
      --composer-h: 130px;
    }
  }

  /* Hide horizontal scrollbar in tab rail (still scrollable) */
  .scrollbar-hidden {
    scrollbar-width: none;
  }

  .scrollbar-hidden::-webkit-scrollbar {
    display: none;
  }

  /* Tabs (pill) look on top */
  .tab-pill {
    white-space: nowrap;
  }

  .tab-rail {
    height: var(--tabs-h);
  }

  .tab-rail .nav-link {
    border-radius: 999px;
  }

  /* Chat list takes the remaining viewport height */
  #chat-list {
    height: calc(100dvh - var(--tabs-h) - var(--header-h) - var(--composer-h));
    overflow: auto;
    padding: .5rem .75rem 1rem;
    background: #e7eefa;
    scroll-behavior: smooth;
  }

  /* Skeleton loader for first paint */
  .skeleton {
    animation: pulse 1.4s ease-in-out infinite;
    background: linear-gradient(90deg, #f0f3f9 25%, #e6ebf6 37%, #f0f3f9 63%);
    background-size: 400% 100%;
    border-radius: 12px;
    min-height: 44px;
    margin: 6px 0;
  }

  @keyframes pulse {
    0% {
      background-position: 100% 0
    }

    100% {
      background-position: -100% 0
    }
  }

  /* Message bubbles */
  .msg {
    max-width: 82%;
    border-radius: 18px;
    padding: .6rem .8rem;
    position: relative;
    box-shadow: 0 1px 0 rgba(0, 0, 0, .04);
  }

  .msg.me {
    margin-left: auto;
    background: #daf8cb;
    border-top-right-radius: 6px;
  }

  .msg.other {
    background: #fff;
    border-top-left-radius: 6px;
  }

  .msg .meta {
    display: flex;
    gap: .35rem;
    align-items: baseline;
  }

  .msg .name {
    font-size: .78rem;
    font-weight: 600;
    opacity: .9;
  }

  .msg .time {
    font-size: .72rem;
    opacity: .6;
    margin-top: .15rem;
  }

  .msg .body {
    margin-top: .2rem;
    line-height: 1.35;
    white-space: pre-wrap;
    word-break: break-word;
  }

  .msg .file-chip {
    font-size: .875rem;
    margin-top: .35rem;
  }

  /* Composer */
  .composer-form {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .6rem .8rem;
    border-top: 1px solid var(--bs-border-color);
    background: #fff;
    position: sticky;
    bottom: 0;
    z-index: 2;
  }

  .composer-input {
    flex: 1;
    border-radius: 2rem;
    background: #f0f1f5;
    display: flex;
    align-items: center;
    padding: .4rem 1rem;
    position: relative;
  }

  .composer-input textarea {
    border: 0;
    background: transparent;
    resize: none;
    outline: 0;
    box-shadow: none;
    width: 100%;
    height: 40px;
  }

  .composer-actions {
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .composer-btn {
    border: 0;
    background: none;
    font-size: 1.25rem;
    color: #666;
    cursor: pointer;
  }

  .composer-btn:hover {
    color: var(--bs-primary);
  }

  .send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #4a4aff;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
  }

  .send-btn[disabled] {
    opacity: .5;
    cursor: not-allowed;
  }

  .send-btn:hover:not([disabled]) {
    background: #3737d8;
  }

  /* Emoji panel */
  .emoji-panel {
    position: absolute;
    bottom: 56px;
    right: .5rem;
    background: #fff;
    border: 1px solid var(--bs-border-color);
    border-radius: .75rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
    padding: .5rem;
    display: none;
    max-width: min(320px, 92vw);
    max-height: 260px;
    overflow: auto;
    z-index: 9999;
  }

  .emoji-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: .25rem;
    font-size: 20px;
  }

  @media (max-width: 576px) {
    .emoji-grid {
      grid-template-columns: repeat(7, 1fr);
    }
  }

  .emoji-btn {
    background: transparent;
    border: 0;
    line-height: 1;
    padding: .25rem;
    cursor: pointer;
  }

  /* Selected file preview chip */
  .attach-chip {
    background: #eef0f6;
    border: 1px dashed #c9cfe1;
    color: #3b4261;
    font-size: .8rem;
    border-radius: 999px;
    padding: .2rem .6rem;
    margin-right: .4rem;
    display: none;
    align-items: center;
    gap: .4rem;
  }

  .attach-chip.show {
    display: inline-flex;
  }

  .attach-chip .x {
    cursor: pointer;
    opacity: .6;
  }
</style>

<div class="container py-3">
  <!-- Header -->
  <div class="d-flex align-items-center gap-3 mb-2" style="height: var(--header-h);">
    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
      style="width:40px;height:40px;">💬</div>
    <div class="min-w-0">
      <h5 class="mb-0 text-truncate" title="{{ $group->name }}"><strong>{{ $group->name }}</strong></h5>
      <small class="text-muted text-truncate d-block" title="{{ $chat->tab }}">{{ $chat->tab }}</small>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger mb-2">{{ $errors->first() }}</div>
  @endif

  <div class="chat-shell">
    <!-- Top: Horizontal slider tabs -->
    <div class="px-2 py-2 border-bottom bg-white tab-rail d-flex align-items-center gap-2">
      <button class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex tab-scroll" data-dir="-1" type="button"
        aria-label="Scroll left">‹</button>
      <div id="chatTabs" class="nav nav-pills flex-nowrap overflow-auto scrollbar-hidden w-100">
        @foreach($group->chats as $c)
          <a class="nav-link mx-1 px-3 py-1 tab-pill {{ $c->id === $chat->id ? 'active' : '' }}"
            href="{{ route('groups.chat.show', [$group, $c]) }}">{{ $c->tab }}</a>
        @endforeach
      </div>
      <button class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex tab-scroll" data-dir="1" type="button"
        aria-label="Scroll right">›</button>
    </div>

    <!-- Pin banner -->
    @if($pinned)
      <div class="pin-banner px-3 py-2 d-flex justify-content-between align-items-center bg-white border-bottom">
        <div class="me-3 small">
          <strong>📌 Pinned:</strong>
          @if($pinned->file)
            <a href="{{ asset('storage/app/public/' . $pinned->file->path) }}" target="_blank">{{ $pinned->file->name }}</a>
            @if($pinned->body) — {{ $pinned->body }} @endif
          @else
            {{ $pinned->body }}
          @endif
        </div>
        <form method="POST" action="{{ route('groups.chat.unpin', [$group, $chat]) }}">
          @csrf
          <button class="btn btn-sm btn-outline-dark" type="submit">Unpin</button>
        </form>
      </div>
    @endif

    @php($lastRenderedId = optional($messages->last())->id ?? 0)
    <!-- Messages -->
    <ul id="chat-list" class="bg-body" data-last-id="{{ $lastRenderedId }}" data-user-id="{{ auth()->id() }}">
      @if(($messages->count() ?? 0) === 0)
        <li class="px-2" id="bootSkeleton">
          <div class="skeleton" style="width: 52%;"></div>
          <div class="skeleton" style="width: 66%;"></div>
          <div class="skeleton" style="width: 41%;"></div>
        </li>
      @endif

      @foreach($messages as $m)
      @php($isMine = optional(auth()->user())->id === $m->user_id)
      <li class="mb-3 px-2" data-id="{{ $m->id }}">
        <div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
          <div class="msg {{ $isMine ? 'me' : 'other' }}">
            <div class="meta">
              <div class="name">{{ $m->user->name ?? 'User' }}</div>
              <div class="time text-muted">{{ $m->created_at->diffForHumans() }}</div>
            </div>
            @if($m->body)
              <div class="body">{!! nl2br(e($m->body)) !!}</div>
            @endif
            @if($m->file)
              <div class="file-chip">📎 <a class="{{ $isMine ? 'text-dark' : '' }}" target="_blank"
                  href="{{ asset('storage/app/public/' . $m->file->path) }}">{{ $m->file->name }}</a></div>
            @endif
            <hr>
            <div class="mt-2">
              <form method="POST" action="{{ route('groups.chat.pin', [$group, $chat, $m]) }}" class="d-inline">
                @csrf


                <button class="btn btn-sm" type="submit"><i class="ti-pin"></i></button>

              </form>
              <!-- Edit button -->
              <button class="btn btn-sm" data-bs-toggle="modal" 
                data-bs-target="#editMessageModal-{{ $m->id }}">
                <i class="ti-pencil"></i>
              </button>

              <!-- Modal -->
              <div class="modal fade" id="editMessageModal-{{ $m->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title">Edit Message</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal">      <i class="ti-close"></i></button>
                    </div>

                    <form method="POST" action="{{ route('chat.message.update', [$group->id, $chat->id, $m->id]) }}">
                      @csrf
                      @method('PUT')

                      <div class="modal-body">
                        <textarea name="body" rows="4" class="form-control">{{ $m->body }}</textarea>
                      </div>

                      <div class="modal-footer">
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      </div>

                    </form>

                  </div>
                </div>
              </div>

              <form action="{{ route('chat.message.destroy', [$group->id, $chat->id, $m->id]) }}" method="POST"
                style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm"
                  onclick="return confirm('Are you sure you want to delete this message?')">
                  <i class="ti-trash"></i>
                </button>
              </form>

            </div>

          </div>
        </div>
      </li>
      @endforeach
    </ul>

    <!-- Composer -->
    <form id="composerForm" class="composer-form" method="POST"
      action="{{ route('groups.chat.message', [$group, $chat]) }}" enctype="multipart/form-data">
      @csrf
      <div class="position-relative w-100 d-flex align-items-center">
        <div class="composer-input w-100">
                  <button type="button" id="btnMic" class="composer-btn me-2" title="Voice">🎤</button>
          <textarea id="msgBox" name="body" placeholder="Message"></textarea>
        </div>

        <div id="emojiPanel" class="emoji-panel">
          <div class="emoji-grid" id="emojiGrid">
            <button class="emoji-btn" type="button">😀</button><button class="emoji-btn"
              type="button">😁</button><button class="emoji-btn" type="button">😂</button>
            <button class="emoji-btn" type="button">🤣</button><button class="emoji-btn"
              type="button">😊</button><button class="emoji-btn" type="button">😍</button>
            <button class="emoji-btn" type="button">😘</button><button class="emoji-btn"
              type="button">😎</button><button class="emoji-btn" type="button">🤔</button>
            <button class="emoji-btn" type="button">😅</button><button class="emoji-btn"
              type="button">🙌</button><button class="emoji-btn" type="button">👍</button>
            <button class="emoji-btn" type="button">🙏</button><button class="emoji-btn"
              type="button">❤️</button><button class="emoji-btn" type="button">🔥</button>
            <button class="emoji-btn" type="button">🎉</button><button class="emoji-btn"
              type="button">🥳</button><button class="emoji-btn" type="button">😴</button>
            <button class="emoji-btn" type="button">🤯</button><button class="emoji-btn"
              type="button">🤝</button><button class="emoji-btn" type="button">💡</button>
            <button class="emoji-btn" type="button">📎</button><button class="emoji-btn"
              type="button">📷</button><button class="emoji-btn" type="button">📝</button>
          </div>
        </div>
      </div>

      <div class="composer-actions ms-1">
        <label class="composer-btn mb-0" title="Attach">
          📎
          <input id="fileAll" type="file" hidden accept=".pdf,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.mp4">
        </label>

        <button type="button" id="btnCamera" class="composer-btn" title="Camera">📷</button>
        <input id="fileCamera" type="file" hidden accept="image/*" capture="environment">

        <button type="button" id="btnEmoji" class="composer-btn" title="Emoji">😊</button>

        <button id="sendBtn" class="send-btn" title="Send" type="submit" disabled>▶</button>
      </div>
                
    </form>
<div>
     <span id="attachChip" class="attach-chip">
            <span id="attachName">file</span>
            <span class="x" id="attachClear" title="Remove">✕</span>
          </span>
          </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // never let emoji buttons submit the form
  (function () {
    document.querySelectorAll('.emoji-btn').forEach(btn => btn.setAttribute('type', 'button'));
  })();

  // open camera
  (function () {
    const camBtn = document.getElementById('btnCamera');
    const camInput = document.getElementById('fileCamera');
    camBtn?.addEventListener('click', () => camInput?.click());
  })();

  // Speech-to-Text
  (function () {
    const btn = document.getElementById('btnMic');
    const ta = document.getElementById('msgBox');
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!btn) return;
    if (!SR) { btn.title = 'Voice not supported on this browser'; btn.style.opacity = .5; return; }

    const rec = new SR();
    rec.lang = document.documentElement.lang || 'en-US';
    rec.continuous = false;
    rec.interimResults = true;

    let listening = false;
    btn.addEventListener('click', () => {
      if (listening) { rec.stop(); return; }
      try { rec.start(); listening = true; btn.textContent = '⏺️'; } catch (_) { }
    });

    rec.onresult = (e) => {
      let txt = '';
      for (let i = 0; i < e.results.length; i++) { txt += e.results[i][0].transcript + ' '; }
      ta.value = (ta.value ? ta.value + ' ' : '') + txt.trim();
      ta.dispatchEvent(new Event('input'));
    };
    rec.onend = () => { listening = false; btn.textContent = '🎤'; };
    rec.onerror = () => { listening = false; btn.textContent = '🎤'; };
  })();

  // Emoji picker
  (function () {
    const panel = document.getElementById('emojiPanel');
    const btn = document.getElementById('btnEmoji');
    const ta = document.getElementById('msgBox');

    btn?.addEventListener('click', () => {
      panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    });

    panel?.addEventListener('click', (e) => {
      if (e.target.classList.contains('emoji-btn')) {
        e.preventDefault();
        const emoji = e.target.textContent;
        const s = ta.selectionStart || 0, en = ta.selectionEnd || 0;
        ta.value = ta.value.slice(0, s) + emoji + ta.value.slice(en);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = s + emoji.length;
        ta.dispatchEvent(new Event('input'));
      }
    });

    document.addEventListener('click', (e) => {
      if (!panel || !btn) return;
      if (!panel.contains(e.target) && e.target !== btn) panel.style.display = 'none';
    });

    window.addEventListener('resize', () => panel && (panel.style.display = 'none'));
    document.getElementById('chat-list')?.addEventListener('scroll', () => panel && (panel.style.display = 'none'));
  })();

  // Autosize textarea
  (function () {
    const ta = document.getElementById('msgBox');
    if (!ta) return;
    function grow() { ta.style.height = '24px'; ta.style.height = Math.min(ta.scrollHeight, 96) + 'px'; }
    ['input', 'change'].forEach(ev => ta.addEventListener(ev, grow));
    grow();
  })();

  // Attachments & send enablement
  (function () {
    const form = document.getElementById('composerForm');
    const all = document.getElementById('fileAll');
    const cam = document.getElementById('fileCamera');
    const chip = document.getElementById('attachChip');
    const nameEl = document.getElementById('attachName');
    const clear = document.getElementById('attachClear');
    const sendBtn = document.getElementById('sendBtn');
    const msgBox = document.getElementById('msgBox');

    function selectedFile() { return all?.files?.[0] || cam?.files?.[0] || null; }
    function hasPayload() { return (msgBox.value || '').trim().length > 0 || !!selectedFile(); }
    function updateSendState() { if (sendBtn) sendBtn.disabled = !hasPayload(); }

    function onChoose(input) {
      const f = input.files && input.files[0];
      if (!f) return;
      nameEl.textContent = f.name;
      chip.classList.add('show');
      updateSendState();
    }

    all?.addEventListener('change', () => onChoose(all));
    cam?.addEventListener('change', () => onChoose(cam));

    clear?.addEventListener('click', () => {
      chip.classList.remove('show');
      all.value = '';
      cam.value = '';
      updateSendState();
    });

    ['input', 'change', 'keyup'].forEach(ev => msgBox?.addEventListener(ev, updateSendState));
    updateSendState();

    form?.addEventListener('submit', (e) => {
      if (!hasPayload()) { e.preventDefault(); return false; }
    });
  })();

  // ---- AJAX submit to avoid full reload ----
  (function () {
    const form = document.getElementById('composerForm');
    const msgBox = document.getElementById('msgBox');
    const fileAll = document.getElementById('fileAll');
    const fileCamera = document.getElementById('fileCamera');
    const chip = document.getElementById('attachChip');
    const sendBtn = document.getElementById('sendBtn');
    const csrf = document.querySelector('input[name="_token"]')?.value || '';

    async function ajaxSubmit(e) {
      e.preventDefault();
      const fd = new FormData();
      fd.append('_token', csrf);
      fd.append('body', msgBox.value || '');

      const picked = fileAll.files[0] || fileCamera.files[0] || null;
      if (picked) fd.append('file', picked, picked.name);

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
          credentials: 'same-origin',
        });

        if (res.status === 422) {
          const data = await res.json().catch(() => ({}));
          alert((data && data.message) || 'Validation error'); return;
        }
        if (!res.ok && res.status !== 302 && res.status !== 204) {
          alert('Upload failed. Please try again.'); return;
        }

        // clear composer
        msgBox.value = '';
        fileAll.value = '';
        fileCamera.value = '';
        chip.classList.remove('show');
        sendBtn.disabled = true;

        // get new messages
        fetchMessages(false);
      } catch (err) {
        console.error(err);
        alert('Network error. Please check your connection.');
      }
    }

    if (form) form.addEventListener('submit', ajaxSubmit);
  })();
</script>

<!-- Polling (10s) + Tabs scroller JS -->
<script>
  // Tabs left/right buttons
  (function () {
    const rail = document.getElementById('chatTabs');
    document.querySelectorAll('.tab-scroll').forEach(btn => {
      btn.addEventListener('click', () => {
        const dir = Number(btn.dataset.dir || 1);
        rail.scrollBy({ left: dir * (rail.clientWidth * 0.8), behavior: 'smooth' });
      });
    });
  })();

  // ---- Polling ----
  const POLL_URL = "{{ route('groups.chat.poll', [$group, $chat]) }}";
  const list = document.getElementById('chat-list');
  const CURRENT_USER_ID = String(list?.dataset?.userId ?? ''); // <-- pull from data attr as string
  let lastId = Number(list?.dataset?.lastId || 0);
  let bootstrapped = lastId > 0;

  function bubble(m) {
    // accept either m.user_id or nested m.user.id from API
    const incomingUserId = m.user_id ?? (m.user && m.user.id) ?? '';
    const me = String(CURRENT_USER_ID) === String(incomingUserId);

    const safeBody = (m.body || '').replaceAll('\n', '<br>');
    const who = (m.user && (m.user.name || m.user)) || m.user || 'User';
    return `
      <li class="mb-3 px-2" data-id="${m.id}">
        <div class="d-flex ${me ? 'justify-content-end' : 'justify-content-start'}">
          <div class="msg ${me ? 'me' : 'other'}">
            <div class="meta">
              <div class="name">${who}</div>
              <div class="time text-muted">${m.created_at ?? ''}</div>
            </div>
            ${m.body ? `<div class="body">${safeBody}</div>` : ''}
            ${m.file_url ? `<div class="file-chip">📎 <a target="_blank" href="${m.file_url}">${m.file_name ?? 'file'}</a></div>` : ''}
          </div>
        </div>
      </li>`;
  }

  function render(items) {
    if (!Array.isArray(items) || items.length === 0) return;
    const frag = document.createElement('div');
    items.forEach(i => {
      if (i.id > lastId) {
        lastId = i.id;
        frag.insertAdjacentHTML('beforeend', bubble(i));
      }
    });
    if (!frag.innerHTML) return;

    if (!bootstrapped) {
      list.innerHTML = frag.innerHTML;
      bootstrapped = true;
    } else {
      list.insertAdjacentHTML('beforeend', frag.innerHTML);
    }
    list.scrollTop = list.scrollHeight;
  }

  async function fetchMessages(initial = false) {
    try {
      const url = new URL(POLL_URL, window.location.origin);
      url.searchParams.set('t', Date.now());
      if (lastId > 0) url.searchParams.set('since_id', String(lastId));
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      });
      const ct = res.headers.get('Content-Type') || '';
      if (!ct.includes('application/json')) return;
      const { data } = await res.json();
      const boot = document.getElementById('bootSkeleton');
      if (boot) boot.remove();
      render(data);
    } catch (_) { /* ignore */ }
  }

  // initial scroll to bottom
  (function () { setTimeout(() => { list.scrollTop = list.scrollHeight; }, 0); })();

  fetchMessages(true);
  setInterval(fetchMessages, 10000);
</script>
@endsection