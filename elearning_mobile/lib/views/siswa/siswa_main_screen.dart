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
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final avatarUrl = user?.fullAvatarUrl ?? '';

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
            icon: const Badge(
              smallSize: 8,
              backgroundColor: Colors.amber,
              child: Icon(Icons.forum_outlined),
            ),
            tooltip: 'Forum Diskusi Komunitas',
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaForumScreen()));
            },
          ),
          IconButton(
            icon: const Badge(
              smallSize: 9,
              backgroundColor: Colors.red,
              child: Icon(Icons.chat_bubble_outline),
            ),
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
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_outlined),
              activeIcon: Icon(Icons.dashboard),
              label: 'Beranda',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.calendar_month_outlined),
              activeIcon: Icon(Icons.calendar_month),
              label: 'Jadwal',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.book_outlined),
              activeIcon: Icon(Icons.book),
              label: 'Materi',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.assignment_outlined),
              activeIcon: Icon(Icons.assignment),
              label: 'Tugas',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.quiz_outlined),
              activeIcon: Icon(Icons.quiz),
              label: 'CBT Quiz',
            ),
          ],
        ),
      ),
    );
  }
}
