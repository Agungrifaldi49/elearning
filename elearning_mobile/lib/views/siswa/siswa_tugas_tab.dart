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
    final catatanController = TextEditingController();
    final fileController = TextEditingController();

    final baseUrl = "https://smkmuthiaharapancicalengka.my.id/assets/uploads/tugas/";
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
                      color: t.isSubmitted
                          ? (t.isGraded ? Colors.green.withValues(alpha: 0.15) : Colors.blue.withValues(alpha: 0.15))
                          : Colors.orange.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      t.isGraded
                          ? 'Nilai: ${t.nilai}'
                          : (t.isSubmitted ? 'Sudah Dikumpul' : 'Belum Dikumpul'),
                      style: TextStyle(
                        color: t.isSubmitted
                            ? (t.isGraded ? Colors.green : Colors.blue)
                            : Colors.orange,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text("Mapel: ${t.namaMapel} • Guru: ${t.namaGuru ?? '-'}", style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
              Text("Deadline: ${t.deadline}", style: const TextStyle(color: Colors.redAccent, fontSize: 12, fontWeight: FontWeight.bold)),
              const SizedBox(height: 14),
              const Text('Instruksi Soal / Tugas:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: SelectableText(
                  t.deskripsi.isEmpty ? 'Tidak ada instruksi khusus.' : t.deskripsi,
                  style: TextStyle(color: Colors.grey.shade900, height: 1.4, fontSize: 13),
                ),
              ),
              const SizedBox(height: 12),
              if (t.filePath != null && t.filePath!.isNotEmpty) ...[
                SizedBox(
                  width: double.infinity,
                  height: 44,
                  child: ElevatedButton.icon(
                    onPressed: () => FileService.openFileOrUrl(context, fileUrl),
                    icon: const Icon(Icons.picture_as_pdf, color: Colors.white),
                    label: const Text('Buka Berkas Lampiran Tugas (PDF)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue.shade700,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
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
                decoration: const InputDecoration(
                  labelText: 'Catatan / Jawaban Teks',
                  hintText: 'Tuliskan rangkuman atau penjelasan jawaban...',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: fileController,
                decoration: const InputDecoration(
                  labelText: 'Nama File / Link Tugas (Opsional)',
                  hintText: 'jawaban_tugas.pdf',
                  prefixIcon: Icon(Icons.attach_file),
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                height: 46,
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
                  icon: const Icon(Icons.send),
                  label: Text(t.isSubmitted ? 'Simpan Perubahan Jawaban' : 'Kirim Jawaban Tugas', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.secondaryColor,
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

  void _openLinkDialog(String title, String url) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tautan langsung berkas lampiran tugas:'),
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
    final tugasList = siswaProvider.tugasList;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '📝 Tugas Siswa',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          Expanded(
            child: siswaProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : tugasList.isEmpty
                    ? const Center(child: Text('Tidak ada tugas untuk saat ini.'))
                    : ListView.builder(
                        itemCount: tugasList.length,
                        itemBuilder: (context, index) {
                          final t = tugasList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        t.namaMapel,
                                        style: const TextStyle(
                                          color: AppTheme.secondaryColor,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: t.isSubmitted
                                              ? (t.isGraded ? Colors.green.withValues(alpha: 0.15) : Colors.blue.withValues(alpha: 0.15))
                                              : Colors.orange.withValues(alpha: 0.15),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          t.isGraded
                                              ? 'Nilai: ${t.nilai}'
                                              : (t.isSubmitted ? 'Sudah Dikumpul' : 'Belum Dikumpul'),
                                          style: TextStyle(
                                            color: t.isSubmitted
                                                ? (t.isGraded ? Colors.green : Colors.blue)
                                                : Colors.orange,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    t.judul,
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(t.deskripsi, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 14)),
                                  const SizedBox(height: 8),
                                  Text(
                                    "Deadline: ${t.deadline}",
                                    style: const TextStyle(fontSize: 12, color: Colors.redAccent, fontWeight: FontWeight.w500),
                                  ),
                                  if (t.komentarGuru != null && t.komentarGuru!.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    Container(
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: Colors.grey.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        "Feedback Guru: ${t.komentarGuru}",
                                        style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic),
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: 12),
                                  Align(
                                    alignment: Alignment.centerRight,
                                    child: ElevatedButton.icon(
                                      onPressed: () => _showTugasDetailAndSubmit(t),
                                      icon: Icon(t.isSubmitted ? Icons.edit : Icons.upload_file, size: 16),
                                      label: Text(t.isSubmitted ? 'Buka Detail / Edit' : 'Buka Detail / Kumpul'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: t.isSubmitted ? Colors.blue : AppTheme.secondaryColor,
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
