import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruTugasTab extends StatefulWidget {
  const GuruTugasTab({super.key});

  @override
  State<GuruTugasTab> createState() => _GuruTugasTabState();
}

class _GuruTugasTabState extends State<GuruTugasTab> {
  @override
  void initState() {
    super.initState();
    _loadTugas();
  }

  void _loadTugas() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchTugas(user.id);
    }
  }

  void _showAddTugasModal() {
    final judulController = TextEditingController();
    final deskripsiController = TextEditingController();
    int selectedMapel = 1;
    int selectedKelas = 1;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom + 20,
          top: 20,
          left: 20,
          right: 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Buat Tugas Baru',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: judulController,
              decoration: const InputDecoration(labelText: 'Judul Tugas'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: deskripsiController,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Instruksi Tugas'),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () async {
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                if (user != null) {
                  final deadline = DateTime.now().add(const Duration(days: 7)).toString().substring(0, 19);
                  final ok = await Provider.of<GuruProvider>(context, listen: false).createTugas(
                    user.id,
                    judulController.text,
                    deskripsiController.text,
                    selectedMapel,
                    selectedKelas,
                    deadline,
                  );
                  if (!mounted) return;
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(ok ? 'Tugas berhasil dipublikasikan!' : 'Gagal membuat tugas'),
                      backgroundColor: ok ? AppTheme.secondaryColor : Colors.red,
                    ),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                backgroundColor: AppTheme.primaryColor,
              ),
              child: const Text('Publikasikan Tugas'),
            ),
          ],
        ),
      ),
    );
  }

  void _viewSubmissions(int tugasId, String judul) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final submissions = await Provider.of<GuruProvider>(context, listen: false)
        .fetchSubmissions(user.id, tugasId);

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.7,
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Pengumpulan Siswa: $judul', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Expanded(
              child: submissions.isEmpty
                  ? const Center(child: Text('Belum ada siswa yang mengumpulkan.'))
                  : ListView.builder(
                      itemCount: submissions.length,
                      itemBuilder: (context, idx) {
                        final s = submissions[idx];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            title: Text(s['nama_siswa'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text("NIS: ${s['nis']} • Catatan: ${s['catatan_siswa'] ?? '-'}"),
                            trailing: Text(
                              s['nilai'] != null ? "Nilai: ${s['nilai']}" : "Belum Dinilai",
                              style: TextStyle(
                                color: s['nilai'] != null ? Colors.green : Colors.orange,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            onTap: () => _showGradingDialog(int.parse(s['id'].toString()), s['nama_siswa']),
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

  void _showGradingDialog(int submissionId, String namaSiswa) {
    final nilaiController = TextEditingController();
    final komentarController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Beri Nilai: $namaSiswa'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nilaiController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Skor Nilai (0-100)'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: komentarController,
              decoration: const InputDecoration(labelText: 'Komentar / Feedback Guru'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              final nil = double.tryParse(nilaiController.text) ?? 0;
              if (user != null) {
                final ok = await Provider.of<GuruProvider>(context, listen: false)
                    .gradeSubmission(user.id, submissionId, nil, komentarController.text);
                if (!mounted) return;
                Navigator.pop(context);
                Navigator.pop(context);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(ok ? 'Nilai berhasil disimpan!' : 'Gagal simpan nilai')),
                );
              }
            },
            child: const Text('Simpan Nilai'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final tugasList = guruProvider.tugasList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '📝 Kelola Tugas',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              ElevatedButton.icon(
                onPressed: _showAddTugasModal,
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Buat Tugas'),
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
              ),
            ],
          ),
          const SizedBox(height: 12),

          Expanded(
            child: guruProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : tugasList.isEmpty
                    ? const Center(child: Text('Belum ada tugas dibuat.'))
                    : ListView.builder(
                        itemCount: tugasList.length,
                        itemBuilder: (context, index) {
                          final t = tugasList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              title: Text(t.judul, style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text("Mapel: ${t.namaMapel} • Kelas: ${t.namaKelas ?? '-'}"),
                              trailing: ElevatedButton(
                                onPressed: () => _viewSubmissions(t.id, t.judul),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.amber.shade700,
                                  padding: const EdgeInsets.symmetric(horizontal: 10),
                                ),
                                child: Text("Koreksi (${t.totalPengumpulan ?? 0})"),
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
