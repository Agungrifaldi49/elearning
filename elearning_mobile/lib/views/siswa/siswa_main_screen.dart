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
  const SiswaMainScreen({super.key, this.initialIndex = 2});

  @override
  State<SiswaMainScreen> createState() => _SiswaMainScreenState();
}

class _SiswaMainScreenState extends State<SiswaMainScreen> {
  late int _currentIndex;

  final List<Widget> _tabs = [
    const SiswaJadwalTab(),
    const SiswaMateriTab(),
    const SiswaDashboardTab(),
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 1,
        title: InkWell(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const EditProfilScreen()));
          },
          borderRadius: BorderRadius.circular(12),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: isDark ? Colors.white24 : AppTheme.secondaryColor.withValues(alpha: 0.15),
                backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
                child: avatarUrl.isEmpty
                    ? Icon(Icons.person, color: isDark ? Colors.white : AppTheme.secondaryColor)
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
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: isDark ? Colors.white : const Color(0xFF0F172A)),
                    ),
                    Text(
                      user?.subTitle ?? 'Siswa SMK MH',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(fontSize: 12, color: isDark ? Colors.white70 : Colors.grey.shade600),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        actions: [
          IconButton(
            icon: Icon(Icons.badge_outlined, color: isDark ? Colors.white : const Color(0xFF0F172A)),
            tooltip: 'Kartu Pelajar Digital',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen()));
            },
          ),
          IconButton(
            icon: Icon(Icons.notifications_outlined, color: isDark ? Colors.white : const Color(0xFF0F172A)),
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
                    child: Icon(Icons.forum_outlined, color: isDark ? Colors.white : const Color(0xFF0F172A)),
                  )
                : Icon(Icons.forum_outlined, color: isDark ? Colors.white : const Color(0xFF0F172A)),
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
                    child: Icon(Icons.chat_bubble_outline, color: isDark ? Colors.white : const Color(0xFF0F172A)),
                  )
                : Icon(Icons.chat_bubble_outline, color: isDark ? Colors.white : const Color(0xFF0F172A)),
            tooltip: 'Pesan & Chat Direct',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaChatScreen()));
            },
          ),
          PopupMenuButton<String>(
            icon: Icon(Icons.more_vert, color: isDark ? Colors.white : const Color(0xFF0F172A)),
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
      bottomNavigationBar: SafeArea(
        top: false,
        child: Container(
          margin: const EdgeInsets.fromLTRB(16, 4, 16, 10),
          height: 68,
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1E293B) : Colors.white,
            borderRadius: BorderRadius.circular(28),
            border: Border.all(
              color: isDark ? Colors.white.withValues(alpha: 0.12) : Colors.grey.shade200,
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: isDark ? 0.4 : 0.08),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
              BoxShadow(
                color: const Color(0xFF10B981).withValues(alpha: 0.1),
                blurRadius: 16,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              // 0. Jadwal
              _buildNavItem(
                index: 0,
                icon: Icons.calendar_month_outlined,
                activeIcon: Icons.calendar_month_rounded,
                label: 'Jadwal',
                badgeCount: unreadJadwal,
                isDark: isDark,
              ),

              // 1. Materi
              _buildNavItem(
                index: 1,
                icon: Icons.menu_book_outlined,
                activeIcon: Icons.menu_book_rounded,
                label: 'Materi',
                badgeCount: unreadMateri,
                isDark: isDark,
              ),

              // 2. Beranda (Center Floating Hero Button)
              _buildCenterHomeNavItem(isDark: isDark),

              // 3. Tugas
              _buildNavItem(
                index: 3,
                icon: Icons.assignment_outlined,
                activeIcon: Icons.assignment_rounded,
                label: 'Tugas',
                badgeCount: unreadTugas,
                isDark: isDark,
              ),

              // 4. CBT Quiz
              _buildNavItem(
                index: 4,
                icon: Icons.quiz_outlined,
                activeIcon: Icons.quiz_rounded,
                label: 'CBT Quiz',
                badgeCount: unreadQuiz,
                isDark: isDark,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required int index,
    required IconData icon,
    required IconData activeIcon,
    required String label,
    required int badgeCount,
    required bool isDark,
  }) {
    final bool isSelected = _currentIndex == index;
    const activeColor = Color(0xFF10B981);
    final Color inactiveColor = isDark ? Colors.white54 : Colors.grey.shade500;

    return InkWell(
      onTap: () {
        setState(() {
          _currentIndex = index;
        });
      },
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeInOut,
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? activeColor.withValues(alpha: 0.12) : Colors.transparent,
          borderRadius: BorderRadius.circular(18),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            badgeCount > 0
                ? Badge(
                    label: Text(
                      '$badgeCount',
                      style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    backgroundColor: Colors.redAccent,
                    child: Icon(
                      isSelected ? activeIcon : icon,
                      color: isSelected ? activeColor : inactiveColor,
                      size: 22,
                    ),
                  )
                : Icon(
                    isSelected ? activeIcon : icon,
                    color: isSelected ? activeColor : inactiveColor,
                    size: 22,
                  ),
            const SizedBox(height: 3),
            AnimatedDefaultTextStyle(
              duration: const Duration(milliseconds: 200),
              style: TextStyle(
                fontSize: isSelected ? 11 : 10.5,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? activeColor : inactiveColor,
              ),
              child: Text(label),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCenterHomeNavItem({required bool isDark}) {
    final bool isSelected = _currentIndex == 2;

    return GestureDetector(
      onTap: () {
        setState(() {
          _currentIndex = 2;
        });
      },
      child: Transform.translate(
        offset: const Offset(0, -6),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              padding: const EdgeInsets.all(3),
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: isSelected
                    ? AppTheme.siswaGradient
                    : LinearGradient(
                        colors: isDark
                            ? [const Color(0xFF334155), const Color(0xFF1E293B)]
                            : [Colors.grey.shade200, Colors.grey.shade300],
                      ),
                boxShadow: isSelected
                    ? [
                        BoxShadow(
                          color: const Color(0xFF10B981).withValues(alpha: 0.45),
                          blurRadius: 14,
                          offset: const Offset(0, 5),
                        ),
                      ]
                    : [],
              ),
              child: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  gradient: isSelected ? AppTheme.siswaGradient : null,
                  color: isSelected ? null : (isDark ? const Color(0xFF1E293B) : Colors.white),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isSelected ? Icons.grid_view_rounded : Icons.grid_view_outlined,
                  color: isSelected ? Colors.white : (isDark ? Colors.white70 : Colors.grey.shade600),
                  size: 22,
                ),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              'Beranda',
              style: TextStyle(
                fontSize: isSelected ? 11 : 10.5,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? const Color(0xFF10B981) : (isDark ? Colors.white54 : Colors.grey.shade500),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
