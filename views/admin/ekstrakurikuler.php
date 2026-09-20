<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-activity text-primary me-2"></i>Manajemen Ekstrakurikuler & Penilaian E-Rapor
                </h4>
                <p class="text-muted small mb-0">
                    Kelola kegiatan ekstrakurikuler sekolah, tentukan pembimbing (Guru terdaftar atau Orang Luar), dan input nilai deskripsi capaian untuk E-Rapor Digital siswa.
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if (!empty($selectedEkskul)): ?>
                    <a href="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Ekskul
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahEkskul">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Ekstrakurikuler
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?= FlashHelper::display() ?>

        <?php if (!empty($selectedEkskul)): ?>
            <!-- =========================================================================
                 TAMPILAN DETAIL ANGGOTA & PENILAIAN DESKRIPSI CAPAIAN UNTUK E-RAPOR
            ========================================================================= -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4">
                                <i class="bi bi-trophy-fill fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($selectedEkskul['nama_ekskul']) ?></h5>
                                <span class="badge <?= $selectedEkskul['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?> rounded-pill px-2.5 py-1 mt-1">
                                    <?= ucfirst($selectedEkskul['status']) ?>
                                </span>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush small mb-3">
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Tipe Pembimbing:</span>
                                <strong>
                                    <?= $selectedEkskul['tipe_pembimbing'] === 'luar' ? '<span class="badge bg-warning text-dark">Orang Luar / Eksternal</span>' : '<span class="badge bg-info text-dark">Guru Terdaftar</span>' ?>
                                </strong>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Nama Pembimbing:</span>
                                <strong class="text-dark">
                                    <?= htmlspecialchars($selectedEkskul['tipe_pembimbing'] === 'luar' ? ($selectedEkskul['nama_pembimbing_luar'] ?: 'Belum diisi') : ($selectedEkskul['nama_guru'] ?: 'Belum ditentukan')) ?>
                                </strong>
                            </li>
                            <?php if (!empty($selectedEkskul['kontak_pembimbing'])): ?>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Kontak / No. HP:</span>
                                <strong><?= htmlspecialchars($selectedEkskul['kontak_pembimbing']) ?></strong>
                            </li>
                            <?php endif; ?>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Jadwal Latihan:</span>
                                <strong><?= htmlspecialchars($selectedEkskul['hari'] ?: '-') ?> (<?= htmlspecialchars($selectedEkskul['jam'] ?: '-') ?>)</strong>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Tempat / Lokasi:</span>
                                <strong><?= htmlspecialchars($selectedEkskul['tempat'] ?: '-') ?></strong>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Total Anggota:</span>
                                <strong class="text-primary fs-6"><?= count($anggotaList) ?> Siswa</strong>
                            </li>
                        </ul>

                        <?php if (!empty($selectedEkskul['deskripsi'])): ?>
                            <div class="p-3 bg-light rounded-3 text-muted small mb-3">
                                <i class="bi bi-info-circle me-1"></i><?= nl2br(htmlspecialchars($selectedEkskul['deskripsi'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Form Tambah Siswa Anggota Manual -->
                        <div class="mt-auto border-top pt-3">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-person-plus-fill text-primary me-1"></i> Tambah Anggota Siswa</h6>
                            <form action="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" method="POST" class="d-flex flex-column gap-2">
                                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                <input type="hidden" name="action" value="add_anggota_manual">
                                <input type="hidden" name="ekskul_id" value="<?= $selectedEkskul['id'] ?>">
                                
                                <select name="siswa_id" class="form-select form-select-sm rounded-3" required>
                                    <option value="">-- Pilih Siswa yang Belum Terdaftar --</option>
                                    <?php foreach ($availableSiswa as $as): ?>
                                        <option value="<?= $as['id'] ?>">
                                            <?= htmlspecialchars($as['nama_lengkap']) ?> (<?= htmlspecialchars($as['nama_kelas'] ?? 'Kelas -') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                                    <i class="bi bi-plus me-1"></i> Tambahkan Siswa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-pencil-square text-success me-1"></i> Penilaian Deskripsi Capaian E-Rapor
                                </h5>
                                <small class="text-muted">
                                    Format nilai berupa predikat & narasi deskripsi capaian yang akan tampil pada lembar E-Rapor Digital siswa.
                                </small>
                            </div>
                        </div>

                        <?php if (empty($anggotaList)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada siswa yang bergabung dalam ekstrakurikuler ini.<br>
                                Siswa dapat mendaftar sendiri melalui akun siswa atau admin dapat menambahkannya secara manual.
                            </div>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                <input type="hidden" name="action" value="save_nilai_deskripsi">
                                <input type="hidden" name="ekskul_id" value="<?= $selectedEkskul['id'] ?>">

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-3" style="font-size: 0.82rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 40px;" class="text-center">No</th>
                                                <th style="min-width: 160px;">Nama Siswa</th>
                                                <th style="width: 90px;">Kelas</th>
                                                <th style="width: 140px;">Predikat</th>
                                                <th style="min-width: 250px;">Nilai / Deskripsi Capaian E-Rapor</th>
                                                <th style="width: 50px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($anggotaList as $idx => $ang): ?>
                                                <tr>
                                                    <td class="text-center text-muted"><?= $idx + 1 ?></td>
                                                    <td>
                                                        <strong class="text-dark d-block"><?= htmlspecialchars($ang['nama_lengkap']) ?></strong>
                                                        <small class="text-muted">NIS: <?= htmlspecialchars($ang['nis'] ?: ($ang['nisn'] ?: '-')) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($ang['nama_kelas'] ?: '-') ?></span>
                                                    </td>
                                                    <td>
                                                        <select name="nilai[<?= $ang['id'] ?>][predikat]" class="form-select form-select-sm rounded-3">
                                                            <option value="Sangat Baik" <?= ($ang['predikat'] ?? '') === 'Sangat Baik' ? 'selected' : '' ?>>Sangat Baik</option>
                                                            <option value="Baik" <?= ($ang['predikat'] ?? '') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                                            <option value="Cukup" <?= ($ang['predikat'] ?? '') === 'Cukup' ? 'selected' : '' ?>>Cukup</option>
                                                            <option value="Kurang" <?= ($ang['predikat'] ?? '') === 'Kurang' ? 'selected' : '' ?>>Kurang</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <textarea name="nilai[<?= $ang['id'] ?>][deskripsi]" rows="2" class="form-control form-control-sm rounded-3" placeholder="Contoh: Aktif dalam kegiatan latihan mingguan dan menunjukkan penguasaan teknik dasar yang sangat baik."><?= htmlspecialchars($ang['nilai_deskripsi'] ?? '') ?></textarea>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" title="Keluarkan Siswa" onclick="confirmRemoveAnggota(<?= $selectedEkskul['id'] ?>, <?= $ang['siswa_id'] ?>, '<?= htmlspecialchars(addslashes($ang['nama_lengkap'])) ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Simpan Seluruh Nilai Deskripsi E-Rapor
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Hidden untuk Hapus Anggota -->
            <form id="formRemoveAnggota" action="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" method="POST" style="display:none;">
                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                <input type="hidden" name="action" value="remove_anggota">
                <input type="hidden" name="ekskul_id" id="removeEkskulId" value="">
                <input type="hidden" name="siswa_id" id="removeSiswaId" value="">
            </form>

        <?php else: ?>
            <!-- =========================================================================
                 TAMPILAN DAFTAR EKSTRAKURIKULER SEKOLAH
            ========================================================================= -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="min-width: 180px;">Nama Ekstrakurikuler</th>
                                <th style="min-width: 220px;">Pembimbing & Kontak</th>
                                <th style="min-width: 150px;">Jadwal & Tempat</th>
                                <th style="width: 110px;" class="text-center">Anggota</th>
                                <th style="width: 90px;" class="text-center">Status</th>
                                <th style="width: 160px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ekskulList)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-activity fs-1 d-block mb-2 text-secondary"></i>
                                        Belum ada kegiatan ekstrakurikuler yang didaftarkan.<br>
                                        Klik tombol <strong>+ Tambah Ekstrakurikuler</strong> untuk menambahkan data baru.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ekskulList as $i => $e): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $i + 1 ?></td>
                                        <td>
                                            <strong class="text-dark d-block fs-6"><?= htmlspecialchars($e['nama_ekskul']) ?></strong>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 260px;">
                                                <?= htmlspecialchars($e['deskripsi'] ?: 'Tidak ada deskripsi') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($e['tipe_pembimbing'] === 'luar'): ?>
                                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                                    <span class="badge bg-warning text-dark" style="font-size: 0.68rem;">Orang Luar / Eksternal</span>
                                                </div>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($e['nama_pembimbing_luar'] ?: '-') ?></strong>
                                            <?php else: ?>
                                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                                    <span class="badge bg-info text-dark" style="font-size: 0.68rem;">Guru Terdaftar</span>
                                                </div>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($e['nama_guru'] ?: 'Belum dipilih') ?></strong>
                                            <?php endif; ?>
                                            <?php if (!empty($e['kontak_pembimbing'])): ?>
                                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($e['kontak_pembimbing']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><i class="bi bi-calendar3 me-1 text-primary"></i><?= htmlspecialchars($e['hari'] ?: '-') ?></div>
                                            <small class="text-muted d-block"><?= htmlspecialchars($e['jam'] ?: '-') ?> &bull; <?= htmlspecialchars($e['tempat'] ?: '-') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1.5 fw-bold">
                                                <i class="bi bi-people me-1"></i><?= (int)$e['total_anggota'] ?> Siswa
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $e['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?> rounded-pill px-2 py-1">
                                                <?= ucfirst($e['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <a href="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler&detail=<?= $e['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1" title="Kelola Anggota & Nilai E-Rapor">
                                                    <i class="bi bi-award me-1"></i> Nilai
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-1.5" title="Edit Data" onclick='editEkskul(<?= json_encode($e) ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1.5" title="Hapus Ekskul" onclick="confirmDeleteEkskul(<?= $e['id'] ?>, '<?= htmlspecialchars(addslashes($e['nama_ekskul'])) ?>')">
                                                    <i class="bi bi-trash"></i>
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

            <!-- Form Hidden untuk Hapus Ekskul -->
            <form id="formDeleteEkskul" action="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" method="POST" style="display:none;">
                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                <input type="hidden" name="action" value="delete_ekskul">
                <input type="hidden" name="id" id="deleteEkskulId" value="">
            </form>
        <?php endif; ?>

    </div>
</main>

<!-- =========================================================================
     MODAL TAMBAH EKSTRAKURIKULER
========================================================================= -->
<div class="modal fade" id="modalTambahEkskul" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Ekstrakurikuler Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                <input type="hidden" name="action" value="create_ekskul">
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                            <input type="text" name="nama_ekskul" class="form-control rounded-3" placeholder="Contoh: Pramuka, Paskibra, Futsal, Rohis, English Club" required>
                        </div>

                        <!-- Opsi Pemilihan Pembimbing (Guru vs Luar) -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Tipe Pembimbing <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 p-2.5 bg-light rounded-3 border">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_pembimbing" id="tipeGuruAdd" value="guru" checked onchange="togglePembimbingAdd('guru')">
                                    <label class="form-check-label fw-semibold text-dark" for="tipeGuruAdd">
                                        <i class="bi bi-person-badge text-primary me-1"></i> Guru Terdaftar (Internal)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_pembimbing" id="tipeLuarAdd" value="luar" onchange="togglePembimbingAdd('luar')">
                                    <label class="form-check-label fw-semibold text-dark" for="tipeLuarAdd">
                                        <i class="bi bi-person-walking text-warning me-1"></i> Orang Luar (Eksternal / Pelatih Luar)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Input jika Guru Terdaftar -->
                        <div class="col-md-7" id="boxGuruAdd">
                            <label class="form-label small fw-semibold text-dark">Pilih Guru Pembimbing</label>
                            <select name="guru_id" class="form-select rounded-3">
                                <option value="">-- Pilih Guru Sekolah --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id'] ?>">
                                        <?= htmlspecialchars($g['nama_lengkap']) ?> (NIP: <?= htmlspecialchars($g['nip'] ?: '-') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Input jika Pembimbing Luar -->
                        <div class="col-md-7 d-none" id="boxLuarAdd">
                            <label class="form-label small fw-semibold text-dark">Nama Pembimbing Luar (Orang Luar)</label>
                            <input type="text" name="nama_pembimbing_luar" class="form-control rounded-3" placeholder="Contoh: Kang Asep (Instruktur Silat Luar)">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-dark">No. HP / WhatsApp Pembimbing</label>
                            <input type="text" name="kontak_pembimbing" class="form-control rounded-3" placeholder="Contoh: 081234567890">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Hari Latihan</label>
                            <input type="text" name="hari" class="form-control rounded-3" placeholder="Contoh: Setiap Jumat">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Waktu / Jam Latihan</label>
                            <input type="text" name="jam" class="form-control rounded-3" placeholder="Contoh: 15:30 - 17:00 WIB">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Tempat Latihan</label>
                            <input type="text" name="tempat" class="form-control rounded-3" placeholder="Contoh: Lapangan Utama / Aula">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Deskripsi & Tujuan Kegiatan</label>
                            <textarea name="deskripsi" rows="2" class="form-control rounded-3" placeholder="Jelaskan secara ringkas kegiatan ekskul ini..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Status Kegiatan</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Ekstrakurikuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL EDIT EKSTRAKURIKULER
========================================================================= -->
<div class="modal fade" id="modalEditEkskul" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Ekstrakurikuler
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/ekstrakurikuler" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                <input type="hidden" name="action" value="edit_ekskul">
                <input type="hidden" name="id" id="editEkskulId" value="">

                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                            <input type="text" name="nama_ekskul" id="editNamaEkskul" class="form-control rounded-3" required>
                        </div>

                        <!-- Opsi Pemilihan Pembimbing (Guru vs Luar) -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Tipe Pembimbing <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 p-2.5 bg-light rounded-3 border">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_pembimbing" id="tipeGuruEdit" value="guru" onchange="togglePembimbingEdit('guru')">
                                    <label class="form-check-label fw-semibold text-dark" for="tipeGuruEdit">
                                        <i class="bi bi-person-badge text-primary me-1"></i> Guru Terdaftar (Internal)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_pembimbing" id="tipeLuarEdit" value="luar" onchange="togglePembimbingEdit('luar')">
                                    <label class="form-check-label fw-semibold text-dark" for="tipeLuarEdit">
                                        <i class="bi bi-person-walking text-warning me-1"></i> Orang Luar (Eksternal)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Input jika Guru Terdaftar -->
                        <div class="col-md-7" id="boxGuruEdit">
                            <label class="form-label small fw-semibold text-dark">Pilih Guru Pembimbing</label>
                            <select name="guru_id" id="editGuruId" class="form-select rounded-3">
                                <option value="">-- Pilih Guru Sekolah --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id'] ?>">
                                        <?= htmlspecialchars($g['nama_lengkap']) ?> (NIP: <?= htmlspecialchars($g['nip'] ?: '-') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Input jika Pembimbing Luar -->
                        <div class="col-md-7 d-none" id="boxLuarEdit">
                            <label class="form-label small fw-semibold text-dark">Nama Pembimbing Luar (Orang Luar)</label>
                            <input type="text" name="nama_pembimbing_luar" id="editNamaLuar" class="form-control rounded-3" placeholder="Contoh: Kang Asep">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-dark">No. HP / WhatsApp Pembimbing</label>
                            <input type="text" name="kontak_pembimbing" id="editKontak" class="form-control rounded-3">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Hari Latihan</label>
                            <input type="text" name="hari" id="editHari" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Waktu / Jam Latihan</label>
                            <input type="text" name="jam" id="editJam" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Tempat Latihan</label>
                            <input type="text" name="tempat" id="editTempat" class="form-control rounded-3">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Deskripsi & Tujuan Kegiatan</label>
                            <textarea name="deskripsi" id="editDeskripsi" rows="2" class="form-control rounded-3"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Status Kegiatan</label>
                            <select name="status" id="editStatus" class="form-select rounded-3">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold">Update Ekstrakurikuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePembimbingAdd(tipe) {
    if (tipe === 'luar') {
        document.getElementById('boxGuruAdd').classList.add('d-none');
        document.getElementById('boxLuarAdd').classList.remove('d-none');
    } else {
        document.getElementById('boxGuruAdd').classList.remove('d-none');
        document.getElementById('boxLuarAdd').classList.add('d-none');
    }
}

function togglePembimbingEdit(tipe) {
    if (tipe === 'luar') {
        document.getElementById('boxGuruEdit').classList.add('d-none');
        document.getElementById('boxLuarEdit').classList.remove('d-none');
    } else {
        document.getElementById('boxGuruEdit').classList.remove('d-none');
        document.getElementById('boxLuarEdit').classList.add('d-none');
    }
}

function editEkskul(data) {
    document.getElementById('editEkskulId').value = data.id || '';
    document.getElementById('editNamaEkskul').value = data.nama_ekskul || '';
    document.getElementById('editKontak').value = data.kontak_pembimbing || '';
    document.getElementById('editHari').value = data.hari || '';
    document.getElementById('editJam').value = data.jam || '';
    document.getElementById('editTempat').value = data.tempat || '';
    document.getElementById('editDeskripsi').value = data.deskripsi || '';
    document.getElementById('editStatus').value = data.status || 'aktif';

    if (data.tipe_pembimbing === 'luar') {
        document.getElementById('tipeLuarEdit').checked = true;
        document.getElementById('editNamaLuar').value = data.nama_pembimbing_luar || '';
        togglePembimbingEdit('luar');
    } else {
        document.getElementById('tipeGuruEdit').checked = true;
        document.getElementById('editGuruId').value = data.guru_id || '';
        togglePembimbingEdit('guru');
    }

    const modal = new bootstrap.Modal(document.getElementById('modalEditEkskul'));
    modal.show();
}

function confirmDeleteEkskul(id, nama) {
    if (confirm("Apakah Anda yakin ingin menghapus kegiatan ekstrakurikuler '" + nama + "'? Semua data keanggotaan dan nilai capaian akan dihapus.")) {
        document.getElementById('deleteEkskulId').value = id;
        document.getElementById('formDeleteEkskul').submit();
    }
}

function confirmRemoveAnggota(ekskulId, siswaId, namaSiswa) {
    if (confirm("Keluarkan siswa '" + namaSiswa + "' dari ekstrakurikuler ini?")) {
        document.getElementById('removeEkskulId').value = ekskulId;
        document.getElementById('removeSiswaId').value = siswaId;
        document.getElementById('formRemoveAnggota').submit();
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
