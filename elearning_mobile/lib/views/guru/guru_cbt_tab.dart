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
    _loadData();
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      final guruProvider = Provider.of<GuruProvider>(context, listen: false);
      guruProvider.fetchQuiz(user.id);
      guruProvider.fetchSusulanRequests(user.id);
    }
  }

  void _showSusulanRequestsModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Consumer<GuruProvider>(
          builder: (context, guruProvider, child) {
            final requests = guruProvider.susulanList;
            final user = Provider.of<AuthProvider>(context, listen: false).currentUser;

            return Container(
              height: MediaQuery.of(context).size.height * 0.8,
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.mark_email_unread_rounded, color: AppTheme.primaryColor),
                          SizedBox(width: 8),
                          Text(
                            'Permintaan Izin Susulan / Suspend',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Kelola & Konfirmasi permohonan siswa yang disuspend atau meminta ujian susulan.',
                    style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
                  ),
                  const SizedBox(height: 16),
                  Expanded(
                    child: requests.isEmpty
                        ? const Center(
                            child: Text('Belum ada permintaan izin susulan / buka suspend.'),
                          )
                        : ListView.builder(
                            itemCount: requests.length,
                            itemBuilder: (context, idx) {
                              final req = requests[idx];
                              final reqId = int.parse(req['id'].toString());
                              final status = req['status'] ?? 'pending';

                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                  side: BorderSide(
                                    color: status == 'pending'
                                        ? Colors.amber.shade400
                                        : (status == 'disetujui' ? Colors.green.shade300 : Colors.red.shade300),
                                    width: status == 'pending' ? 1.5 : 1,
                                  ),
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(14),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Expanded(
                                            child: Text(
                                              "${req['nama_siswa'] ?? 'Siswa'} (${req['nama_kelas'] ?? '-'})",
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                            ),
                                          ),
                                          _buildSusulanStatusBadge(status),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Text(
                                        "Kuis: ${req['judul_quiz'] ?? '-'} • ${req['nama_mapel'] ?? ''}",
                                        style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryColor, fontSize: 12),
                                      ),
                                      if (req['catatan'] != null && req['catatan'].toString().isNotEmpty) ...[
                                        const SizedBox(height: 6),
                                        Container(
                                          padding: const EdgeInsets.all(8),
                                          decoration: BoxDecoration(
                                            color: Colors.grey.shade100,
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            "Catatan Siswa: \"${req['catatan']}\"",
                                            style: TextStyle(fontSize: 12, color: Colors.grey.shade800, fontStyle: FontStyle.italic),
                                          ),
                                        ),
                                      ],
                                      if (status == 'pending') ...[
                                        const SizedBox(height: 12),
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.end,
                                          children: [
                                            OutlinedButton.icon(
                                              onPressed: () async {
                                                if (user == null) return;
                                                final ok = await guruProvider.rejectSusulanRequest(user.id, reqId);
                                                if (!mounted) return;
                                                ScaffoldMessenger.of(context).showSnackBar(
                                                  SnackBar(
                                                    content: Text(ok ? 'Permintaan izin DITOLAK.' : 'Gagal menolak permohonan'),
                                                    backgroundColor: Colors.red,
                                                  ),
                                                );
                                              },
                                              icon: const Icon(Icons.cancel_outlined, size: 16, color: Colors.red),
                                              label: const Text('Tolak', style: TextStyle(color: Colors.red, fontSize: 12)),
                                              style: OutlinedButton.styleFrom(
                                                side: const BorderSide(color: Colors.red),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            ElevatedButton.icon(
                                              onPressed: () async {
                                                if (user == null) return;
                                                final ok = await guruProvider.approveSusulanRequest(user.id, reqId);
                                                if (!mounted) return;
                                                ScaffoldMessenger.of(context).showSnackBar(
                                                  SnackBar(
                                                    content: Text(ok ? 'Permintaan izin DISETUJUI / Buka Suspend Berhasil! ✅' : 'Gagal menyetujui permohonan'),
                                                    backgroundColor: Colors.green,
                                                  ),
                                                );
                                              },
                                              icon: const Icon(Icons.check_circle_rounded, size: 16),
                                              label: const Text('ACC / Setujui', style: TextStyle(fontSize: 12)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: Colors.green,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
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
      },
    );
  }

  Widget _buildSusulanStatusBadge(String status) {
    Color bg;
    Color text;
    String label;

    if (status == 'disetujui') {
      bg = Colors.green.shade100;
      text = Colors.green.shade800;
      label = 'DISETUJUI ✅';
    } else if (status == 'ditolak') {
      bg = Colors.red.shade100;
      text = Colors.red.shade800;
      label = 'DITOLAK ❌';
    } else {
      bg = Colors.amber.shade100;
      text = Colors.amber.shade900;
      label = 'PENDING ⏳';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(6)),
      child: Text(label, style: TextStyle(color: text, fontWeight: FontWeight.bold, fontSize: 10)),
    );
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
    final susulanList = guruProvider.susulanList;
    final pendingCount = susulanList.where((e) => (e['status'] ?? '') == 'pending').length;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Text(
                    '🎯 Kelola Quiz & CBT',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  if (pendingCount > 0) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.amber.shade900,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '$pendingCount Pending',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                      ),
                    ),
                  ],
                ],
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

          // Banner Izin Susulan / Suspend Notification Card
          Card(
            color: pendingCount > 0 ? Colors.amber.shade50 : Colors.blue.shade50,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
              side: BorderSide(
                color: pendingCount > 0 ? Colors.amber.shade300 : Colors.blue.shade200,
              ),
            ),
            child: InkWell(
              borderRadius: BorderRadius.circular(14),
              onTap: _showSusulanRequestsModal,
              child: Padding(
                padding: const EdgeInsets.all(14.0),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: pendingCount > 0 ? Colors.amber : Colors.blue,
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        pendingCount > 0 ? Icons.mark_email_unread_rounded : Icons.mark_email_read_rounded,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            pendingCount > 0
                                ? "📩 Ada $pendingCount Permintaan Izin Susulan!"
                                : "Kelola Permintaan Izin Susulan",
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: pendingCount > 0 ? Colors.amber.shade900 : Colors.blue.shade900,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            pendingCount > 0
                                ? "Siswa telah mengajukan permohonan susulan/buka suspend. Klik untuk ACC / Tolak."
                                : "Lihat riwayat persetujuan izin susulan dan pembukaan suspend kuis.",
                            style: TextStyle(
                              fontSize: 11,
                              color: pendingCount > 0 ? Colors.amber.shade800 : Colors.blue.shade800,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey),
                  ],
                ),
              ),
            ),
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
