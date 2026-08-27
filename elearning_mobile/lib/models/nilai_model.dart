class NilaiModel {
  final int id;
  final int siswaId;
  final int mapelId;
  final String namaMapel;
  final String? kodeMapel;
  final int kkm;
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
    this.kkm = 75,
    required this.nilaiTugas,
    required this.nilaiQuiz,
    required this.nilaiUts,
    required this.nilaiUas,
    required this.nilaiAkhir,
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

  static double _parseDouble(dynamic val, [double defaultVal = 0.0]) {
    if (val == null) return defaultVal;
    if (val is double) return val;
    if (val is num) return val.toDouble();
    if (val is String) {
      return double.tryParse(val) ?? defaultVal;
    }
    return defaultVal;
  }

  static String _parseString(dynamic val, [String defaultVal = '']) {
    if (val == null) return defaultVal;
    return val.toString();
  }

  factory NilaiModel.fromJson(Map<String, dynamic> json) {
    return NilaiModel(
      id: _parseInt(json['id'] ?? json['nilai_id']),
      siswaId: _parseInt(json['siswa_id'] ?? json['user_id']),
      mapelId: _parseInt(json['mapel_id'] ?? json['id_mapel']),
      namaMapel: _parseString(
        json['nama_mapel'] ?? json['mapel_nama'] ?? json['subject'] ?? json['name'],
        'Mata Pelajaran',
      ),
      kodeMapel: json['kode_mapel'] != null ? _parseString(json['kode_mapel']) : null,
      kkm: _parseInt(json['kkm'] ?? json['kkm_mapel'], 75),
      nilaiTugas: _parseDouble(json['nilai_tugas'] ?? json['tugas']),
      nilaiQuiz: _parseDouble(json['nilai_quiz'] ?? json['quiz'] ?? json['kuis']),
      nilaiUts: _parseDouble(json['nilai_uts'] ?? json['uts']),
      nilaiUas: _parseDouble(json['nilai_uas'] ?? json['uas']),
      nilaiAkhir: _parseDouble(json['nilai_akhir'] ?? json['akhir'] ?? json['total_nilai'] ?? json['nilai']),
    );
  }

  /// Standar Predikat Huruf SMK Muthia Harapan Cicalengka
  String get predikat {
    if (nilaiAkhir >= 88) return 'A';
    if (nilaiAkhir >= 78) return 'B';
    if (nilaiAkhir >= 68) return 'C';
    return 'D';
  }

  /// Label Predikat Deskriptif
  String get predikatLabel {
    if (nilaiAkhir >= 88) return 'Sangat Baik';
    if (nilaiAkhir >= 78) return 'Baik';
    if (nilaiAkhir >= 68) return 'Cukup';
    return 'Kurang';
  }

  /// Status Ketuntasan berbasis Nilai Akhir & KKM Mapel
  bool get isTuntas => nilaiAkhir >= kkm;

  /// Helper teks status ketuntasan
  String get statusKetuntasan => isTuntas ? 'TUNTAS' : 'PERLU REMEDIAL';
}
