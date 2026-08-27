import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import 'gabung_kelas_screen.dart';
import 'siswa_materi_tab.dart';
import 'siswa_tugas_tab.dart';
import 'siswa_cbt_tab.dart';

class LearningPathScreen extends StatefulWidget {
  const LearningPathScreen({super.key});

  @override
  State<LearningPathScreen> createState() => _LearningPathScreenState();
}

class _LearningPathScreenState extends State<LearningPathScreen> {
  bool _isLoading = true;
  String _selectedTab = 'semua'; // 'semua', 'dalam_proses', 'selesai', 'belum_dimulai'
  Map<String, dynamic> _data = {};
  final Set<int> _expandedMapelIds = {};

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
          _data = {
            'tingkat': user?.namaKelas ?? 'Kelas Siswa',
            'jurusan': user?.namaJurusan ?? 'Teknik & Kejuruan',
            'capaian_persen': 0,
            'total_mapel': 0,
            'selesai_count': 0,
            'proses_count': 0,
            'belum_count': 0,
            'mapel_list': []
          };
          _isLoading = false;
        });
      }
    }
  }

  void _toggleExpand(int mapelId) {
    setState(() {
      if (_expandedMapelIds.contains(mapelId)) {
        _expandedMapelIds.remove(mapelId);
      } else {
        _expandedMapelIds.add(mapelId);
      }
    });
  }

  void _handleStepAction(String actionType) {
    final type = actionType.toLowerCase();
    if (type == 'materi') {
      Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
        appBar: AppBar(title: const Text('Materi Pembelajaran'), backgroundColor: Colors.indigo.shade900, foregroundColor: Colors.white),
        body: const SiswaMateriTab(),
      )));
    } else if (type == 'tugas') {
      Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
        appBar: AppBar(title: const Text('Tugas & Penugasan KBM'), backgroundColor: Colors.indigo.shade900, foregroundColor: Colors.white),
        body: const SiswaTugasTab(),
      )));
    } else if (type == 'quiz' || type == 'cbt' || type == 'ujian') {
      Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
        appBar: AppBar(title: const Text('Kuis & Ujian CBT Online'), backgroundColor: Colors.indigo.shade900, foregroundColor: Colors.white),
        body: const SiswaCbtTab(),
      )));
    } else {
      _fetchPath();
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final int capaianPct = int.tryParse((_data['capaian_persen'] ?? 0).toString()) ?? 0;
    final int totalMapel = int.tryParse((_data['total_mapel'] ?? 0).toString()) ?? 0;
    final int selesaiCount = int.tryParse((_data['selesai_count'] ?? 0).toString()) ?? 0;
    final int prosesCount = int.tryParse((_data['proses_count'] ?? 0).toString()) ?? 0;
    final int belumCount = int.tryParse((_data['belum_count'] ?? 0).toString()) ?? 0;

    final List mapelListRaw = (_data['mapel_list'] is List) ? _data['mapel_list'] : [];

    final filteredMapel = mapelListRaw.where((m) {
      final cat = (m['status_category'] ?? 'dalam_proses').toString();
      if (_selectedTab == 'semua') return true;
      return cat == _selectedTab;
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Learning Path per Mapel', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: Colors.deepPurple.shade900,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            tooltip: 'Input Key Mapel',
            icon: const Icon(Icons.vpn_key_rounded, color: Colors.amberAccent),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const GabungKelasScreen()),
              ).then((_) => _fetchPath());
            },
          ),
        ],
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
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.amber.shade700,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  '🔥 Alur Belajar • Capaian $capaianPct%',
                                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                ),
                              ),
                              ElevatedButton.icon(
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(builder: (_) => const GabungKelasScreen()),
                                  ).then((_) => _fetchPath());
                                },
                                icon: const Icon(Icons.add_rounded, size: 16, color: Colors.black87),
                                label: const Text('Key Mapel', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.black87)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.amber,
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _data['tingkat'] ?? 'Kelas Siswa',
                            style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _data['jurusan'] ?? 'Teknik & Kejuruan',
                            style: const TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                          const SizedBox(height: 16),

                          // Stats Cards Row
                          Row(
                            children: [
                              Expanded(child: _buildBannerStat('Total Mapel', '$totalMapel', Icons.book_rounded, Colors.white)),
                              const SizedBox(width: 8),
                              Expanded(child: _buildBannerStat('Dalam Proses', '$prosesCount', Icons.timelapse_rounded, Colors.amber)),
                              const SizedBox(width: 8),
                              Expanded(child: _buildBannerStat('Selesai', '$selesaiCount', Icons.check_circle_rounded, Colors.greenAccent)),
                              const SizedBox(width: 8),
                              Expanded(child: _buildBannerStat('Belum', '$belumCount', Icons.lock_rounded, Colors.white70)),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // Filter Tabs Segmented Bar
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                      child: SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            _buildFilterTab('semua', '📋 Semua ($totalMapel)'),
                            const SizedBox(width: 8),
                            _buildFilterTab('dalam_proses', '🟡 Dalam Proses ($prosesCount)'),
                            const SizedBox(width: 8),
                            _buildFilterTab('selesai', '🎉 Selesai Tuntas ($selesaiCount)'),
                            const SizedBox(width: 8),
                            _buildFilterTab('belum_dimulai', '🔒 Belum Dimulai ($belumCount)'),
                          ],
                        ),
                      ),
                    ),

                    // Subject Cards List or Empty State
                    if (mapelListRaw.isEmpty)
                      _buildEmptyEnrolledState()
                    else if (filteredMapel.isEmpty)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 40),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.folder_off_outlined, size: 54, color: Colors.grey.shade400),
                              const SizedBox(height: 10),
                              const Text('Tidak ada mata pelajaran di kategori ini.', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                            ],
                          ),
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: filteredMapel.length,
                        itemBuilder: (context, index) {
                          final mapel = filteredMapel[index];
                          return _buildSubjectCard(mapel, isDark);
                        },
                      ),

                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildEmptyEnrolledState() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.indigo.shade100),
          boxShadow: [
            BoxShadow(
              color: Colors.indigo.withValues(alpha: 0.06),
              blurRadius: 15,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              width: 70,
              height: 70,
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.amber.shade200, width: 2),
              ),
              child: Icon(Icons.vpn_key_rounded, size: 36, color: Colors.amber.shade900),
            ),
            const SizedBox(height: 16),
            const Text(
              'Belum Ada Mapel Terdaftar',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const SizedBox(height: 8),
            Text(
              'Anda belum mendaftar di mata pelajaran apapun. Silakan masukkan Passcode Key resmi dari Guru Pengampu untuk mendaftar mapel dan melihat alur pembelajaran.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey.shade600, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const GabungKelasScreen()),
                  ).then((_) => _fetchPath());
                },
                icon: const Icon(Icons.key_rounded, color: Colors.white, size: 20),
                label: const Text(
                  '🔑 Input Passcode Key Mapel Baru',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.amber.shade800,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 3,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBannerStat(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 18, color: color),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 14)),
          Text(label, style: const TextStyle(color: Colors.white70, fontSize: 9.5)),
        ],
      ),
    );
  }

  Widget _buildFilterTab(String key, String label) {
    final isSelected = _selectedTab == key;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => setState(() => _selectedTab = key),
      selectedColor: Colors.deepPurple.shade900,
      backgroundColor: Colors.grey.shade200,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : Colors.deepPurple.shade900,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        fontSize: 12,
      ),
      showCheckmark: false,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      side: BorderSide.none,
    );
  }

  Widget _buildSubjectCard(dynamic m, bool isDark) {
    final int mapelId = int.tryParse((m['mapel_id'] ?? 0).toString()) ?? 0;
    final String namaMapel = (m['nama_mapel'] ?? 'Mata Pelajaran').toString();
    final String kodeMapel = (m['kode_mapel'] ?? 'MP').toString();
    final String namaGuru = (m['nama_guru'] ?? 'Guru Pengampu').toString();
    final String statusCat = (m['status_category'] ?? 'dalam_proses').toString();
    final String statusLabel = (m['status_label'] ?? 'Dalam Proses').toString();
    final int progressPct = int.tryParse((m['progress_percent'] ?? 0).toString()) ?? 0;
    final int currentStep = int.tryParse((m['current_step'] ?? 1).toString()) ?? 1;
    final List steps = (m['steps'] is List) ? m['steps'] : [];
    final List sequenceItems = (m['sequence_items'] is List) ? m['sequence_items'] : [];
    final bool isExpanded = _expandedMapelIds.contains(mapelId);

    Color badgeBg = Colors.amber.shade50;
    Color badgeFg = Colors.amber.shade900;
    if (statusCat == 'selesai') {
      badgeBg = Colors.green.shade50;
      badgeFg = Colors.green.shade900;
    } else if (statusCat == 'belum_dimulai') {
      badgeBg = Colors.grey.shade100;
      badgeFg = Colors.grey.shade700;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isExpanded ? Colors.deepPurple.shade300 : Colors.grey.shade200, width: isExpanded ? 1.5 : 1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          // Header Card Tile
          InkWell(
            onTap: () => _toggleExpand(mapelId),
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.deepPurple.shade50,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          kodeMapel,
                          style: TextStyle(color: Colors.deepPurple.shade900, fontWeight: FontWeight.bold, fontSize: 10),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: badgeBg,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          statusLabel,
                          style: TextStyle(color: badgeFg, fontWeight: FontWeight.bold, fontSize: 11),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Text(
                    namaMapel,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '✍️ $namaGuru',
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 12),

                  // Progress bar row
                  Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: LinearProgressIndicator(
                            value: (progressPct / 100).toDouble(),
                            backgroundColor: Colors.grey.shade200,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              statusCat == 'selesai' ? Colors.green.shade600 : (statusCat == 'belum_dimulai' ? Colors.grey.shade400 : Colors.amber.shade700),
                            ),
                            minHeight: 8,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        '$progressPct% (Tahap $currentStep/5)',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11.5),
                      ),
                      const SizedBox(width: 8),
                      Icon(
                        isExpanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                        color: Colors.deepPurple.shade900,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Expanded Roadmap & Sequence Items Section
          if (isExpanded) ...[
            const Divider(height: 1),
            Container(
              padding: const EdgeInsets.all(16),
              color: isDark ? const Color(0xFF0F172A) : Colors.deepPurple.shade50.withValues(alpha: 0.3),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (sequenceItems.isNotEmpty) ...[
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          '📋 Tahapan Modul & Evaluasi Pembelajaran:',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.deepPurple.shade100,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            '${sequenceItems.length} Item',
                            style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Colors.deepPurple.shade900),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: sequenceItems.length,
                      itemBuilder: (context, itemIdx) {
                        final item = sequenceItems[itemIdx];
                        return _buildSequenceItemRow(item, itemIdx + 1);
                      },
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 12),
                  ],

                  const Padding(
                    padding: EdgeInsets.only(bottom: 12),
                    child: Text(
                      '🗺️ Ringkasan Alur 5 Tahap Pembelajaran:',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                  ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: steps.length,
                    itemBuilder: (context, idx) {
                      final s = steps[idx];
                      final isLast = idx == steps.length - 1;
                      return _buildStepRoadmapItem(s, isLast: isLast);
                    },
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSequenceItemRow(dynamic item, int stepNo) {
    final String type = (item['type'] ?? 'materi').toString();
    final String title = (item['title'] ?? 'Item Pembelajaran').toString();
    final String desc = (item['desc'] ?? '').toString();
    final String guru = (item['guru'] ?? 'Guru Pengampu').toString();
    final bool isCompleted = item['is_completed'] == true;
    final String actionLabel = (item['action_label'] ?? 'Akses').toString();
    final String actionType = (item['action_type'] ?? type).toString();

    Color categoryBg = Colors.blue.shade50;
    Color categoryFg = Colors.blue.shade900;
    IconData categoryIcon = Icons.book_rounded;
    String categoryText = 'Modul Materi';

    if (type == 'tugas') {
      categoryBg = Colors.purple.shade50;
      categoryFg = Colors.purple.shade900;
      categoryIcon = Icons.assignment_rounded;
      categoryText = 'Penugasan KBM';
    } else if (type == 'quiz' || type == 'ujian') {
      categoryBg = Colors.amber.shade50;
      categoryFg = Colors.amber.shade900;
      categoryIcon = Icons.quiz_rounded;
      categoryText = 'Evaluasi CBT';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isCompleted ? Colors.green.shade200 : Colors.grey.shade200),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CircleAvatar(
            radius: 14,
            backgroundColor: isCompleted ? Colors.green.shade600 : Colors.deepPurple.shade900,
            child: Text(
              '$stepNo',
              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: categoryBg,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(categoryIcon, size: 11, color: categoryFg),
                          const SizedBox(width: 4),
                          Text(categoryText, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: categoryFg)),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: isCompleted ? Colors.green.shade50 : Colors.amber.shade50,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        isCompleted ? 'Selesai 🎉' : 'Belum Selesai ⏳',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: isCompleted ? Colors.green.shade800 : Colors.amber.shade900,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
                if (desc.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    desc,
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
                const SizedBox(height: 4),
                Text(
                  '✍️ $guru',
                  style: TextStyle(fontSize: 10.5, color: Colors.grey.shade500, fontStyle: FontStyle.italic),
                ),
                const SizedBox(height: 8),
                SizedBox(
                  height: 30,
                  child: ElevatedButton.icon(
                    onPressed: () => _handleStepAction(actionType),
                    icon: Icon(isCompleted ? Icons.check_circle_rounded : Icons.play_arrow_rounded, size: 14),
                    label: Text(actionLabel, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isCompleted ? Colors.green.shade700 : Colors.deepPurple.shade800,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      elevation: 0,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStepRoadmapItem(dynamic s, {required bool isLast}) {
    final String judul = (s['judul'] ?? 'Tahap').toString();
    final String sub = (s['sub'] ?? '-').toString();
    final bool isCompleted = s['is_completed'] == true;
    final bool isActive = s['is_active'] == true;
    final bool isLocked = s['is_locked'] == true;
    final String actionLabel = (s['action_label'] ?? 'Pelajari').toString();
    final String actionType = (s['action_type'] ?? 'materi').toString();
    final int completedCount = int.tryParse((s['completed_count'] ?? 0).toString()) ?? 0;
    final int totalCount = int.tryParse((s['total_count'] ?? 1).toString()) ?? 1;

    Color nodeColor = Colors.grey.shade400;
    IconData nodeIcon = Icons.lock_outline_rounded;

    if (isCompleted) {
      nodeColor = Colors.green.shade600;
      nodeIcon = Icons.check_circle_rounded;
    } else if (isActive) {
      nodeColor = Colors.amber.shade700;
      nodeIcon = Icons.play_circle_fill_rounded;
    } else if (!isLocked) {
      nodeColor = Colors.deepPurple.shade700;
      nodeIcon = Icons.radio_button_checked_rounded;
    }

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Column(
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: BoxDecoration(
                  color: nodeColor,
                  shape: BoxShape.circle,
                ),
                child: Icon(nodeIcon, color: Colors.white, size: 16),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 2,
                    margin: const EdgeInsets.symmetric(vertical: 2),
                    color: isCompleted ? Colors.green.shade400 : Colors.grey.shade300,
                  ),
                ),
            ],
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: isActive ? Colors.amber.shade400 : Colors.grey.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          judul,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                      Text(
                        '$completedCount/$totalCount',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: nodeColor),
                      ),
                    ],
                  ),
                  Text(sub, style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600)),
                  if (!isLocked) ...[
                    const SizedBox(height: 6),
                    SizedBox(
                      height: 32,
                      child: ElevatedButton.icon(
                        onPressed: () => _handleStepAction(actionType),
                        icon: Icon(isCompleted ? Icons.replay_rounded : Icons.arrow_forward_rounded, size: 14),
                        label: Text(isCompleted ? 'Tinjau' : actionLabel, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isCompleted ? Colors.grey.shade700 : (isActive ? Colors.amber.shade800 : Colors.deepPurple.shade800),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
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
