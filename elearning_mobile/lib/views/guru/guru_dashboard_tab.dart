import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';
import '../shared/edit_profil_screen.dart';
import '../shared/edugame_screen.dart';
import '../shared/kartu_digital_screen.dart';
import '../shared/library_screen.dart';
import '../shared/live_class_screen.dart';
import '../shared/panduan_screen.dart';
import 'guru_absensi_tab.dart';
import 'guru_bank_soal_screen.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_key_mapel_screen.dart';
import 'guru_recap_absensi_screen.dart';
import 'guru_scan_qr_screen.dart';
import 'guru_siswa_enrolled_screen.dart';

class GuruDashboardTab extends StatefulWidget {
  const GuruDashboardTab({super.key});

  @override
  State<GuruDashboardTab> createState() => _GuruDashboardTabState();
}

class _GuruDashboardTabState extends State<GuruDashboardTab> {
  bool _isFeaturesExpanded = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchDashboard(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final guruProvider = Provider.of<GuruProvider>(context);
    final stats = (guruProvider.dashboardData?['stats'] as Map?) ?? {'materi': 0, 'tugas': 0, 'quiz': 0};
    final jadwalToday = (guruProvider.dashboardData?['jadwal_hari_ini'] as List?) ?? [];

    // Complete Features List for Guru
    final allFeatures = [
      _buildFeatureGridItem(
        icon: Icons.how_to_reg_rounded,
        label: 'Input Absensi',
        color: Colors.teal,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruAbsensiTab())),
      ),
      _buildFeatureGridItem(
        icon: Icons.people_alt_rounded,
        label: 'Siswa Terdaftar',
        color: Colors.indigo.shade800,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruSiswaEnrolledScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.key_rounded,
        label: 'Key Mapel',
        color: Colors.amber.shade900,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruKeyMapelScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.edit_note_rounded,
        label: 'Input Nilai',
        color: Colors.indigo,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruInputNilaiScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.qr_code_scanner_rounded,
        label: 'Scan QR Presensi',
        color: Colors.teal.shade800,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruScanQRScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.inventory_2_rounded,
        label: 'Bank Soal CBT',
        color: Colors.amber.shade900,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruBankSoalScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.videocam_rounded,
        label: 'Live Meeting',
        color: Colors.red,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LiveClassScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.badge_rounded,
        label: 'Kartu Guru',
        color: Colors.blue.shade700,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.assessment_rounded,
        label: 'Rekap Absensi',
        color: Colors.deepPurple,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruRecapAbsensiScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.menu_book_rounded,
        label: 'Perpustakaan',
        color: Colors.deepOrange,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LibraryScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.sports_esports_rounded,
        label: 'EduGame',
        color: Colors.purple,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EduGameScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.help_outline_rounded,
        label: 'Panduan LMS',
        color: Colors.blueGrey,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PanduanScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.person_rounded,
        label: 'Edit Profil',
        color: Colors.blueGrey.shade700,
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EditProfilScreen())),
      ),
    ];

    final displayedFeatures = _isFeaturesExpanded ? allFeatures : allFeatures.take(6).toList();

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome Header Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: AppTheme.guruGradient,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.blue.withValues(alpha: 0.3),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Portal Tenaga Pendidik',
                            style: TextStyle(color: Colors.white70, fontSize: 13),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Selamat Bertugas, Guru! 👨‍🏫',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Icon(Icons.co_present, color: Colors.white, size: 28),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Kelola Materi, Tugas, Quiz, & Absensi Siswa secara Praktis',
                    style: TextStyle(color: Colors.white, fontSize: 12),
                  ),
                ],
              ),
            ),

            if (!guruProvider.hasClockedInToday) ...[
              const SizedBox(height: 12),
              InkWell(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruAbsensiTab())),
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Colors.amber.shade700, Colors.orange.shade800],
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.amber.shade900.withValues(alpha: 0.3),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: const BoxDecoration(
                          color: Colors.white24,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.access_time_filled_rounded, color: Colors.white, size: 24),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🔔 Belum Presensi Masuk Guru',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Ketuk untuk catat presensi kehadiran mengajar hari ini',
                              style: TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white, size: 16),
                    ],
                  ),
                ),
              ),
            ] else if (guruProvider.hasClockedInToday && !guruProvider.hasClockedOutToday) ...[
              const SizedBox(height: 12),
              InkWell(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruAbsensiTab())),
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Colors.indigo.shade700, Colors.blue.shade900],
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.indigo.shade900.withValues(alpha: 0.3),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: const BoxDecoration(
                          color: Colors.white24,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.home_work_rounded, color: Colors.white, size: 24),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🏠 Pengingat Presensi Pulang Guru',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Jam mengajar selesai (10-15m lagi). Catat presensi pulang!',
                              style: TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white, size: 16),
                    ],
                  ),
                ),
              ),
            ],

            const SizedBox(height: 20),

            // Quick Stats Row
            Row(
              children: [
                _buildStatCard(
                  title: 'Materi Upload',
                  count: stats['materi'].toString(),
                  icon: Icons.upload_file,
                  color: Colors.blue,
                  isDark: isDark,
                ),
                const SizedBox(width: 12),
                _buildStatCard(
                  title: 'Tugas Dibuat',
                  count: stats['tugas'].toString(),
                  icon: Icons.task,
                  color: Colors.amber,
                  isDark: isDark,
                ),
                const SizedBox(width: 12),
                _buildStatCard(
                  title: 'Quiz / CBT',
                  count: stats['quiz'].toString(),
                  icon: Icons.quiz,
                  color: Colors.purple,
                  isDark: isDark,
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Feature Shortcuts Grid Header & Toggle Button
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '⚡ Fitur Pengajaran Guru',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                InkWell(
                  onTap: () {
                    setState(() {
                      _isFeaturesExpanded = !_isFeaturesExpanded;
                    });
                  },
                  borderRadius: BorderRadius.circular(8),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          _isFeaturesExpanded ? 'Sembunyikan' : 'Selengkapnya',
                          style: const TextStyle(
                            color: AppTheme.primaryColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                        Icon(
                          _isFeaturesExpanded ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                          color: AppTheme.primaryColor,
                          size: 18,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Animated Grid Container
            AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              curve: Curves.easeInOut,
              child: GridView.count(
                crossAxisCount: 3,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.05,
                children: displayedFeatures,
              ),
            ),

            const SizedBox(height: 24),

            // Teaching Timetable Today
            const Text(
              '📅 Jadwal Mengajar Hari Ini',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),

            if (jadwalToday.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Center(
                    child: Text(
                      'Tidak ada jadwal mengajar hari ini.',
                      style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                    ),
                  ),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: jadwalToday.length,
                itemBuilder: (context, index) {
                  final j = jadwalToday[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.class_rounded, color: AppTheme.primaryColor),
                      ),
                      title: Text(
                        j['nama_mapel'] ?? '',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text("Kelas: ${j['nama_kelas']} • Waktu: ${j['jam_mulai']} - ${j['jam_selesai']}"),
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          j['ruangan'] ?? 'Ruang Kelas',
                          style: const TextStyle(fontSize: 11, color: Colors.blue, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String count,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkSurface : Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 8),
            Text(
              count,
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 11, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureGridItem({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: color.withValues(alpha: 0.2)),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: color, size: 26),
              const SizedBox(height: 4),
              Text(
                label,
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.bold,
                  fontSize: 11,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
