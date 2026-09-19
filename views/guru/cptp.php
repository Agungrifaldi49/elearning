<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Header Breadcrumb & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-card-checklist text-primary me-2"></i>Penyusunan Capaian & Tujuan Pembelajaran (CP & TP)
            </h4>
            <p class="text-muted small mb-0">Susun dan kelola Capaian Pembelajaran (CP) serta Tujuan Pembelajaran (TP) untuk mata pelajaran yang Anda ampu. Data yang Anda susun akan otomatis terhubung ke kurikulum sekolah.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary shadow-sm fw-bold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalAddTP">
                <i class="bi bi-plus-circle me-1"></i> Tambah TP Baru
            </button>
            <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                <i class="bi bi-plus-circle me-1"></i> Tambah CP Baru
            </button>
        </div>
    </div>

    <!-- Flash Notification Alerts -->
    <?php if (class_exists('FlashHelper')): ?>
        <?php if (FlashHelper::hasSuccess()): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= FlashHelper::getSuccess() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (FlashHelper::hasError()): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= FlashHelper::getError() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Banner Informasi Guru & Mapel Ampuan -->
    <div class="card-custom p-3.5 mb-4 shadow-sm border-0" style="background: linear-gradient(135deg, #f0f7ff 0%, #e0f2fe 100%); border-left: 5px solid #0284c7 !important;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white p-3 rounded-circle shadow-xs">
                    <i class="bi bi-person-workspace fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        Selamat datang, <?= htmlspecialchars($guru['nama_lengkap'] ?? 'Bpk/Ibu Guru') ?>
                    </h6>
                    <div class="text-secondary small d-flex flex-wrap align-items-center gap-1.5">
                        <span>Mata Pelajaran yang Anda ampu:</span>
                        <?php if (!empty($teacherMapelList)): ?>
                            <?php foreach ($teacherMapelList as $tmp): ?>
                                <span class="badge bg-primary text-white border px-2 py-1">
                                    <i class="bi bi-journal-bookmark me-1"></i><?= htmlspecialchars($tmp['nama_mapel']) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">Semua Mapel</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="bg-white p-2.5 px-3 rounded-3 shadow-xs border text-center">
                    <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;">CP Terdata</div>
                    <div class="fs-5 fw-bold text-primary"><?= count($cpList) ?></div>
                </div>
                <div class="bg-white p-2.5 px-3 rounded-3 shadow-xs border text-center">
                    <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;">TP Terdata</div>
                    <div class="fs-5 fw-bold text-success"><?= count($tpList) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card-custom p-3.5 mb-4 shadow-sm border">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-end">
            <input type="hidden" name="url" value="guru/cptp">
            
            <div class="col-12 col-md-4">
                <label class="small fw-bold text-muted mb-1">Pilih Mata Pelajaran:</label>
                <select name="filter_mapel_id" class="form-select form-select-sm">
                    <option value="">-- Semua Mata Pelajaran Saya --</option>
                    <?php foreach ($teacherMapelList as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= ($filterMapelId == $mp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="small fw-bold text-muted mb-1">Pilih Kurikulum:</label>
                <select name="filter_kurikulum_id" class="form-select form-select-sm">
                    <?php foreach ($kurikulumList as $kur): ?>
                        <option value="<?= $kur['id'] ?>" <?= ($filterKurId == $kur['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="small fw-bold text-muted mb-1">Pilih Fase:</label>
                <select name="filter_fase_id" class="form-select form-select-sm">
                    <option value="">-- Semua Fase --</option>
                    <?php foreach ($allFaseList as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($filterFaseId == $f['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-bold"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                <?php if ($filterMapelId || $filterFaseId): ?>
                    <a href="<?= BASE_URL ?>index.php?url=guru/cptp" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- CP & TP Table Card -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Daftar Rumusan CP & TP</h5>
                <p class="text-muted small mb-0">Rincian Capaian Pembelajaran dan Tujuan Pembelajaran yang dapat digunakan untuk bahan ajar dan penilaian formatif/sumatif.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                    <i class="bi bi-plus-circle me-1"></i> Tambah CP Baru
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:130px;">Kurikulum & Fase</th>
                        <th style="width:180px;">Mata Pelajaran</th>
                        <th style="width:160px;">Kode & Elemen CP</th>
                        <th>Deskripsi Capaian Pembelajaran</th>
                        <th style="width:340px;">Tujuan Pembelajaran (TP) Terkait</th>
                        <th class="text-center" style="width:100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cpList as $i => $cp): 
                        $childTps = array_filter($tpList, function($t) use ($cp) { return $t['cp_id'] == $cp['id']; });
                        $isMyCp = ($cp['guru_id'] == $guruId);
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border mb-1 d-inline-block"><?= htmlspecialchars($cp['kode_kurikulum']) ?></span>
                                <?php if (!empty($cp['nama_fase'])): ?>
                                    <span class="badge bg-secondary-subtle text-dark border d-block"><?= htmlspecialchars($cp['nama_fase']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($cp['nama_mapel']) ?></div>
                                <?php if ($isMyCp): ?>
                                    <span class="badge bg-success-subtle text-success border mt-1.5 d-inline-block" style="font-size:0.75rem; font-weight:500;">
                                        <i class="bi bi-person-check-fill me-1"></i>Disusun oleh Anda
                                    </span>
                                <?php elseif (!empty($cp['nama_guru'])): ?>
                                    <span class="badge bg-info-subtle text-dark border mt-1.5 d-inline-block" style="font-size:0.72rem; font-weight:normal;">
                                        <i class="bi bi-person-fill text-primary me-1"></i>Oleh: <?= htmlspecialchars($cp['nama_guru']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border mt-1.5 d-inline-block" style="font-size:0.72rem; font-weight:normal;">
                                        <i class="bi bi-shield-check me-1"></i>Admin / Kurikulum
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold font-monospace text-primary"><?= htmlspecialchars($cp['kode_cp']) ?></div>
                                <?php if (!empty($cp['elemen'])): ?>
                                    <span class="badge bg-light text-secondary border mt-1"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($cp['elemen']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small text-dark" style="max-height: 120px; overflow-y: auto;">
                                    <?= nl2br(htmlspecialchars($cp['deskripsi'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php if (empty($childTps)): ?>
                                    <div class="alert alert-light border py-1.5 px-2 mb-2 text-muted small"><i class="bi bi-exclamation-circle me-1"></i> Belum ada TP</div>
                                <?php else: ?>
                                    <div class="tp-list-box mb-2" style="max-height: 180px; overflow-y: auto;">
                                        <?php foreach ($childTps as $tp): ?>
                                            <div class="p-2 mb-1.5 rounded-2 bg-light border d-flex justify-content-between align-items-start gap-2">
                                                <div class="small">
                                                    <strong class="text-primary font-monospace"><?= htmlspecialchars($tp['kode_tp']) ?></strong>
                                                    <?php if (!empty($tp['materi_pokok'])): ?>
                                                        <span class="text-muted fw-semibold">(<?= htmlspecialchars($tp['materi_pokok']) ?>):</span>
                                                    <?php endif; ?>
                                                    <span class="text-secondary"><?= htmlspecialchars($tp['deskripsi']) ?></span>
                                                </div>
                                                <div class="d-flex gap-1 flex-shrink-0">
                                                    <button type="button" class="btn btn-xs btn-outline-warning rounded px-1.5 py-0.5 btn-edit-tp" 
                                                        title="Edit TP"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditTP"
                                                        data-id="<?= $tp['id'] ?>"
                                                        data-cp-id="<?= $tp['cp_id'] ?>"
                                                        data-kode="<?= htmlspecialchars($tp['kode_tp']) ?>"
                                                        data-materi="<?= htmlspecialchars($tp['materi_pokok'] ?? '') ?>"
                                                        data-deskripsi="<?= htmlspecialchars($tp['deskripsi']) ?>">
                                                        <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                    <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" class="d-inline" onsubmit="return confirm('Hapus Tujuan Pembelajaran (TP) ini?');">
                                                        <?= Security::csrfField() ?>
                                                        <input type="hidden" name="action" value="delete_tp">
                                                        <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                                                        <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                                                        <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                                                        <input type="hidden" name="id" value="<?= $tp['id'] ?>">
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded px-1.5 py-0.5" title="Hapus TP"><i class="bi bi-trash" style="font-size: 0.75rem;"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill fw-semibold btn-add-tp-for-cp px-2.5 py-1"
                                    data-bs-toggle="modal" data-bs-target="#modalAddTP"
                                    data-cp-id="<?= $cp['id'] ?>"
                                    data-cp-title="[<?= htmlspecialchars($cp['kode_cp']) ?>] <?= htmlspecialchars($cp['nama_mapel']) ?>">
                                    <i class="bi bi-plus-circle me-1"></i>+ Tambah TP
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-edit-cp" 
                                        title="Edit CP"
                                        data-bs-toggle="modal" data-bs-target="#modalEditCP"
                                        data-id="<?= $cp['id'] ?>"
                                        data-kurikulum-id="<?= $cp['kurikulum_id'] ?>"
                                        data-mapel-id="<?= $cp['mapel_id'] ?>"
                                        data-fase-id="<?= $cp['fase_id'] ?? '' ?>"
                                        data-kode="<?= htmlspecialchars($cp['kode_cp']) ?>"
                                        data-elemen="<?= htmlspecialchars($cp['elemen'] ?? '') ?>"
                                        data-deskripsi="<?= htmlspecialchars($cp['deskripsi']) ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" class="d-inline" onsubmit="return confirm('Hapus Capaian Pembelajaran (CP) ini beserta seluruh TP turunannya?');">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="action" value="delete_cp">
                                        <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                                        <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                                        <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                                        <input type="hidden" name="id" value="<?= $cp['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus CP"><i class="bi bi-trash"></i></button>
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

<!-- =========================================================================== -->
<!-- MODALS CP & TP GURU -->
<!-- =========================================================================== -->

<!-- Modal Add CP -->
<div class="modal fade" id="modalAddCP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_cp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Capaian Pembelajaran (CP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum</label>
                        <select name="kurikulum_id" id="add_cp_kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>" <?= ($filterKurId == $kur['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran yang Anda Ampu</label>
                        <select name="mapel_id" id="add_cp_mapel_id" class="form-select" required>
                            <?php foreach ($teacherMapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>" <?= ($filterMapelId == $mp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Fase</label>
                        <select name="fase_id" id="add_cp_fase_id" class="form-select">
                            <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                            <?php foreach ($allFaseList as $f): ?>
                                <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>" <?= ($filterFaseId == $f['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode CP (Otomatis)</label>
                            <div class="input-group">
                                <input type="text" name="kode_cp" id="add_cp_kode" class="form-control font-monospace" placeholder="Otomatis..." value="">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_regen_cp_code" title="Generate Ulang Kode"><i class="bi bi-arrow-clockwise"></i></button>
                            </div>
                            <small class="text-muted" style="font-size:0.75rem;">Dibuat otomatis, dapat disesuaikan manual.</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Elemen / Ranah</label>
                            <input type="text" name="elemen" class="form-control" placeholder="Menyimak / Membaca / Koding">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Capaian Pembelajaran</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Peserta didik mampu memahami dan menganalisis..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan CP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit CP -->
<div class="modal fade" id="modalEditCP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_cp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_cp_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Capaian Pembelajaran (CP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum</label>
                        <select name="kurikulum_id" id="edit_cp_kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran</label>
                        <select name="mapel_id" id="edit_cp_mapel_id" class="form-select" required>
                            <?php foreach ($teacherMapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Fase</label>
                        <select name="fase_id" id="edit_cp_fase_id" class="form-select">
                            <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                            <?php foreach ($allFaseList as $f): ?>
                                <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>">
                                    <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode CP</label>
                            <input type="text" name="kode_cp" id="edit_cp_kode" class="form-control font-monospace" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Elemen / Ranah</label>
                            <input type="text" name="elemen" id="edit_cp_elemen" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Capaian Pembelajaran</label>
                        <textarea name="deskripsi" id="edit_cp_deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Perbarui CP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add TP -->
<div class="modal fade" id="modalAddTP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Tujuan Pembelajaran (TP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Induk Capaian Pembelajaran (CP)</label>
                        <select name="cp_id" id="add_tp_cp_id" class="form-select" required>
                            <?php 
                            $groupedCpAdd = [];
                            foreach (($allCpForDropdown ?? $cpList) as $c) {
                                $grpKey = ($c['nama_kurikulum'] ?? 'Kurikulum') . ' — ' . ($c['nama_mapel'] ?? 'Mapel');
                                $groupedCpAdd[$grpKey][] = $c;
                            }
                            foreach ($groupedCpAdd as $grpName => $cList): ?>
                                <optgroup label="<?= htmlspecialchars($grpName) ?>">
                                    <?php foreach ($cList as $c): ?>
                                        <option value="<?= $c['id'] ?>">[<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 50, '...')) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode TP (Otomatis)</label>
                            <div class="input-group">
                                <input type="text" name="kode_tp" id="add_tp_kode" class="form-control font-monospace" placeholder="Otomatis..." value="">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_regen_tp_code" title="Generate Ulang Kode"><i class="bi bi-arrow-clockwise"></i></button>
                            </div>
                            <small class="text-muted" style="font-size:0.75rem;">Dibuat otomatis, dapat disesuaikan manual.</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Materi Pokok</label>
                            <input type="text" name="materi_pokok" class="form-control" placeholder="Materi / Topik Utama">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Tujuan Pembelajaran</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Peserta didik mampu mempraktikkan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan TP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit TP -->
<div class="modal fade" id="modalEditTP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_tp_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Tujuan Pembelajaran (TP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Induk Capaian Pembelajaran (CP)</label>
                        <select name="cp_id" id="edit_tp_cp_id" class="form-select" required>
                            <?php foreach ($groupedCpAdd as $grpName => $cList): ?>
                                <optgroup label="<?= htmlspecialchars($grpName) ?>">
                                    <?php foreach ($cList as $c): ?>
                                        <option value="<?= $c['id'] ?>">[<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 50, '...')) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode TP</label>
                            <input type="text" name="kode_tp" id="edit_tp_kode" class="form-control font-monospace" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Materi Pokok</label>
                            <input type="text" name="materi_pokok" id="edit_tp_materi" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Tujuan Pembelajaran</label>
                        <textarea name="deskripsi" id="edit_tp_deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Perbarui TP</button>
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

    // TP Auto Code Listeners
    const selectAddTpCp = document.getElementById('add_tp_cp_id');
    if (selectAddTpCp) {
        selectAddTpCp.addEventListener('change', updateAutoTpCode);
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
        });
    }

    // Direct "+ Tambah TP" from CP row
    document.querySelectorAll('.btn-add-tp-for-cp').forEach(btn => {
        btn.addEventListener('click', function() {
            const cpId = this.dataset.cpId;
            const select = document.getElementById('add_tp_cp_id');
            if (select && cpId) {
                select.value = cpId;
                updateAutoTpCode();
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
