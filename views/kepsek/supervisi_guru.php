<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Supervisi Akademik & Penilaian Kinerja Guru</h4>
                <p class="text-muted small mb-0">Instrumen evaluasi KBM, pembinaan pengajar, dan penilaian berkala mutu pembelajaran oleh Kepala Sekolah.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=supervisi" target="_blank" class="btn btn-outline-primary shadow-sm fw-bold">
                    <i class="bi bi-printer me-1"></i> Cetak Lembar Supervisi PDF
                </a>
                <button type="button" id="btnInputSupervisi" class="btn btn-primary shadow-sm fw-bold" onclick="openCreateModal(event)">
                    <i class="bi bi-plus-circle me-1"></i> Input Supervisi Baru
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi Flash -->
        <?php if ($msgSuccess = FlashHelper::getSuccess()): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msgSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($msgError = FlashHelper::getError()): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msgError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Ringkasan Statistik Supervisi -->
        <?php
        $totalSupervisi = count($supervisiList);
        $avgSupervisi = $totalSupervisi > 0 ? round(array_sum(array_column($supervisiList, 'nilai_akhir')) / $totalSupervisi, 1) : 0;
        $amatBaikCount = count(array_filter($supervisiList, fn($s) => (float)$s['nilai_akhir'] >= 90));
        $baikCount = count(array_filter($supervisiList, fn($s) => (float)$s['nilai_akhir'] >= 80 && (float)$s['nilai_akhir'] < 90));
        ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Supervisi KBM</div>
                    <div class="display-6 fw-bold my-1"><?= $totalSupervisi ?> Berkas</div>
                    <small>Guru Terjadwal & Terevaluasi</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Rata-Rata Skor Kinerja</div>
                    <div class="display-6 fw-bold my-1"><?= $avgSupervisi > 0 ? number_format($avgSupervisi, 1) : '0.0' ?></div>
                    <small><?= $avgSupervisi >= 80 ? 'Kategori Kinerja: Baik / Memuaskan' : 'Perlu Pendampingan KBM' ?></small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-warning text-dark">
                    <div class="small fw-semibold text-uppercase opacity-75">Predikat Amat Baik (A)</div>
                    <div class="display-6 fw-bold my-1"><?= $amatBaikCount ?> Guru</div>
                    <small>Skor Evaluasi &ge; 90.0</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-info text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Predikat Baik (B)</div>
                    <div class="display-6 fw-bold my-1"><?= $baikCount ?> Guru</div>
                    <small>Skor Evaluasi 80.0 - 89.9</small>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Supervisi Guru -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Daftar Hasil Supervisi Akademik Pengajar</h6>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">Dokumen Pimpinan</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($supervisiList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Tanggal Supervisi</th>
                            <th>Guru Pengampu & NIP</th>
                            <th>Mapel & Rombel</th>
                            <th class="text-center">Rincian Skor (4 Pilar)</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Predikat</th>
                            <th>Rekomendasi Tindak Lanjut</th>
                            <th style="width:120px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($supervisiList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-check fs-1 d-block mb-2 text-primary opacity-50"></i>
                                    <div class="fw-bold text-dark mb-1">Belum Ada Lembar Supervisi Akademik</div>
                                    <p class="small text-muted mb-3">Mulai evaluasi berkala proses pembelajaran guru pengampu sekarang.</p>
                                    <button type="button" class="btn btn-primary btn-sm px-3 py-2 rounded-pill fw-bold shadow-sm" onclick="openCreateModal(event)">
                                        <i class="bi bi-plus-circle me-1"></i> Input Supervisi Baru
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($supervisiList as $i => $row): 
                                $skor = (float)$row['nilai_akhir'];
                                $badgePred = match(true) {
                                    $skor >= 90 => 'bg-success',
                                    $skor >= 80 => 'bg-primary',
                                    $skor >= 70 => 'bg-warning text-dark',
                                    default => 'bg-danger'
                                };
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= date('d M Y', strtotime($row['tanggal_supervisi'])) ?></div>
                                        <small class="text-muted">Oleh: <?= htmlspecialchars($row['nama_kepsek'] ?? 'Kepala Sekolah') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_guru']) ?></div>
                                        <small class="text-muted">NIP: <code><?= htmlspecialchars($row['nip'] ?? '-') ?></code></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-primary"><?= htmlspecialchars($row['nama_mapel'] ?? 'Semua Mapel') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['nama_kelas'] ?? 'Rombel Umum') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 small">
                                            <span class="badge bg-light text-dark border" title="Perencanaan">P: <?= $row['skor_perencanaan'] ?></span>
                                            <span class="badge bg-light text-dark border" title="Pelaksanaan">L: <?= $row['skor_pelaksanaan'] ?></span>
                                            <span class="badge bg-light text-dark border" title="Evaluasi">E: <?= $row['skor_evaluasi'] ?></span>
                                            <span class="badge bg-light text-dark border" title="Disiplin">D: <?= $row['skor_kedisiplinan'] ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-5 fw-bold text-dark"><?= number_format($skor, 1) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgePred ?> px-3 py-2 rounded-pill">
                                            <?= htmlspecialchars($row['predikat'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($row['rekomendasi_tindak_lanjut'] ?? '-') ?>">
                                            <?= htmlspecialchars($row['rekomendasi_tindak_lanjut'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" title="Detail Lembar Supervisi" onclick="showDetailModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" title="Edit Data Supervisi" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?>index.php?url=kepsek/supervisiGuru" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data supervisi ini?')">
                                                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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

<!-- Modal Input / Edit Supervisi Guru -->
<div class="modal fade" id="modalSupervisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formSupervisi" method="POST" action="<?= BASE_URL ?>index.php?url=kepsek/supervisiGuru" class="modal-content rounded-4 border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="formSupervisiId" value="">

            <div class="modal-header border-bottom pb-3 px-4 bg-light rounded-top-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-dark" id="modalFormTitle"><i class="bi bi-pencil-square text-primary me-2"></i>Instrumen Supervisi Akademik Guru</h5>
                    <small class="text-muted">Isi formulir penilaian pembelajaran dan pembinaan kinerja guru.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" form="formSupervisi" class="btn btn-primary btn-sm px-3 py-1.5 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill"></i> Simpan
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeModalSupervisi()" aria-label="Close"></button>
                </div>
            </div>

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Pilih Guru yang Disupervisi <span class="text-danger">*</span></label>
                            <select name="guru_id" id="formGuruId" class="form-select" required>
                                <option value="">-- Pilih Tenaga Pengajar --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?> (<?= htmlspecialchars($g['nip'] ?? '-') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Tanggal Supervisi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_supervisi" id="formTanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Mata Pelajaran KBM</label>
                            <select name="mapel_id" id="formMapelId" class="form-select">
                                <option value="">-- Semua / Umum --</option>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Rombel Kelas yang Diobservasi</label>
                            <select name="kelas_id" id="formKelasId" class="form-select">
                                <option value="">-- Semua / Umum --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-sliders me-1 text-primary"></i>Rubrik Penilaian 4 Aspek (Skala 0 - 100)
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">1. Perencanaan Pembelajaran</label>
                                    <span class="badge bg-primary">Bobot 25%</span>
                                </div>
                                <small class="text-muted d-block mb-2">Kelengkapan modul ajar, tujuan pembelajaran (TP/ATP), media bahan ajar.</small>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" name="skor_perencanaan" id="formSkor1" class="form-control fw-bold" placeholder="0 - 100" required oninput="calcLiveScore()">
                                    <span class="input-group-text">/ 100</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">2. Pelaksanaan KBM</label>
                                    <span class="badge bg-success">Bobot 35%</span>
                                </div>
                                <small class="text-muted d-block mb-2">Interaksi mengajar, penguasaan materi, pemanfaatan LMS & ruang virtual.</small>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" name="skor_pelaksanaan" id="formSkor2" class="form-control fw-bold" placeholder="0 - 100" required oninput="calcLiveScore()">
                                    <span class="input-group-text">/ 100</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">3. Evaluasi & Penilaian</label>
                                    <span class="badge bg-warning text-dark">Bobot 25%</span>
                                </div>
                                <small class="text-muted d-block mb-2">Pemberian tugas, variasi soal kuis CBT, rubrik koreksi, ketepatan input rapor.</small>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" name="skor_evaluasi" id="formSkor3" class="form-control fw-bold" placeholder="0 - 100" required oninput="calcLiveScore()">
                                    <span class="input-group-text">/ 100</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">4. Kedisiplinan & Presensi</label>
                                    <span class="badge bg-info text-dark">Bobot 15%</span>
                                </div>
                                <small class="text-muted d-block mb-2">Ketepatan jam check-in selfie, konsistensi hadir, kepatuhan jadwal mengajar.</small>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" name="skor_kedisiplinan" id="formSkor4" class="form-control fw-bold" placeholder="0 - 100" required oninput="calcLiveScore()">
                                    <span class="input-group-text">/ 100</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kalkulasi Nilai Akhir Live Preview -->
                    <div class="alert alert-primary p-3 rounded-3 d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="fw-bold text-dark d-block">Estimasi Nilai Akhir & Predikat:</span>
                            <small class="text-muted">Dihitung otomatis berdasarkan bobot instrumen resmi.</small>
                        </div>
                        <div class="text-end">
                            <span class="fs-4 fw-bold text-primary" id="liveScorePreview">0.0</span>
                            <span class="badge bg-primary ms-2" id="livePredikatPreview">Belum Dinilai</span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-chat-left-text me-1 text-primary"></i>Catatan Kualitatif & Rekomendasi Kepala Sekolah
                    </h6>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Aspek Kekuatan / Keunggulan Guru</label>
                        <textarea name="catatan_kekuatan" id="formKekuatan" rows="2" class="form-control" placeholder="Contoh: Interaktif dalam memanfaatkan kelas virtual, materi modul ajar lengkap dan menarik..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Aspek yang Perlu Ditingkatkan / Diperbaiki</label>
                        <textarea name="catatan_perbaikan" id="formPerbaikan" rows="2" class="form-control" placeholder="Contoh: Perlu mempercepat proses koreksi penilaian tugas siswa agar feedback lebih cepat..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rekomendasi Tindak Lanjut Pembinaan Kepala Sekolah <span class="text-danger">*</span></label>
                        <textarea name="rekomendasi_tindak_lanjut" id="formRekomendasi" rows="3" class="form-control" required placeholder="Contoh: Diberikan penugasan sebagai pemateri IKM, disarankan mengikuti pelatihan media pembelajaran digital..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top pt-3 px-4 bg-light rounded-bottom-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal" onclick="closeModalSupervisi()">Batal</button>
                    <button type="submit" form="formSupervisi" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Hasil Supervisi
                    </button>
                </div>
        </form>
    </div>
</div>

<!-- Modal Pratinjau Detail Hasil Supervisi -->
<div class="modal fade" id="modalDetailSupervisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-person-fill text-primary me-2"></i>Rincian Lembar Supervisi Akademik</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeModalDetailSupervisi()"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailBody">
                <!-- Injected via JavaScript -->
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal" onclick="closeModalDetailSupervisi()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function calcLiveScore() {
    const s1El = document.getElementById('formSkor1');
    const s2El = document.getElementById('formSkor2');
    const s3El = document.getElementById('formSkor3');
    const s4El = document.getElementById('formSkor4');
    if (!s1El || !s2El || !s3El || !s4El) return;

    const s1 = parseFloat(s1El.value);
    const s2 = parseFloat(s2El.value);
    const s3 = parseFloat(s3El.value);
    const s4 = parseFloat(s4El.value);

    const hasInput = !isNaN(s1) || !isNaN(s2) || !isNaN(s3) || !isNaN(s4);
    const v1 = isNaN(s1) ? 0 : s1;
    const v2 = isNaN(s2) ? 0 : s2;
    const v3 = isNaN(s3) ? 0 : s3;
    const v4 = isNaN(s4) ? 0 : s4;

    const total = (v1 * 0.25) + (v2 * 0.35) + (v3 * 0.25) + (v4 * 0.15);
    const scorePreview = document.getElementById('liveScorePreview');
    if (scorePreview) {
        scorePreview.innerText = hasInput ? total.toFixed(1) : '0.0';
    }

    const predElem = document.getElementById('livePredikatPreview');
    if (!predElem) return;

    if (!hasInput) {
        predElem.innerText = 'Belum Dinilai';
        predElem.className = 'badge ms-2 bg-secondary';
        return;
    }

    let pred = 'Kurang (D)';
    let badgeClass = 'bg-danger';
    if (total >= 90) { pred = 'Amat Baik (A)'; badgeClass = 'bg-success'; }
    else if (total >= 80) { pred = 'Baik (B)'; badgeClass = 'bg-primary'; }
    else if (total >= 70) { pred = 'Cukup (C)'; badgeClass = 'bg-warning text-dark'; }

    predElem.innerText = pred;
    predElem.className = 'badge ms-2 ' + badgeClass;
}

function showModalElement(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) {
        console.error('Element modal #' + modalId + ' tidak ditemukan!');
        return;
    }

    // 1. Coba Bootstrap 5 API
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        try {
            const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
            return;
        } catch (e) {
            console.warn('Bootstrap 5 show error, fallback ke jQuery/CSS:', e);
        }
    }

    // 2. Coba jQuery Bootstrap API
    if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
        try {
            window.jQuery(modalEl).modal('show');
            return;
        } catch (e) {
            console.warn('jQuery modal show error, fallback ke CSS:', e);
        }
    }

    // 3. Fallback Vanilla CSS
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.removeAttribute('aria-hidden');
    modalEl.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    let backdrop = document.getElementById('manual-modal-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'manual-modal-backdrop';
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
        backdrop.addEventListener('click', closeModalSupervisi);
    }
}

function hideModalElement(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        try {
            const instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        } catch (e) {}
    }

    if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
        try {
            window.jQuery(modalEl).modal('hide');
        } catch (e) {}
    }

    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');

    const backdrop = document.getElementById('manual-modal-backdrop');
    if (backdrop) backdrop.remove();

    const bsBackdrops = document.querySelectorAll('.modal-backdrop');
    bsBackdrops.forEach(b => b.remove());
}

