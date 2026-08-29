<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Manajemen Jadwal Pelajaran Sekolah</h4>
            <p class="text-muted small mb-0">Penyusunan jadwal KBM harian per-rombel kelas, input massal 1-minggu, dan validasi bentrok realtime.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalAddJadwal">
            <i class="bi bi-plus-circle me-1"></i> Tambah 1 Sesi Jadwal
        </button>
    </div>

    <!-- Stats Summary Cards -->
    <?php
    $conflictCount = 0;
    if (!empty($jadwalList)) {
        foreach ($jadwalList as $jItem) {
            if (!empty($jItem['is_conflict'])) $conflictCount++;
        }
    }
    $totalJadwal = count($jadwalList ?? []);
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-primary">
                <div class="text-muted small fw-bold mb-1"><i class="bi bi-calendar-event me-1 text-primary"></i> Total Sesi KBM</div>
                <div class="fs-4 fw-bold text-dark"><?= $totalJadwal ?> <span class="fs-6 text-muted fw-normal">Sesi</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-info">
                <div class="text-muted small fw-bold mb-1"><i class="bi bi-building me-1 text-info"></i> Total Rombel</div>
                <div class="fs-4 fw-bold text-info"><?= count($kelasList ?? []) ?> <span class="fs-6 text-muted fw-normal">Kelas</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-success">
                <div class="text-muted small fw-bold mb-1"><i class="bi bi-person-badge me-1 text-success"></i> Guru Active</div>
                <div class="fs-4 fw-bold text-success"><?= count($guruList ?? []) ?> <span class="fs-6 text-muted fw-normal">Guru</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 <?= $conflictCount > 0 ? 'border-danger' : 'border-emerald' ?>">
                <div class="text-muted small fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1 <?= $conflictCount > 0 ? 'text-danger' : 'text-success' ?>"></i> Status Bentrok</div>
                <div class="fs-4 fw-bold <?= $conflictCount > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= $conflictCount ?> <span class="fs-6 text-muted fw-normal"><?= $conflictCount > 0 ? 'Bentrok' : 'Aman' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="jadwalTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="tabDaftar-tab" data-bs-toggle="tab" data-bs-target="#tabDaftar" type="button" role="tab">
                <i class="bi bi-table me-2"></i>Daftar & Filter Jadwal Sekolah
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="tabBatch-tab" data-bs-toggle="tab" data-bs-target="#tabBatch" type="button" role="tab">
                <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Input Massal (Batch Builder 1-Minggu)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="jadwalTabContent">

        <!-- ========================================================================= -->
        <!-- TAB 1: DAFTAR & FILTER JADWAL SEKOLAH -->
        <!-- ========================================================================= -->
        <div class="tab-pane fade show active" id="tabDaftar" role="tabpanel">
            
            <!-- Filter Rombel Kelas -->
            <div class="card-custom p-3 mb-4 shadow-sm border-start border-4 border-primary">
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-center">
                    <input type="hidden" name="url" value="admin/jadwal">
                    <div class="col-12 col-md-8 col-lg-6 d-flex align-items-center gap-2">
                        <label class="form-label mb-0 fw-bold small text-nowrap"><i class="bi bi-funnel-fill text-primary me-1"></i>Filter Kelas:</label>
                        <select name="kelas_id" class="form-select fw-semibold" onchange="this.form.submit()">
                            <option value="">-- Semua Rombel Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (isset($selectedKelasId) && $selectedKelasId == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($selectedKelasId)): ?>
                        <div class="col-auto">
                            <a href="<?= BASE_URL ?>index.php?url=admin/jadwal" class="btn btn-outline-secondary btn-sm fw-bold">Reset Filter</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($conflictCount > 0): ?>
                <div class="alert alert-danger shadow-sm border-0 rounded-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>Peringatan Bentrok Jadwal Terdeteksi!</h6>
                        <p class="small mb-0">Terdapat <strong><?= $conflictCount ?> sesi KBM bentrok</strong> (Guru mengajar 2 kelas bersamaan, Kelas 2 mapel bersamaan, atau Ruangan bersamaan). Silakan periksa badge merah <code>⚠️ BENTROK</code> pada tabel di bawah.</p>
                    </div>
                    <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill fw-bold"><?= $conflictCount ?> Sesi Bentrok</span>
                </div>
            <?php endif; ?>

            <!-- Table Jadwal -->
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle <?= !empty($jadwalList) ? 'datatable' : '' ?>">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">No</th>
                                <th>Hari</th>
                                <th>Waktu (Jam KBM)</th>
                                <th>Rombel Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Key Mapel Rombel</th>
                                <th>Ruangan</th>
                                <th style="width:140px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwalList)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada data jadwal pelajaran terdaftar.</td></tr>
                            <?php else: ?>
                                <?php foreach ($jadwalList as $i => $j): 
                                    $hasConflict = !empty($j['is_conflict']);
                                    $conflictReasons = !empty($j['conflict_reasons']) ? implode(' | ', $j['conflict_reasons']) : '';
                                ?>
                                    <tr class="<?= $hasConflict ? 'table-danger' : '' ?>">
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 fs-6">
                                                <i class="bi bi-calendar-day me-1"></i><?= htmlspecialchars($j['hari']) ?>
                                            </span>
                                            <?php if ($hasConflict): ?>
                                                <span class="badge bg-danger text-white ms-1 fw-bold" title="<?= htmlspecialchars($conflictReasons) ?>" data-bs-toggle="tooltip">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>BENTROK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            <i class="bi bi-clock me-1 text-muted"></i>
                                            <?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?> WIB
                                        </td>
                                        <td class="fw-bold text-primary">
                                            <?= htmlspecialchars($j['nama_kelas']) ?>
                                            <?php if (!empty($j['tingkat'])): ?>
                                                <span class="badge bg-light text-secondary border ms-1">Kelas <?= (int)$j['tingkat'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($j['nama_mapel']) ?></td>
                                        <td>
                                            <i class="bi bi-person-badge me-1 text-secondary"></i><?= htmlspecialchars($j['nama_guru']) ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($j['enrollment_key'])): 
                                                $tNum = (int)($j['tingkat'] ?? 0);
                                                $badgeStyle = ($tNum === 10) ? 'background:#e0e7ff; color:#3730a3;' :
                                                             (($tNum === 11) ? 'background:#f3e8ff; color:#6b21a8;' :
                                                             (($tNum === 12) ? 'background:#dcfce7; color:#15803d;' : 'background:#fee2e2; color:#991b1b;'));
                                            ?>
                                                <code class="fs-6 fw-bold px-2.5 py-1 rounded-3 border" style="<?= $badgeStyle ?> letter-spacing:0.5px;">
                                                    <i class="bi bi-key-fill me-1"></i><?= htmlspecialchars($j['enrollment_key']) ?>
                                                </code>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" 
                                                        class="btn btn-outline-warning fw-bold" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditJadwal"
                                                        data-id="<?= $j['id'] ?>"
                                                        data-kelas="<?= $j['kelas_id'] ?>"
                                                        data-mapel="<?= $j['mapel_id'] ?>"
                                                        data-guru="<?= $j['guru_id'] ?>"
                                                        data-hari="<?= htmlspecialchars($j['hari'], ENT_QUOTES) ?>"
                                                        data-mulai="<?= htmlspecialchars($j['jam_mulai'], ENT_QUOTES) ?>"
                                                        data-selesai="<?= htmlspecialchars($j['jam_selesai'], ENT_QUOTES) ?>"
                                                        data-ruangan="<?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas', ENT_QUOTES) ?>">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger fw-bold" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalDeleteJadwal"
                                                        data-id="<?= $j['id'] ?>"
                                                        data-name="<?= htmlspecialchars($j['nama_mapel'] . ' (' . $j['nama_kelas'] . ')', ENT_QUOTES) ?>">
                                                    <i class="bi bi-trash-fill me-1"></i> Hapus
                                                </button>
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

        <!-- ========================================================================= -->
        <!-- TAB 2: INPUT MASSAL MULTI-SESI KELAS (BATCH BUILDER 1-MINGGU) -->
        <!-- ========================================================================= -->
        <div class="tab-pane fade" id="tabBatch" role="tabpanel">
            <div class="card-custom p-4 shadow-sm border-start border-4 border-warning mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Penyusunan Jadwal KBM Massal per-Rombel Kelas</h5>
                        <p class="text-muted small mb-0">Isi banyak sesi jadwal KBM sekaligus dengan <strong>validasi bentrok realtime otomatis di tiap baris sebelum tombol simpan diklik!</strong></p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-warning text-dark fw-bold btn-sm" onclick="generateFullWeekTemplate()">
                            <i class="bi bi-magic me-1"></i> 🚀 Auto Generate Template 1-Minggu (Senin-Sabtu)
                        </button>
                        <button type="button" class="btn btn-success fw-bold btn-sm" onclick="addBatchRow()">
                            <i class="bi bi-plus-circle me-1"></i> + Tambah Baris Sesi KBM
                        </button>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=admin/jadwal" method="POST" id="formBatchJadwal">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="batch_create">

                    <div class="row g-3 mb-4 align-items-center">
                        <div class="col-12 col-md-6 col-lg-5">
                            <label class="form-label fw-bold small"><i class="bi bi-building text-primary me-1"></i>Pilih Rombel Kelas Target <span class="text-danger">*</span></label>
                            <select name="batch_kelas_id" id="batchKelasTarget" class="form-select fw-bold text-primary" onchange="syncBatchKelasIdAndValidate()" required>
                                <option value="">-- Pilih Rombel Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle small" id="tableBatchJadwal">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th style="width: 120px;">Hari</th>
                                    <th style="width: 125px;">Jam Mulai</th>
                                    <th style="width: 125px;">Jam Selesai</th>
                                    <th>Mata Pelajaran <span class="text-danger">*</span></th>
                                    <th>Guru Pengampu <span class="text-danger">*</span></th>
                                    <th style="width: 150px;">Ruangan</th>
                                    <th style="width: 220px;" class="text-center">Status Validasi Realtime</th>
                                    <th style="width: 50px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyBatchJadwal">
                                <!-- Dynamic Batch Rows Rendered via JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                        <div id="batchSummaryInfo" class="small fw-bold text-muted">
                            <i class="bi bi-info-circle text-primary me-1"></i>Status validasi otomatis diperbarui secara realtime pada tiap baris di atas.
                        </div>
                        <button type="submit" id="btnSubmitBatch" class="btn btn-warning text-dark px-5 py-2 fw-bold shadow">
                            <i class="bi bi-save-fill me-2"></i>Simpan Seluruh Jadwal Kelas Ini
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
</main>

