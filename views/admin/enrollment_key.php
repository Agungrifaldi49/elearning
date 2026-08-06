<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-key-fill text-primary me-2"></i>Manajemen Kode Akses / Key Mapel (Multi-Teacher)</h4>
            <p class="text-muted small mb-0">Kelola Key / Password unik pendaftaran mata pelajaran per-guru pengampu agar siswa terdaftar secara teratur dan bebas bentrok.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalSetKey">
            <i class="bi bi-plus-circle me-1"></i> Set / Generate Key Mapel Baru
        </button>
    </div>

    <!-- Info Banner -->
    <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3">
        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:45px; height:45px;">
            <i class="bi bi-shield-lock-fill fs-4"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-dark">Pencegahan Bentrok Multi-Pengampu Mapel</h6>
            <small class="text-muted">Setiap mata pelajaran yang diampu oleh Guru tertentu memiliki <strong>Key / Password khusus</strong>. Siswa wajib memasukkan Key ini sekali di awal pendaftaran mapel agar materi, tugas, dan kuis tersinkronisasi presisi.</small>
        </div>
    </div>

    <!-- Table Key Mapel -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle <?= !empty($keyList) ? 'datatable' : '' ?>">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengampu</th>
                        <th>Rombel Kelas</th>
                        <th>Kode Akses / Key Mapel</th>
                        <th>Siswa Terdaftar</th>
                        <th style="width:140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keyList)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada Kode Akses Mapel yang dibuat. Klik tombol di atas untuk membuat key.</td></tr>
                    <?php else: ?>
                        <?php foreach ($keyList as $i => $k): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold text-dark fs-6">
                                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i><?= htmlspecialchars($k['nama_mapel']) ?>
                                    <small class="text-muted d-block font-monospace">(<?= htmlspecialchars($k['kode_mapel'] ?? 'MAPEL') ?>)</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($k['nama_guru']) ?></div>
                                    <small class="text-muted">NIP: <?= htmlspecialchars($k['nip'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold">
                                        <?= htmlspecialchars($k['nama_kelas'] ?? 'Semua Kelas') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="fs-6 fw-bold text-danger bg-danger-subtle px-3 py-1 rounded-3 border border-danger-subtle" style="letter-spacing:1px;">
                                            <?= htmlspecialchars($k['enrollment_key']) ?>
                                        </code>
                                        <button type="button" class="btn btn-sm btn-light border" title="Salin Key" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($k['enrollment_key'], ENT_QUOTES) ?>'); alert('Key <?= htmlspecialchars($k['enrollment_key'], ENT_QUOTES) ?> berhasil disalin!')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-3 py-2 fs-6">
                                        <i class="bi bi-people-fill me-1"></i><?= (int)$k['total_siswa'] ?> Siswa
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning fw-bold"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalSetKey"
                                            data-mapel="<?= $k['mapel_id'] ?>"
                                            data-guru="<?= $k['guru_id'] ?>"
                                            data-kelas="<?= $k['kelas_id'] ?? '' ?>"
                                            data-key="<?= htmlspecialchars($k['enrollment_key'], ENT_QUOTES) ?>">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Key
                                    </button>
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

<!-- Modal Set Key Mapel -->
<div class="modal fade" id="modalSetKey" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-key-fill text-primary me-2"></i>Set / Edit Key & Password Mapel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/enrollmentKey" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="save_key">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mata Pelajaran</label>
                        <select name="mapel_id" id="key_mapel_id" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Guru Pengampu</label>
                        <select name="guru_id" id="key_guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru Pengampu --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Spesifik Rombel Kelas (Opsional)</label>
                        <select name="kelas_id" id="key_kelas_id" class="form-select">
                            <option value="">-- Semua Kelas (Global Guru) --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Akses / Key Mapel (Passcode)</label>
                        <div class="input-group">
                            <input type="text" name="enrollment_key" id="key_enrollment_key" class="form-control text-uppercase fw-bold" placeholder="Contoh: WEB-GURU1-2026" required style="letter-spacing:1px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('key_enrollment_key').value = 'KEY-' + Math.floor(1000 + Math.random() * 9000)">
                                <i class="bi bi-shuffle me-1"></i> Acak
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Key ini akan diminta kepada siswa saat pendaftaran mapel pertama kali.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Simpan Key Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSetKey = document.getElementById('modalSetKey');
    if (modalSetKey) {
        modalSetKey.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button || !button.getAttribute('data-mapel')) return;
            document.getElementById('key_mapel_id').value = button.getAttribute('data-mapel') || '';
            document.getElementById('key_guru_id').value = button.getAttribute('data-guru') || '';
            document.getElementById('key_kelas_id').value = button.getAttribute('data-kelas') || '';
            document.getElementById('key_enrollment_key').value = button.getAttribute('data-key') || '';
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
