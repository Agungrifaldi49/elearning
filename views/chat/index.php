<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<script src="<?= BASE_URL ?>assets/js/chat.js?v=<?= time() ?>"></script>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Pesan Realtime Polling</h4>
                <p class="text-muted small mb-0">Komunikasi langsung 1-on-1 antar pengguna, filter kontak per Rombel Kelas & Guru, serta manajemen edit & hapus chat.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-2">
                    <i class="bi bi-shield-lock-fill text-warning me-1"></i> Pesan Otomatis Hilang 7 Hari
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                    <i class="bi bi-broadcast me-1"></i> Polling Realtime Aktif
                </span>
            </div>
        </div>

        <div class="card card-custom p-0 overflow-hidden shadow-sm border-0 rounded-4">
            <div class="row g-0">
                
                <!-- Contacts List Column (Left Panel) -->
                <div class="col-12 col-md-5 col-lg-4 border-end bg-white">
                    <div class="p-3 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Kontak Pengguna</h6>
                            <span class="badge bg-primary rounded-pill" id="contactCountBadge"><?= count($contacts) ?> Kontak</span>
                        </div>

                        <!-- Search Bar -->
                        <div class="position-relative mb-2">
                            <input type="text" id="chatSearchInput" class="form-control form-control-sm ps-5 rounded-pill shadow-sm" placeholder="Cari nama, NIS, NIP, kelas, mapel...">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        </div>

                        <!-- Filter Dropdowns (Kelas & Role/Mapel) -->
                        <div class="row g-1">
                            <div class="col-6">
                                <select id="filterRole" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Peran</option>
                                    <option value="guru">Guru / Pengajar</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="administrator">Administrator</option>
                                    <option value="kepala sekolah">Kepala Sekolah</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="filterKelas" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach ($classList as $k): ?>
                                        <option value="<?= strtolower(htmlspecialchars($k['nama_kelas'])) ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Contact List Items -->
                    <div class="list-group list-group-flush overflow-auto" id="contactListContainer" style="max-height: 540px;">
                        <?php if (empty($contacts)): ?>
                            <div class="p-4 text-center text-muted small">Belum ada kontak tersedia.</div>
                        <?php else: ?>
                            <?php foreach ($contacts as $c): 
                                $isSel = ($activeContactId == $c['id']);
                                $avUrl = $c['avatar_url'] ?? '';
                                $hasAvatar = !empty($avUrl);
                                $avatarSrc = $avUrl;
                                
                                $roleLower = strtolower($c['role_name'] ?? '');
                                $kelasName = htmlspecialchars($c['nama_kelas'] ?? '');
                                $mapelName = htmlspecialchars($c['mata_pelajaran'] ?? '');
                            ?>
                                <a href="<?= BASE_URL ?>index.php?url=chat&with=<?= $c['id'] ?>" 
                                   class="list-group-item list-group-item-action p-3 contact-item <?= $isSel ? 'bg-primary bg-opacity-10 border-start border-4 border-primary' : '' ?>"
                                   data-contact-id="<?= $c['id'] ?>"
                                   data-name="<?= strtolower(htmlspecialchars($c['full_name'])) ?>"
                                   data-role="<?= $roleLower ?>"
                                   data-kelas="<?= strtolower($kelasName) ?>"
                                   data-mapel="<?= strtolower($mapelName) ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative flex-shrink-0">
                                            <?php if ($hasAvatar): ?>
                                                <img src="<?= $avatarSrc ?>" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm" style="width:42px; height:42px;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:42px; height:42px; font-size: 1rem;">
                                                    <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (($c['unread_count'] ?? 0) > 0): ?>
                                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="w-100 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="fw-bold mb-0 text-truncate text-dark small"><?= htmlspecialchars($c['full_name']) ?></h6>
                                                <span class="badge bg-secondary opacity-75" style="font-size:0.65rem;"><?= htmlspecialchars($c['role_name']) ?></span>
                                            </div>

                                            <!-- Contextual Subtitle: Kelas for Siswa, Mapel for Guru -->
                                            <?php if (!empty($kelasName)): ?>
                                                <div class="mb-1"><span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:0.68rem;"><i class="bi bi-bounding-box-circles me-1"></i><?= $kelasName ?></span></div>
                                            <?php elseif (!empty($mapelName)): ?>
                                                <div class="mb-1 text-truncate"><span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.68rem;"><i class="bi bi-journal-bookmark me-1"></i><?= $mapelName ?></span></div>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted text-truncate d-block" style="font-size:0.75rem;">
                                                    <?= htmlspecialchars($c['last_message'] ?? 'Klik untuk mengobrol...') ?>
                                                </small>
                                                <?php if (($c['unread_count'] ?? 0) > 0): ?>
                                                    <span class="badge bg-danger rounded-pill ms-1" style="font-size:0.65rem;"><?= $c['unread_count'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat Messages Column (Right Panel) -->
                <div class="col-12 col-md-7 col-lg-8 d-flex flex-column bg-light">
                    <?php if ($activeContactId > 0 && !empty($activeContactInfo)): 
                        $actAvUrl = $activeContactInfo['avatar_url'] ?? '';
                        $actHasAvatar = !empty($actAvUrl);
                        $actAvatarSrc = $actAvUrl;
                    ?>
                        <!-- Active Chat Top Header -->
                        <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($actHasAvatar): ?>
                                    <img src="<?= $actAvatarSrc ?>" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm" style="width:42px; height:42px;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:42px; height:42px;">
                                        <?= strtoupper(substr($activeContactInfo['full_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($activeContactInfo['full_name']) ?></h6>
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="badge bg-secondary" style="font-size:0.68rem;"><?= htmlspecialchars($activeContactInfo['role_name']) ?></span>
                                        <?php if (!empty($activeContactInfo['nama_kelas'])): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:0.68rem;"><?= htmlspecialchars($activeContactInfo['nama_kelas']) ?></span>
                                        <?php elseif (!empty($activeContactInfo['mata_pelajaran'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-truncate" style="font-size:0.68rem; max-width:200px;"><?= htmlspecialchars($activeContactInfo['mata_pelajaran']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success small border border-success-subtle"><i class="bi bi-record-fill me-1 text-success"></i> Terhubung</span>
                        </div>

                        <!-- Chat Messages Box -->
                        <div class="p-4 chat-box flex-grow-1 overflow-auto" id="chatBox" style="min-height: 400px; max-height: 480px;">
                            <?php foreach ($messages as $msg): 
                                $isMe = ($msg['sender_id'] == $user['id']);
                                $isDeletedEveryone = ((int)($msg['is_deleted_everyone'] ?? 0) === 1);
                                $isEdited = ((int)($msg['is_edited'] ?? 0) === 1);
                                $time = date('H:i', strtotime($msg['created_at']));
                            ?>
                                <?php if ($isDeletedEveryone): ?>
                                    <div class="chat-bubble <?= $isMe ? 'sent' : 'received' ?> bg-light text-muted border border-dashed shadow-none" data-msg-id="<?= $msg['id'] ?>">
                                        <div class="fst-italic small text-muted"><i class="bi bi-slash-circle me-1 text-danger"></i> Pesan ini telah dihapus</div>
                                        <div style="font-size: 0.68rem; opacity: 0.6; text-align: right; margin-top: 4px;"><?= $time ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="chat-bubble <?= $isMe ? 'sent' : 'received' ?>" data-msg-id="<?= $msg['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div class="msg-content flex-grow-1"><?= htmlspecialchars($msg['message']) ?></div>
                                            <div class="dropdown ms-1 flex-shrink-0">
                                                <button class="btn btn-link btn-sm p-0 text-reset opacity-50 dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi Pesan">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 0.82rem;">
                                                    <li>
                                                        <a class="dropdown-item py-1 text-danger" href="javascript:void(0)" onclick="ChatApp.deleteMessageForMe(<?= $msg['id'] ?>)">
                                                            <i class="bi bi-trash me-2"></i> Hapus untuk Saya
                                                        </a>
                                                    </li>
                                                    <?php if ($isMe): ?>
                                                        <li>
                                                            <a class="dropdown-item py-1 text-danger fw-semibold" href="javascript:void(0)" onclick="ChatApp.deleteMessageForEveryone(<?= $msg['id'] ?>)">
                                                                <i class="bi bi-slash-circle me-2"></i> Hapus untuk Semua Orang
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end align-items-center mt-1 opacity-75" style="font-size: 0.68rem;">
                                            <span><?= $time ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Chat Input Form -->
                        <div class="p-3 border-top bg-white">
                            <form id="chatForm" class="d-flex gap-2">
                                <?= Security::csrfField() ?>
                                <input type="hidden" id="receiverId" value="<?= $activeContactId ?>">
                                <input type="text" id="messageInput" class="form-control rounded-pill px-4" placeholder="Tuliskan pesan percakapan di sini..." required autocomplete="off">
                                <button type="submit" class="btn btn-primary rounded-circle p-2 flex-shrink-0" style="width:42px; height:42px;" title="Kirim Pesan">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </form>
                        </div>

                    <?php else: ?>
                        <!-- Blank State -->
                        <div class="d-flex align-items-center justify-content-center flex-column h-100 p-5 text-center text-muted" style="min-height: 480px;">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-4 mb-3">
                                <i class="bi bi-chat-dots-fill display-4"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Pilih Kontak Percakapan</h5>
                            <p class="small text-muted mb-0">Gunakan kolom pencarian atau filter kelas/guru di sebelah kiri untuk menemukan pengguna.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</main>

<!-- Client-side Interactive Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('chatSearchInput');
    const filterRole = document.getElementById('filterRole');
    const filterKelas = document.getElementById('filterKelas');
    const contactItems = document.querySelectorAll('.contact-item');
    const countBadge = document.getElementById('contactCountBadge');

    function filterContacts() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const roleVal = filterRole ? filterRole.value.toLowerCase().trim() : '';
        const kelasVal = filterKelas ? filterKelas.value.toLowerCase().trim() : '';

        let visibleCount = 0;

        contactItems.forEach(function(item) {
            const name = item.getAttribute('data-name') || '';
            const role = item.getAttribute('data-role') || '';
            const kelas = item.getAttribute('data-kelas') || '';
            const mapel = item.getAttribute('data-mapel') || '';

            const matchQuery = (query === '' || name.includes(query) || kelas.includes(query) || mapel.includes(query) || role.includes(query));
            const matchRole = (roleVal === '' || role === roleVal);
            const matchKelas = (kelasVal === '' || kelas.includes(kelasVal));

            if (matchQuery && matchRole && matchKelas) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (countBadge) {
            countBadge.textContent = visibleCount + ' Kontak';
        }
    }

    if (searchInput) searchInput.addEventListener('keyup', filterContacts);
    if (filterRole) filterRole.addEventListener('change', filterContacts);
    if (filterKelas) filterKelas.addEventListener('change', filterContacts);
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
