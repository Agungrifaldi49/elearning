<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php 
$isAdminMonitoring = (strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
?>

<style>
/* ── Modal Lebar & Modern (Diperluas untuk Tampilan Profesional) ── */
@media (min-width: 1200px) {
    .modal-materi-xl {
        max-width: 1260px !important;
        width: 95% !important;
    }
}
.modal-materi-xl .modal-content {
    border-radius: 20px !important;
    overflow: hidden;
}

/* ── Rich Text Studio & List Formatting ── */
.rich-editor-box {
    min-height: 240px;
    max-height: 360px;
    overflow-y: auto;
    outline: none;
    line-height: 1.65;
    font-size: 0.93rem;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 14px 16px;
    background: #ffffff;
    transition: all .2s ease;
}
.rich-editor-box:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
}
.rich-editor-box:empty:before {
    content: attr(placeholder);
    color: #94a3b8;
    pointer-events: none;
    display: block;
}

/* ── Dukungan Format List Lengkap (1, A, a, I, i, Bullet) ── */
.rich-editor-box ol, .rendered-materi-content ol { padding-left: 28px; margin-bottom: 10px; }
.rich-editor-box ul, .rendered-materi-content ul { padding-left: 28px; margin-bottom: 10px; list-style-type: disc !important; }
.rich-editor-box li, .rendered-materi-content li { margin-bottom: 4px; }

/* Selector list type */
.rich-editor-box ol[type="A"], .rich-editor-box ol.list-alpha-upper,
.rendered-materi-content ol[type="A"], .rendered-materi-content ol.list-alpha-upper {
    list-style-type: upper-alpha !important;
}
.rich-editor-box ol[type="a"], .rich-editor-box ol.list-alpha-lower,
.rendered-materi-content ol[type="a"], .rendered-materi-content ol.list-alpha-lower {
    list-style-type: lower-alpha !important;
}
.rich-editor-box ol[type="1"], .rich-editor-box ol.list-decimal,
.rendered-materi-content ol[type="1"], .rendered-materi-content ol.list-decimal {
    list-style-type: decimal !important;
}
.rich-editor-box ol[type="I"], .rich-editor-box ol.list-roman-upper,
.rendered-materi-content ol[type="I"], .rendered-materi-content ol.list-roman-upper {
    list-style-type: upper-roman !important;
}
.rich-editor-box ol[type="i"], .rich-editor-box ol.list-roman-lower,
.rendered-materi-content ol[type="i"], .rendered-materi-content ol.list-roman-lower {
    list-style-type: lower-roman !important;
}

/* ── Callout Box Catatan ── */
.rich-callout-box, .rendered-materi-content .rich-callout-box {
    background-color: #f0fdf4;
    border-left: 4px solid #16a34a;
    padding: 10px 14px;
    border-radius: 6px;
    margin: 10px 0;
    color: #166534;
    font-size: 0.88rem;
}
.rich-editor-box h5, .rendered-materi-content h5 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-top: 14px;
    margin-bottom: 6px;
}
</style>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <?php if ($isAdminMonitoring): ?>
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 shadow-sm d-flex align-items-center gap-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 5px solid #0284c7 !important;">
                <div class="bg-primary text-white p-2.5 rounded-3 shadow-xs">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-eye-fill me-1 text-primary"></i>Mode Monitoring Administrator (Pengawasan Guru)</h6>
                    <small class="text-secondary fw-medium">Secara hak akses, Administrator hanya berwenang **mengawasi / memantau (Monitoring Only)** data modul & materi pembelajaran. Admin dapat mengeklik **Preview** dan **Unduh** untuk memeriksa berkas, namun tidak dapat membuat, mengedit, atau menghapus materi milik Guru.</small>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-book-fill text-primary me-2"></i>Kelola Materi & Modul Pembelajaran</h4>
                <p class="text-muted small mb-0">Unggah, pratinjau (preview), edit, dan kelola file PDF, Word, PPT, Video MP4, atau Link Youtube.</p>
            </div>
            <?php if (!$isAdminMonitoring): ?>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMateri">
                    <i class="bi bi-cloud-upload me-1"></i> Unggah Materi Baru
                </button>
            <?php endif; ?>
        </div>

        <div class="card card-custom p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($materiList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Judul Materi</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas Target</th>
                            <th>Jenis File</th>
                            <th>Diupload Pada</th>
                            <th class="text-center" style="min-width: 260px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materiList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada materi pembelajaran yang diunggah.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $kelasMap = !empty($kelasList) ? array_column($kelasList, 'nama_kelas', 'id') : [];
                            ?>
                            <?php foreach ($materiList as $i => $m): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-bold text-dark">
                                        <?= htmlspecialchars($m['judul']) ?>
                                        <?php if (!empty($m['judul_bab'])): ?>
                                            <div class="small text-muted fw-normal mt-1">
                                                <i class="bi bi-bookmark-fill text-primary me-1"></i><?= htmlspecialchars($m['judul_bab']) ?>
                                                <span class="badge bg-light text-dark border ms-1">Urutan: <?= (int)($m['urutan'] ?? 1) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($m['nama_mapel']) ?></td>
                                    <td>
                                        <?php
                                        $targetIds = !empty($m['kelas_ids']) ? array_map('intval', explode(',', $m['kelas_ids'])) : [(int)$m['kelas_id']];
                                        foreach ($targetIds as $tid):
                                            $kName = $kelasMap[$tid] ?? ($tid == $m['kelas_id'] ? $m['nama_kelas'] : '');
                                            if (!empty($kName)):
                                        ?>
                                            <span class="badge bg-info text-dark me-1 mb-1"><?= htmlspecialchars($kName) ?></span>
                                        <?php 
                                            endif;
                                        endforeach;
                                        ?>
                                    </td>
                                    <td><span class="badge bg-primary text-uppercase"><?= htmlspecialchars($m['jenis_file']) ?></span></td>
                                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></small></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                            <!-- Button Preview (Lihat Tanpa Unduh) -->
                                            <button type="button" class="btn btn-sm btn-info text-white px-2" style="font-size:0.75rem;" onclick="openPreviewModal(<?= htmlspecialchars(json_encode([
                                                'id' => $m['id'],
                                                'judul' => $m['judul'],
                                                'judul_bab' => $m['judul_bab'] ?? '',
                                                'nama_mapel' => $m['nama_mapel'],
                                                'nama_kelas' => $m['nama_kelas'] ?? '',
                                                'jenis_file' => $m['jenis_file'],
                                                'file_path' => !empty($m['file_path']) ? BASE_URL . 'assets/uploads/materi/' . $m['file_path'] : '',
                                                'youtube_url' => $m['youtube_url'] ?? '',
                                                'deskripsi' => $m['deskripsi'] ?? ''
                                            ])) ?>)" title="Preview / Lihat Langsung">
                                                <i class="bi bi-eye me-1"></i> Preview
                                            </button>

                                            <?php if (!$isAdminMonitoring): ?>
                                                <!-- Button Edit -->
                                                <button class="btn btn-sm btn-warning text-dark px-2" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#modalEditMateri<?= $m['id'] ?>" title="Edit Materi">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </button>
                                            <?php endif; ?>

                                            <!-- Button Unduh / Link Direct -->
                                            <?php if (!empty($m['file_path'])): ?>
                                                <a href="<?= BASE_URL ?>assets/uploads/materi/<?= htmlspecialchars($m['file_path']) ?>" class="btn btn-sm btn-outline-success px-2" style="font-size:0.75rem;" download title="Unduh File">
                                                    <i class="bi bi-download me-1"></i> Unduh
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!$isAdminMonitoring): ?>
                                                <!-- Button Hapus -->
                                                <form action="<?= BASE_URL ?>index.php?url=guru/materi" method="POST" onsubmit="return confirm('Hapus materi pembelajaran ini?');" class="d-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2" style="font-size:0.75rem;" title="Hapus Materi">
                                                        <i class="bi bi-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

