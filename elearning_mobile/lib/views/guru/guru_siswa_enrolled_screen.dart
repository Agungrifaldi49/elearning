import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruSiswaEnrolledScreen extends StatefulWidget {
  final int? initialMapelId;
  const GuruSiswaEnrolledScreen({super.key, this.initialMapelId});

  @override
  State<GuruSiswaEnrolledScreen> createState() => _GuruSiswaEnrolledScreenState();
}

class _GuruSiswaEnrolledScreenState extends State<GuruSiswaEnrolledScreen> {
  bool _isLoading = true;

  List<dynamic> _keys = [];
  List<dynamic> _classes = [];
  List<dynamic> _allStudents = [];
  List<dynamic> _filteredStudents = [];

  int _selectedMapelId = 0; // 0 = Semua Mapel
  int _selectedClassId = 0; // 0 = Semua Rombel
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    if (widget.initialMapelId != null && widget.initialMapelId! > 0) {
      _selectedMapelId = widget.initialMapelId!;
    }
    _loadEnrolledStudents();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadEnrolledStudents() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final data = await Provider.of<GuruProvider>(context, listen: false).fetchEnrolledStudents(
      user.id,
      mapelId: _selectedMapelId,
      kelasId: _selectedClassId,
      search: _searchController.text.trim(),
    );

