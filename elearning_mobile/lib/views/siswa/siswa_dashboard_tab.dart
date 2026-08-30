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
import 'siswa_cbt_tab.dart';
import 'siswa_materi_tab.dart';
import 'siswa_nilai_tab.dart';
import 'siswa_tugas_tab.dart';

class SiswaDashboardTab extends StatefulWidget {
  const SiswaDashboardTab({super.key});

  @override
  State<SiswaDashboardTab> createState() => _SiswaDashboardTabState();
}

class _SiswaDashboardTabState extends State<SiswaDashboardTab> {
  bool _isFeaturesExpanded = false;

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
    final user = Provider.of<AuthProvider>(context).currentUser;
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dashboardData = siswaProvider.dashboardData ?? {};

    final activeTa = dashboardData['active_ta'] as Map? ?? {'tahun_ajaran': '2025/2026', 'semester': 'Ganjil'};
    final certStats = dashboardData['cert_stats'] as Map? ?? {'predikat': 'Belum Ada Data', 'presensi_log': '0%', 'evaluasi_lms': '0.0 / 100'};
    final stats = dashboardData['stats'] as Map? ?? {'materi': 0, 'tugas': 0, 'quiz': 0, 'presensi_log': '0%'};
    final tugasTerdekat = dashboardData['tugas_terdekat'] as Map?;
    final chartData = dashboardData['chart_data'] as List? ?? [];
    final pengumuman = dashboardData['pengumuman'] as List? ?? [];
    final jadwalList = dashboardData['jadwal_list'] as List? ?? [];
    final jadwalToday = dashboardData['jadwal_hari_ini'] as List? ?? [];
    final displayedJadwal = jadwalToday.isNotEmpty ? jadwalToday : jadwalList;

    final siswaProf = siswaProvider.siswaProfile ?? dashboardData['siswa_profile'] as Map?;
    final hakInfo = siswaProvider.hakAksesInfo ?? dashboardData['hak_akses_info'] as Map?;

    final namaSiswa = siswaProf?['nama_lengkap'] ?? user?.fullName ?? "Siswa";
    final kelasSiswa = siswaProf?['nama_kelas'] ?? user?.namaKelas ?? "Rombel Kelas";
    final jurusanSiswa = siswaProf?['nama_jurusan'] ?? user?.namaJurusan ?? "SMK Muthia Harapan";
    final nisSiswa = siswaProf?['nis']?.toString() ?? user?.nis ?? "-";
    final nisnSiswa = siswaProf?['nisn']?.toString() ?? user?.nisn ?? "-";
    final statusHakAkses = hakInfo?['hak_akses'] ?? siswaProf?['hak_akses'] ?? user?.hakAkses ?? "Siswa (Aktif)";

    final int rawMateri = (stats['materi'] != null && stats['materi'] is num) ? (stats['materi'] as num).toInt() : 0;
    final int rawTugas = (stats['tugas'] != null && stats['tugas'] is num) ? (stats['tugas'] as num).toInt() : 0;
    final int rawQuiz = (stats['quiz'] != null && stats['quiz'] is num) ? (stats['quiz'] as num).toInt() : 0;

    final displayMateri = (rawMateri > 0)
        ? rawMateri.toString()
        : (siswaProvider.materiList.isNotEmpty ? siswaProvider.materiList.length.toString() : '0');

    final displayTugas = (rawTugas > 0)
        ? rawTugas.toString()
        : (siswaProvider.tugasList.isNotEmpty ? siswaProvider.tugasList.length.toString() : '0');

    final displayQuiz = (rawQuiz > 0)
        ? rawQuiz.toString()
        : (siswaProvider.quizList.isNotEmpty ? siswaProvider.quizList.length.toString() : '0');

    final rawPresensiLog = stats['presensi_log'] ?? certStats['presensi_log'];
    final displayPresensiLog = (rawPresensiLog != null && rawPresensiLog.toString() != 'Belum Ada Data' && rawPresensiLog.toString().isNotEmpty)
        ? rawPresensiLog.toString()
        : '0%';

