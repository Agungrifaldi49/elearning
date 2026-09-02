class TugasModel {
  final int id;
  final int guruId;
  final int mapelId;
  final int kelasId;
  final String judul;
  final String deskripsi;
  final String? filePath;
  final String deadline;
  final String namaMapel;
  final String? namaGuru;
  final String? namaKelas;
  final int? submissionId;
  final double? nilai;
  final String? komentarGuru;
  final String? submittedAt;
  final int? totalPengumpulan;
  final String? catatanSiswa;
  final String? filePathSiswa;
  final bool canSubmit;
  final bool isExpired;
  final String lockStatus;
  final String? susulanStatus;

  TugasModel({
    required this.id,
    required this.guruId,
    required this.mapelId,
    required this.kelasId,
    required this.judul,
    required this.deskripsi,
    this.filePath,
    required this.deadline,
    required this.namaMapel,
    this.namaGuru,
    this.namaKelas,
    this.submissionId,
    this.nilai,
    this.komentarGuru,
    this.submittedAt,
    this.totalPengumpulan,
    this.catatanSiswa,
    this.filePathSiswa,
    this.canSubmit = true,
    this.isExpired = false,
    this.lockStatus = 'terbuka',
    this.susulanStatus,
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

  static double? _parseDouble(dynamic val) {
    if (val == null) return null;
    if (val is double) return val;
    if (val is num) return val.toDouble();
    if (val is String) {
      return double.tryParse(val);
    }
    return null;
  }

  factory TugasModel.fromJson(Map<String, dynamic> json) {
    final String dlStr = json['deadline'] ?? '';
    bool calcExpired = false;
    if (dlStr.isNotEmpty) {
      final parsed = DateTime.tryParse(dlStr);
      if (parsed != null && DateTime.now().isAfter(parsed)) {
        calcExpired = true;
      }
    }

    final bool hasServerExpired = json.containsKey('is_expired') && json['is_expired'] != null;
    final bool parsedExpired = hasServerExpired
        ? (json['is_expired'] == true || json['is_expired'] == 1 || json['is_expired'] == '1')
        : calcExpired;

    final bool hasServerCanSubmit = json.containsKey('can_submit') && json['can_submit'] != null;
    final bool parsedCanSubmit = hasServerCanSubmit
        ? (json['can_submit'] == true || json['can_submit'] == 1 || json['can_submit'] == '1')
        : !parsedExpired;

    final String lockSt = json['lock_status']?.toString() ?? (parsedExpired && !parsedCanSubmit ? 'terkunci' : 'terbuka');
    final String? susulanSt = json['susulan_status']?.toString();

    return TugasModel(
      id: _parseInt(json['id']),
      guruId: _parseInt(json['guru_id']),
      mapelId: _parseInt(json['mapel_id']),
      kelasId: _parseInt(json['kelas_id']),
      judul: json['judul'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      filePath: json['file_path'],
      deadline: json['deadline'] ?? '',
      namaMapel: json['nama_mapel'] ?? '',
      namaGuru: json['nama_guru'],
      namaKelas: json['nama_kelas'],
      submissionId: json['submission_id'] != null ? _parseInt(json['submission_id']) : null,
      nilai: _parseDouble(json['nilai']),
      komentarGuru: json['komentar_guru'],
      submittedAt: json['submitted_at'],
      totalPengumpulan: json['total_pengumpulan'] != null ? _parseInt(json['total_pengumpulan']) : null,
      catatanSiswa: json['catatan_siswa']?.toString(),
      filePathSiswa: json['file_path_siswa']?.toString(),
      canSubmit: parsedCanSubmit,
      isExpired: parsedExpired,
      lockStatus: lockSt,
      susulanStatus: susulanSt,
    );
  }

  bool get isSubmitted => submissionId != null;
  bool get isGraded => nilai != null;
  bool get isSusulanPending => susulanStatus == 'pending' || lockStatus == 'pending';
  bool get isSusulanDitolak => susulanStatus == 'ditolak' || lockStatus == 'ditolak';
  bool get isSusulanDisetujui => lockStatus == 'disetujui_susulan' || susulanStatus == 'disetujui';
  bool get isLocked => !canSubmit || (isExpired && !isSubmitted && !isSusulanDisetujui);
}