<!-- Modal Add Single Jadwal -->
<div class="modal fade" id="modalAddJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah 1 Sesi Jadwal KBM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/jadwal" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div id="liveConflictStatusAdd" class="mb-3" style="display:none;"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rombel Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (isset($selectedKelasId) && $selectedKelasId == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" required>
                            <option value="">-- Pilih Mapel --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Guru Pengampu</label>
                        <select name="guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Hari</label>
                        <select name="hari" class="form-select" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control" required value="07:30">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control" required value="09:00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ruangan / Lab</label>
                        <input type="text" name="ruangan" class="form-control" placeholder="Contoh: Lab Komputer 1 / Ruang 10A" value="Ruang Kelas">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Single Jadwal -->
<div class="modal fade" id="modalEditJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Jadwal Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/jadwal" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">

                    <div id="liveConflictStatusEdit" class="mb-3" style="display:none;"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rombel Kelas</label>
                        <select name="kelas_id" id="edit_kelas_id" class="form-select" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mata Pelajaran</label>
                        <select name="mapel_id" id="edit_mapel_id" class="form-select" required>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Guru Pengampu</label>
                        <select name="guru_id" id="edit_guru_id" class="form-select" required>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Hari</label>
                        <select name="hari" id="edit_hari" class="form-select" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ruangan / Lab</label>
                        <input type="text" name="ruangan" id="edit_ruangan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">Perbarui Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Single Jadwal -->
<div class="modal fade" id="modalDeleteJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>index.php?url=admin/jadwal" method="POST">
                <div class="modal-body text-center p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <i class="bi bi-exclamation-triangle text-danger display-4 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-1">Hapus Jadwal?</h6>
                    <p class="text-muted small mb-0" id="delete_name"></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DATA & BATCH BUILDER SCRIPT WITH LIVE REALTIME CONFLICT CHECKER -->
<script>
const MAPEL_OPTIONS = `<?php foreach ($mapelList as $m): ?><option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option><?php endforeach; ?>`;
const GURU_OPTIONS = `<?php foreach ($guruList as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option><?php endforeach; ?>`;

let batchRowCounter = 0;

function addBatchRow(data = {}) {
    batchRowCounter++;
    const tbody = document.getElementById('tbodyBatchJadwal');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.id = 'batchRow_' + batchRowCounter;

    const selectedHari = data.hari || 'Senin';
    const jamMulai = data.jam_mulai || '07:30';
    const jamSelesai = data.jam_selesai || '09:00';
    const ruangan = data.ruangan || 'Ruang Kelas';
    const currentKelasId = document.getElementById('batchKelasTarget') ? document.getElementById('batchKelasTarget').value : '';

    tr.innerHTML = `
        <td class="fw-bold text-center row-index">${tbody.children.length + 1}</td>
        <td>
            <input type="hidden" name="batch[${batchRowCounter}][kelas_id]" class="batch-kelas-hidden" value="${currentKelasId}">
            <select name="batch[${batchRowCounter}][hari]" class="form-select form-select-sm batch-input" required>
                <option value="Senin" ${selectedHari === 'Senin' ? 'selected' : ''}>Senin</option>
                <option value="Selasa" ${selectedHari === 'Selasa' ? 'selected' : ''}>Selasa</option>
                <option value="Rabu" ${selectedHari === 'Rabu' ? 'selected' : ''}>Rabu</option>
                <option value="Kamis" ${selectedHari === 'Kamis' ? 'selected' : ''}>Kamis</option>
                <option value="Jumat" ${selectedHari === 'Jumat' ? 'selected' : ''}>Jumat</option>
                <option value="Sabtu" ${selectedHari === 'Sabtu' ? 'selected' : ''}>Sabtu</option>
            </select>
        </td>
        <td><input type="time" name="batch[${batchRowCounter}][jam_mulai]" class="form-control form-control-sm batch-input" value="${jamMulai}" required></td>
        <td><input type="time" name="batch[${batchRowCounter}][jam_selesai]" class="form-control form-control-sm batch-input" value="${jamSelesai}" required></td>
        <td>
            <select name="batch[${batchRowCounter}][mapel_id]" class="form-select form-select-sm batch-input" required>
                <option value="">-- Pilih Mapel --</option>
                ${MAPEL_OPTIONS}
            </select>
        </td>
        <td>
            <select name="batch[${batchRowCounter}][guru_id]" class="form-select form-select-sm batch-input" required>
                <option value="">-- Pilih Guru --</option>
                ${GURU_OPTIONS}
            </select>
        </td>
        <td><input type="text" name="batch[${batchRowCounter}][ruangan]" class="form-control form-control-sm batch-input" value="${ruangan}" placeholder="Ruangan"></td>
        <td class="text-center batch-status-cell">
            <span class="badge bg-light text-muted border">Pilih Guru & Mapel</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm px-2" onclick="removeBatchRow('${tr.id}')"><i class="bi bi-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    reindexBatchRows();
    attachLiveCheckerToRow(tr);
}

function removeBatchRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        reindexBatchRows();
        validateAllBatchRows();
    }
}

function reindexBatchRows() {
    const rows = document.querySelectorAll('#tbodyBatchJadwal tr');
    rows.forEach((r, idx) => {
        const indexTd = r.querySelector('.row-index');
        if (indexTd) indexTd.innerText = idx + 1;
    });
}

function syncBatchKelasIdAndValidate() {
    const batchTarget = document.getElementById('batchKelasTarget');
    if (!batchTarget) return;
    const kelasId = batchTarget.value;
    document.querySelectorAll('.batch-kelas-hidden').forEach(el => {
        el.value = kelasId;
    });
    validateAllBatchRows();
}

function attachLiveCheckerToRow(tr) {
    const inputs = tr.querySelectorAll('.batch-input');
    inputs.forEach(input => {
        input.addEventListener('change', () => validateBatchRow(tr));
        input.addEventListener('input', () => validateBatchRow(tr));
    });
    validateBatchRow(tr);
}

function isGenericRoomJS(ruangan) {
    if (!ruangan) return true;
    const r = ruangan.trim().toLowerCase();
    const generics = ['ruang kelas', 'ruang kelas masing-masing', 'ruang kbm', 'kelas', '-', 'rg. kelas', 'ruangan kelas', 'ruang kelas / rombel'];
    if (generics.includes(r)) return true;
    if (r.includes('ruang kelas')) return true;
    return false;
}

function validateBatchRow(tr) {
    const batchTarget = document.getElementById('batchKelasTarget');
    const kelasId = tr.querySelector('.batch-kelas-hidden') ? tr.querySelector('.batch-kelas-hidden').value : (batchTarget ? batchTarget.value : '');
    const hariSelect = tr.querySelector('select[name*="[hari]"]');
    const jamMulaiInput = tr.querySelector('input[name*="[jam_mulai]"]');
    const jamSelesaiInput = tr.querySelector('input[name*="[jam_selesai]"]');
    const mapelSelect = tr.querySelector('select[name*="[mapel_id]"]');
    const guruSelect = tr.querySelector('select[name*="[guru_id]"]');
    const ruanganInput = tr.querySelector('input[name*="[ruangan]"]');

    if (!hariSelect || !jamMulaiInput || !jamSelesaiInput || !mapelSelect || !guruSelect) return;

    const hari = hariSelect.value;
    const jamMulai = jamMulaiInput.value;
    const jamSelesai = jamSelesaiInput.value;
    const mapelId = mapelSelect.value;
    const guruId = guruSelect.value;
    const ruangan = ruanganInput ? ruanganInput.value : '';

    const statusTd = tr.querySelector('.batch-status-cell');

    if (!kelasId || !guruId || !mapelId || !hari || !jamMulai || !jamSelesai) {
        tr.classList.remove('table-danger', 'table-success');
        if (statusTd) statusTd.innerHTML = '<span class="badge bg-light text-muted border">Lengkapi Form</span>';
        updateBatchSummary();
        return;
    }

    // Intra-batch conflict check
    const allRows = Array.from(document.querySelectorAll('#tbodyBatchJadwal tr'));
    let localConflict = null;

    for (const otherTr of allRows) {
        if (otherTr === tr) continue;
        const oHari = otherTr.querySelector('select[name*="[hari]"]')?.value;
        const oJamMulai = otherTr.querySelector('input[name*="[jam_mulai]"]')?.value;
        const oJamSelesai = otherTr.querySelector('input[name*="[jam_selesai]"]')?.value;
        const oKelasId = otherTr.querySelector('.batch-kelas-hidden')?.value || batchTarget?.value;
        const oGuruId = otherTr.querySelector('select[name*="[guru_id]"]')?.value;
        const oRuangan = otherTr.querySelector('input[name*="[ruangan]"]')?.value || '';

        if (!oHari || !oJamMulai || !oJamSelesai || !oGuruId) continue;

        if (hari.toLowerCase() === oHari.toLowerCase()) {
            if (jamMulai < oJamSelesai && jamSelesai > oJamMulai) {
                if (guruId && guruId === oGuruId) {
                    localConflict = { type: 'guru', message: 'Bentrok Guru: Guru ini mengajar 2 sesi bersamaan dalam baris batch!' };
                    break;
                }
                if (kelasId && kelasId === oKelasId) {
                    localConflict = { type: 'kelas', message: 'Bentrok Kelas: Kelas ini memiliki 2 mapel bersamaan dalam baris batch!' };
                    break;
                }
                if (!isGenericRoomJS(ruangan) && !isGenericRoomJS(oRuangan) && ruangan.trim().toLowerCase() === oRuangan.trim().toLowerCase()) {
                    localConflict = { type: 'ruangan', message: 'Bentrok Ruangan: Ruangan ini dipakai 2 sesi bersamaan dalam baris batch!' };
                    break;
                }
            }
        }
    }

    if (localConflict) {
        tr.classList.add('table-danger');
        tr.classList.remove('table-success');
        if (statusTd) {
            statusTd.innerHTML = `<span class="badge bg-danger text-white fw-bold px-2 py-1" title="${localConflict.message}" data-bs-toggle="tooltip"><i class="bi bi-exclamation-triangle-fill me-1"></i>BENTROK: ${localConflict.type.toUpperCase()}</span>`;
        }
        updateBatchSummary();
        return;
    }

    if (statusTd) {
        statusTd.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> <small class="text-muted">Cek...</small>';
    }

    const params = new URLSearchParams({
        url: 'admin/checkJadwalConflictApi',
        kelas_id: kelasId,
        guru_id: guruId,
        hari: hari,
        jam_mulai: jamMulai,
        jam_selesai: jamSelesai,
        ruangan: ruangan
    });

    fetch('<?= BASE_URL ?>index.php?' + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data.conflict) {
                tr.classList.add('table-danger');
                tr.classList.remove('table-success');
                if (statusTd) {
                    statusTd.innerHTML = `<span class="badge bg-danger text-white fw-bold px-2 py-1" title="${data.message}" data-bs-toggle="tooltip"><i class="bi bi-exclamation-triangle-fill me-1"></i>BENTROK: ${data.type.toUpperCase()}</span>`;
                }
            } else {
                tr.classList.remove('table-danger');
                tr.classList.add('table-success');
                if (statusTd) {
                    statusTd.innerHTML = `<span class="badge bg-success text-white fw-bold px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>JADWAL AMAN</span>`;
                }
            }
            updateBatchSummary();
        })
        .catch(err => {
            tr.classList.remove('table-danger', 'table-success');
            if (statusTd) statusTd.innerHTML = '<span class="badge bg-light text-muted border">Aman</span>';
            updateBatchSummary();
        });
}

