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
  String _selectedCategory = 'Semua';

  final List<String> _categories = [
    'Semua',
    'Absensi',
    'Ujian',
    'Tugas',
    'Guru',
    'Pengaturan',
  ];

  @override
  void initState() {
    super.initState();
    _fetchPanduan();
  }

  Future<void> _fetchPanduan() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('panduan');
    if (mounted) {
      if (res['success'] == true && res['data'] is List && (res['data'] as List).isNotEmpty) {
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

  void _applyFilter() {
    final query = _searchController.text.trim().toLowerCase();
    setState(() {
      _filteredGuides = _guides.where((g) {
        final judul = (g['judul'] ?? '').toString().toLowerCase();
        final desk = (g['deskripsi'] ?? '').toString().toLowerCase();
        final kat = (g['kategori'] ?? '').toString();

        final matchCategory = _selectedCategory == 'Semua' || kat.toLowerCase() == _selectedCategory.toLowerCase();
        final matchSearch = query.isEmpty || judul.contains(query) || desk.contains(query) || kat.toLowerCase().contains(query);

        return matchCategory && matchSearch;
      }).toList();
    });
  }

  IconData _getCategoryIcon(String kat) {
    switch (kat.toLowerCase()) {
      case 'absensi':
        return Icons.qr_code_scanner_rounded;
      case 'ujian':
      case 'cbt':
        return Icons.assignment_turned_in_rounded;
      case 'tugas':
        return Icons.folder_shared_rounded;
      case 'guru':
        return Icons.school_rounded;
      case 'pengaturan':
        return Icons.settings_rounded;
      default:
        return Icons.help_center_rounded;
    }
  }

  Color _getCategoryColor(String kat) {
    switch (kat.toLowerCase()) {
      case 'absensi':
        return const Color(0xFF10B981);
      case 'ujian':
      case 'cbt':
        return Colors.purple;
      case 'tugas':
        return Colors.blue;
      case 'guru':
        return Colors.indigo;
      case 'pengaturan':
        return Colors.amber.shade800;
      default:
        return Colors.teal;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Buku Panduan & Help Center',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            Text(
              'Pusat Bantuan & Tutorial Penggunaan LMS',
              style: TextStyle(fontSize: 11, color: Colors.white70),
            ),
          ],
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF334155)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Top Search & Filter Container
                Container(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black45,
                        blurRadius: 4,
                        offset: Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      // Search Bar
                      TextField(
                        controller: _searchController,
                        onChanged: (_) => _applyFilter(),
                        decoration: InputDecoration(
                          hintText: 'Cari panduan (misal: presensi, tugas, cbt)...',
                          hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                          prefixIcon: const Icon(Icons.search_rounded, color: Colors.indigo),
                          suffixIcon: _searchController.text.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear_rounded, size: 18),
                                  onPressed: () {
                                    _searchController.clear();
                                    _applyFilter();
                                  },
                                )
                              : null,
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: Colors.grey.shade200),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: const BorderSide(color: Colors.indigo, width: 1.5),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Category Horizontal Chips
                      SizedBox(
                        height: 36,
                        child: ListView.separated(
                          scrollDirection: Axis.horizontal,
                          itemCount: _categories.length,
                          separatorBuilder: (context, index) => const SizedBox(width: 8),
                          itemBuilder: (context, index) {
                            final cat = _categories[index];
                            final isSelected = _selectedCategory == cat;
                            return ChoiceChip(
                              label: Text(cat),
                              selected: isSelected,
                              onSelected: (selected) {
                                if (selected) {
                                  setState(() {
                                    _selectedCategory = cat;
                                  });
                                  _applyFilter();
                                }
                              },
                              selectedColor: Colors.indigo,
                              backgroundColor: const Color(0xFFF1F5F9),
                              labelStyle: TextStyle(
                                color: isSelected ? Colors.white : const Color(0xFF475569),
                                fontSize: 12,
                                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                              ),
                              side: BorderSide(
                                color: isSelected ? Colors.indigo : Colors.grey.shade300,
                              ),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                              padding: const EdgeInsets.symmetric(horizontal: 4),
                            );
                          },
                        ),
                      ),
                    ],
                  ),
                ),

                // Guide List
                Expanded(
                  child: _filteredGuides.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.find_in_page_outlined, size: 56, color: Colors.grey.shade400),
                              const SizedBox(height: 12),
                              const Text(
                                'Topik Panduan Tidak Ditemukan',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF334155)),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Coba kata kunci pencarian atau kategori lain.',
                                style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredGuides.length,
                          itemBuilder: (context, index) {
                            final g = _filteredGuides[index];
                            final kat = g['kategori'] ?? 'Umum';
                            final color = _getCategoryColor(kat);
                            final icon = _getCategoryIcon(kat);

                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.03),
                                    blurRadius: 8,
                                    offset: const Offset(0, 3),
                                  ),
                                ],
                              ),
                              child: Theme(
                                data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                                child: ExpansionTile(
                                  tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                                  leading: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: color.withValues(alpha: 0.12),
                                      shape: BoxShape.circle,
                                    ),
                                    child: Icon(icon, color: color, size: 22),
                                  ),
                                  title: Text(
                                    g['judul'] ?? 'Panduan LMS',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                                  ),
                                  subtitle: Row(
                                    children: [
                                      Container(
                                        margin: const EdgeInsets.only(top: 4),
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: color.withValues(alpha: 0.1),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          kat.toUpperCase(),
                                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color),
                                        ),
                                      ),
                                    ],
                                  ),
                                  children: [
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(16),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF8FAFC),
                                        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
                                        border: Border(top: BorderSide(color: Colors.grey.shade200)),
                                      ),
                                      child: SelectableText(
                                        g['deskripsi'] ?? '',
                                        style: const TextStyle(
                                          color: Color(0xFF1E293B),
                                          height: 1.6,
                                          fontSize: 13,
                                          fontWeight: FontWeight.w400,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
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
