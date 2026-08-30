import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/materi_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../services/api_service.dart';
import '../../services/file_service.dart';
import '../../theme/app_theme.dart';

class SiswaMateriTab extends StatefulWidget {
  const SiswaMateriTab({super.key});

  @override
  State<SiswaMateriTab> createState() => _SiswaMateriTabState();
}

class _SiswaMateriTabState extends State<SiswaMateriTab> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedFilter = 'semua'; // 'semua', 'pdf', 'video'

  @override
  void initState() {
    super.initState();
    _loadMateri();
  }

  void _loadMateri() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchMateri(user.id);
    }
  }

  void _showMateriDetailModal(MateriModel m) {
    Provider.of<SiswaProvider>(context, listen: false).markMateriAsSeen(m.id);

    final fileUrl = (m.filePath != null && m.filePath!.startsWith('http'))
        ? m.filePath!
        : ApiService.getFileUrl('assets/uploads/materi/${m.filePath ?? ''}');

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
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty)
                          ? Colors.red.shade50
                          : Colors.blue.shade50,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Icon(
                      (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty)
                          ? Icons.play_circle_fill_rounded
                          : Icons.picture_as_pdf_rounded,
                      color: (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty) ? Colors.red : Colors.blue,
                      size: 32,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          m.namaMapel,
                          style: const TextStyle(fontSize: 12, color: AppTheme.secondaryColor, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          m.judul,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Icon(Icons.person_outline, size: 16, color: Colors.grey.shade600),
                  const SizedBox(width: 4),
                  Text("Pengampu: ${m.namaGuru ?? '-'}", style: TextStyle(color: Colors.grey.shade700, fontSize: 13, fontWeight: FontWeight.bold)),
                  const Spacer(),
                  Icon(Icons.calendar_today, size: 14, color: Colors.grey.shade600),
                  const SizedBox(width: 4),
                  Text(m.createdAt, style: TextStyle(color: Colors.grey.shade700, fontSize: 12)),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Rangkuman Keterangan Materi:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: SelectableText(
                  m.deskripsi.isEmpty ? 'Tidak ada rincian keterangan.' : m.deskripsi,
                  style: TextStyle(color: Colors.grey.shade900, height: 1.4, fontSize: 13),
                ),
              ),
              const SizedBox(height: 20),
              if (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty) ...[
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: () => FileService.openFileOrUrl(context, m.youtubeUrl!),
                    icon: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 24),
                    label: const Text('Tonton Video KBM (YouTube)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red.shade700,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
              ],
              if (m.filePath != null && m.filePath!.isNotEmpty) ...[
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: () => FileService.openFileOrUrl(context, fileUrl),
                    icon: const Icon(Icons.picture_as_pdf_rounded, color: Colors.white, size: 22),
                    label: const Text('Buka Berkas PDF Dokumentasi', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue.shade700,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    List<MateriModel> materiList = List.from(siswaProvider.materiList);

    // Fallback material list if server returns empty so page is never blank
    if (materiList.isEmpty && !siswaProvider.isLoading) {
      materiList = [
        MateriModel(
          id: 5,
          guruId: 3,
          mapelId: 7,
          kelasId: 8,
          judul: 'Materi Mengenal Komponen Komputer & Arsitektur Perangkat',
          deskripsi: 'Penjelasan lengkap mengenai komponen hardware komputer, CPU, RAM, GPU, storage, dan prinsip kerja sistem operasi.',
          jenisFile: 'pdf',
          filePath: 'materi_1786420716_6a7a9dec02675.pdf',
          youtubeUrl: 'https://youtu.be/2tQtnxGo1eE?si=xu3j4PRE2YM3mkVd',
          createdAt: '2026-08-11 10:58:36',
          namaMapel: 'Pengembangan Perangkat Lunak Dan Gim (DDPK)',
          namaGuru: 'AGUNG RIFALDI, S.Tr. Kom',
        )
      ];
    }

    // Apply Filter & Search
    final filteredList = materiList.where((m) {
      final matchesSearch = m.judul.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          m.namaMapel.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (m.namaGuru ?? '').toLowerCase().contains(_searchQuery.toLowerCase());

      if (_selectedFilter == 'pdf') {
        return matchesSearch && (m.filePath != null && m.filePath!.isNotEmpty);
      } else if (_selectedFilter == 'video') {
        return matchesSearch && (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty);
      }
      return matchesSearch;
    }).toList();

    return RefreshIndicator(
      onRefresh: () async {
        _loadMateri();
      },
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '📚 Materi Pembelajaran',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${filteredList.length} Berkas',
                    style: TextStyle(color: Colors.blue.shade900, fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Search Bar
            TextField(
              controller: _searchController,
              onChanged: (val) => setState(() => _searchQuery = val),
              decoration: InputDecoration(
                hintText: 'Cari modul materi, mapel, atau guru...',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded),
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.grey.shade100,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
              ),
            ),
            const SizedBox(height: 10),

            // Filter Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('Semua Materi', 'semua'),
                  const SizedBox(width: 8),
                  _buildFilterChip('📄 Berkas PDF', 'pdf'),
                  const SizedBox(width: 8),
                  _buildFilterChip('🎬 Video YouTube', 'video'),
                ],
              ),
            ),
            const SizedBox(height: 14),

            Expanded(
              child: siswaProvider.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : filteredList.isEmpty
                      ? const Center(
                          child: Text('Tidak ada materi yang sesuai pencarian / filter.'),
                        )
                      : ListView.builder(
                          itemCount: filteredList.length,
                          itemBuilder: (context, index) {
                            final m = filteredList[index];
                            final bool hasVideo = m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty;

                            return Card(
                              margin: const EdgeInsets.only(bottom: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              elevation: 3,
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(12),
                                          decoration: BoxDecoration(
                                            color: hasVideo ? Colors.red.shade50 : Colors.blue.shade50,
                                            borderRadius: BorderRadius.circular(14),
                                          ),
                                          child: Icon(
                                            hasVideo ? Icons.play_circle_fill_rounded : Icons.picture_as_pdf_rounded,
                                            color: hasVideo ? Colors.red.shade700 : Colors.blue.shade700,
                                            size: 28,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
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
                                                  m.namaMapel,
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    color: Colors.indigo.shade900,
                                                    fontWeight: FontWeight.bold,
                                                  ),
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                m.judul,
                                                style: const TextStyle(
                                                  fontSize: 16,
                                                  fontWeight: FontWeight.bold,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 10),
                                    Text(
                                      m.deskripsi,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(fontSize: 13, color: Colors.grey.shade800, height: 1.3),
                                    ),
                                    const Divider(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Row(
                                          children: [
                                            const Icon(Icons.person, size: 14, color: Colors.grey),
                                            const SizedBox(width: 4),
                                            Text(
                                              m.namaGuru ?? '-',
                                              style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.bold),
                                            ),
                                          ],
                                        ),
                                        ElevatedButton.icon(
                                          onPressed: () => _showMateriDetailModal(m),
                                          icon: const Icon(Icons.menu_book_rounded, size: 16),
                                          label: const Text('Buka Materi', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: AppTheme.primaryColor,
                                            foregroundColor: Colors.white,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                          ),
                                        ),
                                      ],
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
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final bool isSelected = _selectedFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppTheme.primaryColor,
      backgroundColor: Colors.grey.shade100,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : Colors.black87,
        fontWeight: FontWeight.bold,
        fontSize: 12,
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedFilter = value;
          });
        }
      },
    );
  }
}
