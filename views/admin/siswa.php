<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Kelola Data Siswa</h4>
                <p class="text-muted small mb-0">Daftar Peserta Didik SMK Muthia Harapan Cicalengka dengan Pencarian & Edit Massal.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-warning shadow-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalBulkMatrixEdit">
                    <i class="bi bi-pencil-square me-1"></i> Mode Edit Massal (<?= count($siswaList) ?> Siswa)
                </button>
                <a href="<?= BASE_URL ?>index.php?url=admin/templateSiswa" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Template Excel
                </a>
                <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
                    <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                </button>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddSiswa">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Siswa Baru
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar (Multi-criteria search) -->
        <div class="card card-custom p-3 p-md-4 mb-4 shadow-sm border-0 rounded-4">
            <form action="<?= BASE_URL ?>index.php" method="GET" id="autoFilterForm" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="admin/siswa">

                <div class="col-md-4 col-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search text-primary me-1"></i> Cari NISN / NIS / Nama</label>
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

                <div class="col-md-2 col-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-gender-ambiguous text-secondary me-1"></i> Jenis Kelamin</label>
                    <div class="d-flex gap-2">
                        <select name="jk" class="form-select rounded-3" onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            <option value="L" <?= (isset($_GET['jk']) && $_GET['jk'] === 'L') ? 'selected' : '' ?>>Laki-Laki (L)</option>
                            <option value="P" <?= (isset($_GET['jk']) && $_GET['jk'] === 'P') ? 'selected' : '' ?>>Perempuan (P)</option>
                        </select>
                        <?php if (!empty($_GET['q']) || !empty($_GET['kelas_id']) || !empty($_GET['jurusan_id']) || !empty($_GET['jk'])): ?>
                            <a href="<?= BASE_URL ?>index.php?url=admin/siswa" class="btn btn-outline-secondary rounded-3 px-3" title="Reset Filter">
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
            const form = document.getElementById('autoFilterForm');
            if (!searchInput || !form) return;

            let debounceTimer;
            searchInput.addEventListener('input', function() {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                    window.jQuery('.datatable').DataTable().search(this.value).draw();
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    form.submit();
                }, 450);
            });

            if (searchInput.value) {
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            }
        });
        </script>

        <?php if (!empty($_GET['q']) || !empty($_GET['kelas_id']) || !empty($_GET['jurusan_id']) || !empty($_GET['jk']) || !empty($selectedKelas)): ?>
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
                            <?php if (!empty($_GET['jk'])): ?>
                                Gender: <strong><?= $_GET['jk'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></strong> |
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBulkMatrixEdit">
                        <i class="bi bi-pencil-square me-1"></i> Edit Massal Hasil Filter (<?= count($siswaList) ?>)
                    </button>
                    <a href="<?= BASE_URL ?>index.php?url=admin/siswa" class="btn btn-sm btn-outline-dark fw-semibold rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Tampilkan Semua
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Table Data Siswa with Multi-Select Checkboxes -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable" id="tableSiswa">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="selectAllSiswa" title="Pilih Semua Siswa">
                            </th>
                            <th style="width: 50px;">No</th>
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
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input siswa-checkbox" value="<?= $s['id'] ?>">
                                </td>
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
                                            <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
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

<!-- Floating Bulk Action Toolbar (Appears when checkboxes are selected) -->
<div id="floatingBulkBar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg p-3 bg-white border border-primary border-2 rounded-4 d-none" style="z-index: 1055; width: 92%; max-width: 860px;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill" id="selectedCountBadge">0 Siswa Dipilih</span>
            <small class="text-muted d-none d-md-inline">Pilih opsi aksi masal untuk siswa terpilih:</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-warning btn-sm text-dark fw-bold rounded-3" onclick="openBulkKelasModal()">
                <i class="bi bi-door-open-fill me-1"></i> Pindah Kelas Masal
            </button>
            <button type="button" class="btn btn-info btn-sm text-white fw-bold rounded-3" onclick="openBulkJurusanModal()">
                <i class="bi bi-journal-bookmark-fill me-1"></i> Ubah Jurusan
            </button>
            <button type="button" class="btn btn-danger btn-sm fw-bold rounded-3" onclick="confirmBulkDelete()">
                <i class="bi bi-trash me-1"></i> Hapus Terpilih
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" onclick="deselectAllSiswa()">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheck = document.getElementById('selectAllSiswa');
    const siswaChecks = document.querySelectorAll('.siswa-checkbox');
    const floatingBar = document.getElementById('floatingBulkBar');
    const countBadge = document.getElementById('selectedCountBadge');

    function updateBulkState() {
        const checked = document.querySelectorAll('.siswa-checkbox:checked');
        const count = checked.length;
        
        if (count > 0) {
            countBadge.textContent = count + ' Siswa Dipilih';
            floatingBar.classList.remove('d-none');
        } else {
            floatingBar.classList.add('d-none');
        }

        if (selectAllCheck) {
            selectAllCheck.checked = (siswaChecks.length > 0 && count === siswaChecks.length);
        }
    }

    if (selectAllCheck) {
        selectAllCheck.addEventListener('change', function() {
            siswaChecks.forEach(cb => cb.checked = this.checked);
            updateBulkState();
        });
    }

    siswaChecks.forEach(cb => {
        cb.addEventListener('change', updateBulkState);
    });

    window.deselectAllSiswa = function() {
        if (selectAllCheck) selectAllCheck.checked = false;
        siswaChecks.forEach(cb => cb.checked = false);
        updateBulkState();
    };

    window.getSelectedSiswaIds = function() {
        const checked = document.querySelectorAll('.siswa-checkbox:checked');
        return Array.from(checked).map(cb => cb.value);
    };

    window.openBulkKelasModal = function() {
        const ids = getSelectedSiswaIds();
        if (ids.length === 0) return alert('Pilih minimal satu siswa terlebih dahulu.');
        
        const container = document.getElementById('bulkKelasIdsContainer');
        container.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'selected_siswa[]';
            inp.value = id;
            container.appendChild(inp);
        });
        document.getElementById('bulkKelasCountText').textContent = ids.length;

        const modal = new bootstrap.Modal(document.getElementById('modalBulkKelas'));
        modal.show();
    };

    window.openBulkJurusanModal = function() {
        const ids = getSelectedSiswaIds();
        if (ids.length === 0) return alert('Pilih minimal satu siswa terlebih dahulu.');
        
        const container = document.getElementById('bulkJurusanIdsContainer');
        container.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'selected_siswa[]';
            inp.value = id;
            container.appendChild(inp);
        });
        document.getElementById('bulkJurusanCountText').textContent = ids.length;

        const modal = new bootstrap.Modal(document.getElementById('modalBulkJurusan'));
        modal.show();
    };

    window.confirmBulkDelete = function() {
        const ids = getSelectedSiswaIds();
        if (ids.length === 0) return alert('Pilih minimal satu siswa terlebih dahulu.');
        if (confirm('Apakah Anda yakin ingin menghapus ' + ids.length + ' data siswa yang dipilih sekaligus? Data yang dihapus tidak dapat dikembalikan!')) {
            const form = document.getElementById('formBulkDeleteAction');
            const container = document.getElementById('bulkDeleteIdsContainer');
            container.innerHTML = '';
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'selected_siswa[]';
                inp.value = id;
                container.appendChild(inp);
            });
            form.submit();
        }
    };
});
</script>