<!-- ALL MODALS PLACED OUTSIDE TABLE -->
<?php
// Struktur data bab per mapel untuk autocomplete & rekomendasi dinamis
// mapelBabData[mapel_id] = [ ['nama' => '...', 'count' => N, 'max_urutan' => N, 'next_urutan' => N+1], ... ]
$mapelBabData = [];
foreach ($materiList ?? [] as $mItem) {
    $mid = (int)$mItem['mapel_id'];
    $bName = trim($mItem['judul_bab'] ?? '');
    if (!empty($bName)) {
        if (!isset($mapelBabData[$mid][$bName])) {
            $mapelBabData[$mid][$bName] = [
                'nama'        => $bName,
                'count'       => 0,
                'max_urutan'  => 0,
                'next_urutan' => 1
            ];
        }
        $mapelBabData[$mid][$bName]['count']++;
        $curUrutan = (int)($mItem['urutan'] ?? 1);
        if ($curUrutan > $mapelBabData[$mid][$bName]['max_urutan']) {
            $mapelBabData[$mid][$bName]['max_urutan']  = $curUrutan;
            $mapelBabData[$mid][$bName]['next_urutan'] = $curUrutan + 1;
        }
    }
}
?>

<!-- Modal Add Materi -->
<div class="modal fade" id="modalAddMateri" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-materi-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-light py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white p-2.5 rounded-3 shadow-xs">
                        <i class="bi bi-cloud-upload-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold modal-title mb-0 text-dark">Unggah Materi Pembelajaran Baru</h5>
                        <small class="text-muted">Kelola berkas digital, penataan bab e-book, dan tuliskan rangkuman materi singkat dengan format penomoran list.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/materi" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 bg-light-subtle">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="row g-4">
                        <!-- KOLOM KIRI (LEBAR 5 / 12): INFORMASI BERKAS, MAPEL, KELAS & BAB -->
                        <div class="col-lg-5">
                            <div class="d-flex flex-column gap-3">
                                
                                <!-- Card 1: Identitas Berkas & Mata Pelajaran -->
                                <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-text text-primary"></i>1. Identitas Berkas & Target
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Judul Materi <span class="text-danger">*</span></label>
                                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Modul 1 Pemrograman Web PHP" required>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-sm-6">
                                            <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                                            <select name="mapel_id" id="selectMapel_add" class="form-select" required>
                                                <?php foreach ($mapelList as $mp): ?>
                                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label small fw-bold">Jenis Konten <span class="text-danger">*</span></label>
                                            <select name="jenis_file" class="form-select" required>
                                                <option value="pdf">PDF Document</option>
                                                <option value="doc">Word / Office Doc</option>
                                                <option value="ppt">PowerPoint Presentation</option>
                                                <option value="video">Video MP4 File</option>
                                                <option value="youtube">YouTube Video Link</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Pilih Berkas (PDF/Doc/PPT/MP4)</label>
                                            <input type="file" name="file" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Atau Tautan Video YouTube</label>
                                            <input type="url" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                                        </div>
                                    </div>

                                    <!-- Kelas Target -->
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold mb-0">Kelas Target <span class="text-danger">*</span></label>
                                            <div class="form-check me-0">
                                                <input class="form-check-input" type="checkbox" id="selectAllKelasAdd">
                                                <label class="form-check-label small fw-semibold text-primary" for="selectAllKelasAdd" style="cursor:pointer;">Semua Kelas</label>
                                            </div>
                                        </div>
                                        <div class="border rounded-3 p-2 bg-light" style="max-height: 125px; overflow-y: auto;">
                                            <div class="row g-1">
                                                <?php foreach ($kelasList as $k): ?>
                                                    <div class="col-sm-6">
                                                        <div class="form-check bg-white p-1.5 px-2 rounded-2 border d-flex align-items-center gap-1 shadow-xs">
                                                            <input class="form-check-input kelas-add-checkbox ms-1" type="checkbox" name="kelas_ids[]" value="<?= $k['id'] ?>" id="kelas_add_<?= $k['id'] ?>">
                                                            <label class="form-check-label text-dark small fw-medium mb-0 w-100" style="cursor:pointer;" for="kelas_add_<?= $k['id'] ?>">
                                                                <?= htmlspecialchars($k['nama_kelas']) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 2: Penetapan Bab & Urutan E-Book (Smart Bab Selector) -->
                                <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom">
                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="bi bi-book-half text-primary"></i>2. Pengelompokan Bab E-Book
                                        </h6>
                                        <div id="badgeBabMapel_add"></div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                        <small class="text-muted fw-semibold">Pilih metode bab:</small>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-primary active btn-sm" id="btnModeExisting_add">
                                                <i class="bi bi-collection me-1"></i>Bab yang Ada
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnModeNew_add">
                                                <i class="bi bi-plus-circle me-1"></i>+ Bab Baru
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row g-2 align-items-start">
                                        <!-- Container 1: Bab yang Ada -->
                                        <div class="col-8" id="containerBabExisting_add">
                                            <select id="selectBabExisting_add" class="form-select form-select-sm">
                                            </select>
                                            <small class="text-muted d-block mt-1" style="font-size:0.72rem;">
                                                <i class="bi bi-check2-circle text-success me-1"></i>Urutan otomatis tersambung.
                                            </small>
                                        </div>

                                        <!-- Container 2: Bab Baru -->
                                        <div class="col-8 d-none" id="containerBabNew_add">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white text-primary"><i class="bi bi-pencil-square"></i></span>
                                                <input type="text" id="inputBabNew_add" class="form-control" placeholder="Contoh: Bab 2: Desain Web CSS">
                                            </div>
                                            <small class="text-muted d-block mt-1" style="font-size:0.72rem;">
                                                <i class="bi bi-magic text-primary me-1"></i>Tuliskan nama bab baru.
                                            </small>
                                        </div>

                                        <!-- Input Urutan Tampil -->
                                        <div class="col-4">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white small fw-bold">No.</span>
                                                <input type="number" name="urutan" id="inputUrutan_add" class="form-control text-center fw-bold" value="1" min="1" max="999">
                                            </div>
                                            <small class="text-muted d-block mt-1 text-center" style="font-size:0.7rem;">
                                                <span class="badge bg-secondary-subtle text-dark border">Urutan bab</span>
                                            </small>
                                        </div>
                                    </div>

                                    <input type="hidden" name="judul_bab" id="realJudulBab_add" value="">
                                </div>

                            </div>
                        </div>

                        <!-- KOLOM KANAN (LEBAR 7 / 12): MATERI SINGKAT (RICH TEXT STUDIO) & EVALUASI -->
                        <div class="col-lg-7">
                            <div class="d-flex flex-column gap-3 h-100">
                                
                                <!-- Card Materi Singkat dengan List Toolbar Lengkap -->
                                <div class="card border rounded-3 p-3 bg-white shadow-xs flex-grow-1 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                                <i class="bi bi-file-earmark-richtext-fill text-primary"></i>3. Materi Singkat & Rangkuman Pembelajaran
                                            </h6>
                                            <small class="text-muted" style="font-size:0.76rem;">Siswa dapat langsung membaca materi ini secara terstruktur dengan format list penomoran.</small>
                                        </div>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="bi bi-magic me-1"></i>Format List Lengkap (A, a, 1, I)
                                        </span>
                                    </div>

                                    <!-- Toolbar Format List & Styling Lengkap -->
                                    <div class="rich-toolbar p-2 bg-light rounded-3 border mb-2 d-flex align-items-center flex-wrap gap-1 shadow-xs">
                                        <!-- Bold, Italic, Underline -->
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('add', 'bold')" title="Tebal (Bold)"><i class="bi bi-type-bold fw-bold"></i></button>
                                            <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('add', 'italic')" title="Miring (Italic)"><i class="bi bi-type-italic"></i></button>
                                            <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('add', 'underline')" title="Garis Bawah (Underline)"><i class="bi bi-type-underline"></i></button>
                                        </div>

                                        <div class="vr mx-1 my-auto" style="height:22px;"></div>

                                        <!-- DROPDOWN FORMAT PENOMORAN / LIST (A, a, 1, I, i, Bullet) -->
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-primary dropdown-toggle d-flex align-items-center gap-1 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Format Penomoran List">
                                                <i class="bi bi-list-ol"></i>
                                                <span class="small fw-bold">Format List (A, a, 1, I)</span>
                                            </button>
                                            <ul class="dropdown-menu shadow-lg border-0 rounded-3 p-1.5" style="font-size:0.85rem; min-width: 240px; z-index: 1060;">
                                                <li class="dropdown-header small fw-bold text-uppercase text-muted py-1">Pilih Gaya Penomoran:</li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('add', 'A')">
                                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">A, B, C</span>
                                                        <span>Huruf Kapital (A, B, C...)</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('add', 'a')">
                                                        <span class="badge bg-info text-dark fw-bold px-2 py-1">a, b, c</span>
                                                        <span>Huruf Kecil (a, b, c...)</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('add', '1')">
                                                        <span class="badge bg-light text-dark border fw-bold px-2 py-1">1, 2, 3</span>
                                                        <span>Angka Biasa (1, 2, 3...)</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('add', 'I')">
                                                        <span class="badge bg-success text-white fw-bold px-2 py-1">I, II, III</span>
                                                        <span>Romawi Besar (I, II, III...)</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('add', 'i')">
                                                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">i, ii, iii</span>
                                                        <span>Romawi Kecil (i, ii, iii...)</span>
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('add', 'bullet')">
                                                        <span class="badge bg-secondary text-white fw-bold px-2 py-1">• Poin</span>
                                                        <span>Bullet List (Titik)</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="vr mx-1 my-auto" style="height:22px;"></div>

                                        <!-- Sub-Judul & Callout Box -->
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('add', 'formatBlock', '<h5>')" title="Buat Sub-Topik (H5)"><i class="bi bi-type-h5"></i> Sub-Judul</button>
                                            <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="insertCallout('add')" title="Kotak Catatan Khusus"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Catatan</button>
                                        </div>

                                        <!-- Hapus Format -->
                                        <button type="button" class="btn btn-white border btn-sm ms-auto text-danger shadow-xs" onclick="formatDoc('add', 'removeFormat')" title="Hapus Format"><i class="bi bi-eraser me-1"></i>Reset</button>
                                    </div>

                                    <!-- Area Ketik WYSIWYG yang Lega -->
                                    <div id="richEditor_add" class="rich-editor-box flex-grow-1" contenteditable="true" placeholder="Tuliskan materi pembelajaran atau poin-poin penting di sini... Anda dapat memilih Format List (A, a, 1, I) dari tombol di atas untuk membuat daftar berurutan."></div>
                                    <textarea name="deskripsi" id="realDeskripsi_add" class="d-none"></textarea>

                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                        <small class="text-muted" style="font-size:0.72rem;">
                                            <i class="bi bi-info-circle me-1 text-primary"></i>Tekan <b>Enter</b> untuk membuat poin baru berikutnya. Tekan <b>Shift + Enter</b> untuk baris baru dalam poin yang sama.
                                        </small>
                                        <small class="text-success fw-semibold" style="font-size:0.72rem;"><i class="bi bi-check-circle me-1"></i>Tersimpan Otomatis</small>
                                    </div>
                                </div>

                                <!-- Evaluasi Terkait (Kuis & Tugas) -->
                                <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                    <h6 class="fw-bold text-dark mb-2 pb-2 border-bottom d-flex align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-success"></i>4. Hubungkan Evaluasi Terkait (Opsional)
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <label class="form-label small fw-bold mb-1"><i class="bi bi-question-circle text-primary me-1"></i>Kuis Terkait</label>
                                            <div class="border rounded-3 p-2 bg-light" style="max-height: 115px; overflow-y: auto;">
                                                <?php if (empty($quizListForForm)): ?>
                                                    <small class="text-muted d-block p-1">Belum ada data kuis.</small>
                                                <?php else: ?>
                                                    <?php foreach ($quizListForForm as $qf): ?>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="checkbox" name="linked_quiz_ids[]" value="<?= $qf['id'] ?>" id="add_quiz_<?= $qf['id'] ?>">
                                                            <label class="form-check-label small text-dark" for="add_quiz_<?= $qf['id'] ?>" style="cursor:pointer;">
                                                                <?= htmlspecialchars($qf['judul']) ?>
                                                                <?php if (!empty($qf['nama_mapel'])): ?>
                                                                    <span class="text-muted">(<?= htmlspecialchars($qf['nama_mapel']) ?>)</span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label small fw-bold mb-1"><i class="bi bi-file-earmark-check text-success me-1"></i>Tugas Terkait</label>
                                            <div class="border rounded-3 p-2 bg-light" style="max-height: 115px; overflow-y: auto;">
                                                <?php if (empty($tugasListForForm)): ?>
                                                    <small class="text-muted d-block p-1">Belum ada data tugas.</small>
                                                <?php else: ?>
                                                    <?php foreach ($tugasListForForm as $tf): ?>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="checkbox" name="linked_tugas_ids[]" value="<?= $tf['id'] ?>" id="add_tugas_<?= $tf['id'] ?>">
                                                            <label class="form-check-label small text-dark" for="add_tugas_<?= $tf['id'] ?>" style="cursor:pointer;">
                                                                <?= htmlspecialchars($tf['judul']) ?>
                                                                <?php if (!empty($tf['nama_mapel'])): ?>
                                                                    <span class="text-muted">(<?= htmlspecialchars($tf['nama_mapel']) ?>)</span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size:0.72rem;">Kuis & tugas yang dicentang akan langsung terhubung dan dapat dikerjakan siswa setelah membaca materi ini.</small>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0 py-3 px-4 justify-content-between bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-xs">
                        <i class="bi bi-cloud-upload me-1"></i> Unggah Materi Pembelajaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single Shared Dynamic Modal Preview Materi (Mencegah Auto-Download Saat Halaman Dimuat) -->
