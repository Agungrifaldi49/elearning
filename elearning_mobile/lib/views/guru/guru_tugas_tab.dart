import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/file_service.dart';
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
                final nav = Navigator.of(context);
                final messenger = ScaffoldMessenger.of(context);
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
                  nav.pop();
                  messenger.showSnackBar(
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
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.85,
        padding: const EdgeInsets.all(20),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Sheet Drag Handle
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
            
            // Modal Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor.withAlpha(25),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.assignment_turned_in_rounded, color: AppTheme.primaryColor, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        judul,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.black87),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        'Total ${submissions.length} Berkas Kiriman Siswa',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.close_rounded),
                ),
              ],
            ),
            const Divider(height: 24),

            // Submissions List
            Expanded(
              child: submissions.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.inbox_outlined, size: 54, color: Colors.grey.shade400),
                          const SizedBox(height: 12),
                          const Text(
                            'Belum Ada Siswa Mengumpulkan',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Belum ada berkas kiriman untuk tugas ini.',
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      itemCount: submissions.length,
                      itemBuilder: (context, idx) {
                        final s = submissions[idx];
                        final namaSiswa = s['nama_siswa'] ?? s['nama_lengkap'] ?? 'Siswa';
                        final nis = s['nis'] ?? '-';
                        final namaKelas = s['nama_kelas'] ?? '-';
                        final catatan = (s['catatan_siswa'] ?? '').toString().trim();
                        final filePath = (s['file_path'] ?? '').toString().trim();
                        final fileUrl = (s['file_url'] ?? '').toString().trim();

                        final isDrive = fileUrl.contains('drive.google.com') ||
                            fileUrl.contains('docs.google.com') ||
                            catatan.contains('drive.google.com') ||
                            catatan.contains('docs.google.com');

                        final hasUrl = fileUrl.isNotEmpty || catatan.startsWith('http://') || catatan.startsWith('https://');

                        String targetOpenUrl = fileUrl;
                        if (targetOpenUrl.isEmpty) {
                          final RegExp urlRegExp = RegExp(r'(https?://[^\s]+)');
                          final match = urlRegExp.firstMatch(catatan);
                          if (match != null) {
                            targetOpenUrl = match.group(0) ?? '';
                          }
                        }

                        final double? nilaiNum = s['nilai'] != null ? double.tryParse(s['nilai'].toString()) : null;

                        return Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade50,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Student Info & NIS Header
                              Row(
                                children: [
                                  CircleAvatar(
                                    radius: 18,
                                    backgroundColor: AppTheme.primaryColor.withAlpha(35),
                                    child: const Icon(Icons.person_rounded, size: 20, color: AppTheme.primaryColor),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          namaSiswa,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
                                        ),
                                        Text(
                                          'NIS: $nis • Kelas: $namaKelas',
                                          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Score Status Badge
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: nilaiNum != null
                                          ? (nilaiNum >= 75 ? Colors.green.shade100 : Colors.red.shade100)
                                          : Colors.amber.shade100,
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Text(
                                      nilaiNum != null ? 'Nilai: ${nilaiNum.toStringAsFixed(1)}' : 'Belum Dinilai',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: nilaiNum != null
                                            ? (nilaiNum >= 75 ? Colors.green.shade800 : Colors.red.shade800)
                                            : Colors.amber.shade900,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),

                              // Catatan / Note Box if available
                              if (catatan.isNotEmpty) ...[
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(10),
                                  margin: const EdgeInsets.only(bottom: 8),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: Colors.grey.shade200),
                                  ),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Icon(Icons.chat_bubble_outline_rounded, size: 15, color: AppTheme.primaryColor),
                                      const SizedBox(width: 6),
                                      Expanded(
                                        child: Text(
                                          catatan,
                                          style: const TextStyle(fontSize: 12, color: Colors.black87),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],

                              // File Preview / Google Drive Action Box
                              if (hasUrl || filePath.isNotEmpty) ...[
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: isDrive ? Colors.green.shade50 : Colors.blue.shade50,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                      color: isDrive ? Colors.green.shade200 : Colors.blue.shade200,
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(
                                        isDrive ? Icons.add_to_drive_rounded : Icons.insert_drive_file_rounded,
                                        color: isDrive ? Colors.green.shade700 : AppTheme.primaryColor,
                                        size: 20,
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          isDrive ? 'Link Google Drive Siswa' : (filePath.isNotEmpty ? filePath : 'Lampiran Berkas Jawaban'),
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.bold,
                                            color: isDrive ? Colors.green.shade900 : Colors.blue.shade900,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      ElevatedButton.icon(
                                        onPressed: () {
                                          if (targetOpenUrl.isNotEmpty) {
                                            FileService.openFileOrUrl(context, targetOpenUrl);
                                          }
                                        },
                                        icon: Icon(isDrive ? Icons.open_in_new_rounded : Icons.visibility_rounded, size: 14),
                                        label: Text(isDrive ? 'Buka Drive' : 'Lihat Berkas'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: isDrive ? Colors.green.shade700 : AppTheme.primaryColor,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                          textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                          visualDensity: VisualDensity.compact,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 8),
                              ],

                              // Grading Action Bar
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  if (s['submitted_at'] != null)
                                    Text(
                                      'Dikirim: ${s['submitted_at']}',
                                      style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                                    )
                                  else
                                    const SizedBox.shrink(),
                                  
                                  OutlinedButton.icon(
                                    onPressed: () => _showGradingDialog(
                                      int.parse(s['id'].toString()),
                                      namaSiswa,
                                      currentScore: nilaiNum,
                                      currentComment: s['komentar_guru'] ?? '',
                                    ),
                                    icon: const Icon(Icons.edit_note_rounded, size: 18),
                                    label: Text(nilaiNum != null ? 'Edit Nilai' : 'Koreksi & Nilai'),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: AppTheme.primaryColor,
                                      side: const BorderSide(color: AppTheme.primaryColor),
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                      textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                      visualDensity: VisualDensity.compact,
                                    ),
                                  ),
                                ],
                              ),
                            ],
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

  void _showGradingDialog(int submissionId, String namaSiswa, {double? currentScore, String currentComment = ''}) {
    final nilaiController = TextEditingController(text: currentScore != null ? currentScore.toStringAsFixed(0) : '');
    final komentarController = TextEditingController(text: currentComment);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.stars_rounded, color: Colors.amber, size: 24),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'Koreksi & Nilai: $namaSiswa',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Masukkan skor nilai rubrik (0 - 100):',
              style: TextStyle(fontSize: 12, color: Colors.black54, fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: nilaiController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Skor Nilai Siswa',
                prefixIcon: const Icon(Icons.score_rounded),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 10),

            // Quick Score Preset Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [100, 90, 85, 75, 70].map((scoreVal) {
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ActionChip(
                      label: Text('$scoreVal'),
                      labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                      backgroundColor: Colors.grey.shade100,
                      onPressed: () {
                        nilaiController.text = scoreVal.toString();
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 12),

            TextField(
              controller: komentarController,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: 'Catatan Rubrik / Feedback Guru',
                prefixIcon: const Icon(Icons.comment_bank_rounded),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton.icon(
            onPressed: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              final nav = Navigator.of(context);
              final messenger = ScaffoldMessenger.of(context);
              final nil = double.tryParse(nilaiController.text) ?? 0;
              if (user != null) {
                final ok = await Provider.of<GuruProvider>(context, listen: false)
                    .gradeSubmission(user.id, submissionId, nil, komentarController.text);
                nav.pop();
                nav.pop();
                messenger.showSnackBar(
                  SnackBar(
                    content: Text(ok ? 'Skor nilai berhasil disimpan!' : 'Gagal simpan nilai'),
                    backgroundColor: ok ? AppTheme.secondaryColor : Colors.red,
                  ),
                );
              }
            },
            icon: const Icon(Icons.check_circle_rounded, size: 18),
            label: const Text('Simpan Nilai'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
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
                        itemBuilder: (context, idx) {
                          final t = tugasList[idx];
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
