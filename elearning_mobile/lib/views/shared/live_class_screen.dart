import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class LiveClassScreen extends StatefulWidget {
  const LiveClassScreen({super.key});

  @override
  State<LiveClassScreen> createState() => _LiveClassScreenState();
}

class _LiveClassScreenState extends State<LiveClassScreen> {
  bool _isLoading = true;
  List<dynamic> _meetings = [];

  @override
  void initState() {
    super.initState();
    _fetchLiveClasses();
  }

  Future<void> _fetchLiveClasses() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('live_class');
    if (mounted) {
      if (res['success'] == true && res['data'] is List && (res['data'] as List).isNotEmpty) {
        setState(() {
          _meetings = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _meetings = [
            {
              'topik': 'Live Class Virtual: Pemrograman Web & Mobile',
              'nama_guru': 'Tim Pengajar E-Learning',
              'nama_kelas': 'XII RPL 1 & XII RPL 2',
              'waktu': 'Hari Ini, 09:00 - 10:30 WIB',
              'meeting_link': 'https://meet.google.com/abc-defg-hij',
              'platform': 'Google Meet / Embedded',
              'status': 'Sedang Berlangsung'
            },
            {
              'topik': 'Pendalaman CBT & Evaluasi Pembelajaran Digital',
              'nama_guru': 'Kepala Lab Komputer',
              'nama_kelas': 'Semua Rombel XI & XII',
              'waktu': 'Besok, 13:00 - 14:30 WIB',
              'meeting_link': 'https://meet.google.com/xyz-uvwx-rst',
              'platform': 'Virtual Room LMS',
              'status': 'Terjadwal'
            }
          ];
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Kelas Virtual Live Meeting',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            Text(
              'Tatap Muka & Sync Learning Realtime',
              style: TextStyle(fontSize: 11, color: Colors.white70),
            ),
          ],
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF334155)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
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
                      gradient: const LinearGradient(
                        colors: [Color(0xFF4338CA), Color(0xFF6366F1), Color(0xFF818CF8)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
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
                          'Bergabung ke sesi live meeting guru untuk diskusi interaktif, presentasi materi, dan video conference.',
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
                          '${_meetings.length} Sesi Aktif',
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
                      child: const Column(
                        children: [
                          Icon(Icons.video_camera_back_outlined, size: 56, color: Colors.grey),
                          SizedBox(height: 12),
                          Text(
                            'Belum Ada Sesi Live Meeting',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Jadwal pertemuan virtual akan tampil di sini jika guru membuat ruang meeting baru.',
                            textAlign: TextAlign.center,
                            style: TextStyle(color: Colors.grey, fontSize: 12),
                          ),
                        ],
                      ),
                    )
                  else
                    ..._meetings.map((m) {
                      final isLive = (m['status'] ?? '').toString().toLowerCase().contains('langsung') || (m['status'] ?? '').toString().toLowerCase().contains('aktif');
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
                            color: isLive ? Colors.red.shade200 : Colors.grey.shade200,
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
                                color: isLive ? Colors.red.shade50.withValues(alpha: 0.5) : const Color(0xFFF8FAFC),
                                borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                              ),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: isLive ? Colors.red : Colors.indigo,
                                      borderRadius: BorderRadius.circular(16),
                                      boxShadow: [
                                        BoxShadow(
                                          color: (isLive ? Colors.red : Colors.indigo).withValues(alpha: 0.3),
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
                                                color: isLive ? Colors.red : Colors.blue.shade700,
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
                                                    isLive ? 'SEDANG BERLANGSUNG' : 'TERJADWAL',
                                                    style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                                                  ),
                                                ],
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
                                ],
                              ),
                            ),

                            // Details Section
                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Row(
                                          children: [
                                            const Icon(Icons.person_rounded, size: 16, color: Colors.indigo),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                m['nama_guru'] ?? '-',
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Expanded(
                                        child: Row(
                                          children: [
                                            const Icon(Icons.school_rounded, size: 16, color: Colors.teal),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                m['nama_kelas'] ?? '-',
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  Row(
                                    children: [
                                      const Icon(Icons.access_time_filled_rounded, size: 16, color: Colors.amber),
                                      const SizedBox(width: 6),
                                      Text(
                                        m['waktu'] ?? '-',
                                        style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w500),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 16),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: ElevatedButton.icon(
                                          onPressed: () {
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              SnackBar(
                                                content: Text('Membuka tautan meeting: ${m['meeting_link']}'),
                                                backgroundColor: isLive ? Colors.red.shade700 : Colors.indigo.shade700,
                                                behavior: SnackBarBehavior.floating,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              ),
                                            );
                                          },
                                          icon: const Icon(Icons.video_call_rounded, size: 20),
                                          label: Text(
                                            isLive ? 'Gabung Sesi Sekarang' : 'Masuk Ruang Meeting',
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: isLive ? Colors.red : Colors.indigo,
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(vertical: 12),
                                            elevation: 0,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                      );
                    }),
                ],
              ),
            ),
    );
  }
}
