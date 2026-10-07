import 'dart:io';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/tugas_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../services/api_service.dart';
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
  String _selectedStatusFilter = 'semua'; // 'semua', 'belum', 'dikumpul', 'dinilai', 'expired'

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

  void _showRequestPermissionDialog(TugasModel t) {
    final catatanController = TextEditingController();
    showDialog(
      context: context,
      builder: (dialogContext) {
        bool isSubmitting = false;
        return StatefulBuilder(
          builder: (context, setModalState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: const Row(
                children: [
                  Icon(Icons.mark_email_unread_rounded, color: Colors.redAccent),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Permohonan Izin Susulan Tugas',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.red.shade200),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(t.judul, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.black87)),
                          const SizedBox(height: 2),
                          Text("Mapel: ${t.namaMapel}", style: TextStyle(fontSize: 11, color: Colors.red.shade900, fontWeight: FontWeight.w600)),
                          Text("Batas Waktu: ${t.deadline}", style: TextStyle(fontSize: 11, color: Colors.red.shade700)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Batas waktu pengumpulan tugas ini telah terlewati. Pilih alasan cepat atau ketik penjelasan ke Guru Pengampu:',
                      style: TextStyle(fontSize: 12, color: Colors.black87),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        'Sakit / Izin Medis',
                        'Kendala Jaringan / Lampu Padam',
                        'Kendala HP / Laptop',
                        'Urusan Keluarga',
                      ].map((reason) {
                        return InkWell(
                          onTap: () {
                            catatanController.text = reason;
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.red.shade50,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: Colors.red.shade200),
                            ),
                            child: Text(
                              reason,
                              style: TextStyle(fontSize: 11, color: Colors.red.shade900, fontWeight: FontWeight.w600),
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: catatanController,
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'Contoh: Mohon maaf Pak/Bu, saya kemarin sakit dan izin susulan pengumpulan...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSubmitting ? null : () => Navigator.pop(dialogContext),
                  child: const Text('Batal'),
                ),
                ElevatedButton.icon(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                          if (user == null) return;
                          setModalState(() => isSubmitting = true);
                          final res = await Provider.of<SiswaProvider>(context, listen: false).requestTugasSusulan(
                            user.id,
                            t.id,
                            catatanController.text.trim().isEmpty ? 'Permohonan izin susulan pengumpulan tugas' : catatanController.text.trim(),
                          );
                          if (dialogContext.mounted) {
                            Navigator.pop(dialogContext);
                          }
                          if (context.mounted) {
                            final success = res['success'] == true;
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(res['message'] ?? (success ? 'Permohonan izin berhasil dikirimkan!' : 'Gagal mengirimkan permohonan izin')),
                                backgroundColor: success ? Colors.green : Colors.red,
                              ),
                            );
                          }
                        },
                  icon: isSubmitting
                      ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.send_rounded, size: 16),
                  label: const Text('Kirim Permohonan'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.redAccent,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showTugasDetailAndSubmit(TugasModel t) {
    Provider.of<SiswaProvider>(context, listen: false).markTugasAsSeen(t.id);

    final catatanController = TextEditingController(text: t.catatanSiswa ?? '');
    final bool isExistingDrive = t.filePathSiswa != null &&
        (t.filePathSiswa!.startsWith('http://') || t.filePathSiswa!.startsWith('https://'));
    final driveController = TextEditingController(text: isExistingDrive ? t.filePathSiswa! : '');
    File? selectedLocalFile;
    String submissionMode = isExistingDrive ? 'link' : 'file';
    bool isSubmitting = false;

    final fileUrl = t.effectiveFileUrl.isNotEmpty
        ? t.effectiveFileUrl
        : ApiService.getTugasUrl(t.filePath);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
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
                            : (t.isSubmitted
                                ? Colors.blue.shade100
                                : (t.isSusulanDisetujui
                                    ? Colors.teal.shade100
                                    : (t.isSusulanPending
                                        ? Colors.amber.shade100
                                        : (t.isLocked ? Colors.red.shade100 : Colors.orange.shade100)))),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        t.isGraded
                            ? '⭐ Nilai: ${t.nilai}'
                            : (t.isSubmitted
                                ? '✅ Sudah Dikumpul'
                                : (t.isSusulanDisetujui
                                    ? '✅ Izin Disetujui'
                                    : (t.isSusulanPending
                                        ? '⏳ Wait Konfirmasi'
                                        : (t.isLocked ? '🔒 Expired' : '⏳ Belum Dikumpul')))),
                        style: TextStyle(
                          color: t.isGraded
                              ? Colors.green.shade900
                              : (t.isSubmitted
                                  ? Colors.blue.shade900
                                  : (t.isSusulanDisetujui
                                      ? Colors.teal.shade900
                                      : (t.isSusulanPending
                                          ? Colors.amber.shade900
                                          : (t.isLocked ? Colors.red.shade900 : Colors.orange.shade900)))),
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
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () => FileService.openFileOrUrl(context, fileUrl),
                      icon: const Icon(Icons.picture_as_pdf_rounded, color: Colors.white, size: 22),
                      label: const Text('Buka Berkas Lampiran Tugas (PDF)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.blue.shade700,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    height: 44,
                    child: OutlinedButton.icon(
                      onPressed: () => FileService.openFileOrUrl(context, fileUrl, preferInApp: false),
                      icon: const Icon(Icons.download_rounded, size: 20),
                      label: const Text('Unduh Berkas Lampiran ke HP', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.blue.shade800,
                        side: BorderSide(color: Colors.blue.shade300),
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
                        const Row(
                          children: [
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

                // Expiration & Permission Warning Box if Locked
                if (t.isLocked && !t.isSubmitted) ...[
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.red.shade200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.lock_clock_rounded, color: Colors.red.shade800, size: 20),
                            const SizedBox(width: 8),
                            Text(
                              'Akses Pengumpulan Terkunci!',
                              style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red.shade900, fontSize: 13),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Waktu pengumpulan tugas ini telah melewati batas deadline (${t.deadline}). Anda harus meminta izin kepada Guru Pengampu untuk membuka akses tugas ini kembali.',
                          style: TextStyle(fontSize: 12, color: Colors.red.shade900, height: 1.3),
                        ),
                        const SizedBox(height: 12),
                        if (t.isSusulanPending) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            decoration: BoxDecoration(color: Colors.amber.shade100, borderRadius: BorderRadius.circular(8)),
                            child: Row(
                              children: [
                                Icon(Icons.hourglass_top_rounded, size: 16, color: Colors.amber.shade900),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    'Permohonan izin Anda sedang menunggu konfirmasi Guru Pengampu.',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ] else if (t.isSusulanDitolak) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            decoration: BoxDecoration(color: Colors.red.shade100, borderRadius: BorderRadius.circular(8)),
                            child: Row(
                              children: [
                                Icon(Icons.cancel_rounded, size: 16, color: Colors.red.shade900),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    'Permohonan izin Anda ditolak oleh Guru Pengampu.',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.red.shade900),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 10),
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton.icon(
                              onPressed: () {
                                Navigator.pop(context);
                                _showRequestPermissionDialog(t);
                              },
                              icon: const Icon(Icons.mark_email_unread_rounded, size: 16),
                              label: const Text('Minta Izin Lagi'),
                              style: OutlinedButton.styleFrom(foregroundColor: Colors.red.shade800),
                            ),
                          ),
                        ] else ...[
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: () {
                                Navigator.pop(context);
                                _showRequestPermissionDialog(t);
                              },
                              icon: const Icon(Icons.mark_email_unread_rounded, size: 16),
                              label: const Text('✉️ Minta Izin Buka Tugas (Permohonan Susulan)'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.redAccent,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                ] else ...[
                  if (t.isSusulanDisetujui) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.teal.shade50,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.teal.shade300),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.check_circle_rounded, color: Colors.teal.shade800, size: 20),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Permohonan Izin Susulan Disetujui! Akses pengumpulan tugas telah dibuka kembali oleh Guru Pengampu.',
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.teal.shade900),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                  const Text('Form Pengumpulan Jawaban Siswa:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppTheme.secondaryColor)),
                  const SizedBox(height: 10),
                  TextField(
                    controller: catatanController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      labelText: 'Catatan / Jawaban Teks',
                      hintText: 'Tuliskan penjelasan jawaban atau ringkasan tugas di sini...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 14),
                  const Text('Pilih Metode Berkas Jawaban:', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Colors.black87)),
                  const SizedBox(height: 8),

                  // Segmented switch for File Upload vs Google Drive Link
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => submissionMode = 'file'),
                            borderRadius: BorderRadius.circular(12),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              decoration: BoxDecoration(
                                color: submissionMode == 'file' ? AppTheme.secondaryColor : Colors.transparent,
                                borderRadius: BorderRadius.circular(11),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.upload_file_rounded, size: 18, color: submissionMode == 'file' ? Colors.white : Colors.grey.shade700),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Upload File Berkas',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.bold,
                                      color: submissionMode == 'file' ? Colors.white : Colors.grey.shade700,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => submissionMode = 'link'),
                            borderRadius: BorderRadius.circular(12),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              decoration: BoxDecoration(
                                color: submissionMode == 'link' ? AppTheme.secondaryColor : Colors.transparent,
                                borderRadius: BorderRadius.circular(11),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.link_rounded, size: 18, color: submissionMode == 'link' ? Colors.white : Colors.grey.shade700),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Link Google Drive',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.bold,
                                      color: submissionMode == 'link' ? Colors.white : Colors.grey.shade700,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  if (submissionMode == 'file') ...[
                    if (selectedLocalFile != null) ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.green.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.green.shade300),
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: Colors.green.shade100,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(Icons.insert_drive_file_rounded, color: Colors.green.shade800, size: 22),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    selectedLocalFile!.path.split(Platform.pathSeparator).last,
                                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Colors.black87),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${(selectedLocalFile!.lengthSync() / 1024).toStringAsFixed(1)} KB • Siap diunggah',
                                    style: TextStyle(fontSize: 11, color: Colors.green.shade800),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.change_circle_rounded, color: Colors.blue),
                              tooltip: 'Ganti File',
                              onPressed: () async {
                                final result = await FilePicker.pickFiles(
                                  type: FileType.custom,
                                  allowedExtensions: ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png'],
                                );
                                if (result.isNotEmpty && result.first.path != null) {
                                  setModalState(() {
                                    selectedLocalFile = File(result.first.path!);
                                  });
                                }
                              },
                            ),
                            IconButton(
                              icon: const Icon(Icons.close_rounded, color: Colors.red),
                              tooltip: 'Batal',
                              onPressed: () => setModalState(() => selectedLocalFile = null),
                            ),
                          ],
                        ),
                      ),
                    ] else ...[
                      InkWell(
                        onTap: () async {
                          final result = await FilePicker.pickFiles(
                            type: FileType.custom,
                            allowedExtensions: ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png'],
                          );
                          if (result.isNotEmpty && result.first.path != null) {
                            setModalState(() {
                              selectedLocalFile = File(result.first.path!);
                            });
                          }
                        },
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
                          decoration: BoxDecoration(
                            color: Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.blue.shade300, width: 1.5),
                          ),
                          child: Column(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: Colors.blue.shade100,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(Icons.cloud_upload_rounded, color: Colors.blue.shade800, size: 28),
                              ),
                              const SizedBox(height: 8),
                              const Text(
                                'Ketuk untuk Memilih Berkas Tugas dari HP',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.blue),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Format: PDF, Word (DOC/DOCX), Excel, PPT, ZIP, JPG, PNG (Maks 25 MB)',
                                textAlign: TextAlign.center,
                                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ] else ...[
                    TextField(
                      controller: driveController,
                      decoration: InputDecoration(
                        labelText: 'Link Berkas / Google Drive',
                        hintText: 'https://drive.google.com/file/d/.../view?usp=sharing',
                        prefixIcon: const Icon(Icons.link_rounded),
                        helperText: 'Pastikan setelan Google Drive: "Siapa saja yang memiliki link dapat melihat".',
                        helperMaxLines: 2,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                  const SizedBox(height: 12),

                  if (t.isSubmitted && t.filePathSiswa != null && t.filePathSiswa!.isNotEmpty) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.blue.shade200),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: Colors.blue.shade100,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              (t.filePathSiswa!.startsWith('http://') || t.filePathSiswa!.startsWith('https://'))
                                  ? Icons.link_rounded
                                  : Icons.file_present_rounded,
                              color: Colors.blue.shade800,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  (t.filePathSiswa!.startsWith('http://') || t.filePathSiswa!.startsWith('https://'))
                                      ? 'Link Google Drive Terkirim:'
                                      : 'Berkas Jawaban Terkirim:',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blue.shade900),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  t.filePathSiswa!,
                                  style: const TextStyle(fontSize: 12),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                          TextButton.icon(
                            onPressed: () => FileService.openFileOrUrl(context, t.effectiveFileUrlSiswa),
                            icon: const Icon(Icons.open_in_new_rounded, size: 16),
                            label: Text(
                              (t.filePathSiswa!.startsWith('http://') || t.filePathSiswa!.startsWith('https://'))
                                  ? 'Buka Link'
                                  : 'Buka File',
                              style: const TextStyle(fontSize: 12),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                  const SizedBox(height: 6),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                              if (user == null) return;

                              final notes = catatanController.text.trim();
                              final driveLink = driveController.text.trim();

                              if (submissionMode == 'file') {
                                if (selectedLocalFile == null && (!t.isSubmitted || (t.filePathSiswa == null || t.filePathSiswa!.isEmpty)) && notes.isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(
                                      content: Text('Silakan pilih berkas dari HP atau tuliskan catatan jawaban tugas.'),
                                      backgroundColor: Colors.orange,
                                    ),
                                  );
                                  return;
                                }
                              } else {
                                if (driveLink.isEmpty && notes.isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(
                                      content: Text('Silakan masukkan link Google Drive atau tuliskan catatan jawaban tugas.'),
                                      backgroundColor: Colors.orange,
                                    ),
                                  );
                                  return;
                                }
                              }

                              setModalState(() => isSubmitting = true);

                              String finalFilePath = '';
                              File? finalFileToUpload;

                              if (submissionMode == 'file') {
                                if (selectedLocalFile != null) {
                                  finalFileToUpload = selectedLocalFile;
                                } else {
                                  finalFilePath = t.filePathSiswa ?? '';
                                }
                              } else {
                                finalFilePath = driveLink;
                              }

                              final messenger = ScaffoldMessenger.of(context);
                              final nav = Navigator.of(context);

                              final res = await Provider.of<SiswaProvider>(context, listen: false).submitTugasWithResponse(
                                user.id,
                                t.id,
                                notes,
                                finalFilePath,
                                file: finalFileToUpload,
                              );

                              if (mounted) {
                                setModalState(() => isSubmitting = false);
                              }
                              nav.pop();

                              final success = res['success'] == true;
                              messenger.showSnackBar(
                                SnackBar(
                                  content: Text(res['message'] ?? (success ? 'Tugas berhasil dikumpulkan!' : 'Gagal mengirim tugas')),
                                  backgroundColor: success ? Colors.green : Colors.red,
                                ),
                              );
                            },
                      icon: isSubmitting
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.send_rounded),
                      label: Text(
                        isSubmitting
                            ? 'Mengunggah Jawaban...'
                            : (t.isSubmitted ? 'Simpan Perubahan Jawaban' : 'Kirim Jawaban Tugas'),
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.secondaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        );
      },
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
        return matchesSearch && !t.isSubmitted && !t.isLocked;
      } else if (_selectedStatusFilter == 'dikumpul') {
        return matchesSearch && t.isSubmitted && !t.isGraded;
      } else if (_selectedStatusFilter == 'dinilai') {
        return matchesSearch && t.isGraded;
      } else if (_selectedStatusFilter == 'expired') {
        return matchesSearch && !t.isSubmitted && t.isLocked;
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
                  const SizedBox(width: 8),
                  _buildFilterChip('🔒 Expired / Terkunci', 'expired'),
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

                            String statusText = '⏳ Belum';
                            Color statusBg = Colors.orange.shade100;
                            Color statusFg = Colors.orange.shade900;

                            if (t.isGraded) {
                              statusText = '⭐ Nilai: ${t.nilai}';
                              statusBg = Colors.green.shade100;
                              statusFg = Colors.green.shade900;
                            } else if (t.isSubmitted) {
                              statusText = '✅ Dikumpul';
                              statusBg = Colors.blue.shade100;
                              statusFg = Colors.blue.shade900;
                            } else if (t.isSusulanDisetujui) {
                              statusText = '✅ Izin Disetujui';
                              statusBg = Colors.teal.shade100;
                              statusFg = Colors.teal.shade900;
                            } else if (t.isSusulanPending) {
                              statusText = '⏳ Wait Konfirmasi';
                              statusBg = Colors.amber.shade100;
                              statusFg = Colors.amber.shade900;
                            } else if (t.isSusulanDitolak) {
                              statusText = '❌ Izin Ditolak';
                              statusBg = Colors.red.shade100;
                              statusFg = Colors.red.shade900;
                            } else if (t.isLocked) {
                              statusText = '🔒 Expired';
                              statusBg = Colors.red.shade100;
                              statusFg = Colors.red.shade900;
                            }

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
                                        Flexible(
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
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
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 6),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: statusBg,
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            statusText,
                                            style: TextStyle(
                                              color: statusFg,
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
                                        Expanded(
                                          child: Text(
                                            "Batas: ${t.deadline}",
                                            style: const TextStyle(fontSize: 12, color: Colors.redAccent, fontWeight: FontWeight.bold),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),

                                    // Expired Notice strip on card if locked & not submitted
                                    if (!t.isSubmitted && t.isLocked) ...[
                                      const SizedBox(height: 8),
                                      Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: t.isSusulanPending ? Colors.amber.shade50 : Colors.red.shade50,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: t.isSusulanPending ? Colors.amber.shade200 : Colors.red.shade200),
                                        ),
                                        child: Row(
                                          children: [
                                            Icon(
                                              t.isSusulanPending ? Icons.hourglass_top_rounded : Icons.lock_clock_rounded,
                                              size: 14,
                                              color: t.isSusulanPending ? Colors.amber.shade900 : Colors.red.shade800,
                                            ),
                                            const SizedBox(width: 6),
                                            Expanded(
                                              child: Text(
                                                t.isSusulanPending
                                                    ? 'Permohonan izin susulan menunggu konfirmasi Guru'
                                                    : (t.isSusulanDitolak ? 'Permohonan izin susulan ditolak Guru' : 'Batas deadline telah lewat, minta izin ke Guru'),
                                                style: TextStyle(
                                                  fontSize: 11,
                                                  fontWeight: FontWeight.w600,
                                                  color: t.isSusulanPending ? Colors.amber.shade900 : Colors.red.shade900,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],

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
                                        Expanded(
                                          child: Row(
                                            children: [
                                              const Icon(Icons.person, size: 14, color: Colors.grey),
                                              const SizedBox(width: 4),
                                              Expanded(
                                                child: Text(
                                                  t.namaGuru ?? '-',
                                                  style: const TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 6),
                                        if (!t.isSubmitted && t.isLocked) ...[
                                          if (t.isSusulanPending) ...[
                                            ElevatedButton.icon(
                                              onPressed: () => _showTugasDetailAndSubmit(t),
                                              icon: const Icon(Icons.hourglass_top_rounded, size: 14),
                                              label: const Text('Wait Konfirmasi', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: Colors.amber.shade800,
                                                foregroundColor: Colors.white,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                                visualDensity: VisualDensity.compact,
                                              ),
                                            ),
                                          ] else ...[
                                            ElevatedButton.icon(
                                              onPressed: () => _showRequestPermissionDialog(t),
                                              icon: const Icon(Icons.mark_email_unread_rounded, size: 14),
                                              label: Text(t.isSusulanDitolak ? 'Minta Izin Lagi' : 'Minta Izin Buka', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: Colors.redAccent,
                                                foregroundColor: Colors.white,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                                visualDensity: VisualDensity.compact,
                                              ),
                                            ),
                                          ],
                                        ] else ...[
                                          ElevatedButton.icon(
                                            onPressed: () => _showTugasDetailAndSubmit(t),
                                            icon: Icon(t.isSubmitted ? Icons.edit_note_rounded : Icons.upload_file_rounded, size: 14),
                                            label: Text(t.isSubmitted ? 'Edit Jawaban' : 'Kumpul Tugas', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: t.isSubmitted ? Colors.blue.shade800 : AppTheme.secondaryColor,
                                              foregroundColor: Colors.white,
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                              visualDensity: VisualDensity.compact,
                                            ),
                                          ),
                                        ],
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
