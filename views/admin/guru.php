<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>Kelola Data Guru</h4>
                <p class="text-muted small mb-0">Daftar Tenaga Pengajar & Guru SMK Muthia Harapan Cicalengka.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=admin/templateGuru" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Unduh Template Excel
                </a>
                <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
                    <i class="bi bi-file-earmark-excel me-1"></i> Import Excel Guru
                </button>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddGuru">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Guru Baru
                </button>
            </div>
        </div>

        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>JK</th>
                            <th>No Telepon</th>
                            <th>Email</th>
                            <th class="text-center" style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guruList as $i => $g): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><code><?= htmlspecialchars($g['nip']) ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($g['nama_lengkap']) ?></td>
                                <td><span class="badge bg-secondary"><?= $g['jenis_kelamin'] ?></span></td>
                                <td><?= htmlspecialchars($g['no_telepon']) ?></td>
                                <td><?= htmlspecialchars($g['email']) ?></td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Button Detail -->
                                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailGuru<?= $g['id'] ?>" title="Detail Guru">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                        <!-- Button Edit -->
                                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalEditGuru<?= $g['id'] ?>" title="Edit Guru">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <!-- Form Delete -->
                                        <form action="<?= BASE_URL ?>index.php?url=admin/guru" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?');" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                            <button class="btn btn-sm btn-danger" title="Hapus Guru"><i class="bi bi-trash"></i> Hapus</button>
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
</main>

<!-- ALL MODALS PLACED OUTSIDE TABLE -->

<!-- Modal Add Guru -->
<div class="modal fade" id="modalAddGuru" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Guru Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/guru" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIP</label>
                            <input type="text" name="nip" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username Login</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Login</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No Telepon / WA</label>
                            <input type="text" name="no_telepon" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <input type="text" name="alamat" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Data Guru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Detail & Edit Guru -->
<?php foreach ($guruList as $g): ?>
    <!-- Modal Detail Guru -->
    <div class="modal fade" id="modalDetailGuru<?= $g['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-info-circle text-info me-2"></i>Detail Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-3 mb-2" style="width:70px; height:70px;">
                            <?= strtoupper(substr($g['nama_lengkap'], 0, 1)) ?>
                        </div>
                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($g['nama_lengkap']) ?></h6>
                        <small class="text-muted">NIP: <?= htmlspecialchars($g['nip']) ?></small>
                    </div>
                    <table class="table table-sm border-0 small">
                        <tr><td class="text-muted" style="width:35%;">Username</td><td class="fw-semibold">: <?= htmlspecialchars($g['username'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Email</td><td class="fw-semibold">: <?= htmlspecialchars($g['email']) ?></td></tr>
                        <tr><td class="text-muted">Jenis Kelamin</td><td class="fw-semibold">: <?= $g['jenis_kelamin'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></td></tr>
                        <tr><td class="text-muted">No Telepon / WA</td><td class="fw-semibold">: <?= htmlspecialchars($g['no_telepon']) ?></td></tr>
                        <tr><td class="text-muted">Alamat</td><td class="fw-semibold">: <?= htmlspecialchars($g['alamat'] ?? '-') ?></td></tr>
                    </table>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Guru -->
    <div class="modal fade" id="modalEditGuru<?= $g['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/guru" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $g['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NIP</label>
                                <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($g['nip']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($g['nama_lengkap']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($g['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Password Baru (Kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select">
                                    <option value="L" <?= $g['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-Laki</option>
                                    <option value="P" <?= $g['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">No Telepon / WA</label>
                                <input type="text" name="no_telepon" class="form-control" value="<?= htmlspecialchars($g['no_telepon']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat</label>
                                <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($g['alamat'] ?? '') ?>">
                            </div>
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

<!-- Modal Import Guru -->
<div class="modal fade" id="modalImportGuru" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Guru Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/importGuru" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <p class="small text-muted mb-3">Unduh template Excel terlebih dahulu, isi data guru sesuai format, lalu unggah file format <code>.csv</code> atau <code>.xlsx</code>.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File Excel / CSV</label>
                        <input type="file" name="excel_file" class="form-control" accept=".csv, .xls, .xlsx" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <small class="fw-bold text-dark d-block mb-1">Panduan Pengisian Template:</small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Kolom wajib: NIP & Nama Lengkap</li>
                            <li>Password default: <code>123456</code> (jika diubah/kosong)</li>
                            <li>Jenis Kelamin: <code>L</code> (Laki-Laki) atau <code>P</code> (Perempuan)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <a href="<?= BASE_URL ?>index.php?url=admin/templateGuru" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-download me-1"></i> Unduh Template
                    </a>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
