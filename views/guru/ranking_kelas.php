<?php
/**
 * View Leger Nilai & Ranking Siswa (Kelas Binaan Wali Kelas)
 * Menampilkan rekapitulasi nilai per mata pelajaran, total akumulasi nilai,
 * rata-rata, predikat, dan pemeringkatan prestasi siswa secara otomatis.
 */
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">
        <!-- Breadcrumb & Page Header -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/waliKelas&kelas_id=<?= $selectedKelasId ?>" class="text-decoration-none">Wali Kelas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Leger & Ranking</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-trophy-fill text-warning me-2"></i>Leger Nilai & Peringkat Siswa
                </h4>
                <p class="text-muted small mb-0">
                    Rekapitulasi nilai capaian hasil belajar per mata pelajaran, total skor, rata-rata, dan pemeringkatan siswa rombel <strong><?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?></strong>.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace small">
                    <i class="bi bi-calendar3 me-1"></i> TA <?= htmlspecialchars($activeTa['tahun'] ?? 'Aktif') ?> (<?= htmlspecialchars($activeSemester) ?>)
                </span>
                <a href="<?= BASE_URL ?>index.php?url=guru/waliKelas&kelas_id=<?= $selectedKelasId ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small fw-semibold shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Kelas Binaan
                </a>
                <button type="button" onclick="exportTableToExcel('tabelLegerRanking', 'Leger_Nilai_<?= preg_replace('/[^a-zA-Z0-9_-]/', '_', $selectedKelas['nama_kelas'] ?? 'Rombel') ?>')" class="btn btn-success rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1.5"></i> Export Excel
                </button>
                <a href="<?= BASE_URL ?>index.php?url=guru/cetakLegerRanking&kelas_id=<?= $selectedKelasId ?>" target="_blank" class="btn btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1.5"></i> Cetak Leger Resmi (Print)
                </a>
            </div>
        </div>

        <?= FlashHelper::display() ?>

        <!-- Selector Rombel Binaan (Jika Wali Kelas membina lebih dari 1 kelas) -->
        <?php if (count($myWaliKelas) > 1): ?>
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-4">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-semibold me-2"><i class="bi bi-collection me-1"></i>Pilih Rombel Binaan:</span>
                    <div class="nav nav-pills gap-2" role="tablist">
                        <?php foreach ($myWaliKelas as $mwk): ?>
                            <?php $isSelected = ((int)$mwk['id'] === (int)$selectedKelasId); ?>
                            <a href="<?= BASE_URL ?>index.php?url=guru/rankingKelas&kelas_id=<?= $mwk['id'] ?>" 
                               class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $isSelected ? 'active bg-primary text-white shadow-sm' : 'bg-light text-dark' ?>">
                                <i class="bi bi-easel-fill me-1"></i><?= htmlspecialchars($mwk['nama_kelas']) ?>
                                <span class="badge <?= $isSelected ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= (int)$mwk['total_siswa'] ?> Siswa</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Top 3 Podium / Bintang Prestasi Rombel -->
        <?php if (!empty($topThree)): ?>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-stars text-warning me-1.5"></i>Bintang Prestasi & 3 Besar Rombel <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?>
                        </h6>
                        <span class="text-muted small">Berdasarkan Akumulasi Nilai Rata-Rata</span>
                    </div>
                </div>

                <?php 
                $podiumStyles = [
                    1 => [
                        'border' => 'border-warning border-2',
                        'bg_badge' => 'bg-warning text-dark',
                        'icon' => 'bi-trophy-fill text-warning',
                        'label' => 'JUARA 1 (PERINGKAT I)',
                        'medal' => '🥇',
                        'card_bg' => 'linear-gradient(135deg, rgba(254, 243, 199, 0.4) 0%, rgba(255, 255, 255, 1) 100%)'
                    ],
                    2 => [
                        'border' => 'border-secondary border-opacity-50 border-2',
                        'bg_badge' => 'bg-secondary text-white',
                        'icon' => 'bi-award-fill text-secondary',
                        'label' => 'JUARA 2 (PERINGKAT II)',
                        'medal' => '🥈',
                        'card_bg' => 'linear-gradient(135deg, rgba(241, 245, 249, 0.6) 0%, rgba(255, 255, 255, 1) 100%)'
                    ],
                    3 => [
                        'border' => 'border-danger border-opacity-25 border-2',
                        'bg_badge' => 'bg-dark-subtle text-dark',
                        'icon' => 'bi-award-fill text-warning-emphasis',
                        'label' => 'JUARA 3 (PERINGKAT III)',
                        'medal' => '🥉',
                        'card_bg' => 'linear-gradient(135deg, rgba(254, 242, 242, 0.4) 0%, rgba(255, 255, 255, 1) 100%)'
                    ]
                ];
                ?>

                <?php foreach ($topThree as $idx => $tt): ?>
                    <?php 
                    $pos = $idx + 1;
                    $style = $podiumStyles[$pos] ?? $podiumStyles[3];
                    ?>
                    <div class="col-12 col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm h-100 <?= $style['border'] ?> overflow-hidden position-relative" style="background: <?= $style['card_bg'] ?>;">
                            <div class="card-body p-3.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge <?= $style['bg_badge'] ?> rounded-pill px-3 py-1.5 fw-bold shadow-xs small">
                                        <i class="bi <?= $style['icon'] ?> me-1"></i><?= $style['label'] ?>
                                    </span>
                                    <span class="fs-4"><?= $style['medal'] ?></span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($tt['siswa']['nama_lengkap'] ?? '') ?>">
                                    <?= htmlspecialchars($tt['siswa']['nama_lengkap'] ?? '-') ?>
                                </h5>
                                <div class="text-muted small mb-3">
                                    <span class="font-monospace">NIS: <?= htmlspecialchars($tt['siswa']['nis'] ?? '-') ?></span>
                                    <?php if (!empty($tt['siswa']['nisn'])): ?>
                                        <span class="mx-1">•</span><span class="font-monospace">NISN: <?= htmlspecialchars($tt['siswa']['nisn']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="row g-2 pt-2 border-top">
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Rata-Rata Nilai</small>
                                        <span class="fs-4 fw-extrabold text-primary font-monospace"><?= number_format($tt['rata_rata'], 2) ?></span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Total Skor (<?= $tt['total_mapels'] ?> Mapel)</small>
                                        <span class="fs-5 fw-bold text-dark font-monospace"><?= number_format($tt['total_nilai'], 1) ?></span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <span class="badge <?= $tt['predikat']['class'] ?? 'bg-primary' ?> rounded-pill px-2.5 py-1">
                                        Predikat: <?= htmlspecialchars($tt['predikat']['grade'] ?? '-') ?>
                                    </span>
                                    <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporSiswa&siswa_id=<?= $tt['siswa']['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                                        <i class="bi bi-printer me-1"></i> Rapor
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Stat Cards Ringkasan Nilai & Ketuntasan Kelas -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Jumlah Peserta Didik</span>
                            <h5 class="fw-bold text-dark mb-0"><?= $countSiswa ?> <span class="fs-6 fw-normal text-muted">Siswa</span></h5>
                            <small class="text-primary fw-semibold" style="font-size: 0.72rem;"><?= count($mapelList) ?> Mata Pelajaran</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-award-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Rata-Rata Kelas Rombel</span>
                            <h5 class="fw-bold text-dark mb-0"><?= number_format($rombelAvgTotal, 2) ?></h5>
                            <small class="text-warning fw-semibold" style="font-size: 0.72rem;">Skala 100 E-Rapor</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Nilai Rata-rata Tertinggi</span>
                            <h5 class="fw-bold text-dark mb-0"><?= number_format($highestAvg, 2) ?></h5>
                            <small class="text-muted" style="font-size: 0.72rem;">Terendah: <?= number_format($lowestAvg, 2) ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Ketuntasan Penuh Rombel</span>
                            <h5 class="fw-bold text-dark mb-0"><?= $totalSiswaTuntasPenuh ?> / <?= $countSiswa ?> <span class="fs-6 fw-normal text-muted">(<?= $persenTuntasRombel ?>%)</span></h5>
                            <small class="text-info fw-semibold" style="font-size: 0.72rem;">Tuntas Semua Mapel</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Tabel Leger Matriks Nilai & Ranking -->
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom p-3.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-table text-primary me-2"></i>Tabel Leger Nilai & Ranking Kelas
                    </h5>
                    <span class="text-muted small">
                        Daftar lengkap perolehan nilai mata pelajaran, akumulasi skor, rata-rata, dan urutan prestasi peserta didik.
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="cariSiswaLeger" class="form-control bg-light border-start-0" placeholder="Cari nama atau NIS siswa...">
                    </div>
                </div>
            </div>

            <?php if (empty($rankingData)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-exclamation-circle fs-1 d-block mb-3 text-secondary"></i>
                    <h6>Belum Ada Siswa Terdaftar</h6>
                    <p class="small mb-0">Tidak ada data siswa pada rombel <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?>.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="max-height: 700px;">
                    <table class="table table-hover align-middle mb-0 text-nowrap" id="tabelLegerRanking" style="font-size: 0.8rem;">
                        <thead class="table-light sticky-top" style="z-index: 2;">
                            <tr class="text-center align-middle">
                                <th class="text-center py-3" style="width: 70px;">Rank</th>
                                <th class="text-start py-3" style="min-width: 110px;">NIS / NISN</th>
                                <th class="text-start py-3" style="min-width: 220px;">Nama Lengkap Siswa</th>
                                
                                <!-- Kolom Dinamis Per Mata Pelajaran -->
                                <?php foreach ($mapelList as $m): ?>
                                    <th class="text-center py-2 px-2" style="min-width: 85px;" title="<?= htmlspecialchars($m['nama_mapel']) ?> (KKM: <?= (float)$m['kkm'] ?>)">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 110px;">
                                            <?= htmlspecialchars(!empty($m['kode_mapel']) ? $m['kode_mapel'] : $m['nama_mapel']) ?>
                                        </div>
                                        <small class="badge bg-secondary-subtle text-secondary border rounded-pill px-1.5 py-0 font-monospace" style="font-size: 0.65rem;">
                                            KKM <?= (float)$m['kkm'] ?>
                                        </small>
                                    </th>
                                <?php endforeach; ?>

                                <th class="text-center py-3 bg-primary-subtle text-primary fw-bold" style="min-width: 95px;">Total Skor</th>
                                <th class="text-center py-3 bg-warning-subtle text-dark fw-bold" style="min-width: 90px;">Rata-Rata</th>
                                <th class="text-center py-3" style="width: 75px;">Predikat</th>
                                <th class="text-center py-3" style="width: 100px;">Ketuntasan</th>
                                <th class="text-center py-3" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rankingData as $row): ?>
                                <?php 
                                $rank = $row['ranking'];
                                $isTop1 = ($rank === 1);
                                $isTop2 = ($rank === 2);
                                $isTop3 = ($rank === 3);

                                $rowClass = '';
                                if ($isTop1) $rowClass = 'table-warning bg-opacity-10';
                                elseif ($isTop2) $rowClass = 'table-light';
                                ?>
                                <tr class="<?= $rowClass ?> siswa-row">
                                    <!-- Kolom Ranking dengan Lencana Elegan -->
                                    <td class="text-center fw-bold">
                                        <?php if ($isTop1): ?>
                                            <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill shadow-xs">
                                                <i class="bi bi-trophy-fill me-1"></i>1
                                            </span>
                                        <?php elseif ($isTop2): ?>
                                            <span class="badge bg-secondary text-white px-2.5 py-1.5 rounded-pill shadow-xs">
                                                <i class="bi bi-award-fill me-1"></i>2
                                            </span>
                                        <?php elseif ($isTop3): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill shadow-xs">
                                                <i class="bi bi-award-fill me-1"></i>3
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill font-monospace">
                                                <?= $rank ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- NIS / NISN -->
                                    <td class="text-start font-monospace text-muted">
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['siswa']['nis'] ?? '-') ?></div>
                                        <?php if (!empty($row['siswa']['nisn'])): ?>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($row['siswa']['nisn']) ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Nama Lengkap Siswa -->
                                    <td class="text-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-light border text-primary fw-bold" style="width: 32px; height: 32px; font-size: 0.78rem;">
                                                <?= strtoupper(substr($row['siswa']['nama_lengkap'] ?? 'S', 0, 1)) ?>
                                            </div>
                                            <div class="text-truncate" style="max-width: 200px;">
                                                <div class="fw-bold text-dark text-truncate nama-siswa-text" title="<?= htmlspecialchars($row['siswa']['nama_lengkap'] ?? '') ?>">
                                                    <?= htmlspecialchars($row['siswa']['nama_lengkap'] ?? '-') ?>
                                                </div>
                                                <small class="text-muted font-monospace" style="font-size: 0.68rem;">
                                                    <?= $row['tuntas_count'] ?>/<?= $row['total_mapels'] ?> Mapel Tuntas
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Nilai Per Mata Pelajaran -->
                                    <?php foreach ($mapelList as $m): ?>
                                        <?php 
                                        $mId = (int)$m['id'];
                                        $nItem = $row['nilai_mapel'][$mId] ?? null;
                                        $val = (float)($nItem['nilai'] ?? 0);
                                        $kkm = (float)$m['kkm'];
                                        $isTuntas = ($val >= $kkm);
                                        $hasScore = !empty($nItem['has_score']);
                                        ?>
                                        <td class="text-center font-monospace">
                                            <?php if (!$hasScore && $val <= 0): ?>
                                                <span class="text-muted opacity-50">-</span>
                                            <?php elseif ($isTuntas): ?>
                                                <span class="fw-semibold text-dark"><?= number_format($val, 1) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 rounded-pill" title="Remedial (< KKM <?= $kkm ?>)">
                                                    <?= number_format($val, 1) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <!-- Total Skor Nilai Rombel -->
                                    <td class="text-center font-monospace fw-bold bg-primary-subtle text-primary">
                                        <?= number_format($row['total_nilai'], 1) ?>
                                    </td>

                                    <!-- Rata-Rata Nilai Rombel -->
                                    <td class="text-center font-monospace fw-extrabold bg-warning-subtle text-dark fs-6">
                                        <?= number_format($row['rata_rata'], 2) ?>
                                    </td>

                                    <!-- Predikat Rata-Rata -->
                                    <td class="text-center">
                                        <span class="badge <?= $row['predikat']['class'] ?? 'bg-primary' ?> rounded-pill px-2.5 py-1">
                                            <?= htmlspecialchars($row['predikat']['grade'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <!-- Status Ketuntasan -->
                                    <td class="text-center">
                                        <?php if ($row['total_mapels'] > 0 && $row['tuntas_count'] === $row['total_mapels']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Tuntas
                                            </span>
                                        <?php elseif ($row['tuntas_count'] > 0): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Remedial
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">
                                                Belum Ada
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Aksi Cetak Perorangan -->
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporSiswa&siswa_id=<?= $row['siswa']['id'] ?>" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2 py-1" title="Cetak E-Rapor Siswa">
                                            <i class="bi bi-printer text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                        <!-- Footer Tabel: Rata-Rata, Nilai Tertinggi, Terendah Per Mapel -->
                        <tfoot class="table-light border-top-2 fw-bold text-center align-middle sticky-bottom">
                            <!-- Baris 1: Rata-Rata Rombel Per Mapel -->
                            <tr class="bg-light">
                                <td colspan="3" class="text-end py-2 px-3 fw-bold text-dark">
                                    <i class="bi bi-calculator me-1 text-primary"></i>Rata-Rata Kelas:
                                </td>
                                <?php foreach ($mapelList as $m): ?>
                                    <?php $spm = $statsPerMapel[$m['id']] ?? ['avg' => 0]; ?>
                                    <td class="text-center font-monospace text-primary fw-bold">
                                        <?= number_format($spm['avg'] ?? 0, 1) ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center font-monospace text-primary bg-primary-subtle">
                                    <?= number_format(array_sum(array_column($statsPerMapel, 'avg')), 1) ?>
                                </td>
                                <td class="text-center font-monospace text-dark bg-warning-subtle fs-6">
                                    <?= number_format($rombelAvgTotal, 2) ?>
                                </td>
                                <td colspan="3"></td>
                            </tr>

                            <!-- Baris 2: Nilai Tertinggi Per Mapel -->
                            <tr class="bg-light text-muted small">
                                <td colspan="3" class="text-end py-1.5 px-3 fw-semibold">
                                    <i class="bi bi-arrow-up-circle text-success me-1"></i>Nilai Tertinggi:
                                </td>
                                <?php foreach ($mapelList as $m): ?>
                                    <?php $spm = $statsPerMapel[$m['id']] ?? ['max' => 0]; ?>
                                    <td class="text-center font-monospace text-success fw-semibold">
                                        <?= number_format($spm['max'] ?? 0, 1) ?>
                                    </td>
                                <?php endforeach; ?>
                                <td colspan="5"></td>
                            </tr>

                            <!-- Baris 3: Nilai Terendah Per Mapel -->
                            <tr class="bg-light text-muted small">
                                <td colspan="3" class="text-end py-1.5 px-3 fw-semibold">
                                    <i class="bi bi-arrow-down-circle text-danger me-1"></i>Nilai Terendah:
                                </td>
                                <?php foreach ($mapelList as $m): ?>
                                    <?php $spm = $statsPerMapel[$m['id']] ?? ['min' => 0]; ?>
                                    <td class="text-center font-monospace text-danger fw-semibold">
                                        <?= number_format($spm['min'] ?? 0, 1) ?>
                                    </td>
                                <?php endforeach; ?>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Catatan Petunjuk Leger & Keterangan Wali Kelas -->
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3.5 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="bi bi-info-circle-fill text-info me-1.5"></i>Pedoman Perhitungan Leger & Peringkat Rombel
                    </h6>
                    <ul class="text-muted small mb-0 ps-3">
                        <li><strong>Akumulasi Total Skor:</strong> Penjumlahan dari nilai akhir seluruh mata pelajaran yang diajarkan pada rombel binaan.</li>
                        <li><strong>Rata-Rata Nilai:</strong> Dihitung dari Total Skor dibagi dengan Total Mata Pelajaran rombel (Skala 0 - 100).</li>
                        <li><strong>Peringkat (Ranking):</strong> Diurutkan secara otomatis dari rata-rata nilai tertinggi ke terendah. Jika terdapat nilai rata-rata yang sama, urutan diperhitungkan berdasarkan total nilai akumulasi.</li>
                        <li><strong>Ketuntasan:</strong> Mata pelajaran dinyatakan tuntas apabila nilai akhir mencapai atau melampaui Kriteria Ketercapaian Tujuan Pembelajaran (KKTP/KKM) yang ditetapkan sekolah.</li>
                    </ul>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?= BASE_URL ?>index.php?url=guru/cetakLegerRanking&kelas_id=<?= $selectedKelasId ?>" target="_blank" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm">
                        <i class="bi bi-printer-fill me-1.5"></i> Cetak Leger Resmi Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Filter Realtime Pencarian Siswa di Tabel Leger
document.getElementById('cariSiswaLeger')?.addEventListener('input', function() {
    const keyword = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.siswa-row');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(keyword) ? '' : 'none';
    });
});

// Export Tabel Leger ke Excel (.xls) Bersih dan Cepat
function exportTableToExcel(tableID, filename = 'Leger_Nilai') {
    const table = document.getElementById(tableID);
    if (!table) return;

    // Clone tabel agar tidak mengubah tampilan asli
    const cloneTable = table.cloneNode(true);
    
    // Hapus kolom aksi di klon
    const actionHeaders = cloneTable.querySelectorAll('th:last-child');
    actionHeaders.forEach(th => th.remove());
    const actionCells = cloneTable.querySelectorAll('td:last-child');
    actionCells.forEach(td => td.remove());

    const html = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Leger Nilai & Ranking</x:Name>
                            <x:WorksheetOptions>
                                <x:DisplayGridlines/>
                            </x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                table { border-collapse: collapse; width: 100%; font-family: sans-serif; font-size: 11pt; }
                th, td { border: 1px solid #999; padding: 6px; }
                th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
            </style>
        </head>
        <body>
            <h3>LEGER NILAI & PERINGKAT SISWA - <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?></h3>
            <p>Tahun Ajaran: <?= htmlspecialchars($activeTa['tahun'] ?? '') ?> | Semester: <?= htmlspecialchars($activeSemester) ?> | Wali Kelas: <?= htmlspecialchars($waliKelas['nama_lengkap'] ?? '') ?></p>
            \${cloneTable.outerHTML}
        </body>
        </html>
    `;

    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const downloadLink = document.createElement("a");
    downloadLink.href = url;
    downloadLink.download = `${filename}.xls`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
