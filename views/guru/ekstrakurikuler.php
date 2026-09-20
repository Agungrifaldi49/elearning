<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bimbingan Ekstrakurikuler</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-activity text-primary me-2"></i>Bimbingan Ekstrakurikuler & Penilaian E-Rapor
                </h4>
                <p class="text-muted small mb-0">
                    Kelola siswa bimbingan ekstrakurikuler binaan Anda serta input nilai predikat dan deskripsi capaian untuk lembar E-Rapor Digital.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace small">
                    <i class="bi bi-calendar3 me-1"></i> TA <?= htmlspecialchars($activeTa['tahun'] ?? 'Aktif') ?> (<?= htmlspecialchars($activeSemester) ?>)
                </span>
            </div>
        </div>

        <?= FlashHelper::display() ?>

        <?php if (!$isPembimbing): ?>
            <!-- =========================================================================
                 TAMPILAN JIKA GURU BELUM DITUGASKAN OLEH ADMIN SEBAGAI PEMBIMBING
            ========================================================================= -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center my-4">
                <div class="mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 88px; height: 88px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-person-badge fs-1 text-white"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Anda Belum Ditugaskan Sebagai Pembimbing Ekstrakurikuler</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 580px; font-size: 0.95rem; line-height: 1.6;">
                    Fitur bimbingan dan penilaian ekstrakurikuler ini dikhususkan bagi Bapak/Ibu Guru yang telah ditugaskan secara resmi oleh <strong>Administrator Sekolah</strong> sebagai pembimbing kegiatan ekstrakurikuler.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-house-door me-1.5"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- =========================================================================
                 TAMPILAN GURU PEMBIMBING EKSTRAKURIKULER
            ========================================================================= -->

            <!-- Selector Tab Jika Guru Membimbing Lebih Dari 1 Ekskul -->
            <?php if (count($guidedEkskul) > 1): ?>
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-4">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-semibold me-2"><i class="bi bi-collection me-1"></i>Pilih Ekstrakurikuler:</span>
                        <div class="nav nav-pills gap-2" role="tablist">
                            <?php foreach ($guidedEkskul as $ge): ?>
                                <?php $isSelected = ((int)$ge['id'] === (int)$selectedId); ?>
                                <a href="<?= BASE_URL ?>index.php?url=guru/ekstrakurikuler&id=<?= $ge['id'] ?>" 
                                   class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $isSelected ? 'active bg-primary text-white shadow-sm' : 'bg-light text-dark' ?>">
                                    <i class="bi bi-trophy-fill me-1"></i><?= htmlspecialchars($ge['nama_ekskul']) ?>
                                    <span class="badge <?= $isSelected ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= (int)$ge['total_anggota'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($selectedEkskul): ?>
                <div class="row g-3 mb-4">
                    <!-- KARTU INFORMASI BIMBINGAN EKSTRAKURIKULER (KIRI) -->
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="d-flex align-items-center justify-content-center rounded-4 shadow-sm flex-shrink-0" style="width: 58px; height: 58px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                    <i class="bi bi-trophy-fill fs-3 text-white"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($selectedEkskul['nama_ekskul']) ?></h5>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 mt-1 small">
                                        <i class="bi bi-check-circle me-1"></i>Bimbingan Aktif
                                    </span>
                                </div>
                            </div>

                            <ul class="list-group list-group-flush small mb-3">
                                <li class="list-group-item px-0 py-2.5 d-flex justify-content-between">
                                    <span class="text-muted"><i class="bi bi-person-video3 me-1 text-primary"></i>Pembimbing:</span>
                                    <strong class="text-dark"><?= htmlspecialchars($guru['nama_lengkap'] ?? 'Guru Pembimbing') ?></strong>
                                </li>
                                <li class="list-group-item px-0 py-2.5 d-flex justify-content-between">
                                    <span class="text-muted"><i class="bi bi-calendar-check me-1 text-info"></i>Jadwal Rutin:</span>
                                    <strong class="text-dark"><?= htmlspecialchars($selectedEkskul['hari'] ?: '-') ?> (<?= htmlspecialchars($selectedEkskul['jam'] ?: '-') ?>)</strong>
                                </li>
                                <li class="list-group-item px-0 py-2.5 d-flex justify-content-between">
                                    <span class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i>Lokasi / Tempat:</span>
                                    <strong class="text-dark"><?= htmlspecialchars($selectedEkskul['tempat'] ?: '-') ?></strong>
                                </li>
                                <li class="list-group-item px-0 py-2.5 d-flex justify-content-between">
                                    <span class="text-muted"><i class="bi bi-people me-1 text-success"></i>Total Anggota:</span>
                                    <strong class="text-primary fs-6"><?= count($anggotaList) ?> Siswa</strong>
                                </li>
                            </ul>

                            <?php if (!empty($selectedEkskul['deskripsi'])): ?>
                                <div class="p-3 bg-light rounded-3 text-muted small mb-3 border">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-info-circle me-1 text-primary"></i>Keterangan Ekstrakurikuler:</strong>
                                    <?= nl2br(htmlspecialchars($selectedEkskul['deskripsi'])) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Form Tambah Anggota Siswa Binaan Manual -->
                            <div class="mt-auto border-top pt-3">
                                <h6 class="fw-bold text-dark small mb-2">
                                    <i class="bi bi-person-plus-fill text-primary me-1"></i> Daftarkan Siswa ke Ekskul Ini
                                </h6>
                                <p class="text-muted small mb-2" style="font-size: 0.78rem;">
                                    Siswa binaan juga dapat mendaftar mandiri via menu akun siswa masing-masing.
                                </p>
                                <form action="<?= BASE_URL ?>index.php?url=guru/ekstrakurikuler" method="POST" class="d-flex flex-column gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                    <input type="hidden" name="action" value="add_anggota_manual">
                                    <input type="hidden" name="ekskul_id" value="<?= $selectedEkskul['id'] ?>">
                                    
                                    <select name="siswa_id" class="form-select form-select-sm rounded-3" required>
                                        <option value="">-- Pilih Siswa --</option>
                                        <?php foreach ($availableSiswa as $as): ?>
                                            <option value="<?= $as['id'] ?>">
                                                <?= htmlspecialchars($as['nama_lengkap']) ?> (<?= htmlspecialchars($as['nama_kelas'] ?? 'Kelas -') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold">
                                        <i class="bi bi-plus-circle me-1"></i> Tambahkan Siswa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- FORM PENILAIAN PREDIKAT & DESKRIPSI CAPAIAN E-RAPOR (KANAN) -->
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-3 border-bottom">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">
                                        <i class="bi bi-pencil-square text-success me-1"></i> Penilaian Anggota untuk E-Rapor Digital
                                    </h5>
                                    <small class="text-muted">
                                        Nilai predikat & deskripsi capaian kompetensi ini akan otomatis tercantum pada <strong>Laporan Hasil Belajar (E-Rapor Digital)</strong> masing-masing siswa.
                                    </small>
                                </div>
                                <span class="badge bg-info-subtle text-dark border border-info-subtle px-3 py-1.5 rounded-pill small">
                                    <i class="bi bi-mortarboard me-1"></i> <?= count($anggotaList) ?> Peserta Terdaftar
                                </span>
                            </div>

                            <?php if (empty($anggotaList)): ?>
                                <div class="text-center py-5 text-muted">
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                        <i class="bi bi-people text-secondary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Siswa yang Terdaftar</h6>
                                    <p class="text-muted small mb-3" style="max-width: 460px; margin: 0 auto;">
                                        Siswa dapat mendaftar mandiri melalui akun siswa pada menu Kegiatan Ekstrakurikuler, atau Anda dapat menambahkannya melalui formulir pendaftaran di panel sebelah kiri.
                                    </p>
                                </div>
                            <?php else: ?>
                                <form action="<?= BASE_URL ?>index.php?url=guru/ekstrakurikuler" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                    <input type="hidden" name="action" value="save_nilai_deskripsi">
                                    <input type="hidden" name="ekskul_id" value="<?= $selectedEkskul['id'] ?>">

                                    <div class="alert alert-light border d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3 small text-muted">
                                        <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                                        <div>
                                            <strong>Panduan Pengisian:</strong> Berikan predikat capaian (Sangat Baik, Baik, Cukup, Kurang) dan tulis deskripsi capaian hasil pembinaan kegiatan (misal: kehadiran, keterampilan teknis, sportivitas, atau kepemimpinan siswa).
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-3" style="font-size: 0.83rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 35px;" class="text-center">No</th>
                                                    <th style="min-width: 170px;">Identitas Siswa</th>
                                                    <th style="width: 90px;">Kelas</th>
                                                    <th style="width: 140px;">Predikat</th>
                                                    <th style="min-width: 270px;">Deskripsi Capaian Hasil Belajar (E-Rapor)</th>
                                                    <th style="width: 45px;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($anggotaList as $idx => $ang): ?>
                                                    <tr>
                                                        <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                                        <td>
                                                            <strong class="text-dark d-block"><?= htmlspecialchars($ang['nama_lengkap']) ?></strong>
                                                            <small class="text-muted">NIS: <?= htmlspecialchars($ang['nis'] ?: ($ang['nisn'] ?: '-')) ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($ang['nama_kelas'] ?: '-') ?></span>
                                                        </td>
                                                        <td>
                                                            <select name="nilai[<?= $ang['id'] ?>][predikat]" class="form-select form-select-sm rounded-3 fw-semibold">
                                                                <option value="Sangat Baik" <?= ($ang['predikat'] ?? '') === 'Sangat Baik' ? 'selected' : '' ?>>Sangat Baik</option>
                                                                <option value="Baik" <?= ($ang['predikat'] ?? '') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                                                <option value="Cukup" <?= ($ang['predikat'] ?? '') === 'Cukup' ? 'selected' : '' ?>>Cukup</option>
                                                                <option value="Kurang" <?= ($ang['predikat'] ?? '') === 'Kurang' ? 'selected' : '' ?>>Kurang</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <textarea name="nilai[<?= $ang['id'] ?>][deskripsi]" rows="2" class="form-control form-control-sm rounded-3" placeholder="Contoh: Sangat aktif mengikuti kegiatan latihan, menunjukkan kedisiplinan dan penguasaan teknik dasar yang sangat baik."><?= htmlspecialchars($ang['nilai_deskripsi'] ?? '') ?></textarea>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" title="Keluarkan Siswa dari Ekskul Ini" onclick="confirmRemoveAnggota(<?= $selectedEkskul['id'] ?>, <?= $ang['siswa_id'] ?>, '<?= htmlspecialchars(addslashes($ang['nama_lengkap'])) ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>Pastikan seluruh nilai telah diinput dengan benar sebelum menyimpan.
                                        </small>
                                        <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                                            <i class="bi bi-check2-circle me-1"></i> Simpan Penilaian E-Rapor
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Form Tersembunyi untuk Hapus / Keluarkan Anggota Siswa -->
        <form id="formRemoveAnggota" action="<?= BASE_URL ?>index.php?url=guru/ekstrakurikuler" method="POST" style="display:none;">
            <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
            <input type="hidden" name="action" value="remove_anggota">
            <input type="hidden" name="ekskul_id" id="removeEkskulId" value="">
            <input type="hidden" name="siswa_id" id="removeSiswaId" value="">
        </form>
    </div>
</main>

<script>
function confirmRemoveAnggota(ekskulId, siswaId, namaSiswa) {
    if (confirm("Apakah Anda yakin ingin mengeluarkan siswa '" + namaSiswa + "' dari bimbingan ekstrakurikuler ini?")) {
        document.getElementById('removeEkskulId').value = ekskulId;
        document.getElementById('removeSiswaId').value = siswaId;
        document.getElementById('formRemoveAnggota').submit();
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