function validateAllBatchRows() {
    const rows = document.querySelectorAll('#tbodyBatchJadwal tr');
    rows.forEach(tr => validateBatchRow(tr));
}

function updateBatchSummary() {
    const totalRows = document.querySelectorAll('#tbodyBatchJadwal tr').length;
    const conflictRows = document.querySelectorAll('#tbodyBatchJadwal tr.table-danger').length;
    const safeRows = document.querySelectorAll('#tbodyBatchJadwal tr.table-success').length;
    const summaryDiv = document.getElementById('batchSummaryInfo');

    if (summaryDiv) {
        if (conflictRows > 0) {
            summaryDiv.className = 'small fw-bold text-danger';
            summaryDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Total: ${totalRows} Baris | <span class="text-success">${safeRows} Aman</span> | <span class="text-danger">${conflictRows} Bentrok Terdeteksi!</span>`;
        } else {
            summaryDiv.className = 'small fw-bold text-success';
            summaryDiv.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Total: ${totalRows} Baris Sesi | seluruhnya ${safeRows} Aman & Bebas Bentrok!`;
        }
    }
}

function generateFullWeekTemplate() {
    const tbody = document.getElementById('tbodyBatchJadwal');
    if (!tbody) return;
    tbody.innerHTML = '';

    const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const timeSlots = [
        { start: '07:30', end: '09:00' },
        { start: '09:00', end: '10:30' },
        { start: '10:45', end: '12:15' },
        { start: '13:00', end: '14:30' }
    ];

    days.forEach(hari => {
        timeSlots.forEach(slot => {
            addBatchRow({
                hari: hari,
                jam_mulai: slot.start,
                jam_selesai: slot.end,
                ruangan: 'Ruang Kelas'
            });
        });
    });
}

