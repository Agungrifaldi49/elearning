import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';
import 'guru_absensi_tab.dart';

class GuruDashboardTab extends StatefulWidget {
  const GuruDashboardTab({super.key});

  @override
  State<GuruDashboardTab> createState() => _GuruDashboardTabState();
}

class _GuruDashboardTabState extends State<GuruDashboardTab> {
  @override
  void initState() {
    super.initState();
    _loadData();
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchDashboard(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final stats = guruProvider.dashboardData?['stats'] ?? {'materi': 0, 'tugas': 0, 'quiz': 0};
    final jadwalToday = guruProvider.dashboardData?['jadwal_hari_ini'] as List? ?? [];

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
                    color: Colors.blue.withOpacity(0.3),
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
                          color: Colors.white.withOpacity(0.2),
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

            const SizedBox(height: 24),

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

            const SizedBox(height: 24),

            // Quick Action Button
            ElevatedButton.icon(
              onPressed: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruAbsensiTab()));
              },
              icon: const Icon(Icons.how_to_reg_rounded),
              label: const Text('Input Presensi Kehadiran Siswa Hari Ini'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 50),
                backgroundColor: AppTheme.primaryColor,
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
                          color: AppTheme.primaryColor.withOpacity(0.1),
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
                          color: Colors.blue.withOpacity(0.1),
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
              color: Colors.black.withOpacity(0.04),
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
}
