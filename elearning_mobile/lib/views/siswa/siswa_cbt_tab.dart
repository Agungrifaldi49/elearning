import 'dart:async';
import 'package:flutter/material.dart';
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

  void _startQuizExam(QuizModel quiz) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final detailData = await Provider.of<SiswaProvider>(context, listen: false)
        .fetchQuizDetail(user.id, quiz.id);

    if (detailData == null || !mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal memuat soal quiz')),
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
    ).then((_) => _loadQuiz());
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
          const Text(
            '🎯 CBT & Quiz Ujian',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
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
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        q.namaMapel,
                                        style: const TextStyle(
                                          color: AppTheme.primaryColor,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: q.isCompleted
                                              ? (q.statusLulus == 'lulus' ? Colors.green.withOpacity(0.15) : Colors.red.withOpacity(0.15))
                                              : Colors.purple.withOpacity(0.15),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          q.isCompleted
                                              ? 'Nilai: ${q.totalNilai} (${q.statusLulus?.toUpperCase()})'
                                              : 'Tersedia',
                                          style: TextStyle(
                                            color: q.isCompleted
                                                ? (q.statusLulus == 'lulus' ? Colors.green : Colors.red)
                                                : Colors.purple,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
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
                                  const SizedBox(height: 12),
                                  Align(
                                    alignment: Alignment.centerRight,
                                    child: ElevatedButton.icon(
                                      onPressed: () => _startQuizExam(q),
                                      icon: Icon(q.isCompleted ? Icons.replay : Icons.play_arrow_rounded),
                                      label: Text(q.isCompleted ? 'Kerjakan Ulang' : 'Mulai Ujian'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: q.isCompleted ? Colors.grey.shade700 : AppTheme.primaryColor,
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
        ],
      ),
    );
  }
}

// Interactive CBT Exam Engine Screen
class CbtExamEngineScreen extends StatefulWidget {
  final QuizModel quiz;
  final List<SoalModel> soalList;

  const CbtExamEngineScreen({super.key, required this.quiz, required this.soalList});

  @override
  State<CbtExamEngineScreen> createState() => _CbtExamEngineScreenState();
}

class _CbtExamEngineScreenState extends State<CbtExamEngineScreen> {
  int _currentIndex = 0;
  final Map<int, int> _answers = {}; // [soal_id => pilihan_id]
  late Timer _timer;
  late int _remainingSeconds;

  @override
  void initState() {
    super.initState();
    _remainingSeconds = widget.quiz.durasiMenit * 60;
    _startTimer();
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remainingSeconds > 0) {
        setState(() {
          _remainingSeconds--;
        });
      } else {
        _timer.cancel();
        _submitExam();
      }
    });
  }

  @override
  void dispose() {
    _timer.cancel();
    super.dispose();
  }

  String _formatTimer(int totalSeconds) {
    final minutes = (totalSeconds ~/ 60).toString().padLeft(2, '0');
    final seconds = (totalSeconds % 60).toString().padLeft(2, '0');
    return "$minutes:$seconds";
  }

  void _submitExam() async {
    _timer.cancel();
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final res = await Provider.of<SiswaProvider>(context, listen: false)
        .submitQuiz(user.id, widget.quiz.id, _answers);

    if (!mounted) return;

    if (res['success'] == true) {
      final data = res['data'];
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => AlertDialog(
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
    }
  }

  @override
  Widget build(BuildContext context) {
    final soal = widget.soalList[_currentIndex];

    return Scaffold(
      appBar: AppBar(
        title: Text('CBT: ${widget.quiz.judul}'),
        actions: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            margin: const EdgeInsets.only(right: 16),
            decoration: BoxDecoration(
              color: Colors.red.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: [
                const Icon(Icons.timer, color: Colors.red, size: 18),
                const SizedBox(width: 6),
                Text(
                  _formatTimer(_remainingSeconds),
                  style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ],
            ),
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(20.0),
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
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Text(
                  soal.pertanyaan,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
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
                    color: isSelected ? AppTheme.primaryColor.withOpacity(0.15) : null,
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: isSelected ? AppTheme.primaryColor : Colors.grey.shade300,
                        foregroundColor: isSelected ? Colors.white : Colors.black,
                        child: Text(String.fromCharCode(65 + idx)),
                      ),
                      title: Text(pil.teksPilihan),
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
                  OutlinedButton(
                    onPressed: () {
                      setState(() {
                        _currentIndex--;
                      });
                    },
                    child: const Text('Sebelumnya'),
                  )
                else
                  const SizedBox(),
                if (_currentIndex < widget.soalList.length - 1)
                  ElevatedButton(
                    onPressed: () {
                      setState(() {
                        _currentIndex++;
                      });
                    },
                    child: const Text('Selanjutnya'),
                  )
                else
                  ElevatedButton(
                    onPressed: _submitExam,
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                    child: const Text('Kirim Ujian Selesai'),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
