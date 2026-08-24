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
  final String? waktuMasuk;
  final String? waktuPulang;
  final String? namaGuru;

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
    this.waktuMasuk,
    this.waktuPulang,
    this.namaGuru,
  });

  factory AbsensiModel.fromJson(Map<String, dynamic> json) {
    return AbsensiModel(
      id: int.parse((json['id'] ?? 0).toString()),
      jadwalId: int.parse((json['jadwal_id'] ?? 0).toString()),
      siswaId: int.parse((json['siswa_id'] ?? 0).toString()),
      tanggal: json['tanggal'] ?? '',
      status: json['status'] ?? 'Hadir',
      keterangan: json['keterangan'],
      namaMapel: json['nama_mapel'],
      hari: json['hari'],
      jamMulai: json['jam_mulai'],
      waktuMasuk: json['waktu_masuk'],
      waktuPulang: json['waktu_pulang'],
      namaGuru: json['nama_guru'],
    );
  }
}
