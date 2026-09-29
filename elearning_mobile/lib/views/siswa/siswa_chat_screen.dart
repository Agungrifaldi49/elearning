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
    // Safe async post frame callback to prevent 'setState during build' exception
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadContacts();
      }
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadContacts({bool showLoading = true}) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    if (showLoading && _contacts.isEmpty && mounted) {
      setState(() => _isLoading = true);
    }

    List<ChatContactModel> list = [];
    try {
      if (user.roleName.toLowerCase().contains('guru')) {
        final guruProvider = Provider.of<GuruProvider>(context, listen: false);
        await guruProvider.fetchChatContactsSilent(user.id);
        list = guruProvider.chatContacts;
      } else {
        final siswaProvider = Provider.of<SiswaProvider>(context, listen: false);
        await siswaProvider.fetchChatContactsSilent(user.id);
        list = siswaProvider.chatContacts;
      }
    } catch (e) {
      debugPrint("Error loading chat contacts: $e");
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

    if (mounted) {
      _loadContacts(showLoading: false);
    }
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

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Row(
          children: [
            Flexible(
              child: Text(
                'Pesan & Direct Chat',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (totalUnreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.redAccent,
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
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 1,
        actions: [
          IconButton(
            icon: Icon(Icons.refresh_rounded, color: isDark ? Colors.white : const Color(0xFF0F172A)),
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
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
              border: Border(bottom: BorderSide(color: isDark ? Colors.white.withValues(alpha: 0.1) : Colors.grey.shade200)),
            ),
            child: TextField(
              controller: _searchController,
              onChanged: (_) => setState(() {}),
              style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A), fontSize: 14),
              decoration: InputDecoration(
                hintText: 'Cari Kontak Guru, Siswa, Admin, Kepsek...',
                hintStyle: TextStyle(color: isDark ? Colors.white54 : Colors.grey.shade500, fontSize: 13),
                prefixIcon: Icon(Icons.search_rounded, color: isDark ? Colors.white54 : Colors.grey.shade500),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: Icon(Icons.clear_rounded, color: isDark ? Colors.white54 : Colors.grey.shade500, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          setState(() {});
                        },
                      )
                    : null,
                filled: true,
                fillColor: isDark ? const Color(0xFF0F172A) : Colors.grey.shade100,
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
                  if (a.hasUnread && !b.hasUnread) return -1;
                  if (!a.hasUnread && b.hasUnread) return 1;
                  if (a.hasUnread && b.hasUnread) {
                    final unreadComp = b.unreadCount.compareTo(a.unreadCount);
                    if (unreadComp != 0) return unreadComp;
                  }
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
                                final timeStr = _formatChatTime(c.lastTime);
                                final bool hasHistory = c.lastMessage != null && c.lastMessage!.trim().isNotEmpty;
                                final bool isSentByMe = (c.lastSenderId != null && user != null && c.lastSenderId == user.id);

                                final String roleLower = c.roleName.toLowerCase();
                                final bool isGuru = roleLower.contains('guru');
                                final bool isSiswa = roleLower.contains('siswa');
                                final bool isAdmin = roleLower.contains('admin');
                                final bool isKepsek = roleLower.contains('kepala') || roleLower.contains('kepsek');

                                Color roleBgColor;
                                Color roleTextColor;
                                String roleLabel;

                                if (isGuru) {
                                  roleBgColor = isDark ? const Color(0xFF78350F) : const Color(0xFFFEF3C7);
                                  roleTextColor = isDark ? const Color(0xFFFDE68A) : const Color(0xFFB45309);
                                  roleLabel = (c.subRole != null && c.subRole!.isNotEmpty) ? 'Guru • ${c.subRole}' : 'Guru';
                                } else if (isSiswa) {
                                  roleBgColor = isDark ? const Color(0xFF1E3A8A) : const Color(0xFFEFF6FF);
                                  roleTextColor = isDark ? const Color(0xFF93C5FD) : const Color(0xFF1D4ED8);
                                  roleLabel = (c.subRole != null && c.subRole!.isNotEmpty) ? 'Siswa • ${c.subRole}' : 'Siswa';
                                } else if (isAdmin) {
                                  roleBgColor = isDark ? const Color(0xFF581C87) : const Color(0xFFF3E8FF);
                                  roleTextColor = isDark ? const Color(0xFFD8B4FE) : const Color(0xFF7E22CE);
                                  roleLabel = 'Admin';
                                } else if (isKepsek) {
                                  roleBgColor = isDark ? const Color(0xFF064E3B) : const Color(0xFFD1FAE5);
                                  roleTextColor = isDark ? const Color(0xFF6EE7B7) : const Color(0xFF047857);
                                  roleLabel = 'Kepala Sekolah';
                                } else {
                                  roleBgColor = isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9);
                                  roleTextColor = isDark ? const Color(0xFFCBD5E1) : const Color(0xFF475569);
                                  roleLabel = c.roleName;
                                }

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 8),
                                  decoration: BoxDecoration(
                                    color: isDark ? const Color(0xFF1E293B) : Colors.white,
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: c.hasUnread
                                          ? Colors.redAccent
                                          : (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                                      width: c.hasUnread ? 1.5 : 1.0,
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: c.hasUnread
                                            ? Colors.redAccent.withValues(alpha: 0.12)
                                            : Colors.black.withValues(alpha: 0.03),
                                        blurRadius: c.hasUnread ? 10 : 6,
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
                                            // Avatar Container with Online Dot & Unread Indicator
                                            Stack(
                                              clipBehavior: Clip.none,
                                              children: [
                                                CircleAvatar(
                                                  radius: 24,
                                                  backgroundColor: isGuru
                                                      ? (isDark ? const Color(0xFF78350F) : const Color(0xFFFEF3C7))
                                                      : isSiswa
                                                          ? (isDark ? const Color(0xFF1E3A8A) : const Color(0xFFEFF6FF))
                                                          : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                                                  backgroundImage: (c.avatarUrl != null && c.avatarUrl!.isNotEmpty)
                                                      ? NetworkImage(c.avatarUrl!)
                                                      : null,
                                                  child: (c.avatarUrl == null || c.avatarUrl!.isEmpty)
                                                      ? Text(
                                                          c.fullName.isNotEmpty ? c.fullName[0].toUpperCase() : 'U',
                                                          style: TextStyle(
                                                            color: isGuru
                                                                ? (isDark ? const Color(0xFFFDE68A) : const Color(0xFFD97706))
                                                                : (isDark ? const Color(0xFF93C5FD) : const Color(0xFF2563EB)),
                                                            fontWeight: FontWeight.bold,
                                                            fontSize: 18,
                                                          ),
                                                        )
                                                      : null,
                                                ),
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
                                                        border: Border.all(
                                                          color: isDark ? const Color(0xFF1E293B) : Colors.white,
                                                          width: 2,
                                                        ),
                                                      ),
                                                    ),
                                                  ),
                                                if (c.hasUnread)
                                                  Positioned(
                                                    top: -2,
                                                    right: -2,
                                                    child: Container(
                                                      width: 12,
                                                      height: 12,
                                                      decoration: BoxDecoration(
                                                        color: Colors.redAccent,
                                                        shape: BoxShape.circle,
                                                        border: Border.all(
                                                          color: isDark ? const Color(0xFF1E293B) : Colors.white,
                                                          width: 2,
                                                        ),
                                                        boxShadow: [
                                                          BoxShadow(
                                                            color: Colors.redAccent.withValues(alpha: 0.5),
                                                            blurRadius: 4,
                                                            spreadRadius: 1,
                                                          ),
                                                        ],
                                                      ),
                                                    ),
                                                  ),
                                              ],
                                            ),

                                            const SizedBox(width: 12),

                                            // Name & Role & Message Body
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
                                                            fontWeight: c.hasUnread ? FontWeight.w800 : FontWeight.bold,
                                                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                                                          ),
                                                        ),
                                                      ),
                                                      const SizedBox(width: 6),
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                                        decoration: BoxDecoration(
                                                          color: roleBgColor,
                                                          borderRadius: BorderRadius.circular(6),
                                                        ),
                                                        child: Text(
                                                          roleLabel,
                                                          style: TextStyle(
                                                            color: roleTextColor,
                                                            fontSize: 10,
                                                            fontWeight: FontWeight.bold,
                                                          ),
                                                        ),
                                                      ),
                                                    ],
                                                  ),
                                                  const SizedBox(height: 5),
                                                  if (hasHistory)
                                                    Row(
                                                      children: [
                                                        if (isSentByMe) ...[
                                                          Icon(
                                                            Icons.done_all_rounded,
                                                            size: 14,
                                                            color: isDark ? Colors.white60 : const Color(0xFF64748B),
                                                          ),
                                                          const SizedBox(width: 3),
                                                          Text(
                                                            'Anda: ',
                                                            style: TextStyle(
                                                              fontSize: 12,
                                                              fontWeight: FontWeight.w600,
                                                              color: isDark ? Colors.white70 : const Color(0xFF475569),
                                                            ),
                                                          ),
                                                        ],
                                                        Expanded(
                                                          child: Text(
                                                            ProfanityService.filter(c.lastMessage!),
                                                            maxLines: 1,
                                                            overflow: TextOverflow.ellipsis,
                                                            style: TextStyle(
                                                              fontSize: 12,
                                                              fontWeight: c.hasUnread ? FontWeight.bold : FontWeight.normal,
                                                              color: c.hasUnread
                                                                  ? (isDark ? Colors.white : const Color(0xFF0F172A))
                                                                  : (isDark ? Colors.white60 : const Color(0xFF64748B)),
                                                            ),
                                                          ),
                                                        ),
                                                      ],
                                                    )
                                                  else
                                                    Row(
                                                      children: [
                                                        Icon(
                                                          Icons.chat_bubble_outline_rounded,
                                                          size: 13,
                                                          color: isDark ? Colors.white38 : const Color(0xFF94A3B8),
                                                        ),
                                                        const SizedBox(width: 4),
                                                        Expanded(
                                                          child: Text(
                                                            'Belum ada pesan. Ketuk untuk mulai...',
                                                            maxLines: 1,
                                                            overflow: TextOverflow.ellipsis,
                                                            style: TextStyle(
                                                              fontSize: 12,
                                                              fontStyle: FontStyle.italic,
                                                              color: isDark ? Colors.white38 : const Color(0xFF94A3B8),
                                                            ),
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                ],
                                              ),
                                            ),

                                            const SizedBox(width: 8),

                                            // Trailing Column with Timestamp and Unread Pill Badge
                                            Column(
                                              mainAxisAlignment: MainAxisAlignment.center,
                                              crossAxisAlignment: CrossAxisAlignment.end,
                                              children: [
                                                if (timeStr.isNotEmpty)
                                                  Text(
                                                    timeStr,
                                                    style: TextStyle(
                                                      fontSize: 11,
                                                      fontWeight: c.hasUnread ? FontWeight.bold : FontWeight.normal,
                                                      color: c.hasUnread
                                                          ? Colors.redAccent
                                                          : (isDark ? Colors.white54 : const Color(0xFF94A3B8)),
                                                    ),
                                                  ),
                                                const SizedBox(height: 5),
                                                if (c.hasUnread)
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                                    constraints: const BoxConstraints(minWidth: 20),
                                                    decoration: BoxDecoration(
                                                      color: Colors.redAccent,
                                                      borderRadius: BorderRadius.circular(12),
                                                      boxShadow: [
                                                        BoxShadow(
                                                          color: Colors.redAccent.withValues(alpha: 0.35),
                                                          blurRadius: 4,
                                                          offset: const Offset(0, 2),
                                                        ),
                                                      ],
                                                    ),
                                                    child: Text(
                                                      c.unreadCount > 99 ? '99+' : '${c.unreadCount}',
                                                      textAlign: TextAlign.center,
                                                      style: const TextStyle(
                                                        color: Colors.white,
                                                        fontSize: 11,
                                                        fontWeight: FontWeight.bold,
                                                      ),
                                                    ),
                                                  )
                                                else
                                                  Icon(
                                                    Icons.chevron_right_rounded,
                                                    size: 18,
                                                    color: isDark ? Colors.white38 : const Color(0xFFCBD5E1),
                                                  ),
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
        _loadMessages();
      }
    });
    _pollingTimer = Timer.periodic(const Duration(seconds: 2), (_) {
      if (mounted) {
        _loadMessages(showLoading: false);
      }
    });
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

    if (showLoading && _messages.isEmpty && mounted) {
      setState(() => _isLoading = true);
    }

    try {
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
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && _scrollController.hasClients) {
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
      appBar: AppBar(
        titleSpacing: 0,
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 1,
        shadowColor: Colors.black.withValues(alpha: 0.05),
        leading: IconButton(
          icon: Icon(
            Icons.arrow_back_ios_new_rounded,
            size: 20,
            color: isDark ? Colors.white : const Color(0xFF0F172A),
          ),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          children: [
            Stack(
              children: [
                CircleAvatar(
                  radius: 19,
                  backgroundColor: isDark ? const Color(0xFF334155) : const Color(0xFFEEF2FF),
                  backgroundImage: (widget.contact.avatarUrl != null && widget.contact.avatarUrl!.isNotEmpty)
                      ? NetworkImage(widget.contact.avatarUrl!)
                      : null,
                  child: (widget.contact.avatarUrl == null || widget.contact.avatarUrl!.isEmpty)
                      ? Text(
                          widget.contact.fullName.isNotEmpty ? widget.contact.fullName[0].toUpperCase() : 'U',
                          style: TextStyle(
                            color: isDark ? Colors.white : const Color(0xFF4F46E5),
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
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
                        border: Border.all(
                          color: isDark ? const Color(0xFF1E293B) : Colors.white,
                          width: 1.5,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    widget.contact.fullName,
                    overflow: TextOverflow.ellipsis,
                    maxLines: 1,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      if (widget.contact.isOnline) ...[
                        Container(
                          width: 7,
                          height: 7,
                          margin: const EdgeInsets.only(right: 5),
                          decoration: const BoxDecoration(
                            color: Color(0xFF10B981),
                            shape: BoxShape.circle,
                          ),
                        ),
                        const Text(
                          'Online Sekarang',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF10B981),
                          ),
                        ),
                      ] else ...[
                        Flexible(
                          child: Text(
                            widget.contact.roleName,
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark ? Colors.white60 : const Color(0xFF64748B),
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(
              Icons.refresh_rounded,
              size: 22,
              color: isDark ? Colors.white70 : const Color(0xFF475569),
            ),
            tooltip: 'Segarkan Pesan',
            onPressed: () => _loadMessages(showLoading: false),
          ),
          const SizedBox(width: 4),
        ],
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
                            Text(
                              'Belum Ada Pesan',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : const Color(0xFF1E293B),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Ketik pesan di bawah untuk memulai obrolan direct.',
                              style: TextStyle(
                                fontSize: 12,
                                color: isDark ? Colors.white60 : const Color(0xFF64748B),
                              ),
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
                                color: isMe
                                    ? const Color(0xFF4F46E5)
                                    : (isDark ? const Color(0xFF1E293B) : Colors.white),
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
                                      color: isMe
                                          ? Colors.white
                                          : (isDark ? Colors.white : const Color(0xFF0F172A)),
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
                                          color: isMe
                                              ? Colors.white70
                                              : (isDark ? Colors.white60 : const Color(0xFF94A3B8)),
                                          fontSize: 10,
                                        ),
                                      ),
                                      if (isMe) ...[
                                        const SizedBox(width: 4),
                                        Icon(
                                          m.isRead ? Icons.done_all_rounded : Icons.done_rounded,
                                          size: 14,
                                          color: m.isRead ? const Color(0xFF6EE7B7) : Colors.white70,
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
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
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
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: TextField(
                        controller: _messageController,
                        style: TextStyle(
                          fontSize: 14,
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                        ),
                        decoration: InputDecoration(
                          hintText: 'Tulis pesan direct...',
                          hintStyle: TextStyle(
                            color: isDark ? Colors.white38 : const Color(0xFF94A3B8),
                            fontSize: 13,
                          ),
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
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
                        color: Color(0xFF4F46E5),
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
