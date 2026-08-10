<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Kelola Data Siswa</h4>
                <p class="text-muted small mb-0">Daftar Peserta Didik SMK Muthia Harapan Cicalengka.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=admin/templateSiswa" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Unduh Template Excel
                </a>
                <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
                    <i class="bi bi-file-earmark-excel me-1"></i> Import Excel Siswa
                </button>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddSiswa">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Siswa Baru
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar (Auto Filter / Instant Typing Search) -->
        <div class="card card-custom p-3 p-md-4 mb-4 shadow-sm border-0 rounded-4">
            <form action="<?= BASE_URL ?>index.php" method="GET" id="autoFilterForm" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="admin/siswa">

                <div class="col-md-5 col-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search text-primary me-1"></i> Cari NISN / NIS / Nama (Otomatis)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" id="autoSearchInput" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control bg-light border-start-0 rounded-end-3" placeholder="Ketik NISN, NIS, atau Nama..." autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-door-open-fill text-info me-1"></i> Filter Kelas</label>
                    <select name="kelas_id" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $kls): ?>
                            <option value="<?= $kls['id'] ?>" <?= (isset($_GET['kelas_id']) && (int)$_GET['kelas_id'] === (int)$kls['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kls['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-journal-bookmark-fill text-warning me-1"></i> Filter Jurusan</label>
                    <select name="jurusan_id" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Jurusan --</option>
                        <?php foreach ($jurusanList as $jur): ?>
                            <option value="<?= $jur['id'] ?>" <?= (isset($_GET['jurusan_id']) && (int)$_GET['jurusan_id'] === (int)$jur['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($jur['nama_jurusan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1 col-12">
                    <?php if (!empty($_GET['q']) || !empty($_GET['kelas_id']) || !empty($_GET['jurusan_id'])): ?>
                        <a href="<?= BASE_URL ?>index.php?url=admin/siswa" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm fw-semibold" title="Filter Data">
                            <i class="bi bi-funnel-fill"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('autoSearchInput');
            const form = document.getElementById('autoFilterForm');
            if (!searchInput || !form) return;

            let debounceTimer;
            searchInput.addEventListener('input', function() {
                // Instant client-side DataTables filter if available
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                    window.jQuery('.datatable').DataTable().search(this.value).draw();
                }

                // Debounced auto-submit for server-side query persistence
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    form.submit();
                }, 450);
            });

            // Maintain cursor position at end of text after reload
            if (searchInput.value) {
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            }
        });
        </script>

        <?php if (!empty($_GET['q']) || !empty($_GET['kelas_id']) || !empty($_GET['jurusan_id']) || !empty($selectedKelas)): ?>
            <div class="alert alert-primary border-0 rounded-4 shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-funnel-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Hasil Penyaringan Data Siswa: <strong><?= count($siswaList) ?> Siswa Ditemukan</strong></h6>
                        <small class="text-muted">
                            <?php if (!empty($_GET['q'])): ?>
                                Kata Kunci: <strong>"<?= htmlspecialchars($_GET['q']) ?>"</strong> |
                            <?php endif; ?>
                            <?php if (!empty($_GET['kelas_id'])): ?>
                                <?php 
                                    $klsName = 'Kelas #' . $_GET['kelas_id'];
                                    foreach ($kelasList as $k) { if ((int)$k['id'] === (int)$_GET['kelas_id']) { $klsName = $k['nama_kelas']; break; } }
                                ?>
                                Kelas: <strong><?= htmlspecialchars($klsName) ?></strong> |
                            <?php endif; ?>
                            <?php if (!empty($_GET['jurusan_id'])): ?>
                                <?php 
                                    $jurName = 'Jurusan #' . $_GET['jurusan_id'];
                                    foreach ($jurusanList as $j) { if ((int)$j['id'] === (int)$_GET['jurusan_id']) { $jurName = $j['nama_jurusan']; break; } }
                                ?>
                                Jurusan: <strong><?= htmlspecialchars($jurName) ?></strong> |
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>index.php?url=admin/siswa" class="btn btn-sm btn-outline-dark fw-semibold rounded-pill">
                    <i class="bi bi-x-circle me-1"></i> Tampilkan Semua Siswa
                </a>
            </div>
        <?php endif; ?>

        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>JK</th>
                            <th class="text-center" style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswaList as $i => $s): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><code><?= htmlspecialchars($s['nis']) ?></code> / <small><?= htmlspecialchars($s['nisn']) ?></small></td>
                                <td class="fw-bold"><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($s['nama_kelas']) ?></span></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($s['nama_jurusan']) ?></span></td>
                                <td><?= $s['jenis_kelamin'] ?></td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Detail Button -->
                                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailSiswa<?= $s['id'] ?>" title="Detail Siswa">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                        <!-- Edit Button -->
                                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalEditSiswa<?= $s['id'] ?>" title="Edit Siswa">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <!-- Delete Form -->
                                        <form action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?');" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <button class="btn btn-sm btn-danger" title="Hapus Siswa"><i class="bi bi-trash"></i> Hapus</button>
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

