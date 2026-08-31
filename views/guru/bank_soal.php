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

        // Fetch questions and analysis for this quiz
        $soalList = [];
        try {
            $stmtSoal = $db->prepare("SELECT s.*, COUNT(js.id) as total_jawaban, SUM(COALESCE(js.is_benar, 0)) as total_benar FROM soal s LEFT JOIN jawaban_siswa js ON s.id = js.soal_id WHERE s.quiz_id = ? GROUP BY s.id ORDER BY s.id ASC");
            $stmtSoal->execute([$q['id']]);
            $soalList = $stmtSoal->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            try {
                $stmtSoal = $db->prepare("SELECT s.*, 0 as total_jawaban, 0 as total_benar FROM soal s WHERE s.quiz_id = ? ORDER BY s.id ASC");
                $stmtSoal->execute([$q['id']]);
                $soalList = $stmtSoal->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable $e2) {
                $soalList = [];
            }
        }

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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($soalList)): ?>
                                                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada butir soal di paket kuis ini.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($soalList as $i => $s):
                                                        $pct = $s['total_jawaban'] > 0 ? round(($s['total_benar'] / $s['total_jawaban']) * 100) : 0;
                                                        $difficulty = $pct >= 70 ? ['Mudah','success'] : ($pct >= 40 ? ['Sedang','warning'] : ['Sulit','danger']);
                                                    ?>
                                                        <tr>
                                                            <td class="ps-3 fw-bold text-muted"><?= $i + 1 ?></td>
                                                            <td class="fw-medium text-dark">
                                                                <?= htmlspecialchars(mb_strimwidth($s['pertanyaan'], 0, 95, '...')) ?>
                                                                <?php if (!empty($s['gambar'])): ?>
                                                                    <i class="bi bi-image text-primary me-1" title="Memiliki Gambar"></i>
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
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
