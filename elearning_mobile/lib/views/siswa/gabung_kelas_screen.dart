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

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
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

  void _showEnrollMapelDialog() {
    final keyController = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
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
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
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
              final bool isSuccess = res['success'] == true;
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Status pendaftaran rombel diproses'),
                  backgroundColor: isSuccess ? Colors.green : Colors.red,
                ),
              );

              if (isSuccess) {
                if (res['data'] != null && res['data']['nama_kelas'] != null) {
                  final authProvider = Provider.of<AuthProvider>(context, listen: false);
                  final curr = authProvider.currentUser;
                  if (curr != null) {
                    final newDetails = Map<String, dynamic>.from(curr.details ?? {});
                    newDetails['nama_kelas'] = res['data']['nama_kelas'];
                    final userData = {
                      'id': curr.id,
                      'role_id': curr.roleId,
                      'username': curr.username,
                      'email': curr.email,
                      'full_name': curr.fullName,
                      'avatar': curr.avatar,
                    };
                    authProvider.updateUser(userData, newDetails, curr.roleName);
                  }
                }
                _fetchAvailableMapel();
              }
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final enrolledList = _filteredMapelList.where((m) => (m['is_enrolled'] != null && int.parse(m['is_enrolled'].toString()) > 0)).toList();
    final notEnrolledList = _filteredMapelList.where((m) => (m['is_enrolled'] == null || int.parse(m['is_enrolled'].toString()) == 0)).toList();

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Gabung Rombel & Enrol Mapel', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
          backgroundColor: Colors.indigo.shade900,
          foregroundColor: Colors.white,
          elevation: 0,
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
                          label: const Text('Input Key Mapel', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.amber.shade700,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
              child: TextField(
                controller: _searchController,
                onChanged: _filterMapel,
                decoration: InputDecoration(
                  hintText: 'Cari mata pelajaran atau nama guru...',
                  prefixIcon: const Icon(Icons.search, color: Colors.indigo),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                ),
              ),
            ),

            // Tab Bar Grouping Headers
            Container(
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.indigo.shade50,
                border: Border(bottom: BorderSide(color: Colors.grey.shade300)),
              ),
              child: TabBar(
                labelColor: Colors.indigo.shade900,
                unselectedLabelColor: Colors.grey.shade600,
                indicatorColor: Colors.indigo.shade900,
                indicatorWeight: 3,
                labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                tabs: [
                  Tab(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.check_circle_rounded, color: Colors.green, size: 16),
                        const SizedBox(width: 6),
                        Text('Sudah Terdaftar (${enrolledList.length})'),
                      ],
                    ),
                  ),
                  Tab(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.lock_rounded, color: Colors.orange.shade800, size: 16),
                        const SizedBox(width: 6),
                        Text('Belum Terdaftar (${notEnrolledList.length})'),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // TabBarView Body
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: Colors.indigo))
                  : TabBarView(
                      children: [
                        // Tab 1: Enrolled Mapel List
                        RefreshIndicator(
                          onRefresh: _fetchAvailableMapel,
                          child: enrolledList.isEmpty
                              ? ListView(
                                  children: [
                                    const SizedBox(height: 60),
                                    Center(
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.assignment_turned_in_outlined, size: 54, color: Colors.grey.shade400),
                                          const SizedBox(height: 12),
                                          const Text(
                                            'Belum ada mata pelajaran terdaftar.',
                                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            'Gunakan tombol "Input Key Mapel" untuk mendaftar.',
                                            style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                )
                              : ListView.builder(
                                  padding: const EdgeInsets.all(16),
                                  itemCount: enrolledList.length,
                                  itemBuilder: (context, index) {
                                    return _buildMapelCard(enrolledList[index], isEnrolled: true, isDark: isDark);
                                  },
                                ),
                        ),

                        // Tab 2: Not Enrolled Mapel List
                        RefreshIndicator(
                          onRefresh: _fetchAvailableMapel,
                          child: notEnrolledList.isEmpty
                              ? ListView(
                                  children: [
                                    const SizedBox(height: 60),
                                    Center(
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.task_alt_rounded, size: 54, color: Colors.green.shade300),
                                          const SizedBox(height: 12),
                                          const Text(
                                            'Seluruh mata pelajaran tersedia telah Anda terdaftar!',
                                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            'Tidak ada mata pelajaran terkunci saat ini.',
                                            style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                )
                              : ListView.builder(
                                  padding: const EdgeInsets.all(16),
                                  itemCount: notEnrolledList.length,
                                  itemBuilder: (context, index) {
                                    return _buildMapelCard(notEnrolledList[index], isEnrolled: false, isDark: isDark);
                                  },
                                ),
                        ),
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMapelCard(dynamic m, {required bool isEnrolled, required bool isDark}) {
    final namaMapel = (m['nama_mapel'] ?? 'Mata Pelajaran').toString();
    final namaGuru = (m['nama_guru'] ?? '-').toString();
    final namaKelas = m['nama_kelas'];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isEnrolled ? Colors.green.shade200 : Colors.orange.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  namaMapel,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: isEnrolled ? Colors.green.shade50 : Colors.orange.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: isEnrolled ? Colors.green.shade300 : Colors.orange.shade300),
                ),
                child: Text(
                  isEnrolled ? 'Terdaftar' : 'Terkunci',
                  style: TextStyle(
                    color: isEnrolled ? Colors.green.shade900 : Colors.orange.shade900,
                    fontWeight: FontWeight.bold,
                    fontSize: 11.5,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text("👨‍🏫 Guru Pengampu: $namaGuru", style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
          if (namaKelas != null)
            Text("🏫 Kelas Target: $namaKelas", style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
          
          if (!isEnrolled) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.amber.shade200),
              ),
              child: Row(
                children: [
                  Icon(Icons.info_outline_rounded, size: 14, color: Colors.amber.shade900),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      "Mintalah Passcode Key kepada Guru Pengampu untuk mendaftar mapel ini.",
                      style: TextStyle(
                        fontSize: 11.5,
                        color: Colors.amber.shade900,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: 12),
          Align(
            alignment: Alignment.centerRight,
            child: isEnrolled
                ? const Chip(
                    avatar: Icon(Icons.check_circle, color: Colors.green, size: 16),
                    label: Text('Sudah Terdaftar', style: TextStyle(fontSize: 12, color: Colors.green, fontWeight: FontWeight.bold)),
                    backgroundColor: Color(0xFFE8F5E9),
                  )
                : ElevatedButton.icon(
                    onPressed: () => _showEnrollMapelDialog(),
                    icon: const Icon(Icons.key, size: 16),
                    label: const Text('Masukkan Key Mapel', style: TextStyle(fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.amber.shade800,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}
