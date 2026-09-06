<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>Kelola Data Guru</h4>
                <p class="text-muted small mb-0">Daftar Tenaga Pengajar & Guru SMK Muthia Harapan Cicalengka dengan Pencarian & Edit Massal.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-warning shadow-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalBulkMatrixEdit">
                    <i class="bi bi-pencil-square me-1"></i> Mode Edit Massal (<?= count($guruList) ?> Guru)
                </button>
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

        <!-- Filter & Search Toolbar (Multi-criteria search) -->
        <div class="card card-custom p-3 p-md-4 mb-4 shadow-sm border-0 rounded-4">
            <form action="<?= BASE_URL ?>index.php" method="GET" id="autoFilterForm" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="admin/guru">

                <div class="col-md-5 col-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search text-primary me-1"></i> Cari NIP / Nama / Username / Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" id="autoSearchInput" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control bg-light border-start-0 rounded-end-3" placeholder="Ketik NIP, Nama, atau Username..." autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-gender-ambiguous text-info me-1"></i> Jenis Kelamin</label>
                    <select name="jk" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua --</option>
                        <option value="L" <?= (isset($_GET['jk']) && $_GET['jk'] === 'L') ? 'selected' : '' ?>>Laki-Laki (L)</option>
                        <option value="P" <?= (isset($_GET['jk']) && $_GET['jk'] === 'P') ? 'selected' : '' ?>>Perempuan (P)</option>
                    </select>
                </div>

                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Status Kepegawaian</label>
                    <select name="status" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="aktif" <?= (isset($_GET['status']) && $_GET['status'] === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= (isset($_GET['status']) && $_GET['status'] === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="col-md-1 col-12">
                    <?php if (!empty($_GET['q']) || !empty($_GET['jk']) || !empty($_GET['status'])): ?>
                        <a href="<?= BASE_URL ?>index.php?url=admin/guru" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filter">
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

        <?php if (!empty($_GET['q']) || !empty($_GET['jk']) || !empty($_GET['status'])): ?>
            <div class="alert alert-primary border-0 rounded-4 shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-funnel-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Hasil Penyaringan Data Guru: <strong><?= count($guruList) ?> Guru Ditemukan</strong></h6>
                        <small class="text-muted">
                            <?php if (!empty($_GET['q'])): ?>
                                Kata Kunci: <strong>"<?= htmlspecialchars($_GET['q']) ?>"</strong> |
                            <?php endif; ?>
                            <?php if (!empty($_GET['jk'])): ?>
                                Gender: <strong><?= $_GET['jk'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></strong> |
                            <?php endif; ?>
                            <?php if (!empty($_GET['status'])): ?>
                                Status: <strong><?= ucfirst(htmlspecialchars($_GET['status'])) ?></strong> |
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBulkMatrixEdit">
                        <i class="bi bi-pencil-square me-1"></i> Edit Massal Hasil Filter (<?= count($guruList) ?>)
                    </button>
                    <a href="<?= BASE_URL ?>index.php?url=admin/guru" class="btn btn-sm btn-outline-dark fw-semibold rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Tampilkan Semua
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Table Data Guru with Checkboxes -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable" id="tableGuru">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="selectAllGuru" title="Pilih Semua Guru">
                            </th>
                            <th style="width: 50px;">No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>JK</th>
                            <th>No Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guruList as $i => $g): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input guru-checkbox" value="<?= $g['id'] ?>">
                                </td>
                                <td><?= $i + 1 ?></td>
                                <td><code><?= htmlspecialchars($g['nip']) ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($g['nama_lengkap']) ?></td>
                                <td><span class="badge bg-secondary"><?= $g['jenis_kelamin'] ?></span></td>
                                <td><?= htmlspecialchars($g['no_telepon']) ?></td>
                                <td><?= htmlspecialchars($g['email']) ?></td>
                                <td>
                                    <?php if (strtolower($g['status'] ?? 'aktif') === 'aktif'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
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
                                            <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
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

<!-- Floating Bulk Action Toolbar (Appears when checkboxes are selected) -->
<div id="floatingBulkBar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg p-3 bg-white border border-primary border-2 rounded-4 d-none" style="z-index: 1055; width: 92%; max-width: 860px;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill" id="selectedCountBadge">0 Guru Dipilih</span>
            <small class="text-muted d-none d-md-inline">Pilih opsi aksi masal untuk guru terpilih:</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success btn-sm fw-bold rounded-3" onclick="openBulkStatusModal('aktif')">
                <i class="bi bi-check-circle-fill me-1"></i> Set Status Aktif
            </button>
            <button type="button" class="btn btn-secondary btn-sm fw-bold rounded-3" onclick="openBulkStatusModal('nonaktif')">
                <i class="bi bi-x-circle-fill me-1"></i> Set Nonaktif
            </button>
            <button type="button" class="btn btn-danger btn-sm fw-bold rounded-3" onclick="confirmBulkDelete()">
                <i class="bi bi-trash me-1"></i> Hapus Terpilih
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" onclick="deselectAllGuru()">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheck = document.getElementById('selectAllGuru');
    const guruChecks = document.querySelectorAll('.guru-checkbox');
    const floatingBar = document.getElementById('floatingBulkBar');
    const countBadge = document.getElementById('selectedCountBadge');

    function updateBulkState() {
        const checked = document.querySelectorAll('.guru-checkbox:checked');
        const count = checked.length;
        
        if (count > 0) {
            countBadge.textContent = count + ' Guru Dipilih';
            floatingBar.classList.remove('d-none');
        } else {
            floatingBar.classList.add('d-none');
        }

        if (selectAllCheck) {
            selectAllCheck.checked = (guruChecks.length > 0 && count === guruChecks.length);
        }
    }

    if (selectAllCheck) {
        selectAllCheck.addEventListener('change', function() {
            guruChecks.forEach(cb => cb.checked = this.checked);
            updateBulkState();
        });
    }

    guruChecks.forEach(cb => {
        cb.addEventListener('change', updateBulkState);
    });

    window.deselectAllGuru = function() {
        if (selectAllCheck) selectAllCheck.checked = false;
        guruChecks.forEach(cb => cb.checked = false);
        updateBulkState();
    };

    window.getSelectedGuruIds = function() {
        const checked = document.querySelectorAll('.guru-checkbox:checked');
        return Array.from(checked).map(cb => cb.value);
    };

    window.openBulkStatusModal = function(statusVal) {
        const ids = getSelectedGuruIds();
        if (ids.length === 0) return alert('Pilih minimal satu guru terlebih dahulu.');
        
        const form = document.getElementById('formBulkStatusAction');
        const container = document.getElementById('bulkStatusIdsContainer');
        document.getElementById('targetStatusInput').value = statusVal;
        container.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'selected_guru[]';
            inp.value = id;
            container.appendChild(inp);
        });

        if (confirm('Ubah status ' + ids.length + ' guru yang dipilih menjadi ' + statusVal.toUpperCase() + '?')) {
            form.submit();
        }
    };

    window.confirmBulkDelete = function() {
        const ids = getSelectedGuruIds();
        if (ids.length === 0) return alert('Pilih minimal satu guru terlebih dahulu.');
        if (confirm('Apakah Anda yakin ingin menghapus ' + ids.length + ' data guru yang dipilih sekaligus? Data yang dihapus tidak dapat dikembalikan!')) {
            const form = document.getElementById('formBulkDeleteAction');
            const container = document.getElementById('bulkDeleteIdsContainer');
            container.innerHTML = '';
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'selected_guru[]';
                inp.value = id;
                container.appendChild(inp);
            });
            form.submit();
        }
    };
});
</script>

