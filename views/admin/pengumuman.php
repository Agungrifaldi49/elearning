<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-megaphone-fill text-primary me-2"></i>Manajemen Pengumuman & Informasi Sekolah</h4>
            <p class="text-muted small mb-0">Terbitkan pengumuman resmi dan informasi penting secara serentak ke seluruh antarmuka (Dashboard Siswa, Guru, & Kepsek).</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalAddPengumuman">
            <i class="bi bi-plus-circle me-1"></i> Terbitkan Pengumuman Baru
        </button>
    </div>

    <!-- Table Pengumuman -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle <?= !empty($pengumumanList) ? 'datatable' : '' ?>">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Banner</th>
                        <th>Judul Pengumuman & Informasi</th>
                        <th>Target Penerima</th>
                        <th>Mode Tampilan</th>
                        <th>Penulis / Author</th>
                        <th>Tanggal Terbit</th>
                        <th style="width:140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengumumanList)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada pengumuman / informasi diterbitkan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pengumumanList as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td style="width:80px;">
                                    <?php if (!empty($p['banner'])): ?>
                                        <a href="<?= BASE_URL . htmlspecialchars($p['banner']) ?>" target="_blank" title="Klik untuk lihat gambar full">
                                            <img src="<?= BASE_URL . htmlspecialchars($p['banner']) ?>" 
                                                 alt="Banner" 
                                                 class="rounded-3 border shadow-xs" 
                                                 style="width: 70px; height: 45px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1 small" style="font-size:0.7rem;">
                                            <i class="bi bi-image text-secondary me-1"></i>Tanpa Banner
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($p['judul']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width:320px;">
                                        <?= htmlspecialchars(mb_strimwidth(strip_tags($p['isi']), 0, 90, '...')) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php 
                                    $targetMap = [
                                        'all' => ['label' => 'Semua Pengguna', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                        'siswa' => ['label' => 'Khusus Siswa', 'class' => 'bg-info-subtle text-info border border-info-subtle'],
                                        'guru' => ['label' => 'Khusus Guru', 'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                        'kepsek' => ['label' => 'Khusus Kepsek', 'class' => 'bg-warning-subtle text-dark border border-warning-subtle']
                                    ];
                                    $tInfo = $targetMap[$p['target_role']] ?? ['label' => $p['target_role'], 'class' => 'bg-secondary-subtle text-secondary'];
                                    ?>
                                    <span class="badge <?= $tInfo['class'] ?> fw-bold px-3 py-2 fs-6">
                                        <?= $tInfo['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['is_popup']): ?>
                                        <span class="badge bg-danger px-3 py-2 fs-6"><i class="bi bi-bell-fill me-1"></i> Pop-up Penting</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border px-3 py-2 fs-6">Card Informasi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-person-circle me-1 text-secondary"></i><?= htmlspecialchars($p['author'] ?? 'Admin') ?>
                                </td>
                                <td class="text-muted small"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-warning fw-bold" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditPengumuman"
                                                data-id="<?= $p['id'] ?>"
                                                data-judul="<?= htmlspecialchars($p['judul'], ENT_QUOTES) ?>"
                                                data-target="<?= htmlspecialchars($p['target_role'], ENT_QUOTES) ?>"
                                                data-isi="<?= htmlspecialchars($p['isi'], ENT_QUOTES) ?>"
                                                data-popup="<?= $p['is_popup'] ? '1' : '0' ?>"
                                                data-banner="<?= !empty($p['banner']) ? BASE_URL . htmlspecialchars($p['banner']) : '' ?>">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger fw-bold" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeletePengumuman"
                                                data-id="<?= $p['id'] ?>"
                                                data-name="<?= htmlspecialchars($p['judul'], ENT_QUOTES) ?>">
                                            <i class="bi bi-trash-fill me-1"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- Modal Add Pengumuman -->
<div class="modal fade" id="modalAddPengumuman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-megaphone-fill text-primary me-2"></i>Terbitkan Pengumuman / Informasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/pengumuman" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Pengumuman / Informasi <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Jadwal Ujian Akhir Semester Ganjil 2025/2026" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Target Sasaran Penerima</label>
                            <select name="target_role" class="form-select" required>
                                <option value="all">Semua Pengguna (Siswa, Guru, Kepsek, Admin)</option>
                                <option value="siswa">Khusus Siswa & Peserta Didik</option>
                                <option value="guru">Khusus Tenaga Pengajar (Guru)</option>
                                <option value="kepsek">Khusus Kepala Sekolah / Manajemen</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_popup" id="checkPopup">
                                <label class="form-check-label small fw-bold" for="checkPopup">Tampilkan sebagai Notifikasi Pop-up Penting</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                            <span>Gambar Banner Pengumuman</span>
                            <span class="badge bg-secondary-subtle text-secondary border">Opsional</span>
                        </label>
                        <input type="file" name="banner" class="form-control" accept="image/*" onchange="previewAddBanner(this)">
                        <small class="text-muted d-block mt-1">Unggah gambar header banner agar pengumuman tampil lebih atraktif & profesional (Maks. 10 MB). Ekstensi: JPG, PNG, WEBP, GIF.</small>
                        <div id="addBannerPreviewContainer" class="mt-2 d-none">
                            <img id="addBannerPreview" src="" class="img-fluid rounded-3 border shadow-xs w-100" style="max-height: 140px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Isi Pesan Pengumuman / Informasi <span class="text-danger">*</span></label>
                        <textarea name="isi" class="form-control" rows="5" placeholder="Tuliskan isi informasi pengumuman secara rinci dan jelas di sini..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-send-fill me-1"></i> Terbitkan Informasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengumuman -->
<div class="modal fade" id="modalEditPengumuman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Pengumuman / Informasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/pengumuman" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_p_id">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Pengumuman / Informasi <span class="text-danger">*</span></label>
                        <input type="text" name="judul" id="edit_p_judul" class="form-control" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Target Sasaran Penerima</label>
                            <select name="target_role" id="edit_p_target" class="form-select" required>
                                <option value="all">Semua Pengguna</option>
                                <option value="siswa">Khusus Siswa</option>
                                <option value="guru">Khusus Guru</option>
                                <option value="kepsek">Khusus Kepsek</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_popup" id="edit_p_popup">
                                <label class="form-check-label small fw-bold" for="edit_p_popup">Pop-up Penting</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                            <span>Gambar Banner Pengumuman</span>
                            <span class="badge bg-secondary-subtle text-secondary border">Opsional</span>
                        </label>
                        <div id="existingBannerContainer" class="mb-2 d-none">
                            <div class="p-2 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1 font-monospace" style="font-size:0.75rem;">Banner Saat Ini:</small>
                                <img id="existingBannerImg" src="" class="img-fluid rounded-3 border shadow-xs w-100 mb-2" style="max-height: 140px; object-fit: cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_banner" value="1" id="checkRemoveBanner">
                                    <label class="form-check-label small text-danger fw-bold" for="checkRemoveBanner">
                                        <i class="bi bi-trash me-1"></i> Hapus Gambar Banner Ini
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="file" name="banner" class="form-control" accept="image/*" onchange="previewEditBanner(this)">
                        <small class="text-muted d-block mt-1">Pilih file gambar baru jika ingin mengganti banner (Maks. 10 MB).</small>
                        <div id="editBannerPreviewContainer" class="mt-2 d-none">
                            <img id="editBannerPreview" src="" class="img-fluid rounded-3 border shadow-xs w-100" style="max-height: 140px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Isi Pesan Pengumuman / Informasi <span class="text-danger">*</span></label>
                        <textarea name="isi" id="edit_p_isi" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Perbarui Informasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Pengumuman -->
<div class="modal fade" id="modalDeletePengumuman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>index.php?url=admin/pengumuman" method="POST">
                <div class="modal-body text-center p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_p_id">
                    <i class="bi bi-exclamation-triangle text-danger display-4 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-1">Hapus Pengumuman?</h6>
                    <p class="text-muted small mb-0" id="delete_p_name"></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold"><i class="bi bi-trash-fill me-1"></i> Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewAddBanner(input) {
    const container = document.getElementById('addBannerPreviewContainer');
    const preview = document.getElementById('addBannerPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.classList.add('d-none');
    }
}

function previewEditBanner(input) {
    const container = document.getElementById('editBannerPreviewContainer');
    const preview = document.getElementById('editBannerPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalEditEl = document.getElementById('modalEditPengumuman');
    if (modalEditEl) {
        modalEditEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('edit_p_id').value = button.getAttribute('data-id') || '';
            document.getElementById('edit_p_judul').value = button.getAttribute('data-judul') || '';
            document.getElementById('edit_p_target').value = button.getAttribute('data-target') || 'all';
            document.getElementById('edit_p_isi').value = button.getAttribute('data-isi') || '';
            document.getElementById('edit_p_popup').checked = (button.getAttribute('data-popup') === '1');

            const bannerUrl = button.getAttribute('data-banner') || '';
            const existingContainer = document.getElementById('existingBannerContainer');
            const existingImg = document.getElementById('existingBannerImg');
            const checkRemove = document.getElementById('checkRemoveBanner');
            const editPreviewContainer = document.getElementById('editBannerPreviewContainer');
            
            if (checkRemove) checkRemove.checked = false;
            if (editPreviewContainer) editPreviewContainer.classList.add('d-none');

            if (bannerUrl) {
                existingImg.src = bannerUrl;
                existingContainer.classList.remove('d-none');
            } else {
                existingContainer.classList.add('d-none');
            }
        });
    }

    const modalDeleteEl = document.getElementById('modalDeletePengumuman');
    if (modalDeleteEl) {
        modalDeleteEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('delete_p_id').value = button.getAttribute('data-id') || '';
            document.getElementById('delete_p_name').innerText = button.getAttribute('data-name') || '';
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
