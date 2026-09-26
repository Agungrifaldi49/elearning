<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-2 px-md-4 py-3">
<div class="container-fluid px-1 px-md-2">

    <!-- 1. Executive Header -->
    <div class="card-custom p-3 p-md-4 mb-4 border-start border-4 border-md-5 border-warning shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-warning-subtle text-warning text-dark rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                    <i class="bi bi-diagram-3-fill fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold mb-0 text-dark page-title">Monitoring Perangkat Ajar Guru (CP, TP & KKTP)</h4>
                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small"><i class="bi bi-shield-check me-1"></i>Supervisi Kurikulum Merdeka</span>
                    </div>
                    <p class="text-muted small mb-0 page-subtitle">Pengawasan kelengkapan administrasi ajar dewan guru: Capaian Pembelajaran (CP), Tujuan Pembelajaran (TP), dan Kriteria Ketercapaian (KKTP/Asesmen).</p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan
                </button>
                <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringPerangkatAjar" class="btn btn-warning text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </a>
            </div>
        </div>
    </div>

    <!-- 2. KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-custom p-3 border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size:0.75rem;">Total Dewan Guru</span>
                    <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-people-fill"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= count($rawGuruList) ?> <span class="fs-6 fw-normal text-muted">Guru</span></h3>
                <small class="text-muted" style="font-size:0.75rem;">Pengampu Mapel Terdaftar</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-custom p-3 border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size:0.75rem;">Perangkat Lengkap</span>
                    <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-circle-fill"></i></span>
                </div>
                <h3 class="fw-bold text-success mb-0"><?= $totalLengkap ?> <span class="fs-6 fw-normal text-muted">Guru</span></h3>
                <small class="text-success fw-medium" style="font-size:0.75rem;">CP, TP & KKTP Terisi</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-custom p-3 border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size:0.75rem;">Belum Lengkap</span>
                    <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <h3 class="fw-bold text-warning text-dark mb-0"><?= $totalSebagian ?> <span class="fs-6 fw-normal text-muted">Guru</span></h3>
                <small class="text-muted" style="font-size:0.75rem;">Sebagian CP/TP Belum Tuntas</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-custom p-3 border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size:0.75rem;">Belum Mengisi</span>
                    <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-exclamation-triangle-fill"></i></span>
                </div>
                <h3 class="fw-bold text-danger mb-0"><?= $totalBelum ?> <span class="fs-6 fw-normal text-muted">Guru</span></h3>
                <small class="text-danger fw-medium" style="font-size:0.75rem;">Perlu Pembinaan Kepsek</small>
            </div>
        </div>
    </div>

    <!-- Metric Badges Secondary -->
    <div class="card card-custom p-3 mb-4 border-0 shadow-sm rounded-4 bg-light">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-steps text-warning fs-5"></i>
                <span class="fw-bold small text-dark">Total Perangkat Ajar Sekolah:</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill"><i class="bi bi-diagram-2 me-1"></i><?= $globalCp ?> Capaian Pembelajaran (CP)</span>
                <span class="badge bg-info text-dark px-3 py-1.5 rounded-pill"><i class="bi bi-bullseye me-1"></i><?= $globalTp ?> Tujuan Pembelajaran (TP)</span>
                <span class="badge bg-success text-white px-3 py-1.5 rounded-pill"><i class="bi bi-card-checklist me-1"></i><?= $globalKktp ?> Kriteria KKTP Terkonfigurasi</span>
            </div>
        </div>
    </div>

    <!-- 3. Filter & Search Controls -->
    <div class="card card-custom p-3 mb-4 border-0 shadow-sm rounded-4 bg-white">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-center">
            <input type="hidden" name="url" value="kepsek/monitoringPerangkatAjar">
            
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control border-start-0 ps-0" placeholder="Cari nama guru atau NIP...">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Semua Status Keterisian</option>
                    <option value="lengkap" <?= $filterStatus === 'lengkap' ? 'selected' : '' ?>>Lengkap (CP + TP + KKTP)</option>
                    <option value="sebagian" <?= $filterStatus === 'sebagian' ? 'selected' : '' ?>>Sebagian (Belum Selesai)</option>
                    <option value="belum" <?= $filterStatus === 'belum' ? 'selected' : '' ?>>Belum Mengisi (0 CP)</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="guru_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Guru Pengampu</option>
                    <?php foreach ($allGuruDropdown as $ag): ?>
                        <option value="<?= $ag['id'] ?>" <?= $filterGuruId == $ag['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag['nama_lengkap']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold rounded-3">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringPerangkatAjar" class="btn btn-sm btn-outline-secondary rounded-3" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- 4. Main Teacher Perangkat Ajar Table -->
    <div class="card card-custom border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-table me-2 text-primary"></i>Matriks Perangkat Ajar Dewan Guru</h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">Menampilkan <?= count($teacherList) ?> dari <?= count($rawGuruList) ?> Guru</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light text-secondary text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
                    <tr>
                        <th class="ps-3 ps-md-4 text-center" style="width: 50px;">No</th>
                        <th>Profil Guru</th>
                        <th>Mata Pelajaran & Rombel</th>
                        <th class="text-center" style="width: 110px;">Capaian (CP)</th>
                        <th class="text-center" style="width: 110px;">Tujuan (TP)</th>
                        <th class="text-center" style="width: 110px;">KKTP / Asesmen</th>
                        <th class="text-center" style="width: 130px;">Status Supervisi</th>
                        <th class="text-end pe-3 pe-md-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teacherList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                Tidak ditemukan data guru sesuai kriteria pencarian dan filter yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($teacherList as $t): ?>
                            <tr>
                                <td class="ps-3 ps-md-4 text-center text-muted fw-bold"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <?php if (!empty($t['foto'])): ?>
                                            <img src="<?= BASE_URL ?>uploads/guru/<?= htmlspecialchars($t['foto']) ?>" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;" alt="Foto">
                                        <?php else: ?>
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                <?= strtoupper(substr($t['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($t['nama_lengkap']) ?></div>
                                            <div class="text-muted small" style="font-size:0.75rem;">
                                                NIP: <?= !empty($t['nip']) ? htmlspecialchars($t['nip']) : '-' ?>
                                                <?php if (!empty($t['email'])): ?>
                                                    &bull; <i class="bi bi-envelope"></i> <?= htmlspecialchars($t['email']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($t['mapel_ampu'])): ?>
                                        <div class="fw-semibold text-dark mb-0.5"><?= htmlspecialchars($t['mapel_ampu']) ?></div>
                                        <small class="text-muted" style="font-size:0.74rem;"><i class="bi bi-building me-1"></i><?= htmlspecialchars($t['kelas_ampu'] ?: 'Rombel KBM') ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Belum Terjadwal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $t['total_cp'] > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border' ?> px-2.5 py-1 fs-6 fw-bold">
                                        <?= (int)$t['total_cp'] ?> CP
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $t['total_tp'] > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-light text-muted border' ?> px-2.5 py-1 fs-6 fw-bold">
                                        <?= (int)$t['total_tp'] ?> TP
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $t['total_kktp'] > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' ?> px-2.5 py-1 fs-6 fw-bold">
                                        <?= (int)$t['total_kktp'] ?> KKTP
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($t['status_perangkat'] === 'lengkap'): ?>
                                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill small">
                                            <i class="bi bi-check-circle-fill me-1"></i>Lengkap
                                        </span>
                                    <?php elseif ($t['status_perangkat'] === 'sebagian'): ?>
                                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small">
                                            <i class="bi bi-hourglass-split me-1"></i>Sebagian
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill small">
                                            <i class="bi bi-x-circle-fill me-1"></i>Belum Ada
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 pe-md-4">
                                    <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringPerangkatAjar&detail_guru_id=<?= $t['id'] ?>#detailSection" class="btn btn-sm btn-outline-primary rounded-3 px-2.5 py-1">
                                        <i class="bi bi-eye-fill me-1"></i> Rincian CP/TP
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Detail Perangkat Ajar Drill-Down (Jika guru diklik) -->
    <?php if ($detailGuru): ?>
    <div class="card card-custom border-0 shadow-sm rounded-4 bg-white mb-4" id="detailSection">
        <div class="card-header bg-primary text-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-white"><i class="bi bi-folder2-open me-2"></i>Rincian Perangkat Ajar: <?= htmlspecialchars($detailGuru['nama_lengkap']) ?></h5>
                <small class="text-white-50">NIP: <?= !empty($detailGuru['nip']) ? htmlspecialchars($detailGuru['nip']) : '-' ?> &bull; Email: <?= htmlspecialchars($detailGuru['email'] ?? '-') ?></small>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringPerangkatAjar" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                <i class="bi bi-x-lg me-1"></i> Tutup Rincian
            </a>
        </div>

        <div class="card-body p-3 p-md-4">
            <?php if (empty($detailCpList)): ?>
                <div class="alert alert-warning border-0 rounded-4 p-4 text-center mb-0">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-1 d-block mb-2"></i>
                    <h6 class="fw-bold text-dark">Guru Ini Belum Menyusun Capaian Pembelajaran (CP)</h6>
                    <p class="small text-muted mb-0">Guru yang bersangkutan belum menginputkan rumusan elemen CP maupun Tujuan Pembelajaran (TP) pada modul Kurikulum Merdeka.</p>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <span class="fw-bold text-dark">Tersedia <?= count($detailCpList) ?> Capaian Pembelajaran (CP) Terdaftar:</span>
                    <span class="badge bg-light text-muted border">Klik akordeon untuk memeriksa rincian TP & KKTP</span>
                </div>

                <div class="accordion" id="accordionDetailCp">
                    <?php foreach ($detailCpList as $idx => $cp): ?>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-xs">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?> py-3 px-3 px-md-4 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCp<?= $cp['id'] ?>">
                                    <div class="d-flex align-items-center gap-2 flex-wrap text-start w-100 me-2">
                                        <span class="badge bg-primary text-white"><?= htmlspecialchars($cp['kode_cp']) ?></span>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle"><?= htmlspecialchars($cp['nama_mapel']) ?></span>
                                        <span class="text-dark fw-bold"><?= htmlspecialchars($cp['elemen'] ?: 'Elemen Kejuruan') ?></span>
                                        <span class="badge bg-light text-muted border ms-auto"><?= count($cp['tp_list'] ?? []) ?> Target TP</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseCp<?= $cp['id'] ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#accordionDetailCp">
                                <div class="accordion-body bg-white p-3 p-md-4">
                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-file-earmark-text me-1"></i>Deskripsi Capaian Pembelajaran (CP):</h6>
                                        <p class="small text-dark mb-0 lh-base"><?= nl2br(htmlspecialchars($cp['deskripsi'])) ?></p>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-2.5 small text-uppercase"><i class="bi bi-bullseye text-info me-1"></i>Tujuan Pembelajaran (TP) & Kriteria KKTP:</h6>
                                    
                                    <?php if (empty($cp['tp_list'])): ?>
                                        <div class="alert alert-light border small text-muted mb-0">Belum ada Tujuan Pembelajaran (TP) yang ditautkan pada CP ini.</div>
                                    <?php else: ?>
                                        <div class="row g-3">
                                            <?php foreach ($cp['tp_list'] as $tp): ?>
                                                <div class="col-12 col-lg-6">
                                                    <div class="p-3 border rounded-3 bg-white h-100 position-relative border-start border-4 <?= !empty($tp['has_kktp']) ? 'border-success' : 'border-warning' ?>">
                                                        <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                                            <span class="badge bg-dark small"><?= htmlspecialchars($tp['kode_tp']) ?></span>
                                                            <?php if (!empty($tp['has_kktp'])): ?>
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-check-all me-1"></i>KKTP Terkonfigurasi</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning-subtle text-warning text-dark border border-warning-subtle small"><i class="bi bi-clock me-1"></i>KKTP Belum Diatur</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="fw-bold text-dark small mb-1"><?= htmlspecialchars($tp['materi_pokok'] ?: 'Materi Pokok') ?></div>
                                                        <p class="small text-muted mb-2" style="font-size:0.8rem;"><?= htmlspecialchars($tp['deskripsi']) ?></p>

                                                        <?php if (!empty($tp['kktp_metode'])): ?>
                                                            <div class="p-2 bg-light rounded-2 border mt-2 small" style="font-size:0.75rem;">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <strong>Metode KKTP:</strong> <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', $tp['kktp_metode']))) ?></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <strong>Batas Nilai Minimum:</strong> <span class="badge bg-success"><?= number_format($tp['kktp_nilai_min'] ?? 75, 1) ?></span>
                                                                </div>
                                                                <?php if (!empty($tp['kktp_kriteria'])): ?>
                                                                    <div class="text-muted mt-1"><em><?= htmlspecialchars($tp['kktp_kriteria']) ?></em></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<style>
.card-custom {
    background: #ffffff;
    border-radius: 1rem;
    border: 1px solid rgba(0, 0, 0, 0.06);
}
@media (max-width: 768px) {
    .page-title {
        font-size: 1.15rem;
    }
    .page-subtitle {
        font-size: 0.78rem;
    }
    .hero-icon-box {
        width: 46px !important;
        height: 46px !important;
    }
    .hero-icon-box i {
        font-size: 1.4rem !important;
    }
}
@media print {
    .app-sidebar, .app-header, .btn, form, #categoryFilterContainer, .input-group {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
}
</style>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
