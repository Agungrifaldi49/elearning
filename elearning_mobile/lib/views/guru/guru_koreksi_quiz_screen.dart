import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruKoreksiQuizScreen extends StatefulWidget {
  final int? quizIdFilter;

  const GuruKoreksiQuizScreen({super.key, this.quizIdFilter});

  @override
  State<GuruKoreksiQuizScreen> createState() => _GuruKoreksiQuizScreenState();
}

class _GuruKoreksiQuizScreenState extends State<GuruKoreksiQuizScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  int _selectedQuizId = 0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    if (widget.quizIdFilter != null) {
      _selectedQuizId = widget.quizIdFilter!;
    }
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

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      await Provider.of<GuruProvider>(context, listen: false).fetchKoreksiList(user.id);
    }
    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  void _showDetailKoreksiModal(Map<String, dynamic> attempt) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final quizId = int.parse(attempt['quiz_id'].toString());
    final siswaId = int.parse(attempt['siswa_id'].toString());
    final namaSiswa = attempt['nama_siswa'] ?? 'Siswa';
    final namaKelas = attempt['nama_kelas'] ?? '-';
    final namaQuiz = attempt['nama_quiz'] ?? 'Quiz CBT';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return _KoreksiDetailBottomSheet(
          userId: user.id,
          quizId: quizId,
          siswaId: siswaId,
          namaSiswa: namaSiswa,
          namaKelas: namaKelas,
          namaQuiz: namaQuiz,
          attemptData: attempt,
          onSuccess: () => _loadData(),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final allAttempts = guruProvider.koreksiList;
    final quizList = guruProvider.quizList;

    final filteredList = allAttempts.where((item) {
      final qId = int.tryParse(item['quiz_id'].toString()) ?? 0;
      final matchesQuiz = _selectedQuizId == 0 || qId == _selectedQuizId;

      final sName = (item['nama_siswa'] ?? '').toString().toLowerCase();
      final kName = (item['nama_kelas'] ?? '').toString().toLowerCase();
      final qName = (item['nama_quiz'] ?? '').toString().toLowerCase();
      final matchesSearch = _searchQuery.isEmpty ||
          sName.contains(_searchQuery) ||
          kName.contains(_searchQuery) ||
          qName.contains(_searchQuery);

      return matchesQuiz && matchesSearch;
    }).toList();

    // Stats
    final totalPengerjaan = allAttempts.length;
    final perluKoreksi = allAttempts.where((e) => (int.tryParse(e['ungraded_essay_count'].toString()) ?? 0) > 0).length;
    final selesaiKoreksi = totalPengerjaan - perluKoreksi;

    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      appBar: AppBar(
        title: const Text('Koreksi Hasil Quiz CBT', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppTheme.primaryColor,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 🚀 EXECUTIVE HERO HEADER CARD
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF047857)],
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
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.amber,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.rule_folder_rounded, size: 14, color: Colors.black87),
                              SizedBox(width: 4),
                              Text(
                                'Koreksi & Penilaian Essay',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.black87),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(30),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            '$perluKoreksi Perlu Koreksi',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Koreksi Lembar Ujian Siswa',
                      style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Periksa jawaban essay siswa, berikan skor nilai, dan terbitkan nilai akhir hasil ujian CBT.',
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
                      title: 'Total Lembar',
                      value: '$totalPengerjaan Lembar',
                      icon: Icons.assignment_rounded,
                      iconColor: Colors.blue.shade700,
                      bgColor: Colors.blue.shade50,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Perlu Koreksi',
                      value: '$perluKoreksi Siswa',
                      icon: Icons.pending_actions_rounded,
                      iconColor: Colors.amber.shade900,
                      bgColor: Colors.amber.shade50,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Selesai',
                      value: '$selesaiKoreksi Siswa',
                      icon: Icons.check_circle_rounded,
                      iconColor: const Color(0xFF047857),
                      bgColor: const Color(0xFFECFDF5),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // 🎛️ FILTER QUIZ DROPDOWN
              if (quizList.isNotEmpty) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.grey.shade300),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<int>(
                      value: _selectedQuizId,
                      isExpanded: true,
                      icon: const Icon(Icons.keyboard_arrow_down_rounded),
                      items: [
                        const DropdownMenuItem<int>(
                          value: 0,
                          child: Row(
                            children: [
                              Icon(Icons.collections_bookmark_rounded, size: 18, color: AppTheme.primaryColor),
                              SizedBox(width: 8),
                              Text('Semua Quiz CBT', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                            ],
                          ),
                        ),
                        ...quizList.map((q) {
                          return DropdownMenuItem<int>(
                            value: q.id,
                            child: Text(
                              '${q.judul} (${q.namaMapel})',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedQuizId = val;
                          });
                        }
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 12),
              ],

              // 🔍 SEARCH INPUT
              TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Cari nama siswa, NIS, kelas, atau kuis...',
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
              const SizedBox(height: 16),

              // 📑 ATTEMPTS LIST CARDS
              if (_isLoading)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 40),
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (filteredList.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Column(
                      children: [
                        Icon(Icons.fact_check_outlined, size: 54, color: Colors.grey.shade400),
                        const SizedBox(height: 12),
                        const Text(
                          'Belum Ada Pengerjaan Quiz Siswa',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _searchQuery.isNotEmpty
                              ? 'Tidak ditemukan pengerjaan siswa sesuai kata kunci.'
                              : 'Siswa yang telah menyelesaikan CBT akan tampil di sini untuk dikoreksi.',
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
                  itemCount: filteredList.length,
                  itemBuilder: (context, index) {
                    final item = filteredList[index];
                    final namaSiswa = item['nama_siswa'] ?? 'Siswa';
                    final nis = item['nis'] ?? '-';
                    final namaKelas = item['nama_kelas'] ?? '-';
                    final namaQuiz = item['nama_quiz'] ?? item['judul_quiz'] ?? 'Quiz CBT';
                    final namaMapel = item['nama_mapel'] ?? '';
                    final totalNilai = double.tryParse(item['total_nilai'].toString()) ?? 0.0;
                    final ungradedCount = int.tryParse(item['ungraded_essay_count'].toString()) ?? 0;
                    final totalEssay = int.tryParse(item['total_essay_count'].toString()) ?? 0;
                    final needsCorrection = ungradedCount > 0;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: needsCorrection ? Colors.amber.shade300 : Colors.grey.shade200,
                          width: needsCorrection ? 1.5 : 1.0,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: needsCorrection ? Colors.amber.withAlpha(15) : Colors.black.withAlpha(8),
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
                            // Top Accent Bar
                            Container(
                              height: 4,
                              width: double.infinity,
                              color: needsCorrection ? Colors.amber.shade800 : const Color(0xFF10B981),
                            ),

                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Header Row: Student Info & Status Chip
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      CircleAvatar(
                                        radius: 20,
                                        backgroundColor: AppTheme.primaryColor.withAlpha(20),
                                        child: Text(
                                          namaSiswa.isNotEmpty ? namaSiswa.substring(0, 1).toUpperCase() : 'S',
                                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              namaSiswa,
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              'NIS: $nis • Kelas: $namaKelas',
                                              style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: needsCorrection ? Colors.amber.shade50 : const Color(0xFFECFDF5),
                                          borderRadius: BorderRadius.circular(20),
                                          border: Border.all(
                                            color: needsCorrection ? Colors.amber.shade300 : const Color(0xFFA7F3D0),
                                          ),
                                        ),
                                        child: Text(
                                          needsCorrection ? 'PERLU KOREKSI ⏳' : 'SELESAI ✅',
                                          style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            color: needsCorrection ? Colors.amber.shade900 : const Color(0xFF064E3B),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),

                                  // Quiz Title & Mapel
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade50,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: Colors.grey.shade200),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          namaQuiz,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppTheme.primaryColor),
                                        ),
                                        if (namaMapel.isNotEmpty) ...[
                                          const SizedBox(height: 2),
                                          Text(
                                            'Mapel: $namaMapel',
                                            style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                  const SizedBox(height: 12),

                                  // Score & Essay Stats Row
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Row(
                                        children: [
                                          const Icon(Icons.analytics_rounded, size: 16, color: Colors.grey),
                                          const SizedBox(width: 4),
                                          Text(
                                            'Total Nilai: ',
                                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                                          ),
                                          Text(
                                            totalNilai.toStringAsFixed(1),
                                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.black87),
                                          ),
                                        ],
                                      ),
                                      if (totalEssay > 0)
                                        Text(
                                          needsCorrection
                                              ? '$ungradedCount dari $totalEssay Essay Belum Dinilai'
                                              : '$totalEssay Essay Telah Dinilai',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: needsCorrection ? Colors.amber.shade900 : const Color(0xFF047857),
                                          ),
                                        ),
                                    ],
                                  ),
                                  const SizedBox(height: 14),

                                  // Action Button
                                  ElevatedButton.icon(
                                    onPressed: () => _showDetailKoreksiModal(item),
                                    icon: const Icon(Icons.edit_note_rounded, size: 18),
                                    label: Text(needsCorrection ? 'Koreksi Lembar Jawaban' : 'Lihat / Edit Penilaian'),
                                    style: ElevatedButton.styleFrom(
                                      minimumSize: const Size(double.infinity, 44),
                                      backgroundColor: needsCorrection ? Colors.amber.shade800 : AppTheme.primaryColor,
                                      foregroundColor: Colors.white,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                      elevation: 1,
                                    ),
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
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  value,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87),
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

// 📑 BOTTOM SHEET KOREKSI DETAIL COMPONENT
class _KoreksiDetailBottomSheet extends StatefulWidget {
  final int userId;
  final int quizId;
  final int siswaId;
  final String namaSiswa;
  final String namaKelas;
  final String namaQuiz;
  final Map<String, dynamic> attemptData;
  final VoidCallback onSuccess;

  const _KoreksiDetailBottomSheet({
    required this.userId,
    required this.quizId,
    required this.siswaId,
    required this.namaSiswa,
    required this.namaKelas,
    required this.namaQuiz,
    required this.attemptData,
    required this.onSuccess,
  });

  @override
  State<_KoreksiDetailBottomSheet> createState() => _KoreksiDetailBottomSheetState();
}

class _KoreksiDetailBottomSheetState extends State<_KoreksiDetailBottomSheet> {
  List<Map<String, dynamic>> _details = [];
  bool _isLoading = true;
  final Map<int, TextEditingController> _scoreControllers = {};
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  @override
  void dispose() {
    for (var controller in _scoreControllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _fetchDetail() async {
    setState(() => _isLoading = true);
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    final res = await guruProvider.fetchDetailJawabanSiswa(widget.userId, widget.quizId, widget.siswaId);

    if (mounted) {
      setState(() {
        _details = res;
        _isLoading = false;
      });

      for (var item in _details) {
        final soalId = int.parse(item['soal_id'].toString());
        final jns = (item['jenis_soal'] ?? 'pg').toString().toLowerCase();
        if (jns == 'essay') {
          final nilaiExisting = double.tryParse(item['nilai_diberikan']?.toString() ?? '0') ?? 0.0;
          _scoreControllers[soalId] = TextEditingController(text: nilaiExisting > 0 ? nilaiExisting.toStringAsFixed(0) : '0');
        }
      }
    }
  }

  Future<void> _submitScores() async {
    setState(() => _isSaving = true);
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    final Map<int, double> scoresMap = {};

    _scoreControllers.forEach((soalId, controller) {
      final scoreVal = double.tryParse(controller.text.trim()) ?? 0.0;
      scoresMap[soalId] = scoreVal;
    });

    final ok = await guruProvider.submitKoreksiEssay(widget.userId, widget.quizId, widget.siswaId, scoresMap);

    if (mounted) {
      setState(() => _isSaving = false);
      final nav = Navigator.of(context);
      final messenger = ScaffoldMessenger.of(context);
      nav.pop();
      messenger.showSnackBar(
        SnackBar(
          content: Text(ok ? 'Koreksi essay & nilai final kuis berhasil disimpan! ✅' : 'Gagal menyimpan nilai koreksi'),
          backgroundColor: ok ? Colors.green : Colors.red,
        ),
      );
      widget.onSuccess();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
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
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.edit_note_rounded, color: Color(0xFF047857), size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Lembar Koreksi: ${widget.namaSiswa}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    Text('Kelas: ${widget.namaKelas} • ${widget.namaQuiz}', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
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
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _details.isEmpty
                    ? const Center(child: Text('Belum ada data lembar pengerjaan.'))
                    : ListView.builder(
                        itemCount: _details.length,
                        itemBuilder: (context, idx) {
                          final s = _details[idx];
                          final soalId = int.parse(s['soal_id'].toString());
                          final jns = (s['jenis_soal'] ?? 'pg').toString().toLowerCase();
                          final pert = s['pertanyaan'] ?? '';
                          final maxBobot = double.tryParse(s['max_bobot'].toString()) ?? 10.0;
                          final nilaiDiberikan = double.tryParse(s['nilai_diberikan']?.toString() ?? '0') ?? 0.0;
                          final isBenar = (s['is_benar'] ?? 0) == 1;

                          if (jns == 'essay') {
                            final controller = _scoreControllers[soalId] ?? TextEditingController(text: '0');

                            return Container(
                              margin: const EdgeInsets.only(bottom: 14),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: Colors.amber.shade50.withAlpha(80),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.amber.shade300, width: 1.2),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(color: Colors.amber.shade100, borderRadius: BorderRadius.circular(12)),
                                        child: Text('Soal #${idx + 1} (ESSAY)', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber.shade900)),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: BorderRadius.circular(12)),
                                        child: Text('Bobot Maks: ${maxBobot.toStringAsFixed(0)} Poin', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.black87)),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  Text(pert, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.black87)),
                                  const SizedBox(height: 10),

                                  const Text('Jawaban Siswa:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey)),
                                  const SizedBox(height: 4),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: Colors.grey.shade300),
                                    ),
                                    child: Text(
                                      (s['teks_jawaban_essay'] != null && s['teks_jawaban_essay'].toString().isNotEmpty)
                                          ? s['teks_jawaban_essay']
                                          : '(Siswa tidak menginput teks jawaban essay)',
                                      style: TextStyle(
                                        fontSize: 13,
                                        color: (s['teks_jawaban_essay'] != null && s['teks_jawaban_essay'].toString().isNotEmpty) ? Colors.black87 : Colors.grey,
                                        fontStyle: (s['teks_jawaban_essay'] != null && s['teks_jawaban_essay'].toString().isNotEmpty) ? FontStyle.normal : FontStyle.italic,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 12),

                                  // Input Skor Nilai
                                  Row(
                                    children: [
                                      const Text('Input Nilai Skor: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                      const SizedBox(width: 8),
                                      SizedBox(
                                        width: 80,
                                        height: 38,
                                        child: TextField(
                                          controller: controller,
                                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                          decoration: InputDecoration(
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                          ),
                                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Text('/ ${maxBobot.toStringAsFixed(0)} Poin', style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          } else {
                            // PG / True-False Question (Auto Scored)
                            final jawabanPg = s['jawaban_pg_dipilih'] ?? '(Tidak dijawab)';
                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade50,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text('Soal #${idx + 1} (${jns.toUpperCase()})', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.purple)),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: isBenar ? const Color(0xFFECFDF5) : Colors.red.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          isBenar ? 'BENAR (+${nilaiDiberikan.toStringAsFixed(0)})' : 'SALAH (0)',
                                          style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            color: isBenar ? const Color(0xFF047857) : Colors.red.shade800,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Text(pert, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.black87)),
                                  const SizedBox(height: 4),
                                  Text('Jawaban Siswa: $jawabanPg', style: TextStyle(fontSize: 11, color: Colors.grey.shade800, fontStyle: FontStyle.italic)),
                                ],
                              ),
                            );
                          }
                        },
                      ),
          ),
          const SizedBox(height: 12),

          ElevatedButton.icon(
            onPressed: _isSaving ? null : _submitScores,
            icon: _isSaving
                ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Icon(Icons.check_circle_rounded, size: 18),
            label: const Text('Simpan & Finalisasi Nilai Kuis'),
            style: ElevatedButton.styleFrom(
              minimumSize: const Size(double.infinity, 48),
              backgroundColor: const Color(0xFF047857),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }
}
