class QuizModel {
  final int id;
  final int guruId;
  final int mapelId;
  final int kelasId;
  final String judul;
  final String deskripsi;
  final int durasiMenit;
  final int jumlahSoal;
  final String status;
  final String namaMapel;
  final String? namaGuru;
  final String? namaKelas;
  final double? totalNilai;
  final String? statusLulus;
  final String? finishedAt;
  final int? totalPeserta;
  final bool isDisqualified;
  final int pelanggaranCount;
  final bool canAccess;
  final String? susulanStatus;
  final String? accessReason;

  QuizModel({
    required this.id,
    required this.guruId,
    required this.mapelId,
    required this.kelasId,
    required this.judul,
    required this.deskripsi,
    required this.durasiMenit,
    required this.jumlahSoal,
    required this.status,
    required this.namaMapel,
    this.namaGuru,
    this.namaKelas,
    this.totalNilai,
    this.statusLulus,
    this.finishedAt,
    this.totalPeserta,
    this.isDisqualified = false,
    this.pelanggaranCount = 0,
    this.canAccess = true,
    this.susulanStatus,
    this.accessReason,
  });

  factory QuizModel.fromJson(Map<String, dynamic> json) {
    return QuizModel(
      id: int.parse(json['id'].toString()),
      guruId: int.parse(json['guru_id'].toString()),
      mapelId: int.parse(json['mapel_id'].toString()),
      kelasId: int.parse(json['kelas_id'].toString()),
      judul: json['judul'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      durasiMenit: int.parse((json['durasi_menit'] ?? 30).toString()),
      jumlahSoal: int.parse((json['jumlah_soal'] ?? 10).toString()),
      status: json['status'] ?? 'published',
      namaMapel: json['nama_mapel'] ?? '',
      namaGuru: json['nama_guru'],
      namaKelas: json['nama_kelas'],
      totalNilai: json['total_nilai'] != null ? double.parse(json['total_nilai'].toString()) : null,
      statusLulus: json['status_lulus'],
      finishedAt: json['finished_at'],
      totalPeserta: json['total_peserta'] != null ? int.parse(json['total_peserta'].toString()) : null,
      isDisqualified: json['is_disqualified'] == true || json['is_disqualified'].toString() == '1',
      pelanggaranCount: int.parse((json['pelanggaran_count'] ?? 0).toString()),
      canAccess: json['can_access'] != false && json['can_access'].toString() != 'false',
      susulanStatus: json['susulan_status'],
      accessReason: json['access_reason'],
    );
  }

  bool get isCompleted => finishedAt != null;
  bool get isSuspended => isDisqualified || (!canAccess && susulanStatus != 'disetujui');
}

class SoalModel {
  final int id;
  final int quizId;
  final String jenisSoal;
  final String pertanyaan;
  final String? fileGambar;
  final int bobot;
  final List<PilihanModel> pilihan;

  SoalModel({
    required this.id,
    required this.quizId,
    required this.jenisSoal,
    required this.pertanyaan,
    this.fileGambar,
    required this.bobot,
    required this.pilihan,
  });

  factory SoalModel.fromJson(Map<String, dynamic> json) {
    var pList = (json['pilihan'] as List? ?? []).map((e) => PilihanModel.fromJson(e)).toList();
    return SoalModel(
      id: int.parse(json['id'].toString()),
      quizId: int.parse(json['quiz_id'].toString()),
      jenisSoal: json['jenis_soal'] ?? 'pg',
      pertanyaan: json['pertanyaan'] ?? '',
      fileGambar: json['file_gambar'],
      bobot: int.parse((json['bobot'] ?? 10).toString()),
      pilihan: pList,
    );
  }
}

class PilihanModel {
  final int id;
  final int soalId;
  final String teksPilihan;

  PilihanModel({
    required this.id,
    required this.soalId,
    required this.teksPilihan,
  });

  factory PilihanModel.fromJson(Map<String, dynamic> json) {
    return PilihanModel(
      id: int.parse(json['id'].toString()),
      soalId: int.parse(json['soal_id'].toString()),
      teksPilihan: json['teks_pilihan'] ?? '',
    );
  }
}
