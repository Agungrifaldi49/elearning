class MateriModel {
  final int id;
  final int guruId;
  final int mapelId;
  final int kelasId;
  final String judul;
  final String deskripsi;
  final String jenisFile;
  final String? filePath;
  final String? youtubeUrl;
  final String namaMapel;
  final String? namaGuru;
  final String? namaKelas;
  final String createdAt;

  MateriModel({
    required this.id,
    required this.guruId,
    required this.mapelId,
    required this.kelasId,
    required this.judul,
    required this.deskripsi,
    required this.jenisFile,
    this.filePath,
    this.youtubeUrl,
    required this.namaMapel,
    this.namaGuru,
    this.namaKelas,
    required this.createdAt,
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

  factory MateriModel.fromJson(Map<String, dynamic> json) {
    return MateriModel(
      id: _parseInt(json['id']),
      guruId: _parseInt(json['guru_id']),
      mapelId: _parseInt(json['mapel_id']),
      kelasId: _parseInt(json['kelas_id']),
      judul: json['judul'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      jenisFile: json['jenis_file'] ?? 'pdf',
      filePath: json['file_path'],
      youtubeUrl: json['youtube_url'],
      namaMapel: json['nama_mapel'] ?? '',
      namaGuru: json['nama_guru'],
      namaKelas: json['nama_kelas'],
      createdAt: json['created_at'] ?? '',
    );
  }
}
