import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/quiz_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GuruCbtTab extends StatefulWidget {
  const GuruCbtTab({super.key});

  @override
  State<GuruCbtTab> createState() => _GuruCbtTabState();
}

class _GuruCbtTabState extends State<GuruCbtTab> {
  @override
  void initState() {
    super.initState();
    _loadQuiz();
  }

  void _loadQuiz() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchQuiz(user.id);
    }
  }

  void _showBankSoalModal(QuizModel q) async {
    final res = await ApiService.get('guru/bank_soal', params: {'quiz_id': '${q.id}'});
    List<dynamic> soalList = [];
    if (res['success'] == true && res['data'] is List) {
      soalList = res['data'];
    }

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Container(
          height: MediaQuery.of(context).size.height * 0.75,
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text('Bank Soal: ${q.judul}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  ),
                  ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      _showAddSoalModal(q.id);
                    },
                    icon: const Icon(Icons.add, size: 16),
                    label: const Text('Tambah Soal'),
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text("Mapel: ${q.namaMapel} • Kelas: ${q.namaKelas} • Durasi: ${q.durasiMenit} Menit", style: TextStyle(color: Colors.grey.shade700, fontSize: 12)),
              const SizedBox(height: 14),
              Expanded(
                child: soalList.isEmpty
                    ? const Center(child: Text('Bank soal ini belum memiliki pertanyaan.'))
                    : ListView.builder(
                        itemCount: soalList.length,
                        itemBuilder: (context, idx) {
                          final s = soalList[idx];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 10),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text("Soal #${idx + 1} (Bobot: ${s['bobot'] ?? 10} Poin)", style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.purple)),
                                  const SizedBox(height: 4),
                                  Text(s['pertanyaan'] ?? '', style: const TextStyle(fontSize: 14)),
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
      },
    );
  }

  void _showAddSoalModal(int quizId) {
    final soalController = TextEditingController();
    final bobotController = TextEditingController(text: '10');

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tambah Soal Baru'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: soalController,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Teks Pertanyaan Soal', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: bobotController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Bobot Nilai Soal', border: OutlineInputBorder()),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final pert = soalController.text.trim();
              final bobot = int.tryParse(bobotController.text) ?? 10;
              if (pert.isNotEmpty) {
                final res = await ApiService.post('guru/bank_soal', {
                  'quiz_id': quizId,
                  'pertanyaan': pert,
                  'bobot': bobot,
                });
                if (!mounted) return;
                Navigator.pop(context);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message'] ?? 'Soal berhasil ditambahkan!'),
                    backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                  ),
                );
              }
            },
            child: const Text('Simpan Soal'),
          ),
        ],
      ),
    );
  }

  void _showAddQuizModal() {
    final judulController = TextEditingController();
    final deskripsiController = TextEditingController();
    final durasiController = TextEditingController(text: '30');
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
              'Buat Ujian CBT / Quiz Baru',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: judulController,
              decoration: const InputDecoration(labelText: 'Judul Ujian / Quiz'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: deskripsiController,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'Keterangan Ujian'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: durasiController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Durasi (Menit)'),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () async {
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                final durasi = int.tryParse(durasiController.text) ?? 30;
                if (user != null) {
                  final ok = await Provider.of<GuruProvider>(context, listen: false).createQuiz(
                    user.id,
                    judulController.text,
                    deskripsiController.text,
                    selectedMapel,
                    selectedKelas,
                    durasi,
                  );
                  if (!mounted) return;
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(ok ? 'Quiz CBT berhasil diterbitkan!' : 'Gagal membuat quiz'),
                      backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
                    ),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                backgroundColor: AppTheme.primaryColor,
              ),
              child: const Text('Terbitkan Ujian CBT'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final quizList = guruProvider.quizList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '🎯 Kelola Quiz & CBT',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              ElevatedButton.icon(
                onPressed: _showAddQuizModal,
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Buat Quiz CBT'),
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
              ),
            ],
          ),
          const SizedBox(height: 12),

          Expanded(
            child: guruProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : quizList.isEmpty
                    ? const Center(child: Text('Belum ada Quiz / Ujian CBT.'))
                    : ListView.builder(
                        itemCount: quizList.length,
                        itemBuilder: (context, index) {
                          final q = quizList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              onTap: () => _showBankSoalModal(q),
                              leading: Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: Colors.purple.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.quiz, color: Colors.purple),
                              ),
                              title: Text(q.judul, style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text("Durasi: ${q.durasiMenit} mnt • Peserta: ${q.totalPeserta ?? 0}"),
                              trailing: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.green.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  q.status.toUpperCase(),
                                  style: const TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 11),
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
