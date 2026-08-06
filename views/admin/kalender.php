<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar3 text-primary me-2"></i>Kalender Akademik 1 Tahun Ajaran</h4>
            <p class="text-muted small mb-0">Pengelolaan jadwal resmi KBM, Ujian (UTS/UAS/UKK), Libur Nasional, dan Event Sekolah.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddEvent">
            <i class="bi bi-plus-circle me-1"></i> Tambah Event Baru
        </button>
    </div>

    <div class="row g-4">
        <!-- Calendar Main Widget -->
        <div class="col-12 col-lg-8">
            <div class="card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2 text-primary"></i>Tampilan Kalender Interaktif</h6>
                    <small class="text-muted">Klik event pada kalender untuk melihat rincian & opsi ubah data.</small>
                </div>
                <div id="mainCalendar" style="min-height: 580px;"></div>
            </div>
        </div>

        <!-- Right Side: Upcoming Events & Edit Panel -->
        <div class="col-12 col-lg-4">
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Daftar Event (<?= count($events) ?>)</h6>
                    <span class="badge bg-primary">TA 2025/2026</span>
                </div>
                <div class="timeline overflow-auto pe-1" style="max-height: 480px;">
                    <?php if (empty($events)): ?>
                        <p class="text-muted small text-center py-4">Belum ada event kalender akademik yang ditambahkan.</p>
                    <?php else: ?>
                        <?php foreach ($events as $ev):
                            $colorMap = ['ujian'=>'danger','libur'=>'warning','event'=>'success','kegiatan'=>'info'];
                            $color = $colorMap[$ev['type'] ?? 'event'] ?? 'primary';
                        ?>
                            <div class="timeline-item mb-3">
                                <div class="timeline-dot bg-<?= $color ?>"></div>
                                <div class="card-custom p-3 border shadow-sm rounded-3">
                                    <div class="d-flex justify-content-between align-items-start gap-1">
                                        <div class="fw-bold small text-dark"><?= htmlspecialchars($ev['title']) ?></div>
                                        <span class="badge bg-<?= $color ?> text-uppercase" style="font-size:.62rem;"><?= ucfirst($ev['type'] ?? 'event') ?></span>
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size:.75rem;">
                                        <i class="bi bi-calendar-range me-1 text-primary"></i>
                                        <?= date('d M Y', strtotime($ev['tanggal'])) ?>
                                        <?php if (!empty($ev['tanggal_akhir']) && $ev['tanggal_akhir'] !== $ev['tanggal']): ?>
                                            s/d <?= date('d M Y', strtotime($ev['tanggal_akhir'])) ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($ev['deskripsi'])): ?>
                                        <div class="text-muted small mt-1 fst-italic" style="font-size:.72rem;"><?= htmlspecialchars(substr($ev['deskripsi'], 0, 80)) ?></div>
                                    <?php endif; ?>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-1 mt-2 pt-2 border-top justify-content-end">
                                        <button class="btn btn-sm btn-outline-warning text-dark py-0 px-2" style="font-size:0.7rem;" data-bs-toggle="modal" data-bs-target="#modalEditEvent<?= $ev['id'] ?>">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                        <form action="<?= BASE_URL ?>index.php?url=admin/saveKalender" method="POST" onsubmit="return confirm('Hapus event ini dari kalender?');" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.7rem;">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Legend Colors -->
            <div class="card-custom p-3">
                <h6 class="fw-bold mb-2 small"><i class="bi bi-palette me-1"></i>Keterangan Warna Event</h6>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-danger p-2"><i class="bi bi-pencil-fill me-1"></i> Ujian / CBT Evaluasi</span>
                    <span class="badge bg-warning text-dark p-2"><i class="bi bi-sun-fill me-1"></i> Libur Sekolah</span>
                    <span class="badge bg-success p-2"><i class="bi bi-flag-fill me-1"></i> Event Sekolah</span>
                    <span class="badge bg-info text-dark p-2"><i class="bi bi-bookmark-fill me-1"></i> Kegiatan Lain</span>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Modal Add Event -->