// Initialize with 3 rows on page load
document.addEventListener('DOMContentLoaded', function() {
    addBatchRow({ hari: 'Senin', jam_mulai: '07:30', jam_selesai: '09:00' });
    addBatchRow({ hari: 'Senin', jam_mulai: '09:00', jam_selesai: '10:30' });
    addBatchRow({ hari: 'Selasa', jam_mulai: '07:30', jam_selesai: '09:00' });

    // Single Modal Live Checker Scripts
    function setupLiveConflictChecker(modalId, statusBoxId, ignoreIdInputId = null) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const inputs = modal.querySelectorAll('select[name="kelas_id"], select[name="guru_id"], select[name="hari"], input[name="jam_mulai"], input[name="jam_selesai"], input[name="ruangan"]');
        const statusBox = document.getElementById(statusBoxId);
        const submitBtn = modal.querySelector('button[type="submit"]');

        function checkConflict() {
            const kelasSelect = modal.querySelector('select[name="kelas_id"]');
            const guruSelect = modal.querySelector('select[name="guru_id"]');
            const hariSelect = modal.querySelector('select[name="hari"]');
            const jamMulaiInput = modal.querySelector('input[name="jam_mulai"]');
            const jamSelesaiInput = modal.querySelector('input[name="jam_selesai"]');
            const ruanganInput = modal.querySelector('input[name="ruangan"]');

            if (!kelasSelect || !guruSelect || !hariSelect || !jamMulaiInput || !jamSelesaiInput) return;

            const kelasId = kelasSelect.value;
            const guruId = guruSelect.value;
            const hari = hariSelect.value;
            const jamMulai = jamMulaiInput.value;
            const jamSelesai = jamSelesaiInput.value;
            const ruangan = ruanganInput ? ruanganInput.value : '';
            const ignoreId = ignoreIdInputId ? (document.querySelector(ignoreIdInputId) ? document.querySelector(ignoreIdInputId).value : '') : '';

            if (!kelasId || !guruId || !hari || !jamMulai || !jamSelesai) {
                if (statusBox) statusBox.style.display = 'none';
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            if (statusBox) {
                statusBox.style.display = 'block';
                statusBox.className = 'alert alert-info py-2 px-3 small rounded-3 border-0 d-flex align-items-center gap-2 mb-3';
                statusBox.innerHTML = '<div class="spinner-border spinner-border-sm text-info" role="status"></div> <span>Memeriksa ketersediaan...</span>';
            }

            const params = new URLSearchParams({
                url: 'admin/checkJadwalConflictApi',
                kelas_id: kelasId,
                guru_id: guruId,
                hari: hari,
                jam_mulai: jamMulai,
                jam_selesai: jamSelesai,
                ruangan: ruangan,
                id: ignoreId
            });

            fetch('<?= BASE_URL ?>index.php?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    if (!statusBox) return;
                    if (data.conflict) {
                        statusBox.className = 'alert alert-danger py-2 px-3 small rounded-3 border-0 shadow-xs mb-3';
                        statusBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1 fs-6"></i> <strong>Peringatan Bentrok:</strong> ' + data.message;
                        if (submitBtn) submitBtn.disabled = true;
                    } else {
                        statusBox.className = 'alert alert-success py-2 px-3 small rounded-3 border-0 shadow-xs mb-3';
                        statusBox.innerHTML = '<i class="bi bi-check-circle-fill me-1 fs-6"></i> <strong>Jadwal Aman:</strong> Guru, Kelas, dan Ruangan tersedia pada jam tersebut.';
                        if (submitBtn) submitBtn.disabled = false;
                    }
                })
                .catch(err => {
                    if (statusBox) statusBox.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;
                });
        }

        inputs.forEach(input => {
            input.addEventListener('change', checkConflict);
            input.addEventListener('input', checkConflict);
        });

        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(checkConflict, 100);
        });
    }

    setupLiveConflictChecker('modalAddJadwal', 'liveConflictStatusAdd');
    setupLiveConflictChecker('modalEditJadwal', 'liveConflictStatusEdit', '#edit_id');

    const modalEditJadwal = document.getElementById('modalEditJadwal');
    if (modalEditJadwal) {
        modalEditJadwal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('edit_id').value = button.getAttribute('data-id') || '';
            document.getElementById('edit_kelas_id').value = button.getAttribute('data-kelas') || '';
            document.getElementById('edit_mapel_id').value = button.getAttribute('data-mapel') || '';
            document.getElementById('edit_guru_id').value = button.getAttribute('data-guru') || '';
            document.getElementById('edit_hari').value = button.getAttribute('data-hari') || 'Senin';
            document.getElementById('edit_jam_mulai').value = button.getAttribute('data-mulai') || '07:30';
            document.getElementById('edit_jam_selesai').value = button.getAttribute('data-selesai') || '09:00';
            document.getElementById('edit_ruangan').value = button.getAttribute('data-ruangan') || 'Ruang Kelas';
        });
    }

    const modalDeleteJadwal = document.getElementById('modalDeleteJadwal');
    if (modalDeleteJadwal) {
        modalDeleteJadwal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('delete_id').value = button.getAttribute('data-id') || '';
            document.getElementById('delete_name').innerText = button.getAttribute('data-name') || '';
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
