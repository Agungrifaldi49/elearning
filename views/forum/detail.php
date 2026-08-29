<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<script src="<?= BASE_URL ?>assets/js/forum.js?v=<?= time() ?>"></script>

<main class="main-content px-3 px-md-4 pb-4">
    <div class="container-fluid pt-2">
        <input type="hidden" id="activeTopicId" value="<?= $topic['id'] ?>">

        <!-- Navigation Ribbon & Breadcrumb (Prominent Top Clearance Below Navbar Header) -->
        <div class="d-flex align-items-center justify-content-between mb-4 mt-3 flex-wrap gap-3 bg-white p-3 p-md-4 rounded-4 shadow-sm border" style="border-left: 5px solid #0d6efd !important;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=forum" class="btn btn-outline-primary rounded-pill px-4 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-2" style="font-size:0.9rem;">
                    <i class="bi bi-arrow-left-circle-fill fs-5"></i> Kembali ke Daftar Forum
                </a>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 d-none d-md-inline-block" style="font-size:0.82rem;">
                    <i class="bi bi-chat-square-quote-fill me-1"></i> Detail Topik #<?= $topic['id'] ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="#replyFormCard" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow d-inline-flex align-items-center gap-2" style="font-size:0.9rem;">
                    <i class="bi bi-chat-left-text-fill fs-5 text-warning"></i> Tulis Balasan
                </a>
            </div>
        </div>

        <!-- Multi-Column Grid Detail Topik -->
        <div class="row g-4">
            <!-- Left Main Column: Topic Showcase & Discussion Thread -->
            <div class="col-lg-8 col-xl-8">
                <?php 
                    $isAuthor = ((int)($topic['user_id'] ?? 0) === (int)($user['id'] ?? 0));
                    $isAdmin = (strtolower($user['role_name'] ?? '') === 'administrator');
                    $canDelete = ($isAuthor || $isAdmin);
                    $isPrivate = (($topic['visibility'] ?? 'public') === 'private');
                    $roleNameLower = strtolower($topic['role_name'] ?? '');
                    $ringClass = 'avatar-ring-siswa';
                    if (str_contains($roleNameLower, 'admin')) {
                        $ringClass = 'avatar-ring-admin';
                    } else if (str_contains($roleNameLower, 'guru')) {
                        $ringClass = 'avatar-ring-guru';
                    }
                ?>

                <!-- Topic Main Showcase Card -->
                <div class="forum-topic-card p-4 p-md-5 mb-4 shadow-sm rounded-4 bg-white border">
                    <!-- Author Header & Metadata -->
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-ring <?= $ringClass ?>" style="padding:2px;">
                                <div class="avatar-inner" style="width:48px; height:48px; font-size:1.2rem;">
                                    <?= strtoupper(substr($topic['full_name'], 0, 1)) ?>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold mb-1 text-dark d-flex align-items-center gap-2 flex-wrap" style="font-size:1rem;">
                                    <span><?= htmlspecialchars($topic['full_name']) ?></span>
                                    <span class="badge bg-indigo-subtle text-indigo rounded-pill px-2 py-1" style="font-size:0.72rem; background:#e0e7ff; color:#3730a3;">
                                        <?= htmlspecialchars($topic['role_name']) ?>
                                    </span>
                                    <?php if ($isAuthor): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size:0.72rem;">Pembuat Topik</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 flex-wrap" style="font-size:0.8rem;">
                                    <span><i class="bi bi-clock me-1"></i><?= date('d F Y, H:i', strtotime($topic['created_at'])) ?> WIB</span>
                                    <?php if ($topic['nama_mapel']): ?>
                                        <span>•</span>
                                        <span class="text-primary fw-medium"><i class="bi bi-book me-1"></i><?= htmlspecialchars($topic['nama_mapel']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <?php if ($isPrivate): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size:0.8rem;">
                                    <i class="bi bi-lock-fill text-warning me-1"></i> Privat
                                    <?php if (!empty($topic['target_role'])): ?>
                                        (<?= ucfirst(htmlspecialchars($topic['target_role'])) ?>)
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size:0.8rem;">
                                    <i class="bi bi-globe me-1"></i> Publik
                                </span>
                            <?php endif; ?>

                            <?php if ($canDelete): ?>
                                <button class="btn btn-outline-danger btn-sm rounded-circle p-2" title="Hapus Topik Diskusi" data-bs-toggle="modal" data-bs-target="#modalDeleteTopicDetail" style="width:34px; height:34px; line-height:1;">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <!-- Modal Delete Topic Detail -->
                                <div class="modal fade" id="modalDeleteTopicDetail" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content border-0 rounded-4 shadow-lg">
                                            <div class="modal-body text-center p-4">
                                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3">
                                                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                                                </div>
                                                <h6 class="fw-bold mb-2">Hapus Topik Diskusi?</h6>
                                                <p class="small text-muted mb-4">Apakah Anda yakin ingin menghapus topik ini beserta seluruh balasannya secara permanen?</p>
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
                        </div>
                    </div>

                    <!-- Title & Main Content Body -->
                    <h3 class="fw-bold mb-3 text-dark" style="font-size: 1.45rem; line-height: 1.4;"><?= htmlspecialchars($topic['judul']) ?></h3>
                    <div class="text-dark mb-4" style="white-space: pre-line; line-height: 1.8; font-size: 1rem; color: #334155;">
                        <?= htmlspecialchars($topic['konten']) ?>
                    </div>

                    <!-- Attached Image Preview Showcase -->
                    <?php if (!empty($topic['gambar'])): 
                        $topicImgPath = (file_exists(ROOT_PATH . 'assets/uploads/forum/' . $topic['gambar'])) 
                            ? BASE_URL . 'assets/uploads/forum/' . htmlspecialchars($topic['gambar']) 
                            : BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($topic['gambar']);
                    ?>
                        <div class="mb-4">
                            <div class="forum-image-preview-wrapper shadow-sm" style="max-width: 100%; height: 320px; border-radius:18px;" onclick="openLightboxModal('<?= $topicImgPath ?>', '<?= htmlspecialchars(addslashes($topic['judul'])) ?>')">
                                <img src="<?= $topicImgPath ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($topic['gambar']) ?>';" alt="Lampiran Gambar Topik">
                                <div class="forum-image-overlay">
                                    <i class="bi bi-zoom-in me-1"></i> Klik untuk memperbesar gambar
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Multi-Emoji Reaction Bar -->
                    <?php 
                        $reactionMap = [
                            ['type' => 'love', 'emoji' => '❤️', 'label' => 'Love'],
                            ['type' => 'like', 'emoji' => '👍', 'label' => 'Jempol'],
                            ['type' => 'laugh', 'emoji' => '😂', 'label' => 'Ketawa'],
                            ['type' => 'sad', 'emoji' => '😢', 'label' => 'Sedih'],
                            ['type' => 'wow', 'emoji' => '😮', 'label' => 'Kaget'],
                            ['type' => 'fire', 'emoji' => '🔥', 'label' => 'Menyala']
                        ];
                        $rc = $reactions ?? ['love'=>0,'like'=>0,'laugh'=>0,'sad'=>0,'wow'=>0,'fire'=>0,'my_reaction'=>null];
                    ?>
                    <div class="pt-3 border-top">
                        <div class="reaction-bar d-flex flex-wrap align-items-center" data-forum-id="<?= $topic['id'] ?>">
                            <?php foreach ($reactionMap as $r): ?>
                                <?php 
                                    $count = $rc[$r['type']] ?? 0;
                                    $isActive = (($rc['my_reaction'] ?? '') === $r['type']);
                                    $btnClass = $isActive ? 'btn-primary-subtle border-primary text-primary fw-bold shadow-sm' : 'btn-light border-0 text-secondary';
                                ?>
                                <button type="button" class="btn btn-sm <?= $btnClass ?> rounded-pill px-3 py-1 me-2 mb-2 btn-emoji-react" 
                                        onclick="ForumApp.toggleReaction(<?= $topic['id'] ?>, '<?= $r['type'] ?>')" 
                                        title="<?= $r['label'] ?>">
                                    <span class="fs-5 me-1"><?= $r['emoji'] ?></span>
                                    <span class="small fw-semibold"><?= $count > 0 ? $count : '' ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Comments Thread Section -->
                <div class="card border-0 rounded-4 p-4 p-md-5 mb-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-chat-left-dots-fill text-primary"></i> 
                                Tanggapan & Solusi Akademik
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-1 fs-6 ms-1">
                                <span id="commentCountBadge"><?= count($comments) ?></span> Balasan
                            </span>
                        </div>

                        <!-- Quick Action Button to Open Quick Reply Modal -->
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalQuickReply">
                            <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Balas Cepat
                        </button>
                    </div>

                    <!-- Comment Filter & Sort Toolbar -->
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 p-2 bg-light rounded-3 border">
                        <div class="d-flex align-items-center gap-1 overflow-auto" id="commentFilterChips">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 active" onclick="ForumApp.filterComments('all')">
                                <i class="bi bi-grid-fill me-1"></i> Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-dark" onclick="ForumApp.filterComments('guru')">
                                <i class="bi bi-person-badge-fill me-1 text-warning"></i> Solusi Guru
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-dark" onclick="ForumApp.filterComments('image')">
                                <i class="bi bi-image me-1 text-info"></i> Lampiran Foto
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted fw-semibold">Urutan:</small>
                            <select id="commentSortSelect" class="form-select form-select-sm rounded-pill border-secondary-subtle" style="width: auto; font-size:0.8rem;" onchange="ForumApp.sortComments(this.value)">
                                <option value="oldest">Terlama (#1 Dulu)</option>
                                <option value="newest">Terbaru di Atas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Scrollable Comments Thread Box (Max-Height 650px for 100+ Replies) -->
                    <div class="d-flex flex-column gap-3 mb-4 pe-1" id="commentsListContainer" style="max-height: 650px; overflow-y: auto; scroll-behavior: smooth;">
                        <?php if (empty($comments)): ?>
                            <div class="p-5 text-center text-muted rounded-4 bg-light">
                                <i class="bi bi-chat-square-text-fill fs-1 mb-3 text-primary opacity-50 d-block"></i>
                                <h6 class="fw-bold mb-1">Belum Ada Tanggapan</h6>
                                <p class="small mb-0 text-secondary">Berikan solusi atau penjelasan pertama untuk membantu pertanyaan di atas!</p>
                            </div>
                        <?php else: ?>
                            <?php 
                                $cmtIndex = 1;
                                foreach ($comments as $c): 
                                    $cRoleLower = strtolower($c['role_name'] ?? '');
                                    $cRingClass = 'avatar-ring-siswa';
                                    $cardAccentClass = 'comment-card-siswa';
                                    $roleBadge = '<span class="badge bg-indigo-subtle text-indigo rounded-pill px-2 py-0" style="font-size:0.65rem; background:#e0e7ff; color:#3730a3;">Siswa</span>';

                                    if (str_contains($cRoleLower, 'admin')) {
                                        $cRingClass = 'avatar-ring-admin';
                                        $cardAccentClass = 'comment-card-admin';
                                        $roleBadge = '<span class="badge bg-purple-subtle text-purple rounded-pill px-2 py-0" style="font-size:0.65rem; background:#f3e8ff; color:#6b21a8;"><i class="bi bi-shield-check me-1"></i>Administrator</span>';
                                    } else if (str_contains($cRoleLower, 'guru')) {
                                        $cRingClass = 'avatar-ring-guru';
                                        $cardAccentClass = 'comment-card-guru';
                                        $roleBadge = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0" style="font-size:0.65rem;"><i class="bi bi-award-fill me-1 text-warning"></i>Solusi Pengajar</span>';
                                    }
                            ?>
                                <div class="comment-card-item <?= $cardAccentClass ?> shadow-sm" data-comment-id="<?= $c['id'] ?>" data-role="<?= $cRoleLower ?>" data-has-image="<?= !empty($c['gambar']) ? 'true' : 'false' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-ring <?= $cRingClass ?>" style="padding:1px;">
                                                <div class="avatar-inner" style="width:34px; height:34px; font-size:0.85rem;">
                                                    <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-bold small text-dark"><?= htmlspecialchars($c['full_name']) ?></span>
                                                    <?= $roleBadge ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted me-1" style="font-size:0.73rem;"><i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                                            <span class="comment-number-badge">#<?= $cmtIndex++ ?></span>
                                            <button type="button" class="btn btn-sm btn-light border comment-quote-btn" onclick="ForumApp.quoteComment('<?= htmlspecialchars(addslashes($c['full_name'])) ?>', <?= $cmtIndex - 1 ?>)" title="Kutip Balasan Ini">
                                                <i class="bi bi-quote"></i> Kutip
                                            </button>
                                        </div>
                                    </div>

                                    <p class="mb-2 text-dark small" style="white-space: pre-line; line-height:1.65; font-size:0.92rem; color: #334155;"><?= htmlspecialchars($c['komentar']) ?></p>
                                    
                                    <?php if (!empty($c['gambar'])): 
                                        $cFolder = file_exists(ROOT_PATH . 'assets/uploads/forum/' . $c['gambar']) ? 'forum' : 'tugas';
                                        $cmtImg = BASE_URL . 'assets/uploads/' . $cFolder . '/' . htmlspecialchars($c['gambar']);
                                    ?>
                                        <div class="mt-2">
                                            <div class="forum-image-preview-wrapper d-inline-block" style="max-width: 280px; height: 160px; border-radius:12px;" onclick="openLightboxModal('<?= $cmtImg ?>', 'Lampiran Balasan Komentar')">
                                                <img src="<?= $cmtImg ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($c['gambar']) ?>';" class="img-fluid rounded-3 border" style="height: 100%; object-fit: cover;" alt="Lampiran Balasan">
                                                <div class="forum-image-overlay" style="font-size:0.75rem;">
                                                    <i class="bi bi-zoom-in me-1"></i> Perbesar
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Elevated Bottom Reply Form Card -->
                    <div id="replyFormCard" class="border-top pt-4">
                        <div class="bg-light p-4 rounded-4 border shadow-sm">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-reply-fill text-primary fs-5"></i> Berikan Tanggapan atau Solusi Pembelajaran
                                </span>
                                <span class="small text-muted fw-normal" style="font-size:0.78rem;">
                                    Membalas sebagai: <strong><?= htmlspecialchars($user['full_name'] ?? 'Pengguna') ?></strong> (<?= htmlspecialchars($user['role_name'] ?? 'User') ?>)
                                </span>
                            </h6>
                            <form id="commentReplyForm" enctype="multipart/form-data">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="forum_id" value="<?= $topic['id'] ?>">

                                <div class="position-relative mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small text-muted">Tulis tanggapan akademik yang bermanfaat:</span>
                                        <button type="button" id="replyEmojiBtn" class="btn btn-sm btn-white border rounded-pill px-3 py-1 text-secondary shadow-sm" title="Pilih Emoji">
                                            <i class="bi bi-emoji-smile me-1 text-warning"></i> Emoji
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

                                    <textarea name="komentar" id="replyKomentarInput" class="form-control rounded-3 border shadow-sm p-3" rows="3" placeholder="Tuliskan komentar atau solusi penjelasan di sini..." required style="resize: vertical; font-size:0.95rem;"></textarea>
                                </div>

                                <div class="p-3 bg-white rounded-3 border shadow-sm mb-3">
                                    <label class="form-label small fw-semibold text-dark mb-2 d-flex align-items-center gap-1">
                                        <i class="bi bi-image text-primary"></i> Lampiran Gambar Screenshot (Opsional)
                                    </label>
                                    <input type="file" name="gambar" id="replyImageInput" class="form-control form-control-sm rounded-3" accept="image/*" onchange="previewImageInput(this, 'replyImagePreview', 'replyImageContainer')">
                                    <div id="replyImageContainer" class="mt-2 d-none position-relative">
                                        <img id="replyImagePreview" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 140px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-0 m-1 p-1" onclick="clearImageInput('replyImageInput', 'replyImageContainer')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="bi bi-send-fill me-1"></i> Kirim Balasan Diskusi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column: Topic Info & Author Profile Widget -->
            <div class="col-lg-4 col-xl-4">
                <div class="d-flex flex-column gap-4">
                    <!-- Author Information Profile Card -->
                    <div class="forum-sidebar-card">
                        <div class="card-header">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge-fill text-primary fs-5"></i> Pembuat Topik Diskusi
                            </h6>
                        </div>
                        <div class="p-4 text-center">
                            <div class="avatar-ring <?= $ringClass ?> mx-auto mb-3" style="width:64px; height:64px; padding:3px;">
                                <div class="avatar-inner" style="width:58px; height:58px; font-size:1.5rem;">
                                    <?= strtoupper(substr($topic['full_name'], 0, 1)) ?>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark fs-6"><?= htmlspecialchars($topic['full_name']) ?></h6>
                            <span class="badge bg-indigo-subtle text-indigo rounded-pill px-3 py-1 mb-3" style="background:#e0e7ff; color:#3730a3; font-size:0.78rem;">
                                <?= htmlspecialchars($topic['role_name']) ?>
                            </span>

                            <hr class="my-3 opacity-25">

                            <div class="text-start d-flex flex-column gap-2 small text-muted">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-calendar3 me-1 text-primary"></i> Tanggal Dibuat:</span>
                                    <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($topic['created_at'])) ?></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-shield-check me-1 text-primary"></i> Sifat Akses:</span>
                                    <span class="fw-semibold <?= $isPrivate ? 'text-warning' : 'text-success' ?>">
                                        <?= $isPrivate ? '🔒 Privat' : '🌐 Publik' ?>
                                    </span>
                                </div>
                                <?php if ($topic['nama_mapel']): ?>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-journal-text me-1 text-primary"></i> Mata Pelajaran:</span>
                                        <span class="fw-semibold text-primary"><?= htmlspecialchars($topic['nama_mapel']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Discussion Guidelines Card -->
                    <div class="forum-sidebar-card border-0 bg-success bg-opacity-10">
                        <div class="p-4">
                            <h6 class="fw-bold text-success mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Panduan Tanggapan Efektif
                            </h6>
                            <p class="small text-secondary mb-0" style="line-height:1.65; font-size:0.84rem;">
                                Berikan jawaban penjelasan yang terstruktur, komunikatif, dan tepat sasaran. Jika memiliki screenshot atau referensi gambar pendukung, lampirkan pada form balasan komentar.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Quick Reply (Balas Cepat Tanpa Scroll) -->
<div class="modal fade" id="modalQuickReply" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold modal-title d-flex align-items-center gap-2">
                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                    </span>
                    Balas Cepat Diskusi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickReplyForm" enctype="multipart/form-data">
                <div class="modal-body px-4 py-3">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="forum_id" value="<?= $topic['id'] ?>">

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="small text-muted mb-1">Membalas Topik:</div>
                        <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($topic['judul']) ?></div>
                    </div>

                    <div class="mb-3 position-relative">
                        <label class="form-label small fw-semibold">Isi Balasan / Solusi Akademik <span class="text-danger">*</span></label>
                        <textarea name="komentar" id="quickReplyKomentarInput" class="form-control rounded-3" rows="4" placeholder="Tuliskan jawaban atau solusi Anda di sini..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lampiran Gambar Screenshot (Opsional)</label>
                        <input type="file" name="gambar" id="quickReplyImageInput" class="form-control rounded-3" accept="image/*" onchange="previewImageInput(this, 'quickReplyImagePreview', 'quickReplyImageContainer')">
                        <div id="quickReplyImageContainer" class="mt-2 d-none position-relative">
                            <img id="quickReplyImagePreview" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 160px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-0 m-1 p-1" onclick="clearImageInput('quickReplyImageInput', 'quickReplyImageContainer')">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Kirim Balasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Floating Action Buttons for 100+ Replies -->
<div class="forum-floating-actions">
    <button type="button" class="floating-btn-reply" data-bs-toggle="modal" data-bs-target="#modalQuickReply" title="Balas Cepat Diskusi (Tanpa Scroll)">
        <i class="bi bi-chat-left-text-fill"></i>
    </button>
    <button type="button" class="floating-btn-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Kembali ke Atas">
        <i class="bi bi-arrow-up-short fs-4"></i>
    </button>
</div>

<!-- Image Lightbox Zoom Modal -->
<div class="modal fade lightbox-modal" id="globalLightboxModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white small" id="lightboxTitle">Lampiran Gambar</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="lightboxImage" src="" alt="Pratinjau Gambar">
            </div>
        </div>
    </div>
</div>

<script>
function openLightboxModal(src, title) {
    const modalElem = document.getElementById('globalLightboxModal');
    const imgElem = document.getElementById('lightboxImage');
    const titleElem = document.getElementById('lightboxTitle');
    if (modalElem && imgElem) {

        imgElem.src = src;
        if (titleElem) titleElem.textContent = title || 'Pratinjau Gambar';
        const bsModal = new bootstrap.Modal(modalElem);
        bsModal.show();
    }
}

function previewImageInput(input, imgId, containerId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(imgId).src = e.target.result;
            document.getElementById(containerId).classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function clearImageInput(inputId, containerId) {
    const input = document.getElementById(inputId);
    if (input) input.value = '';
    const container = document.getElementById(containerId);
    if (container) container.classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('replyEmojiBtn');
    const popover = document.getElementById('replyEmojiPopover');
    const closeBtn = document.getElementById('closeReplyEmojiBtn');
    const container = document.getElementById('replyEmojiListContainer');
    const input = document.getElementById('replyKomentarInput');
    const catTabs = document.querySelectorAll('.reply-emoji-cat-tab');

    if (btn && popover && container && input) {
        const emojiCategories = {
            smileys: ['😀','😃','😄','😁','😆','😅','😂','🤣','🥲','🥹','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😮‍💨','😤','😠','😡','🤬','🤯','😱','😨','😰','😥','😓','🤗','🤔','🫣','🤭','🥱','😴','🤤','😷','🤒','🤕','🤢','🤮','🤧','😵','🤠'],
            gestures: ['👍','👎','👌','🤌','🤏','✌️','🤞','🫰','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','🫵','👋','🤚','🖐️','✋','🖖','🫱','🫲','🫳','🫴','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🫁','🧠','🗣️','👤','👥'],
            love: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❤️‍🔥','❤️‍🩹','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','☸️','✡️','🔯','✴️','☯️','☦️','🔒','🔓','🔑','🛡️','⚔️','⚖️','💯','⚡','✨','🌟','⭐'],
            education: ['🎓','📚','📖','📜','📑','📰','📊','📈','📉','📄','📅','📆','📇','📋','📁','📂','📒','📓','📔','📕','📗','📘','📙','🖋️','🖊️','🖌️','🖍️','📝','✏️','📏','📐','✂️','📌','📍','🔍','🔎','🏫','👨‍🏫','👩‍🏫','👨‍🎓','👩‍🎓','💻','🖥️','🖨️','📱','⌨️','🖱️','💾'],
            activities: ['🔥','🎉','🎊','🎈','🎁','🎀','🏆','🥇','🥈','🥉','🏅','🎖️','🎗️','⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🥏','🎱','🪀','🏓','🏸','🏒','🏑','🥍','🏏','🥊','🥋','🚀','🎯','🇮🇩','🏁','🚩']
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
    }
});
</script>

<!-- Quick Reply Modal for 100+ Replies UX -->
<div class="modal fade" id="modalQuickReply" tabindex="-1" aria-labelledby="modalQuickReplyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-primary text-white p-4 rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalQuickReplyLabel">
                    <i class="bi bi-lightning-charge-fill text-warning me-2"></i> Balas Diskusi Cepat
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickReplyForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="forum_id" value="<?= $topic['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Tanggapan / Solusi Akademik Anda</label>
                        <textarea name="komentar" id="quickReplyKomentarInput" class="form-control rounded-3 p-3" rows="4" placeholder="Tuliskan balasan Anda di sini..." required style="resize: vertical; font-size:0.95rem;"></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">Lampiran Foto Screenshot (Opsional)</label>
                        <input type="file" name="gambar" id="quickReplyImageInput" class="form-control form-control-sm rounded-3" accept="image/*" onchange="previewImageInput(this, 'quickReplyImagePreview', 'quickReplyImageContainer')">
                        <div id="quickReplyImageContainer" class="mt-2 d-none position-relative">
                            <img id="quickReplyImagePreview" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 140px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-0 m-1 p-1" onclick="clearImageInput('quickReplyImageInput', 'quickReplyImageContainer')">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Kirim Balasan Cepat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Floating Actions (Quick Reply & Scroll Top) -->
<div class="forum-floating-actions">
    <button type="button" class="floating-btn-reply" data-bs-toggle="modal" data-bs-target="#modalQuickReply" title="Balas Diskusi Cepat">
        <i class="bi bi-chat-fill"></i>
    </button>
    <button type="button" class="floating-btn-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Kembali ke Atas">
        <i class="bi bi-arrow-up-short"></i>
    </button>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
