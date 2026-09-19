<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Header Breadcrumb & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Manajemen Kurikulum, CP/TP & Struktur Penilaian
            </h4>
            <p class="text-muted small mb-0">Kelola arsitektur kurikulum dinamis, pemetaan fase/tingkat, capaian pembelajaran, dan bobot penilaian tanpa mengunci sistem.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm fw-bold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalAddKurikulum">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kurikulum Baru
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Kurikulum</div>
                        <div class="fs-3 fw-bold text-dark my-1"><?= count($kurikulumList) ?></div>
                        <small class="text-primary fw-semibold"><i class="bi bi-collection me-1"></i>Data-driven system</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-mortarboard fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Rombel Terpetakan</div>
                        <div class="fs-3 fw-bold text-success my-1"><?= count($rombelKurikulumList) ?></div>
                        <small class="text-muted"><i class="bi bi-check-circle me-1"></i>Multi-Tahun Ajaran</small>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-building-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Fase & Tingkat</div>
                        <div class="fs-3 fw-bold text-info my-1"><?= count($allFaseList) ?></div>
                        <small class="text-muted"><i class="bi bi-layers me-1"></i>Fase E, F & Lainnya</small>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-diagram-2 fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total CP & TP</div>
                        <div class="fs-3 fw-bold text-dark my-1"><?= count($cpList) ?> <span class="fs-6 text-muted">CP</span> / <?= count($tpList) ?> <span class="fs-6 text-muted">TP</span></div>
                        <small class="text-muted"><i class="bi bi-bookmark-check me-1"></i>Capaian Pembelajaran</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-award fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills Tabs -->
    <div class="card-custom p-2 mb-4 shadow-sm">
        <ul class="nav nav-pills nav-fill gap-2" id="kurikulumTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'kurikulum' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabKurikulum">
                    <i class="bi bi-mortarboard-fill me-1.5"></i> 1. Master Kurikulum
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'fase' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabFase">
                    <i class="bi bi-layers-fill me-1.5"></i> 2. Fase / Tingkat
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'rombel' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabRombel">
                    <i class="bi bi-building me-1.5"></i> 3. Kurikulum Rombel
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'struktur' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabStruktur">
                    <i class="bi bi-journal-text me-1.5"></i> 4. Struktur Mapel
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'cptp' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabCPTP">
                    <i class="bi bi-card-checklist me-1.5"></i> 5. CP & TP
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'komponen' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabKomponen">
                    <i class="bi bi-sliders me-1.5"></i> 6. Bobot Penilaian
                </button>
            </li>
        </ul>
    </div>

    <!-- TAB CONTENTS -->
    <div class="tab-content" id="kurikulumTabContent">

        <!-- =================================================================== -->
        <!-- TAB 1: MASTER KURIKULUM -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'kurikulum' ? 'show active' : '' ?>" id="tabKurikulum">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Daftar Kurikulum Terdaftar</h5>
                        <p class="text-muted small mb-0">Kurikulum tidak di-hardcode. Kurikulum baru dapat ditambahkan kapan saja tanpa merusak arsip kurikulum lama.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddKurikulum">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Kurikulum
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kode</th>
                                <th>Nama Kurikulum</th>
                                <th>Periode Berlaku</th>
                                <th>Status</th>
                                <th>Rombel & Rapor Terkait</th>
                                <th class="text-center" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kurikulumList as $i => $k): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fs-6 font-monospace"><?= htmlspecialchars($k['kode']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($k['nama']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($k['deskripsi'] ?: 'Kurikulum pembelajaran resmi SMK') ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= $k['tahun_mulai'] ?></span> s/d 
                                        <?= $k['tahun_selesai'] ? "<span class='fw-semibold'>{$k['tahun_selesai']}</span>" : "<span class='badge bg-success-subtle text-success border'>Sekarang</span>" ?>
                                    </td>
                                    <td>
                                        <?php if ($k['status'] === 'aktif'): ?>
                                            <span class="badge bg-success px-3 py-2 shadow-xs"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        <?php elseif ($k['status'] === 'arsip'): ?>
                                            <span class="badge bg-secondary px-3 py-2"><i class="bi bi-archive me-1"></i>Arsip</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">Rombel: <strong><?= $k['total_rombel_terhubung'] ?></strong> kelas</small>
                                        <small class="d-block text-muted">Histori Rapor: <strong><?= $k['total_rapor_terbit'] ?></strong> siswa</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditKurikulum<?= $k['id'] ?>" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus kurikulum ini? (Hanya bisa dihapus jika belum ada riwayat nilai/rapor)');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_kurikulum">
                                                <input type="hidden" name="redirect_tab" value="kurikulum">
                                                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Kurikulum -->
                                <div class="modal fade" id="modalEditKurikulum<?= $k['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="update_kurikulum">
                                                <input type="hidden" name="redirect_tab" value="kurikulum">
                                                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="fw-bold mb-0">Edit Kurikulum: <?= htmlspecialchars($k['nama']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Kode Kurikulum</label>
                                                        <input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($k['kode']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Nama Kurikulum</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($k['nama']) ?>" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Tahun Mulai</label>
                                                            <input type="number" name="tahun_mulai" class="form-control" value="<?= $k['tahun_mulai'] ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">Tahun Selesai (Opsional)</label>
                                                            <input type="number" name="tahun_selesai" class="form-control" value="<?= $k['tahun_selesai'] ?>" placeholder="Kosongkan jika aktif">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Status Kurikulum</label>
                                                        <select name="status" class="form-select">
                                                            <option value="aktif" <?= $k['status'] === 'aktif' ? 'selected' : '' ?>>Aktif (Dapat Dipakai KBM)</option>
                                                            <option value="non-aktif" <?= $k['status'] === 'non-aktif' ? 'selected' : '' ?>>Non-Aktif</option>
                                                            <option value="arsip" <?= $k['status'] === 'arsip' ? 'selected' : '' ?>>Arsip (Hanya Untuk Histori Nilai)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Deskripsi</label>
                                                        <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($k['deskripsi'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 2: FASE / TINGKAT STRUKTUR -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'fase' ? 'show active' : '' ?>" id="tabFase">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Pengaturan Fase / Jenjang Tingkat</h5>
                        <p class="text-muted small mb-0">Fase tidak dibatasi hanya E/F. Anda dapat menambahkan fase atau jenjang struktur baru sesuai kebutuhan kurikulum masa depan.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddFase">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Fase Baru
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kurikulum</th>
                                <th>Kode Fase</th>
                                <th>Nama Lengkap Fase</th>
                                <th>Tingkat Kelas</th>
                                <th>Keterangan</th>
                                <th class="text-center" style="width:140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allFaseList as $i => $f): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($f['nama_kurikulum']) ?></span></td>
                                    <td><strong class="font-monospace fs-6 text-primary">Fase <?= htmlspecialchars($f['kode']) ?></strong></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($f['nama']) ?></td>
                                    <td><span class="badge bg-info-subtle text-dark border"><?= htmlspecialchars($f['tingkat_kelas'] ?: 'Semua') ?></span></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($f['keterangan'] ?: '-') ?></small></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditFase<?= $f['id'] ?>" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus fase ini?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_fase">
                                                <input type="hidden" name="redirect_tab" value="fase">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Fase -->
                                <div class="modal fade" id="modalEditFase<?= $f['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="update_fase">
                                                <input type="hidden" name="redirect_tab" value="fase">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="fw-bold mb-0">Edit Fase: <?= htmlspecialchars($f['nama']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Kode Fase</label>
                                                        <input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($f['kode']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Nama Fase</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($f['nama']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Target Tingkat Kelas (misal: X atau XI,XII)</label>
                                                        <input type="text" name="tingkat_kelas" class="form-control" value="<?= htmlspecialchars($f['tingkat_kelas'] ?? '') ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Keterangan</label>
                                                        <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($f['keterangan'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 3: KURIKULUM ROMBEL KELAS & VALIDASI BENTROK -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'rombel' ? 'show active' : '' ?>" id="tabRombel">
            <div class="card-custom p-4 shadow-sm mb-4">
                
                <!-- Notice Banner: Conflict Validation Rules -->
                <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle p-2.5 flex-shrink-0">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-primary">Integritas & Anti-Bentrok Kurikulum Rombel:</h6>
                        <small class="text-muted d-block">
                            Sistem secara otomatis <strong>mencegah lebih dari 1 kurikulum aktif</strong> untuk rombel yang sama pada tahun ajaran yang sama.
                            Namun, histori kurikulum tahun-tahun sebelumnya (misal 2026/2027 vs 2029/2030) tetap tersimpan utuh dan terisolasi tanpa saling menimpa.
                        </small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Pemetaan Kurikulum Rombel Kelas</h5>
                        <p class="text-muted small mb-0">Tentukan kurikulum dan fase yang berlaku untuk setiap kelas per tahun ajaran.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAssignRombel">
                        <i class="bi bi-plus-circle me-1"></i> Pasang Kurikulum ke Rombel
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Tahun Ajaran</th>
                                <th>Rombel Kelas</th>
                                <th>Kurikulum Yang Digunakan</th>
                                <th>Fase / Jenjang</th>
                                <th>Status Periode</th>
                                <th class="text-center" style="width:140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rombelKurikulumList as $i => $rk): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <span class="badge <?= !empty($rk['ta_is_active']) ? 'bg-success' : 'bg-secondary' ?> fw-bold px-2.5 py-1.5">
                                            <?= htmlspecialchars($rk['tahun_ajaran'] ?? $rk['nama_tahun']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($rk['nama_kelas']) ?></div>
                                        <small class="text-muted">Tingkat <?= htmlspecialchars($rk['tingkat']) ?> (<?= htmlspecialchars($rk['nama_jurusan'] ?? 'Umum') ?>)</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fs-6">
                                            <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($rk['nama_kurikulum']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($rk['nama_fase'])): ?>
                                            <span class="badge bg-info-subtle text-dark border px-2.5 py-1.5"><?= htmlspecialchars($rk['nama_fase']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rk['status'] === 'aktif'): ?>
                                            <span class="badge bg-success shadow-xs"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        <?php elseif ($rk['status'] === 'selesai'): ?>
                                            <span class="badge bg-secondary"><i class="bi bi-check2-all me-1"></i>Selesai / Alumni</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditRombel<?= $rk['id'] ?>" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Lepaskan kurikulum dari rombel ini?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_rombel">
                                                <input type="hidden" name="redirect_tab" value="rombel">
                                                <input type="hidden" name="id" value="<?= $rk['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Rombel Kurikulum -->
                                <div class="modal fade" id="modalEditRombel<?= $rk['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="update_rombel">
                                                <input type="hidden" name="redirect_tab" value="rombel">
                                                <input type="hidden" name="id" value="<?= $rk['id'] ?>">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="fw-bold mb-0">Edit Kurikulum Rombel <?= htmlspecialchars($rk['nama_kelas']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Pilih Kurikulum</label>
                                                        <select name="kurikulum_id" class="form-select" required>
                                                            <?php foreach ($kurikulumList as $kur): ?>
                                                                <option value="<?= $kur['id'] ?>" <?= $rk['kurikulum_id'] == $kur['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Pilih Fase</label>
                                                        <select name="fase_id" class="form-select">
                                                            <option value="">-- Tanpa Fase Khusus --</option>
                                                            <?php foreach ($allFaseList as $f): ?>
                                                                <option value="<?= $f['id'] ?>" <?= $rk['fase_id'] == $f['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Status Hubungan</label>
                                                        <select name="status" class="form-select">
                                                            <option value="aktif" <?= $rk['status'] === 'aktif' ? 'selected' : '' ?>>Aktif (Berjalan Saat Ini)</option>
                                                            <option value="selesai" <?= $rk['status'] === 'selesai' ? 'selected' : '' ?>>Selesai (Arsip Periode Lalu)</option>
                                                            <option value="non-aktif" <?= $rk['status'] === 'non-aktif' ? 'selected' : '' ?>>Non-Aktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 4: STRUKTUR MATA PELAJARAN PER KURIKULUM -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'struktur' ? 'show active' : '' ?>" id="tabStruktur">
            <div class="card-custom p-4 shadow-sm mb-4">
                
                <!-- Filter Kurikulum Selector -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 border-bottom pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted text-nowrap">Pilih Kurikulum:</label>
                        <select class="form-select form-select-sm fw-bold" onchange="location.href='<?= BASE_URL ?>index.php?url=admin/kurikulum&tab=struktur&kurikulum_id=' + this.value">
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>" <?= $selectedKurId == $kur['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddStrukturMapel">
                        <i class="bi bi-plus-circle me-1"></i> Kaitkan Mapel ke Kurikulum Ini
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kode Mapel</th>
                                <th>Nama Mata Pelajaran</th>
                                <th>Kelompok</th>
                                <th>Fase Target</th>
                                <th>Tingkat</th>
                                <th>Alokasi JP</th>
                                <th>KKM Standar</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($strukturMapelList)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada mata pelajaran yang dikaitkan ke kurikulum ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($strukturMapelList as $i => $sm): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><code><?= htmlspecialchars($sm['kode_mapel']) ?></code></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($sm['nama_mapel']) ?></td>
                                        <td><span class="badge bg-secondary-subtle text-dark border"><?= htmlspecialchars($sm['kelompok_mapel']) ?></span></td>
                                        <td><?= htmlspecialchars($sm['nama_fase'] ?? 'Semua Fase') ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($sm['tingkat'] ?: 'Semua') ?></span></td>
                                        <td><?= $sm['alokasi_jp'] ?> JP / Minggu</td>
                                        <td><span class="fw-bold text-success"><?= $sm['kkm'] ?></span></td>
                                        <td class="text-center">
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Lepaskan mapel ini dari kurikulum?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_mapel">
                                                <input type="hidden" name="redirect_tab" value="struktur">
                                                <input type="hidden" name="id" value="<?= $sm['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Lepas"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 5: CAPAIAN PEMBELAJARAN (CP) & TUJUAN PEMBELAJARAN (TP) -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'cptp' ? 'show active' : '' ?>" id="tabCPTP">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-card-checklist text-primary me-2"></i>Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP)</h5>
                        <p class="text-muted small mb-0">Setiap kurikulum memiliki CP/TP terisolasi sehingga pergantian kurikulum masa depan tidak akan mengganggu materi & rapor masa lalu.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddTP">
                            <i class="bi bi-plus-circle me-1"></i> Tambah TP Baru
                        </button>
                        <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                            <i class="bi bi-plus-circle me-1"></i> Tambah CP Baru
                        </button>
                    </div>
                </div>

                <!-- Filter Box for CP & TP -->
                <div class="bg-light p-3 rounded-3 mb-4 border">
                    <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-center">
                        <input type="hidden" name="url" value="admin/kurikulum">
                        <input type="hidden" name="tab" value="cptp">
                        <div class="col-12 col-md-3">
                            <label class="small fw-bold text-muted mb-1">Filter Kurikulum:</label>
                            <select name="filter_kurikulum_id" class="form-select form-select-sm">
                                <option value="">-- Semua Kurikulum --</option>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>" <?= ($filterCpKurId == $kur['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="small fw-bold text-muted mb-1">Filter Mata Pelajaran:</label>
                            <select name="filter_mapel_id" class="form-select form-select-sm">
                                <option value="">-- Semua Mata Pelajaran --</option>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= ($filterCpMapelId == $mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="small fw-bold text-muted mb-1">Filter Fase:</label>
                            <select name="filter_fase_id" class="form-select form-select-sm">
                                <option value="">-- Semua Fase --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" <?= ($filterCpFaseId == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-1 align-items-end pt-md-3">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill fw-bold"><i class="bi bi-funnel me-1"></i> Filter</button>
                            <?php if ($filterCpKurId || $filterCpMapelId || $filterCpFaseId): ?>
                                <a href="<?= BASE_URL ?>index.php?url=admin/kurikulum&tab=cptp" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th style="width:130px;">Kurikulum & Fase</th>
                                <th style="width:170px;">Mata Pelajaran</th>
                                <th style="width:160px;">Kode & Elemen CP</th>
                                <th>Deskripsi Capaian Pembelajaran</th>
                                <th style="width:340px;">Tujuan Pembelajaran (TP) Terkait</th>
                                <th class="text-center" style="width:110px;">Aksi CP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cpList)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> Tidak ada data CP yang cocok dengan kriteria filter.</td></tr>
                            <?php else: ?>
                                <?php foreach ($cpList as $i => $cp): 
                                    $childTps = array_filter($tpList, function($t) use ($cp) { return $t['cp_id'] == $cp['id']; });
                                ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border mb-1 d-inline-block"><?= htmlspecialchars($cp['kode_kurikulum']) ?></span>
                                            <?php if (!empty($cp['nama_fase'])): ?>
                                                <span class="badge bg-secondary-subtle text-dark border d-block"><?= htmlspecialchars($cp['nama_fase']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($cp['nama_mapel']) ?></td>
                                        <td>
                                            <div class="fw-bold font-monospace text-primary"><?= htmlspecialchars($cp['kode_cp']) ?></div>
                                            <?php if (!empty($cp['elemen'])): ?>
                                                <span class="badge bg-light text-secondary border mt-1"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($cp['elemen']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><div class="small text-dark" style="max-height: 120px; overflow-y: auto;"><?= nl2br(htmlspecialchars($cp['deskripsi'])) ?></div></td>
                                        <td>
                                            <?php if (empty($childTps)): ?>
                                                <div class="alert alert-light border py-1.5 px-2 mb-2 text-muted small"><i class="bi bi-exclamation-circle me-1"></i> Belum ada TP</div>
                                            <?php else: ?>
                                                <div class="tp-list-box mb-2" style="max-height: 180px; overflow-y: auto;">
                                                    <?php foreach ($childTps as $tp): ?>
                                                        <div class="p-2 mb-1.5 rounded-2 bg-light border d-flex justify-content-between align-items-start gap-2">
                                                            <div class="small">
                                                                <strong class="text-primary font-monospace"><?= htmlspecialchars($tp['kode_tp']) ?></strong>
                                                                <?php if (!empty($tp['materi_pokok'])): ?>
                                                                    <span class="text-muted fw-semibold">(<?= htmlspecialchars($tp['materi_pokok']) ?>):</span>
                                                                <?php endif; ?>
                                                                <span class="text-secondary"><?= htmlspecialchars($tp['deskripsi']) ?></span>
                                                            </div>
                                                            <div class="d-flex gap-1 flex-shrink-0">
                                                                <button type="button" class="btn btn-xs btn-outline-warning rounded px-1.5 py-0.5 btn-edit-tp" 
                                                                    title="Edit TP"
                                                                    data-bs-toggle="modal" data-bs-target="#modalEditTP"
                                                                    data-id="<?= $tp['id'] ?>"
                                                                    data-cp-id="<?= $tp['cp_id'] ?>"
                                                                    data-kode="<?= htmlspecialchars($tp['kode_tp']) ?>"
                                                                    data-materi="<?= htmlspecialchars($tp['materi_pokok'] ?? '') ?>"
                                                                    data-deskripsi="<?= htmlspecialchars($tp['deskripsi']) ?>">
                                                                    <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                                                                </button>
                                                                <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus Tujuan Pembelajaran (TP) ini?');">
                                                                    <?= Security::csrfField() ?>
                                                                    <input type="hidden" name="action" value="delete_tp">
                                                                    <input type="hidden" name="redirect_tab" value="cptp">
                                                                    <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                                                                    <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                                                                    <input type="hidden" name="id" value="<?= $tp['id'] ?>">
                                                                    <button type="submit" class="btn btn-xs btn-outline-danger rounded px-1.5 py-0.5" title="Hapus TP"><i class="bi bi-trash" style="font-size: 0.75rem;"></i></button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill fw-semibold btn-add-tp-for-cp px-2 py-1"
                                                data-bs-toggle="modal" data-bs-target="#modalAddTP"
                                                data-cp-id="<?= $cp['id'] ?>"
                                                data-cp-title="[<?= htmlspecialchars($cp['kode_cp']) ?>] <?= htmlspecialchars($cp['nama_mapel']) ?>">
                                                <i class="bi bi-plus-circle me-1"></i>+ Tambah TP
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-edit-cp" 
                                                    title="Edit CP"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditCP"
                                                    data-id="<?= $cp['id'] ?>"
                                                    data-kurikulum-id="<?= $cp['kurikulum_id'] ?>"
                                                    data-mapel-id="<?= $cp['mapel_id'] ?>"
                                                    data-fase-id="<?= $cp['fase_id'] ?? '' ?>"
                                                    data-kode="<?= htmlspecialchars($cp['kode_cp']) ?>"
                                                    data-elemen="<?= htmlspecialchars($cp['elemen'] ?? '') ?>"
                                                    data-deskripsi="<?= htmlspecialchars($cp['deskripsi']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus CP ini beserta seluruh TP turunannya?');">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="delete_cp">
                                                    <input type="hidden" name="redirect_tab" value="cptp">
                                                    <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                                                    <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                                                    <input type="hidden" name="id" value="<?= $cp['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus CP"><i class="bi bi-trash"></i></button>
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

        <!-- =================================================================== -->
        <!-- TAB 6: KOMPONEN & BOBOT PENILAIAN DINAMIS -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'komponen' ? 'show active' : '' ?>" id="tabKomponen">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 border-bottom pb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Konfigurasi Bobot Penilaian per Kurikulum</h5>
                        <p class="text-muted small mb-0">Rumus pengolahan nilai tidak di-hardcode di file PHP. Anda dapat menentukan komponen (Tugas, Kuis, STS, SAS, Praktik, Projek) dan bobot persentasenya per kurikulum.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted text-nowrap">Pilih Kurikulum:</label>
                        <select class="form-select form-select-sm fw-bold" onchange="location.href='<?= BASE_URL ?>index.php?url=admin/kurikulum&tab=komponen&kurikulum_id=' + this.value">
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>" <?= $selectedKurId == $kur['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kur['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" id="formBobot">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="save_komponen">
                    <input type="hidden" name="redirect_tab" value="komponen">
                    <input type="hidden" name="kurikulum_id" value="<?= $selectedKurId ?>">

                    <div class="table-responsive mb-3">
                        <table class="table table-hover align-middle border" id="tableKomponen">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:200px;">Kode Komponen</th>
                                    <th>Nama Komponen Penilaian</th>
                                    <th style="width:160px;">Bobot Persentase (%)</th>
                                    <th>Keterangan Tambahan</th>
                                    <th style="width:60px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($komponenList as $kIndex => $kp): ?>
                                    <tr>
                                        <td>
                                            <input type="text" name="kode_komponen[]" class="form-control form-control-sm font-monospace" value="<?= htmlspecialchars($kp['kode_komponen']) ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="nama_komponen[]" class="form-control form-control-sm" value="<?= htmlspecialchars($kp['nama_komponen']) ?>" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="bobot_persen[]" class="form-control form-control-sm bobot-input text-end fw-bold" step="0.5" min="0" max="100" value="<?= (float)$kp['bobot_persen'] ?>" required oninput="recalcTotalBobot()">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="deskripsi[]" class="form-control form-control-sm" value="<?= htmlspecialchars($kp['deskripsi'] ?? '') ?>" placeholder="Opsional">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotalBobot();"><i class="bi bi-x-lg"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">TOTAL AKUMULASI BOBOT:</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="totalBobotDisplay" class="form-control form-control-sm text-end fw-bold bg-white" readonly value="100%">
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <span id="badgeBobotStatus" class="badge bg-success py-1.5 px-3">Total Tepat 100%</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 fw-bold" onclick="addKomponenRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris Komponen
                        </button>
                        <button type="submit" id="btnSubmitBobot" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Simpan Konfigurasi Bobot
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
</main>

<!-- =========================================================================== -->
<!-- MODALS -->
<!-- =========================================================================== -->

<!-- Modal Add Kurikulum -->
<div class="modal fade" id="modalAddKurikulum" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_kurikulum">
                <input type="hidden" name="redirect_tab" value="kurikulum">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Tambah Kurikulum Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Kurikulum (contoh: K13, KMDK, K2029)</label>
                        <input type="text" name="kode" class="form-control font-monospace" placeholder="KMDK" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kurikulum Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Kurikulum Merdeka SMK" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tahun Mulai</label>
                            <input type="number" name="tahun_mulai" class="form-control" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tahun Selesai (Opsional)</label>
                            <input type="number" name="tahun_selesai" class="form-control" placeholder="Kosongkan jika aktif">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Kurikulum</label>
                        <select name="status" class="form-select">
                            <option value="aktif" selected>Aktif</option>
                            <option value="non-aktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Pedoman kurikulum sekolah..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Daftarkan Kurikulum</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Fase -->
<div class="modal fade" id="modalAddFase" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_fase">
                <input type="hidden" name="redirect_tab" value="fase">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Tambah Fase / Tingkat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum Induk</label>
                        <select name="kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Fase (misal: E, F, Tingkat 1)</label>
                        <input type="text" name="kode" class="form-control font-monospace" placeholder="E" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap Fase</label>
                        <input type="text" name="nama" class="form-control" placeholder="Fase E (Kelas X)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Tingkat Kelas (misal: X atau XI,XII)</label>
                        <input type="text" name="tingkat_kelas" class="form-control" placeholder="X">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Fase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Assign Rombel Kurikulum -->
<div class="modal fade" id="modalAssignRombel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="assign_rombel">
                <input type="hidden" name="redirect_tab" value="rombel">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Pasang Kurikulum ke Rombel Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" class="form-select" required>
                            <?php foreach ($taList as $ta): ?>
                                <option value="<?= $ta['id'] ?>" <?= !empty($ta['is_active']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ta['tahun_ajaran'] ?? $ta['tahun']) ?> (Semester <?= htmlspecialchars($ta['semester']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Rombel Kelas</label>
                        <select name="rombel_id" class="form-select" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (Tingkat <?= $k['tingkat'] ?> - <?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum Yang Berlaku</label>
                        <select name="kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Fase / Jenjang (Opsional)</label>
                        <select name="fase_id" class="form-select">
                            <option value="">-- Tanpa Fase Khusus --</option>
                            <?php foreach ($allFaseList as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Hubungan</label>
                        <select name="status" class="form-select">
                            <option value="aktif" selected>Aktif (Berjalan Saat Ini)</option>
                            <option value="selesai">Selesai / Riwayat Lalu</option>
                        </select>
                        <small class="text-muted">Hanya boleh ada 1 kurikulum berstatus 'Aktif' per rombel pada tahun ajaran yang dipilih.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Terapkan Kurikulum</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Struktur Mapel -->
<div class="modal fade" id="modalAddStrukturMapel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="add_mapel">
                <input type="hidden" name="redirect_tab" value="struktur">
                <input type="hidden" name="kurikulum_id" value="<?= $selectedKurId ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Kaitkan Mata Pelajaran ke Kurikulum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" required>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fase Target</label>
                        <select name="fase_id" class="form-select">
                            <option value="">-- Semua Fase --</option>
                            <?php foreach ($faseKurikulumList as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kelompok Mapel</label>
                            <select name="kelompok_mapel" class="form-select">
                                <option value="Kejuruan">Kejuruan</option>
                                <option value="Umum">Umum</option>
                                <option value="Pilihan">Pilihan</option>
                                <option value="Muatan Lokal">Muatan Lokal</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tingkat Kelas</label>
                            <select name="tingkat" class="form-select">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Alokasi JP/Minggu</label>
                            <input type="number" name="alokasi_jp" class="form-control" value="2" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">KKM Standar</label>
                            <input type="number" name="kkm" class="form-control" value="75" min="0" max="100" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Kaitkan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add CP -->
<div class="modal fade" id="modalAddCP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_cp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Capaian Pembelajaran (CP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum</label>
                        <select name="kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>" <?= ($filterCpKurId == $kur['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" required>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>" <?= ($filterCpMapelId == $mp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Fase</label>
                        <select name="fase_id" class="form-select">
                            <option value="">-- Tanpa Fase Khusus --</option>
                            <?php foreach ($allFaseList as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= ($filterCpFaseId == $f['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode CP (contoh: CP-RPL-01)</label>
                            <input type="text" name="kode_cp" class="form-control font-monospace" placeholder="CP-RPL-01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Elemen / Ranah</label>
                            <input type="text" name="elemen" class="form-control" placeholder="Algoritma & Pemrograman">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Capaian Pembelajaran</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Peserta didik mampu memahami..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan CP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit CP -->
<div class="modal fade" id="modalEditCP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_cp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <input type="hidden" name="id" id="edit_cp_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Capaian Pembelajaran (CP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum</label>
                        <select name="kurikulum_id" id="edit_cp_kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran</label>
                        <select name="mapel_id" id="edit_cp_mapel_id" class="form-select" required>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Fase</label>
                        <select name="fase_id" id="edit_cp_fase_id" class="form-select">
                            <option value="">-- Tanpa Fase Khusus --</option>
                            <?php foreach ($allFaseList as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode CP</label>
                            <input type="text" name="kode_cp" id="edit_cp_kode" class="form-control font-monospace" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Elemen / Ranah</label>
                            <input type="text" name="elemen" id="edit_cp_elemen" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Capaian Pembelajaran</label>
                        <textarea name="deskripsi" id="edit_cp_deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Perbarui CP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add TP -->
<div class="modal fade" id="modalAddTP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_tp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Tujuan Pembelajaran (TP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Induk Capaian Pembelajaran (CP)</label>
                        <select name="cp_id" id="add_tp_cp_id" class="form-select" required>
                            <?php foreach (($allCpForDropdown ?? $cpList) as $c): ?>
                                <option value="<?= $c['id'] ?>">[<?= htmlspecialchars($c['kode_kurikulum'] ?? '') ?> | <?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel']) ?> - <?= htmlspecialchars(substr($c['deskripsi'], 0, 45)) ?>...</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode TP (misal: TP-01.1)</label>
                            <input type="text" name="kode_tp" class="form-control font-monospace" placeholder="TP-01.1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Materi Pokok</label>
                            <input type="text" name="materi_pokok" class="form-control" placeholder="Arsitektur Database">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Tujuan Pembelajaran</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Memahami dan mempraktikkan konfigurasi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan TP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit TP -->
<div class="modal fade" id="modalEditTP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_tp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <input type="hidden" name="id" id="edit_tp_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Tujuan Pembelajaran (TP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Induk Capaian Pembelajaran (CP)</label>
                        <select name="cp_id" id="edit_tp_cp_id" class="form-select" required>
                            <?php foreach (($allCpForDropdown ?? $cpList) as $c): ?>
                                <option value="<?= $c['id'] ?>">[<?= htmlspecialchars($c['kode_kurikulum'] ?? '') ?> | <?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel']) ?> - <?= htmlspecialchars(substr($c['deskripsi'], 0, 45)) ?>...</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode TP</label>
                            <input type="text" name="kode_tp" id="edit_tp_kode" class="form-control font-monospace" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Materi Pokok</label>
                            <input type="text" name="materi_pokok" id="edit_tp_materi" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Tujuan Pembelajaran</label>
                        <textarea name="deskripsi" id="edit_tp_deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Perbarui TP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function recalcTotalBobot() {
    const inputs = document.querySelectorAll('.bobot-input');
    let total = 0;
    inputs.forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });

    const display = document.getElementById('totalBobotDisplay');
    const badge = document.getElementById('badgeBobotStatus');
    const submitBtn = document.getElementById('btnSubmitBobot');

    if (display) {
        display.value = total.toFixed(1) + '%';
    }

    if (badge && submitBtn) {
        if (Math.abs(total - 100.0) < 0.1) {
            badge.className = 'badge bg-success py-1.5 px-3';
            badge.textContent = '✓ Total Tepat 100%';
            if (display) {
                display.classList.remove('text-danger');
                display.classList.add('text-success');
            }
            submitBtn.disabled = false;
        } else {
            badge.className = 'badge bg-danger py-1.5 px-3';
            badge.textContent = '⚠️ Wajib 100% (Selisih: ' + (100 - total).toFixed(1) + '%)';
            if (display) {
                display.classList.remove('text-success');
                display.classList.add('text-danger');
            }
            submitBtn.disabled = true;
        }
    }
}

function addKomponenRow() {
    const tbody = document.querySelector('#tableKomponen tbody');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="kode_komponen[]" class="form-control form-control-sm font-monospace" placeholder="kode" required></td>
        <td><input type="text" name="nama_komponen[]" class="form-control form-control-sm" placeholder="Nama Komponen" required></td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="bobot_persen[]" class="form-control form-control-sm bobot-input text-end fw-bold" step="0.5" min="0" max="100" value="10" required oninput="recalcTotalBobot()">
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td><input type="text" name="deskripsi[]" class="form-control form-control-sm" placeholder="Opsional"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotalBobot();"><i class="bi bi-x-lg"></i></button></td>
    `;
    tbody.appendChild(tr);
    recalcTotalBobot();
}

document.addEventListener('DOMContentLoaded', () => {
    recalcTotalBobot();

    // Direct "+ Tambah TP" from CP row
    document.querySelectorAll('.btn-add-tp-for-cp').forEach(btn => {
        btn.addEventListener('click', function() {
            const cpId = this.dataset.cpId;
            const select = document.getElementById('add_tp_cp_id');
            if (select && cpId) {
                select.value = cpId;
            }
        });
    });

    // Populate Edit CP Modal
    document.querySelectorAll('.btn-edit-cp').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_cp_id').value = this.dataset.id || '';
            document.getElementById('edit_cp_kode').value = this.dataset.kode || '';
            document.getElementById('edit_cp_elemen').value = this.dataset.elemen || '';
            document.getElementById('edit_cp_deskripsi').value = this.dataset.deskripsi || '';
            
            const selKur = document.getElementById('edit_cp_kurikulum_id');
            if (selKur && this.dataset.kurikulumId) selKur.value = this.dataset.kurikulumId;

            const selMapel = document.getElementById('edit_cp_mapel_id');
            if (selMapel && this.dataset.mapelId) selMapel.value = this.dataset.mapelId;

            const selFase = document.getElementById('edit_cp_fase_id');
            if (selFase) selFase.value = this.dataset.faseId || '';
        });
    });

    // Populate Edit TP Modal
    document.querySelectorAll('.btn-edit-tp').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_tp_id').value = this.dataset.id || '';
            document.getElementById('edit_tp_kode').value = this.dataset.kode || '';
            document.getElementById('edit_tp_materi').value = this.dataset.materi || '';
            document.getElementById('edit_tp_deskripsi').value = this.dataset.deskripsi || '';

            const selCp = document.getElementById('edit_tp_cp_id');
            if (selCp && this.dataset.cpId) selCp.value = this.dataset.cpId;
        });
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
