import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';

class GuruRecapAbsensiScreen extends StatefulWidget {
  const GuruRecapAbsensiScreen({super.key});

  @override
  State<GuruRecapAbsensiScreen> createState() => _GuruRecapAbsensiScreenState();
}

class _GuruRecapAbsensiScreenState extends State<GuruRecapAbsensiScreen> {
  bool _isLoading = true;
  int _viewMode = 0; // 0 = Riwayat Harian, 1 = Matriks Bulanan
  int _groupMode = 0; // 0 = Per Kelas, 1 = Per Mapel

  List<dynamic> _classes = [];
  List<dynamic> _allRecords = [];
  List<dynamic> _filteredRecords = [];
  
  Map<String, dynamic> _summary = {
    'total_records': 0,
    'hadir': 0,
    'izin': 0,
    'sakit': 0,
    'alpa': 0,
  };

  Map<String, dynamic>? _monthlyRecap;
  List<dynamic> _monthlyData = [];

  int _selectedClassId = 0; // 0 = Semua Kelas
  String _selectedClassName = 'Semua Kelas';
  
  late String _selectedBulan;
  late String _selectedTahun;

  final TextEditingController _searchController = TextEditingController();

  final Map<String, String> _bulanNames = {
    '01': 'Januari',
    '02': 'Februari',
    '03': 'Maret',
    '04': 'April',
    '05': 'Mei',
    '06': 'Juni',
    '07': 'Juli',
    '08': 'Agustus',
    '09': 'September',
    '10': 'Oktober',
    '11': 'November',
    '12': 'Desember',
  };

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _selectedBulan = now.month.toString().padLeft(2, '0');
    _selectedTahun = now.year.toString();
    _fetchRecap();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchRecap() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final data = await Provider.of<GuruProvider>(context, listen: false).fetchRecapAbsensiData(
      user.id,
      kelasId: _selectedClassId,
      bulan: _selectedBulan,
      tahun: _selectedTahun,
      search: _searchController.text.trim(),
    );

