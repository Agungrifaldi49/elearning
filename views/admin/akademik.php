<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Manajemen Akademik</h4>
        <p class="text-muted small mb-4">Pengelolaan Jurusan, Kelas, dan Mata Pelajaran SMK Muthia Harapan Cicalengka.</p>

        <ul class="nav nav-pills mb-4 gap-2" id="akademikTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active rounded-pill px-4" data-bs-toggle="tab" data-bs-target="#tabJurusan"><i class="bi bi-diagram-3 me-1"></i> Jurusan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-4" data-bs-toggle="tab" data-bs-target="#tabKelas"><i class="bi bi-building me-1"></i> Kelas (Rombel)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-4" data-bs-toggle="tab" data-bs-target="#tabMapel"><i class="bi bi-book me-1"></i> Mata Pelajaran</button>
            </li>
        </ul>

        <div class="tab-content" id="akademikTabContent">
            <!-- Tab 1: Jurusan -->
            <div class="tab-pane fade show active" id="tabJurusan">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Daftar Jurusan</h6>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddJurusan"><i class="bi bi-plus-circle me-1"></i> Tambah Jurusan</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Jurusan</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jurusanList as $j): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($j['kode_jurusan']) ?></code></td>
                                        <td class="fw-bold"><?= htmlspecialchars($j['nama_jurusan']) ?></td>
                                        <td><?= htmlspecialchars($j['deskripsi'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailJurusan<?= $j['id'] ?>" title="Detail"><i class="bi bi-eye"></i> Detail</button>
                                                <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalEditJurusan<?= $j['id'] ?>" title="Edit"><i class="bi bi-pencil-square"></i> Edit</button>
                                                <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST" onsubmit="return confirm('Hapus jurusan ini?');" class="d-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="target" value="jurusan">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kelas -->
            <div class="tab-pane fade" id="tabKelas">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Daftar Kelas (Rombel)</h6>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddKelas"><i class="bi bi-plus-circle me-1"></i> Tambah Kelas</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Tingkat</th>
                                    <th>Nama Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Wali Kelas</th>
                                    <th class="text-center" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kelasList as $k): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= $k['tingkat'] ?></span></td>
                                        <td class="fw-bold"><?= htmlspecialchars($k['nama_kelas']) ?></td>
                                        <td><?= htmlspecialchars($k['nama_jurusan']) ?></td>
                                        <td>
                                            <?php if (!empty($k['nama_walikelas'])): ?>
                                                <span class="fw-semibold text-dark"><i class="bi bi-person-badge me-1 text-primary"></i><?= htmlspecialchars($k['nama_walikelas']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Belum Ditentukan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailKelas<?= $k['id'] ?>" title="Detail"><i class="bi bi-eye"></i> Detail</button>
                                                <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalEditKelas<?= $k['id'] ?>" title="Edit"><i class="bi bi-pencil-square"></i> Edit</button>
                                                <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST" onsubmit="return confirm('Hapus kelas ini?');" class="d-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="target" value="kelas">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Mapel -->
            <div class="tab-pane fade" id="tabMapel">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Daftar Mata Pelajaran</h6>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddMapel"><i class="bi bi-plus-circle me-1"></i> Tambah Mapel</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Mapel</th>
                                    <th>Nama Mata Pelajaran</th>
                                    <th>Jurusan Spesifik</th>
                                    <th class="text-center" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mapelList as $m): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($m['kode_mapel']) ?></code></td>
                                        <td class="fw-bold"><?= htmlspecialchars($m['nama_mapel']) ?></td>
                                        <td><?= htmlspecialchars($m['nama_jurusan'] ?? 'Semua Jurusan / Umum') ?></td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailMapel<?= $m['id'] ?>" title="Detail"><i class="bi bi-eye"></i> Detail</button>
                                                <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalEditMapel<?= $m['id'] ?>" title="Edit"><i class="bi bi-pencil-square"></i> Edit</button>
                                                <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?');" class="d-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="target" value="mapel">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ALL MODALS PLACED OUTSIDE TABLES -->

<!-- Modal Add Jurusan -->
<div class="modal fade" id="modalAddJurusan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Jurusan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="target" value="jurusan">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Jurusan</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: RPL" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Jurusan</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Jurusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Kelas -->
<div class="modal fade" id="modalAddKelas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="target" value="kelas">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: X RPL 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tingkat</label>
                        <select name="tingkat" class="form-select">
                            <option value="X">Tingkat X</option>
                            <option value="XI">Tingkat XI</option>
                            <option value="XII">Tingkat XII</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jurusan</label>
                        <select name="jurusan_id" class="form-select" required>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Mapel -->
<div class="modal fade" id="modalAddMapel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="target" value="mapel">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                            <span>Kode Mapel</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="bi bi-magic me-1"></i>Otomatis Sistem</span>
                        </label>
                        <input type="text" name="kode" class="form-control bg-light" placeholder="Dibuat Otomatis Sistem (contoh: MPL-RPL-09)" readonly tabindex="-1">
                        <small class="text-muted" style="font-size:0.72rem;">Kode Mapel akan digenerasi otomatis secara unik oleh sistem berdasarkan jurusan & urutan ID.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Mapel</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Pemrograman Web" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jurusan Spesifik (Optional)</label>
                        <select name="jurusan_id" class="form-select">
                            <option value="0">Semua Jurusan / Umum</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Detail & Edit Jurusan -->
<?php foreach ($jurusanList as $j): ?>
    <!-- Modal Detail Jurusan -->
    <div class="modal fade" id="modalDetailJurusan<?= $j['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-info-circle text-info me-2"></i>Detail Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm border-0 small">
                        <tr><td class="text-muted" style="width:35%;">Kode Jurusan</td><td class="fw-semibold">: <code><?= htmlspecialchars($j['kode_jurusan']) ?></code></td></tr>
                        <tr><td class="text-muted">Nama Jurusan</td><td class="fw-semibold">: <?= htmlspecialchars($j['nama_jurusan']) ?></td></tr>
                        <tr><td class="text-muted">Deskripsi</td><td class="fw-semibold">: <?= htmlspecialchars($j['deskripsi'] ?? '-') ?></td></tr>
                    </table>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Jurusan -->
    <div class="modal fade" id="modalEditJurusan<?= $j['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="target" value="jurusan">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $j['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode Jurusan</label>
                            <input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($j['kode_jurusan']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Jurusan</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($j['nama_jurusan']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2"><?= htmlspecialchars($j['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modals Detail & Edit Kelas -->
<?php foreach ($kelasList as $k): ?>
    <!-- Modal Detail Kelas -->
    <div class="modal fade" id="modalDetailKelas<?= $k['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-info-circle text-info me-2"></i>Detail Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm border-0 small">
                        <tr><td class="text-muted" style="width:35%;">Nama Kelas</td><td class="fw-semibold">: <?= htmlspecialchars($k['nama_kelas']) ?></td></tr>
                        <tr><td class="text-muted">Tingkat</td><td class="fw-semibold">: <?= htmlspecialchars($k['tingkat']) ?></td></tr>
                        <tr><td class="text-muted">Jurusan</td><td class="fw-semibold">: <?= htmlspecialchars($k['nama_jurusan']) ?></td></tr>
                    </table>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kelas -->
    <div class="modal fade" id="modalEditKelas<?= $k['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="target" value="kelas">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $k['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control" value="<?= htmlspecialchars($k['nama_kelas']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tingkat</label>
                            <select name="tingkat" class="form-select">
                                <option value="X" <?= $k['tingkat'] === 'X' ? 'selected' : '' ?>>Tingkat X</option>
                                <option value="XI" <?= $k['tingkat'] === 'XI' ? 'selected' : '' ?>>Tingkat XI</option>
                                <option value="XII" <?= $k['tingkat'] === 'XII' ? 'selected' : '' ?>>Tingkat XII</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jurusan</label>
                            <select name="jurusan_id" class="form-select" required>
                                <?php foreach ($jurusanList as $j): ?>
                                    <option value="<?= $j['id'] ?>" <?= $j['id'] == $k['jurusan_id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modals Detail & Edit Mapel -->
<?php foreach ($mapelList as $m): ?>
    <!-- Modal Detail Mapel -->
    <div class="modal fade" id="modalDetailMapel<?= $m['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-info-circle text-info me-2"></i>Detail Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm border-0 small">
                        <tr><td class="text-muted" style="width:35%;">Kode Mapel</td><td class="fw-semibold">: <code><?= htmlspecialchars($m['kode_mapel']) ?></code></td></tr>
                        <tr><td class="text-muted">Nama Mapel</td><td class="fw-semibold">: <?= htmlspecialchars($m['nama_mapel']) ?></td></tr>
                        <tr><td class="text-muted">Jurusan Spesifik</td><td class="fw-semibold">: <?= htmlspecialchars($m['nama_jurusan'] ?? 'Semua Jurusan / Umum') ?></td></tr>
                    </table>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Mapel -->
    <div class="modal fade" id="modalEditMapel<?= $m['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/akademik" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="target" value="mapel">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode Mapel</label>
                            <input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($m['kode_mapel']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Mapel</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($m['nama_mapel']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jurusan Spesifik (Optional)</label>
                            <select name="jurusan_id" class="form-select">
                                <option value="0">Semua Jurusan / Umum</option>
                                <?php foreach ($jurusanList as $j): ?>
                                    <option value="<?= $j['id'] ?>" <?= $j['id'] == $m['jurusan_id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
