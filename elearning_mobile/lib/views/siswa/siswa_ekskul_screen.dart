import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class SiswaEkskulScreen extends StatefulWidget {
  const SiswaEkskulScreen({super.key});

  @override
  State<SiswaEkskulScreen> createState() => _SiswaEkskulScreenState();
}

class _SiswaEkskulScreenState extends State<SiswaEkskulScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  bool _isLoading = true;
  String _errorMessage = '';

  Map<String, dynamic> _activeTa = {};
  List<dynamic> _myEkskul = [];
  List<dynamic> _allEkskul = [];
  List<dynamic> _enrolledIds = [];

  bool _isActionInProgress = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _searchController.addListener(() {
      setState(() {});
    });
    _fetchEkskulData();
  }

  @override
  void dispose() {
    _tabController.dispose();
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

  Future<void> _fetchEkskulData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final params = <String, String>{
      'user_id': userId.toString(),
    };

    final res = await ApiService.get('siswa/ekstrakurikuler', params: params);

    if (!mounted) return;

    final bool isSuccess = _isTrue(res['success']) || _isTrue(res['status']);
    final dynamic rawData = res['data'];

    if (isSuccess && rawData is Map) {
      final dataMap = Map<String, dynamic>.from(rawData);
      final List myEks = (dataMap['my_ekskul'] is List) ? dataMap['my_ekskul'] : [];
      final List allEks = (dataMap['all_ekskul'] is List) ? dataMap['all_ekskul'] : [];
      final List enrIds = (dataMap['enrolled_ids'] is List) ? dataMap['enrolled_ids'] : [];

      setState(() {
        _activeTa = (dataMap['active_ta'] is Map) ? Map<String, dynamic>.from(dataMap['active_ta']) : {};
        _myEkskul = myEks;
        _allEkskul = allEks;
        _enrolledIds = enrIds;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res['message']?.toString() ?? 'Gagal memuat data ekstrakurikuler.';
        _isLoading = false;
      });
    }
  }

  Future<void> _joinEkskul(int ekskulId, String namaEkskul) async {
    final bool? confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF2563EB).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.add_task_rounded, color: Color(0xFF2563EB), size: 24),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'Ikuti Ekstrakurikuler',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Text(
          'Apakah Anda yakin ingin bergabung dan mengikuti kegiatan "$namaEkskul"?\n\n'
          'Keikutsertaan Anda akan tercatat resmi pada semester ini dan dinilai untuk E-Rapor Digital.',
          style: const TextStyle(fontSize: 14, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF2563EB),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              elevation: 0,
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Ikuti Sekarang', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (!mounted) return;
    if (confirm != true) return;

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    setState(() => _isActionInProgress = true);

    final res = await ApiService.post('siswa/ekstrakurikuler', {
      'action': 'join_ekskul',
      'sub_action': 'join_ekskul',
      'ekskul_id': ekskulId,
      'user_id': userId,
    });

    setState(() => _isActionInProgress = false);

    final bool isSuccess = _isTrue(res['success']) || _isTrue(res['status']);
    final message = res['message']?.toString() ?? (isSuccess ? 'Berhasil bergabung!' : 'Gagal bergabung.');

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(isSuccess ? Icons.check_circle_rounded : Icons.error_outline_rounded, color: Colors.white),
            const SizedBox(width: 10),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: isSuccess ? const Color(0xFF10B981) : Colors.redAccent,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );

    if (isSuccess) {
      await _fetchEkskulData();
    }
  }

  Future<void> _leaveEkskul(int ekskulId, String namaEkskul) async {
    final bool? confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.warning_amber_rounded, color: Colors.red, size: 24),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'Batalkan Keikutsertaan',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Text(
          'Apakah Anda yakin ingin membatalkan keikutsertaan pada "$namaEkskul"?\n\n'
          'Data keikutsertaan Anda pada semester ini akan dihapus dari daftar anggota.',
          style: const TextStyle(fontSize: 14, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Kembali', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              elevation: 0,
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Batalkan', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (!mounted) return;
    if (confirm != true) return;

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    setState(() => _isActionInProgress = true);

    final res = await ApiService.post('siswa/ekstrakurikuler', {
      'action': 'leave_ekskul',
      'sub_action': 'leave_ekskul',
      'ekskul_id': ekskulId,
      'user_id': userId,
    });

    setState(() => _isActionInProgress = false);

    final bool isSuccess = _isTrue(res['success']) || _isTrue(res['status']);
    final message = res['message']?.toString() ?? (isSuccess ? 'Keikutsertaan dibatalkan.' : 'Gagal membatalkan.');

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(isSuccess ? Icons.check_circle_rounded : Icons.error_outline_rounded, color: Colors.white),
            const SizedBox(width: 10),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: isSuccess ? const Color(0xFF10B981) : Colors.redAccent,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );

    if (isSuccess) {
      await _fetchEkskulData();
    }
  }

  void _openWhatsApp(String phone) async {
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (cleanPhone.isEmpty) return;

    final url = 'https://wa.me/$cleanPhone';
    final uri = Uri.parse(url);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tidak dapat membuka tautan WhatsApp.')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  Map<String, dynamic> _getEkskulTheme(String nama) {
    final low = nama.toLowerCase().trim();
    if (low.contains('pramuka')) {
      return {
        'icon': Icons.explore_rounded,
        'gradient': const [Color(0xFF065F46), Color(0xFF047857)],
        'color': const Color(0xFF059669),
        'bg_soft': const Color(0xFFECFDF5),
      };
    } else if (low.contains('paskibra')) {
      return {
        'icon': Icons.flag_rounded,
        'gradient': const [Color(0xFF991B1B), Color(0xFFDC2626)],
        'color': const Color(0xFFDC2626),
        'bg_soft': const Color(0xFFFEF2F2),
      };
    } else if (low.contains('futsal') || low.contains('bola')) {
      return {
        'icon': Icons.sports_soccer_rounded,
        'gradient': const [Color(0xFF0284C7), Color(0xFF0369A1)],
        'color': const Color(0xFF0284C7),
        'bg_soft': const Color(0xFFF0F9FF),
      };
    } else if (low.contains('basket') || low.contains('voli')) {
      return {
        'icon': Icons.emoji_events_rounded,
        'gradient': const [Color(0xFFD97706), Color(0xFFB45309)],
        'color': const Color(0xFFD97706),
        'bg_soft': const Color(0xFFFFFBEB),
      };
    } else if (low.contains('pmr') || low.contains('uks')) {
      return {
        'icon': Icons.favorite_rounded,
        'gradient': const [Color(0xFFE11D48), Color(0xFFBE123C)],
        'color': const Color(0xFFE11D48),
        'bg_soft': const Color(0xFFFFF1F2),
      };
    } else if (low.contains('rohis') || low.contains('islam')) {
      return {
        'icon': Icons.nightlight_round,
        'gradient': const [Color(0xFF0F766E), Color(0xFF115E59)],
        'color': const Color(0xFF0F766E),
        'bg_soft': const Color(0xFFF0FDFA),
      };
    } else if (low.contains('it') || low.contains('coding') || low.contains('komputer') || low.contains('robot')) {
      return {
        'icon': Icons.computer_rounded,
        'gradient': const [Color(0xFF4338CA), Color(0xFF3730A3)],
        'color': const Color(0xFF4338CA),
        'bg_soft': const Color(0xFFEEF2FF),
      };
    } else if (low.contains('musik') || low.contains('band') || low.contains('seni')) {
      return {
        'icon': Icons.music_note_rounded,
        'gradient': const [Color(0xFF7C3AED), Color(0xFF6D28D9)],
        'color': const Color(0xFF7C3AED),
        'bg_soft': const Color(0xFFF5F3FF),
      };
    } else if (low.contains('english') || low.contains('bahasa')) {
      return {
        'icon': Icons.translate_rounded,
        'gradient': const [Color(0xFF0891B2), Color(0xFF0E7490)],
        'color': const Color(0xFF0891B2),
        'bg_soft': const Color(0xFFECFEFF),
      };
    }
    return {
      'icon': Icons.military_tech_rounded,
      'gradient': const [Color(0xFF1E3A8A), Color(0xFF2563EB)],
      'color': const Color(0xFF2563EB),
      'bg_soft': const Color(0xFFEFF6FF),
    };
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1E3A8A),
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        title: Text(
          'Portal Ekstrakurikuler',
          style: GoogleFonts.outfit(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan Data',
            onPressed: _fetchEkskulData,
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Container(
            color: const Color(0xFF1E3A8A),
            child: TabBar(
              controller: _tabController,
              indicatorColor: Colors.amber,
              indicatorWeight: 3.5,
              labelColor: Colors.white,
              unselectedLabelColor: Colors.white70,
              labelStyle: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13.5),
              tabs: [
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.bookmark_added_rounded, size: 18),
                      const SizedBox(width: 6),
                      Text('Ekskul Saya (${_myEkskul.length})'),
                    ],
                  ),
                ),
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.grid_view_rounded, size: 18),
                      const SizedBox(width: 6),
                      Text('Pilihan Ekskul (${_allEkskul.length})'),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      body: Stack(
        children: [
          _isLoading
              ? const Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CircularProgressIndicator(),
                      SizedBox(height: 16),
                      Text('Memuat portal ekstrakurikuler...'),
                    ],
                  ),
                )
              : _errorMessage.isNotEmpty
                  ? _buildErrorState(isDark)
                  : RefreshIndicator(
                      onRefresh: _fetchEkskulData,
                      child: Column(
                        children: [
                          _buildHeroBanner(isDark),
                          _buildSearchField(isDark),
                          Expanded(
                            child: TabBarView(
                              controller: _tabController,
                              children: [
                                _buildMyEkskulTab(isDark),
                                _buildAllEkskulTab(isDark),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
          if (_isActionInProgress)
            Container(
              color: Colors.black45,
              child: const Center(
                child: Card(
                  elevation: 8,
                  child: Padding(
                    padding: EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircularProgressIndicator(),
                        SizedBox(height: 16),
                        Text('Memproses data...', style: TextStyle(fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildHeroBanner(bool isDark) {
    final ta = _activeTa['tahun_ajaran']?.toString() ?? '2026/2027';
    final semester = _activeTa['semester']?.toString() ?? 'Ganjil';
    final totalJoined = _myEkskul.length;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            const Color(0xFF1E3A8A),
            const Color(0xFF2563EB).withValues(alpha: 0.95),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.white24),
            ),
            child: const Icon(Icons.stars_rounded, color: Colors.amber, size: 26),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white30),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.calendar_today_rounded, size: 10, color: Colors.amber),
                          const SizedBox(width: 4),
                          Text(
                            'T.A $ta • $semester',
                            style: GoogleFonts.outfit(
                              fontSize: 10.5,
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withValues(alpha: 0.25),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: const Color(0xFF10B981).withValues(alpha: 0.5)),
                      ),
                      child: Text(
                        '$totalJoined Diikuti',
                        style: GoogleFonts.outfit(
                          fontSize: 10.5,
                          color: const Color(0xFFA7F3D0),
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  'Salurkan bakat, kepemimpinan & raih capaian E-Rapor Digital',
                  style: GoogleFonts.outfit(
                    fontSize: 11,
                    color: Colors.white.withValues(alpha: 0.85),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchField(bool isDark) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      child: TextField(
        controller: _searchController,
        style: GoogleFonts.outfit(fontSize: 13.5),
        decoration: InputDecoration(
          isDense: true,
          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          hintText: 'Cari ekstrakurikuler, pembimbing, atau hari...',
          hintStyle: TextStyle(
            color: isDark ? Colors.white38 : Colors.grey.shade500,
            fontSize: 13,
          ),
          prefixIcon: const Icon(Icons.search_rounded, size: 20),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 18),
                  onPressed: () => _searchController.clear(),
                )
              : null,
          filled: true,
          fillColor: isDark ? const Color(0xFF1E293B) : Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: Color(0xFF2563EB), width: 1.5),
          ),
        ),
      ),
    );
  }

  Widget _buildMyEkskulTab(bool isDark) {
    final query = _searchController.text.toLowerCase().trim();
    final filtered = _myEkskul.where((e) {
      if (query.isEmpty) return true;
      final name = (e['nama_ekskul'] ?? '').toString().toLowerCase();
      final coach = (e['pembimbing_nama'] ?? e['nama_guru_pembimbing'] ?? e['nama_pembimbing_luar'] ?? '').toString().toLowerCase();
      final hari = (e['hari'] ?? '').toString().toLowerCase();
      return name.contains(query) || coach.contains(query) || hari.contains(query);
    }).toList();

    if (filtered.isEmpty) {
      return Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: const Color(0xFF2563EB).withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.explore_off_rounded, size: 54, color: Color(0xFF2563EB)),
              ),
              const SizedBox(height: 16),
              Text(
                _myEkskul.isEmpty
                    ? 'Anda Belum Mengikuti Ekstrakurikuler'
                    : 'Tidak Ditemukan',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _myEkskul.isEmpty
                    ? 'Pilihlah salah satu atau lebih ekstrakurikuler yang sesuai dengan minat dan bakat Anda pada tab "Pilihan Ekskul".'
                    : 'Tidak ada ekstrakurikuler yang cocok dengan kata kunci "$query".',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: isDark ? Colors.white60 : Colors.grey.shade600,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 18),
              if (_myEkskul.isEmpty)
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                    elevation: 0,
                  ),
                  icon: const Icon(Icons.arrow_forward_rounded, size: 18),
                  label: const Text('Pilih Ekstrakurikuler', style: TextStyle(fontWeight: FontWeight.bold)),
                  onPressed: () {
                    _tabController.animateTo(1);
                  },
                ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: filtered.length,
      itemBuilder: (ctx, idx) {
        final item = filtered[idx];
        return _buildMyEkskulCard(item, isDark);
      },
    );
  }

  Widget _buildMyEkskulCard(dynamic item, bool isDark) {
    final nama = _cleanText((item['nama_ekskul'] ?? 'Ekstrakurikuler').toString());
    final ekskulId = int.tryParse((item['ekskul_id'] ?? item['id'] ?? 0).toString()) ?? 0;
    final theme = _getEkskulTheme(nama);

    final isLuar = item['tipe_pembimbing'] == 'luar';
    final coachName = _cleanText((item['pembimbing_nama'] ?? (isLuar ? item['nama_pembimbing_luar'] : item['nama_guru_pembimbing']) ?? 'Pembimbing').toString());
    final kontak = (item['kontak_pembimbing'] ?? '').toString().trim();

    final hari = _cleanText((item['hari'] ?? '-').toString());
    final jam = _cleanText((item['jam'] ?? '-').toString());
    final tempat = _cleanText((item['tempat'] ?? '-').toString());

    final predikat = _cleanText((item['predikat'] ?? 'Sangat Baik').toString());
    final nilaiDeskripsi = _cleanText((item['nilai_deskripsi'] ?? '').toString());

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
            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Accent Gradient Bar
            Container(
              height: 5,
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: theme['gradient'] as List<Color>,
                ),
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header Row: Icon, Nama Ekskul, Status Badge, Leave Button
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(colors: theme['gradient'] as List<Color>),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: (theme['color'] as Color).withValues(alpha: 0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Icon(theme['icon'] as IconData, color: Colors.white, size: 22),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              nama,
                              style: GoogleFonts.outfit(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : const Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 3),
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF10B981).withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: const Color(0xFF10B981).withValues(alpha: 0.3)),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.check_circle_rounded, size: 10, color: Color(0xFF10B981)),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Anggota Aktif',
                                        style: GoogleFonts.outfit(
                                          fontSize: 10.5,
                                          fontWeight: FontWeight.bold,
                                          color: const Color(0xFF10B981),
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
                      // Tombol Batal
                      InkWell(
                        onTap: () => _leaveEkskul(ekskulId, nama),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.red.withValues(alpha: 0.4)),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.close_rounded, size: 13, color: Colors.red),
                              SizedBox(width: 2),
                              Text(
                                'Batal',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: Colors.red,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Coach Info Box
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF0F172A).withValues(alpha: 0.6) : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          isLuar ? Icons.person_pin_circle_rounded : Icons.person_rounded,
                          size: 16,
                          color: const Color(0xFF2563EB),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Text(
                                    isLuar ? 'PEMBIMBING LUAR' : 'GURU PEMBIMBING',
                                    style: GoogleFonts.outfit(
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.w700,
                                      color: isDark ? Colors.white54 : Colors.grey.shade600,
                                      letterSpacing: 0.3,
                                    ),
                                  ),
                                ],
                              ),
                              Text(
                                coachName,
                                style: GoogleFonts.outfit(
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w600,
                                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        if (kontak.isNotEmpty)
                          IconButton(
                            icon: const Icon(Icons.chat_bubble_rounded, size: 18, color: Color(0xFF10B981)),
                            tooltip: 'Chat WhatsApp: $kontak',
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            onPressed: () => _openWhatsApp(kontak),
                          ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 10),

                  // Jadwal & Lokasi 2 Kolom Mini
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0F172A).withValues(alpha: 0.4) : const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.calendar_month_rounded, size: 15, color: Color(0xFFD97706)),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Jadwal',
                                      style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600),
                                    ),
                                    Text(
                                      hari.isNotEmpty && hari != '-' ? '$hari ($jam)' : '-',
                                      style: GoogleFonts.outfit(fontSize: 11.5, fontWeight: FontWeight.bold),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0F172A).withValues(alpha: 0.4) : const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.location_on_rounded, size: 15, color: Color(0xFFDC2626)),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Lokasi',
                                      style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600),
                                    ),
                                    Text(
                                      tempat.isNotEmpty ? tempat : 'Area Sekolah',
                                      style: GoogleFonts.outfit(fontSize: 11.5, fontWeight: FontWeight.bold),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Official E-Rapor Digital Score Box
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: isDark
                            ? [const Color(0xFF0F172A), const Color(0xFF1E293B)]
                            : [const Color(0xFFF8FAFC), const Color(0xFFEFF6FF)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: const Color(0xFF2563EB).withValues(alpha: 0.25),
                        width: 1.2,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.description_rounded, size: 14, color: Color(0xFF2563EB)),
                                const SizedBox(width: 5),
                                Text(
                                  'Penilaian E-Rapor Digital',
                                  style: GoogleFonts.outfit(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFF2563EB),
                                  ),
                                ),
                              ],
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFF2563EB),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                'Predikat: $predikat',
                                style: GoogleFonts.outfit(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF1E293B) : Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Text(
                            nilaiDeskripsi.isNotEmpty
                                ? nilaiDeskripsi
                                : 'Deskripsi capaian nilai sedang dalam proses evaluasi oleh Pembimbing Ekstrakurikuler.',
                            style: GoogleFonts.outfit(
                              fontSize: 11.5,
                              fontStyle: nilaiDeskripsi.isEmpty ? FontStyle.italic : FontStyle.normal,
                              color: nilaiDeskripsi.isEmpty
                                  ? (isDark ? Colors.white38 : Colors.grey.shade500)
                                  : (isDark ? Colors.white70 : const Color(0xFF334155)),
                              height: 1.35,
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
      ),
    );
  }

  Widget _buildAllEkskulTab(bool isDark) {
    final query = _searchController.text.toLowerCase().trim();
    final filtered = _allEkskul.where((e) {
      if (query.isEmpty) return true;
      final name = (e['nama_ekskul'] ?? '').toString().toLowerCase();
      final coach = (e['pembimbing_nama'] ?? e['nama_guru'] ?? e['nama_pembimbing_luar'] ?? '').toString().toLowerCase();
      final hari = (e['hari'] ?? '').toString().toLowerCase();
      return name.contains(query) || coach.contains(query) || hari.contains(query);
    }).toList();

    if (filtered.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.search_off_rounded, size: 54, color: Colors.grey),
              const SizedBox(height: 16),
              Text(
                'Tidak Ditemukan',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Tidak ada ekstrakurikuler yang sesuai dengan kata pencarian "$query".',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: isDark ? Colors.white60 : Colors.grey.shade600),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: filtered.length,
      itemBuilder: (ctx, idx) {
        final item = filtered[idx];
        return _buildAllEkskulCard(item, isDark);
      },
    );
  }

  Widget _buildAllEkskulCard(dynamic item, bool isDark) {
    final nama = _cleanText((item['nama_ekskul'] ?? 'Ekstrakurikuler').toString());
    final ekskulId = int.tryParse((item['id'] ?? 0).toString()) ?? 0;
    final theme = _getEkskulTheme(nama);

    final bool isJoined = _isTrue(item['is_joined']) || _enrolledIds.contains(ekskulId);
    final isLuar = item['tipe_pembimbing'] == 'luar';
    final coachName = _cleanText((item['pembimbing_nama'] ?? (isLuar ? item['nama_pembimbing_luar'] : item['nama_guru']) ?? 'Pembimbing').toString());
    final deskripsi = _cleanText((item['deskripsi'] ?? 'Kegiatan pembinaan minat, bakat, ketrampilan serta pembentukan karakter positif siswa.').toString());

    final hari = _cleanText((item['hari'] ?? '-').toString());
    final jam = _cleanText((item['jam'] ?? '-').toString());
    final tempat = _cleanText((item['tempat'] ?? '-').toString());
    final totalAnggota = int.tryParse((item['total_anggota'] ?? 0).toString()) ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isJoined
              ? const Color(0xFF10B981).withValues(alpha: 0.4)
              : (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          width: isJoined ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Accent Gradient Bar
            Container(
              height: 5,
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: theme['gradient'] as List<Color>,
                ),
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header Row: Icon, Nama, Badge Pembimbing
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(colors: theme['gradient'] as List<Color>),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: (theme['color'] as Color).withValues(alpha: 0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Icon(theme['icon'] as IconData, color: Colors.white, size: 22),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              nama,
                              style: GoogleFonts.outfit(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : const Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 3),
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: (isLuar ? Colors.amber : Colors.blue).withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(
                                      color: (isLuar ? Colors.amber : Colors.blue).withValues(alpha: 0.4),
                                    ),
                                  ),
                                  child: Text(
                                    isLuar ? 'Pembimbing Luar' : 'Guru Internal',
                                    style: GoogleFonts.outfit(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                      color: isLuar ? const Color(0xFFB45309) : const Color(0xFF1D4ED8),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.grey.withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    '$totalAnggota Anggota',
                                    style: GoogleFonts.outfit(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w600,
                                      color: isDark ? Colors.white60 : Colors.grey.shade700,
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

                  const SizedBox(height: 10),

                  // Deskripsi
                  Text(
                    deskripsi,
                    style: GoogleFonts.outfit(
                      fontSize: 12,
                      color: isDark ? Colors.white70 : Colors.grey.shade700,
                      height: 1.35,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),

                  const SizedBox(height: 10),

                  // Mini Details (Pembimbing, Jadwal, Lokasi)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF0F172A).withValues(alpha: 0.5) : const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                      ),
                    ),
                    child: Column(
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.person_outline_rounded, size: 14, color: Color(0xFF2563EB)),
                            const SizedBox(width: 6),
                            Text(
                              'Pembimbing:',
                              style: TextStyle(fontSize: 11, color: isDark ? Colors.white54 : Colors.grey.shade600),
                            ),
                            const Spacer(),
                            Flexible(
                              child: Text(
                                coachName,
                                style: GoogleFonts.outfit(fontSize: 11.5, fontWeight: FontWeight.bold),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.access_time_rounded, size: 14, color: Color(0xFFD97706)),
                            const SizedBox(width: 6),
                            Text(
                              'Jadwal:',
                              style: TextStyle(fontSize: 11, color: isDark ? Colors.white54 : Colors.grey.shade600),
                            ),
                            const Spacer(),
                            Text(
                              hari.isNotEmpty && hari != '-' ? '$hari ($jam)' : '-',
                              style: GoogleFonts.outfit(fontSize: 11.5, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.location_on_outlined, size: 14, color: Color(0xFFDC2626)),
                            const SizedBox(width: 6),
                            Text(
                              'Lokasi:',
                              style: TextStyle(fontSize: 11, color: isDark ? Colors.white54 : Colors.grey.shade600),
                            ),
                            const Spacer(),
                            Flexible(
                              child: Text(
                                tempat.isNotEmpty ? tempat : 'Area Sekolah',
                                style: GoogleFonts.outfit(fontSize: 11.5, fontWeight: FontWeight.bold),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Button Action: Joined vs Join
                  if (isJoined)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 9),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFF10B981).withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.done_all_rounded, size: 16, color: Color(0xFF10B981)),
                          const SizedBox(width: 6),
                          Text(
                            'Sudah Anda Ikuti',
                            style: GoogleFonts.outfit(
                              fontSize: 12.5,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF10B981),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2563EB),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(vertical: 9),
                          elevation: 0,
                        ),
                        icon: const Icon(Icons.add_circle_outline_rounded, size: 16),
                        label: Text(
                          'Ikuti Ekstrakurikuler',
                          style: GoogleFonts.outfit(fontSize: 12.5, fontWeight: FontWeight.bold),
                        ),
                        onPressed: () => _joinEkskul(ekskulId, nama),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_rounded, size: 54, color: Colors.redAccent),
            const SizedBox(height: 16),
            Text(
              'Gagal Memuat Data',
              style: GoogleFonts.outfit(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: isDark ? Colors.white : const Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _errorMessage,
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: isDark ? Colors.white60 : Colors.grey.shade600),
            ),
            const SizedBox(height: 18),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2563EB),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Lagi'),
              onPressed: _fetchEkskulData,
            ),
          ],
        ),
      ),
    );
  }
}
