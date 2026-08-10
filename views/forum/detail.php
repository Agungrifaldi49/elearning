<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <a href="<?= BASE_URL ?>index.php?url=forum" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Forum
        </a>

        <!-- Topic Detail -->
        <?php 
            $isAuthor = ((int)($topic['user_id'] ?? 0) === (int)($user['id'] ?? 0));
            $isAdmin = (strtolower($user['role_name'] ?? '') === 'administrator');
            $canDelete = ($isAuthor || $isAdmin);
            $isPrivate = (($topic['visibility'] ?? 'public') === 'private');
        ?>
        <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm border-0 rounded-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 48px; height: 48px;">
                        <?= strtoupper(substr($topic['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($topic['full_name']) ?> <span class="badge bg-secondary opacity-75 ms-1"><?= htmlspecialchars($topic['role_name']) ?></span></h6>
                        <small class="text-muted"><?= date('d F Y, H:i', strtotime($topic['created_at'])) ?> <?= $topic['nama_mapel'] ? '| <i class="bi bi-journal-bookmark text-primary me-1"></i>' . htmlspecialchars($topic['nama_mapel']) : '' ?></small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($isPrivate): ?>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-1">
                            <i class="bi bi-lock-fill text-warning me-1"></i> Privat
                            <?php if (!empty($topic['target_role'])): ?>
                                (<?= ucfirst(htmlspecialchars($topic['target_role'])) ?>)
                            <?php endif; ?>
                            <?php if (!empty($topic['target_nama_kelas'])): ?>
                                - <?= htmlspecialchars($topic['target_nama_kelas']) ?>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                            <i class="bi bi-globe me-1"></i> Publik
                        </span>
                    <?php endif; ?>

                    <?php if ($canDelete): ?>
                        <button class="btn btn-outline-danger btn-sm rounded-circle p-2" title="Hapus Topik Diskusi" data-bs-toggle="modal" data-bs-target="#modalDeleteTopicDetail">
                            <i class="bi bi-trash3"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <h4 class="fw-bold mb-3 text-dark"><?= htmlspecialchars($topic['judul']) ?></h4>
            <p class="lead text-muted fs-6 mb-4" style="white-space: pre-line;"><?= htmlspecialchars($topic['konten']) ?></p>

            <?php if ($topic['gambar']): ?>
                <div class="mb-4">
                    <img src="<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($topic['gambar']) ?>" class="img-fluid rounded-4 shadow-sm" style="max-height: 400px; object-fit: cover;">
                </div>
            <?php endif; ?>
        </div>

        <?php if ($canDelete): ?>
            <!-- Modal Delete Topic Detail -->
            <div class="modal fade" id="modalDeleteTopicDetail" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <div class="modal-body text-center p-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Hapus Topik Diskusi?</h6>
                            <p class="small text-muted mb-4">Apakah Anda yakin ingin menghapus topik <strong>"<?= htmlspecialchars($topic['judul']) ?>"</strong>? Seluruh balasan komentar akan ikut terhapus secara permanen.</p>
                            <form action="<?= BASE_URL ?>index.php?url=forum" method="POST">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light w-100 rounded-pill" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger w-100 rounded-pill">Ya, Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-chat-left-dots text-primary me-2"></i> Tanggapan Diskusi (<?= count($comments) ?>)</h5>

            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($comments as $c): ?>
                    <div class="p-3 bg-light rounded-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fw-bold small"><?= htmlspecialchars($c['full_name']) ?></span>
                            <span class="badge bg-primary" style="font-size:0.65rem;"><?= htmlspecialchars($c['role_name']) ?></span>
                            <small class="text-muted ms-auto"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                        </div>
                        <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($c['komentar'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Comment Form -->
            <form action="<?= BASE_URL ?>index.php?url=forum/detail&id=<?= $topic['id'] ?>" method="POST">
                <?= Security::csrfField() ?>
                <div class="mb-3 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold mb-0">Tulis Tanggapan Anda <span class="text-danger">*</span></label>
                        <button type="button" id="replyEmojiBtn" class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-secondary" title="Pilih Emoji">
                            <i class="bi bi-emoji-smile me-1"></i> Sisipkan Emoji
                        </button>
                    </div>

                    <!-- Reply Emoji Picker Popover Box -->
                    <div id="replyEmojiPopover" class="card shadow-lg border-0 rounded-4 position-absolute bottom-100 end-0 mb-2 d-none" style="width: 340px; z-index: 1060;">
                        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                            <span class="small fw-bold"><i class="bi bi-emoji-smile me-1"></i> Pilih Emoji</span>
                            <button type="button" class="btn-close btn-close-white small" id="closeReplyEmojiBtn"></button>
                        </div>
                        <div class="bg-light border-bottom d-flex justify-content-around p-1" id="replyEmojiCatTabs">
                            <button type="button" class="btn btn-sm btn-light border-0 reply-emoji-cat-tab active" data-cat="smileys" style="font-size: 1.1rem;">😀</button>
                            <button type="button" class="btn btn-sm btn-light border-0 reply-emoji-cat-tab" data-cat="gestures" style="font-size: 1.1rem;">👋</button>
                            <button type="button" class="btn btn-sm btn-light border-0 reply-emoji-cat-tab" data-cat="love" style="font-size: 1.1rem;">❤️</button>
                            <button type="button" class="btn btn-sm btn-light border-0 reply-emoji-cat-tab" data-cat="education" style="font-size: 1.1rem;">🎓</button>
                            <button type="button" class="btn btn-sm btn-light border-0 reply-emoji-cat-tab" data-cat="activities" style="font-size: 1.1rem;">🔥</button>
                        </div>
                        <div class="card-body p-2 overflow-auto" style="max-height: 220px; font-size: 1.3rem;">
                            <div class="d-flex flex-wrap gap-1" id="replyEmojiListContainer"></div>
                        </div>
                    </div>

                    <textarea name="komentar" id="replyKomentarInput" class="form-control rounded-3" rows="3" placeholder="Tuliskan komentar atau solusi..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill">
                    <i class="bi bi-send me-1"></i> Kirim Balasan
                </button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('replyEmojiBtn');
    const popover = document.getElementById('replyEmojiPopover');
    const closeBtn = document.getElementById('closeReplyEmojiBtn');
    const container = document.getElementById('replyEmojiListContainer');
    const input = document.getElementById('replyKomentarInput');
    const catTabs = document.querySelectorAll('.reply-emoji-cat-tab');

    if (!btn || !popover || !container || !input) return;

    const emojiCategories = {
        smileys: ['😀','😃','😄','😁','😆','😅','😂','🤣','🥲','🥹','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😮‍💨','😤','😠','😡','🤬','🤯','😱','😨','😰','😥','😓','🤗','🤔','🫣','🤭','🥱','😴','🤤','😷','🤒','🤕','🤢','🤮','🤧','😵','🤠'],
        gestures: ['👍','👎','👌','🤌','🤏','✌️','🤞','🫰','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','🫵','👋','🤚','🖐️','✋','🖖','🫱','🫲','🫳','🫴','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🫁','🧠','🗣️','👤','👥'],
        love: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❤️‍🔥','❤️‍🩹','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','☸️','✡️','🔯','✴️','☯️','☦️','🔒','🔓','🔑','🛡️','⚔️','⚖️','💯','⚡','✨','🌟','⭐'],
        education: ['🎓','📚','📖','📜','📑','📰','📊','📈','📉','📄','📅','📆','📇','📋','📁','📂','📒','📓','📔','📕','📗','📘','📙','🖋️','🖊️','🖌️','🖍️','📝','✏️','📏','📐','✂️','📌','📍','🔍','🔎','🏫','👨‍🏫','👩‍🏫','👨‍🎓','👩‍🎓','💻','🖥️','🖨️','📱','⌨️','🖱️','💾'],
        activities: ['🔥','🎉','🎊','🎈','🎁','🎀','🏆','🥇','🥈','🥉','🏅','🎖️','🎗️','⚽','🏀','🏈','⚾','🥎','tennis','🏐','rugby','🥏','bowling','🪀','pingpong','badminton','hockey','fieldhockey','lacrosse','cricket','boxing','martialarts','rocket','target','🇮🇩','🏁','🚩']
    };

    function renderCategory(catKey) {
        const list = emojiCategories[catKey] || emojiCategories.smileys;
        container.innerHTML = list.map(e => `
            <button type="button" class="btn btn-light btn-sm p-1 fs-5 border-0 reply-emoji-item" style="width: 36px; height: 36px; line-height: 1;">${e}</button>
        `).join('');

        container.querySelectorAll('.reply-emoji-item').forEach(item => {
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
    }

    renderCategory('smileys');

    catTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.stopPropagation();
            catTabs.forEach(t => t.classList.remove('active', 'bg-white', 'shadow-sm'));
            tab.classList.add('active', 'bg-white', 'shadow-sm');
            const cat = tab.getAttribute('data-cat');
            renderCategory(cat);
        });
    });

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        popover.classList.toggle('d-none');
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => popover.classList.add('d-none'));
    }

    document.addEventListener('click', (e) => {
        if (!popover.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
            popover.classList.add('d-none');
        }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
