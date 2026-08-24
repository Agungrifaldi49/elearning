import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/forum_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class SiswaForumScreen extends StatefulWidget {
  const SiswaForumScreen({super.key});

  @override
  State<SiswaForumScreen> createState() => _SiswaForumScreenState();
}

class _SiswaForumScreenState extends State<SiswaForumScreen> {
  List<ForumModel> _topics = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadForum();
  }

  Future<void> _loadForum() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('forum/list');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _topics = (res['data'] as List).map((e) => ForumModel.fromJson(e)).toList();
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showNewTopicDialog() {
    final judulController = TextEditingController();
    final kontenController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: const [
            Icon(Icons.add_comment, color: AppTheme.primaryColor),
            SizedBox(width: 8),
            Text('Buat Diskusi Komunitas', style: TextStyle(fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: judulController,
              decoration: const InputDecoration(
                labelText: 'Judul Topik Diskusi',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: kontenController,
              maxLines: 4,
              decoration: const InputDecoration(
                labelText: 'Isi Pertanyaan / Penjelasan',
                border: OutlineInputBorder(),
              ),
            ),
          ],
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
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
            child: const Text('Terbitkan'),
          ),
        ],
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
                top: 16,
                left: 16,
                right: 16,
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
                            forum.judul,
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                        ),
                        IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text("Oleh: ${forum.fullName} (${forum.roleName}) • ${forum.createdAt}", style: const TextStyle(color: Colors.grey, fontSize: 12)),
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: SelectableText(forum.konten, style: const TextStyle(fontSize: 14, height: 1.4)),
                    ),
                    const SizedBox(height: 16),
                    const Text('Komentar & Diskusi:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    const SizedBox(height: 8),
                    Expanded(
                      child: isLoadingComments
                          ? const Center(child: CircularProgressIndicator())
                          : comments.isEmpty
                              ? const Center(child: Text('Belum ada komentar. Jadilah yang pertama berkomentar!'))
                              : ListView.builder(
                                  itemCount: comments.length,
                                  itemBuilder: (context, index) {
                                    final c = comments[index];
                                    return ListTile(
                                      contentPadding: EdgeInsets.zero,
                                      leading: CircleAvatar(
                                        backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.15),
                                        child: Text(c['full_name'] != null && c['full_name'].toString().isNotEmpty ? c['full_name'][0] : 'U'),
                                      ),
                                      title: Text(c['full_name'] ?? 'User', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                      subtitle: Text(c['isi_komentar'] ?? '', style: const TextStyle(fontSize: 13, color: Colors.black87)),
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
                                hintText: 'Tulis komentar...',
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
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : _topics.isEmpty
                  ? const Center(child: Text('Belum ada topik diskusi.'))
                  : ListView.builder(
                      itemCount: _topics.length,
                      itemBuilder: (context, index) {
                        final f = _topics[index];
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
                                        child: Text(
                                          f.fullName.isNotEmpty ? f.fullName[0] : 'U',
                                          style: const TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.bold),
                                        ),
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
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.indigo.withValues(alpha: 0.1),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          f.kategori,
                                          style: const TextStyle(color: Colors.indigo, fontSize: 11, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Text(f.judul, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                  const SizedBox(height: 6),
                                  Text(
                                    f.konten,
                                    maxLines: 3,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(fontSize: 14, color: Colors.grey.shade800),
                                  ),
                                  const SizedBox(height: 14),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Row(
                                        children: [
                                          const Icon(Icons.chat_bubble_outline_rounded, size: 16, color: Colors.grey),
                                          const SizedBox(width: 6),
                                          Text("${f.totalKomentar} Komentar", style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.bold)),
                                        ],
                                      ),
                                      const Text("Buka Diskusi →", style: TextStyle(color: Colors.indigo, fontSize: 12, fontWeight: FontWeight.bold)),
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
      ),
    );
  }
}
