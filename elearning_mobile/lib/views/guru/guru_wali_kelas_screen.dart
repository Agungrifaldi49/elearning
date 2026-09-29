import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GuruWaliKelasScreen extends StatefulWidget {
  const GuruWaliKelasScreen({super.key});

  @override
  State<GuruWaliKelasScreen> createState() => _GuruWaliKelasScreenState();
}

class _GuruWaliKelasScreenState extends State<GuruWaliKelasScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  bool _isWaliKelas = false;
  Map<String, dynamic>? _kelasBinaan;
  List<dynamic> _waliKelasList = [];
  List<dynamic> _siswaList = [];
  Map<String, dynamic> _stats = {
    'total_siswa': 0,
    'avg_nilai': 0,
    'kehadiran_persen': 0,
  };

  int? _selectedKelasId;
  String _searchQuery = '';
  String _filterTag = 'all'; // 'all', 'alpa', 'prestasi'
  DateTime _presensiDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchWaliKelasData();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchWaliKelasData([int? kelasId]) async {
    setState(() => _isLoading = true);
    try {
      final auth = Provider.of<AuthProvider>(context, listen: false);
      final uid = auth.currentUser?.id ?? 0;
      final params = <String, String>{'user_id': uid.toString()};
      if (kelasId != null && kelasId > 0) {
        params['kelas_id'] = kelasId.toString();
      }

      final res = await ApiService.get('guru/wali_kelas', params: params);
      if (mounted) {
        if (res['success'] == true && res['data'] != null) {
          final data = res['data'];
          setState(() {
            _isWaliKelas = data['is_wali_kelas'] == true;
            _kelasBinaan = data['kelas_binaan'];
            _waliKelasList = data['wali_kelas_list'] ?? [];
            _siswaList = data['siswa_list'] ?? [];
            _stats = Map<String, dynamic>.from(data['stats'] ?? {});
            if (_kelasBinaan != null) {
              _selectedKelasId = int.tryParse(_kelasBinaan!['id'].toString());
            }
            _isLoading = false;
          });
        } else {
          setState(() {
            _isWaliKelas = false;
            _isLoading = false;
          });
        }
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _isWaliKelas = false;
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _updateAbsensi(int siswaId, String status) async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final uid = auth.currentUser?.id ?? 0;
    final dateStr = '${_presensiDate.year}-${_presensiDate.month.toString().padLeft(2, '0')}-${_presensiDate.day.toString().padLeft(2, '0')}';

    // Optimistic UI update
    setState(() {
      for (var s in _siswaList) {
        if (int.tryParse(s['id'].toString()) == siswaId) {
          s['absensi']['status_hari_ini'] = status;
          break;
        }
      }
    });

    final res = await ApiService.post('guru/wali_kelas', {
      'action': 'input_absensi_siswa',
      'user_id': uid,
      'siswa_id': siswaId,
      'status': status,
      'tanggal': dateStr,
    });

    if (mounted) {
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Presensi berhasil diperbarui menjadi "$status"'),
            backgroundColor: const Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 2),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal memperbarui presensi'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
        _fetchWaliKelasData(_selectedKelasId);
      }
    }
  }

  Future<void> _saveCatatanWali(int siswaId, String catatan) async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final uid = auth.currentUser?.id ?? 0;

    final res = await ApiService.post('guru/wali_kelas', {
      'action': 'save_catatan_wali',
      'user_id': uid,
      'siswa_id': siswaId,
      'catatan': catatan,
    });

    if (mounted) {
      if (res['success'] == true) {
        // Update local state
        setState(() {
          for (var s in _siswaList) {
            if (int.tryParse(s['id'].toString()) == siswaId) {
              s['catatan_wali'] = catatan;
              break;
            }
          }
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Catatan wali kelas rapor berhasil disimpan!'),
            backgroundColor: Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal menyimpan catatan'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _openWhatsApp(String phone, String name) async {
    if (phone.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Nomor telepon/WhatsApp siswa tidak tersedia.'),
          backgroundColor: Colors.orange,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    var clean = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (clean.startsWith('0')) {
      clean = '62${clean.substring(1)}';
    } else if (!clean.startsWith('62')) {
      clean = '62$clean';
    }

    final message = Uri.encodeComponent('Halo, saya Wali Kelas SMK Muthia Harapan Cicalengka, ingin mengonfirmasi perkembangan ananda $name.');
    final uri = Uri.parse('https://wa.me/$clean?text=$message');

    try {
      final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!ok) {
        await launchUrl(uri, mode: LaunchMode.platformDefault);
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tidak dapat membuka aplikasi WhatsApp.'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _showStudentDetailModal(Map<String, dynamic> s) {
    final sId = int.tryParse(s['id'].toString()) ?? 0;
    final name = (s['nama_lengkap'] ?? 'Siswa').toString();
    final nis = (s['nis'] ?? '-').toString();
    final nisn = (s['nisn'] ?? '-').toString();
    final phone = (s['no_telepon'] ?? '').toString();
    final gender = (s['jenis_kelamin'] ?? '-').toString() == 'P' ? 'Perempuan' : 'Laki-Laki';
    final address = (s['alamat'] ?? '-').toString();

    final absensi = s['absensi'] as Map? ?? {};
    final hadir = absensi['hadir'] ?? 0;
    final izin = absensi['izin'] ?? 0;
    final sakit = absensi['sakit'] ?? 0;
    final alpa = absensi['alpa'] ?? 0;
    final persenKehadiran = absensi['persentase'] ?? 100;

    final nilai = s['nilai'] as Map? ?? {};
    final avgNilai = nilai['avg'] ?? 0;
    final predikat = (nilai['predikat'] ?? '-').toString();
    final mapels = (nilai['mapels'] as List?) ?? [];

    final catatanController = TextEditingController(text: s['catatan_wali']?.toString() ?? '');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.85,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          expand: false,
          builder: (_, scrollController) {
            return SingleChildScrollView(
              controller: scrollController,
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

                  // Header Siswa Info
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 28,
                        backgroundColor: const Color(0xFF4338CA).withValues(alpha: 0.1),
                        backgroundImage: s['avatar'] != null && s['avatar'].toString().startsWith('http')
                            ? NetworkImage(s['avatar'])
                            : null,
                        child: s['avatar'] == null || !s['avatar'].toString().startsWith('http')
                            ? Text(name.isNotEmpty ? name[0] : 'S', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20, color: Color(0xFF4338CA)))
                            : null,
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(name, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                            const SizedBox(height: 2),
                            Text('NIS: $nis  •  NISN: $nisn', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                            const SizedBox(height: 2),
                            Text('$gender  •  $address', style: TextStyle(fontSize: 11, color: Colors.grey.shade500), maxLines: 1, overflow: TextOverflow.ellipsis),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // WhatsApp Action Button
                  if (phone.isNotEmpty)
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF25D366),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          elevation: 0,
                        ),
                        onPressed: () => _openWhatsApp(phone, name),
                        icon: const Icon(Icons.chat_rounded, size: 18),
                        label: Text('Hubungi Siswa / Ortu via WhatsApp ($phone)', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      ),
                    ),
                  const SizedBox(height: 20),

                  // Section 1: Ringkasan Kehadiran Rombel
                  const Text('Rekap Presensi Siswa', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Persentase Kehadiran', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                            Text('$persenKehadiran%', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: persenKehadiran >= 80 ? Colors.green.shade700 : Colors.red.shade700)),
                          ],
                        ),
                        const SizedBox(height: 10),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: LinearProgressIndicator(
                            value: (persenKehadiran as num) / 100,
                            minHeight: 8,
                            backgroundColor: Colors.grey.shade200,
                            valueColor: AlwaysStoppedAnimation<Color>(persenKehadiran >= 80 ? const Color(0xFF10B981) : const Color(0xFFEF4444)),
                          ),
                        ),
                        const SizedBox(height: 14),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildStatBadge('Hadir', hadir.toString(), const Color(0xFF10B981)),
                            _buildStatBadge('Izin', izin.toString(), const Color(0xFF3B82F6)),
                            _buildStatBadge('Sakit', sakit.toString(), const Color(0xFFF59E0B)),
                            _buildStatBadge('Alpa', alpa.toString(), const Color(0xFFEF4444)),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Section 2: Mata Pelajaran yang Diikuti
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Mata Pelajaran yang Diikuti (${mapels.length})', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(color: Colors.indigo.shade50, borderRadius: BorderRadius.circular(12)),
                        child: Text('Rata-rata: $avgNilai ($predikat)', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.indigo.shade700)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),

                  if (mapels.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12)),
                      child: const Center(
                        child: Text(
                          'Siswa belum terdaftar pada mata pelajaran (Belum mendaftar mapel via Kode Key Mapel).',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey, fontSize: 12),
                        ),
                      ),
                    )
                  else
                    ...mapels.map((m) {
                      final nAkhir = (m['nilai_akhir'] ?? 0).toDouble();
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(m['nama_mapel'] ?? 'Mapel', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                                  const SizedBox(height: 2),
                                  Text('Guru: ${m['nama_guru'] ?? 'Guru Pengampu'}', style: TextStyle(fontSize: 11, color: Colors.indigo.shade700, fontWeight: FontWeight.w500)),
                                  const SizedBox(height: 3),
                                  Text('Tugas: ${m['nilai_tugas']} • Quiz: ${m['nilai_quiz']} • UTS: ${m['nilai_uts']} • UAS: ${m['nilai_uas']}', style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: nAkhir >= 75 ? Colors.green.shade50 : Colors.red.shade50,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Column(
                                children: [
                                  Text(
                                    nAkhir.toStringAsFixed(1),
                                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: nAkhir >= 75 ? Colors.green.shade700 : Colors.red.shade700),
                                  ),
                                  Text(
                                    'Nilai Akhir',
                                    style: TextStyle(fontSize: 9, color: nAkhir >= 75 ? Colors.green.shade700 : Colors.red.shade700),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                  const SizedBox(height: 20),

                  // Section 3: Catatan Wali Kelas (Rapor)
                  const Text('Catatan Wali Kelas untuk Rapor', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                  const SizedBox(height: 4),
                  Text('Catatan ini akan tercetak pada buku laporan penilaian hasil belajar (E-Rapor).', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: catatanController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'Tuliskan apresiasi, motivasi, atau evaluasi untuk siswa ini...',
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4338CA),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      onPressed: () {
                        Navigator.pop(ctx);
                        _saveCatatanWali(sId, catatanController.text.trim());
                      },
                      icon: const Icon(Icons.save_rounded, size: 18),
                      label: const Text('Simpan Catatan Rapor', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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

  Widget _buildStatBadge(String label, String value, Color color) {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(value, style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: color)),
        ),
        const SizedBox(height: 4),
        Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
      ],
    );
  }

  List<dynamic> get _filteredStudents {
    return _siswaList.where((s) {
      final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
      final nis = (s['nis'] ?? '').toString().toLowerCase();
      final query = _searchQuery.toLowerCase().trim();

      final matchesQuery = query.isEmpty || name.contains(query) || nis.contains(query);
      if (!matchesQuery) return false;

      if (_filterTag == 'alpa') {
        final alpa = (s['absensi']?['alpa'] ?? 0) as int;
        return alpa > 0;
      } else if (_filterTag == 'prestasi') {
        final avg = (s['nilai']?['avg'] ?? 0) as num;
        return avg >= 80;
      }
      return true;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Portal Wali Kelas', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 18)),
            Text('Kontrol Penuh Absensi & Perkembangan Siswa', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF4338CA)),
            tooltip: 'Segarkan Data',
            onPressed: () => _fetchWaliKelasData(_selectedKelasId),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : !_isWaliKelas
              ? _buildNotWaliState()
              : Column(
                  children: [
                    // Header Overview Card
                    _buildWaliHeaderCard(),

                    // Tab Navigation
                    Container(
                      color: Colors.white,
                      child: TabBar(
                        controller: _tabController,
                        labelColor: const Color(0xFF4338CA),
                        unselectedLabelColor: const Color(0xFF64748B),
                        indicatorColor: const Color(0xFF4338CA),
                        indicatorWeight: 3,
                        labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        tabs: const [
                          Tab(icon: Icon(Icons.people_alt_rounded, size: 20), text: 'Data Siswa'),
                          Tab(icon: Icon(Icons.fact_check_rounded, size: 20), text: 'Presensi Rombel'),
                          Tab(icon: Icon(Icons.grade_rounded, size: 20), text: 'Nilai & Rapor'),
                        ],
                      ),
                    ),

                    // Tab Views
                    Expanded(
                      child: TabBarView(
                        controller: _tabController,
                        children: [
                          _buildTabSiswa(),
                          _buildTabPresensi(),
                          _buildTabNilai(),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildNotWaliState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(color: Colors.amber.shade50, shape: BoxShape.circle),
              child: Icon(Icons.workspace_premium_rounded, size: 64, color: Colors.amber.shade700),
            ),
            const SizedBox(height: 20),
            const Text(
              'Anda Belum Terdaftar Sebagai Wali Kelas',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Fitur ini khusus untuk Tenaga Pendidik yang diberi amanah sebagai Wali Kelas binaan. Hubungi Administrator untuk penugasan wali kelas.',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.4),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildWaliHeaderCard() {
    final namaKelas = _kelasBinaan?['nama_kelas'] ?? 'Kelas Binaan';
    final namaJurusan = _kelasBinaan?['nama_jurusan'] ?? 'Jurusan SMK';
    final totalSiswa = _stats['total_siswa'] ?? _siswaList.length;
    final avgNilai = _stats['avg_nilai'] ?? 0;
    final kehadiran = _stats['kehadiran_persen'] ?? 100;

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: AppTheme.guruGradient,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF4338CA).withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 5),
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
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.workspace_premium_rounded, color: Colors.amber, size: 14),
                    SizedBox(width: 6),
                    Text(
                      'WALI KELAS RESMI',
                      style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                    ),
                  ],
                ),
              ),
              if (_waliKelasList.length > 1)
                DropdownButton<int>(
                  value: _selectedKelasId,
                  dropdownColor: const Color(0xFF312E81),
                  underline: const SizedBox(),
                  icon: const Icon(Icons.arrow_drop_down, color: Colors.white),
                  items: _waliKelasList.map<DropdownMenuItem<int>>((k) {
                    return DropdownMenuItem<int>(
                      value: int.tryParse(k['id'].toString()),
                      child: Text(k['nama_kelas'] ?? '', style: const TextStyle(color: Colors.white, fontSize: 12)),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      _fetchWaliKelasData(val);
                    }
                  },
                ),
            ],
          ),
          const SizedBox(height: 12),
          Text(namaKelas, style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
          Text(namaJurusan, style: const TextStyle(color: Colors.white70, fontSize: 13)),
          const SizedBox(height: 16),

          // 3 Statistics Cards
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildHeaderStat('Siswa', '$totalSiswa Anak', Icons.people_rounded),
                Container(height: 24, width: 1, color: Colors.white24),
                _buildHeaderStat('Rata Nilai', avgNilai.toString(), Icons.analytics_rounded),
                Container(height: 24, width: 1, color: Colors.white24),
                _buildHeaderStat('Kehadiran', '$kehadiran%', Icons.fact_check_rounded),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderStat(String title, String val, IconData icon) {
    return Column(
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: Colors.white70, size: 13),
            const SizedBox(width: 4),
            Text(title, style: const TextStyle(color: Colors.white70, fontSize: 11)),
          ],
        ),
        const SizedBox(height: 2),
        Text(val, style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold)),
      ],
    );
  }

  // ================= TAB 1: DATA SISWA =================
  Widget _buildTabSiswa() {
    final filtered = _filteredStudents;

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      children: [
        // Search & Filter Bar
        Row(
          children: [
            Expanded(
              child: Container(
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
                child: TextField(
                  decoration: const InputDecoration(
                    hintText: 'Cari nama siswa / NIS...',
                    prefixIcon: Icon(Icons.search, size: 20, color: Colors.grey),
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(vertical: 12),
                  ),
                  onChanged: (val) => setState(() => _searchQuery = val),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),

        // Filter chips
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _buildFilterChip('Semua (${_siswaList.length})', 'all'),
              const SizedBox(width: 8),
              _buildFilterChip('Ada Alpa / Perlu Dipantau', 'alpa'),
              const SizedBox(width: 8),
              _buildFilterChip('Nilai Unggul (≥80)', 'prestasi'),
            ],
          ),
        ),
        const SizedBox(height: 14),

        if (filtered.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
            child: const Center(child: Text('Tidak ada siswa yang sesuai filter.', style: TextStyle(color: Colors.grey, fontSize: 13))),
          )
        else
          ...filtered.map((s) {
            final name = (s['nama_lengkap'] ?? 'Siswa').toString();
            final nis = (s['nis'] ?? '-').toString();
            final absensi = s['absensi'] as Map? ?? {};
            final alpa = absensi['alpa'] ?? 0;
            final persenHadir = absensi['persentase'] ?? 100;
            final nilai = s['nilai'] as Map? ?? {};
            final avgNilai = nilai['avg'] ?? 0;
            final statusToday = absensi['status_hari_ini'] ?? 'Belum Absen';

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: alpa > 0 ? Colors.red.shade200 : Colors.grey.shade200),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 3)),
                ],
              ),
              child: ListTile(
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                leading: CircleAvatar(
                  radius: 24,
                  backgroundColor: const Color(0xFF4338CA).withValues(alpha: 0.1),
                  backgroundImage: s['avatar'] != null && s['avatar'].toString().startsWith('http')
                      ? NetworkImage(s['avatar'])
                      : null,
                  child: s['avatar'] == null || !s['avatar'].toString().startsWith('http')
                      ? Text(name.isNotEmpty ? name[0] : 'S', style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF4338CA)))
                      : null,
                ),
                title: Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 2),
                    Text('NIS: $nis  •  Hari Ini: $statusToday', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: persenHadir >= 80 ? Colors.green.shade50 : Colors.red.shade50,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'Kehadiran $persenHadir%',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: persenHadir >= 80 ? Colors.green.shade700 : Colors.red.shade700),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'Nilai: $avgNilai',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.blue.shade700),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.purple.shade50,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '${nilai['total_mapel'] ?? 0} Mapel',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.purple.shade700),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                trailing: const Icon(Icons.chevron_right_rounded, color: Colors.grey),
                onTap: () => _showStudentDetailModal(s),
              ),
            );
          }),
      ],
    );
  }

  Widget _buildFilterChip(String label, String tag) {
    final isSelected = _filterTag == tag;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 12, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal, color: isSelected ? Colors.white : const Color(0xFF334155))),
      selected: isSelected,
      selectedColor: const Color(0xFF4338CA),
      backgroundColor: Colors.white,
      showCheckmark: false,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20), side: BorderSide(color: isSelected ? const Color(0xFF4338CA) : Colors.grey.shade300)),
      onSelected: (_) => setState(() => _filterTag = tag),
    );
  }

  // ================= TAB 2: KONTROL PRESENSI =================
  Widget _buildTabPresensi() {
    final dateFormatted = '${_presensiDate.day.toString().padLeft(2, '0')}/${_presensiDate.month.toString().padLeft(2, '0')}/${_presensiDate.year}';

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      children: [
        // Date Selector Bar
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200)),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.event_available_rounded, color: Color(0xFF4338CA), size: 22),
                  const SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Tanggal Presensi Rombel', style: TextStyle(fontSize: 11, color: Colors.grey)),
                      Text(dateFormatted, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E293B))),
                    ],
                  ),
                ],
              ),
              OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () async {
                  final picked = await showDatePicker(
                    context: context,
                    initialDate: _presensiDate,
                    firstDate: DateTime(2025),
                    lastDate: DateTime.now().add(const Duration(days: 7)),
                  );
                  if (picked != null) {
                    setState(() => _presensiDate = picked);
                  }
                },
                icon: const Icon(Icons.calendar_month_rounded, size: 16),
                label: const Text('Ubah Tanggal', style: TextStyle(fontSize: 12)),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        const Text('Kontrol Cepat Status Absensi Siswa:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E293B))),
        const SizedBox(height: 10),

        ..._siswaList.map((s) {
          final sId = int.tryParse(s['id'].toString()) ?? 0;
          final name = (s['nama_lengkap'] ?? 'Siswa').toString();
          final currentStatus = (s['absensi']?['status_hari_ini'] ?? 'Belum Absen').toString();

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                      const SizedBox(height: 2),
                      Text('Status: $currentStatus', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                    ],
                  ),
                ),
                // 4 Status Buttons: H, I, S, A
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildAbsensiButton(sId, 'Hadir', 'H', Colors.green, currentStatus == 'Hadir'),
                    const SizedBox(width: 4),
                    _buildAbsensiButton(sId, 'Izin', 'I', Colors.blue, currentStatus == 'Izin'),
                    const SizedBox(width: 4),
                    _buildAbsensiButton(sId, 'Sakit', 'S', Colors.amber.shade700, currentStatus == 'Sakit'),
                    const SizedBox(width: 4),
                    _buildAbsensiButton(sId, 'Alpa', 'A', Colors.red, currentStatus == 'Alpa'),
                  ],
                ),
              ],
            ),
          );
        }),
      ],
    );
  }

  Widget _buildAbsensiButton(int sId, String statusVal, String label, Color color, bool isCurrent) {
    return InkWell(
      onTap: () => _updateAbsensi(sId, statusVal),
      borderRadius: BorderRadius.circular(8),
      child: Container(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          color: isCurrent ? color : color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: isCurrent ? color : color.withValues(alpha: 0.4)),
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: isCurrent ? Colors.white : color,
            ),
          ),
        ),
      ),
    );
  }

  // ================= TAB 3: NILAI & RAPOR =================
  Widget _buildTabNilai() {
    // Sort students by average score descending
    final sortedList = List<dynamic>.from(_siswaList);
    sortedList.sort((a, b) {
      final aAvg = (a['nilai']?['avg'] ?? 0) as num;
      final bAvg = (b['nilai']?['avg'] ?? 0) as num;
      return bAvg.compareTo(aAvg);
    });

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      children: [
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.indigo.shade50,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.indigo.shade100),
          ),
          child: Row(
            children: [
              const Icon(Icons.info_outline_rounded, color: Color(0xFF4338CA), size: 22),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Daftar ranking akademik rombel. Ketuk nama siswa untuk meninjau detail nilai per mapel dan menyusun catatan rapor.',
                  style: TextStyle(fontSize: 12, color: Colors.indigo.shade900, height: 1.3),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        ...sortedList.asMap().entries.map((entry) {
          final index = entry.key;
          final s = entry.value;
          final name = (s['nama_lengkap'] ?? 'Siswa').toString();
          final nis = (s['nis'] ?? '-').toString();
          final nilai = s['nilai'] as Map? ?? {};
          final avg = nilai['avg'] ?? 0;
          final predikat = (nilai['predikat'] ?? '-').toString();
          final catatan = (s['catatan_wali'] ?? '').toString().trim();
          final hasCatatan = catatan.isNotEmpty;

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: InkWell(
              onTap: () => _showStudentDetailModal(s),
              child: Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: index < 3 ? Colors.amber.shade100 : Colors.grey.shade100,
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        '#${index + 1}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: index < 3 ? Colors.amber.shade900 : Colors.grey.shade700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                        const SizedBox(height: 2),
                        Text('NIS: $nis  •  ${nilai['total_mapel'] ?? 0} Mapel Diikuti', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(hasCatatan ? Icons.check_circle : Icons.edit_note_rounded, size: 14, color: hasCatatan ? Colors.green : Colors.orange),
                            const SizedBox(width: 4),
                            Text(hasCatatan ? 'Catatan Rapor Terisi' : 'Catatan Belum Diisi', style: TextStyle(fontSize: 10, color: hasCatatan ? Colors.green.shade700 : Colors.orange.shade700, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(color: const Color(0xFF4338CA), borderRadius: BorderRadius.circular(8)),
                        child: Text(avg.toString(), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                      ),
                      const SizedBox(height: 2),
                      Text('Grade $predikat', style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }
}
