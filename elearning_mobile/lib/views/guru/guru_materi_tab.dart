import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/materi_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruMateriTab extends StatefulWidget {
  const GuruMateriTab({super.key});

  @override
  State<GuruMateriTab> createState() => _GuruMateriTabState();
}

class _GuruMateriTabState extends State<GuruMateriTab> {
  @override
  void initState() {
    super.initState();
    _loadMateri();
  }

  void _loadMateri() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<GuruProvider>(context, listen: false).fetchMateri(user.id);
    }
  }

  void _showMateriDetailModal(MateriModel m) {
    final baseUrl = "https://smkmuthiaharapancicalengka.my.id/assets/uploads/materi/";
    final fileUrl = (m.filePath != null && m.filePath!.startsWith('http'))
        ? m.filePath!
        : "$baseUrl${m.filePath ?? ''}";

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
                      color: Colors.blue.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      m.jenisFile == 'video' || m.youtubeUrl != null ? Icons.play_circle_fill_rounded : Icons.picture_as_pdf_rounded,
                      color: Colors.blue,
                      size: 30,
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
              Text("Kelas Target: ${m.namaKelas ?? '-'}", style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
              const SizedBox(height: 12),
              const Text('Keterangan & Ringkasan:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: SelectableText(
                  m.deskripsi.isEmpty ? 'Tidak ada keterangan.' : m.deskripsi,
                  style: TextStyle(color: Colors.grey.shade900, height: 1.4, fontSize: 13),
                ),
              ),
              const SizedBox(height: 20),
              if (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty) ...[
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: ElevatedButton.icon(
                    onPressed: () => _openLinkDialog('Video Learning YouTube', m.youtubeUrl!),
                    icon: const Icon(Icons.play_arrow, color: Colors.white),
                    label: const Text('Tonton Video Learning (YouTube)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red.shade700,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
              ],
              if (m.filePath != null && m.filePath!.isNotEmpty) ...[
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: ElevatedButton.icon(
                    onPressed: () => _openLinkDialog('Dokumen File PDF', fileUrl),
                    icon: const Icon(Icons.picture_as_pdf, color: Colors.white),
                    label: const Text('Buka / Unduh Berkas Materi PDF', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue.shade700,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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

  void _openLinkDialog(String title, String url) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tautan langsung berkas / video materi:'),
            const SizedBox(height: 8),
            SelectableText(
              url,
              style: const TextStyle(color: Colors.blue, decoration: TextDecoration.underline, fontWeight: FontWeight.bold, fontSize: 13),
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

  void _showAddMateriModal() {
    final judulController = TextEditingController();
    final deskripsiController = TextEditingController();
    final youtubeController = TextEditingController();
    int selectedMapel = 1;
    int selectedKelas = 1;
    String jenisFile = 'pdf';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
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
            const Text(
              'Upload Materi Pembelajaran',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: judulController,
              decoration: const InputDecoration(labelText: 'Judul Materi'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: deskripsiController,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Deskripsi / Ringkasan'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: youtubeController,
              decoration: const InputDecoration(
                labelText: 'Link YouTube / File Path',
                hintText: 'https://youtube.com/...',
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () async {
                final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                if (user != null) {
                  final ok = await Provider.of<GuruProvider>(context, listen: false).createMateri(
                    user.id,
                    judulController.text,
                    deskripsiController.text,
                    selectedMapel,
                    selectedKelas,
                    jenisFile,
                    youtubeController.text,
                  );
                  if (!mounted) return;
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(ok ? 'Materi berhasil diterbitkan!' : 'Gagal upload materi'),
                      backgroundColor: ok ? AppTheme.secondaryColor : Colors.red,
                    ),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                backgroundColor: AppTheme.primaryColor,
              ),
              child: const Text('Terbitkan Materi'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final materiList = guruProvider.materiList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '📚 Kelola Materi',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              ElevatedButton.icon(
                onPressed: _showAddMateriModal,
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Tambah Materi'),
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
              ),
            ],
          ),
          const SizedBox(height: 12),

          Expanded(
            child: guruProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : materiList.isEmpty
                    ? const Center(child: Text('Belum ada materi dipublikasikan.'))
                    : ListView.builder(
                        itemCount: materiList.length,
                        itemBuilder: (context, index) {
                          final m = materiList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              onTap: () => _showMateriDetailModal(m),
                              leading: Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: Colors.blue.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.book, color: Colors.blue),
                              ),
                              title: Text(m.judul, style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text("Mapel: ${m.namaMapel} • Kelas: ${m.namaKelas ?? '-'}"),
                              trailing: IconButton(
                                icon: const Icon(Icons.info_outline, color: Colors.blue),
                                onPressed: () => _showMateriDetailModal(m),
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
