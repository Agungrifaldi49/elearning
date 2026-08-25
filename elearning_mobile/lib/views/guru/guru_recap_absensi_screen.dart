import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GuruRecapAbsensiScreen extends StatefulWidget {
  const GuruRecapAbsensiScreen({super.key});

  @override
  State<GuruRecapAbsensiScreen> createState() => _GuruRecapAbsensiScreenState();
}

class _GuruRecapAbsensiScreenState extends State<GuruRecapAbsensiScreen> {
  bool _isLoading = true;
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

  int _selectedClassId = 0; // 0 = Semua Kelas
  String _selectedClassName = 'Semua Kelas';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchRecap();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchRecap() async {
    setState(() => _isLoading = true);

    final res = await ApiService.get('guru/recap_absensi', params: {
      'kelas_id': _selectedClassId.toString(),
      'search': _searchController.text.trim(),
    });

    if (mounted) {
      if (res['success'] == true && res['data'] is Map) {
        final data = res['data'];
        setState(() {
          _classes = data['classes'] as List? ?? [];
          _allRecords = data['records'] as List? ?? [];
          _summary = Map<String, dynamic>.from(data['summary'] ?? _summary);
          _applyFilters();
          _isLoading = false;
        });
      } else if (res['success'] == true && res['data'] is List) {
        setState(() {
          _allRecords = res['data'];
          _applyFilters();
          _isLoading = false;
        });
      } else {
        setState(() {
          _allRecords = [];
          _filteredRecords = [];
          _isLoading = false;
        });
      }
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
        final nis = (r['nis'] ?? '').toString().toLowerCase();
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
      if (st.contains('hadir') || st.contains('tepat')) hadir++;
      else if (st.contains('izin')) izin++;
      else if (st.contains('sakit')) sakit++;
      else alpa++;
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
      ),
      body: Column(
        children: [
          // Header Hero Container
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
                  style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(
                  'Rekapitulasi riwayat presensi siswa terdaftar di mata pelajaran Anda',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 12),
                ),
                const SizedBox(height: 14),

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

          // Class Filter Chips Bar
          Container(
            height: 50,
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: [
                Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: const Text('Semua Kelas'),
                    selected: _selectedClassId == 0,
                    selectedColor: Colors.teal.shade700,
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
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(cname),
                      selected: isSelected,
                      selectedColor: Colors.teal.shade700,
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

          // Summary Stats Cards Row
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            child: Row(
              children: [
                _buildSummaryCard(
                  title: 'Kehadiran',
                  value: '${hadirPercent.toStringAsFixed(1)}%',
                  subText: '$hadirCount Siswa',
                  color: Colors.green,
                  icon: Icons.pie_chart_rounded,
                ),
                const SizedBox(width: 8),
                _buildSummaryCard(
                  title: 'Izin',
                  value: '$izinCount',
                  subText: 'Izin Resmi',
                  color: Colors.blue,
                  icon: Icons.assignment_turned_in_rounded,
                ),
                const SizedBox(width: 8),
                _buildSummaryCard(
                  title: 'Sakit',
                  value: '$sakitCount',
                  subText: 'Surat Sakit',
                  color: Colors.orange,
                  icon: Icons.local_hospital_rounded,
                ),
                const SizedBox(width: 8),
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

          // Detailed List of Records
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchRecap,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredRecords.isEmpty
                      ? Center(
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
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredRecords.length,
                          itemBuilder: (context, index) {
                            final r = _filteredRecords[index];
                            final name = (r['nama_lengkap'] ?? 'Siswa').toString();
                            final nis = (r['nis'] ?? '-').toString();
                            final className = (r['nama_kelas'] ?? 'Umum').toString();
                            final status = (r['status'] ?? 'Hadir').toString();
                            final date = (r['tanggal'] ?? '-').toString();
                            final mapelName = (r['nama_mapel'] ?? '').toString();
                            final waktuMasuk = (r['waktu_masuk'] ?? '').toString();
                            final waktuPulang = (r['waktu_pulang'] ?? '').toString();

                            Color statusColor = Colors.green;
                            final st = status.toLowerCase();
                            if (st.contains('izin')) statusColor = Colors.blue;
                            else if (st.contains('sakit')) statusColor = Colors.orange;
                            else if (st.contains('alpa') || st.contains('alpha')) statusColor = Colors.red;

                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              elevation: 1.5,
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: statusColor.withValues(alpha: 0.15),
                                  child: Text(
                                    name.isNotEmpty ? name[0] : 'S',
                                    style: TextStyle(color: statusColor, fontWeight: FontWeight.bold),
                                  ),
                                ),
                                title: Row(
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
                                subtitle: Padding(
                                  padding: const EdgeInsets.only(top: 4),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'NIS: $nis • Rombel $className',
                                        style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                      ),
                                      if (mapelName.isNotEmpty) ...[
                                        const SizedBox(height: 2),
                                        Text(
                                          '📘 Mapel: $mapelName',
                                          style: TextStyle(fontSize: 10, color: Colors.teal.shade800, fontWeight: FontWeight.w600),
                                        ),
                                      ],
                                      const SizedBox(height: 2),
                                      Row(
                                        children: [
                                          Icon(Icons.calendar_today_rounded, size: 12, color: Colors.grey.shade600),
                                          const SizedBox(width: 4),
                                          Text(
                                            date,
                                            style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                                          ),
                                          if (waktuMasuk.isNotEmpty) ...[
                                            const SizedBox(width: 10),
                                            Icon(Icons.access_time_rounded, size: 12, color: Colors.grey.shade600),
                                            const SizedBox(width: 2),
                                            Text(
                                              'Masuk: $waktuMasuk',
                                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                                            ),
                                          ],
                                          if (waktuPulang.isNotEmpty) ...[
                                            const SizedBox(width: 8),
                                            Text(
                                              '• Pulang: $waktuPulang',
                                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ],
                                  ),
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

  Widget _buildSummaryCard({
    required String title,
    required String value,
    required String subText,
    required Color color,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 20),
            const SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: color),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Colors.grey.shade800),
            ),
          ],
        ),
      ),
    );
  }
}
