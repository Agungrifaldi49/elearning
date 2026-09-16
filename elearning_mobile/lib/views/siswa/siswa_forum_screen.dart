import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../models/forum_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../services/profanity_service.dart';
import '../../theme/app_theme.dart';
import 'forum_detail_screen.dart';

class SiswaForumScreen extends StatefulWidget {
  const SiswaForumScreen({super.key});

  @override
  State<SiswaForumScreen> createState() => _SiswaForumScreenState();
}

class _SiswaForumScreenState extends State<SiswaForumScreen> {
  List<ForumModel> _topics = [];
  List<ForumModel> _filteredTopics = [];
  bool _isLoading = false;
  String _selectedFilter = 'semua'; // 'semua', 'public', 'private'

  Future<XFile?> _pickImage() async {
    try {
      final picker = ImagePicker();
      return await picker.pickImage(source: ImageSource.gallery, imageQuality: 80, maxWidth: 1200);
    } catch (e) {
      debugPrint('Error picking image: $e');
      return null;
    }
  }

  void _showImageViewer(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.black.withValues(alpha: 0.9),
        insetPadding: EdgeInsets.zero,
        child: Stack(
          children: [
            Center(
              child: InteractiveViewer(
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const Icon(Icons.broken_image, color: Colors.white, size: 60),
                ),
              ),
            ),
            Positioned(
              top: 40,
              right: 20,
              child: IconButton(
                icon: const Icon(Icons.close, color: Colors.white, size: 30),
                onPressed: () => Navigator.pop(context),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    _loadForum();
  }

  Future<void> _loadForum() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    setState(() => _isLoading = true);

    final params = user != null ? {'user_id': user.id.toString()} : null;
    final res = await ApiService.get('forum/list', params: params);

    if (mounted) {
      List<ForumModel> list = [];
      if (res['success'] == true && res['data'] is List) {
        list = (res['data'] as List).map((e) => ForumModel.fromJson(e)).toList();
      }

      // Fallback topic if server list is empty to ensure UI is never blank
      if (list.isEmpty) {
        list = [
          ForumModel(
            id: 1,
            userId: user?.id ?? 1,
            judul: 'Selamat Datang di Forum Komunitas SMK Muthia Harapan Cicalengka',
            konten: 'Media diskusi resmi KBM, absensi QR Code, jadwal pelajaran, dan CBT Online SMK Muthia Harapan Cicalengka. Ketuk tombol + Topik Baru di kanan bawah untuk memulai diskusi!',
            kategori: 'Umum',
            visibility: 'public',
            targetNamaKelas: 'Semua Kelas',
            fullName: (user?.fullName.isNotEmpty == true) ? user!.fullName : 'Admin E-Learning',
            avatar: 'default_avatar.png',
            avatarUrl: user?.fullAvatarUrl,
            roleName: (user?.roleName.isNotEmpty == true) ? user!.roleName : 'Admin',
            totalKomentar: 0,
            createdAt: 'Baru Saja',
          )
        ];
      }

      setState(() {
        _topics = list;
        _applyFilter();
        _isLoading = false;
      });
    }
  }

  void _applyFilter() {
    final filter = _selectedFilter.toLowerCase().trim();
    if (filter == 'public') {
      _filteredTopics = _topics.where((t) => t.visibility.toLowerCase().trim() != 'private').toList();
    } else if (filter == 'private') {
      _filteredTopics = _topics.where((t) => t.visibility.toLowerCase().trim() == 'private').toList();
    } else {
      _filteredTopics = List.from(_topics);
    }
  }

  void _showNewTopicDialog() {
    final judulController = TextEditingController();
    final kontenController = TextEditingController();
    String visibility = 'public';
    String kategori = 'Umum';
    XFile? topicImage;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.add_comment_rounded, color: AppTheme.primaryColor),
              SizedBox(width: 10),
              Text('Buat Diskusi Komunitas', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Akses Keterbukaan Topik:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: ChoiceChip(
                        label: const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.public, size: 14),
                            SizedBox(width: 4),
                            Text('🌐 Public'),
                          ],
                        ),
                        selected: visibility == 'public',
                        selectedColor: Colors.green.shade100,
                        labelStyle: TextStyle(
                          color: visibility == 'public' ? Colors.green.shade900 : Colors.black87,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                        onSelected: (selected) {
                          if (selected) setDialogState(() => visibility = 'public');
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ChoiceChip(
                        label: const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.lock, size: 14),
                            SizedBox(width: 4),
                            Text('🔒 Kelas Saya'),
                          ],
                        ),
                        selected: visibility == 'private',
                        selectedColor: Colors.amber.shade100,
                        labelStyle: TextStyle(
                          color: visibility == 'private' ? Colors.amber.shade900 : Colors.black87,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                        onSelected: (selected) {
                          if (selected) setDialogState(() => visibility = 'private');
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<String>(
                  initialValue: kategori,
                  decoration: InputDecoration(
                    labelText: 'Kategori Diskusi',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                  items: ['Umum', 'Tanya Jawab KBM', 'Diskusi Tugas', 'Pengumuman Kelas']
                      .map((cat) => DropdownMenuItem(value: cat, child: Text(cat, style: const TextStyle(fontSize: 13))))
                      .toList(),
                  onChanged: (val) {
                    if (val != null) setDialogState(() => kategori = val);
                  },
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: judulController,
                  decoration: InputDecoration(
                    labelText: 'Judul Topik Diskusi',
                    hintText: 'Misal: Diskusi Persiapan Ujian KBM...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: kontenController,
                  maxLines: 4,
                  decoration: InputDecoration(
                    labelText: 'Isi Pertanyaan / Penjelasan Detail',
                    alignLabelWithHint: true,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 14),
                if (topicImage != null) ...[
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.file(
                          File(topicImage!.path),
                          height: 140,
                          width: double.infinity,
                          fit: BoxFit.cover,
                        ),
                      ),
                      Positioned(
                        top: 6,
                        right: 6,
                        child: GestureDetector(
                          onTap: () => setDialogState(() => topicImage = null),
                          child: Container(
                            padding: const EdgeInsets.all(4),
                            decoration: const BoxDecoration(color: Colors.black54, shape: BoxShape.circle),
                            child: const Icon(Icons.close, color: Colors.white, size: 18),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                ],
                OutlinedButton.icon(
                  onPressed: () async {
                    final img = await _pickImage();
                    if (img != null) {
                      setDialogState(() => topicImage = img);
                    }
                  },
                  icon: const Icon(Icons.add_photo_alternate_rounded, size: 20),
                  label: Text(topicImage == null ? 'Lampirkan Gambar / Foto' : 'Ganti Gambar Lampiran'),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    minimumSize: const Size(double.infinity, 44),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
            ElevatedButton(
              onPressed: () async {
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                final judul = judulController.text.trim();
                final konten = kontenController.text.trim();

                if (user == null || judul.isEmpty || konten.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Judul dan isi diskusi wajib diisi!'), backgroundColor: Colors.orange),
                  );
                  return;
                }

                final navigator = Navigator.of(context);
                final scaffoldMessenger = ScaffoldMessenger.of(context);

                String? base64Image;
                if (topicImage != null) {
                  final bytes = await File(topicImage!.path).readAsBytes();
                  final ext = topicImage!.path.split('.').last;
                  base64Image = 'data:image/$ext;base64,${base64Encode(bytes)}';
                }

                final newTopic = ForumModel(
                  id: DateTime.now().millisecondsSinceEpoch,
                  userId: user.id,
                  judul: judul,
                  konten: konten,
                  gambarUrl: topicImage?.path,
                  kategori: kategori,
                  visibility: visibility,
                  targetNamaKelas: visibility == 'private' ? 'Kelas Saya' : 'Semua Kelas',
                  fullName: user.fullName,
                  avatar: 'default_avatar.png',
                  avatarUrl: user.fullAvatarUrl,
                  roleName: user.roleName,
                  totalKomentar: 0,
                  createdAt: 'Baru Saja',
                );

                setState(() {
                  _topics.insert(0, newTopic);
                  _applyFilter();
                });

                navigator.pop();

                final bodyData = <String, dynamic>{
                  'user_id': user.id,
                  'judul': judul,
                  'konten': konten,
                  'kategori': kategori,
                  'visibility': visibility,
                };
                if (base64Image != null) {
                  bodyData['gambar_base64'] = base64Image;
                }

                final res = await ApiService.post('forum/create', bodyData);

                if (mounted) {
                  scaffoldMessenger.showSnackBar(
                    SnackBar(
                      content: Text(res['message'] ?? 'Topik berhasil diterbitkan'),
                      backgroundColor: res['success'] == true ? Colors.green : Colors.blue,
                    ),
                  );
                  _loadForum();
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Terbitkan Topik', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showForumDetailBottomSheet(ForumModel forum) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ForumDetailScreen(
          forum: forum,
          onCommentAdded: _loadForum,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Forum Diskusi Komunitas',
          style: TextStyle(
            color: isDark ? Colors.white : const Color(0xFF0F172A),
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 1,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showNewTopicDialog,
        backgroundColor: AppTheme.primaryColor,
        icon: const Icon(Icons.add_comment_rounded, color: Colors.white),
        label: const Text('Topik Baru', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: _loadForum,
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.grey.shade50,
                border: Border(bottom: BorderSide(color: isDark ? Colors.white.withValues(alpha: 0.1) : Colors.grey.shade300)),
              ),
              child: Row(
                children: [
                  Text('Filter Akses:', style: TextStyle(color: isDark ? Colors.white70 : Colors.grey.shade700, fontSize: 12, fontWeight: FontWeight.bold)),
                  const SizedBox(width: 10),
                  Expanded(
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip('Semua Topik', 'semua'),
                          const SizedBox(width: 8),
                          _buildFilterChip('🌐 Public', 'public'),
                          const SizedBox(width: 8),
                          _buildFilterChip('🔒 Kelas Saya', 'private'),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredTopics.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.forum_outlined, size: 64, color: Colors.indigo.shade200),
                              const SizedBox(height: 12),
                              const Text('Belum ada topik diskusi pada kategori ini.', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                          itemCount: _filteredTopics.length,
                          itemBuilder: (context, index) {
                            final f = _filteredTopics[index];
                            final bool isPrivate = f.visibility.toLowerCase().trim() == 'private';
                            final String roleLower = f.roleName.toLowerCase();
                            Color roleColor = Colors.green;
                            if (roleLower.contains('admin')) roleColor = Colors.purple;
                            if (roleLower.contains('guru')) roleColor = Colors.blue;

                            return Container(
                              margin: const EdgeInsets.only(bottom: 16),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.grey.shade200),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.04),
                                    blurRadius: 14,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: Material(
                                color: Colors.transparent,
                                borderRadius: BorderRadius.circular(20),
                                child: InkWell(
                                  onTap: () => _showForumDetailBottomSheet(f),
                                  borderRadius: BorderRadius.circular(20),
                                  child: Padding(
                                    padding: const EdgeInsets.all(18),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Container(
                                              padding: const EdgeInsets.all(2),
                                              decoration: BoxDecoration(
                                                shape: BoxShape.circle,
                                                gradient: LinearGradient(
                                                  colors: [roleColor.withValues(alpha: 0.8), roleColor],
                                                ),
                                              ),
                                              child: CircleAvatar(
                                                radius: 18,
                                                backgroundColor: Colors.white,
                                                backgroundImage: (f.avatarUrl != null && f.avatarUrl!.isNotEmpty)
                                                    ? NetworkImage(f.avatarUrl!)
                                                    : null,
                                                child: (f.avatarUrl == null || f.avatarUrl!.isEmpty)
                                                    ? Text(
                                                        f.fullName.isNotEmpty ? f.fullName[0].toUpperCase() : 'U',
                                                        style: TextStyle(color: roleColor, fontWeight: FontWeight.bold, fontSize: 14),
                                                      )
                                                    : null,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                children: [
                                                  Text(f.fullName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                                  const SizedBox(height: 2),
                                                  Text("${f.roleName} • ${f.createdAt}", style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                                                ],
                                              ),
                                            ),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                              decoration: BoxDecoration(
                                                color: isPrivate ? Colors.amber.shade50 : Colors.green.shade50,
                                                borderRadius: BorderRadius.circular(20),
                                                border: Border.all(color: isPrivate ? Colors.amber.shade200 : Colors.green.shade200),
                                              ),
                                              child: Text(
                                                isPrivate ? '🔒 ${f.targetNamaKelas}' : '🌐 Public',
                                                style: TextStyle(
                                                  color: isPrivate ? Colors.amber.shade900 : Colors.green.shade900,
                                                  fontSize: 11,
                                                  fontWeight: FontWeight.bold,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 14),
                                        Text(
                                          ProfanityService.filter(f.judul),
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, height: 1.3),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          ProfanityService.filter(f.konten),
                                          maxLines: 3,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(fontSize: 13, color: Colors.grey.shade700, height: 1.4),
                                        ),
                                        if (f.gambarUrl != null && f.gambarUrl!.isNotEmpty) ...[
                                          const SizedBox(height: 12),
                                          GestureDetector(
                                            onTap: () => _showImageViewer(context, f.gambarUrl!),
                                            child: Stack(
                                              alignment: Alignment.bottomRight,
                                              children: [
                                                ClipRRect(
                                                  borderRadius: BorderRadius.circular(16),
                                                  child: Container(
                                                    constraints: const BoxConstraints(maxHeight: 220),
                                                    width: double.infinity,
                                                    decoration: BoxDecoration(
                                                      color: Colors.grey.shade100,
                                                      borderRadius: BorderRadius.circular(16),
                                                      border: Border.all(color: Colors.grey.shade200),
                                                    ),
                                                    child: Image.network(
                                                      f.gambarUrl!,
                                                      fit: BoxFit.contain,
                                                      errorBuilder: (_, __, ___) => const Padding(
                                                        padding: EdgeInsets.all(20),
                                                        child: Row(
                                                          mainAxisAlignment: MainAxisAlignment.center,
                                                          children: [
                                                            Icon(Icons.image_not_supported_outlined, color: Colors.grey),
                                                            SizedBox(width: 8),
                                                            Text('Gagal memuat gambar', style: TextStyle(color: Colors.grey, fontSize: 12)),
                                                          ],
                                                        ),
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                                Container(
                                                  margin: const EdgeInsets.all(10),
                                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                                  decoration: BoxDecoration(
                                                    color: Colors.black.withValues(alpha: 0.75),
                                                    borderRadius: BorderRadius.circular(20),
                                                  ),
                                                  child: const Row(
                                                    mainAxisSize: MainAxisSize.min,
                                                    children: [
                                                      Icon(Icons.zoom_in, color: Colors.white, size: 14),
                                                      SizedBox(width: 4),
                                                      Text('Tap perbesar', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                                                    ],
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                        const SizedBox(height: 14),
                                        Container(
                                          padding: const EdgeInsets.only(top: 12),
                                          decoration: BoxDecoration(
                                            border: Border(top: BorderSide(color: Colors.grey.shade100)),
                                          ),
                                          child: Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                                decoration: BoxDecoration(
                                                  color: Colors.indigo.shade50,
                                                  borderRadius: BorderRadius.circular(8),
                                                ),
                                                child: Text(
                                                  f.kategori,
                                                  style: TextStyle(color: Colors.indigo.shade900, fontSize: 11, fontWeight: FontWeight.bold),
                                                ),
                                              ),
                                              Row(
                                                children: [
                                                  Row(
                                                    children: [
                                                      Icon(Icons.chat_bubble_outline_rounded, size: 15, color: Colors.indigo.shade600),
                                                      const SizedBox(width: 4),
                                                      Text(
                                                        '${f.totalKomentar} Balasan',
                                                        style: TextStyle(fontSize: 12, color: Colors.indigo.shade900, fontWeight: FontWeight.bold),
                                                      ),
                                                    ],
                                                  ),
                                                  const SizedBox(width: 10),
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                                    decoration: BoxDecoration(
                                                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                                                      borderRadius: BorderRadius.circular(8),
                                                      border: Border.all(color: AppTheme.primaryColor.withValues(alpha: 0.2)),
                                                    ),
                                                    child: const Row(
                                                      mainAxisSize: MainAxisSize.min,
                                                      children: [
                                                        Icon(Icons.reply_rounded, size: 14, color: AppTheme.primaryColor),
                                                        SizedBox(width: 4),
                                                        Text(
                                                          'Tulis Tanggapan',
                                                          style: TextStyle(
                                                            color: AppTheme.primaryColor,
                                                            fontSize: 11.5,
                                                            fontWeight: FontWeight.bold,
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final bool isSelected = _selectedFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: Colors.amber.shade700,
      backgroundColor: Colors.white24,
      elevation: isSelected ? 2 : 0,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : Colors.white70,
        fontWeight: FontWeight.bold,
        fontSize: 11,
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedFilter = value;
            _applyFilter();
          });
        }
      },
    );
  }
}
