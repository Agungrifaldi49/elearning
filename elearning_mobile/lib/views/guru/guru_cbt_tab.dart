import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';
import 'guru_bank_soal_screen.dart';

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
            final requests = guruProvider.susulanList;
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
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
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
                          const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Permintaan Izin Susulan / Suspend',
                                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                              Text(
                                'Konfirmasi pengajuan ujian susulan siswa',
                                style: TextStyle(fontSize: 11, color: Colors.grey),
                              ),
                            ],
                          ),
                        ],
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
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.end,
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



  void _showAddQuizModal() {
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final judulController = TextEditingController();
    final deskripsiController = TextEditingController();
    final durasiController = TextEditingController(text: '30');

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

    int selectedMapelId = mapels.isNotEmpty ? (mapels.first['id'] is int ? mapels.first['id'] as int : int.parse(mapels.first['id'].toString())) : 1;
    int selectedKelasId = kelases.isNotEmpty ? (kelases.first['id'] is int ? kelases.first['id'] as int : int.parse(kelases.first['id'].toString())) : 1;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          return Container(
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
            child: SingleChildScrollView(
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
                          color: Colors.purple.shade50,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(Icons.quiz_rounded, color: Colors.purple.shade800, size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Buat Ujian CBT / Quiz Baru', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                            Text('Atur judul, mapel, kelas rujukan, dan durasi', style: TextStyle(fontSize: 11, color: Colors.grey)),
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

                  const Text('Judul Ujian CBT / Quiz *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: judulController,
                    decoration: InputDecoration(
                      hintText: 'Contoh: Kuis 1 Dasar-Dasar Kejuruan',
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
                    const Text('Kelas Target *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      initialValue: selectedKelasId,
                      decoration: InputDecoration(
                        prefixIcon: const Icon(Icons.groups_rounded, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: kelases.map((kls) {
                        final id = kls['id'] is int ? kls['id'] as int : int.parse(kls['id'].toString());
                        return DropdownMenuItem<int>(
                          value: id,
                          child: Text(
                            kls['nama_kelas']?.toString() ?? 'Kelas #$id',
                            style: const TextStyle(fontSize: 13),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setModalState(() {
                            selectedKelasId = val;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 14),
                  ],

                  const Text('Durasi Pengerjaan (Menit) *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
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
                  const SizedBox(height: 14),

                  const Text('Petunjuk Ujian / Keterangan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: deskripsiController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: 'Petunjuk pengerjaan quiz CBT...',
                      prefixIcon: const Icon(Icons.description_rounded, size: 20),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton.icon(
                    onPressed: () async {
                      if (judulController.text.trim().isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Judul ujian wajib diisi!'), backgroundColor: Colors.red),
                        );
                        return;
                      }

                      final nav = Navigator.of(context);
                      final messenger = ScaffoldMessenger.of(context);
                      final durasi = int.tryParse(durasiController.text) ?? 30;

                      final ok = await guruProvider.createQuiz(
                        user.id,
                        judulController.text.trim(),
                        deskripsiController.text.trim(),
                        selectedMapelId,
                        selectedKelasId,
                        durasi,
                      );

                      nav.pop();
                      messenger.showSnackBar(
                        SnackBar(
                          content: Text(ok ? 'Quiz CBT berhasil diterbitkan!' : 'Gagal membuat quiz'),
                          backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
                        ),
                      );
                    },
                    icon: const Icon(Icons.publish_rounded, size: 18),
                    label: const Text('Terbitkan Ujian CBT'),
                    style: ElevatedButton.styleFrom(
                      minimumSize: const Size(double.infinity, 50),
                      backgroundColor: Colors.purple.shade800,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final quizList = guruProvider.quizList;
    final susulanList = guruProvider.susulanList;
    final pendingCount = susulanList.where((e) => (e['status'] ?? '').toString().toLowerCase() == 'pending').length;

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
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF7C3AED)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
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
                              Icon(Icons.quiz_rounded, size: 14, color: Colors.black87),
                              SizedBox(width: 4),
                              Flexible(
                                child: Text(
                                  'CBT & Quiz Center',
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
                        onPressed: _showAddQuizModal,
                        icon: const Icon(Icons.add_circle_rounded, size: 16),
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
                                // Mapel & Status Badges
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Flexible(
                                      child: Container(
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
                                            Flexible(
                                              child: Text(
                                                q.namaMapel,
                                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.purple.shade800),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
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
                                Row(
                                  children: [
                                    Icon(Icons.timer_rounded, size: 14, color: Colors.grey.shade600),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${q.durasiMenit} Menit',
                                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                    ),
                                    const SizedBox(width: 12),
                                    Icon(Icons.people_alt_rounded, size: 14, color: Colors.grey.shade600),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${q.totalPeserta ?? 0} Peserta',
                                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                    ),
                                    const SizedBox(width: 12),
                                    Icon(Icons.groups_rounded, size: 14, color: Colors.grey.shade600),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        q.namaKelas ?? 'Semua Kelas',
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 14),

                                // Action Row
                                Row(
                                  children: [
                                    Expanded(
                                      child: ElevatedButton.icon(
                                        onPressed: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(builder: (_) => GuruBankSoalScreen(quiz: q)),
                                          );
                                        },
                                        icon: const Icon(Icons.format_list_bulleted_rounded, size: 16),
                                        label: const Text('Kelola Bank Soal'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.purple.shade800,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(vertical: 10),
                                          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          elevation: 1,
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

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
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
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  value,
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.black87),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
