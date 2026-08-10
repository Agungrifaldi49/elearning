/**
 * Production-Grade SPA Real-Time Chat Engine
 * Clean 1-on-1 Realtime Chat System
 *
 * Features:
 * - Real-Time Instant Message Sending & AJAX Polling
 * - Smart Scroll Lock (User can scroll UP freely without auto-snapping to bottom)
 * - 7-Day Automatic Chat Expiration / Purge for Security
 * - Incremental DOM Diffing Engine (Zero innerHTML destruction)
 * - Left Sidebar Contacts Sync (Last message & unread badge)
 */

const ChatApp = {
    state: {
        currentUserId: 0,
        activeWithId: 0,
        messages: [],
        contacts: [],
        lastMessagesHash: '',
        lastContactsHash: '',
        isUserScrolling: false,
        isInitialLoad: true,
        isMutating: false
    },

    init: function() {
        const chatBox = document.getElementById('chatBox');
        const chatForm = document.getElementById('chatForm');
        const receiverIdInput = document.getElementById('receiverId');

        if (!chatBox || !chatForm || !receiverIdInput) return;

        this.state.activeWithId = parseInt(receiverIdInput.value || 0, 10);
        this.state.currentUserId = parseInt(document.body.getAttribute('data-user-id') || 0, 10);

        this.bindEvents();
        this.initEmojiPicker();
        this.startPolling();
    },

    initEmojiPicker: function() {
        const btn = document.getElementById('emojiPickerBtn');
        const popover = document.getElementById('emojiPickerPopover');
        const closeBtn = document.getElementById('closeEmojiPickerBtn');
        const container = document.getElementById('emojiListContainer');
        const input = document.getElementById('messageInput');

        if (!btn || !popover || !container || !input) return;

        const emojis = [
            '😊','😂','🤣','😍','🥰','😎','😇','🥳','🤯','😱','😴','🤔','🙃','🤐','😷',
            '👍','👎','👏','🙏','✌️','🤝','💪','👌','👋','👊','🖐️','🙌','🤘',
            '❤️','💖','💕','🔥','🎉','🚀','💡','📌','✅','⚡','💯','⭐','🏆','🎖️',
            '🎓','📚','✏️','💬','🔔','📢','✨','🌟','🎯','📝','💻','📱','🏫','👨‍🏫','👩‍🎓'
        ];

        container.innerHTML = emojis.map(e => `
            <button type="button" class="btn btn-light btn-sm p-1 fs-5 border-0 emoji-item" style="width: 38px; height: 38px; line-height: 1;">${e}</button>
        `).join('');

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            popover.classList.toggle('d-none');
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', () => popover.classList.add('d-none'));
        }

        container.querySelectorAll('.emoji-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const emoji = item.textContent.trim();
                const start = input.selectionStart || input.value.length;
                const end = input.selectionEnd || input.value.length;
                const val = input.value;
                input.value = val.substring(0, start) + emoji + val.substring(end);
                input.focus();
                input.selectionStart = input.selectionEnd = start + emoji.length;
            });
        });

        document.addEventListener('click', (e) => {
            if (!popover.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                popover.classList.add('d-none');
            }
        });
    },

    bindEvents: function() {
        const chatBox = document.getElementById('chatBox');
        const chatForm = document.getElementById('chatForm');

        // Track Scroll Intent: If distance from bottom > 80px, user is reading history -> LOCK AUTO SCROLL
        chatBox.addEventListener('scroll', () => {
            const distanceFromBottom = chatBox.scrollHeight - chatBox.clientHeight - chatBox.scrollTop;
            this.state.isUserScrolling = (distanceFromBottom > 80);
        });

        // Send Form Submit (Optimistic UI)
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const messageInput = document.getElementById('messageInput');
            const messageText = messageInput ? messageInput.value.trim() : '';
            const receiverId = this.state.activeWithId;
            const csrfTokenInput = document.querySelector('#chatForm input[name="csrf_token"]');
            const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

            if (!messageText || !receiverId) return;

            messageInput.value = '';
            this.state.isUserScrolling = false; // Reset scroll so user sees their new message
            this.sendOptimisticMessage(messageText, receiverId, csrfToken);
        });
    },

    startPolling: function() {
        this.fetchState(true);
        setInterval(() => this.fetchState(false), 1500);
    },

    normalizeMessagesList: function(rawList) {
        return rawList.map(msg => ({
            id: parseInt(msg.id, 10),
            sender_id: parseInt(msg.sender_id, 10),
            receiver_id: parseInt(msg.receiver_id, 10),
            message: String(msg.message || ''),
            is_deleted_everyone: parseInt(msg.is_deleted_everyone || 0, 10),
            is_edited: parseInt(msg.is_edited || 0, 10),
            created_at: msg.created_at,
            sender_name: String(msg.sender_name || ''),
            sender_avatar: String(msg.sender_avatar || ''),
            is_me: (msg.is_me === true || parseInt(msg.sender_id, 10) === this.state.currentUserId)
        }));
    },

    fetchState: function(force = false) {
        if (!this.state.activeWithId) return;
        if (this.state.isMutating && !force) return;

        fetch(`${BASE_URL}index.php?url=chat/fetch&with=${this.state.activeWithId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (res.current_user_id) {
                        this.state.currentUserId = parseInt(res.current_user_id, 10);
                    }

                    const normalizedMessages = this.normalizeMessagesList(res.data || []);
                    const messagesHash = JSON.stringify(normalizedMessages);
                    const contactsHash = JSON.stringify(res.contacts || []);

                    if (force || contactsHash !== this.state.lastContactsHash) {
                        this.state.contacts = res.contacts || [];
                        this.state.lastContactsHash = contactsHash;
                        this.renderContacts();
                    }

                    if (force || messagesHash !== this.state.lastMessagesHash) {
                        this.state.messages = normalizedMessages;
                        this.state.lastMessagesHash = messagesHash;
                        this.renderMessagesIncremental();
                    }
                }
            })
            .catch(err => console.error('Chat state sync error:', err));
    },

    /**
     * Incremental DOM Diffing Engine:
     * Never destroys chatBox.innerHTML. Patches existing DOM nodes or appends new ones smoothly.
     */
    renderMessagesIncremental: function() {
        const chatBox = document.getElementById('chatBox');
        if (!chatBox) return;

        const existingMap = new Map();
        chatBox.querySelectorAll('[data-msg-id]').forEach(node => {
            const id = parseInt(node.getAttribute('data-msg-id'), 10);
            if (!isNaN(id)) existingMap.set(id, node);
        });

        const activeMsgIds = new Set();

        this.state.messages.forEach(msg => {
            const msgId = parseInt(msg.id, 10);
            activeMsgIds.add(msgId);

            const isMe = msg.is_me;
            const bubbleClass = isMe ? 'sent' : 'received';
            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const isDeletedEveryone = (msg.is_deleted_everyone === 1);

            let existingNode = existingMap.get(msgId);

            if (existingNode) {
                if (isDeletedEveryone && !existingNode.classList.contains('is-deleted-node')) {
                    existingNode.className = `chat-bubble ${bubbleClass} bg-light text-muted border border-dashed shadow-none is-deleted-node`;
                    existingNode.innerHTML = `
                        <div class="fst-italic small text-muted"><i class="bi bi-slash-circle me-1 text-danger"></i> Pesan ini telah dihapus</div>
                        <div style="font-size: 0.68rem; opacity: 0.6; text-align: right; margin-top: 4px;">${time}</div>
                    `;
                } else if (!isDeletedEveryone) {
                    const msgContent = existingNode.querySelector('.msg-content');
                    if (msgContent && msgContent.textContent !== msg.message) {
                        msgContent.textContent = msg.message;
                    }
                }
            } else {
                const newNode = document.createElement('div');

                if (isDeletedEveryone) {
                    newNode.className = `chat-bubble ${bubbleClass} bg-light text-muted border border-dashed shadow-none is-deleted-node`;
                    newNode.setAttribute('data-msg-id', msgId);
                    newNode.innerHTML = `
                        <div class="fst-italic small text-muted"><i class="bi bi-slash-circle me-1 text-danger"></i> Pesan ini telah dihapus</div>
                        <div style="font-size: 0.68rem; opacity: 0.6; text-align: right; margin-top: 4px;">${time}</div>
                    `;
                } else {
                    newNode.className = `chat-bubble ${bubbleClass}`;
                    newNode.setAttribute('data-msg-id', msgId);
                    newNode.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="msg-content flex-grow-1">${this.escapeHtml(msg.message)}</div>
                            <div class="dropdown ms-1 flex-shrink-0">
                                <button class="btn btn-link btn-sm p-0 text-reset opacity-50 dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi Pesan">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 0.82rem;">
                                    <li>
                                        <a class="dropdown-item py-1 text-danger" href="javascript:void(0)" onclick="ChatApp.deleteMessageForMe(${msgId})">
                                            <i class="bi bi-trash me-2"></i> Hapus untuk Saya
                                        </a>
                                    </li>
                                    ${isMe ? `
                                        <li>
                                            <a class="dropdown-item py-1 text-danger fw-semibold" href="javascript:void(0)" onclick="ChatApp.deleteMessageForEveryone(${msgId})">
                                                <i class="bi bi-slash-circle me-2"></i> Hapus untuk Semua Orang
                                            </a>
                                        </li>
                                    ` : ''}
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end align-items-center mt-1 opacity-75" style="font-size: 0.68rem;">
                            <span>${time}</span>
                        </div>
                    `;
                }
                chatBox.appendChild(newNode);
            }
        });

        // Cleanly remove elements that were deleted
        existingMap.forEach((node, id) => {
            const numericId = parseInt(id, 10);
            if (!activeMsgIds.has(numericId)) {
                node.remove();
            }
        });

        // CRITICAL SCROLL POSITION FIX:
        // Only scroll to bottom on initial load or if user is NOT scrolling reading older history
        if (this.state.isInitialLoad || !this.state.isUserScrolling) {
            chatBox.scrollTop = chatBox.scrollHeight;
            this.state.isInitialLoad = false;
        }
    },

    renderContacts: function() {
        const container = document.getElementById('contactListContainer');
        if (!container || !this.state.contacts) return;

        this.state.contacts.forEach(c => {
            let itemElem = container.querySelector(`.contact-item[data-contact-id="${c.id}"]`);
            if (!itemElem) {
                itemElem = container.querySelector(`.contact-item[href$="with=${c.id}"]`);
            }
            if (itemElem) {
                const nameElem = itemElem.querySelector('.fw-bold.mb-0.text-truncate');
                if (nameElem && c.full_name && nameElem.textContent !== c.full_name) {
                    nameElem.textContent = c.full_name;
                }

                const avatarBox = itemElem.querySelector('.position-relative.flex-shrink-0');
                if (avatarBox && c.avatar_url) {
                    let imgElem = avatarBox.querySelector('img');
                    if (imgElem) {
                        if (imgElem.src !== c.avatar_url) {
                            imgElem.src = c.avatar_url;
                        }
                    } else {
                        const initCircle = avatarBox.querySelector('.rounded-circle.bg-primary');
                        if (initCircle) {
                            initCircle.remove();
                            const newImg = document.createElement('img');
                            newImg.src = c.avatar_url;
                            newImg.alt = 'Avatar';
                            newImg.className = 'rounded-circle object-fit-cover shadow-sm';
                            newImg.style.width = '42px';
                            newImg.style.height = '42px';
                            avatarBox.prepend(newImg);
                        }
                    }
                }

                const msgPreviewElem = itemElem.querySelector('.text-muted.text-truncate');
                if (msgPreviewElem && c.last_message) {
                    msgPreviewElem.textContent = c.last_message;
                }
                const unreadBadgeElem = itemElem.querySelector('.badge.bg-danger');
                if (c.unread_count > 0) {
                    if (unreadBadgeElem) {
                        unreadBadgeElem.textContent = c.unread_count;
                        unreadBadgeElem.style.display = '';
                    }
                } else if (unreadBadgeElem) {
                    unreadBadgeElem.style.display = 'none';
                }

                const statusDot = itemElem.querySelector('.status-dot');
                if (statusDot) {
                    const isOnline = (parseInt(c.is_online, 10) === 1);
                    statusDot.className = `position-absolute bottom-0 end-0 p-1 border border-2 border-white rounded-circle status-dot ${isOnline ? 'bg-success' : 'bg-danger'}`;
                }

                const statusBadge = itemElem.querySelector('.status-badge');
                if (statusBadge) {
                    const isOnline = (parseInt(c.is_online, 10) === 1);
                    if (isOnline) {
                        statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-0 status-badge';
                        statusBadge.innerHTML = '<i class="bi bi-circle-fill text-success me-1" style="font-size:0.45rem;"></i>Online';
                    } else {
                        statusBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0 status-badge';
                        statusBadge.innerHTML = '<i class="bi bi-circle-fill text-danger me-1" style="font-size:0.45rem;"></i>Off';
                    }
                }
            }
        });
    },

    // --- REALTIME MESSAGE SENDING ---
    sendOptimisticMessage: function(messageText, receiverId, csrfToken) {
        this.state.isMutating = true; // Lock polling

        const tempId = Date.now();
        const now = new Date().toISOString();
        const cleanText = this.filterBadWords(messageText);

        const optimisticMsg = {
            id: tempId,
            sender_id: this.state.currentUserId,
            receiver_id: receiverId,
            message: cleanText,
            created_at: now,
            sender_name: '',
            sender_avatar: '',
            is_me: true
        };

        this.state.messages.push(optimisticMsg);
        this.state.isUserScrolling = false; // Auto scroll down for user's own sent message
        this.renderMessagesIncremental();

        const formData = new FormData();
        formData.append('receiver_id', receiverId);
        formData.append('message', messageText);
        formData.append('csrf_token', csrfToken);

        fetch(`${BASE_URL}index.php?url=chat/send`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(() => {
            this.state.isMutating = false; // Unlock polling
            this.fetchState(true);
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchState(true);
        });
    },

    filterBadWords: function(text) {
        if (!text) return '';
        const badWords = [
            'anjing', 'babi', 'bangsat', 'kontol', 'memek', 'pantek', 'pepek', 'itil', 'peler', 'ngentot',
            'jembut', 'goblok', 'tolol', 'kampret', 'bajingan', 'bego', 'jancok', 'jancuk', 'modar', 'perek',
            'lonte', 'silit', 'sange', 'tetek', 'toket', 'ngaceng', 'crot', 'bokep', 'porno', 'banci', 'bencong',
            'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'cunt', 'dick', 'pussy', 'cock', 'motherfucker',
            'porn', 'nude', 'naked', 'suicide', 'bacok', 'gorok', 'membunuh'
        ];

        let filtered = text;
        badWords.forEach(word => {
            if (word.length < 2) return;
            const regex = new RegExp('\\b' + word.split('').join('[\\s\\._\\-\\*]*') + '\\b', 'gi');
            filtered = filtered.replace(regex, function(match) {
                if (match.length <= 2) return '*'.repeat(match.length);
                return match[0] + '*'.repeat(match.length - 2) + match[match.length - 1];
            });
        });
        return filtered;
    },

    deleteMessageForMe: function(chatId) {
        if (!confirm('Hapus pesan ini dari tampilan Anda?')) return;
        this.state.isMutating = true;

        const csrfTokenInput = document.querySelector('#chatForm input[name="csrf_token"]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

        const formData = new FormData();
        formData.append('chat_id', chatId);
        formData.append('csrf_token', csrfToken);

        fetch(`${BASE_URL}index.php?url=chat/deleteForMe`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            this.state.isMutating = false;
            if (res.status === 'success') {
                const node = document.querySelector(`.chat-bubble[data-msg-id="${chatId}"]`);
                if (node) {
                    node.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    node.style.opacity = '0';
                    node.style.transform = 'scale(0.9)';
                    setTimeout(() => node.remove(), 300);
                }
                this.fetchState(true);
            } else {
                alert(res.message || 'Gagal menghapus pesan');
            }
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchState(true);
        });
    },

    deleteMessageForEveryone: function(chatId) {
        if (!confirm('Tarik / hapus pesan ini untuk semua orang?')) return;
        this.state.isMutating = true;

        const csrfTokenInput = document.querySelector('#chatForm input[name="csrf_token"]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

        const formData = new FormData();
        formData.append('chat_id', chatId);
        formData.append('csrf_token', csrfToken);

        fetch(`${BASE_URL}index.php?url=chat/deleteForEveryone`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            this.state.isMutating = false;
            if (res.status === 'success') {
                this.fetchState(true);
            } else {
                alert(res.message || 'Gagal menghapus pesan untuk semua orang');
            }
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchState(true);
        });
    },

    clearConversation: function() {
        if (!this.state.activeWithId) return;
        if (!confirm('Apakah Anda yakin ingin menghapus seluruh pesan obrolan dengan kontak ini?')) return;

        this.state.isMutating = true;
        const csrfTokenInput = document.querySelector('#chatForm input[name="csrf_token"]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

        const formData = new FormData();
        formData.append('with', this.state.activeWithId);
        formData.append('csrf_token', csrfToken);

        fetch(`${BASE_URL}index.php?url=chat/clearHistory`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            this.state.isMutating = false;
            if (res.status === 'success') {
                const chatBox = document.getElementById('chatBox');
                if (chatBox) chatBox.innerHTML = '';
                this.state.messages = [];
                this.fetchState(true);
            } else {
                alert(res.message || 'Gagal membersihkan obrolan');
            }
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchState(true);
        });
    },

    escapeHtml: function(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ChatApp.init();
});
