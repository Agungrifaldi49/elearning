import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/forum_model.dart';
import '../../providers/auth_provider.dart';
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
      if (res['success'] == true && res['data'] is List) {
        final list = (res['data'] as List).map((e) => ForumModel.fromJson(e)).toList();
        setState(() {
          _topics = list;
          _applyFilter();
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
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

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: const [
              Icon(Icons.add_comment_rounded, color: AppTheme.primaryColor),
              SizedBox(width: 8),
              Text('Buat Diskusi Komunitas', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Akses Topik (Visibility):', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                const SizedBox(height: 6),
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
                  value: kategori,
                  decoration: InputDecoration(
                    labelText: 'Kategori Diskusi',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
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
                    hintText: 'Misal: Pertanyaan Soal CBT No. 5...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: kontenController,
                  maxLines: 4,
                  decoration: InputDecoration(
                    labelText: 'Isi Pertanyaan / Deskripsi Topik',
                    alignLabelWithHint: true,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
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

                final res = await ApiService.post('forum/create', {
                  'user_id': user.id,
                  'judul': judul,
                  'konten': konten,
                  'kategori': kategori,
                  'visibility': visibility,
                });

                if (!mounted) return;
                Navigator.pop(context);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message'] ?? 'Status diterbitkan'),
                    backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                  ),
                );

                if (res['success'] == true) {
                  _loadForum();
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('Terbitkan Topik', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showForumDetailBottomSheet(ForumModel forum) async {
    final commentController = TextEditingController();
    bool isLoadingComments = true;
    List<dynamic> comments = [];

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
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
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
              child: Container(
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
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: SelectableText(
                        ProfanityService.filter(forum.konten),
                        style: const TextStyle(fontSize: 14, height: 1.4),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text('Komentar & Thread Diskusi:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    const SizedBox(height: 8),
                    Expanded(
                      child: isLoadingComments
                          ? const Center(child: CircularProgressIndicator())
                          : comments.isEmpty
                              ? const Center(child: Text('Belum ada komentar. Berikan tanggapan Anda!'))
                              : ListView.builder(
                                  itemCount: comments.length,
                                  itemBuilder: (context, index) {
                                    final c = comments[index];
                                    final avatarUrl = c['avatar_url'];
                                    final fullName = c['full_name'] ?? 'User';

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
                                      subtitle: Text(
                                        ProfanityService.filter(c['isi_komentar'] ?? ''),
                                        style: const TextStyle(fontSize: 13, color: Colors.black87),
                                      ),
                                      trailing: Text(c['created_at'] ?? '', style: const TextStyle(fontSize: 10, color: Colors.grey)),
                                    );
                                  },
                                ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: commentController,
                              decoration: InputDecoration(
                                hintText: 'Tulis tanggapan / komentar...',
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

                              commentController.clear();
                              final res = await ApiService.post('forum/comment', {
                                'user_id': user.id,
                                'forum_id': forum.id,
                                'komentar': text,
                              });

                              if (res['success'] == true) {
                                isLoadingComments = true;
                                await fetchComments();
                                setModalState(() {});
                                _loadForum();
                              }
                            },
                            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
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
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              color: Colors.indigo.shade900,
              child: Row(
                children: [
                  const Text('Filter Access:', style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.bold)),
                  const SizedBox(width: 10),
                  Expanded(
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip('Semua Topik', 'semua'),
                          const SizedBox(width: 6),
                          _buildFilterChip('🌐 Public', 'public'),
                          const SizedBox(width: 6),
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
                      ? const Center(child: Text('Belum ada topik diskusi pada kategori ini.'))
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredTopics.length,
                          itemBuilder: (context, index) {
                            final f = _filteredTopics[index];
                            final bool isPrivate = f.visibility.toLowerCase() == 'private';

                            return Card(
                              margin: const EdgeInsets.only(bottom: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              elevation: 3,
                              child: InkWell(
                                onTap: () => _showForumDetailBottomSheet(f),
                                borderRadius: BorderRadius.circular(14),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          CircleAvatar(
                                            backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.15),
                                            backgroundImage: (f.avatarUrl != null && f.avatarUrl!.isNotEmpty)
                                                ? NetworkImage(f.avatarUrl!)
                                                : null,
                                            child: (f.avatarUrl == null || f.avatarUrl!.isEmpty)
                                                ? Text(
                                                    f.fullName.isNotEmpty ? f.fullName[0] : 'U',
                                                    style: const TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.bold),
                                                  )
                                                : null,
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(f.fullName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                                Text("${f.roleName} • ${f.createdAt}", style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                              ],
                                            ),
                                          ),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: isPrivate ? Colors.amber.shade100 : Colors.green.shade100,
                                              borderRadius: BorderRadius.circular(8),
                                            ),
                                            child: Text(
                                              isPrivate ? '🔒 ${f.targetNamaKelas}' : '🌐 Public',
                                              style: TextStyle(
                                                color: isPrivate ? Colors.amber.shade900 : Colors.green.shade900,
                                                fontSize: 10,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 12),
                                      Text(ProfanityService.filter(f.judul), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                                      const SizedBox(height: 6),
                                      Text(
                                        ProfanityService.filter(f.konten),
                                        maxLines: 3,
                                        overflow: TextOverflow.ellipsis,
                                        style: TextStyle(fontSize: 13, color: Colors.grey.shade800),
                                      ),
                                      const Divider(height: 20),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: Colors.indigo.shade50,
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(
                                              f.kategori,
                                              style: TextStyle(color: Colors.indigo.shade900, fontSize: 11, fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                          Row(
                                            children: [
                                              const Icon(Icons.mode_comment_outlined, size: 14, color: Colors.grey),
                                              const SizedBox(width: 4),
                                              Text(
                                                '${f.totalKomentar} Balasan',
                                                style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.bold),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ],
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
