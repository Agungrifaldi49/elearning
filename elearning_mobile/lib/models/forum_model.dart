class ForumModel {
  final int id;
  final int userId;
  final String judul;
  final String konten;
  final String kategori;
  final String fullName;
  final String avatar;
  final String roleName;
  final int totalKomentar;
  final String createdAt;

  ForumModel({
    required this.id,
    required this.userId,
    required this.judul,
    required this.konten,
    required this.kategori,
    required this.fullName,
    required this.avatar,
    required this.roleName,
    required this.totalKomentar,
    required this.createdAt,
  });

  factory ForumModel.fromJson(Map<String, dynamic> json) {
    return ForumModel(
      id: int.parse(json['id'].toString()),
      userId: int.parse(json['user_id'].toString()),
      judul: json['judul'] ?? '',
      konten: json['konten'] ?? '',
      kategori: json['kategori'] ?? 'Umum',
      fullName: json['full_name'] ?? '',
      avatar: json['avatar'] ?? 'default_avatar.png',
      roleName: json['role_name'] ?? '',
      totalKomentar: int.parse((json['total_komentar'] ?? 0).toString()),
      createdAt: json['created_at'] ?? '',
    );
  }
}

class KomentarModel {
  final int id;
  final int forumId;
  final int userId;
  final String isiKomentar;
  final String fullName;
  final String avatar;
  final String createdAt;

  KomentarModel({
    required this.id,
    required this.forumId,
    required this.userId,
    required this.isiKomentar,
    required this.fullName,
    required this.avatar,
    required this.createdAt,
  });

  factory KomentarModel.fromJson(Map<String, dynamic> json) {
    return KomentarModel(
      id: int.parse(json['id'].toString()),
      forumId: int.parse(json['forum_id'].toString()),
      userId: int.parse(json['user_id'].toString()),
      isiKomentar: json['isi_komentar'] ?? '',
      fullName: json['full_name'] ?? '',
      avatar: json['avatar'] ?? 'default_avatar.png',
      createdAt: json['created_at'] ?? '',
    );
  }
}
