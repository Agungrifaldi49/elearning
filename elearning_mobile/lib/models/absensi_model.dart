class AbsensiModel {
  final int id;
  final int jadwalId;
  final int siswaId;
  final String tanggal;
  final String status;
  final String? keterangan;
  final String? namaMapel;
  final String? hari;
  final String? jamMulai;

  AbsensiModel({
    required this.id,
    required this.jadwalId,
    required this.siswaId,
    required this.tanggal,
    required this.status,
    this.keterangan,
    this.namaMapel,
    this.hari,
    this.jamMulai,
  });

  factory AbsensiModel.fromJson(Map<String, dynamic> json) {
    return AbsensiModel(
      id: int.parse(json['id'].toString()),
      jadwalId: int.parse(json['jadwal_id'].toString()),
      siswaId: int.parse(json['siswa_id'].toString()),
      tanggal: json['tanggal'] ?? '',
      status: json['status'] ?? 'Hadir',
      keterangan: json['keterangan'],
      namaMapel: json['nama_mapel'],
      hari: json['hari'],
      jamMulai: json['jam_mulai'],
    );
  }
}
