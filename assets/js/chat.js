/* ================================================================
   MUJSTAYS — Chat Polling JavaScript
================================================================ */

(function () {
  'use strict';

  const chatMessages = document.getElementById('chat-messages');
  const chatInput    = document.getElementById('chat-input');
  const chatForm     = document.getElementById('chat-form');
  const receiverId   = document.querySelector('[data-receiver-id]')?.dataset.receiverId;
  const pgId         = document.querySelector('[data-pg-id]')?.dataset.pgId;

  if (!chatMessages) return;

  let lastMessageId = parseInt(chatMessages.dataset.lastId || 0);
  let currentReceiverId = receiverId;
  let currentPgId = pgId;
  let isPolling = false;

  // Scroll to the bottom of chat
  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Format time
  function formatTime(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
  }

  // Render a new message bubble
  function renderMessage(msg, isSent) {
    const div = document.createElement('div');
    div.className = 'message-wrap';
    const cls = isSent ? 'sent' : 'received';
    div.innerHTML = `
      <div class="message-bubble ${cls}">
        <div>${escapeHtml(msg.message_text)}</div>
        <div class="message-time">${formatTime(msg.sent_at)}</div>
      </div>`;
    chatMessages.appendChild(div);
    lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }

  // Poll for new messages every 5 seconds
  function pollMessages() {
    if (isPolling || !currentReceiverId) return;
    isPolling = true;

    fetch(`${BASE_URL}/api/messages-poll.php?receiver_id=${currentReceiverId}&after=${lastMessageId}&pg_id=${currentPgId || ''}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => r.json())
      .then(data => {
        if (data.messages && data.messages.length > 0) {
          data.messages.forEach(msg => renderMessage(msg, false));
          scrollToBottom();
          // Update unread badge
          const badge = document.querySelector('.bell-badge');
          if (badge && data.unread_count > 0) badge.textContent = data.unread_count;
        }
      })
      .catch(err => console.warn('Chat poll error:', err))
      .finally(() => { isPolling = false; });
  }

  // Send message
  if (chatForm) {
    chatForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!currentReceiverId) return;
      const text = chatInput?.value.trim();
      if (!text) return;

      const sendBtn = chatForm.querySelector('button[type="submit"]');
      sendBtn.disabled = true;

      const formData = new FormData();
      formData.append('receiver_id', currentReceiverId);
      formData.append('message_text', text);
      formData.append('pg_id', currentPgId || '');
      formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

      fetch(`${BASE_URL}/api/send-message.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            renderMessage(data.message, true);
            chatInput.value = '';
            scrollToBottom();
          } else {
            if (window.showToast) showToast(data.error || 'Failed to send message', 'error');
            else alert(data.error || 'Failed to send message');
          }
        })
        .catch(() => {
          if (window.showToast) showToast('Connection error', 'error');
          else alert('Connection error');
        })
        .finally(() => { sendBtn.disabled = false; chatInput.focus(); });
    });
  }

  // Conversation list switching
  function switchChat(userId, userName, userPg) {
    currentReceiverId = userId;
    currentPgId = userPg;

    // Update chat window state
    const windowPlaceholder = document.querySelector('.chat-window .empty-state');
    if (windowPlaceholder) {
       // If we were on placeholder, we need to reload or build the UI.
       // Easiest is to reload if it's the first time, but we already have switch logic below.
       window.location.href = `${window.location.pathname}?with=${userId}${userPg ? '&pg_id=' + userPg : ''}`;
       return;
    }

    // Update chat header
    const nameEl = document.querySelector('.chat-header .chat-header-name');
    if (nameEl) nameEl.textContent = userName;
    
    // Update PG display in header if exists
    const pgEl = document.querySelector('.chat-header [style*="font-size: 12px"]');
    if (pgEl) pgEl.style.display = 'none'; // Simple hide for now if switching

    // Load conversation via AJAX
    chatMessages.innerHTML = '<div style="text-align:center;padding:40px;color:#94A3B8"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    fetch(`${BASE_URL}/api/get-conversation.php?with=${userId}&pg_id=${userPg || ''}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => r.json())
      .then(data => {
        chatMessages.innerHTML = '';
        chatMessages.dataset.receiverId = userId;
        chatMessages.dataset.pgId = userPg || '';
        
        if (chatInput) {
          chatInput.dataset.receiverId = userId;
          chatInput.dataset.pgId = userPg || '';
        }

        // Update URL without reload
        const newUrl = `${window.location.pathname}?with=${userId}${userPg ? '&pg_id=' + userPg : ''}`;
        window.history.pushState({ path: newUrl }, '', newUrl);

        if (data.messages) {
          data.messages.forEach(msg => renderMessage(msg, msg.is_sent));
          lastMessageId = data.last_id || 0;
        }
        scrollToBottom();
        
        // Restart polling context
        lastMessageId = data.last_id || 0;
      });

    // Mark as read
    fetch(`${BASE_URL}/api/mark-read.php`, {
      method: 'POST',
      body: new URLSearchParams({ sender_id: userId }),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
  }

  document.querySelectorAll('.chat-item[data-user-id]').forEach(item => {
    item.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('active'));
      this.classList.add('active');
      switchChat(this.dataset.userId, this.dataset.userName, this.dataset.pgId);
    });
  });

  // Owner Search Logic
  const ownerSearchInput = document.getElementById('owner-search');
  const searchResultsList = document.getElementById('search-results-list');

  if (ownerSearchInput) {
    ownerSearchInput.addEventListener('input', function() {
      const q = this.value.trim();
      if (q.length < 2) {
        searchResultsList.style.display = 'none';
        return;
      }

      fetch(`${BASE_URL}/api/search-owners.php?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
        if (data.success && data.owners.length > 0) {
          searchResultsList.innerHTML = '';
          data.owners.forEach(owner => {
            const div = document.createElement('div');
            div.style.padding = '10px 14px';
            div.style.cursor = 'pointer';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.gap = '10px';
            div.style.borderBottom = '1px solid var(--border)';
            div.onmouseover = () => div.style.background = 'var(--bg)';
            div.onmouseout = () => div.style.background = 'none';
            div.innerHTML = `
              <div class="chat-avatar" style="width:30px;height:30px;font-size:12px;background:var(--accent);color:#fff">
                ${owner.name.charAt(0).toUpperCase()}
              </div>
              <div style="font-size:13px;font-weight:600">${owner.name}</div>
            `;
            div.onclick = () => {
              window.location.href = `${BASE_URL}/user/chat.php?with=${owner.id}`;
            };
            searchResultsList.appendChild(div);
          });
          searchResultsList.style.display = 'block';
        } else {
          searchResultsList.style.display = 'none';
        }
      });
    });

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
      if (!ownerSearchInput.contains(e.target) && !searchResultsList.contains(e.target)) {
        searchResultsList.style.display = 'none';
      }
    });
  }

  // Initial scroll
  scrollToBottom();

  // Start polling
  setInterval(pollMessages, 3000);
  if (currentReceiverId) pollMessages();

})();