function closeModalSupervisi() {
    hideModalElement('modalSupervisi');
}

function closeModalDetailSupervisi() {
    hideModalElement('modalDetailSupervisi');
}

function openCreateModal(arg1 = null, arg2 = null) {
    let e = null;
    let guruId = null;
    if (arg1 && typeof arg1.preventDefault === 'function') {
        e = arg1;
        guruId = arg2;
    } else if (typeof arg1 === 'number' || typeof arg1 === 'string') {
        guruId = arg1;
    }
    if (e) {
        try {
            e.preventDefault();
            e.stopPropagation();
        } catch (err) {}
    }

    const formSupervisiId = document.getElementById('formSupervisiId');
    if (formSupervisiId) formSupervisiId.value = '';

    const titleEl = document.getElementById('modalFormTitle');
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i>Input Instrumen Supervisi Akademik Guru';

    const formTanggal = document.getElementById('formTanggal');
    if (formTanggal) formTanggal.value = '<?= date('Y-m-d') ?>';

    const formGuruId = document.getElementById('formGuruId');
    if (formGuruId) formGuruId.value = guruId ? guruId : '';

    const formMapelId = document.getElementById('formMapelId');
    if (formMapelId) formMapelId.value = '';

    const formKelasId = document.getElementById('formKelasId');
    if (formKelasId) formKelasId.value = '';

    const formSkor1 = document.getElementById('formSkor1');
    if (formSkor1) formSkor1.value = '';

    const formSkor2 = document.getElementById('formSkor2');
    if (formSkor2) formSkor2.value = '';

    const formSkor3 = document.getElementById('formSkor3');
    if (formSkor3) formSkor3.value = '';

    const formSkor4 = document.getElementById('formSkor4');
    if (formSkor4) formSkor4.value = '';

    const formKekuatan = document.getElementById('formKekuatan');
    if (formKekuatan) formKekuatan.value = '';

    const formPerbaikan = document.getElementById('formPerbaikan');
    if (formPerbaikan) formPerbaikan.value = '';

    const formRekomendasi = document.getElementById('formRekomendasi');
    if (formRekomendasi) formRekomendasi.value = '';

    calcLiveScore();
    showModalElement('modalSupervisi');
}

