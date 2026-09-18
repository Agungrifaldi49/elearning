<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Hasil Supervisi Akademik & Penilaian Kinerja Guru</h4>
                <p class="text-muted small mb-0">Transkrip evaluasi KBM, umpan balik pembinaan, dan rubrik penilaian 4 pilar dari Kepala Sekolah.</p>
            </div>
            <?php if (!empty($supervisiList)): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if (!empty($supervisiTerbaru)): ?>
                        <a href="<?= BASE_URL ?>index.php?url=guru/cetakSupervisi&id=<?= $supervisiTerbaru['id'] ?>" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                            <i class="bi bi-printer-fill me-1"></i> Cetak Lembar Supervisi (PDF)
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>index.php?url=guru/cetakSupervisi&type=rekap" target="_blank" class="btn btn-outline-secondary shadow-sm fw-bold">
                        <i class="bi bi-file-earmark-text me-1"></i> Cetak Rekap Riwayat
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ringkasan Statistik Metrik Supervisi -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Supervisi Diterima</div>
                    <div class="display-6 fw-bold my-1"><?= $totalSupervisi ?> Berkas</div>
                    <small>Riwayat Evaluasi KBM</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Rata-Rata Skor Kinerja</div>
                    <div class="display-6 fw-bold my-1"><?= $avgSupervisi > 0 ? number_format($avgSupervisi, 1) : '0.0' ?></div>
                    <small><?= $avgSupervisi >= 80 ? 'Kategori: Baik / Memuaskan' : ($avgSupervisi > 0 ? 'Perlu Pendampingan KBM' : 'Belum Ada Penilaian') ?></small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-warning text-dark">
                    <div class="small fw-semibold text-uppercase opacity-75">Predikat Kinerja Terakhir</div>
                    <div class="h3 fw-bold my-2"><?= htmlspecialchars($supervisiTerbaru['predikat'] ?? 'Belum Dinilai') ?></div>
                    <small><?= !empty($supervisiTerbaru['nilai_akhir']) ? 'Skor Akhir: ' . number_format((float)$supervisiTerbaru['nilai_akhir'], 1) : 'Menunggu Observasi' ?></small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-info text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Evaluasi Terakhir</div>
                    <div class="h4 fw-bold my-2"><?= !empty($supervisiTerbaru['tanggal_supervisi']) ? date('d M Y', strtotime($supervisiTerbaru['tanggal_supervisi'])) : '-' ?></div>
                    <small><?= !empty($supervisiTerbaru['nama_kepsek']) ? 'Oleh: ' . htmlspecialchars($supervisiTerbaru['nama_kepsek']) : 'Kepala Sekolah' ?></small>
                </div>
            </div>
        </div>

        <?php if (!empty($supervisiTerbaru)): ?>
            <!-- Card Evaluasi Supervisi Terkini (Highlight) -->
            <div class="card card-custom p-4 shadow-sm border-0 rounded-4 mb-4 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 border-bottom pb-3">
                    <div>
                        <div class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill mb-1">
                            <i class="bi bi-star-fill text-warning me-1"></i> Evaluasi Supervisi Terkini
                        </div>
                        <h5 class="fw-bold text-dark mb-0">
                            Observasi KBM Tanggal <?= date('d F Y', strtotime($supervisiTerbaru['tanggal_supervisi'])) ?>
                        </h5>
                        <small class="text-muted">
                            Penilai: <b><?= htmlspecialchars($supervisiTerbaru['nama_kepsek'] ?? 'Kepala Sekolah') ?></b> | 
                            Mapel: <b><?= htmlspecialchars($supervisiTerbaru['nama_mapel'] ?? 'Semua Mapel') ?></b> | 
                            Kelas: <b><?= htmlspecialchars($supervisiTerbaru['nama_kelas'] ?? 'Rombel Umum') ?></b>
                        </small>
                    </div>
                    <div class="text-end d-flex align-items-center gap-2">
                        <div>
                            <span class="fs-3 fw-bold text-primary"><?= number_format((float)$supervisiTerbaru['nilai_akhir'], 1) ?></span>
                            <span class="badge bg-success ms-2 px-3 py-2 fs-6 rounded-pill"><?= htmlspecialchars($supervisiTerbaru['predikat'] ?? '-') ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>index.php?url=guru/cetakSupervisi&id=<?= $supervisiTerbaru['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3 py-2 shadow-xs" title="Cetak Lembar Supervisi Ini">
                            <i class="bi bi-printer-fill me-1"></i> Cetak Lembar
                        </a>
                    </div>
                </div>

                <!-- 4 Pilar Penilaian -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="p-3 bg-light rounded-4 border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark">1. Perencanaan</span>
                                <span class="badge bg-primary">Bobot 25%</span>
                            </div>
                            <div class="fs-4 fw-bold text-dark"><?= $supervisiTerbaru['skor_perencanaan'] ?> <small class="fs-6 text-muted">/ 100</small></div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, (float)$supervisiTerbaru['skor_perencanaan']) ?>%;"></div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Modul ajar, tujuan KBM & materi</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="p-3 bg-light rounded-4 border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark">2. Pelaksanaan</span>
                                <span class="badge bg-success">Bobot 35%</span>
                            </div>
                            <div class="fs-4 fw-bold text-dark"><?= $supervisiTerbaru['skor_pelaksanaan'] ?> <small class="fs-6 text-muted">/ 100</small></div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, (float)$supervisiTerbaru['skor_pelaksanaan']) ?>%;"></div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Interaksi kelas & pemanfaatan LMS</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="p-3 bg-light rounded-4 border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark">3. Evaluasi</span>
                                <span class="badge bg-warning text-dark">Bobot 25%</span>
                            </div>
                            <div class="fs-4 fw-bold text-dark"><?= $supervisiTerbaru['skor_evaluasi'] ?> <small class="fs-6 text-muted">/ 100</small></div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= min(100, (float)$supervisiTerbaru['skor_evaluasi']) ?>%;"></div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Tugas, kuis CBT & koreksi rapor</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="p-3 bg-light rounded-4 border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark">4. Disiplin & Hadir</span>
                                <span class="badge bg-info text-dark">Bobot 15%</span>
                            </div>
                            <div class="fs-4 fw-bold text-dark"><?= $supervisiTerbaru['skor_kedisiplinan'] ?> <small class="fs-6 text-muted">/ 100</small></div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= min(100, (float)$supervisiTerbaru['skor_kedisiplinan']) ?>%;"></div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Ketepatan selfie presensi & jadwal</small>
                        </div>
                    </div>
                </div>

                <!-- Feedback & Catatan Pembinaan Kepala Sekolah -->
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-success-subtle rounded-4 h-100 border border-success-subtle">
                            <b class="small text-success d-block mb-1"><i class="bi bi-star-fill me-1"></i>Kekuatan & Keunggulan Anda:</b>
                            <p class="small text-dark mb-0"><?= nl2br(htmlspecialchars($supervisiTerbaru['catatan_kekuatan'] ?: 'Pembelajaran interaktif dan materi modul telah tersusun dengan baik.')) ?></p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-primary-subtle rounded-4 h-100 border border-primary-subtle">
                            <b class="small text-primary d-block mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Aspek yang Perlu Ditingkatkan:</b>
                            <p class="small text-dark mb-0"><?= nl2br(htmlspecialchars($supervisiTerbaru['catatan_perbaikan'] ?: 'Tingkatkan umpan balik koreksi tugas siswa secara berkelanjutan.')) ?></p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-warning-subtle rounded-4 h-100 border border-warning-subtle">
                            <b class="small text-dark d-block mb-1"><i class="bi bi-chat-heart-fill text-danger me-1"></i>Rekomendasi Tindak Lanjut Kepala Sekolah:</b>
                            <p class="small text-dark mb-0 fw-semibold"><?= nl2br(htmlspecialchars($supervisiTerbaru['rekomendasi_tindak_lanjut'] ?: 'Pertahankan kualitas KBM dan ikuti forum sharing praktik baik sesama guru.')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabel Riwayat Semua Berkas Supervisi -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Evaluasi Supervisi Akademik Pengajar</h6>
                <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">Total: <?= $totalSupervisi ?> Lembar</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($supervisiList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Tanggal Supervisi</th>
                            <th>Kepala Sekolah (Penilai)</th>
                            <th>Mapel & Rombel</th>
                            <th class="text-center">Skor 4 Pilar</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Predikat</th>
                            <th>Rekomendasi Tindak Lanjut</th>
                            <th style="width:115px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($supervisiList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-award fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-bold text-dark mb-1">Belum Ada Lembar Supervisi Akademik</div>
                                    <p class="small text-muted mb-0">Kepala Sekolah belum menerbitkan lembar supervisi pembelajaran untuk akun pengajar Anda. Hasil penilaian dan pembinaan berkala akan otomatis tampil di sini setelah instrumen diisi oleh Kepala Sekolah.</p>
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
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_kepsek'] ?? 'Kepala Sekolah') ?></div>
                                        <small class="text-muted">Pimpinan Satuan Pendidikan</small>
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
                                        <span class="badge <?= $badgePred ?> px-3 py-1.5 rounded-pill">
                                            <?= htmlspecialchars($row['predikat'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($row['rekomendasi_tindak_lanjut'] ?? '-') ?>">
                                            <?= htmlspecialchars($row['rekomendasi_tindak_lanjut'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" title="Lihat Lembar Supervisi Lengkap" onclick="showDetailModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>index.php?url=guru/cetakSupervisi&id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Cetak Lembar Supervisi PDF">
                                                <i class="bi bi-printer-fill"></i>
                                            </a>
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
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <a href="#" id="modalBtnCetak" target="_blank" class="btn btn-primary px-3 rounded-3 fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Lembar Supervisi PDF
                </a>
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal" onclick="closeModalDetailSupervisi()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showDetailModal(row) {
    const html = `
        <div class="text-center mb-3">
            <h5 class="fw-bold mb-0 text-dark">${row.nama_guru}</h5>
            <small class="text-muted">NIP: ${row.nip || '-'} | Tanggal Observasi: ${row.tanggal_supervisi}</small>
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

    const btnCetak = document.getElementById('modalBtnCetak');
    if (btnCetak) btnCetak.href = '<?= BASE_URL ?>index.php?url=guru/cetakSupervisi&id=' + row.id;

    const modalEl = document.getElementById('modalDetailSupervisi');
    if (!modalEl) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        try {
            const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
            return;
        } catch (e) {}
    }

    if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
        try {
            window.jQuery(modalEl).modal('show');
            return;
        } catch (e) {}
    }

    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.removeAttribute('aria-hidden');
    modalEl.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
}

function closeModalDetailSupervisi() {
    const modalEl = document.getElementById('modalDetailSupervisi');
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
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
