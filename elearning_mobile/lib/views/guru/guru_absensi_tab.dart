import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruAbsensiTab extends StatefulWidget {
  const GuruAbsensiTab({super.key});

  @override
  State<GuruAbsensiTab> createState() => _GuruAbsensiTabState();
}

class _GuruAbsensiTabState extends State<GuruAbsensiTab> {
  List<dynamic> _students = [];
  final Map<int, String> _records = {}; // [siswa_id => status]
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadStudents();
  }

  void _loadStudents() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() {
      _isLoading = true;
    });

    final students = await Provider.of<GuruProvider>(context, listen: false)
        .fetchStudentsForAbsensi(user.id, 1);

    setState(() {
      _students = students;
      for (var s in _students) {
        final sid = int.parse(s['id'].toString());
        _records[sid] = 'Hadir';
      }
      _isLoading = false;
    });
  }

  void _saveAbsensi() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final ok = await Provider.of<GuruProvider>(context, listen: false)
        .saveAbsensi(user.id, 1, _records);

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ok ? 'Presensi siswa berhasil disimpan!' : 'Gagal menyimpan presensi'),
        backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekap Presensi Siswa Kelas'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              '📋 Tandai Kehadiran Siswa Hari Ini',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),

            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _students.isEmpty
                      ? const Center(child: Text('Tidak ada data siswa.'))
                      : ListView.builder(
                          itemCount: _students.length,
                          itemBuilder: (context, index) {
                            final s = _students[index];
                            final sid = int.parse(s['id'].toString());
                            final currentStatus = _records[sid] ?? 'Hadir';

                            return Card(
                              margin: const EdgeInsets.only(bottom: 8),
                              child: ListTile(
                                leading: CircleAvatar(
                                  child: Text(s['nama_lengkap'][0]),
                                ),
                                title: Text(s['nama_lengkap'], style: const TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Text("NIS: ${s['nis']}"),
                                trailing: DropdownButton<String>(
                                  value: currentStatus,
                                  items: const [
                                    DropdownMenuItem(value: 'Hadir', child: Text('Hadir', style: TextStyle(color: Colors.green))),
                                    DropdownMenuItem(value: 'Izin', child: Text('Izin', style: TextStyle(color: Colors.blue))),
                                    DropdownMenuItem(value: 'Sakit', child: Text('Sakit', style: TextStyle(color: Colors.orange))),
                                    DropdownMenuItem(value: 'Alpa', child: Text('Alpa', style: TextStyle(color: Colors.red))),
                                  ],
                                  onChanged: (val) {
                                    if (val != null) {
                                      setState(() {
                                        _records[sid] = val;
                                      });
                                    }
                                  },
                                ),
                              ),
                            );
                          },
                        ),
            ),

            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _saveAbsensi,
              icon: const Icon(Icons.save_rounded),
              label: const Text('Simpan Presensi Kehadiran'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 50),
                backgroundColor: AppTheme.primaryColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
