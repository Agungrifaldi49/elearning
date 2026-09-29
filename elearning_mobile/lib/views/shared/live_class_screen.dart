import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class LiveClassScreen extends StatefulWidget {
  const LiveClassScreen({super.key});

  @override
  State<LiveClassScreen> createState() => _LiveClassScreenState();
}

class _LiveClassScreenState extends State<LiveClassScreen> {
  bool _isLoading = true;
  List<dynamic> _meetings = [];
  List<Map<String, dynamic>> _mapelOptions = [];
  List<Map<String, dynamic>> _kelasOptions = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchLiveClasses();
    });
  }

  Future<void> _fetchLiveClasses() async {
    setState(() => _isLoading = true);
    try {
      final auth = Provider.of<AuthProvider>(context, listen: false);
      final user = auth.currentUser;
      final roleStr = user?.isGuru == true ? 'guru' : (user?.isSiswa == true ? 'siswa' : '');
      final uid = user?.id ?? 0;
      final endpoint = 'live_class?user_id=$uid&role=$roleStr';

      final res = await ApiService.get(endpoint);
      if (mounted) {
        if (res['success'] == true && res['data'] is List) {
          setState(() {
            _meetings = res['data'];
            if (res['meta'] != null) {
              final metaMapel = res['meta']['mapel_options'];
              if (metaMapel is List) {
                _mapelOptions = List<Map<String, dynamic>>.from(metaMapel);
              }
              final metaKelas = res['meta']['kelas_options'];
              if (metaKelas is List) {
                _kelasOptions = List<Map<String, dynamic>>.from(metaKelas);
              }
            }
            _isLoading = false;
          });
        } else {
          setState(() {
            _meetings = [];
            _isLoading = false;
          });
        }
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _meetings = [];
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _launchMeeting(String? urlStr) async {
    if (urlStr == null || urlStr.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Tautan meeting tidak tersedia atau belum ditentukan.'),
          backgroundColor: Colors.red,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final trimmed = urlStr.trim();
    final uri = Uri.tryParse(trimmed);
    if (uri != null) {
      try {
        final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
        if (!ok) {
          await launchUrl(uri, mode: LaunchMode.platformDefault);
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Tidak dapat membuka tautan: $trimmed'),
              backgroundColor: Colors.red,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Format tautan tidak valid: $trimmed'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _copyMeetingLink(String? url) {
    if (url == null || url.trim().isEmpty) return;
    Clipboard.setData(ClipboardData(text: url.trim()));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Row(
          children: [
            Icon(Icons.check_circle, color: Colors.white, size: 18),
            SizedBox(width: 8),
            Expanded(child: Text('Tautan meeting berhasil disalin ke clipboard!')),
          ],
        ),
        backgroundColor: const Color(0xFF10B981),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  Future<void> _deleteMeeting(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Sesi Meeting?'),
        content: const Text('Apakah Anda yakin ingin menghapus sesi tatap muka digital ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final res = await ApiService.post('live_class', {
        'action': 'delete',
        'id': id,
      });

      if (mounted) {
        if (res['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Sesi live meeting berhasil dihapus.'),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
            ),
          );
          _fetchLiveClasses();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(res['message'] ?? 'Gagal menghapus sesi meeting.'),
              backgroundColor: Colors.red,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    }
  }

  void _showCreateMeetingBottomSheet() {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final user = auth.currentUser;

    final topikController = TextEditingController();
    final deskripsiController = TextEditingController();
    final linkController = TextEditingController();

    String selectedPlatform = 'meet';
    int? selectedMapelId = _mapelOptions.isNotEmpty ? int.tryParse(_mapelOptions.first['id'].toString()) : null;
    int? selectedKelasId; // null = semua kelas
    DateTime selectedDate = DateTime.now();
    TimeOfDay selectedJamMulai = TimeOfDay.now();
    TimeOfDay selectedJamSelesai = TimeOfDay(
      hour: (TimeOfDay.now().hour + 1) % 24,
      minute: TimeOfDay.now().minute,
    );

    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.video_call_rounded, color: Color(0xFF4338CA), size: 28),
                            SizedBox(width: 8),
                            Text(
                              'Buat Sesi Live Meeting',
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                            ),
                          ],
                        ),
                        IconButton(
                          onPressed: () => Navigator.pop(ctx),
                          icon: const Icon(Icons.close_rounded, color: Colors.grey),
                        ),
                      ],
                    ),
                    const Divider(height: 24),

                    // Topik Meeting
                    const Text('Topik Pertemuan *', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: topikController,
                      decoration: InputDecoration(
                        hintText: 'Contoh: Tatap Muka & Diskusi Bab 3',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Mata Pelajaran
                    if (_mapelOptions.isNotEmpty) ...[
                      const Text('Mata Pelajaran', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<int>(
                        initialValue: selectedMapelId,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        ),
                        items: _mapelOptions.map((m) {
                          return DropdownMenuItem<int>(
                            value: int.tryParse(m['id'].toString()),
                            child: Text(
                              m['nama_mapel'] ?? 'Mapel',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) => setModalState(() => selectedMapelId = val),
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Kelas Target
                    const Text('Target Peserta Kelas', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int?>(
                      initialValue: selectedKelasId,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: [
                        const DropdownMenuItem<int?>(
                          value: null,
                          child: Text('Semua Kelas (Umum)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF4338CA))),
                        ),
                        ..._kelasOptions.map((k) {
                          return DropdownMenuItem<int?>(
                            value: int.tryParse(k['id'].toString()),
                            child: Text(k['nama_kelas'] ?? 'Kelas', style: const TextStyle(fontSize: 13)),
                          );
                        }),
                      ],
                      onChanged: (val) => setModalState(() => selectedKelasId = val),
                    ),
                    const SizedBox(height: 14),

                    // Platform
                    const Text('Platform Meeting', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: selectedPlatform,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'meet', child: Text('Google Meet (Disarankan)')),
                        DropdownMenuItem(value: 'zoom', child: Text('Zoom Meeting')),
                        DropdownMenuItem(value: 'embedded', child: Text('Virtual Room LMS (WebRTC Bawaan)')),
                      ],
                      onChanged: (val) => setModalState(() => selectedPlatform = val ?? 'meet'),
                    ),
                    const SizedBox(height: 14),

                    // Tautan Meeting (Google Meet / Zoom URL)
                    if (selectedPlatform != 'embedded') ...[
                      const Text('Tautan Ruang Meeting *', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: linkController,
                        decoration: InputDecoration(
                          hintText: selectedPlatform == 'meet' ? 'https://meet.google.com/abc-defg-hij' : 'https://zoom.us/j/...',
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        ),
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Tanggal & Jam
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Tanggal', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                              const SizedBox(height: 6),
                              OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: () async {
                                  final picked = await showDatePicker(
                                    context: context,
                                    initialDate: selectedDate,
                                    firstDate: DateTime.now().subtract(const Duration(days: 1)),
                                    lastDate: DateTime.now().add(const Duration(days: 60)),
                                  );
                                  if (picked != null) {
                                    setModalState(() => selectedDate = picked);
                                  }
                                },
                                icon: const Icon(Icons.calendar_today_rounded, size: 16),
                                label: Text(
                                  '${selectedDate.year}-${selectedDate.month.toString().padLeft(2, '0')}-${selectedDate.day.toString().padLeft(2, '0')}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Jam Mulai', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                              const SizedBox(height: 6),
                              OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: () async {
                                  final picked = await showTimePicker(
                                    context: context,
                                    initialTime: selectedJamMulai,
                                  );
                                  if (picked != null) {
                                    setModalState(() => selectedJamMulai = picked);
                                  }
                                },
                                icon: const Icon(Icons.access_time_rounded, size: 16),
                                label: Text(
                                  '${selectedJamMulai.hour.toString().padLeft(2, '0')}:${selectedJamMulai.minute.toString().padLeft(2, '0')}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Deskripsi Pertemuan
                    const Text('Deskripsi / Petunjuk (Opsional)', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: deskripsiController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        hintText: 'Petunjuk tambahan untuk peserta meeting...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4338CA),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          elevation: 0,
                        ),
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                final topik = topikController.text.trim();
                                if (topik.isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Topik pertemuan wajib diisi')),
                                  );
                                  return;
                                }

                                final meetingLink = linkController.text.trim();
                                if (selectedPlatform != 'embedded' && meetingLink.isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Tautan meeting wajib diisi untuk Google Meet / Zoom')),
                                  );
                                  return;
                                }

                                setModalState(() => isSubmitting = true);

                                final tglFormatted = '${selectedDate.year}-${selectedDate.month.toString().padLeft(2, '0')}-${selectedDate.day.toString().padLeft(2, '0')}';
                                final jamMulaiFormatted = '${selectedJamMulai.hour.toString().padLeft(2, '0')}:${selectedJamMulai.minute.toString().padLeft(2, '0')}';
                                final jamSelesaiFormatted = '${selectedJamSelesai.hour.toString().padLeft(2, '0')}:${selectedJamSelesai.minute.toString().padLeft(2, '0')}';

                                final nav = Navigator.of(context);
                                final messenger = ScaffoldMessenger.of(context);

                                final res = await ApiService.post('live_class', {
                                  'action': 'create',
                                  'user_id': user?.id ?? 0,
                                  'mapel_id': selectedMapelId ?? 1,
                                  'kelas_id': selectedKelasId,
                                  'topik': topik,
                                  'deskripsi': deskripsiController.text.trim(),
                                  'platform': selectedPlatform,
                                  'meeting_link': meetingLink,
                                  'tgl_pertemuan': tglFormatted,
                                  'jam_mulai': jamMulaiFormatted,
                                  'jam_selesai': jamSelesaiFormatted,
                                });

                                if (mounted) {
                                  nav.pop();
                                  if (res['success'] == true) {
                                    messenger.showSnackBar(
                                      const SnackBar(
                                        content: Text('Sesi Live Meeting berhasil dijadwalkan!'),
                                        backgroundColor: Colors.green,
                                        behavior: SnackBarBehavior.floating,
                                      ),
                                    );
                                    _fetchLiveClasses();
                                  } else {
                                    messenger.showSnackBar(
                                      SnackBar(
                                        content: Text(res['message'] ?? 'Gagal membuat sesi meeting'),
                                        backgroundColor: Colors.red,
                                        behavior: SnackBarBehavior.floating,
                                      ),
                                    );
                                  }
                                }
                              },
                        icon: isSubmitting
                            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Icon(Icons.check_circle_rounded),
                        label: Text(
                          isSubmitting ? 'Memproses...' : 'Publikasikan Sesi Live Meeting',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
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
    final auth = Provider.of<AuthProvider>(context);
    final isGuru = auth.currentUser?.isGuru == true;

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Kelas Virtual Live Meeting',
              style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 18),
            ),
            Text(
              'Tatap Muka & Sync Learning Realtime',
              style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
            ),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        actions: [
          if (isGuru)
            IconButton(
              icon: const Icon(Icons.add_circle_outline_rounded, color: Color(0xFF4338CA)),
              tooltip: 'Buat Live Meeting Baru',
              onPressed: _showCreateMeetingBottomSheet,
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF64748B)),
            tooltip: 'Segarkan',
            onPressed: _fetchLiveClasses,
          ),
        ],
      ),
      floatingActionButton: isGuru
          ? FloatingActionButton.extended(
              onPressed: _showCreateMeetingBottomSheet,
              backgroundColor: const Color(0xFF4338CA),
              foregroundColor: Colors.white,
              elevation: 4,
              icon: const Icon(Icons.video_call_rounded),
              label: const Text('Buat Meeting', style: TextStyle(fontWeight: FontWeight.bold)),
            )
          : null,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchLiveClasses,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Hero Header Banner
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: AppTheme.guruGradient,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF4338CA).withValues(alpha: 0.3),
                          blurRadius: 15,
                          offset: const Offset(0, 6),
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
                                  Icon(Icons.sensors, color: Colors.white, size: 14),
                                  SizedBox(width: 6),
                                  Text(
                                    'SYNC VIRTUAL ROOM',
                                    style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                                  ),
                                ],
                              ),
                            ),
                            const Icon(Icons.videocam_rounded, color: Colors.white, size: 28),
                          ],
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'Ruang Tatap Muka Digital',
                          style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Bergabung ke sesi live meeting resmi untuk diskusi interaktif, presentasi materi, dan video conference pembelajaran.',
                          style: TextStyle(color: Colors.white70, fontSize: 12, height: 1.4),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Daftar Sesi Meeting Interaktif',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.indigo.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.indigo.shade100),
                        ),
                        child: Text(
                          '${_meetings.length} Sesi Terdata',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.indigo.shade700),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  if (_meetings.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(32),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: Column(
                        children: [
                          const Icon(Icons.video_camera_back_outlined, size: 56, color: Colors.grey),
                          const SizedBox(height: 12),
                          const Text(
                            'Belum Ada Sesi Live Meeting',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Jadwal pertemuan tatap muka virtual akan muncul di sini jika guru membuat ruang meeting baru.',
                            textAlign: TextAlign.center,
                            style: TextStyle(color: Colors.grey, fontSize: 12),
                          ),
                          if (isGuru) ...[
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF4338CA),
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                elevation: 0,
                              ),
                              onPressed: _showCreateMeetingBottomSheet,
                              icon: const Icon(Icons.add, size: 18),
                              label: const Text('Buat Sesi Pertama Sekarang', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                            ),
                          ],
                        ],
                      ),
                    )
                  else
                    ..._meetings.map((m) {
                      final statusStr = (m['status'] ?? '').toString();
                      final isLive = m['is_live'] == true || statusStr.toLowerCase().contains('langsung');
                      final isFinished = statusStr.toLowerCase().contains('selesai');
                      final isOwner = m['is_owner'] == true || isGuru;
                      final meetingId = int.tryParse(m['id'].toString()) ?? 0;
                      final link = (m['meeting_link'] ?? '').toString();
                      final platformLabel = (m['platform_label'] ?? 'Ruang Virtual').toString();
                      final deskripsi = (m['deskripsi'] ?? '').toString().trim();

                      Color statusColor = Colors.indigo;
                      if (isLive) {
                        statusColor = Colors.red;
                      } else if (isFinished) {
                        statusColor = const Color(0xFF64748B);
                      }

                      return Container(
                        margin: const EdgeInsets.only(bottom: 16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.04),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                          border: Border.all(
                            color: isLive ? Colors.red.shade300 : Colors.grey.shade200,
                            width: isLive ? 1.5 : 1.0,
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Card Header
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: isLive ? Colors.red.shade50.withValues(alpha: 0.6) : const Color(0xFFF8FAFC),
                                borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                              ),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: statusColor,
                                      borderRadius: BorderRadius.circular(16),
                                      boxShadow: [
                                        BoxShadow(
                                          color: statusColor.withValues(alpha: 0.3),
                                          blurRadius: 8,
                                          offset: const Offset(0, 3),
                                        ),
                                      ],
                                    ),
                                    child: const Icon(Icons.videocam_rounded, color: Colors.white, size: 24),
                                  ),
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                              decoration: BoxDecoration(
                                                color: statusColor,
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                              child: Row(
                                                mainAxisSize: MainAxisSize.min,
                                                children: [
                                                  if (isLive) ...[
                                                    Container(
                                                      width: 6,
                                                      height: 6,
                                                      decoration: const BoxDecoration(
                                                        color: Colors.white,
                                                        shape: BoxShape.circle,
                                                      ),
                                                    ),
                                                    const SizedBox(width: 4),
                                                  ],
                                                  Text(
                                                    statusStr.toUpperCase(),
                                                    style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                                                  ),
                                                ],
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                              decoration: BoxDecoration(
                                                color: Colors.indigo.shade50,
                                                borderRadius: BorderRadius.circular(12),
                                                border: Border.all(color: Colors.indigo.shade100),
                                              ),
                                              child: Text(
                                                platformLabel,
                                                style: TextStyle(color: Colors.indigo.shade700, fontSize: 9, fontWeight: FontWeight.bold),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          m['topik'] ?? 'Virtual Meeting',
                                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if (isOwner && meetingId > 0)
                                    PopupMenuButton<String>(
                                      icon: const Icon(Icons.more_vert, color: Colors.grey, size: 20),
                                      onSelected: (val) {
                                        if (val == 'delete') {
                                          _deleteMeeting(meetingId);
                                        }
                                      },
                                      itemBuilder: (ctx) => [
                                        const PopupMenuItem(
                                          value: 'delete',
                                          child: Row(
                                            children: [
                                              Icon(Icons.delete_outline, color: Colors.red, size: 18),
                                              SizedBox(width: 8),
                                              Text('Hapus Sesi', style: TextStyle(color: Colors.red, fontSize: 13)),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                ],
                              ),
                            ),

                            // Details Section
                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  if (deskripsi.isNotEmpty) ...[
                                    Text(
                                      deskripsi,
                                      style: TextStyle(fontSize: 12, color: Colors.grey.shade700, height: 1.4),
                                    ),
                                    const SizedBox(height: 12),
                                  ],

                                  // Row 1: Guru & Mapel
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Row(
                                          children: [
                                            const Icon(Icons.person_rounded, size: 16, color: Color(0xFF4338CA)),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                m['nama_guru'] ?? 'Guru Pengampu',
                                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Expanded(
                                        child: Row(
                                          children: [
                                            const Icon(Icons.book_rounded, size: 16, color: Colors.teal),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                m['nama_mapel'] ?? 'Mata Pelajaran',
                                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),

                                  // Row 2: Kelas & Waktu
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Row(
                                          children: [
                                            const Icon(Icons.school_rounded, size: 16, color: Colors.blue),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                m['nama_kelas'] ?? 'Semua Kelas',
                                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Expanded(
                                        child: Row(
                                          children: [
                                            const Icon(Icons.access_time_filled_rounded, size: 16, color: Colors.amber),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                m['waktu'] ?? '-',
                                                style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w500),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 16),

                                  // Card Action Button Row
                                  Row(
                                    children: [
                                      Expanded(
                                        child: ElevatedButton.icon(
                                          onPressed: () => _launchMeeting(link),
                                          icon: Icon(
                                            isLive ? Icons.sensors_rounded : Icons.video_call_rounded,
                                            size: 20,
                                          ),
                                          label: Text(
                                            isLive
                                                ? 'Gabung Sesi Sekarang'
                                                : (isFinished ? 'Buka Arsip Ruang Meeting' : 'Masuk Ruang Meeting'),
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: isLive
                                                ? Colors.red
                                                : (isFinished ? const Color(0xFF475569) : const Color(0xFF4338CA)),
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(vertical: 12),
                                            elevation: 0,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          ),
                                        ),
                                      ),
                                      if (link.isNotEmpty) ...[
                                        const SizedBox(width: 8),
                                        Container(
                                          decoration: BoxDecoration(
                                            border: Border.all(color: Colors.grey.shade300),
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: IconButton(
                                            icon: const Icon(Icons.copy_rounded, size: 18, color: Color(0xFF4338CA)),
                                            tooltip: 'Salin Tautan Ruang Meeting',
                                            onPressed: () => _copyMeetingLink(link),
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
                      );
                    }),
                ],
              ),
            ),
    );
  }
}
