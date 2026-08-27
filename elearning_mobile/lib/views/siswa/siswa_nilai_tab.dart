import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/nilai_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaNilaiTab extends StatefulWidget {
  const SiswaNilaiTab({super.key});

  @override
  State<SiswaNilaiTab> createState() => _SiswaNilaiTabState();
}

class _SiswaNilaiTabState extends State<SiswaNilaiTab> {
  final TextEditingController _searchController = TextEditingController();
  String _filterStatus = 'semua'; // 'semua', 'tuntas', 'remedial'

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadNilai();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadNilai() async {
    try {
      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
      if (user != null) {
        debugPrint("[SiswaNilaiTab] Triggering fetchNilai for user_id: ${user.id}");
        await Provider.of<SiswaProvider>(context, listen: false).fetchNilai(user.id);
      } else {
        debugPrint("[SiswaNilaiTab] Warning: currentUser is null");
      }
    } catch (e, stack) {
      debugPrint("[SiswaNilaiTab] Error in _loadNilai: $e");
      debugPrint("Stacktrace: $stack");
    }
  }

  Color _getScoreColor(double score) {
    if (score >= 88) return const Color(0xFF10B981); // Emerald Green
    if (score >= 78) return const Color(0xFF2563EB); // Royal Blue
    if (score >= 68) return const Color(0xFFF59E0B); // Amber Yellow
    return const Color(0xFFEF4444); // Crimson Red
  }

