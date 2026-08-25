import '../services/api_service.dart';

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
  final String? accessStatus;
  final String? susulanStatus;
  final String? accessReason;
  final int maxAttempts;
  final int attemptCount;
  final String kategori;
  final String? accessKey;

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
    this.accessStatus,
    this.susulanStatus,
    this.accessReason,
    this.maxAttempts = 1,
    this.attemptCount = 0,
    this.kategori = 'kuis',
    this.accessKey,
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
      isDisqualified: json['is_disqualified'] == true || json['is_disqualified'].toString() == '1' || json['access_status'] == 'diskualifikasi',
      pelanggaranCount: int.parse((json['pelanggaran_count'] ?? 0).toString()),
      canAccess: json['can_access'] != false && json['can_access'].toString() != 'false',
      accessStatus: json['access_status'] ?? 'terbuka',
      susulanStatus: json['susulan_status'],
      accessReason: json['access_reason'],
      maxAttempts: int.parse((json['max_attempts'] ?? 1).toString()),
      attemptCount: int.parse((json['attempt_count'] ?? 0).toString()),
      kategori: json['kategori'] ?? 'kuis',
      accessKey: json['access_key'] ?? json['token'] ?? json['kunci_akses'],
    );
  }

  bool get isCompleted => finishedAt != null || totalNilai != null;
  bool get isUnlimitedAttempts => maxAttempts == 0 || maxAttempts >= 99;
  bool get isSuspended => (isDisqualified || accessStatus == 'diskualifikasi') && susulanStatus != 'disetujui';
  bool get isTerkunci => (accessStatus == 'terkunci') && susulanStatus != 'disetujui';
  bool get isMaxAttemptsReached => !isUnlimitedAttempts && maxAttempts > 0 && (attemptCount >= maxAttempts || accessStatus == 'max_attempts_reached') && susulanStatus != 'disetujui';

  bool get isUts => kategori.toLowerCase() == 'uts';
  bool get isUas => kategori.toLowerCase() == 'uas';
  bool get requiresToken => isUts || isUas || (accessKey != null && accessKey!.trim().isNotEmpty);
  String get kategoriBadgeText {
    if (isUts) return 'UTS 🔑';
    if (isUas) return 'UAS 🔑';
    if (accessKey != null && accessKey!.trim().isNotEmpty) return 'Kuis (Token) 🔑';
    return 'Kuis Harian';
  }
}

class SoalModel {
  final int id;
  final int quizId;
  final String jenisSoal;
  final String pertanyaan;
  final String? fileGambar;
  final String? fileGambarUrl;
  final int bobot;
  final List<PilihanModel> pilihan;

  SoalModel({
    required this.id,
    required this.quizId,
    required this.jenisSoal,
    required this.pertanyaan,
    this.fileGambar,
    this.fileGambarUrl,
    required this.bobot,
    required this.pilihan,
  });

  factory SoalModel.fromJson(Map<String, dynamic> json) {
    var pList = (json['pilihan'] as List? ?? []).map((e) => PilihanModel.fromJson(e)).toList();
    String? gPath = json['file_gambar'] ?? json['gambar'];
    String? gUrl = json['file_gambar_url'] ?? json['gambar_url'] ?? json['full_gambar_url'];

    if (gPath != null && gPath.toString().trim().isNotEmpty) {
      String pathStr = gPath.toString().trim();
      if (pathStr.startsWith('http://') || pathStr.startsWith('https://')) {
        gUrl = pathStr;
      } else {
        String cleanPath = pathStr.replaceFirst(RegExp(r'^/'), '');
        if (!cleanPath.startsWith('assets/uploads/') && !cleanPath.startsWith('uploads/')) {
          cleanPath = 'assets/uploads/soal/' + cleanPath;
        } else if (cleanPath.startsWith('uploads/')) {
          cleanPath = 'assets/' + cleanPath;
        }

        String rootUrl = ApiService.baseUrl;
        int apiIdx = rootUrl.indexOf('/api.php');
        if (apiIdx != -1) {
          rootUrl = rootUrl.substring(0, apiIdx);
        } else {
          int queryIdx = rootUrl.indexOf('?');
          if (queryIdx != -1) {
            rootUrl = rootUrl.substring(0, queryIdx);
          }
        }
        if (!rootUrl.endsWith('/')) {
          rootUrl += '/';
        }
        gUrl = rootUrl + cleanPath;
      }
    }

    return SoalModel(
      id: int.parse(json['id'].toString()),
      quizId: int.parse(json['quiz_id'].toString()),
      jenisSoal: json['jenis_soal'] ?? 'pg',
      pertanyaan: json['pertanyaan'] ?? '',
      fileGambar: gPath,
      fileGambarUrl: gUrl,
      bobot: int.parse((json['bobot'] ?? 10).toString()),
      pilihan: pList,
    );
  }

  bool get hasGambar => fileGambarUrl != null && fileGambarUrl!.isNotEmpty;
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
