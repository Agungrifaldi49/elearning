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
  List<dynamic> _jadwalList = [];
  List<dynamic> _classes = [];
  List<dynamic> _allStudents = [];
  List<dynamic> _filteredStudents = [];
  
  final Map<int, String> _records = {}; // [siswa_id => status]
  final Map<int, String> _keterangan = {}; // [siswa_id => keterangan]
  final Map<int, TextEditingController> _ketControllers = {};

  int _selectedJadwalId = 0;
  int _selectedClassId = 0; // 0 = Semua Kelas
  String _selectedClassName = 'Semua Kelas';
  String _selectedTanggal = DateTime.now().toString().split(' ')[0];
  
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
    for (var c in _ketControllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _loadAbsensiData() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final data = await Provider.of<GuruProvider>(context, listen: false).fetchAbsensiData(
      user.id,
      jadwalId: _selectedJadwalId,
      kelasId: _selectedClassId,
      tanggal: _selectedTanggal,
    );

    if (mounted) {
      setState(() {
        _jadwalList = data['jadwal_list'] as List? ?? [];
        if (_selectedJadwalId == 0 && _jadwalList.isNotEmpty) {
          _selectedJadwalId = int.parse((data['selected_jadwal_id'] ?? _jadwalList[0]['id']).toString());
        }
        if (data['tanggal'] != null && (data['tanggal'] as String).isNotEmpty) {
          _selectedTanggal = data['tanggal'] as String;
        }

        _classes = data['classes'] as List? ?? [];
        _allStudents = data['students'] as List? ?? [];
        
        // Initialize status and keterangan from DB
        _records.clear();
        _keterangan.clear();
        for (var c in _ketControllers.values) {
          c.dispose();
        }
        _ketControllers.clear();

        for (var s in _allStudents) {
          final sid = int.parse((s['siswa_id'] ?? s['id'] ?? 0).toString());
          final dbStatus = (s['status'] ?? '').toString();
          final dbKet = (s['keterangan'] ?? '').toString();
          final isQrScan = (s['qr_code'] != null && s['qr_code'].toString().isNotEmpty);

          if (dbStatus.isNotEmpty) {
            _records[sid] = dbStatus;
          } else if (isQrScan) {
            _records[sid] = 'Hadir';
          } else {
            _records[sid] = 'Hadir';
          }

          _keterangan[sid] = dbKet;
          _ketControllers[sid] = TextEditingController(text: dbKet);
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
        final nis = (s['nis'] ?? s['nisn'] ?? '').toString().toLowerCase();
        return name.contains(query) || nis.contains(query);
      }).toList();
    }

    setState(() {
      _filteredStudents = list;
    });
  }

  void _setSemuaHadir() {
    setState(() {
      for (var s in _filteredStudents) {
        final sid = int.parse((s['siswa_id'] ?? s['id'] ?? 0).toString());
        _records[sid] = 'Hadir';
      }
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Seluruh siswa pada daftar telah di-set status Hadir!'),
        duration: Duration(seconds: 2),
      ),
    );
  }

  Future<void> _pickDate() async {
    final initialDate = DateTime.tryParse(_selectedTanggal) ?? DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime(2024),
      lastDate: DateTime(2030),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppTheme.primaryColor,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      final formatted = picked.toString().split(' ')[0];
      if (formatted != _selectedTanggal) {
        setState(() {
          _selectedTanggal = formatted;
        });
        _loadAbsensiData();
      }
    }
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

    // Sync controllers to _keterangan map
    _ketControllers.forEach((sid, controller) {
      _keterangan[sid] = controller.text;
    });

    final ok = await Provider.of<GuruProvider>(context, listen: false).saveAbsensi(
      user.id,
      _selectedJadwalId,
      _selectedTanggal,
      _records,
      _keterangan,
    );

    if (!mounted) return;

    setState(() => _isSaving = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ok
            ? 'Presensi ${_records.length} siswa terdaftar berhasil disimpan ke database!'
            : 'Gagal menyimpan presensi. Periksa koneksi server.'),
        backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );

    if (ok) {
      _loadAbsensiData();
    }
  }

  // Count Live Status Summaries
  Map<String, int> _getSummaryStats() {
    int hadir = 0, izin = 0, sakit = 0, alpa = 0;
    for (var s in _filteredStudents) {
      final sid = int.parse((s['siswa_id'] ?? s['id'] ?? 0).toString());
      final st = _records[sid] ?? 'Hadir';
      if (st.toLowerCase() == 'hadir') {
        hadir++;
      } else if (st.toLowerCase() == 'izin') {
        izin++;
      } else if (st.toLowerCase() == 'sakit') {
        sakit++;
      } else {
        alpa++;
      }
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
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Muat Ulang Data DB',
            onPressed: _loadAbsensiData,
          ),
        ],
      ),
      body: Column(
        children: [
          // Header Hero Card with Schedule & Date Selection
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
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Expanded(
                      child: Text(
                        '📋 Presensi Siswa KBM & QR Scan',
                        style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    ),
                    InkWell(
                      onTap: _pickDate,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white24,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.calendar_month_rounded, color: Colors.white, size: 16),
                            const SizedBox(width: 6),
                            Text(
                              _selectedTanggal,
                              style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                const Text(
                  'Data presensi terhubung langsung dengan Database & Hasil Scan QR Code',
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
                const SizedBox(height: 12),

                // Jadwal Mengajar Dropdown Filter
                if (_jadwalList.isNotEmpty) ...[
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.white30),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int>(
                        value: _selectedJadwalId > 0 ? _selectedJadwalId : null,
                        dropdownColor: AppTheme.primaryColor,
                        icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white),
                        isExpanded: true,
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        items: _jadwalList.map((j) {
                          final jid = int.parse((j['id'] ?? 0).toString());
                          final hari = (j['hari'] ?? '').toString();
                          final mapel = (j['nama_mapel'] ?? 'Mapel').toString();
                          final kelas = (j['nama_kelas'] ?? 'Kelas').toString();
                          final jamMulai = (j['jam_mulai'] ?? '').toString();
                          final jamStr = jamMulai.length >= 5 ? jamMulai.substring(0, 5) : jamMulai;

                          return DropdownMenuItem<int>(
                            value: jid,
                            child: Text(
                              '$hari | $mapel - $kelas ($jamStr)',
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            setState(() {
                              _selectedJadwalId = val;
                            });
                            _loadAbsensiData();
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

          // Class Filter Chips & Set Semua Hadir Row
          Container(
            height: 48,
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    children: [
                      Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: ChoiceChip(
                          label: const Text('Semua Rombel'),
                          selected: _selectedClassId == 0,
                          selectedColor: AppTheme.primaryColor,
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
                            selectedColor: AppTheme.primaryColor,
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

                // Set Semua Hadir Button
                Padding(
                  padding: const EdgeInsets.only(right: 12),
                  child: TextButton.icon(
                    onPressed: _filteredStudents.isEmpty ? null : _setSemuaHadir,
                    icon: const Icon(Icons.done_all_rounded, size: 16, color: Colors.green),
                    label: const Text(
                      'Semua Hadir',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.green),
                    ),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      backgroundColor: Colors.green.shade50,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Summary Stats Pills Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
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
                              'Belum ada siswa terdaftar di jadwal/kelas $_selectedClassName.',
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
                                        '${studentsInClass.length} Siswa',
                                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              // Student Cards
                              ...studentsInClass.map((s) {
                                final sid = int.parse((s['siswa_id'] ?? s['id'] ?? 0).toString());
                                final name = (s['nama_lengkap'] ?? 'Siswa').toString();
                                final nis = (s['nis'] ?? s['nisn'] ?? '-').toString();
                                final qrCode = (s['qr_code'] ?? '').toString();
                                final waktuHadir = (s['waktu_hadir'] ?? s['waktu_masuk'] ?? '').toString();
                                final isQrScan = qrCode.isNotEmpty;
                                final currentStatus = _records[sid] ?? 'Hadir';

                                return Card(
                                  margin: const EdgeInsets.only(bottom: 8),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                  elevation: 1.5,
                                  child: Padding(
                                    padding: const EdgeInsets.all(12),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
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
                                                  Row(
                                                    children: [
                                                      Expanded(
                                                        child: Text(
                                                          name,
                                                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                                                        ),
                                                      ),
                                                      if (isQrScan) ...[
                                                        Container(
                                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                          decoration: BoxDecoration(
                                                            color: Colors.green.shade50,
                                                            border: Border.all(color: Colors.green.shade300),
                                                            borderRadius: BorderRadius.circular(8),
                                                          ),
                                                          child: Row(
                                                            mainAxisSize: MainAxisSize.min,
                                                            children: [
                                                              const Icon(Icons.qr_code_scanner, size: 12, color: Colors.green),
                                                              const SizedBox(width: 3),
                                                              Text(
                                                                waktuHadir.length >= 16 ? waktuHadir.substring(11, 16) : 'Scan QR',
                                                                style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.green.shade800),
                                                              ),
                                                            ],
                                                          ),
                                                        ),
                                                      ],
                                                    ],
                                                  ),
                                                  const SizedBox(height: 2),
                                                  Text(
                                                    'NIS: $nis • Rombel $className',
                                                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                                  ),
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

                                        const SizedBox(height: 8),

                                        // Optional Keterangan TextField
                                        SizedBox(
                                          height: 36,
                                          child: TextField(
                                            controller: _ketControllers[sid],
                                            onChanged: (val) {
                                              _keterangan[sid] = val;
                                            },
                                            style: const TextStyle(fontSize: 11),
                                            decoration: InputDecoration(
                                              hintText: 'Keterangan opsional (misal: Sakit flu, Izin urusan keluarga)...',
                                              hintStyle: TextStyle(fontSize: 10, color: Colors.grey.shade500),
                                              filled: true,
                                              fillColor: Colors.grey.shade50,
                                              border: OutlineInputBorder(
                                                borderRadius: BorderRadius.circular(8),
                                                borderSide: BorderSide(color: Colors.grey.shade300),
                                              ),
                                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                            ),
                                          ),
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
                      : 'Simpan Presensi Kelas (${_filteredStudents.length} Siswa)',
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
    final bool isSelected = currentStatus.toLowerCase() == status.toLowerCase();

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
