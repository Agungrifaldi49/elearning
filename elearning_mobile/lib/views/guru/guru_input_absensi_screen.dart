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
  String _selectedKategori = 'masuk'; // 'masuk' or 'pulang'
  String _activeFilter = 'all'; // 'all', 'belum_masuk', 'belum_pulang'

  Map<String, dynamic>? _scheduleInfo;

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

  bool get _isToday {
    final nowStr = DateTime.now().toString().substring(0, 10);
    return _formattedDate == nowStr;
  }

  bool get _canAbsenMasuk {
    if (!_isToday) return true;
    return _scheduleInfo?['can_absen_masuk'] != false;
  }

  bool get _canAbsenPulang {
    if (!_isToday) return true;
    return _scheduleInfo?['can_absen_pulang'] != false;
  }

  bool _studentHasMasuk(dynamic s) {
    final wMasuk = (s['waktu_masuk'] ?? s['waktu_hadir'] ?? '').toString();
    final st = (s['status_absensi'] ?? '').toString();
    return wMasuk.isNotEmpty && st.toLowerCase() == 'hadir';
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
      final sched = data['schedule'] != null ? Map<String, dynamic>.from(data['schedule']) : null;

      _absensiMap.clear();
      _keteranganMap.clear();

      for (var s in sList) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        var st = (s['status_absensi'] ?? 'Belum Absen').toString();
        if (st.toLowerCase() == 'alpha') st = 'Alpa';
        if (_selectedKategori == 'pulang') {
          if (_studentHasMasuk(s)) {
            _absensiMap[sid] = 'Hadir';
          } else {
            _absensiMap[sid] = '';
          }
        } else {
          _absensiMap[sid] = (st != 'Belum Absen') ? st : 'Hadir';
        }
        if (s['keterangan'] != null) {
          _keteranganMap[sid] = s['keterangan'].toString();
        }
      }

      setState(() {
        _mapelList = mList;
        _selectedMapelId = selId;
        _students = sList;
        _scheduleInfo = sched;
        _applySearchFilter();
        _isLoading = false;
      });
    }
  }

  void _switchKategori(String kategori) {
    if (_selectedKategori == kategori) return;
    setState(() {
      _selectedKategori = kategori;
      for (var s in _students) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        if (_selectedKategori == 'pulang') {
          if (_studentHasMasuk(s)) {
            _absensiMap[sid] = 'Hadir';
          } else {
            _absensiMap[sid] = '';
          }
        } else {
          var st = (s['status_absensi'] ?? 'Belum Absen').toString();
          if (st.toLowerCase() == 'alpha') st = 'Alpa';
          _absensiMap[sid] = (st != 'Belum Absen') ? st : 'Hadir';
        }
      }
      _applySearchFilter();
    });
  }

  bool _isAbsentStatus(String st) {
    final s = st.toLowerCase().trim();
    return s == 'izin' || s == 'sakit' || s == 'alpha' || s == 'alpa';
  }

  int get _countBelumMasuk {
    return _students.where((s) {
      final sid = int.parse((s['siswa_id'] ?? 0).toString());
      final currentSt = _absensiMap[sid] ?? (s['status_absensi'] ?? '').toString();
      if (_isAbsentStatus(currentSt)) return false;
      final wMasuk = (s['waktu_masuk'] ?? s['waktu_hadir'] ?? '').toString();
      final status = (s['status_absensi'] ?? '').toString();
      return wMasuk.isEmpty || status == 'Belum Absen';
    }).length;
  }

  int get _countBelumPulang {
    return _students.where((s) {
      if (!_studentHasMasuk(s)) return false;
      final wPulang = (s['waktu_pulang'] ?? '').toString();
      return wPulang.isEmpty;
    }).length;
  }

  void _applySearchFilter() {
    final query = _searchController.text.trim().toLowerCase();
    
    setState(() {
      _filteredStudents = _students.where((s) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        final currentSt = _absensiMap[sid] ?? (s['status_absensi'] ?? '').toString();
        final name = (s['nama_lengkap'] ?? '').toString().toLowerCase();
        final nis = (s['nis'] ?? s['nisn'] ?? '').toString().toLowerCase();
        final kelas = (s['nama_kelas'] ?? '').toString().toLowerCase();
        final matchesQuery = query.isEmpty || name.contains(query) || nis.contains(query) || kelas.contains(query);

        if (!matchesQuery) return false;

        if (_activeFilter == 'belum_masuk') {
          if (_isAbsentStatus(currentSt)) return false;
          final wMasuk = (s['waktu_masuk'] ?? s['waktu_hadir'] ?? '').toString();
          final status = (s['status_absensi'] ?? '').toString();
          return wMasuk.isEmpty || status == 'Belum Absen';
        } else if (_activeFilter == 'belum_pulang') {
          if (!_studentHasMasuk(s)) return false;
          final wPulang = (s['waktu_pulang'] ?? '').toString();
          return wPulang.isEmpty;
        }

        return true;
      }).toList();
    });
  }

  void _markAllStatus(String status) {
    setState(() {
      int count = 0;
      for (var s in _filteredStudents) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        if (_selectedKategori == 'pulang') {
          if (_studentHasMasuk(s)) {
            _absensiMap[sid] = status;
            count++;
          }
        } else {
          _absensiMap[sid] = status;
          count++;
        }
      }
      if (_selectedKategori == 'pulang') {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(count > 0
                ? '$count siswa yang sudah absen masuk ditandai Hadir Pulang.'
                : 'Tidak ada siswa yang sudah absen masuk untuk ditandai pulang.'),
            duration: const Duration(seconds: 2),
          ),
        );
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

  void _showScheduleWarningDialog({required String title, required String message}) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 24),
            const SizedBox(width: 8),
            Expanded(child: Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold))),
          ],
        ),
        content: Text(message, style: const TextStyle(fontSize: 13, height: 1.4)),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.of(ctx).pop(),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Mengerti', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Future<void> _saveAbsensi() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null || _selectedMapelId <= 0) return;

    // 1. Cek validasi jadwal jam operasional sekolah/KBM
    if (_selectedKategori == 'pulang' && !_canAbsenPulang) {
      final jPulang = _scheduleInfo?['jam_pulang_mulai'] ?? '15:00';
      final cTime = _scheduleInfo?['current_time'] ?? '';
      _showScheduleWarningDialog(
        title: 'Belum Memasuki Jam Pulang',
        message: 'Presensi Pulang hanya dapat dilakukan mulai pukul $jPulang WIB sesuai jadwal KBM/sekolah.\n\nWaktu saat ini: ${cTime.isNotEmpty ? "$cTime WIB" : "Belum jam pulang"}.',
      );
      return;
    }

    if (_selectedKategori == 'masuk' && !_canAbsenMasuk) {
      final jMasuk = _scheduleInfo?['jam_masuk_mulai'] ?? '06:00';
      final cTime = _scheduleInfo?['current_time'] ?? '';
      _showScheduleWarningDialog(
        title: 'Belum Memasuki Jam Masuk',
        message: 'Presensi Masuk baru dibuka mulai pukul $jMasuk WIB.\n\nWaktu saat ini: ${cTime.isNotEmpty ? "$cTime WIB" : "Belum jam masuk"}.',
      );
      return;
    }

    // 2. Cek validasi data absen pulang (harus sudah absen masuk)
    final Map<int, String> payloadMap = {};
    if (_selectedKategori == 'pulang') {
      for (var s in _students) {
        final sid = int.parse((s['siswa_id'] ?? 0).toString());
        final status = _absensiMap[sid] ?? '';
        if (status.isNotEmpty && _studentHasMasuk(s)) {
          payloadMap[sid] = status;
        }
      }

      if (payloadMap.isEmpty) {
        _showScheduleWarningDialog(
          title: 'Tidak Ada Siswa Yang Memenuhi Syarat',
          message: 'Seluruh siswa yang dipilih belum melakukan Absen Masuk.\n\nSecara sistem, siswa WAJIB melakukan Absen Masuk terlebih dahulu sebelum bisa melakukan Absen Pulang!',
        );
        return;
      }
    } else {
      payloadMap.addAll(_absensiMap);
    }

    if (payloadMap.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada data siswa terdaftar yang dapat dipresensi.')),
      );
      return;
    }

    setState(() => _isSaving = true);

    final res = await Provider.of<GuruProvider>(context, listen: false).saveManualAttendance(
      user.id,
      _selectedMapelId,
      _formattedDate,
      payloadMap,
      keteranganMap: _keteranganMap,
      kategori: _selectedKategori,
    );

    if (mounted) {
      setState(() => _isSaving = false);
      final ok = res['success'] == true;
      final katLabel = _selectedKategori == 'masuk' ? 'MASUK' : 'PULANG';
      final msg = (res['message'] != null && res['message'].toString().isNotEmpty)
          ? res['message'].toString()
          : (ok ? 'Presensi manual ($katLabel) berhasil disimpan ke database!' : 'Gagal menyimpan presensi manual.');

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
          duration: const Duration(seconds: 4),
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

    final numBelumMasuk = _countBelumMasuk;
    final numBelumPulang = _countBelumPulang;

    final jamMasukMulai = (_scheduleInfo?['jam_masuk_mulai'] ?? '06:00').toString();
    final jamMasukBatas = (_scheduleInfo?['jam_masuk_batas'] ?? '07:30').toString();
    final jamPulangMulai = (_scheduleInfo?['jam_pulang_mulai'] ?? '15:00').toString();
    final currentTime = (_scheduleInfo?['current_time'] ?? '').toString();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Input Presensi Manual', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 17)),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: AppTheme.guruGradient,
          ),
        ),
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
            decoration: const BoxDecoration(
              gradient: AppTheme.guruGradient,
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
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
                      icon: const Icon(Icons.keyboard_arrow_down_rounded, color: AppTheme.primaryColor),
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

                // Schedule Info Chip
                Container(
                  margin: const EdgeInsets.only(top: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.16),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white30),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.schedule_rounded, size: 14, color: Colors.white),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Jadwal KBM: Masuk $jamMasukMulai - $jamMasukBatas WIB • Pulang Mulai $jamPulangMulai WIB',
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),

                // Kategori Selector Segmented Toggle (Absen Masuk vs Absen Pulang)
                Container(
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  padding: const EdgeInsets.all(4),
                  child: Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () => _switchKategori('masuk'),
                          borderRadius: BorderRadius.circular(10),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            decoration: BoxDecoration(
                              color: _selectedKategori == 'masuk' ? Colors.amber.shade600 : Colors.transparent,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.sunny, size: 16, color: _selectedKategori == 'masuk' ? Colors.white : Colors.white70),
                                const SizedBox(width: 6),
                                Text(
                                  '☀️ Absen Masuk',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: _selectedKategori == 'masuk' ? Colors.white : Colors.white70,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        child: InkWell(
                          onTap: () => _switchKategori('pulang'),
                          borderRadius: BorderRadius.circular(10),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            decoration: BoxDecoration(
                              color: _selectedKategori == 'pulang' ? Colors.indigo.shade600 : Colors.transparent,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.nights_stay_rounded, size: 16, color: _selectedKategori == 'pulang' ? Colors.white : Colors.white70),
                                const SizedBox(width: 6),
                                Text(
                                  '🌙 Absen Pulang',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: _selectedKategori == 'pulang' ? Colors.white : Colors.white70,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
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

          // Schedule Alert Banner (Jika belum masuk jam masuk atau belum masuk jam pulang)
          if (!_isLoading && _isToday) ...[
            if (_selectedKategori == 'pulang' && !_canAbsenPulang) ...[
              Container(
                margin: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.red.shade300),
                ),
                child: Row(
                  children: [
                    Icon(Icons.lock_clock_rounded, color: Colors.red.shade800, size: 24),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '⛔ Belum Memasuki Jadwal Jam Pulang',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.red.shade900),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Presensi pulang baru dapat disimpan mulai pukul $jamPulangMulai WIB (Waktu saat ini: ${currentTime.isNotEmpty ? "$currentTime WIB" : "sekarang"}). Sesuai jadwal resmi, belum waktunya siswa pulang.',
                            style: TextStyle(fontSize: 11, color: Colors.red.shade800),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ] else if (_selectedKategori == 'masuk' && !_canAbsenMasuk) ...[
              Container(
                margin: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.orange.shade50,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.orange.shade300),
                ),
                child: Row(
                  children: [
                    Icon(Icons.lock_clock_rounded, color: Colors.orange.shade800, size: 24),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '⏰ Belum Memasuki Jadwal Jam Masuk',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.orange.shade900),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Presensi masuk baru dibuka mulai pukul $jamMasukMulai WIB (Waktu saat ini: ${currentTime.isNotEmpty ? "$currentTime WIB" : "sekarang"}).',
                            style: TextStyle(fontSize: 11, color: Colors.orange.shade900),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],

          // Smart Reminder Banner (Pengingat Belum Absen Masuk & Pulang)
          if (!_isLoading && _students.isNotEmpty && (numBelumMasuk > 0 || numBelumPulang > 0)) ...[
            Container(
              margin: const EdgeInsets.fromLTRB(16, 12, 16, 0),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.amber.shade300),
              ),
              child: Row(
                children: [
                  Icon(Icons.warning_amber_rounded, color: Colors.amber.shade900, size: 22),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '🔔 Pengingat Presensi Siswa',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.amber.shade900),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '$numBelumMasuk siswa belum Absen Masuk • $numBelumPulang siswa hadir belum Absen Pulang hari ini.',
                          style: TextStyle(fontSize: 11, color: Colors.amber.shade900),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],

          // Filter Chips Row & Search Box
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 8),
            child: Column(
              children: [
                // Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('all', 'Semua (${_students.length})', Icons.people_alt_rounded, Colors.blue),
                      const SizedBox(width: 8),
                      _buildFilterChip('belum_masuk', '☀️ Belum Masuk ($numBelumMasuk)', Icons.sunny, Colors.orange),
                      const SizedBox(width: 8),
                      _buildFilterChip('belum_pulang', '🌙 Siap Pulang ($numBelumPulang)', Icons.nights_stay_rounded, Colors.indigo),
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // Search Box
                Row(
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
                                'Belum ada data siswa sesuai filter ini.',
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

                            final isQrScanned = (s['is_qr_scanned'] == 1 || s['is_qr_scanned'] == '1' || s['is_qr_scanned'] == true);
                            final waktuMasukRaw = (s['waktu_masuk'] ?? s['waktu_hadir'] ?? '').toString();
                            final waktuPulangRaw = (s['waktu_pulang'] ?? '').toString();

                            String jamMasukStr = 'Belum Absen';
                            if (waktuMasukRaw.length >= 16) {
                              jamMasukStr = '${waktuMasukRaw.substring(11, 16)} WIB';
                            } else if (waktuMasukRaw.isNotEmpty) {
                              jamMasukStr = waktuMasukRaw;
                            }

                            String jamPulangStr = 'Belum Pulang';
                            if (waktuPulangRaw.length >= 16) {
                              jamPulangStr = '${waktuPulangRaw.substring(11, 16)} WIB';
                            } else if (waktuPulangRaw.isNotEmpty) {
                              jamPulangStr = waktuPulangRaw;
                            }

                            final hasMasuk = jamMasukStr != 'Belum Absen';
                            final hasPulang = jamPulangStr != 'Belum Pulang';
                            final isAbsent = _isAbsentStatus(currentStatus);
                            final isAlpa = currentStatus.toLowerCase() == 'alpa' || currentStatus.toLowerCase() == 'alpha';
                            final isCardQrGreen = isQrScanned && !isAbsent;

                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: isCardQrGreen ? Colors.green.shade50.withValues(alpha: 0.4) : Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                  color: isCardQrGreen
                                      ? Colors.green.shade300
                                      : (isAbsent ? (isAlpa ? Colors.red.shade300 : Colors.orange.shade300) : Colors.grey.shade200),
                                  width: (isCardQrGreen || isAbsent) ? 1.5 : 1.0,
                                ),
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
                                        backgroundColor: isCardQrGreen
                                            ? Colors.green.shade100
                                            : (isAbsent
                                                ? (isAlpa ? Colors.red.shade50 : Colors.orange.shade50)
                                                : AppTheme.primaryColor.withValues(alpha: 0.1)),
                                        child: Text(
                                          name.isNotEmpty ? name[0].toUpperCase() : 'S',
                                          style: TextStyle(
                                            color: isCardQrGreen
                                                ? Colors.green.shade800
                                                : (isAbsent
                                                    ? (isAlpa ? Colors.red.shade700 : Colors.orange.shade800)
                                                    : AppTheme.primaryColor),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Row(
                                              children: [
                                                Expanded(
                                                  child: Text(
                                                    name,
                                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                                if (isQrScanned) ...[
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                                    decoration: BoxDecoration(
                                                      color: isAbsent ? Colors.amber.shade50 : Colors.green.shade100,
                                                      borderRadius: BorderRadius.circular(6),
                                                      border: Border.all(
                                                        color: isAbsent ? Colors.amber.shade400 : Colors.green.shade400,
                                                        width: 0.8,
                                                      ),
                                                    ),
                                                    child: Row(
                                                      mainAxisSize: MainAxisSize.min,
                                                      children: [
                                                        Icon(
                                                          Icons.qr_code_2_rounded,
                                                          size: 12,
                                                          color: isAbsent ? Colors.amber.shade800 : Colors.green,
                                                        ),
                                                        const SizedBox(width: 3),
                                                        Text(
                                                          isAbsent ? 'Gerbang ($currentStatus)' : 'Scan QR',
                                                          style: TextStyle(
                                                            fontSize: 9.5,
                                                            fontWeight: FontWeight.bold,
                                                            color: isAbsent ? Colors.amber.shade900 : Colors.green.shade900,
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ],
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
                                  const SizedBox(height: 8),

                                  // Dual Status Badges (Masuk & Pulang Time Indicators)
                                  Row(
                                    children: [
                                      // Masuk Status Badge
                                      Expanded(
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                                          decoration: BoxDecoration(
                                            color: _isAbsentStatus(currentStatus)
                                                ? Colors.orange.shade50
                                                : (hasMasuk ? Colors.green.shade50 : Colors.amber.shade50),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(
                                              color: _isAbsentStatus(currentStatus)
                                                  ? Colors.orange.shade200
                                                  : (hasMasuk ? Colors.green.shade200 : Colors.amber.shade200),
                                            ),
                                          ),
                                          child: Row(
                                            children: [
                                              Icon(
                                                _isAbsentStatus(currentStatus) ? Icons.info_outline_rounded : Icons.sunny,
                                                size: 12,
                                                color: _isAbsentStatus(currentStatus)
                                                    ? Colors.orange.shade800
                                                    : (hasMasuk ? Colors.green.shade800 : Colors.amber.shade900),
                                              ),
                                              const SizedBox(width: 4),
                                              Expanded(
                                                child: Text(
                                                  _isAbsentStatus(currentStatus) ? 'Masuk: $currentStatus' : 'Masuk: $jamMasukStr',
                                                  style: TextStyle(
                                                    fontSize: 10,
                                                    fontWeight: FontWeight.bold,
                                                    color: _isAbsentStatus(currentStatus)
                                                        ? Colors.orange.shade900
                                                        : (hasMasuk ? Colors.green.shade900 : Colors.amber.shade900),
                                                  ),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      // Pulang Status Badge
                                      Expanded(
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                                          decoration: BoxDecoration(
                                            color: _isAbsentStatus(currentStatus)
                                                ? Colors.grey.shade100
                                                : (hasPulang ? Colors.indigo.shade50 : Colors.grey.shade100),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(
                                              color: _isAbsentStatus(currentStatus)
                                                  ? Colors.grey.shade300
                                                  : (hasPulang ? Colors.indigo.shade200 : Colors.grey.shade300),
                                            ),
                                          ),
                                          child: Row(
                                            children: [
                                              Icon(
                                                _isAbsentStatus(currentStatus) ? Icons.do_not_disturb_on_rounded : Icons.nights_stay_rounded,
                                                size: 12,
                                                color: _isAbsentStatus(currentStatus)
                                                    ? Colors.grey.shade600
                                                    : (hasPulang ? Colors.indigo.shade800 : Colors.grey.shade600),
                                              ),
                                              const SizedBox(width: 4),
                                              Expanded(
                                                child: Text(
                                                  _isAbsentStatus(currentStatus) ? 'Tidak Hadir ($currentStatus)' : 'Pulang: $jamPulangStr',
                                                  style: TextStyle(
                                                    fontSize: 10,
                                                    fontWeight: FontWeight.bold,
                                                    color: _isAbsentStatus(currentStatus)
                                                        ? Colors.grey.shade700
                                                        : (hasPulang ? Colors.indigo.shade900 : Colors.grey.shade700),
                                                  ),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Status Controls:
                                  if (_selectedKategori == 'pulang' && !_studentHasMasuk(s)) ...[
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                      decoration: BoxDecoration(
                                        color: Colors.amber.shade50,
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(color: Colors.amber.shade300),
                                      ),
                                      child: Row(
                                        children: [
                                          Icon(Icons.lock_clock_rounded, size: 15, color: Colors.amber.shade900),
                                          const SizedBox(width: 6),
                                          Expanded(
                                            child: Text(
                                              'Belum Absen Masuk • Tidak dapat Absen Pulang',
                                              style: TextStyle(
                                                fontSize: 10.5,
                                                fontWeight: FontWeight.bold,
                                                color: Colors.amber.shade900,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ] else if (_selectedKategori == 'pulang' && _studentHasMasuk(s)) ...[
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                      decoration: BoxDecoration(
                                        color: hasPulang ? Colors.green.shade50 : Colors.indigo.shade50,
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(
                                          color: hasPulang ? Colors.green.shade300 : Colors.indigo.shade300,
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(
                                            hasPulang ? Icons.check_circle_rounded : Icons.nights_stay_rounded,
                                            size: 15,
                                            color: hasPulang ? Colors.green.shade800 : Colors.indigo.shade800,
                                          ),
                                          const SizedBox(width: 6),
                                          Text(
                                            hasPulang ? 'Sudah Absen Pulang ($jamPulangStr)' : 'Siap Presensi Pulang (Hadir)',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              color: hasPulang ? Colors.green.shade900 : Colors.indigo.shade900,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ] else ...[
                                    // Status Radio Segment Buttons (Hadir, Izin, Sakit, Alpa)
                                    Row(
                                      children: [
                                        _buildStatusPill(sid, 'Hadir', 'H', Colors.green, currentStatus),
                                        const SizedBox(width: 6),
                                        _buildStatusPill(sid, 'Izin', 'I', Colors.blue, currentStatus),
                                        const SizedBox(width: 6),
                                        _buildStatusPill(sid, 'Sakit', 'S', Colors.orange, currentStatus),
                                        const SizedBox(width: 6),
                                        _buildStatusPill(sid, 'Alpa', 'A', Colors.red, currentStatus),
                                      ],
                                    ),
                                  ],
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
        backgroundColor: (_selectedKategori == 'pulang' && _isToday && !_canAbsenPulang) ||
                (_selectedKategori == 'masuk' && _isToday && !_canAbsenMasuk)
            ? Colors.grey.shade600
            : (_selectedKategori == 'masuk' ? AppTheme.primaryColor : Colors.indigo.shade800),
        foregroundColor: Colors.white,
        icon: _isSaving
            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
            : Icon((_selectedKategori == 'pulang' && _isToday && !_canAbsenPulang) ||
                    (_selectedKategori == 'masuk' && _isToday && !_canAbsenMasuk)
                ? Icons.lock_clock_rounded
                : Icons.save_rounded),
        label: Text(
          _isSaving
              ? 'Menyimpan...'
              : (_selectedKategori == 'pulang' && _isToday && !_canAbsenPulang)
                  ? 'Belum Jam Pulang ($jamPulangMulai WIB)'
                  : (_selectedKategori == 'masuk' && _isToday && !_canAbsenMasuk)
                      ? 'Belum Jam Masuk ($jamMasukMulai WIB)'
                      : 'Simpan Presensi (${_selectedKategori == 'masuk' ? 'MASUK' : 'PULANG'})',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  Widget _buildFilterChip(String filterKey, String label, IconData icon, Color color) {
    final isSelected = _activeFilter == filterKey;

    return InkWell(
      onTap: () {
        setState(() {
          _activeFilter = filterKey;
          _applySearchFilter();
        });
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? color : color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: isSelected ? color : color.withValues(alpha: 0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 13, color: isSelected ? Colors.white : color),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: isSelected ? Colors.white : color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusPill(int sid, String status, String code, Color color, String currentStatus) {
    final isSelected = currentStatus.toLowerCase() == status.toLowerCase() ||
        (status.toLowerCase() == 'alpa' && currentStatus.toLowerCase() == 'alpha') ||
        (status.toLowerCase() == 'alpha' && currentStatus.toLowerCase() == 'alpa');

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