    if (mounted) {
      setState(() {
        _classes = data['classes'] as List? ?? [];
        _allRecords = data['records'] as List? ?? [];
        _summary = Map<String, dynamic>.from(data['summary'] ?? _summary);

        if (data['monthly_recap'] is Map) {
          _monthlyRecap = Map<String, dynamic>.from(data['monthly_recap']);
          _monthlyData = (_monthlyRecap?['data'] as List?) ?? [];
        } else {
          _monthlyRecap = null;
          _monthlyData = [];
        }

        _applyFilters();
        _isLoading = false;
      });
    }
  }

  void _applyFilters() {
    final query = _searchController.text.trim().toLowerCase();
    List<dynamic> list = List.from(_allRecords);

    if (_selectedClassId > 0) {
      list = list.where((r) {
        final kid = int.parse((r['kelas_id'] ?? 0).toString());
        return kid == _selectedClassId;
      }).toList();
    }

    if (query.isNotEmpty) {
      list = list.where((r) {
        final name = (r['nama_lengkap'] ?? '').toString().toLowerCase();
        final nis = (r['nis'] ?? r['nisn'] ?? '').toString().toLowerCase();
        final status = (r['status'] ?? '').toString().toLowerCase();
        final date = (r['tanggal'] ?? '').toString().toLowerCase();
        final mapel = (r['nama_mapel'] ?? '').toString().toLowerCase();
        return name.contains(query) || nis.contains(query) || status.contains(query) || date.contains(query) || mapel.contains(query);
      }).toList();
    }

    // Recompute live summary stats based on filtered list
    int hadir = 0, izin = 0, sakit = 0, alpa = 0;
    for (var r in list) {
      final st = (r['status'] ?? '').toString().toLowerCase();
      if (st.contains('hadir') || st.contains('tepat')) {
        hadir++;
      } else if (st.contains('izin')) {
        izin++;
      } else if (st.contains('sakit')) {
        sakit++;
      } else {
        alpa++;
      }
    }

    setState(() {
      _filteredRecords = list;
      _summary = {
        'total_records': list.length,
        'hadir': hadir,
        'izin': izin,
        'sakit': sakit,
        'alpa': alpa,
      };
    });
  }

  // Group Records by Class or Mapel
  Map<String, List<dynamic>> _groupRecords() {
    final Map<String, List<dynamic>> grouped = {};
    for (var r in _filteredRecords) {
      String groupKey = 'Tanpa Kelompok';
      if (_groupMode == 0) {
        groupKey = (r['nama_kelas'] ?? 'Umum').toString();
      } else {
        groupKey = (r['nama_mapel'] ?? 'Mata Pelajaran').toString();
      }

      if (!grouped.containsKey(groupKey)) {
        grouped[groupKey] = [];
      }
      grouped[groupKey]!.add(r);
    }
    return grouped;
  }

  @override
  Widget build(BuildContext context) {
    final totalRecs = int.parse((_summary['total_records'] ?? 0).toString());
    final hadirCount = int.parse((_summary['hadir'] ?? 0).toString());
    final izinCount = int.parse((_summary['izin'] ?? 0).toString());
    final sakitCount = int.parse((_summary['sakit'] ?? 0).toString());
    final alpaCount = int.parse((_summary['alpa'] ?? 0).toString());

    final double hadirPercent = totalRecs > 0 ? (hadirCount / totalRecs * 100) : 0.0;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekap Presensi Siswa Terdaftar'),
        backgroundColor: Colors.teal.shade800,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Muat Ulang Data',
            onPressed: _fetchRecap,
          ),
        ],
      ),
      body: Column(
        children: [
          // Header Hero Container with Month/Year Filters
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Colors.teal.shade800, Colors.teal.shade600],
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
                  '📊 Laporan Rekapitulasi Presensi Terdaftar',
                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(
                  'Rekapitulasi riwayat presensi harian & matriks bulanan siswa terdaftar',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11),
                ),
                const SizedBox(height: 12),

                // Month and Year Dropdowns Row
                Row(
                  children: [
                    Expanded(
                      flex: 3,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _selectedBulan,
                            dropdownColor: Colors.teal.shade800,
                            icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 18),
                            isExpanded: true,
                            style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                            items: _bulanNames.entries.map((entry) {
                              return DropdownMenuItem<String>(
                                value: entry.key,
                                child: Text('📅 Bulan: ${entry.value}'),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedBulan = val);
                                _fetchRecap();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      flex: 2,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _selectedTahun,
                            dropdownColor: Colors.teal.shade800,
                            icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 18),
                            isExpanded: true,
                            style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                            items: ['2024', '2025', '2026', '2027'].map((y) {
                              return DropdownMenuItem<String>(
                                value: y,
                                child: Text('Tahun: $y'),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedTahun = val);
                                _fetchRecap();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                // Search Bar
                TextField(
                  controller: _searchController,
                  onChanged: (_) => _applyFilters(),
                  style: const TextStyle(color: Colors.white, fontSize: 13),
                  decoration: InputDecoration(
                    hintText: 'Cari Nama, NIS, Mapel, Tanggal (YYYY-MM-DD)...',
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

          // View Mode Selector & Grouping Toggle Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            color: Colors.white,
            child: Column(
              children: [
                // View Mode Toggle Segmented Control (Riwayat vs Matriks)
                Row(
                  children: [
                    Expanded(
                      child: InkWell(
                        onTap: () => setState(() => _viewMode = 0),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 7),
                          decoration: BoxDecoration(
                            color: _viewMode == 0 ? Colors.teal.shade700 : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '📋 Log Riwayat Presensi',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: _viewMode == 0 ? Colors.white : Colors.black87,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: InkWell(
                        onTap: () => setState(() => _viewMode = 1),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 7),
                          decoration: BoxDecoration(
                            color: _viewMode == 1 ? Colors.teal.shade700 : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '📅 Matriks Rekap Bulanan',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: _viewMode == 1 ? Colors.white : Colors.black87,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),

                // Grouping Mode Segmented Control (Per Kelas vs Per Mapel)
                Row(
                  children: [
                    Expanded(
                      child: InkWell(
                        onTap: () => setState(() => _groupMode = 0),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 6),
                          decoration: BoxDecoration(
                            color: _groupMode == 0 ? Colors.teal.shade50 : Colors.grey.shade50,
                            border: Border.all(color: _groupMode == 0 ? Colors.teal : Colors.grey.shade300),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '🏫 Kelompok Per Kelas',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: _groupMode == 0 ? Colors.teal.shade800 : Colors.grey.shade700,
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
                          padding: const EdgeInsets.symmetric(vertical: 6),
                          decoration: BoxDecoration(
                            color: _groupMode == 1 ? Colors.amber.shade50 : Colors.grey.shade50,
                            border: Border.all(color: _groupMode == 1 ? Colors.amber.shade800 : Colors.grey.shade300),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '📘 Kelompok Per Mapel',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: _groupMode == 1 ? Colors.amber.shade900 : Colors.grey.shade700,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),

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
                          selectedColor: Colors.teal.shade700,
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
                                _fetchRecap();
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
                            selectedColor: Colors.teal.shade700,
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
                                  _fetchRecap();
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

          // Summary Stats Cards Row
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            child: Row(
              children: [
                _buildSummaryCard(
                  title: 'Kehadiran',
                  value: '${hadirPercent.toStringAsFixed(1)}%',
                  subText: '$hadirCount Siswa',
                  color: Colors.green,
                  icon: Icons.pie_chart_rounded,
                ),
                const SizedBox(width: 6),
                _buildSummaryCard(
                  title: 'Izin',
                  value: '$izinCount',
                  subText: 'Izin Resmi',
                  color: Colors.blue,
                  icon: Icons.assignment_turned_in_rounded,
                ),
                const SizedBox(width: 6),
                _buildSummaryCard(
                  title: 'Sakit',
                  value: '$sakitCount',
                  subText: 'Surat Sakit',
                  color: Colors.orange,
                  icon: Icons.local_hospital_rounded,
                ),
                const SizedBox(width: 6),
                _buildSummaryCard(
                  title: 'Alpa',
                  value: '$alpaCount',
                  subText: 'Tanpa Ket.',
                  color: Colors.red,
                  icon: Icons.cancel_rounded,
                ),
              ],
            ),
          ),

          // Main View (Mode 0: Riwayat Logs Grouped, Mode 1: Matriks Bulanan)
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchRecap,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _viewMode == 0
                      ? _buildGroupedDailyLogView()
                      : _buildMonthlyMatrixView(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGroupedDailyLogView() {
    if (_filteredRecords.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_late_rounded, size: 54, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              'Belum ada data rekap presensi terdaftar untuk $_selectedClassName.',
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    final groupedData = _groupRecords();

    return ListView(
      padding: const EdgeInsets.all(12),
      children: groupedData.entries.map((entry) {
        final groupName = entry.key;
        final recordsInGroup = entry.value;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Section Header (Class or Mapel)
            Container(
              width: double.infinity,
              margin: const EdgeInsets.only(top: 8, bottom: 8),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: _groupMode == 0 ? Colors.teal.shade50 : Colors.amber.shade50,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: _groupMode == 0 ? Colors.teal.shade200 : Colors.amber.shade200),
              ),
              child: Row(
                children: [
                  Icon(
                    _groupMode == 0 ? Icons.school_rounded : Icons.menu_book_rounded,
                    size: 18,
                    color: _groupMode == 0 ? Colors.teal.shade800 : Colors.amber.shade900,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _groupMode == 0 ? 'Kelas: $groupName' : 'Mapel: $groupName',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: _groupMode == 0 ? Colors.teal.shade800 : Colors.amber.shade900,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: _groupMode == 0 ? Colors.teal.shade800 : Colors.amber.shade900,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${recordsInGroup.length} Record',
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),

            // Record Cards
            ...recordsInGroup.map((r) {
              final name = (r['nama_lengkap'] ?? 'Siswa').toString();
              final nis = (r['nis'] ?? r['nisn'] ?? '-').toString();
              final className = (r['nama_kelas'] ?? 'Umum').toString();
              final status = (r['status'] ?? 'Hadir').toString();
              final date = (r['tanggal'] ?? '-').toString();
              final mapelName = (r['nama_mapel'] ?? '').toString();
              final waktuMasuk = (r['waktu_masuk'] ?? r['waktu_hadir'] ?? '').toString();
              final waktuPulang = (r['waktu_pulang'] ?? '').toString();
              final keterangan = (r['keterangan'] ?? '').toString();
              final qrCode = (r['qr_code'] ?? '').toString();

              Color statusColor = Colors.green;
              final st = status.toLowerCase();
              if (st.contains('izin')) {
                statusColor = Colors.blue;
              } else if (st.contains('sakit')) {
                statusColor = Colors.orange;
              } else if (st.contains('alpa') || st.contains('alpha')) {
                statusColor = Colors.red;
              }

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
                            backgroundColor: statusColor.withValues(alpha: 0.15),
                            child: Text(
                              name.isNotEmpty ? name[0] : 'S',
                              style: TextStyle(color: statusColor, fontWeight: FontWeight.bold),
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
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: statusColor,
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        status,
                                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'NIS: $nis • Rombel $className',
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),

                      if (mapelName.isNotEmpty) ...[
                        Text(
                          '📘 Mapel: $mapelName',
                          style: TextStyle(fontSize: 11, color: Colors.teal.shade800, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 4),
                      ],

                      Row(
                        children: [
                          Icon(Icons.calendar_today_rounded, size: 12, color: Colors.grey.shade600),
                          const SizedBox(width: 4),
                          Text(
                            date,
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade700, fontWeight: FontWeight.w600),
                          ),
                          if (waktuMasuk.isNotEmpty) ...[
                            const SizedBox(width: 10),
                            Icon(Icons.access_time_rounded, size: 12, color: Colors.grey.shade600),
                            const SizedBox(width: 2),
                            Text(
                              'Masuk: ${waktuMasuk.length >= 16 ? waktuMasuk.substring(11, 16) : waktuMasuk} WIB',
                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                            ),
                          ],
                          if (waktuPulang.isNotEmpty) ...[
                            const SizedBox(width: 6),
                            Text(
                              '• Pulang: ${waktuPulang.length >= 16 ? waktuPulang.substring(11, 16) : waktuPulang} WIB',
                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                            ),
                          ],
                        ],
                      ),

                      if (qrCode.isNotEmpty || keterangan.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Wrap(
                          spacing: 6,
                          runSpacing: 4,
                          children: [
                            if (qrCode.isNotEmpty)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.green.shade50,
                                  border: Border.all(color: Colors.green.shade200),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.qr_code_scanner, size: 11, color: Colors.green),
                                    SizedBox(width: 3),
                                    Text('Presensi QR Digital', style: TextStyle(fontSize: 9, color: Colors.green, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                              ),
                            if (keterangan.isNotEmpty)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade100,
                                  border: Border.all(color: Colors.grey.shade300),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'Catatan: $keterangan',
                                  style: TextStyle(fontSize: 9, color: Colors.grey.shade800),
                                ),
                              ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              );
            }),
          ],
        );
      }).toList(),
    );
  }

  Widget _buildMonthlyMatrixView() {
    if (_monthlyData.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.table_chart_outlined, size: 54, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              'Belum ada data matriks bulanan untuk ${_bulanNames[_selectedBulan]} $_selectedTahun.',
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    final numDays = int.parse((_monthlyRecap?['num_days'] ?? 30).toString());

    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        // Legend Card
        Card(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Padding(
            padding: const EdgeInsets.all(10),
            child: Wrap(
              spacing: 8,
              runSpacing: 6,
              alignment: WrapAlignment.center,
              children: [
                _buildLegendChip('H', 'Hadir', Colors.green),
                _buildLegendChip('TL', 'Terlambat', Colors.amber.shade800),
                _buildLegendChip('S', 'Sakit', Colors.orange),
                _buildLegendChip('I', 'Izin', Colors.blue),
                _buildLegendChip('A', 'Alpa', Colors.red),
              ],
            ),
          ),
        ),
        const SizedBox(height: 8),

        // Horizontal Scrollable Matrix Table Card
        Card(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          elevation: 2,
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: DataTable(
              columnSpacing: 10,
              horizontalMargin: 12,
              headingRowHeight: 40,
              dataRowMinHeight: 40,
              dataRowMaxHeight: 48,
              headingRowColor: WidgetStateProperty.all(Colors.teal.shade50),
              columns: [
                const DataColumn(label: Text('No', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                const DataColumn(label: Text('NIS', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                const DataColumn(label: Text('Nama Siswa', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                const DataColumn(label: Text('Kelas', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                ...List.generate(numDays, (index) => DataColumn(
                  label: Text('${index + 1}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 10)),
                )),
                const DataColumn(label: Text('H', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.green, fontSize: 11))),
                const DataColumn(label: Text('S', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.orange, fontSize: 11))),
                const DataColumn(label: Text('I', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blue, fontSize: 11))),
                const DataColumn(label: Text('A', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red, fontSize: 11))),
                const DataColumn(label: Text('%', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
              ],
              rows: List.generate(_monthlyData.length, (index) {
                final row = _monthlyData[index];
                final name = (row['nama_lengkap'] ?? 'Siswa').toString();
                final nis = (row['nis'] ?? row['nisn'] ?? '-').toString();
                final className = (row['nama_kelas'] ?? '-').toString();
                final daily = (row['daily'] as Map?) ?? {};
                final totalHadir = row['total_hadir'] ?? 0;
                final totalSakit = row['total_sakit'] ?? 0;
                final totalIzin = row['total_izin'] ?? 0;
                final totalAlpa = row['total_alpa'] ?? 0;
                final persentase = row['persentase'] ?? 0;

                return DataRow(
                  cells: [
                    DataCell(Text('${index + 1}', style: const TextStyle(fontSize: 10))),
                    DataCell(Text(nis, style: const TextStyle(fontSize: 10, fontFamily: 'monospace'))),
                    DataCell(Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                    DataCell(Text(className, style: const TextStyle(fontSize: 10))),
                    ...List.generate(numDays, (dIdx) {
                      final dayNum = dIdx + 1;
                      final val = (daily[dayNum.toString()] ?? daily[dayNum] ?? '-').toString();
                      return DataCell(
                        Center(
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                            decoration: BoxDecoration(
                              color: _getMatrixCellBg(val),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              val,
                              style: TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                color: _getMatrixCellTextColor(val),
                              ),
                            ),
                          ),
                        ),
                      );
                    }),
                    DataCell(Text('$totalHadir', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green, fontSize: 11))),
                    DataCell(Text('$totalSakit', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.orange, fontSize: 11))),
                    DataCell(Text('$totalIzin', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.blue, fontSize: 11))),
                    DataCell(Text('$totalAlpa', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.red, fontSize: 11))),
                    DataCell(Text('$persentase%', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                  ],
                );
              }),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildLegendChip(String code, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        '$code: $label',
        style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.bold),
      ),
    );
  }

  Color _getMatrixCellBg(String val) {
    switch (val) {
      case 'H': return Colors.green.shade100;
      case 'TL': return Colors.amber.shade100;
      case 'S': return Colors.orange.shade100;
      case 'I': return Colors.blue.shade100;
      case 'A': return Colors.red.shade100;
      default: return Colors.transparent;
    }
  }

  Color _getMatrixCellTextColor(String val) {
    switch (val) {
      case 'H': return Colors.green.shade900;
      case 'TL': return Colors.amber.shade900;
      case 'S': return Colors.orange.shade900;
      case 'I': return Colors.blue.shade900;
      case 'A': return Colors.red.shade900;
      default: return Colors.grey.shade600;
    }
  }

  Widget _buildSummaryCard({
    required String title,
    required String value,
    required String subText,
    required Color color,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 18),
            const SizedBox(height: 2),
            Text(
              value,
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: color),
            ),
            Text(
              title,
              style: TextStyle(fontSize: 9, fontWeight: FontWeight.w600, color: Colors.grey.shade800),
            ),
          ],
        ),
      ),
    );
  }
}
