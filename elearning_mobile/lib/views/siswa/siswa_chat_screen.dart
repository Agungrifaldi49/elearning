import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/chat_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class SiswaChatScreen extends StatefulWidget {
  const SiswaChatScreen({super.key});

  @override
  State<SiswaChatScreen> createState() => _SiswaChatScreenState();
}

class _SiswaChatScreenState extends State<SiswaChatScreen> {
  List<ChatContactModel> _contacts = [];
  List<ChatContactModel> _filteredContacts = [];
  bool _isLoading = false;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadContacts();
  }

  Future<void> _loadContacts() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final res = await ApiService.get('chat/contacts', params: {'user_id': user.id.toString()});
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _contacts = (res['data'] as List).map((e) => ChatContactModel.fromJson(e)).toList();
          _filteredContacts = _contacts;
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  void _filterContacts(String query) {
    if (query.isEmpty) {
      setState(() => _filteredContacts = _contacts);
    } else {
      final q = query.toLowerCase();
      setState(() {
        _filteredContacts = _contacts.where((c) {
          final name = c.fullName.toLowerCase();
          final role = c.roleName.toLowerCase();
          return name.contains(q) || role.contains(q);
        }).toList();
      });
    }
  }

  void _openChatRoom(ChatContactModel contact) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ChatRoomScreen(contact: contact)),
    ).then((_) => _loadContacts());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pesan & Chat Direct'),
        backgroundColor: Colors.indigo.shade900,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              onChanged: _filterContacts,
              decoration: InputDecoration(
                hintText: 'Cari nama guru atau teman siswa...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filteredContacts.isEmpty
                    ? const Center(child: Text('Kontak pesan tidak ditemukan.'))
                    : RefreshIndicator(
                        onRefresh: _loadContacts,
                        child: ListView.builder(
                          itemCount: _filteredContacts.length,
                          itemBuilder: (context, index) {
                            final c = _filteredContacts[index];
                            final bool isGuru = c.roleName.toLowerCase().contains('guru');

                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 1,
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: isGuru ? Colors.amber.withValues(alpha: 0.2) : AppTheme.primaryColor.withValues(alpha: 0.15),
                                  child: Text(
                                    c.fullName.isNotEmpty ? c.fullName[0] : 'U',
                                    style: TextStyle(
                                      color: isGuru ? Colors.amber.shade900 : AppTheme.primaryColor,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                title: Row(
                                  children: [
                                    Expanded(
                                      child: Text(c.fullName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: isGuru ? Colors.amber.shade100 : Colors.blue.shade100,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        c.roleName,
                                        style: TextStyle(
                                          color: isGuru ? Colors.amber.shade900 : Colors.blue.shade900,
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                subtitle: const Text('Ketuk untuk membuka percakapan direct', style: TextStyle(fontSize: 12, color: Colors.grey)),
                                trailing: const Icon(Icons.chevron_right_rounded, color: Colors.grey),
                                onTap: () => _openChatRoom(c),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}

class ChatRoomScreen extends StatefulWidget {
  final ChatContactModel contact;

  const ChatRoomScreen({super.key, required this.contact});

  @override
  State<ChatRoomScreen> createState() => _ChatRoomScreenState();
}

class _ChatRoomScreenState extends State<ChatRoomScreen> {
  final _messageController = TextEditingController();
  List<ChatMessageModel> _messages = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadMessages();
  }

  Future<void> _loadMessages() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final res = await ApiService.get('chat/messages', params: {
      'user_id': user.id.toString(),
      'receiver_id': widget.contact.id.toString(),
    });

    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _messages = (res['data'] as List).map((e) => ChatMessageModel.fromJson(e)).toList();
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  void _sendMessage() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final text = _messageController.text.trim();
    if (user == null || text.isEmpty) return;

    _messageController.clear();
    final res = await ApiService.post('chat/messages', {
      'user_id': user.id,
      'receiver_id': widget.contact.id,
      'pesan': text,
    });

    if (res['success'] == true) {
      _loadMessages();
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            CircleAvatar(
              radius: 16,
              backgroundColor: Colors.white24,
              child: Text(widget.contact.fullName.isNotEmpty ? widget.contact.fullName[0] : 'U', style: const TextStyle(color: Colors.white, fontSize: 13)),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.contact.fullName, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  Text(widget.contact.roleName, style: const TextStyle(fontSize: 11, color: Colors.white70)),
                ],
              ),
            ),
          ],
        ),
        backgroundColor: Colors.indigo.shade900,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _messages.isEmpty
                    ? Center(
                        child: Text(
                          "Belum ada pesan dengan ${widget.contact.fullName}.\nMulai percakapan sekarang!",
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Colors.grey),
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final m = _messages[index];
                          final isMe = m.senderId == user?.id;

                          return Align(
                            alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
                              decoration: BoxDecoration(
                                color: isMe ? Colors.indigo.shade800 : Colors.grey.shade200,
                                borderRadius: BorderRadius.only(
                                  topLeft: const Radius.circular(14),
                                  topRight: const Radius.circular(14),
                                  bottomLeft: isMe ? const Radius.circular(14) : Radius.zero,
                                  bottomRight: isMe ? Radius.zero : const Radius.circular(14),
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    m.pesan,
                                    style: TextStyle(color: isMe ? Colors.white : Colors.black87, fontSize: 14),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    m.createdAt,
                                    style: TextStyle(color: isMe ? Colors.white70 : Colors.grey.shade600, fontSize: 9),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4)],
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Ketik pesan...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(24)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  icon: const Icon(Icons.send_rounded, color: AppTheme.primaryColor),
                  onPressed: _sendMessage,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
