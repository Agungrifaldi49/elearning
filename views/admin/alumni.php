<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$alumniList = is_array($alumniList ?? null) ? $alumniList : [];
$kelasList = is_array($kelasList ?? null) ? $kelasList : [];
$allKelasList = is_array($allKelasList ?? null) ? $allKelasList : $kelasList;
$jurusanList = is_array($jurusanList ?? null) ? $jurusanList : [];
$alumniStats = is_array($alumniStats ?? null) ? $alumniStats : ['total' => count($alumniList), 'laki' => 0, 'perempuan' => 0, 'per_jurusan' => []];
?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-mortarboard-fill text-success me-2"></i>Data Alumni & Kelulusan</h4>
                <p class="text-muted small mb-0">Kelola arsip peserta didik yang telah lulus secara mandiri agar data siswa aktif tetap rapi dan profesional.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=admin/siswa" class="btn btn-outline-primary shadow-sm fw-semibold">
                    <i class="bi bi-people-fill me-1"></i> Ke Data Siswa Aktif
                </a>
                <a href="<?= BASE_URL ?>index.php?url=admin/cetakAlumni<?= !empty($_SERVER['QUERY_STRING']) ? '&' . http_build_query(array_diff_key($_GET, ['url' => ''])) : '' ?>" target="_blank" class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Dokumen
                </a>
                <a href="<?= BASE_URL ?>index.php?url=admin/exportAlumniCsv<?= !empty($_SERVER['QUERY_STRING']) ? '&' . http_build_query(array_diff_key($_GET, ['url' => ''])) : '' ?>" class="btn btn-outline-success shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor CSV
                </a>
                <button type="button" class="btn btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBulkAlumni">
                    <i class="bi bi-check2-all me-1"></i> Aksi Massal Alumni
                </button>
            </div>
        </div>

        <!-- Alumni Summary Statistic Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Alumni</span>
                            <h3 class="fw-bold text-success mb-0 mt-1"><?= (int)($alumniStats['total'] ?? count($alumniList)) ?></h3>
                            <small class="text-muted">Peserta Didik Lulus</small>
                        </div>
                        <div class="bg-success-subtle text-success rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-mortarboard-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Alumni Laki-laki</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1"><?= (int)($alumniStats['laki'] ?? 0) ?></h3>
                            <small class="text-muted">Total L</small>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-gender-male fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Alumni Perempuan</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1"><?= (int)($alumniStats['perempuan'] ?? 0) ?></h3>
                            <small class="text-muted">Total P</small>
                        </div>
                        <div class="bg-danger-subtle text-danger rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-gender-female fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Sebaran Jurusan</span>
                            <div class="d-flex gap-1 flex-wrap mt-1">
                                <?php if (!empty($alumniStats['per_jurusan'])): ?>
                                    <?php foreach ($alumniStats['per_jurusan'] as $jStat): ?>
                                        <span class="badge bg-light text-dark border small fw-normal">
                                            <?= htmlspecialchars($jStat['nama_jurusan']) ?>: <strong><?= $jStat['cnt'] ?></strong>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted small">Belum ada data</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="bg-warning-subtle text-warning rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-diagram-3-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card card-custom p-3 p-md-4 mb-4 shadow-sm border-0 rounded-4">
            <form action="<?= BASE_URL ?>index.php" method="GET" id="autoFilterForm" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="admin/alumni">

                <div class="col-md-4 col-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search text-primary me-1"></i> Cari NISN / NIS / Nama Alumni</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" id="autoSearchInput" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control bg-light border-start-0" placeholder="Ketik NISN, NIS, atau Nama..." autocomplete="off">
                        <button type="submit" class="btn btn-primary rounded-end-3 px-3"><i class="bi bi-search me-1"></i> Cari</button>
                    </div>
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

                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-door-open-fill text-info me-1"></i> Kelas Terakhir</label>
                    <select name="kelas_id" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach (($allKelasList ?? $kelasList) as $kls): ?>
                            <option value="<?= $kls['id'] ?>" <?= (isset($_GET['kelas_id']) && (int)$_GET['kelas_id'] === (int)$kls['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kls['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-gender-ambiguous text-secondary me-1"></i> Jenis Kelamin</label>
                    <div class="d-flex gap-2">
                        <select name="jk" class="form-select rounded-3" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            <option value="L" <?= (isset($_GET['jk']) && $_GET['jk'] === 'L') ? 'selected' : '' ?>>Laki-laki (L)</option>
                            <option value="P" <?= (isset($_GET['jk']) && $_GET['jk'] === 'P') ? 'selected' : '' ?>>Perempuan (P)</option>
                        </select>
                        <?php if (!empty($_GET['q']) || !empty($_GET['kelas_id']) || !empty($_GET['jurusan_id']) || !empty($_GET['jk'])): ?>
                            <a href="<?= BASE_URL ?>index.php?url=admin/alumni" class="btn btn-outline-secondary rounded-3 px-3" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('autoSearchInput');
            if (!searchInput) return;

            // Live filter instan pada tabel menggunakan DataTables tanpa memicu reload halaman
            searchInput.addEventListener('input', function() {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                    window.jQuery('#tableAlumni').DataTable().search(this.value).draw();
                }
            });
        });
        </script>

        <!-- Main Data Alumni Table Card -->
        <div class="card card-custom p-3 p-md-4 mb-4 shadow-sm border-0 rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/alumni" method="POST" id="formTableAlumni">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" id="tableActionInput" value="">
                <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-mortarboard-fill me-1"></i> Total <?= count($alumniList) ?> Alumni
                        </span>
                        <span id="selectedCountBadge" class="badge bg-primary px-3 py-2 rounded-pill d-none">
                            <span id="selectedCountText">0</span> dipilih
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-3 d-none" id="btnBulkRestoreHeader" onclick="submitBulkAction('bulk_restore')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Aktifkan Kembali Terpilih
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 d-none" id="btnBulkDeleteHeader" onclick="submitBulkAction('bulk_delete')">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable" id="tableAlumni">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="checkAllAlumni">
                                </th>
                                <th width="50" class="text-center">No</th>
                                <th>Alumni / Identitas</th>
                                <th>NIS & NISN</th>
                                <th>Jurusan & Rombel Terakhir</th>
                                <th class="text-center">JK</th>
                                <th>Kontak Siswa & Ortu</th>
                                <th class="text-center">Status</th>
                                <th width="150" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alumniList)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <i class="bi bi-mortarboard text-secondary opacity-50" style="font-size: 3rem;"></i>
                                            <h6 class="fw-bold mt-2 text-dark">Belum Ada Data Siswa Berstatus Alumni</h6>
                                            <p class="small text-muted mb-3">Siswa yang diubah statusnya menjadi <code>alumni</code> pada halaman Data Siswa akan otomatis muncul di sini.</p>
                                            <a href="<?= BASE_URL ?>index.php?url=admin/siswa" class="btn btn-sm btn-primary rounded-3 px-3">
                                                <i class="bi bi-people-fill me-1"></i> Lihat Data Siswa Aktif
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($alumniList as $a): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="selected_siswa[]" value="<?= $a['id'] ?>" class="form-check-input alumni-checkbox">
                                        </td>
                                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= BASE_URL ?>assets/images/avatars/<?= htmlspecialchars($a['avatar'] ?: 'default_avatar.png') ?>" 
                                                     class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;" 
                                                     onerror="this.src='<?= BASE_URL ?>assets/images/avatars/default_avatar.png'">
                                                <div>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($a['nama_lengkap']) ?></div>
                                                    <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($a['username']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><span class="badge bg-light text-dark border">NIS: <?= htmlspecialchars($a['nis'] ?: '-') ?></span></div>
                                            <small class="text-muted">NISN: <?= htmlspecialchars($a['nisn'] ?: '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle"><?= htmlspecialchars($a['nama_jurusan']) ?></span>
                                            <div class="small text-muted mt-1"><i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($a['nama_kelas']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($a['jenis_kelamin'] ?? 'L') === 'L'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">L</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">P</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><i class="bi bi-telephone text-success me-1"></i><small><?= htmlspecialchars($a['no_telepon'] ?: '-') ?></small></div>
                                            <?php if (!empty($a['no_ortu'])): ?>
                                                <div><i class="bi bi-people text-warning me-1"></i><small class="text-muted">Ortu: <?= htmlspecialchars($a['no_ortu']) ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Alumni / Lulus
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm">
                                                <button type="button" class="btn btn-sm btn-outline-info" title="Lihat Profil Detail"
                                                        onclick='openDetailModal(<?= json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" title="Edit Kontak Alumni"
                                                        onclick='openEditModal(<?= json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Aktifkan Kembali ke Siswa Aktif"
                                                        onclick="openRestoreModal(<?= (int)$a['id'] ?>, '<?= htmlspecialchars(addslashes($a['nama_lengkap'])) ?>', <?= (int)$a['kelas_id'] ?>)">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Data Alumni"
                                                        onclick="confirmDeleteAlumni(<?= (int)$a['id'] ?>, '<?= htmlspecialchars(addslashes($a['nama_lengkap'])) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Modal Detail Profil Alumni -->
<div class="modal fade" id="modalDetailAlumni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Detail Data Alumni</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4 text-center border-end">
                        <img id="detailAvatar" src="" class="rounded-circle border shadow-sm mb-3" style="width: 110px; height: 110px; object-fit: cover;">
                        <h6 class="fw-bold mb-1" id="detailNama">-</h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-2">Alumni / Lulus</span>
                        <div class="text-muted small" id="detailUsername">-</div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="text-muted small fw-bold">NIS</label>
                                <div class="fw-semibold text-dark" id="detailNis">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">NISN</label>
                                <div class="fw-semibold text-dark" id="detailNisn">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">Jurusan</label>
                                <div class="fw-semibold text-dark" id="detailJurusan">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">Kelas Terakhir</label>
                                <div class="fw-semibold text-dark" id="detailKelas">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">Jenis Kelamin</label>
                                <div class="fw-semibold text-dark" id="detailJk">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">No. Telepon Siswa</label>
                                <div class="fw-semibold text-dark" id="detailTelepon">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">No. Telepon Orang Tua</label>
                                <div class="fw-semibold text-dark" id="detailNoOrtu">-</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold">Email</label>
                                <div class="fw-semibold text-dark" id="detailEmail">-</div>
                            </div>
                            <div class="col-12">
                                <label class="text-muted small fw-bold">Alamat Rumah</label>
                                <div class="fw-semibold text-dark p-2 bg-light rounded-3" id="detailAlamat">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Kontak Alumni -->
<div class="modal fade" id="modalEditAlumni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/alumni" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Perbarui Data Kontak Alumni</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="editNama" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">NIS</label>
                            <input type="text" name="nis" id="editNis" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">NISN</label>
                            <input type="text" name="nisn" id="editNisn" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jurusan</label>
                            <select name="jurusan_id" id="editJurusan" class="form-select rounded-3" required>
                                <?php foreach ($jurusanList as $jur): ?>
                                    <option value="<?= $jur['id'] ?>"><?= htmlspecialchars($jur['nama_jurusan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Kelas Terakhir</label>
                            <select name="kelas_id" id="editKelas" class="form-select rounded-3" required>
                                <?php foreach ($kelasList as $kls): ?>
                                    <option value="<?= $kls['id'] ?>"><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="editJk" class="form-select rounded-3">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">No. Telepon Siswa</label>
                            <input type="text" name="no_telepon" id="editTelepon" class="form-control rounded-3" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">No. Telepon Orang Tua</label>
                            <input type="text" name="no_ortu" id="editNoOrtu" class="form-control rounded-3" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Kosongkan bila tidak diubah">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Alamat Rumah</label>
                            <textarea name="alamat" id="editAlamat" rows="2" class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Restore Alumni ke Siswa Aktif -->
<div class="modal fade" id="modalRestoreAlumni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/alumni" method="POST" onsubmit="const btn = this.querySelector('button[type=submit]'); if(btn){ btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm me-1\'></span> Memproses...'; }">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="restore">
                <input type="hidden" name="id" id="restoreId">
                <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>Aktifkan Kembali Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                        <i class="bi bi-person-check-fill fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Kembalikan Siswa <span id="restoreNama" class="text-success"></span> ke Status Aktif?</h6>
                    <p class="text-muted small mb-4">Siswa ini akan kembali terdaftar pada data siswa aktif e-learning dan akun loginnya akan diaktifkan.</p>

                    <div class="text-start">
                        <label class="form-label small fw-bold text-muted">Pilih Kelas Penempatan Siswa (Kelas Aktif):</label>
                        <select name="kelas_id" id="restoreKelasId" class="form-select rounded-3" required>
                            <?php foreach ($kelasList as $kls): ?>
                                <option value="<?= $kls['id'] ?>"><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Siswa akan otomatis masuk ke data siswa aktif pada rombel yang dipilih.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Ya, Aktifkan Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Aksi Massal Alumni -->
<div class="modal fade" id="modalBulkAlumni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-gear-wide-connected me-2"></i>Aksi Massal Data Alumni</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Tindakan massal ini akan diterapkan pada alumni yang Anda centang pada tabel di halaman ini.</p>
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold text-muted">Kelas Penempatan (Bila Mengaktifkan Siswa Terpilih):</label>
                    <select id="bulkTargetKelasId" class="form-select rounded-3">
                        <?php foreach ($kelasList as $kls): ?>
                            <option value="<?= $kls['id'] ?>"><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success p-3 text-start rounded-3" onclick="submitBulkAction('bulk_restore')">
                        <div class="fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>Aktifkan Kembali Siswa Terpilih</div>
                        <small class="text-muted">Kembalikan status alumni yang dicentang menjadi Siswa Aktif.</small>
                    </button>
                    <button type="button" class="btn btn-outline-danger p-3 text-start rounded-3" onclick="submitBulkAction('bulk_delete')">
                        <div class="fw-bold"><i class="bi bi-trash me-2"></i>Hapus Permanen Alumni Terpilih</div>
                        <small class="text-muted">Hapus data alumni yang dicentang secara permanen.</small>
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Single -->
<div class="modal fade" id="modalDeleteAlumni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/alumni" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

                <div class="modal-body p-4 text-center">
                    <div class="text-danger mb-3"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                    <h6 class="fw-bold">Hapus Data Alumni?</h6>
                    <p class="text-muted small mb-3">Yakin ingin menghapus data <strong id="deleteNama"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-danger rounded-3 px-3 fw-semibold">Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAllAlumni');
    const checkboxes = document.querySelectorAll('.alumni-checkbox');
    const selectedBadge = document.getElementById('selectedCountBadge');
    const selectedText = document.getElementById('selectedCountText');
    const btnRestore = document.getElementById('btnBulkRestoreHeader');
    const btnDelete = document.getElementById('btnBulkDeleteHeader');

    function updateSelection() {
        const checked = document.querySelectorAll('.alumni-checkbox:checked');
        const count = checked.length;
        if (selectedText) selectedText.textContent = count;
        if (count > 0) {
            selectedBadge?.classList.remove('d-none');
            btnRestore?.classList.remove('d-none');
            btnDelete?.classList.remove('d-none');
        } else {
            selectedBadge?.classList.add('d-none');
            btnRestore?.classList.add('d-none');
            btnDelete?.classList.add('d-none');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            updateSelection();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });
});

function openDetailModal(data) {
    document.getElementById('detailNama').textContent = data.nama_lengkap || '-';
    document.getElementById('detailUsername').textContent = '@' + (data.username || '-');
    document.getElementById('detailNis').textContent = data.nis || '-';
    document.getElementById('detailNisn').textContent = data.nisn || '-';
    document.getElementById('detailJurusan').textContent = data.nama_jurusan || '-';
    document.getElementById('detailKelas').textContent = data.nama_kelas || '-';
    document.getElementById('detailJk').textContent = data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    document.getElementById('detailTelepon').textContent = data.no_telepon || '-';
    document.getElementById('detailNoOrtu').textContent = data.no_ortu || '-';
    document.getElementById('detailEmail').textContent = data.email || '-';
    document.getElementById('detailAlamat').textContent = data.alamat || 'Alamat belum diatur.';
    
    const avatarImg = document.getElementById('detailAvatar');
    const avatarSrc = data.avatar ? ('<?= BASE_URL ?>assets/images/avatars/' + data.avatar) : '<?= BASE_URL ?>assets/images/avatars/default_avatar.png';
    avatarImg.src = avatarSrc;

    const modal = new bootstrap.Modal(document.getElementById('modalDetailAlumni'));
    modal.show();
}

function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editNama').value = data.nama_lengkap || '';
    document.getElementById('editNis').value = data.nis || '';
    document.getElementById('editNisn').value = data.nisn || '';
    document.getElementById('editJurusan').value = data.jurusan_id || 1;
    document.getElementById('editKelas').value = data.kelas_id || 1;
    document.getElementById('editJk').value = data.jenis_kelamin || 'L';
    document.getElementById('editTelepon').value = data.no_telepon || '';
    document.getElementById('editNoOrtu').value = data.no_ortu || '';
    document.getElementById('editEmail').value = data.email || '';
    document.getElementById('editAlamat').value = data.alamat || '';

    const modal = new bootstrap.Modal(document.getElementById('modalEditAlumni'));
    modal.show();
}

function openRestoreModal(id, nama, kelasId) {
    document.getElementById('restoreId').value = id;
    document.getElementById('restoreNama').textContent = nama;
    const select = document.getElementById('restoreKelasId');
    if (select) {
        if (kelasId && select.querySelector('option[value="' + kelasId + '"]')) {
            select.value = kelasId;
        } else if (select.options.length > 0) {
            select.selectedIndex = 0;
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('modalRestoreAlumni'));
    modal.show();
}

function confirmDeleteAlumni(id, nama) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteNama').textContent = nama;
    const modal = new bootstrap.Modal(document.getElementById('modalDeleteAlumni'));
    modal.show();
}

function submitBulkAction(action) {
    const checked = document.querySelectorAll('.alumni-checkbox:checked');
    if (checked.length === 0) {
        alert('Silakan pilih (centang) minimal satu alumni terlebih dahulu.');
        return;
    }
    const label = action === 'bulk_restore' ? 'mengaktifkan kembali ' + checked.length + ' alumni terpilih' : 'menghapus ' + checked.length + ' alumni terpilih';
    if (!confirm('Apakah Anda yakin ingin ' + label + '?')) {
        return;
    }

    // Jika bulk_restore, sertakan target kelas aktif penempatan
    if (action === 'bulk_restore') {
        const bulkKls = document.getElementById('bulkTargetKelasId');
        if (bulkKls) {
            let hiddenKls = document.getElementById('targetKelasIdBulk');
            if (!hiddenKls) {
                hiddenKls = document.createElement('input');
                hiddenKls.type = 'hidden';
                hiddenKls.id = 'targetKelasIdBulk';
                hiddenKls.name = 'target_kelas_id';
                document.getElementById('formTableAlumni').appendChild(hiddenKls);
            }
            hiddenKls.value = bulkKls.value;
        }
    }

    document.getElementById('tableActionInput').value = action;
    document.getElementById('formTableAlumni').submit();
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