<!-- Modal Add Siswa -->
<div class="modal fade" id="modalAddSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIS</label>
                            <input type="text" name="nis" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NISN</label>
                            <input type="text" name="nisn" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap Siswa</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jurusan</label>
                            <select name="jurusan_id" class="form-select" required>
                                <?php foreach ($jurusanList as $j): ?>
                                    <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
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
                            <label class="form-label small fw-semibold">No Telepon / WA</label>
                            <input type="text" name="no_telepon" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Data Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Detail & Edit Siswa -->
<?php foreach ($siswaList as $s): ?>
    <!-- Modal Detail Siswa -->
    <div class="modal fade" id="modalDetailSiswa<?= $s['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-info-circle text-info me-2"></i>Detail Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center fw-bold fs-3 mb-2" style="width:70px; height:70px;">
                            <?= strtoupper(substr($s['nama_lengkap'], 0, 1)) ?>
                        </div>
                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['nama_lengkap']) ?></h6>
                        <small class="text-muted">NIS: <?= htmlspecialchars($s['nis']) ?> | NISN: <?= htmlspecialchars($s['nisn']) ?></small>
                    </div>
                    <table class="table table-sm border-0 small">
                        <tr><td class="text-muted" style="width:35%;">Kelas</td><td class="fw-semibold">: <?= htmlspecialchars($s['nama_kelas']) ?></td></tr>
                        <tr><td class="text-muted">Jurusan</td><td class="fw-semibold">: <?= htmlspecialchars($s['nama_jurusan']) ?></td></tr>
                        <tr><td class="text-muted">Username</td><td class="fw-semibold">: <?= htmlspecialchars($s['username'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Email</td><td class="fw-semibold">: <?= htmlspecialchars($s['email']) ?></td></tr>
                        <tr><td class="text-muted">Jenis Kelamin</td><td class="fw-semibold">: <?= $s['jenis_kelamin'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></td></tr>
                        <tr><td class="text-muted">No Telepon / WA</td><td class="fw-semibold">: <?= htmlspecialchars($s['no_telepon'] ?? '-') ?></td></tr>
                    </table>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Siswa -->
    <div class="modal fade" id="modalEditSiswa<?= $s['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NIS</label>
                                <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($s['nis']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NISN</label>
                                <input type="text" name="nisn" class="form-control" value="<?= htmlspecialchars($s['nisn']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap Siswa</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($s['nama_lengkap']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <?php foreach ($kelasList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= $k['id'] == $s['kelas_id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jurusan</label>
                                <select name="jurusan_id" class="form-select" required>
                                    <?php foreach ($jurusanList as $j): ?>
                                        <option value="<?= $j['id'] ?>" <?= $j['id'] == $s['jurusan_id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select">
                                    <option value="L" <?= $s['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-Laki</option>
                                    <option value="P" <?= $s['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Password Baru (Kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">No Telepon / WA</label>
                                <input type="text" name="no_telepon" class="form-control" value="<?= htmlspecialchars($s['no_telepon'] ?? '') ?>">
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

<!-- Modal Import Siswa -->
<div class="modal fade" id="modalImportSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Siswa Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/importSiswa" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <p class="small text-muted mb-3">Unduh template Excel terlebih dahulu, isi data siswa sesuai format, lalu unggah file format <code>.csv</code> atau <code>.xlsx</code>.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File Excel / CSV</label>
                        <input type="file" name="excel_file" class="form-control" accept=".csv, .xls, .xlsx" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <small class="fw-bold text-dark d-block mb-1">Panduan Pengisian Template:</small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Kolom wajib: NIS, NISN, & Nama Lengkap</li>
                            <li>Kesesuaian Kelas & Jurusan: Isi nama kelas (contoh: <code>X RPL 1</code>) dan nama jurusan</li>
                            <li>Password default: <code>123456</code> (jika diubah/kosong)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <a href="<?= BASE_URL ?>index.php?url=admin/templateSiswa" class="btn btn-outline-success btn-sm">
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
