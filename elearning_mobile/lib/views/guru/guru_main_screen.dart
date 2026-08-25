import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';
import '../auth/login_screen.dart';
import '../shared/edugame_screen.dart';
import '../shared/kartu_digital_screen.dart';
import '../shared/library_screen.dart';
import 'guru_dashboard_tab.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_jadwal_tab.dart';
import 'guru_materi_tab.dart';
import 'guru_tugas_tab.dart';
import 'guru_cbt_tab.dart';
import 'guru_absensi_tab.dart';
import '../siswa/siswa_forum_screen.dart';
import '../siswa/siswa_chat_screen.dart';
import '../shared/edit_profil_screen.dart';
import '../../services/attendance_reminder_service.dart';

class GuruMainScreen extends StatefulWidget {
  const GuruMainScreen({super.key});

  @override
  State<GuruMainScreen> createState() => _GuruMainScreenState();
}

class _GuruMainScreenState extends State<GuruMainScreen> {
  int _currentIndex = 0;

  final List<Widget> _tabs = [
    const GuruDashboardTab(),
    const GuruJadwalTab(),
    const GuruMateriTab(),
    const GuruTugasTab(),
    const GuruCbtTab(),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
      if (user != null) {
        final guruProvider = Provider.of<GuruProvider>(context, listen: false);
        guruProvider.fetchDashboard(user.id);
        guruProvider.fetchQuiz(user.id);
        guruProvider.fetchSusulanRequests(user.id);
        guruProvider.fetchTugas(user.id);
        guruProvider.fetchForumSilent(user.id);
        guruProvider.fetchChatContactsSilent(user.id);
        guruProvider.startRealtimeSync(user.id);
        
        Future.delayed(const Duration(milliseconds: 1500), () {
          if (mounted) {
            AttendanceReminderService.checkAndShowReminder(
              context: context,
              isGuru: true,
              hasClockedInToday: guruProvider.hasClockedInToday,
              hasClockedOutToday: guruProvider.hasClockedOutToday,
            );
          }
        });
      }
    });
  }

  @override
  void dispose() {
    Provider.of<GuruProvider>(context, listen: false).stopRealtimeSync();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final avatarUrl = user?.fullAvatarUrl ?? '';
    final guruProvider = Provider.of<GuruProvider>(context);
    final unreadForum = guruProvider.unreadForumCount;
    final unreadChat = guruProvider.unreadChatCount;

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
                backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.2),
                backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
                child: avatarUrl.isEmpty
                    ? const Icon(Icons.co_present, color: AppTheme.primaryColor)
                    : null,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user?.fullName ?? 'Guru',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      user?.subTitle ?? 'Guru SMK MH',
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
            tooltip: 'Kartu Guru Digital',
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
              } else if (value == 'input_nilai') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruInputNilaiScreen()));
              } else if (value == 'absensi') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruAbsensiTab()));
              } else if (value == 'library') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const LibraryScreen()));
              } else if (value == 'game') {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const EduGameScreen()));
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'profil', child: Row(children: [Icon(Icons.person, size: 20), SizedBox(width: 8), Text('Edit & Update Profil')])),
              const PopupMenuItem(value: 'kartu', child: Text('Kartu Guru Digital')),
              const PopupMenuItem(value: 'input_nilai', child: Text('Input & Edit Nilai Siswa')),
              const PopupMenuItem(value: 'absensi', child: Text('Input Absensi Kelas')),
              const PopupMenuItem(value: 'library', child: Text('Perpustakaan Digital')),
              const PopupMenuItem(value: 'game', child: Text('EduGame & Kuis Interaktif')),
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
          selectedItemColor: AppTheme.primaryColor,
          unselectedItemColor: Colors.grey,
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          items: [
            const BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_outlined),
              activeIcon: Icon(Icons.dashboard),
              label: 'Dashboard',
            ),
            const BottomNavigationBarItem(
              icon: Icon(Icons.calendar_month_outlined),
              activeIcon: Icon(Icons.calendar_month),
              label: 'Jadwal',
            ),
            const BottomNavigationBarItem(
              icon: Icon(Icons.upload_file_outlined),
              activeIcon: Icon(Icons.upload_file),
              label: 'Materi',
            ),
            const BottomNavigationBarItem(
              icon: Icon(Icons.task_outlined),
              activeIcon: Icon(Icons.task),
              label: 'Tugas',
            ),
            BottomNavigationBarItem(
              icon: Consumer<GuruProvider>(
                builder: (context, guruProvider, child) {
                  final pendingCount = guruProvider.susulanList
                      .where((e) => (e['status'] ?? '') == 'pending')
                      .length;
                  if (pendingCount > 0) {
                    return Badge(
                      label: Text('$pendingCount'),
                      backgroundColor: Colors.amber.shade900,
                      child: const Icon(Icons.quiz_outlined),
                    );
                  }
                  return const Icon(Icons.quiz_outlined);
                },
              ),
              activeIcon: Consumer<GuruProvider>(
                builder: (context, guruProvider, child) {
                  final pendingCount = guruProvider.susulanList
                      .where((e) => (e['status'] ?? '') == 'pending')
                      .length;
                  if (pendingCount > 0) {
                    return Badge(
                      label: Text('$pendingCount'),
                      backgroundColor: Colors.amber.shade900,
                      child: const Icon(Icons.quiz),
                    );
                  }
                  return const Icon(Icons.quiz);
                },
              ),
              label: 'CBT Quiz',
            ),
          ],
        ),
      ),
    );
  }
}
