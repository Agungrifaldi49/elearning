import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruInputAbsensiScreen extends StatefulWidget {
  final int? initialMapelId;
  const GuruInputAbsensiScreen({super.key, this.initialMapelId});

  @override
  State<GuruInputAbsensiScreen> createState() => _GuruInputAbsensiScreenState();
}

class _GuruInputAbsensiScreenState extends State<GuruInputAbsensiScreen> {
  bool _isLoading = true;
  bool _isSaving = false;

  List<dynamic> _mapelList = [];
  int _selectedMapelId = 0;
  DateTime _selectedDate = DateTime.now();

  List<dynamic> _students = [];
  List<dynamic> _filteredStudents = [];
  final Map<int, String> _absensiMap = {};
  final Map<int, String> _keteranganMap = {};

  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    if (widget.initialMapelId != null && widget.initialMapelId! > 0) {
      _selectedMapelId = widget.initialMapelId!;
    }
    _loadData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  String get _formattedDate {
    return _selectedDate.toString().substring(0, 10);
  }

  Future<void> _loadData() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final data = await Provider.of<GuruProvider>(context, listen: false).fetchInputAbsensiData(
      user.id,
      mapelId: _selectedMapelId,
      tanggal: _formattedDate,
    );

    if (mounted) {
      final mList = data['mapel_list'] as List? ?? [];
      int selId = _selectedMapelId;
      if (selId <= 0 && mList.isNotEmpty) {
        selId = int.parse((mList[0]['id'] ?? 0).toString());
      }
      final sList = data['students'] as List? ?? [];

      _absensiMap.clear();
      _keteranganMap.clear();

      for (var s in sList) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        final st = (s['status_absensi'] ?? 'Belum Absen').toString();
        _absensiMap[sid] = (st != 'Belum Absen') ? st : 'Hadir';
        if (s['keterangan'] != null) {
          _keteranganMap[sid] = s['keterangan'].toString();
        }
      }

