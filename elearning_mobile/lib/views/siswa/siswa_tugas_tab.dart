import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaTugasTab extends StatefulWidget {
  const SiswaTugasTab({super.key});

  @override
  State<SiswaTugasTab> createState() => _SiswaTugasTabState();
}

class _SiswaTugasTabState extends State<SiswaTugasTab> {
  @override
  void initState() {
    super.initState();
    _loadTugas();
  }

  void _loadTugas() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchTugas(user.id);
    }
  }

  void _showSubmitDialog(int tugasId, String judul) {
    final catatanController = TextEditingController();
    final fileController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('Pengumpulan: $judul'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: catatanController,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Catatan Jawaban Siswa',
                  hintText: 'Tuliskan rangkuman atau penjelasan jawaban...',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: fileController,
                decoration: const InputDecoration(
                  labelText: 'URL / Nama File Tugas',
                  hintText: 'jawaban_tugas.pdf',
                  prefixIcon: Icon(Icons.attach_file),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () async {
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                if (user != null) {
                  final ok = await Provider.of<SiswaProvider>(context, listen: false).submitTugas(
                    user.id,
                    tugasId,
                    catatanController.text,
                    fileController.text,
                  );
                  if (!mounted) return;
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(ok ? 'Tugas berhasil dikumpulkan!' : 'Gagal mengirim tugas'),
                      backgroundColor: ok ? AppTheme.secondaryColor : Colors.red,
                    ),
                  );
                }
              },
              child: const Text('Kirim Jawaban'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final tugasList = siswaProvider.tugasList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '📝 Tugas Siswa',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          Expanded(
            child: siswaProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : tugasList.isEmpty
                    ? const Center(child: Text('Tidak ada tugas untuk saat ini.'))
                    : ListView.builder(
                        itemCount: tugasList.length,
                        itemBuilder: (context, index) {
                          final t = tugasList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        t.namaMapel,
                                        style: const TextStyle(
                                          color: AppTheme.secondaryColor,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: t.isSubmitted
                                              ? (t.isGraded ? Colors.green.withOpacity(0.15) : Colors.blue.withOpacity(0.15))
                                              : Colors.orange.withOpacity(0.15),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          t.isGraded
                                              ? 'Nilai: ${t.nilai}'
                                              : (t.isSubmitted ? 'Sudah Dikumpul' : 'Belum Dikumpul'),
                                          style: TextStyle(
                                            color: t.isSubmitted
                                                ? (t.isGraded ? Colors.green : Colors.blue)
                                                : Colors.orange,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    t.judul,
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(t.deskripsi, style: const TextStyle(fontSize: 14)),
                                  const SizedBox(height: 8),
                                  Text(
                                    "Deadline: ${t.deadline}",
                                    style: const TextStyle(fontSize: 12, color: Colors.redAccent, fontWeight: FontWeight.w500),
                                  ),
                                  if (t.komentarGuru != null && t.komentarGuru!.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    Container(
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: Colors.grey.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        "Feedback Guru: ${t.komentarGuru}",
                                        style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic),
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: 12),
                                  Align(
                                    alignment: Alignment.centerRight,
                                    child: ElevatedButton.icon(
                                      onPressed: () => _showSubmitDialog(t.id, t.judul),
                                      icon: Icon(t.isSubmitted ? Icons.edit : Icons.upload_file, size: 16),
                                      label: Text(t.isSubmitted ? 'Edit Pengumpulan' : 'Kumpul Tugas'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: t.isSubmitted ? Colors.blue : AppTheme.secondaryColor,
                                      ),
                                    ),
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
