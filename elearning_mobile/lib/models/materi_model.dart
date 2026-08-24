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

  factory MateriModel.fromJson(Map<String, dynamic> json) {
    return MateriModel(
      id: int.parse(json['id'].toString()),
      guruId: int.parse(json['guru_id'].toString()),
      mapelId: int.parse(json['mapel_id'].toString()),
      kelasId: int.parse(json['kelas_id'].toString()),
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