  Color _getScoreBgColor(double score) {
    if (score >= 88) return const Color(0xFFECFDF5);
    if (score >= 78) return const Color(0xFFEFF6FF);
    if (score >= 68) return const Color(0xFFFFFBEB);
    return const Color(0xFFFEF2F2);
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final rawNilaiList = siswaProvider.nilaiList;

    // Filter Logic
    final query = _searchController.text.toLowerCase().trim();
    final filteredList = rawNilaiList.where((n) {
      final matchesSearch = n.namaMapel.toLowerCase().contains(query) ||
          (n.kodeMapel != null && n.kodeMapel!.toLowerCase().contains(query));

      if (!matchesSearch) return false;

      if (_filterStatus == 'tuntas') return n.isTuntas;
      if (_filterStatus == 'remedial') return !n.isTuntas;
      return true;
    }).toList();

    // Statistics Calculation
    double sumNilai = 0;
    int tuntasCount = 0;
    int remedialCount = 0;

    for (var n in rawNilaiList) {
      sumNilai += n.nilaiAkhir;
      if (n.isTuntas) {
        tuntasCount++;
      } else {
        remedialCount++;
      }
    }

    final avgScore = rawNilaiList.isNotEmpty ? (sumNilai / rawNilaiList.length) : 0.0;
    final overallPredikat = rawNilaiList.isNotEmpty
        ? (avgScore >= 88 ? 'A (Sangat Baik)' : (avgScore >= 78 ? 'B (Baik)' : (avgScore >= 68 ? 'C (Cukup)' : 'D (Kurang)')))
        : 'Belum Ada Nilai';

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Rekap Nilai & E-Rapor Digital',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Sinkronkan Nilai Real-time',
            onPressed: _loadNilai,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadNilai,
        color: AppTheme.secondaryColor,
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Glassmorphic Hero Banner Header
                    _buildHeroHeader(user?.fullName, avgScore, overallPredikat, rawNilaiList.length, tuntasCount, remedialCount),

                    const SizedBox(height: 20),

                    // Search & Filter Controls
                    _buildSearchAndFilters(),

                    const SizedBox(height: 16),

                    // Section Title
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          '📊 Daftar Evaluasi Mata Pelajaran',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                        ),
                        Text(
                          '${filteredList.length} Mapel',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // Content Area
                    if (siswaProvider.isLoading)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 40.0),
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              CircularProgressIndicator(),
                              SizedBox(height: 12),
                              Text(
                                'Memuat Rekap Nilai...',
                                style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                              ),
                            ],
                          ),
                        ),
                      )
                    else if (rawNilaiList.isEmpty)
                      _buildEmptyState()
                    else if (filteredList.isEmpty)
                      _buildNoSearchResultsState()
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: filteredList.length,
                        itemBuilder: (context, index) {
                          final item = filteredList[index];
                          return _buildSubjectGradeCard(item);
                        },
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroHeader(String? studentName, double avgScore, String overallPredikat, int totalMapel, int tuntasCount, int remedialCount) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1E3A8A), Color(0xFF2563EB)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A8A).withValues(alpha: 0.3),
            blurRadius: 18,
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
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    studentName ?? 'Siswa Active',
                    style: const TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(height: 2),
                  const Text(
                    'Transkrip E-Rapor Digital',
                    style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.workspace_premium_rounded, color: Colors.amberAccent, size: 28),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 16),

          Row(
            children: [
              // Main GPA Display
              Expanded(
                flex: 3,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Rata-Rata Nilai Akhir', style: TextStyle(color: Colors.white60, fontSize: 12)),
                    const SizedBox(height: 4),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.baseline,
                      textBaseline: TextBaseline.alphabetic,
                      children: [
                        Text(
                          avgScore.toStringAsFixed(1),
                          style: const TextStyle(color: Colors.white, fontSize: 34, fontWeight: FontWeight.w800),
                        ),
                        const SizedBox(width: 4),
                        const Text('/ 100', style: TextStyle(color: Colors.white60, fontSize: 14)),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.amber.withValues(alpha: 0.2),
                        border: Border.all(color: Colors.amber.withValues(alpha: 0.5)),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        'Predikat: $overallPredikat',
                        style: const TextStyle(color: Colors.amberAccent, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ),

              // KPI Stats Breakdown Chips
              Expanded(
                flex: 4,
                child: Column(
                  children: [
                    _buildKpiStatRow('Total Mapel', '$totalMapel Mapel', Icons.book_outlined, Colors.white70),
                    const SizedBox(height: 8),
                    _buildKpiStatRow('Status Tuntas', '$tuntasCount Mapel', Icons.check_circle_outline, const Color(0xFF6EE7B7)),
                    const SizedBox(height: 8),
                    _buildKpiStatRow('Perlu Remedial', '$remedialCount Mapel', Icons.warning_amber_rounded, Colors.orangeAccent),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKpiStatRow(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 8),
          Expanded(
            child: Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
          ),
          Text(value, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildSearchAndFilters() {
    return Column(
      children: [
        // Search TextField
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.02),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: TextField(
            controller: _searchController,
            onChanged: (val) => setState(() {}),
            decoration: InputDecoration(
              hintText: 'Cari mata pelajaran atau kode...',
              hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
              prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF64748B)),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 18, color: Color(0xFF94A3B8)),
                      onPressed: () {
                        _searchController.clear();
                        setState(() {});
                      },
                    )
                  : null,
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            ),
          ),
        ),

        const SizedBox(height: 10),

        // Filter Chips
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _buildFilterChip('Semua Mapel', 'semua', Icons.grid_view_rounded),
              const SizedBox(width: 8),
              _buildFilterChip('Tuntas TBM', 'tuntas', Icons.check_circle_rounded, activeColor: const Color(0xFF10B981)),
              const SizedBox(width: 8),
              _buildFilterChip('Perlu Remedial', 'remedial', Icons.warning_amber_rounded, activeColor: const Color(0xFFEF4444)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildFilterChip(String label, String value, IconData icon, {Color activeColor = const Color(0xFF2563EB)}) {
    final isSelected = _filterStatus == value;
    return ChoiceChip(
      showCheckmark: false,
      avatar: Icon(icon, size: 16, color: isSelected ? Colors.white : const Color(0xFF64748B)),
      label: Text(
        label,
        style: TextStyle(
          color: isSelected ? Colors.white : const Color(0xFF475569),
          fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
          fontSize: 12,
        ),
      ),
      selected: isSelected,
      selectedColor: activeColor,
      backgroundColor: Colors.white,
      side: BorderSide(color: isSelected ? activeColor : const Color(0xFFE2E8F0)),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      elevation: isSelected ? 2 : 0,
      onSelected: (bool selected) {
        setState(() {
          _filterStatus = value;
        });
      },
    );
  }

  Widget _buildSubjectGradeCard(NilaiModel n) {
    final scoreColor = _getScoreColor(n.nilaiAkhir);
    final scoreBgColor = _getScoreBgColor(n.nilaiAkhir);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(20),
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () => _showScoreDetailModal(n),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Mapel & Badges
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: scoreBgColor,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Icon(Icons.menu_book_rounded, color: scoreColor, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            n.namaMapel,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          const SizedBox(height: 2),
                          Row(
                            children: [
                              if (n.kodeMapel != null && n.kodeMapel!.isNotEmpty) ...[
                                Text(
                                  n.kodeMapel!,
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                                ),
                                const Text(' • ', style: TextStyle(color: Color(0xFFCBD5E1))),
                              ],
                              Text(
                                'KKM: ${n.kkm}',
                                style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    // Status Badge (TUNTAS / REMEDIAL)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: n.isTuntas ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: n.isTuntas ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            n.isTuntas ? Icons.check_circle_rounded : Icons.warning_amber_rounded,
                            size: 13,
                            color: n.isTuntas ? const Color(0xFF059669) : const Color(0xFFDC2626),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            n.statusKetuntasan,
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: n.isTuntas ? const Color(0xFF059669) : const Color(0xFFDC2626),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 14),

                // Linear Progress Bar for Nilai Akhir
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Nilai Akhir Rapor',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                        ),
                        Row(
                          children: [
                            Text(
                              n.nilaiAkhir.toStringAsFixed(1),
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: scoreColor),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: scoreColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                'Predikat ${n.predikat}',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: scoreColor),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(
                        value: (n.nilaiAkhir / 100).clamp(0.0, 1.0),
                        minHeight: 8,
                        backgroundColor: const Color(0xFFF1F5F9),
                        valueColor: AlwaysStoppedAnimation<Color>(scoreColor),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 14),
                const Divider(height: 1, color: Color(0xFFF1F5F9)),
                const SizedBox(height: 12),

                // Component Score Grid Breakdown
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildSubScorePill('📝 Tugas', n.nilaiTugas),
                    _buildSubScorePill('💡 Quiz', n.nilaiQuiz),
                    _buildSubScorePill('📑 UTS', n.nilaiUts),
                    _buildSubScorePill('🎓 UAS', n.nilaiUas),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSubScorePill(String label, double val) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: Column(
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500)),
          const SizedBox(height: 3),
          Text(
            val.toStringAsFixed(0),
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
          ),
        ],
      ),
    );
  }

  void _showScoreDetailModal(NilaiModel n) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(24),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: const Color(0xFFCBD5E1),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          n.namaMapel,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                        ),
                        const Text(
                          'Detail Rincian Komponen Evaluasi & Formula',
                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),

              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 12),

              // Final Score Card in Modal
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: _getScoreBgColor(n.nilaiAkhir),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: _getScoreColor(n.nilaiAkhir).withValues(alpha: 0.3)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Nilai Akhir Rapor', style: TextStyle(fontSize: 12, color: Color(0xFF475569))),
                        const SizedBox(height: 2),
                        Text(
                          n.nilaiAkhir.toStringAsFixed(1),
                          style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: _getScoreColor(n.nilaiAkhir)),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          'Predikat ${n.predikat}',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: _getScoreColor(n.nilaiAkhir)),
                        ),
                        Text(
                          n.predikatLabel,
                          style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 16),
              const Text('Rincian Bobot Komponen Penilaian:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
              const SizedBox(height: 10),

              _buildModalComponentRow('📝 Tugas Harian', n.nilaiTugas, '20%'),
              _buildModalComponentRow('💡 Kuis & Evaluation', n.nilaiQuiz, '20%'),
              _buildModalComponentRow('📑 Ujian Tengah Semester (UTS)', n.nilaiUts, '30%'),
              _buildModalComponentRow('🎓 Ujian Akhir Semester (UAS)', n.nilaiUas, '30%'),

              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline_rounded, size: 18, color: Color(0xFF475569)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        n.isTuntas
                            ? 'Pencapaian Anda telah memenuhi kriteria ketuntasan minimal (KKM ${n.kkm}). Pertahankan kinerja belajar Anda!'
                            : 'Nilai Anda masih berada di bawah batas KKM (${n.kkm}). Silakan hubungi Guru Pengampu untuk program remedial.',
                        style: const TextStyle(fontSize: 11, color: Color(0xFF475569)),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.secondaryColor,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Tutup Rincian', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildModalComponentRow(String title, double score, String weight) {
    final color = _getScoreColor(score);
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(fontSize: 13, color: Color(0xFF475569))),
          Row(
            children: [
              Text(
                score.toStringAsFixed(0),
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: color),
              ),
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  'Bobot $weight',
                  style: const TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          const Icon(Icons.inventory_2_outlined, size: 54, color: Color(0xFF94A3B8)),
          const SizedBox(height: 12),
          const Text(
            'Belum Ada Rekap Nilai',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
          ),
          const SizedBox(height: 6),
          const Text(
            'Data rekap nilai & rapor akan muncul secara otomatis setelah Guru menginput atau nilai kuis/tugas disinkronkan.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.secondaryColor,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: _loadNilai,
            icon: const Icon(Icons.refresh_rounded, size: 16, color: Colors.white),
            label: const Text('Refresh Data Nilai', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildNoSearchResultsState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Column(
        children: [
          Icon(Icons.search_off_rounded, size: 48, color: Color(0xFF94A3B8)),
          SizedBox(height: 8),
          Text(
            'Mata Pelajaran Tidak Ditemukan',
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
          ),
          SizedBox(height: 4),
          Text(
            'Coba gunakan kata kunci pencarian lain atau ganti filter status.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
        ],
      ),
    );
  }
}
