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

  void _loadForum() async {
    setState(() {
      _isLoading = true;
    });
    final res = await ApiService.get('forum/list');
    if (res['success'] == true && res['data'] is List) {
      _topics = (res['data'] as List).map((e) => ForumModel.fromJson(e)).toList();
    }
    setState(() {
      _isLoading = false;
    });
  }

  void _showNewTopicDialog() {
    final judulController = TextEditingController();
    final kontenController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Buat Diskusi Baru'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: judulController,
              decoration: const InputDecoration(labelText: 'Judul Topik'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: kontenController,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Isi Pertanyaan / Diskusi'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
              if (user != null) {
                final res = await ApiService.post('forum/create', {
                  'user_id': user.id,
                  'judul': judulController.text,
                  'konten': kontenController.text,
                });
                if (!mounted) return;
                Navigator.pop(context);
                if (res['success'] == true) {
                  _loadForum();
                }
              }
            },
            child: const Text('Terbitkan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Forum Diskusi Komunitas'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showNewTopicDialog,
        backgroundColor: AppTheme.secondaryColor,
        icon: const Icon(Icons.add_comment_rounded, color: Colors.white),
        label: const Text('Diskusi Baru', style: TextStyle(color: Colors.white)),
      ),
      body: Padding(
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
                        margin: const EdgeInsets.only(bottom: 12),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  CircleAvatar(
                                    child: Text(f.fullName.isNotEmpty ? f.fullName[0] : 'U'),
                                  ),
                                  const SizedBox(width: 10),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(f.fullName, style: const TextStyle(fontWeight: FontWeight.bold)),
                                      Text("${f.roleName} • ${f.createdAt}", style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                    ],
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              Text(f.judul, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                              const SizedBox(height: 4),
                              Text(f.konten, style: const TextStyle(fontSize: 14)),
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  const Icon(Icons.chat_bubble_outline, size: 16, color: Colors.grey),
                                  const SizedBox(width: 4),
                                  Text("${f.totalKomentar} Komentar", style: const TextStyle(fontSize: 12, color: Colors.grey)),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
