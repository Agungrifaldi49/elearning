import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GuruCptpScreen extends StatefulWidget {
  const GuruCptpScreen({super.key});

  @override
  State<GuruCptpScreen> createState() => _GuruCptpScreenState();
}

class _GuruCptpScreenState extends State<GuruCptpScreen> {
  bool _isLoading = true;
  String _errorMessage = '';

  int _selectedMapelId = 0; // 0 for all subjects
  int _selectedKurikulumId = 0;
  int _selectedFaseId = 0;
  final TextEditingController _searchController = TextEditingController();

  Map<String, dynamic> _stats = {};
  List<dynamic> _teacherMapels = [];
  List<dynamic> _kurikulumList = [];
  List<dynamic> _faseList = [];
  List<dynamic> _mapelGroups = [];

  final Set<int> _expandedCpIds = {};
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

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
      final userId = user?.id ?? 0;

      final params = <String, String>{
        'user_id': userId.toString(),
      };
      if (_selectedMapelId > 0) {
        params['filter_mapel_id'] = _selectedMapelId.toString();
      }
      if (_selectedKurikulumId > 0) {
        params['filter_kurikulum_id'] = _selectedKurikulumId.toString();
      }
      if (_selectedFaseId > 0) {
        params['filter_fase_id'] = _selectedFaseId.toString();
      }
      if (_searchController.text.trim().isNotEmpty) {
        params['q'] = _searchController.text.trim();
      }

      final res = await ApiService.get('guru/cptp', params: params);
      if (!mounted) return;

      final bool isSuccess = _isTrue(res['success']) || _isTrue(res['status']);
      final dynamic rawData = res['data'];

