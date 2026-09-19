<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- Header Breadcrumb & Page Actions -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Penilaian & Kurikulum</li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Penyusunan CP & TP</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <span class="badge rounded-circle p-2 bg-primary-subtle text-primary">
                    <i class="bi bi-card-checklist fs-4"></i>
                </span>
                Penyusunan Capaian & Tujuan Pembelajaran (CP & TP)
            </h4>
            <p class="text-muted small mb-0">Kelola dan rumuskan Capaian Pembelajaran (CP) serta Tujuan Pembelajaran (TP) untuk mata pelajaran yang Anda ampu secara terstruktur.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary shadow-sm fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddTP">
                <i class="bi bi-bullseye fs-5"></i>
                <span>+ Tambah TP Baru</span>
            </button>
            <button type="button" class="btn btn-primary shadow-sm fw-semibold px-3.5 py-2 rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span>+ Tambah CP Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Notification Alerts -->
    <?php if (class_exists('FlashHelper')): ?>
        <?php if (FlashHelper::hasSuccess()): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4 border-0 border-start border-success border-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div><?= FlashHelper::getSuccess() ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (FlashHelper::hasError()): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-4 border-0 border-start border-danger border-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    <div><?= FlashHelper::getError() ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Summary Metrics & Teacher Banner -->
    <div class="row g-3 mb-4">
        <!-- Teacher Profile & Mapel Card -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5" style="background: linear-gradient(135deg, #f0f7ff 0%, #e0f2fe 100%); border-left: 5px solid #0284c7 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white p-3 rounded-4 shadow-sm flex-shrink-0">
                        <i class="bi bi-person-workspace fs-3"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 small fw-normal">Guru Pengampu</span>
                            <span class="badge bg-white text-primary border rounded-pill px-2 py-0.5 small"><?= htmlspecialchars($kurikulumList[0]['kode'] ?? 'Kurikulum') ?></span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate">
                            <?= htmlspecialchars($guru['nama_lengkap'] ?? 'Bpk/Ibu Guru') ?>
                        </h6>
                        <div class="text-secondary small d-flex flex-wrap align-items-center gap-1.5 mt-1">
                            <span class="text-muted"><i class="bi bi-book me-1"></i>Mapel Anda:</span>
                            <?php if (!empty($teacherMapelList)): ?>
                                <?php foreach ($teacherMapelList as $tmp): ?>
                                    <span class="badge bg-white text-primary border shadow-xs px-2 py-1 rounded-2">
                                        <?= htmlspecialchars($tmp['nama_mapel']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge bg-white text-muted border px-2 py-1">Semua Mata Pelajaran</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric CP Card -->
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size:0.75rem; letter-spacing: 0.5px;">Capaian (CP)</span>
                        <h3 class="fw-bold text-primary mb-0"><?= count($cpList) ?></h3>
                        <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-check-all text-primary me-1"></i>Tersedia dalam kurikulum</span>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric TP Card -->
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size:0.75rem; letter-spacing: 0.5px;">Tujuan (TP)</span>
                        <h3 class="fw-bold text-success mb-0"><?= count($tpList) ?></h3>
                        <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-bullseye text-success me-1"></i>Siap untuk asesmen/rapor</span>
                    </div>
                    <div class="rounded-circle p-3 bg-success-subtle text-success">
                        <i class="bi bi-bullseye fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2.5 align-items-end">
            <input type="hidden" name="url" value="guru/cptp">
            
            <div class="col-12 col-md-4">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-book text-primary"></i>
                    <span>Mata Pelajaran:</span>
                </label>
                <select name="filter_mapel_id" class="form-select rounded-3 py-2" onchange="this.form.submit()">
                    <option value="">-- Semua Mata Pelajaran Saya --</option>
                    <?php foreach ($teacherMapelList as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= ($filterMapelId == $mp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-mortarboard text-primary"></i>
                    <span>Kurikulum:</span>
                </label>
                <select name="filter_kurikulum_id" class="form-select rounded-3 py-2" onchange="this.form.submit()">
                    <?php foreach ($kurikulumList as $kur): ?>
                        <option value="<?= $kur['id'] ?>" <?= ($filterKurId == $kur['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-layers text-primary"></i>
                    <span>Fase / Jenjang:</span>
                </label>
                <select name="filter_fase_id" class="form-select rounded-3 py-2" onchange="this.form.submit()">
                    <option value="">-- Semua Fase --</option>
                    <?php foreach ($allFaseList as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($filterFaseId == $f['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1.5">
                <button type="submit" class="btn btn-primary flex-fill fw-semibold rounded-3 py-2 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
                <?php if ($filterMapelId || $filterFaseId): ?>
                    <a href="<?= BASE_URL ?>index.php?url=guru/cptp" class="btn btn-outline-secondary rounded-3 py-2 px-3" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- CP & TP Table Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-list-stars text-primary"></i>
                    Daftar Rumusan Capaian (CP) & Tujuan Pembelajaran (TP)
                </h5>
                <p class="text-muted small mb-0 mt-0.5">Disusun per elemen kompetensi untuk dasar penilaian formatif, sumatif, dan deskripsi rapor.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border px-2.5 py-1.5 rounded-pill small">
                    Total CP: <strong class="text-primary"><?= count($cpList) ?></strong>
                </span>
                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-semibold px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                    <i class="bi bi-plus-circle me-1"></i> Tambah CP
                </button>
            </div>
        </div>

        <div class="table-responsive p-3">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:40px;">No</th>
                        <th style="width:130px;">Kurikulum & Fase</th>
                        <th style="width:180px;">Mata Pelajaran</th>
                        <th style="width:170px;">Kode & Elemen CP</th>
                        <th>Deskripsi Capaian Pembelajaran</th>
                        <th style="min-width:360px;">Tujuan Pembelajaran (TP) Terkait</th>
                        <th class="text-center" style="width:100px;">Aksi CP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cpList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-semibold">Belum ada data Capaian Pembelajaran (CP) untuk filter ini.</span><br>
                                <small class="text-muted">Klik tombol <strong>Tambah CP Baru</strong> di atas untuk mulai merumuskan CP.</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cpList as $i => $cp): 
                            $childTps = array_filter($tpList, function($t) use ($cp) { return $t['cp_id'] == $cp['id']; });
                            $isMyCp = ($cp['guru_id'] == $guruId);
                        ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-2 mb-1 d-inline-block font-monospace">
                                        <?= htmlspecialchars($cp['kode_kurikulum']) ?>
                                    </span>
                                    <?php if (!empty($cp['nama_fase'])): ?>
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5 rounded-2 d-block small">
                                            <?= htmlspecialchars($cp['nama_fase']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($cp['nama_mapel']) ?></div>
                                    <?php if ($isMyCp): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mt-1.5 d-inline-flex align-items-center gap-1 rounded-pill px-2 py-0.5" style="font-size:0.75rem;">
                                            <i class="bi bi-person-check-fill"></i> Disusun oleh Anda
                                        </span>
                                    <?php elseif (!empty($cp['nama_guru'])): ?>
                                        <span class="badge bg-info-subtle text-dark border border-info-subtle mt-1.5 d-inline-flex align-items-center gap-1 rounded-pill px-2 py-0.5" style="font-size:0.72rem;">
                                            <i class="bi bi-person-fill text-primary"></i> <?= htmlspecialchars($cp['nama_guru']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border mt-1.5 d-inline-flex align-items-center gap-1 rounded-pill px-2 py-0.5" style="font-size:0.72rem;">
                                            <i class="bi bi-shield-check text-muted"></i> Kurikulum Sekolah
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary text-white font-monospace px-2.5 py-1 rounded-2 mb-1 d-inline-block shadow-xs">
                                        <?= htmlspecialchars($cp['kode_cp']) ?>
                                    </span>
                                    <?php if (!empty($cp['elemen'])): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-2 small">
                                                <i class="bi bi-tag-fill me-1 text-primary" style="font-size:0.7rem;"></i><?= htmlspecialchars($cp['elemen']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="p-2.5 rounded-3 bg-light border-0 text-dark small" style="line-height: 1.6; max-height: 140px; overflow-y: auto; background-color: #f8fafc;">
                                        <?= nl2br(htmlspecialchars($cp['deskripsi'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <!-- Child TP Cards List -->
                                    <?php if (empty($childTps)): ?>
                                        <div class="p-2.5 rounded-3 bg-light border border-dashed text-center text-muted small mb-2">
                                            <i class="bi bi-info-circle me-1"></i> Belum ada TP turunan untuk CP ini.
                                        </div>
                                    <?php else: ?>
                                        <div class="tp-container d-flex flex-column gap-2 mb-2" style="max-height: 220px; overflow-y: auto;">
                                            <?php foreach ($childTps as $tp): ?>
                                                <div class="p-2.5 rounded-3 bg-white border shadow-xs d-flex justify-content-between align-items-start gap-2" style="border-left: 3px solid #0d6efd !important;">
                                                    <div class="small flex-grow-1 min-w-0">
                                                        <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1">
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded">
                                                                <?= htmlspecialchars($tp['kode_tp']) ?>
                                                            </span>
                                                            <?php if (!empty($tp['materi_pokok'])): ?>
                                                                <span class="badge bg-light text-dark border px-2 py-0.5 rounded" style="font-size:0.72rem;">
                                                                    <i class="bi bi-bookmark text-primary me-0.5"></i><?= htmlspecialchars($tp['materi_pokok']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="text-secondary" style="line-height: 1.45;">
                                                            <?= htmlspecialchars($tp['deskripsi']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-1 flex-shrink-0 align-items-center">
                                                        <button type="button" class="btn btn-sm btn-light border text-warning rounded-2 p-1 px-1.5 btn-edit-tp" 
                                                            title="Edit TP"
                                                            data-bs-toggle="modal" data-bs-target="#modalEditTP"
                                                            data-id="<?= $tp['id'] ?>"
                                                            data-cp-id="<?= $tp['cp_id'] ?>"
                                                            data-kode="<?= htmlspecialchars($tp['kode_tp']) ?>"
                                                            data-materi="<?= htmlspecialchars($tp['materi_pokok'] ?? '') ?>"
                                                            data-deskripsi="<?= htmlspecialchars($tp['deskripsi']) ?>">
                                                            <i class="bi bi-pencil-fill" style="font-size: 0.8rem;"></i>
                                                        </button>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Tujuan Pembelajaran (TP) ini?');">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="delete_tp">
                                                            <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                                                            <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                                                            <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                                                            <input type="hidden" name="id" value="<?= $tp['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-light border text-danger rounded-2 p-1 px-1.5" title="Hapus TP">
                                                                <i class="bi bi-trash-fill" style="font-size: 0.8rem;"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Button to Add TP for this specific CP -->
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold btn-add-tp-for-cp px-3 py-1 d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal" data-bs-target="#modalAddTP"
                                        data-cp-id="<?= $cp['id'] ?>"
                                        data-cp-title="[<?= htmlspecialchars($cp['kode_cp']) ?>] <?= htmlspecialchars($cp['nama_mapel']) ?>">
                                        <i class="bi bi-plus-circle"></i> Tambah TP Turunan
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-edit-cp rounded-start-3" 
                                            title="Edit CP"
                                            data-bs-toggle="modal" data-bs-target="#modalEditCP"
                                            data-id="<?= $cp['id'] ?>"
                                            data-kurikulum-id="<?= $cp['kurikulum_id'] ?>"
                                            data-mapel-id="<?= $cp['mapel_id'] ?>"
                                            data-fase-id="<?= $cp['fase_id'] ?? '' ?>"
                                            data-kode="<?= htmlspecialchars($cp['kode_cp']) ?>"
                                            data-elemen="<?= htmlspecialchars($cp['elemen'] ?? '') ?>"
                                            data-deskripsi="<?= htmlspecialchars($cp['deskripsi']) ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" class="d-inline" onsubmit="return confirm('Hapus Capaian Pembelajaran (CP) ini beserta seluruh TP turunannya? Data asesmen yang merujuk juga akan terdampak.');">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete_cp">
                                            <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                                            <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                                            <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                                            <input type="hidden" name="id" value="<?= $cp['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger rounded-end-3" title="Hapus CP">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
</main>

<!-- =========================================================================== -->
<!-- MODALS CP & TP GURU (ENLARGED, SPACIOUS & PROFESSIONAL) -->
<!-- =========================================================================== -->

<!-- Modal Add CP (Spacious Large Modal) -->
<div class="modal fade" id="modalAddCP" tabindex="-1" aria-labelledby="modalAddCPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_cp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-primary text-white shadow-xs">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalAddCPLabel">Tambah Capaian Pembelajaran (CP)</h5>
                            <small class="text-muted">Rumuskan kompetensi akhir mata pelajaran untuk tingkat atau fase kurikulum tertentu.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <!-- Kurikulum -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Pilih Kurikulum <span class="text-danger">*</span>
                            </label>
                            <select name="kurikulum_id" id="add_cp_kurikulum_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>" <?= ($filterKurId == $kur['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Kurikulum yang sedang aktif di sekolah.</div>
                        </div>

                        <!-- Mata Pelajaran -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-book text-primary me-1"></i>Mata Pelajaran yang Diampu <span class="text-danger">*</span>
                            </label>
                            <select name="mapel_id" id="add_cp_mapel_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($teacherMapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= ($filterMapelId == $mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Mata pelajaran yang Anda susun CP-nya.</div>
                        </div>

                        <!-- Fase -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-layers text-primary me-1"></i>Fase / Jenjang Kelas
                            </label>
                            <select name="fase_id" id="add_cp_fase_id" class="form-select rounded-3 py-2">
                                <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>" <?= ($filterFaseId == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Contoh: Fase E (Kelas X), Fase F (Kelas XI - XII).</div>
                        </div>

                        <!-- Elemen / Ranah -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-tags text-primary me-1"></i>Elemen / Ranah Pembelajaran
                            </label>
                            <input type="text" name="elemen" class="form-control rounded-3 py-2" placeholder="Contoh: Pemrograman Dasar / Analisis Data">
                            <div class="form-text small text-muted">Domain materi inti capaian pembelajaran.</div>
                        </div>

                        <!-- Kode CP Auto -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <label class="form-label small fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <i class="bi bi-upc-scan text-primary"></i>
                                        Kode CP (Terstandar Otomatis)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 ms-1" style="font-size:0.7rem;">Otomatis</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-2" id="btn_regen_cp_code" title="Generate ulang kode CP">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Regenerate Kode
                                    </button>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 font-monospace text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="kode_cp" id="add_cp_kode" class="form-control font-monospace fw-bold border-start-0 py-2" placeholder="CP-..." value="" required>
                                </div>
                                <div class="form-text small text-muted mt-1">Kode otomatis dibentuk berdasarkan Kurikulum & Mata Pelajaran. Anda dapat menyesuaikannya bila diperlukan.</div>
                            </div>
                        </div>

                        <!-- Deskripsi CP -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-primary me-1"></i>Deskripsi Capaian Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="add_cp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Tuliskan rumusan capaian pembelajaran secara lengkap... Contoh: Peserta didik mampu merancang, memprogram, dan menguji solusi perangkat lunak menggunakan bahasa pemrograman terstruktur serta berorientasi objek secara mandiri dan bertanggung jawab."></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="form-text small text-muted">Gunakan bahasa kompetensi yang jelas dan terukur.</span>
                                <span class="form-text small text-muted" id="add_cp_char_count">0 karakter</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-save"></i>
                        <span>Simpan Capaian Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit CP (Spacious Large Modal) -->
<div class="modal fade" id="modalEditCP" tabindex="-1" aria-labelledby="modalEditCPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_cp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_cp_id">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-primary text-white shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditCPLabel">Perbarui Capaian Pembelajaran (CP)</h5>
                            <small class="text-muted">Edit informasi atau redaksi kompetensi capaian pembelajaran yang telah dibuat.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Pilih Kurikulum <span class="text-danger">*</span>
                            </label>
                            <select name="kurikulum_id" id="edit_cp_kurikulum_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-book text-primary me-1"></i>Mata Pelajaran <span class="text-danger">*</span>
                            </label>
                            <select name="mapel_id" id="edit_cp_mapel_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($teacherMapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-layers text-primary me-1"></i>Fase / Jenjang Kelas
                            </label>
                            <select name="fase_id" id="edit_cp_fase_id" class="form-select rounded-3 py-2">
                                <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>">
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-tags text-primary me-1"></i>Elemen / Ranah Pembelajaran
                            </label>
                            <input type="text" name="elemen" id="edit_cp_elemen" class="form-control rounded-3 py-2" placeholder="Elemen ranah materi">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-upc-scan text-primary me-1"></i>Kode CP <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_cp" id="edit_cp_kode" class="form-control font-monospace fw-bold rounded-3 py-2" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-primary me-1"></i>Deskripsi Capaian Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="edit_cp_deskripsi" class="form-control rounded-3 p-3" rows="5" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle"></i>
                        <span>Perbarui Capaian Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add TP (Spacious Large Modal with Parent CP Context Preview) -->
<div class="modal fade" id="modalAddTP" tabindex="-1" aria-labelledby="modalAddTPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-success text-white shadow-xs">
                            <i class="bi bi-bullseye fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalAddTPLabel">Tambah Tujuan Pembelajaran (TP)</h5>
                            <small class="text-muted">Rumuskan tujuan pembelajaran spesifik sebagai turunan operasional dari Capaian Pembelajaran.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Step 1: Parent CP Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1.5">
                            <i class="bi bi-diagram-3 text-success me-1"></i>Pilih Induk Capaian Pembelajaran (CP) <span class="text-danger">*</span>
                        </label>
                        <select name="cp_id" id="add_tp_cp_id" class="form-select rounded-3 py-2" required>
                            <?php 
                            $groupedCpAdd = [];
                            foreach (($allCpForDropdown ?? $cpList) as $c) {
                                $grpKey = ($c['nama_kurikulum'] ?? 'Kurikulum') . ' — ' . ($c['nama_mapel'] ?? 'Mapel');
                                $groupedCpAdd[$grpKey][] = $c;
                            }
                            foreach ($groupedCpAdd as $grpName => $cList): ?>
                                <optgroup label="<?= htmlspecialchars($grpName) ?>">
                                    <?php foreach ($cList as $c): ?>
                                        <option value="<?= $c['id'] ?>" data-deskripsi="<?= htmlspecialchars($c['deskripsi']) ?>" data-elemen="<?= htmlspecialchars($c['elemen'] ?? '') ?>" data-mapel="<?= htmlspecialchars($c['nama_mapel'] ?? '') ?>">
                                            [<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel'] ?? '') ?> — <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 75, '...')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Parent CP Context Banner (Live Preview) -->
                    <div class="p-3 rounded-3 border mb-3 bg-light" id="add_tp_cp_preview_box" style="background-color: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded" id="preview_cp_kode">
                                CP Terpilih
                            </span>
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded small" id="preview_cp_elemen">
                                Elemen Ranah
                            </span>
                        </div>
                        <div class="small text-dark mt-1" id="preview_cp_deskripsi" style="line-height: 1.5; font-style: italic;">
                            Pilih salah satu CP di atas untuk melihat rumusan induk.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Kode TP Auto -->
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 rounded-3 border bg-light h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <i class="bi bi-upc-scan text-success"></i> Kode TP (Otomatis)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-1.5 py-0.5 ms-1" style="font-size:0.68rem;">Otomatis</span>
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2 rounded" id="btn_regen_tp_code" title="Generate ulang kode TP">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <div class="input-group mt-1">
                                    <span class="input-group-text bg-white border-end-0 font-monospace text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="kode_tp" id="add_tp_kode" class="form-control font-monospace fw-bold border-start-0 py-2" placeholder="TP-..." value="" required>
                                </div>
                                <div class="form-text small text-muted mt-1" style="font-size:0.75rem;">Turunan terstandar dari kode CP induk.</div>
                            </div>
                        </div>

                        <!-- Materi Pokok -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-bookmark text-success me-1"></i>Materi Pokok / Pokok Bahasan
                            </label>
                            <input type="text" name="materi_pokok" class="form-control rounded-3 py-2" placeholder="Contoh: Algoritma Pencarian & Pengurutan">
                            <div class="form-text small text-muted">Subjek materi pembelajaran yang akan diujikan.</div>
                        </div>

                        <!-- Deskripsi TP -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-success me-1"></i>Deskripsi Tujuan Pembelajaran (TP) <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="add_tp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Tuliskan tujuan pembelajaran yang operasional... Contoh: Peserta didik mampu mengimplementasikan algoritma binary search pada larik data terurut dan menganalisis efisiensi komputasinya dengan benar melalui praktikum mandiri."></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="form-text small text-muted"><i class="bi bi-lightbulb text-warning me-1"></i>Tips ABCD: <strong>A</strong>udience, <strong>B</strong>ehavior, <strong>C</strong>ondition, <strong>D</strong>egree.</span>
                                <span class="form-text small text-muted" id="add_tp_char_count">0 karakter</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-save"></i>
                        <span>Simpan Tujuan Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit TP (Spacious Large Modal) -->
<div class="modal fade" id="modalEditTP" tabindex="-1" aria-labelledby="modalEditTPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_tp_id">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-success text-white shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditTPLabel">Perbarui Tujuan Pembelajaran (TP)</h5>
                            <small class="text-muted">Edit rumusan atau materi pokok pada butir Tujuan Pembelajaran.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1.5">
                            <i class="bi bi-diagram-3 text-success me-1"></i>Pilih Induk Capaian Pembelajaran (CP) <span class="text-danger">*</span>
                        </label>
                        <select name="cp_id" id="edit_tp_cp_id" class="form-select rounded-3 py-2" required>
                            <?php foreach ($groupedCpAdd as $grpName => $cList): ?>
                                <optgroup label="<?= htmlspecialchars($grpName) ?>">
                                    <?php foreach ($cList as $c): ?>
                                        <option value="<?= $c['id'] ?>">[<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel'] ?? '') ?> — <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 75, '...')) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-upc-scan text-success me-1"></i>Kode TP <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_tp" id="edit_tp_kode" class="form-control font-monospace fw-bold rounded-3 py-2" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-bookmark text-success me-1"></i>Materi Pokok
                            </label>
                            <input type="text" name="materi_pokok" id="edit_tp_materi" class="form-control rounded-3 py-2">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-success me-1"></i>Deskripsi Tujuan Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="edit_tp_deskripsi" class="form-control rounded-3 p-3" rows="5" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle"></i>
                        <span>Perbarui Tujuan Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Next Code Maps for Auto-generation
const nextCpCodeMap = <?= json_encode($nextCpCodeMap ?? []) ?>;
const nextTpCodeMap = <?= json_encode($nextTpCodeMap ?? []) ?>;

function updateAutoCpCode() {
    const kurId = document.getElementById('add_cp_kurikulum_id')?.value;
    const mapelId = document.getElementById('add_cp_mapel_id')?.value;
    const kodeInp = document.getElementById('add_cp_kode');
    if (kodeInp && kurId && mapelId) {
        if (nextCpCodeMap[kurId] && nextCpCodeMap[kurId][mapelId]) {
            kodeInp.value = nextCpCodeMap[kurId][mapelId];
        }
    }
}

function updateAutoTpCode() {
    const cpId = document.getElementById('add_tp_cp_id')?.value;
    const kodeInp = document.getElementById('add_tp_kode');
    if (kodeInp && cpId) {
        if (nextTpCodeMap[cpId]) {
            kodeInp.value = nextTpCodeMap[cpId];
        }
    }
    updateParentCpPreview();
}

function updateParentCpPreview() {
    const cpSelect = document.getElementById('add_tp_cp_id');
    const previewKode = document.getElementById('preview_cp_kode');
    const previewElemen = document.getElementById('preview_cp_elemen');
    const previewDeskripsi = document.getElementById('preview_cp_deskripsi');

    if (!cpSelect || !previewDeskripsi) return;

    const opt = cpSelect.options[cpSelect.selectedIndex];
    if (opt) {
        const desk = opt.getAttribute('data-deskripsi') || opt.text || '';
        const elem = opt.getAttribute('data-elemen') || 'Elemen Umum';
        const match = opt.text.match(/^\[(.*?)\]/);
        const kode = match ? match[1] : 'CP Induk';

        if (previewKode) previewKode.textContent = kode;
        if (previewElemen) previewElemen.textContent = elem ? elem : 'Elemen Umum';
        if (previewDeskripsi) previewDeskripsi.textContent = desk;
    }
}

function filterFaseDropdown(kurikulumSelectId, faseSelectId) {
    const kurSel = document.getElementById(kurikulumSelectId);
    const faseSel = document.getElementById(faseSelectId);
    if (!kurSel || !faseSel) return;

    const selectedKurId = kurSel.value;
    const options = faseSel.querySelectorAll('option');

    let currentlySelectedStillValid = false;

    options.forEach(opt => {
        const optKurId = opt.getAttribute('data-kurikulum-id');
        if (!optKurId || optKurId === '' || optKurId === selectedKurId) {
            opt.style.display = '';
            opt.disabled = false;
            if (opt.selected) currentlySelectedStillValid = true;
        } else {
            opt.style.display = 'none';
            opt.disabled = true;
            if (opt.selected) opt.selected = false;
        }
    });

    if (!currentlySelectedStillValid) {
        faseSel.value = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Character counters for textareas
    const addCpDesk = document.getElementById('add_cp_deskripsi');
    const addCpCount = document.getElementById('add_cp_char_count');
    if (addCpDesk && addCpCount) {
        addCpDesk.addEventListener('input', () => {
            addCpCount.textContent = addCpDesk.value.length + ' karakter';
        });
    }

    const addTpDesk = document.getElementById('add_tp_deskripsi');
    const addTpCount = document.getElementById('add_tp_char_count');
    if (addTpDesk && addTpCount) {
        addTpDesk.addEventListener('input', () => {
            addTpCount.textContent = addTpDesk.value.length + ' karakter';
        });
    }

    // Dynamic Fase filtering in modals
    const kurAddCp = document.getElementById('add_cp_kurikulum_id');
    if (kurAddCp) {
        kurAddCp.addEventListener('change', () => {
            filterFaseDropdown('add_cp_kurikulum_id', 'add_cp_fase_id');
            updateAutoCpCode();
        });
        filterFaseDropdown('add_cp_kurikulum_id', 'add_cp_fase_id');
    }

    const mapelAddCp = document.getElementById('add_cp_mapel_id');
    if (mapelAddCp) {
        mapelAddCp.addEventListener('change', updateAutoCpCode);
    }

    const btnRegenCp = document.getElementById('btn_regen_cp_code');
    if (btnRegenCp) {
        btnRegenCp.addEventListener('click', updateAutoCpCode);
    }

    const modalAddCpEl = document.getElementById('modalAddCP');
    if (modalAddCpEl) {
        modalAddCpEl.addEventListener('shown.bs.modal', () => {
            const kodeInp = document.getElementById('add_cp_kode');
            if (kodeInp && !kodeInp.value) {
                updateAutoCpCode();
            }
        });
    }

    // TP Auto Code Listeners & Parent Preview
    const selectAddTpCp = document.getElementById('add_tp_cp_id');
    if (selectAddTpCp) {
        selectAddTpCp.addEventListener('change', updateAutoTpCode);
        updateParentCpPreview();
    }

    const btnRegenTp = document.getElementById('btn_regen_tp_code');
    if (btnRegenTp) {
        btnRegenTp.addEventListener('click', updateAutoTpCode);
    }

    const modalAddTpEl = document.getElementById('modalAddTP');
    if (modalAddTpEl) {
        modalAddTpEl.addEventListener('shown.bs.modal', () => {
            const kodeInp = document.getElementById('add_tp_kode');
            if (kodeInp && !kodeInp.value) {
                updateAutoTpCode();
            }
            updateParentCpPreview();
        });
    }

    // Direct "+ Tambah TP Turunan" from CP row
    document.querySelectorAll('.btn-add-tp-for-cp').forEach(btn => {
        btn.addEventListener('click', function() {
            const cpId = this.dataset.cpId;
            const select = document.getElementById('add_tp_cp_id');
            if (select && cpId) {
                select.value = cpId;
                updateAutoTpCode();
                updateParentCpPreview();
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
            if (selKur && this.dataset.kurikulumId) {
                selKur.value = this.dataset.kurikulumId;
                filterFaseDropdown('edit_cp_kurikulum_id', 'edit_cp_fase_id');
            }

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

    // Auto adjust datatables on window resize
    window.addEventListener('resize', () => {
        if (window.jQuery && jQuery.fn.dataTable) {
            jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
