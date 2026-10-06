<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$db = Database::getConnection();

// Group Quiz Packages by Mata Pelajaran (Mapel)
$mapelGroups = [];
$totalPaketAll = count($quizList ?? []);
$totalSoalAll = 0;
$totalPgAll = 0;
$totalEssayAll = 0;

if (!empty($quizList)) {
    // Collect all quiz IDs for single batch query
    $quizIds = array_values(array_filter(array_unique(array_map('intval', array_column($quizList, 'id')))));
    $allSoalAnalysis = [];
    if (!empty($quizIds)) {
        $inQuiz = implode(',', $quizIds);
        try {
            $stmtAllSoal = $db->query("
                SELECT s.*, COUNT(js.id) as total_jawaban, SUM(COALESCE(js.is_benar, 0)) as total_benar 
                FROM soal s 
                LEFT JOIN jawaban_siswa js ON s.id = js.soal_id 
                WHERE s.quiz_id IN ($inQuiz) 
                GROUP BY s.id 
                ORDER BY s.quiz_id ASC, s.id ASC
            ");
            while ($row = $stmtAllSoal->fetch(PDO::FETCH_ASSOC)) {
                $allSoalAnalysis[(int)$row['quiz_id']][] = $row;
            }
        } catch (Throwable $e) {
            try {
                $stmtAllSoal = $db->query("SELECT s.*, 0 as total_jawaban, 0 as total_benar FROM soal s WHERE s.quiz_id IN ($inQuiz) ORDER BY s.quiz_id ASC, s.id ASC");
                while ($row = $stmtAllSoal->fetch(PDO::FETCH_ASSOC)) {
                    $allSoalAnalysis[(int)$row['quiz_id']][] = $row;
                }
            } catch (Throwable $e2) {
                $allSoalAnalysis = [];
            }
        }
        // Preload choices for all questions
        $allSoalFlat = [];
        foreach ($allSoalAnalysis as $qSoals) {
            foreach ($qSoals as $sRow) {
                $allSoalFlat[] = (int)$sRow['id'];
            }
        }
        $choicesMap = [];
        if (!empty($allSoalFlat)) {
            $inSIds = implode(',', $allSoalFlat);
            try {
                $stmtPil = $db->query("SELECT * FROM pilihan_jawaban WHERE soal_id IN ($inSIds) ORDER BY id ASC");
                while ($pRow = $stmtPil->fetch(PDO::FETCH_ASSOC)) {
                    $choicesMap[(int)$pRow['soal_id']][] = $pRow;
                }
            } catch (Throwable $ePil) {}
        }
    }

    foreach ($quizList as $q) {
        $mapelName = !empty($q['nama_mapel']) ? $q['nama_mapel'] : 'Umum / Lainnya';
        if (!isset($mapelGroups[$mapelName])) {
            $mapelGroups[$mapelName] = [
                'nama_mapel' => $mapelName,
                'total_quizzes' => 0,
                'total_soal' => 0,
                'quizzes' => []
            ];
        }

        $soalList = $allSoalAnalysis[(int)$q['id']] ?? [];
        foreach ($soalList as &$sItem) {
            $sItem['pilihan'] = $choicesMap[(int)$sItem['id']] ?? [];
        }
        unset($sItem);
        $qCount = count($soalList);
        $q['soal_list'] = $soalList;

        $mapelGroups[$mapelName]['quizzes'][] = $q;
        $mapelGroups[$mapelName]['total_quizzes']++;
        $mapelGroups[$mapelName]['total_soal'] += $qCount;

        $totalSoalAll += $qCount;
        foreach ($soalList as $sItem) {
            if (($sItem['jenis_soal'] ?? 'pg') === 'essay') {
                $totalEssayAll++;
            } else {
                $totalPgAll++;
            }
        }
    }
}
?>

<!-- Custom Styling for Accordion Groups & Responsive Layout -->
<style>
.mapel-group-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    transition: all 0.25s ease-in-out;
}
.mapel-group-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}
.mapel-header-bar {
    cursor: pointer;
    user-select: none;
    border-radius: 16px;
    transition: background 0.2s ease;
}
.mapel-header-bar:hover {
    background: #f8fafc;
}
.badge-mapel-count {
    background: #e0e7ff;
    color: #4338ca;
    font-weight: 700;
}
.badge-soal-count {
    background: #dcfce7;
    color: #15803d;
    font-weight: 700;
}
.quiz-item-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}
</style>