<!-- Form Hidden Bulk Delete -->
<form id="formBulkDeleteAction" action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST" class="d-none">
    <?= Security::csrfField() ?>
    <input type="hidden" name="action" value="bulk_delete">
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
    <div id="bulkDeleteIdsContainer"></div>
</form>

<!-- Modal Bulk Pindah Kelas -->
<div class="modal fade" id="modalBulkKelas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-door-open-fill text-warning me-2"></i>Pindah Kelas Masal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="bulk_update_kelas">
                    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
                    <div id="bulkKelasIdsContainer"></div>

                    <div class="alert alert-info border-0 rounded-3 small">
                        Anda akan memindahkan <strong id="bulkKelasCountText">0</strong> siswa terpilih ke kelas baru secara sekaligus.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Kelas Tujuan Baru</label>
                        <select name="target_kelas_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">Pindahkan Masal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Ubah Jurusan -->
<div class="modal fade" id="modalBulkJurusan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-journal-bookmark-fill text-info me-2"></i>Ubah Jurusan Masal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="bulk_update_jurusan">
                    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
                    <div id="bulkJurusanIdsContainer"></div>

                    <div class="alert alert-info border-0 rounded-3 small">
                        Anda akan mengubah jurusan <strong id="bulkJurusanCountText">0</strong> siswa terpilih.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Jurusan Tujuan</label>
                        <select name="target_jurusan_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Jurusan Tujuan --</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white px-4 fw-bold">Ubah Jurusan Masal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Massal Matrix (Mode Tabel / Spreadsheet Edit Sekaligus) -->
