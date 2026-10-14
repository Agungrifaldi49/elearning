import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../views/shared/kartu_digital_screen.dart';
import '../views/guru/guru_input_absensi_screen.dart';

class AttendanceReminderService {
  // ---------------------------------------------------------------------------
  // Pengingat presensi pribadi guru/siswa (masuk & pulang via Kartu Digital)
  // ---------------------------------------------------------------------------

  static Future<void> checkAndShowReminder({
    required BuildContext context,
    required bool isGuru,
    required bool hasClockedInToday,
    required bool hasClockedOutToday,
    bool isAbsentToday = false,
  }) async {
    if (isAbsentToday) return;

    final now = DateTime.now();
    final todayStr = '${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}';
    final prefs = await SharedPreferences.getInstance();

    final lastEntryReminder = prefs.getString('last_entry_reminder_$todayStr');
    final lastExitReminder = prefs.getString('last_exit_reminder_$todayStr');

    // 1. Belum Absen Masuk
    if (!hasClockedInToday && lastEntryReminder == null) {
      await prefs.setString('last_entry_reminder_$todayStr', 'shown');
      if (context.mounted) _showEntryReminderDialog(context, isGuru);
      return;
    }

    // 2. Belum Absen Pulang
    if (hasClockedInToday && !hasClockedOutToday && lastExitReminder == null) {
      await prefs.setString('last_exit_reminder_$todayStr', 'shown');
      if (context.mounted) _showExitReminderDialog(context, isGuru);
      return;
    }
  }

  // ---------------------------------------------------------------------------
  // Pengingat absensi KBM kelas siswa (khusus guru)
  // ---------------------------------------------------------------------------

  /// Cek & tampilkan pop-up pengingat absensi KBM untuk guru.
  /// [reminderData] = GuruProvider.attendanceReminder (null = tidak ada jadwal pending).
  /// Hanya tampilkan satu dialog per pemanggilan; snooze 10 menit per jadwal.
  static Future<void> checkAndShowKBMReminder({
    required BuildContext context,
    required Map<String, dynamic>? reminderData,
  }) async {
    if (reminderData == null) return;
    if (reminderData['has_pending'] != true) return;

    final schedules = reminderData['schedules'];
    if (schedules is! List || schedules.isEmpty) return;

    final now = DateTime.now();
    final todayStr = '${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}';
    final prefs = await SharedPreferences.getInstance();

    for (final sched in schedules) {
      if (sched is! Map) continue;
      final jadwalId = sched['id']?.toString() ?? '0';
      final snoozeKey = 'kbm_reminder_snooze_${todayStr}_$jadwalId';
      final snoozeUntil = prefs.getInt(snoozeKey) ?? 0;

      // Lewati jika masih dalam periode snooze
      if (snoozeUntil > now.millisecondsSinceEpoch) continue;

      if (!context.mounted) return;
      _showKBMReminderDialog(context, prefs, todayStr, sched);
      return; // hanya satu dialog per pemanggilan
    }
  }

