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
import '../shared/pengumuman_auto_slider.dart';
import 'guru_bank_soal_screen.dart';
import 'guru_input_absensi_screen.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_key_mapel_screen.dart';
import 'guru_recap_absensi_screen.dart';
import 'guru_scan_qr_screen.dart';

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
    final user = Provider.of<AuthProvider>(context).currentUser;
    final guruProvider = Provider.of<GuruProvider>(context);
    
    final guruData = (guruProvider.dashboardData?['guru'] as Map?) ?? {};
    final namaGuru = (guruData['nama_lengkap'] ?? user?.fullName ?? 'Guru').toString();
    final nipGuru = (guruData['nip'] ?? '-').toString();
    
    final stats = (guruProvider.dashboardData?['stats'] as Map?) ?? {'materi': 0, 'tugas': 0, 'quiz': 0, 'siswa_terdaftar': 0};
    final jadwalToday = (guruProvider.dashboardData?['jadwal_hari_ini'] as List?) ?? [];
    final pengumuman = (guruProvider.dashboardData?['pengumuman'] as List?) ?? [];
    final materiTerbaru = (guruProvider.dashboardData?['materi_terbaru'] as List?) ?? [];
    
    final activeTa = (guruProvider.dashboardData?['active_ta'] as Map?) ?? {};
    final taTahun = (activeTa['tahun_ajaran'] ?? activeTa['tahun'] ?? '').toString();
    final taSem = (activeTa['semester'] ?? '').toString();
    final tahunAjaranStr = (guruProvider.dashboardData?['tahun_ajaran'] ?? (taTahun.isNotEmpty ? "T.A. $taTahun — Semester $taSem" : 'T.A. 2025/2026 — Semester Ganjil')).toString();

    // Complete Features List for Guru
    final allFeatures = [
      _buildFeatureGridItem(
        icon: Icons.vpn_key_rounded,
        label: 'Kode Key Mapel',
        color: const Color(0xFF38BDF8),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruKeyMapelScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.edit_note_rounded,
        label: 'Input Nilai',
        color: const Color(0xFF818CF8),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruInputNilaiScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.how_to_reg_rounded,
        label: 'Presensi Manual',
        color: const Color(0xFF34D399),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruInputAbsensiScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.bar_chart_rounded,
        label: 'Rekap Presensi',
        color: const Color(0xFF60A5FA),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruRecapAbsensiScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.qr_code_scanner_rounded,
        label: 'Scan QR Presensi',
        color: const Color(0xFF2DD4BF),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruScanQRScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.inventory_2_rounded,
        label: 'Bank Soal CBT',
        color: const Color(0xFFA78BFA),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruBankSoalScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.videocam_rounded,
        label: 'Live Meeting',
        color: const Color(0xFFF472B6),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LiveClassScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.badge_rounded,
        label: 'Kartu Guru',
        color: const Color(0xFF3B82F6),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.menu_book_rounded,
        label: 'Perpustakaan',
        color: const Color(0xFF38BDF8),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LibraryScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.sports_esports_rounded,
        label: 'EduGame',
        color: const Color(0xFFC084FC),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EduGameScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.help_outline_rounded,
        label: 'Panduan LMS',
        color: const Color(0xFF94A3B8),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PanduanScreen())),
      ),
      _buildFeatureGridItem(
        icon: Icons.person_rounded,
        label: 'Edit Profil',
        color: const Color(0xFF64748B),
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
                    color: const Color(0xFF0F172A).withValues(alpha: 0.4),
                    blurRadius: 14,
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
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Portal Tenaga Pendidik',
                              style: TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Selamat Datang, $namaGuru',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'NIP: $nipGuru',
                              style: const TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                        ),
                        child: const Icon(Icons.co_present, color: Colors.white, size: 28),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.calendar_month_rounded, color: Colors.white, size: 16),
                        const SizedBox(width: 8),
                        Text(
                          tahunAjaranStr,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            if (!guruProvider.hasClockedInToday) ...[
              const SizedBox(height: 14),
              InkWell(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF1E3A8A), Color(0xFF0F172A), Color(0xFF020617)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF2563EB).withValues(alpha: 0.3)),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF0F172A).withValues(alpha: 0.35),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFF3B82F6).withValues(alpha: 0.25),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.access_time_filled_rounded, color: Color(0xFF60A5FA), size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🔔 Belum Presensi Masuk Guru',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Ketuk untuk membuka Kartu Guru Digital & presensi masuk',
                              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white70, size: 16),
                    ],
                  ),
                ),
              ),
            ] else if (guruProvider.hasClockedInToday && !guruProvider.hasClockedOutToday) ...[
              const SizedBox(height: 14),
              InkWell(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF1D4ED8), Color(0xFF0F172A), Color(0xFF020617)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF3B82F6).withValues(alpha: 0.3)),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF0F172A).withValues(alpha: 0.35),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFF60A5FA).withValues(alpha: 0.25),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.home_work_rounded, color: Color(0xFF93C5FD), size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🏠 Pengingat Presensi Pulang Guru',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Ketuk untuk membuka Kartu Guru Digital & presensi pulang',
                              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white70, size: 16),
                    ],
                  ),
                ),
              ),
            ] else ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: isDark
                        ? [const Color(0xFF0F172A), const Color(0xFF1E3A8A).withValues(alpha: 0.5)]
                        : [const Color(0xFFEFF6FF), const Color(0xFFDBEAFE)],
                  ),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: isDark ? const Color(0xFF1E3A8A) : const Color(0xFFBFDBFE),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(Icons.check_circle_rounded, color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF2563EB), size: 22),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        '🎉 Presensi Masuk & Pulang Anda hari ini telah LENGKAP',
                        style: TextStyle(
                          color: isDark ? Colors.white : const Color(0xFF1E3A8A),
                          fontWeight: FontWeight.bold,
                          fontSize: 12.5,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 20),

            // 4 KPI Stat Cards Grid (Matching Web Dashboard)
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 2.2,
              children: [
                _buildStatCard(
                  title: 'Materi Upload',
                  count: stats['materi'].toString(),
                  icon: Icons.upload_file_rounded,
                  color: const Color(0xFF38BDF8),
                  isDark: isDark,
                ),
                _buildStatCard(
                  title: 'Tugas Active',
                  count: stats['tugas'].toString(),
                  icon: Icons.task_alt_rounded,
                  color: const Color(0xFF34D399),
                  isDark: isDark,
                ),
                _buildStatCard(
                  title: 'Quiz / CBT',
                  count: stats['quiz'].toString(),
                  icon: Icons.quiz_rounded,
                  color: const Color(0xFFA78BFA),
                  isDark: isDark,
                ),
                _buildStatCard(
                  title: 'Siswa Terdaftar',
                  count: (stats['siswa_terdaftar'] ?? 0).toString(),
                  icon: Icons.people_alt_rounded,
                  color: const Color(0xFF60A5FA),
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
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.check_circle_outline_rounded, color: Colors.green.shade400, size: 36),
                        const SizedBox(height: 8),
                        Text(
                          'Tidak ada jadwal mengajar hari ini.',
                          style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.grey.shade600, fontWeight: FontWeight.w500),
                        ),
                      ],
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
                  final jamMulai = j['jam_mulai']?.toString().substring(0, 5) ?? '';
                  final jamSelesai = j['jam_selesai']?.toString().substring(0, 5) ?? '';

                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
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
                      subtitle: Text("Kelas: ${j['nama_kelas']} • Waktu: $jamMulai - $jamSelesai WIB"),
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

            // Recent Materi Section (Preview)
            if (materiTerbaru.isNotEmpty) ...[
              const SizedBox(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '📚 Materi Terbaru Saya',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.blue.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${materiTerbaru.length} Modul',
                      style: const TextStyle(color: Colors.blue, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: materiTerbaru.length > 3 ? 3 : materiTerbaru.length,
                itemBuilder: (context, index) {
                  final m = materiTerbaru[index] as Map;
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    child: ListTile(
                      leading: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.menu_book_rounded, color: Colors.blue, size: 20),
                      ),
                      title: Text(
                        m['judul']?.toString() ?? '',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Text(
                        "${m['nama_mapel'] ?? 'Mapel'} • ${m['nama_kelas'] ?? 'Kelas Target'}",
                        style: TextStyle(fontSize: 11, color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                      ),
                    ),
                  );
                },
              ),
            ],

            const SizedBox(height: 24),

            // Announcements Section (Auto Slider Carousel)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '📢 Pengumuman Sekolah',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                if (pengumuman.isNotEmpty)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${pengumuman.length} Informasi',
                      style: const TextStyle(color: AppTheme.primaryColor, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            PengumumanAutoSlider(pengumumanList: pengumuman),
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
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkSurface : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark
              ? const Color(0xFF1E3A8A).withValues(alpha: 0.4)
              : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.04),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  count,
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color),
                ),
                const SizedBox(height: 1),
                Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark ? const Color(0xFF94A3B8) : Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          ),
        ],
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