      if (isSuccess && rawData is Map) {
        final dataMap = Map<String, dynamic>.from(rawData);
        final List groups = (dataMap['mapel_groups'] is List) ? dataMap['mapel_groups'] : [];
        final List mapels = (dataMap['teacher_mapels'] is List) ? dataMap['teacher_mapels'] : [];
        final List kurikulums = (dataMap['kurikulum_list'] is List) ? dataMap['kurikulum_list'] : [];
        final List fases = (dataMap['fase_list'] is List) ? dataMap['fase_list'] : [];

        // Auto-expand all mapel groups and top CPs
        _expandedMapelIds.clear();
        for (var g in groups) {
          final mId = int.tryParse((g['mapel_id'] ?? 0).toString()) ?? 0;
          if (mId > 0) {
            _expandedMapelIds.add(mId);
            final cpList = (g['cp_list'] is List) ? g['cp_list'] : [];
            for (var c in cpList) {
              final cId = int.tryParse((c['id'] ?? 0).toString()) ?? 0;
              if (cId > 0) {
                _expandedCpIds.add(cId);
              }
            }
          }
        }

        setState(() {
          _stats = (dataMap['stats'] is Map) ? Map<String, dynamic>.from(dataMap['stats']) : {};
          _teacherMapels = mapels;
          _kurikulumList = kurikulums;
          _faseList = fases;
          _mapelGroups = groups;
          if (_selectedKurikulumId == 0 && kurikulums.isNotEmpty) {
            _selectedKurikulumId = int.tryParse((kurikulums[0]['id'] ?? 0).toString()) ?? 0;
          }
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = res['message']?.toString() ?? 'Gagal memuat data CP & TP Guru.';
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Terjadi kesalahan sistem: $e';
          _isLoading = false;
        });
      }
    }
  }

  // --- Modal Form: Tambah / Edit CP ---
  void _showCpFormDialog({Map<String, dynamic>? cpData, int? defaultMapelId}) async {
    final isEdit = cpData != null;
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    int mapelId = cpData != null
        ? (int.tryParse((cpData['mapel_id'] ?? 0).toString()) ?? 0)
        : (defaultMapelId ?? (_teacherMapels.isNotEmpty ? (int.tryParse((_teacherMapels[0]['id'] ?? 0).toString()) ?? 0) : 0));
    int kurId = cpData != null
        ? (int.tryParse((cpData['kurikulum_id'] ?? 0).toString()) ?? 0)
        : (_selectedKurikulumId > 0
            ? _selectedKurikulumId
            : (_kurikulumList.isNotEmpty ? (int.tryParse((_kurikulumList[0]['id'] ?? 0).toString()) ?? 0) : 0));
    int? faseId = cpData != null ? (int.tryParse((cpData['fase_id'] ?? '').toString())) : null;

    final kodeController = TextEditingController(text: cpData?['kode_cp']?.toString() ?? '');
    final elemenController = TextEditingController(text: cpData?['elemen']?.toString() ?? '');
    final deskripsiController = TextEditingController(text: cpData?['deskripsi']?.toString() ?? '');

    // Fetch next CP code if creating
    if (!isEdit && kodeController.text.isEmpty && kurId > 0 && mapelId > 0) {
      try {
        final resCode = await ApiService.post('guru/cptp?user_id=$userId', {
          'action': 'get_next_code',
          'type': 'cp',
          'kurikulum_id': kurId,
          'mapel_id': mapelId,
        });
        if (resCode['success'] == true && resCode['data']?['next_code'] != null) {
          kodeController.text = resCode['data']['next_code'].toString();
        }
      } catch (_) {}
    }

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalContext, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEEF2FF),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.bookmark_added_rounded, color: Color(0xFF4F46E5), size: 24),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isEdit ? 'Edit Capaian Pembelajaran' : 'Tambah Capaian Pembelajaran (CP)',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF0F172A),
                                ),
                              ),
                              Text(
                                'Penyusunan kompetensi inti Kurikulum Merdeka',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 11.5,
                                  color: const Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close_rounded),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    const Divider(height: 24),

                    // Mapel Dropdown
                    Text('Mata Pelajaran', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      initialValue: mapelId > 0 ? mapelId : null,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                      ),
                      hint: const Text('Pilih Mata Pelajaran'),
                      items: _teacherMapels.map((m) {
                        final id = int.tryParse((m['id'] ?? 0).toString()) ?? 0;
                        return DropdownMenuItem<int>(
                          value: id,
                          child: Text(m['nama_mapel']?.toString() ?? 'Mapel', overflow: TextOverflow.ellipsis),
                        );
                      }).toList(),
                      onChanged: (val) async {
                        if (val != null) {
                          setModalState(() => mapelId = val);
                          if (!isEdit && kurId > 0) {
                            try {
                              final resCode = await ApiService.post('guru/cptp?user_id=$userId', {
                                'action': 'get_next_code',
                                'type': 'cp',
                                'kurikulum_id': kurId,
                                'mapel_id': val,
                              });
                              if (resCode['success'] == true && resCode['data']?['next_code'] != null) {
                                setModalState(() {
                                  kodeController.text = resCode['data']['next_code'].toString();
                                });
                              }
                            } catch (_) {}
                          }
                        }
                      },
                    ),
                    const SizedBox(height: 14),

                    // Kurikulum & Fase Row
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Kurikulum', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<int>(
                                initialValue: kurId > 0 ? kurId : null,
                                decoration: InputDecoration(
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  filled: true,
                                  fillColor: const Color(0xFFF8FAFC),
                                ),
                                hint: const Text('Kurikulum'),
                                items: _kurikulumList.map((k) {
                                  final id = int.tryParse((k['id'] ?? 0).toString()) ?? 0;
                                  return DropdownMenuItem<int>(
                                    value: id,
                                    child: Text(k['kode']?.toString() ?? 'KM', overflow: TextOverflow.ellipsis),
                                  );
                                }).toList(),
                                onChanged: (val) {
                                  if (val != null) setModalState(() => kurId = val);
                                },
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Fase / Tingkat', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<int>(
                                initialValue: faseId,
                                decoration: InputDecoration(
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  filled: true,
                                  fillColor: const Color(0xFFF8FAFC),
                                ),
                                hint: const Text('Pilih Fase'),
                                items: [
                                  const DropdownMenuItem<int>(value: null, child: Text('Semua Fase')),
                                  ..._faseList.map((f) {
                                    final id = int.tryParse((f['id'] ?? 0).toString()) ?? 0;
                                    return DropdownMenuItem<int>(
                                      value: id,
                                      child: Text(f['nama']?.toString() ?? 'Fase', overflow: TextOverflow.ellipsis),
                                    );
                                  }),
                                ],
                                onChanged: (val) {
                                  setModalState(() => faseId = val);
                                },
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Kode CP
                    Text('Kode CP', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: kodeController,
                      decoration: InputDecoration(
                        hintText: 'Contoh: CP-RPL-01',
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Elemen CP
                    Text('Elemen Pembelajaran', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: elemenController,
                      decoration: InputDecoration(
                        hintText: 'Contoh: Pemrograman Berorientasi Objek',
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Deskripsi CP
                    Text('Deskripsi Capaian Pembelajaran', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: deskripsiController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Tuliskan deskripsi lengkap capaian pembelajaran fase ini...',
                        contentPadding: const EdgeInsets.all(14),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Submit Button
                    ElevatedButton.icon(
                      onPressed: () async {
                        if (mapelId <= 0) {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih mata pelajaran')));
                          return;
                        }
                        if (deskripsiController.text.trim().isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Deskripsi CP wajib diisi')));
                          return;
                        }

                        Navigator.pop(ctx);
                        setState(() => _isLoading = true);

                        final body = {
                          'action': isEdit ? 'update_cp' : 'create_cp',
                          if (isEdit) 'id': cpData['id'],
                          'kurikulum_id': kurId,
                          'mapel_id': mapelId,
                          'fase_id': faseId ?? '',
                          'kode_cp': kodeController.text.trim(),
                          'elemen': elemenController.text.trim(),
                          'deskripsi': deskripsiController.text.trim(),
                        };

                        final res = await ApiService.post('guru/cptp?user_id=$userId', body);
                        if (!mounted) return;

                        if (res['success'] == true) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(res['message']?.toString() ?? 'CP berhasil disimpan!'),
                              backgroundColor: const Color(0xFF10B981),
                            ),
                          );
                          _fetchCptpData();
                        } else {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(res['message']?.toString() ?? 'Gagal menyimpan CP'),
                              backgroundColor: Colors.red,
                            ),
                          );
                          setState(() => _isLoading = false);
                        }
                      },
                      icon: const Icon(Icons.check_rounded, color: Colors.white),
                      label: Text(
                        isEdit ? 'Perbarui Capaian Pembelajaran' : 'Simpan Capaian Pembelajaran',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4F46E5),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
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

  // --- Modal Form: Tambah / Edit TP ---
  void _showTpFormDialog({required int cpId, String? cpKode, String? cpElemen, Map<String, dynamic>? tpData}) async {
    final isEdit = tpData != null;
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final kodeController = TextEditingController(text: tpData?['kode_tp']?.toString() ?? '');
    final materiController = TextEditingController(text: tpData?['materi_pokok']?.toString() ?? '');
    final deskripsiController = TextEditingController(text: tpData?['deskripsi']?.toString() ?? '');
    final urutanController = TextEditingController(text: (tpData?['urutan'] ?? 1).toString());

    // Auto-generate code if creating
    if (!isEdit && kodeController.text.isEmpty && cpId > 0) {
      try {
        final resCode = await ApiService.post('guru/cptp?user_id=$userId', {
          'action': 'get_next_code',
          'type': 'tp',
          'cp_id': cpId,
        });
        if (resCode['success'] == true && resCode['data']?['next_code'] != null) {
          kodeController.text = resCode['data']['next_code'].toString();
        }
      } catch (_) {}
    }

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.flag_rounded, color: Color(0xFF10B981), size: 24),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isEdit ? 'Edit Tujuan Pembelajaran' : 'Tambah Tujuan Pembelajaran (TP)',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF0F172A),
                            ),
                          ),
                          Text(
                            'Induk: ${cpKode ?? "CP"} • ${cpElemen ?? ""}',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 11.5,
                              color: const Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 24),

                // Kode TP & Urutan Row
                Row(
                  children: [
                    Expanded(
                      flex: 3,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Kode TP', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 6),
                          TextField(
                            controller: kodeController,
                            decoration: InputDecoration(
                              hintText: 'Contoh: TP-01.1',
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      flex: 2,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Urutan TP', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 6),
                          TextField(
                            controller: urutanController,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              hintText: '1',
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Materi Pokok
                Text('Materi Pokok / Lingkup Topik', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                const SizedBox(height: 6),
                TextField(
                  controller: materiController,
                  decoration: InputDecoration(
                    hintText: 'Contoh: Struktur Kontrol Percabangan & Perulangan',
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                  ),
                ),
                const SizedBox(height: 14),

                // Deskripsi TP
                Text('Deskripsi Tujuan Pembelajaran (TP)', style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold)),
                const SizedBox(height: 6),
                TextField(
                  controller: deskripsiController,
                  maxLines: 4,
                  decoration: InputDecoration(
                    hintText: 'Tuliskan kompetensi yang diharapkan dicapai siswa...',
                    contentPadding: const EdgeInsets.all(14),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                  ),
                ),
                const SizedBox(height: 24),

                // Submit Button
                ElevatedButton.icon(
                  onPressed: () async {
                    if (deskripsiController.text.trim().isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Deskripsi TP wajib diisi')));
                      return;
                    }

                    Navigator.pop(ctx);
                    setState(() => _isLoading = true);

                    final body = {
                      'action': isEdit ? 'update_tp' : 'create_tp',
                      if (isEdit) 'id': tpData['id'],
                      'cp_id': cpId,
                      'kode_tp': kodeController.text.trim(),
                      'materi_pokok': materiController.text.trim(),
                      'deskripsi': deskripsiController.text.trim(),
                      'urutan': int.tryParse(urutanController.text.trim()) ?? 1,
                    };

                    final res = await ApiService.post('guru/cptp?user_id=$userId', body);
                    if (!mounted) return;

                    if (res['success'] == true) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(res['message']?.toString() ?? 'TP berhasil disimpan!'),
                          backgroundColor: const Color(0xFF10B981),
                        ),
                      );
                      _fetchCptpData();
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(res['message']?.toString() ?? 'Gagal menyimpan TP'),
                          backgroundColor: Colors.red,
                        ),
                      );
                      setState(() => _isLoading = false);
                    }
                  },
                  icon: const Icon(Icons.check_rounded, color: Colors.white),
                  label: Text(
                    isEdit ? 'Perbarui Tujuan Pembelajaran' : 'Simpan Tujuan Pembelajaran',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // --- Delete Confirmations ---
  void _confirmDeleteCp(int cpId, String kodeCp) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Hapus CP $kodeCp?', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
        content: const Text(
          'Menghapus Capaian Pembelajaran ini akan otomatis menghapus atau mengarsipkan seluruh Tujuan Pembelajaran (TP) di bawahnya.',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              final userId = user?.id ?? 0;

              final res = await ApiService.post('guru/cptp?user_id=$userId', {
                'action': 'delete_cp',
                'id': cpId,
              });
              if (!mounted) return;

              if (res['success'] == true) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message']?.toString() ?? 'CP berhasil dihapus'),
                    backgroundColor: const Color(0xFF10B981),
                  ),
                );
                _fetchCptpData();
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message']?.toString() ?? 'Gagal menghapus CP'),
                    backgroundColor: Colors.red,
                  ),
                );
                setState(() => _isLoading = false);
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
            child: const Text('Ya, Hapus', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _confirmDeleteTp(int tpId, String kodeTp) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Hapus TP $kodeTp?', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
        content: const Text('Apakah Anda yakin ingin menghapus Tujuan Pembelajaran (TP) ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              final userId = user?.id ?? 0;

              final res = await ApiService.post('guru/cptp?user_id=$userId', {
                'action': 'delete_tp',
                'id': tpId,
              });
              if (!mounted) return;

              if (res['success'] == true) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message']?.toString() ?? 'TP berhasil dihapus'),
                    backgroundColor: const Color(0xFF10B981),
                  ),
                );
                _fetchCptpData();
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message']?.toString() ?? 'Gagal menghapus TP'),
                    backgroundColor: Colors.red,
                  ),
                );
                setState(() => _isLoading = false);
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
            child: const Text('Ya, Hapus', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          'Penyusunan CP & TP',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.bold,
            fontSize: 18,
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
            onPressed: _fetchCptpData,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showCpFormDialog(defaultMapelId: _selectedMapelId > 0 ? _selectedMapelId : null),
        backgroundColor: const Color(0xFF4F46E5),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Tambah CP', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFF4F46E5)),
                  SizedBox(height: 16),
                  Text(
                    'Memuat Data Kurikulum CP & TP...',
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
                        Icon(Icons.error_outline_rounded, size: 56, color: Colors.red.shade400),
                        const SizedBox(height: 16),
                        Text(
                          'Gagal Memuat CP & TP',
                          style: GoogleFonts.plusJakartaSans(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _errorMessage,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton.icon(
                          onPressed: _fetchCptpData,
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
              : RefreshIndicator(
                  color: const Color(0xFF4F46E5),
                  onRefresh: _fetchCptpData,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // 1. Welcome & Info Hero Card
                        _buildHeroHeader(),
                        const SizedBox(height: 16),

                        // 2. Statistics Quick Cards
                        _buildStatsRow(),
                        const SizedBox(height: 16),

                        // 3. Filter & Search Controls
                        _buildFilterSection(),
                        const SizedBox(height: 20),

                        // 4. Main CP & TP List grouped by Mapel
                        if (_mapelGroups.isEmpty)
                          _buildEmptyState()
                        else
                          ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _mapelGroups.length,
                            itemBuilder: (context, idx) {
                              final g = _mapelGroups[idx];
                              return _buildMapelGroupCard(g);
                            },
                          ),
                        const SizedBox(height: 80), // Padding for FAB
                      ],
                    ),
                  ),
                ),
    );
  }

  // --- Header Hero Card ---
  Widget _buildHeroHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: AppTheme.guruGradient,
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [
          BoxShadow(
            color: Color(0x334F46E5),
            blurRadius: 16,
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
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                ),
                child: const Icon(Icons.track_changes_rounded, color: Colors.white, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Penyusunan CP & TP Guru',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Kelola Capaian & Tujuan Pembelajaran SMK Terpadu',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 12,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.25),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.school_rounded, color: Colors.amber, size: 14),
                const SizedBox(width: 6),
                Text(
                  'Standar Kurikulum Merdeka Kejuruan (Fase E & Fase F)',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // --- Stats Row ---
  Widget _buildStatsRow() {
    final totalMapel = _stats['total_mapel'] ?? _teacherMapels.length;
    final totalCp = _stats['total_cp'] ?? 0;
    final totalTp = _stats['total_tp'] ?? 0;

    return Row(
      children: [
        _buildStatCard('Mata Pelajaran', totalMapel.toString(), Icons.menu_book_rounded, const Color(0xFF2563EB)),
        const SizedBox(width: 10),
        _buildStatCard('Capaian (CP)', totalCp.toString(), Icons.bookmark_added_rounded, const Color(0xFF7C3AED)),
        const SizedBox(width: 10),
        _buildStatCard('Tujuan (TP)', totalTp.toString(), Icons.flag_rounded, const Color(0xFF10B981)),
      ],
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: const [
            BoxShadow(
              color: Color(0x080F172A),
              blurRadius: 8,
              offset: Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 10.5,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF64748B),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // --- Filter & Search Section ---
  Widget _buildFilterSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Search Input
          TextField(
            controller: _searchController,
            onSubmitted: (_) => _fetchCptpData(),
            decoration: InputDecoration(
              hintText: 'Cari kode, materi, atau deskripsi CP/TP...',
              hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
              prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF64748B), size: 20),
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
              fillColor: const Color(0xFFF8FAFC),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // Mapel Filter Dropdown
          DropdownButtonFormField<int>(
            initialValue: _selectedMapelId,
            isExpanded: true,
            decoration: InputDecoration(
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
            ),
            items: [
              const DropdownMenuItem<int>(
                value: 0,
                child: Text('Semua Mata Pelajaran Diampu', style: TextStyle(fontWeight: FontWeight.w600)),
              ),
              ..._teacherMapels.map((m) {
                final id = int.tryParse((m['id'] ?? 0).toString()) ?? 0;
                return DropdownMenuItem<int>(
                  value: id,
                  child: Text(m['nama_mapel']?.toString() ?? 'Mapel'),
                );
              }),
            ],
            onChanged: (val) {
              if (val != null) {
                setState(() => _selectedMapelId = val);
                _fetchCptpData();
              }
            },
          ),
          const SizedBox(height: 10),

          // Fase Filter Dropdown
          DropdownButtonFormField<int>(
            initialValue: _selectedFaseId,
            isExpanded: true,
            decoration: InputDecoration(
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
            ),
            items: [
              const DropdownMenuItem<int>(
                value: 0,
                child: Text('Semua Fase (Fase E & F)', style: TextStyle(fontWeight: FontWeight.w600)),
              ),
              ..._faseList.map((f) {
                final id = int.tryParse((f['id'] ?? 0).toString()) ?? 0;
                return DropdownMenuItem<int>(
                  value: id,
                  child: Text(f['nama']?.toString() ?? 'Fase'),
                );
              }),
            ],
            onChanged: (val) {
              if (val != null) {
                setState(() => _selectedFaseId = val);
                _fetchCptpData();
              }
            },
          ),
        ],
      ),
    );
  }

  // --- Mapel Group Card ---
  Widget _buildMapelGroupCard(Map<String, dynamic> group) {
    final mapelId = int.tryParse((group['mapel_id'] ?? 0).toString()) ?? 0;
    final namaMapel = group['nama_mapel']?.toString() ?? 'Mata Pelajaran';
    final cpCount = group['cp_count'] ?? 0;
    final tpCount = group['tp_count'] ?? 0;
    final cpList = (group['cp_list'] is List) ? group['cp_list'] : [];
    final isExpanded = _expandedMapelIds.contains(mapelId);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x060F172A),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Bar
          InkWell(
            onTap: () {
              setState(() {
                if (isExpanded) {
                  _expandedMapelIds.remove(mapelId);
                } else {
                  _expandedMapelIds.add(mapelId);
                }
              });
            },
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.vertical(
                  top: const Radius.circular(20),
                  bottom: Radius.circular(isExpanded ? 0 : 20),
                ),
                border: Border(bottom: BorderSide(color: isExpanded ? const Color(0xFFE2E8F0) : Colors.transparent)),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEEF2FF),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.auto_stories_rounded, color: Color(0xFF4F46E5), size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          namaMapel,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 15,
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
                                color: const Color(0xFFEDE9FE),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '$cpCount CP',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF6D28D9),
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFDCFCE7),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '$tpCount TP',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF15803D),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.add_circle_outline_rounded, color: Color(0xFF4F46E5)),
                    tooltip: 'Tambah CP untuk Mapel ini',
                    onPressed: () => _showCpFormDialog(defaultMapelId: mapelId),
                  ),
                  Icon(
                    isExpanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    color: const Color(0xFF64748B),
                  ),
                ],
              ),
            ),
          ),

          // Content when expanded
          if (isExpanded)
            Padding(
              padding: const EdgeInsets.all(16),
              child: cpList.isEmpty
                  ? Container(
                      padding: const EdgeInsets.all(24),
                      alignment: Alignment.center,
                      child: Column(
                        children: [
                          Icon(Icons.inbox_rounded, size: 40, color: Colors.grey.shade400),
                          const SizedBox(height: 8),
                          Text(
                            'Belum ada Capaian Pembelajaran (CP) untuk mapel ini.',
                            style: GoogleFonts.plusJakartaSans(fontSize: 12, color: const Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 12),
                          OutlinedButton.icon(
                            onPressed: () => _showCpFormDialog(defaultMapelId: mapelId),
                            icon: const Icon(Icons.add_rounded, size: 16),
                            label: const Text('Buat CP Pertama'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF4F46E5),
                              side: const BorderSide(color: Color(0xFF4F46E5)),
                            ),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: cpList.length,
                      itemBuilder: (context, cpIdx) {
                        final cp = cpList[cpIdx];
                        return _buildCpCard(cp);
                      },
                    ),
            ),
        ],
      ),
    );
  }

  // --- Capaian Pembelajaran Card ---
  Widget _buildCpCard(Map<String, dynamic> cp) {
    final cpId = int.tryParse((cp['id'] ?? 0).toString()) ?? 0;
    final kodeCp = cp['kode_cp']?.toString() ?? 'CP';
    final elemen = cp['elemen']?.toString() ?? '';
    final deskripsi = cp['deskripsi']?.toString() ?? '';
    final kodeFase = cp['kode_fase']?.toString() ?? 'E';
    final namaFase = cp['nama_fase']?.toString() ?? 'Fase $kodeFase';
    final totalTp = cp['total_tp'] ?? 0;
    final tps = (cp['tujuan_pembelajaran'] is List) ? cp['tujuan_pembelajaran'] : [];
    final isExpanded = _expandedCpIds.contains(cpId);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // CP Header Bar
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF4F46E5),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        kodeCp,
                        style: GoogleFonts.robotoMono(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEDE9FE),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        namaFase,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF6D28D9),
                        ),
                      ),
                    ),
                    const Spacer(),
                    // Actions Menu (Edit / Delete)
                    PopupMenuButton<String>(
                      icon: const Icon(Icons.more_vert_rounded, size: 20, color: Color(0xFF64748B)),
                      onSelected: (val) {
                        if (val == 'edit') {
                          _showCpFormDialog(cpData: cp);
                        } else if (val == 'delete') {
                          _confirmDeleteCp(cpId, kodeCp);
                        } else if (val == 'add_tp') {
                          _showTpFormDialog(cpId: cpId, cpKode: kodeCp, cpElemen: elemen);
                        }
                      },
                      itemBuilder: (ctx) => [
                        const PopupMenuItem(
                          value: 'add_tp',
                          child: Row(
                            children: [
                              Icon(Icons.add_rounded, size: 18, color: Color(0xFF10B981)),
                              SizedBox(width: 8),
                              Text('Tambah TP'),
                            ],
                          ),
                        ),
                        const PopupMenuItem(
                          value: 'edit',
                          child: Row(
                            children: [
                              Icon(Icons.edit_rounded, size: 18, color: Color(0xFF3B82F6)),
                              SizedBox(width: 8),
                              Text('Edit CP'),
                            ],
                          ),
                        ),
                        const PopupMenuItem(
                          value: 'delete',
                          child: Row(
                            children: [
                              Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFEF4444)),
                              SizedBox(width: 8),
                              Text('Hapus CP'),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                if (elemen.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(
                    elemen,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                ],
                const SizedBox(height: 4),
                Text(
                  deskripsi,
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    height: 1.5,
                    color: const Color(0xFF475569),
                  ),
                ),
                const SizedBox(height: 10),

                // TP Summary Toggle Bar
                InkWell(
                  onTap: () {
                    setState(() {
                      if (isExpanded) {
                        _expandedCpIds.remove(cpId);
                      } else {
                        _expandedCpIds.add(cpId);
                      }
                    });
                  },
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.format_list_bulleted_rounded, size: 16, color: Color(0xFF10B981)),
                        const SizedBox(width: 6),
                        Text(
                          '$totalTp Tujuan Pembelajaran (TP)',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F172A),
                          ),
                        ),
                        const Spacer(),
                        Text(
                          isExpanded ? 'Tutup' : 'Lihat Rincian',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF4F46E5),
                          ),
                        ),
                        Icon(
                          isExpanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                          size: 16,
                          color: const Color(0xFF4F46E5),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // TP Children Items
          if (isExpanded)
            Container(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Divider(height: 1),
                  const SizedBox(height: 12),
                  if (tps.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(16),
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        children: [
                          const Icon(Icons.playlist_add_rounded, size: 32, color: Color(0xFF94A3B8)),
                          const SizedBox(height: 6),
                          Text(
                            'Belum ada TP untuk CP ini.',
                            style: GoogleFonts.plusJakartaSans(fontSize: 11.5, color: const Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 8),
                          ElevatedButton.icon(
                            onPressed: () => _showTpFormDialog(cpId: cpId, cpKode: kodeCp, cpElemen: elemen),
                            icon: const Icon(Icons.add_rounded, size: 14, color: Colors.white),
                            label: const Text('Tambah TP Pertama', style: TextStyle(fontSize: 11, color: Colors.white)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF10B981),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: tps.length,
                      itemBuilder: (context, tpIdx) {
                        final tp = tps[tpIdx];
                        return _buildTpCard(cpId, kodeCp, elemen, tp);
                      },
                    ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  // --- Tujuan Pembelajaran (TP) Card ---
  Widget _buildTpCard(int cpId, String cpKode, String cpElemen, Map<String, dynamic> tp) {
    final tpId = int.tryParse((tp['id'] ?? 0).toString()) ?? 0;
    final kodeTp = tp['kode_tp']?.toString() ?? 'TP';
    final materiPokok = tp['materi_pokok']?.toString() ?? '';
    final deskripsi = tp['deskripsi']?.toString() ?? '';
    final kktpNilaiMin = (tp['kktp_nilai_min'] ?? 75.0).toString();

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  kodeTp,
                  style: GoogleFonts.robotoMono(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF15803D),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  'KKTP: $kktpNilaiMin',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF475569),
                  ),
                ),
              ),
              const Spacer(),
              // Edit TP
              IconButton(
                icon: const Icon(Icons.edit_outlined, size: 16, color: Color(0xFF64748B)),
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                onPressed: () => _showTpFormDialog(cpId: cpId, cpKode: cpKode, cpElemen: cpElemen, tpData: tp),
              ),
              const SizedBox(width: 12),
              // Delete TP
              IconButton(
                icon: const Icon(Icons.delete_outline_rounded, size: 16, color: Color(0xFFEF4444)),
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                onPressed: () => _confirmDeleteTp(tpId, kodeTp),
              ),
            ],
          ),
          if (materiPokok.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              materiPokok,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 12.5,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF1E293B),
              ),
            ),
          ],
          const SizedBox(height: 4),
          Text(
            deskripsi,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 11.5,
              height: 1.45,
              color: const Color(0xFF64748B),
            ),
          ),
        ],
      ),
    );
  }

  // --- Empty State ---
  Widget _buildEmptyState() {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              color: Color(0xFFEEF2FF),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.library_books_rounded, size: 48, color: Color(0xFF4F46E5)),
          ),
          const SizedBox(height: 16),
          Text(
            'Belum Ada Capaian & Tujuan Pembelajaran',
            textAlign: TextAlign.center,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Silakan mulai menyusun CP dan TP untuk mata pelajaran yang Anda ampu.',
            textAlign: TextAlign.center,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 12,
              color: const Color(0xFF64748B),
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: () => _showCpFormDialog(defaultMapelId: _selectedMapelId > 0 ? _selectedMapelId : null),
            icon: const Icon(Icons.add_rounded, color: Colors.white),
            label: const Text('Susun CP Baru Sekarang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4F46E5),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }
}
