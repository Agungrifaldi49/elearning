import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import 'edugame_result_screen.dart';

class EduGamePlayScreen extends StatefulWidget {
  final int gameId;
  final Map<String, dynamic> gameDetail;

  const EduGamePlayScreen({
    super.key,
    required this.gameId,
    required this.gameDetail,
  });

  @override
  State<EduGamePlayScreen> createState() => _EduGamePlayScreenState();
}

class _EduGamePlayScreenState extends State<EduGamePlayScreen> with TickerProviderStateMixin {
  bool _isLoading = true;
  List<dynamic> _soalList = [];

  int _currentIndex = 0;
  int _score = 0;
  int _combo = 0;
  int _maxCombo = 0;
  int _totalCorrect = 0;
  int _totalTimeSeconds = 0;

  int _questionDuration = 15;
  int _timeRemaining = 15;
  Timer? _questionTimer;
  Timer? _totalTimer;

  String? _selectedOption;
  bool _hasAnswered = false;
  String? _lastFeedbackMessage;
  bool? _lastAnswerCorrect;

  late AnimationController _progressController;

  String get _tipeGame => (widget.gameDetail['tipe_game'] ?? 'quiz_speed').toString().toLowerCase();

  Color get _themeColor {
    switch (_tipeGame) {
      case 'car_racing':
        return const Color(0xFFE11D48); // Rose/Red (Turbo Racing)
      case 'mario_run':
        return const Color(0xFFD97706); // Amber/Gold (Mario Runner)
      case 'spin_wheel':
        return const Color(0xFF0D9488); // Teal (Spin Wheel)
      case 'memory_match':
        return const Color(0xFF7C3AED); // Deep Purple (Memory Match)
      case 'quiz_speed':
      default:
        return const Color(0xFF4F46E5); // Indigo (Speed Battle)
    }
  }

  Color get _themeDarkColor {
    switch (_tipeGame) {
      case 'car_racing':
        return const Color(0xFF9F1239);
      case 'mario_run':
        return const Color(0xFF92400E);
      case 'spin_wheel':
        return const Color(0xFF115E59);
      case 'memory_match':
        return const Color(0xFF5B21B6);
      case 'quiz_speed':
      default:
        return const Color(0xFF3730A3);
    }
  }

  String get _modeTitle {
    switch (_tipeGame) {
      case 'car_racing':
        return '🏎️ Turbo Car Racing';
      case 'mario_run':
        return '🍄 Super Mario Runner';
      case 'spin_wheel':
        return '🎡 Spin Wheel Quiz';
      case 'memory_match':
        return '🧩 Memory Match';
      case 'quiz_speed':
      default:
        return '⚡ Quiz Speed Battle';
    }
  }

  String get _modeBannerDesc {
    switch (_tipeGame) {
      case 'car_racing':
        return 'Jalur Balapan Nitro • Jawab Cepat & Pacu Kecepatan Mobil!';
      case 'mario_run':
        return 'Platformer Retro • Taklukkan Rintangan & Kumpulkan Koin!';
      case 'spin_wheel':
        return 'Roda Putar Kuis • Raih Poin Maksimal!';
      case 'memory_match':
        return 'Kartu Asosiasi Memori • Fokus & Jawab Tepat!';
      case 'quiz_speed':
      default:
        return 'Adu Kecepatan & Ketepatan • Cetak Rekor Skor Tertinggi!';
    }
  }

  @override
  void initState() {
    super.initState();
    _questionDuration = int.tryParse((widget.gameDetail['durasi_per_soal'] ?? 15).toString()) ?? 15;
    _timeRemaining = _questionDuration;

    _progressController = AnimationController(
      vsync: this,
      duration: Duration(seconds: _questionDuration),
    );

    _loadGameData();
  }

  @override
  void dispose() {
    _questionTimer?.cancel();
    _totalTimer?.cancel();
    _progressController.dispose();
    super.dispose();
  }

