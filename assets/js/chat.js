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
        this.startPolling();
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
        setInterval(() => this.fetchState(false), 2500);
    },

    normalizeMessagesList: function(rawList) {
        return rawList.map(msg => ({
            id: parseInt(msg.id, 10),
            sender_id: parseInt(msg.sender_id, 10),
            receiver_id: parseInt(msg.receiver_id, 10),
            message: String(msg.message || ''),
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

            let existingNode = existingMap.get(msgId);

            if (existingNode) {
                const msgContent = existingNode.querySelector('.msg-content');
                if (msgContent && msgContent.textContent !== msg.message) {
                    msgContent.textContent = msg.message;
                }
            } else {
                const newNode = document.createElement('div');
                newNode.className = `chat-bubble ${bubbleClass}`;
                newNode.setAttribute('data-msg-id', msgId);
                newNode.innerHTML = `
                    <div class="msg-content">${this.escapeHtml(msg.message)}</div>
                    <div class="d-flex justify-content-end align-items-center mt-1 opacity-75" style="font-size: 0.68rem;">
                        <span>${time}</span>
                    </div>
                `;
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
            const itemElem = container.querySelector(`.contact-item[data-contact-id="${c.id}"]`);
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
            }
        });
    },

    // --- REALTIME MESSAGE SENDING ---
    sendOptimisticMessage: function(messageText, receiverId, csrfToken) {
        this.state.isMutating = true; // Lock polling

        const tempId = Date.now();
        const now = new Date().toISOString();

        const optimisticMsg = {
            id: tempId,
            sender_id: this.state.currentUserId,
            receiver_id: receiverId,
            message: messageText,
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
