<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php 
$isAdminMonitoring = (strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <?php if ($isAdminMonitoring): ?>
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 shadow-sm d-flex align-items-center gap-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 5px solid #0284c7 !important;">
                <div class="bg-primary text-white p-2.5 rounded-3 shadow-xs">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-eye-fill me-1 text-primary"></i>Mode Monitoring Administrator (Pengawasan Guru)</h6>
                    <small class="text-secondary fw-medium">Secara hak akses, Administrator hanya berwenang **mengawasi / memantau (Monitoring Only)** data modul & materi pembelajaran. Admin dapat mengeklik **Preview** dan **Unduh** untuk memeriksa berkas, namun tidak dapat membuat, mengedit, atau menghapus materi milik Guru.</small>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-book-fill text-primary me-2"></i>Kelola Materi & Modul Pembelajaran</h4>
                <p class="text-muted small mb-0">Unggah, pratinjau (preview), edit, dan kelola file PDF, Word, PPT, Video MP4, atau Link Youtube.</p>
            </div>
            <?php if (!$isAdminMonitoring): ?>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMateri">
                    <i class="bi bi-cloud-upload me-1"></i> Unggah Materi Baru
                </button>
            <?php endif; ?>
        </div>

        <div class="card card-custom p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($materiList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Judul Materi</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas Target</th>
                            <th>Jenis File</th>
                            <th>Diupload Pada</th>
                            <th class="text-center" style="min-width: 260px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materiList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada materi pembelajaran yang diunggah.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $kelasMap = !empty($kelasList) ? array_column($kelasList, 'nama_kelas', 'id') : [];
                            ?>
                            <?php foreach ($materiList as $i => $m): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($m['judul']) ?></td>
                                    <td><?= htmlspecialchars($m['nama_mapel']) ?></td>
                                    <td>
                                        <?php
                                        $targetIds = !empty($m['kelas_ids']) ? array_map('intval', explode(',', $m['kelas_ids'])) : [(int)$m['kelas_id']];
                                        foreach ($targetIds as $tid):
                                            $kName = $kelasMap[$tid] ?? ($tid == $m['kelas_id'] ? $m['nama_kelas'] : '');
                                            if (!empty($kName)):
                                        ?>
                                            <span class="badge bg-info text-dark me-1 mb-1"><?= htmlspecialchars($kName) ?></span>
                                        <?php 
                                            endif;
                                        endforeach;
                                        ?>
                                    </td>
                                    <td><span class="badge bg-primary text-uppercase"><?= htmlspecialchars($m['jenis_file']) ?></span></td>
                                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></small></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                            <!-- Button Preview (Lihat Tanpa Unduh) -->
                                            <button class="btn btn-sm btn-info text-white px-2" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#modalPreviewMateri<?= $m['id'] ?>" title="Preview / Lihat Langsung">
                                                <i class="bi bi-eye me-1"></i> Preview
                                            </button>

                                            <?php if (!$isAdminMonitoring): ?>
                                                <!-- Button Edit -->
                                                <button class="btn btn-sm btn-warning text-dark px-2" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#modalEditMateri<?= $m['id'] ?>" title="Edit Materi">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </button>
                                            <?php endif; ?>

                                            <!-- Button Unduh / Link Direct -->
                                            <?php if (!empty($m['file_path'])): ?>
                                                <a href="<?= BASE_URL ?>assets/uploads/materi/<?= htmlspecialchars($m['file_path']) ?>" class="btn btn-sm btn-outline-success px-2" style="font-size:0.75rem;" download title="Unduh File">
                                                    <i class="bi bi-download me-1"></i> Unduh
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!$isAdminMonitoring): ?>
                                                <!-- Button Hapus -->
                                                <form action="<?= BASE_URL ?>index.php?url=guru/materi" method="POST" onsubmit="return confirm('Hapus materi pembelajaran ini?');" class="d-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2" style="font-size:0.75rem;" title="Hapus Materi">
                                                        <i class="bi bi-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

<!-- ALL MODALS PLACED OUTSIDE TABLE -->

