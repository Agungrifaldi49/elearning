import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/jadwal_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';
import 'guru_input_absensi_screen.dart';

class GuruJadwalTab extends StatefulWidget {
  const GuruJadwalTab({super.key});

  @override
  State<GuruJadwalTab> createState() => _GuruJadwalTabState();
}

class _GuruJadwalTabState extends State<GuruJadwalTab> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedHari = 'Semua';

  final List<String> _hariList = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  @override
  void initState() {
    super.initState();
    _loadJadwal();
    _setTodayAsDefault();
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text.toLowerCase().trim();
      });
    });
  }

  void _setTodayAsDefault() {
    final days = [1, 2, 3, 4, 5, 6];
    final dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    final now = DateTime.now();
    if (days.contains(now.weekday)) {
      _selectedHari = dayNames[now.weekday - 1];
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadJadwal() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchJadwal(user.id);
    }
  }

  String _getTodayName() {
    const days = {
      DateTime.monday: 'Senin',
      DateTime.tuesday: 'Selasa',
      DateTime.wednesday: 'Rabu',
      DateTime.thursday: 'Kamis',
      DateTime.friday: 'Jumat',
      DateTime.saturday: 'Sabtu',
      DateTime.sunday: 'Minggu'
    };
    return days[DateTime.now().weekday] ?? 'Senin';
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final allJadwal = guruProvider.jadwalList;
    final todayName = _getTodayName();

    final filteredJadwal = allJadwal.where((j) {
      final matchesHari = _selectedHari == 'Semua' || j.hari.toLowerCase() == _selectedHari.toLowerCase();
      final matchesSearch = _searchQuery.isEmpty ||
          j.namaMapel.toLowerCase().contains(_searchQuery) ||
          (j.namaKelas ?? '').toLowerCase().contains(_searchQuery) ||
          j.ruangan.toLowerCase().contains(_searchQuery);
      return matchesHari && matchesSearch;
    }).toList();

    // Calculate stats
    final totalJadwal = allJadwal.length;
    final todayJadwalCount = allJadwal.where((j) => j.hari.toLowerCase() == todayName.toLowerCase()).length;
    final mapelSet = allJadwal.map((e) => e.namaMapel).toSet().length;

    return RefreshIndicator(
      onRefresh: () async => _loadJadwal(),
      color: AppTheme.primaryColor,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 🚀 EXECUTIVE HERO CARD HEADER
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF0369A1)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withAlpha(60),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.amber,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.calendar_month_rounded, size: 14, color: Colors.black87),
                            const SizedBox(width: 4),
                            Text(
                              'Hari Ini: $todayName',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.black87),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.white.withAlpha(30),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '$todayJadwalCount Sesi Hari Ini',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Jadwal Mengajar KBM',
                    style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Pantau waktu sesi mengajar, ruang kelas, dan lakukan presensi kehadiran siswa.',
                    style: TextStyle(fontSize: 12, color: Colors.white.withAlpha(200), height: 1.4),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 📊 KPI STATS STRIP
            Row(
              children: [
                Expanded(
                  child: _buildKpiCard(
                    title: 'Total Sesi KBM',
                    value: '$totalJadwal Sesi',
                    icon: Icons.auto_stories_rounded,
                    iconColor: Colors.blue.shade700,
                    bgColor: Colors.blue.shade50,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildKpiCard(
                    title: 'Mapel Diampu',
                    value: '$mapelSet Mapel',
                    icon: Icons.book_outlined,
                    iconColor: Colors.emerald.shade700,
                    bgColor: Colors.emerald.shade50,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // 🗓️ DAY FILTER CHIPS (Horizontal Scrollable)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _hariList.map((hari) {
                  final isSel = _selectedHari == hari;
                  final isToday = hari.toLowerCase() == todayName.toLowerCase();

                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      selected: isSel,
                      label: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(hari),
                          if (isToday && hari != 'Semua') ...[
                            const SizedBox(width: 4),
                            Container(
                              width: 6,
                              height: 6,
                              decoration: const BoxDecoration(
                                color: Colors.amber,
                                shape: BoxShape.circle,
                              ),
                            ),
                          ],
                        ],
                      ),
                      labelStyle: TextStyle(
                        fontSize: 12,
                        fontWeight: isSel ? FontWeight.bold : FontWeight.w500,
                        color: isSel ? Colors.white : Colors.black87,
                      ),
                      selectedColor: AppTheme.primaryColor,
                      backgroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: BorderSide(color: isSel ? AppTheme.primaryColor : Colors.grey.shade300),
                      ),
                      onSelected: (selected) {
                        setState(() {
                          _selectedHari = hari;
                        });
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 14),

            // 🔍 SEARCH INPUT
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari mata pelajaran, kelas, atau ruang...',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded),
                        onPressed: () => _searchController.clear(),
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(color: Colors.grey.shade200),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(color: Colors.grey.shade200),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // 📑 SCHEDULE CARDS LIST
            if (guruProvider.isLoading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 40),
                child: Center(child: CircularProgressIndicator()),
              )
            else if (filteredJadwal.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      Icon(Icons.event_busy_rounded, size: 54, color: Colors.grey.shade400),
                      const SizedBox(height: 12),
                      Text(
                        _selectedHari == 'Semua' ? 'Belum Ada Jadwal Mengajar' : 'Tidak Ada Sesi di Hari $_selectedHari',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _searchQuery.isNotEmpty
                            ? 'Tidak ditemukan jadwal sesuai pencarian "$_searchQuery".'
                            : 'Silakan pilih hari lain untuk melihat jadwal mengajar.',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: filteredJadwal.length,
                itemBuilder: (context, index) {
                  final j = filteredJadwal[index];
                  final isTodaySession = j.hari.toLowerCase() == todayName.toLowerCase();

                  return Container(
                    margin: const EdgeInsets.only(bottom: 14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isTodaySession ? AppTheme.primaryColor.withAlpha(80) : Colors.grey.shade200,
                        width: isTodaySession ? 1.5 : 1.0,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: isTodaySession ? AppTheme.primaryColor.withAlpha(15) : Colors.black.withAlpha(8),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Top accent bar
                          Container(
                            height: 4,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: isTodaySession
                                    ? [AppTheme.primaryColor, Colors.teal]
                                    : [Colors.indigo.shade400, Colors.blue.shade600],
                              ),
                            ),
                          ),

                          Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Time & Hari Badge Header
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                      decoration: BoxDecoration(
                                        color: isTodaySession ? Colors.emerald.shade50 : Colors.indigo.shade50,
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(
                                          color: isTodaySession ? Colors.emerald.shade200 : Colors.indigo.shade200,
                                        ),
                                      ),
                                      child: Row(
                                        children: [
                                          Icon(
                                            Icons.access_time_filled_rounded,
                                            size: 13,
                                            color: isTodaySession ? Colors.emerald.shade800 : Colors.indigo.shade800,
                                          ),
                                          const SizedBox(width: 5),
                                          Text(
                                            '${j.jamMulai} - ${j.jamSelesai} WIB',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              color: isTodaySession ? Colors.emerald.shade900 : Colors.indigo.shade900,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                      decoration: BoxDecoration(
                                        color: Colors.amber.shade50,
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(color: Colors.amber.shade300),
                                      ),
                                      child: Row(
                                        children: [
                                          Icon(Icons.meeting_room_rounded, size: 13, color: Colors.amber.shade900),
                                          const SizedBox(width: 4),
                                          Text(
                                            j.ruangan,
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.amber.shade900,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),

                                // Subject Title
                                Text(
                                  j.namaMapel,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.black87,
                                    height: 1.3,
                                  ),
                                ),
                                const SizedBox(height: 6),

                                // Class Info & Day Badge
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: Colors.blue.shade50,
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        'Hari ${j.hari}',
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Icon(Icons.groups_rounded, size: 16, color: Colors.grey.shade600),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        'Kelas Target: ${j.namaKelas ?? 'Semua Kelas'}',
                                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 14),

                                // Quick Action Buttons
                                Row(
                                  children: [
                                    Expanded(
                                      child: ElevatedButton.icon(
                                        onPressed: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(builder: (_) => const GuruInputAbsensiScreen()),
                                          );
                                        },
                                        icon: const Icon(Icons.how_to_reg_rounded, size: 16),
                                        label: const Text('Input Presensi Siswa'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: AppTheme.primaryColor,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(vertical: 10),
                                          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          elevation: 1,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 10, offset: const Offset(0, 2)),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  value,
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.black87),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
