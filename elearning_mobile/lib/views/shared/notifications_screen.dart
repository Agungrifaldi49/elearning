import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../services/fcm_service.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _notifications = [];
  int _unreadCount = 0;

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final userId = authProvider.user?['id'];

    if (userId == null) {
      setState(() {
        _isLoading = false;
        _errorMessage = "Sesi pengguna tidak valid.";
      });
      return;
    }

    try {
      final response = await ApiService.get('get_notifications', queryParameters: {'user_id': userId});

      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        setState(() {
          _notifications = data['notifications'] ?? [];
          _unreadCount = data['unread_count'] ?? 0;
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = response['message'] ?? "Gagal memuat notifikasi.";
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = "Gagal terhubung ke server: $e";
        _isLoading = false;
      });
    }
  }

  Future<void> _markAsRead({int? notificationId}) async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final userId = authProvider.user?['id'];

    try {
      final body = <String, dynamic>{'user_id': userId};
      if (notificationId != null) {
        body['notification_id'] = notificationId;
      }

      await ApiService.post('mark_notification_read', body);
      _fetchNotifications();
    } catch (_) {}
  }

  IconData _getNotificationIcon(String type) {
    switch (type.toLowerCase()) {
      case 'absensi':
        return Icons.alarm_on_rounded;
      case 'jadwal':
        return Icons.auto_stories_rounded;
      case 'chat':
        return Icons.chat_rounded;
      case 'forum':
        return Icons.forum_rounded;
      case 'live_class':
        return Icons.videocam_rounded;
      case 'pengumuman':
      default:
        return Icons.campaign_rounded;
    }
  }

  Color _getNotificationColor(String type) {
    switch (type.toLowerCase()) {
      case 'absensi':
        return const Color(0xFFF59E0B); // Amber
      case 'jadwal':
        return const Color(0xFF3B82F6); // Blue
      case 'chat':
        return const Color(0xFF10B981); // Emerald
      case 'forum':
        return const Color(0xFF8B5CF6); // Purple
      case 'live_class':
        return const Color(0xFFEF4444); // Red
      case 'pengumuman':
      default:
        return const Color(0xFF06B6D4); // Cyan
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Text(
              "Pusat Notifikasi",
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            if (_unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.redAccent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  "$_unreadCount baru",
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ],
        ),
        actions: [
          if (_unreadCount > 0)
            IconButton(
              icon: const Icon(Icons.done_all_rounded),
              tooltip: "Tandai semua dibaca",
              onPressed: () => _markAsRead(),
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: "Muat Ulang",
            onPressed: _fetchNotifications,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchNotifications,
        child: _buildBody(isDark),
      ),
    );
  }

  Widget _buildBody(bool isDark) {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline_rounded, size: 48, color: Colors.red.shade400),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _fetchNotifications,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text("Coba Lagi"),
              )
            ],
          ),
        ),
      );
    }

    if (_notifications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.blue.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.notifications_off_outlined,
                size: 64,
                color: Colors.blueAccent,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              "Belum Ada Notifikasi",
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            const Text(
              "Pemberitahuan aktivitas KBM, absensi, chat, dan forum akan tampil di sini.",
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      itemCount: _notifications.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final item = _notifications[index];
        final bool isRead = (item['is_read'] == 1 || item['is_read'] == true || item['is_read'] == '1');
        final String type = item['type'] ?? 'general';
        final Color themeColor = _getNotificationColor(type);

        return Card(
          elevation: isRead ? 0.5 : 2,
          color: isRead 
              ? (isDark ? const Color(0xFF1E293B) : Colors.white) 
              : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF0F9FF)),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(
              color: isRead ? Colors.transparent : themeColor.withOpacity(0.3),
              width: 1,
            ),
          ),
          child: InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: () {
              if (!isRead) {
                _markAsRead(notificationId: item['id']);
              }
              FcmService.handleNotificationNavigation({
                'type': type,
                'id': item['target_id'] ?? item['id'],
                'title': item['title'],
              });
            },
            child: Padding(
              padding: const EdgeInsets.all(14.0),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: themeColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(
                      _getNotificationIcon(type),
                      color: themeColor,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                item['title'] ?? 'Notifikasi System',
                                style: TextStyle(
                                  fontWeight: isRead ? FontWeight.w600 : FontWeight.bold,
                                  fontSize: 14,
                                  color: isDark ? Colors.white : Colors.black87,
                                ),
                              ),
                            ),
                            if (!isRead)
                              Container(
                                width: 8,
                                height: 8,
                                decoration: BoxDecoration(
                                  color: themeColor,
                                  shape: BoxShape.circle,
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          item['message'] ?? '',
                          style: TextStyle(
                            fontSize: 13,
                            color: isDark ? Colors.grey.shade300 : Colors.grey.shade700,
                            height: 1.3,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          item['created_at'] ?? '',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.grey.shade500 : Colors.grey.shade500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
