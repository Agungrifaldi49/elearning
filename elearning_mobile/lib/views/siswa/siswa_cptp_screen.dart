import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import 'gabung_kelas_screen.dart';

class SiswaCptpScreen extends StatefulWidget {
  const SiswaCptpScreen({super.key});

  @override
  State<SiswaCptpScreen> createState() => _SiswaCptpScreenState();
}

class _SiswaCptpScreenState extends State<SiswaCptpScreen> {
  bool _isLoading = true;
  String _errorMessage = '';
  int _selectedMapelId = 0; // 0 for all enrolled mapels
  final TextEditingController _searchController = TextEditingController();

  Map<String, dynamic> _kurikulum = {};
  Map<String, dynamic> _stats = {};
  List<dynamic> _enrolledMapels = [];
  List<dynamic> _mapelGroups = [];
  final Set<int> _expandedMapelIds = {};

  @override
  void initState() {
    super.initState();
    _fetchCptpData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  bool _isTrue(dynamic val) {
    if (val == null) return false;
    if (val is bool) return val;
    if (val is num) return val != 0;
    final str = val.toString().toLowerCase().trim();
    return str == 'true' || str == '1' || str == 'yes';
  }

  Future<void> _fetchCptpData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final params = <String, String>{
      'user_id': userId.toString(),
    };
    if (_selectedMapelId > 0) {
      params['mapel_id'] = _selectedMapelId.toString();
    }
    if (_searchController.text.trim().isNotEmpty) {
      params['q'] = _searchController.text.trim();
    }

    final res = await ApiService.get('siswa/cptp', params: params);

    if (!mounted) return;

    final bool isSuccess = _isTrue(res['success']) || _isTrue(res['status']);
    final dynamic rawData = res['data'];

    if (isSuccess && rawData is Map) {
      final dataMap = Map<String, dynamic>.from(rawData);
      final List groups = (dataMap['mapel_groups'] is List) ? dataMap['mapel_groups'] : [];
      final List enrolled = (dataMap['enrolled_mapels'] is List) ? dataMap['enrolled_mapels'] : [];

      _expandedMapelIds.clear();
      for (var g in groups) {
        final mId = int.tryParse((g['mapel_id'] ?? 0).toString()) ?? 0;
        if (mId > 0) {
          _expandedMapelIds.add(mId); // default expanded
        }
      }

      setState(() {
        _kurikulum = (dataMap['kurikulum'] is Map) ? Map<String, dynamic>.from(dataMap['kurikulum']) : {};
        _stats = (dataMap['stats'] is Map) ? Map<String, dynamic>.from(dataMap['stats']) : {};
        _enrolledMapels = enrolled;
        _mapelGroups = groups;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res['message']?.toString() ?? 'Gagal memuat data CP & TP.';
        _isLoading = false;
      });
    }
  }

  void _toggleExpand(int mapelId) {
    setState(() {
      if (_expandedMapelIds.contains(mapelId)) {
        _expandedMapelIds.remove(mapelId);
      } else {
        _expandedMapelIds.add(mapelId);
      }
    });
  }

  void _expandAll() {
    setState(() {
      for (var g in _mapelGroups) {
        final mId = int.tryParse((g['mapel_id'] ?? 0).toString()) ?? 0;
        if (mId > 0) _expandedMapelIds.add(mId);
      }
    });
  }

