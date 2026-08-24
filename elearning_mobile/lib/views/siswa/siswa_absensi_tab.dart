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
        builder: (_) => Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.qr_code_scanner, size: 64, color: AppTheme.secondaryColor),
              const SizedBox(height: 16),
              const Text(
                'Presensi Kehadiran Mobile',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text('Tekan tombol di bawah untuk mencatat presensi Hadir Anda.'),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () async {
                  final ok = await siswaProvider.checkinAbsensi(user.id, 1, 'Hadir');
                  if (!mounted) return;
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(ok ? 'Presensi berhasil dicatat!' : 'Gagal presensi'),
                      backgroundColor: ok ? AppTheme.secondaryColor : Colors.red,
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.secondaryColor),
                child: const Text('Presensi Masuk (Hadir)'),
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

    return Scaffold(
      appBar: AppBar(
        title: const Text('Presensi Kehadiran Siswa'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.secondaryColor.withOpacity(0.12),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  const Icon(Icons.qr_code_2_rounded, size: 36, color: AppTheme.secondaryColor),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Presensi Mandiri / QR Check-in', style: TextStyle(fontWeight: FontWeight.bold)),
                        Text('Lakukan presensi kelas secara praktis', style: TextStyle(fontSize: 12, color: Colors.grey)),
                      ],
                    ),
                  ),
                  ElevatedButton(
                    onPressed: _simulasiCheckin,
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.secondaryColor),
                    child: const Text('Check-in'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              '📋 Riwayat Kehadiran (30 Hari Terakhir)',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: siswaProvider.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : absensiList.isEmpty
                      ? const Center(child: Text('Belum ada data absensi recorded.'))
                      : ListView.builder(
                          itemCount: absensiList.length,
                          itemBuilder: (context, index) {
                            final a = absensiList[index];
                            Color statusColor = Colors.green;
                            if (a.status == 'Izin') statusColor = Colors.blue;
                            if (a.status == 'Sakit') statusColor = Colors.orange;
                            if (a.status == 'Alpa') statusColor = Colors.red;

                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: statusColor.withOpacity(0.15),
                                  child: Icon(Icons.event_available, color: statusColor),
                                ),
                                title: Text(a.namaMapel ?? 'Mata Pelajaran', style: const TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Text("Tanggal: ${a.tanggal} • ${a.hari ?? ''}"),
                                trailing: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: statusColor.withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    a.status,
                                    style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 12),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }
}
