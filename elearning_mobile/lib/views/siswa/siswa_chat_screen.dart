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
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadContacts();
  }

  void _loadContacts() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() {
      _isLoading = true;
    });

    final res = await ApiService.get('chat/contacts', params: {'user_id': user.id.toString()});
    if (res['success'] == true && res['data'] is List) {
      _contacts = (res['data'] as List).map((e) => ChatContactModel.fromJson(e)).toList();
    }

    setState(() {
      _isLoading = false;
    });
  }

  void _openChatRoom(ChatContactModel contact) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ChatRoomScreen(contact: contact)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pesan & Chat Direct'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _contacts.isEmpty
              ? const Center(child: Text('Belum ada kontak pesan.'))
              : ListView.builder(
                  itemCount: _contacts.length,
                  itemBuilder: (context, index) {
                    final c = _contacts[index];
                    return ListTile(
                      leading: CircleAvatar(
                        backgroundColor: AppTheme.primaryColor.withOpacity(0.1),
                        child: Text(c.fullName.isNotEmpty ? c.fullName[0] : 'U'),
                      ),
                      title: Text(c.fullName, style: const TextStyle(fontWeight: FontWeight.bold)),
                      subtitle: Text(c.roleName),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => _openChatRoom(c),
                    );
                  },
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

  @override
  void initState() {
    super.initState();
    _loadMessages();
  }

  void _loadMessages() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final res = await ApiService.get('chat/messages', params: {
      'user_id': user.id.toString(),
      'receiver_id': widget.contact.id.toString(),
    });

    if (res['success'] == true && res['data'] is List) {
      setState(() {
        _messages = (res['data'] as List).map((e) => ChatMessageModel.fromJson(e)).toList();
      });
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
        title: Text(widget.contact.fullName),
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final m = _messages[index];
                final isMe = m.senderId == user?.id;

                return Align(
                  alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: isMe ? AppTheme.primaryColor : Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      m.pesan,
                      style: TextStyle(color: isMe ? Colors.white : Colors.black87),
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
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 4)],
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: const InputDecoration(
                      hintText: 'Ketik pesan...',
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
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
