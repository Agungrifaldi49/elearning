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
      tanggal: (json['tanggal'] ?? '').toString(),
      status: (json['status'] ?? 'Hadir').toString(),
      keterangan: json['keterangan']?.toString(),
      namaMapel: json['nama_mapel']?.toString(),
      hari: json['hari']?.toString(),
      jamMulai: json['jam_mulai']?.toString(),
      waktuMasuk: (json['waktu_masuk'] ?? json['waktu_hadir'])?.toString(),
      waktuPulang: json['waktu_pulang']?.toString(),
      namaGuru: json['nama_guru']?.toString(),
    );
  }

  bool get isAbsent => ['sakit', 'izin', 'alpa', 'alpha'].contains(status.toLowerCase());

  String get formattedTanggal {
    if (tanggal.isEmpty) return '-';
    try {
      final dt = DateTime.parse(tanggal.contains(' ') ? tanggal.split(' ')[0] : tanggal);
      final days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      final months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
      
      final dayName = (hari != null && hari!.isNotEmpty) ? hari : days[dt.weekday % 7];
      return "$dayName, ${dt.day} ${months[dt.month - 1]} ${dt.year}";
    } catch (_) {
      return (hari != null && hari!.isNotEmpty) ? "$hari $tanggal" : tanggal;
    }
  }

  String get formattedJamMasuk {
    if (isAbsent) return '-';
    final raw = waktuMasuk ?? jamMulai;
    if (raw == null || raw.isEmpty) return '-';
    
    if (raw.contains(' ')) {
      final parts = raw.split(' ');
      if (parts.length >= 2) {
        final t = parts[1];
        final tSub = t.length >= 5 ? t.substring(0, 5) : t;
        return "$tSub WIB";
      }
    }
    final sub = raw.length >= 5 ? raw.substring(0, 5) : raw;
    return "$sub WIB";
  }

  String get formattedJamPulang {
    if (isAbsent) return '-';
    if (waktuPulang == null || waktuPulang!.isEmpty) return 'Belum Pulang';
    
    final raw = waktuPulang!;
    if (raw.contains(' ')) {
      final parts = raw.split(' ');
      if (parts.length >= 2) {
        final t = parts[1];
        final tSub = t.length >= 5 ? t.substring(0, 5) : t;
        return "$tSub WIB";
      }
    }
    final sub = raw.length >= 5 ? raw.substring(0, 5) : raw;
    return "$sub WIB";
  }
}
