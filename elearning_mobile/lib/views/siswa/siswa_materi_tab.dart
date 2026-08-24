import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/materi_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaMateriTab extends StatefulWidget {
  const SiswaMateriTab({super.key});

  @override
  State<SiswaMateriTab> createState() => _SiswaMateriTabState();
}

class _SiswaMateriTabState extends State<SiswaMateriTab> {
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
              Row(
                children: [
                  Icon(Icons.person_outline, size: 16, color: Colors.grey.shade600),
                  const SizedBox(width: 4),
                  Text("Pengampu: ${m.namaGuru ?? '-'}", style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
                  const Spacer(),
                  Icon(Icons.calendar_today, size: 14, color: Colors.grey.shade600),
                  const SizedBox(width: 4),
                  Text(m.createdAt, style: TextStyle(color: Colors.grey.shade700, fontSize: 12)),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Keterangan & Rangkuman Materi:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(10),
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
                    onPressed: () => _openLinkDialog('Dokumen File Materi PDF', fileUrl),
                    icon: const Icon(Icons.picture_as_pdf, color: Colors.white),
                    label: const Text('Buka / Unduh Berkas Dokumentasi Materi', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
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

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final materiList = siswaProvider.materiList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '📚 Materi Pembelajaran',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          Expanded(
            child: siswaProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : materiList.isEmpty
                    ? const Center(child: Text('Belum ada materi pembelajaran.'))
                    : ListView.builder(
                        itemCount: materiList.length,
                        itemBuilder: (context, index) {
                          final m = materiList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(10),
                                        decoration: BoxDecoration(
                                          color: Colors.blue.withValues(alpha: 0.1),
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: Icon(
                                          m.jenisFile == 'video' || m.youtubeUrl != null
                                              ? Icons.play_circle_fill_rounded
                                              : Icons.picture_as_pdf_rounded,
                                          color: Colors.blue,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              m.namaMapel,
                                              style: const TextStyle(
                                                fontSize: 12,
                                                color: AppTheme.secondaryColor,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
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
                                  const SizedBox(height: 12),
                                  Text(
                                    m.deskripsi,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 14),
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        "Oleh: ${m.namaGuru ?? '-'}",
                                        style: const TextStyle(fontSize: 12, color: Colors.grey),
                                      ),
                                      ElevatedButton.icon(
                                        onPressed: () => _showMateriDetailModal(m),
                                        icon: const Icon(Icons.menu_book, size: 16),
                                        label: const Text('Buka Detail Materi', style: TextStyle(fontSize: 12)),
                                        style: ElevatedButton.styleFrom(
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
    );
  }
}
