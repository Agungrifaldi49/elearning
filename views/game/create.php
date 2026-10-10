<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-secondary mb-3 rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Arena Game
        </a>

        <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm border-0 rounded-4">
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-controller fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Buat Game Edukasi Baru</h4>
                    <p class="text-muted small mb-0">Rancang tantangan kuis interaktif seru berbasis waktu dan poin untuk siswa.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>index.php?url=game/create" method="POST" enctype="multipart/form-data" id="formCreateGame">
                <?= Security::csrfField() ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-8 col-12">
                        <label class="form-label small fw-bold">Judul Game Edukasi <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control rounded-3" placeholder="Contoh: Tantangan Pemrograman Web Prepared Statements" required>
                    </div>
                    <div class="col-md-4 col-12">
                        <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="mapel_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-6">
                        <label class="form-label small fw-bold">Kelas Sasaran (Opsional)</label>
                        <select name="kelas_id" class="form-select rounded-3">
                            <option value="0">Semua Kelas</option>
                            <?php foreach ($classList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-6">
                        <label class="form-label small fw-bold">Durasi Timer per Soal (Detik)</label>
                        <select name="durasi_per_soal" class="form-select rounded-3">
                            <option value="10">10 Detik (Kecepatan Tinggi 🔥)</option>
                            <option value="15" selected>15 Detik (Standar ⚡)</option>
                            <option value="20">20 Detik (Sedang ⏱️)</option>
                            <option value="30">30 Detik (Santai 🎯)</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-12">
                        <label class="form-label small fw-bold">KKM Kelulusan Game (Poin Minimal)</label>
                        <input type="number" name="kkm" class="form-control rounded-3" value="75" min="10" max="100" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Uraian / Deskripsi Game (Opsional)</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="2" placeholder="Tuliskan petunjuk aturan permainan atau salam pembuka..."></textarea>
                    </div>
                </div>

                <!-- Choice of Game Mode / Tipe Game Edukasi -->
                <div class="mb-4">
                    <label class="form-label small fw-bold d-block text-dark">
                        <i class="bi bi-controller text-danger me-1"></i> Pilih Mode / Tipe Game Edukasi <span class="text-danger">*</span>
                    </label>
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <input type="radio" class="btn-check" name="tipe_game" id="tipeMario" value="mario_run" checked>
                            <label class="btn btn-outline-warning p-3 w-100 text-start rounded-4 h-100 shadow-xs border-2" for="tipeMario">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fs-2">🍄</span>
                                    <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.68rem;">⭐ SUPER MARIO</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Super Mario Runner</h6>
                                <small class="text-muted d-block" style="font-size:0.78rem;">
                                    Karakter berlari & melompati rintangan. Saat stamina habis, jawab kuis untuk isi ulang stamina (Full 100%)!
                                </small>
                            </label>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <input type="radio" class="btn-check" name="tipe_game" id="tipeSpeed" value="quiz_speed">
                            <label class="btn btn-outline-primary p-3 w-100 text-start rounded-4 h-100 shadow-xs border-2" for="tipeSpeed">
                                <div class="fs-2 mb-2">⚡</div>
                                <h6 class="fw-bold text-dark mb-1">Quiz Speed Battle</h6>
                                <small class="text-muted d-block" style="font-size:0.78rem;">
                                    Pertarungan kuis cepat berkejaran dengan timer countdown dan streak bonus multiplier.
                                </small>
                            </label>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <input type="radio" class="btn-check" name="tipe_game" id="tipeWheel" value="spin_wheel">
                            <label class="btn btn-outline-success p-3 w-100 text-start rounded-4 h-100 shadow-xs border-2" for="tipeWheel">
                                <div class="fs-2 mb-2">🎡</div>
                                <h6 class="fw-bold text-dark mb-1">Spin Wheel Quiz</h6>
                                <small class="text-muted d-block" style="font-size:0.78rem;">
                                    Roda keberuntungan berputar acak untuk memilih kategori pertanyaan kuis siswa.
                                </small>
                            </label>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <input type="radio" class="btn-check" name="tipe_game" id="tipeMemory" value="memory_match">
                            <label class="btn btn-outline-danger p-3 w-100 text-start rounded-4 h-100 shadow-xs border-2" for="tipeMemory">
                                <div class="fs-2 mb-2">🧩</div>
                                <h6 class="fw-bold text-dark mb-1">Memory Match Cards</h6>
                                <small class="text-muted d-block" style="font-size:0.78rem;">
                                    Pencocokan kartu pasangan istilah dan jawaban kuis interaktif.
                                </small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section Input Soal Game -->
                <!-- 🎯 SECTION: IMPORT SOAL DARI QUIZ & UJIAN CBT (OPSIONAL) -->
                <div class="card p-4 border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4 mb-4 shadow-xs">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 42px; height: 42px;">
                                <i class="bi bi-patch-question-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-primary-emphasis mb-0">
                                    Ambil Soal dari Quiz & Ujian CBT <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 ms-1 fw-bold" style="font-size: 0.7rem;">Fitur Baru • Opsional</span>
                                </h6>
                                <small class="text-muted" style="font-size: 0.82rem;">Pernah membuat kuis atau ujian CBT sebelumnya? Anda dapat langsung menyalin bank soalnya ke Game Edukasi ini tanpa mengetik ulang.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-7 col-12">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-journal-check text-primary me-1"></i> Pilih Quiz / Ujian CBT Sumber:
                            </label>
                            <select id="selectQuizSource" name="import_quiz_id" class="form-select rounded-3">
                                <option value="">-- Pilih Quiz / Ujian CBT yang pernah Anda buat --</option>
                                <?php if (!empty($quizList)): ?>
                                    <?php foreach ($quizList as $q): ?>
                                        <option value="<?= $q['id'] ?>"
                                                data-judul="<?= htmlspecialchars($q['judul']) ?>"
                                                data-mapel="<?= $q['mapel_id'] ?>"
                                                data-kelas="<?= $q['kelas_id'] ?>"
                                                data-total="<?= $q['total_soal'] ?>"
                                                data-kategori="<?= strtoupper($q['kategori'] ?? 'KUIS') ?>">
                                            [<?= strtoupper($q['kategori'] ?? 'KUIS') ?>] <?= htmlspecialchars($q['judul']) ?> - <?= htmlspecialchars($q['nama_mapel'] ?? 'Mapel') ?> (<?= $q['total_soal'] ?> Soal)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled selected>-- Anda belum memiliki paket Quiz & Ujian CBT --</option>
                                <?php endif; ?>
                            </select>
                            <?php if (empty($quizList)): ?>
                                <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-info-circle me-1 text-primary"></i> Anda belum memiliki paket Quiz / Ujian CBT. Silakan buat soal manual di bawah atau gunakan template Excel.
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-5 col-12 d-flex gap-2">
                            <button type="button" id="btnLoadQuizSoal" class="btn btn-primary rounded-3 fw-bold px-3 py-2 flex-grow-1 shadow-xs" <?= empty($quizList) ? 'disabled' : '' ?>>
                                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Ambil & Terapkan Soal
                            </button>
                            <button type="button" id="btnResetSoal" class="btn btn-outline-secondary rounded-3 px-3 py-2" title="Bersihkan dan Mulai Baru">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="form-check mt-2.5">
                        <input class="form-check-input" type="checkbox" id="checkSyncMeta" checked>
                        <label class="form-check-label small text-secondary" for="checkSyncMeta">
                            Otomatis sinkronkan <strong>Judul Game</strong>, <strong>Mata Pelajaran</strong>, dan <strong>Kelas</strong> dari Quiz yang dipilih
                        </label>
                    </div>

                    <div id="quizImportStatus" class="alert d-none mt-3 mb-0 rounded-3 py-2.5 px-3 small border-0 shadow-xs">
                        <div class="d-flex align-items-center gap-2">
                            <i id="quizImportStatusIcon" class="bi fs-5"></i>
                            <div id="quizImportStatusText"></div>
                        </div>
                    </div>
                </div>

                <!-- 📄 EXCEL / CSV TEMPLATE & IMPORT SECTION -->
                <div class="card p-3.5 bg-success-subtle border border-success-subtle rounded-4 mb-4 shadow-xs">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="fw-bold text-success-emphasis d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-excel-fill fs-4 text-success"></i>
                            <span>Import Soal Game Edukasi dari Excel / CSV (Sekaligus)</span>
                        </div>
                        <a href="<?= BASE_URL ?>index.php?url=game/downloadTemplate" class="btn btn-sm btn-success rounded-pill fw-bold px-3">
                            <i class="bi bi-download me-1"></i> Download Template Excel (.csv)
                        </a>
                    </div>
                    <small class="text-secondary mb-2 d-block" style="font-size:0.82rem;">
                        Ingin memasukkan banyak soal sekaligus tanpa mengetik satu per satu? Download template Excel di atas, isi soal Anda, lalu unggah berkasnya di bawah ini:
                    </small>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-file-earmark-arrow-up-fill text-success"></i></span>
                        <input type="file" name="file_excel" class="form-control rounded-end-3" accept=".csv, .xlsx, .xls">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pt-3 border-top">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-question-square-fill text-primary me-2"></i>Daftar Soal Pertanyaan</h5>
                    <button type="button" id="btnAddSoal" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Soal Manual
                    </button>
                </div>

                <div id="soalContainer" class="d-flex flex-column gap-4 mb-4">
                    <!-- Default Soal 1 -->
                    <div class="card p-4 rounded-4 border bg-light position-relative soal-item" data-index="0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold fs-6">Soal #1</span>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-remove-soal" title="Hapus Soal">
                                <i class="bi bi-trash3 fs-5"></i>
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Teks Pertanyaan <span class="text-muted">(Diisi jika buat manual)</span></label>
                            <textarea name="soal[0][pertanyaan]" class="form-control rounded-3" rows="2" placeholder="Tuliskan soal pertanyaan game..."></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">A</span>
                                    <input type="text" name="soal[0][opsi_a]" class="form-control" placeholder="Pilihan Jawaban A">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">B</span>
                                    <input type="text" name="soal[0][opsi_b]" class="form-control" placeholder="Pilihan Jawaban B">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">C</span>
                                    <input type="text" name="soal[0][opsi_c]" class="form-control" placeholder="Pilihan Jawaban C">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">D</span>
                                    <input type="text" name="soal[0][opsi_d]" class="form-control" placeholder="Pilihan Jawaban D">
                                </div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-semibold">Kunci Jawaban Benar</label>
                                <select name="soal[0][kunci_jawaban]" class="form-select rounded-3">
                                    <option value="a">A</option>
                                    <option value="b">B</option>
                                    <option value="c">C</option>
                                    <option value="d">D</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-semibold">Bobot Poin Soal</label>
                                <input type="number" name="soal[0][poin]" class="form-control rounded-3" value="10" min="5" max="100">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning text-dark px-5 py-3 rounded-pill fw-bold shadow-lg fs-6">
                        <i class="bi bi-cloud-check-fill me-2"></i> Simpan Game Edukasi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('soalContainer');
    const btnAdd = document.getElementById('btnAddSoal');
    const formCreateGame = document.getElementById('formCreateGame');
    const selectQuiz = document.getElementById('selectQuizSource');
    const btnLoadQuiz = document.getElementById('btnLoadQuizSoal');
    const btnResetSoal = document.getElementById('btnResetSoal');
    const checkSyncMeta = document.getElementById('checkSyncMeta');
    const statusBox = document.getElementById('quizImportStatus');
    const statusIcon = document.getElementById('quizImportStatusIcon');
    const statusText = document.getElementById('quizImportStatusText');

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderSoalCard(idx, data = {}) {
        const pert = data.pertanyaan ? escapeHtml(data.pertanyaan) : '';
        const a = data.opsi_a ? escapeHtml(data.opsi_a) : '';
        const b = data.opsi_b ? escapeHtml(data.opsi_b) : '';
        const c = data.opsi_c ? escapeHtml(data.opsi_c) : '';
        const d = data.opsi_d ? escapeHtml(data.opsi_d) : '';
        const kunci = (data.kunci_jawaban || 'a').toLowerCase();
        const poin = data.poin || 10;

        return `
            <div class="card p-4 rounded-4 border bg-light position-relative soal-item" data-index="${idx}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold fs-6">Soal #${idx + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-remove-soal" title="Hapus Soal">
                        <i class="bi bi-trash3 fs-5"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Teks Pertanyaan</label>
                    <textarea name="soal[${idx}][pertanyaan]" class="form-control rounded-3" rows="2" placeholder="Tuliskan soal pertanyaan game...">${pert}</textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">A</span>
                            <input type="text" name="soal[${idx}][opsi_a]" class="form-control" placeholder="Pilihan Jawaban A" value="${a}">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">B</span>
                            <input type="text" name="soal[${idx}][opsi_b]" class="form-control" placeholder="Pilihan Jawaban B" value="${b}">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">C</span>
                            <input type="text" name="soal[${idx}][opsi_c]" class="form-control" placeholder="Pilihan Jawaban C" value="${c}">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">D</span>
                            <input type="text" name="soal[${idx}][opsi_d]" class="form-control" placeholder="Pilihan Jawaban D" value="${d}">
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 col-12">
                        <label class="form-label small fw-semibold">Kunci Jawaban Benar</label>
                        <select name="soal[${idx}][kunci_jawaban]" class="form-select rounded-3">
                            <option value="a" ${kunci === 'a' ? 'selected' : ''}>A</option>
                            <option value="b" ${kunci === 'b' ? 'selected' : ''}>B</option>
                            <option value="c" ${kunci === 'c' ? 'selected' : ''}>C</option>
                            <option value="d" ${kunci === 'd' ? 'selected' : ''}>D</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-12">
                        <label class="form-label small fw-semibold">Bobot Poin Soal</label>
                        <input type="number" name="soal[${idx}][poin]" class="form-control rounded-3" value="${poin}" min="5" max="100">
                    </div>
                </div>
            </div>
        `;
    }

    function updateBadges() {
        if (!container) return;
        container.querySelectorAll('.soal-item').forEach((item, idx) => {
            item.setAttribute('data-index', idx);
            const badge = item.querySelector('.badge');
            if (badge) badge.textContent = `Soal #${idx + 1}`;
        });
    }

    function attachRemoveHandlers() {
        if (!container) return;
        container.querySelectorAll('.btn-remove-soal').forEach(btn => {
            btn.onclick = function() {
                const items = container.querySelectorAll('.soal-item');
                const fileInput = formCreateGame ? formCreateGame.querySelector('input[name="file_excel"]') : null;
                const hasExcelFile = fileInput && fileInput.files && fileInput.files.length > 0;

                if (items.length <= 1 && !hasExcelFile) {
                    alert('Game Edukasi harus memiliki minimal 1 soal (manual, dari Quiz, atau dari Excel).');
                    return;
                }
                btn.closest('.soal-item').remove();
                updateBadges();
            };
        });
    }

    if (btnAdd && container) {
        btnAdd.addEventListener('click', function() {
            const idx = container.querySelectorAll('.soal-item').length;
            container.insertAdjacentHTML('beforeend', renderSoalCard(idx));
            attachRemoveHandlers();
        });
    }

    // Handle Load Quiz Soal via AJAX
    if (btnLoadQuiz && selectQuiz) {
        btnLoadQuiz.addEventListener('click', function() {
            const quizId = selectQuiz.value;
            if (!quizId) {
                alert('Silakan pilih salah satu Quiz & Ujian CBT terlebih dahulu pada dropdown.');
                selectQuiz.focus();
                return;
            }

            const origHtml = btnLoadQuiz.innerHTML;
            btnLoadQuiz.disabled = true;
            btnLoadQuiz.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Memuat Soal...';

            fetch('<?= BASE_URL ?>index.php?url=game/getQuizQuestions&quiz_id=' + encodeURIComponent(quizId))
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        statusBox.className = 'alert alert-danger mt-3 mb-0 rounded-3 py-2.5 px-3 small border-0 shadow-xs';
                        statusIcon.className = 'bi bi-exclamation-triangle-fill text-danger fs-5';
                        statusText.innerHTML = `<strong>Gagal:</strong> ${data.message || 'Tidak dapat memuat soal dari quiz terpilih.'}`;
                        statusBox.classList.remove('d-none');
                        return;
                    }

                    // Optional metadata synchronization
                    if (checkSyncMeta && checkSyncMeta.checked && data.quiz) {
                        const inputJudul = document.querySelector('input[name="judul"]');
                        if (inputJudul) {
                            inputJudul.value = 'Game: ' + data.quiz.judul;
                        }
                        const selectMapel = document.querySelector('select[name="mapel_id"]');
                        if (selectMapel && data.quiz.mapel_id) {
                            selectMapel.value = data.quiz.mapel_id;
                        }
                        const selectKelas = document.querySelector('select[name="kelas_id"]');
                        if (selectKelas && data.quiz.kelas_id) {
                            selectKelas.value = data.quiz.kelas_id;
                        }
                    }

                    // Populate questions into container
                    container.innerHTML = '';
                    data.soal.forEach((s, idx) => {
                        container.insertAdjacentHTML('beforeend', renderSoalCard(idx, s));
                    });
                    attachRemoveHandlers();

                    // Show success feedback
                    statusBox.className = 'alert alert-success mt-3 mb-0 rounded-3 py-2.5 px-3 small border-0 shadow-xs';
                    statusIcon.className = 'bi bi-check-circle-fill text-success fs-5';
                    statusText.innerHTML = `<strong>Berhasil Diimpor!</strong> Sebanyak <strong>${data.total} butir soal</strong> dari Quiz <em>"${escapeHtml(data.quiz.judul)}"</em> telah berhasil dimuat ke bawah. Anda dapat meninjau, mengedit teks/kunci/poin, atau menambah soal baru.`;
                    statusBox.classList.remove('d-none');

                    // Smooth scroll to question container
                    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(err => {
                    statusBox.className = 'alert alert-danger mt-3 mb-0 rounded-3 py-2.5 px-3 small border-0 shadow-xs';
                    statusIcon.className = 'bi bi-exclamation-circle-fill text-danger fs-5';
                    statusText.innerHTML = `<strong>Kendala Jaringan:</strong> Gagal terhubung ke server untuk mengambil soal quiz.`;
                    statusBox.classList.remove('d-none');
                })
                .finally(() => {
                    btnLoadQuiz.disabled = false;
                    btnLoadQuiz.innerHTML = origHtml;
                });
        });
    }

    // Reset button handler
    if (btnResetSoal) {
        btnResetSoal.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mengosongkan soal dan kembali ke template 1 soal manual?')) {
                if (selectQuiz) selectQuiz.value = '';
                if (statusBox) statusBox.classList.add('d-none');
                container.innerHTML = renderSoalCard(0);
                attachRemoveHandlers();
            }
        });
    }

    // Form submission validation
    if (formCreateGame) {
        formCreateGame.addEventListener('submit', function(e) {
            const fileInput = formCreateGame.querySelector('input[name="file_excel"]');
            const hasExcelFile = fileInput && fileInput.files && fileInput.files.length > 0;
            const hasQuizSelected = selectQuiz && selectQuiz.value && parseInt(selectQuiz.value) > 0;

            let hasValidManualSoal = false;
            const questions = formCreateGame.querySelectorAll('.soal-item');
            questions.forEach(q => {
                const tanya = q.querySelector('textarea[name*="[pertanyaan]"]');
                const opsiA = q.querySelector('input[name*="[opsi_a]"]');
                const opsiB = q.querySelector('input[name*="[opsi_b]"]');
                if (tanya && tanya.value.trim() !== '' && opsiA && opsiA.value.trim() !== '' && opsiB && opsiB.value.trim() !== '') {
                    hasValidManualSoal = true;
                }
            });

            if (!hasExcelFile && !hasValidManualSoal && !hasQuizSelected) {
                alert('Silakan masukkan minimal 1 soal manual, ambil dari Quiz & Ujian CBT, atau pilih berkas Excel/CSV template soal.');
                e.preventDefault();
            }
        });
    }

    attachRemoveHandlers();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
