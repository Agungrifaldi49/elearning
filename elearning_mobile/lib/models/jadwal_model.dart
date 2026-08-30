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

  static int _parseInt(dynamic val, [int defaultVal = 0]) {
    if (val == null) return defaultVal;
    if (val is int) return val;
    if (val is num) return val.toInt();
    if (val is String) {
      return int.tryParse(val) ?? (double.tryParse(val)?.toInt() ?? defaultVal);
    }
    return defaultVal;
  }

  factory JadwalModel.fromJson(Map<String, dynamic> json) {
    return JadwalModel(
      id: _parseInt(json['id']),
      kelasId: _parseInt(json['kelas_id']),
      mapelId: _parseInt(json['mapel_id']),
      guruId: _parseInt(json['guru_id']),
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