<div class="modal fade" id="modalPreviewMateri" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold modal-title" id="previewModalTitle"><i class="bi bi-eye text-info me-2"></i>Pratinjau Materi</h5>
                    <small class="text-muted" id="previewModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closePreviewModal()"></button>
            </div>
            <div class="modal-body">
                <div id="previewModalDescBox" class="p-3 bg-light rounded-3 mb-3 small border d-none">
                    <strong class="d-block mb-1 text-primary"><i class="bi bi-card-text me-1"></i>Materi Singkat / Rangkuman:</strong>
                    <div id="previewModalDesc" class="rendered-materi-content text-dark"></div>
                </div>

                <div id="previewMediaContainer" class="ratio-container text-center bg-dark rounded-3 overflow-hidden p-2">
                    <!-- Konten berkas / media HANYA dimuat secara dinamis saat tombol Preview diklik -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closePreviewModal()">Tutup Pratinjau</button>
                <a href="#" id="previewModalDownloadBtn" class="btn btn-success fw-bold d-none" download>
                    <i class="bi bi-download me-1"></i> Unduh File
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modals Edit Materi -->
<?php foreach ($materiList as $m): ?>
    <?php if (!$isAdminMonitoring): ?>
    <!-- Modal Edit Materi -->
    <div class="modal fade" id="modalEditMateri<?= $m['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-materi-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 bg-light py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning text-dark p-2.5 rounded-3 shadow-xs">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold modal-title mb-0 text-dark">Edit Materi Pembelajaran</h5>
                            <small class="text-muted">Perbarui data berkas, pemindahan bab, serta edit ringkasan materi dengan format list.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=guru/materi" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4 bg-light-subtle">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">

                        <div class="row g-4">
                            <!-- KOLOM KIRI (LEBAR 5 / 12): INFORMASI BERKAS, MAPEL, KELAS & BAB -->
                            <div class="col-lg-5">
                                <div class="d-flex flex-column gap-3">

                                    <!-- Card 1: Identitas Berkas -->
                                    <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-text text-warning"></i>1. Identitas Berkas & Target
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Judul Materi <span class="text-danger">*</span></label>
                                            <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($m['judul']) ?>" required>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                                                <select name="mapel_id" id="selectMapel_edit_<?= $m['id'] ?>" class="form-select" required>
                                                    <?php foreach ($mapelList as $mp): ?>
                                                        <option value="<?= $mp['id'] ?>" <?= $m['mapel_id'] == $mp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-bold">Jenis Konten <span class="text-danger">*</span></label>
                                                <select name="jenis_file" class="form-select" required>
                                                    <option value="pdf" <?= $m['jenis_file'] === 'pdf' ? 'selected' : '' ?>>PDF Document</option>
                                                    <option value="doc" <?= $m['jenis_file'] === 'doc' ? 'selected' : '' ?>>Word / Office Doc</option>
                                                    <option value="ppt" <?= $m['jenis_file'] === 'ppt' ? 'selected' : '' ?>>PowerPoint Presentation</option>
                                                    <option value="video" <?= $m['jenis_file'] === 'video' ? 'selected' : '' ?>>Video MP4 File</option>
                                                    <option value="youtube" <?= $m['jenis_file'] === 'youtube' ? 'selected' : '' ?>>YouTube Video Link</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Ganti Berkas (Opsional)</label>
                                                <input type="file" name="file" class="form-control">
                                                <?php if (!empty($m['file_path'])): ?>
                                                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;"><i class="bi bi-paperclip me-1"></i>File saat ini: <?= htmlspecialchars($m['file_path']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Atau Tautan Video YouTube</label>
                                                <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars($m['youtube_url'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <!-- Kelas Target -->
                                        <div class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label small fw-bold mb-0">Kelas Target <span class="text-danger">*</span></label>
                                                <div class="form-check me-0">
                                                    <input class="form-check-input select-all-edit" type="checkbox" id="selectAllKelasEdit<?= $m['id'] ?>" data-target="<?= $m['id'] ?>">
                                                    <label class="form-check-label small fw-semibold text-primary" for="selectAllKelasEdit<?= $m['id'] ?>" style="cursor:pointer;">Semua Kelas</label>
                                                </div>
                                            </div>
                                            <div class="border rounded-3 p-2 bg-light" style="max-height: 125px; overflow-y: auto;">
                                                <div class="row g-1">
                                                    <?php
                                                    $editTargetIds = !empty($m['kelas_ids']) ? array_map('intval', explode(',', $m['kelas_ids'])) : [(int)$m['kelas_id']];
                                                    ?>
                                                    <?php foreach ($kelasList as $k): ?>
                                                        <div class="col-sm-6">
                                                            <div class="form-check bg-white p-1.5 px-2 rounded-2 border d-flex align-items-center gap-1 shadow-xs">
                                                                <input class="form-check-input kelas-edit-checkbox-<?= $m['id'] ?> ms-1" type="checkbox" name="kelas_ids[]" value="<?= $k['id'] ?>" id="kelas_edit_<?= $m['id'] ?>_<?= $k['id'] ?>" <?= in_array((int)$k['id'], $editTargetIds) ? 'checked' : '' ?>>
                                                                <label class="form-check-label text-dark small fw-medium mb-0 w-100" style="cursor:pointer;" for="kelas_edit_<?= $m['id'] ?>_<?= $k['id'] ?>">
                                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 2: Penetapan Bab & Urutan E-Book Edit -->
                                    <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom">
                                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                                <i class="bi bi-book-half text-warning"></i>2. Pengelompokan Bab E-Book
                                            </h6>
                                            <div id="badgeBabMapel_edit_<?= $m['id'] ?>"></div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                            <small class="text-muted fw-semibold">Pilih metode bab:</small>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-warning text-dark active btn-sm" id="btnModeExisting_edit_<?= $m['id'] ?>">
                                                    <i class="bi bi-collection me-1"></i>Pilih Bab yang Ada
                                                </button>
                                                <button type="button" class="btn btn-outline-warning text-dark btn-sm" id="btnModeNew_edit_<?= $m['id'] ?>">
                                                    <i class="bi bi-plus-circle me-1"></i>+ Bab Baru
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row g-2 align-items-start">
                                            <div class="col-8" id="containerBabExisting_edit_<?= $m['id'] ?>">
                                                <select id="selectBabExisting_edit_<?= $m['id'] ?>" class="form-select form-select-sm">
                                                </select>
                                                <small class="text-muted d-block mt-1" style="font-size:0.72rem;">
                                                    <i class="bi bi-arrow-left-right text-primary me-1"></i>Pindahkan ke bab lain.
                                                </small>
                                            </div>

                                            <div class="col-8 d-none" id="containerBabNew_edit_<?= $m['id'] ?>">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-warning"><i class="bi bi-pencil-square"></i></span>
                                                    <input type="text" id="inputBabNew_edit_<?= $m['id'] ?>" class="form-control" value="<?= htmlspecialchars($m['judul_bab'] ?? '') ?>" placeholder="Ketik nama Bab baru">
                                                </div>
                                                <small class="text-muted d-block mt-1" style="font-size:0.72rem;">
                                                    <i class="bi bi-magic text-warning me-1"></i>Tuliskan nama bab baru.
                                                </small>
                                            </div>

                                            <div class="col-4">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white small fw-bold">No.</span>
                                                    <input type="number" name="urutan" id="inputUrutan_edit_<?= $m['id'] ?>" class="form-control text-center fw-bold" value="<?= (int)($m['urutan'] ?? 1) ?>" min="1" max="999">
                                                </div>
                                                <small class="text-muted d-block mt-1 text-center" style="font-size:0.7rem;">
                                                    <span class="badge bg-secondary-subtle text-dark border">Urutan bab</span>
                                                </small>
                                            </div>
                                        </div>

                                        <input type="hidden" name="judul_bab" id="realJudulBab_edit_<?= $m['id'] ?>" value="<?= htmlspecialchars($m['judul_bab'] ?? '') ?>">
                                    </div>

                                </div>
                            </div>

                            <!-- KOLOM KANAN (LEBAR 7 / 12): MATERI SINGKAT (RICH TEXT STUDIO) & EVALUASI -->
                            <div class="col-lg-7">
                                <div class="d-flex flex-column gap-3 h-100">

                                    <!-- Card Materi Singkat dengan List Toolbar Lengkap -->
                                    <div class="card border rounded-3 p-3 bg-white shadow-xs flex-grow-1 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom">
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                                    <i class="bi bi-file-earmark-richtext-fill text-warning"></i>3. Materi Singkat & Rangkuman Pembelajaran
                                                </h6>
                                                <small class="text-muted" style="font-size:0.76rem;">Siswa dapat langsung membaca materi ini secara terstruktur dengan format list penomoran.</small>
                                            </div>
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-magic me-1"></i>Format List Lengkap (A, a, 1, I)
                                            </span>
                                        </div>

                                        <!-- Toolbar Format List & Styling Lengkap -->
                                        <div class="rich-toolbar p-2 bg-light rounded-3 border mb-2 d-flex align-items-center flex-wrap gap-1 shadow-xs">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('edit_<?= $m['id'] ?>', 'bold')" title="Tebal (Bold)"><i class="bi bi-type-bold fw-bold"></i></button>
                                                <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('edit_<?= $m['id'] ?>', 'italic')" title="Miring (Italic)"><i class="bi bi-type-italic"></i></button>
                                                <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('edit_<?= $m['id'] ?>', 'underline')" title="Garis Bawah (Underline)"><i class="bi bi-type-underline"></i></button>
                                            </div>

                                            <div class="vr mx-1 my-auto" style="height:22px;"></div>

                                            <!-- DROPDOWN FORMAT PENOMORAN / LIST (A, a, 1, I, i, Bullet) -->
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-warning text-dark dropdown-toggle d-flex align-items-center gap-1 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Format Penomoran List">
                                                    <i class="bi bi-list-ol"></i>
                                                    <span class="small fw-bold">Format List (A, a, 1, I)</span>
                                                </button>
                                                <ul class="dropdown-menu shadow-lg border-0 rounded-3 p-1.5" style="font-size:0.85rem; min-width: 240px; z-index: 1060;">
                                                    <li class="dropdown-header small fw-bold text-uppercase text-muted py-1">Pilih Gaya Penomoran:</li>
                                                    <li>
                                                        <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('edit_<?= $m['id'] ?>', 'A')">
                                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">A, B, C</span>
                                                            <span>Huruf Kapital (A, B, C...)</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('edit_<?= $m['id'] ?>', 'a')">
                                                            <span class="badge bg-info text-dark fw-bold px-2 py-1">a, b, c</span>
                                                            <span>Huruf Kecil (a, b, c...)</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('edit_<?= $m['id'] ?>', '1')">
                                                            <span class="badge bg-light text-dark border fw-bold px-2 py-1">1, 2, 3</span>
                                                            <span>Angka Biasa (1, 2, 3...)</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('edit_<?= $m['id'] ?>', 'I')">
                                                            <span class="badge bg-success text-white fw-bold px-2 py-1">I, II, III</span>
                                                            <span>Romawi Besar (I, II, III...)</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('edit_<?= $m['id'] ?>', 'i')">
                                                            <span class="badge bg-warning text-dark fw-bold px-2 py-1">i, ii, iii</span>
                                                            <span>Romawi Kecil (i, ii, iii...)</span>
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <a class="dropdown-item rounded-2 py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="applyListFormat('edit_<?= $m['id'] ?>', 'bullet')">
                                                            <span class="badge bg-secondary text-white fw-bold px-2 py-1">• Poin</span>
                                                            <span>Bullet List (Titik)</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="vr mx-1 my-auto" style="height:22px;"></div>

                                            <!-- Sub-Judul & Callout Box -->
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="formatDoc('edit_<?= $m['id'] ?>', 'formatBlock', '<h5>')" title="Buat Sub-Topik (H5)"><i class="bi bi-type-h5"></i> Sub-Judul</button>
                                                <button type="button" class="btn btn-white border btn-sm shadow-xs" onclick="insertCallout('edit_<?= $m['id'] ?>')" title="Kotak Catatan Khusus"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Catatan</button>
                                            </div>

                                            <!-- Hapus Format -->
                                            <button type="button" class="btn btn-white border btn-sm ms-auto text-danger shadow-xs" onclick="formatDoc('edit_<?= $m['id'] ?>', 'removeFormat')" title="Hapus Format"><i class="bi bi-eraser me-1"></i>Reset</button>
                                        </div>

                                        <!-- Area Ketik WYSIWYG yang Lega (Pre-filled dengan safeHtml) -->
                                        <div id="richEditor_edit_<?= $m['id'] ?>" class="rich-editor-box flex-grow-1" contenteditable="true" placeholder="Tuliskan materi pembelajaran atau poin-poin penting di sini..."><?= Security::safeHtml($m['deskripsi'] ?? '') ?></div>
                                        <textarea name="deskripsi" id="realDeskripsi_edit_<?= $m['id'] ?>" class="d-none"><?= htmlspecialchars($m['deskripsi'] ?? '') ?></textarea>

                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                            <small class="text-muted" style="font-size:0.72rem;">
                                                <i class="bi bi-info-circle me-1 text-primary"></i>Tekan <b>Enter</b> untuk membuat poin baru berikutnya. Tekan <b>Shift + Enter</b> untuk baris baru dalam poin yang sama.
                                            </small>
                                            <small class="text-success fw-semibold" style="font-size:0.72rem;"><i class="bi bi-check-circle me-1"></i>Tersimpan Otomatis</small>
                                        </div>
                                    </div>

                                    <!-- Evaluasi Terkait (Kuis & Tugas) -->
                                    <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                        <h6 class="fw-bold text-dark mb-2 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="bi bi-check2-circle text-success"></i>4. Hubungkan Evaluasi Terkait (Opsional)
                                        </h6>
                                        <div class="row g-3">
                                            <?php 
                                            $existingQuizIds = !empty($m['quiz_ids']) ? array_map('intval', explode(',', $m['quiz_ids'])) : [];
                                            $existingTugasIds = !empty($m['tugas_ids']) ? array_map('intval', explode(',', $m['tugas_ids'])) : [];
                                            ?>
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-bold mb-1"><i class="bi bi-question-circle text-primary me-1"></i>Kuis Terkait</label>
                                                <div class="border rounded-3 p-2 bg-light" style="max-height: 115px; overflow-y: auto;">
                                                    <?php if (empty($quizListForForm)): ?>
                                                        <small class="text-muted d-block p-1">Belum ada data kuis aktif.</small>
                                                    <?php else: ?>
                                                        <?php foreach ($quizListForForm as $qf): ?>
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox" name="linked_quiz_ids[]" value="<?= $qf['id'] ?>" id="edit_quiz_<?= $m['id'] ?>_<?= $qf['id'] ?>" <?= in_array((int)$qf['id'], $existingQuizIds) ? 'checked' : '' ?>>
                                                                <label class="form-check-label small text-dark" for="edit_quiz_<?= $m['id'] ?>_<?= $qf['id'] ?>" style="cursor:pointer;">
                                                                    <?= htmlspecialchars($qf['judul']) ?>
                                                                    <?php if (!empty($qf['nama_mapel'])): ?>
                                                                        <span class="text-muted">(<?= htmlspecialchars($qf['nama_mapel']) ?>)</span>
                                                                    <?php endif; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-bold mb-1"><i class="bi bi-file-earmark-check text-success me-1"></i>Tugas Terkait</label>
                                                <div class="border rounded-3 p-2 bg-light" style="max-height: 115px; overflow-y: auto;">
                                                    <?php if (empty($tugasListForForm)): ?>
                                                        <small class="text-muted d-block p-1">Belum ada data tugas.</small>
                                                    <?php else: ?>
                                                        <?php foreach ($tugasListForForm as $tf): ?>
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox" name="linked_tugas_ids[]" value="<?= $tf['id'] ?>" id="edit_tugas_<?= $m['id'] ?>_<?= $tf['id'] ?>" <?= in_array((int)$tf['id'], $existingTugasIds) ? 'checked' : '' ?>>
                                                                <label class="form-check-label small text-dark" for="edit_tugas_<?= $m['id'] ?>_<?= $tf['id'] ?>" style="cursor:pointer;">
                                                                    <?= htmlspecialchars($tf['judul']) ?>
                                                                    <?php if (!empty($tf['nama_mapel'])): ?>
                                                                        <span class="text-muted">(<?= htmlspecialchars($tf['nama_mapel']) ?>)</span>
                                                                    <?php endif; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size:0.72rem;">Kuis & tugas yang dicentang akan langsung terhubung dan dapat dikerjakan siswa setelah membaca materi ini.</small>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer border-0 py-3 px-4 justify-content-between bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold shadow-xs">
                            <i class="bi bi-pencil-square me-1"></i> Simpan Perubahan Materi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
// --- DATA BAB PER MAPEL DARI DATABASE (AUTOCOMPLETE & SMART SUGGESTION) ---
const materiBabData = <?= json_encode($mapelBabData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

// --- RICH TEXT STUDIO & LIST FORMATTING ENGINE (A, a, 1, I, i, Bullet) ---
function formatDoc(prefix, cmd, value = null) {
    const editor = document.getElementById('richEditor_' + prefix);
    if (!editor) return;
    editor.focus();
    document.execCommand(cmd, false, value);
    syncEditorContent(prefix);
}

function applyListFormat(prefix, listType) {
    const editor = document.getElementById('richEditor_' + prefix);
    if (!editor) return;
    editor.focus();

    if (listType === 'bullet') {
        document.execCommand('insertUnorderedList', false, null);
    } else {
        const selection = window.getSelection();
        let existingOl = null;
        if (selection && selection.rangeCount > 0) {
            let node = selection.getRangeAt(0).startContainer;
            while (node && node !== editor) {
                if (node.nodeName === 'OL') {
                    existingOl = node;
                    break;
                }
                node = node.parentNode;
            }
        }

        if (existingOl) {
            setOlType(existingOl, listType);
        } else {
            document.execCommand('insertOrderedList', false, null);
            const newSelection = window.getSelection();
            if (newSelection && newSelection.rangeCount > 0) {
                let n = newSelection.getRangeAt(0).startContainer;
                while (n && n !== editor) {
                    if (n.nodeName === 'OL') {
                        setOlType(n, listType);
                        break;
                    }
                    n = n.parentNode;
                }
            }
        }
    }
    syncEditorContent(prefix);
}

function setOlType(olNode, listType) {
    if (!olNode) return;
    olNode.setAttribute('type', listType);
    const mapStyle = {
        'A': 'upper-alpha',
        'a': 'lower-alpha',
        '1': 'decimal',
        'I': 'upper-roman',
        'i': 'lower-roman'
    };
    if (mapStyle[listType]) {
        olNode.style.listStyleType = mapStyle[listType];
        olNode.classList.remove('list-alpha-upper', 'list-alpha-lower', 'list-decimal', 'list-roman-upper', 'list-roman-lower');
        const mapClass = {
            'A': 'list-alpha-upper',
            'a': 'list-alpha-lower',
            '1': 'list-decimal',
            'I': 'list-roman-upper',
            'i': 'list-roman-lower'
        };
        if (mapClass[listType]) {
            olNode.classList.add(mapClass[listType]);
        }
    }
}

function insertCallout(prefix) {
    const editor = document.getElementById('richEditor_' + prefix);
    if (!editor) return;
    editor.focus();
    const calloutHtml = '<div class="rich-callout-box my-2"><strong><i class="bi bi-lightbulb-fill me-1 text-warning"></i>Catatan KBM:</strong> Tuliskan poin atau rangkuman penting di sini...</div><p><br></p>';
    document.execCommand('insertHTML', false, calloutHtml);
    syncEditorContent(prefix);
}

function syncEditorContent(prefix) {
    const editor = document.getElementById('richEditor_' + prefix);
    const textarea = document.getElementById('realDeskripsi_' + prefix);
    if (editor && textarea) {
        textarea.value = editor.innerHTML.trim();
    }
}

function initBabSelector(prefix, defaultMapelId, initialBab, initialUrutan) {
    const selectMapel       = document.getElementById('selectMapel_' + prefix);
    const containerExisting = document.getElementById('containerBabExisting_' + prefix);
    const containerNew      = document.getElementById('containerBabNew_' + prefix);
    const selectExisting    = document.getElementById('selectBabExisting_' + prefix);
    const inputNew          = document.getElementById('inputBabNew_' + prefix);
    const inputUrutan       = document.getElementById('inputUrutan_' + prefix);
    const realInput         = document.getElementById('realJudulBab_' + prefix);
    const btnExisting       = document.getElementById('btnModeExisting_' + prefix);
    const btnNew            = document.getElementById('btnModeNew_' + prefix);
    const badgeMapel        = document.getElementById('badgeBabMapel_' + prefix);

    if (!selectExisting || !inputNew || !realInput) return;

    let activeMode = 'existing';
    let currentBabVal = (initialBab || '').trim();

    function updateOptions(mapelId) {
        const rawBabObj = materiBabData[mapelId] || {};
        const babList = Object.values(rawBabObj);
        selectExisting.innerHTML = '';

        if (babList.length === 0) {
            // Belum ada bab terdaftar untuk mapel ini
            if (badgeMapel) badgeMapel.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-info-circle me-1"></i>Belum ada bab terdaftar</span>';
            if (btnExisting) {
                btnExisting.classList.add('disabled');
                btnExisting.setAttribute('disabled', 'disabled');
            }
            setMode('new', true);
            realInput.value = inputNew.value.trim();
        } else {
            // Ada bab terdaftar
            if (badgeMapel) badgeMapel.innerHTML = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-folder-check me-1"></i>${babList.length} Bab Terdaftar</span>`;
            if (btnExisting) {
                btnExisting.classList.remove('disabled');
                btnExisting.removeAttribute('disabled');
            }

            // Opsi opsional tanpa bab
            const optNone = document.createElement('option');
            optNone.value = '';
            optNone.textContent = '-- Materi Umum (Tanpa Bab) --';
            optNone.dataset.next = '999';
            selectExisting.appendChild(optNone);

            let matched = false;
            babList.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.nama;
                opt.textContent = `${b.nama} (${b.count} materi) — Urutan selanjutnya: #${b.next_urutan}`;
                opt.dataset.next = b.next_urutan;
                if (currentBabVal && b.nama.toLowerCase() === currentBabVal.toLowerCase()) {
                    opt.selected = true;
                    matched = true;
                }
                selectExisting.appendChild(opt);
            });

            if (currentBabVal && !matched) {
                // Bab materi ini adalah bab custom / baru
                setMode('new', false);
                inputNew.value = currentBabVal;
                realInput.value = currentBabVal;
            } else if (!currentBabVal && activeMode === 'new') {
                setMode('new', false);
                realInput.value = inputNew.value.trim();
            } else {
                setMode('existing', false);
                syncExisting();
            }
        }
    }

    function setMode(mode, lockExisting) {
        activeMode = mode;
        const isEdit = prefix.startsWith('edit_');
        const primaryClass = isEdit ? 'btn-warning' : 'btn-primary';
        const outlineClass = isEdit ? 'btn-outline-warning' : 'btn-outline-primary';

        if (mode === 'existing' && !lockExisting) {
            containerExisting.classList.remove('d-none');
            containerNew.classList.add('d-none');
            if (btnExisting) {
                btnExisting.classList.add('active', primaryClass);
                btnExisting.classList.remove(outlineClass);
            }
            if (btnNew) {
                btnNew.classList.remove('active', primaryClass);
                btnNew.classList.add(outlineClass);
            }
            syncExisting();
        } else {
            containerExisting.classList.add('d-none');
            containerNew.classList.remove('d-none');
            if (btnNew) {
                btnNew.classList.add('active', primaryClass);
                btnNew.classList.remove(outlineClass);
            }
            if (btnExisting) {
                btnExisting.classList.remove('active', primaryClass);
                btnExisting.classList.add(outlineClass);
                if (lockExisting) {
                    btnExisting.classList.add('disabled');
                    btnExisting.setAttribute('disabled', 'disabled');
                }
            }
            realInput.value = inputNew.value.trim();
            if (prefix === 'add' && inputUrutan) {
                inputUrutan.value = '1';
            }
        }
    }

    function syncExisting() {
        const selectedOpt = selectExisting.options[selectExisting.selectedIndex];
        if (selectedOpt) {
            realInput.value = selectedOpt.value;
            // Jika mode Add, langsung sarankan nomor urutan berikutnya
            if (prefix === 'add' && inputUrutan && selectedOpt.dataset.next) {
                inputUrutan.value = selectedOpt.dataset.next;
            }
        } else {
            realInput.value = '';
        }
    }

    if (selectMapel) {
        selectMapel.addEventListener('change', function() {
            currentBabVal = '';
            updateOptions(this.value);
        });
    }

    if (btnExisting) {
        btnExisting.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.classList.contains('disabled')) setMode('existing', false);
        });
    }

    if (btnNew) {
        btnNew.addEventListener('click', function(e) {
            e.preventDefault();
            setMode('new', false);
            inputNew.focus();
        });
    }

    selectExisting.addEventListener('change', syncExisting);

    inputNew.addEventListener('input', function() {
        realInput.value = this.value.trim();
    });

    const initialMapel = selectMapel ? selectMapel.value : defaultMapelId;
    updateOptions(initialMapel);
}

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Selector Bab Pintar pada Modal Add
    const selectMapelAdd = document.getElementById('selectMapel_add');
    if (selectMapelAdd) {
        initBabSelector('add', selectMapelAdd.value || 0, '', 1);
    }

    // Inisialisasi Selector Bab Pintar pada setiap Modal Edit
    <?php foreach ($materiList as $mItem): ?>
        initBabSelector(
            'edit_<?= $mItem['id'] ?>',
            <?= (int)$mItem['mapel_id'] ?>,
            <?= json_encode($mItem['judul_bab'] ?? '') ?>,
            <?= (int)($mItem['urutan'] ?? 1) ?>
        );
    <?php endforeach; ?>

    // --- ADD MATERI SELECT ALL & VALIDATION ---
    const selectAllCheckbox = document.getElementById('selectAllKelasAdd');
    const kelasCheckboxes = document.querySelectorAll('.kelas-add-checkbox');
    const formAddMateri = document.querySelector('#modalAddMateri form');

    if (selectAllCheckbox && kelasCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            kelasCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
        });

        kelasCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const totalChecked = document.querySelectorAll('.kelas-add-checkbox:checked').length;
                selectAllCheckbox.checked = (totalChecked === kelasCheckboxes.length);
            });
        });
    }

    if (formAddMateri) {
        formAddMateri.addEventListener('submit', function(e) {
            const checkedClasses = document.querySelectorAll('.kelas-add-checkbox:checked');
            if (checkedClasses.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 Kelas Target terlebih dahulu!');
            }
        });
    }

    // --- EDIT MATERI SELECT ALL & VALIDATION ---
    document.querySelectorAll('.select-all-edit').forEach(function(selectAllCb) {
        const targetId = selectAllCb.getAttribute('data-target');
        const editCheckboxes = document.querySelectorAll('.kelas-edit-checkbox-' + targetId);

        selectAllCb.addEventListener('change', function() {
            editCheckboxes.forEach(cb => {
                cb.checked = selectAllCb.checked;
            });
        });

        editCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const totalChecked = document.querySelectorAll('.kelas-edit-checkbox-' + targetId + ':checked').length;
                selectAllCb.checked = (totalChecked === editCheckboxes.length);
            });
        });
    });

    document.querySelectorAll('form[action*="guru/materi"]').forEach(function(form) {
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && actionInput.value === 'update') {
            form.addEventListener('submit', function(e) {
                const checkedClasses = form.querySelectorAll('input[name="kelas_ids[]"]:checked');
                if (checkedClasses.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal 1 Kelas Target terlebih dahulu!');
                }
            });
        }
    });

    // Inisialisasi Sinkronisasi Real-Time Rich Text Studio
    document.querySelectorAll('.rich-editor-box').forEach(function(editor) {
        const prefix = editor.id.replace('richEditor_', '');
        editor.addEventListener('input', function() {
            syncEditorContent(prefix);
        });
        editor.addEventListener('blur', function() {
            syncEditorContent(prefix);
        });
    });

    // Pastikan semua form materi melakukan sinkronisasi materi singkat sebelum submit
    document.querySelectorAll('form[action*="guru/materi"]').forEach(function(form) {
        form.addEventListener('submit', function() {
            const realDeskripsi = form.querySelector('textarea[id^="realDeskripsi_"]');
            if (realDeskripsi) {
                const prefix = realDeskripsi.id.replace('realDeskripsi_', '');
                syncEditorContent(prefix);
            }
        });
    });

    // Cleanup preview container when modal is dismissed
    const previewModalEl = document.getElementById('modalPreviewMateri');
    if (previewModalEl) {
        previewModalEl.addEventListener('hidden.bs.modal', function () {
            closePreviewModal();
        });
    }
});