<main class="main-content px-3 px-md-4 pb-4">
    <div class="container-fluid">
        <?= FlashHelper::display() ?>

        <!-- Top Title Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-database-fill-gear text-primary me-2"></i>Bank Soal & Analisis Butir Soal</h4>
                <p class="text-muted small mb-0">Repositori soal terkelompok per Mata Pelajaran dilengkapi analisis tingkat kesulitan & fitur buka-tutup (accordion).</p>
            </div>
        </div>

        <!-- Global Summary KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                    <div class="fw-bold text-primary fs-3"><?= count($mapelGroups) ?></div>
                    <small class="text-muted fw-semibold">Kelompok Mapel</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                    <div class="fw-bold text-dark fs-3"><?= $totalPaketAll ?></div>
                    <small class="text-muted fw-semibold">Total Paket Kuis</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                    <div class="fw-bold text-success fs-3"><?= $totalSoalAll ?></div>
                    <small class="text-muted fw-semibold">Total Butir Soal</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                    <div class="fw-bold text-info fs-3"><?= $totalPgAll ?> <span class="fs-6 fw-normal text-muted">PG</span> / <?= $totalEssayAll ?> <span class="fs-6 fw-normal text-muted">Essay</span></div>
                    <small class="text-muted fw-semibold">Komposisi Soal</small>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="row g-3 mb-4 align-items-center">
            <div class="col-12 col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchBankSoalInput" class="form-control border-start-0 ps-0" placeholder="Cari nama mata pelajaran, judul kuis, atau pertanyaan soal..." onkeyup="filterBankSoalGroups()">
                </div>
            </div>
            <div class="col-12 col-md-5">
                <select id="filterMapelSelect" class="form-select fw-semibold" onchange="filterBankSoalGroups()">
                    <option value="">-- Semua Kelompok Mata Pelajaran --</option>
                    <?php foreach (array_keys($mapelGroups) as $mName): ?>
                        <option value="<?= htmlspecialchars($mName) ?>"><?= htmlspecialchars($mName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Mapel Groups Container -->
        <div id="mapelGroupsContainer">
            <?php if (empty($mapelGroups)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white text-muted">
                    <i class="bi bi-folder-x fs-1 text-slate-300 d-block mb-2"></i>
                    Belum ada paket kuis atau bank soal yang tersedia.
                </div>
            <?php else: ?>
                <?php 
                $groupIndex = 1;
                foreach ($mapelGroups as $mapelName => $group): 
                ?>
                    <div class="mapel-group-card mb-4 shadow-sm" 
                         data-mapel="<?= htmlspecialchars(strtolower($mapelName)) ?>" 
                         data-search="<?= htmlspecialchars(strtolower($mapelName . ' ' . implode(' ', array_column($group['quizzes'], 'judul')))) ?>">
                        
                        <!-- Mapel Header Bar (Expand/Collapse Clickable) -->
                        <div class="mapel-header-bar p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2" 
                             onclick="toggleMapelGroup('mapelGroupBody<?= $groupIndex ?>', this)">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="bi bi-journal-bookmark-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($group['nama_mapel']) ?></h5>
                                    <div class="d-flex gap-2 flex-wrap align-items-center">
                                        <span class="badge badge-mapel-count rounded-pill px-3 py-1 small">
                                            <i class="bi bi-collection-fill me-1"></i><?= $group['total_quizzes'] ?> Paket Kuis
                                        </span>
                                        <span class="badge badge-soal-count rounded-pill px-3 py-1 small">
                                            <i class="bi bi-question-circle-fill me-1"></i><?= $group['total_soal'] ?> Butir Soal
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold btn-toggle-state no-print">
                                <i class="bi bi-chevron-down me-1 icon-toggle"></i> <span class="text-toggle">Buka Paket Soal</span>
                            </button>
                        </div>

                        <!-- Collapsible Body for Quizzes under this Mapel -->
                        <div id="mapelGroupBody<?= $groupIndex ?>" class="mapel-group-body p-3 p-md-4 border-top d-none">
                            <?php foreach ($group['quizzes'] as $qIndex => $q): 
                                $soalList = $q['soal_list'];
                            ?>
                                <div class="quiz-item-box p-3 p-md-4 mb-3">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark fs-6">
                                                <i class="bi bi-journal-check text-success me-1.5"></i><?= htmlspecialchars($q['judul']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                Kelas: <span class="fw-bold text-dark"><?= htmlspecialchars($q['nama_kelas']) ?></span> | 
                                                Guru Pengampu: <span class="fw-bold text-primary"><i class="bi bi-person-fill me-0.5"></i><?= htmlspecialchars($q['nama_guru'] ?? 'Guru Pengampu') ?></span> | 
                                                Durasi: <span class="fw-bold text-dark"><?= $q['durasi_menit'] ?> Menit</span> | 
                                                Batas Kuis: <?= !empty($q['deadline']) ? date('d M Y H:i', strtotime($q['deadline'])) : 'Tanpa Batas' ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                                            <?= count($soalList) ?> Butir Soal
                                        </span>
                                    </div>

                                    <!-- Question Details & Analysis Table -->
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle small bg-white rounded-3 border">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:45px" class="ps-3">#</th>
                                                    <th>Pertanyaan / Soal</th>
                                                    <th>Jenis</th>
                                                    <th>Bobot</th>
                                                    <th>Dijawab</th>
                                                    <th style="min-width: 140px;">% Benar (Ketepatan)</th>
                                                    <th>Tingkat Kesulitan</th>
                                                    <th style="width: 90px;" class="text-center no-print">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($soalList)): ?>
                                                    <tr><td colspan="8" class="text-center text-muted py-3">Belum ada butir soal di paket kuis ini.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($soalList as $i => $s):
                                                        $pct = $s['total_jawaban'] > 0 ? round(($s['total_benar'] / $s['total_jawaban']) * 100) : 0;
                                                        $difficulty = $pct >= 70 ? ['Mudah','success'] : ($pct >= 40 ? ['Sedang','warning'] : ['Sulit','danger']);
                                                    ?>
                                                        <tr>
                                                            <td class="ps-3 fw-bold text-muted"><?= $i + 1 ?></td>
                                                            <td class="fw-medium text-dark">
                                                                <div><?= htmlspecialchars(mb_strimwidth($s['pertanyaan'], 0, 95, '...')) ?></div>
                                                                <?php 
                                                                    $rawGbr = !empty($s['gambar']) ? trim($s['gambar']) : (!empty($s['file_gambar']) ? trim($s['file_gambar']) : '');
                                                                    if (!empty($rawGbr)): 
                                                                        if (strpos($rawGbr, 'http://') === 0 || strpos($rawGbr, 'https://') === 0) {
                                                                        $imgSrc = $rawGbr;
                                                                    } else {
                                                                        $cleanP = ltrim($rawGbr, '/');
                                                                        if (strpos($cleanP, 'assets/') === 0) {
                                                                            $imgSrc = BASE_URL . $cleanP;
                                                                        } elseif (strpos($cleanP, 'uploads/') === 0) {
                                                                            $imgSrc = BASE_URL . 'assets/' . $cleanP;
                                                                        } else {
                                                                            $imgSrc = BASE_URL . 'assets/uploads/soal/' . $cleanP;
                                                                        }
                                                                    }
                                                                ?>
                                                                    <div class="mt-1.5">
                                                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 text-decoration-none" onclick="previewBankSoalImage('<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>')">
                                                                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Img" style="width: 16px; height: 16px; object-fit: cover; border-radius: 3px;" class="me-1" onerror="this.style.display='none'">
                                                                            <i class="bi bi-zoom-in me-1"></i>Lihat Gambar
                                                                        </button>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?= $s['jenis_soal'] === 'pg' ? 'primary' : ($s['jenis_soal'] === 'essay' ? 'info' : 'secondary') ?> rounded-pill px-2.5">
                                                                    <?= strtoupper($s['jenis_soal']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="fw-semibold"><?= $s['bobot'] ?> Poin</td>
                                                            <td><?= $s['total_jawaban'] ?? 0 ?> Siswa</td>
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                                                                        <div class="progress-bar bg-<?= $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') ?>"
                                                                             style="width:<?= $pct ?>%"></div>
                                                                    </div>
                                                                    <span class="fw-bold small" style="min-width:32px;"><?= $pct ?>%</span>
                                                                </div>
                                                            </td>
                                                            <td><span class="badge bg-<?= $difficulty[1] ?> rounded-pill px-3"><?= $difficulty[0] ?></span></td>
                                                            <td class="text-center no-print">
                                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" onclick='openEditSoalModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)' title="Edit Soal">
                                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                <?php 
                $groupIndex++;
                endforeach; 
                ?>
            <?php endif; ?>
        </div>

        <!-- Group Pagination Controls (Maksimal 10 Kelompok Mapel per Halaman) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top gap-2" id="paginationMapelContainer">
            <div class="small text-muted fw-semibold">
                Menampilkan <span id="mapelPageStart" class="fw-bold text-dark">0</span> - <span id="mapelPageEnd" class="fw-bold text-dark">0</span> dari <span id="mapelTotalCount" class="fw-bold text-primary">0</span> kelompok Mata Pelajaran
            </div>
            <div class="d-flex gap-1.5 align-items-center" id="mapelPaginationButtons">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-bold" id="btnPrevMapel" onclick="changeMapelPage(-1)">
                    <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                </button>
                <div class="d-inline-flex gap-1" id="mapelPageNumbers"></div>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs" id="btnNextMapel" onclick="changeMapelPage(1)">
                    Lanjutkan <i class="bi bi-chevron-right ms-1"></i>
                </button>
            </div>
        </div>

    </div>
</main>

<!-- Interactive Accordion & Pagination JavaScript -->
<script>
let currentMapelPage = 1;
const mapelItemsPerPage = 10;
let filteredMapelCards = [];

function toggleMapelGroup(bodyId, triggerElem) {
    const bodyElem = document.getElementById(bodyId);
    if (!bodyElem) return;

    const isHidden = bodyElem.classList.contains('d-none');
    
    if (isHidden) {
        bodyElem.classList.remove('d-none');
    } else {
        bodyElem.classList.add('d-none');
    }

    // Find button container
    const card = bodyElem.closest('.mapel-group-card');
    if (card) {
        const btnState = card.querySelector('.btn-toggle-state');
        if (btnState) {
            const iconElem = btnState.querySelector('.icon-toggle');
            const textElem = btnState.querySelector('.text-toggle');
            if (isHidden) {
                if (iconElem) iconElem.className = 'bi bi-chevron-up me-1 icon-toggle';
                if (textElem) textElem.textContent = 'Sembunyikan';
                btnState.classList.remove('btn-outline-primary');
                btnState.classList.add('btn-primary');
            } else {
                if (iconElem) iconElem.className = 'bi bi-chevron-down me-1 icon-toggle';
                if (textElem) textElem.textContent = 'Buka Paket Soal';
                btnState.classList.remove('btn-primary');
                btnState.classList.add('btn-outline-primary');
            }
        }
    }
}

function changeMapelPage(delta) {
    const totalPages = Math.ceil(filteredMapelCards.length / mapelItemsPerPage) || 1;
    const newPage = currentMapelPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        currentMapelPage = newPage;
        renderMapelPage();
    }
}

function goToMapelPage(pageNum) {
    currentMapelPage = pageNum;
    renderMapelPage();
}

function renderMapelPage() {
    const allCards = document.querySelectorAll('.mapel-group-card');
    allCards.forEach(c => c.style.display = 'none');

    const totalVisible = filteredMapelCards.length;
    const totalPages = Math.ceil(totalVisible / mapelItemsPerPage) || 1;

    if (currentMapelPage > totalPages) currentMapelPage = totalPages;
    if (currentMapelPage < 1) currentMapelPage = 1;

    const startIdx = (currentMapelPage - 1) * mapelItemsPerPage;
    const endIdx = Math.min(startIdx + mapelItemsPerPage, totalVisible);

    for (let i = startIdx; i < endIdx; i++) {
        const card = filteredMapelCards[i];
        if (card) card.style.display = '';
    }

    // Update Pagination Text Info
    const elStart = document.getElementById('mapelPageStart');
    const elEnd = document.getElementById('mapelPageEnd');
    const elTotal = document.getElementById('mapelTotalCount');

    if (elStart) elStart.textContent = totalVisible > 0 ? (startIdx + 1) : 0;
    if (elEnd) elEnd.textContent = endIdx;
    if (elTotal) elTotal.textContent = totalVisible;

    // Update Button Disabled States
    const btnPrev = document.getElementById('btnPrevMapel');
    const btnNext = document.getElementById('btnNextMapel');
    if (btnPrev) btnPrev.disabled = (currentMapelPage <= 1);
    if (btnNext) btnNext.disabled = (currentMapelPage >= totalPages || totalVisible === 0);

    // Render Page Number Buttons
    const pageNumContainer = document.getElementById('mapelPageNumbers');
    if (pageNumContainer) {
        pageNumContainer.innerHTML = '';
        if (totalPages > 1) {
            for (let p = 1; p <= totalPages; p++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm rounded-pill px-2.5 py-1 fw-bold ' + (p === currentMapelPage ? 'btn-primary' : 'btn-outline-secondary');
                btn.textContent = p;
                btn.onclick = (function(page) { return function() { goToMapelPage(page); }; })(p);
                pageNumContainer.appendChild(btn);
            }
        }
    }
}

function filterBankSoalGroups() {
    const searchVal = (document.getElementById('searchBankSoalInput')?.value || '').toLowerCase().trim();
    const mapelVal = (document.getElementById('filterMapelSelect')?.value || '').toLowerCase().trim();

    const cards = document.querySelectorAll('.mapel-group-card');
    filteredMapelCards = [];

    cards.forEach(card => {
        const mapelAttr = (card.getAttribute('data-mapel') || '').toLowerCase();
        const searchAttr = (card.getAttribute('data-search') || '').toLowerCase();

        const matchSearch = !searchVal || searchAttr.includes(searchVal);
        const matchMapel = !mapelVal || mapelAttr === mapelVal;

        if (matchSearch && matchMapel) {
            filteredMapelCards.push(card);
        }
    });

    currentMapelPage = 1;
    renderMapelPage();
}

document.addEventListener('DOMContentLoaded', function() {
    filterBankSoalGroups();
});

function previewBankSoalImage(imgUrl) {
    const imgTag = document.getElementById('previewBankSoalImgTag');
    if (imgTag) {
        imgTag.src = imgUrl;
        const modalEl = document.getElementById('modalPreviewBankSoalImage');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
}

function openEditSoalModal(soal) {
    if (!soal) return;
    document.getElementById('editSoalId').value = soal.id || soal.soal_id || '';
    document.getElementById('editPertanyaan').value = soal.pertanyaan || soal.soal || '';
    document.getElementById('editBobot').value = soal.bobot || 10;
    
    let jenis = (soal.jenis_soal || 'pg').toLowerCase();
    if (jenis === 'true/false') jenis = 'tf';
    document.getElementById('editJenisSoal').value = jenis;

    // Check image
    const currentImgContainer = document.getElementById('editCurrentImgContainer');
    const currentImgTag = document.getElementById('editCurrentImgTag');
    const checkHapus = document.getElementById('checkHapusGambar');
    if (checkHapus) checkHapus.checked = false;

    if (soal.gambar && soal.gambar.trim() !== '') {
        const raw = soal.gambar.trim();
        let fullUrl = '';
        if (raw.startsWith('http://') || raw.startsWith('https://')) {
            fullUrl = raw;
        } else {
            const clean = raw.replace(/^\/+/, '');
            if (clean.startsWith('assets/')) {
                fullUrl = '<?= BASE_URL ?>' + clean;
            } else if (clean.startsWith('uploads/')) {
                fullUrl = '<?= BASE_URL ?>assets/' + clean;
            } else {
                fullUrl = '<?= BASE_URL ?>assets/uploads/soal/' + clean;
            }
        }
        currentImgTag.src = fullUrl;
        currentImgContainer.classList.remove('d-none');
    } else {
        currentImgContainer.classList.add('d-none');
    }

    // Populate choices
    const container = document.getElementById('editPilihanList');
    container.innerHTML = '';
    const choices = Array.isArray(soal.pilihan) ? soal.pilihan : [];

    if (choices.length > 0) {
        choices.forEach((p, idx) => {
            const label = String.fromCharCode(65 + idx);
            const isBenar = (p.is_benar == 1 || p.is_benar === true || p.is_benar === '1');
            const teks = p.teks_pilihan || p.teks || '';
            container.appendChild(createChoiceItem(idx, label, teks, isBenar));
        });
    } else {
        ['A', 'B', 'C', 'D'].forEach((label, idx) => {
            container.appendChild(createChoiceItem(idx, label, '', idx === 0));
        });
    }

    // Set TF answer if applicable
    if (jenis === 'tf') {
        let isBenarTrue = true;
        if (choices.length > 0) {
            const firstIsBenar = (choices[0].is_benar == 1 || choices[0].is_benar === true);
            const firstTeks = (choices[0].teks_pilihan || choices[0].teks || '').toLowerCase();
            if (firstTeks.includes('salah') && firstIsBenar) {
                isBenarTrue = false;
            }
        }
        document.getElementById('editTfBenar').checked = isBenarTrue;
        document.getElementById('editTfSalah').checked = !isBenarTrue;
    }

    toggleEditJenisFields();

    const modalEl = document.getElementById('modalEditBankSoal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function createChoiceItem(idx, label, text, isBenar) {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <div class="input-group-text bg-light">
            <input class="form-check-input mt-0 me-1" type="radio" name="jawaban_benar" value="${idx}" ${isBenar ? 'checked' : ''} title="Tandai sebagai kunci benar">
            <span class="fw-bold">${label}</span>
        </div>
        <input type="text" name="pilihan[${idx}]" class="form-control" value="${text.replace(/"/g, '&quot;')}" placeholder="Pilihan jawaban ${label}..." required>
    `;
    return div;
}

function toggleEditJenisFields() {
    const jenis = document.getElementById('editJenisSoal').value;
    const pgBox = document.getElementById('editPgOptionsContainer');
    const tfBox = document.getElementById('editTfOptionsContainer');
    const essayBox = document.getElementById('editEssayInfoContainer');

    pgBox.classList.add('d-none');
    tfBox.classList.add('d-none');
    essayBox.classList.add('d-none');

    // Remove required from PG inputs if not PG
    const pgInputs = pgBox.querySelectorAll('input[type="text"]');

    if (jenis === 'pg') {
        pgBox.classList.remove('d-none');
        pgInputs.forEach(i => i.setAttribute('required', 'required'));
    } else if (jenis === 'tf') {
        tfBox.classList.remove('d-none');
        pgInputs.forEach(i => i.removeAttribute('required'));
    } else {
        essayBox.classList.remove('d-none');
        pgInputs.forEach(i => i.removeAttribute('required'));
    }
}
</script>

<!-- Modal Preview Gambar Soal -->
<div class="modal fade" id="modalPreviewBankSoalImage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-image me-1.5 text-primary"></i>Lampiran Gambar Soal</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="previewBankSoalImgTag" src="" alt="Gambar Soal" class="img-fluid rounded-3 shadow-sm border" style="max-height: 480px; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Soal -->
<div class="modal fade" id="modalEditBankSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="edit_soal">
                <input type="hidden" name="soal_id" id="editSoalId" value="">
                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Butir Soal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Jenis Soal</label>
                            <select name="jenis_soal" id="editJenisSoal" class="form-select rounded-3" onchange="toggleEditJenisFields()">
                                <option value="pg">Pilihan Ganda (PG)</option>
                                <option value="tf">Benar / Salah (True/False)</option>
                                <option value="essay">Uraian / Essay</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Bobot Poin</label>
                            <input type="number" name="bobot" id="editBobot" class="form-control rounded-3" min="1" value="10" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Teks Pertanyaan Soal *</label>
                            <textarea name="pertanyaan" id="editPertanyaan" class="form-control rounded-3" rows="4" required></textarea>
                        </div>

                        <!-- Gambar Soal -->
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Lampiran Gambar Soal (Opsional)</label>
                            <div id="editCurrentImgContainer" class="d-none mb-2 p-2 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <img id="editCurrentImgTag" src="" alt="Gambar Saat Ini" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                    <div>
                                        <div class="small fw-bold text-dark">Gambar Soal Saat Ini</div>
                                        <small class="text-muted">Tersimpan di server</small>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hapus_gambar" value="1" id="checkHapusGambar">
                                    <label class="form-check-label small text-danger fw-semibold" for="checkHapusGambar">
                                        Hapus Gambar Ini
                                    </label>
                                </div>
                            </div>
                            <input type="file" name="gambar_soal" class="form-control rounded-3" accept="image/*">
                            <small class="text-muted d-block mt-1">Format: JPG, PNG, WEBP. Maks 5MB. Unggah file baru untuk menggantikan gambar lama.</small>
                        </div>

                        <!-- Opsi Jawaban PG -->
                        <div class="col-12" id="editPgOptionsContainer">
                            <label class="form-label fw-bold small text-muted d-flex justify-content-between align-items-center">
                                <span>Pilihan Jawaban & Tandai Kunci Benar *</span>
                                <small class="text-primary fw-normal">Pilih radio button di sebelah kiri untuk kunci benar</small>
                            </label>
                            <div id="editPilihanList">
                                <!-- Generated by JS -->
                            </div>
                        </div>

                        <!-- Opsi Jawaban TF -->
                        <div class="col-12 d-none" id="editTfOptionsContainer">
                            <label class="form-label fw-bold small text-muted">Kunci Jawaban Benar (True / False) *</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jawaban_tf" id="editTfBenar" value="BENAR" checked>
                                    <label class="form-check-label fw-semibold" for="editTfBenar">Benar (True)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jawaban_tf" id="editTfSalah" value="SALAH">
                                    <label class="form-check-label fw-semibold" for="editTfSalah">Salah (False)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Info Essay -->
                        <div class="col-12 d-none" id="editEssayInfoContainer">
                            <div class="alert alert-info rounded-3 mb-0 small">
                                <i class="bi bi-info-circle-fill me-1.5"></i>
                                Soal jenis <strong>Essay</strong> tidak memiliki pilihan jawaban otomatis. Penilaian dilakukan oleh Guru di menu Koreksi Kuis.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1.5"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