function openEditModal(row) {
    const formSupervisiId = document.getElementById('formSupervisiId');
    if (formSupervisiId) formSupervisiId.value = row.id || '';

    const titleEl = document.getElementById('modalFormTitle');
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-pencil-square text-warning me-2"></i>Edit Hasil Supervisi Akademik Guru';

    const formTanggal = document.getElementById('formTanggal');
    if (formTanggal) formTanggal.value = row.tanggal_supervisi || '<?= date('Y-m-d') ?>';

    const formGuruId = document.getElementById('formGuruId');
    if (formGuruId) formGuruId.value = row.guru_id || '';

    const formMapelId = document.getElementById('formMapelId');
    if (formMapelId) formMapelId.value = row.mapel_id || '';

    const formKelasId = document.getElementById('formKelasId');
    if (formKelasId) formKelasId.value = row.kelas_id || '';

    const formSkor1 = document.getElementById('formSkor1');
    if (formSkor1) formSkor1.value = row.skor_perencanaan ?? '';

    const formSkor2 = document.getElementById('formSkor2');
    if (formSkor2) formSkor2.value = row.skor_pelaksanaan ?? '';

    const formSkor3 = document.getElementById('formSkor3');
    if (formSkor3) formSkor3.value = row.skor_evaluasi ?? '';

    const formSkor4 = document.getElementById('formSkor4');
    if (formSkor4) formSkor4.value = row.skor_kedisiplinan ?? '';

    const formKekuatan = document.getElementById('formKekuatan');
    if (formKekuatan) formKekuatan.value = row.catatan_kekuatan || '';

    const formPerbaikan = document.getElementById('formPerbaikan');
    if (formPerbaikan) formPerbaikan.value = row.catatan_perbaikan || '';

    const formRekomendasi = document.getElementById('formRekomendasi');
    if (formRekomendasi) formRekomendasi.value = row.rekomendasi_tindak_lanjut || '';

    calcLiveScore();
    showModalElement('modalSupervisi');
}

