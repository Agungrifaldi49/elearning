<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar-event-fill text-primary me-2"></i>Manajemen Tahun Ajaran & Semester</h4>
            <p class="text-muted small mb-0">Kelola periode Tahun Ajaran dan status Semester berjalan (Ganjil / Genap) secara profesional & terpusat.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalAddTA">
            <i class="bi bi-plus-circle me-1"></i> Tambah Periode Baru
        </button>
    </div>

    <!-- Active Academic Year & Quick Stats Banner -->
    <?php
    $activeTa = null;
    foreach ($taList as $ta) {
        if (!empty($ta['is_active'])) {
            $activeTa = $ta;
            break;
        }
    }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-8">
            <div class="card-custom p-4 shadow-sm border-start border-4 border-primary h-100 bg-gradient text-dark">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px; height:52px;">
                        <i class="bi bi-calendar-check-fill fs-2"></i>
                    </div>
                    <div>
                        <div class="text-uppercase fw-bold text-muted small" style="font-size:0.75rem;">Periode Pembelajaran Aktif Berjalan</div>
                        <h5 class="fw-bold mb-1 text-primary">
                            Tahun Ajaran <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? ($activeTa['tahun'] ?? '2025/2026')) ?> — Semester <?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>
                        </h5>
                        <p class="text-muted small mb-0">Seluruh modul E-Learning (Materi, Tugas, CBT, Presensi, dan E-Rapor) terkunci otomatis pada periode ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card-custom p-4 shadow-sm border-start border-4 border-info h-100 text-center d-flex flex-column justify-content-center">
                <div class="text-muted small fw-bold">TOTAL PERIODE TERDAFTAR</div>
                <div class="fs-2 fw-bold text-info my-1"><?= count($taList) ?></div>
                <small class="text-muted">Master Data Periode Akademik</small>
            </div>
        </div>
    </div>

    <!-- Table Tahun Ajaran & Semester -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle <?= !empty($taList) ? 'datatable' : '' ?>">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Status Aktivasi</th>
                        <th>Tanggal Diperbarui / Dibuat</th>
                        <th style="width:200px;" class="text-center">Tindakan / Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($taList)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data Tahun Ajaran terdaftar. Klik tombol di atas untuk menambah.</td></tr>
                    <?php else: ?>
                        <?php foreach ($taList as $i => $ta): 
                            $taTahun = $ta['tahun_ajaran'] ?? ($ta['tahun'] ?? '-');
                            $taSem = $ta['semester'] ?? 'Ganjil';
                            $taActive = !empty($ta['is_active']) || (isset($ta['status']) && $ta['status'] === 'aktif');
                            $taDate = !empty($ta['created_at']) ? date('d M Y, H:i', strtotime($ta['created_at'])) : date('d M Y, H:i');
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold text-dark fs-6">
                                    <i class="bi bi-calendar3 me-2 text-primary"></i><?= htmlspecialchars($taTahun) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $taSem === 'Ganjil' ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle' ?> fw-bold px-3 py-2 fs-6">
                                        Semester <?= htmlspecialchars($taSem) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($taActive): ?>
                                        <span class="badge bg-success px-3 py-2 fs-6 shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> PERIODE AKTIF</span>
                                    <?php else: ?>
                                        <form action="<?= BASE_URL ?>index.php?url=admin/tahunAjaran" method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="set_active">
                                            <input type="hidden" name="id" value="<?= $ta['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success fw-bold">
                                                <i class="bi bi-power me-1"></i> Set Aktifkan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= $taDate ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-warning fw-bold"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditTA"
                                                data-id="<?= $ta['id'] ?>"
                                                data-tahun="<?= htmlspecialchars($taTahun, ENT_QUOTES) ?>"
                                                data-semester="<?= htmlspecialchars($taSem, ENT_QUOTES) ?>"
                                                data-active="<?= $taActive ? '1' : '0' ?>">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                        <?php if (!$taActive): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger fw-bold"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDeleteTA"
                                                    data-id="<?= $ta['id'] ?>"
                                                    data-name="<?= htmlspecialchars($taTahun . ' Semester ' . $taSem, ENT_QUOTES) ?>">
                                                <i class="bi bi-trash-fill me-1"></i> Hapus
                                            </button>
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

<!-- Modal Add TA -->
<div class="modal fade" id="modalAddTA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Periode Tahun Ajaran & Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/tahunAjaran" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-control" placeholder="Contoh: 2026/2027" required value="2026/2027">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="Ganjil">Ganjil (Semester 1 / 3 / 5)</option>
                            <option value="Genap">Genap (Semester 2 / 4 / 6)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="checkActive">
                        <label class="form-check-label small fw-bold" for="checkActive">Set Sebagai Periode Aktif Langsung</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Simpan Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit TA -->
<div class="modal fade" id="modalEditTA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Periode Tahun Ajaran & Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/tahunAjaran" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_ta_id">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" id="edit_ta_tahun" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Semester</label>
                        <select name="semester" id="edit_ta_semester" class="form-select" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_ta_active">
                        <label class="form-check-label small fw-bold" for="edit_ta_active">Periode Aktif Berjalan</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Perbarui Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete TA -->
<div class="modal fade" id="modalDeleteTA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>index.php?url=admin/tahunAjaran" method="POST">
                <div class="modal-body text-center p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_ta_id">
                    <i class="bi bi-exclamation-triangle text-danger display-4 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-1">Hapus Tahun Ajaran?</h6>
                    <p class="text-muted small mb-0" id="delete_ta_name"></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold"><i class="bi bi-trash-fill me-1"></i> Hapus Permanen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEditEl = document.getElementById('modalEditTA');
    if (modalEditEl) {
        modalEditEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('edit_ta_id').value = button.getAttribute('data-id') || '';
            document.getElementById('edit_ta_tahun').value = button.getAttribute('data-tahun') || '';
            document.getElementById('edit_ta_semester').value = button.getAttribute('data-semester') || 'Ganjil';
            document.getElementById('edit_ta_active').checked = (button.getAttribute('data-active') === '1');
        });
    }

    const modalDeleteEl = document.getElementById('modalDeleteTA');
    if (modalDeleteEl) {
        modalDeleteEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('delete_ta_id').value = button.getAttribute('data-id') || '';
            document.getElementById('delete_ta_name').innerText = button.getAttribute('data-name') || '';
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
