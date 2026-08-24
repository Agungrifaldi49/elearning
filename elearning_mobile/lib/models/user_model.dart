class UserModel {
  final int id;
  final int roleId;
  final String username;
  final String email;
  final String fullName;
  final String avatar;
  final String roleName;
  final Map<String, dynamic>? details;

  UserModel({
    required this.id,
    required this.roleId,
    required this.username,
    required this.email,
    required this.fullName,
    required this.avatar,
    required this.roleName,
    this.details,
  });

  factory UserModel.fromJson(Map<String, dynamic> json, Map<String, dynamic>? detailsData, String roleStr) {
    return UserModel(
      id: int.parse(json['id'].toString()),
      roleId: int.parse(json['role_id'].toString()),
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      fullName: json['full_name'] ?? '',
      avatar: json['avatar'] ?? 'default_avatar.png',
      roleName: roleStr,
      details: detailsData,
    );
  }

  bool get isGuru => roleName.toLowerCase() == 'guru';
  bool get isSiswa => roleName.toLowerCase() == 'siswa';

  String get subTitle {
    if (isSiswa && details != null) {
      return "${details!['nama_kelas'] ?? ''} - ${details!['nama_jurusan'] ?? ''}";
    } else if (isGuru && details != null) {
      return "NIP: ${details!['nip'] ?? '-'}";
    }
    return roleName;
  }
}
