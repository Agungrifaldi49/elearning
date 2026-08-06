<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-bounding-box-circles text-primary me-2"></i>Manajemen Kelas & Mapel Pengampuan Guru</h4>
            <p class="text-muted small mb-0">Kelola Rombel Kelas Ajar, Kode Akses (Key) Mapel, dan Data Siswa Terdaftar secara teratur & rapi.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGuruSetKey">
                <i class="bi bi-key-fill me-1"></i> Set Key Mapel
            </button>
            <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-cloud-upload me-1"></i> Upload Materi
            </a>
            <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Tugas Baru
            </a>
        </div>
    </div>

    <!-- Active Tab Selection Logic -->
    <?php
    $activeTab = $_GET['tab'] ?? (!empty($filterMapelId) || !empty($filterKelasId) || !empty($filterJurusanId) || !empty($filterSearch) ? 'siswa' : 'kelas');
    ?>

    <!-- Navigation Pills / Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded-4 border" id="guruVirtualTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-3 py-2 <?= $activeTab === 'kelas' ? 'active shadow-sm' : 'text-dark' ?> rounded-3" 
                    id="tab-kelas-btn" data-bs-toggle="pill" data-bs-target="#pane-kelas" type="button" role="tab" aria-controls="pane-kelas" aria-selected="<?= $activeTab === 'kelas' ? 'true' : 'false' ?>">
                <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>1. Rombel Kelas Virtual Saya
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-3 py-2 <?= $activeTab === 'key' ? 'active shadow-sm' : 'text-dark' ?> rounded-3" 
                    id="tab-key-btn" data-bs-toggle="pill" data-bs-target="#pane-key" type="button" role="tab" aria-controls="pane-key" aria-selected="<?= $activeTab === 'key' ? 'true' : 'false' ?>">
                <i class="bi bi-key-fill me-2 text-warning"></i>2. Kode Akses (Key / Passcode) Mapel
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-3 py-2 <?= $activeTab === 'siswa' ? 'active shadow-sm' : 'text-dark' ?> rounded-3" 
                    id="tab-siswa-btn" data-bs-toggle="pill" data-bs-target="#pane-siswa" type="button" role="tab" aria-controls="pane-siswa" aria-selected="<?= $activeTab === 'siswa' ? 'true' : 'false' ?>">
                <i class="bi bi-people-fill me-2 text-success"></i>3. Data Siswa Terdaftar Mapel (<?= count($siswaEnrolledList) ?>)
            </button>
        </li>
    </ul>

    <!-- Tab Content Panes -->
    <div class="tab-content" id="guruVirtualTabsContent">
        
        <!-- PANE 1: ROMBEL KELAS VIRTUAL SAYA -->
        <div class="tab-pane fade <?= $activeTab === 'kelas' ? 'show active' : '' ?>" id="pane-kelas" role="tabpanel" aria-labelledby="tab-kelas-btn">
            <div class="row g-4 mb-4">
                <?php if (empty($kelasList)): ?>
                    <div class="col-12 text-center py-5 text-muted card-custom">
                        <i class="bi bi-bounding-box fs-1 d-block mb-2 text-secondary"></i>
                        Belum ada rombel kelas terdaftar. Silakan hubungi Administrator untuk penugasan kelas.
                    </div>
                <?php else: ?>
                    <?php foreach ($kelasList as $k): 
                        $isMyWaliKelas = ($k['wali_kelas_id'] ?? 0) == ($guru['id'] ?? 0);
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card-custom p-4 h-100 position-relative border-top border-4 <?= $isMyWaliKelas ? 'border-success' : 'border-primary' ?> shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary mb-1">Tingkat <?= htmlspecialchars($k['tingkat'] ?? 'X') ?></span>
                                    <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($k['nama_kelas']) ?></h5>
                                </div>
                                <?php if ($isMyWaliKelas): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Saya Wali Kelas</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Rombel Ajar</span>
                                <?php endif; ?>
                            </div>

                            <p class="text-muted small mb-3"><?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?></p>

                            <div class="p-3 bg-light rounded-3 mb-3 small">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted"><i class="bi bi-person-badge me-1"></i>Wali Kelas:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($k['nama_walikelas'] ?? 'Belum Ditentukan') ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Kode Gabung Rombel:</span>
                                    <code class="fw-bold text-primary">MH-<?= strtoupper(substr(md5($k['id']), 0, 6)) ?></code>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Total Siswa Rombel:</span>
                                    <span class="fw-bold text-success"><?= $k['total_siswa'] ?> Siswa</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Materi Saya Unggah:</span>
                                    <span class="fw-bold text-info"><?= $k['total_materi_guru'] ?> Modul</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-1 mt-auto pt-2">
                                <a href="<?= BASE_URL ?>index.php?url=guru/absensi&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-success px-2" style="font-size:0.78rem;">
                                    <i class="bi bi-calendar-check me-1"></i> Presensi
                                </a>
                                <a href="<?= BASE_URL ?>index.php?url=guru/materi&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary px-2" style="font-size:0.78rem;">
                                    <i class="bi bi-book me-1"></i> Materi Saya
                                </a>
                                <a href="<?= BASE_URL ?>index.php?url=guru/tugas&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-warning text-dark px-2" style="font-size:0.78rem;">
                                    <i class="bi bi-card-checklist me-1"></i> Tugas
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANE 2: KODE AKSES (KEY / PASSCODE) MAPEL SAYA -->
        <div class="tab-pane fade <?= $activeTab === 'key' ? 'show active' : '' ?>" id="pane-key" role="tabpanel" aria-labelledby="tab-key-btn">
            <div class="card-custom p-4 shadow-sm mb-4 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shield-lock-fill text-warning me-2 fs-5"></i>Kode Akses (Key / Password) Pendaftaran Mapel Saya
                        </h5>
                        <small class="text-muted">Bagikan Key ini kepada siswa agar mereka terdaftar pada mata pelajaran pengampuan Anda.</small>
                    </div>
                    <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGuruSetKey">
                        <i class="bi bi-plus-circle me-1"></i> Buat / Edit Key Mapel
                    </button>
                </div>

                <?php if (empty($myKeys)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-key fs-1 d-block mb-2 text-warning"></i>
                        Belum ada Key Mapel yang dibuat. Klik tombol <strong>Buat / Edit Key Mapel</strong> untuk memulai.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($myKeys as $mk): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary-subtle text-primary fw-bold fs-6"><?= htmlspecialchars($mk['nama_mapel']) ?></span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                                <i class="bi bi-people-fill me-1"></i><?= (int)$mk['total_siswa'] ?> Siswa
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mb-3">Rombel: <?= htmlspecialchars($mk['nama_kelas'] ?? 'Semua Rombel') ?></small>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded-3 border">
                                        <code class="fs-6 fw-bold text-danger mb-0" style="letter-spacing:1px;"><?= htmlspecialchars($mk['enrollment_key']) ?></code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($mk['enrollment_key'], ENT_QUOTES) ?>'); alert('Key <?= htmlspecialchars($mk['enrollment_key'], ENT_QUOTES) ?> berhasil disalin!')">
                                            <i class="bi bi-clipboard me-1"></i> Salin
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANE 3: DATA SISWA TERDAFTAR MAPEL SAYA -->
        <div class="tab-pane fade <?= $activeTab === 'siswa' ? 'show active' : '' ?>" id="pane-siswa" role="tabpanel" aria-labelledby="tab-siswa-btn">
            <div class="card-custom p-4 shadow-sm mb-4 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-people-fill text-success me-2 fs-4"></i>Data Siswa Terdaftar di Mapel Saya
                        </h5>
                        <small class="text-muted">Daftar siswa yang telah memasukkan Key Mapel Anda secara otomatis terdata di bawah ini.</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 fw-bold">
                        Total: <?= count($siswaEnrolledList) ?> Siswa Terdaftar
                    </span>
                </div>

                <!-- Filter & Search Controls -->
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 mb-3">
                    <input type="hidden" name="url" value="guru/kelasVirtual">
                    <input type="hidden" name="tab" value="siswa">

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="mapel_id" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                            <option value="">-- Semua Mapel Saya --</option>
                            <?php 
                            $guruMapels = !empty($myMapelList) ? $myMapelList : $mapelList;
                            foreach ($guruMapels as $m): 
                            ?>
                                <option value="<?= $m['id'] ?>" <?= (isset($filterMapelId) && $filterMapelId == $m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="kelas_id" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                            <option value="">-- Semua Kelas Saya --</option>
                            <?php 
                            $guruKelasList = !empty($myKelasList) ? $myKelasList : $kelasList;
                            foreach ($guruKelasList as $k): 
                            ?>
                                <option value="<?= $k['id'] ?>" <?= (isset($filterKelasId) && $filterKelasId == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="jurusan_id" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                            <option value="">-- Semua Jurusan --</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>" <?= (isset($filterJurusanId) && $filterJurusanId == $j['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['nama_jurusan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3 d-flex gap-1">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Nama / NISN..." value="<?= htmlspecialchars($filterSearch ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if (!empty($filterMapelId) || !empty($filterKelasId) || !empty($filterJurusanId) || !empty($filterSearch)): ?>
                            <a href="<?= BASE_URL ?>index.php?url=guru/kelasVirtual&tab=siswa" class="btn btn-sm btn-outline-secondary fw-bold text-nowrap">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Table Enrolled Students -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle <?= !empty($siswaEnrolledList) ? 'datatable' : '' ?>">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">No</th>
                                <th>NISN / NIS</th>
                                <th>Nama Siswa</th>
                                <th>Rombel Kelas</th>
                                <th>Jurusan</th>
                                <th>Mata Pelajaran Terdaftar</th>
                                <th>Tanggal Join / Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($siswaEnrolledList)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Belum ada data siswa terdaftar yang sesuai dengan kriteria pencarian / filter Anda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($siswaEnrolledList as $idx => $se): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><code class="fw-bold text-dark"><?= htmlspecialchars($se['nisn'] ?? ($se['nis'] ?? '-')) ?></code></td>
                                        <td class="fw-bold text-dark">
                                            <i class="bi bi-person-circle text-primary me-2"></i><?= htmlspecialchars($se['nama_lengkap']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary fw-bold">
                                                <?= htmlspecialchars($se['nama_kelas'] ?? 'Umum') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($se['nama_jurusan'] ?? 'Umum') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                                <i class="bi bi-book-fill me-1"></i><?= htmlspecialchars($se['nama_mapel']) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            <?= date('d M Y, H:i', strtotime($se['enrolled_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
</main>

<!-- Modal Set Key Mapel Saya (Guru) -->
<div class="modal fade" id="modalGuruSetKey" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-key-fill text-warning me-2"></i>Set / Edit Key & Passcode Mapel Saya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/kelasVirtual" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="save_key">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran Pengampuan</label>
                        <select name="mapel_id" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Rombel Kelas (Opsional)</label>
                        <select name="kelas_id" class="form-select">
                            <option value="">-- Semua Kelas Pengampuan --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Akses / Key Mapel Pendaftaran (Passcode)</label>
                        <div class="input-group">
                            <input type="text" name="enrollment_key" id="guru_enrollment_key" class="form-control text-uppercase fw-bold" placeholder="Contoh: MTK-GURU1-2026" required style="letter-spacing:1px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('guru_enrollment_key').value = 'KEY-' + Math.floor(1000 + Math.random() * 9000)">
                                <i class="bi bi-shuffle me-1"></i> Acak
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Siswa akan diminta memasukkan Key ini sebelum dapat mengakses materi/tugas mapel Anda.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Simpan Key Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
