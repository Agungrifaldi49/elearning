import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaAbsensiTab extends StatefulWidget {
  const SiswaAbsensiTab({super.key});

  @override
  State<SiswaAbsensiTab> createState() => _SiswaAbsensiTabState();
}

class _SiswaAbsensiTabState extends State<SiswaAbsensiTab> {
  @override
  void initState() {
    super.initState();
    _loadAbsensi();
  }

  void _loadAbsensi() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchAbsensi(user.id);
    }
  }

  void _simulasiCheckin() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final siswaProvider = Provider.of<SiswaProvider>(context, listen: false);

    if (user != null) {
      showModalBottomSheet(
        context: context,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        builder: (_) => Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.qr_code_scanner_rounded, size: 64, color: AppTheme.secondaryColor),
              const SizedBox(height: 16),
              const Text(
                'Presensi Kehadiran Mobile',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text(
                'Tekan tombol di bawah untuk mencatat presensi Masuk / Hadir Anda ke database.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final ok = await siswaProvider.checkinAbsensi(user.id, 1, 'Tepat Waktu');
                    if (!mounted) return;
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(ok ? 'Presensi Masuk Tepat Waktu Berhasil Dicatat!' : 'Gagal mencatat presensi'),
                        backgroundColor: ok ? Colors.green : Colors.red,
                      ),
                    );
                  },
                  icon: const Icon(Icons.check_circle_rounded, color: Colors.white),
                  label: const Text('Presensi Masuk Tepat Waktu', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green.shade700,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final absensiList = siswaProvider.absensiList;
    final stats = siswaProvider.absensiStats ?? {
      'total_hadir': absensiList.length,
      'tepat_waktu': absensiList.where((a) => a.status.toLowerCase().contains('tepat') || a.status.toLowerCase() == 'hadir').length,
      'terlambat': absensiList.where((a) => a.status.toLowerCase().contains('telat') || a.status.toLowerCase().contains('terlambat')).length,
      'sudah_pulang': absensiList.where((a) => a.status.toLowerCase().contains('pulang')).length,
      'izin_sakit_alpha': absensiList.where((a) => ['sakit', 'izin', 'alpa', 'alpha'].contains(a.status.toLowerCase())).length,
    };

    return Scaffold(
      appBar: AppBar(
        title: const Text('Laporan & History Presensi'),
        backgroundColor: Colors.indigo.shade900,
        foregroundColor: Colors.white,
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadAbsensi(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Attendance Quick Action Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.indigo.shade900, Colors.indigo.shade700],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.indigo.withValues(alpha: 0.3),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.qr_code_2_rounded, size: 36, color: Colors.white),
                    ),
                    const SizedBox(width: 14),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Presensi QR Code Mobile', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                          SizedBox(height: 2),
                          Text('Simulasi Presensi Hadir / Scanner QR', style: TextStyle(fontSize: 12, color: Colors.white70)),
                        ],
                      ),
                    ),
                    ElevatedButton(
                      onPressed: _simulasiCheckin,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.amber.shade700,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      child: const Text('Check-in', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Summary Stats Report Header
              const Text(
                '📊 Ringkasan Laporan Kehadiran',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: _buildReportStatCard(
                      title: 'Tepat Waktu',
                      count: (stats['tepat_waktu'] ?? 0).toString(),
                      color: Colors.green.shade700,
                      bgColor: Colors.green.shade50,
                      icon: Icons.check_circle_rounded,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildReportStatCard(
                      title: 'Terlambat',
                      count: (stats['terlambat'] ?? 0).toString(),
                      color: Colors.orange.shade800,
                      bgColor: Colors.orange.shade50,
                      icon: Icons.warning_amber_rounded,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildReportStatCard(
                      title: 'Pulang',
                      count: (stats['sudah_pulang'] ?? 0).toString(),
                      color: Colors.blue.shade800,
                      bgColor: Colors.blue.shade50,
                      icon: Icons.home_rounded,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // Attendance History List Timeline
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '📋 History Presensi Siswa',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    'Total Record: ${absensiList.length}',
                    style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              siswaProvider.isLoading
                  ? const Padding(
                      padding: EdgeInsets.symmetric(vertical: 40),
                      child: Center(child: CircularProgressIndicator()),
                    )
                  : absensiList.isEmpty
                      ? Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(32),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Column(
                            children: [
                              Icon(Icons.event_busy, size: 48, color: Colors.grey),
                              SizedBox(height: 8),
                              Text('Belum ada riwayat presensi recorded.', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: absensiList.length,
                          itemBuilder: (context, index) {
                            final a = absensiList[index];
                            final st = a.status.toLowerCase();

                            Color statusColor = Colors.green.shade700;
                            IconData statusIcon = Icons.check_circle_rounded;
                            String statusLabel = a.status;

                            if (st.contains('telat') || st.contains('terlambat')) {
                              statusColor = Colors.orange.shade800;
                              statusIcon = Icons.access_time_filled_rounded;
                            } else if (st.contains('pulang')) {
                              statusColor = Colors.blue.shade700;
                              statusIcon = Icons.home_rounded;
                            } else if (st == 'izin' || st == 'sakit') {
                              statusColor = Colors.amber.shade800;
                              statusIcon = Icons.note_alt_rounded;
                            } else if (st == 'alpa' || st == 'alpha') {
                              statusColor = Colors.red.shade700;
                              statusIcon = Icons.cancel_rounded;
                            }

                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              elevation: 2,
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Row(
                                          children: [
                                            Icon(Icons.calendar_today_rounded, size: 16, color: Colors.indigo.shade900),
                                            const SizedBox(width: 6),
                                            Text(
                                              "${a.hari ?? ''} ${a.tanggal}".trim(),
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                            ),
                                          ],
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: statusColor.withValues(alpha: 0.12),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Icon(statusIcon, size: 14, color: statusColor),
                                              const SizedBox(width: 4),
                                              Text(
                                                statusLabel,
                                                style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 12),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    const Divider(height: 20),
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              const Text('Jam Masuk / Pre-KBM:', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                              Text(
                                                a.waktuMasuk ?? a.jamMulai ?? '06:45 WIB',
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.black87),
                                              ),
                                            ],
                                          ),
                                        ),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              const Text('Jam Pulang / Selesai:', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                              Text(
                                                a.waktuPulang ?? '15:00 WIB',
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.black87),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (a.namaMapel != null && a.namaMapel!.isNotEmpty) ...[
                                      const SizedBox(height: 8),
                                      Text(
                                        "Mata Pelajaran: ${a.namaMapel}",
                                        style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReportStatCard({
    required String title,
    required String count,
    required Color color,
    required Color bgColor,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 6),
          Text(count, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 2),
          Text(title, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}
