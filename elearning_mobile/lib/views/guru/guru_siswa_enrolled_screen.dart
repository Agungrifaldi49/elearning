import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';

class GuruSiswaEnrolledScreen extends StatefulWidget {
  final int? initialMapelId;
  const GuruSiswaEnrolledScreen({super.key, this.initialMapelId});

  @override
  State<GuruSiswaEnrolledScreen> createState() => _GuruSiswaEnrolledScreenState();
}

class _GuruSiswaEnrolledScreenState extends State<GuruSiswaEnrolledScreen> {
  bool _isLoading = true;
  int _groupMode = 0; // 0 = Per Mapel, 1 = Per Kelas

  List<dynamic> _keys = [];
  List<dynamic> _classes = [];
  List<dynamic> _allStudents = [];
  List<dynamic> _filteredStudents = [];

  int _selectedMapelId = 0; // 0 = Semua Mapel
  int _selectedClassId = 0; // 0 = Semua Kelas
  String _selectedClassName = 'Semua Kelas';

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
        final keyMapel = (s['key_mapel'] ?? '').toString().toLowerCase();

        return name.contains(query) ||
            nis.contains(query) ||
            mapel.contains(query) ||
            kelas.contains(query) ||
            keyMapel.contains(query);
      }).toList();
    }

    setState(() {
      _filteredStudents = list;
    });
  }

  // Group Students by Mapel or Class based on _groupMode
  Map<String, List<dynamic>> _groupStudents() {
    final Map<String, List<dynamic>> grouped = {};
    for (var s in _filteredStudents) {
      String groupKey = 'Tanpa Kelompok';
      if (_groupMode == 0) {
        groupKey = (s['nama_mapel'] ?? 'Mata Pelajaran').toString();
      } else {
        groupKey = (s['nama_kelas'] ?? 'Tanpa Kelas').toString();
      }

      if (!grouped.containsKey(groupKey)) {
        grouped[groupKey] = [];
      }
      grouped[groupKey]!.add(s);
    }
    return grouped;
  }

  // Extract unique Mapels from _keys and _allStudents
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
    final groupedData = _groupStudents();
    final uniqueMapels = _getUniqueMapels();
    final totalSiswa = _filteredStudents.length;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Siswa Terdaftar Mapel'),
        backgroundColor: Colors.indigo.shade800,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Muat Ulang Data',
            onPressed: _loadEnrolledStudents,
          ),
        ],
      ),
      body: Column(
        children: [
          // Hero Container Header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Colors.indigo.shade800, Colors.indigo.shade600],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(24),
                bottomRight: Radius.circular(24),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '🎓 Data Siswa Terdaftar (Key Mapel)',
                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Daftar siswa yang telah memasukkan Kode Key Mapel pada pengampuan Anda',
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
                const SizedBox(height: 12),

                // Statistics Pills Row
                Row(
                  children: [
                    _buildStatBadge('👥 Total Siswa', '$totalSiswa Siswa', Colors.indigo.shade100, Colors.indigo.shade900),
                    const SizedBox(width: 8),
                    _buildStatBadge('📚 Total Mapel', '${uniqueMapels.length} Mapel', Colors.amber.shade100, Colors.amber.shade900),
                    const SizedBox(width: 8),
                    _buildStatBadge('🏫 Total Rombel', '${_classes.length} Kelas', Colors.teal.shade100, Colors.teal.shade900),
                  ],
                ),
                const SizedBox(height: 12),

                // Search Bar
                TextField(
                  controller: _searchController,
                  onChanged: (_) => _applyFilters(),
                  style: const TextStyle(color: Colors.white, fontSize: 13),
                  decoration: InputDecoration(
                    hintText: 'Cari Nama Siswa, NIS, Mapel, atau Kode Key...',
                    hintStyle: const TextStyle(color: Colors.white60),
                    prefixIcon: const Icon(Icons.search, color: Colors.white60),
                    filled: true,
                    fillColor: Colors.white.withValues(alpha: 0.18),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                ),
              ],
            ),
          ),

          // Grouping Toggle Segmented Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            color: Colors.white,
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: InkWell(
                        onTap: () => setState(() => _groupMode = 0),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 7),
                          decoration: BoxDecoration(
                            color: _groupMode == 0 ? Colors.indigo.shade700 : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '📘 Kelompok Per Mapel',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: _groupMode == 0 ? Colors.white : Colors.black87,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: InkWell(
                        onTap: () => setState(() => _groupMode = 1),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 7),
                          decoration: BoxDecoration(
                            color: _groupMode == 1 ? Colors.indigo.shade700 : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '🏫 Kelompok Per Kelas',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: _groupMode == 1 ? Colors.white : Colors.black87,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),

                // Mapel Selection Chips Row
                if (uniqueMapels.isNotEmpty) ...[
                  SizedBox(
                    height: 34,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        Padding(
                          padding: const EdgeInsets.only(right: 6),
                          child: ChoiceChip(
                            label: const Text('Semua Mapel'),
                            selected: _selectedMapelId == 0,
                            selectedColor: Colors.indigo.shade700,
                            labelStyle: TextStyle(
                              color: _selectedMapelId == 0 ? Colors.white : Colors.black87,
                              fontWeight: FontWeight.bold,
                              fontSize: 11,
                            ),
                            onSelected: (selected) {
                              if (selected) {
                                setState(() {
                                  _selectedMapelId = 0;
                                  _applyFilters();
                                });
                              }
                            },
                          ),
                        ),
                        ...uniqueMapels.map((m) {
                          final mid = m['id'] as int;
                          final mname = m['nama'] as String;
                          final isSelected = _selectedMapelId == mid;

                          return Padding(
                            padding: const EdgeInsets.only(right: 6),
                            child: ChoiceChip(
                              label: Text(mname),
                              selected: isSelected,
                              selectedColor: Colors.indigo.shade700,
                              labelStyle: TextStyle(
                                color: isSelected ? Colors.white : Colors.black87,
                                fontWeight: FontWeight.bold,
                                fontSize: 11,
                              ),
                              onSelected: (selected) {
                                if (selected) {
                                  setState(() {
                                    _selectedMapelId = mid;
                                    _applyFilters();
                                  });
                                }
                              },
                            ),
                          );
                        }),
                      ],
                    ),
                  ),
                  const SizedBox(height: 4),
                ],

                // Class Chips Row
                SizedBox(
                  height: 34,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    children: [
                      Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: ChoiceChip(
                          label: const Text('Semua Rombel'),
                          selected: _selectedClassId == 0,
                          selectedColor: Colors.indigo.shade700,
                          labelStyle: TextStyle(
                            color: _selectedClassId == 0 ? Colors.white : Colors.black87,
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                          ),
                          onSelected: (selected) {
                            if (selected) {
                              setState(() {
                                _selectedClassId = 0;
                                _selectedClassName = 'Semua Rombel';
                                _applyFilters();
                              });
                            }
                          },
                        ),
                      ),
                      ..._classes.map((c) {
                        final cid = int.parse((c['id'] ?? 0).toString());
                        final cname = (c['nama_kelas'] ?? 'Kelas').toString();
                        final isSelected = _selectedClassId == cid;

                        return Padding(
                          padding: const EdgeInsets.only(right: 6),
                          child: ChoiceChip(
                            label: Text(cname),
                            selected: isSelected,
                            selectedColor: Colors.indigo.shade700,
                            labelStyle: TextStyle(
                              color: isSelected ? Colors.white : Colors.black87,
                              fontWeight: FontWeight.bold,
                              fontSize: 11,
                            ),
                            onSelected: (selected) {
                              if (selected) {
                                setState(() {
                                  _selectedClassId = cid;
                                  _selectedClassName = cname;
                                  _applyFilters();
                                });
                              }
                            },
                          ),
                        );
                      }),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Main Student List Body
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
                              Icon(Icons.person_search_rounded, size: 54, color: Colors.grey.shade400),
                              const SizedBox(height: 12),
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 24),
                                child: Text(
                                  'Belum ada siswa yang terdaftar di $_selectedClassName.',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView(
                          padding: const EdgeInsets.all(12),
                          children: groupedData.entries.map((entry) {
                            final groupName = entry.key;
                            final studentsInGroup = entry.value;

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Group Section Header
                                Container(
                                  width: double.infinity,
                                  margin: const EdgeInsets.only(top: 8, bottom: 8),
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: _groupMode == 0 ? Colors.indigo.shade50 : Colors.amber.shade50,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: _groupMode == 0 ? Colors.indigo.shade200 : Colors.amber.shade200),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(
                                        _groupMode == 0 ? Icons.menu_book_rounded : Icons.school_rounded,
                                        size: 18,
                                        color: _groupMode == 0 ? Colors.indigo.shade800 : Colors.amber.shade900,
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          _groupMode == 0 ? 'Mapel: $groupName' : 'Kelas: $groupName',
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.bold,
                                            color: _groupMode == 0 ? Colors.indigo.shade800 : Colors.amber.shade900,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: _groupMode == 0 ? Colors.indigo.shade800 : Colors.amber.shade900,
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: Text(
                                          '${studentsInGroup.length} Siswa',
                                          style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),

                                // Student Cards
                                ...studentsInGroup.map((s) {
                                  final name = (s['nama_lengkap'] ?? 'Siswa').toString();
                                  final nis = (s['nis'] ?? s['nisn'] ?? '-').toString();
                                  final className = (s['nama_kelas'] ?? 'Tanpa Kelas').toString();
                                  final jurusanName = (s['nama_jurusan'] ?? '-').toString();
                                  final mapelName = (s['nama_mapel'] ?? 'Mata Pelajaran').toString();
                                  final keyMapel = (s['key_mapel'] ?? s['enrollment_key'] ?? '-').toString();
                                  final enrolledAt = (s['enrolled_at'] ?? '').toString();

                                  return Card(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                    elevation: 1.5,
                                    child: Padding(
                                      padding: const EdgeInsets.all(12),
                                      child: Row(
                                        children: [
                                          CircleAvatar(
                                            backgroundColor: Colors.indigo.shade50,
                                            child: Text(
                                              name.isNotEmpty ? name[0] : 'S',
                                              style: TextStyle(color: Colors.indigo.shade800, fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  name,
                                                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                                                ),
                                                const SizedBox(height: 2),
                                                Text(
                                                  'NIS: $nis  •  Kelas: $className',
                                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade700, fontWeight: FontWeight.w500),
                                                ),
                                                if (jurusanName != '-' && jurusanName.isNotEmpty) ...[
                                                  const SizedBox(height: 2),
                                                  Text(
                                                    'Jurusan: $jurusanName',
                                                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                                  ),
                                                ],
                                                const SizedBox(height: 4),
                                                Wrap(
                                                  spacing: 6,
                                                  runSpacing: 4,
                                                  children: [
                                                    Container(
                                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                      decoration: BoxDecoration(
                                                        color: Colors.blue.shade50,
                                                        border: Border.all(color: Colors.blue.shade200),
                                                        borderRadius: BorderRadius.circular(6),
                                                      ),
                                                      child: Text(
                                                        '📘 Mapel: $mapelName',
                                                        style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.blue.shade900),
                                                      ),
                                                    ),
                                                    if (keyMapel != '-')
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                        decoration: BoxDecoration(
                                                          color: Colors.amber.shade50,
                                                          border: Border.all(color: Colors.amber.shade300),
                                                          borderRadius: BorderRadius.circular(6),
                                                        ),
                                                        child: Text(
                                                          '🔑 Key: $keyMapel',
                                                          style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                                        ),
                                                      ),
                                                  ],
                                                ),
                                                if (enrolledAt.isNotEmpty) ...[
                                                  const SizedBox(height: 4),
                                                  Text(
                                                    'Terdaftar: ${enrolledAt.length >= 10 ? enrolledAt.substring(0, 10) : enrolledAt}',
                                                    style: TextStyle(fontSize: 9, color: Colors.grey.shade500),
                                                  ),
                                                ],
                                              ],
                                            ),
                                          ),
                                          const Icon(Icons.check_circle_rounded, color: Colors.green, size: 20),
                                        ],
                                      ),
                                    ),
                                  );
                                }),
                              ],
                            );
                          }).toList(),
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatBadge(String label, String value, Color bg, Color text) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: text),
            ),
            Text(
              label,
              style: TextStyle(fontSize: 9, color: text.withValues(alpha: 0.8), fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }
}