  void _collapseAll() {
    setState(() {
      _expandedMapelIds.clear();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          'Capaian & TP Mapel',
          style: GoogleFonts.outfit(
            fontWeight: FontWeight.bold,
            fontSize: 18,
            color: Colors.white,
          ),
        ),
        backgroundColor: isDark ? const Color(0xFF1E293B) : const Color(0xFF1E3A8A),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan Data',
            onPressed: _fetchCptpData,
          ),
          IconButton(
            icon: const Icon(Icons.group_add_rounded),
            tooltip: 'Gabung Kelas',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const GabungKelasScreen()),
              ).then((_) => _fetchCptpData());
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchCptpData,
        color: AppTheme.primaryColor,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage.isNotEmpty
                ? _buildErrorView(isDark)
                : _buildContentView(isDark),
      ),
    );
  }

  Widget _buildErrorView(bool isDark) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(24),
      children: [
        const SizedBox(height: 60),
        Center(
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.red.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.error_outline_rounded, color: Colors.red, size: 48),
              ),
              const SizedBox(height: 16),
              Text(
                'Terjadi Kendala',
                style: GoogleFonts.outfit(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _errorMessage,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  color: isDark ? Colors.white70 : Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: _fetchCptpData,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildContentView(bool isDark) {
    final namaKurikulum = _kurikulum['nama']?.toString() ?? 'Kurikulum Merdeka SMK';
    final kodeKurikulum = _kurikulum['kode']?.toString() ?? 'KMDK';
    final faseKurikulum = _kurikulum['fase']?.toString() ?? 'Fase E (Kelas X)';

    int dynamicTotalCp = 0;
    int dynamicTotalTp = 0;
    for (var g in _mapelGroups) {
      if (g is Map) {
        final cps = (g['cp_list'] is List)
            ? (g['cp_list'] as List)
            : ((g['cps'] is List) ? (g['cps'] as List) : []);
        dynamicTotalCp += cps.length;
        for (var c in cps) {
          if (c is Map) {
            final tps = (c['tp_list'] is List)
                ? (c['tp_list'] as List)
                : ((c['tps'] is List) ? (c['tps'] as List) : ((c['tp'] is List) ? (c['tp'] as List) : []));
            dynamicTotalTp += tps.length;
          }
        }
      }
    }

    final totalMapel = int.tryParse((_stats['total_mapel'] ?? 0).toString()) ?? _enrolledMapels.length;
    final totalCp = dynamicTotalCp > 0 ? dynamicTotalCp : (int.tryParse((_stats['total_cp'] ?? 0).toString()) ?? 0);
    final totalTp = dynamicTotalTp > 0 ? dynamicTotalTp : (int.tryParse((_stats['total_tp'] ?? 0).toString()) ?? 0);

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        // 1. Hero Banner Gradient Card
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1E3A8A), Color(0xFF2563EB), Color(0xFF38BDF8)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(18),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF2563EB).withValues(alpha: 0.25),
                blurRadius: 14,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Wrap(
                spacing: 8,
                runSpacing: 6,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '$namaKurikulum ($kodeKurikulum)',
                      style: const TextStyle(
                        color: Color(0xFF1E3A8A),
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      faseKurikulum,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.track_changes_rounded, color: Colors.white, size: 24),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Capaian & Tujuan Pembelajaran',
                          style: GoogleFonts.outfit(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        Text(
                          'Target KBM & Kriteria Ketuntasan (KKTP)',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.white.withValues(alpha: 0.9),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        const SizedBox(height: 14),

        // 2. Metric KPI Cards (2x2 Grid)
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'Mapel Terdaftar',
                value: '$totalMapel',
                sub: 'Sesuai Rombel',
                icon: Icons.menu_book_rounded,
                color: const Color(0xFF3B82F6),
                isDark: isDark,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildMetricCard(
                title: 'Capaian (CP)',
                value: '$totalCp',
                sub: 'Target Induk',
                icon: Icons.account_tree_rounded,
                color: const Color(0xFF06B6D4),
                isDark: isDark,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'Tujuan (TP)',
                value: '$totalTp',
                sub: 'Sasaran KBM',
                icon: Icons.gps_fixed_rounded,
                color: const Color(0xFF10B981),
                isDark: isDark,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildMetricCard(
                title: 'Standar KKTP',
                value: 'Min 75',
                sub: 'Ketuntasan',
                icon: Icons.military_tech_rounded,
                color: const Color(0xFFF59E0B),
                isDark: isDark,
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        // 3. Search & Filter Bar
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1E293B) : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.03),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Search Input Field
              TextField(
                controller: _searchController,
                style: TextStyle(
                  fontSize: 13,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
                decoration: InputDecoration(
                  hintText: 'Cari materi pokok, CP, atau kata kunci...',
                  hintStyle: TextStyle(
                    fontSize: 12,
                    color: isDark ? Colors.white38 : Colors.grey.shade400,
                  ),
                  prefixIcon: const Icon(Icons.search_rounded, size: 20),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear_rounded, size: 18),
                          onPressed: () {
                            _searchController.clear();
                            _fetchCptpData();
                          },
                        )
                      : null,
                  filled: true,
                  fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFF3B82F6), width: 1.5),
                  ),
                ),
                onSubmitted: (_) => _fetchCptpData(),
              ),

              const SizedBox(height: 12),

              // Enrolled Subject Filter Chips (Scrollable horizontal)
              if (_enrolledMapels.isNotEmpty) ...[
                Text(
                  'Filter Mapel Terdaftar:',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white70 : Colors.grey.shade600,
                  ),
                ),
                const SizedBox(height: 6),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      // "Semua Mapel" Chip
                      Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: ChoiceChip(
                          label: Text(
                            'Semua (${_enrolledMapels.length})',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: _selectedMapelId == 0
                                  ? Colors.white
                                  : (isDark ? Colors.white70 : const Color(0xFF334155)),
                            ),
                          ),
                          selected: _selectedMapelId == 0,
                          selectedColor: const Color(0xFF2563EB),
                          backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                          onSelected: (selected) {
                            if (selected) {
                              setState(() => _selectedMapelId = 0);
                              _fetchCptpData();
                            }
                          },
                        ),
                      ),
                      // Individual Mapel Chips
                      ..._enrolledMapels.map((em) {
                        final mId = int.tryParse((em['mapel_id'] ?? em['id'] ?? 0).toString()) ?? 0;
                        final mName = em['nama_mapel']?.toString() ?? 'Mapel';
                        final isSelected = _selectedMapelId == mId;

                        return Padding(
                          padding: const EdgeInsets.only(right: 6),
                          child: ChoiceChip(
                            label: Text(
                              mName,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: isSelected
                                    ? Colors.white
                                    : (isDark ? Colors.white70 : const Color(0xFF334155)),
                              ),
                            ),
                            selected: isSelected,
                            selectedColor: const Color(0xFF2563EB),
                            backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                            onSelected: (selected) {
                              setState(() => _selectedMapelId = selected ? mId : 0);
                              _fetchCptpData();
                            },
                          ),
                        );
                      }),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),

        const SizedBox(height: 12),

        // 4. Quick Expand / Collapse Row
        if (_mapelGroups.isNotEmpty)
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Daftar Rincian Target Pembelajaran:',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white70 : Colors.grey.shade700,
                ),
              ),
              Row(
                children: [
                  GestureDetector(
                    onTap: _expandAll,
                    child: Text(
                      'Buka Semua',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF2563EB),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text('•', style: TextStyle(color: Colors.grey.shade400, fontSize: 11)),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: _collapseAll,
                    child: Text(
                      'Tutup Semua',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: isDark ? Colors.white60 : Colors.grey.shade600,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),

        const SizedBox(height: 10),

        // 5. Main Subject Groups List
        if (_mapelGroups.isEmpty)
          _buildEmptyState(isDark)
        else
          ..._mapelGroups.map((group) => _buildMapelCard(group, isDark)),
      ],
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String value,
    required String sub,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white60 : Colors.grey.shade600,
                  ),
                ),
                Text(
                  value,
                  style: GoogleFonts.outfit(
                    fontSize: 17,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : const Color(0xFF0F172A),
                  ),
                ),
                Text(
                  sub,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 10,
                    color: color,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(28),
      margin: const EdgeInsets.only(top: 10),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        children: [
          Icon(Icons.search_off_rounded, size: 48, color: Colors.grey.shade400),
          const SizedBox(height: 12),
          Text(
            'Tidak Ada CP & TP yang Sesuai',
            style: GoogleFonts.outfit(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: isDark ? Colors.white : const Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            _enrolledMapels.isEmpty
                ? 'Anda belum terdaftar pada mata pelajaran manapun di rombel ini.'
                : 'Tidak ditemukan hasil untuk pencarian "${_searchController.text}".',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              color: isDark ? Colors.white60 : Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 16),
          if (_enrolledMapels.isEmpty)
            ElevatedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const GabungKelasScreen()),
                ).then((_) => _fetchCptpData());
              },
              icon: const Icon(Icons.group_add_rounded, size: 18),
              label: const Text('Gabung Kelas Sekarang'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            )
          else
            TextButton.icon(
              onPressed: () {
                _searchController.clear();
                setState(() => _selectedMapelId = 0);
                _fetchCptpData();
              },
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('Reset Pencarian'),
            ),
        ],
      ),
    );
  }

  Widget _buildMapelCard(Map<String, dynamic> group, bool isDark) {
    final mapelId = int.tryParse((group['mapel_id'] ?? 0).toString()) ?? 0;
    final namaMapel = group['nama_mapel']?.toString() ?? 'Mata Pelajaran';
    final kodeMapel = group['kode_mapel']?.toString() ?? '';
    final namaGuru = group['nama_guru']?.toString() ?? 'Guru Pengampu';

    final cpList = (group['cp_list'] is List)
        ? (group['cp_list'] as List)
        : ((group['cps'] is List) ? (group['cps'] as List) : ((group['cp'] is List) ? (group['cp'] as List) : []));

    int calculatedTp = 0;
    for (var c in cpList) {
      if (c is Map) {
        final tps = (c['tp_list'] is List)
            ? (c['tp_list'] as List)
            : ((c['tps'] is List) ? (c['tps'] as List) : ((c['tp'] is List) ? (c['tp'] as List) : []));
        calculatedTp += tps.length;
      }
    }
    final totalTp = calculatedTp > 0 ? calculatedTp : (int.tryParse((group['total_tp'] ?? 0).toString()) ?? 0);
    final totalCp = cpList.isNotEmpty ? cpList.length : (int.tryParse((group['total_cp'] ?? 0).toString()) ?? 0);
    final isExpanded = _expandedMapelIds.contains(mapelId);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Bar (Tappable to Expand/Collapse)
          InkWell(
            onTap: () => _toggleExpand(mapelId),
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFF2563EB).withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.book_rounded, color: Color(0xFF2563EB), size: 20),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                namaMapel,
                                style: GoogleFonts.outfit(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                                ),
                              ),
                            ),
                            if (kodeMapel.isNotEmpty) ...[
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  kodeMapel,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: isDark ? Colors.white70 : const Color(0xFF475569),
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Guru: $namaGuru',
                          style: TextStyle(
                            fontSize: 12,
                            color: isDark ? Colors.white60 : Colors.grey.shade600,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Wrap(
                          spacing: 6,
                          runSpacing: 4,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFF3B82F6).withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '$totalCp Capaian (CP)',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF2563EB),
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '$totalTp Tujuan (TP)',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF059669),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Icon(
                    isExpanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    color: isDark ? Colors.white60 : Colors.grey.shade500,
                    size: 24,
                  ),
                ],
              ),
            ),
          ),

          // Collapsible Body
          if (isExpanded)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Divider(height: 1),
                  const SizedBox(height: 12),
                  if (cpList.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Text(
                        'Guru pengampu sedang menyusun CP & TP untuk mata pelajaran ini.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark ? Colors.white60 : Colors.grey.shade600,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    )
                  else
                    ...cpList.map((cp) => _buildCpItem(cp, isDark)),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCpItem(dynamic cp, bool isDark) {
    if (cp is! Map) return const SizedBox.shrink();
    final cpMap = Map<String, dynamic>.from(cp);

    final kodeCp = (cpMap['kode_cp'] ?? cpMap['kode'] ?? 'CP').toString();
    final elemen = (cpMap['elemen'] ?? '').toString();
    final deskripsi = (cpMap['deskripsi'] ?? '').toString();
    final fase = (cpMap['fase'] ?? cpMap['nama_fase'] ?? '').toString();

    // Safely extract TP list across all possible response keys
    List tpList = [];
    if (cpMap['tp_list'] is List) {
      tpList = cpMap['tp_list'] as List;
    } else if (cpMap['tps'] is List) {
      tpList = cpMap['tps'] as List;
    } else if (cpMap['tp'] is List) {
      tpList = cpMap['tp'] as List;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // CP Header Box
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(13)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2563EB),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    kodeCp,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'monospace',
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (elemen.isNotEmpty)
                        Text(
                          'Elemen: $elemen',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                          ),
                        ),
                      Row(
                        children: [
                          Text(
                            '${tpList.length} Butir Tujuan Pembelajaran',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark ? Colors.white60 : Colors.grey.shade600,
                            ),
                          ),
                          if (fase.isNotEmpty) ...[
                            const SizedBox(width: 6),
                            Text('•', style: TextStyle(color: Colors.grey.shade400, fontSize: 10)),
                            const SizedBox(width: 6),
                            Text(
                              fase,
                              style: TextStyle(
                                fontSize: 10.5,
                                color: isDark ? Colors.white54 : Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // CP Description Text
          if (deskripsi.isNotEmpty)
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Rumusan Capaian Pembelajaran (CP):',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.3,
                      color: isDark ? Colors.white54 : Colors.grey.shade600,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    deskripsi,
                    style: TextStyle(
                      fontSize: 12,
                      height: 1.5,
                      color: isDark ? Colors.white70 : const Color(0xFF334155),
                    ),
                  ),
                ],
              ),
            ),

          // Child TP Items Section - ALWAYS VISIBLE WITH COUNTER
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Divider(height: 1),
                const SizedBox(height: 10),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Rincian Tujuan Pembelajaran (TP):',
                      style: GoogleFonts.outfit(
                        fontSize: 12.5,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : const Color(0xFF1E293B),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                      decoration: BoxDecoration(
                        color: tpList.isNotEmpty
                            ? const Color(0xFF10B981).withValues(alpha: 0.12)
                            : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        '${tpList.length} Butir TP',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: tpList.isNotEmpty
                              ? const Color(0xFF059669)
                              : (isDark ? Colors.white60 : Colors.grey.shade600),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                if (tpList.isNotEmpty)
                  ...tpList.map((tp) => _buildTpItem(tp, isDark))
                else
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                      ),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          Icons.info_outline_rounded,
                          size: 18,
                          color: isDark ? const Color(0xFF38BDF8) : const Color(0xFF2563EB),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Belum ada butir TP turunan untuk Capaian Pembelajaran ini. Bapak/Ibu guru sedang merumuskan rincian Tujuan Pembelajaran.',
                            style: TextStyle(
                              fontSize: 11.5,
                              height: 1.4,
                              color: isDark ? Colors.white60 : Colors.grey.shade600,
                              fontStyle: FontStyle.italic,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _cleanText(String text) {
    if (text.isEmpty) return text;
    return text
        .replaceAll('&#039;', "'")
        .replaceAll('&quot;', '"')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&nbsp;', ' ')
        .trim();
  }

  Widget _buildTpItem(dynamic tp, bool isDark) {
    if (tp is! Map) return const SizedBox.shrink();
    final tpMap = Map<String, dynamic>.from(tp);

    final rawKode = (tpMap['kode_tp'] ?? tpMap['kode'] ?? 'TP').toString();
    final kodeTp = _cleanText(rawKode.isNotEmpty ? rawKode : 'TP');

    final rawMateri = (tpMap['materi_pokok'] ?? tpMap['materi'] ?? '').toString();
    final materiPokok = _cleanText(rawMateri);

    final rawDeskripsi = (tpMap['deskripsi'] != null && tpMap['deskripsi'].toString().trim().isNotEmpty)
        ? tpMap['deskripsi'].toString()
        : (rawMateri.isNotEmpty ? rawMateri : (tpMap['nama_tp']?.toString() ?? 'Tujuan Pembelajaran'));
    final deskripsi = _cleanText(rawDeskripsi);

    final kktpMetode = (tpMap['kktp_metode'] ?? tpMap['metode'] ?? 'interval_nilai').toString();
    final kktpNilaiMin = double.tryParse((tpMap['kktp_nilai_min'] ?? tpMap['nilai_minimum'] ?? 75).toString()) ?? 75.0;
    final kktpTargetInd = int.tryParse((tpMap['kktp_target_ind'] ?? tpMap['target_indikator_count'] ?? 0).toString()) ?? 0;
    final kktpKriteria = _cleanText((tpMap['kktp_kriteria'] ?? tpMap['deskripsi_kriteria'] ?? '').toString());

    String kktpLabel;
    Color kktpColor;
    if (kktpMetode == 'checklist') {
      kktpLabel = 'Target: $kktpTargetInd Indikator';
      kktpColor = const Color(0xFFF59E0B);
    } else if (kktpMetode == 'rubrik') {
      kktpLabel = 'Rubrik Min: ${kktpNilaiMin.toInt()}';
      kktpColor = const Color(0xFF06B6D4);
    } else {
      kktpLabel = 'Batas KKTP: ${kktpNilaiMin.toInt()}';
      kktpColor = const Color(0xFF10B981);
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.15 : 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Left accent color strip (KKTP color based)
              Container(
                width: 4.5,
                color: kktpColor,
              ),
              // Main card content
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Top Row: Kode TP + Materi Pokok + KKTP Pill
                      Wrap(
                        spacing: 6,
                        runSpacing: 5,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981).withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              kodeTp,
                              style: const TextStyle(
                                color: Color(0xFF059669),
                                fontSize: 10.5,
                                fontWeight: FontWeight.bold,
                                fontFamily: 'monospace',
                              ),
                            ),
                          ),
                          if (materiPokok.isNotEmpty)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                materiPokok,
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w600,
                                  color: isDark ? Colors.white70 : const Color(0xFF334155),
                                ),
                              ),
                            ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                            decoration: BoxDecoration(
                              color: kktpColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              kktpLabel,
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.bold,
                                color: kktpColor,
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 8),

                      // TP Description
                      Text(
                        deskripsi,
                        style: TextStyle(
                          fontSize: 12,
                          height: 1.5,
                          color: isDark ? Colors.white : const Color(0xFF1E293B),
                        ),
                      ),

                      // KKTP Details Guide
                      if (kktpKriteria.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Icon(Icons.info_outline_rounded, size: 14, color: Color(0xFF06B6D4)),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  'Pedoman KKTP: $kktpKriteria',
                                  style: TextStyle(
                                    fontSize: 10,
                                    height: 1.4,
                                    color: isDark ? Colors.white60 : Colors.grey.shade600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
