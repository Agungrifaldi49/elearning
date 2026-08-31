import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaJadwalTab extends StatefulWidget {
  const SiswaJadwalTab({super.key});

  @override
  State<SiswaJadwalTab> createState() => _SiswaJadwalTabState();
}

class _SiswaJadwalTabState extends State<SiswaJadwalTab> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedHari = 'Semua';

  final List<String> _hariList = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  @override
  void initState() {
    super.initState();
    _setTodayAsDefault();
    _loadJadwal();
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text.toLowerCase().trim();
      });
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        Provider.of<SiswaProvider>(context, listen: false).markAllJadwalAsSeen();
      }
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
      Provider.of<SiswaProvider>(context, listen: false).fetchJadwal(user.id);
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
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final allJadwal = siswaProvider.jadwalList;
    final todayName = _getTodayName();

    final filteredJadwal = allJadwal.where((j) {
      final matchesHari = _selectedHari == 'Semua' || j.hari.toLowerCase() == _selectedHari.toLowerCase();
      final matchesSearch = _searchQuery.isEmpty ||
          j.namaMapel.toLowerCase().contains(_searchQuery) ||
          (j.namaGuru ?? '').toLowerCase().contains(_searchQuery) ||
          j.ruangan.toLowerCase().contains(_searchQuery);
      return matchesHari && matchesSearch;
    }).toList();

    // Stats calculations
    final totalJadwal = allJadwal.length;
    final todayJadwalCount = allJadwal.where((j) => j.hari.toLowerCase() == todayName.toLowerCase()).length;
    final mapelCount = allJadwal.map((e) => e.namaMapel).toSet().length;

    return RefreshIndicator(
      onRefresh: () async => _loadJadwal(),
      color: AppTheme.primaryColor,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 🚀 EXECUTIVE HERO HEADER BANNER
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF0D9488)],
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
                            const Icon(Icons.calendar_today_rounded, size: 13, color: Colors.black87),
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
                    'Jadwal Pelajaran Siswa',
                    style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Jadwal Kegiatan Belajar Mengajar (KBM) interaktif dan waktu ruang kelas.',
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
                    title: 'Total Pelajaran',
                    value: '$totalJadwal Sesi',
                    icon: Icons.menu_book_rounded,
                    iconColor: Colors.teal.shade700,
                    bgColor: Colors.teal.shade50,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildKpiCard(
                    title: 'Total Mapel',
                    value: '$mapelCount Mapel',
                    icon: Icons.school_rounded,
                    iconColor: Colors.blue.shade700,
                    bgColor: Colors.blue.shade50,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // 🗓️ DAY FILTER CHIPS
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
                      selectedColor: AppTheme.secondaryColor,
                      backgroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: BorderSide(color: isSel ? AppTheme.secondaryColor : Colors.grey.shade300),
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
                hintText: 'Cari mata pelajaran, guru pengampu, atau ruang...',
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

            // 📑 TIMELINE SCHEDULE LIST
            if (siswaProvider.isLoading)
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
                        _selectedHari == 'Semua' ? 'Belum Ada Jadwal Pelajaran' : 'Tidak Ada Pelajaran di Hari $_selectedHari',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _searchQuery.isNotEmpty
                            ? 'Tidak ditemukan jadwal sesuai pencarian "$_searchQuery".'
                            : 'Silakan pilih hari lain untuk melihat jadwal KBM.',
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
                        color: isTodaySession ? AppTheme.secondaryColor.withAlpha(80) : Colors.grey.shade200,
                        width: isTodaySession ? 1.5 : 1.0,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: isTodaySession ? AppTheme.secondaryColor.withAlpha(15) : Colors.black.withAlpha(8),
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
                          // Accent bar
                          Container(
                            height: 4,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: isTodaySession
                                    ? [AppTheme.secondaryColor, const Color(0xFF10B981)]
                                    : [Colors.blue.shade400, Colors.teal.shade600],
                              ),
                            ),
                          ),

                          Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Time & Room Header Badges
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                      decoration: BoxDecoration(
                                        color: isTodaySession ? const Color(0xFFECFDF5) : Colors.blue.shade50,
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(
                                          color: isTodaySession ? const Color(0xFFA7F3D0) : Colors.blue.shade200,
                                        ),
                                      ),
                                      child: Row(
                                        children: [
                                          Icon(
                                            Icons.access_time_filled_rounded,
                                            size: 13,
                                            color: isTodaySession ? const Color(0xFF065F46) : Colors.blue.shade800,
                                          ),
                                          const SizedBox(width: 5),
                                          Text(
                                            '${j.jamMulai} - ${j.jamSelesai} WIB',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              color: isTodaySession ? const Color(0xFF064E3B) : Colors.blue.shade900,
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

                                // Subject Name
                                Text(
                                  j.namaMapel,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.black87,
                                    height: 1.3,
                                  ),
                                ),
                                const SizedBox(height: 8),

                                // Teacher Avatar & Name
                                Container(
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: Colors.grey.shade50,
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: Colors.grey.shade200),
                                  ),
                                  child: Row(
                                    children: [
                                      CircleAvatar(
                                        radius: 14,
                                        backgroundColor: AppTheme.primaryColor.withAlpha(30),
                                        child: const Icon(Icons.person_rounded, size: 16, color: AppTheme.primaryColor),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              'Guru Pengampu',
                                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                                            ),
                                            Text(
                                              j.namaGuru ?? 'Guru SMK MH',
                                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.black87),
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.teal.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          'Hari ${j.hari}',
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.teal.shade800),
                                        ),
                                      ),
                                    ],
                                  ),
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