function escapeHtml(text) {
    if (!text) return '';
    const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function openPreviewModal(item) {
    document.getElementById('previewModalTitle').innerHTML = '<i class="bi bi-eye text-info me-2"></i>Pratinjau Materi: ' + escapeHtml(item.judul);
    const babText = item.judul_bab ? 'Bab: ' + item.judul_bab + ' | ' : '';
    document.getElementById('previewModalSubtitle').textContent = babText + 'Mapel: ' + (item.nama_mapel || '-') + (item.nama_kelas ? ' | Kelas: ' + item.nama_kelas : '');

    const descBox = document.getElementById('previewModalDescBox');
    const descEl = document.getElementById('previewModalDesc');
    if (item.deskripsi && item.deskripsi.trim() !== '') {
        descEl.innerHTML = item.deskripsi;
        descBox.classList.remove('d-none');
    } else {
        descBox.classList.add('d-none');
    }

    const container = document.getElementById('previewMediaContainer');
    container.innerHTML = '';

    const downloadBtn = document.getElementById('previewModalDownloadBtn');
    if (item.file_path) {
        downloadBtn.href = item.file_path;
        downloadBtn.classList.remove('d-none');
    } else {
        downloadBtn.classList.add('d-none');
    }

    if (item.jenis_file === 'youtube' && item.youtube_url) {
        let ytId = '';
        const match = item.youtube_url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
        if (match && match[1]) ytId = match[1];

        if (ytId) {
            container.innerHTML = `<iframe width="100%" height="520" src="https://www.youtube.com/embed/${ytId}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius:8px;"></iframe>`;
        } else {
            container.innerHTML = `<div class="py-5"><a href="${escapeHtml(item.youtube_url)}" target="_blank" class="btn btn-danger my-4"><i class="bi bi-youtube me-2"></i> Tonton di Youtube</a></div>`;
        }
    } else if (item.jenis_file === 'video' && item.file_path) {
        container.innerHTML = `<video src="${escapeHtml(item.file_path)}" controls autoplay width="100%" style="max-height:520px; border-radius:8px;"></video>`;
    } else if (item.jenis_file === 'pdf' && item.file_path) {
        container.innerHTML = `<iframe src="${escapeHtml(item.file_path)}#toolbar=1" width="100%" height="540px" style="border:none; border-radius:8px; background:#fff;"></iframe>`;
    } else if (item.file_path) {
        const ext = item.file_path.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            container.innerHTML = `<img src="${escapeHtml(item.file_path)}" class="img-fluid rounded-3 mx-auto d-block" style="max-height:520px;">`;
        } else {
            container.innerHTML = `
                <div class="py-5 text-white">
                    <i class="bi bi-file-earmark-word fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="fw-bold mb-2">${escapeHtml(item.judul)}</h6>
                    <p class="small text-white-50">Pratinjau dokumen Microsoft Office / PPT dapat diakses melalui tombol di bawah.</p>
                    <a href="${escapeHtml(item.file_path)}" target="_blank" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka File Dokumen
                    </a>
                </div>`;
        }
    } else {
        container.innerHTML = `<div class="py-5 text-white"><p class="mb-0">Tidak ada berkas yang dapat ditampilkan.</p></div>`;
    }

    const modalEl = document.getElementById('modalPreviewMateri');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.jQuery) {
            window.jQuery(modalEl).modal('show');
        }
    }
}

function closePreviewModal() {
    const container = document.getElementById('previewMediaContainer');
    if (container) container.innerHTML = '';
    const modalEl = document.getElementById('modalPreviewMateri');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const inst = bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
