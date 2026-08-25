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
  List<dynamic> _mapelList = [];
  List<dynamic> _classes = [];
  List<dynamic> _allStudents = [];
  List<dynamic> _filteredStudents = [];
  final Map<int, String> _records = {}; // [siswa_id => status]

  int _selectedMapelId = 0; // 0 = Semua Mapel Saya
  int _selectedClassId = 0; // 0 = Semua Kelas
  String _selectedClassName = 'Semua Kelas';
  bool _isLoading = true;
  bool _isSaving = false;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadAbsensiData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadAbsensiData() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final data = await Provider.of<GuruProvider>(context, listen: false)
        .fetchAbsensiData(user.id, _selectedClassId, mapelId: _selectedMapelId);

    if (mounted) {
      setState(() {
        _mapelList = data['mapel_list'] as List? ?? [];
        _classes = data['classes'] as List? ?? [];
        _allStudents = data['students'] as List? ?? [];
        
        // Initialize default status to 'Hadir' if not set
        for (var s in _allStudents) {
          final sid = int.parse((s['id'] ?? 0).toString());
          if (!_records.containsKey(sid)) {
            _records[sid] = 'Hadir';
          }
        }

        _applyFilters();
        _isLoading = false;
      });
    }
  }

  void _applyFilters() {
    final query = _searchController.text.trim().toLowerCase();
    List<dynamic> list = List.from(_allStudents);

    if (_selectedClassId > 0) {
      list = list.where((s) {
        final kid = int.parse((s['kelas_id'] ?? 0).toString());
        return kid == _selectedClassId;
      }).toList();
    }

    if (query.isNotEmpty) {
      list = list.where((s) {
        final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
        final nis = (s['nis'] ?? '').toString().toLowerCase();
        return name.contains(query) || nis.contains(query);
      }).toList();
    }

    setState(() {
      _filteredStudents = list;
    });
  }

  void _saveAbsensi() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    if (_records.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada data siswa terdaftar untuk disimpan.')),
      );
      return;
    }

    setState(() => _isSaving = true);

    final ok = await Provider.of<GuruProvider>(context, listen: false)
        .saveAbsensi(user.id, 1, _records);

    if (!mounted) return;

    setState(() => _isSaving = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ok
            ? 'Presensi ${_records.length} siswa terdaftar berhasil disimpan!'
            : 'Gagal menyimpan presensi. Periksa koneksi internet.'),
        backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  // Count Live Status Summaries
  Map<String, int> _getSummaryStats() {
    int hadir = 0, izin = 0, sakit = 0, alpa = 0;
    for (var s in _filteredStudents) {
      final sid = int.parse((s['id'] ?? 0).toString());
      final st = _records[sid] ?? 'Hadir';
      if (st == 'Hadir') hadir++;
      else if (st == 'Izin') izin++;
      else if (st == 'Sakit') sakit++;
      else if (st == 'Alpa') alpa++;
    }
    return {'hadir': hadir, 'izin': izin, 'sakit': sakit, 'alpa': alpa};
  }

  // Group Students by Class Name
  Map<String, List<dynamic>> _groupStudentsByClass() {
    final Map<String, List<dynamic>> grouped = {};
    for (var s in _filteredStudents) {
      final className = (s['nama_kelas'] ?? 'Tanpa Kelas').toString();
      if (!grouped.containsKey(className)) {
        grouped[className] = [];
      }
      grouped[className]!.add(s);
    }
    return grouped;
  }

  @override
  Widget build(BuildContext context) {
    final stats = _getSummaryStats();
    final groupedData = _groupStudentsByClass();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Input Presensi Siswa Terdaftar'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Header Hero Card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              gradient: AppTheme.primaryGradient,
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(24),
                bottomRight: Radius.circular(24),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '📋 Presensi Siswa Terdaftar di Mapel Saya',
                  style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Menampilkan siswa yang telah mengambil & terdaftar pada mata pelajaran Anda',
                  style: TextStyle(color: Colors.white70, fontSize: 12),
                ),
                const SizedBox(height: 12),

                // Mapel Dropdown Filter
                if (_mapelList.isNotEmpty) ...[
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.white30),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int>(
                        value: _selectedMapelId,
                        dropdownColor: AppTheme.primaryColor,
                        icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white),
                        isExpanded: true,
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        items: [
                          const DropdownMenuItem<int>(
                            value: 0,
                            child: Text('📚 Semua Mapel Saya'),
                          ),
                          ..._mapelList.map((m) {
                            final mid = int.parse((m['mapel_id'] ?? 0).toString());
                            final mname = (m['nama_mapel'] ?? 'Mapel').toString();
                            return DropdownMenuItem<int>(
                              value: mid,
                              child: Text('📘 $mname'),
                            );
                          }),
                        ],
                        onChanged: (val) {
                          if (val != null) {
                            setState(() {
                              _selectedMapelId = val;
                              _loadAbsensiData();
                            });
                          }
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                ],

                // Search Bar
                TextField(
                  controller: _searchController,
                  onChanged: (_) => _applyFilters(),
                  style: const TextStyle(color: Colors.white, fontSize: 13),
                  decoration: InputDecoration(
                    hintText: 'Cari Nama Siswa atau NIS...',
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

          // Class Filter Chips Bar
          Container(
            height: 52,
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: [
                Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: const Text('Semua Kelas'),
                    selected: _selectedClassId == 0,
                    selectedColor: AppTheme.primaryColor,
                    labelStyle: TextStyle(
                      color: _selectedClassId == 0 ? Colors.white : Colors.black87,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                    onSelected: (selected) {
                      if (selected) {
                        setState(() {
                          _selectedClassId = 0;
                          _selectedClassName = 'Semua Kelas';
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
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(cname),
                      selected: isSelected,
                      selectedColor: AppTheme.primaryColor,
                      labelStyle: TextStyle(
                        color: isSelected ? Colors.white : Colors.black87,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
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

          // Summary Stats Pills Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: Colors.grey.shade100,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatPill('🟢 Hadir', stats['hadir'] ?? 0, Colors.green),
                _buildStatPill('🔵 Izin', stats['izin'] ?? 0, Colors.blue),
                _buildStatPill('🟠 Sakit', stats['sakit'] ?? 0, Colors.orange),
                _buildStatPill('🔴 Alpa', stats['alpa'] ?? 0, Colors.red),
              ],
            ),
          ),

          // Main Student List Grouped by Class
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filteredStudents.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.how_to_reg_rounded, size: 54, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            Text(
                              'Belum ada siswa terdaftar di mapel/kelas $_selectedClassName.',
                              style: TextStyle(color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      )
                    : ListView(
                        padding: const EdgeInsets.all(12),
                        children: groupedData.entries.map((entry) {
                          final className = entry.key;
                          final studentsInClass = entry.value;

                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Class Section Header
                              Container(
                                width: double.infinity,
                                margin: const EdgeInsets.only(top: 8, bottom: 8),
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                decoration: BoxDecoration(
                                  color: Colors.blue.shade50,
                                  borderRadius: BorderRadius.circular(10),
                                  border: Border.all(color: Colors.blue.shade100),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.school_rounded, size: 18, color: AppTheme.primaryColor),
                                    const SizedBox(width: 8),
                                    Text(
                                      'Kelas: $className',
                                      style: const TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.primaryColor,
                                      ),
                                    ),
                                    const Spacer(),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: AppTheme.primaryColor,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        '${studentsInClass.length} Terdaftar',
                                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              // Student Cards
                              ...studentsInClass.map((s) {
                                final sid = int.parse((s['id'] ?? 0).toString());
                                final name = (s['nama_lengkap'] ?? 'Siswa').toString();
                                final nis = (s['nis'] ?? '-').toString();
                                final enrolledMapel = (s['nama_mapel_enrolled'] ?? '').toString();
                                final currentStatus = _records[sid] ?? 'Hadir';

                                return Card(
                                  margin: const EdgeInsets.only(bottom: 8),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                  elevation: 1.5,
                                  child: Padding(
                                    padding: const EdgeInsets.all(12),
                                    child: Column(
                                      children: [
                                        Row(
                                          children: [
                                            CircleAvatar(
                                              backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.15),
                                              child: Text(
                                                name.isNotEmpty ? name[0] : 'S',
                                                style: const TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.bold),
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
                                                    'NIS: $nis • Rombel $className',
                                                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                                  ),
                                                  if (enrolledMapel.isNotEmpty) ...[
                                                    const SizedBox(height: 2),
                                                    Text(
                                                      '📘 Mapel: $enrolledMapel',
                                                      style: TextStyle(fontSize: 10, color: Colors.blue.shade700, fontWeight: FontWeight.w600),
                                                    ),
                                                  ],
                                                ],
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 10),

                                        // Status Toggle Pills Button Row
                                        Row(
                                          children: [
                                            _buildStatusToggleChip(sid, 'Hadir', Colors.green, currentStatus),
                                            const SizedBox(width: 6),
                                            _buildStatusToggleChip(sid, 'Izin', Colors.blue, currentStatus),
                                            const SizedBox(width: 6),
                                            _buildStatusToggleChip(sid, 'Sakit', Colors.orange, currentStatus),
                                            const SizedBox(width: 6),
                                            _buildStatusToggleChip(sid, 'Alpa', Colors.red, currentStatus),
                                          ],
                                        ),
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

          // Bottom Sticky Save Button Bar
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.08),
                  blurRadius: 10,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _isSaving ? null : _saveAbsensi,
                icon: _isSaving
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Icon(Icons.save_rounded, color: Colors.white),
                label: Text(
                  _isSaving
                      ? 'Menyimpan Presensi...'
                      : 'Simpan Presensi Kelas (${_filteredStudents.length} Siswa Terdaftar)',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 3,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatPill(String label, int count, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        '$label: $count',
        style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold),
      ),
    );
  }

  Widget _buildStatusToggleChip(int sid, String status, Color color, String currentStatus) {
    final bool isSelected = currentStatus == status;

    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() {
            _records[sid] = status;
          });
        },
        borderRadius: BorderRadius.circular(8),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(vertical: 7),
          decoration: BoxDecoration(
            color: isSelected ? color : color.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: isSelected ? color : color.withValues(alpha: 0.3),
              width: isSelected ? 1.5 : 1,
            ),
          ),
          child: Text(
            status,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: isSelected ? Colors.white : color,
              fontSize: 11,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
            ),
          ),
        ),
      ),
    );
  }
}
