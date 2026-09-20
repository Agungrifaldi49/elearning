<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
if (!function_exists('formatTpDescriptionHtml')) {
    function formatTpDescriptionHtml($text) {
        if (empty($text)) return '';
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        if (count($lines) <= 1 && !preg_match('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓✔☑▪▫►▶→➔➢+~–—\x{2022}\x{25AA}\x{2713}\x{2714}])\s*/u', trim($text))) {
            return '<div class="tp-deskripsi-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . nl2br(htmlspecialchars($text)) . '</div>';
        }
        $html = '<div class="tp-formatted-list d-flex flex-column" style="gap: 6px; margin-top: 3px;">';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            // 1. Numbered: 1. or 1) or 1-
            if (preg_match('/^(\d+)[\.\)\-]\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace rounded-pill flex-shrink-0" style="font-size:0.72rem; min-width:22px; padding: 2.5px 6px; text-align:center; font-weight:700;">' . $m[1] . '</span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 2. Lettered: a. or A. or a)
            } elseif (preg_match('/^([a-zA-Z])[\.\)]\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="badge bg-secondary-subtle text-dark border font-monospace rounded-pill flex-shrink-0" style="font-size:0.72rem; min-width:22px; padding: 2.5px 6px; text-align:center; font-weight:700;">' . strtoupper($m[1]) . '</span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 3. Checkmarks: ✓, ✔, ☑
            } elseif (preg_match('/^([✓✔☑\x{2713}\x{2714}])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="text-success flex-shrink-0 fw-bold" style="font-size:0.95rem; line-height:1.5; width:18px; text-align:center;"><i class="bi bi-check-circle-fill"></i></span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 4. Arrows: →, ➔, ➢, ►, >
            } elseif (preg_match('/^([→➔➢►▶>])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:0.95rem; line-height:1.5; width:18px; text-align:center;"><i class="bi bi-arrow-right-short fs-5"></i></span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 5. Bullets & Other Symbols: •, -, *, ▪, ▫, +
            } elseif (preg_match('/^([•\-\*▪▫+–—\x{2022}\x{25AA}])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:1.15rem; line-height:1.2; width:18px; text-align:center;">•</span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            } else {
                $html .= '<div class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($trimmed) . '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}
?>

<style>
.tp-formatted-list {
    font-size: 0.88rem;
}
.tp-list-row {
    margin-bottom: 0.15rem;
}
.tp-list-row:last-child {
    margin-bottom: 0;
}
.tp-list-text {
    word-break: break-word;
}
.rombel-card {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1.5px solid #e2e8f0;
    background-color: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.rombel-card:hover {
    border-color: #3b82f6 !important;
    background-color: #f8fafc !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.08);
}
.rombel-card.selected-card {
    border-color: #2563eb !important;
    background: #f0f7ff !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
}
.rombel-checkbox {
    width: 1.2rem;
    height: 1.2rem;
    cursor: pointer;
}
.rombel-scroll-box::-webkit-scrollbar {
    width: 6px;
}
.rombel-scroll-box::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 4px;
}
.rombel-scroll-box::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.rombel-scroll-box::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Header Breadcrumb & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Manajemen Kurikulum, CP/TP & Struktur Penilaian
            </h4>
            <p class="text-muted small mb-0">Kelola arsitektur kurikulum dinamis, pemetaan fase/tingkat, capaian pembelajaran, dan bobot penilaian tanpa mengunci sistem.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm fw-bold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalAddKurikulum">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kurikulum Baru
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

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Kurikulum</div>
                        <div class="fs-3 fw-bold text-dark my-1"><?= count($kurikulumList) ?></div>
                        <small class="text-primary fw-semibold"><i class="bi bi-collection me-1"></i>Data-driven system</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-mortarboard fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Rombel Terpetakan</div>
                        <div class="fs-3 fw-bold text-success my-1"><?= count($rombelKurikulumList) ?></div>
                        <small class="text-muted"><i class="bi bi-check-circle me-1"></i>Multi-Tahun Ajaran</small>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-building-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Fase & Tingkat</div>
                        <div class="fs-3 fw-bold text-info my-1"><?= count($allFaseList) ?></div>
                        <small class="text-muted"><i class="bi bi-layers me-1"></i>Fase E, F & Lainnya</small>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-diagram-2 fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 shadow-sm border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total CP & TP</div>
                        <div class="fs-3 fw-bold text-dark my-1"><?= count($cpList) ?> <span class="fs-6 text-muted">CP</span> / <?= count($tpList) ?> <span class="fs-6 text-muted">TP</span></div>
                        <small class="text-muted"><i class="bi bi-bookmark-check me-1"></i>Capaian Pembelajaran</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-award fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills Tabs -->
    <div class="card-custom p-2 mb-4 shadow-sm">
        <ul class="nav nav-pills nav-fill gap-2" id="kurikulumTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $activeTab === 'kurikulum' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabKurikulum" role="tab" aria-selected="<?= $activeTab === 'kurikulum' ? 'true' : 'false' ?>">
                    <i class="bi bi-mortarboard-fill me-1.5"></i> 1. Master Kurikulum
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $activeTab === 'fase' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabFase" role="tab" aria-selected="<?= $activeTab === 'fase' ? 'true' : 'false' ?>">
                    <i class="bi bi-layers-fill me-1.5"></i> 2. Fase / Tingkat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $activeTab === 'rombel' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabRombel" role="tab" aria-selected="<?= $activeTab === 'rombel' ? 'true' : 'false' ?>">
                    <i class="bi bi-building me-1.5"></i> 3. Kurikulum Rombel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $activeTab === 'struktur' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabStruktur" role="tab" aria-selected="<?= $activeTab === 'struktur' ? 'true' : 'false' ?>">
                    <i class="bi bi-journal-text me-1.5"></i> 4. Struktur Mapel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $activeTab === 'cptp' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabCPTP" role="tab" aria-selected="<?= $activeTab === 'cptp' ? 'true' : 'false' ?>">
                    <i class="bi bi-card-checklist me-1.5"></i> 5. CP & TP
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $activeTab === 'komponen' ? 'active' : '' ?> rounded-3 fw-semibold py-2.5" data-bs-toggle="tab" data-bs-target="#tabKomponen" role="tab" aria-selected="<?= $activeTab === 'komponen' ? 'true' : 'false' ?>">
                    <i class="bi bi-sliders me-1.5"></i> 6. Bobot Penilaian
                </button>
            </li>
        </ul>
    </div>

    <!-- TAB CONTENTS -->
    <div class="tab-content" id="kurikulumTabContent">

        <!-- =================================================================== -->
        <!-- TAB 1: MASTER KURIKULUM -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'kurikulum' ? 'show active' : '' ?>" id="tabKurikulum">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Daftar Kurikulum Terdaftar</h5>
                        <p class="text-muted small mb-0">Kurikulum tidak di-hardcode. Kurikulum baru dapat ditambahkan kapan saja tanpa merusak arsip kurikulum lama.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddKurikulum">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Kurikulum
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kode</th>
                                <th>Nama Kurikulum</th>
                                <th>Periode Berlaku</th>
                                <th>Status</th>
                                <th>Rombel & Rapor Terkait</th>
                                <th class="text-center" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kurikulumList as $i => $k): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fs-6 font-monospace"><?= htmlspecialchars($k['kode']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($k['nama']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($k['deskripsi'] ?: 'Kurikulum pembelajaran resmi SMK') ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= $k['tahun_mulai'] ?></span> s/d 
                                        <?= $k['tahun_selesai'] ? "<span class='fw-semibold'>{$k['tahun_selesai']}</span>" : "<span class='badge bg-success-subtle text-success border'>Sekarang</span>" ?>
                                    </td>
                                    <td>
                                        <?php if ($k['status'] === 'aktif'): ?>
                                            <span class="badge bg-success px-3 py-2 shadow-xs"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        <?php elseif ($k['status'] === 'arsip'): ?>
                                            <span class="badge bg-secondary px-3 py-2"><i class="bi bi-archive me-1"></i>Arsip</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">Rombel: <strong><?= $k['total_rombel_terhubung'] ?></strong> kelas</small>
                                        <small class="d-block text-muted">Histori Rapor: <strong><?= $k['total_rapor_terbit'] ?></strong> siswa</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-kurikulum" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditKurikulum"
                                                data-id="<?= $k['id'] ?>"
                                                data-kode="<?= htmlspecialchars($k['kode']) ?>"
                                                data-nama="<?= htmlspecialchars($k['nama']) ?>"
                                                data-mulai="<?= $k['tahun_mulai'] ?>"
                                                data-selesai="<?= $k['tahun_selesai'] ?>"
                                                data-status="<?= $k['status'] ?>"
                                                data-deskripsi="<?= htmlspecialchars($k['deskripsi'] ?? '') ?>"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus kurikulum ini? (Hanya bisa dihapus jika belum ada riwayat nilai/rapor)');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_kurikulum">
                                                <input type="hidden" name="redirect_tab" value="kurikulum">
                                                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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

        <!-- =================================================================== -->
        <!-- TAB 2: FASE / TINGKAT STRUKTUR -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'fase' ? 'show active' : '' ?>" id="tabFase">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Pengaturan Fase / Jenjang Tingkat</h5>
                        <p class="text-muted small mb-0">Fase tidak dibatasi hanya E/F. Anda dapat menambahkan fase atau jenjang struktur baru sesuai kebutuhan kurikulum masa depan.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddFase">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Fase Baru
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kurikulum</th>
                                <th>Kode Fase</th>
                                <th>Nama Lengkap Fase</th>
                                <th>Tingkat Kelas</th>
                                <th>Keterangan</th>
                                <th class="text-center" style="width:140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allFaseList as $i => $f): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($f['nama_kurikulum']) ?></span></td>
                                    <td><strong class="font-monospace fs-6 text-primary">Fase <?= htmlspecialchars($f['kode']) ?></strong></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($f['nama']) ?></td>
                                    <td><span class="badge bg-info-subtle text-dark border"><?= htmlspecialchars($f['tingkat_kelas'] ?: 'Semua') ?></span></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($f['keterangan'] ?: '-') ?></small></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-fase" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditFase"
                                                data-id="<?= $f['id'] ?>"
                                                data-kurikulum-id="<?= $f['kurikulum_id'] ?>"
                                                data-kode="<?= htmlspecialchars($f['kode']) ?>"
                                                data-nama="<?= htmlspecialchars($f['nama']) ?>"
                                                data-tingkat="<?= htmlspecialchars($f['tingkat_kelas'] ?? '') ?>"
                                                data-keterangan="<?= htmlspecialchars($f['keterangan'] ?? '') ?>"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus fase ini?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_fase">
                                                <input type="hidden" name="redirect_tab" value="fase">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
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

        <!-- =================================================================== -->
        <!-- TAB 3: KURIKULUM ROMBEL KELAS & VALIDASI BENTROK -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'rombel' ? 'show active' : '' ?>" id="tabRombel">
            <div class="card-custom p-4 shadow-sm mb-4">
                
                <!-- Notice Banner: Conflict Validation Rules -->
                <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle p-2.5 flex-shrink-0">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-primary">Integritas & Anti-Bentrok Kurikulum Rombel:</h6>
                        <small class="text-muted d-block">
                            Sistem secara otomatis <strong>mencegah lebih dari 1 kurikulum aktif</strong> untuk rombel yang sama pada tahun ajaran yang sama.
                            Namun, histori kurikulum tahun-tahun sebelumnya (misal 2026/2027 vs 2029/2030) tetap tersimpan utuh dan terisolasi tanpa saling menimpa.
                        </small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Pemetaan Kurikulum Rombel Kelas</h5>
                        <p class="text-muted small mb-0">Tentukan kurikulum dan fase yang berlaku untuk setiap kelas per tahun ajaran.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAssignRombel">
                        <i class="bi bi-plus-circle me-1"></i> Pasang Kurikulum ke Rombel
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Tahun Ajaran</th>
                                <th>Rombel Kelas</th>
                                <th>Kurikulum Yang Digunakan</th>
                                <th>Fase / Jenjang</th>
                                <th>Status Periode</th>
                                <th class="text-center" style="width:140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rombelKurikulumList as $i => $rk): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <span class="badge <?= !empty($rk['ta_is_active']) ? 'bg-success' : 'bg-secondary' ?> fw-bold px-2.5 py-1.5">
                                            <?= htmlspecialchars($rk['tahun_ajaran'] ?? $rk['nama_tahun']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($rk['nama_kelas']) ?></div>
                                        <small class="text-muted">Tingkat <?= htmlspecialchars($rk['tingkat']) ?> (<?= htmlspecialchars($rk['nama_jurusan'] ?? 'Umum') ?>)</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fs-6">
                                            <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($rk['nama_kurikulum']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $faseDisplay = $rk['nama_fase'] ?? '';
                                        if (empty($faseDisplay)) {
                                            $t = strtoupper(trim($rk['tingkat'] ?? ''));
                                            $n = strtoupper(trim($rk['nama_kelas'] ?? ''));
                                            if (in_array($t, ['XI', 'XII', '11', '12']) || strpos($n, 'XII') !== false || strpos($n, 'XI') !== false) {
                                                $faseDisplay = 'Fase F (Kelas XI - XII)';
                                            } elseif ($t === 'X' || strpos($n, 'X') !== false || strpos($n, '10') !== false) {
                                                $faseDisplay = 'Fase E (Kelas X)';
                                            }
                                        }
                                        ?>
                                        <?php if (!empty($faseDisplay)): ?>
                                            <span class="badge bg-info-subtle text-dark border px-2.5 py-1.5"><?= htmlspecialchars($faseDisplay) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rk['status'] === 'aktif'): ?>
                                            <span class="badge bg-success shadow-xs"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        <?php elseif ($rk['status'] === 'selesai'): ?>
                                            <span class="badge bg-secondary"><i class="bi bi-check2-all me-1"></i>Selesai / Alumni</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-rombel" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditRombel"
                                                data-id="<?= $rk['id'] ?>"
                                                data-kelas="<?= htmlspecialchars($rk['nama_kelas']) ?>"
                                                data-kurikulum-id="<?= $rk['kurikulum_id'] ?>"
                                                data-fase-id="<?= $rk['fase_id'] ?? '' ?>"
                                                data-status="<?= $rk['status'] ?>"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Lepaskan kurikulum dari rombel ini?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_rombel">
                                                <input type="hidden" name="redirect_tab" value="rombel">
                                                <input type="hidden" name="id" value="<?= $rk['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
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

        <!-- =================================================================== -->
        <!-- TAB 4: STRUKTUR MATA PELAJARAN PER KURIKULUM -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'struktur' ? 'show active' : '' ?>" id="tabStruktur">
            <div class="card-custom p-4 shadow-sm mb-4">
                
                <!-- Filter Kurikulum Selector -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 border-bottom pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted text-nowrap">Pilih Kurikulum:</label>
                        <select class="form-select form-select-sm fw-bold" onchange="location.href='<?= BASE_URL ?>index.php?url=admin/kurikulum&tab=struktur&kurikulum_id=' + this.value">
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>" <?= $selectedKurId == $kur['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddStrukturMapel">
                        <i class="bi bi-plus-circle me-1"></i> Kaitkan Mapel ke Kurikulum Ini
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kode Mapel</th>
                                <th>Nama Mata Pelajaran</th>
                                <th>Kelompok</th>
                                <th>Fase Target</th>
                                <th>Tingkat</th>
                                <th>Alokasi JP</th>
                                <th>KKM Standar</th>
                                <th class="text-center" style="width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($strukturMapelList as $i => $sm): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><code><?= htmlspecialchars($sm['kode_mapel']) ?></code></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($sm['nama_mapel']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border"><?= htmlspecialchars($sm['kelompok_mapel']) ?></span>
                                        <?php if (!empty($sm['nama_jurusan'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border mt-1 d-block"><i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($sm['nama_jurusan']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($sm['nama_fase'] ?? 'Semua Fase') ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($sm['tingkat'] ?: 'Semua') ?></span></td>
                                    <td><?= $sm['alokasi_jp'] ?> JP / Minggu</td>
                                    <td><span class="fw-bold text-success"><?= $sm['kkm'] ?></span></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-struktur" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditStrukturMapel"
                                                data-id="<?= $sm['id'] ?>"
                                                data-mapel-nama="<?= htmlspecialchars($sm['nama_mapel']) ?>"
                                                data-mapel-kode="<?= htmlspecialchars($sm['kode_mapel']) ?>"
                                                data-fase-id="<?= $sm['fase_id'] ?? '' ?>"
                                                data-jurusan-id="<?= $sm['jurusan_id'] ?? '' ?>"
                                                data-tingkat="<?= htmlspecialchars($sm['tingkat'] ?? 'X') ?>"
                                                data-kelompok="<?= htmlspecialchars($sm['kelompok_mapel'] ?? 'Kejuruan') ?>"
                                                data-jp="<?= $sm['alokasi_jp'] ?>"
                                                data-kkm="<?= $sm['kkm'] ?>"
                                                title="Edit Konfigurasi Mapel">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Lepaskan mapel ini dari kurikulum?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_mapel">
                                                <input type="hidden" name="redirect_tab" value="struktur">
                                                <input type="hidden" name="kurikulum_id" value="<?= $selectedKurId ?>">
                                                <input type="hidden" name="id" value="<?= $sm['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Lepas"><i class="bi bi-trash"></i></button>
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

        <!-- =================================================================== -->
        <!-- TAB 5: CAPAIAN PEMBELAJARAN (CP) & TUJUAN PEMBELAJARAN (TP) -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'cptp' ? 'show active' : '' ?>" id="tabCPTP">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-card-checklist text-primary me-2"></i>Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP)</h5>
                        <p class="text-muted small mb-0">Setiap kurikulum memiliki CP/TP terisolasi sehingga pergantian kurikulum masa depan tidak akan mengganggu materi & rapor masa lalu.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddTP">
                            <i class="bi bi-plus-circle me-1"></i> Tambah TP Baru
                        </button>
                        <button class="btn btn-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                            <i class="bi bi-plus-circle me-1"></i> Tambah CP Baru
                        </button>
                    </div>
                </div>

                <!-- Filter Box for CP & TP -->
                <div class="bg-light p-3 rounded-3 mb-4 border">
                    <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-center">
                        <input type="hidden" name="url" value="admin/kurikulum">
                        <input type="hidden" name="tab" value="cptp">
                        <div class="col-12 col-md-3">
                            <label class="small fw-bold text-muted mb-1">Filter Kurikulum:</label>
                            <select name="filter_kurikulum_id" class="form-select form-select-sm">
                                <option value="">-- Semua Kurikulum --</option>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>" <?= ($filterCpKurId == $kur['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="small fw-bold text-muted mb-1">Filter Mata Pelajaran:</label>
                            <select name="filter_mapel_id" class="form-select form-select-sm">
                                <option value="">-- Semua Mata Pelajaran --</option>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= ($filterCpMapelId == $mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="small fw-bold text-muted mb-1">Filter Fase:</label>
                            <select name="filter_fase_id" class="form-select form-select-sm">
                                <option value="">-- Semua Fase --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" <?= ($filterCpFaseId == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-1 align-items-end pt-md-3">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill fw-bold"><i class="bi bi-funnel me-1"></i> Filter</button>
                            <?php if ($filterCpKurId || $filterCpMapelId || $filterCpFaseId): ?>
                                <a href="<?= BASE_URL ?>index.php?url=admin/kurikulum&tab=cptp" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th style="width:130px;">Kurikulum & Fase</th>
                                <th style="width:170px;">Mata Pelajaran</th>
                                <th style="width:160px;">Kode & Elemen CP</th>
                                <th>Deskripsi Capaian Pembelajaran</th>
                                <th style="width:340px;">Tujuan Pembelajaran (TP) Terkait</th>
                                <th class="text-center" style="width:110px;">Aksi CP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cpList as $i => $cp): 
                                $childTps = array_filter($tpList, function($t) use ($cp) { return $t['cp_id'] == $cp['id']; });
                            ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border mb-1 d-inline-block"><?= htmlspecialchars($cp['kode_kurikulum']) ?></span>
                                            <?php if (!empty($cp['nama_fase'])): ?>
                                                <span class="badge bg-secondary-subtle text-dark border d-block"><?= htmlspecialchars($cp['nama_fase']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            <div><?= htmlspecialchars($cp['nama_mapel']) ?></div>
                                            <div class="mt-1 d-flex align-items-center">
                                                <?php if (!empty($cp['nama_guru'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 rounded-pill px-2.5 py-0.5" style="font-size:0.73rem; font-weight:500;">
                                                        <i class="bi bi-person-badge-fill"></i> Guru: <?= htmlspecialchars($cp['nama_guru']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border d-inline-flex align-items-center gap-1 rounded-pill px-2.5 py-0.5" style="font-size:0.72rem; font-weight:normal;">
                                                        <i class="bi bi-shield-check text-muted"></i> Tim Kurikulum / Admin
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold font-monospace text-primary"><?= htmlspecialchars($cp['kode_cp']) ?></div>
                                            <?php if (!empty($cp['elemen'])): ?>
                                                <span class="badge bg-light text-secondary border mt-1"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($cp['elemen']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><div class="small text-dark" style="max-height: 120px; overflow-y: auto;"><?= nl2br(htmlspecialchars($cp['deskripsi'])) ?></div></td>
                                        <td style="min-width: 340px; padding: 12px 14px !important;">
                                            <?php if (empty($childTps)): ?>
                                                <div class="alert alert-light border py-2 px-2.5 mb-2 text-muted small"><i class="bi bi-exclamation-circle me-1"></i> Belum ada TP</div>
                                            <?php else: ?>
                                                <div class="tp-list-box mb-2" style="max-height: 220px; overflow-y: auto;">
                                                    <?php foreach ($childTps as $tp): ?>
                                                        <div class="p-2.5 mb-2 rounded-3 bg-white border shadow-xs d-flex justify-content-between align-items-start gap-2" style="border-left: 3px solid #0d6efd !important;">
                                                            <div class="small flex-grow-1">
                                                                <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1">
                                                                    <strong class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded"><?= htmlspecialchars($tp['kode_tp']) ?></strong>
                                                                    <?php if (!empty($tp['materi_pokok'])): ?>
                                                                        <span class="badge bg-light text-dark border px-2 py-0.5 rounded" style="font-size:0.72rem;"><?= htmlspecialchars($tp['materi_pokok']) ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($tp['nama_guru'])): ?>
                                                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5 rounded" style="font-size:0.68rem;" title="Penyusun TP">
                                                                            <i class="bi bi-person me-0.5"></i><?= htmlspecialchars($tp['nama_guru']) ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="tp-deskripsi-wrapper text-dark" style="line-height: 1.6; font-size: 0.88rem; color: #0f172a !important;">
                                                                    <?= formatTpDescriptionHtml($tp['deskripsi']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex gap-1 flex-shrink-0 pt-0.5">
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
                                                                <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus Tujuan Pembelajaran (TP) ini?');">
                                                                    <?= Security::csrfField() ?>
                                                                    <input type="hidden" name="action" value="delete_tp">
                                                                    <input type="hidden" name="redirect_tab" value="cptp">
                                                                    <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                                                                    <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                                                                    <input type="hidden" name="filter_fase_id" value="<?= $filterCpFaseId ?? '' ?>">
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
                                                data-cp-kode="<?= htmlspecialchars($cp['kode_cp']) ?>"
                                                data-cp-mapel="<?= htmlspecialchars($cp['nama_mapel']) ?>"
                                                data-cp-elemen="<?= htmlspecialchars($cp['elemen'] ?? '') ?>"
                                                data-cp-deskripsi="<?= htmlspecialchars($cp['deskripsi']) ?>">
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
                                                <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" class="d-inline" onsubmit="return confirm('Hapus CP ini beserta seluruh TP turunannya?');">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="delete_cp">
                                                    <input type="hidden" name="redirect_tab" value="cptp">
                                                    <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                                                    <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                                                    <input type="hidden" name="filter_fase_id" value="<?= $filterCpFaseId ?? '' ?>">
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

        <!-- =================================================================== -->
        <!-- TAB 6: KOMPONEN & BOBOT PENILAIAN DINAMIS -->
        <!-- =================================================================== -->
        <div class="tab-pane fade <?= $activeTab === 'komponen' ? 'show active' : '' ?>" id="tabKomponen">
            <div class="card-custom p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 border-bottom pb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Konfigurasi Bobot Penilaian per Kurikulum</h5>
                        <p class="text-muted small mb-0">Rumus pengolahan nilai tidak di-hardcode di file PHP. Anda dapat menentukan komponen (Tugas, Kuis, STS, SAS, Praktik, Projek) dan bobot persentasenya per kurikulum.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted text-nowrap">Pilih Kurikulum:</label>
                        <select class="form-select form-select-sm fw-bold" onchange="location.href='<?= BASE_URL ?>index.php?url=admin/kurikulum&tab=komponen&kurikulum_id=' + this.value">
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>" <?= $selectedKurId == $kur['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kur['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST" id="formBobot">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="save_komponen">
                    <input type="hidden" name="redirect_tab" value="komponen">
                    <input type="hidden" name="kurikulum_id" value="<?= $selectedKurId ?>">

                    <!-- Quick Preset Buttons -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 bg-light p-2.5 rounded-3 border">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="small fw-bold text-muted"><i class="bi bi-magic text-primary me-1"></i>Muat Cepat Template Bobot:</span>
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" onclick="loadBobotPreset('standar')">
                                <i class="bi bi-check-circle me-1"></i>Standar (20/20/30/30)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 fw-semibold" onclick="loadBobotPreset('merdeka')">
                                <i class="bi bi-stars me-1"></i>Kurikulum Merdeka (40/30/30)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 fw-semibold" onclick="loadBobotPreset('vokasi')">
                                <i class="bi bi-tools me-1"></i>SMK Vokasi / Praktik (20/20/40/20)
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 fw-bold" onclick="addKomponenRow()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-hover align-middle border" id="tableKomponen">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:200px;">Kode Komponen</th>
                                    <th>Nama Komponen Penilaian</th>
                                    <th style="width:160px;">Bobot Persentase (%)</th>
                                    <th>Keterangan Tambahan</th>
                                    <th style="width:60px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($komponenList as $kIndex => $kp): ?>
                                    <tr>
                                        <td>
                                            <input type="text" name="kode_komponen[]" class="form-control form-control-sm font-monospace" value="<?= htmlspecialchars($kp['kode_komponen']) ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="nama_komponen[]" class="form-control form-control-sm" value="<?= htmlspecialchars($kp['nama_komponen']) ?>" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="bobot_persen[]" class="form-control form-control-sm bobot-input text-end fw-bold" step="0.5" min="0" max="100" value="<?= (float)$kp['bobot_persen'] ?>" required oninput="recalcTotalBobot()">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="deskripsi[]" class="form-control form-control-sm" value="<?= htmlspecialchars($kp['deskripsi'] ?? '') ?>" placeholder="Opsional">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotalBobot();"><i class="bi bi-x-lg"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">TOTAL AKUMULASI BOBOT:</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="totalBobotDisplay" class="form-control form-control-sm text-end fw-bold bg-white" readonly value="100%">
                                        </div>
                                    </td>
                                    <td colspan="2">
                                        <span id="badgeBobotStatus" class="badge bg-success py-1.5 px-3">Total Tepat 100%</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 fw-bold" onclick="addKomponenRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris Komponen
                        </button>
                        <button type="submit" id="btnSubmitBobot" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Simpan Konfigurasi Bobot
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
</main>

<!-- =========================================================================== -->
<!-- MODALS -->
<!-- =========================================================================== -->

<!-- Modal Add Kurikulum -->
<div class="modal fade" id="modalAddKurikulum" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_kurikulum">
                <input type="hidden" name="redirect_tab" value="kurikulum">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Tambah Kurikulum Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Kurikulum (contoh: K13, KMDK, K2029)</label>
                        <input type="text" name="kode" class="form-control font-monospace" placeholder="KMDK" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kurikulum Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Kurikulum Merdeka SMK" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tahun Mulai</label>
                            <input type="number" name="tahun_mulai" class="form-control" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tahun Selesai (Opsional)</label>
                            <input type="number" name="tahun_selesai" class="form-control" placeholder="Kosongkan jika aktif">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Kurikulum</label>
                        <select name="status" class="form-select">
                            <option value="aktif" selected>Aktif</option>
                            <option value="non-aktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Pedoman kurikulum sekolah..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Daftarkan Kurikulum</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Fase -->
<div class="modal fade" id="modalAddFase" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_fase">
                <input type="hidden" name="redirect_tab" value="fase">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Tambah Fase / Tingkat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum Induk</label>
                        <select name="kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Fase (misal: E, F, Tingkat 1)</label>
                        <input type="text" name="kode" class="form-control font-monospace" placeholder="E" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap Fase</label>
                        <input type="text" name="nama" class="form-control" placeholder="Fase E (Kelas X)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Tingkat Kelas (misal: X atau XI,XII)</label>
                        <input type="text" name="tingkat_kelas" class="form-control" placeholder="X">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Fase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Assign Rombel Kurikulum -->
<div class="modal fade" id="modalAssignRombel" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="assign_rombel">
                <input type="hidden" name="redirect_tab" value="rombel">
                
                <!-- Modal Header -->
                <div class="modal-header px-4 pt-4 pb-3 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            <i class="bi bi-building-add fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0.5 text-dark">Pasang Kurikulum ke Rombel Kelas</h5>
                            <p class="text-muted small mb-0">Tentukan periode, kurikulum, dan pilih rombel kelas secara fleksibel.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    
                    <!-- Section 1: Periode & Kurikulum Utama -->
                    <div class="bg-light bg-opacity-60 p-3 p-md-3.5 rounded-4 border mb-4">
                        <div class="row g-3 g-md-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1.5">
                                    <i class="bi bi-calendar3 text-primary me-1.5"></i>Tahun Ajaran <span class="text-danger">*</span>
                                </label>
                                <select name="tahun_ajaran_id" class="form-select rounded-3 py-2 px-3 shadow-xs" required>
                                    <?php foreach ($taList as $ta): ?>
                                        <option value="<?= $ta['id'] ?>" <?= !empty($ta['is_active']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ta['tahun_ajaran'] ?? $ta['tahun']) ?> (Semester <?= htmlspecialchars($ta['semester']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1.5">
                                    <i class="bi bi-mortarboard text-primary me-1.5"></i>Pilih Kurikulum Yang Berlaku <span class="text-danger">*</span>
                                </label>
                                <select name="kurikulum_id" id="assign_rombel_kurikulum_id" class="form-select rounded-3 py-2 px-3 shadow-xs" required>
                                    <?php foreach ($kurikulumList as $kur): ?>
                                        <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Multi-selection Checkboxes Rombel Kelas -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-2">
                            <label class="form-label fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-ui-checks-grid text-primary fs-5"></i>
                                <span>Pilih Rombel Kelas <span class="text-danger">*</span></span>
                            </label>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-3 py-1.5 rounded-pill" id="rombelCountBadge">
                                0 kelas dipilih
                            </span>
                        </div>
                        <p class="text-muted small mb-3">
                            Centang checkbox pada rombel yang ingin dipasangkan kurikulum (bisa memilih lebih dari 1 kelas sekaligus):
                        </p>

                        <!-- Controls: Search & Selection Toolbar -->
                        <div class="p-3 bg-light rounded-4 border mb-3 shadow-xs">
                            <div class="row g-2.5 align-items-center">
                                <div class="col-12 col-sm-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3 px-2.5"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" id="searchRombelAssign" class="form-control border-start-0 rounded-end-3 py-1.5" placeholder="Cari nama kelas atau jurusan...">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 text-sm-end">
                                    <div class="btn-group btn-group-sm shadow-xs">
                                        <button type="button" class="btn btn-outline-primary px-3 py-1.5 fw-semibold" id="btnSelectAllRombel">
                                            <i class="bi bi-check-all me-1"></i>Pilih Semua
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary px-3 py-1.5 fw-semibold" id="btnUnselectAllRombel">
                                            <i class="bi bi-x me-1"></i>Batal Semua
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Action & Filter Buttons -->
                            <div class="d-flex align-items-center gap-2 flex-wrap pt-2.5 mt-2.5 border-top" style="font-size: 0.8rem;">
                                <span class="text-muted me-1 fw-bold d-flex align-items-center gap-1">
                                    <i class="bi bi-lightning-charge-fill text-warning"></i>Pilih Cepat:
                                </span>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5 btn-select-tingkat fw-semibold" data-tingkat="X">+ Semua Kelas X</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5 btn-select-tingkat fw-semibold" data-tingkat="XI">+ Semua Kelas XI</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5 btn-select-tingkat fw-semibold" data-tingkat="XII">+ Semua Kelas XII</button>
                                
                                <span class="text-muted mx-1 opacity-50">|</span>
                                
                                <span class="text-muted me-1 fw-bold">Filter:</span>
                                <button type="button" class="btn btn-xs btn-info rounded-pill py-1 px-2.5 btn-filter-tingkat text-white fw-semibold" data-tingkat="all">Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill py-1 px-2.5 btn-filter-tingkat fw-semibold" data-tingkat="X">Tingkat X</button>
                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill py-1 px-2.5 btn-filter-tingkat fw-semibold" data-tingkat="XI">Tingkat XI</button>
                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill py-1 px-2.5 btn-filter-tingkat fw-semibold" data-tingkat="XII">Tingkat XII</button>
                            </div>
                        </div>

                        <!-- Checkbox Cards Container -->
                        <div class="p-3 border rounded-4 bg-white rombel-scroll-box shadow-xs" style="max-height: 270px; overflow-y: auto;">
                            <?php if (empty($kelasList)): ?>
                                <div class="text-muted text-center py-4 small">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                    Tidak ada data kelas yang tersedia.
                                </div>
                            <?php else: ?>
                                <div class="row g-2.5" id="rombelCheckboxList">
                                    <?php foreach ($kelasList as $k): ?>
                                        <?php 
                                        $tingkatUpper = strtoupper(trim($k['tingkat']));
                                        $normalizedTingkat = in_array($tingkatUpper, ['10', 'X']) ? 'X' : (in_array($tingkatUpper, ['11', 'XI']) ? 'XI' : (in_array($tingkatUpper, ['12', 'XII']) ? 'XII' : $tingkatUpper));
                                        ?>
                                        <div class="col-12 col-sm-6 rombel-check-item" 
                                             data-tingkat="<?= htmlspecialchars($normalizedTingkat) ?>" 
                                             data-nama="<?= htmlspecialchars(strtolower($k['nama_kelas'] . ' ' . ($k['nama_jurusan'] ?? '') . ' ' . $k['tingkat'])) ?>">
                                            <div class="form-check p-3 rounded-3 bg-light-subtle h-100 d-flex align-items-center gap-3 rombel-card cursor-pointer">
                                                <input class="form-check-input rombel-checkbox flex-shrink-0 mt-0" 
                                                       type="checkbox" 
                                                       name="rombel_ids[]" 
                                                       value="<?= $k['id'] ?>" 
                                                       id="rombel_chk_<?= $k['id'] ?>" 
                                                       data-tingkat="<?= htmlspecialchars($normalizedTingkat) ?>" 
                                                       data-kelas="<?= htmlspecialchars($k['nama_kelas']) ?>">
                                                <label class="form-check-label flex-grow-1 cursor-pointer mb-0" for="rombel_chk_<?= $k['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-0.5">
                                                        <span class="fw-bold text-dark text-truncate" style="max-width: 175px; font-size: 0.92rem;">
                                                            <?= htmlspecialchars($k['nama_kelas']) ?>
                                                        </span>
                                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-0.5 rounded-2" style="font-size: 0.72rem;">
                                                            Tingkat <?= htmlspecialchars($k['tingkat']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-muted text-truncate" style="font-size: 0.78rem;">
                                                        <?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div id="rombelValidationError" class="text-danger small mt-2 p-2 px-3 rounded-3 bg-danger-subtle border border-danger-subtle fw-semibold d-none">
                            <i class="bi bi-exclamation-triangle-fill me-1.5"></i>Silakan centang minimal 1 rombel kelas terlebih dahulu sebelum menerapkan.
                        </div>
                    </div>

                    <!-- Section 3: Pengaturan Fase & Status -->
                    <div class="bg-light bg-opacity-40 p-3 p-md-3.5 rounded-4 border mb-2">
                        <div class="row g-3 g-md-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1.5">
                                    <i class="bi bi-layers text-primary me-1.5"></i>Pilih Fase / Jenjang (Opsional)
                                </label>
                                <select name="fase_id" id="assign_rombel_fase_id" class="form-select rounded-3 py-2 px-3 shadow-xs">
                                    <option value="" data-kurikulum-id="">-- Auto (Menyesuaikan Masing-Masing Kelas) --</option>
                                    <?php foreach ($allFaseList as $f): ?>
                                        <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>" data-kode="<?= htmlspecialchars($f['kode'] ?? '') ?>" data-tingkat="<?= htmlspecialchars($f['tingkat_kelas'] ?? '') ?>">
                                            <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-1.5" style="font-size: 0.76rem; line-height: 1.4;">
                                    <i class="bi bi-info-circle me-1 text-primary"></i>Mode <strong>Auto</strong> otomatis menentukan Fase E (Kelas X) atau Fase F (Kelas XI/XII) untuk tiap rombel.
                                </small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1.5">
                                    <i class="bi bi-toggle-on text-primary me-1.5"></i>Status Hubungan
                                </label>
                                <select name="status" class="form-select rounded-3 py-2 px-3 shadow-xs">
                                    <option value="aktif" selected>Aktif (Berjalan Saat Ini)</option>
                                    <option value="selesai">Selesai / Riwayat Lalu</option>
                                </select>
                                <small class="text-muted d-block mt-1.5" style="font-size: 0.76rem; line-height: 1.4;">
                                    <i class="bi bi-shield-check me-1 text-success"></i>Hanya boleh ada 1 kurikulum berstatus <strong>Aktif</strong> per rombel pada tahun ajaran yang sama.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer px-4 py-3 border-top bg-light bg-opacity-60 rounded-bottom-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small d-none d-md-flex align-items-center gap-1.5">
                        <i class="bi bi-lightning-charge text-warning"></i>
                        <span>Perubahan otomatis disinkronkan ke e-rapor kelas terpilih.</span>
                    </div>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnSubmitAssignRombel">
                            <i class="bi bi-check2-circle fs-6"></i>
                            <span>Terapkan Kurikulum</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Struktur Mapel -->
<div class="modal fade" id="modalAddStrukturMapel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="add_mapel">
                <input type="hidden" name="redirect_tab" value="struktur">
                <input type="hidden" name="kurikulum_id" value="<?= $selectedKurId ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Kaitkan Mata Pelajaran ke Kurikulum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" required>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fase Target</label>
                        <select name="fase_id" class="form-select">
                            <option value="">-- Semua Fase --</option>
                            <?php foreach ($faseKurikulumList as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jurusan / Konsentrasi Keahlian (Opsional)</label>
                        <select name="jurusan_id" class="form-select">
                            <option value="">-- Berlaku Semua Jurusan / Umum --</option>
                            <?php foreach ($jurusanList as $jur): ?>
                                <option value="<?= $jur['id'] ?>"><?= htmlspecialchars($jur['nama_jurusan']) ?> (<?= htmlspecialchars($jur['kode_jurusan'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih jika mapel ini dikhususkan untuk jurusan/kejuruan tertentu.</small>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kelompok Mapel</label>
                            <select name="kelompok_mapel" class="form-select">
                                <option value="Kejuruan">Kejuruan</option>
                                <option value="Umum">Umum</option>
                                <option value="Pilihan">Pilihan</option>
                                <option value="Muatan Lokal">Muatan Lokal</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tingkat Kelas</label>
                            <select name="tingkat" class="form-select">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Alokasi JP/Minggu</label>
                            <input type="number" name="alokasi_jp" class="form-control" value="2" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">KKM Standar</label>
                            <input type="number" name="kkm" class="form-control" value="75" min="0" max="100" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Kaitkan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add CP -->
<!-- Modal Add CP (Spacious Large Modal) -->
<div class="modal fade" id="modalAddCP" tabindex="-1" aria-labelledby="modalAddCPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_cp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterCpFaseId ?? '' ?>">

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
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Pilih Kurikulum <span class="text-danger">*</span>
                            </label>
                            <select name="kurikulum_id" id="add_cp_kurikulum_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>" <?= ($filterCpKurId == $kur['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Kurikulum yang dihubungkan dengan CP ini.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-book text-primary me-1"></i>Pilih Mata Pelajaran <span class="text-danger">*</span>
                            </label>
                            <select name="mapel_id" id="add_cp_mapel_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= ($filterCpMapelId == $mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Mata pelajaran yang memiliki capaian kompetensi ini.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-layers text-primary me-1"></i>Pilih Fase / Jenjang Kelas
                            </label>
                            <select name="fase_id" id="add_cp_fase_id" class="form-select rounded-3 py-2">
                                <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>" <?= ($filterCpFaseId == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Contoh: Fase E (Kelas X), Fase F (Kelas XI - XII).</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-tags text-primary me-1"></i>Elemen / Ranah Pembelajaran
                            </label>
                            <input type="text" name="elemen" id="add_cp_elemen" class="form-control rounded-3 py-2" placeholder="Algoritma / Desain / Literasi">
                            <div class="form-text small text-muted">Domain ranah materi kompetensi pembelajaran.</div>
                        </div>

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
                                <div class="form-text small text-muted mt-1">Dibuat otomatis berdasarkan kurikulum & mapel terpilih.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-primary me-1"></i>Deskripsi Capaian Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="add_cp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Tuliskan rumusan capaian pembelajaran secara lengkap... Contoh: Peserta didik mampu memahami dan menerapkan proses bisnis pada bidang keahlian dengan benar."></textarea>
                            <div class="form-text small text-muted mt-1">Gunakan redaksi kompetensi yang jelas dan terukur.</div>
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
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_cp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterCpFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_cp_id">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-primary text-white shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditCPLabel">Perbarui Capaian Pembelajaran (CP)</h5>
                            <small class="text-muted">Perbarui data atau rumusan kompetensi Capaian Pembelajaran.</small>
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
                                <i class="bi bi-book text-primary me-1"></i>Pilih Mata Pelajaran <span class="text-danger">*</span>
                            </label>
                            <select name="mapel_id" id="edit_cp_mapel_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-layers text-primary me-1"></i>Pilih Fase / Jenjang Kelas
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
                            <input type="text" name="elemen" id="edit_cp_elemen" class="form-control rounded-3 py-2">
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

<!-- Modal Add TP (Spacious Large Modal) -->
<div class="modal fade" id="modalAddTP" tabindex="-1" aria-labelledby="modalAddTPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_tp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterCpFaseId ?? '' ?>">

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
                                        <option value="<?= $c['id'] ?>"
                                                data-kode="<?= htmlspecialchars($c['kode_cp']) ?>"
                                                data-mapel="<?= htmlspecialchars($c['nama_mapel'] ?? '') ?>"
                                                data-elemen="<?= htmlspecialchars($c['elemen'] ?? '') ?>"
                                                data-deskripsi="<?= htmlspecialchars($c['deskripsi']) ?>">
                                            [<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel'] ?? '') ?> — <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 75, '...')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Complete Parent CP Reference Card in Admin Modal -->
                    <div class="card border border-success border-opacity-25 rounded-3 mb-3.5 bg-light overflow-hidden shadow-xs">
                        <div class="card-header bg-success bg-opacity-10 py-2.5 px-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-success text-white font-monospace px-2.5 py-1.5 shadow-xs" id="admin_preview_cp_kode">CP-...</span>
                                <span class="fw-bold text-dark fs-6" id="admin_preview_cp_mapel">Mata Pelajaran</span>
                            </div>
                            <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill small" id="admin_preview_cp_elemen" style="display: none;">Elemen</span>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <div class="small fw-bold text-success text-uppercase mb-1.5 d-flex align-items-center gap-1.5" style="font-size:0.75rem; letter-spacing:0.5px;">
                                <i class="bi bi-journal-text fs-6"></i>
                                <span>Rumusan Capaian Pembelajaran (CP) Acuan:</span>
                            </div>
                            <div class="p-3 rounded-3 text-dark border border-success-subtle" id="admin_preview_cp_deskripsi" style="line-height: 1.7; font-size: 0.92rem; background-color: #f0fdf4; white-space: pre-wrap; word-break: break-word;">
                                Memuat rumusan CP acuan...
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
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

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-bookmark text-success me-1"></i>Materi Pokok / Pokok Bahasan
                            </label>
                            <input type="text" name="materi_pokok" id="add_tp_materi" class="form-control rounded-3 py-2" placeholder="Topik Utama / Pokok Bahasan">
                            <div class="form-text small text-muted">Materi pokok yang diujikan dalam TP ini.</div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-2">
                                <label class="form-label small fw-bold text-secondary mb-0 d-flex align-items-center gap-1">
                                    <i class="bi bi-card-text text-success me-1"></i>Deskripsi Tujuan Pembelajaran (TP) <span class="text-danger">*</span>
                                </label>
                                <!-- Quick List Formatting Toolbar -->
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-muted small me-1" style="font-size:0.72rem;">Format List:</span>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Format List TP">
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'number')" title="Daftar Bernomor (1., 2., 3.)">
                                            <i class="bi bi-list-ol"></i> 1. 2. 3.
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'letter')" title="Daftar Berhuruf (a., b., c.)">
                                            <i class="bi bi-fonts"></i> a. b. c.
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'bullet')" title="Daftar Simbol Bullet (•)">
                                            <i class="bi bi-list-ul"></i> • Bullet
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'dash')" title="Daftar Simbol Strip (-)">
                                            <i class="bi bi-dash"></i> - Strip
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'check')" title="Daftar Simbol Centang (✓)">
                                            <i class="bi bi-check2-square"></i> ✓ Cek
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'arrow')" title="Daftar Simbol Panah (→)">
                                            <i class="bi bi-arrow-right"></i> → Panah
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <textarea name="deskripsi" id="add_tp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Tuliskan tujuan pembelajaran yang spesifik dan terukur (dapat berupa list 1., 2. / a., b. / •, -)..."></textarea>
                            <div class="form-text small text-muted mt-1"><i class="bi bi-lightbulb text-warning me-1"></i>Tekan <strong>Enter</strong> pada baris list untuk otomatis melanjutkan nomor/huruf/simbol list berikutnya.</div>
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
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_tp">
                <input type="hidden" name="redirect_tab" value="cptp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterCpKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterCpMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterCpFaseId ?? '' ?>">
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
                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-2">
                                <label class="form-label small fw-bold text-secondary mb-0 d-flex align-items-center gap-1">
                                    <i class="bi bi-card-text text-success me-1"></i>Deskripsi Tujuan Pembelajaran <span class="text-danger">*</span>
                                </label>
                                <!-- Quick List Formatting Toolbar -->
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-muted small me-1" style="font-size:0.72rem;">Format List:</span>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Format List TP">
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'number')" title="Daftar Bernomor (1., 2., 3.)">
                                            <i class="bi bi-list-ol"></i> 1. 2. 3.
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'letter')" title="Daftar Berhuruf (a., b., c.)">
                                            <i class="bi bi-fonts"></i> a. b. c.
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'bullet')" title="Daftar Simbol Bullet (•)">
                                            <i class="bi bi-list-ul"></i> • Bullet
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'dash')" title="Daftar Simbol Strip (-)">
                                            <i class="bi bi-dash"></i> - Strip
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'check')" title="Daftar Simbol Centang (✓)">
                                            <i class="bi bi-check2-square"></i> ✓ Cek
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'arrow')" title="Daftar Simbol Panah (→)">
                                            <i class="bi bi-arrow-right"></i> → Panah
                                        </button>
                                    </div>
                                </div>
                            </div>
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

<!-- =========================================================================== -->
<!-- REUSABLE MODALS (OUTSIDE TBODY) -->
<!-- =========================================================================== -->

<!-- Modal Edit Kurikulum (Reusable) -->
<div class="modal fade" id="modalEditKurikulum" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_kurikulum">
                <input type="hidden" name="redirect_tab" value="kurikulum">
                <input type="hidden" name="id" id="edit_kurikulum_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Master Kurikulum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Kurikulum</label>
                        <input type="text" name="kode" id="edit_kurikulum_kode" class="form-control font-monospace" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kurikulum</label>
                        <input type="text" name="nama" id="edit_kurikulum_nama" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tahun Mulai</label>
                            <input type="number" name="tahun_mulai" id="edit_kurikulum_mulai" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tahun Selesai (Opsional)</label>
                            <input type="number" name="tahun_selesai" id="edit_kurikulum_selesai" class="form-control" placeholder="Kosongkan jika aktif">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Kurikulum</label>
                        <select name="status" id="edit_kurikulum_status" class="form-select">
                            <option value="aktif">Aktif (Dapat Dipakai KBM)</option>
                            <option value="non-aktif">Non-Aktif</option>
                            <option value="arsip">Arsip (Hanya Untuk Histori Nilai)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_kurikulum_deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Fase (Reusable) -->
<div class="modal fade" id="modalEditFase" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_fase">
                <input type="hidden" name="redirect_tab" value="fase">
                <input type="hidden" name="id" id="edit_fase_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Fase / Tingkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum Induk</label>
                        <select name="kurikulum_id" id="edit_fase_kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Fase</label>
                        <input type="text" name="kode" id="edit_fase_kode" class="form-control font-monospace" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Fase</label>
                        <input type="text" name="nama" id="edit_fase_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Tingkat Kelas (misal: X atau XI,XII)</label>
                        <input type="text" name="tingkat_kelas" id="edit_fase_tingkat" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="keterangan" id="edit_fase_keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Rombel Kurikulum (Reusable) -->
<div class="modal fade" id="modalEditRombel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_rombel">
                <input type="hidden" name="redirect_tab" value="rombel">
                <input type="hidden" name="id" id="edit_rombel_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Kurikulum Rombel <span id="edit_rombel_kelas_text" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kurikulum</label>
                        <select name="kurikulum_id" id="edit_rombel_kurikulum_id" class="form-select" required>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= $kur['id'] ?>">
                                    <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Fase</label>
                        <select name="fase_id" id="edit_rombel_fase_id" class="form-select">
                            <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                            <?php foreach ($allFaseList as $f): ?>
                                <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>">
                                    <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Hubungan</label>
                        <select name="status" id="edit_rombel_status" class="form-select">
                            <option value="aktif">Aktif (Berjalan Saat Ini)</option>
                            <option value="selesai">Selesai (Arsip Periode Lalu)</option>
                            <option value="non-aktif">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Struktur Mapel (Reusable) -->
<div class="modal fade" id="modalEditStrukturMapel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= BASE_URL ?>index.php?url=admin/kurikulum" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_mapel">
                <input type="hidden" name="redirect_tab" value="struktur">
                <input type="hidden" name="kurikulum_id" value="<?= $selectedKurId ?>">
                <input type="hidden" name="id" id="edit_struktur_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Konfigurasi Mapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Mata Pelajaran</label>
                        <div class="p-2.5 bg-light rounded-3 border fw-bold text-dark fs-6" id="edit_struktur_mapel_text">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fase Target</label>
                        <select name="fase_id" id="edit_struktur_fase_id" class="form-select">
                            <option value="">-- Semua Fase --</option>
                            <?php foreach ($faseKurikulumList as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jurusan / Konsentrasi Keahlian (Opsional)</label>
                        <select name="jurusan_id" id="edit_struktur_jurusan_id" class="form-select">
                            <option value="">-- Berlaku Semua Jurusan / Umum --</option>
                            <?php foreach ($jurusanList as $jur): ?>
                                <option value="<?= $jur['id'] ?>"><?= htmlspecialchars($jur['nama_jurusan']) ?> (<?= htmlspecialchars($jur['kode_jurusan'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kelompok Mapel</label>
                            <select name="kelompok_mapel" id="edit_struktur_kelompok" class="form-select">
                                <option value="Kejuruan">Kejuruan</option>
                                <option value="Umum">Umum</option>
                                <option value="Pilihan">Pilihan</option>
                                <option value="Muatan Lokal">Muatan Lokal</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tingkat Kelas</label>
                            <select name="tingkat" id="edit_struktur_tingkat" class="form-select">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Alokasi JP/Minggu</label>
                            <input type="number" name="alokasi_jp" id="edit_struktur_jp" class="form-control" min="1" max="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">KKM Standar</label>
                            <input type="number" name="kkm" id="edit_struktur_kkm" class="form-control" min="0" max="100" step="0.5" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function recalcTotalBobot() {
    const inputs = document.querySelectorAll('.bobot-input');
    let total = 0;
    inputs.forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });

    const display = document.getElementById('totalBobotDisplay');
    const badge = document.getElementById('badgeBobotStatus');
    const submitBtn = document.getElementById('btnSubmitBobot');

    if (display) {
        display.value = total.toFixed(1) + '%';
    }

    if (badge && submitBtn) {
        if (Math.abs(total - 100.0) < 0.1) {
            badge.className = 'badge bg-success py-1.5 px-3';
            badge.textContent = '✓ Total Tepat 100%';
            if (display) {
                display.classList.remove('text-danger');
                display.classList.add('text-success');
            }
            submitBtn.disabled = false;
        } else {
            badge.className = 'badge bg-danger py-1.5 px-3';
            badge.textContent = '⚠️ Wajib 100% (Selisih: ' + (100 - total).toFixed(1) + '%)';
            if (display) {
                display.classList.remove('text-success');
                display.classList.add('text-danger');
            }
            submitBtn.disabled = true;
        }
    }
}

function addKomponenRow(kode = '', nama = '', bobot = 10, ket = '') {
    const tbody = document.querySelector('#tableKomponen tbody');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="kode_komponen[]" class="form-control form-control-sm font-monospace" placeholder="kode" value="${escapeHtml(kode)}" required></td>
        <td><input type="text" name="nama_komponen[]" class="form-control form-control-sm" placeholder="Nama Komponen" value="${escapeHtml(nama)}" required></td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="bobot_persen[]" class="form-control form-control-sm bobot-input text-end fw-bold" step="0.5" min="0" max="100" value="${bobot}" required oninput="recalcTotalBobot()">
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td><input type="text" name="deskripsi[]" class="form-control form-control-sm" placeholder="Opsional" value="${escapeHtml(ket)}"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotalBobot();"><i class="bi bi-x-lg"></i></button></td>
    `;
    tbody.appendChild(tr);
    recalcTotalBobot();
}

function loadBobotPreset(type) {
    const tbody = document.querySelector('#tableKomponen tbody');
    if (!tbody) return;

    if (!confirm('Ganti seluruh baris komponen penilaian dengan template ini?')) {
        return;
    }

    tbody.innerHTML = '';
    if (type === 'standar') {
        addKomponenRow('tugas', 'Tugas Mandiri / Harian', 20, 'Penugasan dan portofolio materi KBM.');
        addKomponenRow('quiz', 'Kuis / Formatif Harian', 20, 'Evaluasi pemahaman tujuan pembelajaran.');
        addKomponenRow('uts', 'Sumatif Tengah Semester (STS)', 30, 'Ujian tengah semester.');
        addKomponenRow('uas', 'Sumatif Akhir Semester (SAS)', 30, 'Ujian akhir semester.');
    } else if (type === 'merdeka') {
        addKomponenRow('formatif', 'Asesmen Formatif (TP)', 40, 'Penilaian ketercapaian Tujuan Pembelajaran harian.');
        addKomponenRow('sumatif_lm', 'Sumatif Lingkup Materi', 30, 'Evaluasi per satu atau lebih Capaian Pembelajaran.');
        addKomponenRow('sumatif_akhir', 'Sumatif Akhir Semester (SAS)', 30, 'Evaluasi menyeluruh kompetensi semester.');
    } else if (type === 'vokasi') {
        addKomponenRow('teori', 'Tes Teori Kejuruan / Kuis', 20, 'Evaluasi konsep kejuruan dasar.');
        addKomponenRow('tugas', 'Tugas / Laporan Praktikum', 20, 'Laporan lembar kerja siswa (jobsheet).');
        addKomponenRow('praktik', 'Uji Kompetensi / Projek Vokasi', 40, 'Praktik bengkel / pembuatan produk nyata.');
        addKomponenRow('uas', 'Sumatif Akhir Semester (SAS)', 20, 'Evaluasi akhir komprehensif.');
    }
    recalcTotalBobot();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Dynamically filter Fase dropdown options based on selected Kurikulum
function filterFaseDropdown(kurikulumSelectId, faseSelectId) {
    const kurSel = document.getElementById(kurikulumSelectId);
    const faseSel = document.getElementById(faseSelectId);
    if (!kurSel || !faseSel) return;

    const selectedKurId = kurSel.value;
    const options = faseSel.querySelectorAll('option');

    let firstVisibleMatch = null;
    let currentlySelectedStillValid = false;

    options.forEach(opt => {
        const optKurId = opt.getAttribute('data-kurikulum-id');
        if (!optKurId || optKurId === '' || optKurId === selectedKurId) {
            opt.style.display = '';
            opt.disabled = false;
            if (opt.selected) currentlySelectedStillValid = true;
            if (!firstVisibleMatch && opt.value !== '') firstVisibleMatch = opt.value;
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

// Quick List Toolbar Formatter
function insertTpListFormat(textareaId, type) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    let prefix = '1. ';
    if (type === 'letter') prefix = 'a. ';
    else if (type === 'bullet') prefix = '• ';
    else if (type === 'dash') prefix = '- ';
    else if (type === 'check') prefix = '✓ ';
    else if (type === 'arrow') prefix = '→ ';

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const val = textarea.value;

    if (start === end) {
        const before = val.substring(0, start);
        const after = val.substring(end);
        const needsNewline = before.length > 0 && !before.endsWith('\n');
        const insertText = (needsNewline ? '\n' : '') + prefix;
        textarea.value = before + insertText + after;
        textarea.selectionStart = textarea.selectionEnd = start + insertText.length;
    } else {
        const selectedText = val.substring(start, end);
        const lines = selectedText.split('\n');
        let counter = 1;
        let letterCode = 97; // 'a'
        const formatted = lines.map(line => {
            if (line.trim() === '') return line;
            if (type === 'number') return (counter++) + '. ' + line.replace(/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪])\s*/, '');
            if (type === 'letter') return String.fromCharCode(letterCode++) + '. ' + line.replace(/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪])\s*/, '');
            return prefix + line.replace(/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪])\s*/, '');
        }).join('\n');

        textarea.value = val.substring(0, start) + formatted + val.substring(end);
        textarea.selectionStart = start;
        textarea.selectionEnd = start + formatted.length;
    }
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

// Smart Enter List Continuation
function setupSmartListTextarea(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            const cursor = this.selectionStart;
            const text = this.value;
            const lastLineBreak = text.lastIndexOf('\n', cursor - 1);
            const lineStart = lastLineBreak === -1 ? 0 : lastLineBreak + 1;
            const currentLine = text.substring(lineStart, cursor);

            // Match Number: e.g. "1. "
            const matchNum = currentLine.match(/^(\d+)[\.\)]\s*(.*)$/);
            if (matchNum) {
                e.preventDefault();
                const num = parseInt(matchNum[1], 10);
                const content = matchNum[2];
                if (content.trim() === '') {
                    this.value = text.substring(0, lineStart) + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = lineStart;
                } else {
                    const nextItem = '\n' + (num + 1) + '. ';
                    this.value = text.substring(0, cursor) + nextItem + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = cursor + nextItem.length;
                }
                this.dispatchEvent(new Event('input'));
                return;
            }

            // Match Letter: e.g. "a. " or "A. "
            const matchLetter = currentLine.match(/^([a-zA-Z])[\.\)]\s*(.*)$/);
            if (matchLetter) {
                e.preventDefault();
                const charCode = matchLetter[1].charCodeAt(0);
                const content = matchLetter[2];
                if (content.trim() === '') {
                    this.value = text.substring(0, lineStart) + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = lineStart;
                } else {
                    const nextItem = '\n' + String.fromCharCode(charCode + 1) + '. ';
                    this.value = text.substring(0, cursor) + nextItem + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = cursor + nextItem.length;
                }
                this.dispatchEvent(new Event('input'));
                return;
            }

            // Match Bullet / Symbol: e.g. "• ", "- ", "* ", "✓ "
            const matchSymbol = currentLine.match(/^([•\-\*✓▪▫►▶→➔➢+~–—])\ *(.*)$/);
            if (matchSymbol) {
                e.preventDefault();
                const sym = matchSymbol[1];
                const content = matchSymbol[2];
                if (content.trim() === '') {
                    this.value = text.substring(0, lineStart) + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = lineStart;
                } else {
                    const nextItem = '\n' + sym + ' ';
                    this.value = text.substring(0, cursor) + nextItem + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = cursor + nextItem.length;
                }
                this.dispatchEvent(new Event('input'));
                return;
            }
        }
    });
}

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
    updateParentCpPreviewAdmin();
}

function updateParentCpPreviewAdmin() {
    const cpSelect = document.getElementById('add_tp_cp_id');
    const previewKode = document.getElementById('admin_preview_cp_kode');
    const previewMapel = document.getElementById('admin_preview_cp_mapel');
    const previewElemen = document.getElementById('admin_preview_cp_elemen');
    const previewDeskripsi = document.getElementById('admin_preview_cp_deskripsi');

    if (!cpSelect) return;

    const opt = cpSelect.options[cpSelect.selectedIndex];
    if (opt) {
        const kode = opt.getAttribute('data-kode') || (opt.text.match(/^\[(.*?)\]/) ? opt.text.match(/^\[(.*?)\]/)[1] : 'CP Induk');
        const mapel = opt.getAttribute('data-mapel') || '';
        const elem = opt.getAttribute('data-elemen') || '';
        const desk = opt.getAttribute('data-deskripsi') || opt.text || '';

        if (previewKode) previewKode.textContent = kode;
        if (previewMapel) previewMapel.textContent = mapel ? mapel : 'Mata Pelajaran';
        if (previewElemen) {
            if (elem) {
                previewElemen.textContent = 'Elemen: ' + elem;
                previewElemen.style.display = 'inline-block';
            } else {
                previewElemen.style.display = 'none';
            }
        }
        if (previewDeskripsi) previewDeskripsi.textContent = desk ? desk : 'Deskripsi CP belum tersedia.';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    recalcTotalBobot();

    // Initialize Smart List Handlers
    setupSmartListTextarea('add_tp_deskripsi');
    setupSmartListTextarea('edit_tp_deskripsi');

    // DataTables dynamic adjustment when tab is switched
    if (window.jQuery) {
        jQuery('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (jQuery.fn.dataTable) {
                const api = jQuery.fn.dataTable.tables({ visible: true, api: true });
                api.columns.adjust();
                if (api.responsive && typeof api.responsive.recalc === 'function') {
                    try { api.responsive.recalc(); } catch (err) {}
                }
            }

            // Sync active tab to URL parameter without reloading
            const target = jQuery(e.target).attr('data-bs-target');
            const tabMap = {
                '#tabKurikulum': 'kurikulum',
                '#tabFase': 'fase',
                '#tabRombel': 'rombel',
                '#tabStruktur': 'struktur',
                '#tabCPTP': 'cptp',
                '#tabKomponen': 'komponen'
            };
            if (tabMap[target]) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabMap[target]);
                window.history.replaceState({}, '', url.toString());
            }
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
        modalAddCpEl.addEventListener('show.bs.modal', () => {
            const elemenInp = document.getElementById('add_cp_elemen');
            if (elemenInp) elemenInp.value = '';

            const deskInp = document.getElementById('add_cp_deskripsi');
            if (deskInp) deskInp.value = '';

            updateAutoCpCode();
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
    function resetAdminAddTpForm(targetCpId = null, btnEl = null) {
        const select = document.getElementById('add_tp_cp_id');
        if (select && targetCpId) {
            select.value = targetCpId;
        }

        const materiInp = document.getElementById('add_tp_materi');
        if (materiInp) materiInp.value = '';

        const deskInp = document.getElementById('add_tp_deskripsi');
        if (deskInp) deskInp.value = '';

        updateAutoTpCode();

        if (btnEl && (btnEl.getAttribute('data-cp-deskripsi') || btnEl.dataset.cpDeskripsi)) {
            const previewKode = document.getElementById('admin_preview_cp_kode');
            const previewMapel = document.getElementById('admin_preview_cp_mapel');
            const previewElemen = document.getElementById('admin_preview_cp_elemen');
            const previewDeskripsi = document.getElementById('admin_preview_cp_deskripsi');

            const kode = btnEl.getAttribute('data-cp-kode') || btnEl.dataset.cpKode;
            const mapel = btnEl.getAttribute('data-cp-mapel') || btnEl.dataset.cpMapel;
            const elem = btnEl.getAttribute('data-cp-elemen') || btnEl.dataset.cpElemen;
            const desk = btnEl.getAttribute('data-cp-deskripsi') || btnEl.dataset.cpDeskripsi;

            if (previewKode && kode) previewKode.textContent = kode;
            if (previewMapel && mapel) previewMapel.textContent = mapel;
            if (previewElemen) {
                if (elem) {
                    previewElemen.textContent = 'Elemen: ' + elem;
                    previewElemen.style.display = 'inline-block';
                } else {
                    previewElemen.style.display = 'none';
                }
            }
            if (previewDeskripsi && desk) previewDeskripsi.textContent = desk;
        } else {
            updateParentCpPreviewAdmin();
        }
    }

    if (modalAddTpEl) {
        modalAddTpEl.addEventListener('show.bs.modal', function(e) {
            const button = e.relatedTarget;
            const targetCpId = button ? button.getAttribute('data-cp-id') : null;
            resetAdminAddTpForm(targetCpId, button);
        });
    }

    // Direct "+ Tambah TP" from CP row in admin
    document.querySelectorAll('.btn-add-tp-for-cp').forEach(btn => {
        btn.addEventListener('click', function() {
            const cpId = this.dataset.cpId;
            resetAdminAddTpForm(cpId, this);
        });
    });

    const kurEditCp = document.getElementById('edit_cp_kurikulum_id');
    if (kurEditCp) {
        kurEditCp.addEventListener('change', () => filterFaseDropdown('edit_cp_kurikulum_id', 'edit_cp_fase_id'));
    }

    const kurAssignRombel = document.getElementById('assign_rombel_kurikulum_id');
    const faseAssignSelect = document.getElementById('assign_rombel_fase_id');

    function autoMatchFaseForRombel(tingkat, selectElement) {
        if (!tingkat || !selectElement) return;
        const t = tingkat.toUpperCase().trim();
        const targetKode = (t === 'XI' || t === 'XII' || t === '11' || t === '12' || t.includes('XI') || t.includes('XII')) ? 'F' : 'E';
        for (let i = 0; i < selectElement.options.length; i++) {
            const opt = selectElement.options[i];
            const kode = (opt.getAttribute('data-kode') || '').toUpperCase();
            const txt = opt.textContent.toUpperCase();
            if (kode === targetKode || txt.includes('FASE ' + targetKode)) {
                selectElement.selectedIndex = i;
                break;
            }
        }
    }

    if (kurAssignRombel) {
        kurAssignRombel.addEventListener('change', () => {
            filterFaseDropdown('assign_rombel_kurikulum_id', 'assign_rombel_fase_id');
        });
        filterFaseDropdown('assign_rombel_kurikulum_id', 'assign_rombel_fase_id');
    }

    // Modal Assign Rombel Checkbox Logic
    const rombelCheckboxes = document.querySelectorAll('#modalAssignRombel .rombel-checkbox');
    const rombelCountBadge = document.getElementById('rombelCountBadge');
    const searchRombelInput = document.getElementById('searchRombelAssign');
    const btnSelectAllRombel = document.getElementById('btnSelectAllRombel');
    const btnUnselectAllRombel = document.getElementById('btnUnselectAllRombel');
    const validationErrorEl = document.getElementById('rombelValidationError');
    const formAssignRombel = document.querySelector('#modalAssignRombel form');

    function updateRombelCheckedCount() {
        const checkedBoxes = document.querySelectorAll('#modalAssignRombel .rombel-checkbox:checked');
        const count = checkedBoxes.length;
        if (rombelCountBadge) {
            rombelCountBadge.textContent = count + ' kelas dipilih';
            if (count > 0) {
                rombelCountBadge.className = 'badge bg-primary text-white fw-bold px-2.5 py-1';
            } else {
                rombelCountBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2.5 py-1';
            }
        }
        if (validationErrorEl && count > 0) {
            validationErrorEl.classList.add('d-none');
        }

        // Highlight selected cards
        document.querySelectorAll('#modalAssignRombel .rombel-card').forEach(card => {
            const chk = card.querySelector('.rombel-checkbox');
            if (chk && chk.checked) {
                card.classList.add('selected-card');
            } else {
                card.classList.remove('selected-card');
            }
        });

        // Auto-match Fase if all selected checkboxes share same tingkat and fase dropdown is currently empty or on Auto
        if (count > 0 && faseAssignSelect && (!faseAssignSelect.value || faseAssignSelect.value === '')) {
            let uniqueTingkat = new Set();
            checkedBoxes.forEach(cb => {
                const tk = (cb.getAttribute('data-tingkat') || '').trim();
                if (tk) uniqueTingkat.add(tk);
            });
            if (uniqueTingkat.size === 1) {
                const singleTingkat = Array.from(uniqueTingkat)[0];
                autoMatchFaseForRombel(singleTingkat, faseAssignSelect);
            }
        }
    }

    rombelCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateRombelCheckedCount);
    });

    // Make clicking the card toggle the checkbox
    document.querySelectorAll('#modalAssignRombel .rombel-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') {
                const chk = this.querySelector('.rombel-checkbox');
                if (chk) {
                    chk.checked = !chk.checked;
                    updateRombelCheckedCount();
                }
            }
        });
    });

    if (btnSelectAllRombel) {
        btnSelectAllRombel.addEventListener('click', () => {
            const visibleItems = document.querySelectorAll('#modalAssignRombel .rombel-check-item:not(.d-none) .rombel-checkbox');
            visibleItems.forEach(cb => { cb.checked = true; });
            updateRombelCheckedCount();
        });
    }

    if (btnUnselectAllRombel) {
        btnUnselectAllRombel.addEventListener('click', () => {
            rombelCheckboxes.forEach(cb => { cb.checked = false; });
            updateRombelCheckedCount();
        });
    }

    // Quick select by Tingkat buttons (+ Semua Kelas X, etc.)
    document.querySelectorAll('#modalAssignRombel .btn-select-tingkat').forEach(btn => {
        btn.addEventListener('click', function() {
            const t = this.getAttribute('data-tingkat');
            document.querySelectorAll('#modalAssignRombel .rombel-check-item').forEach(item => {
                const itemTingkat = item.getAttribute('data-tingkat');
                if (itemTingkat === t) {
                    const cb = item.querySelector('.rombel-checkbox');
                    if (cb) cb.checked = true;
                }
            });
            updateRombelCheckedCount();
        });
    });

    // Quick filter display by Tingkat pills
    document.querySelectorAll('#modalAssignRombel .btn-filter-tingkat').forEach(btn => {
        btn.addEventListener('click', function() {
            const t = this.getAttribute('data-tingkat');
            document.querySelectorAll('#modalAssignRombel .btn-filter-tingkat').forEach(b => {
                b.classList.remove('btn-info', 'text-white');
                b.classList.add('btn-outline-info');
            });
            this.classList.remove('btn-outline-info');
            this.classList.add('btn-info', 'text-white');
            
            document.querySelectorAll('#modalAssignRombel .rombel-check-item').forEach(item => {
                const itemTingkat = item.getAttribute('data-tingkat');
                if (t === 'all' || itemTingkat === t) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    });

    // Live search filter
    if (searchRombelInput) {
        searchRombelInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('#modalAssignRombel .rombel-check-item').forEach(item => {
                const nama = item.getAttribute('data-nama') || '';
                if (!query || nama.includes(query)) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    }

    // Form submit validation
    if (formAssignRombel) {
        formAssignRombel.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('#modalAssignRombel .rombel-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                if (validationErrorEl) {
                    validationErrorEl.classList.remove('d-none');
                }
                const container = document.getElementById('rombelCheckboxList');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                return false;
            }
        });
    }

    const kurEditRombel = document.getElementById('edit_rombel_kurikulum_id');
    if (kurEditRombel) {
        kurEditRombel.addEventListener('change', () => filterFaseDropdown('edit_rombel_kurikulum_id', 'edit_rombel_fase_id'));
    }

    // Modal Edit Kurikulum
    document.querySelectorAll('.btn-edit-kurikulum').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_kurikulum_id').value = this.dataset.id || '';
            document.getElementById('edit_kurikulum_kode').value = this.dataset.kode || '';
            document.getElementById('edit_kurikulum_nama').value = this.dataset.nama || '';
            document.getElementById('edit_kurikulum_mulai').value = this.dataset.mulai || '';
            document.getElementById('edit_kurikulum_selesai').value = this.dataset.selesai || '';
            document.getElementById('edit_kurikulum_status').value = this.dataset.status || 'aktif';
            document.getElementById('edit_kurikulum_deskripsi').value = this.dataset.deskripsi || '';
        });
    });

    // Modal Edit Fase
    document.querySelectorAll('.btn-edit-fase').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_fase_id').value = this.dataset.id || '';
            document.getElementById('edit_fase_kode').value = this.dataset.kode || '';
            document.getElementById('edit_fase_nama').value = this.dataset.nama || '';
            document.getElementById('edit_fase_tingkat').value = this.dataset.tingkat || '';
            document.getElementById('edit_fase_keterangan').value = this.dataset.keterangan || '';
            
            const selKur = document.getElementById('edit_fase_kurikulum_id');
            if (selKur && this.dataset.kurikulumId) selKur.value = this.dataset.kurikulumId;
        });
    });

    // Modal Edit Rombel
    document.querySelectorAll('.btn-edit-rombel').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_rombel_id').value = this.dataset.id || '';
            const kelasLabel = document.getElementById('edit_rombel_kelas_text');
            const kelasNama = this.dataset.kelas || '';
            if (kelasLabel) kelasLabel.textContent = kelasNama;

            const selKur = document.getElementById('edit_rombel_kurikulum_id');
            if (selKur && this.dataset.kurikulumId) {
                selKur.value = this.dataset.kurikulumId;
                filterFaseDropdown('edit_rombel_kurikulum_id', 'edit_rombel_fase_id');
            }

            const selFase = document.getElementById('edit_rombel_fase_id');
            if (selFase) {
                selFase.value = this.dataset.faseId || '';
                if (!selFase.value && kelasNama) {
                    autoMatchFaseForRombel(kelasNama, selFase);
                }
            }

            const selStatus = document.getElementById('edit_rombel_status');
            if (selStatus) selStatus.value = this.dataset.status || 'aktif';
        });
    });

    // Modal Edit Struktur Mapel
    document.querySelectorAll('.btn-edit-struktur').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_struktur_id').value = this.dataset.id || '';
            
            const txt = document.getElementById('edit_struktur_mapel_text');
            if (txt) txt.textContent = (this.dataset.mapelKode ? `[${this.dataset.mapelKode}] ` : '') + (this.dataset.mapelNama || '');

            const selFase = document.getElementById('edit_struktur_fase_id');
            if (selFase) selFase.value = this.dataset.faseId || '';

            const selJur = document.getElementById('edit_struktur_jurusan_id');
            if (selJur) selJur.value = this.dataset.jurusanId || '';

            const selKel = document.getElementById('edit_struktur_kelompok');
            if (selKel && this.dataset.kelompok) selKel.value = this.dataset.kelompok;

            const selTingkat = document.getElementById('edit_struktur_tingkat');
            if (selTingkat && this.dataset.tingkat) selTingkat.value = this.dataset.tingkat;

            const inpJp = document.getElementById('edit_struktur_jp');
            if (inpJp) inpJp.value = this.dataset.jp || 2;

            const inpKkm = document.getElementById('edit_struktur_kkm');
            if (inpKkm) inpKkm.value = this.dataset.kkm || 75;
        });
    });

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

    // Populate Edit CP Modal — use show.bs.modal for reliable DataTables support
    const modalEditCpElAdmin = document.getElementById('modalEditCP');
    if (modalEditCpElAdmin) {
        modalEditCpElAdmin.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            if (!btn || !btn.classList.contains('btn-edit-cp')) return;

            const id = btn.getAttribute('data-id') || '';
            const kode = btn.getAttribute('data-kode') || '';
            const elemen = btn.getAttribute('data-elemen') || '';
            const deskripsi = btn.getAttribute('data-deskripsi') || '';
            const kurikulumId = btn.getAttribute('data-kurikulum-id') || '';
            const mapelId = btn.getAttribute('data-mapel-id') || '';
            const faseId = btn.getAttribute('data-fase-id') || '';

            const idInp = document.getElementById('edit_cp_id');
            const kodeInp = document.getElementById('edit_cp_kode');
            const elemenInp = document.getElementById('edit_cp_elemen');
            const deskInp = document.getElementById('edit_cp_deskripsi');

            if (idInp) idInp.value = id;
            if (kodeInp) kodeInp.value = kode;
            if (elemenInp) elemenInp.value = elemen;
            if (deskInp) deskInp.value = deskripsi;

            const selKur = document.getElementById('edit_cp_kurikulum_id');
            if (selKur && kurikulumId) {
                selKur.value = kurikulumId;
                filterFaseDropdown('edit_cp_kurikulum_id', 'edit_cp_fase_id');
            }

            const selMapel = document.getElementById('edit_cp_mapel_id');
            if (selMapel && mapelId) selMapel.value = mapelId;

            const selFase = document.getElementById('edit_cp_fase_id');
            if (selFase) selFase.value = faseId;
        });
    }

    // Populate Edit TP Modal — use show.bs.modal for reliable DataTables support
    const modalEditTpElAdmin = document.getElementById('modalEditTP');
    if (modalEditTpElAdmin) {
        modalEditTpElAdmin.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            if (!btn || !btn.classList.contains('btn-edit-tp')) return;

            const id = btn.getAttribute('data-id') || '';
            const kode = btn.getAttribute('data-kode') || '';
            const materi = btn.getAttribute('data-materi') || '';
            const deskripsi = btn.getAttribute('data-deskripsi') || '';
            const cpId = btn.getAttribute('data-cp-id') || '';

            const idInp = document.getElementById('edit_tp_id');
            const kodeInp = document.getElementById('edit_tp_kode');
            const materiInp = document.getElementById('edit_tp_materi');
            const deskInp = document.getElementById('edit_tp_deskripsi');
            const selCp = document.getElementById('edit_tp_cp_id');

            if (idInp) idInp.value = id;
            if (kodeInp) kodeInp.value = kode;
            if (materiInp) materiInp.value = materi;
            if (deskInp) deskInp.value = deskripsi;

            if (selCp && cpId) {
                selCp.value = cpId;
            }
        });
    }

    // Form Bobot Penilaian submit validation
    const formBobot = document.getElementById('formBobot');
    if (formBobot) {
        formBobot.addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('.bobot-input');
            let total = 0;
            inputs.forEach(inp => { total += parseFloat(inp.value) || 0; });
            if (Math.abs(total - 100.0) >= 0.1) {
                e.preventDefault();
                alert('Total akumulasi bobot harus tepat 100%! Saat ini total: ' + total.toFixed(1) + '%. Silakan sesuaikan bobot komponen.');
                return false;
            }
        });
    }

    // Auto adjust datatables on window resize
    window.addEventListener('resize', () => {
        if (window.jQuery && jQuery.fn.dataTable) {
            jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