    if (mounted) {
      setState(() {
        _keys = data['keys'] as List? ?? [];
        _classes = data['classes'] as List? ?? [];
        _allStudents = data['students'] as List? ?? [];
        _applyFilters();
        _isLoading = false;
      });
    }
  }

  void _applyFilters() {
    final query = _searchController.text.trim().toLowerCase();
    List<dynamic> list = List.from(_allStudents);

    if (_selectedMapelId > 0) {
      list = list.where((s) {
        final mid = int.parse((s['mapel_id'] ?? 0).toString());
        return mid == _selectedMapelId;
      }).toList();
    }

    if (_selectedClassId > 0) {
      list = list.where((s) {
        final kid = int.parse((s['kelas_id'] ?? 0).toString());
        return kid == _selectedClassId;
      }).toList();
    }

    if (query.isNotEmpty) {
      list = list.where((s) {
        final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
        final nis = (s['nis'] ?? s['nisn'] ?? '').toString().toLowerCase();
        final mapel = (s['nama_mapel'] ?? '').toString().toLowerCase();
        final kelas = (s['nama_kelas'] ?? '').toString().toLowerCase();

        return name.contains(query) ||
            nis.contains(query) ||
            mapel.contains(query) ||
            kelas.contains(query);
      }).toList();
    }

    setState(() {
      _filteredStudents = list;
    });
  }

  List<Map<String, dynamic>> _getUniqueMapels() {
    final Map<int, String> mapelMap = {};
    for (var k in _keys) {
      final mid = int.parse((k['mapel_id'] ?? 0).toString());
      final mname = (k['nama_mapel'] ?? 'Mapel').toString();
      if (mid > 0) mapelMap[mid] = mname;
    }
    for (var s in _allStudents) {
      final mid = int.parse((s['mapel_id'] ?? 0).toString());
      final mname = (s['nama_mapel'] ?? 'Mapel').toString();
      if (mid > 0 && !mapelMap.containsKey(mid)) mapelMap[mid] = mname;
    }
    return mapelMap.entries.map((e) => {'id': e.key, 'nama': e.value}).toList();
  }

  @override
  Widget build(BuildContext context) {
    final uniqueMapels = _getUniqueMapels();
    final totalSiswa = _filteredStudents.length;
    final isFiltered = _selectedMapelId > 0 || _selectedClassId > 0 || _searchController.text.isNotEmpty;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Siswa Terdaftar Mapel', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Muat Ulang',
            onPressed: _loadEnrolledStudents,
          ),
        ],
      ),
      body: Column(
        children: [
          // Header Card with Gradient & Integrated Search Box
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
            decoration: BoxDecoration(
              color: AppTheme.primaryColor,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(24)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Total Siswa Terdaftar',
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '$totalSiswa Siswa',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                // Search Input Box
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.08),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: TextField(
                    controller: _searchController,
                    onChanged: (_) => _applyFilters(),
                    style: const TextStyle(fontSize: 13, color: Colors.black87),
                    decoration: InputDecoration(
                      hintText: 'Cari Nama, NIS, atau Kelas...',
                      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                      prefixIcon: Icon(Icons.search_rounded, color: AppTheme.primaryColor, size: 20),
                      suffixIcon: _searchController.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded, size: 18),
                              onPressed: () {
                                _searchController.clear();
                                _applyFilters();
                              },
                            )
                          : null,
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Clean Filter Controls Bar
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(
              children: [
                // Mapel Dropdown Filter Pill
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
                    decoration: BoxDecoration(
                      color: _selectedMapelId > 0 ? AppTheme.primaryColor.withValues(alpha: 0.1) : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _selectedMapelId > 0 ? AppTheme.primaryColor : Colors.grey.shade300,
                      ),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int>(
                        value: _selectedMapelId,
                        isExpanded: true,
                        icon: Icon(Icons.keyboard_arrow_down_rounded, color: _selectedMapelId > 0 ? AppTheme.primaryColor : Colors.grey.shade600, size: 18),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: _selectedMapelId > 0 ? FontWeight.bold : FontWeight.normal,
                          color: _selectedMapelId > 0 ? AppTheme.primaryColor : Colors.grey.shade800,
                        ),
                        items: [
                          const DropdownMenuItem<int>(
                            value: 0,
                            child: Text('Semua Mapel'),
                          ),
                          ...uniqueMapels.map((m) {
                            return DropdownMenuItem<int>(
                              value: m['id'] as int,
                              child: Text(m['nama'] as String, overflow: TextOverflow.ellipsis),
                            );
                          }),
                        ],
                        onChanged: (val) {
                          if (val != null) {
                            setState(() {
                              _selectedMapelId = val;
                              _applyFilters();
                            });
                          }
                        },
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // Kelas Dropdown Filter Pill
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
                    decoration: BoxDecoration(
                      color: _selectedClassId > 0 ? AppTheme.primaryColor.withValues(alpha: 0.1) : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _selectedClassId > 0 ? AppTheme.primaryColor : Colors.grey.shade300,
                      ),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int>(
                        value: _selectedClassId,
                        isExpanded: true,
                        icon: Icon(Icons.keyboard_arrow_down_rounded, color: _selectedClassId > 0 ? AppTheme.primaryColor : Colors.grey.shade600, size: 18),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: _selectedClassId > 0 ? FontWeight.bold : FontWeight.normal,
                          color: _selectedClassId > 0 ? AppTheme.primaryColor : Colors.grey.shade800,
                        ),
                        items: [
                          const DropdownMenuItem<int>(
                            value: 0,
                            child: Text('Semua Kelas'),
                          ),
                          ..._classes.map((c) {
                            final cid = int.parse((c['id'] ?? 0).toString());
                            final cname = (c['nama_kelas'] ?? 'Kelas').toString();
                            return DropdownMenuItem<int>(
                              value: cid,
                              child: Text(cname, overflow: TextOverflow.ellipsis),
                            );
                          }),
                        ],
                        onChanged: (val) {
                          if (val != null) {
                            setState(() {
                              _selectedClassId = val;
                              _applyFilters();
                            });
                          }
                        },
                      ),
                    ),
                  ),
                ),

                // Reset Button if Filtered
                if (isFiltered) ...[
                  const SizedBox(width: 6),
                  InkWell(
                    onTap: () {
                      setState(() {
                        _selectedMapelId = 0;
                        _selectedClassId = 0;
                        _searchController.clear();
                        _applyFilters();
                      });
                    },
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.red.shade200),
                      ),
                      child: Icon(Icons.refresh_rounded, size: 18, color: Colors.red.shade700),
                    ),
                  ),
                ],
              ],
            ),
          ),

          // Enrolled Student List Body
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadEnrolledStudents,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredStudents.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade100,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(Icons.person_search_rounded, size: 40, color: Colors.grey.shade400),
                              ),
                              const SizedBox(height: 12),
                              Text(
                                'Tidak ada siswa terdaftar',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.grey.shade700),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Coba sesuaikan filter atau pencarian Anda',
                                style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 4, 16, 20),
                          itemCount: _filteredStudents.length,
                          itemBuilder: (context, index) {
                            final s = _filteredStudents[index];
                            final name = (s['nama_lengkap'] ?? 'Siswa').toString();
                            final nis = (s['nis'] ?? s['nisn'] ?? '-').toString();
                            final className = (s['nama_kelas'] ?? 'Tanpa Kelas').toString();
                            final jurusanName = (s['nama_jurusan'] ?? '-').toString();
                            final mapelName = (s['nama_mapel'] ?? 'Mata Pelajaran').toString();
                            final enrolledAt = (s['enrolled_at'] ?? '').toString();

                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.03),
                                    blurRadius: 8,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Row(
                                  children: [
                                    // Avatar Initial Circle
                                    Container(
                                      width: 44,
                                      height: 44,
                                      decoration: BoxDecoration(
                                        gradient: LinearGradient(
                                          colors: [AppTheme.primaryColor.withValues(alpha: 0.15), AppTheme.primaryColor.withValues(alpha: 0.05)],
                                          begin: Alignment.topLeft,
                                          end: Alignment.bottomRight,
                                        ),
                                        shape: BoxShape.circle,
                                        border: Border.all(color: AppTheme.primaryColor.withValues(alpha: 0.2)),
                                      ),
                                      child: Center(
                                        child: Text(
                                          name.isNotEmpty ? name[0].toUpperCase() : 'S',
                                          style: TextStyle(
                                            color: AppTheme.primaryColor,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 16,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),

                                    // Student Main Details
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            name,
                                            style: const TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.black87,
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          Row(
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                                decoration: BoxDecoration(
                                                  color: Colors.grey.shade100,
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  'NIS: $nis',
                                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                                                ),
                                              ),
                                              const SizedBox(width: 6),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                                decoration: BoxDecoration(
                                                  color: Colors.blue.shade50,
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  className,
                                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                                                ),
                                              ),
                                            ],
                                          ),
                                          if (jurusanName != '-' && jurusanName.isNotEmpty) ...[
                                            const SizedBox(height: 4),
                                            Text(
                                              'Jurusan: $jurusanName',
                                              style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                            ),
                                          ],
                                          const SizedBox(height: 6),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                                            decoration: BoxDecoration(
                                              color: Colors.amber.shade50,
                                              borderRadius: BorderRadius.circular(8),
                                              border: Border.all(color: Colors.amber.shade200),
                                            ),
                                            child: Text(
                                              '📘 $mapelName',
                                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),

                                    // Right Date & Status Badge
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.end,
                                      children: [
                                        const Icon(Icons.check_circle_rounded, color: Colors.green, size: 18),
                                        if (enrolledAt.isNotEmpty) ...[
                                          const SizedBox(height: 12),
                                          Text(
                                            enrolledAt.length >= 10 ? enrolledAt.substring(0, 10) : enrolledAt,
                                            style: TextStyle(fontSize: 9, color: Colors.grey.shade400),
                                          ),
                                        ],
                                      ],
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
