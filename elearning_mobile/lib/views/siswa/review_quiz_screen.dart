import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class ReviewQuizScreen extends StatefulWidget {
  final int quizId;
  const ReviewQuizScreen({super.key, required this.quizId});

  @override
  State<ReviewQuizScreen> createState() => _ReviewQuizScreenState();
}

class _ReviewQuizScreenState extends State<ReviewQuizScreen> {
  bool _isLoading = true;
  List<dynamic> _reviews = [];

  @override
  void initState() {
    super.initState();
    _fetchReview();
  }

  Future<void> _fetchReview() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('siswa/review_quiz?quiz_id=${widget.quizId}');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _reviews = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _reviews = [];
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Review Jawaban Quiz'),
        backgroundColor: Colors.purple,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _reviews.isEmpty
              ? const Center(child: Text('Belum ada data review jawaban.'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _reviews.length,
                  itemBuilder: (context, index) {
                    final r = _reviews[index];
                    final isBenar = r['is_benar'] == 1 || r['is_benar'] == '1';
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(
                                  isBenar ? Icons.check_circle : Icons.cancel,
                                  color: isBenar ? Colors.green : Colors.red,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'Soal #${index + 1}',
                                  style: const TextStyle(fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(r['pertanyaan'] ?? '', style: const TextStyle(fontSize: 15)),
                            const SizedBox(height: 8),
                            Text(
                              'Jawaban Anda: ${r['jawaban_siswa'] ?? '-'}',
                              style: TextStyle(color: isBenar ? Colors.green.shade800 : Colors.red.shade800),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
