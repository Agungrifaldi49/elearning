import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class PanduanScreen extends StatefulWidget {
  const PanduanScreen({super.key});

  @override
  State<PanduanScreen> createState() => _PanduanScreenState();
}

class _PanduanScreenState extends State<PanduanScreen> {
  bool _isLoading = true;
  List<dynamic> _guides = [];

  @override
  void initState() {
    super.initState();
    _fetchPanduan();
  }

  Future<void> _fetchPanduan() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('panduan');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _guides = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _guides = [
            {
              'judul': 'Panduan Akses & Presensi Mobile',
              'deskripsi': 'Petunjuk melakukan presensi harian dan QR code di HP.'
            },
            {
              'judul': 'Panduan Pengerjaan CBT & Tugas',
              'deskripsi': 'Tata cara menjawab quiz CBT online dan mengunggah tugas.'
            },
            {
              'judul': 'Panduan Guru Input Nilai & Bank Soal',
              'deskripsi': 'Petunjuk untuk bapak/ibu guru mengelola soal dan menilai.'
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
        title: const Text('Buku Panduan & Help Center'),
        backgroundColor: Colors.blueGrey,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _guides.length,
              itemBuilder: (context, index) {
                final g = _guides[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ExpansionTile(
                    leading: const Icon(Icons.help_outline, color: Colors.blueGrey),
                    title: Text(
                      g['judul'] ?? 'Panduan LMS',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(
                          g['deskripsi'] ?? '',
                          style: TextStyle(color: Colors.grey.shade800, height: 1.4),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
    );
  }
}