      setState(() {
        _mapelList = mList;
        _selectedMapelId = selId;
        _students = sList;
        _applySearchFilter();
        _isLoading = false;
      });
    }
  }

  void _applySearchFilter() {
    final query = _searchController.text.trim().toLowerCase();
    if (query.isEmpty) {
      setState(() => _filteredStudents = List.from(_students));
      return;
    }

    setState(() {
      _filteredStudents = _students.where((s) {
        final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
        final nis = (s['nis'] ?? s['nisn'] ?? '').toString().toLowerCase();
        final kelas = (s['nama_kelas'] ?? '').toString().toLowerCase();
        return name.contains(query) || nis.contains(query) || kelas.contains(query);
      }).toList();
    });
  }

  void _markAllStatus(String status) {
    setState(() {
      for (var s in _filteredStudents) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        _absensiMap[sid] = status;
      }
    });
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
      _loadData();
    }
  }

  Future<void> _saveAbsensi() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null || _selectedMapelId <= 0) return;

    if (_absensiMap.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada data siswa terdaftar yang dapat dipresensi.')),
      );
      return;
    }

    setState(() => _isSaving = true);

    final ok = await Provider.of<GuruProvider>(context, listen: false).saveManualAttendance(
      user.id,
      _selectedMapelId,
      _formattedDate,
      _absensiMap,
      keteranganMap: _keteranganMap,
    );

    if (mounted) {
      setState(() => _isSaving = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ok ? 'Presensi manual berhasil disimpan ke database!' : 'Gagal menyimpan presensi manual.'),
          backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
        ),
      );
      if (ok) {
        _loadData();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final validMapelVal = (_selectedMapelId > 0 && _mapelList.any((m) => int.parse((m['id'] ?? 0).toString()) == _selectedMapelId))
        ? _selectedMapelId
        : (_mapelList.isNotEmpty ? int.parse((_mapelList[0]['id'] ?? 0).toString()) : 0);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Input Presensi Manual', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      body: Column(
        children: [
          // Header Controls Box
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppTheme.primaryColor,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(24)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Mapel Selection Dropdown
                const Text('Pilih Mata Pelajaran Pengampuan:', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<int>(
                      value: validMapelVal > 0 ? validMapelVal : null,
                      isExpanded: true,
                      hint: const Text('Pilih Mata Pelajaran'),
                      icon: Icon(Icons.keyboard_arrow_down_rounded, color: AppTheme.primaryColor),
                      items: _mapelList.map((m) {
                        final mid = int.parse((m['id'] ?? 0).toString());
                        final mname = (m['nama_mapel'] ?? 'Mapel').toString();
                        return DropdownMenuItem<int>(
                          value: mid,
                          child: Text(mname, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedMapelId = val;
                          });
                          _loadData();
                        }
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Date & Quick Actions Row
                Row(
                  children: [
                    // Date Picker Button
                    Expanded(
                      child: InkWell(
                        onTap: _selectDate,
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.18),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white30),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.calendar_today_rounded, size: 16, color: Colors.white),
                              const SizedBox(width: 8),
                              Text(
                                _formattedDate,
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Mark All Hadir Button
                    ElevatedButton.icon(
                      onPressed: () => _markAllStatus('Hadir'),
                      icon: const Icon(Icons.done_all_rounded, size: 16),
                      label: const Text('Semua Hadir', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green.shade700,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Search Box & Count Indicator
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: TextField(
                      controller: _searchController,
                      onChanged: (_) => _applySearchFilter(),
                      style: const TextStyle(fontSize: 12),
                      decoration: const InputDecoration(
                        hintText: 'Cari nama siswa terdaftar...',
                        prefixIcon: Icon(Icons.search_rounded, size: 18),
                        border: InputBorder.none,
                        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.blue.shade200),
                  ),
                  child: Text(
                    '${_filteredStudents.length} Siswa',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.blue.shade900),
                  ),
                ),
              ],
            ),
          ),

          // Enrolled Students Attendance List
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadData,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredStudents.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.person_off_rounded, size: 48, color: Colors.grey.shade400),
                              const SizedBox(height: 10),
                              Text(
                                'Belum ada siswa terdaftar di mapel ini.',
                                style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 4, 16, 80),
                          itemCount: _filteredStudents.length,
                          itemBuilder: (context, index) {
                            final s = _filteredStudents[index];
                            final sid = int.parse((s['siswa_id'] ?? 0).toString());
                            final name = (s['nama_lengkap'] ?? 'Siswa').toString();
                            final nis = (s['nis'] ?? s['nisn'] ?? '-').toString();
                            final className = (s['nama_kelas'] ?? 'Tanpa Kelas').toString();
                            final jurusanName = (s['nama_jurusan'] ?? '-').toString();
                            final currentStatus = _absensiMap[sid] ?? 'Hadir';

                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
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
                                    children: [
                                      CircleAvatar(
                                        radius: 18,
                                        backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
                                        child: Text(
                                          name.isNotEmpty ? name[0].toUpperCase() : 'S',
                                          style: TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.bold, fontSize: 14),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              name,
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              'NIS: $nis • $className ${jurusanName != '-' ? '($jurusanName)' : ''}',
                                              style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Status Radio Segment Buttons (Hadir, Izin, Sakit, Alpha)
                                  Row(
                                    children: [
                                      _buildStatusPill(sid, 'Hadir', 'H', Colors.green, currentStatus),
                                      const SizedBox(width: 6),
                                      _buildStatusPill(sid, 'Izin', 'I', Colors.blue, currentStatus),
                                      const SizedBox(width: 6),
                                      _buildStatusPill(sid, 'Sakit', 'S', Colors.orange, currentStatus),
                                      const SizedBox(width: 6),
                                      _buildStatusPill(sid, 'Alpha', 'A', Colors.red, currentStatus),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _isSaving ? null : _saveAbsensi,
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        icon: _isSaving
            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
            : const Icon(Icons.save_rounded),
        label: Text(
          _isSaving ? 'Menyimpan...' : 'Simpan Presensi Manual',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  Widget _buildStatusPill(int sid, String status, String code, Color color, String currentStatus) {
    final isSelected = currentStatus.toLowerCase() == status.toLowerCase();

    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() {
            _absensiMap[sid] = status;
          });
        },
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 7),
          decoration: BoxDecoration(
            color: isSelected ? color : color.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: isSelected ? color : color.withValues(alpha: 0.3)),
          ),
          child: Text(
            '$code - $status',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.bold,
              color: isSelected ? Colors.white : color.withValues(alpha: 0.9),
            ),
          ),
        ),
      ),
    );
  }
}
