import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaMateriTab extends StatefulWidget {
  const SiswaMateriTab({super.key});

  @override
  State<SiswaMateriTab> createState() => _SiswaMateriTabState();
}

class _SiswaMateriTabState extends State<SiswaMateriTab> {
  @override
  void initState() {
    super.initState();
    _loadMateri();
  }

  void _loadMateri() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchMateri(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final materiList = siswaProvider.materiList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '📚 Materi Pembelajaran',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          Expanded(
            child: siswaProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : materiList.isEmpty
                    ? const Center(child: Text('Belum ada materi pembelajaran.'))
                    : ListView.builder(
                        itemCount: materiList.length,
                        itemBuilder: (context, index) {
                          final m = materiList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(10),
                                        decoration: BoxDecoration(
                                          color: Colors.blue.withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: Icon(
                                          m.jenisFile == 'video' || m.youtubeUrl != null
                                              ? Icons.play_circle_fill_rounded
                                              : Icons.picture_as_pdf_rounded,
                                          color: Colors.blue,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              m.namaMapel,
                                              style: const TextStyle(
                                                fontSize: 12,
                                                color: AppTheme.secondaryColor,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                            Text(
                                              m.judul,
                                              style: const TextStyle(
                                                fontSize: 16,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Text(
                                    m.deskripsi,
                                    style: const TextStyle(fontSize: 14),
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        "Oleh: ${m.namaGuru ?? '-'}",
                                        style: const TextStyle(fontSize: 12, color: Colors.grey),
                                      ),
                                      ElevatedButton.icon(
                                        onPressed: () {
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(
                                              content: Text('Membuka ${m.judul} (${m.jenisFile.toUpperCase()})'),
                                            ),
                                          );
                                        },
                                        icon: const Icon(Icons.file_download_outlined, size: 16),
                                        label: const Text('Buka Materi', style: TextStyle(fontSize: 12)),
                                        style: ElevatedButton.styleFrom(
                                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
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
