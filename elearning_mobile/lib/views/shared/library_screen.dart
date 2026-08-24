import 'package:flutter/material.dart';
import '../../services/api_service.dart';

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
              'kategori': 'Panduan'
            },
            {
              'judul': 'Modul Pemrograman Web & Mobile',
              'penulis': 'Tim IT E-Learning',
              'kategori': 'Teknologi Informasi'
            },
            {
              'judul': 'Dasar-Dasar Teknik & Kejuruan',
              'penulis': 'Tim Guru Kejuruan',
              'kategori': 'Kejuruan'
            }
          ];
          _isLoading = false;
        });
      }
    }
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
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('Membuka ${book['judul']}...'),
                              backgroundColor: Colors.indigo,
                            ),
                          );
                        },
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