function showDetailModal(row) {
    const html = `
        <div class="text-center mb-3">
            <h5 class="fw-bold mb-0 text-dark">${row.nama_guru}</h5>
            <small class="text-muted">NIP: ${row.nip || '-'} | Tanggal: ${row.tanggal_supervisi}</small>
        </div>
        <div class="p-3 bg-light rounded-3 mb-3">
            <div class="row text-center">
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Perencanaan (25%)</small>
                    <b class="fs-6">${row.skor_perencanaan}</b>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Pelaksanaan (35%)</small>
                    <b class="fs-6">${row.skor_pelaksanaan}</b>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block">Evaluasi (25%)</small>
                    <b class="fs-6">${row.skor_evaluasi}</b>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block">Disiplin (15%)</small>
                    <b class="fs-6">${row.skor_kedisiplinan}</b>
                </div>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">Nilai Akhir:</span>
                <div>
                    <span class="fs-5 fw-bold text-primary">${parseFloat(row.nilai_akhir).toFixed(1)}</span>
                    <span class="badge bg-success ms-2">${row.predikat || '-'}</span>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <b class="small text-dark d-block mb-1"><i class="bi bi-star-fill text-warning me-1"></i>Kekuatan Guru:</b>
            <p class="small text-muted mb-2">${row.catatan_kekuatan || 'Belum ada catatan'}</p>
        </div>
        <div class="mb-2">
            <b class="small text-dark d-block mb-1"><i class="bi bi-exclamation-circle text-primary me-1"></i>Aspek Perbaikan:</b>
            <p class="small text-muted mb-2">${row.catatan_perbaikan || 'Belum ada catatan'}</p>
        </div>
        <div class="mb-2">
            <b class="small text-dark d-block mb-1"><i class="bi bi-chat-heart text-danger me-1"></i>Rekomendasi Tindak Lanjut:</b>
            <p class="small text-dark bg-warning-subtle p-2 rounded-2 mb-0">${row.rekomendasi_tindak_lanjut || '-'}</p>
        </div>
    `;
    const detailBody = document.getElementById('modalDetailBody');
    if (detailBody) detailBody.innerHTML = html;
    showModalElement('modalDetailSupervisi');
}

// Pasang event listener saat DOM ready
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnInputSupervisi');
    if (btn) {
        btn.addEventListener('click', function(e) {
            openCreateModal(e);
        });
    }

    <?php if (!empty($editData)): ?>
        openEditModal(<?= json_encode($editData) ?>);
    <?php elseif (!empty($selectedGuruId)): ?>
        openCreateModal(<?= (int)$selectedGuruId ?>);
    <?php endif; ?>
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
