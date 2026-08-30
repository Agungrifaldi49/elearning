import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/tugas_model.dart';
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
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedMapel = 'Semua';

  @override
  void initState() {
    super.initState();
    _loadTugas();
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text.toLowerCase();
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadTugas() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      await Provider.of<GuruProvider>(context, listen: false).fetchTugas(user.id);
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
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
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
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor.withAlpha(25),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.add_task_rounded, color: AppTheme.primaryColor, size: 24),
                ),
                const SizedBox(width: 12),
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Buat Tugas Modul Baru', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    Text('Tambahkan petunjuk penugasan untuk siswa', style: TextStyle(fontSize: 11, color: Colors.grey)),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),

            TextField(
              controller: judulController,
              decoration: InputDecoration(
                labelText: 'Judul Tugas Pembelajaran',
                hintText: 'Contoh: Tugas 1 Modul Auth MVC',
                prefixIcon: const Icon(Icons.title_rounded),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 12),

            TextField(
              controller: deskripsiController,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Instruksi & Rubrik Penilaian',
                hintText: 'Tuliskan petunjuk pengerjaan tugas secara rinci...',
                prefixIcon: const Icon(Icons.description_rounded),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 20),

            ElevatedButton.icon(
              onPressed: () async {
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                final nav = Navigator.of(context);
                final messenger = ScaffoldMessenger.of(context);
                if (user != null && judulController.text.trim().isNotEmpty) {
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
              icon: const Icon(Icons.send_rounded, size: 18),
              label: const Text('Publikasikan Tugas Baru'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 50),
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showTaskDetailDialog(TugasModel t) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: Colors.blue.shade100, borderRadius: BorderRadius.circular(12)),
                  child: const Icon(Icons.menu_book_rounded, color: AppTheme.primaryColor, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(t.judul, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      Text('Mapel: ${t.namaMapel} • Target: ${t.namaKelas ?? 'Semua Kelas'}', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                    ],
                  ),
                ),
                IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close)),
              ],
            ),
            const SizedBox(height: 16),

            // Deadline Box
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: Colors.amber.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.amber.shade200)),
              child: Row(
                children: [
                  Icon(Icons.alarm_rounded, color: Colors.amber.shade900, size: 20),
                  const SizedBox(width: 8),
                  Text('Batas Deadline: ${t.deadline}', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.amber.shade900)),
                ],
              ),
            ),
            const SizedBox(height: 14),

            const Text('Petunjuk Pengerjaan & Rubrik Penilaian:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            const SizedBox(height: 6),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(12)),
              child: Text(t.deskripsi, style: const TextStyle(fontSize: 12, height: 1.5, color: Colors.black87)),
            ),
            const SizedBox(height: 16),

            if (t.filePath != null && t.filePath!.isNotEmpty) ...[
              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                  FileService.showInAppPreview(context, t.filePath!, 'Lampiran Soal: ${t.judul}', studentName: 'Guru');
                },
                icon: const Icon(Icons.file_present_rounded),
                label: const Text('Buka Berkas Soal / Modul'),
                style: ElevatedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 46),
                  backgroundColor: AppTheme.primaryColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 10),
            ],

            ElevatedButton.icon(
              onPressed: () {
                Navigator.pop(context);
                _viewSubmissions(t.id, t.judul);
              },
              icon: const Icon(Icons.grading_rounded),
              label: Text('Periksa & Nilai Siswa (${t.totalPengumpulan ?? 0})'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 46),
                backgroundColor: Colors.amber.shade700,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _viewSubmissions(int tugasId, String judul) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final submissions = await Provider.of<GuruProvider>(context, listen: false).fetchSubmissions(user.id, tugasId);

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
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            
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

                              if (hasUrl || filePath.isNotEmpty) ...[
                                Container(
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: isDrive ? Colors.green.shade50 : Colors.blue.shade50,
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(
                                      color: isDrive ? Colors.green.shade200 : Colors.blue.shade200,
                                    ),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
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
                                        ],
                                      ),
                                      const SizedBox(height: 8),

                                      Row(
                                        children: [
                                          Expanded(
                                            child: ElevatedButton.icon(
                                              onPressed: () {
                                                if (targetOpenUrl.isNotEmpty) {
                                                  FileService.showInAppPreview(
                                                    context,
                                                    targetOpenUrl,
                                                    isDrive ? 'Google Drive Jawaban' : (filePath.isNotEmpty ? filePath : 'Berkas Jawaban'),
                                                    studentName: namaSiswa,
                                                  );
                                                }
                                              },
                                              icon: Icon(isDrive ? Icons.visibility_rounded : Icons.remove_red_eye_rounded, size: 14),
                                              label: Text(isDrive ? 'Pratinjau Drive' : 'Lihat di App'),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: isDrive ? Colors.green.shade700 : AppTheme.primaryColor,
                                                foregroundColor: Colors.white,
                                                padding: const EdgeInsets.symmetric(vertical: 6),
                                                textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                                visualDensity: VisualDensity.compact,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: OutlinedButton.icon(
                                              onPressed: () {
                                                if (targetOpenUrl.isNotEmpty) {
                                                  FileService.openFileOrUrl(context, targetOpenUrl, preferInApp: false);
                                                }
                                              },
                                              icon: Icon(isDrive ? Icons.open_in_new_rounded : Icons.download_rounded, size: 14),
                                              label: Text(isDrive ? 'Buka Link' : 'Unduh File'),
                                              style: OutlinedButton.styleFrom(
                                                foregroundColor: isDrive ? Colors.green.shade900 : AppTheme.primaryColor,
                                                side: BorderSide(color: isDrive ? Colors.green.shade400 : AppTheme.primaryColor),
                                                padding: const EdgeInsets.symmetric(vertical: 6),
                                                textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                                visualDensity: VisualDensity.compact,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 8),
                              ],

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
    final allTugas = guruProvider.tugasList;

    // Filter tasks by search query and mapel
    final filteredTugas = allTugas.where((t) {
      final matchesSearch = _searchQuery.isEmpty ||
          t.judul.toLowerCase().contains(_searchQuery) ||
          t.namaMapel.toLowerCase().contains(_searchQuery) ||
          (t.namaKelas ?? '').toLowerCase().contains(_searchQuery);
      final matchesMapel = _selectedMapel == 'Semua' || t.namaMapel == _selectedMapel;
      return matchesSearch && matchesMapel;
    }).toList();

    // Extract list of mapels for filter chips
    final mapelSet = <String>{'Semua'};
    for (var t in allTugas) {
      if (t.namaMapel.isNotEmpty) mapelSet.add(t.namaMapel);
    }

    // Stats calculations
    final totalTugasCount = allTugas.length;
    int totalKirimanCount = 0;
    for (var t in allTugas) {
      totalKirimanCount += (t.totalPengumpulan ?? 0);
    }

    return RefreshIndicator(
      onRefresh: _loadTugas,
      color: AppTheme.primaryColor,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 🚀 EXECUTIVE GLASSMORPHIC HERO BANNER
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF0D9488)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withAlpha(50),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.amber,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.assignment_rounded, size: 14, color: Colors.black87),
                              SizedBox(width: 4),
                              Flexible(
                                child: Text(
                                  'Control Center Tugas',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.black87),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton.icon(
                        onPressed: _showAddTugasModal,
                        icon: const Icon(Icons.add_circle_rounded, size: 16),
                        label: const Text('Buat Tugas'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.amber.shade700,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                          visualDensity: VisualDensity.compact,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Kelola Penugasan & Rubrik',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Buat tugas modul baru, unggah petunjuk pengerjaan, periksa berkas kiriman siswa, dan berikan skor nilai.',
                    style: TextStyle(fontSize: 12, color: Colors.white.withAlpha(200), height: 1.4),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 📊 KPI SUMMARY STATS CARDS
            Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 10, offset: const Offset(0, 2)),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.teal.shade50,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(Icons.bookmark_added_rounded, color: Colors.teal.shade700, size: 20),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Total Tugas', style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600), overflow: TextOverflow.ellipsis),
                              Text('$totalTugasCount Modul', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.black87), overflow: TextOverflow.ellipsis),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 10, offset: const Offset(0, 2)),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(Icons.groups_rounded, color: Colors.blue.shade700, size: 20),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Total Kiriman', style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600), overflow: TextOverflow.ellipsis),
                              Text('$totalKirimanCount Berkas', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.black87), overflow: TextOverflow.ellipsis),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // 🔍 SEARCH BAR & FILTER CHIPS
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari judul tugas, mapel, atau kelas...',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded),
                        onPressed: () => _searchController.clear(),
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(color: Colors.grey.shade200),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(color: Colors.grey.shade200),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Mapel Filter Chips
            if (mapelSet.length > 1)
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: mapelSet.map((mName) {
                    final isSel = _selectedMapel == mName;
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        selected: isSel,
                        label: Text(mName),
                        labelStyle: TextStyle(
                          fontSize: 12,
                          fontWeight: isSel ? FontWeight.bold : FontWeight.w500,
                          color: isSel ? Colors.white : Colors.black87,
                        ),
                        selectedColor: AppTheme.primaryColor,
                        backgroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                          side: BorderSide(color: isSel ? AppTheme.primaryColor : Colors.grey.shade300),
                        ),
                        onSelected: (selected) {
                          setState(() {
                            _selectedMapel = mName;
                          });
                        },
                      ),
                    );
                  }).toList(),
                ),
              ),
            const SizedBox(height: 16),

            // 📑 TASK MODULES LIST
            if (guruProvider.isLoading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 40),
                child: Center(child: CircularProgressIndicator()),
              )
            else if (filteredTugas.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      Icon(Icons.assignment_late_outlined, size: 54, color: Colors.grey.shade400),
                      const SizedBox(height: 12),
                      const Text(
                        'Belum Ada Penugasan',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _searchQuery.isNotEmpty ? 'Tidak ada tugas sesuai kata kunci pencarian.' : 'Klik tombol "+ Buat Tugas" untuk menambah modul baru.',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: filteredTugas.length,
                itemBuilder: (context, index) {
                  final t = filteredTugas[index];
                  final subCount = t.totalPengumpulan ?? 0;
                  final DateTime? deadlineDt = DateTime.tryParse(t.deadline);
                  final bool isExpired = deadlineDt != null && DateTime.now().isAfter(deadlineDt);

                  return Container(
                    margin: const EdgeInsets.only(bottom: 14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: Colors.grey.shade200),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withAlpha(8),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Top Status Accent Bar
                          Container(
                            height: 4,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: isExpired
                                    ? [Colors.red.shade400, Colors.red.shade700]
                                    : [AppTheme.primaryColor, Colors.teal],
                              ),
                            ),
                          ),

                          Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Mapel & Class Header Badges
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Flexible(
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.teal.shade50,
                                          borderRadius: BorderRadius.circular(20),
                                          border: Border.all(color: Colors.teal.shade200),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(Icons.book_rounded, size: 12, color: Colors.teal.shade800),
                                            const SizedBox(width: 4),
                                            Flexible(
                                              child: Text(
                                                t.namaMapel,
                                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.teal.shade800),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Flexible(
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.blue.shade50,
                                          borderRadius: BorderRadius.circular(20),
                                          border: Border.all(color: Colors.blue.shade200),
                                        ),
                                        child: Text(
                                          t.namaKelas ?? 'Semua Kelas',
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 10),

                                // Task Title
                                Text(
                                  t.judul,
                                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.black87, height: 1.3),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 6),

                                // Description Snippet
                                Text(
                                  t.deskripsi,
                                  style: TextStyle(fontSize: 12, color: Colors.grey.shade700, height: 1.4),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 12),

                                // Deadline & Submission Bar
                                Row(
                                  children: [
                                    Icon(
                                      isExpired ? Icons.error_outline_rounded : Icons.access_time_rounded,
                                      size: 14,
                                      color: isExpired ? Colors.red : Colors.amber.shade900,
                                    ),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        isExpired ? 'Expired: ${t.deadline}' : 'Deadline: ${t.deadline}',
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          color: isExpired ? Colors.red : Colors.amber.shade900,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 14),

                                // Actions Row (Koreksi + Detail)
                                Row(
                                  children: [
                                    Expanded(
                                      flex: 3,
                                      child: ElevatedButton.icon(
                                        onPressed: () => _viewSubmissions(t.id, t.judul),
                                        icon: const Icon(Icons.assignment_turned_in_rounded, size: 16),
                                        label: Text('Koreksi & Nilai ($subCount)'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.amber.shade700,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(vertical: 10),
                                          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          elevation: 1,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      flex: 2,
                                      child: OutlinedButton.icon(
                                        onPressed: () => _showTaskDetailDialog(t),
                                        icon: const Icon(Icons.info_outline_rounded, size: 16),
                                        label: const Text('Detail'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: AppTheme.primaryColor,
                                          side: const BorderSide(color: AppTheme.primaryColor),
                                          padding: const EdgeInsets.symmetric(vertical: 10),
                                          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }
}