<!-- Modal Add Materi -->
<div class="modal fade" id="modalAddMateri" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-cloud-upload text-primary me-2"></i>Unggah Materi Pembelajaran Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/materi" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Modul 1 Pemrograman Web PHP" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" class="form-select" required>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Jenis File / Konten <span class="text-danger">*</span></label>
                            <select name="jenis_file" class="form-select" required>
                                <option value="pdf">PDF Document</option>
                                <option value="doc">Word / Office Doc</option>
                                <option value="ppt">PowerPoint Presentation</option>
                                <option value="video">Video MP4 File</option>
                                <option value="youtube">YouTube Video Link</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Kelas Target (Bisa Pilih Lebih dari 1 Kelas) <span class="text-danger">*</span></label>
                                <div class="form-check me-0">
                                    <input class="form-check-input" type="checkbox" id="selectAllKelasAdd">
                                    <label class="form-check-label small fw-semibold text-primary" for="selectAllKelasAdd" style="cursor:pointer;">
                                        <i class="bi bi-check-all me-1"></i>Pilih Semua Kelas
                                    </label>
                                </div>
                            </div>
                            <div class="border rounded-3 p-2 bg-light" style="max-height: 170px; overflow-y: auto;">
                                <div class="row g-2">
                                    <?php foreach ($kelasList as $k): ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-check bg-white p-2 rounded-2 border d-flex align-items-center gap-2 shadow-xs">
                                                <input class="form-check-input kelas-add-checkbox ms-1" type="checkbox" name="kelas_ids[]" value="<?= $k['id'] ?>" id="kelas_add_<?= $k['id'] ?>">
                                                <label class="form-check-label text-dark small fw-medium mb-0 w-100" style="cursor:pointer;" for="kelas_add_<?= $k['id'] ?>">
                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Centang satu atau beberapa kelas target untuk mengunggah materi ini sekaligus.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Deskripsi / Penjelasan Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan petunjuk pembelajaran bagi siswa..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pilih File (PDF/Doc/PPT/MP4)</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Atau Link Youtube URL</label>
                            <input type="url" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Unggah Materi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Preview & Edit Materi -->
