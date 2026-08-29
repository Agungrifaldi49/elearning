<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<script src="<?= BASE_URL ?>assets/js/forum.js?v=<?= time() ?>"></script>

<main class="main-content px-3 px-md-4 pb-5">
    <div class="container-fluid pt-2">
        <!-- Hero Header Banner (Modern Indigo Gradient Overlay) -->
        <div class="forum-hero-banner p-4 p-md-5 mb-4 shadow-lg position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 70%, #4338ca 100%) !important; color: #ffffff !important; border-radius: 24px; overflow: hidden;">
            <div class="row align-items-center g-3 position-relative" style="z-index: 2;">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="forum-hero-stat-chip" style="background: rgba(255,255,255,0.15) !important; backdrop-filter: blur(10px); color: #ffffff !important; border: 1px solid rgba(255,255,255,0.25); border-radius: 50px; padding: 6px 16px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-chat-square-text-fill text-warning me-1"></i> <?= count($topics) ?> Topik Diskusi
                        </span>
                        <span class="forum-hero-stat-chip" style="background: rgba(255,255,255,0.15) !important; backdrop-filter: blur(10px); color: #ffffff !important; border: 1px solid rgba(255,255,255,0.25); border-radius: 50px; padding: 6px 16px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-people-fill text-info me-1"></i> Komunitas KBM Aktif
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white display-6" style="letter-spacing: -0.5px;">Forum Diskusi & Komunitas Pembelajaran</h2>
                    <p class="mb-0 text-white-70 fs-6" style="max-width: 680px; line-height: 1.6; color: rgba(255, 255, 255, 0.85) !important;">
                        Ruang kolaborasi akademik resmi SMK Muthia Harapan Cicalengka. Bagikan pertanyaan, kirim tanggapan, sertakan lampiran gambar, dan berdiskusi secara interaktif.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-3 shadow-lg hover-elevation" data-bs-toggle="modal" data-bs-target="#modalAddTopic" style="font-size: 0.95rem;">
                        <i class="bi bi-plus-circle-fill me-2 fs-5"></i> Buat Topik Diskusi
                    </button>
                </div>
            </div>
        </div>

        <!-- Full-Width Toolbar: Filter Chips & Search Box -->
        <div class="row g-3 mb-4 align-items-center bg-white p-3 rounded-4 shadow-sm border">
            <div class="col-md-7 col-lg-8">
                <div class="d-flex align-items-center gap-2 overflow-auto py-1" id="forumFilterChips">
                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm forum-filter-chip active" data-filter="all">
                        <i class="bi bi-grid-fill me-1"></i> Semua Topik
                    </button>
                    <button class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-dark forum-filter-chip" data-filter="public">
                        <i class="bi bi-globe me-1 text-primary"></i> Publik
                    </button>
                    <button class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-dark forum-filter-chip" data-filter="private">
                        <i class="bi bi-lock-fill me-1 text-warning"></i> Privat
                    </button>
                </div>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="forumSearchInput" class="form-control rounded-pill ps-5 pe-4 py-2 border shadow-sm" placeholder="Cari topik diskusi atau pembuat..." style="font-size: 0.9rem;">
                    <button class="btn btn-sm btn-light rounded-circle position-absolute top-50 end-0 translate-middle-y me-2 p-1 text-muted d-none" id="clearSearchBtn" type="button">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2-Column Responsive Layout (Main Stream + Right Sidebar) -->
        <div class="row g-4">
            <!-- Left Main Column: Discussion Topics Stream -->
            <div class="col-lg-8 col-xl-8">
                <div class="d-flex flex-column gap-4" id="forumTopicsContainer">
                    <?php if (empty($topics)): ?>
                        <div class="p-5 text-center text-muted rounded-4 bg-white shadow-sm border">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-4 mb-3">
                                <i class="bi bi-chat-square-quote-fill display-5"></i>
                            </div>
                            <h5 class="fw-bold mb-1 text-dark">Belum Ada Topik Diskusi</h5>
                            <p class="small mb-3 text-secondary">Jadilah yang pertama untuk memulai pertanyaan atau bahan diskusi baru!</p>
                            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddTopic">
                                <i class="bi bi-plus-circle me-1"></i> Mulai Diskusi Pertama
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topics as $t): 
                            $isAuthor = ((int)($t['user_id'] ?? 0) === (int)($user['id'] ?? 0));
                            $isAdmin = (strtolower($user['role_name'] ?? '') === 'administrator');
                            $canDelete = ($isAuthor || $isAdmin);
                            $isPrivate = (($t['visibility'] ?? 'public') === 'private');
                            $roleNameLower = strtolower($t['role_name'] ?? '');
                            $ringClass = 'avatar-ring-siswa';
                            if (str_contains($roleNameLower, 'admin')) {
                                $ringClass = 'avatar-ring-admin';
                            } else if (str_contains($roleNameLower, 'guru')) {
                                $ringClass = 'avatar-ring-guru';
                            }
                        ?>
                            <div class="topic-card-item" data-topic-id="<?= $t['id'] ?>" data-visibility="<?= $t['visibility'] ?? 'public' ?>">
                                <div class="forum-topic-card p-4 rounded-4 shadow-sm bg-white border">
                                    <!-- Author & Metadata Header -->
                                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-ring <?= $ringClass ?>" style="padding:2px; flex-shrink: 0;">
                                                <div class="avatar-inner" style="width:42px; height:42px; font-size:1rem; font-weight:bold;">
                                                    <?= strtoupper(substr($t['full_name'], 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark d-flex align-items-center gap-2 flex-wrap" style="font-size:0.95rem;">
                                                    <span><?= htmlspecialchars($t['full_name']) ?></span>
                                                    <span class="badge bg-indigo-subtle text-indigo rounded-pill px-2 py-1" style="font-size:0.7rem; background:#e0e7ff; color:#3730a3;">
                                                        <?= htmlspecialchars($t['role_name']) ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted" style="font-size:0.78rem;">
                                                    <i class="bi bi-clock me-1"></i><?= date('d F Y, H:i', strtotime($t['created_at'])) ?> WIB
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($isPrivate): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size:0.75rem;">
                                                    <i class="bi bi-lock-fill text-warning me-1"></i> Privat
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size:0.75rem;">
                                                    <i class="bi bi-globe me-1"></i> Publik
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($canDelete): ?>
                                                <button class="btn btn-outline-danger btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" title="Hapus Topik Diskusi" data-bs-toggle="modal" data-bs-target="#modalDeleteTopic<?= $t['id'] ?>" style="width:30px; height:30px;">
                                                    <i class="bi bi-trash3" style="font-size:0.8rem;"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Title & Description -->
                                    <h5 class="fw-bold mb-2">
                                        <a href="<?= BASE_URL ?>index.php?url=forum/detail&id=<?= $t['id'] ?>" class="text-decoration-none text-dark hover-primary" style="line-height: 1.4;">
                                            <?= htmlspecialchars($t['judul']) ?>
                                        </a>
                                    </h5>
                                    <p class="text-secondary mb-3" style="font-size:0.92rem; line-height: 1.6; color: #475569;">
                                        <?= htmlspecialchars(substr($t['konten'], 0, 220)) ?><?= strlen($t['konten']) > 220 ? '...' : '' ?>
                                    </p>

                                    <!-- Attached Image Preview Showcase -->
                                    <?php if (!empty($t['gambar'])): 
                                        $imgPath = (file_exists(ROOT_PATH . 'assets/uploads/forum/' . $t['gambar'])) 
                                            ? BASE_URL . 'assets/uploads/forum/' . htmlspecialchars($t['gambar']) 
                                            : BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($t['gambar']);
                                    ?>
                                        <div class="mb-3">
                                            <div class="forum-image-preview-wrapper shadow-sm" style="display: inline-block; max-width: 440px; max-height: 260px; border-radius: 16px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openLightboxModal('<?= $imgPath ?>', '<?= htmlspecialchars(addslashes($t['judul'])) ?>')">
                                                <img src="<?= $imgPath ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($t['gambar']) ?>';" alt="Lampiran Gambar Forum" style="max-width: 100%; max-height: 240px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; border-radius: 14px;">
                                                <div class="forum-image-overlay">
                                                    <span><i class="bi bi-zoom-in me-1"></i> Perbesar Gambar</span>
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
                                        $rc = $t['reactions'] ?? ['love'=>0,'like'=>0,'laugh'=>0,'sad'=>0,'wow'=>0,'fire'=>0,'my_reaction'=>null];
                                    ?>
                                    <div class="pt-3 mb-3 border-top">
                                        <div class="reaction-bar d-flex flex-wrap align-items-center" data-forum-id="<?= $t['id'] ?>">
                                            <?php foreach ($reactionMap as $r): ?>
                                                <?php 
                                                    $count = $rc[$r['type']] ?? 0;
                                                    $isActive = (($rc['my_reaction'] ?? '') === $r['type']);
                                                    $btnClass = $isActive ? 'btn-primary-subtle border-primary text-primary fw-bold shadow-sm' : 'btn-light border-0 text-secondary';
                                                ?>
                                                <button type="button" class="btn btn-sm <?= $btnClass ?> rounded-pill px-3 py-1 me-2 mb-2 btn-emoji-react" 
                                                        onclick="ForumApp.toggleReaction(<?= $t['id'] ?>, '<?= $r['type'] ?>')" 
                                                        title="<?= $r['label'] ?>">
                                                    <span class="fs-6 me-1"><?= $r['emoji'] ?></span>
                                                    <span class="small fw-semibold"><?= $count > 0 ? $count : '' ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Card Action Footer -->
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                                        <a href="<?= BASE_URL ?>index.php?url=forum/detail&id=<?= $t['id'] ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="font-size:0.88rem;">
                                            <i class="bi bi-chat-text-fill"></i> <?= $t['total_replies'] ?> Balasan Diskusi
                                        </a>
                                        <?php if (!empty($t['nama_mapel'])): ?>
                                            <span class="badge bg-light text-primary border rounded-pill px-3 py-2 fw-semibold" style="font-size:0.8rem;">
                                                <i class="bi bi-journal-text me-1"></i><?= htmlspecialchars($t['nama_mapel']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($canDelete): ?>
                                    <!-- Modal Delete Topic -->
                                    <div class="modal fade" id="modalDeleteTopic<?= $t['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                                <div class="modal-body text-center p-4">
                                                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3">
                                                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-2">Hapus Topik Diskusi?</h6>
                                                    <p class="small text-muted mb-4">Apakah Anda yakin ingin menghapus topik <strong>"<?= htmlspecialchars($t['judul']) ?>"</strong>? Seluruh balasan komentar akan ikut terhapus secara permanen.</p>
                                                    <form action="<?= BASE_URL ?>index.php?url=forum" method="POST">
                                                        <?= Security::csrfField() ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="topic_id" value="<?= $t['id'] ?>">
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
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Sidebar Column: Forum Summary & Guidelines -->
            <div class="col-lg-4 col-xl-4">
                <div class="d-flex flex-column gap-4">
                    <!-- Community Stats Widget -->
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-bar-chart-fill text-primary"></i> Ringkasan Komunitas
                        </h6>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                        <i class="bi bi-chat-left-text-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0"><?= count($topics) ?></div>
                                        <div class="small text-muted" style="font-size:0.78rem;">Total Topik Aktif</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                                        <i class="bi bi-people-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">SMK Muthia Harapan</div>
                                        <div class="small text-muted" style="font-size:0.78rem;">Komunitas Akademik</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Discussion Etiquette & Guidelines Card -->
                    <div class="card border-0 rounded-4 shadow-sm bg-primary bg-opacity-10 p-4">
                        <h6 class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check fs-5"></i> Panduan Berdiskusi
                        </h6>
                        <ul class="small text-secondary ps-3 mb-0" style="line-height: 1.7; font-size: 0.85rem;">
                            <li>Gunakan bahasa yang santun, jelas, dan komunikatif.</li>
                            <li>Tuliskan pertanyaan dengan judul yang spesifik.</li>
                            <li>Sertakan foto/screenshot jika memerlukan solusi teknis.</li>
                            <li>Hormati perbedaan pendapat sesama anggota komunitas.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Add Topic -->
<div class="modal fade" id="modalAddTopic" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold modal-title d-flex align-items-center gap-2">
                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex">
                        <i class="bi bi-chat-left-quote-fill"></i>
                    </span>
                    Buat Topik Diskusi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=forum" method="POST" enctype="multipart/form-data">
                <div class="modal-body px-4 py-3">
                    <?= Security::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul Topik / Pertanyaan Utama <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control rounded-3 py-2" placeholder="Contoh: Pemahaman Dasar Prepared Statements di PHP MySQL" required>
                    </div>

                    <!-- Visibility Selection (Public vs Private) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sifat Akses Diskusi <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="visibility" id="visPublic" value="public" checked onchange="togglePrivateOptions(false)">
                                <label class="btn btn-outline-success w-100 py-2 rounded-3 text-start d-flex align-items-center gap-2" for="visPublic">
                                    <i class="bi bi-globe fs-5"></i>
                                    <div>
                                        <div class="fw-bold small">Publik</div>
                                        <div class="text-muted" style="font-size:0.7rem;">Dapat dilihat seluruh pengguna</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="visibility" id="visPrivate" value="private" onchange="togglePrivateOptions(true)">
                                <label class="btn btn-outline-warning w-100 py-2 rounded-3 text-start d-flex align-items-center gap-2" for="visPrivate">
                                    <i class="bi bi-lock-fill fs-5"></i>
                                    <div>
                                        <div class="fw-bold small">Privat</div>
                                        <div class="text-muted" style="font-size:0.7rem;">Terbatas untuk sasaran khusus</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Private Target Options -->
                    <div id="privateOptionsContainer" class="p-3 bg-light rounded-3 mb-3 border border-warning-subtle d-none">
                        <div class="fw-bold small text-dark mb-2"><i class="bi bi-person-gear text-warning me-1"></i> Batasi Sasaran Penerima Privat</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-muted">Peran Sasaran</label>
                                <select name="target_role" class="form-select form-select-sm rounded-3">
                                    <option value="all">Semua Peran Sasaran</option>
                                    <option value="guru">Khusus Guru / Pengajar</option>
                                    <option value="siswa">Khusus Siswa</option>
                                    <option value="kepala sekolah">Khusus Kepala Sekolah</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Kelas Sasaran (Opsional)</label>
                                <select name="target_kelas_id" class="form-select form-select-sm rounded-3">
                                    <option value="0">Semua Kelas</option>
                                    <?php if (!empty($classList)): ?>
                                        <?php foreach ($classList as $kls): ?>
                                            <option value="<?= $kls['id'] ?>"><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mata Pelajaran (Opsional)</label>
                        <select name="mapel_id" class="form-select rounded-3">
                            <option value="0">Umum / Semua Mapel</option>
                            <?php if (!empty($mapelList)): ?>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Isi Pertanyaan / Penjelasan Detail <span class="text-danger">*</span></label>
                            <button type="button" id="forumEmojiBtn" class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-secondary" title="Pilih Emoji">
                                <i class="bi bi-emoji-smile me-1"></i> Emoji
                            </button>
                        </div>

                        <!-- Forum Emoji Picker Popover Box -->
                        <div id="forumEmojiPopover" class="card shadow-lg border-0 rounded-4 position-absolute bottom-100 end-0 mb-2 d-none" style="width: 340px; z-index: 1060;">
                            <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                                <span class="small fw-bold"><i class="bi bi-emoji-smile me-1"></i> Pilih Emoji</span>
                                <button type="button" class="btn-close btn-close-white small" id="closeForumEmojiBtn"></button>
                            </div>
                            <div class="bg-light border-bottom d-flex justify-content-around p-1" id="forumEmojiCatTabs">
                                <button type="button" class="btn btn-sm btn-light border-0 forum-emoji-cat-tab active" data-cat="smileys" style="font-size: 1.1rem;">😀</button>
                                <button type="button" class="btn btn-sm btn-light border-0 forum-emoji-cat-tab" data-cat="gestures" style="font-size: 1.1rem;">👋</button>
                                <button type="button" class="btn btn-sm btn-light border-0 forum-emoji-cat-tab" data-cat="love" style="font-size: 1.1rem;">❤️</button>
                                <button type="button" class="btn btn-sm btn-light border-0 forum-emoji-cat-tab" data-cat="education" style="font-size: 1.1rem;">🎓</button>
                                <button type="button" class="btn btn-sm btn-light border-0 forum-emoji-cat-tab" data-cat="activities" style="font-size: 1.1rem;">🔥</button>
                            </div>
                            <div class="card-body p-2 overflow-auto" style="max-height: 220px; font-size: 1.3rem;">
                                <div class="d-flex flex-wrap gap-1" id="forumEmojiListContainer"></div>
                            </div>
                        </div>

                        <textarea name="konten" id="forumKontenInput" class="form-control rounded-3" rows="4" placeholder="Tuliskan detail pertanyaan atau uraian topik yang ingin didiskusikan..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lampiran Gambar Screenshot / Foto (Opsional)</label>
                        <input type="file" name="gambar" id="modalTopicImageInput" class="form-control rounded-3" accept="image/*" onchange="previewImageInput(this, 'modalTopicImagePreview', 'modalTopicImageContainer')">
                        <div id="modalTopicImageContainer" class="mt-2 d-none position-relative">
                            <img id="modalTopicImagePreview" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 180px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-0 m-1 p-1" onclick="clearImageInput('modalTopicImageInput', 'modalTopicImageContainer')">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Posting Topik</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<div class="modal fade lightbox-modal" id="globalLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg" style="background: rgba(15, 23, 42, 0.96) !important; color: #ffffff !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.15) !important;">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="modal-title text-white small fw-bold d-flex align-items-center gap-2" id="lightboxTitle">
                    <i class="bi bi-image text-warning me-1"></i> Lampiran Gambar
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3 p-md-4 d-flex align-items-center justify-content-center" style="min-height: 220px;">
                <img id="lightboxImage" src="" alt="Pratinjau Gambar" style="max-height: 75vh; max-width: 100%; width: auto; height: auto; object-fit: contain; margin: 0 auto; display: block; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.6);">
            </div>
        </div>
    </div>
</div>

<script>
function togglePrivateOptions(show) {
    const container = document.getElementById('privateOptionsContainer');
    if (container) {
        if (show) {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
        }
    }
}

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
    const btn = document.getElementById('forumEmojiBtn');
    const popover = document.getElementById('forumEmojiPopover');
    const closeBtn = document.getElementById('closeForumEmojiBtn');
    const container = document.getElementById('forumEmojiListContainer');
    const input = document.getElementById('forumKontenInput');
    const catTabs = document.querySelectorAll('.forum-emoji-cat-tab');

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
                <button type="button" class="btn btn-light btn-sm p-1 fs-5 border-0 forum-emoji-item" style="width: 36px; height: 36px; line-height: 1;">${e}</button>
            `).join('');

            container.querySelectorAll('.forum-emoji-item').forEach(item => {
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

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
