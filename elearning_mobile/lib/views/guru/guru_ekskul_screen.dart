import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class GuruEkskulScreen extends StatefulWidget {
  final int? initialEkskulId;
  const GuruEkskulScreen({super.key, this.initialEkskulId});

  @override
  State<GuruEkskulScreen> createState() => _GuruEkskulScreenState();
}

class _GuruEkskulScreenState extends State<GuruEkskulScreen> {
  bool _isLoading = true;
  bool _isSaving = false;
  String _errorMessage = '';

  bool _isPembimbing = false;
  Map<String, dynamic> _activeTa = {};
  List<dynamic> _guidedEkskul = [];
  Map<String, dynamic>? _selectedEkskul;
  int _selectedEkskulId = 0;

  List<dynamic> _anggotaList = [];
  List<dynamic> _availableSiswa = [];

  // Controllers & values for student assessment [anggota_id -> {'predikat': ..., 'deskripsi': ...}]
  final Map<int, String> _predikatMap = {};
  final Map<int, TextEditingController> _deskripsiControllers = {};

  final TextEditingController _searchController = TextEditingController();
  String _searchKeyword = '';

  @override
  void initState() {
    super.initState();
    _selectedEkskulId = widget.initialEkskulId ?? 0;
    _fetchData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    for (final c in _deskripsiControllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _fetchData({int? ekskulId}) async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final targetId = ekskulId ?? (_selectedEkskulId > 0 ? _selectedEkskulId : null);
    final params = {
      'user_id': userId.toString(),
      if (targetId != null && targetId > 0) 'id': targetId.toString(),
    };

    try {
      final res = await ApiService.get('guru/ekstrakurikuler', params: params);
      if (!mounted) return;

      if (res['success'] == true && res['data'] != null) {
        final data = res['data'];
        _isPembimbing = data['is_pembimbing'] == true;
        _activeTa = (data['active_ta'] as Map?)?.cast<String, dynamic>() ?? {};
        _guidedEkskul = (data['guided_ekskul'] as List?) ?? [];
        _selectedEkskul = (data['selected_ekskul'] as Map?)?.cast<String, dynamic>();
        _selectedEkskulId = int.tryParse((_selectedEkskul?['id'] ?? 0).toString()) ?? 0;

        _anggotaList = (data['anggota_list'] as List?) ?? [];
        _availableSiswa = (data['available_siswa'] as List?) ?? [];

        // Clear existing controllers and re-populate
        for (final c in _deskripsiControllers.values) {
          c.dispose();
        }
        _deskripsiControllers.clear();
        _predikatMap.clear();

        for (final ang in _anggotaList) {
          final aId = int.tryParse((ang['id'] ?? 0).toString()) ?? 0;
          if (aId > 0) {
            _predikatMap[aId] = (ang['predikat'] ?? 'Sangat Baik').toString();
            _deskripsiControllers[aId] = TextEditingController(text: (ang['nilai_deskripsi'] ?? '').toString());
          }
        }

        setState(() => _isLoading = false);
      } else {
        setState(() {
          _isLoading = false;
          _errorMessage = res['message']?.toString() ?? 'Gagal memuat data bimbingan ekstrakurikuler.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = 'Terjadi kesalahan jaringan: $e';
      });
    }
  }

  // --- Simpan Penilaian E-Rapor Batch ---
  Future<void> _savePenilaian() async {
    if (_selectedEkskulId <= 0 || _anggotaList.isEmpty) return;

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    setState(() => _isSaving = true);

    // Build payload matching Web GuruController format
    final Map<String, dynamic> nilaiPayload = {};
    for (final ang in _anggotaList) {
      final aId = int.tryParse((ang['id'] ?? 0).toString()) ?? 0;
      if (aId > 0) {
        nilaiPayload[aId.toString()] = {
          'predikat': _predikatMap[aId] ?? 'Sangat Baik',
          'deskripsi': _deskripsiControllers[aId]?.text.trim() ?? '',
        };
      }
    }

    final body = {
      'action': 'save_nilai_deskripsi',
      'ekskul_id': _selectedEkskulId,
      'nilai': nilaiPayload,
    };

    try {
      final res = await ApiService.post('guru/ekstrakurikuler?user_id=$userId', body);
      if (!mounted) return;
      setState(() => _isSaving = false);

      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                const SizedBox(width: 10),
                Expanded(child: Text(res['message']?.toString() ?? 'Penilaian E-Rapor berhasil disimpan!')),
              ],
            ),
            backgroundColor: const Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
        _fetchData(ekskulId: _selectedEkskulId);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message']?.toString() ?? 'Gagal menyimpan penilaian E-Rapor'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSaving = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kendala saat menyimpan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  // --- Dialog Tambah Siswa Manual ---
  void _showAddAnggotaDialog() {
    int? selectedSiswaId;
    String searchSiswa = '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            final mediaQuery = MediaQuery.of(ctx);
            final bottomInset = mediaQuery.viewInsets.bottom;
            final availableHeight = (mediaQuery.size.height - bottomInset).clamp(300.0, mediaQuery.size.height * 0.85);

            final filteredSiswa = _availableSiswa.where((s) {
              final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
              final nis = (s['nis'] ?? '').toString().toLowerCase();
              final kelas = (s['nama_kelas'] ?? '').toString().toLowerCase();
              final q = searchSiswa.toLowerCase().trim();
              if (q.isEmpty) return true;
              return name.contains(q) || nis.contains(q) || kelas.contains(q);
            }).toList();

            return Padding(
              padding: EdgeInsets.only(bottom: bottomInset),
              child: Container(
                constraints: BoxConstraints(maxHeight: availableHeight),
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Drag indicator
                    Container(
                      margin: const EdgeInsets.only(top: 10, bottom: 6),
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                    // Header
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 6, 12, 10),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.person_add_alt_1_rounded, color: Color(0xFFD97706), size: 22),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Daftarkan Siswa Binaan',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFF0F172A),
                                  ),
                                ),
                                Text(
                                  'Pilih siswa untuk bergabung ke ${_selectedEkskul?['nama_ekskul'] ?? "Ekskul"}',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 11,
                                    color: const Color(0xFF64748B),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close_rounded, size: 20),
                            onPressed: () => Navigator.pop(ctx),
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 1),

                    // Search input
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
                      child: TextField(
                        decoration: InputDecoration(
                          isDense: true,
                          hintText: 'Cari nama, NIS, atau kelas siswa...',
                          prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF64748B)),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                        ),
                        onChanged: (val) {
                          setModalState(() => searchSiswa = val);
                        },
                      ),
                    ),

                    // Siswa list
                    Flexible(
                      child: filteredSiswa.isEmpty
                          ? Container(
                              padding: const EdgeInsets.all(32),
                              alignment: Alignment.center,
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.search_off_rounded, size: 40, color: Colors.grey.shade400),
                                  const SizedBox(height: 8),
                                  Text(
                                    searchSiswa.isEmpty
                                        ? 'Semua siswa telah terdaftar ke ekskul ini.'
                                        : 'Tidak ada siswa yang cocok dengan pencarian.',
                                    textAlign: TextAlign.center,
                                    style: GoogleFonts.plusJakartaSans(fontSize: 12, color: const Color(0xFF64748B)),
                                  ),
                                ],
                              ),
                            )
                          : ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                              itemCount: filteredSiswa.length,
                              itemBuilder: (context, idx) {
                                final s = filteredSiswa[idx];
                                final sId = int.tryParse((s['id'] ?? 0).toString()) ?? 0;
                                final isChosen = selectedSiswaId == sId;

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 6),
                                  decoration: BoxDecoration(
                                    color: isChosen ? const Color(0xFFEEF2FF) : Colors.white,
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(
                                      color: isChosen ? const Color(0xFF4F46E5) : const Color(0xFFE2E8F0),
                                      width: isChosen ? 1.5 : 1,
                                    ),
                                  ),
                                  child: ListTile(
                                    dense: true,
                                    leading: CircleAvatar(
                                      backgroundColor: isChosen ? const Color(0xFF4F46E5) : const Color(0xFFF1F5F9),
                                      foregroundColor: isChosen ? Colors.white : const Color(0xFF475569),
                                      child: Text(
                                        (s['nama_lengkap'] ?? 'S').toString().substring(0, 1).toUpperCase(),
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                    ),
                                    title: Text(
                                      s['nama_lengkap']?.toString() ?? 'Siswa',
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: GoogleFonts.plusJakartaSans(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                        color: const Color(0xFF0F172A),
                                      ),
                                    ),
                                    subtitle: Text(
                                      'Kelas: ${s['nama_kelas'] ?? "-"} • NIS: ${s['nis'] ?? "-"}',
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: GoogleFonts.plusJakartaSans(fontSize: 11, color: const Color(0xFF64748B)),
                                    ),
                                    trailing: isChosen
                                        ? const Icon(Icons.check_circle_rounded, color: Color(0xFF4F46E5), size: 22)
                                        : const Icon(Icons.radio_button_unchecked_rounded, color: Color(0xFF94A3B8), size: 20),
                                    onTap: () {
                                      setModalState(() => selectedSiswaId = sId);
                                    },
                                  ),
                                );
                              },
                            ),
                    ),

                    // Action buttons
                    Container(
                      padding: const EdgeInsets.fromLTRB(20, 10, 20, 16),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
                      ),
                      child: ElevatedButton.icon(
                        onPressed: selectedSiswaId == null || selectedSiswaId! <= 0
                            ? null
                            : () async {
                                Navigator.pop(ctx);
                                await _addAnggota(selectedSiswaId!);
                              },
                        icon: const Icon(Icons.check_rounded, color: Colors.white, size: 20),
                        label: const Text(
                          'Daftarkan Siswa ke Ekskul Ini',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4F46E5),
                          disabledBackgroundColor: Colors.grey.shade300,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          minimumSize: const Size.fromHeight(48),
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

  Future<void> _addAnggota(int siswaId) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    setState(() => _isLoading = true);

    final body = {
      'action': 'add_anggota_manual',
      'ekskul_id': _selectedEkskulId,
      'siswa_id': siswaId,
    };

    final res = await ApiService.post('guru/ekstrakurikuler?user_id=$userId', body);
    if (!mounted) return;

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? 'Siswa berhasil didaftarkan!'),
          backgroundColor: const Color(0xFF10B981),
        ),
      );
      _fetchData(ekskulId: _selectedEkskulId);
    } else {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? 'Gagal mendaftarkan siswa'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  // --- Konfirmasi Keluarkan Anggota ---
  void _confirmRemoveAnggota(int siswaId, String namaSiswa) {
    showDialog(
      context: context,
      builder: (dialogCtx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.person_remove_rounded, color: Color(0xFFEF4444), size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Keluarkan Siswa?',
                  style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          content: Text(
            'Apakah Anda yakin ingin mengeluarkan "$namaSiswa" dari bimbingan ${_selectedEkskul?['nama_ekskul'] ?? "ekstrakurikuler ini"}?',
            style: GoogleFonts.plusJakartaSans(fontSize: 13, color: const Color(0xFF475569)),
          ),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogCtx),
              child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B))),
            ),
            ElevatedButton(
              onPressed: () async {
                Navigator.pop(dialogCtx);
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                final userId = user?.id ?? 0;

                setState(() => _isLoading = true);

                final body = {
                  'action': 'remove_anggota',
                  'ekskul_id': _selectedEkskulId,
                  'siswa_id': siswaId,
                };

                final res = await ApiService.post('guru/ekstrakurikuler?user_id=$userId', body);
                if (!mounted) return;

                if (res['success'] == true) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(res['message']?.toString() ?? 'Siswa berhasil dikeluarkan.'),
                      backgroundColor: const Color(0xFF10B981),
                    ),
                  );
                  _fetchData(ekskulId: _selectedEkskulId);
                } else {
                  setState(() => _isLoading = false);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(res['message']?.toString() ?? 'Gagal mengeluarkan siswa.'),
                      backgroundColor: Colors.red,
                    ),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFEF4444),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('Keluarkan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          'Bimbingan Ekstrakurikuler',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.bold,
            fontSize: 16.5,
            color: isDark ? Colors.white : const Color(0xFF0F172A),
          ),
        ),
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 0.5,
        actions: [
          IconButton(
            tooltip: 'Segarkan',
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => _fetchData(ekskulId: _selectedEkskulId),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFFF59E0B)),
                  SizedBox(height: 16),
                  Text(
                    'Memuat Bimbingan Ekstrakurikuler...',
                    style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                  ),
                ],
              ),
            )
          : _errorMessage.isNotEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.error_outline_rounded, size: 54, color: Colors.red.shade400),
                        const SizedBox(height: 16),
                        Text(
                          'Gagal Memuat Data',
                          style: GoogleFonts.plusJakartaSans(fontSize: 17, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _errorMessage,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton.icon(
                          onPressed: () => _fetchData(ekskulId: _selectedEkskulId),
                          icon: const Icon(Icons.refresh_rounded),
                          label: const Text('Coba Lagi'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF4F46E5),
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : !_isPembimbing
                  ? _buildNotPembimbingState()
                  : RefreshIndicator(
                      color: const Color(0xFFF59E0B),
                      onRefresh: () async => _fetchData(ekskulId: _selectedEkskulId),
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // 1. Hero Card
                            _buildHeroCard(),
                            const SizedBox(height: 14),

                            // 2. Ekskul Selector Tabs (jika guru membimbing > 1 ekskul)
                            if (_guidedEkskul.length > 1) ...[
                              _buildEkskulSelectorTabs(),
                              const SizedBox(height: 14),
                            ],

                            // 3. Information Card (Selected Ekskul)
                            if (_selectedEkskul != null) ...[
                              _buildEkskulDetailCard(),
                              const SizedBox(height: 16),
                            ],

                            // 4. Penilaian E-Rapor Section
                            _buildPenilaianSection(),
                          ],
                        ),
                      ),
                    ),
      // Sticky bottom bar to save grades
      bottomNavigationBar: (_isPembimbing && _anggotaList.isNotEmpty && !_isLoading)
          ? Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x100F172A),
                    blurRadius: 10,
                    offset: Offset(0, -3),
                  ),
                ],
              ),
              child: SafeArea(
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${_anggotaList.length} Siswa Terdaftar',
                            style: GoogleFonts.plusJakartaSans(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: isDark ? Colors.white : const Color(0xFF0F172A),
                            ),
                          ),
                          Text(
                            'Predikat & Deskripsi E-Rapor',
                            style: GoogleFonts.plusJakartaSans(fontSize: 11, color: const Color(0xFF64748B)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton.icon(
                      onPressed: _isSaving ? null : _savePenilaian,
                      icon: _isSaving
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                      label: Text(
                        _isSaving ? 'Menyimpan...' : 'Simpan E-Rapor',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 2,
                      ),
                    ),
                  ],
                ),
              ),
            )
          : null,
    );
  }

  // --- Hero Header Card ---
  Widget _buildHeroCard() {
    final taTahun = _activeTa['tahun'] ?? '2025/2026';
    final taSem = _activeTa['semester'] ?? 'Ganjil';

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFD97706), Color(0xFFB45309), Color(0xFF78350F)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [
          BoxShadow(
            color: Color(0x33D97706),
            blurRadius: 14,
            offset: Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.emoji_events_rounded, color: Colors.white, size: 28),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Bimbingan Ekstrakurikuler',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 16.5,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Kelola siswa binaan & input capaian E-Rapor Digital',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11.5,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.25),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.calendar_month_rounded, color: Colors.amber, size: 14),
                const SizedBox(width: 6),
                Flexible(
                  child: Text(
                    'TA $taTahun ($taSem)',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // --- Ekskul Selector Tabs (Jika > 1) ---
  Widget _buildEkskulSelectorTabs() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: _guidedEkskul.map((ge) {
          final id = int.tryParse((ge['id'] ?? 0).toString()) ?? 0;
          final isSelected = id == _selectedEkskulId;
          final nama = ge['nama_ekskul']?.toString() ?? 'Ekskul';
          final count = ge['total_anggota'] ?? 0;

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              avatar: Icon(
                Icons.emoji_events_rounded,
                size: 16,
                color: isSelected ? Colors.white : const Color(0xFFD97706),
              ),
              label: Text('$nama ($count)'),
              labelStyle: GoogleFonts.plusJakartaSans(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                color: isSelected ? Colors.white : const Color(0xFF0F172A),
              ),
              selected: isSelected,
              selectedColor: const Color(0xFFD97706),
              backgroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
                side: BorderSide(
                  color: isSelected ? const Color(0xFFD97706) : const Color(0xFFE2E8F0),
                ),
              ),
              onSelected: (_) {
                if (id != _selectedEkskulId) {
                  _fetchData(ekskulId: id);
                }
              },
            ),
          );
        }).toList(),
      ),
    );
  }

  // --- Detail Kartu Ekskul Terpilih ---
  Widget _buildEkskulDetailCard() {
    final e = _selectedEkskul!;
    final nama = e['nama_ekskul']?.toString() ?? 'Ekstrakurikuler';
    final hari = (e['hari'] ?? '-').toString();
    final jam = (e['jam'] ?? '-').toString();
    final tempat = (e['tempat'] ?? '-').toString();
    final deskripsi = (e['deskripsi'] ?? '').toString();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x060F172A),
            blurRadius: 8,
            offset: Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.stars_rounded, color: Color(0xFFD97706), size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      nama,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFDCFCE7),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'Bimbingan Aktif',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 10.5,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF15803D),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '${_anggotaList.length} Siswa',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF64748B),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Divider(height: 1),
          const SizedBox(height: 10),

          // Jadwal & Lokasi Row
          Row(
            children: [
              Expanded(
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today_rounded, size: 14, color: Color(0xFF64748B)),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        '$hari ($jam)',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.plusJakartaSans(fontSize: 11.5, color: const Color(0xFF334155)),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Row(
                  children: [
                    const Icon(Icons.place_rounded, size: 15, color: Color(0xFFEF4444)),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        tempat,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.plusJakartaSans(fontSize: 11.5, color: const Color(0xFF334155)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          if (deskripsi.isNotEmpty) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Text(
                deskripsi,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.plusJakartaSans(fontSize: 11, color: const Color(0xFF64748B), height: 1.4),
              ),
            ),
          ],

          const SizedBox(height: 12),

          // Button daftarkan siswa
          OutlinedButton.icon(
            onPressed: _showAddAnggotaDialog,
            icon: const Icon(Icons.person_add_alt_1_rounded, size: 16),
            label: const Text('Daftarkan Siswa ke Ekskul Ini'),
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFFD97706),
              side: const BorderSide(color: Color(0xFFD97706)),
              padding: const EdgeInsets.symmetric(vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ],
      ),
    );
  }

  // --- Penilaian Anggota untuk E-Rapor Digital ---
  Widget _buildPenilaianSection() {
    final filteredAnggota = _anggotaList.where((a) {
      final name = (a['nama_lengkap'] ?? '').toString().toLowerCase();
      final nis = (a['nis'] ?? '').toString().toLowerCase();
      final kelas = (a['nama_kelas'] ?? '').toString().toLowerCase();
      final q = _searchKeyword.toLowerCase().trim();
      if (q.isEmpty) return true;
      return name.contains(q) || nis.contains(q) || kelas.contains(q);
    }).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Section Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Penilaian Anggota E-Rapor',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF0F172A),
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFEEF2FF),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                '${filteredAnggota.length} Siswa',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF4F46E5),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          'Input predikat capaian & deskripsi hasil pembinaan untuk dicetak pada lembar E-Rapor Digital.',
          style: GoogleFonts.plusJakartaSans(fontSize: 11, color: const Color(0xFF64748B)),
        ),
        const SizedBox(height: 12),

        // Search Input
        TextField(
          controller: _searchController,
          decoration: InputDecoration(
            isDense: true,
            hintText: 'Cari siswa bimbingan...',
            prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF64748B)),
            suffixIcon: _searchKeyword.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear_rounded, size: 18),
                    onPressed: () {
                      _searchController.clear();
                      setState(() => _searchKeyword = '');
                    },
                  )
                : null,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            filled: true,
            fillColor: Colors.white,
          ),
          onChanged: (val) {
            setState(() => _searchKeyword = val);
          },
        ),
        const SizedBox(height: 14),

        if (filteredAnggota.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            alignment: Alignment.center,
            child: Column(
              children: [
                Icon(Icons.group_off_rounded, size: 44, color: Colors.grey.shade400),
                const SizedBox(height: 10),
                Text(
                  _searchKeyword.isNotEmpty
                      ? 'Tidak ada siswa yang sesuai dengan kata pencarian.'
                      : 'Belum ada siswa yang terdaftar di ekskul ini.',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.plusJakartaSans(fontSize: 12.5, color: const Color(0xFF64748B)),
                ),
                if (_searchKeyword.isEmpty) ...[
                  const SizedBox(height: 14),
                  ElevatedButton.icon(
                    onPressed: _showAddAnggotaDialog,
                    icon: const Icon(Icons.person_add_alt_1_rounded, size: 16, color: Colors.white),
                    label: const Text('Daftarkan Siswa Sekarang', style: TextStyle(color: Colors.white)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFD97706),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ],
              ],
            ),
          )
        else
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: filteredAnggota.length,
            itemBuilder: (context, idx) {
              final ang = filteredAnggota[idx];
              return _buildAnggotaNilaiCard(ang, idx + 1);
            },
          ),
      ],
    );
  }

  // --- Card Per Siswa untuk Input Nilai E-Rapor ---
  Widget _buildAnggotaNilaiCard(Map<String, dynamic> ang, int no) {
    final anggotaId = int.tryParse((ang['id'] ?? 0).toString()) ?? 0;
    final siswaId = int.tryParse((ang['siswa_id'] ?? 0).toString()) ?? 0;
    final nama = ang['nama_lengkap']?.toString() ?? 'Siswa';
    final nis = (ang['nis'] ?? '-').toString();
    final kelas = (ang['nama_kelas'] ?? '-').toString();

    final currentPredikat = _predikatMap[anggotaId] ?? 'Sangat Baik';
    final descController = _deskripsiControllers[anggotaId];

    Color getPredikatColor(String p) {
      switch (p) {
        case 'Sangat Baik':
          return const Color(0xFF10B981);
        case 'Baik':
          return const Color(0xFF3B82F6);
        case 'Cukup':
          return const Color(0xFFF59E0B);
        case 'Kurang':
          return const Color(0xFFEF4444);
        default:
          return const Color(0xFF64748B);
      }
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x040F172A),
            blurRadius: 6,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Row Header: No, Nama, Kelas, Action Remove
          Row(
            children: [
              Container(
                width: 24,
                height: 24,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  no.toString(),
                  style: GoogleFonts.robotoMono(fontSize: 11, fontWeight: FontWeight.bold, color: const Color(0xFF475569)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      nama,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 13.5,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF0F172A),
                      ),
                    ),
                    Text(
                      'Kelas: $kelas • NIS: $nis',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(fontSize: 11, color: const Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              IconButton(
                icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFEF4444)),
                tooltip: 'Keluarkan Siswa',
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                onPressed: () => _confirmRemoveAnggota(siswaId, nama),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 10),

          // Predikat Selector
          Row(
            children: [
              Text(
                'Predikat:',
                style: GoogleFonts.plusJakartaSans(fontSize: 11.5, fontWeight: FontWeight.bold, color: const Color(0xFF334155)),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10),
                  decoration: BoxDecoration(
                    color: getPredikatColor(currentPredikat).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: getPredikatColor(currentPredikat).withValues(alpha: 0.4)),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: currentPredikat,
                      isDense: true,
                      isExpanded: true,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: getPredikatColor(currentPredikat),
                      ),
                      icon: Icon(Icons.arrow_drop_down_rounded, color: getPredikatColor(currentPredikat)),
                      items: const [
                        DropdownMenuItem(value: 'Sangat Baik', child: Text('Sangat Baik')),
                        DropdownMenuItem(value: 'Baik', child: Text('Baik')),
                        DropdownMenuItem(value: 'Cukup', child: Text('Cukup')),
                        DropdownMenuItem(value: 'Kurang', child: Text('Kurang')),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          setState(() => _predikatMap[anggotaId] = val);
                        }
                      },
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Deskripsi Capaian Text Field
          Text(
            'Deskripsi Capaian Hasil Belajar (E-Rapor):',
            style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.bold, color: const Color(0xFF475569)),
          ),
          const SizedBox(height: 6),
          TextField(
            controller: descController,
            maxLines: 2,
            style: GoogleFonts.plusJakartaSans(fontSize: 12),
            decoration: InputDecoration(
              isDense: true,
              hintText: 'Contoh: Sangat aktif mengikuti latihan rutin, menunjukkan kedisiplinan dan kepemimpinan yang baik.',
              hintStyle: const TextStyle(fontSize: 11.5, color: Color(0xFF94A3B8)),
              contentPadding: const EdgeInsets.all(10),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
            ),
          ),
          const SizedBox(height: 6),

          // Template Chip Shortcuts
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildTemplateChip('Disiplin & Aktif', 'Sangat aktif mengikuti kegiatan latihan rutin dan memiliki kedisiplinan yang tinggi.', descController),
                const SizedBox(width: 6),
                _buildTemplateChip('Penguasaan Teknik', 'Menunjukkan penguasaan teknik dasar serta antusiasme yang sangat baik.', descController),
                const SizedBox(width: 6),
                _buildTemplateChip('Kepemimpinan', 'Menunjukkan sikap kepemimpinan, kerja sama tim, dan sportivitas yang unggul.', descController),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTemplateChip(String label, String templateText, TextEditingController? controller) {
    return ActionChip(
      visualDensity: VisualDensity.compact,
      label: Text(label),
      labelStyle: GoogleFonts.plusJakartaSans(fontSize: 10, color: const Color(0xFF4F46E5), fontWeight: FontWeight.w600),
      backgroundColor: const Color(0xFFEEF2FF),
      side: const BorderSide(color: Color(0xFFC7D2FE)),
      onPressed: () {
        if (controller != null) {
          controller.text = templateText;
        }
      },
    );
  }

  // --- Tampilan Jika Guru Bukan Pembimbing ---
  Widget _buildNotPembimbingState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Container(
          padding: const EdgeInsets.all(28),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: const [
              BoxShadow(
                color: Color(0x060F172A),
                blurRadius: 12,
                offset: Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.person_pin_rounded, size: 48, color: Colors.white),
              ),
              const SizedBox(height: 20),
              Text(
                'Anda Belum Ditugaskan Sebagai Pembimbing',
                textAlign: TextAlign.center,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 16.5,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Fitur bimbingan dan penilaian ekstrakurikuler ini dikhususkan bagi Bapak/Ibu Guru yang telah ditugaskan secara resmi oleh Administrator Sekolah sebagai pembimbing kegiatan ekstrakurikuler.',
                textAlign: TextAlign.center,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 12,
                  height: 1.5,
                  color: const Color(0xFF64748B),
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.arrow_back_rounded, size: 18, color: Colors.white),
                label: const Text('Kembali ke Dashboard', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4F46E5),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
