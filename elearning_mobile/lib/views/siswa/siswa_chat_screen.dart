import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/chat_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../services/profanity_service.dart';
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
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    _loadContacts();
    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (_) => _loadContacts(showLoading: false));
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadContacts({bool showLoading = true}) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    if (showLoading && _contacts.isEmpty) {
      setState(() => _isLoading = true);
    }

    final res = await ApiService.get('chat/contacts', params: {'user_id': user.id.toString()});
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        final list = (res['data'] as List).map((e) => ChatContactModel.fromJson(e)).toList();
        setState(() {
          _contacts = list;
          _filterContacts(_searchController.text);
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  void _filterContacts(String query) {
    List<ChatContactModel> list;
    if (query.isEmpty) {
      list = List.from(_contacts);
    } else {
      final q = query.toLowerCase();
      list = _contacts.where((c) {
        return c.fullName.toLowerCase().contains(q) || c.roleName.toLowerCase().contains(q);
      }).toList();
    }

    // Contacts with unread messages (unreadCount > 0) MUST be sorted to the VERY TOP of the contact list!
    list.sort((a, b) {
      if (a.unreadCount > 0 && b.unreadCount == 0) return -1;
      if (a.unreadCount == 0 && b.unreadCount > 0) return 1;
      if (a.unreadCount > 0 && b.unreadCount > 0) {
        return b.unreadCount.compareTo(a.unreadCount);
      }
      return 0;
    });

    setState(() {
      _filteredContacts = list;
    });
  }

  void _openChatRoom(ChatContactModel contact) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ChatRoomScreen(contact: contact),
      ),
    );
    _loadContacts(showLoading: false);
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
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.indigo.shade900,
            child: TextField(
              controller: _searchController,
              onChanged: _filterContacts,
              style: const TextStyle(color: Colors.white),
              decoration: InputDecoration(
                hintText: 'Cari Guru, Siswa, Admin, Kepsek...',
                hintStyle: const TextStyle(color: Colors.white60),
                prefixIcon: const Icon(Icons.search, color: Colors.white60),
                filled: true,
                fillColor: Colors.white.withValues(alpha: 0.15),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              ),
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () => _loadContacts(),
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredContacts.isEmpty
                      ? const Center(child: Text('Tidak ada kontak tersedia.'))
                      : ListView.builder(
                          itemCount: _filteredContacts.length,
                          itemBuilder: (context, index) {
                            final c = _filteredContacts[index];
                            final bool isGuru = c.roleName.toLowerCase().contains('guru');

                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 1,
                              child: ListTile(
                                leading: Stack(
                                  clipBehavior: Clip.none,
                                  children: [
                                    CircleAvatar(
                                      backgroundColor: isGuru ? Colors.amber.withValues(alpha: 0.2) : AppTheme.primaryColor.withValues(alpha: 0.15),
                                      backgroundImage: (c.avatarUrl != null && c.avatarUrl!.isNotEmpty)
                                          ? NetworkImage(c.avatarUrl!)
                                          : null,
                                      child: (c.avatarUrl == null || c.avatarUrl!.isEmpty)
                                          ? Text(
                                              c.fullName.isNotEmpty ? c.fullName[0] : 'U',
                                              style: TextStyle(
                                                color: isGuru ? Colors.amber.shade900 : AppTheme.primaryColor,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            )
                                          : null,
                                    ),
                                    if (c.unreadCount > 0)
                                      Positioned(
                                        right: -2,
                                        top: -2,
                                        child: Container(
                                          padding: const EdgeInsets.all(4),
                                          decoration: const BoxDecoration(
                                            color: Colors.red,
                                            shape: BoxShape.circle,
                                          ),
                                          constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                                          child: Text(
                                            '${c.unreadCount}',
                                            textAlign: TextAlign.center,
                                            style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                      ),
                                  ],
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
                                subtitle: Text(
                                  c.lastMessage != null && c.lastMessage!.isNotEmpty
                                      ? ProfanityService.filter(c.lastMessage)
                                      : 'Ketuk untuk percakapan direct',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: c.unreadCount > 0 ? FontWeight.bold : FontWeight.normal,
                                    color: c.unreadCount > 0 ? Colors.black87 : Colors.grey.shade600,
                                  ),
                                ),
                                trailing: c.unreadCount > 0
                                    ? Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.red.shade600,
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: Text(
                                          '${c.unreadCount} BARU',
                                          style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                                        ),
                                      )
                                    : const Icon(Icons.chevron_right_rounded, color: Colors.grey),
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
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _loadMessages();
    _pollingTimer = Timer.periodic(const Duration(seconds: 3), (_) => _loadMessages(showLoading: false));
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _loadMessages({bool showLoading = true}) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    if (showLoading && _messages.isEmpty) {
      setState(() => _isLoading = true);
    }

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
      _loadMessages(showLoading: false);
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
              radius: 18,
              backgroundColor: Colors.white24,
              backgroundImage: (widget.contact.avatarUrl != null && widget.contact.avatarUrl!.isNotEmpty)
                  ? NetworkImage(widget.contact.avatarUrl!)
                  : null,
              child: (widget.contact.avatarUrl == null || widget.contact.avatarUrl!.isEmpty)
                  ? Text(
                      widget.contact.fullName.isNotEmpty ? widget.contact.fullName[0] : 'U',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                    )
                  : null,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    widget.contact.fullName,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    widget.contact.roleName,
                    style: const TextStyle(fontSize: 11, color: Colors.white70),
                  ),
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
                    ? const Center(child: Text('Belum ada pesan. Mulai percakapan sekarang!'))
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final m = _messages[index];
                          final isMe = user != null && m.senderId == user.id;

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
                                    ProfanityService.filter(m.pesan),
                                    style: TextStyle(color: isMe ? Colors.white : Colors.black87, fontSize: 14),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    m.createdAt,
                                    style: TextStyle(
                                      color: isMe ? Colors.white70 : Colors.grey.shade600,
                                      fontSize: 10,
                                    ),
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
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.grey.withValues(alpha: 0.2),
                  blurRadius: 6,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Tulis pesan direct...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(24)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton.filled(
                  onPressed: _sendMessage,
                  icon: const Icon(Icons.send_rounded),
                  style: IconButton.styleFrom(backgroundColor: Colors.indigo.shade800),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
