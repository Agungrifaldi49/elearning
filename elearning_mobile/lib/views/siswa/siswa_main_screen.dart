import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../theme/app_theme.dart';
import '../auth/login_screen.dart';
import '../shared/edugame_screen.dart';
import '../shared/kartu_digital_screen.dart';
import '../shared/library_screen.dart';
import 'siswa_dashboard_tab.dart';
import 'siswa_jadwal_tab.dart';
import 'siswa_materi_tab.dart';
import 'siswa_tugas_tab.dart';
import 'siswa_cbt_tab.dart';
import 'siswa_absensi_tab.dart';
import 'siswa_nilai_tab.dart';
import 'siswa_forum_screen.dart';
import 'siswa_chat_screen.dart';
import 'gabung_kelas_screen.dart';
import '../shared/notifications_screen.dart';
import '../shared/edit_profil_screen.dart';
import '../../services/attendance_reminder_service.dart';

import '../../providers/siswa_provider.dart';

class SiswaMainScreen extends StatefulWidget {
  final int initialIndex;
  const SiswaMainScreen({super.key, this.initialIndex = 0});

  @override
  State<SiswaMainScreen> createState() => _SiswaMainScreenState();
}

class _SiswaMainScreenState extends State<SiswaMainScreen> {
  late int _currentIndex;

  final List<Widget> _tabs = [
    const SiswaDashboardTab(),
    const SiswaJadwalTab(),
    const SiswaMateriTab(),
    const SiswaTugasTab(),
    const SiswaCbtTab(),
  ];

  void _showLogoutConfirmationDialog(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (dialogContext) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
          elevation: 12,
          backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
          child: Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              gradient: LinearGradient(
                colors: isDark
                    ? [const Color(0xFF1E293B), const Color(0xFF0F172A)]
                    : [Colors.white, Colors.red.shade50.withValues(alpha: 0.4)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: const LinearGradient(
                      colors: [Color(0xFFEF4444), Color(0xFFDC2626)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.redAccent.withValues(alpha: 0.35),
                        blurRadius: 18,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.power_settings_new_rounded,
                    color: Colors.white,
                    size: 38,
                  ),
                ),
                const SizedBox(height: 18),
                Text(
                  'Konfirmasi Keluar',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.outfit(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : const Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Apakah Anda yakin ingin keluar dari akun MHC E-Learning Mobile?',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    height: 1.4,
                    color: isDark ? Colors.white70 : Colors.grey.shade700,
                    fontWeight: FontWeight.w400,
                  ),
                ),
                const SizedBox(height: 18),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF334155) : Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark ? Colors.blue.shade700.withValues(alpha: 0.3) : Colors.blue.shade200,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.shield_outlined,
                        size: 20,
                        color: isDark ? Colors.blue.shade300 : Colors.blue.shade800,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Sesi Anda akan diakhiri. Data KBM & progres belajar Anda tetap tersimpan aman.',
                          style: GoogleFonts.inter(
                            fontSize: 11.5,
                            height: 1.35,
                            color: isDark ? Colors.blue.shade100 : Colors.blue.shade900,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 22),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(dialogContext),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          side: BorderSide(
                            color: isDark ? Colors.white24 : Colors.grey.shade400,
                          ),
                        ),
                        child: Text(
                          'Batal',
                          style: GoogleFonts.outfit(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white70 : Colors.grey.shade800,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          Navigator.pop(dialogContext);
                          await Provider.of<AuthProvider>(context, listen: false).logout();
                          if (!mounted) return;

                          Navigator.of(context).pushAndRemoveUntil(
                            PageRouteBuilder(
                              transitionDuration: const Duration(milliseconds: 400),
                              pageBuilder: (context, animation, secondaryAnimation) => const LoginScreen(),
                              transitionsBuilder: (context, animation, secondaryAnimation, child) {
                                final fadeIn = Tween<double>(begin: 0.0, end: 1.0).animate(
                                  CurvedAnimation(parent: animation, curve: Curves.easeOut),
                                );
                                final slideUp = Tween<Offset>(
                                  begin: const Offset(0.0, 0.06),
                                  end: Offset.zero,
                                ).animate(
                                  CurvedAnimation(parent: animation, curve: Curves.easeOutCubic),
                                );

                                return FadeTransition(
                                  opacity: fadeIn,
                                  child: SlideTransition(
                                    position: slideUp,
                                    child: child,
                                  ),
                                );
                              },
                            ),
                            (route) => false,
                          );
                        },
                        icon: const Icon(Icons.logout_rounded, size: 18, color: Colors.white),
                        label: Text(
                          'Ya, Keluar',
                          style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          backgroundColor: const Color(0xFFEF4444),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          elevation: 4,
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

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
      if (user != null) {
        final siswaProvider = Provider.of<SiswaProvider>(context, listen: false);
        siswaProvider.loadSeenState();
        siswaProvider.fetchDashboard(user.id);
        siswaProvider.fetchJadwal(user.id);
        siswaProvider.fetchMateri(user.id);
        siswaProvider.fetchQuiz(user.id);
        siswaProvider.fetchTugas(user.id);
        siswaProvider.fetchForumSilent(user.id);
        siswaProvider.fetchChatContactsSilent(user.id);
        siswaProvider.fetchAbsensi(user.id).then((_) {
          if (mounted) {
            AttendanceReminderService.checkAndShowReminder(
              context: context,
              isGuru: false,
              hasClockedInToday: siswaProvider.hasClockedInToday,
              hasClockedOutToday: siswaProvider.hasClockedOutToday,
              isAbsentToday: siswaProvider.isAbsentToday,
            );
          }
        });
        siswaProvider.startRealtimeSync(user.id);
      }
    });
  }

