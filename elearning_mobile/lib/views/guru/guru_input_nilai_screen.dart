import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class GuruInputNilaiScreen extends StatefulWidget {
  const GuruInputNilaiScreen({super.key});

  @override
  State<GuruInputNilaiScreen> createState() => _GuruInputNilaiScreenState();
}

class _GuruInputNilaiScreenState extends State<GuruInputNilaiScreen> {
  bool _isLoading = true;
  List<dynamic> _kelasList = [];
  List<dynamic> _mapelList = [];
  int? _selectedKelasId;
  int? _selectedMapelId;
  List<dynamic> _students = [];
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData({int? targetKelasId, int? targetMapelId}) async {
    setState(() => _isLoading = true);
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final userId = auth.currentUser?.id ?? 0;

    String url = 'guru/input_nilai?user_id=$userId';
    if (targetKelasId != null && targetKelasId > 0) {
      url += '&kelas_id=$targetKelasId';
    }
    if (targetMapelId != null && targetMapelId > 0) {
      url += '&mapel_id=$targetMapelId';
    }

    final res = await ApiService.get(url);

    if (mounted) {
      if (res['success'] == true && res['data'] != null) {
        final data = res['data'];
        setState(() {
          _kelasList = data['kelas_list'] ?? [];
          _mapelList = data['mapel_list'] ?? [];
          _selectedKelasId = data['selected_kelas_id'] ?? (_kelasList.isNotEmpty ? _kelasList[0]['id'] : null);
          _selectedMapelId = data['selected_mapel_id'] ?? (_mapelList.isNotEmpty ? _mapelList[0]['id'] : null);
          _students = data['students'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal memuat data kelas & siswa.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _onKelasChanged(int? newKelasId) {
    if (newKelasId != null && newKelasId != _selectedKelasId) {
      _fetchData(targetKelasId: newKelasId, targetMapelId: _selectedMapelId);
    }
  }

  void _onMapelChanged(int? newMapelId) {
    if (newMapelId != null && newMapelId != _selectedMapelId) {
      _fetchData(targetKelasId: _selectedKelasId, targetMapelId: newMapelId);
    }
  }

  void _showEditNilaiBottomSheet(Map<String, dynamic> student) {
    final siswaId = student['siswa_id'] ?? 0;
    final namaSiswa = student['nama_lengkap'] ?? 'Siswa';
    final nis = student['nis'] ?? '-';

    final tugasCtrl = TextEditingController(text: (student['nilai_tugas'] ?? 0).toString());
    final quizCtrl = TextEditingController(text: (student['nilai_quiz'] ?? 0).toString());
    final utsCtrl = TextEditingController(text: (student['nilai_uts'] ?? 0).toString());
    final uasCtrl = TextEditingController(text: (student['nilai_uas'] ?? 0).toString());

    double currentAkhir = double.tryParse((student['nilai_akhir'] ?? 0).toString()) ?? 0.0;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            void calculateLiveScore() {
              final t = double.tryParse(tugasCtrl.text.trim()) ?? 0.0;
              final q = double.tryParse(quizCtrl.text.trim()) ?? 0.0;
              final u1 = double.tryParse(utsCtrl.text.trim()) ?? 0.0;
              final u2 = double.tryParse(uasCtrl.text.trim()) ?? 0.0;

              final weights = <double>[];
              if (t > 0) weights.add(t * 0.20);
              if (q > 0) weights.add(q * 0.20);
              if (u1 > 0) weights.add(u1 * 0.30);
              if (u2 > 0) weights.add(u2 * 0.30);

              double totalWeight = 0;
              if (t > 0) totalWeight += 0.20;
              if (q > 0) totalWeight += 0.20;
              if (u1 > 0) totalWeight += 0.30;
              if (u2 > 0) totalWeight += 0.30;

              double calc = 0;
              if (totalWeight > 0) {
                calc = weights.fold(0.0, (sum, w) => sum + w) / totalWeight;
              } else {
                calc = (t * 0.2) + (q * 0.2) + (u1 * 0.3) + (u2 * 0.3);
              }

              setModalState(() {
                currentAkhir = double.parse(calc.toStringAsFixed(1));
              });
            }

            String getPredikatStr(double score) {
              if (score >= 88) return 'A (Sangat Baik)';
              if (score >= 78) return 'B (Baik)';
              if (score >= 68) return 'C (Cukup)';
              return 'D (Perlu Bimbingan)';
            }

            Color getPredikatColor(double score) {
              if (score >= 88) return const Color(0xFF10B981);
              if (score >= 78) return const Color(0xFF0284C7);
              if (score >= 68) return const Color(0xFFD97706);
              return const Color(0xFFEF4444);
            }

            final predColor = getPredikatColor(currentAkhir);

            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              padding: EdgeInsets.only(
                top: 20,
                left: 20,
                right: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Handle Bar
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Header Student Info
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 22,
                          backgroundColor: const Color(0xFF0D9488).withValues(alpha: 0.1),
                          child: const Icon(Icons.person_rounded, color: Color(0xFF0D9488), size: 24),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                namaSiswa,
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                'NIS: $nis',
                                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          onPressed: () => Navigator.pop(ctx),
                          icon: const Icon(Icons.close_rounded, color: Colors.grey),
                        ),
                      ],
                    ),
                    const Divider(height: 24),

                    // Estimasi Nilai Akhir Box
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      decoration: BoxDecoration(
                        color: predColor.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: predColor.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Nilai Akhir E-Rapor (Kalkulasi)',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                currentAkhir.toStringAsFixed(1),
                                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: predColor),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                            decoration: BoxDecoration(
                              color: predColor,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              getPredikatStr(currentAkhir),
                              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Inputs Row (Tugas & Quiz)
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: tugasCtrl,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            onChanged: (_) => calculateLiveScore(),
                            decoration: InputDecoration(
                              labelText: 'Tugas (20%)',
                              prefixIcon: const Icon(Icons.assignment_rounded, color: Colors.blue, size: 18),
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextFormField(
                            controller: quizCtrl,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            onChanged: (_) => calculateLiveScore(),
                            decoration: InputDecoration(
                              labelText: 'Quiz (20%)',
                              prefixIcon: const Icon(Icons.help_outline_rounded, color: Colors.amber, size: 18),
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    // Inputs Row (UTS & UAS)
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: utsCtrl,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            onChanged: (_) => calculateLiveScore(),
                            decoration: InputDecoration(
                              labelText: 'UTS (30%)',
                              prefixIcon: const Icon(Icons.description_rounded, color: Colors.indigo, size: 18),
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextFormField(
                            controller: uasCtrl,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            onChanged: (_) => calculateLiveScore(),
                            decoration: InputDecoration(
                              labelText: 'UAS (30%)',
                              prefixIcon: const Icon(Icons.grade_rounded, color: Colors.purple, size: 18),
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          final auth = Provider.of<AuthProvider>(context, listen: false);
                          final userId = auth.currentUser?.id ?? 0;

                          final payload = {
                            'siswa_id': siswaId,
                            'mapel_id': _selectedMapelId ?? 0,
                            'nilai_tugas': double.tryParse(tugasCtrl.text.trim()) ?? 0,
                            'nilai_quiz': double.tryParse(quizCtrl.text.trim()) ?? 0,
                            'nilai_uts': double.tryParse(utsCtrl.text.trim()) ?? 0,
                            'nilai_uas': double.tryParse(uasCtrl.text.trim()) ?? 0,
                          };

                          final messenger = ScaffoldMessenger.of(context);
                          final res = await ApiService.post('guru/input_nilai?user_id=$userId', payload);

                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                          }
                          if (mounted) {
                            if (res['success'] == true) {
                              messenger.showSnackBar(
                                SnackBar(
                                  content: Text(res['message'] ?? 'Nilai $namaSiswa berhasil disimpan!'),
                                  backgroundColor: const Color(0xFF0D9488),
                                  behavior: SnackBarBehavior.floating,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                              );
                              _fetchData(targetKelasId: _selectedKelasId, targetMapelId: _selectedMapelId);
                            } else {
                              messenger.showSnackBar(
                                SnackBar(
                                  content: Text(res['message'] ?? 'Gagal menyimpan nilai'),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
                          }
                        },
                        icon: const Icon(Icons.save_rounded, size: 20),
                        label: const Text('Simpan Nilai Siswa', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0D9488),
                          foregroundColor: Colors.white,
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final filteredStudents = _students.where((s) {
      final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
      final nis = (s['nis'] ?? '').toString().toLowerCase();
      final q = _searchQuery.toLowerCase().trim();
      return q.isEmpty || name.contains(q) || nis.contains(q);
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Input & Rekap Nilai E-Rapor',
              style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 17),
            ),
            Text(
              'Kelola Nilai Akademik Siswa Per-Rombel',
              style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
            ),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
      ),
      body: Column(
        children: [
          // Filter Bar Section
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.03),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    // Kelas Selector Dropdown
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: _selectedKelasId,
                            isExpanded: true,
                            hint: const Text('Pilih Kelas', style: TextStyle(fontSize: 12)),
                            icon: const Icon(Icons.arrow_drop_down_rounded, color: Color(0xFF0D9488)),
                            items: _kelasList.map<DropdownMenuItem<int>>((k) {
                              return DropdownMenuItem<int>(
                                value: k['id'],
                                child: Text(
                                  k['nama_kelas'] ?? 'Kelas',
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                            onChanged: _onKelasChanged,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Mapel Selector Dropdown
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: _selectedMapelId,
                            isExpanded: true,
                            hint: const Text('Pilih Mapel', style: TextStyle(fontSize: 12)),
                            icon: const Icon(Icons.arrow_drop_down_rounded, color: Color(0xFF0D9488)),
                            items: _mapelList.map<DropdownMenuItem<int>>((m) {
                              return DropdownMenuItem<int>(
                                value: m['id'],
                                child: Text(
                                  m['nama_mapel'] ?? 'Mapel',
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0D9488)),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                            onChanged: _onMapelChanged,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                // Search Bar Field
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val),
                  decoration: InputDecoration(
                    hintText: 'Cari nama siswa atau NIS...',
                    hintStyle: TextStyle(fontSize: 12, color: Colors.grey.shade400),
                    prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF0D9488)),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide(color: Colors.grey.shade200),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide(color: Colors.grey.shade200),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFF0D9488)),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Main Content List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF0D9488)))
                : filteredStudents.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.folder_off_rounded, size: 64, color: Colors.grey.shade300),
                            const SizedBox(height: 12),
                            const Text(
                              'Belum Ada Data Siswa pada Kelas Ini',
                              style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Silakan pilih rombel kelas & mapel ajar lain.',
                              style: TextStyle(fontSize: 12, color: Colors.grey),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(14),
                        itemCount: filteredStudents.length,
                        itemBuilder: (context, index) {
                          final s = filteredStudents[index];
                          final nama = s['nama_lengkap'] ?? 'Siswa';
                          final nis = s['nis'] ?? '-';
                          final nAkhir = double.tryParse((s['nilai_akhir'] ?? 0).toString()) ?? 0.0;
                          final predikat = s['predikat'] ?? '-';

                          Color getBadgeColor(double val) {
                            if (val >= 88) return const Color(0xFF10B981);
                            if (val >= 78) return const Color(0xFF0284C7);
                            if (val >= 68) return const Color(0xFFD97706);
                            return const Color(0xFFEF4444);
                          }

                          final badgeColor = getBadgeColor(nAkhir);

                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: Colors.grey.shade200),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.02),
                                  blurRadius: 8,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                            ),
                            child: InkWell(
                              onTap: () => _showEditNilaiBottomSheet(s),
                              borderRadius: BorderRadius.circular(16),
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Column(
                                  children: [
                                    // Row 1: Profile & Final Grade Badge
                                    Row(
                                      children: [
                                        CircleAvatar(
                                          radius: 20,
                                          backgroundColor: const Color(0xFF0D9488).withValues(alpha: 0.1),
                                          child: Text(
                                            nama.isNotEmpty ? nama[0].toUpperCase() : 'S',
                                            style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0D9488)),
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                nama,
                                                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                              Text(
                                                'NIS: $nis',
                                                style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 8),

                                        // Final Grade Badge
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                          decoration: BoxDecoration(
                                            color: badgeColor.withValues(alpha: 0.1),
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(color: badgeColor.withValues(alpha: 0.3)),
                                          ),
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.end,
                                            children: [
                                              Text(
                                                nAkhir > 0 ? nAkhir.toStringAsFixed(1) : '-',
                                                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: badgeColor),
                                              ),
                                              Text(
                                                predikat,
                                                style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: badgeColor),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    const Divider(height: 20),

                                    // Row 2: Components Pill Breakdown & Edit Button
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Wrap(
                                            spacing: 6,
                                            runSpacing: 6,
                                            children: [
                                              _buildComponentChip('Tugas', s['nilai_tugas'], Colors.blue),
                                              _buildComponentChip('Quiz', s['nilai_quiz'], Colors.amber.shade800),
                                              _buildComponentChip('UTS', s['nilai_uts'], Colors.indigo),
                                              _buildComponentChip('UAS', s['nilai_uas'], Colors.purple),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 6),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFF0D9488).withValues(alpha: 0.1),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: const Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Icon(Icons.edit_note_rounded, size: 16, color: Color(0xFF0D9488)),
                                              SizedBox(width: 4),
                                              Text(
                                                'Edit Nilai',
                                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF0D9488)),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
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

  Widget _buildComponentChip(String label, dynamic val, Color color) {
    final double numVal = double.tryParse((val ?? 0).toString()) ?? 0.0;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            '$label: ',
            style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: color),
          ),
          Text(
            numVal > 0 ? numVal.toStringAsFixed(0) : '-',
            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color),
          ),
        ],
      ),
    );
  }
}