  Future<void> _loadGameData() async {
    setState(() => _isLoading = true);

    final res = await ApiService.get('game/play', params: {'id': widget.gameId.toString()});
    if (mounted) {
      if (res['success'] == true && res['data'] != null) {
        final sList = (res['data']['soal'] as List?) ?? [];
        setState(() {
          _soalList = sList;
          _isLoading = false;
        });
        if (_soalList.isNotEmpty) {
          _startTotalTimer();
          _startQuestionTimer();
        }
      } else {
        setState(() => _isLoading = false);
        if (res['message'] != null && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(res['message'].toString()),
              backgroundColor: Colors.red.shade700,
            ),
          );
        }
      }
    }
  }

  void _startTotalTimer() {
    _totalTimer?.cancel();
    _totalTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() => _totalTimeSeconds++);
      }
    });
  }

  void _startQuestionTimer() {
    _questionTimer?.cancel();
    setState(() {
      _timeRemaining = _questionDuration;
      _selectedOption = null;
      _hasAnswered = false;
      _lastFeedbackMessage = null;
      _lastAnswerCorrect = null;
    });

    _progressController.stop();
    _progressController.value = 1.0;
    _progressController.reverse(from: 1.0);

    _questionTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_timeRemaining > 1) {
        setState(() => _timeRemaining--);
      } else {
        timer.cancel();
        _handleTimeOut();
      }
    });
  }

  void _handleTimeOut() {
    if (_hasAnswered) return;

    setState(() {
      _hasAnswered = true;
      _combo = 0;
      _lastAnswerCorrect = false;
      _lastFeedbackMessage = '⏰ WAKTU HABIS! Waktu menjawab pertanyaan ini telah habis.';
    });

    _progressController.stop();

    Future.delayed(const Duration(milliseconds: 2200), () {
      if (mounted) _nextQuestion();
    });
  }

  void _selectAnswer(String optionKey) {
    if (_hasAnswered) return;

    _questionTimer?.cancel();
    _progressController.stop();

    final currentSoal = _soalList[_currentIndex];
    final correctKey = (currentSoal['kunci_jawaban'] ?? 'a').toString().toLowerCase();
    final isCorrect = optionKey.toLowerCase() == correctKey;

    final questionPoint = int.tryParse((currentSoal['poin'] ?? 10).toString()) ?? 10;

    setState(() {
      _selectedOption = optionKey;
      _hasAnswered = true;
      _lastAnswerCorrect = isCorrect;

      if (isCorrect) {
        _totalCorrect++;
        _combo++;
        if (_combo > _maxCombo) _maxCombo = _combo;

        final comboBonus = (_combo > 1) ? (_combo * 2) : 0;
        final earned = questionPoint + comboBonus;
        _score += earned;

        if (_tipeGame == 'car_racing') {
          _lastFeedbackMessage = '🏎️ SPEED BOOST NITRO! +$earned Poin ${_combo > 1 ? "(Combo x$_combo 🔥)" : ""}';
        } else if (_tipeGame == 'mario_run') {
          _lastFeedbackMessage = '⭐ SUPER STAR POWER! +$earned Poin ${_combo > 1 ? "(Combo x$_combo 🔥)" : ""}';
        } else {
          _lastFeedbackMessage = '✨ JAWABAN TEPAT! +$earned Poin ${_combo > 1 ? "(Combo x$_combo 🔥)" : ""}';
        }
      } else {
        _combo = 0;
        _lastFeedbackMessage = '❌ JAWABAN KURANG TEPAT! Terus semangat!';
      }
    });

    Future.delayed(const Duration(milliseconds: 2300), () {
      if (mounted) _nextQuestion();
    });
  }

  void _nextQuestion() {
    if (_currentIndex < _soalList.length - 1) {
      setState(() {
        _currentIndex++;
      });
      _startQuestionTimer();
    } else {
      _finishGame();
    }
  }

  Future<void> _finishGame() async {
    _questionTimer?.cancel();
    _totalTimer?.cancel();
    _progressController.stop();

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    if (mounted) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => EduGameResultScreen(
            gameId: widget.gameId,
            gameDetail: widget.gameDetail,
            score: _score,
            maxCombo: _maxCombo,
            totalCorrect: _totalCorrect,
            totalSoal: _soalList.length,
            totalTimeSeconds: _totalTimeSeconds,
            userId: userId,
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (_isLoading) {
      return Scaffold(
        backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(color: _themeColor),
              const SizedBox(height: 16),
              const Text(
                'Memuat Arena Game Interaktif...',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
              ),
            ],
          ),
        ),
      );
    }

    if (_soalList.isEmpty) {
      return Scaffold(
        appBar: AppBar(
          title: Text(widget.gameDetail['judul'] ?? 'Game Edukasi'),
          backgroundColor: _themeDarkColor,
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.sentiment_dissatisfied_rounded, size: 64, color: Colors.grey.shade400),
                const SizedBox(height: 16),
                const Text(
                  'Belum ada soal tersedia untuk game ini.',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 20),
                ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Kembali'),
                ),
              ],
            ),
          ),
        ),
      );
    }

    final currentSoal = _soalList[_currentIndex];
    final questionText = (currentSoal['pertanyaan'] ?? '').toString();
    final opsiA = (currentSoal['opsi_a'] ?? '').toString();
    final opsiB = (currentSoal['opsi_b'] ?? '').toString();
    final opsiC = (currentSoal['opsi_c'] ?? '').toString();
    final opsiD = (currentSoal['opsi_d'] ?? '').toString();
    final correctKey = (currentSoal['kunci_jawaban'] ?? 'a').toString().toLowerCase();
    final penjelasan = (currentSoal['penjelasan'] ?? '').toString();

    final options = [
      {'key': 'a', 'text': opsiA},
      {'key': 'b', 'text': opsiB},
      if (opsiC.isNotEmpty) {'key': 'c', 'text': opsiC},
      if (opsiD.isNotEmpty) {'key': 'd', 'text': opsiD},
    ];

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Column(
          children: [
            Text(
              widget.gameDetail['judul'] ?? 'Game Edukasi',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            Text(
              _modeTitle,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w400, color: Colors.white70),
            ),
          ],
        ),
        backgroundColor: _themeDarkColor,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.pause_circle_filled_rounded, size: 26),
            tooltip: 'Hentikan Permainan',
            onPressed: () => _showPauseDialog(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Animated Timer Progress Bar
          AnimatedBuilder(
            animation: _progressController,
            builder: (context, child) {
              return LinearProgressIndicator(
                value: _progressController.value,
                minHeight: 6,
                backgroundColor: Colors.grey.shade300,
                valueColor: AlwaysStoppedAnimation<Color>(
                  _progressController.value > 0.4
                      ? _themeColor
                      : (_progressController.value > 0.2 ? Colors.orange : Colors.red),
                ),
              );
            },
          ),

          // Header Mode & Dashboard Bar
          Container(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isDark
                    ? [const Color(0xFF1E293B), const Color(0xFF0F172A)]
                    : [_themeColor.withValues(alpha: 0.12), _themeColor.withValues(alpha: 0.04)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              border: Border(bottom: BorderSide(color: _themeColor.withValues(alpha: 0.2))),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Soal Counter Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: _themeDarkColor,
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: _themeDarkColor.withValues(alpha: 0.3),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Text(
                        'Soal ${_currentIndex + 1} / ${_soalList.length}',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                    ),

                    // Combo Badge
                    if (_combo > 1)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(colors: [Colors.orange.shade700, Colors.red.shade700]),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.orange.withValues(alpha: 0.4),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Text(
                          '🔥 x$_combo Combo!',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ),

                    // Timer & Score Badge
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: _themeColor.withValues(alpha: 0.4)),
                          ),
                          child: Row(
                            children: [
                              Icon(Icons.timer_rounded, color: _themeDarkColor, size: 16),
                              const SizedBox(width: 4),
                              Text(
                                '${_timeRemaining}s',
                                style: TextStyle(color: _themeDarkColor, fontWeight: FontWeight.bold, fontSize: 12.5),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.amber.shade100,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.amber.shade400),
                          ),
                          child: Row(
                            children: [
                              Icon(Icons.star_rounded, color: Colors.amber.shade900, size: 16),
                              const SizedBox(width: 4),
                              Text(
                                '$_score Poin',
                                style: TextStyle(color: Colors.amber.shade900, fontWeight: FontWeight.bold, fontSize: 12.5),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    Icon(Icons.sports_esports_rounded, size: 14, color: _themeColor),
                    const SizedBox(width: 5),
                    Expanded(
                      child: Text(
                        _modeBannerDesc,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: isDark ? Colors.grey.shade300 : _themeDarkColor,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Main Game Play Body
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Feedback Banner (Speed Boost / Star Power / Correct / Wrong)
                  if (_hasAnswered && _lastFeedbackMessage != null) ...[
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 300),
                      width: double.infinity,
                      margin: const EdgeInsets.only(bottom: 14),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      decoration: BoxDecoration(
                        color: _lastAnswerCorrect == true ? Colors.green.shade600 : Colors.red.shade600,
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: (_lastAnswerCorrect == true ? Colors.green : Colors.red).withValues(alpha: 0.35),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          Icon(
                            _lastAnswerCorrect == true ? Icons.check_circle_rounded : Icons.info_outline_rounded,
                            color: Colors.white,
                            size: 20,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              _lastFeedbackMessage!,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  // Question Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.05),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                      border: Border.all(color: _themeColor.withValues(alpha: 0.2)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: _themeColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                widget.gameDetail['nama_mapel'] ?? 'Mata Pelajaran',
                                style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: _themeDarkColor),
                              ),
                            ),
                            const Spacer(),
                            Text(
                              'Poin: ${currentSoal['poin'] ?? 10}',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Text(
                          questionText,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, height: 1.4),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Options List
                  ...options.map((opt) {
                    final key = opt['key'] as String;
                    final text = opt['text'] as String;

                    final isSelected = _selectedOption == key;
                    final isCorrect = key.toLowerCase() == correctKey;

                    Color cardColor = isDark ? const Color(0xFF1E293B) : Colors.white;
                    Color borderColor = Colors.grey.shade300;
                    Color textColor = isDark ? Colors.white : Colors.black87;
                    IconData? statusIcon;

                    if (_hasAnswered) {
                      if (isCorrect) {
                        cardColor = Colors.green.shade50;
                        borderColor = Colors.green.shade500;
                        textColor = Colors.green.shade900;
                        statusIcon = Icons.check_circle_rounded;
                      } else if (isSelected) {
                        cardColor = Colors.red.shade50;
                        borderColor = Colors.red.shade500;
                        textColor = Colors.red.shade900;
                        statusIcon = Icons.cancel_rounded;
                      }
                    }

                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: InkWell(
                        onTap: () => _selectAnswer(key),
                        borderRadius: BorderRadius.circular(16),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 250),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: cardColor,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: borderColor, width: isSelected || (_hasAnswered && isCorrect) ? 2 : 1),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.03),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 34,
                                height: 34,
                                alignment: Alignment.center,
                                decoration: BoxDecoration(
                                  color: isSelected || (_hasAnswered && isCorrect)
                                      ? (isCorrect ? Colors.green : Colors.red)
                                      : _themeColor.withValues(alpha: 0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: Text(
                                  key.toUpperCase(),
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: isSelected || (_hasAnswered && isCorrect) ? Colors.white : _themeDarkColor,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Text(
                                  text,
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: isSelected || (_hasAnswered && isCorrect) ? FontWeight.bold : FontWeight.w500,
                                    color: textColor,
                                  ),
                                ),
                              ),
                              if (statusIcon != null)
                                Icon(statusIcon, color: isCorrect ? Colors.green : Colors.red, size: 22),
                            ],
                          ),
                        ),
                      ),
                    );
                  }),

                  // Explanation Box
                  if (_hasAnswered && penjelasan.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.blue.shade200),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.lightbulb_rounded, color: Colors.blue.shade800, size: 20),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Penjelasan Soal:',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: Colors.blue.shade900),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  penjelasan,
                                  style: TextStyle(fontSize: 12, color: Colors.blue.shade900),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showPauseDialog() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Icon(Icons.pause_circle_filled_rounded, color: _themeColor),
              const SizedBox(width: 8),
              const Text('Game Dihentikan'),
            ],
          ),
          content: const Text('Apakah Anda yakin ingin keluar dari permainan saat ini? Skor Anda belum tersimpan.'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Lanjutkan Game'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                Navigator.pop(context);
              },
              style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
              child: const Text('Keluar Permainan'),
            ),
          ],
        );
      },
    );
  }
}
