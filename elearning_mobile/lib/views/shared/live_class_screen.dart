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
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _meetings = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _meetings = [
            {
              'topik': 'Live Class Pemrograman Web & Mobile',
              'nama_guru': 'Tim Pengajar MH',
              'nama_kelas': 'XII RPL',
              'waktu': 'Hari Ini, 09:00 WIB',
              'meeting_link': 'https://meet.google.com/abc-defg-hij',
              'status': 'Sedang Berlangsung'
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
      appBar: AppBar(
        title: const Text('Kelas Virtual Live Meeting'),
        backgroundColor: Colors.redAccent,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchLiveClasses,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _meetings.length,
                itemBuilder: (context, index) {
                  final m = _meetings[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: Colors.red.withValues(alpha: 0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.videocam, color: Colors.red, size: 28),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      m['topik'] ?? 'Meeting Virtual',
                                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                    ),
                                    Text(
                                      'Pengajar: ${m['nama_guru'] ?? '-'} • ${m['nama_kelas'] ?? '-'}',
                                      style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              const Icon(Icons.access_time, size: 16, color: Colors.grey),
                              const SizedBox(width: 6),
                              Text(m['waktu'] ?? '', style: const TextStyle(fontSize: 13)),
                            ],
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: () {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Membuka tautan: ${m['meeting_link']}...'),
                                    backgroundColor: Colors.redAccent,
                                  ),
                                );
                              },
                              icon: const Icon(Icons.video_call),
                              label: const Text('Gabung Kelas Live Virtual'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.redAccent,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
