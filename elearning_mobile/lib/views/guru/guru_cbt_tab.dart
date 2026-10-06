import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';
import '../../models/quiz_model.dart';
import 'guru_bank_soal_screen.dart';
import 'guru_koreksi_quiz_screen.dart';

class GuruCbtTab extends StatefulWidget {
  const GuruCbtTab({super.key});

  @override
  State<GuruCbtTab> createState() => _GuruCbtTabState();
}

class _GuruCbtTabState extends State<GuruCbtTab> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedStatus = 'Semua';

  final List<String> _statusList = ['Semua', 'Published', 'Draft', 'Archived'];

  @override
  void initState() {
    super.initState();
    _loadData();
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text.toLowerCase().trim();
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      final guruProvider = Provider.of<GuruProvider>(context, listen: false);
      guruProvider.fetchQuiz(user.id);
      guruProvider.fetchSusulanRequests(user.id);
      guruProvider.fetchJadwal(user.id);
      guruProvider.fetchKoreksiList(user.id);
    }
  }

  void _showSusulanRequestsModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Consumer<GuruProvider>(
          builder: (context, guruProvider, child) {
            final requests = guruProvider.susulanList.where((e) => (e['type'] ?? 'quiz').toString().toLowerCase() == 'quiz').toList();
            final user = Provider.of<AuthProvider>(context, listen: false).currentUser;

            return Container(
              height: MediaQuery.of(context).size.height * 0.82,
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              padding: const EdgeInsets.all(20),
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
                          color: Colors.amber.shade100,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(Icons.mark_email_unread_rounded, color: Colors.amber.shade900, size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Permintaan Izin Susulan / Suspend',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            Text(
                              'Konfirmasi pengajuan ujian susulan siswa',
                              style: TextStyle(fontSize: 11, color: Colors.grey),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                  const Divider(height: 24),
                  Expanded(
                    child: requests.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.mark_email_read_outlined, size: 54, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                const Text(
                                  'Belum Ada Permintaan Izin Susulan',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Semua pengajuan susulan/buka suspend siswa telah diproses.',
                                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                                ),
                              ],
                            ),
                          )
                        : ListView.builder(
                            itemCount: requests.length,
                            itemBuilder: (context, idx) {
                              final req = requests[idx];
                              final reqId = int.parse(req['id'].toString());
                              final status = (req['status'] ?? 'pending').toString().toLowerCase();

                              return Container(
                                margin: const EdgeInsets.only(bottom: 12),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(
                                    color: status == 'pending'
                                        ? Colors.amber.shade400
                                        : (status == 'disetujui' ? Colors.green.shade300 : Colors.red.shade300),
                                    width: status == 'pending' ? 1.5 : 1,
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            "${req['nama_siswa'] ?? 'Siswa'} (${req['nama_kelas'] ?? '-'})",
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
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
                                      const SizedBox(height: 8),
                                      Container(
                                        width: double.infinity,
                                        padding: const EdgeInsets.all(10),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(10),
                                          border: Border.all(color: Colors.grey.shade200),
                                        ),
                                        child: Text(
                                          "Catatan Siswa: \"${req['catatan']}\"",
                                          style: TextStyle(fontSize: 12, color: Colors.grey.shade800, fontStyle: FontStyle.italic),
                                        ),
                                      ),
                                    ],
                                    if (status == 'pending') ...[
                                      const SizedBox(height: 12),
                                      Wrap(
                                        alignment: WrapAlignment.end,
                                        spacing: 8,
                                        runSpacing: 8,
                                        children: [
                                          OutlinedButton.icon(
                                            onPressed: () async {
                                              if (user == null) return;
                                              final messenger = ScaffoldMessenger.of(context);
                                              final ok = await guruProvider.rejectSusulanRequest(user.id, reqId);
                                              messenger.showSnackBar(
                                                SnackBar(
                                                  content: Text(ok ? 'Permintaan izin DITOLAK.' : 'Gagal menolak permohonan'),
                                                  backgroundColor: Colors.red,
                                                ),
                                              );
                                            },
                                            icon: const Icon(Icons.cancel_outlined, size: 16, color: Colors.red),
                                            label: const Text('Tolak', style: TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold)),
                                            style: OutlinedButton.styleFrom(
                                              side: const BorderSide(color: Colors.red),
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              visualDensity: VisualDensity.compact,
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          ElevatedButton.icon(
                                            onPressed: () async {
                                              if (user == null) return;
                                              final messenger = ScaffoldMessenger.of(context);
                                              final ok = await guruProvider.approveSusulanRequest(user.id, reqId);
                                              messenger.showSnackBar(
                                                SnackBar(
                                                  content: Text(ok ? 'Permintaan izin DISETUJUI / Buka Suspend Berhasil! ✅' : 'Gagal menyetujui permohonan'),
                                                  backgroundColor: Colors.green,
                                                ),
                                              );
                                            },
                                            icon: const Icon(Icons.check_circle_rounded, size: 16),
                                            label: const Text('ACC / Setujui', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: Colors.green,
                                              foregroundColor: Colors.white,
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              visualDensity: VisualDensity.compact,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ],
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
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(label, style: TextStyle(color: text, fontWeight: FontWeight.bold, fontSize: 10)),
    );
  }



  void _showAddQuizModal([QuizModel? quizToEdit]) {
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final isEdit = quizToEdit != null;
    final judulController = TextEditingController(text: quizToEdit?.judul ?? '');
    final deskripsiController = TextEditingController(text: quizToEdit?.deskripsi ?? '');
    final durasiController = TextEditingController(text: (quizToEdit?.durasiMenit ?? 30).toString());
    final accessKeyController = TextEditingController(text: quizToEdit?.accessKey ?? '');

    final List<Map<String, dynamic>> mapels = [];
    final Set<int> mapelIdsSeen = {};

    for (var j in guruProvider.jadwalList) {
      if (j.mapelId > 0 && !mapelIdsSeen.contains(j.mapelId)) {
        mapelIdsSeen.add(j.mapelId);
        mapels.add({'id': j.mapelId, 'nama_mapel': j.namaMapel});
      }
    }
    for (var m in guruProvider.mapelList) {
      final mId = m['id'] is int ? m['id'] as int : int.tryParse(m['id'].toString()) ?? 0;
      if (mId > 0 && !mapelIdsSeen.contains(mId)) {
        mapelIdsSeen.add(mId);
        mapels.add(m);
      }
    }
    if (quizToEdit != null && quizToEdit.mapelId > 0 && !mapelIdsSeen.contains(quizToEdit.mapelId)) {
      mapels.insert(0, {'id': quizToEdit.mapelId, 'nama_mapel': quizToEdit.namaMapel});
      mapelIdsSeen.add(quizToEdit.mapelId);
    }

    final List<Map<String, dynamic>> kelases = [];
    final Set<int> kelasIdsSeen = {};

    for (var j in guruProvider.jadwalList) {
      if (j.kelasId > 0 && !kelasIdsSeen.contains(j.kelasId)) {
        kelasIdsSeen.add(j.kelasId);
        kelases.add({'id': j.kelasId, 'nama_kelas': j.namaKelas ?? 'Kelas #${j.kelasId}'});
      }
    }
    for (var k in guruProvider.kelasList) {
      final kId = k['id'] is int ? k['id'] as int : int.tryParse(k['id'].toString()) ?? 0;
      if (kId > 0 && !kelasIdsSeen.contains(kId)) {
        kelasIdsSeen.add(kId);
        kelases.add(k);
      }
    }
    if (quizToEdit != null && quizToEdit.kelasId > 0 && !kelasIdsSeen.contains(quizToEdit.kelasId)) {
      kelases.insert(0, {'id': quizToEdit.kelasId, 'nama_kelas': quizToEdit.namaKelas ?? 'Kelas #${quizToEdit.kelasId}'});
      kelasIdsSeen.add(quizToEdit.kelasId);
    }

    int selectedMapelId = quizToEdit != null && quizToEdit.mapelId > 0
        ? quizToEdit.mapelId
        : (mapels.isNotEmpty ? (mapels.first['id'] is int ? mapels.first['id'] as int : int.parse(mapels.first['id'].toString())) : 1);

    List<int> selectedKelasIds = quizToEdit != null && quizToEdit.targetKelasIds.isNotEmpty
        ? List<int>.from(quizToEdit.targetKelasIds)
        : (kelases.isNotEmpty ? [(kelases.first['id'] is int ? kelases.first['id'] as int : int.parse(kelases.first['id'].toString()))] : [1]);

    String selectedKategori = quizToEdit?.kategori ?? 'kuis';
    String selectedRandomSoal = quizToEdit?.randomSoal ?? 'Y';
    int selectedMaxAttempts = quizToEdit?.maxAttempts ?? 1;
    DateTime? selectedDeadline = quizToEdit?.deadline != null ? DateTime.tryParse(quizToEdit!.deadline!) : null;
    bool isSubmitting = false;

    // Helper to generate clean random token
    String generateRandomToken({String prefix = ''}) {
      const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
      final rand = Random();
      final code = List.generate(4, (_) => chars[rand.nextInt(chars.length)]).join();
      return prefix.isNotEmpty ? '$prefix-$code' : List.generate(6, (_) => chars[rand.nextInt(chars.length)]).join();
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          final screenHeight = MediaQuery.of(context).size.height;
          final viewInsetsBottom = MediaQuery.of(context).viewInsets.bottom;

          return Container(
            constraints: BoxConstraints(maxHeight: screenHeight * 0.90),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Top drag handle & Header
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 12, 12, 10),
                  child: Column(
                    children: [
                      Center(
                        child: Container(
                          width: 44,
                          height: 5,
                          margin: const EdgeInsets.only(bottom: 12),
                          decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: isEdit ? Colors.blue.shade50 : Colors.purple.shade50,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: isEdit ? Colors.blue.shade200 : Colors.purple.shade100),
                            ),
                            child: Icon(
                              isEdit ? Icons.edit_note_rounded : Icons.quiz_rounded,
                              color: isEdit ? Colors.blue.shade800 : Colors.purple.shade800,
                              size: 24,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  isEdit ? 'Edit Ujian CBT / Quiz' : 'Buat Ujian CBT / Quiz Baru',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: -0.3),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  isEdit
                                      ? 'Perbarui judul, durasi, kelas, dan batas waktu kuis'
                                      : 'Atur kategori ujian, token, durasi, dan deadline',
                                  style: const TextStyle(fontSize: 11, color: Colors.grey),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            onPressed: () => Navigator.pop(context),
                            icon: const Icon(Icons.close_rounded),
                            style: IconButton.styleFrom(backgroundColor: Colors.grey.shade100),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const Divider(height: 1),

                // Scrollable Form Body
                Flexible(
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    padding: EdgeInsets.fromLTRB(20, 16, 20, viewInsetsBottom + 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // SECTION 1: INFORMASI UTAMA
                        _buildSectionHeader(Icons.info_outline_rounded, '1. Informasi Utama Kuis'),
                        const SizedBox(height: 8),

                        const Text('Judul Quiz / Ujian *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                        const SizedBox(height: 6),
                        TextField(
                          controller: judulController,
                          decoration: InputDecoration(
                            hintText: 'Contoh: Kuis 1 Pemrograman Dasar CBT',
                            prefixIcon: const Icon(Icons.title_rounded, size: 20),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          ),
                        ),
                        const SizedBox(height: 14),

                        if (mapels.isNotEmpty) ...[
                          const Text('Mata Pelajaran *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          const SizedBox(height: 6),
                          DropdownButtonFormField<int>(
                            initialValue: selectedMapelId,
                            isExpanded: true,
                            decoration: InputDecoration(
                              prefixIcon: const Icon(Icons.book_rounded, size: 20),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            ),
                            items: mapels.map((mp) {
                              final id = mp['id'] is int ? mp['id'] as int : int.parse(mp['id'].toString());
                              return DropdownMenuItem<int>(
                                value: id,
                                child: Text(
                                  mp['nama_mapel']?.toString() ?? 'Mapel #$id',
                                  style: const TextStyle(fontSize: 13),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setModalState(() {
                                  selectedMapelId = val;
                                });
                              }
                            },
                          ),
                          const SizedBox(height: 14),
                        ],

                        if (kelases.isNotEmpty) ...[
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('Kelas Target *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                              InkWell(
                                onTap: () {
                                  setModalState(() {
                                    if (selectedKelasIds.length == kelases.length) {
                                      selectedKelasIds = [(kelases.first['id'] is int ? kelases.first['id'] as int : int.parse(kelases.first['id'].toString()))];
                                    } else {
                                      selectedKelasIds = kelases.map((k) => k['id'] is int ? k['id'] as int : int.parse(k['id'].toString())).toList();
                                    }
                                  });
                                },
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 6),
                                  child: Text(
                                    selectedKelasIds.length == kelases.length ? 'Pilih 1 Saja' : 'Pilih Semua Kelas',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.purple.shade800),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: kelases.map((kls) {
                              final id = kls['id'] is int ? kls['id'] as int : int.parse(kls['id'].toString());
                              final isSelected = selectedKelasIds.contains(id);
                              return FilterChip(
                                selected: isSelected,
                                label: Text(kls['nama_kelas']?.toString() ?? 'Kelas #$id'),
                                labelStyle: TextStyle(
                                  fontSize: 11,
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                  color: isSelected ? Colors.purple.shade900 : Colors.black87,
                                ),
                                selectedColor: Colors.purple.shade100,
                                backgroundColor: Colors.grey.shade100,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10),
                                  side: BorderSide(color: isSelected ? Colors.purple.shade400 : Colors.grey.shade300),
                                ),
                                showCheckmark: true,
                                checkmarkColor: Colors.purple.shade900,
                                onSelected: (sel) {
                                  setModalState(() {
                                    if (sel) {
                                      selectedKelasIds.add(id);
                                    } else {
                                      if (selectedKelasIds.length > 1) {
                                        selectedKelasIds.remove(id);
                                      }
                                    }
                                  });
                                },
                              );
                            }).toList(),
                          ),
                          const SizedBox(height: 4),
                          const Text('Bisa memilih lebih dari 1 kelas sekaligus.', style: TextStyle(fontSize: 10, color: Colors.grey)),
                          const SizedBox(height: 18),
                        ],

                        // SECTION 2: KATEGORI & TOKEN
                        _buildSectionHeader(Icons.military_tech_rounded, '2. Kategori Pelaksanaan & Kunci Akses'),
                        const SizedBox(height: 8),

                        const Text('Kategori Pelaksanaan Ujian *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                        const SizedBox(height: 6),
                        DropdownButtonFormField<String>(
                          initialValue: selectedKategori,
                          isExpanded: true,
                          decoration: InputDecoration(
                            prefixIcon: const Icon(Icons.stars_rounded, size: 20, color: Colors.amber),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          ),
                          items: const [
                            DropdownMenuItem(
                              value: 'kuis',
                              child: Text('📝 Kuis Harian / Evaluasi CBT (Token Opsional)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis),
                            ),
                            DropdownMenuItem(
                              value: 'uts',
                              child: Text('🏆 UTS (Ujian Tengah Semester) - Auto Token 🔑', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis),
                            ),
                            DropdownMenuItem(
                              value: 'uas',
                              child: Text('🎓 UAS (Ujian Akhir Semester) - Auto Token 🔑', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis),
                            ),
                          ],
                          onChanged: (val) {
                            if (val != null) {
                              setModalState(() {
                                selectedKategori = val;
                                if (val == 'uts') {
                                  if (accessKeyController.text.trim().isEmpty || !accessKeyController.text.startsWith('UTS-')) {
                                    accessKeyController.text = generateRandomToken(prefix: 'UTS');
                                  }
                                } else if (val == 'uas') {
                                  if (accessKeyController.text.trim().isEmpty || !accessKeyController.text.startsWith('UAS-')) {
                                    accessKeyController.text = generateRandomToken(prefix: 'UAS');
                                  }
                                }
                              });
                            }
                          },
                        ),
                        const SizedBox(height: 14),

                        const Text('Kunci Akses (Token Ujian)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Expanded(
                              child: TextField(
                                controller: accessKeyController,
                                textCapitalization: TextCapitalization.characters,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontFamily: 'monospace', letterSpacing: 1.5, fontSize: 14),
                                decoration: InputDecoration(
                                  hintText: selectedKategori == 'kuis' ? 'Opsional untuk Kuis Harian' : 'Wajib (Auto Terisi)',
                                  prefixIcon: const Icon(Icons.key_rounded, size: 20, color: Colors.redAccent),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  filled: true,
                                  fillColor: Colors.amber.shade50.withAlpha(80),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            OutlinedButton.icon(
                              onPressed: () {
                                setModalState(() {
                                  final pfx = selectedKategori != 'kuis' ? selectedKategori.toUpperCase() : '';
                                  accessKeyController.text = generateRandomToken(prefix: pfx);
                                });
                              },
                              icon: const Icon(Icons.refresh_rounded, size: 16),
                              label: const Text('Acak Token', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.purple.shade800,
                                side: BorderSide(color: Colors.purple.shade300),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        const Text('Otomatis terisi jika memilih UTS / UAS (atau klik tombol Acak Token).', style: TextStyle(fontSize: 10, color: Colors.grey)),
                        const SizedBox(height: 18),

                        // SECTION 3: WAKTU & PENGATURAN
                        _buildSectionHeader(Icons.tune_rounded, '3. Durasi, Batas Waktu & Urutan'),
                        const SizedBox(height: 8),

                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Durasi (Menit) *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                  const SizedBox(height: 6),
                                  TextField(
                                    controller: durasiController,
                                    keyboardType: TextInputType.number,
                                    decoration: InputDecoration(
                                      hintText: '30',
                                      prefixIcon: const Icon(Icons.timer_rounded, size: 20),
                                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Acak Urutan Soal *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                  const SizedBox(height: 6),
                                  DropdownButtonFormField<String>(
                                    initialValue: selectedRandomSoal,
                                    isExpanded: true,
                                    decoration: InputDecoration(
                                      prefixIcon: const Icon(Icons.shuffle_rounded, size: 20),
                                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                    ),
                                    items: const [
                                      DropdownMenuItem(
                                        value: 'Y',
                                        child: Text('Ya (Acak)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis),
                                      ),
                                      DropdownMenuItem(
                                        value: 'N',
                                        child: Text('Tidak (Urut)', style: TextStyle(fontSize: 12), overflow: TextOverflow.ellipsis),
                                      ),
                                    ],
                                    onChanged: (val) {
                                      if (val != null) {
                                        setModalState(() {
                                          selectedRandomSoal = val;
                                        });
                                      }
                                    },
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),

                        // Interactive Deadline Card
                        const Text('Batas Waktu / Deadline (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: selectedDeadline != null ? Colors.red.shade50 : Colors.grey.shade50,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: selectedDeadline != null ? Colors.red.shade200 : Colors.grey.shade300),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: selectedDeadline != null ? Colors.red.shade100 : Colors.grey.shade200,
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  selectedDeadline != null ? Icons.alarm_on_rounded : Icons.alarm_off_rounded,
                                  size: 20,
                                  color: selectedDeadline != null ? Colors.red.shade800 : Colors.grey.shade600,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      selectedDeadline != null
                                          ? 'Batas: ${DateFormat('dd MMM yyyy, HH:mm').format(selectedDeadline!)} WIB'
                                          : 'Tanpa Batas Waktu',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12,
                                        color: selectedDeadline != null ? Colors.red.shade900 : Colors.black87,
                                      ),
                                    ),
                                    Text(
                                      selectedDeadline != null
                                          ? 'Kuis otomatis ditutup setelah jadwal ini'
                                          : 'Kuis dapat dikerjakan kapan saja oleh siswa',
                                      style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                                    ),
                                  ],
                                ),
                              ),
                              if (selectedDeadline != null)
                                IconButton(
                                  icon: const Icon(Icons.close_rounded, size: 18, color: Colors.red),
                                  visualDensity: VisualDensity.compact,
                                  padding: const EdgeInsets.all(4),
                                  constraints: const BoxConstraints(),
                                  onPressed: () {
                                    setModalState(() {
                                      selectedDeadline = null;
                                    });
                                  },
                                  tooltip: 'Hapus Batas Waktu',
                                ),
                              ElevatedButton(
                                onPressed: () async {
                                  final now = DateTime.now();
                                  final initialDate = selectedDeadline ?? now.add(const Duration(days: 1));
                                  final pickedDate = await showDatePicker(
                                    context: context,
                                    initialDate: initialDate,
                                    firstDate: now,
                                    lastDate: now.add(const Duration(days: 365)),
                                    builder: (ctx, child) => Theme(
                                      data: Theme.of(ctx).copyWith(
                                        colorScheme: ColorScheme.light(primary: Colors.purple.shade800),
                                      ),
                                      child: child!,
                                    ),
                                  );

                                  if (pickedDate != null) {
                                    if (!context.mounted) return;
                                    final initialTime = selectedDeadline != null
                                        ? TimeOfDay(hour: selectedDeadline!.hour, minute: selectedDeadline!.minute)
                                        : const TimeOfDay(hour: 23, minute: 59);
                                    final pickedTime = await showTimePicker(
                                      context: context,
                                      initialTime: initialTime,
                                      builder: (ctx, child) => Theme(
                                        data: Theme.of(ctx).copyWith(
                                          colorScheme: ColorScheme.light(primary: Colors.purple.shade800),
                                        ),
                                        child: child!,
                                      ),
                                    );

                                    if (pickedTime != null) {
                                      setModalState(() {
                                        selectedDeadline = DateTime(
                                          pickedDate.year,
                                          pickedDate.month,
                                          pickedDate.day,
                                          pickedTime.hour,
                                          pickedTime.minute,
                                        );
                                      });
                                    }
                                  }
                                },
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: selectedDeadline != null ? Colors.red.shade700 : Colors.purple.shade700,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                  textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  visualDensity: VisualDensity.compact,
                                ),
                                child: Text(selectedDeadline != null ? 'Ubah' : 'Atur Waktu'),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 14),

                        // Kesempatan Mengerjakan
                        const Text('Kesempatan Mengerjakan (Max Attempts)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                        const SizedBox(height: 6),
                        DropdownButtonFormField<int>(
                          initialValue: selectedMaxAttempts,
                          isExpanded: true,
                          decoration: InputDecoration(
                            prefixIcon: const Icon(Icons.replay_rounded, size: 20, color: Colors.blue),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          ),
                          items: const [
                            DropdownMenuItem(value: 1, child: Text('1x Percobaan (Standar)', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 2, child: Text('2x Percobaan', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 3, child: Text('3x Percobaan (Ambil Nilai Tertinggi)', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 5, child: Text('5x Percobaan', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 0, child: Text('Tanpa Batas (Unlimited Attempts)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold))),
                          ],
                          onChanged: (val) {
                            if (val != null) {
                              setModalState(() {
                                selectedMaxAttempts = val;
                              });
                            }
                          },
                        ),
                        const SizedBox(height: 4),
                        const Text('Siswa dapat mengulang kuis & sistem mengambil Nilai Tertinggi.', style: TextStyle(fontSize: 10, color: Colors.grey)),
                        const SizedBox(height: 18),

                        // SECTION 4: PETUNJUK
                        _buildSectionHeader(Icons.description_rounded, '4. Petunjuk Ujian (Opsional)'),
                        const SizedBox(height: 8),

                        TextField(
                          controller: deskripsiController,
                          maxLines: 2,
                          decoration: InputDecoration(
                            hintText: 'Petunjuk pengerjaan quiz CBT...',
                            prefixIcon: const Icon(Icons.notes_rounded, size: 20),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          ),
                        ),
                        const SizedBox(height: 22),

                        // Submit Button
                        ElevatedButton.icon(
                          onPressed: isSubmitting
                              ? null
                              : () async {
                                  if (judulController.text.trim().isEmpty) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Judul ujian wajib diisi!'), backgroundColor: Colors.red),
                                    );
                                    return;
                                  }

                                  if (selectedKelasIds.isEmpty) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Pilih minimal satu kelas target!'), backgroundColor: Colors.red),
                                    );
                                    return;
                                  }

                                  setModalState(() => isSubmitting = true);

                                  final nav = Navigator.of(context);
                                  final messenger = ScaffoldMessenger.of(context);
                                  final durasi = int.tryParse(durasiController.text) ?? 30;

                                  String? tokenValue = accessKeyController.text.trim().toUpperCase();
                                  if (selectedKategori != 'kuis' && tokenValue.isEmpty) {
                                    tokenValue = generateRandomToken(prefix: selectedKategori.toUpperCase());
                                  }

                                  final deadlineStr = selectedDeadline != null
                                      ? DateFormat('yyyy-MM-dd HH:mm:ss').format(selectedDeadline!)
                                      : null;

                                  final ok = isEdit
                                      ? await guruProvider.updateQuiz(
                                          user.id,
                                          quizToEdit.id,
                                          judulController.text.trim(),
                                          deskripsiController.text.trim(),
                                          selectedMapelId,
                                          selectedKelasIds.first,
                                          durasi,
                                          kategori: selectedKategori,
                                          deadline: deadlineStr,
                                          randomSoal: selectedRandomSoal,
                                          randomJawaban: 'Y',
                                          maxAttempts: selectedMaxAttempts,
                                          accessKey: tokenValue.isNotEmpty ? tokenValue : null,
                                          kelasIds: selectedKelasIds,
                                        )
                                      : await guruProvider.createQuiz(
                                          user.id,
                                          judulController.text.trim(),
                                          deskripsiController.text.trim(),
                                          selectedMapelId,
                                          selectedKelasIds.first,
                                          durasi,
                                          kategori: selectedKategori,
                                          deadline: deadlineStr,
                                          randomSoal: selectedRandomSoal,
                                          randomJawaban: 'Y',
                                          maxAttempts: selectedMaxAttempts,
                                          accessKey: tokenValue.isNotEmpty ? tokenValue : null,
                                          kelasIds: selectedKelasIds,
                                        );

                                  if (context.mounted) {
                                    nav.pop();
                                    messenger.showSnackBar(
                                      SnackBar(
                                        content: Text(ok
                                            ? (isEdit ? 'Quiz CBT berhasil diperbarui! ✅' : 'Quiz CBT berhasil diterbitkan! ✅')
                                            : (isEdit ? 'Gagal memperbarui quiz ❌' : 'Gagal membuat quiz ❌')),
                                        backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
                                      ),
                                    );
                                  }
                                },
                          icon: isSubmitting
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                )
                              : Icon(isEdit ? Icons.save_rounded : Icons.publish_rounded, size: 18),
                          label: Text(isSubmitting
                              ? (isEdit ? 'Menyimpan Perubahan...' : 'Menerbitkan Ujian...')
                              : (isEdit ? 'Simpan Perubahan Quiz' : 'Terbitkan Ujian CBT')),
                          style: ElevatedButton.styleFrom(
                            minimumSize: const Size(double.infinity, 52),
                            backgroundColor: isEdit ? Colors.blue.shade800 : Colors.purple.shade800,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            elevation: 2,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSectionHeader(IconData icon, String title) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.purple.shade800),
        const SizedBox(width: 6),
        Text(
          title,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            color: Colors.purple.shade900,
            letterSpacing: 0.2,
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final quizList = guruProvider.quizList;
    final pendingCount = guruProvider.pendingQuizSusulanCount;
    final koreksiList = guruProvider.koreksiList;
    final perluKoreksiCount = koreksiList.where((e) => (int.tryParse(e['ungraded_essay_count'].toString()) ?? 0) > 0).length;

    final filteredQuiz = quizList.where((q) {
      final matchesStatus = _selectedStatus == 'Semua' || q.status.toLowerCase() == _selectedStatus.toLowerCase();
      final matchesSearch = _searchQuery.isEmpty ||
          q.judul.toLowerCase().contains(_searchQuery) ||
          q.namaMapel.toLowerCase().contains(_searchQuery) ||
          (q.namaKelas ?? '').toLowerCase().contains(_searchQuery);
      return matchesStatus && matchesSearch;
    }).toList();

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      color: AppTheme.primaryColor,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 🚀 EXECUTIVE HERO BANNER CARD
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: AppTheme.guruGradient,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withAlpha(60),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Wrap(
                    alignment: WrapAlignment.spaceBetween,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: const Color(0xFF38BDF8),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.quiz_rounded, size: 14, color: Color(0xFF0F172A)),
                            SizedBox(width: 4),
                            Text(
                              'CBT & Quiz Center',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                            ),
                          ],
                        ),
                      ),
                      ElevatedButton.icon(
                        onPressed: _showAddQuizModal,
                        icon: const Icon(Icons.add_circle_rounded, size: 15),
                        label: const Text('Buat Quiz'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.purple.shade600,
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
                    'Kelola Ujian CBT & Kuis',
                    style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Buat kuis interaktif, kelola bank soal, dan konfirmasi permohonan izin susulan siswa.',
                    style: TextStyle(fontSize: 12, color: Colors.white.withAlpha(200), height: 1.4),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 📊 KPI STATS STRIP
            Row(
              children: [
                Expanded(
                  child: _buildKpiCard(
                    title: 'Total Quiz CBT',
                    value: '${quizList.length} Quiz',
                    icon: Icons.quiz_outlined,
                    iconColor: Colors.purple.shade700,
                    bgColor: Colors.purple.shade50,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: InkWell(
                    onTap: _showSusulanRequestsModal,
                    borderRadius: BorderRadius.circular(16),
                    child: _buildKpiCard(
                      title: 'Izin Susulan',
                      value: '$pendingCount Pending',
                      icon: pendingCount > 0 ? Icons.mark_email_unread_rounded : Icons.mark_email_read_rounded,
                      iconColor: pendingCount > 0 ? Colors.amber.shade900 : Colors.blue.shade700,
                      bgColor: pendingCount > 0 ? Colors.amber.shade50 : Colors.blue.shade50,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),

            // 📩 BANNER PERMINTAAN IZIN SUSULAN CARD
            Container(
              decoration: BoxDecoration(
                color: pendingCount > 0 ? Colors.amber.shade50 : Colors.blue.shade50,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  color: pendingCount > 0 ? Colors.amber.shade400 : Colors.blue.shade200,
                  width: pendingCount > 0 ? 1.5 : 1.0,
                ),
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  borderRadius: BorderRadius.circular(18),
                  onTap: _showSusulanRequestsModal,
                  child: Padding(
                    padding: const EdgeInsets.all(14.0),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: pendingCount > 0 ? Colors.amber.shade800 : AppTheme.primaryColor,
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            pendingCount > 0 ? Icons.notification_important_rounded : Icons.mail_rounded,
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
                                  fontSize: 13,
                                  color: pendingCount > 0 ? Colors.amber.shade900 : Colors.blue.shade900,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                pendingCount > 0
                                    ? "Siswa mengajukan permohonan susulan/buka suspend. Klik untuk ACC / Tolak."
                                    : "Lihat riwayat persetujuan izin susulan dan pembukaan suspend kuis.",
                                style: TextStyle(
                                  fontSize: 11,
                                  color: pendingCount > 0 ? Colors.amber.shade800 : Colors.blue.shade800,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Colors.grey),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 10),

            // 📝 BANNER KOREKSI JAWABAN ESSAY SISWA CARD
            Container(
              decoration: BoxDecoration(
                color: perluKoreksiCount > 0 ? const Color(0xFFECFDF5) : Colors.teal.shade50,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  color: perluKoreksiCount > 0 ? const Color(0xFF10B981) : Colors.teal.shade200,
                  width: perluKoreksiCount > 0 ? 1.5 : 1.0,
                ),
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  borderRadius: BorderRadius.circular(18),
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const GuruKoreksiQuizScreen()),
                    );
                  },
                  child: Padding(
                    padding: const EdgeInsets.all(14.0),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: perluKoreksiCount > 0 ? const Color(0xFF047857) : Colors.teal,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.edit_note_rounded,
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
                                perluKoreksiCount > 0
                                    ? "📝 Ada $perluKoreksiCount Pengerjaan Perlu Koreksi!"
                                    : "Koreksi Hasil Quiz & Jawaban Essay",
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                  color: perluKoreksiCount > 0 ? const Color(0xFF064E3B) : Colors.teal.shade900,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                perluKoreksiCount > 0
                                    ? "Jawaban essay siswa telah dikirim. Klik untuk mengoreksi & memberi nilai."
                                    : "Periksa lembar pengerjaan kuis siswa dan finalisasi nilai akhir.",
                                style: TextStyle(
                                  fontSize: 11,
                                  color: perluKoreksiCount > 0 ? const Color(0xFF047857) : Colors.teal.shade800,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Colors.grey),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // 🔍 SEARCH BAR & STATUS FILTER CHIPS
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari judul kuis, mapel, atau kelas...',
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

            // Status Filter Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _statusList.map((st) {
                  final isSel = _selectedStatus == st;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      selected: isSel,
                      label: Text(st),
                      labelStyle: TextStyle(
                        fontSize: 12,
                        fontWeight: isSel ? FontWeight.bold : FontWeight.w500,
                        color: isSel ? Colors.white : Colors.black87,
                      ),
                      selectedColor: Colors.purple.shade800,
                      backgroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: BorderSide(color: isSel ? Colors.purple.shade800 : Colors.grey.shade300),
                      ),
                      onSelected: (selected) {
                        setState(() {
                          _selectedStatus = st;
                        });
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 16),

            // 📑 QUIZ LIST
            if (guruProvider.isLoading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 40),
                child: Center(child: CircularProgressIndicator()),
              )
            else if (filteredQuiz.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      Icon(Icons.quiz_outlined, size: 54, color: Colors.grey.shade400),
                      const SizedBox(height: 12),
                      const Text(
                        'Belum Ada Ujian CBT / Quiz',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _searchQuery.isNotEmpty
                            ? 'Tidak ada kuis sesuai kata kunci pencarian.'
                            : 'Klik "+ Buat Quiz" untuk merilis ujian CBT baru.',
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
                itemCount: filteredQuiz.length,
                itemBuilder: (context, index) {
                  final q = filteredQuiz[index];
                  final isPublished = q.status.toLowerCase() == 'published';

                  return Container(
                    margin: const EdgeInsets.only(bottom: 14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
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
                      borderRadius: BorderRadius.circular(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Status Accent Bar
                          Container(
                            height: 4,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: isPublished
                                    ? [Colors.green.shade400, Colors.teal]
                                    : [Colors.amber.shade400, Colors.orange],
                              ),
                            ),
                          ),

                          Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Mapel, Kategori & Status Badges
                                Wrap(
                                  alignment: WrapAlignment.spaceBetween,
                                  crossAxisAlignment: WrapCrossAlignment.center,
                                  spacing: 6,
                                  runSpacing: 6,
                                  children: [
                                    Wrap(
                                      spacing: 6,
                                      runSpacing: 4,
                                      crossAxisAlignment: WrapCrossAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: Colors.purple.shade50,
                                            borderRadius: BorderRadius.circular(20),
                                            border: Border.all(color: Colors.purple.shade200),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Icon(Icons.book_rounded, size: 12, color: Colors.purple.shade800),
                                              const SizedBox(width: 4),
                                              ConstrainedBox(
                                                constraints: const BoxConstraints(maxWidth: 130),
                                                child: Text(
                                                  q.namaMapel,
                                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.purple.shade800),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: q.isUts || q.isUas ? Colors.amber.shade100 : Colors.blue.shade50,
                                            borderRadius: BorderRadius.circular(20),
                                            border: Border.all(
                                              color: q.isUts || q.isUas ? Colors.amber.shade400 : Colors.blue.shade200,
                                            ),
                                          ),
                                          child: Text(
                                            q.kategoriBadgeText,
                                            style: TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: q.isUts || q.isUas ? Colors.amber.shade900 : Colors.blue.shade900,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: isPublished ? Colors.green.shade50 : Colors.amber.shade50,
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(color: isPublished ? Colors.green.shade300 : Colors.amber.shade300),
                                      ),
                                      child: Text(
                                        q.status.toUpperCase(),
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: isPublished ? Colors.green.shade800 : Colors.amber.shade900,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 10),

                                // Title
                                Text(
                                  q.judul,
                                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.black87, height: 1.3),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 6),

                                // Info Row (Durasi & Peserta)
                                Wrap(
                                  spacing: 12,
                                  runSpacing: 6,
                                  crossAxisAlignment: WrapCrossAlignment.center,
                                  children: [
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.timer_rounded, size: 14, color: Colors.grey.shade600),
                                        const SizedBox(width: 4),
                                        Text(
                                          '${q.durasiMenit} Menit',
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                        ),
                                      ],
                                    ),
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.people_alt_rounded, size: 14, color: Colors.grey.shade600),
                                        const SizedBox(width: 4),
                                        Text(
                                          '${q.totalPeserta ?? 0} Peserta',
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                        ),
                                      ],
                                    ),
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.groups_rounded, size: 14, color: Colors.grey.shade600),
                                        const SizedBox(width: 4),
                                        ConstrainedBox(
                                          constraints: const BoxConstraints(maxWidth: 160),
                                          child: Text(
                                            q.namaKelas ?? 'Semua Kelas',
                                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),

                                // Meta Tags: Token, Deadline, Acak Soal, Attempts
                                Wrap(
                                  spacing: 6,
                                  runSpacing: 6,
                                  children: [
                                    if (q.accessKey != null && q.accessKey!.trim().isNotEmpty)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.amber.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: Colors.amber.shade300),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(Icons.key_rounded, size: 11, color: Colors.amber.shade900),
                                            const SizedBox(width: 3),
                                            Text(
                                              'Token: ${q.accessKey}',
                                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                            ),
                                          ],
                                        ),
                                      ),
                                    if (q.deadline != null && q.deadline!.trim().isNotEmpty)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.red.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: Colors.red.shade200),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(Icons.timer_outlined, size: 11, color: Colors.red.shade700),
                                            const SizedBox(width: 3),
                                            Text(
                                              'Batas: ${q.deadline}',
                                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.red.shade700),
                                            ),
                                          ],
                                        ),
                                      ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: Colors.grey.shade100,
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(color: Colors.grey.shade300),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            q.randomSoal == 'Y' ? Icons.shuffle_rounded : Icons.format_list_numbered_rounded,
                                            size: 11,
                                            color: Colors.grey.shade700,
                                          ),
                                          const SizedBox(width: 3),
                                          Text(
                                            q.randomSoal == 'Y' ? 'Soal Diacak' : 'Soal Urut',
                                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Colors.grey.shade800),
                                          ),
                                        ],
                                      ),
                                    ),
                                    if (q.maxAttempts > 1 || q.isUnlimitedAttempts)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.blue.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: Colors.blue.shade200),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(Icons.replay_rounded, size: 11, color: Colors.blue.shade700),
                                            const SizedBox(width: 3),
                                            Text(
                                              q.isUnlimitedAttempts ? 'Percobaan Bebas' : '${q.maxAttempts}x Percobaan',
                                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                                            ),
                                          ],
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 14),

                                // Action Row (Edit, Kelola Soal, Salin Token, Hapus)
                                Wrap(
                                  spacing: 8,
                                  runSpacing: 8,
                                  crossAxisAlignment: WrapCrossAlignment.center,
                                  children: [
                                    if (q.accessKey != null && q.accessKey!.trim().isNotEmpty)
                                      OutlinedButton.icon(
                                        onPressed: () {
                                          Clipboard.setData(ClipboardData(text: q.accessKey!.trim()));
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(
                                              content: Text('Token "${q.accessKey}" berhasil disalin! 📋'),
                                              backgroundColor: Colors.purple.shade800,
                                              duration: const Duration(seconds: 2),
                                            ),
                                          );
                                        },
                                        icon: const Icon(Icons.copy_rounded, size: 13),
                                        label: const Text('Salin Token'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: Colors.purple.shade800,
                                          side: BorderSide(color: Colors.purple.shade300),
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                          textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                      ),
                                    OutlinedButton.icon(
                                      onPressed: () => _showAddQuizModal(q),
                                      icon: const Icon(Icons.edit_note_rounded, size: 15),
                                      label: const Text('Edit Quiz'),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: Colors.blue.shade800,
                                        side: BorderSide(color: Colors.blue.shade300),
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                        textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      ),
                                    ),
                                    ElevatedButton.icon(
                                      onPressed: () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(builder: (_) => GuruBankSoalScreen(quiz: q)),
                                        );
                                      },
                                      icon: const Icon(Icons.format_list_bulleted_rounded, size: 14),
                                      label: const Text('Kelola Soal'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.purple.shade800,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                        textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        elevation: 1,
                                      ),
                                    ),
                                    IconButton(
                                      tooltip: 'Hapus Quiz',
                                      visualDensity: VisualDensity.compact,
                                      padding: const EdgeInsets.all(4),
                                      constraints: const BoxConstraints(),
                                      icon: Icon(Icons.delete_outline_rounded, color: Colors.red.shade400, size: 20),
                                      onPressed: () => _confirmDeleteQuiz(q),
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

  void _confirmDeleteQuiz(QuizModel q) {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(10)),
              child: Icon(Icons.delete_forever_rounded, color: Colors.red.shade700, size: 22),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text('Hapus Ujian CBT?', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        content: Text(
          'Apakah Anda yakin ingin menghapus ujian "${q.judul}"? Semua butir soal dan data pengerjaan siswa terkait kuis ini akan dihapus.',
          style: const TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final messenger = ScaffoldMessenger.of(context);
              final ok = await guruProvider.deleteQuiz(user.id, q.id);
              messenger.showSnackBar(
                SnackBar(
                  content: Text(ok ? 'Quiz "${q.judul}" berhasil dihapus! 🗑️' : 'Gagal menghapus quiz ❌'),
                  backgroundColor: ok ? Colors.green.shade700 : Colors.red,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade700,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Hapus Quiz'),
          ),
        ],
      ),
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
  }) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isCompact = constraints.maxWidth < 150;
        return Container(
          padding: EdgeInsets.all(isCompact ? 10 : 12),
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
                padding: EdgeInsets.all(isCompact ? 6 : 8),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: iconColor, size: isCompact ? 18 : 20),
              ),
              SizedBox(width: isCompact ? 6 : 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      title,
                      style: TextStyle(fontSize: isCompact ? 10 : 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                      overflow: TextOverflow.ellipsis,
                      maxLines: 1,
                    ),
                    Text(
                      value,
                      style: TextStyle(fontSize: isCompact ? 12 : 14, fontWeight: FontWeight.bold, color: Colors.black87),
                      overflow: TextOverflow.ellipsis,
                      maxLines: 1,
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