<div class="modal fade" id="modalBulkMatrixEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Mode Edit Massal Data Siswa (Tabel Spreadsheet)</h5>
                    <p class="text-muted small mb-0">Ubah data NIS, NISN, Nama, Kelas, Jurusan, & JK secara sekaligus dalam 1 kali simpan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/siswa" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="bulk_edit_matrix">
                    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

                    <div class="table-responsive" style="max-height: 480px;">
                        <table class="table table-bordered align-middle small table-striped">
                            <thead class="table-primary sticky-top">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th style="width: 140px;">NIS</th>
                                    <th style="width: 140px;">NISN</th>
                                    <th>Nama Lengkap Siswa</th>
                                    <th style="width: 160px;">Kelas</th>
                                    <th style="width: 160px;">Jurusan</th>
                                    <th style="width: 90px;">JK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($siswaList as $idx => $sw): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $idx + 1 ?></td>
                                        <td>
                                            <input type="text" name="matrix_siswa[<?= $sw['id'] ?>][nis]" value="<?= htmlspecialchars($sw['nis']) ?>" class="form-control form-control-sm font-monospace" required>
                                        </td>
                                        <td>
                                            <input type="text" name="matrix_siswa[<?= $sw['id'] ?>][nisn]" value="<?= htmlspecialchars($sw['nisn']) ?>" class="form-control form-control-sm font-monospace" required>
                                        </td>
                                        <td>
                                            <input type="text" name="matrix_siswa[<?= $sw['id'] ?>][nama_lengkap]" value="<?= htmlspecialchars($sw['nama_lengkap']) ?>" class="form-control form-control-sm fw-semibold" required>
                                        </td>
                                        <td>
                                            <select name="matrix_siswa[<?= $sw['id'] ?>][kelas_id]" class="form-select form-select-sm" required>
                                                <?php foreach ($kelasList as $kls): ?>
                                                    <option value="<?= $kls['id'] ?>" <?= $kls['id'] == $sw['kelas_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kls['nama_kelas']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="matrix_siswa[<?= $sw['id'] ?>][jurusan_id]" class="form-select form-select-sm" required>
                                                <?php foreach ($jurusanList as $jur): ?>
                                                    <option value="<?= $jur['id'] ?>" <?= $jur['id'] == $sw['jurusan_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($jur['nama_jurusan']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="matrix_siswa[<?= $sw['id'] ?>][jenis_kelamin]" class="form-select form-select-sm">
                                                <option value="L" <?= $sw['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>L</option>
                                                <option value="P" <?= $sw['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>P</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Total <?= count($siswaList) ?> baris data siap diperbarui.</span>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold"><i class="bi bi-check-all me-1"></i> Simpan Semua Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

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

<!-- Modals Detail & Edit Siswa Single -->
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

    <!-- Modal Edit Siswa Single -->
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
                        <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

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
