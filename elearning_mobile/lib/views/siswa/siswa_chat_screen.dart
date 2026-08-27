import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/chat_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../providers/guru_provider.dart';
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
  bool _isLoading = false;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadContacts();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadContacts({bool showLoading = true}) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    if (showLoading && _contacts.isEmpty) {
      setState(() => _isLoading = true);
    }

    List<ChatContactModel> list;
    if (user.roleName.toLowerCase().contains('guru')) {
      final guruProvider = Provider.of<GuruProvider>(context, listen: false);
      await guruProvider.fetchChatContactsSilent(user.id);
      list = guruProvider.chatContacts;
    } else {
      final siswaProvider = Provider.of<SiswaProvider>(context, listen: false);
      await siswaProvider.fetchChatContactsSilent(user.id);
      list = siswaProvider.chatContacts;
    }

    if (mounted) {
      setState(() {
        _contacts = list;
        _isLoading = false;
      });
    }
  }

  void _openChatRoom(ChatContactModel contact) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      if (user.roleName.toLowerCase().contains('guru')) {
        Provider.of<GuruProvider>(context, listen: false).markContactChatAsRead(user.id, contact.id);
      } else {
        Provider.of<SiswaProvider>(context, listen: false).markContactChatAsRead(user.id, contact.id);
      }
    }

    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ChatRoomScreen(contact: contact),
      ),
    );

    _loadContacts(showLoading: false);
  }

  String _formatChatTime(String? rawTime) {
    if (rawTime == null || rawTime.trim().isEmpty) return '';
    try {
      final dt = DateTime.parse(rawTime).toLocal();
      final now = DateTime.now();

      if (dt.year == now.year && dt.month == now.month && dt.day == now.day) {
        final hour = dt.hour.toString().padLeft(2, '0');
        final minute = dt.minute.toString().padLeft(2, '0');
        return '$hour:$minute';
      }
      
      final yesterday = now.subtract(const Duration(days: 1));
      if (dt.year == yesterday.year && dt.month == yesterday.month && dt.day == yesterday.day) {
        return 'Kemarin';
      }

      final months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
      return '${dt.day} ${months[dt.month - 1]}';
    } catch (_) {
      return rawTime;
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final isGuruRole = user?.roleName.toLowerCase().contains('guru') ?? false;

    Stream<List<ChatContactModel>> chatStream;
    List<ChatContactModel> initialContacts;
    int totalUnreadCount = 0;

    if (isGuruRole) {
      final guruProvider = Provider.of<GuruProvider>(context);
      chatStream = guruProvider.chatContactsStream;
      initialContacts = guruProvider.chatContacts;
      totalUnreadCount = guruProvider.unreadChatCount;
    } else {
      final siswaProvider = Provider.of<SiswaProvider>(context);
      chatStream = siswaProvider.chatContactsStream;
      initialContacts = siswaProvider.chatContacts;
      totalUnreadCount = siswaProvider.unreadChatCount;
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Row(
          children: [
            const Text(
              'Pesan & Direct Chat',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            if (totalUnreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.red,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$totalUnreadCount Baru',
                  style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ],
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan Kontak',
            onPressed: () => _loadContacts(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Header Search Box
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
            decoration: const BoxDecoration(
              color: Color(0xFF0F172A),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
            ),
            child: TextField(
              controller: _searchController,
              onChanged: (_) => setState(() {}),
              style: const TextStyle(color: Colors.white, fontSize: 14),
              decoration: InputDecoration(
                hintText: 'Cari Kontak Guru, Siswa, Admin, Kepsek...',
                hintStyle: const TextStyle(color: Colors.white54, fontSize: 13),
                prefixIcon: const Icon(Icons.search_rounded, color: Colors.white54),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, color: Colors.white54, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          setState(() {});
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.white.withValues(alpha: 0.12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),

          // Contacts List Body
          Expanded(
            child: StreamBuilder<List<ChatContactModel>>(
              stream: chatStream,
              initialData: initialContacts,
              builder: (context, snapshot) {
                final currentList = snapshot.data ?? initialContacts;
                List<ChatContactModel> displayList = List.from(currentList);

                // Search Filter
                final query = _searchController.text.trim().toLowerCase();
                if (query.isNotEmpty) {
                  displayList = displayList.where((c) {
                    return c.fullName.toLowerCase().contains(query) || c.roleName.toLowerCase().contains(query);
                  }).toList();
                }

                // Automatic Real-Time Sorting (Unread -> Latest Message Time -> Name)
                displayList.sort((a, b) {
                  if (a.unreadCount > 0 && b.unreadCount == 0) return -1;
                  if (a.unreadCount == 0 && b.unreadCount > 0) return 1;
                  final timeComp = b.lastMessageTime.compareTo(a.lastMessageTime);
                  if (timeComp != 0) return timeComp;
                  return a.fullName.compareTo(b.fullName);
                });

                return RefreshIndicator(
                  onRefresh: () => _loadContacts(),
                  color: AppTheme.secondaryColor,
                  child: _isLoading && displayList.isEmpty
                      ? const Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              CircularProgressIndicator(),
                              SizedBox(height: 12),
                              Text('Memuat daftar percakapan...', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                            ],
                          ),
                        )
                      : displayList.isEmpty
                          ? SingleChildScrollView(
                              physics: const AlwaysScrollableScrollPhysics(),
                              padding: const EdgeInsets.symmetric(vertical: 60),
                              child: Center(
                                child: Column(
                                  children: [
                                    Icon(Icons.chat_bubble_outline_rounded, size: 54, color: Colors.grey.shade400),
                                    const SizedBox(height: 12),
                                    const Text(
                                      'Belum Ada Kontak / Chat Direct',
                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF1E293B)),
                                    ),
                                    const SizedBox(height: 4),
                                    const Text(
                                      'Kontak Guru dan Siswa akan otomatis muncul di sini.',
                                      style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              itemCount: displayList.length,
                              itemBuilder: (context, index) {
                                final c = displayList[index];
                                final bool isGuru = c.roleName.toLowerCase().contains('guru');
                                final hasUnread = c.unreadCount > 0;
                                final timeStr = _formatChatTime(c.lastTime);

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 8),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: hasUnread ? const Color(0xFFEF4444) : const Color(0xFFE2E8F0),
                                      width: hasUnread ? 1.5 : 1.0,
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: hasUnread
                                            ? const Color(0xFFEF4444).withValues(alpha: 0.08)
                                            : Colors.black.withValues(alpha: 0.02),
                                        blurRadius: 8,
                                        offset: const Offset(0, 3),
                                      ),
                                    ],
                                  ),
                                  child: Material(
                                    color: Colors.transparent,
                                    borderRadius: BorderRadius.circular(16),
                                    child: InkWell(
                                      borderRadius: BorderRadius.circular(16),
                                      onTap: () => _openChatRoom(c),
                                      child: Padding(
                                        padding: const EdgeInsets.all(12.0),
                                        child: Row(
                                          children: [
                                            // Avatar Container with Online & Unread Badge
                                            Stack(
                                              children: [
                                                CircleAvatar(
                                                  radius: 24,
                                                  backgroundColor: isGuru
                                                      ? const Color(0xFFFEF3C7)
                                                      : const Color(0xFFEFF6FF),
                                                  backgroundImage: (c.avatarUrl != null && c.avatarUrl!.isNotEmpty)
                                                      ? NetworkImage(c.avatarUrl!)
                                                      : null,
                                                  child: (c.avatarUrl == null || c.avatarUrl!.isEmpty)
                                                      ? Text(
                                                          c.fullName.isNotEmpty ? c.fullName[0].toUpperCase() : 'U',
                                                          style: TextStyle(
                                                            color: isGuru ? const Color(0xFFD97706) : const Color(0xFF2563EB),
                                                            fontWeight: FontWeight.bold,
                                                            fontSize: 18,
                                                          ),
                                                        )
                                                      : null,
                                                ),
                                                // Online indicator dot
                                                if (c.isOnline)
                                                  Positioned(
                                                    right: 0,
                                                    bottom: 0,
                                                    child: Container(
                                                      width: 13,
                                                      height: 13,
                                                      decoration: BoxDecoration(
                                                        color: const Color(0xFF10B981),
                                                        shape: BoxShape.circle,
                                                        border: Border.all(color: Colors.white, width: 2),
                                                      ),
                                                    ),
                                                  ),
                                              ],
                                            ),

                                            const SizedBox(width: 12),

                                            // Name & Message Body
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                children: [
                                                  Row(
                                                    children: [
                                                      Expanded(
                                                        child: Text(
                                                          c.fullName,
                                                          maxLines: 1,
                                                          overflow: TextOverflow.ellipsis,
                                                          style: TextStyle(
                                                            fontSize: 14,
                                                            fontWeight: hasUnread ? FontWeight.w800 : FontWeight.bold,
                                                            color: const Color(0xFF0F172A),
                                                          ),
                                                        ),
                                                      ),
                                                      const SizedBox(width: 6),
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                                        decoration: BoxDecoration(
                                                          color: isGuru ? const Color(0xFFFEF3C7) : const Color(0xFFF1F5F9),
                                                          borderRadius: BorderRadius.circular(6),
                                                        ),
                                                        child: Text(
                                                          c.roleName,
                                                          style: TextStyle(
                                                            color: isGuru ? const Color(0xFFB45309) : const Color(0xFF475569),
                                                            fontSize: 10,
                                                            fontWeight: FontWeight.bold,
                                                          ),
                                                        ),
                                                      ),
                                                    ],
                                                  ),
                                                  const SizedBox(height: 4),
                                                  Text(
                                                    c.lastMessage != null && c.lastMessage!.isNotEmpty
                                                        ? ProfanityService.filter(c.lastMessage)
                                                        : 'Ketuk untuk memulai percakapan...',
                                                    maxLines: 1,
                                                    overflow: TextOverflow.ellipsis,
                                                    style: TextStyle(
                                                      fontSize: 12,
                                                      fontWeight: hasUnread ? FontWeight.bold : FontWeight.normal,
                                                      color: hasUnread ? const Color(0xFF1E293B) : const Color(0xFF64748B),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),

                                            const SizedBox(width: 8),

                                            // Time & Unread Badge Column
                                            Column(
                                              crossAxisAlignment: CrossAxisAlignment.end,
                                              children: [
                                                if (timeStr.isNotEmpty)
                                                  Text(
                                                    timeStr,
                                                    style: TextStyle(
                                                      fontSize: 11,
                                                      fontWeight: hasUnread ? FontWeight.bold : FontWeight.w500,
                                                      color: hasUnread ? const Color(0xFFEF4444) : const Color(0xFF94A3B8),
                                                    ),
                                                  ),
                                                const SizedBox(height: 4),
                                                if (hasUnread)
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                    decoration: BoxDecoration(
                                                      color: const Color(0xFFEF4444),
                                                      borderRadius: BorderRadius.circular(12),
                                                      boxShadow: [
                                                        BoxShadow(
                                                          color: const Color(0xFFEF4444).withValues(alpha: 0.3),
                                                          blurRadius: 4,
                                                          offset: const Offset(0, 2),
                                                        ),
                                                      ],
                                                    ),
                                                    child: Text(
                                                      '${c.unreadCount}',
                                                      style: const TextStyle(
                                                        color: Colors.white,
                                                        fontSize: 11,
                                                        fontWeight: FontWeight.w900,
                                                      ),
                                                    ),
                                                  )
                                                else
                                                  const Icon(Icons.chevron_right_rounded, size: 18, color: Color(0xFFCBD5E1)),
                                              ],
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                );
              },
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
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  List<ChatMessageModel> _messages = [];
  bool _isLoading = true;
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _triggerMarkAsRead();
      }
    });
    _loadMessages();
    _pollingTimer = Timer.periodic(const Duration(seconds: 2), (_) => _loadMessages(showLoading: false));
  }

  void _triggerMarkAsRead() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      if (user.roleName.toLowerCase().contains('guru')) {
        Provider.of<GuruProvider>(context, listen: false).markContactChatAsRead(user.id, widget.contact.id);
      } else {
        Provider.of<SiswaProvider>(context, listen: false).markContactChatAsRead(user.id, widget.contact.id);
      }
    }
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
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
        final newMessages = (res['data'] as List).map((e) => ChatMessageModel.fromJson(e)).toList();
        final isFirstLoad = _messages.isEmpty;
        final hasNewCount = newMessages.length != _messages.length;

        setState(() {
          _messages = newMessages;
          _isLoading = false;
        });

        if (isFirstLoad || hasNewCount) {
          _scrollToBottom();
        }
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
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
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        titleSpacing: 0,
        title: Row(
          children: [
            Stack(
              children: [
                CircleAvatar(
                  radius: 19,
                  backgroundColor: Colors.white24,
                  backgroundImage: (widget.contact.avatarUrl != null && widget.contact.avatarUrl!.isNotEmpty)
                      ? NetworkImage(widget.contact.avatarUrl!)
                      : null,
                  child: (widget.contact.avatarUrl == null || widget.contact.avatarUrl!.isEmpty)
                      ? Text(
                          widget.contact.fullName.isNotEmpty ? widget.contact.fullName[0].toUpperCase() : 'U',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                        )
                      : null,
                ),
                if (widget.contact.isOnline)
                  Positioned(
                    right: 0,
                    bottom: 0,
                    child: Container(
                      width: 10,
                      height: 10,
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981),
                        shape: BoxShape.circle,
                        border: Border.all(color: const Color(0xFF0F172A), width: 1.5),
                      ),
                    ),
                  ),
              ],
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
                    widget.contact.isOnline ? '🟢 Online Sekarang' : widget.contact.roleName,
                    style: TextStyle(
                      fontSize: 11,
                      color: widget.contact.isOnline ? const Color(0xFF6EE7B7) : Colors.white70,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _messages.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.mark_chat_unread_outlined, size: 48, color: Colors.grey.shade400),
                            const SizedBox(height: 10),
                            const Text(
                              'Belum Ada Pesan',
                              style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Ketik pesan di bawah untuk memulai obrolan direct.',
                              style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        controller: _scrollController,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final m = _messages[index];
                          final isMe = user != null && m.senderId == user.id;

                          return Align(
                            alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.78),
                              decoration: BoxDecoration(
                                color: isMe ? const Color(0xFF1E3A8A) : Colors.white,
                                borderRadius: BorderRadius.only(
                                  topLeft: const Radius.circular(16),
                                  topRight: const Radius.circular(16),
                                  bottomLeft: isMe ? const Radius.circular(16) : Radius.zero,
                                  bottomRight: isMe ? Radius.zero : const Radius.circular(16),
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.03),
                                    blurRadius: 6,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    ProfanityService.filter(m.pesan),
                                    style: TextStyle(
                                      color: isMe ? Colors.white : const Color(0xFF0F172A),
                                      fontSize: 14,
                                      height: 1.3,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        m.createdAt,
                                        style: TextStyle(
                                          color: isMe ? Colors.white60 : const Color(0xFF94A3B8),
                                          fontSize: 10,
                                        ),
                                      ),
                                      if (isMe) ...[
                                        const SizedBox(width: 4),
                                        Icon(
                                          m.isRead ? Icons.done_all_rounded : Icons.done_rounded,
                                          size: 14,
                                          color: m.isRead ? const Color(0xFF6EE7B7) : Colors.white60,
                                        ),
                                      ],
                                    ],
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
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -3),
                ),
              ],
            ),
            child: SafeArea(
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: TextField(
                        controller: _messageController,
                        style: const TextStyle(fontSize: 14),
                        decoration: const InputDecoration(
                          hintText: 'Tulis pesan direct...',
                          hintStyle: TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: _sendMessage,
                    borderRadius: BorderRadius.circular(24),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: const BoxDecoration(
                        color: Color(0xFF1E3A8A),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
