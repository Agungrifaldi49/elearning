import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import 'edugame_play_screen.dart';

class EduGameResultScreen extends StatefulWidget {
  final int gameId;
  final Map<String, dynamic> gameDetail;
  final int score;
  final int maxCombo;
  final int totalCorrect;
  final int totalSoal;
  final int totalTimeSeconds;
  final int userId;

  const EduGameResultScreen({
    super.key,
    required this.gameId,
    required this.gameDetail,
    required this.score,
    required this.maxCombo,
    required this.totalCorrect,
    required this.totalSoal,
    required this.totalTimeSeconds,
    required this.userId,
  });

  @override
  State<EduGameResultScreen> createState() => _EduGameResultScreenState();
}

class _EduGameResultScreenState extends State<EduGameResultScreen> {
  bool _isSaving = true;
  bool _isPassed = false;
  int _kkm = 75;
  List<dynamic> _leaderboard = [];

  @override
  void initState() {
    super.initState();
    _kkm = int.tryParse((widget.gameDetail['kkm'] ?? 75).toString()) ?? 75;
    _isPassed = widget.score >= _kkm;
    _submitScore();
  }

  Future<void> _submitScore() async {
    setState(() => _isSaving = true);

    final res = await ApiService.post('game/submit_score', {
      'game_id': widget.gameId.toString(),
      'user_id': widget.userId.toString(),
      'skor_akhir': widget.score.toString(),
      'max_combo': widget.maxCombo.toString(),
      'total_benar': widget.totalCorrect.toString(),
      'total_soal': widget.totalSoal.toString(),
      'waktu_selesai': widget.totalTimeSeconds.toString(),
    });

    if (mounted) {
      if (res['success'] == true && res['data'] != null) {
        final lb = (res['data']['leaderboard'] as List?) ?? [];
        final stLulus = res['data']['status_lulus'] == 'lulus';
        setState(() {
          _isPassed = stLulus;
          _leaderboard = lb;
          _isSaving = false;
        });
      } else {
        setState(() => _isSaving = false);
      }
    }
  }

  String _formatTime(int seconds) {
    final m = seconds ~/ 60;
    final s = seconds % 60;
    if (m > 0) {
      return '$m m $s s';
    }
    return '$s Detik';
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Hasil Permainan', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.purple.shade800,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        automaticallyImplyLeading: false,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Result Trophy Header Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: _isPassed
                      ? [Colors.purple.shade800, Colors.indigo.shade900]
                      : [Colors.deepOrange.shade800, Colors.red.shade900],
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: (_isPassed ? Colors.purple : Colors.red).withValues(alpha: 0.3),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(
                      color: Colors.white24,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      _isPassed ? Icons.emoji_events_rounded : Icons.replay_rounded,
                      size: 56,
                      color: Colors.amber,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    _isPassed ? '🎉 SELAMAT! ANDA LULUS' : '💪 HASIL PERMAINAN',
                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _isPassed
                        ? 'Skor Anda melampaui KKM ($_kkm Poin)'
                        : 'Tetap semangat! KKM game ini adalah $_kkm Poin',
                    style: const TextStyle(color: Colors.white70, fontSize: 12.5),
                  ),
                  const SizedBox(height: 16),

                  // Big Score Display
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      '${widget.score} POIN',
                      style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.bold,
                        color: _isPassed ? Colors.purple.shade900 : Colors.red.shade900,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Performance Statistics Breakdown Grid
            Row(
              children: [
                _buildStatTile(
                  'Benar',
                  '${widget.totalCorrect} / ${widget.totalSoal}',
                  Colors.green,
                  Icons.check_circle_rounded,
                  isDark,
                ),
                const SizedBox(width: 10),
                _buildStatTile(
                  'Max Combo',
                  '🔥 x${widget.maxCombo}',
                  Colors.orange,
                  Icons.local_fire_department_rounded,
                  isDark,
                ),
                const SizedBox(width: 10),
                _buildStatTile(
                  'Waktu',
                  _formatTime(widget.totalTimeSeconds),
                  Colors.blue,
                  Icons.timer_rounded,
                  isDark,
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Leaderboard Title
            const Row(
              children: [
                Icon(Icons.leaderboard_rounded, color: Colors.purple, size: 22),
                SizedBox(width: 8),
                Text(
                  '🏆 Papan Peringkat Top 10 Siswa',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Leaderboard List
            if (_isSaving)
              const Padding(padding: EdgeInsets.all(20), child: Center(child: CircularProgressIndicator()))
            else if (_leaderboard.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1E293B) : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: const Text(
                  'Belum ada peringkat tersimpan.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: _leaderboard.length,
                itemBuilder: (context, index) {
                  final lb = _leaderboard[index];
                  final name = (lb['nama_siswa'] ?? 'Siswa').toString();
                  final kelas = (lb['nama_kelas'] ?? '-').toString();
                  final score = lb['skor_akhir'] ?? 0;
                  final maxCombo = lb['max_combo'] ?? 0;

                  String rankBadge = '${index + 1}';
                  Color rankColor = Colors.grey.shade700;
                  if (index == 0) {
                    rankBadge = '🥇';
                    rankColor = Colors.amber;
                  } else if (index == 1) {
                    rankBadge = '🥈';
                    rankColor = Colors.blueGrey;
                  } else if (index == 2) {
                    rankBadge = '🥉';
                    rankColor = Colors.orange.shade800;
                  }

                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: index < 3 ? rankColor.withValues(alpha: 0.5) : Colors.grey.shade200),
                    ),
                    child: Row(
                      children: [
                        SizedBox(
                          width: 28,
                          child: Text(
                            rankBadge,
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                name,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                '$kelas • Max Combo x$maxCombo',
                                style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.purple.shade50,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            '$score Poin',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.purple.shade900),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),

            const SizedBox(height: 24),

            // Navigation Buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                    },
                    icon: const Icon(Icons.arrow_back_rounded),
                    label: const Text('Arena Game'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(
                          builder: (_) => EduGamePlayScreen(
                            gameId: widget.gameId,
                            gameDetail: widget.gameDetail,
                          ),
                        ),
                      );
                    },
                    icon: const Icon(Icons.replay_rounded),
                    label: const Text('Main Lagi', style: TextStyle(fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatTile(String label, String value, Color color, IconData icon, bool isDark) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E293B) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color),
            ),
            Text(
              label,
              style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      ),
    );
  }
}