<?php foreach ($materiList as $m): ?>
    <!-- Modal Preview Materi (Melihat tanpa unduh) -->
    <div class="modal fade" id="modalPreviewMateri<?= $m['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold modal-title"><i class="bi bi-eye text-info me-2"></i>Pratinjau Materi: <?= htmlspecialchars($m['judul']) ?></h5>
                        <small class="text-muted">Mapel: <?= htmlspecialchars($m['nama_mapel']) ?> | Kelas: <?= htmlspecialchars($m['nama_kelas']) ?></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($m['deskripsi'])): ?>
                        <div class="p-3 bg-light rounded-3 mb-3 small border">
                            <strong>Keterangan Guru:</strong> <?= htmlspecialchars($m['deskripsi']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="ratio-container text-center bg-dark rounded-3 overflow-hidden p-2">
                        <?php if ($m['jenis_file'] === 'youtube' && !empty($m['youtube_url'])): ?>
                            <?php
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $m['youtube_url'], $matches);
                            $ytId = $matches[1] ?? '';
                            ?>
                            <?php if ($ytId): ?>
                                <iframe width="100%" height="520" src="https://www.youtube.com/embed/<?= $ytId ?>?autoplay=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius:8px;"></iframe>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($m['youtube_url']) ?>" target="_blank" class="btn btn-danger my-5"><i class="bi bi-youtube me-2"></i> Tonton di Youtube</a>
                            <?php endif; ?>

                        <?php elseif ($m['jenis_file'] === 'video' && !empty($m['file_path'])): ?>
                            <video src="<?= BASE_URL ?>assets/uploads/materi/<?= htmlspecialchars($m['file_path']) ?>" controls width="100%" style="max-height:520px; border-radius:8px;"></video>

                        <?php elseif ($m['jenis_file'] === 'pdf' && !empty($m['file_path'])): ?>
                            <iframe src="<?= BASE_URL ?>assets/uploads/materi/<?= htmlspecialchars($m['file_path']) ?>" width="100%" height="540px" style="border:none; border-radius:8px;"></iframe>

                        <?php elseif (!empty($m['file_path'])): ?>
                            <div class="py-5 text-white">
                                <i class="bi bi-file-earmark-word fs-1 text-warning mb-2 d-block"></i>
                                <h6 class="fw-bold mb-2"><?= htmlspecialchars($m['judul']) ?></h6>
                                <p class="small text-white-50">Pratinjau dokumen Microsoft Office / PPT dapat diakses melalui tombol di bawah.</p>
                                <a href="<?= BASE_URL ?>assets/uploads/materi/<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka File Dokumen
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup Pratinjau</button>
                    <?php if (!empty($m['file_path'])): ?>
                        <a href="<?= BASE_URL ?>assets/uploads/materi/<?= htmlspecialchars($m['file_path']) ?>" class="btn btn-success fw-bold" download>
                            <i class="bi bi-download me-1"></i> Unduh File
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Materi -->
    <div class="modal fade" id="modalEditMateri<?= $m['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Materi Pembelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=guru/materi" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Judul Materi <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($m['judul']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mapel_id" class="form-select" required>
                                    <?php foreach ($mapelList as $mp): ?>
                                        <option value="<?= $mp['id'] ?>" <?= $m['mapel_id'] == $mp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Jenis File / Konten <span class="text-danger">*</span></label>
                                <select name="jenis_file" class="form-select" required>
                                    <option value="pdf" <?= $m['jenis_file'] === 'pdf' ? 'selected' : '' ?>>PDF Document</option>
                                    <option value="doc" <?= $m['jenis_file'] === 'doc' ? 'selected' : '' ?>>Word / Office Doc</option>
                                    <option value="ppt" <?= $m['jenis_file'] === 'ppt' ? 'selected' : '' ?>>PowerPoint Presentation</option>
                                    <option value="video" <?= $m['jenis_file'] === 'video' ? 'selected' : '' ?>>Video MP4 File</option>
                                    <option value="youtube" <?= $m['jenis_file'] === 'youtube' ? 'selected' : '' ?>>YouTube Video Link</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Kelas Target (Bisa Pilih Lebih dari 1 Kelas) <span class="text-danger">*</span></label>
                                    <div class="form-check me-0">
                                        <input class="form-check-input select-all-edit" type="checkbox" id="selectAllKelasEdit<?= $m['id'] ?>" data-target="<?= $m['id'] ?>">
                                        <label class="form-check-label small fw-semibold text-primary" for="selectAllKelasEdit<?= $m['id'] ?>" style="cursor:pointer;">
                                            <i class="bi bi-check-all me-1"></i>Pilih Semua Kelas
                                        </label>
                                    </div>
                                </div>
                                <div class="border rounded-3 p-2 bg-light" style="max-height: 170px; overflow-y: auto;">
                                    <div class="row g-2">
                                        <?php
                                        $editTargetIds = !empty($m['kelas_ids']) ? array_map('intval', explode(',', $m['kelas_ids'])) : [(int)$m['kelas_id']];
                                        ?>
                                        <?php foreach ($kelasList as $k): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="form-check bg-white p-2 rounded-2 border d-flex align-items-center gap-2 shadow-xs">
                                                    <input class="form-check-input kelas-edit-checkbox-<?= $m['id'] ?> ms-1" type="checkbox" name="kelas_ids[]" value="<?= $k['id'] ?>" id="kelas_edit_<?= $m['id'] ?>_<?= $k['id'] ?>" <?= in_array((int)$k['id'], $editTargetIds) ? 'checked' : '' ?>>
                                                    <label class="form-check-label text-dark small fw-medium mb-0 w-100" style="cursor:pointer;" for="kelas_edit_<?= $m['id'] ?>_<?= $k['id'] ?>">
                                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Anda dapat memilih lebih dari 1 kelas untuk memperbarui atau menyebarkan materi ini ke kelas lain.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Deskripsi / Penjelasan Singkat</label>
                                <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($m['deskripsi'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Ganti File (Opsional)</label>
                                <input type="file" name="file" class="form-control">
                                <?php if (!empty($m['file_path'])): ?>
                                    <small class="text-muted">File saat ini: <?= htmlspecialchars($m['file_path']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Atau Link Youtube URL</label>
                                <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars($m['youtube_url'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- ADD MATERI SELECT ALL & VALIDATION ---
    const selectAllCheckbox = document.getElementById('selectAllKelasAdd');
    const kelasCheckboxes = document.querySelectorAll('.kelas-add-checkbox');
    const formAddMateri = document.querySelector('#modalAddMateri form');

    if (selectAllCheckbox && kelasCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            kelasCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
        });

        kelasCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const totalChecked = document.querySelectorAll('.kelas-add-checkbox:checked').length;
                selectAllCheckbox.checked = (totalChecked === kelasCheckboxes.length);
            });
        });
    }

    if (formAddMateri) {
        formAddMateri.addEventListener('submit', function(e) {
            const checkedClasses = document.querySelectorAll('.kelas-add-checkbox:checked');
            if (checkedClasses.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 Kelas Target terlebih dahulu!');
            }
        });
    }

    // --- EDIT MATERI SELECT ALL & VALIDATION ---
    document.querySelectorAll('.select-all-edit').forEach(function(selectAllCb) {
        const targetId = selectAllCb.getAttribute('data-target');
        const editCheckboxes = document.querySelectorAll('.kelas-edit-checkbox-' + targetId);

        selectAllCb.addEventListener('change', function() {
            editCheckboxes.forEach(cb => {
                cb.checked = selectAllCb.checked;
            });
        });

        editCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const totalChecked = document.querySelectorAll('.kelas-edit-checkbox-' + targetId + ':checked').length;
                selectAllCb.checked = (totalChecked === editCheckboxes.length);
            });
        });
    });

    document.querySelectorAll('form[action*="guru/materi"]').forEach(function(form) {
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && actionInput.value === 'update') {
            form.addEventListener('submit', function(e) {
                const checkedClasses = form.querySelectorAll('input[name="kelas_ids[]"]:checked');
                if (checkedClasses.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal 1 Kelas Target terlebih dahulu!');
                }
            });
        }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
