import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import 'edugame_play_screen.dart';

class EduGameScreen extends StatefulWidget {
  const EduGameScreen({super.key});

  @override
  State<EduGameScreen> createState() => _EduGameScreenState();
}

class _EduGameScreenState extends State<EduGameScreen> {
  bool _isLoading = true;
  List<dynamic> _games = [];
  List<dynamic> _filteredGames = [];

  final TextEditingController _searchController = TextEditingController();
  String _selectedMode = 'Semua';

  final List<String> _gameModes = [
    'Semua',
    'Kuis Speed',
    'Spin Wheel',
    'Memory Match',
    'Mario Runner'
  ];

  @override
  void initState() {
    super.initState();
    _fetchGames();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchGames() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    setState(() => _isLoading = true);

    final res = await ApiService.get('game', params: {'user_id': userId.toString()});
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _games = List<dynamic>.from(res['data'] ?? []);
          _applyFilters();
          _isLoading = false;
        });
      } else {
        setState(() {
          _games = [];
          _applyFilters();
          _isLoading = false;
        });
        if (res['message'] != null && res['message'].toString().isNotEmpty && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(res['message'].toString()),
              backgroundColor: Colors.red.shade700,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    }
  }

  void _applyFilters() {
    final query = _searchController.text.trim().toLowerCase();

    setState(() {
      _filteredGames = _games.where((game) {
        final judul = (game['judul'] ?? game['nama_game'] ?? '').toString().toLowerCase();
        final mapel = (game['nama_mapel'] ?? '').toString().toLowerCase();
        final guru = (game['nama_guru'] ?? '').toString().toLowerCase();
        final tipe = (game['tipe_game'] ?? '').toString().toLowerCase().trim();

        final matchesSearch = query.isEmpty ||
            judul.contains(query) ||
            mapel.contains(query) ||
            guru.contains(query);

        bool matchesMode = true;
        if (_selectedMode == 'Kuis Speed') {
          matchesMode = tipe == 'quiz_speed' || tipe.contains('speed');
        } else if (_selectedMode == 'Spin Wheel') {
          matchesMode = tipe == 'spin_wheel' || tipe.contains('spin') || tipe.contains('wheel');
        } else if (_selectedMode == 'Memory Match') {
          matchesMode = tipe == 'memory_match' || tipe.contains('memory');
        } else if (_selectedMode == 'Mario Runner') {
          matchesMode = tipe == 'mario_run' || tipe == 'runner' || tipe.contains('mario');
        }

        return matchesSearch && matchesMode;
      }).toList();
    });
  }

  String _formatTipeGame(String? tipe) {
    switch (tipe?.toLowerCase()) {
      case 'quiz_speed':
        return 'Kuis Speed ⚡';
      case 'spin_wheel':
        return 'Spin Wheel 🎡';
      case 'memory_match':
        return 'Memory Match 🧩';
      case 'mario_run':
      case 'runner':
        return 'Runner Quiz 🎮';
      default:
        return 'Kuis Interaktif 🎯';
    }
  }

  IconData _getModeIcon(String mode) {
    switch (mode) {
      case 'Kuis Speed':
        return Icons.bolt_rounded;
      case 'Spin Wheel':
        return Icons.rotate_right_rounded;
      case 'Memory Match':
        return Icons.extension_rounded;
      case 'Mario Runner':
        return Icons.directions_run_rounded;
      case 'Semua':
      default:
        return Icons.grid_view_rounded;
    }
  }

  Color _getTipeColor(String? tipe) {
    switch (tipe?.toLowerCase()) {
      case 'quiz_speed':
        return Colors.orange.shade800;
      case 'spin_wheel':
        return Colors.purple.shade800;
      case 'memory_match':
        return Colors.blue.shade800;
      case 'mario_run':
      case 'runner':
        return Colors.green.shade800;
      default:
        return Colors.indigo.shade800;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('EduGame & Arena Kuis', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 17)),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: AppTheme.guruGradient,
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            tooltip: 'Muat Ulang Data',
            onPressed: _fetchGames,
          ),
        ],
      ),
      body: Column(
        children: [
          // Hero Header Box
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 20),
            decoration: BoxDecoration(
              gradient: AppTheme.guruGradient,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(24)),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0F172A).withValues(alpha: 0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.sports_esports_rounded, color: Color(0xFF38BDF8), size: 28),
                    SizedBox(width: 10),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Arena Belajar Game Edukasi',
                          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          'Bermain sambil mengasah pengetahuan!',
                          style: TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Search Bar
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.08),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: TextField(
                    controller: _searchController,
                    onChanged: (_) => _applyFilters(),
                    style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                    decoration: InputDecoration(
                      hintText: 'Cari judul game atau mata pelajaran...',
                      hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8), fontWeight: FontWeight.normal),
                      prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF6B21A8)),
                      suffixIcon: _searchController.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded, size: 18, color: Color(0xFF94A3B8)),
                              onPressed: () {
                                _searchController.clear();
                                _applyFilters();
                              },
                            )
                          : null,
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(height: 14),

                // Game Mode Chips Filter Row (High Contrast Custom Chips)
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: _gameModes.map((mode) {
                      final isSelected = _selectedMode == mode;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            onTap: () {
                              setState(() {
                                _selectedMode = mode;
                                _applyFilters();
                              });
                            },
                            borderRadius: BorderRadius.circular(20),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 7),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? Colors.white
                                    : const Color(0x33FFFFFF), // Frosted glass 20%
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: isSelected
                                      ? Colors.white
                                      : const Color(0x80FFFFFF), // Visible 50% border
                                  width: 1.4,
                                ),
                                boxShadow: isSelected
                                    ? [
                                        BoxShadow(
                                          color: Colors.black.withValues(alpha: 0.18),
                                          blurRadius: 6,
                                          offset: const Offset(0, 2),
                                        ),
                                      ]
                                    : null,
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _getModeIcon(mode),
                                    size: 15,
                                    color: isSelected ? const Color(0xFF5B21B6) : Colors.white,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    mode,
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                                      color: isSelected ? const Color(0xFF5B21B6) : Colors.white,
                                      letterSpacing: 0.2,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),

          // Main Game List Body
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchGames,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: Colors.purple))
                  : _filteredGames.isEmpty
                      ? SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(20),
                                  decoration: BoxDecoration(
                                    color: Colors.purple.shade50,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(Icons.videogame_asset_off_rounded, size: 54, color: Colors.purple.shade400),
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  _searchController.text.isNotEmpty || _selectedMode != 'Semua'
                                      ? 'Tidak ada game yang sesuai filter'
                                      : 'Belum Ada Game Edukasi di Database',
                                  style: TextStyle(
                                    color: isDark ? Colors.white : Colors.black87,
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  _searchController.text.isNotEmpty || _selectedMode != 'Semua'
                                      ? 'Coba sesuaikan pencarian atau pilih mode game lainnya.'
                                      : 'Game edukasi interaktif dari database sekolah akan muncul di sini.',
                                  style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                                  textAlign: TextAlign.center,
                                ),
                                const SizedBox(height: 20),
                                ElevatedButton.icon(
                                  onPressed: _fetchGames,
                                  icon: const Icon(Icons.refresh_rounded, size: 18),
                                  label: const Text('Segarkan Data'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.purple.shade800,
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
                          itemCount: _filteredGames.length,
                          itemBuilder: (context, index) {
                            final game = _filteredGames[index];
                            final gameId = int.tryParse((game['id'] ?? 0).toString()) ?? 0;
                            final judul = (game['judul'] ?? game['nama_game'] ?? 'Game Edukasi').toString();
                            final deskripsi = (game['deskripsi'] ?? '').toString();
                            final mapelName = (game['nama_mapel'] ?? 'Mata Pelajaran').toString();
                            final guruName = (game['nama_guru'] ?? 'Guru Pengampu').toString();
                            final tipeGame = (game['tipe_game'] ?? 'quiz_speed').toString();
                            final totalSoal = int.tryParse((game['total_soal'] ?? 0).toString()) ?? 0;
                            final kkm = int.tryParse((game['kkm'] ?? 75).toString()) ?? 75;
                            final myBestScore = game['my_best_score'] != null ? int.tryParse(game['my_best_score'].toString()) : null;
                            final myStatus = game['my_status']?.toString();

                            final modeColor = _getTipeColor(tipeGame);

                            return Container(
                              margin: const EdgeInsets.only(bottom: 16),
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.grey.shade200),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.04),
                                    blurRadius: 8,
                                    offset: const Offset(0, 3),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          color: modeColor.withValues(alpha: 0.12),
                                          shape: BoxShape.circle,
                                        ),
                                        child: Icon(
                                          Icons.sports_esports_rounded,
                                          color: modeColor,
                                          size: 28,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              judul,
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                            ),
                                            const SizedBox(height: 3),
                                            Row(
                                              children: [
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: modeColor,
                                                    borderRadius: BorderRadius.circular(8),
                                                  ),
                                                  child: Text(
                                                    _formatTipeGame(tipeGame),
                                                    style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                                                  ),
                                                ),
                                                const SizedBox(width: 6),
                                                Text(
                                                  '• $totalSoal Soal',
                                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                                                ),
                                              ],
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),

                                  if (deskripsi.isNotEmpty) ...[
                                    const SizedBox(height: 10),
                                    Text(
                                      deskripsi,
                                      style: TextStyle(fontSize: 12.5, color: Colors.grey.shade700, height: 1.3),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],

                                  const SizedBox(height: 12),
                                  const Divider(height: 1),
                                  const SizedBox(height: 10),

                                  // Footer Meta Info Row & Best Score Badge
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              '📚 $mapelName',
                                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                            Text(
                                              '👨‍🏫 $guruName • KKM: $kkm Poin',
                                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ],
                                        ),
                                      ),

                                      if (myBestScore != null) ...[
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: myStatus == 'lulus' ? Colors.green.shade50 : Colors.orange.shade50,
                                            borderRadius: BorderRadius.circular(10),
                                            border: Border.all(color: myStatus == 'lulus' ? Colors.green.shade300 : Colors.orange.shade300),
                                          ),
                                          child: Text(
                                            '🏆 Best: $myBestScore Poin',
                                            style: TextStyle(
                                              fontSize: 10.5,
                                              fontWeight: FontWeight.bold,
                                              color: myStatus == 'lulus' ? Colors.green.shade900 : Colors.orange.shade900,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),

                                  const SizedBox(height: 14),

                                  // Play Button
                                  SizedBox(
                                    width: double.infinity,
                                    child: ElevatedButton.icon(
                                      onPressed: () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (_) => EduGamePlayScreen(
                                              gameId: gameId,
                                              gameDetail: Map<String, dynamic>.from(game),
                                            ),
                                          ),
                                        ).then((_) => _fetchGames());
                                      },
                                      icon: const Icon(Icons.play_circle_filled_rounded, size: 20),
                                      label: const Text('Mainkan Sekarang', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5)),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: modeColor,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        elevation: 2,
                                      ),
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
}
