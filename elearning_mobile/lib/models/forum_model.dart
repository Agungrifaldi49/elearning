class ForumModel {
  final int id;
  final int userId;
  final String judul;
  final String konten;
  final String? gambar;
  final String? gambarUrl;
  final String kategori;
  final String visibility;
  final String targetNamaKelas;
  final String fullName;
  final String avatar;
  final String? avatarUrl;
  final String roleName;
  final int totalKomentar;
  final String createdAt;

  ForumModel({
    required this.id,
    required this.userId,
    required this.judul,
    required this.konten,
    this.gambar,
    this.gambarUrl,
    required this.kategori,
    this.visibility = 'public',
    this.targetNamaKelas = 'Semua Kelas',
    required this.fullName,
    required this.avatar,
    this.avatarUrl,
    required this.roleName,
    required this.totalKomentar,
    required this.createdAt,
  });

  factory ForumModel.fromJson(Map<String, dynamic> json) {
    String? avUrl = json['avatar_url'];
    if (avUrl == null && json['avatar'] != null && json['avatar'].toString().startsWith('http')) {
      avUrl = json['avatar'];
    }
    String? gUrl = json['gambar_url'];
    if (gUrl == null && json['gambar'] != null && json['gambar'].toString().startsWith('http')) {
      gUrl = json['gambar'];
    }
    return ForumModel(
      id: int.parse((json['id'] ?? 0).toString()),
      userId: int.parse((json['user_id'] ?? 0).toString()),
      judul: json['judul'] ?? '',
      konten: json['konten'] ?? '',
      gambar: json['gambar'],
      gambarUrl: gUrl,
      kategori: json['kategori'] ?? 'Umum',
      visibility: json['visibility'] ?? 'public',
      targetNamaKelas: json['target_nama_kelas'] ?? json['nama_kelas'] ?? 'Semua Kelas',
      fullName: json['full_name'] ?? '',
      avatar: json['avatar'] ?? 'default_avatar.png',
      avatarUrl: avUrl,
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
  final String? gambar;
  final String? gambarUrl;
  final String fullName;
  final String avatar;
  final String? avatarUrl;
  final String createdAt;

  KomentarModel({
    required this.id,
    required this.forumId,
    required this.userId,
    required this.isiKomentar,
    this.gambar,
    this.gambarUrl,
    required this.fullName,
    required this.avatar,
    this.avatarUrl,
    required this.createdAt,
  });

  factory KomentarModel.fromJson(Map<String, dynamic> json) {
    String? avUrl = json['avatar_url'];
    if (avUrl == null && json['avatar'] != null && json['avatar'].toString().startsWith('http')) {
      avUrl = json['avatar'];
    }
    String? gUrl = json['gambar_url'];
    if (gUrl == null && json['gambar'] != null && json['gambar'].toString().startsWith('http')) {
      gUrl = json['gambar'];
    }
    return KomentarModel(
      id: int.parse((json['id'] ?? 0).toString()),
      forumId: int.parse((json['forum_id'] ?? 0).toString()),
      userId: int.parse((json['user_id'] ?? 0).toString()),
      isiKomentar: json['isi_komentar'] ?? json['komentar'] ?? '',
      gambar: json['gambar'],
      gambarUrl: gUrl,
      fullName: json['full_name'] ?? '',
      avatar: json['avatar'] ?? 'default_avatar.png',
      avatarUrl: avUrl,
      createdAt: json['created_at'] ?? '',
    );
  }
}
