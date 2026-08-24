class NilaiModel {
  final int id;
  final int siswaId;
  final int mapelId;
  final String namaMapel;
  final String? kodeMapel;
  final double nilaiTugas;
  final double nilaiQuiz;
  final double nilaiUts;
  final double nilaiUas;
  final double nilaiAkhir;

  NilaiModel({
    required this.id,
    required this.siswaId,
    required this.mapelId,
    required this.namaMapel,
    this.kodeMapel,
    required this.nilaiTugas,
    required this.nilaiQuiz,
    required this.nilaiUts,
    required this.nilaiUas,
    required this.nilaiAkhir,
  });

  factory NilaiModel.fromJson(Map<String, dynamic> json) {
    return NilaiModel(
      id: int.parse(json['id'].toString()),
      siswaId: int.parse(json['siswa_id'].toString()),
      mapelId: int.parse(json['mapel_id'].toString()),
      namaMapel: json['nama_mapel'] ?? '',
      kodeMapel: json['kode_mapel'],
      nilaiTugas: double.parse((json['nilai_tugas'] ?? 0).toString()),
      nilaiQuiz: double.parse((json['nilai_quiz'] ?? 0).toString()),
      nilaiUts: double.parse((json['nilai_uts'] ?? 0).toString()),
      nilaiUas: double.parse((json['nilai_uas'] ?? 0).toString()),
      nilaiAkhir: double.parse((json['nilai_akhir'] ?? 0).toString()),
    );
  }

  String get predikat {
    if (nilaiAkhir >= 90) return 'A';
    if (nilaiAkhir >= 80) return 'B';
    if (nilaiAkhir >= 70) return 'C';
    if (nilaiAkhir >= 60) return 'D';
    return 'E';
  }
}
