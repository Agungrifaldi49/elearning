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

            <form action="<?= BASE_URL ?>index.php?url=game/create" method="POST" id="formCreateGame">
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
                <div class="d-flex justify-content-between align-items-center mb-3 pt-3 border-top">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-question-square-fill text-primary me-2"></i>Daftar Soal Pertanyaan</h5>
                    <button type="button" id="btnAddSoal" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Soal
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
                            <label class="form-label small fw-semibold">Teks Pertanyaan <span class="text-danger">*</span></label>
                            <textarea name="soal[0][pertanyaan]" class="form-control rounded-3" rows="2" placeholder="Tuliskan soal pertanyaan game..." required></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">A</span>
                                    <input type="text" name="soal[0][opsi_a]" class="form-control" placeholder="Pilihan Jawaban A" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">B</span>
                                    <input type="text" name="soal[0][opsi_b]" class="form-control" placeholder="Pilihan Jawaban B" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">C</span>
                                    <input type="text" name="soal[0][opsi_c]" class="form-control" placeholder="Pilihan Jawaban C" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">D</span>
                                    <input type="text" name="soal[0][opsi_d]" class="form-control" placeholder="Pilihan Jawaban D" required>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-semibold">Kunci Jawaban Benar <span class="text-danger">*</span></label>
                                <select name="soal[0][kunci_jawaban]" class="form-select rounded-3" required>
                                    <option value="a">A</option>
                                    <option value="b">B</option>
                                    <option value="c">C</option>
                                    <option value="d">D</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-semibold">Bobot Poin Soal</label>
                                <input type="number" name="soal[0][poin]" class="form-control rounded-3" value="10" min="5" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow">
                        <i class="bi bi-check-circle me-1"></i> Simpan & Publikasikan Game
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let soalCount = 1;
    const container = document.getElementById('soalContainer');
    const btnAdd = document.getElementById('btnAddSoal');

    if (!container || !btnAdd) return;

    btnAdd.addEventListener('click', function() {
        soalCount++;
        const idx = soalCount - 1;
        const html = `
            <div class="card p-4 rounded-4 border bg-light position-relative soal-item" data-index="${idx}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold fs-6">Soal #${soalCount}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-remove-soal" title="Hapus Soal">
                        <i class="bi bi-trash3 fs-5"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Teks Pertanyaan <span class="text-danger">*</span></label>
                    <textarea name="soal[${idx}][pertanyaan]" class="form-control rounded-3" rows="2" placeholder="Tuliskan soal pertanyaan game..." required></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">A</span>
                            <input type="text" name="soal[${idx}][opsi_a]" class="form-control" placeholder="Pilihan Jawaban A" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">B</span>
                            <input type="text" name="soal[${idx}][opsi_b]" class="form-control" placeholder="Pilihan Jawaban B" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">C</span>
                            <input type="text" name="soal[${idx}][opsi_c]" class="form-control" placeholder="Pilihan Jawaban C" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">D</span>
                            <input type="text" name="soal[${idx}][opsi_d]" class="form-control" placeholder="Pilihan Jawaban D" required>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 col-12">
                        <label class="form-label small fw-semibold">Kunci Jawaban Benar <span class="text-danger">*</span></label>
                        <select name="soal[${idx}][kunci_jawaban]" class="form-select rounded-3" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-12">
                        <label class="form-label small fw-semibold">Bobot Poin Soal</label>
                        <input type="number" name="soal[${idx}][poin]" class="form-control rounded-3" value="10" min="5" max="100" required>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        attachRemoveHandlers();
    });

    function attachRemoveHandlers() {
        container.querySelectorAll('.btn-remove-soal').forEach(btn => {
            btn.onclick = function() {
                const items = container.querySelectorAll('.soal-item');
                if (items.length <= 1) {
                    alert('Game Edukasi harus memiliki minimal 1 soal.');
                    return;
                }
                btn.closest('.soal-item').remove();
            };
        });
    }

    attachRemoveHandlers();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
