import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/file_service.dart';

class LibraryScreen extends StatefulWidget {
  const LibraryScreen({super.key});

  @override
  State<LibraryScreen> createState() => _LibraryScreenState();
}

class _LibraryScreenState extends State<LibraryScreen> {
  final TextEditingController _searchController = TextEditingController();
  bool _isLoading = true;
  bool _isGridView = true;
  String _selectedCategory = 'Semua';
  List<dynamic> _books = [];
  List<dynamic> _filteredBooks = [];

  final List<String> _categories = [
    'Semua',
    'Panduan',
    'Teknologi Informasi',
    'Kejuruan',
    'Umum',
    'Sains',
  ];

  @override
  void initState() {
    super.initState();
    _fetchLibraryData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchLibraryData() async {
    setState(() => _isLoading = true);
    final res = await ApiService.get('library', params: {
      'search': _searchController.text.trim(),
      'kategori': _selectedCategory,
    });
    if (mounted) {
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _books = res['data'];
          _applyFilter();
          _isLoading = false;
        });
      } else {
        setState(() {
          _books = [];
          _applyFilter();
          _isLoading = false;
        });
      }
    }
  }

  void _applyFilter() {
    final q = _searchController.text.trim().toLowerCase();
    setState(() {
      _filteredBooks = _books.where((b) {
        final title = (b['judul'] ?? '').toString().toLowerCase();
        final author = (b['penulis'] ?? '').toString().toLowerCase();
        final desc = (b['deskripsi'] ?? '').toString().toLowerCase();
        final kat = (b['kategori'] ?? '').toString().toLowerCase();

        final matchesSearch = q.isEmpty || title.contains(q) || author.contains(q) || desc.contains(q);
        final matchesKat = _selectedCategory == 'Semua' || kat == _selectedCategory.toLowerCase();

        return matchesSearch && matchesKat;
      }).toList();
    });
  }

  void _showBookDetail(Map<String, dynamic> book) {
    final String judul = book['judul'] ?? 'Buku Digital';
    final String penulis = book['penulis'] ?? '-';
    final String kategori = book['kategori'] ?? 'Umum';
    final String deskripsi = book['deskripsi'] ?? 'Koleksi e-book / modul digital Perpustakaan SMK Muthia Harapan Cicalengka.';
    final String fileUrl = book['file_url'] ??
        (book['file_path'] != null ? "https://smkmuthiaharapancicalengka.my.id/${book['file_path']}" : "https://smkmuthiaharapancicalengka.my.id/assets/docs/panduan.pdf");
    final String fileType = (book['file_type'] ?? 'PDF').toString().toUpperCase();
    final double rating = double.tryParse((book['rating'] ?? 4.8).toString()) ?? 4.8;
    final int views = int.tryParse((book['views_count'] ?? 150).toString()) ?? 150;
    final int sizeMb = (int.tryParse((book['file_size'] ?? 2000000).toString()) ?? 2000000) ~/ 1024 ~/ 1024;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        final isDark = Theme.of(context).brightness == Brightness.dark;

        return Container(
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1E293B) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          ),
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 20,
            top: 16,
            left: 20,
            right: 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 5,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade400,
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Book Cover 3D Card
                  Container(
                    width: 80,
                    height: 110,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Colors.indigo.shade800, Colors.blue.shade900],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.indigo.withValues(alpha: 0.3),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.menu_book_rounded, color: Colors.white, size: 36),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.amber.shade700,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            fileType,
                            style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.indigo.shade50,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            kategori.toUpperCase(),
                            style: TextStyle(color: Colors.indigo.shade900, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          judul,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '✍️ Penulis: $penulis',
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Icon(Icons.star_rounded, size: 16, color: Colors.amber.shade700),
                            const SizedBox(width: 4),
                            Text('$rating', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            const SizedBox(width: 12),
                            Icon(Icons.visibility_rounded, size: 14, color: Colors.grey.shade600),
                            const SizedBox(width: 4),
                            Text('$views x dibaca', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              
              // Metadata Pill Row
              Row(
                children: [
                  Expanded(child: _buildMetaPill('Format', fileType, Icons.picture_as_pdf_rounded)),
                  const SizedBox(width: 8),
                  Expanded(child: _buildMetaPill('Ukuran', '${sizeMb > 0 ? sizeMb : 2} MB', Icons.folder_zip_rounded)),
                  const SizedBox(width: 8),
                  Expanded(child: _buildMetaPill('Akses', 'Gratis', Icons.verified_user_rounded)),
                ],
              ),
              const SizedBox(height: 16),

              const Text('Ringkasan / Deskripsi Buku:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF334155) : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  deskripsi,
                  style: TextStyle(fontSize: 12.5, height: 1.4, color: isDark ? Colors.white70 : Colors.grey.shade800),
                ),
              ),
              const SizedBox(height: 20),

              // Action Button
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    FileService.openFileOrUrl(context, fileUrl);
                  },
                  icon: const Icon(Icons.menu_book_rounded, color: Colors.white),
                  label: const Text('Buka / Unduh Berkas Buku PDF', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.indigo.shade900,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildMetaPill(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
      decoration: BoxDecoration(
        color: Colors.indigo.shade50.withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.indigo.shade100),
      ),
      child: Column(
        children: [
          Icon(icon, size: 16, color: Colors.indigo.shade900),
          const SizedBox(height: 2),
          Text(value, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11.5, color: Colors.indigo.shade900)),
          Text(label, style: TextStyle(fontSize: 9.5, color: Colors.grey.shade600)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final featuredBooks = _books.where((b) => (b['is_featured'] == 1 || b['is_featured'] == '1')).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Perpustakaan Digital', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: Colors.indigo.shade900,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: Icon(_isGridView ? Icons.view_list_rounded : Icons.grid_view_rounded),
            tooltip: _isGridView ? 'Tampilan List' : 'Tampilan Grid',
            onPressed: () => setState(() => _isGridView = !_isGridView),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.indigo))
          : RefreshIndicator(
              onRefresh: _fetchLibraryData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Hero Banner Header
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.indigo.shade900, Colors.blue.shade900],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: const Text('E-Book & Modul Digital Hub', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Koleksi Literasi SMK Muthia Harapan',
                            style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Akses ${_books.length} koleksi buku digital, modul praktikum, dan materi kejuruan kapan saja.',
                            style: const TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                          const SizedBox(height: 16),

                          // Search TextField
                          TextField(
                            controller: _searchController,
                            onChanged: (_) => _applyFilter(),
                            style: const TextStyle(color: Colors.white),
                            decoration: InputDecoration(
                              hintText: 'Cari judul buku, penulis, atau kata kunci...',
                              hintStyle: const TextStyle(color: Colors.white60, fontSize: 13),
                              prefixIcon: const Icon(Icons.search, color: Colors.white70),
                              suffixIcon: _searchController.text.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.clear, color: Colors.white70),
                                      onPressed: () {
                                        _searchController.clear();
                                        _applyFilter();
                                      },
                                    )
                                  : null,
                              filled: true,
                              fillColor: Colors.white.withValues(alpha: 0.15),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Category Chips Horizontal Scroll
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      child: SizedBox(
                        height: 38,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: _categories.length,
                          itemBuilder: (context, index) {
                            final cat = _categories[index];
                            final isSelected = _selectedCategory == cat;

                            return Padding(
                              padding: const EdgeInsets.only(right: 8),
                              child: FilterChip(
                                label: Text(cat),
                                selected: isSelected,
                                onSelected: (_) {
                                  setState(() => _selectedCategory = cat);
                                  _applyFilter();
                                },
                                labelStyle: TextStyle(
                                  color: isSelected ? Colors.white : (isDark ? Colors.white70 : Colors.indigo.shade900),
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                  fontSize: 12,
                                ),
                                selectedColor: Colors.indigo.shade900,
                                backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.indigo.shade50,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                side: BorderSide.none,
                                showCheckmark: false,
                              ),
                            );
                          },
                        ),
                      ),
                    ),

                    // Featured Carousel Section (if any featured books)
                    if (featuredBooks.isNotEmpty && _selectedCategory == 'Semua' && _searchController.text.isEmpty) ...[
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
                        child: Row(
                          children: [
                            Icon(Icons.local_fire_department_rounded, color: Colors.orange.shade800, size: 20),
                            const SizedBox(width: 6),
                            const Text(
                              'Rekomendasi Modul & E-Book',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      SizedBox(
                        height: 165,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: featuredBooks.length,
                          itemBuilder: (context, index) {
                            final b = featuredBooks[index];
                            return _buildFeaturedCard(b, isDark);
                          },
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Collection Header & Counter
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            '📖 Semua Koleksi E-Book (${_filteredBooks.length})',
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          Text(
                            _isGridView ? 'Mode Grid' : 'Mode List',
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Main Book Collection Display
                    _filteredBooks.isEmpty
                        ? Padding(
                            padding: const EdgeInsets.symmetric(vertical: 40),
                            child: Center(
                              child: Column(
                                children: [
                                  Icon(Icons.menu_book_outlined, size: 54, color: Colors.grey.shade400),
                                  const SizedBox(height: 10),
                                  const Text('Buku tidak ditemukan.', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                  const SizedBox(height: 4),
                                  Text('Coba gunakan kata kunci pencarian atau kategori lain.', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                                ],
                              ),
                            ),
                          )
                        : _isGridView
                            ? GridView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 2,
                                  childAspectRatio: 0.72,
                                  crossAxisSpacing: 12,
                                  mainAxisSpacing: 12,
                                ),
                                itemCount: _filteredBooks.length,
                                itemBuilder: (context, index) {
                                  return _buildGridBookCard(_filteredBooks[index], isDark);
                                },
                              )
                            : ListView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                itemCount: _filteredBooks.length,
                                itemBuilder: (context, index) {
                                  return _buildListBookCard(_filteredBooks[index], isDark);
                                },
                              ),
                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildFeaturedCard(dynamic b, bool isDark) {
    final judul = (b['judul'] ?? 'Judul Buku').toString();
    final penulis = (b['penulis'] ?? '-').toString();
    final kategori = (b['kategori'] ?? 'Umum').toString();
    final rating = (b['rating'] ?? 4.9).toString();

    return InkWell(
      onTap: () => _showBookDetail(b),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        width: 260,
        margin: const EdgeInsets.only(right: 12),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: isDark ? [const Color(0xFF1E293B), const Color(0xFF334155)] : [Colors.indigo.shade900, Colors.blue.shade900],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.indigo.withValues(alpha: 0.25),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            // Cover 3D
            Container(
              width: 70,
              height: 100,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.white30),
              ),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.menu_book_rounded, color: Colors.amber, size: 36),
                  SizedBox(height: 4),
                  Text('PDF', style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                ],
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      kategori.toUpperCase(),
                      style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    judul,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    penulis,
                    style: const TextStyle(color: Colors.white70, fontSize: 11),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.star_rounded, color: Colors.amber, size: 14),
                      const SizedBox(width: 4),
                      Text(rating, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.amber.shade700,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text('Baca', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGridBookCard(dynamic b, bool isDark) {
    final judul = (b['judul'] ?? 'Judul Buku').toString();
    final penulis = (b['penulis'] ?? '-').toString();
    final kategori = (b['kategori'] ?? 'Umum').toString();
    final fileType = (b['file_type'] ?? 'PDF').toString().toUpperCase();

    return InkWell(
      onTap: () => _showBookDetail(b),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E293B) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade300),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Cover Section
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.indigo.shade800, Colors.blue.shade800],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: Stack(
                  children: [
                    Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.menu_book_rounded, color: Colors.white, size: 42),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: Colors.amber.shade700,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(fileType, style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                    ),
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.4),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          kategori,
                          style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Content Section
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    judul,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    penulis,
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.star_rounded, size: 14, color: Colors.amber.shade700),
                          const SizedBox(width: 2),
                          const Text('4.8', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.indigo.shade50,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          'Detail',
                          style: TextStyle(color: Colors.indigo.shade900, fontSize: 10.5, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildListBookCard(dynamic b, bool isDark) {
    final judul = (b['judul'] ?? 'Judul Buku').toString();
    final penulis = (b['penulis'] ?? '-').toString();
    final kategori = (b['kategori'] ?? 'Umum').toString();
    final fileType = (b['file_type'] ?? 'PDF').toString().toUpperCase();

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      elevation: 2,
      child: ListTile(
        contentPadding: const EdgeInsets.all(12),
        onTap: () => _showBookDetail(b),
        leading: Container(
          width: 52,
          height: 68,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [Colors.indigo.shade900, Colors.blue.shade900],
            ),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.menu_book_rounded, color: Colors.white, size: 28),
              const SizedBox(height: 2),
              Text(fileType, style: const TextStyle(color: Colors.amber, fontSize: 9, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
        title: Text(
          judul,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.indigo.shade50,
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  kategori,
                  style: TextStyle(color: Colors.indigo.shade900, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  '✍️ $penulis',
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
        trailing: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: Colors.indigo.shade50,
            shape: BoxShape.circle,
          ),
          child: Icon(Icons.arrow_forward_ios_rounded, color: Colors.indigo.shade900, size: 14),
        ),
      ),
    );
  }
}
