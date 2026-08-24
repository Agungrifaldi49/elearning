import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class GuruRecapAbsensiScreen extends StatefulWidget {
  const GuruRecapAbsensiScreen({super.key});

  @override
  State<GuruRecapAbsensiScreen> createState() => _GuruRecapAbsensiScreenState();
}

class _GuruRecapAbsensiScreenState extends State<GuruRecapAbsensiScreen> {
  bool _isLoading = true;
  List<dynamic> _recaps = [];

  @override
  void initState() {
    super.initState();
    _fetchRecap();
  }

  Future<void> _fetchRecap() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('guru/recap_absensi');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _recaps = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _recaps = [];
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekapitulasi Presensi Bulanan'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _recaps.isEmpty
              ? const Center(child: Text('Belum ada data rekap presensi bulanan.'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _recaps.length,
                  itemBuilder: (context, index) {
                    final r = _recaps[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 10),
                      child: ListTile(
                        leading: const CircleAvatar(
                          backgroundColor: Colors.teal,
                          foregroundColor: Colors.white,
                          child: Icon(Icons.person),
                        ),
                        title: Text(r['nama_lengkap'] ?? 'Siswa', style: const TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: Text("Tanggal: ${r['tanggal']} • Status: ${r['status']}"),
                      ),
                    );
                  },
                ),
    );
  }
}
