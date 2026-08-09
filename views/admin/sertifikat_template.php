<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Template & Generator Sertifikat Digital</h4>
            <p class="text-muted small mb-0">Kelola template sertifikat resmi kelulusan, prestasi, UKK, dan cetak sertifikat siswa otomatis.</p>
        </div>
        <form action="<?= BASE_URL ?>index.php?url=admin/sertifikat" method="POST" class="d-inline">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="generate_massal">
            <button type="submit" class="btn btn-warning text-dark fw-bold shadow-sm">
                <i class="bi bi-gear-fill me-1"></i> Generate Sertifikat Massal (<?= count($siswaList) ?> Siswa)
            </button>
        </form>
    </div>

    <!-- Template Selector Cards -->
    <div class="row g-4 mb-4">
        <!-- Template 1: Kelulusan LMS -->
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 text-center <?= ($activeTemplate === 'kelulusan') ? 'border border-2 border-primary bg-primary-subtle bg-opacity-10' : '' ?>">
                <div class="p-3 bg-primary-subtle text-primary rounded-3 mb-3">
                    <i class="bi bi-award fs-1"></i>
                </div>
                <h6 class="fw-bold mb-1">Template Kelulusan LMS</h6>
                <small class="text-muted d-block mb-3">Sertifikat resmi penyelesaian modul & evaluasi e-learning semester.</small>
                
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>index.php?url=admin/previewSertifikat&template=kelulusan" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Preview
                    </a>
                    <?php if ($activeTemplate === 'kelulusan'): ?>
                        <span class="badge bg-primary px-3 py-2 fs-6">Template Aktif</span>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>index.php?url=admin/sertifikat" method="POST" class="d-inline">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="action" value="set_template">
                            <input type="hidden" name="template" value="kelulusan">
                            <button type="submit" class="btn btn-sm btn-primary">Gunakan Template</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Template 2: Prestasi Akademik -->
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 text-center <?= ($activeTemplate === 'prestasi') ? 'border border-2 border-success bg-success-subtle bg-opacity-10' : '' ?>">
                <div class="p-3 bg-success-subtle text-success rounded-3 mb-3">
                    <i class="bi bi-trophy fs-1"></i>
                </div>
                <h6 class="fw-bold mb-1">Template Prestasi Akademik</h6>
                <small class="text-muted d-block mb-3">Sertifikat khusus siswa berprestasi & peraih nilai terbaik kelas.</small>
                
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>index.php?url=admin/previewSertifikat&template=prestasi" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-eye"></i> Preview
                    </a>
                    <?php if ($activeTemplate === 'prestasi'): ?>
                        <span class="badge bg-success px-3 py-2 fs-6">Template Aktif</span>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>index.php?url=admin/sertifikat" method="POST" class="d-inline">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="action" value="set_template">
                            <input type="hidden" name="template" value="prestasi">
                            <button type="submit" class="btn btn-sm btn-success">Gunakan Template</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Template 3: Uji Kompetensi Keahlian (UKK) -->
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 text-center <?= ($activeTemplate === 'ukk') ? 'border border-2 border-warning bg-warning-subtle bg-opacity-10' : '' ?>">
                <div class="p-3 bg-warning-subtle text-warning rounded-3 mb-3">
                    <i class="bi bi-patch-check fs-1"></i>
                </div>
                <h6 class="fw-bold mb-1">Template Kompetensi UKK</h6>
                <small class="text-muted d-block mb-3">Sertifikat hasil kelulusan Uji Kompetensi Keahlian (UKK SMK).</small>
                
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>index.php?url=admin/previewSertifikat&template=ukk" target="_blank" class="btn btn-sm btn-outline-warning text-dark">
                        <i class="bi bi-eye"></i> Preview
                    </a>
                    <?php if ($activeTemplate === 'ukk'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">Template Aktif</span>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>index.php?url=admin/sertifikat" method="POST" class="d-inline">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="action" value="set_template">
                            <input type="hidden" name="template" value="ukk">
                            <button type="submit" class="btn btn-sm btn-warning text-dark">Gunakan Template</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Sertifikat Table -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Daftar Penerbitan & Sertifikat Siswa Terdaftar</h6>
            <span class="badge bg-info text-dark">Format Aktif: <?= strtoupper($activeTemplate) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>No. Sertifikat</th>
                        <th>Nama Siswa</th>
                        <th>Kelas & Jurusan</th>
                        <th>Jenis Sertifikat</th>
                        <th>Tanggal Terbit</th>
                        <th>Status QR</th>
                        <th class="text-center">Aksi Preview & Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswaList as $i => $s): ?>
                        <tr>
                            <td><code>SMKMH/CERT/<?= date('Y') ?>/<?= str_pad($s['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                            <td class="fw-bold"><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($s['nama_kelas']) ?> — <small class="text-muted"><?= htmlspecialchars($s['nama_jurusan']) ?></small></td>
                            <td>
                                <?php if ($activeTemplate === 'prestasi'): ?>
                                    <span class="badge bg-success">Prestasi Akademik</span>
                                <?php elseif ($activeTemplate === 'ukk'): ?>
                                    <span class="badge bg-warning text-dark">Uji Kompetensi UKK</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Kelulusan LMS</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y') ?></td>
                            <td><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terverifikasi QR</span></td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>index.php?url=admin/previewSertifikat&siswa_id=<?= $s['id'] ?>&template=<?= $activeTemplate ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-printer me-1"></i> Preview & Cetak
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
