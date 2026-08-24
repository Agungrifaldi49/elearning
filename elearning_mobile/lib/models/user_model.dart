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
    final avatarFile = (json['avatar_url'] ?? json['avatar'] ?? detailsData?['foto_profil'] ?? detailsData?['foto'] ?? detailsData?['avatar'] ?? '').toString();
    return UserModel(
      id: int.parse(json['id'].toString()),
      roleId: int.parse(json['role_id'].toString()),
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      fullName: json['full_name'] ?? detailsData?['nama_lengkap'] ?? '',
      avatar: avatarFile,
      roleName: roleStr,
      details: detailsData,
    );
  }

  bool get isGuru => roleName.toLowerCase() == 'guru';
  bool get isSiswa => roleName.toLowerCase() == 'siswa';

  String get fullAvatarUrl {
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) {
      return avatar;
    }
    if (avatar.isNotEmpty && avatar != 'default_avatar.png' && avatar != 'default.png') {
      return 'https://smkmuthiaharapancicalengka.my.id/assets/uploads/profile/$avatar';
    }
    return '';
  }

  String get nis => details?['nis']?.toString() ?? username;
  String get nisn => details?['nisn']?.toString() ?? '-';
  String get nip => details?['nip']?.toString() ?? username;
  String get noTelepon => details?['no_telepon']?.toString() ?? '-';
  String get alamat => details?['alamat']?.toString() ?? '-';
  String get jenisKelamin => (details?['jenis_kelamin']?.toString() == 'P') ? 'Perempuan' : 'Laki-Laki';
  String get namaKelas => details?['nama_kelas']?.toString() ?? 'Kelas';
  String get namaJurusan => details?['nama_jurusan']?.toString() ?? 'Jurusan SMK';

  String get subTitle {
    if (isSiswa && details != null) {
      return "$namaKelas - $namaJurusan";
    } else if (isGuru && details != null) {
      return "NIP: $nip";
    }
    return roleName;
  }
}
