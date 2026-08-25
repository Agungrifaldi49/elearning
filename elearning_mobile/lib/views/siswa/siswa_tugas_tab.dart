import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/tugas_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../services/file_service.dart';
import '../../theme/app_theme.dart';

class SiswaTugasTab extends StatefulWidget {
  const SiswaTugasTab({super.key});

  @override
  State<SiswaTugasTab> createState() => _SiswaTugasTabState();
}

class _SiswaTugasTabState extends State<SiswaTugasTab> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedStatusFilter = 'semua'; // 'semua', 'belum', 'dikumpul', 'dinilai'

  @override
  void initState() {
    super.initState();
    _loadTugas();
  }

  void _loadTugas() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchTugas(user.id);
    }
  }

  void _showTugasDetailAndSubmit(TugasModel t) {
    Provider.of<SiswaProvider>(context, listen: false).markTugasAsSeen(t.id);

    final catatanController = TextEditingController();
    final fileController = TextEditingController();

    const baseUrl = "https://smkmuthiaharapancicalengka.my.id/assets/uploads/tugas/";
    final fileUrl = (t.filePath != null && t.filePath!.startsWith('http'))
        ? t.filePath!
        : "$baseUrl${t.filePath ?? ''}";

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
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        t.judul,
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: t.isGraded
                            ? Colors.green.shade100
                            : (t.isSubmitted ? Colors.blue.shade100 : Colors.orange.shade100),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        t.isGraded
                            ? '⭐ Nilai: ${t.nilai}'
                            : (t.isSubmitted ? '✅ Sudah Dikumpul' : '⏳ Belum Dikumpul'),
                        style: TextStyle(
                          color: t.isGraded
                              ? Colors.green.shade900
                              : (t.isSubmitted ? Colors.blue.shade900 : Colors.orange.shade900),
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text("Mapel: ${t.namaMapel} • Guru: ${t.namaGuru ?? '-'}", style: TextStyle(color: Colors.grey.shade700, fontSize: 13, fontWeight: FontWeight.w500)),
                Row(
                  children: [
                    const Icon(Icons.access_time_rounded, size: 14, color: Colors.redAccent),
                    const SizedBox(width: 4),
                    Text("Batas Waktu: ${t.deadline}", style: const TextStyle(color: Colors.redAccent, fontSize: 12, fontWeight: FontWeight.bold)),
                  ],
                ),
                const SizedBox(height: 14),
                const Text('Instruksi Soal / Tugas:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const SizedBox(height: 6),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: SelectableText(
                    t.deskripsi.isEmpty ? 'Tidak ada instruksi khusus dari guru.' : t.deskripsi,
                    style: TextStyle(color: Colors.grey.shade900, height: 1.4, fontSize: 13),
                  ),
                ),
                const SizedBox(height: 12),
                if (t.filePath != null && t.filePath!.isNotEmpty) ...[
                  SizedBox(
                    width: double.infinity,
                    height: 46,
                    child: ElevatedButton.icon(
                      onPressed: () => FileService.openFileOrUrl(context, fileUrl),
                      icon: const Icon(Icons.picture_as_pdf_rounded, color: Colors.white),
                      label: const Text('Buka Berkas Lampiran Tugas (PDF)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.blue.shade700,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                ],

                if (t.isGraded && t.komentarGuru != null && t.komentarGuru!.isNotEmpty) ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.green.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.green.shade200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: const [
                            Icon(Icons.rate_review_rounded, size: 16, color: Colors.green),
                            SizedBox(width: 6),
                            Text('Catatan / Feedback Guru:', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.green, fontSize: 12)),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(t.komentarGuru!, style: const TextStyle(fontSize: 13, fontStyle: FontStyle.italic, color: Colors.black87)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                ],

                const Divider(),
                const SizedBox(height: 6),
                const Text('Form Pengumpulan Jawaban Siswa:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppTheme.secondaryColor)),
                const SizedBox(height: 10),
                TextField(
                  controller: catatanController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    labelText: 'Catatan / Jawaban Teks',
                    hintText: 'Tuliskan penjelasan jawaban Anda di sini...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: fileController,
                  decoration: InputDecoration(
                    labelText: 'Nama Berkas / Link Tugas Google Drive (Opsional)',
                    hintText: 'jawaban_tugas.pdf atau https://drive.google.com/...',
                    prefixIcon: const Icon(Icons.attach_file_rounded),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                      if (user != null) {
                        final ok = await Provider.of<SiswaProvider>(context, listen: false).submitTugas(
                          user.id,
                          t.id,
                          catatanController.text,
                          fileController.text,
                        );
                        if (!mounted) return;
                        Navigator.pop(context);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(ok ? 'Tugas berhasil dikumpulkan!' : 'Gagal mengirim tugas'),
                            backgroundColor: ok ? Colors.green : Colors.red,
                          ),
                        );
                      }
                    },
                    icon: const Icon(Icons.send_rounded),
                    label: Text(t.isSubmitted ? 'Simpan Perubahan Jawaban' : 'Kirim Jawaban Tugas', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.secondaryColor,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final tugasList = siswaProvider.tugasList;

    // Apply Filter & Search
    final filteredList = tugasList.where((t) {
      final matchesSearch = t.judul.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          t.namaMapel.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (t.namaGuru ?? '').toLowerCase().contains(_searchQuery.toLowerCase());

      if (_selectedStatusFilter == 'belum') {
        return matchesSearch && !t.isSubmitted;
      } else if (_selectedStatusFilter == 'dikumpul') {
        return matchesSearch && t.isSubmitted && !t.isGraded;
      } else if (_selectedStatusFilter == 'dinilai') {
        return matchesSearch && t.isGraded;
      }
      return matchesSearch;
    }).toList();

    return RefreshIndicator(
      onRefresh: () async {
        _loadTugas();
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
                  '📝 Tugas Siswa',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.indigo.shade50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${filteredList.length} Tugas',
                    style: TextStyle(color: Colors.indigo.shade900, fontWeight: FontWeight.bold, fontSize: 12),
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
                hintText: 'Cari tugas, mapel, atau nama guru...',
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
                  _buildFilterChip('Semua Tugas', 'semua'),
                  const SizedBox(width: 8),
                  _buildFilterChip('⏳ Belum Dikumpul', 'belum'),
                  const SizedBox(width: 8),
                  _buildFilterChip('✅ Sudah Dikumpul', 'dikumpul'),
                  const SizedBox(width: 8),
                  _buildFilterChip('⭐ Sudah Dinilai', 'dinilai'),
                ],
              ),
            ),
            const SizedBox(height: 14),

            Expanded(
              child: siswaProvider.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : filteredList.isEmpty
                      ? const Center(child: Text('Tidak ada tugas pada kategori / pencarian ini.'))
                      : ListView.builder(
                          itemCount: filteredList.length,
                          itemBuilder: (context, index) {
                            final t = filteredList[index];

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
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: Colors.indigo.shade50,
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            t.namaMapel,
                                            style: TextStyle(
                                              color: Colors.indigo.shade900,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 11,
                                            ),
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: t.isGraded
                                                ? Colors.green.shade100
                                                : (t.isSubmitted ? Colors.blue.shade100 : Colors.orange.shade100),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            t.isGraded
                                                ? '⭐ Nilai: ${t.nilai}'
                                                : (t.isSubmitted ? '✅ Dikumpul' : '⏳ Belum'),
                                            style: TextStyle(
                                              color: t.isGraded
                                                  ? Colors.green.shade900
                                                  : (t.isSubmitted ? Colors.blue.shade900 : Colors.orange.shade900),
                                              fontWeight: FontWeight.bold,
                                              fontSize: 11,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 10),
                                    Text(
                                      t.judul,
                                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      t.deskripsi,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(fontSize: 13, color: Colors.grey.shade800, height: 1.3),
                                    ),
                                    const SizedBox(height: 10),
                                    Row(
                                      children: [
                                        const Icon(Icons.access_time_rounded, size: 14, color: Colors.redAccent),
                                        const SizedBox(width: 4),
                                        Text(
                                          "Batas: ${t.deadline}",
                                          style: const TextStyle(fontSize: 12, color: Colors.redAccent, fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                    if (t.komentarGuru != null && t.komentarGuru!.isNotEmpty) ...[
                                      const SizedBox(height: 8),
                                      Container(
                                        padding: const EdgeInsets.all(10),
                                        decoration: BoxDecoration(
                                          color: Colors.green.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          "Catatan Guru: ${t.komentarGuru}",
                                          style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic, color: Colors.green),
                                        ),
                                      ),
                                    ],
                                    const Divider(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Row(
                                          children: [
                                            const Icon(Icons.person, size: 14, color: Colors.grey),
                                            const SizedBox(width: 4),
                                            Text(
                                              t.namaGuru ?? '-',
                                              style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.bold),
                                            ),
                                          ],
                                        ),
                                        ElevatedButton.icon(
                                          onPressed: () => _showTugasDetailAndSubmit(t),
                                          icon: Icon(t.isSubmitted ? Icons.edit_note_rounded : Icons.upload_file_rounded, size: 16),
                                          label: Text(t.isSubmitted ? 'Edit Jawaban' : 'Kumpul Tugas', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: t.isSubmitted ? Colors.blue.shade800 : AppTheme.secondaryColor,
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
    final bool isSelected = _selectedStatusFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppTheme.secondaryColor,
      backgroundColor: Colors.grey.shade100,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : Colors.black87,
        fontWeight: FontWeight.bold,
        fontSize: 12,
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedStatusFilter = value;
          });
        }
      },
    );
  }
}
