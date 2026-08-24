class JadwalModel {
  final int id;
  final int kelasId;
  final int mapelId;
  final int guruId;
  final String hari;
  final String jamMulai;
  final String jamSelesai;
  final String ruangan;
  final String namaMapel;
  final String? kodeMapel;
  final String? namaGuru;
  final String? namaKelas;

  JadwalModel({
    required this.id,
    required this.kelasId,
    required this.mapelId,
    required this.guruId,
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
    required this.ruangan,
    required this.namaMapel,
    this.kodeMapel,
    this.namaGuru,
    this.namaKelas,
  });

  factory JadwalModel.fromJson(Map<String, dynamic> json) {
    return JadwalModel(
      id: int.parse(json['id'].toString()),
      kelasId: int.parse(json['kelas_id'].toString()),
      mapelId: int.parse(json['mapel_id'].toString()),
      guruId: int.parse(json['guru_id'].toString()),
      hari: json['hari'] ?? '',
      jamMulai: json['jam_mulai'] ?? '',
      jamSelesai: json['jam_selesai'] ?? '',
      ruangan: json['ruangan'] ?? 'Ruang Kelas',
      namaMapel: json['nama_mapel'] ?? '',
      kodeMapel: json['kode_mapel'],
      namaGuru: json['nama_guru'],
      namaKelas: json['nama_kelas'],
    );
  }
}
