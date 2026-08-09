<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-shield-check text-danger me-2"></i>Audit Log & Security Monitor</h4>
            <p class="text-muted small mb-0">Catatan aktivitas sistem, login, dan keamanan secara real-time.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>index.php?url=admin/exportLogs" class="btn btn-outline-success">
                <i class="bi bi-download me-1"></i> Export CSV
            </a>
            <button class="btn btn-outline-danger" onclick="confirmClearLogs()">
                <i class="bi bi-trash me-1"></i> Hapus Log Lama
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="text-primary fs-3 fw-bold"><?= $stats['total_logs'] ?? 0 ?></div>
                <small class="text-muted">Total Event Log</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="text-success fs-3 fw-bold"><?= $stats['login_sukses'] ?? 0 ?></div>
                <small class="text-muted">Login Berhasil</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="text-danger fs-3 fw-bold"><?= $stats['login_gagal'] ?? 0 ?></div>
                <small class="text-muted">Login Gagal</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="text-warning fs-3 fw-bold"><?= $stats['aktivitas_hari_ini'] ?? 0 ?></div>
                <small class="text-muted">Aktivitas Hari Ini</small>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card-custom p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" id="filterText" class="form-control form-control-sm" placeholder="Cari user, aksi, IP...">
            </div>
            <div class="col-md-3">
                <select id="filterLevel" class="form-select form-select-sm">
                    <option value="">Semua Level</option>
                    <option value="INFO">INFO</option>
                    <option value="WARNING">WARNING</option>
                    <option value="CRITICAL">CRITICAL</option>
                    <option value="ERROR">ERROR</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="filterDate" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100" onclick="filterLogs()">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Log Table -->
    <div class="card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle <?= !empty($logs) ? 'datatable' : '' ?>" id="logsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Waktu</th>
                        <th>Level</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Aksi / Event</th>
                        <th>Keterangan</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-text fs-1 d-block mb-2 text-secondary"></i>
                                <small>Belum ada log yang tercatat. Log akan muncul setelah ada aktivitas sistem.</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $levelConfig = match($log['level'] ?? 'INFO') {
                                'CRITICAL' => ['danger', 'bi-exclamation-octagon-fill'],
                                'ERROR' => ['danger', 'bi-x-circle-fill'],
                                'WARNING' => ['warning', 'bi-exclamation-triangle-fill'],
                                default => ['info', 'bi-info-circle-fill']
                            };
                            ?>
                            <tr>
                                <td class="small text-muted text-nowrap">
                                    <?= date('d/m H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $levelConfig[0] ?>">
                                        <i class="bi <?= $levelConfig[1] ?> me-1"></i>
                                        <?= $log['level'] ?? 'INFO' ?>
                                    </span>
                                </td>
                                <td class="fw-bold small"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                                <td><span class="badge bg-secondary small"><?= htmlspecialchars($log['role'] ?? '-') ?></span></td>
                                <td class="small fw-semibold"><?= htmlspecialchars($log['action'] ?? '-') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars(substr($log['description'] ?? '', 0, 60)) ?>...</td>
                                <td><code class="small"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                <td class="small text-muted text-nowrap"><?= htmlspecialchars(substr($log['user_agent'] ?? '', 0, 25)) ?>...</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<script>
function filterLogs() {
    const text = document.getElementById('filterText').value.toLowerCase();
    const level = document.getElementById('filterLevel').value;
    document.querySelectorAll('#logsTable tbody tr').forEach(tr => {
        const rowText = tr.textContent.toLowerCase();
        const rowLevel = tr.querySelector('.badge')?.textContent?.trim() || '';
        const matchText = !text || rowText.includes(text);
        const matchLevel = !level || rowLevel.includes(level);
        tr.style.display = matchText && matchLevel ? '' : 'none';
    });
}

function confirmClearLogs() {
    Swal.fire({
        title: 'Hapus Log Lama?',
        text: 'Semua log lebih dari 30 hari akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(r => {
            window.location.href = '<?= BASE_URL ?>index.php?url=admin/clearLogs';
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