    // 11 Features List
    final allFeatures = [
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
            // Welcome Hero Card (Matches Web Design & Database Permissions)
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
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.verified_user_rounded, color: Colors.white, size: 13),
                            SizedBox(width: 4),
                            Text(
                              'Portal Pembelajaran Siswa',
                              style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.greenAccent.shade700,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.shield_rounded, color: Colors.white, size: 13),
                            const SizedBox(width: 4),
                            Text(
                              "Hak Akses: $statusHakAkses",
                              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.amber,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          "T.A. ${activeTa['tahun_ajaran']} — ${activeTa['semester']}",
                          style: const TextStyle(color: Colors.black87, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Selamat Datang, $namaSiswa! 👋',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    "Kelas: $kelasSiswa  |  Jurusan: $jurusanSiswa",
                    style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    "NIS: $nisSiswa  |  NISN: $nisnSiswa",
                    style: const TextStyle(color: Colors.white70, fontSize: 11),
                  ),
                ],
              ),
            ),

            // Clock-In / Clock-Out Reminder Bar
            if (!siswaProvider.hasClockedInToday) ...[
              const SizedBox(height: 12),
              InkWell(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
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
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🔔 Belum Presensi Masuk',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Ketuk untuk membuka Kartu Pelajar & presensi masuk',
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
            ] else if (siswaProvider.hasClockedInToday && !siswaProvider.hasClockedOutToday) ...[
              const SizedBox(height: 12),
              InkWell(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KartuDigitalScreen())),
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
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🏠 Pengingat Presensi Pulang',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Ketuk untuk membuka Kartu Pelajar & presensi pulang',
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

            // 4 Real Database KPI Stats Cards (Matches Web)
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.5,
              children: [
                _buildKpiCard(
                  title: 'Materi Dibaca',
                  count: displayMateri,
                  subtitle: 'Tersedia Rombel',
                  icon: Icons.menu_book_rounded,
                  color: Colors.blue,
                  isDark: isDark,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
                    appBar: AppBar(title: const Text('Materi Pembelajaran'), backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
                    body: const SiswaMateriTab(),
                  ))),
                ),
                _buildKpiCard(
                  title: 'Tugas Aktif',
                  count: displayTugas,
                  subtitle: 'Perlu Dikumpulkan',
                  icon: Icons.assignment_rounded,
                  color: Colors.orange,
                  isDark: isDark,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
                    appBar: AppBar(title: const Text('Tugas & Penugasan'), backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
                    body: const SiswaTugasTab(),
                  ))),
                ),
                _buildKpiCard(
                  title: 'Kuis & CBT',
                  count: displayQuiz,
                  subtitle: 'Evaluasi Sekolah',
                  icon: Icons.quiz_rounded,
                  color: Colors.purple,
                  isDark: isDark,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
                    appBar: AppBar(title: const Text('Kuis & CBT Ujian'), backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
                    body: const SiswaCbtTab(),
                  ))),
                ),
                _buildKpiCard(
                  title: 'Presensi Log',
                  count: displayPresensiLog,
                  subtitle: 'Kehadiran Real',
                  icon: Icons.event_available_rounded,
                  color: Colors.teal,
                  isDark: isDark,
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
                    appBar: AppBar(title: const Text('Presensi & Kehadiran'), backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
                    body: const SiswaAbsensiTab(),
                  ))),
                ),
              ],
            ),

            // Nearest Assignment Deadline Widget
            if (tugasTerdekat != null && (tugasTerdekat['judul'] ?? '').isNotEmpty) ...[
              const SizedBox(height: 16),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkSurface : Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.amber.shade300),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.amber.shade700,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.timer_outlined, color: Colors.white, size: 24),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'TENGGAT PENUGASAN TERDEKAT',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.amber),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            tugasTerdekat['judul'] ?? 'Tugas KBM',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            "Deadline: ${tugasTerdekat['deadline'] ?? '-'}",
                            style: const TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.amber.shade700,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SiswaTugasTab())),
                      child: const Text('Kerjakan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            ],

            // Real Predikat & LMS Certificate Summary Card
            const SizedBox(height: 16),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkSurface : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.green.shade200),
                boxShadow: [
                  BoxShadow(
                    color: Colors.green.withValues(alpha: 0.05),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.green.shade50,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(Icons.workspace_premium_rounded, color: Colors.green.shade700, size: 28),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Evaluasi & Predikat Belajar Real',
                          style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          certStats['predikat']?.toString() ?? 'Belum Ada Data',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.green.shade800),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          "Rerata LMS: ${certStats['evaluasi_lms'] ?? '0.0 / 100'}",
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                  OutlinedButton(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.green.shade800,
                      side: BorderSide(color: Colors.green.shade400),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    ),
                    onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SertifikatScreen())),
                    child: const Text('Sertifikat', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),

            // Subject Grade Performance Breakdown (Chart Representation)
            if (chartData.isNotEmpty) ...[
              const SizedBox(height: 20),
              const Text(
                '📊 Rerata Nilai Evaluasi Per-Mapel',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkSurface : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Column(
                  children: chartData.map((cd) {
                    final mapelName = cd['nama_mapel']?.toString() ?? 'Mapel';
                    final avgGrade = double.tryParse(cd['avg_nilai']?.toString() ?? '0') ?? 0.0;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(mapelName, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                              Text(
                                "${avgGrade.toStringAsFixed(1)} / 100",
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(4),
                            child: LinearProgressIndicator(
                              value: (avgGrade / 100).clamp(0.0, 1.0),
                              minHeight: 6,
                              backgroundColor: Colors.grey.shade100,
                              valueColor: AlwaysStoppedAnimation<Color>(
                                avgGrade >= 85 ? Colors.green : (avgGrade >= 75 ? Colors.blue : Colors.orange),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],

            const SizedBox(height: 20),

            // Feature Shortcuts Grid Header & Toggle Button
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '⚡ Fitur Utama Siswa',
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

            // Today's & Weekly Timetable Section (Matches Web Dashboard)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  jadwalToday.isNotEmpty ? '📅 Jadwal Pelajaran Hari Ini' : '📅 Jadwal KBM Rombel Rujukan',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                if (jadwalToday.isNotEmpty)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Hari Ini',
                      style: TextStyle(color: AppTheme.primaryColor, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            if (displayedJadwal.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Center(
                    child: Text(
                      'Belum ada jadwal pelajaran terdaftar untuk rombel Anda 🎉',
                      style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                    ),
                  ),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: displayedJadwal.length,
                itemBuilder: (context, index) {
                  final j = displayedJadwal[index];
                  final hari = j['hari']?.toString() ?? 'Hari';
                  final jamMulai = j['jam_mulai']?.toString() ?? '';
                  final jamSelesai = j['jam_selesai']?.toString() ?? '';
                  final jamStr = (jamMulai.isNotEmpty && jamSelesai.isNotEmpty)
                      ? "${jamMulai.length > 5 ? jamMulai.substring(0, 5) : jamMulai} - ${jamSelesai.length > 5 ? jamSelesai.substring(0, 5) : jamSelesai} WIB"
                      : "Jam KBM";

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
                      title: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            margin: const EdgeInsets.only(right: 6),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              hari,
                              style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                            ),
                          ),
                          Expanded(
                            child: Text(
                              j['nama_mapel'] ?? 'Mata Pelajaran',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                          ),
                        ],
                      ),
                      subtitle: Text(
                        "Guru: ${j['nama_guru'] ?? 'Guru Pengampu'} • $jamStr",
                        style: const TextStyle(fontSize: 12),
                      ),
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          j['ruangan'] ?? 'Ruang KBM',
                          style: const TextStyle(color: Colors.blue, fontSize: 11, fontWeight: FontWeight.bold),
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
                            p['isi_pengumuman'] ?? p['isi'] ?? p['konten'] ?? '',
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

  Widget _buildKpiCard({
    required String title,
    required String count,
    required String subtitle,
    required IconData icon,
    required Color color,
    required bool isDark,
    VoidCallback? onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkSurface : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 8,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    title.toUpperCase(),
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey),
                  ),
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.15),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(icon, color: color, size: 18),
                  ),
                ],
              ),
              Text(
                count,
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color),
              ),
              Text(
                subtitle,
                style: const TextStyle(fontSize: 10, color: Colors.grey),
              ),
            ],
          ),
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