<!-- Form Hidden Bulk Delete -->
<form id="formBulkDeleteAction" action="<?= BASE_URL ?>index.php?url=admin/guru" method="POST" class="d-none">
    <?= Security::csrfField() ?>
    <input type="hidden" name="action" value="bulk_delete">
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
    <div id="bulkDeleteIdsContainer"></div>
</form>

<!-- Form Hidden Bulk Status -->
<form id="formBulkStatusAction" action="<?= BASE_URL ?>index.php?url=admin/guru" method="POST" class="d-none">
    <?= Security::csrfField() ?>
    <input type="hidden" name="action" value="bulk_update_status">
    <input type="hidden" name="target_status" id="targetStatusInput" value="">
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
    <div id="bulkStatusIdsContainer"></div>
</form>

<!-- Modal Edit Massal Matrix Guru (Mode Tabel / Spreadsheet Edit Sekaligus) -->
<div class="modal fade" id="modalBulkMatrixEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Mode Edit Massal Data Guru (Tabel Spreadsheet)</h5>
                    <p class="text-muted small mb-0">Ubah data NIP, Nama Lengkap, Email, Telepon, JK, & Status secara sekaligus dalam 1 kali simpan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/guru" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="bulk_edit_matrix">
                    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

                    <div class="table-responsive" style="max-height: 480px;">
                        <table class="table table-bordered align-middle small table-striped">
                            <thead class="table-primary sticky-top">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th style="width: 160px;">NIP</th>
                                    <th>Nama Lengkap Guru</th>
                                    <th style="width: 200px;">Email</th>
                                    <th style="width: 150px;">No Telepon / WA</th>
                                    <th style="width: 90px;">JK</th>
                                    <th style="width: 110px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guruList as $idx => $gw): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $idx + 1 ?></td>
                                        <td>
                                            <input type="text" name="matrix_guru[<?= $gw['id'] ?>][nip]" value="<?= htmlspecialchars($gw['nip']) ?>" class="form-control form-control-sm font-monospace" required>
                                        </td>
                                        <td>
                                            <input type="text" name="matrix_guru[<?= $gw['id'] ?>][nama_lengkap]" value="<?= htmlspecialchars($gw['nama_lengkap']) ?>" class="form-control form-control-sm fw-semibold" required>
                                        </td>
                                        <td>
                                            <input type="email" name="matrix_guru[<?= $gw['id'] ?>][email]" value="<?= htmlspecialchars($gw['email']) ?>" class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <input type="text" name="matrix_guru[<?= $gw['id'] ?>][no_telepon]" value="<?= htmlspecialchars($gw['no_telepon']) ?>" class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <select name="matrix_guru[<?= $gw['id'] ?>][jenis_kelamin]" class="form-select form-select-sm">
                                                <option value="L" <?= $gw['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>L</option>
                                                <option value="P" <?= $gw['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>P</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="matrix_guru[<?= $gw['id'] ?>][status]" class="form-select form-select-sm">
                                                <option value="aktif" <?= strtolower($gw['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                                <option value="nonaktif" <?= strtolower($gw['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Total <?= count($guruList) ?> baris data guru siap diperbarui.</span>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold"><i class="bi bi-check-all me-1"></i> Simpan Semua Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

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

<!-- Modals Detail & Edit Guru Single -->
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
                        <tr><td class="text-muted">Status</td><td class="fw-semibold">: <?= ucfirst(htmlspecialchars($g['status'] ?? 'aktif')) ?></td></tr>
                    </table>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Guru Single -->
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
                        <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

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
