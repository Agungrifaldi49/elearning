import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/file_service.dart';

class LibraryScreen extends StatefulWidget {
  const LibraryScreen({super.key});

  @override
  State<LibraryScreen> createState() => _LibraryScreenState();
}

class _LibraryScreenState extends State<LibraryScreen> {
  bool _isLoading = true;
  List<dynamic> _books = [];

  @override
  void initState() {
    super.initState();
    _fetchLibraryData();
  }

  Future<void> _fetchLibraryData() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('library');
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _books = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _books = [
            {
              'judul': 'Buku Panduan E-Learning SMK',
              'penulis': 'Tim Kurikulum SMK',
              'kategori': 'Panduan',
              'file_url': 'https://smkmuthiaharapancicalengka.my.id/assets/docs/panduan.pdf'
            },
            {
              'judul': 'Modul Pemrograman Web & Mobile',
              'penulis': 'Tim IT E-Learning',
              'kategori': 'Teknologi Informasi',
              'file_url': 'https://smkmuthiaharapancicalengka.my.id/assets/docs/modul_web.pdf'
            },
            {
              'judul': 'Dasar-Dasar Teknik & Kejuruan',
              'penulis': 'Tim Guru Kejuruan',
              'kategori': 'Kejuruan',
              'file_url': 'https://smkmuthiaharapancicalengka.my.id/assets/docs/kejuruan.pdf'
            }
          ];
          _isLoading = false;
        });
      }
    }
  }

  void _showBookDetail(Map<String, dynamic> book) {
    final String judul = book['judul'] ?? 'Buku Digital';
    final String penulis = book['penulis'] ?? '-';
    final String kategori = book['kategori'] ?? 'Umum';
    final String deskripsi = book['deskripsi'] ?? 'Buku digital koleksi Perpustakaan SMK Muthia Harapan Cicalengka.';
    final String fileUrl = book['file_url'] ??
        (book['file_path'] != null ? "https://smkmuthiaharapancicalengka.my.id/${book['file_path']}" : "https://smkmuthiaharapancicalengka.my.id/assets/docs/panduan.pdf");

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 20,
            top: 20,
            left: 20,
            right: 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 54,
                    height: 70,
                    decoration: BoxDecoration(
                      color: Colors.indigo.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: Colors.indigo.shade200),
                    ),
                    child: const Icon(Icons.menu_book_rounded, color: Colors.indigo, size: 36),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.indigo.shade50,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            kategori.toUpperCase(),
                            style: TextStyle(color: Colors.indigo.shade800, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          judul,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          'Penulis: $penulis',
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Ringkasan / Deskripsi Buku:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  deskripsi,
                  style: TextStyle(color: Colors.grey.shade800, fontSize: 13, height: 1.4),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 46,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    FileService.openFileOrUrl(context, fileUrl);
                  },
                  icon: const Icon(Icons.picture_as_pdf, color: Colors.white),
                  label: const Text('Buka / Unduh File PDF Buku', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.indigo,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _openBookFile(String fileUrl, String judul) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: const [
            Icon(Icons.picture_as_pdf, color: Colors.red),
            SizedBox(width: 8),
            Expanded(child: Text('Tautan File PDF Buku', style: TextStyle(fontSize: 16))),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Judul: $judul', style: const TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            const Text('Tautan langsung berkas digital PDF:', style: TextStyle(fontSize: 12, color: Colors.grey)),
            const SizedBox(height: 4),
            SelectableText(
              fileUrl,
              style: const TextStyle(color: Colors.blue, decoration: TextDecoration.underline, fontSize: 13, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Perpustakaan Digital'),
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchLibraryData,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _books.length,
                itemBuilder: (context, index) {
                  final book = _books[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 3,
                    child: ListTile(
                      contentPadding: const EdgeInsets.all(12),
                      onTap: () => _showBookDetail(book),
                      leading: Container(
                        width: 48,
                        height: 60,
                        decoration: BoxDecoration(
                          color: Colors.indigo.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(
                          Icons.menu_book_rounded,
                          color: Colors.indigo,
                          size: 32,
                        ),
                      ),
                      title: Text(
                        book['judul'] ?? 'Judul Buku',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      subtitle: Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Text(
                          'Penulis: ${book['penulis'] ?? '-'}\nKategori: ${book['kategori'] ?? 'Umum'}',
                          style: TextStyle(
                            color: Colors.grey.shade700,
                            height: 1.3,
                          ),
                        ),
                      ),
                      trailing: IconButton(
                        icon: const Icon(Icons.picture_as_pdf, color: Colors.red),
                        onPressed: () => _showBookDetail(book),
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
