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
  });

  factory TugasModel.fromJson(Map<String, dynamic> json) {
    return TugasModel(
      id: int.parse(json['id'].toString()),
      guruId: int.parse(json['guru_id'].toString()),
      mapelId: int.parse(json['mapel_id'].toString()),
      kelasId: int.parse(json['kelas_id'].toString()),
      judul: json['judul'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      filePath: json['file_path'],
      deadline: json['deadline'] ?? '',
      namaMapel: json['nama_mapel'] ?? '',
      namaGuru: json['nama_guru'],
      namaKelas: json['nama_kelas'],
      submissionId: json['submission_id'] != null ? int.parse(json['submission_id'].toString()) : null,
      nilai: json['nilai'] != null ? double.parse(json['nilai'].toString()) : null,
      komentarGuru: json['komentar_guru'],
      submittedAt: json['submitted_at'],
      totalPengumpulan: json['total_pengumpulan'] != null ? int.parse(json['total_pengumpulan'].toString()) : null,
    );
  }

  bool get isSubmitted => submissionId != null;
  bool get isGraded => nilai != null;
}
