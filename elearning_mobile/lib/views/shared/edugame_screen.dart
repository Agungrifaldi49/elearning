import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class EduGameScreen extends StatefulWidget {
  const EduGameScreen({super.key});

  @override
  State<EduGameScreen> createState() => _EduGameScreenState();
}

class _EduGameScreenState extends State<EduGameScreen> {
  bool _isLoading = true;
  List<dynamic> _games = [];

  @override
  void initState() {
    super.initState();
    _fetchGames();
  }

  Future<void> _fetchGames() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('game');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _games = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _games = [
            {
              'nama_game': 'Kuis Cerdas Cermat SMK',
              'deskripsi': 'Uji wawasan umum dan kejuruanmu di kuis interaktif!',
              'kategori': 'Kuis Interaktif',
              'level': 'Sedang'
            },
            {
              'nama_game': 'Tebak Istilah IT & Kejuruan',
              'deskripsi': 'Game tebak kata seputar istilah keahlian SMK.',
              'kategori': 'Puzzle & Istilah',
              'level': 'Mudah'
            }
          ];
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('EduGame & Kuis Interaktif'),
        backgroundColor: Colors.purple,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchGames,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _games.length,
                itemBuilder: (context, index) {
                  final game = _games[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    elevation: 4,
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.purple.withValues(alpha: 0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.sports_esports_rounded,
                                  color: Colors.purple,
                                  size: 32,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      game['nama_game'] ?? 'Game Edukasi',
                                      style: const TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Chip(
                                      label: Text(
                                        game['kategori'] ?? 'Umum',
                                        style: const TextStyle(
                                          fontSize: 12,
                                          color: Colors.white,
                                        ),
                                      ),
                                      backgroundColor: Colors.purple,
                                      visualDensity: VisualDensity.compact,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Text(
                            game['deskripsi'] ?? '',
                            style: TextStyle(
                              color: Colors.grey.shade800,
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: () {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Memulai ${game['nama_game']}...'),
                                    backgroundColor: Colors.purple,
                                  ),
                                );
                              },
                              icon: const Icon(Icons.play_arrow_rounded),
                              label: const Text('Mainkan Sekarang'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.purple,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