<div class="modal fade" id="modalAddEvent" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Event Kalender Akademik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/saveKalender" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Event / Agenda <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Ujian Tengah Semester (UTS) Ganjil" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tanggal Selesai (Opsional)</label>
                            <input type="date" name="tanggal_akhir" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori Event</label>
                        <select name="type" class="form-select">
                            <option value="ujian">Ujian / Evaluasi CBT</option>
                            <option value="event">Event Sekolah & Pembelajaran</option>
                            <option value="libur">Libur Semester & Nasional</option>
                            <option value="kegiatan">Kegiatan Ekstrakurikuler / Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan keterangan detail kegiatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Edit Event -->
<?php foreach ($events as $ev): ?>
    <div class="modal fade" id="modalEditEvent<?= $ev['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Event Kalender</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/saveKalender" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $ev['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Event / Agenda <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ev['title']) ?>" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($ev['tanggal']) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($ev['tanggal_akhir'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kategori Event</label>
                            <select name="type" class="form-select">
                                <option value="ujian" <?= ($ev['type'] ?? '') === 'ujian' ? 'selected' : '' ?>>Ujian / Evaluasi CBT</option>
                                <option value="event" <?= ($ev['type'] ?? '') === 'event' ? 'selected' : '' ?>>Event Sekolah & Pembelajaran</option>
                                <option value="libur" <?= ($ev['type'] ?? '') === 'libur' ? 'selected' : '' ?>>Libur Semester & Nasional</option>
                                <option value="kegiatan" <?= ($ev['type'] ?? '') === 'kegiatan' ? 'selected' : '' ?>>Kegiatan Ekstrakurikuler / Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($ev['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- FullCalendar 6 Script & Initialization -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const colorMap = { ujian:'#DC3545', libur:'#FFC107', event:'#198754', kegiatan:'#0DCAF0' };
    
    const eventsData = <?= json_encode(array_map(fn($e) => [
        'id' => $e['id'],
        'title' => $e['title'],
        'start' => $e['tanggal'],
        'end' => !empty($e['tanggal_akhir']) ? date('Y-m-d', strtotime($e['tanggal_akhir'] . ' +1 day')) : null,
        'backgroundColor' => '#0D6EFD',
        'extendedProps' => [
            'type' => $e['type'] ?? 'event',
            'deskripsi' => $e['deskripsi'] ?? '',
            'tanggal_mulai' => $e['tanggal'],
            'tanggal_akhir' => $e['tanggal_akhir'] ?? ''
        ]
    ], $events ?? []), JSON_UNESCAPED_UNICODE) ?>;

    eventsData.forEach(ev => {
        ev.backgroundColor = colorMap[ev.extendedProps?.type] || '#0D6EFD';
        ev.borderColor = ev.backgroundColor;
    });

    const calendarEl = document.getElementById('mainCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        events: eventsData,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'multiMonthYear,dayGridMonth,timeGridWeek,listYear'
        },
        buttonText: {
            today: 'Hari Ini',
            multiMonthYear: '1 Tahun Ajaran',
            dayGridMonth: 'Bulan',
            timeGridWeek: 'Minggu',
            listYear: 'Daftar 1 Tahun'
        },
        eventClick: (info) => {
            const ev = info.event;
            const props = ev.extendedProps || {};
            const eventId = ev.id;
            
            Swal.fire({
                title: ev.title,
                html: `
                    <div class="text-start">
                        <span class="badge bg-primary text-uppercase mb-2">${props.type || 'event'}</span>
                        <p class="small text-muted mb-1"><i class="bi bi-calendar me-1"></i> Mulai: <strong>${props.tanggal_mulai || ev.startStr}</strong></p>
                        ${props.tanggal_akhir ? `<p class="small text-muted mb-2"><i class="bi bi-calendar-check me-1"></i> Selesai: <strong>${props.tanggal_akhir}</strong></p>` : ''}
                        ${props.deskripsi ? `<div class="p-2 bg-light rounded small border">${props.deskripsi}</div>` : ''}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-pencil-square me-1"></i> Edit Event',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    const modalEl = document.getElementById(`modalEditEvent${eventId}`);
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            });
        }
    });
    
    calendar.render();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