  static void _showKBMReminderDialog(
    BuildContext context,
    SharedPreferences prefs,
    String todayStr,
    Map schedData,
  ) {
    final jadwalId = schedData['id']?.toString() ?? '0';
    final namaMapel = schedData['nama_mapel']?.toString() ?? 'Mata Pelajaran';
    final namaKelas = schedData['nama_kelas']?.toString() ?? 'Kelas';
    final jamMulai = schedData['jam_mulai_fmt']?.toString() ?? schedData['jam_mulai']?.toString() ?? '';
    final jamSelesai = schedData['jam_selesai_fmt']?.toString() ?? schedData['jam_selesai']?.toString() ?? '';
    final ruangan = schedData['ruangan']?.toString() ?? '';
    final mapelId = schedData['mapel_id'];

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
          elevation: 16,
          backgroundColor: Colors.transparent,
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              gradient: const LinearGradient(
                colors: [Color(0xFF1E40AF), Color(0xFF3B82F6), Color(0xFF06B6D4)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF3B82F6).withValues(alpha: 0.45),
                  blurRadius: 32,
                  spreadRadius: 4,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Icon
                  Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.18),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.notifications_active_rounded,
                      size: 40,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    '🔔 Waktunya Absensi Kelas!',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Anda memiliki jadwal KBM yang belum diabsensi.',
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.white.withValues(alpha: 0.85),
                      height: 1.4,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  // Info card jadwal
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _infoRow(Icons.book_rounded, namaMapel),
                        const SizedBox(height: 6),
                        _infoRow(Icons.people_alt_rounded, namaKelas),
                        if (jamMulai.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          _infoRow(Icons.schedule_rounded, '$jamMulai – $jamSelesai'),
                        ],
                        if (ruangan.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          _infoRow(Icons.room_rounded, ruangan),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  // Tombol utama: isi absensi
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => GuruInputAbsensiScreen(
                              initialMapelId: mapelId != null ? int.tryParse(mapelId.toString()) : null,
                            ),
                          ),
                        );
                      },
                      icon: const Icon(Icons.how_to_reg_rounded, size: 20),
                      label: const Text(
                        'Isi Absensi Kelas Sekarang',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: const Color(0xFF1E40AF),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 0,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Tombol snooze
                  SizedBox(
                    width: double.infinity,
                    child: TextButton(
                      onPressed: () async {
                        Navigator.pop(ctx);
                        final snoozeKey = 'kbm_reminder_snooze_${todayStr}_$jadwalId';
                        final snoozeUntilMs = DateTime.now()
                            .add(const Duration(minutes: 10))
                            .millisecondsSinceEpoch;
                        await prefs.setInt(snoozeKey, snoozeUntilMs);
                      },
                      style: TextButton.styleFrom(
                        foregroundColor: Colors.white.withValues(alpha: 0.75),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      child: const Text('Ingatkan Lagi 10 Menit Lagi'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  static Widget _infoRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.white.withValues(alpha: 0.85)),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(
              fontSize: 13,
              color: Colors.white,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // Dialog presensi masuk pribadi
  // ---------------------------------------------------------------------------

  static void _showEntryReminderDialog(BuildContext context, bool isGuru) {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          elevation: 10,
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.amber.shade100,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.access_time_filled_rounded,
                    size: 44,
                    color: Colors.amber.shade900,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  isGuru ? '🔔 Pengingat Presensi Masuk Guru' : '🔔 Pengingat Presensi Masuk',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 10),
                Text(
                  'Halo! Anda belum mencatat presensi masuk hari ini. Yuk buka Kartu Digital sekarang untuk mencatat presensi masuk secara resmi!',
                  style: TextStyle(fontSize: 13, color: Colors.grey.shade700, height: 1.4),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: TextButton(
                        onPressed: () => Navigator.pop(context),
                        style: TextButton.styleFrom(foregroundColor: Colors.grey.shade600),
                        child: const Text('Nanti Saja'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      flex: 2,
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const KartuDigitalScreen()),
                          );
                        },
                        icon: const Icon(Icons.qr_code_scanner, size: 18),
                        label: const Text('Kartu Digital', style: TextStyle(fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primaryColor,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // ---------------------------------------------------------------------------
  // Dialog presensi pulang pribadi
  // ---------------------------------------------------------------------------

  static void _showExitReminderDialog(BuildContext context, bool isGuru) {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          elevation: 10,
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.indigo.shade100,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.home_work_rounded,
                    size: 44,
                    color: Colors.indigo.shade900,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  isGuru ? '🏠 Pengingat Presensi Pulang Guru' : '🏠 Pengingat Presensi Pulang',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 10),
                Text(
                  'Waktu kegiatan telah mendekati jam pulang. Jangan lupa untuk membuka Kartu Digital dan mencatat presensi kepulangan Anda hari ini!',
                  style: TextStyle(fontSize: 13, color: Colors.grey.shade700, height: 1.4),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: TextButton(
                        onPressed: () => Navigator.pop(context),
                        style: TextButton.styleFrom(foregroundColor: Colors.grey.shade600),
                        child: const Text('Tutup'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      flex: 2,
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const KartuDigitalScreen()),
                          );
                        },
                        icon: const Icon(Icons.directions_run_rounded, size: 18),
                        label: const Text('Kartu Digital', style: TextStyle(fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.indigo.shade800,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
