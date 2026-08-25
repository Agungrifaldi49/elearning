import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/quiz_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaQuizReviewScreen extends StatefulWidget {
  final QuizModel quiz;
  const SiswaQuizReviewScreen({super.key, required this.quiz});

  @override
  State<SiswaQuizReviewScreen> createState() => _SiswaQuizReviewScreenState();
}

class _SiswaQuizReviewScreenState extends State<SiswaQuizReviewScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _reviewData;

  @override
  void initState() {
    super.initState();
    _loadReview();
  }

  void _loadReview() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final data = await Provider.of<SiswaProvider>(context, listen: false)
        .fetchQuizReview(user.id, widget.quiz.id);

    if (mounted) {
      setState(() {
        _reviewData = data;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Hasil & Pembahasan: ${widget.quiz.judul}', style: const TextStyle(fontSize: 16)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _reviewData == null
              ? const Center(child: Text('Gagal memuat hasil kuis.'))
              : _buildReviewContent(),
    );
  }

  Widget _buildReviewContent() {
    final hasil = _reviewData!['hasil'] ?? {};
    final summary = _reviewData!['summary'] ?? {};
    final soalList = (_reviewData!['soal'] as List? ?? []);

    final totalNilai = double.tryParse((hasil['total_nilai'] ?? widget.quiz.totalNilai ?? 0).toString()) ?? 0.0;
    final statusLulus = (hasil['status_lulus'] ?? widget.quiz.statusLulus ?? 'lulus').toString().toLowerCase();
    final isLulus = statusLulus == 'lulus';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Card - Nilai & Status
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isLulus
                    ? [Colors.green.shade700, Colors.teal.shade900]
                    : [Colors.red.shade700, Colors.red.shade900],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: (isLulus ? Colors.green : Colors.red).withValues(alpha: 0.3),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                const Text(
                  'NILAI HASIL AKHIR',
                  style: TextStyle(color: Colors.white70, fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 1.2),
                ),
                const SizedBox(height: 6),
                Text(
                  totalNilai.toStringAsFixed(1),
                  style: const TextStyle(color: Colors.white, fontSize: 44, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    isLulus ? 'LULUS UJIAN ✅' : 'TIDAK LULUS ❌',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildMetricBox('Total Soal', '${summary['total_soal'] ?? 0}'),
                    _buildMetricBox('Benar', '${summary['total_benar'] ?? 0}', color: Colors.greenAccent),
                    _buildMetricBox('Salah', '${summary['total_salah'] ?? 0}', color: Colors.redAccent),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          const Text(
            '📝 Pembahasan Jawaban Soal:',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: soalList.length,
            itemBuilder: (context, idx) {
              final s = soalList[idx];
              final pilihanList = (s['pilihan'] as List? ?? []);
              final jSiswa = s['jawaban_siswa'];
              final isCorrect = jSiswa != null && (jSiswa['is_benar'] == 1 || jSiswa['is_benar'].toString() == '1');
              final selectedPilihanId = jSiswa != null && jSiswa['pilihan_id'] != null ? int.tryParse(jSiswa['pilihan_id'].toString()) : null;

              return Card(
                margin: const EdgeInsets.only(bottom: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(
                    color: isCorrect ? Colors.green.shade300 : Colors.red.shade300,
                    width: 1.5,
                  ),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Soal #${idx + 1}',
                              style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryColor, fontSize: 12),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: isCorrect ? Colors.green.shade100 : Colors.red.shade100,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              isCorrect ? 'Poin: ${s['bobot']} / ${s['bobot']} (Benar ✓)' : 'Poin: 0 / ${s['bobot']} (Salah ✗)',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: isCorrect ? Colors.green.shade900 : Colors.red.shade900,
                                fontSize: 11,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Text(
                        s['pertanyaan'] ?? '',
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                      if (s['file_gambar_url'] != null && s['file_gambar_url'].toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Image.network(
                            s['file_gambar_url'],
                            height: 180,
                            width: double.infinity,
                            fit: BoxFit.contain,
                            errorBuilder: (_, __, ___) => const SizedBox(),
                          ),
                        ),
                      ],
                      const SizedBox(height: 14),

                      // Choices List
                      Column(
                        children: List.generate(pilihanList.length, (pIdx) {
                          final pil = pilihanList[pIdx];
                          final pilId = int.parse(pil['id'].toString());
                          final isBenarOption = pil['is_benar'] == 1 || pil['is_benar'].toString() == '1';
                          final isSelectedByStudent = selectedPilihanId == pilId;

                          Color bgColor = Colors.grey.shade50;
                          Color borderColor = Colors.grey.shade300;
                          Color badgeBg = Colors.grey.shade300;
                          Color badgeText = Colors.black;
                          String suffixLabel = '';

                          if (isBenarOption) {
                            bgColor = Colors.green.shade50;
                            borderColor = Colors.green.shade500;
                            badgeBg = Colors.green.shade600;
                            badgeText = Colors.white;
                            suffixLabel = isSelectedByStudent ? ' (Jawaban Anda - Benar ✓)' : ' (Jawaban Benar ✓)';
                          } else if (isSelectedByStudent) {
                            bgColor = Colors.red.shade50;
                            borderColor = Colors.red.shade500;
                            badgeBg = Colors.red.shade600;
                            badgeText = Colors.white;
                            suffixLabel = ' (Jawaban Anda - Salah ✗)';
                          }

                          return Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: bgColor,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: borderColor, width: isBenarOption || isSelectedByStudent ? 1.5 : 1),
                            ),
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 14,
                                  backgroundColor: badgeBg,
                                  child: Text(
                                    String.fromCharCode(65 + pIdx),
                                    style: TextStyle(color: badgeText, fontSize: 12, fontWeight: FontWeight.bold),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    "${pil['teks_pilihan']}$suffixLabel",
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: isBenarOption || isSelectedByStudent ? FontWeight.bold : FontWeight.normal,
                                      color: isBenarOption
                                          ? Colors.green.shade900
                                          : (isSelectedByStudent ? Colors.red.shade900 : Colors.black87),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        }),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildMetricBox(String label, String value, {Color color = Colors.white}) {
    return Column(
      children: [
        Text(value, style: TextStyle(color: color, fontSize: 20, fontWeight: FontWeight.bold)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
      ],
    );
  }
}
