import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';
import '../shared/edit_profil_screen.dart';
import '../shared/edugame_screen.dart';
import '../shared/kartu_digital_screen.dart';
import '../shared/library_screen.dart';
import '../shared/live_class_screen.dart';
import '../shared/panduan_screen.dart';
import 'gabung_kelas_screen.dart';
import 'learning_path_screen.dart';
import 'sertifikat_screen.dart';
import 'siswa_absensi_tab.dart';
import 'siswa_nilai_tab.dart';

class SiswaDashboardTab extends StatefulWidget {
  const SiswaDashboardTab({super.key});

  @override
  State<SiswaDashboardTab> createState() => _SiswaDashboardTabState();
}

class _SiswaDashboardTabState extends State<SiswaDashboardTab> {
  @override
  void initState() {
    super.initState();
    _loadData();
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchDashboard(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final stats = siswaProvider.dashboardData?['stats'] ?? {'materi': 0, 'tugas': 0, 'quiz': 0};
    final pengumuman = siswaProvider.dashboardData?['pengumuman'] as List? ?? [];
    final jadwalToday = siswaProvider.dashboardData?['jadwal_hari_ini'] as List? ?? [];

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome Banner Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: AppTheme.siswaGradient,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.secondaryColor.withValues(alpha: 0.3),
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
                            'Portal Siswa Mobile',
                            style: TextStyle(color: Colors.white70, fontSize: 13),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Semangat Belajar! 🚀',
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
                        child: const Icon(Icons.school, color: Colors.white, size: 28),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'SMK Muthia Harapan Cicalengka - LMS Mobile Engine',
                    style: TextStyle(color: Colors.white, fontSize: 12),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Quick Stats Row
            Row(
              children: [
                _buildStatCard(
                  title: 'Materi',
                  count: stats['materi'].toString(),
                  icon: Icons.menu_book_rounded,
                  color: Colors.blue,
                  isDark: isDark,
                ),
                const SizedBox(width: 12),
                _buildStatCard(
                  title: 'Tugas',
                  count: stats['tugas'].toString(),
                  icon: Icons.assignment_rounded,
                  color: Colors.orange,
                  isDark: isDark,
                ),
                const SizedBox(width: 12),
                _buildStatCard(
                  title: 'Quiz/CBT',
                  count: stats['quiz'].toString(),
                  icon: Icons.quiz_rounded,
                  color: Colors.purple,
                  isDark: isDark,
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Feature Shortcuts Grid
            const Text(
              '⚡ Seluruh Fitur Siswa LMS',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            GridView.count(
              crossAxisCount: 3,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.05,
              children: [
                _buildFeatureGridItem(
                  icon: Icons.badge_rounded,
                  label: 'Kartu Pelajar',
                  color: Colors.indigo,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.group_add_rounded,
                  label: 'Gabung Kelas',
                  color: Colors.deepOrange,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GabungKelasScreen())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.videocam_rounded,
                  label: 'Live Meeting',
                  color: Colors.red,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LiveClassScreen())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.menu_book_rounded,
                  label: 'Perpustakaan',
                  color: Colors.blue,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LibraryScreen())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.sports_esports_rounded,
                  label: 'EduGame',
                  color: Colors.purple,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EduGameScreen())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.alt_route_rounded,
                  label: 'Learning Path',
                  color: Colors.deepPurple,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LearningPathScreen())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.check_circle_rounded,
                  label: 'Presensi',
                  color: Colors.teal,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaAbsensiTab())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.grade_rounded,
                  label: 'Rekap Nilai',
                  color: Colors.amber.shade800,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaNilaiTab())),
                ),
                _buildFeatureGridItem(
                  icon: Icons.workspace_premium_rounded,
                  label: 'Sertifikat',
                  color: Colors.amber.shade900,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SertifikatScreen())),
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
              ],
            ),

            const SizedBox(height: 24),

            // Today's Timetable Section
            const Text(
              '📅 Jadwal Pelajaran Hari Ini',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            if (jadwalToday.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Center(
                    child: Text(
                      'Tidak ada jadwal pelajaran hari ini 🎉',
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
                          color: AppTheme.secondaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.access_time_filled, color: AppTheme.secondaryColor),
                      ),
                      title: Text(
                        j['nama_mapel'] ?? '',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text("Guru: ${j['nama_guru']} • ${j['jam_mulai']} - ${j['jam_selesai']}"),
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

            const SizedBox(height: 24),

            // Announcements Section
            const Text(
              '📢 Pengumuman Sekolah',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            if (pengumuman.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Center(
                    child: Text(
                      'Belum ada pengumuman terbaru.',
                      style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                    ),
                  ),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: pengumuman.length,
                itemBuilder: (context, index) {
                  final p = pengumuman[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.campaign, color: Colors.orange, size: 20),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  p['judul'] ?? '',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            p['isi_pengumuman'] ?? p['konten'] ?? '',
                            style: const TextStyle(fontSize: 14),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            "Diterbitkan: ${p['created_at'] ?? ''}",
                            style: const TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                        ],
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
              style: const TextStyle(fontSize: 12, color: Colors.grey),
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
