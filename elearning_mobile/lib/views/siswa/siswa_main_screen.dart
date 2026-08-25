import 'package:flutter/material.dart';
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
import '../shared/edit_profil_screen.dart';
import '../../services/attendance_reminder_service.dart';

import '../../providers/siswa_provider.dart';

class SiswaMainScreen extends StatefulWidget {
  const SiswaMainScreen({super.key});

  @override
  State<SiswaMainScreen> createState() => _SiswaMainScreenState();
}

class _SiswaMainScreenState extends State<SiswaMainScreen> {
  int _currentIndex = 0;

  final List<Widget> _tabs = [
    const SiswaDashboardTab(),
    const SiswaJadwalTab(),
    const SiswaMateriTab(),
    const SiswaTugasTab(),
    const SiswaCbtTab(),
  ];

  @override
  void initState() {
    super.initState();
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
                await Provider.of<AuthProvider>(context, listen: false).logout();
                if (!mounted) return;
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                );
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
              const PopupMenuItem(value: 'profil', child: Row(children: [Icon(Icons.person, size: 20), SizedBox(width: 8), Text('Edit & Update Profil')])),
              const PopupMenuItem(value: 'gabung_kelas', child: Row(children: [Icon(Icons.key, size: 20, color: Colors.amber), SizedBox(width: 8), Text('Gabung Rombel & Key Mapel')])),
              const PopupMenuItem(value: 'kartu', child: Text('Kartu Pelajar Digital')),
              const PopupMenuItem(value: 'library', child: Text('Perpustakaan Digital')),
              const PopupMenuItem(value: 'game', child: Text('EduGame & Kuis Interaktif')),
              const PopupMenuItem(value: 'absensi', child: Text('Presensi Kehadiran')),
              const PopupMenuItem(value: 'nilai', child: Text('Rekap Nilai & Raport')),
              const PopupMenuDivider(),
              const PopupMenuItem(value: 'logout', child: Text('Keluar / Logout', style: TextStyle(color: Colors.red))),
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
