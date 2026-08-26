import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruRecapAbsensiScreen extends StatefulWidget {
  const GuruRecapAbsensiScreen({super.key});

  @override
  State<GuruRecapAbsensiScreen> createState() => _GuruRecapAbsensiScreenState();
}

class _GuruRecapAbsensiScreenState extends State<GuruRecapAbsensiScreen> {
  bool _isLoading = true;

  int _selectedMonth = DateTime.now().month;
  int _selectedYear = DateTime.now().year;

  List<dynamic> _mapelList = [];
  List<dynamic> _kelasList = [];
  int _selectedMapelId = 0;
  int _selectedKelasId = 0;

  Map<String, dynamic> _summary = {
    'total_hadir': 0,
    'total_sakit': 0,
    'total_izin': 0,
    'total_alpa': 0,
    'avg_persentase': 0,
  };

  List<dynamic> _recapData = [];
  List<dynamic> _filteredData = [];

  final TextEditingController _searchController = TextEditingController();

  final List<String> _monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];

  @override
  void initState() {
    super.initState();
    _loadRecap();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadRecap() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final bulanStr = sprintfTwoDigits(_selectedMonth);
    final data = await Provider.of<GuruProvider>(context, listen: false).fetchRecapAbsensiData(
      user.id,
      bulan: bulanStr,
      tahun: _selectedYear,
      mapelId: _selectedMapelId,
      kelasId: _selectedKelasId,
    );

    if (mounted) {
      final mList = data['mapel_list'] as List? ?? [];
      final kList = data['kelas_list'] as List? ?? [];
      final sum = (data['summary'] as Map?) ?? {
        'total_hadir': 0,
        'total_sakit': 0,
        'total_izin': 0,
        'total_alpa': 0,
        'avg_persentase': 0,
      };
      final recList = data['data'] as List? ?? (data['records'] as List? ?? []);

      setState(() {
        _mapelList = mList;
        _kelasList = kList;
        _summary = Map<String, dynamic>.from(sum);
        _recapData = recList;
        _applySearchFilter();
        _isLoading = false;
      });
    }
  }

  String sprintfTwoDigits(int val) {
    return val < 10 ? '0$val' : '$val';
  }

  void _applySearchFilter() {
    final query = _searchController.text.trim().toLowerCase();
    if (query.isEmpty) {
      setState(() => _filteredData = List.from(_recapData));
      return;
    }

    setState(() {
      _filteredData = _recapData.where((item) {
        final name = (item['nama_lengkap'] ?? '').toString().toLowerCase();
        final nis = (item['nis'] ?? item['nisn'] ?? '').toString().toLowerCase();
        final kelas = (item['nama_kelas'] ?? '').toString().toLowerCase();
        return name.contains(query) || nis.contains(query) || kelas.contains(query);
      }).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final totalHadir = _summary['total_hadir'] ?? 0;
    final totalIzin = _summary['total_izin'] ?? 0;
    final totalSakit = _summary['total_sakit'] ?? 0;
    final totalAlpa = _summary['total_alpa'] ?? 0;
    final avgPct = (_summary['avg_persentase'] ?? 0).toString();

    final validMapelVal = (_selectedMapelId > 0 && _mapelList.any((m) => int.parse((m['id'] ?? 0).toString()) == _selectedMapelId))
        ? _selectedMapelId
        : 0;
    final validKelasVal = (_selectedKelasId > 0 && _kelasList.any((k) => int.parse((k['id'] ?? 0).toString()) == _selectedKelasId))
        ? _selectedKelasId
        : 0;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Rekap Presensi Bulanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Muat Ulang Data',
            onPressed: _loadRecap,
          ),
        ],
      ),
      body: Column(
        children: [
          // Header Gradient Box (Month Selector & Filters)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
            decoration: BoxDecoration(
              color: AppTheme.primaryColor,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(24)),
            ),
            child: Column(
              children: [
                // Month & Year Selector Row
                Row(
                  children: [
                    // Month Dropdown Pill
                    Expanded(
                      flex: 3,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: _selectedMonth,
                            isExpanded: true,
                            icon: Icon(Icons.calendar_month_rounded, color: AppTheme.primaryColor, size: 18),
                            items: List.generate(12, (index) {
                              return DropdownMenuItem<int>(
                                value: index + 1,
                                child: Text(
                                  _monthNames[index],
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                              );
                            }),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedMonth = val);
                                _loadRecap();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Year Dropdown Pill
                    Expanded(
                      flex: 2,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: _selectedYear,
                            isExpanded: true,
                            icon: Icon(Icons.arrow_drop_down_rounded, color: AppTheme.primaryColor, size: 20),
                            items: [2024, 2025, 2026, 2027].map((y) {
                              return DropdownMenuItem<int>(
                                value: y,
                                child: Text('$y', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedYear = val);
                                _loadRecap();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                // Mapel & Kelas Filters Row
                Row(
                  children: [
                    // Mapel Filter
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: validMapelVal,
                            isExpanded: true,
                            dropdownColor: AppTheme.primaryColor,
                            icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 18),
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                            items: [
                              const DropdownMenuItem<int>(
                                value: 0,
                                child: Text('Semua Mapel', style: TextStyle(color: Colors.black87)),
                              ),
                              ..._mapelList.map((m) {
                                final mid = int.parse((m['id'] ?? 0).toString());
                                return DropdownMenuItem<int>(
                                  value: mid,
                                  child: Text((m['nama_mapel'] ?? 'Mapel').toString(), style: const TextStyle(color: Colors.black87)),
                                );
                              }),
                            ],
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedMapelId = val);
                                _loadRecap();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Kelas Filter
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: validKelasVal,
                            isExpanded: true,
                            dropdownColor: AppTheme.primaryColor,
                            icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 18),
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                            items: [
                              const DropdownMenuItem<int>(
                                value: 0,
                                child: Text('Semua Kelas', style: TextStyle(color: Colors.black87)),
                              ),
                              ..._kelasList.map((k) {
                                final kid = int.parse((k['id'] ?? 0).toString());
                                return DropdownMenuItem<int>(
                                  value: kid,
                                  child: Text((k['nama_kelas'] ?? 'Kelas').toString(), style: const TextStyle(color: Colors.black87)),
                                );
                              }),
                            ],
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedKelasId = val);
                                _loadRecap();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Overview Statistics Grid Banner
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
            child: Column(
              children: [
                Row(
                  children: [
                    _buildStatMiniCard('Hadir', '$totalHadir', Colors.green, Icons.check_circle_rounded),
                    const SizedBox(width: 8),
                    _buildStatMiniCard('Izin', '$totalIzin', Colors.blue, Icons.info_rounded),
                    const SizedBox(width: 8),
                    _buildStatMiniCard('Sakit', '$totalSakit', Colors.orange, Icons.local_hospital_rounded),
                    const SizedBox(width: 8),
                    _buildStatMiniCard('Alpha', '$totalAlpa', Colors.red, Icons.cancel_rounded),
                  ],
                ),
                const SizedBox(height: 8),

                // Average Attendance Percentage Banner
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [AppTheme.primaryColor, AppTheme.primaryColor.withValues(alpha: 0.85)],
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: AppTheme.primaryColor.withValues(alpha: 0.2),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.pie_chart_rounded, color: Colors.white, size: 20),
                          const SizedBox(width: 8),
                          const Text(
                            'Rata-Rata Kehadiran Rombel:',
                            style: TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          '$avgPct%',
                          style: TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Search Box & Count Indicator
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 6, 16, 6),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: TextField(
                      controller: _searchController,
                      onChanged: (_) => _applySearchFilter(),
                      style: const TextStyle(fontSize: 12),
                      decoration: const InputDecoration(
                        hintText: 'Cari nama siswa atau kelas...',
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
                    '${_filteredData.length} Siswa',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.blue.shade900),
                  ),
                ),
              ],
            ),
          ),

          // Student Attendance List
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadRecap,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredData.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.assignment_late_rounded, size: 48, color: Colors.grey.shade400),
                              const SizedBox(height: 10),
                              Text(
                                'Belum ada data rekap presensi untuk periode ini.',
                                style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 4, 16, 20),
                          itemCount: _filteredData.length,
                          itemBuilder: (context, index) {
                            final item = _filteredData[index];
                            final name = (item['nama_lengkap'] ?? 'Siswa').toString();
                            final nis = (item['nis'] ?? item['nisn'] ?? '-').toString();
                            final className = (item['nama_kelas'] ?? 'Tanpa Kelas').toString();
                            final jurusanName = (item['nama_jurusan'] ?? '-').toString();

                            final h = item['total_hadir'] ?? 0;
                            final i = item['total_izin'] ?? 0;
                            final s = item['total_sakit'] ?? 0;
                            final a = item['total_alpa'] ?? 0;
                            final pct = (item['persentase'] ?? 0).toDouble();

                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF1E293B) : Colors.white,
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
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              'NIS: $nis • $className ${jurusanName != '-' ? '($jurusanName)' : ''}',
                                              style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600),
                                            ),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 6),

                                      // Percentage Badge
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: pct >= 80 ? Colors.green.shade50 : (pct >= 50 ? Colors.orange.shade50 : Colors.red.shade50),
                                          borderRadius: BorderRadius.circular(10),
                                          border: Border.all(
                                            color: pct >= 80 ? Colors.green.shade300 : (pct >= 50 ? Colors.orange.shade300 : Colors.red.shade300),
                                          ),
                                        ),
                                        child: Text(
                                          '${pct.toStringAsFixed(0)}%',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: pct >= 80 ? Colors.green.shade800 : (pct >= 50 ? Colors.orange.shade800 : Colors.red.shade800),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Status Breakdown Chips (H, I, S, A)
                                  Row(
                                    children: [
                                      _buildCountPill('Hadir', '$h', Colors.green),
                                      const SizedBox(width: 6),
                                      _buildCountPill('Izin', '$i', Colors.blue),
                                      const SizedBox(width: 6),
                                      _buildCountPill('Sakit', '$s', Colors.orange),
                                      const SizedBox(width: 6),
                                      _buildCountPill('Alpha', '$a', Colors.red),
                                    ],
                                  ),
                                  const SizedBox(height: 8),

                                  // Progress Bar Indicator
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(4),
                                    child: LinearProgressIndicator(
                                      value: (pct / 100).clamp(0.0, 1.0),
                                      backgroundColor: Colors.grey.shade200,
                                      color: pct >= 80 ? Colors.green : (pct >= 50 ? Colors.orange : Colors.red),
                                      minHeight: 4,
                                    ),
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
    );
  }

  Widget _buildStatMiniCard(String label, String value, Color color, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 6),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Column(
          children: [
            Icon(icon, size: 16, color: color),
            const SizedBox(height: 2),
            Text(
              value,
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color),
            ),
            Text(
              label,
              style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w600, color: color.withValues(alpha: 0.8)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCountPill(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 5),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Text(
          '$label: $value',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: color),
        ),
      ),
    );
  }
}
