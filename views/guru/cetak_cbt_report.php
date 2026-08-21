<?php
/**
 * Cetak Laporan & Rekap Nilai CBT - SMK Muthia Harapan Cicalengka
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Evaluation Quiz & Ujian CBT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1e293b;
            background: #f8fafc;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .table-print th, .table-print td {
            border: 1px solid #cbd5e1 !important;
            padding: 8px 12px;
            font-size: 0.88rem;
        }
        .table-print th {
            background-color: #f1f5f9 !important;
            color: #0f172a;
            font-weight: 700;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
        }
    </style>
</head>
<body class="py-4">

<div class="container bg-white p-4 p-md-5 rounded-4 shadow-sm border">
    
    <!-- Action Bar (No Print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print bg-light p-3 rounded-3 border">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-printer-fill me-2 text-primary"></i>Mode Cetak / Simpan PDF Laporan CBT</h5>
            <small class="text-muted">Dokumen telah diformat secara otomatis untuk dicetak atau disimpan sebagai berkas PDF.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4 rounded-pill">
                <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary fw-semibold px-3 rounded-pill">
                Tutup Window
            </button>
        </div>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="kop-surat d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-3 p-3 text-center fw-bold fs-3 shadow-xs" style="width:64px; height:64px;">
            🎓
        </div>
        <div>
            <h4 class="fw-bold mb-0 text-uppercase font-heading" style="letter-spacing: -0.5px;">SMK MUTHIA HARAPAN CICALENGKA</h4>
            <p class="mb-0 small text-muted">Sistem Manajemen Pembelajaran Digital (LMS) & Evaluation Center CBT Online</p>
            <small class="text-secondary">Jl. Raya Cicalengka, Kabupaten Bandung, Jawa Barat | Telepon: (022) 1234567 | Website: smkmuthiaharapancicalengka.my.id</small>
        </div>
    </div>

    <?php if ($reportQuizId !== 'all' && !empty($quizReportDetail)): ?>
        <!-- DOKUMEN CETAK PER QUIZ -->
        <?php 
        $qInfo = $quizReportDetail['quiz'];
        $rData = $quizReportDetail['report_data'];
        ?>

        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark text-uppercase mb-1">LAPORAN HASIL EVALUASI QUIZ / UJIAN CBT</h4>
            <h5 class="fw-semibold text-primary"><?= htmlspecialchars($qInfo['judul']) ?></h5>
        </div>

        <table class="table table-borderless small mb-4 bg-light rounded-3 p-2 border">
            <tr>
                <td style="width:150px;" class="fw-bold text-muted">Mata Pelajaran</td>
                <td>: <strong><?= htmlspecialchars($qInfo['nama_mapel']) ?></strong></td>
                <td style="width:150px;" class="fw-bold text-muted">Durasi Pengerjaan</td>
                <td>: <?= $qInfo['durasi_menit'] ?> Menit</td>
            </tr>
            <tr>
                <td class="fw-bold text-muted">Target Kelas</td>
                <td>: <strong><?= htmlspecialchars($qInfo['nama_kelas']) ?></strong></td>
                <td class="fw-bold text-muted">Kategori Ujian</td>
                <td>: <?= strtoupper($qInfo['kategori'] ?? 'Kuis') ?></td>
            </tr>
            <tr>
                <td class="fw-bold text-muted">Statistik Peserta</td>
                <td>: <?= $quizReportDetail['total_submitted'] ?> Submit dari <?= $quizReportDetail['total_siswa'] ?> Siswa</td>
                <td class="fw-bold text-muted">Rata-Rata Class</td>
                <td>: <strong><?= $quizReportDetail['avg_score'] ?> Poin</strong></td>
            </tr>
        </table>

        <table class="table table-print align-middle mb-4">
            <thead>
                <tr>
                    <th class="text-center" style="width:40px;">No</th>
                    <th>NISN / NIS</th>
                    <th>Nama Lengkap Siswa</th>
                    <th>Kelas</th>
                    <th class="text-center">Status Pengerjaan</th>
                    <th class="text-center">Attempt</th>
                    <th class="text-center">Waktu Submit</th>
                    <th class="text-center">Nilai Quiz</th>
                    <th class="text-center">Status KKM (70)</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($rData as $item): 
                    $st = $item['siswa'];
                    $sub = $item['submission'];
                    $score = $item['score'];
                    $status = $item['status'];
                ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++ ?></td>
                        <td class="font-monospace small"><?= htmlspecialchars($st['nisn'] ?: ($st['nis'] ?: '-')) ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($st['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($st['nama_kelas']) ?></td>
                        <td class="text-center">
                            <?= $sub ? 'Sudah Mengerjakan' : 'Belum Mengerjakan' ?>
                        </td>
                        <td class="text-center"><?= $sub ? $sub['attempt_count'] . 'x' : '-' ?></td>
                        <td class="text-center small"><?= $sub && !empty($sub['finished_at']) ? date('d/m/Y H:i', strtotime($sub['finished_at'])) : '-' ?></td>
                        <td class="text-center fw-bold fs-6">
                            <?= $score !== null ? number_format($score, 1) : '-' ?>
                        </td>
                        <td class="text-center fw-bold">
                            <?php if ($status === 'lulus'): ?>
                                <span class="text-success">LULUS</span>
                            <?php elseif ($status === 'tidak_lulus'): ?>
                                <span class="text-danger">BELUM TUNTAS</span>
                            <?php elseif ($status === 'menunggu_essay'): ?>
                                <span class="text-warning">PERLU KOREKSI</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- DOKUMEN CETAK REKAPITULASI MATRIX ALL QUIZ -->
        <?php 
        $mQuizzes = $rekapCbtMatrix['quizzes'] ?? [];
        $mMatrix = $rekapCbtMatrix['matrix'] ?? [];
        ?>

        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark text-uppercase mb-1">REKAPITULASI MATRIX HASIL SELURUH QUIZ & UJIAN CBT</h4>
            <h5 class="fw-semibold text-primary">Tahun Ajaran <?= date('Y') ?> / <?= date('Y') + 1 ?></h5>
        </div>

        <table class="table table-print align-middle mb-4">
            <thead>
                <tr>
                    <th class="text-center" style="width:40px;">No</th>
                    <th>NISN / NIS</th>
                    <th>Nama Lengkap Siswa</th>
                    <th>Kelas</th>
                    <?php foreach ($mQuizzes as $qzHeader): ?>
                        <th class="text-center px-2" style="min-width:90px;">
                            <?= htmlspecialchars($qzHeader['judul']) ?>
                        </th>
                    <?php endforeach; ?>
                    <th class="text-center bg-light">RATA-RATA NILAI AKHIR</th>
                    <th class="text-center">PREDIKAT</th>
                </tr>
            </thead>
            <tbody>
                <?php $noM = 1; foreach ($mMatrix as $rowM): 
                    $stM = $rowM['siswa'];
                    $scMap = $rowM['scores'];
                    $finalAvg = $rowM['final_avg'];
                    $predikat = $rowM['predikat'];
                    $predikatLabel = $rowM['predikat_label'];
                ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $noM++ ?></td>
                        <td class="font-monospace small"><?= htmlspecialchars($stM['nisn'] ?: ($stM['nis'] ?: '-')) ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($stM['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($stM['nama_kelas']) ?></td>
                        <?php foreach ($mQuizzes as $qzHeader): 
                            $qId = $qzHeader['id'];
                            $val = $scMap[$qId] ?? null;
                        ?>
                            <td class="text-center fw-semibold">
                                <?= $val !== null ? number_format($val, 1) : '-' ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-center fw-extrabold text-primary fs-6 bg-light">
                            <?= number_format($finalAvg, 1) ?>
                        </td>
                        <td class="text-center fw-bold">
                            <?= $predikat ?> (<?= $predikatLabel ?>)
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

    <!-- Tanda Tangan & Legalisasi Berkas -->
    <div class="row pt-4 mt-4 text-center">
        <div class="col-6">
            <p class="small text-muted mb-5">Mengetahui,<br>Waka Kurikulum / Kepala Sekolah</p>
            <p class="fw-bold text-dark text-decoration-underline mb-0">( ________________________ )</p>
            <small class="text-muted">NIP: _____________________</small>
        </div>
        <div class="col-6">
            <p class="small text-muted mb-5">Cicalengka, <?= date('d F Y') ?><br>Guru Pengajar Mata Pelajaran</p>
            <p class="fw-bold text-dark text-decoration-underline mb-0">( <?= htmlspecialchars(AuthHelper::user()['nama_lengkap'] ?? 'Guru Pengajar') ?> )</p>
            <small class="text-muted">NIP: <?= htmlspecialchars(AuthHelper::user()['nip'] ?? '-') ?></small>
        </div>
    </div>

</div>

<script>
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 500);
};
</script>
</body>
</html>
