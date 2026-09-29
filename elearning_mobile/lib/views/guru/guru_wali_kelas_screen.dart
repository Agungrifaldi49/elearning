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
  String _presensiSearchQuery = '';
  String _presensiStatusFilter = 'all'; // 'all', 'perlu_tinjauan', 'belum_absen', 'alpa', 'izin_sakit', 'hadir'

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
      final dateStr = '${_presensiDate.year}-${_presensiDate.month.toString().padLeft(2, '0')}-${_presensiDate.day.toString().padLeft(2, '0')}';
      final params = <String, String>{
        'user_id': uid.toString(),
        'tanggal': dateStr,
      };
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

  void _recalculateStats() {
    int hadir = 0;
    int izin = 0;
    int sakit = 0;
    int alpa = 0;
    int belum = 0;
    for (var s in _siswaList) {
      final st = (s['absensi']?['status_hari_ini'] ?? 'Belum Absen').toString().toLowerCase();
      if (st == 'hadir') {
        hadir++;
      } else if (st == 'izin' || st == 'ijin') {
        izin++;
      } else if (st == 'sakit') {
        sakit++;
      } else if (st == 'alpa' || st == 'alpha' || st == 'tanpa keterangan') {
        alpa++;
      } else {
        belum++;
      }
    }
    _stats['hadir_hari_ini'] = hadir;
    _stats['izin_hari_ini'] = izin;
    _stats['sakit_hari_ini'] = sakit;
    _stats['alpa_hari_ini'] = alpa;
    _stats['belum_absen_hari_ini'] = belum;
  }

  Future<void> _updateAbsensi(int siswaId, String status, [String? keterangan]) async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final uid = auth.currentUser?.id ?? 0;
    final dateStr = '${_presensiDate.year}-${_presensiDate.month.toString().padLeft(2, '0')}-${_presensiDate.day.toString().padLeft(2, '0')}';

    // Optimistic UI update
    setState(() {
      for (var s in _siswaList) {
        if (int.tryParse(s['id'].toString()) == siswaId) {
          if (s['absensi'] == null) {
            s['absensi'] = <String, dynamic>{};
          }
          s['absensi']['status_hari_ini'] = status;
          s['absensi']['sudah_absen'] = status.toLowerCase() != 'belum absen';
          if (keterangan != null) {
            s['absensi']['keterangan'] = keterangan;
          }
          if (status == 'Hadir' && (s['absensi']['waktu_masuk'] == null || s['absensi']['waktu_masuk'].toString().isEmpty)) {
            final now = DateTime.now();
            s['absensi']['waktu_masuk'] = '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')} WIB';
          }
          _recalculateStats();
          break;
        }
      }
    });

    final payload = <String, dynamic>{
      'action': 'input_absensi_siswa',
      'user_id': uid,
      'siswa_id': siswaId,
      'status': status,
      'tanggal': dateStr,
    };
    if (keterangan != null) {
      payload['keterangan'] = keterangan;
    }

    final res = await ApiService.post('guru/wali_kelas', payload);

    if (mounted) {
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                const SizedBox(width: 8),
                Expanded(child: Text('Presensi berhasil diperbarui menjadi "$status"${keterangan != null && keterangan.isNotEmpty ? ' ($keterangan)' : ''}')),
              ],
            ),
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

  void _showEditAbsensiDialog(Map<String, dynamic> s) {
    final sId = int.tryParse(s['id'].toString()) ?? 0;
    final name = (s['nama_lengkap'] ?? 'Siswa').toString();
    final nis = (s['nis'] ?? '-').toString();
    final absensi = s['absensi'] as Map? ?? {};
    final currentStatus = (absensi['status_hari_ini'] ?? 'Belum Absen').toString();
    final initialKeterangan = (absensi['keterangan'] ?? '').toString();

    String selectedStatus = ['Hadir', 'Izin', 'Sakit', 'Alpa'].contains(currentStatus) ? currentStatus : 'Izin';
    final ketController = TextEditingController(text: initialKeterangan);

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(color: const Color(0xFF4338CA).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.edit_calendar_rounded, color: Color(0xFF4338CA), size: 22),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text('Koreksi Presensi Siswa', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                        const SizedBox(height: 2),
                        Text('NIS: $nis  •  Status Saat Ini: $currentStatus', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('Pilih Status Sebenarnya:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      _buildDialogStatusOption('Hadir', const Color(0xFF10B981), selectedStatus == 'Hadir', () => setDialogState(() => selectedStatus = 'Hadir')),
                      _buildDialogStatusOption('Izin', const Color(0xFF3B82F6), selectedStatus == 'Izin', () => setDialogState(() => selectedStatus = 'Izin')),
                      _buildDialogStatusOption('Sakit', const Color(0xFFF59E0B), selectedStatus == 'Sakit', () => setDialogState(() => selectedStatus = 'Sakit')),
                      _buildDialogStatusOption('Alpa', const Color(0xFFEF4444), selectedStatus == 'Alpa', () => setDialogState(() => selectedStatus = 'Alpa')),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Text('Keterangan / Alasan:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
                  const SizedBox(height: 6),
                  TextField(
                    controller: ketController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: selectedStatus == 'Izin'
                          ? 'Cth: Surat izin orang tua / Acara keluarga'
                          : selectedStatus == 'Sakit'
                              ? 'Cth: Sakit demam / Surat dokter terlampir'
                              : 'Keterangan tambahan (opsional)',
                      hintStyle: const TextStyle(fontSize: 12, color: Colors.grey),
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '💡 Wali kelas dapat mengoreksi siswa yang semula Alpa menjadi Izin atau Sakit berdasarkan keterangan dari orang tua.',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontStyle: FontStyle.italic, height: 1.3),
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Batal', style: TextStyle(color: Colors.grey)),
              ),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4338CA),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  _updateAbsensi(sId, selectedStatus, ketController.text.trim());
                },
                icon: const Icon(Icons.check_rounded, size: 16),
                label: const Text('Simpan Koreksi'),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildDialogStatusOption(String label, Color color, bool isSelected, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? color : color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: isSelected ? color : color.withValues(alpha: 0.3), width: isSelected ? 1.5 : 1),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isSelected ? Icons.check_circle_rounded : Icons.circle_outlined,
              size: 14,
              color: isSelected ? Colors.white : color,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isSelected ? Colors.white : color),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _openWhatsAppPresensi(String phone, String name, String currentStatus) async {
    if (phone.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Nomor telepon / WhatsApp siswa atau orang tua belum tercatat.'),
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

    final dateFormatted = '${_presensiDate.day.toString().padLeft(2, '0')}/${_presensiDate.month.toString().padLeft(2, '0')}/${_presensiDate.year}';
    final message = Uri.encodeComponent(
      'Halo, Selamat pagi/siang. Kami dari Wali Kelas SMK ingin konfirmasi mengenai presensi ananda $name pada tanggal $dateFormatted. '
      'Pada sistem presensi saat ini tercatat "$currentStatus". '
      'Apakah ananda sedang berhalangan hadir (ada surat izin/sakit)? Mohon konfirmasinya ya. Terima kasih.'
    );
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

  Widget _buildStudentAvatar(Map<String, dynamic> s, {double radius = 20, double fontSize = 13}) {
    final name = (s['nama_lengkap'] ?? 'Siswa').toString();
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'S';
    final rawAvatar = (s['avatar'] ?? '').toString().trim();
    final avatarUrl = ApiService.getFileUrl(rawAvatar);

    final bool hasValidImage = avatarUrl.isNotEmpty &&
        !avatarUrl.endsWith('default_avatar.png') &&
        !avatarUrl.endsWith('default.png');

    if (!hasValidImage) {
      return CircleAvatar(
        radius: radius,
        backgroundColor: const Color(0xFF4338CA).withValues(alpha: 0.12),
        child: Text(
          initial,
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: const Color(0xFF4338CA),
            fontSize: fontSize,
          ),
        ),
      );
    }

    return CircleAvatar(
      radius: radius,
      backgroundColor: const Color(0xFF4338CA).withValues(alpha: 0.12),
      child: ClipOval(
        child: Image.network(
          avatarUrl,
          width: radius * 2,
          height: radius * 2,
          fit: BoxFit.cover,
          errorBuilder: (context, error, stackTrace) {
            return Center(
              child: Text(
                initial,
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF4338CA),
                  fontSize: fontSize,
                ),
              ),
            );
          },
          loadingBuilder: (context, child, loadingProgress) {
            if (loadingProgress == null) return child;
            return Center(
              child: SizedBox(
                width: radius,
                height: radius,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: const Color(0xFF4338CA).withValues(alpha: 0.6),
                ),
              ),
            );
          },
        ),
      ),
    );
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
                      _buildStudentAvatar(s, radius: 28, fontSize: 20),
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
                leading: _buildStudentAvatar(s, radius: 24, fontSize: 14),
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
  List<dynamic> get _filteredPresensiStudents {
    return _siswaList.where((s) {
      final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
      final nis = (s['nis'] ?? '').toString().toLowerCase();
      final query = _presensiSearchQuery.toLowerCase().trim();

      if (query.isNotEmpty && !name.contains(query) && !nis.contains(query)) {
        return false;
      }

      final st = (s['absensi']?['status_hari_ini'] ?? 'Belum Absen').toString().toLowerCase();
      if (_presensiStatusFilter == 'belum_absen') {
        return st == 'belum absen' || st.isEmpty;
      } else if (_presensiStatusFilter == 'alpa') {
        return st == 'alpa' || st == 'alpha' || st == 'tanpa keterangan';
      } else if (_presensiStatusFilter == 'izin_sakit') {
        return st == 'izin' || st == 'ijin' || st == 'sakit';
      } else if (_presensiStatusFilter == 'hadir') {
        return st == 'hadir';
      } else if (_presensiStatusFilter == 'perlu_tinjauan') {
        return st == 'alpa' || st == 'alpha' || st == 'tanpa keterangan' || st == 'belum absen' || st.isEmpty;
      }
      return true;
    }).toList();
  }

  Widget _buildTabPresensi() {
    final now = DateTime.now();
    final isToday = _presensiDate.year == now.year && _presensiDate.month == now.month && _presensiDate.day == now.day;

    final dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    final monthNames = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    final dayStr = dayNames[_presensiDate.weekday - 1];
    final monthStr = monthNames[_presensiDate.month - 1];
    final dateFormatted = '$dayStr, ${_presensiDate.day} $monthStr ${_presensiDate.year}';

    final hadirCount = int.tryParse(_stats['hadir_hari_ini']?.toString() ?? '0') ?? 0;
    final izinCount = int.tryParse(_stats['izin_hari_ini']?.toString() ?? '0') ?? 0;
    final sakitCount = int.tryParse(_stats['sakit_hari_ini']?.toString() ?? '0') ?? 0;
    final alpaCount = int.tryParse(_stats['alpa_hari_ini']?.toString() ?? '0') ?? 0;
    final belumCount = int.tryParse(_stats['belum_absen_hari_ini']?.toString() ?? '0') ?? 0;
    final totalSiswa = _siswaList.length;

    final filteredList = _filteredPresensiStudents;

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      children: [
        // 1. Date Selector Bar
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF4338CA).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.event_available_rounded, color: Color(0xFF4338CA), size: 22),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Tanggal Presensi Rombel', style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.w500)),
                              const SizedBox(height: 2),
                              Text(
                                dateFormatted,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (!isToday)
                        Padding(
                          padding: const EdgeInsets.only(right: 6),
                          child: TextButton(
                            style: TextButton.styleFrom(
                              visualDensity: VisualDensity.compact,
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              foregroundColor: const Color(0xFF4338CA),
                            ),
                            onPressed: () {
                              setState(() => _presensiDate = DateTime.now());
                              _fetchWaliKelasData(_selectedKelasId);
                            },
                            child: const Text('Hari Ini', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                          ),
                        ),
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
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
                            _fetchWaliKelasData(_selectedKelasId);
                          }
                        },
                        icon: const Icon(Icons.calendar_month_rounded, size: 15),
                        label: const Text('Pilih', style: TextStyle(fontSize: 12)),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),

        // 2. Monitoring & Discrepancy Guidance Card
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFEEF2FF),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFC7D2FE)),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.verified_user_rounded, color: Color(0xFF4338CA), size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Pengawasan & Verifikasi Presensi Wali Kelas',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF312E81)),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'Tinjau kehadiran siswa rombel Anda. Jika ada siswa yang seharusnya Izin/Sakit namun tercatat Alpa atau Belum Absen, gunakan tombol koreksi untuk menyesuaikan status dan menambahkan keterangan resmi.',
                      style: TextStyle(fontSize: 11, color: Colors.indigo.shade800, height: 1.35),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // 3. Status Summary Metrics Cards (Grid)
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _buildPresensiCounterCard('Hadir', hadirCount, totalSiswa, const Color(0xFF10B981), Icons.check_circle_rounded, 'hadir'),
              const SizedBox(width: 8),
              _buildPresensiCounterCard('Izin', izinCount, totalSiswa, const Color(0xFF3B82F6), Icons.mail_rounded, 'izin_sakit'),
              const SizedBox(width: 8),
              _buildPresensiCounterCard('Sakit', sakitCount, totalSiswa, const Color(0xFFF59E0B), Icons.medical_services_rounded, 'izin_sakit'),
              const SizedBox(width: 8),
              _buildPresensiCounterCard('Alpa', alpaCount, totalSiswa, const Color(0xFFEF4444), Icons.warning_rounded, 'alpa'),
              const SizedBox(width: 8),
              _buildPresensiCounterCard('Belum Absen', belumCount, totalSiswa, const Color(0xFF64748B), Icons.hourglass_empty_rounded, 'belum_absen'),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // 4. Search and Filter Chips
        Container(
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
          child: TextField(
            decoration: InputDecoration(
              hintText: 'Cari siswa untuk cek/koreksi kehadiran...',
              hintStyle: const TextStyle(fontSize: 12, color: Colors.grey),
              prefixIcon: const Icon(Icons.search, size: 20, color: Colors.grey),
              suffixIcon: _presensiSearchQuery.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, size: 18, color: Colors.grey),
                      onPressed: () => setState(() => _presensiSearchQuery = ''),
                    )
                  : null,
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(vertical: 12),
            ),
            onChanged: (val) => setState(() => _presensiSearchQuery = val),
          ),
        ),
        const SizedBox(height: 10),

        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _buildPresensiFilterChip('Semua ($totalSiswa)', 'all'),
              const SizedBox(width: 8),
              _buildPresensiFilterChip(
                '⚠️ Perlu Ditinjau (${alpaCount + belumCount})',
                'perlu_tinjauan',
                isWarning: (alpaCount + belumCount) > 0,
              ),
              const SizedBox(width: 8),
              _buildPresensiFilterChip('Belum Absen ($belumCount)', 'belum_absen'),
              const SizedBox(width: 8),
              _buildPresensiFilterChip('Alpa ($alpaCount)', 'alpa'),
              const SizedBox(width: 8),
              _buildPresensiFilterChip('Izin/Sakit (${izinCount + sakitCount})', 'izin_sakit'),
              const SizedBox(width: 8),
              _buildPresensiFilterChip('Hadir ($hadirCount)', 'hadir'),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // 5. Students Attendance List
        if (filteredList.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.person_search_rounded, size: 48, color: Colors.grey.shade400),
                const SizedBox(height: 12),
                const Text(
                  'Tidak Ada Siswa yang Sesuai Filter',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E293B)),
                ),
                const SizedBox(height: 4),
                Text(
                  _presensiStatusFilter != 'all'
                      ? 'Coba ganti filter status atau ketuk tombol "Semua" di atas.'
                      : 'Coba ubah kata kunci pencarian nama atau NIS.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                ),
                if (_presensiStatusFilter != 'all') ...[
                  const SizedBox(height: 12),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF4338CA),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () => setState(() => _presensiStatusFilter = 'all'),
                    child: const Text('Tampilkan Semua Siswa', style: TextStyle(fontSize: 12)),
                  ),
                ],
              ],
            ),
          )
        else
          ...filteredList.map((s) {
            final sId = int.tryParse(s['id'].toString()) ?? 0;
            final name = (s['nama_lengkap'] ?? 'Siswa').toString();
            final nis = (s['nis'] ?? '-').toString();
            final phone = (s['no_telepon'] ?? '').toString();
            final absensi = s['absensi'] as Map? ?? {};
            final currentStatus = (absensi['status_hari_ini'] ?? 'Belum Absen').toString();
            final waktuMasuk = (absensi['waktu_masuk'] ?? '').toString();
            final keterangan = (absensi['keterangan'] ?? '').toString().trim();
            final isAlpa = ['alpa', 'alpha', 'tanpa keterangan'].contains(currentStatus.toLowerCase());
            final isBelum = currentStatus.toLowerCase() == 'belum absen' || currentStatus.isEmpty;
            final isHadir = currentStatus.toLowerCase() == 'hadir';
            final isIzin = ['izin', 'ijin'].contains(currentStatus.toLowerCase());
            final isSakit = currentStatus.toLowerCase() == 'sakit';

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isAlpa
                      ? Colors.red.shade300
                      : isBelum
                          ? Colors.orange.shade200
                          : Colors.grey.shade200,
                  width: (isAlpa || isBelum) ? 1.5 : 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: isAlpa
                        ? Colors.red.withValues(alpha: 0.05)
                        : Colors.black.withValues(alpha: 0.03),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Upper Card: Student Info & WA action
                  Padding(
                    padding: const EdgeInsets.only(left: 14, right: 14, top: 12, bottom: 8),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildStudentAvatar(s, radius: 20, fontSize: 13),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                              const SizedBox(height: 2),
                              Text('NIS: $nis', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                            ],
                          ),
                        ),
                        // Direct WhatsApp Follow-up Button
                        IconButton(
                          tooltip: 'Hubungi Siswa / Ortu via WhatsApp',
                          style: IconButton.styleFrom(
                            backgroundColor: const Color(0xFF25D366).withValues(alpha: 0.1),
                            padding: const EdgeInsets.all(8),
                            minimumSize: const Size(36, 36),
                          ),
                          icon: const Icon(Icons.chat_rounded, color: Color(0xFF16A34A), size: 18),
                          onPressed: () => _openWhatsAppPresensi(phone, name, currentStatus),
                        ),
                      ],
                    ),
                  ),

                  // Middle: Visual Status Banner
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: isHadir
                            ? const Color(0xFFF0FDF4)
                            : isIzin
                                ? const Color(0xFFEFF6FF)
                                : isSakit
                                    ? const Color(0xFFFFFBEB)
                                    : isAlpa
                                        ? const Color(0xFFFEF2F2)
                                        : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isHadir
                              ? const Color(0xFFBBF7D0)
                              : isIzin
                                  ? const Color(0xFFBFDBFE)
                                  : isSakit
                                      ? const Color(0xFFFDE68A)
                                      : isAlpa
                                          ? const Color(0xFFFECACA)
                                          : Colors.grey.shade300,
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            isHadir
                                ? Icons.check_circle_rounded
                                : isIzin
                                    ? Icons.mail_rounded
                                    : isSakit
                                        ? Icons.medical_services_rounded
                                        : isAlpa
                                            ? Icons.warning_rounded
                                            : Icons.access_time_rounded,
                            size: 16,
                            color: isHadir
                                ? const Color(0xFF16A34A)
                                : isIzin
                                    ? const Color(0xFF2563EB)
                                    : isSakit
                                        ? const Color(0xFFD97706)
                                        : isAlpa
                                            ? const Color(0xFFDC2626)
                                            : Colors.grey.shade700,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text(
                                      'Status: $currentStatus',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: isHadir
                                            ? const Color(0xFF15803D)
                                            : isIzin
                                                ? const Color(0xFF1D4ED8)
                                                : isSakit
                                                    ? const Color(0xFFB45309)
                                                    : isAlpa
                                                        ? const Color(0xFFB91C1C)
                                                        : const Color(0xFF475569),
                                      ),
                                    ),
                                    if (waktuMasuk.isNotEmpty && isHadir) ...[
                                      const SizedBox(width: 6),
                                      Text(
                                        '($waktuMasuk)',
                                        style: TextStyle(fontSize: 11, color: Colors.green.shade800, fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ],
                                ),
                                if (keterangan.isNotEmpty)
                                  Padding(
                                    padding: const EdgeInsets.only(top: 2),
                                    child: Text(
                                      'Ket: $keterangan',
                                      style: TextStyle(fontSize: 11, color: Colors.grey.shade700, fontStyle: FontStyle.italic),
                                    ),
                                  )
                                else if (isAlpa)
                                  const Padding(
                                    padding: EdgeInsets.only(top: 2),
                                    child: Text(
                                      'Tanpa Keterangan • Perlu Verifikasi Ortu',
                                      style: TextStyle(fontSize: 10, color: Color(0xFFDC2626), fontWeight: FontWeight.w500),
                                    ),
                                  )
                                else if (isBelum)
                                  const Padding(
                                    padding: EdgeInsets.only(top: 2),
                                    child: Text(
                                      'Siswa belum melakukan presensi masuk',
                                      style: TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          // Quick Edit Note Button
                          InkWell(
                            onTap: () => _showEditAbsensiDialog(s),
                            borderRadius: BorderRadius.circular(6),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: Colors.grey.shade300),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.edit_note_rounded, size: 14, color: Color(0xFF4338CA)),
                                  SizedBox(width: 4),
                                  Text('Koreksi', style: TextStyle(fontSize: 11, color: Color(0xFF4338CA), fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Bottom Action: Quick 1-Tap Status Correction
                  Padding(
                    padding: const EdgeInsets.all(12),
                    child: Row(
                      children: [
                        const Text('Set Cepat:', style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.w600)),
                        const Spacer(),
                        _buildQuickStatusChip(sId, 'Hadir', 'H', const Color(0xFF10B981), isHadir),
                        const SizedBox(width: 6),
                        _buildQuickStatusChip(sId, 'Izin', 'I', const Color(0xFF3B82F6), isIzin, promptKeterangan: true, studentData: s),
                        const SizedBox(width: 6),
                        _buildQuickStatusChip(sId, 'Sakit', 'S', const Color(0xFFF59E0B), isSakit, promptKeterangan: true, studentData: s),
                        const SizedBox(width: 6),
                        _buildQuickStatusChip(sId, 'Alpa', 'A', const Color(0xFFEF4444), isAlpa),
                      ],
                    ),
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  Widget _buildPresensiCounterCard(String label, int count, int total, Color color, IconData icon, String filterTag) {
    final isSelected = _presensiStatusFilter == filterTag;
    final pct = total > 0 ? ((count / total) * 100).round() : 0;

    return InkWell(
      onTap: () {
        setState(() {
          _presensiStatusFilter = (_presensiStatusFilter == filterTag) ? 'all' : filterTag;
        });
      },
      borderRadius: BorderRadius.circular(14),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 105,
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: isSelected ? color.withValues(alpha: 0.15) : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? color : color.withValues(alpha: 0.25),
            width: isSelected ? 2 : 1,
          ),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 4, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Icon(icon, size: 16, color: color),
                Text('$pct%', style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              '$count',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPresensiFilterChip(String label, String tag, {bool isWarning = false}) {
    final isSelected = _presensiStatusFilter == tag;
    return ChoiceChip(
      label: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? Colors.white : (isWarning ? const Color(0xFFDC2626) : const Color(0xFF334155)),
        ),
      ),
      selected: isSelected,
      selectedColor: isWarning ? const Color(0xFFDC2626) : const Color(0xFF4338CA),
      backgroundColor: isWarning ? const Color(0xFFFEF2F2) : Colors.white,
      showCheckmark: false,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: BorderSide(
          color: isSelected
              ? (isWarning ? const Color(0xFFDC2626) : const Color(0xFF4338CA))
              : (isWarning ? const Color(0xFFFECACA) : Colors.grey.shade300),
        ),
      ),
      onSelected: (_) => setState(() => _presensiStatusFilter = tag),
    );
  }

  Widget _buildQuickStatusChip(int sId, String statusVal, String label, Color color, bool isCurrent, {bool promptKeterangan = false, Map<String, dynamic>? studentData}) {
    return InkWell(
      onTap: () {
        if (promptKeterangan && studentData != null) {
          // If teacher wants to set Izin or Sakit, open the dialog so they can easily enter the explanation
          _showEditAbsensiDialog(studentData);
        } else {
          _updateAbsensi(sId, statusVal);
        }
      },
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        width: 38,
        height: 32,
        decoration: BoxDecoration(
          color: isCurrent ? color : color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isCurrent ? color : color.withValues(alpha: 0.4),
            width: isCurrent ? 1.5 : 1,
          ),
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
                  const SizedBox(width: 8),
                  _buildStudentAvatar(s, radius: 18, fontSize: 12),
                  const SizedBox(width: 10),
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
