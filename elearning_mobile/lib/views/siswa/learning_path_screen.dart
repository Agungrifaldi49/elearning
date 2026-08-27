import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class LearningPathScreen extends StatefulWidget {
  const LearningPathScreen({super.key});

  @override
  State<LearningPathScreen> createState() => _LearningPathScreenState();
}

class _LearningPathScreenState extends State<LearningPathScreen> {
  bool _isLoading = true;
  Map<String, dynamic> _data = {};

  @override
  void initState() {
    super.initState();
    _fetchPath();
  }

  Future<void> _fetchPath() async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;
    final res = await ApiService.get('siswa/learning_path?user_id=$userId');
    if (mounted) {
      if (res['success'] == true && res['data'] is Map<String, dynamic>) {
        setState(() {
          _data = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _data = _getDefaultFallbackData(user?.namaKelas, user?.namaJurusan);
          _isLoading = false;
        });
      }
    }
  }

  Map<String, dynamic> _getDefaultFallbackData(String? kelas, String? jurusan) {
    return {
      'tingkat': kelas ?? 'Kelas XII RPL 1',
      'jurusan': jurusan ?? 'Rekayasa Perangkat Lunak & Game',
      'capaian_persen': 68,
      'modul_selesai': 11,
      'total_modul': 15,
      'current_step': 3,
      'steps': [
        {
          'step': 1,
          'judul': '📘 1. Memahami Materi Pembelajaran',
          'sub': 'Membaca modul, e-book, & menyimak video KBM',
          'completed_count': 15,
          'total_count': 15,
          'percent': 100,
          'is_completed': true,
          'is_active': false,
          'is_locked': false,
          'action_type': 'materi',
          'action_label': 'Pelajari Materi'
        },
        {
          'step': 2,
          'judul': '📝 2. Pengerjaan Tugas & Latihan',
          'sub': 'Mengumpulkan PR, tugas praktikum, & kejuruan',
          'completed_count': 8,
          'total_count': 10,
          'percent': 80,
          'is_completed': true,
          'is_active': false,
          'is_locked': false,
          'action_type': 'tugas',
          'action_label': 'Kerjakan Tugas'
        },
        {
          'step': 3,
          'judul': '💡 3. Kuis Harian & Formatif',
          'sub': 'Mengikuti kuis harian, speed quiz, & review bab',
          'completed_count': 4,
          'total_count': 6,
          'percent': 66,
          'is_completed': false,
          'is_active': true,
          'is_locked': false,
          'action_type': 'kuis',
          'action_label': 'Ikuti Kuis Harian'
        },
        {
          'step': 4,
          'judul': '🎯 4. Ujian Tengah Semester (UTS)',
          'sub': 'Evaluasi capaian tengah semester via CBT Engine',
          'completed_count': 0,
          'total_count': 1,
          'percent': 0,
          'is_completed': false,
          'is_active': false,
          'is_locked': false,
          'action_type': 'cbt',
          'action_label': 'Ikuti UTS / CBT'
        },
        {
          'step': 5,
          'judul': '🏆 5. Ujian Akhir Semester (UAS)',
          'sub': 'Evaluasi kelulusan akhir semester & sertifikasi',
          'completed_count': 0,
          'total_count': 1,
          'percent': 0,
          'is_completed': false,
          'is_active': false,
          'is_locked': true,
          'action_type': 'cbt',
          'action_label': 'Ikuti UAS'
        }
      ]
    };
  }

  void _handleStepAction(String actionType) {
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final int capaianPct = int.tryParse((_data['capaian_persen'] ?? 68).toString()) ?? 68;
    final List steps = (_data['steps'] is List) ? _data['steps'] : _getDefaultFallbackData(null, null)['steps'];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Learning Path & Alur Belajar', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: Colors.deepPurple.shade900,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.deepPurple))
          : RefreshIndicator(
              onRefresh: _fetchPath,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Hero Progress Banner
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.deepPurple.shade900, Colors.indigo.shade900],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.amber.shade700,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: const Text(
                                  '🔥 Level: Pembelajar Aktif',
                                  style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Text(
                            _data['tingkat'] ?? 'Kelas XII',
                            style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _data['jurusan'] ?? 'Jurusan Kejuruan SMK',
                            style: const TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                          const SizedBox(height: 16),

                          // Progress Stats Card inside Banner
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                            ),
                            child: Row(
                              children: [
                                SizedBox(
                                  width: 52,
                                  height: 52,
                                  child: Stack(
                                    fit: StackFit.expand,
                                    children: [
                                      CircularProgressIndicator(
                                        value: (capaianPct / 100).toDouble(),
                                        strokeWidth: 6,
                                        backgroundColor: Colors.white24,
                                        valueColor: const AlwaysStoppedAnimation<Color>(Colors.amber),
                                      ),
                                      Center(
                                        child: Text(
                                          '$capaianPct%',
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Capaian Alur Pembelajaran',
                                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Selesaikan seluruh 5 tahapan untuk mencapai ketuntasan kurikulum.',
                                        style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11),
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

                    // Section Title
                    const Padding(
                      padding: EdgeInsets.fromLTRB(16, 20, 16, 12),
                      child: Text(
                        '🗺️ Stepper Roadmap Alur Pembelajaran',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    ),

                    // 5-Step Timeline Nodes
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: steps.length,
                      itemBuilder: (context, index) {
                        final stepData = steps[index];
                        final isLast = index == steps.length - 1;
                        return _buildTimelineStepCard(stepData, isLast: isLast, isDark: isDark);
                      },
                    ),

                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildTimelineStepCard(dynamic s, {required bool isLast, required bool isDark}) {
    final int stepNum = int.tryParse((s['step'] ?? 1).toString()) ?? 1;
    final String judul = (s['judul'] ?? 'Tahap Pembelajaran').toString();
    final String sub = (s['sub'] ?? '-').toString();
    final bool isCompleted = s['is_completed'] == true;
    final bool isActive = s['is_active'] == true;
    final bool isLocked = s['is_locked'] == true;
    final String actionLabel = (s['action_label'] ?? 'Pelajari').toString();
    final String actionType = (s['action_type'] ?? 'materi').toString();
    final int completedCount = int.tryParse((s['completed_count'] ?? 0).toString()) ?? 0;
    final int totalCount = int.tryParse((s['total_count'] ?? 1).toString()) ?? 1;
    final int percent = int.tryParse((s['percent'] ?? 0).toString()) ?? 0;

    Color nodeColor = Colors.grey.shade400;
    IconData nodeIcon = Icons.lock_outline_rounded;
    String statusLabel = 'Terkunci 🔒';
    Color statusBg = Colors.grey.shade100;
    Color statusFg = Colors.grey.shade700;

    if (isCompleted) {
      nodeColor = Colors.green.shade600;
      nodeIcon = Icons.check_circle_rounded;
      statusLabel = 'Selesai Tuntas ✅';
      statusBg = Colors.green.shade50;
      statusFg = Colors.green.shade900;
    } else if (isActive) {
      nodeColor = Colors.amber.shade700;
      nodeIcon = Icons.play_circle_fill_rounded;
      statusLabel = 'Sedang Berlangsung 🟡';
      statusBg = Colors.amber.shade50;
      statusFg = Colors.amber.shade900;
    } else if (!isLocked) {
      nodeColor = Colors.deepPurple.shade700;
      nodeIcon = Icons.radio_button_checked_rounded;
      statusLabel = 'Siap Diikuti 🚀';
      statusBg = Colors.indigo.shade50;
      statusFg = Colors.indigo.shade900;
    }

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Timeline Node Indicator Column
          Column(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: nodeColor,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: nodeColor.withValues(alpha: 0.3),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Icon(nodeIcon, color: Colors.white, size: 20),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 3,
                    margin: const EdgeInsets.symmetric(vertical: 4),
                    color: isCompleted ? Colors.green.shade400 : Colors.grey.shade300,
                  ),
                ),
            ],
          ),
          const SizedBox(width: 14),

          // Content Card Column
          Expanded(
            child: Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isActive ? Colors.amber.shade400 : (isCompleted ? Colors.green.shade300 : Colors.grey.shade200),
                  width: isActive ? 2 : 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
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
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: statusBg,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          statusLabel,
                          style: TextStyle(color: statusFg, fontWeight: FontWeight.bold, fontSize: 11),
                        ),
                      ),
                      Text(
                        'Step $stepNum of 5',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    judul,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    sub,
                    style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 12),

                  // Progress Bar & Counts
                  Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: LinearProgressIndicator(
                            value: (percent / 100).toDouble(),
                            backgroundColor: Colors.grey.shade200,
                            valueColor: AlwaysStoppedAnimation<Color>(nodeColor),
                            minHeight: 6,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Text(
                        '$completedCount/$totalCount',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11.5),
                      ),
                    ],
                  ),

                  if (!isLocked) ...[
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      height: 38,
                      child: ElevatedButton.icon(
                        onPressed: () => _handleStepAction(actionType),
                        icon: Icon(
                          isCompleted ? Icons.replay_rounded : Icons.arrow_forward_rounded,
                          size: 16,
                        ),
                        label: Text(
                          isCompleted ? 'Tinjau Ulang' : actionLabel,
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isCompleted ? Colors.grey.shade800 : (isActive ? Colors.amber.shade800 : Colors.deepPurple.shade800),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          elevation: 0,
                        ),
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
}