  @override
  void dispose() {
    Provider.of<SiswaProvider>(context, listen: false).stopRealtimeSync();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final avatarUrl = user?.fullAvatarUrl ?? '';
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final unreadJadwal = siswaProvider.unreadJadwalCount;
    final unreadMateri = siswaProvider.unreadMateriCount;
    final unreadTugas = siswaProvider.unreadTugasCount;
    final unreadQuiz = siswaProvider.unreadQuizCount;
    final unreadForum = siswaProvider.unreadForumCount;
    final unreadChat = siswaProvider.unreadChatCount;

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: InkWell(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const EditProfilScreen()));
          },
          borderRadius: BorderRadius.circular(12),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: AppTheme.secondaryColor.withValues(alpha: 0.2),
                backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
                child: avatarUrl.isEmpty
                    ? const Icon(Icons.person, color: AppTheme.secondaryColor)
                    : null,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user?.fullName ?? 'Siswa',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      user?.subTitle ?? 'Siswa SMK MH',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.badge_outlined),
            tooltip: 'Kartu Pelajar Digital',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen()));
            },
          ),
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            tooltip: 'Pusat Notifikasi',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsScreen()));
            },
          ),
          IconButton(
            icon: unreadForum > 0
                ? Badge(
                    label: Text('$unreadForum', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                    backgroundColor: Colors.amber.shade800,
                    child: const Icon(Icons.forum_outlined),
                  )
                : const Icon(Icons.forum_outlined),
            tooltip: 'Forum Diskusi Komunitas',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaForumScreen()));
            },
          ),
          IconButton(
            icon: unreadChat > 0
                ? Badge(
                    label: Text('$unreadChat', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                    backgroundColor: Colors.red,
                    child: const Icon(Icons.chat_bubble_outline),
                  )
                : const Icon(Icons.chat_bubble_outline),
            tooltip: 'Pesan & Chat Direct',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaChatScreen()));
            },
          ),
          PopupMenuButton<String>(
            onSelected: (value) async {
              if (value == 'logout') {
                _showLogoutConfirmationDialog(context);
              } else if (value == 'profil') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const EditProfilScreen()));
              } else if (value == 'kartu') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen()));
              } else if (value == 'gabung_kelas') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const GabungKelasScreen()));
              } else if (value == 'library') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const LibraryScreen()));
              } else if (value == 'game') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const EduGameScreen()));
              } else if (value == 'absensi') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaAbsensiTab()));
              } else if (value == 'nilai') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaNilaiTab()));
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'profil', child: Row(children: [Icon(Icons.person_outline_rounded, size: 20, color: Colors.blue), SizedBox(width: 10), Text('Edit & Update Profil')])),
              const PopupMenuItem(value: 'gabung_kelas', child: Row(children: [Icon(Icons.key_rounded, size: 20, color: Colors.amber), SizedBox(width: 10), Text('Gabung Rombel & Key Mapel')])),
              const PopupMenuItem(value: 'kartu', child: Row(children: [Icon(Icons.badge_outlined, size: 20, color: Colors.purple), SizedBox(width: 10), Text('Kartu Pelajar Digital')])),
              const PopupMenuItem(value: 'library', child: Row(children: [Icon(Icons.local_library_outlined, size: 20, color: Colors.deepOrange), SizedBox(width: 10), Text('Perpustakaan Digital')])),
              const PopupMenuItem(value: 'game', child: Row(children: [Icon(Icons.sports_esports_outlined, size: 20, color: Colors.pink), SizedBox(width: 10), Text('EduGame & Kuis Interaktif')])),
              const PopupMenuItem(value: 'absensi', child: Row(children: [Icon(Icons.qr_code_scanner_rounded, size: 20, color: Colors.green), SizedBox(width: 10), Text('Presensi Kehadiran')])),
              const PopupMenuItem(value: 'nilai', child: Row(children: [Icon(Icons.grade_outlined, size: 20, color: Colors.teal), SizedBox(width: 10), Text('Rekap Nilai & Raport')])),
              const PopupMenuDivider(),
              const PopupMenuItem(value: 'logout', child: Row(children: [Icon(Icons.logout_rounded, size: 20, color: Colors.red), SizedBox(width: 10), Text('Keluar / Logout', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold))])),
            ],
          ),
        ],
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: _tabs,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
          },
          type: BottomNavigationBarType.fixed,
          selectedItemColor: AppTheme.secondaryColor,
          unselectedItemColor: Colors.grey,
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          items: [
            const BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_outlined),
              activeIcon: Icon(Icons.dashboard),
              label: 'Beranda',
            ),
            BottomNavigationBarItem(
              icon: unreadJadwal > 0
                  ? Badge(
                      label: Text('$unreadJadwal', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.calendar_month_outlined),
                    )
                  : const Icon(Icons.calendar_month_outlined),
              activeIcon: unreadJadwal > 0
                  ? Badge(
                      label: Text('$unreadJadwal', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.calendar_month),
                    )
                  : const Icon(Icons.calendar_month),
              label: 'Jadwal',
            ),
            BottomNavigationBarItem(
              icon: unreadMateri > 0
                  ? Badge(
                      label: Text('$unreadMateri', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.book_outlined),
                    )
                  : const Icon(Icons.book_outlined),
              activeIcon: unreadMateri > 0
                  ? Badge(
                      label: Text('$unreadMateri', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.book),
                    )
                  : const Icon(Icons.book),
              label: 'Materi',
            ),
            BottomNavigationBarItem(
              icon: unreadTugas > 0
                  ? Badge(
                      label: Text('$unreadTugas', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.assignment_outlined),
                    )
                  : const Icon(Icons.assignment_outlined),
              activeIcon: unreadTugas > 0
                  ? Badge(
                      label: Text('$unreadTugas', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.assignment),
                    )
                  : const Icon(Icons.assignment),
              label: 'Tugas',
            ),
            BottomNavigationBarItem(
              icon: unreadQuiz > 0
                  ? Badge(
                      label: Text('$unreadQuiz', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.quiz_outlined),
                    )
                  : const Icon(Icons.quiz_outlined),
              activeIcon: unreadQuiz > 0
                  ? Badge(
                      label: Text('$unreadQuiz', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                      backgroundColor: Colors.red,
                      child: const Icon(Icons.quiz),
                    )
                  : const Icon(Icons.quiz),
              label: 'CBT Quiz',
            ),
          ],
        ),
      ),
    );
  }
}
