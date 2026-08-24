import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GabungKelasScreen extends StatefulWidget {
  const GabungKelasScreen({super.key});

  @override
  State<GabungKelasScreen> createState() => _GabungKelasScreenState();
}

class _GabungKelasScreenState extends State<GabungKelasScreen> {
  final TextEditingController _searchController = TextEditingController();
  bool _isLoading = true;
  List<dynamic> _mapelList = [];
  List<dynamic> _filteredMapelList = [];

  @override
  void initState() {
    super.initState();
    _fetchAvailableMapel();
  }

  Future<void> _fetchAvailableMapel([String search = '']) async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final res = await ApiService.get('siswa/available_mapel', params: {
      'user_id': '$userId',
      'search': search,
    });

    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _mapelList = res['data'];
          _filteredMapelList = _mapelList;
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  void _filterMapel(String query) {
    if (query.isEmpty) {
      setState(() => _filteredMapelList = _mapelList);
    } else {
      final q = query.toLowerCase();
      setState(() {
        _filteredMapelList = _mapelList.where((m) {
          final nama = (m['nama_mapel'] ?? '').toString().toLowerCase();
          final guru = (m['nama_guru'] ?? '').toString().toLowerCase();
          final kelas = (m['nama_kelas'] ?? '').toString().toLowerCase();
          return nama.contains(q) || guru.contains(q) || kelas.contains(q);
        }).toList();
      });
    }
  }

  void _showEnrollMapelDialog([String initialKey = '']) {
    final keyController = TextEditingController(text: initialKey);
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: const [
            Icon(Icons.key_outlined, color: Colors.amber),
            SizedBox(width: 8),
            Text('Passcode Key Mapel', style: TextStyle(fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Masukkan Passcode Key resmi dari Guru Pengampu atau Admin:'),
            const SizedBox(height: 12),
            TextField(
              controller: keyController,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                labelText: 'Passcode Key / Kode Akses Mapel',
                hintText: 'Misal: MPL-1-2-582 atau WEB-GURU1',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.lock_outline),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              final keyVal = keyController.text.trim();
              if (keyVal.isEmpty) return;

              final res = await ApiService.post('siswa/gabung_kelas', {
                'user_id': user?.id ?? 0,
                'action': 'enroll_mapel',
                'key_mapel': keyVal,
              });

              if (!mounted) return;
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Status pendaftaran diproses'),
                  backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                ),
              );

              if (res['success'] == true) {
                _fetchAvailableMapel();
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.amber.shade800, foregroundColor: Colors.white),
            child: const Text('Verifikasi & Daftar'),
          ),
        ],
      ),
    );
  }

  void _showJoinKodeKelasDialog() {
    final kodeController = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: const [
            Icon(Icons.meeting_room_outlined, color: Colors.blue),
            SizedBox(width: 8),
            Text('Kode Rombel Kelas Utama', style: TextStyle(fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Masukkan Kode Akses Rombel resmi dari Wali Kelas Anda:'),
            const SizedBox(height: 12),
            TextField(
              controller: kodeController,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                labelText: 'Kode Akses Rombel',
                hintText: 'Misal: MH-A1B2C3 atau X RPL 1',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.class_outlined),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              final kodeVal = kodeController.text.trim();
              if (kodeVal.isEmpty) return;

              final res = await ApiService.post('siswa/gabung_kelas', {
                'user_id': user?.id ?? 0,
                'action': 'join_kelas',
                'kode_kelas': kodeVal,
              });

              if (!mounted) return;
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Status pendaftaran rombel diproses'),
                  backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                ),
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
            child: const Text('Verifikasi Rombel'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Gabung Rombel & Enrol Mapel'),
        backgroundColor: Colors.indigo.shade900,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // Hero Banner Card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Colors.indigo.shade900, Colors.blue.shade900],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Text('Passcode Key Protection', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                const Text(
                  'Pendaftaran Mapel & Rombel Digital',
                  style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(
                  user?.namaKelas != null ? "Rombel Kelas Utama: ${user!.namaKelas}" : "Anda belum memilih rombel kelas utama.",
                  style: const TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _showEnrollMapelDialog(),
                        icon: const Icon(Icons.key, size: 16),
                        label: const Text('Input Key Mapel', style: TextStyle(fontSize: 13)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.amber.shade700,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _showJoinKodeKelasDialog,
                        icon: const Icon(Icons.class_outlined, size: 16, color: Colors.white),
                        label: const Text('Kode Rombel', style: TextStyle(fontSize: 13, color: Colors.white)),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: Colors.white54),
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Search Field
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              onChanged: _filterMapel,
              decoration: InputDecoration(
                hintText: 'Cari mata pelajaran atau nama guru...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),

          // Mapel List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filteredMapelList.isEmpty
                    ? const Center(child: Text('Mata pelajaran tidak ditemukan.'))
                    : RefreshIndicator(
                        onRefresh: _fetchAvailableMapel,
                        child: ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: _filteredMapelList.length,
                          itemBuilder: (context, index) {
                            final m = _filteredMapelList[index];
                            final bool isEnrolled = (m['is_enrolled'] != null && int.parse(m['is_enrolled'].toString()) > 0);
                            final String mapelKey = m['enrollment_key'] ?? '';

                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 2,
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            m['nama_mapel'] ?? 'Mata Pelajaran',
                                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: isEnrolled ? Colors.green.withValues(alpha: 0.15) : Colors.orange.withValues(alpha: 0.15),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            isEnrolled ? 'Terdaftar' : 'Terkunci',
                                            style: TextStyle(
                                              color: isEnrolled ? Colors.green : Colors.orange.shade800,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Text("Guru Pengampu: ${m['nama_guru'] ?? '-'}", style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
                                    if (m['nama_kelas'] != null)
                                      Text("Kelas Target: ${m['nama_kelas']}", style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                                    const SizedBox(height: 12),
                                    Align(
                                      alignment: Alignment.centerRight,
                                      child: isEnrolled
                                          ? const Chip(
                                              avatar: Icon(Icons.check_circle, color: Colors.green, size: 16),
                                              label: Text('Sudah Terdaftar', style: TextStyle(fontSize: 12, color: Colors.green)),
                                              backgroundColor: Color(0xFFE8F5E9),
                                            )
                                          : ElevatedButton.icon(
                                              onPressed: () => _showEnrollMapelDialog(''),
                                              icon: const Icon(Icons.key, size: 16),
                                              label: const Text('Masukkan Key Mapel'),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: Colors.amber.shade800,
                                                foregroundColor: Colors.white,
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
          ),
        ],
      ),
    );
  }
}
