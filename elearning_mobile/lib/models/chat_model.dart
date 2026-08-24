class ChatMessageModel {
  final int id;
  final int senderId;
  final int receiverId;
  final String pesan;
  final String? senderName;
  final String? receiverName;
  final String createdAt;

  ChatMessageModel({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.pesan,
    this.senderName,
    this.receiverName,
    required this.createdAt,
  });

  factory ChatMessageModel.fromJson(Map<String, dynamic> json) {
    return ChatMessageModel(
      id: int.parse((json['id'] ?? 0).toString()),
      senderId: int.parse((json['sender_id'] ?? 0).toString()),
      receiverId: int.parse((json['receiver_id'] ?? 0).toString()),
      pesan: json['pesan'] ?? '',
      senderName: json['sender_name'],
      receiverName: json['receiver_name'],
      createdAt: json['created_at'] ?? '',
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

  ChatContactModel({
    required this.id,
    required this.fullName,
    required this.avatar,
    this.avatarUrl,
    required this.roleName,
    this.lastMessage,
    this.lastTime,
    this.unreadCount = 0,
  });

  factory ChatContactModel.fromJson(Map<String, dynamic> json) {
    String? avUrl = json['avatar_url'];
    if (avUrl == null && json['avatar'] != null && json['avatar'].toString().startsWith('http')) {
      avUrl = json['avatar'];
    }
    return ChatContactModel(
      id: int.parse((json['id'] ?? 0).toString()),
      fullName: json['full_name'] ?? '',
      avatar: json['avatar'] ?? 'default_avatar.png',
      avatarUrl: avUrl,
      roleName: json['role_name'] ?? '',
      lastMessage: json['last_message'],
      lastTime: json['last_time'],
      unreadCount: int.parse((json['unread_count'] ?? 0).toString()),
    );
  }
}
