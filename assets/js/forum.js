/**
 * Forum Realtime Polling & Multi-Emoji Reaction Engine (assets/js/forum.js)
 * Zero-reload AJAX Topic Creation, Comment Replies, Topic Deletion, Multi-Emoji Reactions, and Realtime Polling
 */
const ForumApp = {
    state: {
        isPolling: false,
        pollInterval: null,
        activeTopicId: 0,
        isMutating: false
    },

    init: function() {
        const topicsContainer = document.getElementById('forumTopicsContainer');
        const commentsContainer = document.getElementById('commentsListContainer');
        const modalForm = document.querySelector('#modalAddTopic form');
        const commentForm = document.querySelector('#commentReplyForm');

        // Setup AJAX Submit for Add Topic Modal
        if (modalForm) {
            modalForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitAddTopic(modalForm);
            });
        }

        // Setup AJAX Submit for Comment Reply Form
        if (commentForm) {
            const topicIdInput = document.getElementById('activeTopicId');
            if (topicIdInput) {
                this.state.activeTopicId = parseInt(topicIdInput.value, 10);
            }
            commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment(commentForm);
            });
        }

        // Start Realtime Polling
        if (topicsContainer) {
            this.startTopicPolling();
        } else if (commentsContainer && this.state.activeTopicId > 0) {
            this.startCommentPolling();
        }
    },

    // --- MULTI-EMOJI REACTION ENGINE (AJAX) ---
    toggleReaction: function(forumId, type) {
        const csrfTokenInput = document.querySelector('input[name=csrf_token]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

        const formData = new FormData();
        formData.append('forum_id', forumId);
        formData.append('type', type);
        formData.append('csrf_token', csrfToken);
        formData.append('is_ajax', '1');

        fetch(`${BASE_URL}index.php?url=forum/react`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.summary) {
                this.updateReactionUI(res.forum_id, res.summary);
            }
        })
        .catch(() => {});
    },

    updateReactionUI: function(forumId, summary) {
        const container = document.querySelector(`.reaction-bar[data-forum-id="${forumId}"]`);
        if (!container) return;

        const reactionMap = [
            { type: 'love', emoji: '❤️', label: 'Love' },
            { type: 'like', emoji: '👍', label: 'Jempol' },
            { type: 'laugh', emoji: '😂', label: 'Ketawa' },
            { type: 'sad', emoji: '😢', label: 'Sedih' },
            { type: 'wow', emoji: '😮', label: 'Kaget' },
            { type: 'fire', emoji: '🔥', label: 'Menyala' }
        ];

        const html = reactionMap.map(r => {
            const count = summary[r.type] || 0;
            const isActive = (summary.my_reaction === r.type);
            const activeClass = isActive ? 'btn-primary-subtle border-primary text-primary fw-bold shadow-sm' : 'btn-light border-0 text-secondary';
            
            return `
                <button type="button" class="btn btn-sm ${activeClass} rounded-pill px-2 py-1 me-1 mb-1 btn-emoji-react" 
                        onclick="ForumApp.toggleReaction(${forumId}, '${r.type}')" 
                        title="${r.label}">
                    <span class="fs-6 me-1">${r.emoji}</span>
                    <span class="small">${count > 0 ? count : ''}</span>
                </button>
            `;
        }).join('');

        container.innerHTML = html;
    },

    // --- TOPIC CREATION (AJAX) ---
    submitAddTopic: function(form) {
        this.state.isMutating = true;
        const formData = new FormData(form);
        formData.append('is_ajax', '1');

        fetch(`${BASE_URL}index.php?url=forum`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            this.state.isMutating = false;
            if (res.status === 'success') {
                form.reset();
                const modalElem = document.getElementById('modalAddTopic');
                if (modalElem && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const inst = bootstrap.Modal.getInstance(modalElem);
                    if (inst) inst.hide();
                }
                this.fetchTopics();
            } else {
                alert(res.message || 'Gagal membuat topik diskusi');
            }
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchTopics();
        });
    },

    // --- COMMENT REPLY (AJAX) ---
    submitComment: function(form) {
        if (!this.state.activeTopicId) return;
        this.state.isMutating = true;

        const formData = new FormData(form);
        formData.append('is_ajax', '1');

        fetch(`${BASE_URL}index.php?url=forum/detail&id=${this.state.activeTopicId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            this.state.isMutating = false;
            if (res.status === 'success') {
                const textarea = form.querySelector('textarea[name="komentar"]');
                if (textarea) textarea.value = '';
                this.fetchComments();
            } else {
                alert(res.message || 'Gagal menambahkan komentar');
            }
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchComments();
        });
    },

    // --- TOPIC DELETION (AJAX) ---
    deleteTopic: function(topicId, csrfToken) {
        if (!confirm('Apakah Anda yakin ingin menghapus topik diskusi ini? Seluruh balasan komentar akan ikut terhapus.')) return;

        this.state.isMutating = true;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('topic_id', topicId);
        formData.append('csrf_token', csrfToken);
        formData.append('is_ajax', '1');

        fetch(`${BASE_URL}index.php?url=forum`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            this.state.isMutating = false;
            if (res.status === 'success') {
                if (window.location.href.includes('url=forum/detail')) {
                    window.location.href = `${BASE_URL}index.php?url=forum`;
                } else {
                    this.fetchTopics();
                }
            } else {
                alert(res.message || 'Gagal menghapus topik');
            }
        })
        .catch(() => {
            this.state.isMutating = false;
            this.fetchTopics();
        });
    },

    // --- REALTIME TOPIC POLLING ---
    startTopicPolling: function() {
        if (this.state.isPolling) return;
        this.state.isPolling = true;

        this.state.pollInterval = setInterval(() => {
            if (!this.state.isMutating) {
                this.fetchTopics();
            }
        }, 3000);
    },

    fetchTopics: function() {
        fetch(`${BASE_URL}index.php?url=forum/fetchUpdates`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.topics) {
                this.renderTopics(res.topics);
            }
        })
        .catch(() => {});
    },

    renderTopics: function(topics) {
        const container = document.getElementById('forumTopicsContainer');
        if (!container) return;

        if (!topics || topics.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-chat-square-text display-4 opacity-50 mb-2"></i>
                    <p class="mb-0">Belum ada topik diskusi yang tersedia.</p>
                </div>
            `;
            return;
        }

        const html = topics.map(t => {
            const isPrivate = (t.visibility === 'private');
            const initial = t.full_name ? t.full_name.charAt(0).toUpperCase() : 'U';
            const reactions = t.reactions || { love:0, like:0, laugh:0, sad:0, wow:0, fire:0, my_reaction: null };

            const reactionMap = [
                { type: 'love', emoji: '❤️', label: 'Love' },
                { type: 'like', emoji: '👍', label: 'Jempol' },
                { type: 'laugh', emoji: '😂', label: 'Ketawa' },
                { type: 'sad', emoji: '😢', label: 'Sedih' },
                { type: 'wow', emoji: '😮', label: 'Kaget' },
                { type: 'fire', emoji: '🔥', label: 'Menyala' }
            ];

            const reactionButtons = reactionMap.map(r => {
                const count = reactions[r.type] || 0;
                const isActive = (reactions.my_reaction === r.type);
                const activeClass = isActive ? 'btn-primary-subtle border-primary text-primary fw-bold shadow-sm' : 'btn-light border-0 text-secondary';

                return `
                    <button type="button" class="btn btn-sm ${activeClass} rounded-pill px-2 py-1 me-1 mb-1 btn-emoji-react" 
                            onclick="ForumApp.toggleReaction(${t.id}, '${r.type}')" 
                            title="${r.label}">
                        <span class="fs-6 me-1">${r.emoji}</span>
                        <span class="small">${count > 0 ? count : ''}</span>
                    </button>
                `;
            }).join('');

            return `
                <div class="col-12 topic-card-item" data-topic-id="${t.id}">
                    <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px;">
                                    ${initial}
                                </div>
                                <div>
                                    <div class="fw-bold mb-0 text-dark">
                                        ${t.full_name} 
                                        <span class="badge bg-secondary opacity-75 ms-1">${t.role_name}</span>
                                    </div>
                                    <small class="text-muted">
                                        ${t.created_at} 
                                        ${t.nama_mapel ? '| <i class="bi bi-journal-bookmark text-primary me-1"></i>' + t.nama_mapel : ''}
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                ${isPrivate ? `
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-lock-fill text-warning me-1"></i> Privat
                                        ${t.target_role ? `(${t.target_role})` : ''}
                                        ${t.target_nama_kelas ? `- ${t.target_nama_kelas}` : ''}
                                    </span>
                                ` : `
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-globe me-1"></i> Publik
                                    </span>
                                `}

                                ${t.can_delete ? `
                                    <button class="btn btn-outline-danger btn-sm rounded-circle p-2" title="Hapus Topik Diskusi" onclick="ForumApp.deleteTopic(${t.id}, '${document.querySelector('input[name=csrf_token]') ? document.querySelector('input[name=csrf_token]').value : ''}')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <h5 class="fw-bold mb-2">
                            <a href="${BASE_URL}index.php?url=forum/detail&id=${t.id}" class="text-decoration-none text-dark hover-primary">
                                ${t.judul}
                            </a>
                        </h5>
                        <p class="text-muted mb-3">${t.konten_preview}...</p>

                        <!-- Multi-Emoji Reaction Bar -->
                        <div class="mb-3 pt-2 border-top">
                            <div class="reaction-bar d-flex flex-wrap align-items-center" data-forum-id="${t.id}">
                                ${reactionButtons}
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <a href="${BASE_URL}index.php?url=forum/detail&id=${t.id}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-chat-text me-1"></i> ${t.total_replies} Balasan Diskusi
                            </a>
                            <span class="small text-muted"><i class="bi bi-clock me-1"></i> Diposting ${t.posted_time} WIB</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    },

    // --- REALTIME COMMENT POLLING ---
    startCommentPolling: function() {
        if (this.state.isPolling || !this.state.activeTopicId) return;
        this.state.isPolling = true;

        this.state.pollInterval = setInterval(() => {
            if (!this.state.isMutating) {
                this.fetchComments();
            }
        }, 2500);
    },

    fetchComments: function() {
        if (!this.state.activeTopicId) return;
        fetch(`${BASE_URL}index.php?url=forum/fetchComments&id=${this.state.activeTopicId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.comments) {
                this.renderComments(res.comments);
            }
        })
        .catch(() => {});
    },

    renderComments: function(comments) {
        const container = document.getElementById('commentsListContainer');
        const badgeElem = document.getElementById('commentCountBadge');
        if (!container) return;

        if (badgeElem) {
            badgeElem.textContent = comments.length;
        }

        if (!comments || comments.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-muted small">Belum ada tanggapan balasan pada topik ini.</div>';
            return;
        }

        const html = comments.map(c => `
            <div class="p-3 bg-light rounded-4 border-start border-3 border-primary shadow-sm" data-comment-id="${c.id}">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="fw-bold small text-dark">${c.full_name}</span>
                    <span class="badge bg-primary" style="font-size:0.65rem;">${c.role_name}</span>
                    <small class="text-muted ms-auto">${c.created_at}</small>
                </div>
                <p class="mb-0 text-muted small" style="white-space: pre-line;">${c.komentar}</p>
            </div>
        `).join('');

        container.innerHTML = html;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ForumApp.init();
});
