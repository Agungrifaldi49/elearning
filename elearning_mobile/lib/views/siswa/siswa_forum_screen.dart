import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../models/forum_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../services/profanity_service.dart';
import '../../theme/app_theme.dart';

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
          title: Row(
            children: const [
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

                final navigator = Navigator.of(context);
                final scaffoldMessenger = ScaffoldMessenger.of(context);
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

  void _showForumDetailBottomSheet(ForumModel forum) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      if (user.roleName.toLowerCase().contains('guru')) {
        Provider.of<GuruProvider>(context, listen: false).markForumAsSeen(forum.id);
      } else {
        Provider.of<SiswaProvider>(context, listen: false).markForumAsSeen(forum.id);
      }
    }

    final commentController = TextEditingController();
    bool isLoadingComments = true;
    List<dynamic> comments = [];
    XFile? commentImage;

    Future<void> fetchComments() async {
      final res = await ApiService.get('forum/detail', params: {'forum_id': forum.id.toString()});
      if (res['success'] == true && res['data'] is Map && res['data']['comments'] is List) {
        comments = res['data']['comments'];
      }
      isLoadingComments = false;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            if (isLoadingComments) {
              fetchComments().then((_) {
                if (context.mounted) {
                  setModalState(() {});
                }
              });
            }

            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 16,
                right: 16,
                top: 16,
              ),
              child: SizedBox(
                height: MediaQuery.of(context).size.height * 0.75,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            ProfanityService.filter(forum.judul),
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const Divider(),
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.15),
                          backgroundImage: (forum.avatarUrl != null && forum.avatarUrl!.isNotEmpty)
                              ? NetworkImage(forum.avatarUrl!)
                              : null,
                          child: (forum.avatarUrl == null || forum.avatarUrl!.isEmpty)
                              ? Text(forum.fullName.isNotEmpty ? forum.fullName[0] : 'U')
                              : null,
                        ),
                        const SizedBox(width: 10),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(forum.fullName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                            Text("${forum.roleName} • ${forum.createdAt}", style: const TextStyle(fontSize: 11, color: Colors.grey)),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.indigo.shade50.withValues(alpha: 0.5),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.indigo.shade100),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          SelectableText(
                            ProfanityService.filter(forum.konten),
                            style: const TextStyle(fontSize: 14, height: 1.4, color: Colors.black87),
                          ),
                          if (forum.gambarUrl != null && forum.gambarUrl!.isNotEmpty) ...[
                            const SizedBox(height: 10),
                            GestureDetector(
                              onTap: () => _showImageViewer(context, forum.gambarUrl!),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.network(
                                  forum.gambarUrl!,
                                  height: 200,
                                  width: double.infinity,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const SizedBox(),
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text('Komentar & Thread Tanggapan:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    const SizedBox(height: 8),
                    Expanded(
                      child: isLoadingComments
                          ? const Center(child: CircularProgressIndicator())
                          : comments.isEmpty
                              ? const Center(child: Text('Belum ada tanggapan. Berikan jawaban Anda!'))
                              : ListView.builder(
                                  itemCount: comments.length,
                                  itemBuilder: (context, index) {
                                    final c = comments[index];
                                    final avatarUrl = c['avatar_url'];
                                    final fullName = c['full_name'] ?? 'User';
                                    final cmtImgUrl = c['gambar_url'];

                                    return ListTile(
                                      contentPadding: EdgeInsets.zero,
                                      leading: CircleAvatar(
                                        backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.15),
                                        backgroundImage: (avatarUrl != null && avatarUrl.toString().isNotEmpty)
                                            ? NetworkImage(avatarUrl)
                                            : null,
                                        child: (avatarUrl == null || avatarUrl.toString().isEmpty)
                                            ? Text(fullName.isNotEmpty ? fullName[0] : 'U')
                                            : null,
                                      ),
                                      title: Text(fullName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                      subtitle: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            ProfanityService.filter(c['isi_komentar'] ?? ''),
                                            style: const TextStyle(fontSize: 13, color: Colors.black87),
                                          ),
                                          if (cmtImgUrl != null && cmtImgUrl.toString().isNotEmpty) ...[
                                            const SizedBox(height: 6),
                                            GestureDetector(
                                              onTap: () => _showImageViewer(context, cmtImgUrl.toString()),
                                              child: ClipRRect(
                                                borderRadius: BorderRadius.circular(8),
                                                child: Image.network(
                                                  cmtImgUrl.toString(),
                                                  height: 120,
                                                  width: 180,
                                                  fit: BoxFit.cover,
                                                  errorBuilder: (_, __, ___) => const SizedBox(),
                                                ),
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                      trailing: Text(c['created_at'] ?? '', style: const TextStyle(fontSize: 10, color: Colors.grey)),
                                    );
                                  },
                                ),
                    ),
                    if (commentImage != null) ...[
                      Padding(
                        padding: const EdgeInsets.only(bottom: 6),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.file(
                                File(commentImage!.path),
                                height: 50,
                                width: 50,
                                fit: BoxFit.cover,
                              ),
                            ),
                            const SizedBox(width: 8),
                            const Text('Gambar terlampir', style: TextStyle(fontSize: 12, color: Colors.indigo, fontWeight: FontWeight.bold)),
                            const Spacer(),
                            IconButton(
                              icon: const Icon(Icons.cancel, color: Colors.grey, size: 20),
                              onPressed: () => setModalState(() => commentImage = null),
                            ),
                          ],
                        ),
                      ),
                    ],
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.add_a_photo_rounded, color: Colors.indigo),
                            onPressed: () async {
                              final img = await _pickImage();
                              if (img != null) {
                                setModalState(() => commentImage = img);
                              }
                            },
                          ),
                          Expanded(
                            child: TextField(
                              controller: commentController,
                              decoration: InputDecoration(
                                hintText: 'Tulis tanggapan / balasan...',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          ElevatedButton(
                            onPressed: () async {
                              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                              final text = commentController.text.trim();
                              if (user == null || text.isEmpty) return;

                              String? base64CmtImage;
                              if (commentImage != null) {
                                final bytes = await File(commentImage!.path).readAsBytes();
                                final ext = commentImage!.path.split('.').last;
                                base64CmtImage = 'data:image/$ext;base64,${base64Encode(bytes)}';
                              }

                              commentController.clear();
                              setModalState(() => commentImage = null);

                              final bodyData = <String, dynamic>{
                                'user_id': user.id,
                                'forum_id': forum.id,
                                'komentar': text,
                              };
                              if (base64CmtImage != null) {
                                bodyData['gambar_base64'] = base64CmtImage;
                              }

                              final res = await ApiService.post('forum/comment', bodyData);

                              if (res['success'] == true) {
                                isLoadingComments = true;
                                await fetchComments();
                                setModalState(() {});
                                _loadForum();
                              }
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTheme.primaryColor,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            child: const Icon(Icons.send_rounded),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Forum Diskusi Komunitas'),
        backgroundColor: Colors.indigo.shade900,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showNewTopicDialog,
        backgroundColor: Colors.amber.shade800,
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
                color: Colors.indigo.shade900,
                boxShadow: [
                  BoxShadow(
                    color: Colors.indigo.shade900.withValues(alpha: 0.3),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  const Text('Filter Akses:', style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.bold)),
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
                                                    color: Colors.black.withOpacity(0.75),
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
                                                  Icon(Icons.chat_bubble_outline_rounded, size: 15, color: Colors.indigo.shade600),
                                                  const SizedBox(width: 4),
                                                  Text(
                                                    '${f.totalKomentar} Balasan',
                                                    style: TextStyle(fontSize: 12, color: Colors.indigo.shade900, fontWeight: FontWeight.bold),
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
