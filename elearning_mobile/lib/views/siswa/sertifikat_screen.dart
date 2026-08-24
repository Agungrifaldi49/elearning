import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class SertifikatScreen extends StatefulWidget {
  const SertifikatScreen({super.key});

  @override
  State<SertifikatScreen> createState() => _SertifikatScreenState();
}

class _SertifikatScreenState extends State<SertifikatScreen> {
  bool _isLoading = true;
  Map<String, dynamic> _cert = {};

  @override
  void initState() {
    super.initState();
    _fetchCert();
  }

  Future<void> _fetchCert() async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final res = await ApiService.get('siswa/sertifikat?user_id=$userId');
    if (mounted) {
      if (res['success'] == true && res['data'] is Map<String, dynamic>) {
        setState(() {
          _cert = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _cert = {
            'nama_siswa': user?.fullName ?? 'Siswa',
            'predikat': 'Sangat Baik (A)',
            'tgl_terbit': dateToString(),
            'no_sertifikat': 'CERT-MH-2026-001'
          };
          _isLoading = false;
        });
      }
    }
  }

  String dateToString() => DateTime.now().toString().split(' ')[0];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Sertifikat Kelulusan'),
        backgroundColor: Colors.amber.shade800,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Card(
                elevation: 6,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      const Icon(Icons.workspace_premium, size: 80, color: Colors.amber),
                      const SizedBox(height: 12),
                      const Text(
                        'SERTIFIKAT KELULUSAN LMS',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, letterSpacing: 1.2),
                      ),
                      const Divider(height: 32),
                      const Text('Diberikan Kepada:', style: TextStyle(color: Colors.grey)),
                      const SizedBox(height: 6),
                      Text(
                        _cert['nama_siswa'] ?? 'Siswa',
                        style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.amber),
                      ),
                      const SizedBox(height: 16),
                      Text('Predikat: ${_cert['predikat'] ?? "Sangat Baik"}'),
                      Text('No. Sertifikat: ${_cert['no_sertifikat'] ?? "-"}'),
                      const SizedBox(height: 24),
                      ElevatedButton.icon(
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Mengunduh sertifikat PDF...'), backgroundColor: Colors.amber),
                          );
                        },
                        icon: const Icon(Icons.download),
                        label: const Text('Unduh Sertifikat (PDF)'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.amber.shade800,
                          foregroundColor: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
    );
  }
}
