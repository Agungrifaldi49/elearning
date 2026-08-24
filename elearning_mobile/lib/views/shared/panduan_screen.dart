import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class PanduanScreen extends StatefulWidget {
  const PanduanScreen({super.key});

  @override
  State<PanduanScreen> createState() => _PanduanScreenState();
}

class _PanduanScreenState extends State<PanduanScreen> {
  bool _isLoading = true;
  List<dynamic> _guides = [];
  List<dynamic> _filteredGuides = [];
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchPanduan();
  }

  Future<void> _fetchPanduan() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('panduan');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _guides = res['data'];
          _filteredGuides = _guides;
          _isLoading = false;
        });
      } else {
        setState(() {
          _guides = [
            {
              'judul': 'Panduan Presensi QR Code Mobile',
              'kategori': 'Absensi',
              'deskripsi': "1. Buka Tab Presensi / Scan QR Code pada aplikasi Mobile.\n2. Untuk Guru: Arahkan kamera HP ke Kartu QR Presensi Siswa.\n3. Untuk Siswa: Tunjukkan Kartu Presensi Digital ke Guru.\n4. Sistem akan secara otomatis memutar suara konfirmasi presensi dan menyimpan data ke database E-Learning."
            },
            {
              'judul': 'Panduan Pengerjaan CBT & Quiz Online',
              'kategori': 'Ujian',
              'deskripsi': "1. Masuk ke Tab CBT & Quiz.\n2. Pilih Ujian atau Quiz yang tersedia dan klik 'Mulai Ujian'.\n3. Jawab soal secara berurutan sebelum batas waktu timer habis.\n4. Klik 'Kirim Ujian Selesai' untuk mengirimkan nilai secara otomatis ke database."
            },
            {
              'judul': 'Panduan Pengunduhan & Pengumpulan Tugas',
              'kategori': 'Tugas',
              'deskripsi': "1. Masuk ke Tab Tugas.\n2. Klik pada kartu Tugas untuk membuka Detail Instruksi dan File Lampiran dari Guru.\n3. Unduh atau buka file instruksi tugas.\n4. Ketikkan Catatan Jawaban / Masukkan nama file jawaban lalu klik 'Kirim Jawaban'."
            },
            {
              'judul': 'Panduan Pengelolaan Bank Soal Guru',
              'kategori': 'Guru',
              'deskripsi': "1. Guru masuk ke Tab CBT & Quiz lalu pilih 'Buat Quiz CBT'.\n2. Klik pada Kartu Quiz untuk membuka Bank Soal.\n3. Klik 'Tambah Soal' untuk memasukkan pertanyaan, bobot nilai, dan pilihan jawaban.\n4. Soal yang dibuat akan langsung tersimpan di Bank Soal E-Learning."
            },
            {
              'judul': 'Panduan Edit Profil & Upload Foto',
              'kategori': 'Pengaturan',
              'deskripsi': "1. Buka Menu Samping / AppBar Header di pojok kanan atas.\n2. Pilih 'Edit & Update Profil'.\n3. Ketuk ikon Kamera pada foto profil untuk memilih foto dari Galeri HP atau memotret langsung dari Kamera.\n4. Isikan data nomor telepon, alamat, password baru jika perlu, lalu klik 'Simpan Perubahan Profil'."
            }
          ];
          _filteredGuides = _guides;
          _isLoading = false;
        });
      }
    }
  }

  void _filterGuides(String query) {
    if (query.isEmpty) {
      setState(() => _filteredGuides = _guides);
    } else {
      setState(() {
        _filteredGuides = _guides.where((g) {
          final judul = (g['judul'] ?? '').toString().toLowerCase();
          final desk = (g['deskripsi'] ?? '').toString().toLowerCase();
          final kat = (g['kategori'] ?? '').toString().toLowerCase();
          final q = query.toLowerCase();
          return judul.contains(q) || desk.contains(q) || kat.contains(q);
        }).toList();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Buku Panduan & Help Center'),
        backgroundColor: Colors.blueGrey.shade800,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  color: Colors.blueGrey.shade50,
                  child: TextField(
                    controller: _searchController,
                    onChanged: _filterGuides,
                    decoration: InputDecoration(
                      hintText: 'Cari panduan (misal: presensi, tugas, cbt)...',
                      prefixIcon: const Icon(Icons.search),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                ),
                Expanded(
                  child: _filteredGuides.isEmpty
                      ? const Center(child: Text('Topik panduan tidak ditemukan.'))
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredGuides.length,
                          itemBuilder: (context, index) {
                            final g = _filteredGuides[index];
                            final kat = g['kategori'] ?? 'Umum';
                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 2,
                              child: ExpansionTile(
                                leading: Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: Colors.blueGrey.shade100,
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.help_outline, color: Colors.blueGrey),
                                ),
                                title: Text(
                                  g['judul'] ?? 'Panduan LMS',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                ),
                                subtitle: Text(
                                  "Kategori: $kat",
                                  style: TextStyle(fontSize: 12, color: Colors.blueGrey.shade600),
                                ),
                                children: [
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(16),
                                    color: Colors.grey.shade50,
                                    child: SelectableText(
                                      g['deskripsi'] ?? '',
                                      style: TextStyle(color: Colors.grey.shade900, height: 1.5, fontSize: 14),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }
}
