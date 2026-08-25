import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../models/quiz_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaCbtTab extends StatefulWidget {
  const SiswaCbtTab({super.key});

  @override
  State<SiswaCbtTab> createState() => _SiswaCbtTabState();
}

class _SiswaCbtTabState extends State<SiswaCbtTab> {
  @override
  void initState() {
    super.initState();
    _loadQuiz();
  }

  void _loadQuiz() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchQuiz(user.id);
    }
  }

  void _showRequestPermissionModal(QuizModel quiz) {
    final catatanController = TextEditingController(
      text: 'Mohon izin untuk membuka kunci kuis CBT / Ujian Susulan karena kuis sebelumnya disuspend / terkunci.',
    );

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.mark_email_unread_rounded, color: AppTheme.primaryColor),
            const SizedBox(width: 8),
            const Expanded(
              child: Text(
                'Minta Izin Guru Pengampu',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.amber.shade300),
              ),
              child: Row(
                children: [
                  const Icon(Icons.warning_amber_rounded, color: Colors.amber, size: 28),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Kuis "${quiz.judul}" telah terkunci/disuspend. Ajukan permohonan ke Guru Pengampu untuk membuka kembali akses kuis.',
                      style: TextStyle(fontSize: 12, color: Colors.amber.shade900),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'Alasan / Catatan ke Guru:',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: catatanController,
              maxLines: 3,
              style: const TextStyle(fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Tuliskan alasan permohonan izin Anda...',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.all(12),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton.icon(
            onPressed: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              if (user == null) return;
              final catatan = catatanController.text.trim();
              if (catatan.isEmpty) return;

              Navigator.pop(context);
              final ok = await Provider.of<SiswaProvider>(context, listen: false)
                  .requestQuizSusulan(user.id, quiz.id, catatan);

              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    ok
                        ? '📩 Permintaan izin Ujian Susulan / Buka Suspend telah dikirimkan ke Guru Pengampu!'
                        : 'Gagal mengirimkan permintaan izin',
                  ),
                  backgroundColor: ok ? Colors.green : Colors.red,
                ),
              );
            },
            icon: const Icon(Icons.send_rounded, size: 16),
            label: const Text('Kirim Permohonan'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ],
      ),
    );
  }

  void _confirmStartQuiz(QuizModel quiz) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.shield_outlined, color: AppTheme.primaryColor),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Portal CBT Ujian Online',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              quiz.judul,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
            ),
            Text(
              'Mapel: ${quiz.namaMapel} • Durasi: ${quiz.durasiMenit} Menit',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.red.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.gavel_rounded, color: Colors.red, size: 18),
                      SizedBox(width: 6),
                      Text(
                        'Aturan Pengawasan Otomatis:',
                        style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red, fontSize: 13),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  _buildRuleItem('Layar HP akan otomatis dikunci ke Mode Fullscreen.'),
                  _buildRuleItem('Dilarang meminimalkan app, keluar fullscreen, atau berpindah aplikasi.'),
                  _buildRuleItem('Toleransi pelanggaran 1x peringatan. Lebih dari itu, kuis otomatis DISUSPEND & DIBATALKAN!'),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              _startQuizExam(quiz);
            },
            icon: const Icon(Icons.fullscreen_rounded),
            label: const Text('Mulai Ujian (Fullscreen)'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRuleItem(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.check_circle, color: Colors.red, size: 14),
          const SizedBox(width: 6),
          Expanded(
            child: Text(
              text,
              style: TextStyle(fontSize: 12, color: Colors.red.shade900, height: 1.2),
            ),
          ),
        ],
      ),
    );
  }

  void _startQuizExam(QuizModel quiz) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final detailData = await Provider.of<SiswaProvider>(context, listen: false)
        .fetchQuizDetail(user.id, quiz.id);

    if (!mounted) return;

    if (detailData == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(quiz.accessReason ?? 'Gagal memuat soal quiz atau akses ujian telah disuspend/terkunci.'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    final soalList = (detailData['soal'] as List? ?? [])
        .map((e) => SoalModel.fromJson(e))
        .toList();

    if (soalList.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Quiz ini belum memiliki soal!')),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => CbtExamEngineScreen(quiz: quiz, soalList: soalList),
      ),
    ).then((result) {
      _loadQuiz();
      if (result == 'disqualified' && mounted) {
        _showDisqualifiedAlert(quiz);
      }
    });
  }

  void _showDisqualifiedAlert(QuizModel quiz) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.cancel_rounded, color: Colors.red, size: 28),
            SizedBox(width: 8),
            Expanded(
              child: Text(
                '🚫 DISUSPEND & DIBATALKAN!',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.red),
              ),
            ),
          ],
        ),
        content: const Text(
          'Anda telah melanggar aturan ujian sebanyak 2 kali (berpindah aplikasi / keluar fullscreen).\n\nPengerjaan kuis Anda secara otomatis DIBERHENTIKAN dan DISUSPEND. Silakan ajukan izin ke Guru Pengampu untuk pengerjaan susulan.',
          style: TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Tutup'),
          ),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              _showRequestPermissionModal(quiz);
            },
            icon: const Icon(Icons.mark_email_unread_rounded, size: 16),
            label: const Text('Minta Izin Guru Pengampu 📩'),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red.shade700),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final quizList = siswaProvider.quizList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '🎯 CBT & Quiz Ujian',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              IconButton(
                icon: const Icon(Icons.refresh, color: AppTheme.primaryColor),
                onPressed: _loadQuiz,
                tooltip: 'Refresh Kuis',
              ),
            ],
          ),
          const SizedBox(height: 12),

          Expanded(
            child: siswaProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : quizList.isEmpty
                    ? const Center(child: Text('Belum ada quiz atau ujian CBT.'))
                    : ListView.builder(
                        itemCount: quizList.length,
                        itemBuilder: (context, index) {
                          final q = quizList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Text(
                                          q.namaMapel,
                                          style: const TextStyle(
                                            color: AppTheme.primaryColor,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                      _buildStatusBadge(q),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    q.judul,
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    "Durasi: ${q.durasiMenit} Menit • Jumlah Soal: ${q.jumlahSoal}",
                                    style: const TextStyle(fontSize: 13, color: Colors.grey),
                                  ),
                                  if (q.isSuspended) ...[
                                    const SizedBox(height: 8),
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: Colors.red.shade50,
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(color: Colors.red.shade200),
                                      ),
                                      child: Row(
                                        children: [
                                          const Icon(Icons.gavel_rounded, color: Colors.red, size: 16),
                                          const SizedBox(width: 6),
                                          Expanded(
                                            child: Text(
                                              q.accessReason ?? 'Kuis disuspend/didiskualifikasi karena melanggar aturan ujian online.',
                                              style: TextStyle(fontSize: 11, color: Colors.red.shade900, fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: 12),
                                  Align(
                                    alignment: Alignment.centerRight,
                                    child: _buildActionButton(q),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(QuizModel q) {
    if (q.isSuspended) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.red.shade100,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.red.shade300),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.block, color: Colors.red, size: 12),
            SizedBox(width: 4),
            Text(
              'DISUSPEND 🚫',
              style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 11),
            ),
          ],
        ),
      );
    } else if (q.isCompleted) {
      final isLulus = q.statusLulus == 'lulus';
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: isLulus ? Colors.green.withValues(alpha: 0.15) : Colors.red.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          'Nilai: ${q.totalNilai} (${q.statusLulus?.toUpperCase()})',
          style: TextStyle(
            color: isLulus ? Colors.green : Colors.red,
            fontWeight: FontWeight.bold,
            fontSize: 11,
          ),
        ),
      );
    } else {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.purple.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Text(
          'Tersedia',
          style: TextStyle(
            color: Colors.purple,
            fontWeight: FontWeight.bold,
            fontSize: 11,
          ),
        ),
      );
    }
  }

  Widget _buildActionButton(QuizModel q) {
    if (q.isSuspended) {
      if (q.susulanStatus == 'pending') {
        return ElevatedButton.icon(
          onPressed: () => _showRequestPermissionModal(q),
          icon: const Icon(Icons.hourglass_top_rounded, size: 16),
          label: const Text('Izin Terkirim ⏳ (Menunggu Guru)'),
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.amber.shade800,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      } else {
        return ElevatedButton.icon(
          onPressed: () => _showRequestPermissionModal(q),
          icon: const Icon(Icons.mark_email_unread_rounded, size: 16),
          label: const Text('Minta Izin Guru Pengampu 📩'),
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.red.shade700,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      }
    } else if (q.isCompleted) {
      return ElevatedButton.icon(
        onPressed: () => _confirmStartQuiz(q),
        icon: const Icon(Icons.replay),
        label: const Text('Kerjakan Ulang'),
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.grey.shade700,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    } else {
      return ElevatedButton.icon(
        onPressed: () => _confirmStartQuiz(q),
        icon: const Icon(Icons.play_arrow_rounded),
        label: const Text('Mulai Ujian (Fullscreen)'),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primaryColor,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    }
  }
}

// Interactive CBT Exam Engine Screen (Strict Anti-Cheating & Fullscreen Enabled)
class CbtExamEngineScreen extends StatefulWidget {
  final QuizModel quiz;
  final List<SoalModel> soalList;

  const CbtExamEngineScreen({super.key, required this.quiz, required this.soalList});

  @override
  State<CbtExamEngineScreen> createState() => _CbtExamEngineScreenState();
}

class _CbtExamEngineScreenState extends State<CbtExamEngineScreen> with WidgetsBindingObserver {
  int _currentIndex = 0;
  final Map<int, int> _answers = {}; // [soal_id => pilihan_id]
  late Timer _timer;
  late int _remainingSeconds;

  int _warningCount = 0;
  bool _isExamActive = true;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    // 1. Enable Fullscreen Sticky Immersive Mode
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);

    _remainingSeconds = widget.quiz.durasiMenit * 60;
    _startTimer();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    // 2. Restore Standard System Overlays (Status Bar & Navigation Bar)
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: SystemUiOverlay.values);
    _timer.cancel();
    super.dispose();
  }

  // 3. Monitor Lifecycle Changes (Detect App Minimize, Home Button, Switch App)
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (!_isExamActive || _isSubmitting) return;

    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      _handleSecurityViolation();
    }
  }

  void _handleSecurityViolation() async {
    if (!_isExamActive || _isSubmitting) return;

    _warningCount++;
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      // Send background violation ping to API
      Provider.of<SiswaProvider>(context, listen: false)
          .recordQuizViolation(user.id, widget.quiz.id);
    }

    if (_warningCount < 2) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.warning_rounded, color: Colors.orange, size: 28),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  '⚠️ PERINGATAN KECURANGAN (1/2)',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.orange),
                ),
              ),
            ],
          ),
          content: const Text(
            'Anda terdeteksi keluar dari aplikasi / layar penuh!\n\nMohon tetap berada di dalam aplikasi kuis CBT. Jika Anda melanggar 1x lagi, ujian Anda akan DIBATALKAN & DISUSPEND secara otomatis oleh sistem!',
            style: TextStyle(fontSize: 13),
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
              },
              style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
              child: const Text('Kembali ke Ujian Fullscreen'),
            ),
          ],
        ),
      );
    } else {
      // Force exit and auto suspend exam on 2nd violation
      _isExamActive = false;
      _timer.cancel();
      _submitExam(isForceDisqualified: true);
    }
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remainingSeconds > 0) {
        if (mounted) {
          setState(() {
            _remainingSeconds--;
          });
        }
      } else {
        _timer.cancel();
        _submitExam();
      }
    });
  }

  String _formatTimer(int totalSeconds) {
    final minutes = (totalSeconds ~/ 60).toString().padLeft(2, '0');
    final seconds = (totalSeconds % 60).toString().padLeft(2, '0');
    return "$minutes:$seconds";
  }

  void _submitExam({bool isForceDisqualified = false}) async {
    if (_isSubmitting) return;
    _isSubmitting = true;
    _isExamActive = false;
    _timer.cancel();

    // Restore standard UI System Chrome
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: SystemUiOverlay.values);

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) {
      if (mounted) Navigator.pop(context, isForceDisqualified ? 'disqualified' : null);
      return;
    }

    if (isForceDisqualified) {
      // Instantly pop back to SiswaCbtTab without keeping user on exam screen
      if (mounted) Navigator.pop(context, 'disqualified');
      Provider.of<SiswaProvider>(context, listen: false)
          .submitQuiz(user.id, widget.quiz.id, _answers);
      return;
    }

    final res = await Provider.of<SiswaProvider>(context, listen: false)
        .submitQuiz(user.id, widget.quiz.id, _answers);

    if (!mounted) return;

    if (isForceDisqualified) {
      Navigator.pop(context);
      return;
    }

    if (res['success'] == true) {
      final data = res['data'];
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('🎉 Ujian Selesai!'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'NILAI ANDA: ${data['total_nilai']}',
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
              ),
              const SizedBox(height: 8),
              Text(
                'Status: ${data['status_lulus'] == 'lulus' ? 'LULUS ✅' : 'TIDAK LULUS ❌'}',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: data['status_lulus'] == 'lulus' ? Colors.green : Colors.red,
                ),
              ),
            ],
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                Navigator.pop(context);
              },
              child: const Text('Kembali ke Dashboard'),
            ),
          ],
        ),
      );
    } else {
      Navigator.pop(context);
    }
  }

  void _showExitWarning() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Keluar dari Ujian?'),
        content: const Text(
          'Ujian masih berlangsung. Jika Anda keluar sekarang, pengerjaan kuis akan dianggap sebagai pelanggaran keamanan!',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Lanjutkan Ujian'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _handleSecurityViolation();
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Keluar Paksa'),
          ),
        ],
      ),
    );
  }

  void _showQuestionGridModalSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        final answeredCount = _answers.length;
        final totalCount = widget.soalList.length;

        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              height: MediaQuery.of(context).size.height * 0.7,
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.grid_view_rounded, color: AppTheme.primaryColor),
                          SizedBox(width: 8),
                          Text(
                            'Navigasi Nomor Soal Ujian',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          'Sudah Dijawab: $answeredCount Dari $totalCount Soal',
                          style: TextStyle(color: Colors.grey.shade700, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          '${((answeredCount / (totalCount == 0 ? 1 : totalCount)) * 100).toInt()}% Selesai',
                          style: const TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.bold, fontSize: 11),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Indicator Legend
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildLegendItem(Colors.green, 'Sudah Dijawab ✓'),
                      _buildLegendItem(AppTheme.primaryColor, 'Sedang Dibuka 🎯'),
                      _buildLegendItem(Colors.grey.shade400, 'Belum Dijawab ⚪'),
                    ],
                  ),
                  const SizedBox(height: 16),

                  Expanded(
                    child: GridView.builder(
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 5,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                        childAspectRatio: 1.1,
                      ),
                      itemCount: totalCount,
                      itemBuilder: (context, idx) {
                        final qItem = widget.soalList[idx];
                        final isAnswered = _answers.containsKey(qItem.id);
                        final isCurrent = idx == _currentIndex;

                        Color bgColor;
                        Color textColor;
                        BorderSide border;

                        if (isCurrent) {
                          bgColor = AppTheme.primaryColor;
                          textColor = Colors.white;
                          border = const BorderSide(color: Colors.amber, width: 2.5);
                        } else if (isAnswered) {
                          bgColor = Colors.green;
                          textColor = Colors.white;
                          border = BorderSide.none;
                        } else {
                          bgColor = Colors.grey.shade100;
                          textColor = Colors.black87;
                          border = BorderSide(color: Colors.grey.shade300);
                        }

                        return InkWell(
                          onTap: () {
                            setState(() {
                              _currentIndex = idx;
                            });
                            Navigator.pop(context);
                          },
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            decoration: BoxDecoration(
                              color: bgColor,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.fromBorderSide(border),
                              boxShadow: isCurrent
                                  ? [BoxShadow(color: AppTheme.primaryColor.withValues(alpha: 0.4), blurRadius: 6)]
                                  : null,
                            ),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  '${idx + 1}',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 15,
                                    color: textColor,
                                  ),
                                ),
                                if (isAnswered && !isCurrent)
                                  const Icon(Icons.check_circle, color: Colors.white, size: 12)
                                else if (isCurrent)
                                  const Icon(Icons.edit, color: Colors.amber, size: 12),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _showImagePreviewDialog(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (_) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(12),
        child: Stack(
          alignment: Alignment.topRight,
          children: [
            InteractiveViewer(
              minScale: 0.5,
              maxScale: 4.0,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.contain,
                ),
              ),
            ),
            Positioned(
              top: 10,
              right: 10,
              child: CircleAvatar(
                backgroundColor: Colors.black.withValues(alpha: 0.7),
                child: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLegendItem(Color color, String label) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(3)),
        ),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final soal = widget.soalList[_currentIndex];

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        _showExitWarning();
      },
      child: Scaffold(
        appBar: AppBar(
          automaticallyImplyLeading: false,
          title: Text('CBT: ${widget.quiz.judul}', style: const TextStyle(fontSize: 16)),
          actions: [
            InkWell(
              onTap: _showQuestionGridModalSheet,
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                margin: const EdgeInsets.only(right: 6),
                decoration: BoxDecoration(
                  color: Colors.blue.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.grid_view_rounded, color: Colors.blue, size: 16),
                    const SizedBox(width: 4),
                    Text(
                      '${_answers.length}/${widget.soalList.length} Dijawab',
                      style: const TextStyle(color: Colors.blue, fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              margin: const EdgeInsets.only(right: 12),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  const Icon(Icons.timer, color: Colors.red, size: 16),
                  const SizedBox(width: 4),
                  Text(
                    _formatTimer(_remainingSeconds),
                    style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ],
              ),
            ),
          ],
        ),
        body: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Soal Nomor ${_currentIndex + 1} dari ${widget.soalList.length}',
                    style: const TextStyle(fontSize: 14, color: Colors.grey, fontWeight: FontWeight.bold),
                  ),
                  Text('Bobot: ${soal.bobot} Poin', style: const TextStyle(fontSize: 12, color: Colors.blue)),
                ],
              ),
              const SizedBox(height: 12),
              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        soal.pertanyaan,
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                      if (soal.hasGambar) ...[
                        const SizedBox(height: 12),
                        InkWell(
                          onTap: () => _showImagePreviewDialog(context, soal.fileGambarUrl!),
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.grey.shade300),
                              color: Colors.grey.shade50,
                            ),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: Stack(
                                children: [
                                  Image.network(
                                    soal.fileGambarUrl!,
                                    fit: BoxFit.contain,
                                    width: double.infinity,
                                    height: 280,
                                    errorBuilder: (context, error, stackTrace) => Container(
                                      padding: const EdgeInsets.all(16),
                                      color: Colors.red.shade50,
                                      child: Row(
                                        children: [
                                          const Icon(Icons.broken_image_rounded, color: Colors.red, size: 24),
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                const Text(
                                                  'Gagal memuat gambar soal',
                                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.red),
                                                ),
                                                Text(
                                                  soal.fileGambarUrl ?? '',
                                                  maxLines: 1,
                                                  overflow: TextOverflow.ellipsis,
                                                  style: TextStyle(fontSize: 10, color: Colors.red.shade800),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    loadingBuilder: (context, child, loadingProgress) {
                                      if (loadingProgress == null) return child;
                                      return Container(
                                        height: 140,
                                        alignment: Alignment.center,
                                        child: Column(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            const CircularProgressIndicator(strokeWidth: 2.5),
                                            const SizedBox(height: 8),
                                            Text(
                                              'Memuat gambar soal...',
                                              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),
                                  Positioned(
                                    right: 8,
                                    bottom: 8,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: Colors.black.withValues(alpha: 0.65),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: const Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(Icons.zoom_in, color: Colors.white, size: 14),
                                          SizedBox(width: 4),
                                          Text(
                                            'Perbesar Gambar',
                                            style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text('Pilihan Jawaban:', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),

              Expanded(
                child: ListView.builder(
                  itemCount: soal.pilihan.length,
                  itemBuilder: (context, idx) {
                    final pil = soal.pilihan[idx];
                    final isSelected = _answers[soal.id] == pil.id;
                    return Card(
                      color: isSelected ? AppTheme.primaryColor.withValues(alpha: 0.15) : null,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                        side: isSelected
                            ? const BorderSide(color: AppTheme.primaryColor, width: 1.5)
                            : BorderSide.none,
                      ),
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isSelected ? AppTheme.primaryColor : Colors.grey.shade300,
                          foregroundColor: isSelected ? Colors.white : Colors.black,
                          child: Text(String.fromCharCode(65 + idx)),
                        ),
                        title: Text(pil.teksPilihan, style: const TextStyle(fontSize: 14)),
                        onTap: () {
                          setState(() {
                            _answers[soal.id] = pil.id;
                          });
                        },
                      ),
                    );
                  },
                ),
              ),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  if (_currentIndex > 0)
                    OutlinedButton.icon(
                      onPressed: () {
                        setState(() {
                          _currentIndex--;
                        });
                      },
                      icon: const Icon(Icons.arrow_back, size: 16),
                      label: const Text('Sebelumnya'),
                    )
                  else
                    const SizedBox(),
                  ElevatedButton.icon(
                    onPressed: _showQuestionGridModalSheet,
                    icon: const Icon(Icons.grid_view_rounded, size: 16),
                    label: Text('${_currentIndex + 1}/${widget.soalList.length} 🔢'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.amber.shade800,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                  if (_currentIndex < widget.soalList.length - 1)
                    ElevatedButton.icon(
                      onPressed: () {
                        setState(() {
                          _currentIndex++;
                        });
                      },
                      icon: const Icon(Icons.arrow_forward, size: 16),
                      label: const Text('Selanjutnya'),
                    )
                  else
                    ElevatedButton.icon(
                      onPressed: () => _submitExam(),
                      icon: const Icon(Icons.check_circle_rounded, size: 16),
                      style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                      label: const Text('Kirim Selesai'),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
