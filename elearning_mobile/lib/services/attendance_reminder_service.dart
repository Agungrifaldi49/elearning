import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../views/shared/kartu_digital_screen.dart';

class AttendanceReminderService {
  static Future<void> checkAndShowReminder({
    required BuildContext context,
    required bool isGuru,
    required bool hasClockedInToday,
    required bool hasClockedOutToday,
    bool isAbsentToday = false,
  }) async {
    // If student/teacher is marked Sakit, Izin, or Alpha today, do not show check-in or check-out popup dialogs
    if (isAbsentToday) return;

    final now = DateTime.now();
    final todayStr = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
    final prefs = await SharedPreferences.getInstance();

    final lastEntryReminder = prefs.getString('last_entry_reminder_$todayStr');
    final lastExitReminder = prefs.getString('last_exit_reminder_$todayStr');

    // 1. Belum Absen Masuk Reminder (Entry check-in)
    if (!hasClockedInToday && lastEntryReminder == null) {
      await prefs.setString('last_entry_reminder_$todayStr', 'shown');
      if (context.mounted) {
        _showEntryReminderDialog(context, isGuru);
      }
      return;
    }

    // 2. Pengingat Absen Pulang (Exit check-out when clocked in but not clocked out)
    if (hasClockedInToday && !hasClockedOutToday && lastExitReminder == null) {
      await prefs.setString('last_exit_reminder_$todayStr', 'shown');
      if (context.mounted) {
        _showExitReminderDialog(context, isGuru);
      }
      return;
    }
  }

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
                        style: TextButton.styleFrom(
                          foregroundColor: Colors.grey.shade600,
                        ),
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
                            MaterialPageRoute(
                              builder: (_) => const KartuDigitalScreen(),
                            ),
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
                        style: TextButton.styleFrom(
                          foregroundColor: Colors.grey.shade600,
                        ),
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
                            MaterialPageRoute(
                              builder: (_) => const KartuDigitalScreen(),
                            ),
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
