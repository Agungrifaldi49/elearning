import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruJadwalTab extends StatefulWidget {
  const GuruJadwalTab({super.key});

  @override
  State<GuruJadwalTab> createState() => _GuruJadwalTabState();
}

class _GuruJadwalTabState extends State<GuruJadwalTab> {
  @override
  void initState() {
    super.initState();
    _loadJadwal();
  }

  void _loadJadwal() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchJadwal(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final jadwalList = guruProvider.jadwalList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '📅 Jadwal Mengajar Guru',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          Expanded(
            child: guruProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : jadwalList.isEmpty
                    ? const Center(child: Text('Belum ada jadwal mengajar.'))
                    : ListView.builder(
                        itemCount: jadwalList.length,
                        itemBuilder: (context, index) {
                          final j = jadwalList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: AppTheme.primaryColor.withOpacity(0.1),
                                child: Text(
                                  j.hari.substring(0, 3),
                                  style: const TextStyle(
                                    color: AppTheme.primaryColor,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                                ),
                              ),
                              title: Text(
                                j.namaMapel,
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                              subtitle: Text("Kelas: ${j.namaKelas ?? '-'} • Waktu: ${j.jamMulai} - ${j.jamSelesai}"),
                              trailing: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.blue.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  j.ruangan,
                                  style: const TextStyle(fontSize: 11, color: Colors.blue, fontWeight: FontWeight.bold),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
