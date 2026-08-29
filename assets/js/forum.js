/**
 * Forum Realtime Polling & Multi-Emoji Reaction Engine (assets/js/forum.js)
 * Zero-reload AJAX Topic Creation, Comment Replies, Topic Deletion, Multi-Emoji Reactions, and Realtime Polling
 */
const ForumApp = {
    state: {
        isPolling: false,
        pollInterval: null,
        activeTopicId: 0,
        isMutating: false,
        lastTopicsSig: '',
        lastCommentsSig: ''
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
                this.state.lastTopicsSig = '';
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
                const imgInput = form.querySelector('input[type="file"]');
                if (imgInput) imgInput.value = '';
                const imgContainer = document.getElementById('replyImageContainer');
                if (imgContainer) imgContainer.classList.add('d-none');
                this.state.lastCommentsSig = '';
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
                    this.state.lastTopicsSig = '';
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
        }, 4000);
    },

    fetchTopics: function() {
        fetch(`${BASE_URL}index.php?url=forum/fetchUpdates`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.topics) {
                const sig = JSON.stringify(res.topics.map(t => ({ id: t.id, total_replies: t.total_replies, created_at: t.created_at, gambar: t.gambar })));
                if (sig !== this.state.lastTopicsSig) {
                    this.state.lastTopicsSig = sig;
                    this.renderTopics(res.topics);
                }
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
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-chat-square-quote-fill display-5"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Belum Ada Topik Diskusi</h5>
                    <p class="small mb-3">Jadilah yang pertama untuk memulai pertanyaan atau bahan diskusi baru!</p>
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalAddTopic">
                        <i class="bi bi-plus-circle me-1"></i> Mulai Diskusi Pertama
                    </button>
                </div>
            `;
            return;
        }

        const html = topics.map(t => {
            const isPrivate = (t.visibility === 'private');
            const initial = t.full_name ? t.full_name.charAt(0).toUpperCase() : 'U';
            const reactions = t.reactions || { love:0, like:0, laugh:0, sad:0, wow:0, fire:0, my_reaction: null };

            const roleLower = (t.role_name || '').toLowerCase();
            let ringClass = 'avatar-ring-siswa';
            if (roleLower.includes('admin')) ringClass = 'avatar-ring-admin';
            else if (roleLower.includes('guru')) ringClass = 'avatar-ring-guru';

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
                    <button type="button" class="btn btn-sm ${activeClass} rounded-pill px-3 py-1 me-2 mb-2 btn-emoji-react" 
                            onclick="ForumApp.toggleReaction(${t.id}, '${r.type}')" 
                            title="${r.label}">
                        <span class="fs-6 me-1">${r.emoji}</span>
                        <span class="small fw-semibold">${count > 0 ? count : ''}</span>
                    </button>
                `;
            }).join('');

            const primaryUrl = t.gambar_url || (BASE_URL + 'assets/uploads/forum/' + t.gambar);
            const fallbackUrl = BASE_URL + 'assets/uploads/tugas/' + t.gambar;

            const imgHtml = (t.gambar) ? `
                <div class="mb-3">
                    <div class="forum-image-preview-wrapper shadow-sm" style="display: inline-block; max-width: 440px; max-height: 260px; border-radius: 16px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openLightboxModal('${primaryUrl}', '${(t.judul || '').replace(/'/g, "\\'")}')">
                        <img src="${primaryUrl}" onerror="this.onerror=null; this.src='${fallbackUrl}';" alt="Lampiran Gambar Forum" style="max-width: 100%; max-height: 240px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; border-radius: 14px;">
                        <div class="forum-image-overlay">
                            <span><i class="bi bi-zoom-in me-1"></i> Perbesar Gambar</span>
                        </div>
                    </div>
                </div>
            ` : '';

            return `
                <div class="topic-card-item" data-topic-id="${t.id}" data-visibility="${t.visibility || 'public'}">
                    <div class="forum-topic-card p-4 rounded-4 shadow-sm bg-white border">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-ring ${ringClass}" style="padding:2px; flex-shrink: 0;">
                                    <div class="avatar-inner" style="width:42px; height:42px; font-size:1rem; font-weight:bold;">
                                        ${initial}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2 flex-wrap" style="font-size:0.95rem;">
                                        <span>${t.full_name}</span>
                                        <span class="badge bg-indigo-subtle text-indigo rounded-pill px-2 py-1" style="font-size:0.7rem; background:#e0e7ff; color:#3730a3;">
                                            ${t.role_name}
                                        </span>
                                    </div>
                                    <small class="text-muted" style="font-size:0.78rem;">
                                        <i class="bi bi-clock me-1"></i>${t.created_at}
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                ${isPrivate ? `
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size:0.75rem;">
                                        <i class="bi bi-lock-fill text-warning me-1"></i> Privat
                                    </span>
                                ` : `
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size:0.75rem;">
                                        <i class="bi bi-globe me-1"></i> Publik
                                    </span>
                                `}

                                ${t.can_delete ? `
                                    <button class="btn btn-outline-danger btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" title="Hapus Topik Diskusi" onclick="ForumApp.deleteTopic(${t.id}, '${document.querySelector('input[name=csrf_token]') ? document.querySelector('input[name=csrf_token]').value : ''}')" style="width:30px; height:30px;">
                                        <i class="bi bi-trash3" style="font-size:0.8rem;"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <h5 class="fw-bold mb-2">
                            <a href="${BASE_URL}index.php?url=forum/detail&id=${t.id}" class="text-decoration-none text-dark hover-primary" style="line-height: 1.4;">
                                ${t.judul}
                            </a>
                        </h5>
                        <p class="text-secondary mb-3" style="font-size:0.92rem; line-height: 1.6; color: #475569;">${t.konten_preview}...</p>

                        ${imgHtml}

                        <div class="pt-3 mb-3 border-top">
                            <div class="reaction-bar d-flex flex-wrap align-items-center" data-forum-id="${t.id}">
                                ${reactionButtons}
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                            <a href="${BASE_URL}index.php?url=forum/detail&id=${t.id}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="font-size:0.88rem;">
                                <i class="bi bi-chat-text-fill"></i> ${t.total_replies} Balasan Diskusi
                            </a>
                            ${t.nama_mapel ? `
                                <span class="badge bg-light text-primary border rounded-pill px-3 py-2 fw-semibold" style="font-size:0.8rem;">
                                    <i class="bi bi-journal-text me-1"></i>${t.nama_mapel}
                                </span>
                            ` : ''}
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
                const sig = JSON.stringify(res.comments.map(c => ({ id: c.id, created_at: c.created_at, gambar: c.gambar })));
                if (sig !== this.state.lastCommentsSig) {
                    this.state.lastCommentsSig = sig;
                    this.renderComments(res.comments);
                }
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
            container.innerHTML = `
                <div class="p-5 text-center text-muted rounded-4 bg-light">
                    <i class="bi bi-chat-square-text-fill fs-1 mb-3 text-primary opacity-50 d-block"></i>
                    <h6 class="fw-bold mb-1">Belum Ada Tanggapan</h6>
                    <p class="small mb-0 text-secondary">Berikan solusi atau penjelasan pertama untuk membantu pertanyaan di atas!</p>
                </div>
            `;
            return;
        }

        let cmtIndex = 1;
        const html = comments.map(c => {
            const initial = c.full_name ? c.full_name.charAt(0).toUpperCase() : 'U';
            const roleLower = (c.role_name || '').toLowerCase();
            let cRingClass = 'avatar-ring-siswa';
            let cardAccentClass = 'comment-card-siswa';
            let roleBadge = '<span class="badge bg-indigo-subtle text-indigo rounded-pill px-2 py-0" style="font-size:0.65rem; background:#e0e7ff; color:#3730a3;">Siswa</span>';

            if (roleLower.includes('admin')) {
                cRingClass = 'avatar-ring-admin';
                cardAccentClass = 'comment-card-admin';
                roleBadge = '<span class="badge bg-purple-subtle text-purple rounded-pill px-2 py-0" style="font-size:0.65rem; background:#f3e8ff; color:#6b21a8;"><i class="bi bi-shield-check me-1"></i>Administrator</span>';
            } else if (roleLower.includes('guru')) {
                cRingClass = 'avatar-ring-guru';
                cardAccentClass = 'comment-card-guru';
                roleBadge = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0" style="font-size:0.65rem;"><i class="bi bi-award-fill me-1 text-warning"></i>Solusi Pengajar</span>';
            }

            const cPrimaryUrl = c.gambar_url || (BASE_URL + 'assets/uploads/forum/' + c.gambar);
            const cFallbackUrl = BASE_URL + 'assets/uploads/tugas/' + c.gambar;
            const currentNum = cmtIndex++;

            const cmtImg = (c.gambar) ? `
                <div class="mt-2 text-start">
                    <div class="forum-image-preview-wrapper shadow-sm d-inline-block" style="max-width: 320px; max-height: 220px; border-radius: 16px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openLightboxModal('${cPrimaryUrl}', 'Lampiran Balasan Komentar')">
                        <img src="${cPrimaryUrl}" onerror="this.onerror=null; this.src='${cFallbackUrl}';" alt="Lampiran Balasan" style="max-width: 100%; max-height: 200px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; border-radius: 14px;">
                        <div class="forum-image-overlay">
                            <span><i class="bi bi-zoom-in me-1"></i> Perbesar</span>
                        </div>
                    </div>
                </div>
            ` : '';

            return `
                <div class="comment-card-item ${cardAccentClass} shadow-sm" data-comment-id="${c.id}" data-role="${roleLower}" data-has-image="${c.gambar ? 'true' : 'false'}">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-ring ${cRingClass}" style="padding:1px;">
                                <div class="avatar-inner" style="width:34px; height:34px; font-size:0.85rem;">
                                    ${initial}
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-bold small text-dark">${c.full_name}</span>
                                    ${roleBadge}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted me-1" style="font-size:0.73rem;"><i class="bi bi-clock me-1"></i>${c.created_at}</small>
                            <span class="comment-number-badge">#${currentNum}</span>
                            <button type="button" class="btn btn-sm btn-light border comment-quote-btn" onclick="ForumApp.quoteComment('${(c.full_name || '').replace(/'/g, "\\'")}', ${currentNum})" title="Kutip Balasan Ini">
                                <i class="bi bi-quote"></i> Kutip
                            </button>
                        </div>
                    </div>
                    <p class="mb-2 text-dark small" style="white-space: pre-line; line-height:1.65; font-size:0.92rem; color: #334155;">${c.komentar}</p>
                    ${cmtImg}
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    },

    quoteComment: function(authorName, commentNum) {
        const input = document.getElementById('replyKomentarInput') || document.getElementById('quickReplyKomentarInput');
        if (input) {
            const prefix = `@${authorName} (#${commentNum}): `;
            input.value = prefix + input.value;
            input.focus();
            
            const replyForm = document.getElementById('replyFormCard');
            if (replyForm) {
                replyForm.scrollIntoView({ behavior: 'smooth' });
            }
        }
    },

    filterComments: function(type) {
        const container = document.getElementById('commentsListContainer');
        if (!container) return;

        const chips = document.querySelectorAll('#commentFilterChips button');
        chips.forEach(chip => chip.classList.remove('btn-primary', 'active'));
        chips.forEach(chip => chip.classList.add('btn-light', 'text-dark'));

        event.currentTarget.classList.remove('btn-light', 'text-dark');
        event.currentTarget.classList.add('btn-primary', 'active');

        const items = container.querySelectorAll('.comment-card-item');
        items.forEach(item => {
            const role = item.getAttribute('data-role') || '';
            const hasImage = item.getAttribute('data-has-image') === 'true';

            if (type === 'all') {
                item.classList.remove('d-none');
            } else if (type === 'guru') {
                if (role.includes('guru')) item.classList.remove('d-none');
                else item.classList.add('d-none');
            } else if (type === 'image') {
                if (hasImage) item.classList.remove('d-none');
                else item.classList.add('d-none');
            }
        });
    },

    sortComments: function(order) {
        const container = document.getElementById('commentsListContainer');
        if (!container) return;

        const items = Array.from(container.querySelectorAll('.comment-card-item'));
        if (items.length === 0) return;

        items.sort((a, b) => {
            const idA = parseInt(a.getAttribute('data-comment-id')) || 0;
            const idB = parseInt(b.getAttribute('data-comment-id')) || 0;
            return order === 'newest' ? idB - idA : idA - idB;
        });

        items.forEach(item => container.appendChild(item));
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ForumApp.init();

    // Quick Reply Form Handler
    const quickForm = document.getElementById('quickReplyForm');
    if (quickForm) {
        quickForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_comment');

            ForumApp.state.isMutating = true;
            fetch(`${BASE_URL}index.php?url=forum/detail&id=${ForumApp.state.activeTopicId}`, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                this.reset();
                const modalElem = document.getElementById('modalQuickReply');
                if (modalElem) {
                    const modal = bootstrap.Modal.getInstance(modalElem);
                    if (modal) modal.hide();
                }
                ForumApp.fetchComments();
            })
            .catch(err => console.error(err))
            .finally(() => {
                ForumApp.state.isMutating = false;
            });
        });
    }
});


