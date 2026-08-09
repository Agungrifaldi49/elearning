<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-bounding-box-circles text-primary me-2"></i>Manajemen Kelas Virtual & Wali Kelas</h4>
            <p class="text-muted small mb-0">Daftar rombel kelas virtual, penentuan Wali Kelas, kode gabung kelas, dan anggota siswa.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahKelasModal">
            <i class="bi bi-plus-circle me-1"></i> Buat Kelas Virtual Baru
        </button>
    </div>

    <?php
    $db = Database::getConnection();
    try {
        $cols = $db->query("SHOW COLUMNS FROM kelas LIKE 'wali_kelas_id'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE kelas ADD COLUMN wali_kelas_id INT NULL");
        }
    } catch (Exception $e) {}

    $guruList = $db->query("SELECT * FROM guru ORDER BY nama_lengkap ASC")->fetchAll();

    $kelasList = $db->query("
        SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas, g.nip as nip_walikelas,
               (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
               (SELECT COUNT(*) FROM materi m WHERE m.kelas_id = k.id) as total_materi
        FROM kelas k
        LEFT JOIN jurusan j ON k.jurusan_id = j.id
        LEFT JOIN guru g ON k.wali_kelas_id = g.id
        ORDER BY k.tingkat ASC, k.nama_kelas ASC
    ")->fetchAll();
    ?>

    <!-- Kelas Virtual Cards Grid -->
    <div class="row g-4 mb-4">
        <?php if (empty($kelasList)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-bounding-box fs-1 d-block mb-2 text-secondary"></i>
                Belum ada kelas virtual. Klik "Buat Kelas Virtual Baru" untuk menambahkan.
            </div>
        <?php else: ?>
            <?php foreach ($kelasList as $k): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card-custom p-4 h-100 position-relative border-top border-4 border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-1">Tingkat <?= htmlspecialchars($k['tingkat'] ?? 'X') ?></span>
                            <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($k['nama_kelas']) ?></h5>
                        </div>
                        <span class="badge bg-success">Aktif</span>
                    </div>

                    <p class="text-muted small mb-3"><?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?></p>

                    <div class="p-3 bg-light rounded-3 mb-3 small">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted"><i class="bi bi-person-badge me-1"></i>Wali Kelas:</span>
                            <?php if (!empty($k['nama_walikelas'])): ?>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($k['nama_walikelas']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Belum Ditentukan</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Kode Akses Kelas:</span>
                            <code class="fw-bold text-primary">MH-<?= strtoupper(substr(md5($k['id']), 0, 6)) ?></code>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Anggota Siswa:</span>
                            <span class="fw-bold text-success"><?= $k['total_siswa'] ?> Siswa</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center gap-1 mt-auto pt-2">
                        <button class="btn btn-sm btn-outline-warning text-dark px-2" style="font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#modalWaliKelas<?= $k['id'] ?>">
                            <i class="bi bi-pencil-square me-1"></i> Atur Wali Kelas
                        </button>
                        <a href="<?= BASE_URL ?>index.php?url=admin/siswa&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary px-2" style="font-size:0.78rem;">
                            <i class="bi bi-people me-1"></i> Lihat Anggota
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</main>

<!-- Modals Atur Wali Kelas -->
<?php foreach ($kelasList as $k): ?>
    <div class="modal fade" id="modalWaliKelas<?= $k['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-person-badge text-warning me-2"></i>Penentuan Wali Kelas: <?= htmlspecialchars($k['nama_kelas']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/setWaliKelas" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="kelas_id" value="<?= $k['id'] ?>">

                        <p class="text-muted small mb-3">Pilih salah satu guru terdaftar sebagai Wali Kelas resmi untuk rombel <strong><?= htmlspecialchars($k['nama_kelas']) ?></strong> (<?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?>).</p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Wali Kelas</label>
                            <select name="guru_id" class="form-select" required>
                                <option value="">-- Belum Ada / Kosongkan Wali Kelas --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ($k['wali_kelas_id'] ?? 0) == $g['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nama_lengkap']) ?> (NIP: <?= htmlspecialchars($g['nip'] ?? '-') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Wali Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal Buat Kelas Virtual Baru -->
<div class="modal fade" id="tambahKelasModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Buat Kelas Virtual Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                <div class="modal-body pt-2">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="target" value="kelas">
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kelas / Rombel <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: X RPL 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tingkat Kelas <span class="text-danger">*</span></label>
                        <select name="tingkat" class="form-select" required>
                            <option value="X">Tingkat X (10)</option>
                            <option value="XI">Tingkat XI (11)</option>
                            <option value="XII">Tingkat XII (12)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jurusan / Kompetensi Keahlian <span class="text-danger">*</span></label>
                        <select name="jurusan_id" class="form-select" required>
                            <?php
                            $jurusanList = $db->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC")->fetchAll();
                            foreach ($jurusanList as $j):
                            ?>
                                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Wali Kelas (Opsional)</label>
                        <select name="wali_kelas_id" class="form-select">
                            <option value="">-- Pilih Wali Kelas (Bisa diatur nanti) --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>">
                                    <?= htmlspecialchars($g['nama_lengkap']) ?> (NIP: <?= htmlspecialchars($g['nip'] ?? '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Kelas Virtual</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
