import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../auth/login_screen.dart';

class KepsekMainScreen extends StatefulWidget {
  const KepsekMainScreen({super.key});

  @override
  State<KepsekMainScreen> createState() => _KepsekMainScreenState();
}

class _KepsekMainScreenState extends State<KepsekMainScreen> {
  int _currentIndex = 0;
  bool _isLoading = true;
  String? _errorMessage;

  Map<String, dynamic>? _dashboardData;
  List<dynamic> _teachersList = [];
  List<dynamic> _classList = [];
  Map<String, dynamic>? _attendanceData;
  List<dynamic> _supervisiList = [];

  @override
  void initState() {
    super.initState();
    _loadAllData();
  }

  Future<void> _loadAllData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final dashRes = await ApiService.get('kepsek/dashboard');
      final guruRes = await ApiService.get('kepsek/guru');
      final siswaRes = await ApiService.get('kepsek/siswa');
      final presRes = await ApiService.get('kepsek/presensi');
      final supRes = await ApiService.get('kepsek/supervisi');

      if (mounted) {
        setState(() {
          if (dashRes['success'] == true) {
            _dashboardData = dashRes['data'];
          }
          if (guruRes['success'] == true && guruRes['data'] != null) {
            _teachersList = guruRes['data']['teachers'] ?? [];
          }
          if (siswaRes['success'] == true && siswaRes['data'] != null) {
            _classList = siswaRes['data']['classes'] ?? [];
          }
          if (presRes['success'] == true) {
            _attendanceData = presRes['data'];
          }
          if (supRes['success'] == true && supRes['data'] != null) {
            _supervisiList = supRes['data']['supervisi_list'] ?? [];
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Gagal memuat data monitoring: $e';
          _isLoading = false;
        });
      }
    }
  }

  void _showLogoutDialog() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.logout_rounded, color: Colors.redAccent),
            const SizedBox(width: 8),
            Text('Keluar Akun', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text(
          'Apakah Anda yakin ingin keluar dari Akun Eksekutif Kepala Sekolah?',
          style: GoogleFonts.inter(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.redAccent,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () async {
              Navigator.pop(dialogCtx);
              final authProvider = Provider.of<AuthProvider>(context, listen: false);
              await authProvider.logout();
              if (!mounted) return;
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
  }

  void _showBroadcastDialog() {
    final titleController = TextEditingController();
    final bodyController = TextEditingController();
    bool isPosting = false;
    final scaffoldMessenger = ScaffoldMessenger.of(context);

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (dialogContext, setModalState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.campaign_rounded, color: Colors.redAccent, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Maklumat Resmi Kepsek',
                  style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'Pengumuman ini akan langsung dipublikasikan ke antarmuka guru & siswa serta dikirim melalui Push Notification FCM.',
                  style: GoogleFonts.inter(fontSize: 12, color: Colors.grey.shade600),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: titleController,
                  decoration: InputDecoration(
                    labelText: 'Judul Maklumat',
                    hintText: 'Contoh: Himbauan Pelaksanaan UTS Ganjil',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: bodyController,
                  maxLines: 4,
                  decoration: InputDecoration(
                    labelText: 'Isi Maklumat / Arahan',
                    hintText: 'Tuliskan pesan pembinaan atau instruksi pimpinan...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: isPosting ? null : () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            ElevatedButton.icon(
              onPressed: isPosting
                  ? null
                  : () async {
                      final title = titleController.text.trim();
                      final body = bodyController.text.trim();
                      if (title.isEmpty || body.isEmpty) {
                        scaffoldMessenger.showSnackBar(
                          const SnackBar(content: Text('Judul dan isi maklumat wajib diisi!')),
                        );
                        return;
                      }

                      setModalState(() => isPosting = true);
                      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                      final res = await ApiService.post('kepsek/pengumuman', {
                        'user_id': user?.id ?? 4,
                        'judul': title,
                        'isi': body,
                      });

                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);

                      if (res['success'] == true) {
                        scaffoldMessenger.showSnackBar(
                          const SnackBar(
                            backgroundColor: Colors.green,
                            content: Text('Maklumat Kepala Sekolah berhasil diterbitkan & disiarkan!'),
                          ),
                        );
                        _loadAllData();
                      } else {
                        scaffoldMessenger.showSnackBar(
                          SnackBar(
                            backgroundColor: Colors.redAccent,
                            content: Text(res['message'] ?? 'Gagal menerbitkan maklumat'),
                          ),
                        );
                      }
                    },
              icon: isPosting
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.send_rounded, size: 16),
              label: Text(isPosting ? 'Menyiarkan...' : 'Siarkan Sekarang'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1E3A8A), Color(0xFF3B82F6)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.shield_rounded, color: Colors.white, size: 20),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'EXECUTIVE MONITORING',
                  style: GoogleFonts.outfit(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.8,
                    color: isDark ? Colors.white : const Color(0xFF1E293B),
                  ),
                ),
                Text(
                  'SMK Muthia Harapan Cicalengka',
                  style: GoogleFonts.inter(
                    fontSize: 10,
                    color: Colors.grey.shade500,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Segarkan Data',
            onPressed: _loadAllData,
            icon: const Icon(Icons.refresh_rounded),
          ),
          IconButton(
            tooltip: 'Keluar',
            onPressed: _showLogoutDialog,
            icon: const Icon(Icons.power_settings_new_rounded, color: Colors.redAccent),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Memuat data monitoring eksekutif...', style: TextStyle(color: Colors.grey)),
                ],
              ),
            )
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.warning_amber_rounded, size: 48, color: Colors.amber),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: _loadAllData,
                          icon: const Icon(Icons.refresh),
                          label: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              : IndexedStack(
                  index: _currentIndex,
                  children: [
                    _buildDashboardTab(user, isDark),
                    _buildGuruTab(isDark),
                    _buildSiswaTab(isDark),
                    _buildPresensiTab(isDark),
                    _buildSupervisiTab(isDark),
                  ],
                ),
      floatingActionButton: _currentIndex == 0 || _currentIndex == 4
          ? FloatingActionButton.extended(
              onPressed: _showBroadcastDialog,
              backgroundColor: const Color(0xFF1E3A8A),
              foregroundColor: Colors.white,
              icon: const Icon(Icons.campaign_rounded),
              label: Text('Siarkan Maklumat', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
            )
          : null,
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        child: NavigationBar(
          selectedIndex: _currentIndex,
          onDestinationSelected: (idx) => setState(() => _currentIndex = idx),
          backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.dashboard_outlined),
              selectedIcon: Icon(Icons.dashboard_rounded, color: Color(0xFF2563EB)),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_search_outlined),
              selectedIcon: Icon(Icons.person_search_rounded, color: Color(0xFF2563EB)),
              label: 'Guru',
            ),
            NavigationDestination(
              icon: Icon(Icons.school_outlined),
              selectedIcon: Icon(Icons.school_rounded, color: Color(0xFF2563EB)),
              label: 'Siswa',
            ),
            NavigationDestination(
              icon: Icon(Icons.camera_alt_outlined),
              selectedIcon: Icon(Icons.camera_alt_rounded, color: Color(0xFF2563EB)),
              label: 'Presensi',
            ),
            NavigationDestination(
              icon: Icon(Icons.military_tech_outlined),
              selectedIcon: Icon(Icons.military_tech_rounded, color: Color(0xFF2563EB)),
              label: 'Supervisi',
            ),
          ],
        ),
      ),
    );
  }

  // --- TAB 1: EXECUTIVE DASHBOARD ---
  Widget _buildDashboardTab(dynamic user, bool isDark) {
    final kpi = _dashboardData?['kpi'] ?? {};
    final todayAtt = _dashboardData?['today_attendance'] ?? {};

    return RefreshIndicator(
      onRefresh: _loadAllData,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Banner Pimpinan
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E3A8A)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF1E3A8A).withValues(alpha: 0.3),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 54,
                    height: 54,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                      color: Colors.blue.shade800,
                    ),
                    child: const Icon(Icons.person_pin_rounded, color: Colors.white, size: 32),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Kepala Sekolah',
                          style: GoogleFonts.inter(color: Colors.blue.shade200, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          user?.fullName ?? 'Bapak Kepala Sekolah',
                          style: GoogleFonts.outfit(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Sistem Pengawasan Terpusat LMS',
                          style: GoogleFonts.inter(color: Colors.white70, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // Stat Cards Grid
            Text(
              'Indikator Kinerja Utama (KPI Realtime)',
              style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: _buildMetricCard(
                    title: 'Total Guru',
                    value: '${kpi['total_guru'] ?? _teachersList.length}',
                    subtitle: '${kpi['total_materi'] ?? 0} Modul Terbit',
                    icon: Icons.badge_rounded,
                    color: Colors.blue,
                    isDark: isDark,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildMetricCard(
                    title: 'Total Siswa',
                    value: '${kpi['total_siswa'] ?? 0}',
                    subtitle: '${kpi['total_kelas'] ?? _classList.length} Rombel Kelas',
                    icon: Icons.groups_rounded,
                    color: const Color(0xFF10B981),
                    isDark: isDark,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: _buildMetricCard(
                    title: 'Rata-Rata Nilai',
                    value: '${kpi['avg_score'] ?? '0.0'}',
                    subtitle: 'Capaian Leger & CBT',
                    icon: Icons.auto_graph_rounded,
                    color: Colors.amber.shade700,
                    isDark: isDark,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildMetricCard(
                    title: 'Tingkat Hadir',
                    value: '${kpi['attendance_rate'] ?? 0}%',
                    subtitle: 'Presensi Harian',
                    icon: Icons.check_circle_rounded,
                    color: Colors.indigo,
                    isDark: isDark,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),

            // Banner Presensi Hari Ini
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.blue.withValues(alpha: 0.15)),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.camera_alt_rounded, color: Colors.green, size: 20),
                          const SizedBox(width: 8),
                          Text('Kehadiran Guru Hari Ini', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13)),
                        ],
                      ),
                      TextButton(
                        onPressed: () => setState(() => _currentIndex = 3),
                        child: const Text('Detail', style: TextStyle(fontSize: 12)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildMiniBadge('Hadir', '${todayAtt['hadir'] ?? 0}', Colors.green),
                      _buildMiniBadge('Terlambat', '${todayAtt['terlambat'] ?? 0}', Colors.orange),
                      _buildMiniBadge('Izin/Sakit', '${todayAtt['izin'] ?? 0}', Colors.blue),
                      _buildMiniBadge('Belum', '${todayAtt['belum_hadir'] ?? 0}', Colors.redAccent),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 70), // Spacing for FAB
          ],
        ),
      ),
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.2)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 6),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title, style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.bold)),
              Icon(icon, color: color, size: 20),
            ],
          ),
          const SizedBox(height: 6),
          Text(value, style: GoogleFonts.outfit(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 2),
          Text(subtitle, style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade500)),
        ],
      ),
    );
  }

  Widget _buildMiniBadge(String label, String value, Color color) {
    return Column(
      children: [
        Text(value, style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
        Text(label, style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade600)),
      ],
    );
  }

  // --- TAB 2: MONITORING GURU ---
  Widget _buildGuruTab(bool isDark) {
    return RefreshIndicator(
      onRefresh: _loadAllData,
      child: _teachersList.isEmpty
          ? const Center(child: Text('Belum ada data pengajar terdaftar.'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _teachersList.length,
              itemBuilder: (context, index) {
                final g = _teachersList[index];
                final supScore = g['nilai_supervisi'] != null ? double.tryParse(g['nilai_supervisi'].toString()) : null;

                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E293B) : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 22,
                            backgroundColor: Colors.blue.shade100,
                            child: const Icon(Icons.person, color: Color(0xFF1E3A8A)),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  g['nama_lengkap'] ?? '',
                                  style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                                ),
                                Text(
                                  'NIP: ${g['nip'] ?? '-'}',
                                  style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500),
                                ),
                              ],
                            ),
                          ),
                          if (supScore != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.green.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.green.shade200),
                              ),
                              child: Text(
                                'Skor: ${supScore.toStringAsFixed(1)}',
                                style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.green.shade800),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Divider(height: 1),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          _buildGuruMetricBadge('Modul', '${g['total_materi'] ?? 0}', Icons.book),
                          _buildGuruMetricBadge('Tugas', '${g['total_tugas'] ?? 0}', Icons.checklist),
                          _buildGuruMetricBadge('Kuis CBT', '${g['total_quiz'] ?? 0}', Icons.quiz),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
    );
  }

  Widget _buildGuruMetricBadge(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 14, color: Colors.grey.shade600),
        const SizedBox(width: 4),
        Text('$value $label', style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w600)),
      ],
    );
  }

  // --- TAB 3: MONITORING SISWA ---
  Widget _buildSiswaTab(bool isDark) {
    return RefreshIndicator(
      onRefresh: _loadAllData,
      child: _classList.isEmpty
          ? const Center(child: Text('Belum ada data rombel kelas.'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _classList.length,
              itemBuilder: (context, index) {
                final k = _classList[index];
                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E293B) : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.indigo.shade50,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(Icons.school_rounded, color: Colors.indigo),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              k['nama_kelas'] ?? '',
                              style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                            ),
                            Text(
                              'Program Keahlian SMK',
                              style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          'Rombel Aktif',
                          style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
    );
  }

  // --- TAB 4: PRESENSI REALTIME ---
  Widget _buildPresensiTab(bool isDark) {
    final list = _attendanceData?['list'] as List<dynamic>? ?? [];

    return RefreshIndicator(
      onRefresh: _loadAllData,
      child: list.isEmpty
          ? const Center(child: Text('Belum ada data presensi guru hari ini.'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              itemBuilder: (context, index) {
                final att = list[index];
                final hasMasuk = att['waktu_masuk'] != null && att['waktu_masuk'].toString().isNotEmpty;
                final status = att['status_kehadiran'] ?? (hasMasuk ? 'Hadir' : 'Belum Hadir');

                Color badgeColor = Colors.grey;
                if (status == 'Hadir') badgeColor = Colors.green;
                if (status == 'Terlambat') badgeColor = Colors.orange;
                if (status == 'Izin' || status == 'Sakit') badgeColor = Colors.blue;

                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E293B) : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 6),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: badgeColor.withValues(alpha: 0.15),
                        ),
                        child: Icon(
                          hasMasuk ? Icons.camera_alt_rounded : Icons.person_off_rounded,
                          color: badgeColor,
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              att['nama_lengkap'] ?? '',
                              style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold),
                            ),
                            Text(
                              hasMasuk ? 'Masuk: ${att['waktu_masuk']}' : 'Belum Check-in',
                              style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: badgeColor.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          status,
                          style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.bold, color: badgeColor),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
    );
  }

  // --- TAB 5: SUPERVISI & REKOMENDASI ---
  Widget _buildSupervisiTab(bool isDark) {
    return RefreshIndicator(
      onRefresh: _loadAllData,
      child: _supervisiList.isEmpty
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.military_tech_rounded, size: 48, color: Colors.amber),
                    const SizedBox(height: 12),
                    Text(
                      'Belum ada lembar supervisi tersimpan.',
                      style: GoogleFonts.inter(fontSize: 14, color: Colors.grey),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Anda dapat menginput supervisi di Web Portal E-Learning untuk instrumen penilaian komprehensif.',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade400),
                    ),
                  ],
                ),
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _supervisiList.length,
              itemBuilder: (context, index) {
                final s = _supervisiList[index];
                final score = double.tryParse(s['nilai_akhir']?.toString() ?? '0') ?? 0;

                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E293B) : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              s['nama_guru'] ?? '',
                              style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.blue.shade50,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              'Nilai: ${score.toStringAsFixed(1)} (${s['predikat'] ?? '-'})',
                              style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blue.shade900),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Tanggal: ${s['tanggal_supervisi'] ?? '-'}',
                        style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500),
                      ),
                      if (s['rekomendasi_tindak_lanjut'] != null && s['rekomendasi_tindak_lanjut'].toString().isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.amber.shade50,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            'Rekomendasi: ${s['rekomendasi_tindak_lanjut']}',
                            style: GoogleFonts.inter(fontSize: 11, color: Colors.amber.shade900),
                          ),
                        ),
                      ],
                    ],
                  ),
                );
              },
            ),
    );
  }
}
