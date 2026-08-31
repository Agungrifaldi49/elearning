import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/quiz_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GuruBankSoalScreen extends StatefulWidget {
  final QuizModel? quiz;

  const GuruBankSoalScreen({super.key, this.quiz});

  @override
  State<GuruBankSoalScreen> createState() => _GuruBankSoalScreenState();
}

class _GuruBankSoalScreenState extends State<GuruBankSoalScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedJenis = 'Semua';
  int _selectedQuizId = 0; // 0 = Semua Quiz

  List<dynamic> _soalList = [];
  bool _isLoading = true;

  final List<String> _jenisList = ['Semua', 'PG', 'Essay', 'True/False'];

  @override
  void initState() {
    super.initState();
    if (widget.quiz != null) {
      _selectedQuizId = widget.quiz!.id;
    }
    _loadSoalData();
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

  Future<void> _loadSoalData() async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final params = <String, String>{};
    if (user != null) {
      params['user_id'] = '${user.id}';
    }
    if (_selectedQuizId > 0) {
      params['quiz_id'] = '$_selectedQuizId';
    }

    try {
      final res = await ApiService.get('guru/bank_soal', params: params);
      if (mounted) {
        if (res['success'] == true && res['data'] is List) {
          setState(() {
            _soalList = res['data'];
            _isLoading = false;
          });
        } else {
          setState(() {
            _soalList = [];
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _deleteSoal(int soalId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Soal Pertanyaan?'),
        content: const Text('Soal dan kunci pilihan jawaban terkait akan dihapus secara permanen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final res = await ApiService.post('guru/bank_soal', {
        'action': 'delete',
        'soal_id': soalId,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Soal berhasil dihapus!'),
            backgroundColor: res['success'] == true ? Colors.green : Colors.red,
          ),
        );
        _loadSoalData();
      }
    }
  }

  void _showAddSoalModal() {
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    final quizList = guruProvider.quizList;

    int currentQuizId = _selectedQuizId > 0
        ? _selectedQuizId
        : (quizList.isNotEmpty ? quizList.first.id : (widget.quiz?.id ?? 1));

    String jenisSoal = 'pg';
    final pertController = TextEditingController();
    final bobotController = TextEditingController(text: '10');

    final optAController = TextEditingController();
    final optBController = TextEditingController();
    final optCController = TextEditingController();
    final optDController = TextEditingController();

    int correctIndex = 0; // 0=A, 1=B, 2=C, 3=D

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
                        child: Icon(Icons.add_task_rounded, color: Colors.purple.shade800, size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Tambah Soal ke Bank Soal', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                            Text('Input pertanyaan, jenis soal, & opsi jawaban', style: TextStyle(fontSize: 11, color: Colors.grey)),
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

                  if (quizList.isNotEmpty) ...[
                    const Text('Pilih Ujian CBT / Quiz Target *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      initialValue: currentQuizId,
                      decoration: InputDecoration(
                        prefixIcon: const Icon(Icons.quiz_rounded, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: quizList.map((q) {
                        return DropdownMenuItem<int>(
                          value: q.id,
                          child: Text(
                            '${q.judul} (${q.namaMapel})',
                            style: const TextStyle(fontSize: 13),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setModalState(() {
                            currentQuizId = val;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 14),
                  ],

                  const Text('Jenis Soal *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Expanded(
                        child: ChoiceChip(
                          label: const Text('Pilihan Ganda'),
                          selected: jenisSoal == 'pg',
                          selectedColor: Colors.purple.shade800,
                          labelStyle: TextStyle(
                            color: jenisSoal == 'pg' ? Colors.white : Colors.black87,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                          onSelected: (val) {
                            if (val) setModalState(() => jenisSoal = 'pg');
                          },
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ChoiceChip(
                          label: const Text('Essay'),
                          selected: jenisSoal == 'essay',
                          selectedColor: Colors.purple.shade800,
                          labelStyle: TextStyle(
                            color: jenisSoal == 'essay' ? Colors.white : Colors.black87,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                          onSelected: (val) {
                            if (val) setModalState(() => jenisSoal = 'essay');
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  const Text('Teks Pertanyaan Soal *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: pertController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'Tuliskan teks instruksi atau pertanyaan soal...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 14),

                  const Text('Bobot Skor Nilai (Poin) *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: bobotController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      prefixIcon: const Icon(Icons.score_rounded, size: 20),
                      hintText: '10',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // OPTIONS FOR PG TYPE
                  if (jenisSoal == 'pg') ...[
                    const Text('Opsi Pilihan Jawaban (Tandai Kunci Jawaban Benar) *',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppTheme.primaryColor)),
                    const SizedBox(height: 8),

                    _buildOptionInputField(
                      label: 'Opsi A',
                      controller: optAController,
                      isSelected: correctIndex == 0,
                      onTapSelect: () => setModalState(() => correctIndex = 0),
                    ),
                    const SizedBox(height: 8),
                    _buildOptionInputField(
                      label: 'Opsi B',
                      controller: optBController,
                      isSelected: correctIndex == 1,
                      onTapSelect: () => setModalState(() => correctIndex = 1),
                    ),
                    const SizedBox(height: 8),
                    _buildOptionInputField(
                      label: 'Opsi C',
                      controller: optCController,
                      isSelected: correctIndex == 2,
                      onTapSelect: () => setModalState(() => correctIndex = 2),
                    ),
                    const SizedBox(height: 8),
                    _buildOptionInputField(
                      label: 'Opsi D',
                      controller: optDController,
                      isSelected: correctIndex == 3,
                      onTapSelect: () => setModalState(() => correctIndex = 3),
                    ),
                    const SizedBox(height: 16),
                  ],

                  ElevatedButton.icon(
                    onPressed: () async {
                      final pert = pertController.text.trim();
                      if (pert.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Teks pertanyaan wajib diisi!'), backgroundColor: Colors.red),
                        );
                        return;
                      }

                      final nav = Navigator.of(context);
                      final messenger = ScaffoldMessenger.of(context);
                      final bobot = int.tryParse(bobotController.text) ?? 10;

                      List<Map<String, dynamic>> pilihans = [];
                      if (jenisSoal == 'pg') {
                        final opts = [
                          optAController.text.trim(),
                          optBController.text.trim(),
                          optCController.text.trim(),
                          optDController.text.trim(),
                        ];
                        for (int i = 0; i < opts.length; i++) {
                          if (opts[i].isNotEmpty) {
                            pilihans.add({
                              'teks': opts[i],
                              'is_benar': i == correctIndex ? 1 : 0,
                            });
                          }
                        }
                      }

                      final res = await ApiService.post('guru/bank_soal', {
                        'quiz_id': currentQuizId,
                        'jenis_soal': jenisSoal,
                        'pertanyaan': pert,
                        'bobot': bobot,
                        'pilihan': pilihans,
                      });

                      nav.pop();
                      messenger.showSnackBar(
                        SnackBar(
                          content: Text(res['message'] ?? 'Soal berhasil disimpan!'),
                          backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                        ),
                      );
                      _loadSoalData();
                    },
                    icon: const Icon(Icons.save_rounded, size: 18),
                    label: const Text('Simpan ke Bank Soal'),
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

  Widget _buildOptionInputField({
    required String label,
    required TextEditingController controller,
    required bool isSelected,
    required VoidCallback onTapSelect,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: isSelected ? const Color(0xFFECFDF5) : Colors.grey.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isSelected ? const Color(0xFF10B981) : Colors.grey.shade300,
          width: isSelected ? 1.5 : 1.0,
        ),
      ),
      child: InkWell(
        onTap: onTapSelect,
        borderRadius: BorderRadius.circular(12),
        child: Row(
          children: [
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 6),
              width: 20,
              height: 20,
              decoration: BoxDecoration(
                color: isSelected ? const Color(0xFF10B981) : Colors.transparent,
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected ? const Color(0xFF10B981) : Colors.grey.shade400,
                  width: 1.5,
                ),
              ),
              child: isSelected
                  ? const Icon(Icons.check, size: 13, color: Colors.white)
                  : null,
            ),
            const SizedBox(width: 4),
          Text(
            '$label: ',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: isSelected ? const Color(0xFF065F46) : Colors.black87,
              fontSize: 12,
            ),
          ),
          Expanded(
            child: TextField(
              controller: controller,
              decoration: const InputDecoration(
                hintText: 'Teks opsi jawaban...',
                border: InputBorder.none,
                isDense: true,
              ),
              style: const TextStyle(fontSize: 12),
            ),
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

    // Filter soal list
    final filteredSoal = _soalList.where((s) {
      final jns = (s['jenis_soal'] ?? 'pg').toString().toLowerCase();
      final matchesJenis = _selectedJenis == 'Semua' ||
          (_selectedJenis == 'PG' && jns == 'pg') ||
          (_selectedJenis == 'Essay' && jns == 'essay') ||
          (_selectedJenis == 'True/False' && (jns == 'tf' || jns == 'true/false'));

      final qTitle = (s['judul_quiz'] ?? '').toString().toLowerCase();
      final pert = (s['pertanyaan'] ?? '').toString().toLowerCase();
      final matchesSearch = _searchQuery.isEmpty || qTitle.contains(_searchQuery) || pert.contains(_searchQuery);

      return matchesJenis && matchesSearch;
    }).toList();

    final totalPG = _soalList.where((e) => (e['jenis_soal'] ?? 'pg').toString().toLowerCase() == 'pg').length;
    final totalEssay = _soalList.length - totalPG;

    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      appBar: AppBar(
        title: const Text('Bank Soal CBT Guru', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddSoalModal,
        backgroundColor: Colors.purple.shade800,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_task_rounded),
        label: const Text('Tambah Soal', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: _loadSoalData,
        color: AppTheme.primaryColor,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 🚀 EXECUTIVE HERO BANNER CARD
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF6B21A8)],
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
                              Icon(Icons.inventory_2_rounded, size: 14, color: Colors.black87),
                              SizedBox(width: 4),
                              Text(
                                'Repository Bank Soal',
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
                            '${_soalList.length} Total Soal',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Kelola Bank Soal CBT',
                      style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Kelola pertanyaan, pilihan ganda, kunci jawaban, dan bobot skor secara presisi.',
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
                      title: 'Total Soal',
                      value: '${_soalList.length} Soal',
                      icon: Icons.assignment_rounded,
                      iconColor: Colors.purple.shade700,
                      bgColor: Colors.purple.shade50,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Soal PG',
                      value: '$totalPG Soal',
                      icon: Icons.fact_check_rounded,
                      iconColor: Colors.teal.shade700,
                      bgColor: Colors.teal.shade50,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Essay / TF',
                      value: '$totalEssay Soal',
                      icon: Icons.edit_note_rounded,
                      iconColor: Colors.blue.shade700,
                      bgColor: Colors.blue.shade50,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // 🎛️ QUIZ DROPDOWN FILTER & SEARCH BAR
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
                          _loadSoalData();
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
                  hintText: 'Cari pertanyaan soal atau judul kuis...',
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

              // 🏷️ TYPE FILTER CHIPS
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: _jenisList.map((jns) {
                    final isSel = _selectedJenis == jns;
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        selected: isSel,
                        label: Text(jns),
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
                            _selectedJenis = jns;
                          });
                        },
                      ),
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 16),

              // 📑 SOAL LIST CARDS
              if (_isLoading)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 40),
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (filteredSoal.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Column(
                      children: [
                        Icon(Icons.assignment_late_outlined, size: 54, color: Colors.grey.shade400),
                        const SizedBox(height: 12),
                        const Text(
                          'Belum Ada Soal di Bank Soal',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _searchQuery.isNotEmpty
                              ? 'Tidak ditemukan soal sesuai pencarian "$_searchQuery".'
                              : 'Klik "+ Tambah Soal" di bawah untuk mengisi bank soal.',
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
                  itemCount: filteredSoal.length,
                  itemBuilder: (context, index) {
                    final s = filteredSoal[index];
                    final soalId = int.parse(s['id'].toString());
                    final jenisSoal = (s['jenis_soal'] ?? 'pg').toString().toUpperCase();
                    final quizTitle = s['judul_quiz'] ?? 'Ujian CBT';
                    final mapelName = s['nama_mapel'] ?? '';
                    final bobot = s['bobot'] ?? 10;
                    final List<dynamic> pilihans = s['pilihan'] is List ? s['pilihan'] : [];

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
                            // Accent top bar
                            Container(
                              height: 4,
                              width: double.infinity,
                              color: jenisSoal == 'PG' ? Colors.purple.shade700 : Colors.teal,
                            ),

                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Header Badges Row
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: Colors.purple.shade50,
                                              borderRadius: BorderRadius.circular(20),
                                            ),
                                            child: Text(
                                              'Soal #${index + 1}',
                                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.purple.shade800),
                                            ),
                                          ),
                                          const SizedBox(width: 6),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: Colors.blue.shade50,
                                              borderRadius: BorderRadius.circular(20),
                                            ),
                                            child: Text(
                                              jenisSoal,
                                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                                            ),
                                          ),
                                        ],
                                      ),
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: Colors.amber.shade50,
                                              borderRadius: BorderRadius.circular(20),
                                              border: Border.all(color: Colors.amber.shade300),
                                            ),
                                            child: Text(
                                              'Bobot: $bobot Poin',
                                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                            ),
                                          ),
                                          const SizedBox(width: 4),
                                          IconButton(
                                            icon: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 20),
                                            onPressed: () => _deleteSoal(soalId),
                                            visualDensity: VisualDensity.compact,
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Quiz Title & Mapel Tag
                                  Text(
                                    "$quizTitle ${mapelName.isNotEmpty ? '• $mapelName' : ''}",
                                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                                  ),
                                  const SizedBox(height: 6),

                                  // Pertanyaan
                                  Text(
                                    s['pertanyaan'] ?? '',
                                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.black87, height: 1.4),
                                  ),
                                  const SizedBox(height: 12),

                                  // Options List for PG
                                  if (pilihans.isNotEmpty) ...[
                                    const Text('Pilihan Jawaban & Kunci:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey)),
                                    const SizedBox(height: 6),
                                    Column(
                                      children: pilihans.asMap().entries.map((entry) {
                                        final idx = entry.key;
                                        final opt = entry.value;
                                        final optLabel = String.fromCharCode(65 + idx); // A, B, C, D
                                        final isBenar = (opt['is_benar'] ?? 0) == 1 || (opt['is_benar'] ?? false) == true;
                                        final teks = opt['teks_pilihan'] ?? opt['teks'] ?? '';

                                        return Container(
                                          margin: const EdgeInsets.only(bottom: 6),
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: isBenar ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(
                                              color: isBenar ? const Color(0xFF10B981) : Colors.grey.shade200,
                                              width: isBenar ? 1.5 : 1.0,
                                            ),
                                          ),
                                          child: Row(
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(
                                                  color: isBenar ? const Color(0xFF10B981) : Colors.grey.shade300,
                                                  borderRadius: BorderRadius.circular(8),
                                                ),
                                                child: Text(
                                                  optLabel,
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 11,
                                                    color: isBenar ? Colors.white : Colors.black87,
                                                  ),
                                                ),
                                              ),
                                              const SizedBox(width: 8),
                                              Expanded(
                                                child: Text(
                                                  teks,
                                                  style: TextStyle(
                                                    fontSize: 12,
                                                    fontWeight: isBenar ? FontWeight.bold : FontWeight.normal,
                                                    color: isBenar ? const Color(0xFF064E3B) : Colors.black87,
                                                  ),
                                                ),
                                              ),
                                              if (isBenar)
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: const Color(0xFF10B981),
                                                    borderRadius: BorderRadius.circular(6),
                                                  ),
                                                  child: const Row(
                                                    mainAxisSize: MainAxisSize.min,
                                                    children: [
                                                      Icon(Icons.check, size: 12, color: Colors.white),
                                                      SizedBox(width: 2),
                                                      Text('KUNCI BENAR', style: TextStyle(fontSize: 9, color: Colors.white, fontWeight: FontWeight.bold)),
                                                    ],
                                                  ),
                                                ),
                                            ],
                                          ),
                                        );
                                      }).toList(),
                                    ),
                                  ],
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
