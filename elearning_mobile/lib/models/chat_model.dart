class ChatMessageModel {
  final int id;
  final int senderId;
  final int receiverId;
  final String pesan;
  final String? senderName;
  final String? receiverName;
  final String createdAt;
  final bool isRead;

  ChatMessageModel({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.pesan,
    this.senderName,
    this.receiverName,
    required this.createdAt,
    this.isRead = false,
  });

  static int _parseInt(dynamic val, [int defaultVal = 0]) {
    if (val == null) return defaultVal;
    if (val is int) return val;
    if (val is num) return val.toInt();
    if (val is String) {
      return int.tryParse(val) ?? (double.tryParse(val)?.toInt() ?? defaultVal);
    }
    return defaultVal;
  }

  factory ChatMessageModel.fromJson(Map<String, dynamic> json) {
    final rawIsRead = json['is_read'];
    bool isReadBool = false;
    if (rawIsRead is bool) {
      isReadBool = rawIsRead;
    } else if (rawIsRead != null) {
      isReadBool = _parseInt(rawIsRead) == 1 || rawIsRead.toString().toLowerCase() == 'true';
    }

    return ChatMessageModel(
      id: _parseInt(json['id']),
      senderId: _parseInt(json['sender_id']),
      receiverId: _parseInt(json['receiver_id']),
      pesan: (json['pesan'] ?? json['message'] ?? '').toString(),
      senderName: json['sender_name']?.toString(),
      receiverName: json['receiver_name']?.toString(),
      createdAt: (json['created_at'] ?? '').toString(),
      isRead: isReadBool,
    );
  }
}

class ChatContactModel {
  final int id;
  final String fullName;
  final String avatar;
  final String? avatarUrl;
  final String roleName;
  final String? lastMessage;
  final String? lastTime;
  final int unreadCount;
  final bool isOnline;

  ChatContactModel({
    required this.id,
    required this.fullName,
    required this.avatar,
    this.avatarUrl,
    required this.roleName,
    this.lastMessage,
    this.lastTime,
    this.unreadCount = 0,
    this.isOnline = false,
  });

  static int _parseInt(dynamic val, [int defaultVal = 0]) {
    if (val == null) return defaultVal;
    if (val is int) return val;
    if (val is num) return val.toInt();
    if (val is String) {
      return int.tryParse(val) ?? (double.tryParse(val)?.toInt() ?? defaultVal);
    }
    return defaultVal;
  }

  /// Get DateTime timestamp of the latest message safely for sorting comparison
  DateTime get lastMessageTime {
    if (lastTime == null || lastTime!.trim().isEmpty) {
      return DateTime.fromMillisecondsSinceEpoch(0);
    }
    try {
      return DateTime.parse(lastTime!);
    } catch (_) {
      return DateTime.fromMillisecondsSinceEpoch(0);
    }
  }

  factory ChatContactModel.fromJson(Map<String, dynamic> json) {
    String? avUrl = json['avatar_url']?.toString();
    final rawAv = json['avatar'] ?? json['avatar_file'];
    if (avUrl == null && rawAv != null && rawAv.toString().startsWith('http')) {
      avUrl = rawAv.toString();
    }

    final rawOnline = json['is_online'];
    bool isOnlineBool = false;
    if (rawOnline is bool) {
      isOnlineBool = rawOnline;
    } else if (rawOnline != null) {
      isOnlineBool = _parseInt(rawOnline) == 1 || rawOnline.toString().toLowerCase() == 'true';
    }

    return ChatContactModel(
      id: _parseInt(json['id'] ?? json['user_id']),
      fullName: (json['full_name'] ?? json['nama_lengkap'] ?? json['name'] ?? 'Pengguna').toString(),
      avatar: (rawAv ?? 'default_avatar.png').toString(),
      avatarUrl: avUrl,
      roleName: (json['role_name'] ?? json['role'] ?? 'Member').toString(),
      lastMessage: json['last_message']?.toString(),
      lastTime: json['last_time']?.toString() ?? json['last_message_time']?.toString(),
      unreadCount: _parseInt(json['unread_count'] ?? json['unread'] ?? 0),
      isOnline: isOnlineBool,
    );
  }

  ChatContactModel copyWith({
    int? id,
    String? fullName,
    String? avatar,
    String? avatarUrl,
    String? roleName,
    String? lastMessage,
    String? lastTime,
    int? unreadCount,
    bool? isOnline,
  }) {
    return ChatContactModel(
      id: id ?? this.id,
      fullName: fullName ?? this.fullName,
      avatar: avatar ?? this.avatar,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      roleName: roleName ?? this.roleName,
      lastMessage: lastMessage ?? this.lastMessage,
      lastTime: lastTime ?? this.lastTime,
      unreadCount: unreadCount ?? this.unreadCount,
      isOnline: isOnline ?? this.isOnline,
    );
  }
}
