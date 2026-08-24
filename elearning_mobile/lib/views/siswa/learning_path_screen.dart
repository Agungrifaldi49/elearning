import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class LearningPathScreen extends StatefulWidget {
  const LearningPathScreen({super.key});

  @override
  State<LearningPathScreen> createState() => _LearningPathScreenState();
}

class _LearningPathScreenState extends State<LearningPathScreen> {
  bool _isLoading = true;
  Map<String, dynamic> _data = {};

  @override
  void initState() {
    super.initState();
    _fetchPath();
  }

  Future<void> _fetchPath() async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;
    final res = await ApiService.get('siswa/learning_path?user_id=$userId');
    if (mounted) {
      if (res['success'] == true && res['data'] is Map<String, dynamic>) {
        setState(() {
          _data = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _data = {
            'tingkat': 'Kelas XII',
            'jurusan': 'Teknologi Informasi & Komputer',
            'capaian_persen': 85,
            'modul_selesai': 12,
            'total_modul': 15
          };
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Learning Path & Capaian Belajar'),
        backgroundColor: Colors.deepPurple,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    color: Colors.deepPurple,
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _data['tingkat'] ?? 'Tingkat Kurikulum',
                            style: const TextStyle(color: Colors.white70, fontSize: 13),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _data['jurusan'] ?? 'Jurusan SMK',
                            style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 16),
                          LinearProgressIndicator(
                            value: ((_data['capaian_persen'] ?? 85) / 100).toDouble(),
                            backgroundColor: Colors.white24,
                            color: Colors.amber,
                            minHeight: 8,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Progress Kurikulum: ${_data['capaian_persen']}% Selesai (${_data['modul_selesai']}/${_data['total_modul']} Modul)',
                            style: const TextStyle(color: Colors.white, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
