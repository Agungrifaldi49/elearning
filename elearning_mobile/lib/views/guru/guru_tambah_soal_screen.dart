import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../models/quiz_model.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class DraftSoalItem {
  String jenisSoal; // 'pg', 'tf', 'essay'
  final TextEditingController pertController;
  final TextEditingController bobotController;
  File? gambarFile;
  List<TextEditingController> opsiControllers;
  int correctOptionIndex; // For PG
  bool tfCorrectIsBenar; // For True/False: true = BENAR, false = SALAH

  DraftSoalItem({
    this.jenisSoal = 'pg',
    String initialPertanyaan = '',
    String initialBobot = '10',
    this.gambarFile,
    List<String>? initialOptions,
    this.correctOptionIndex = 0,
    this.tfCorrectIsBenar = true,
  })  : pertController = TextEditingController(text: initialPertanyaan),
        bobotController = TextEditingController(text: initialBobot),
        opsiControllers = (initialOptions != null && initialOptions.isNotEmpty)
            ? initialOptions.map((e) => TextEditingController(text: e)).toList()
            : [
                TextEditingController(),
                TextEditingController(),
                TextEditingController(),
                TextEditingController(),
              ];

  void dispose() {
    pertController.dispose();
    bobotController.dispose();
    for (var c in opsiControllers) {
      c.dispose();
    }
  }
}

class GuruTambahSoalScreen extends StatefulWidget {
  final int quizId;
  final List<QuizModel> quizList;

  const GuruTambahSoalScreen({
    super.key,
    required this.quizId,
    required this.quizList,
  });

  @override
  State<GuruTambahSoalScreen> createState() => _GuruTambahSoalScreenState();
}

class _GuruTambahSoalScreenState extends State<GuruTambahSoalScreen> {
  late int _selectedQuizId;
  List<QuizModel> _quizList = [];
  final List<DraftSoalItem> _draftList = [];
  final ImagePicker _picker = ImagePicker();
  bool _isSubmitting = false;
  String _submitProgressText = '';

  @override
  void initState() {
    super.initState();
    _quizList = List<QuizModel>.from(widget.quizList);
    _selectedQuizId = widget.quizId > 0
        ? widget.quizId
        : (_quizList.isNotEmpty ? _quizList.first.id : 0);
    // Add initial question
    _draftList.add(DraftSoalItem());

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initQuizList();
    });
  }

  Future<void> _initQuizList() async {
    if (_quizList.isEmpty) {
      final gp = Provider.of<GuruProvider>(context, listen: false);
      await gp.fetchQuizList();
      if (mounted) {
        setState(() {
          _quizList = List<QuizModel>.from(gp.quizList);
          if (_selectedQuizId <= 0 && _quizList.isNotEmpty) {
            _selectedQuizId = _quizList.first.id;
          }
        });
      }
    } else {
      if (_selectedQuizId <= 0 && _quizList.isNotEmpty) {
        setState(() {
          _selectedQuizId = _quizList.first.id;
        });
      }
    }
  }

  @override
  void dispose() {
    for (var item in _draftList) {
      item.dispose();
    }
    super.dispose();
  }

  void _addNewQuestion() {
    setState(() {
      _draftList.add(DraftSoalItem());
    });
  }

  void _removeQuestion(int index) {
    if (_draftList.length <= 1) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Minimal harus ada 1 soal dalam formulir.'), duration: Duration(seconds: 1)),
      );
      return;
    }
    setState(() {
      final removed = _draftList.removeAt(index);
      removed.dispose();
    });
  }

  Future<void> _pickImage(DraftSoalItem item) async {
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
              const Text('Lampirkan Gambar Soal', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 6),
              const Text('Pilih sumber gambar untuk pertanyaan ini', style: TextStyle(fontSize: 12, color: Colors.grey)),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => Navigator.pop(context, ImageSource.camera),
                      icon: const Icon(Icons.camera_alt_rounded),
                      label: const Text('Kamera'),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.pop(context, ImageSource.gallery),
                      icon: const Icon(Icons.photo_library_rounded),
                      label: const Text('Galeri'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.purple.shade800,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );

    if (source != null) {
      try {
        final picked = await _picker.pickImage(
          source: source,
          maxWidth: 900,
          maxHeight: 900,
          imageQuality: 70,
        );
        if (picked != null) {
          setState(() {
            item.gambarFile = File(picked.path);
          });
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Gagal memilih gambar: $e'), backgroundColor: Colors.red),
          );
        }
      }
    }
  }

  Future<void> _submitAllQuestions() async {
    // 0. Ensure target quiz is selected
    if (_selectedQuizId <= 0) {
      if (_quizList.isNotEmpty) {
        _selectedQuizId = _quizList.first.id;
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Target Quiz belum tersedia! Silakan buat Quiz CBT terlebih dahulu.'),
            backgroundColor: Colors.red,
          ),
        );
        return;
      }
    }

    // 1. Validation
    for (int i = 0; i < _draftList.length; i++) {
      final item = _draftList[i];
      final qNum = i + 1;
      if (item.pertController.text.trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Teks pertanyaan Soal #$qNum masih kosong!'), backgroundColor: Colors.red),
        );
        return;
      }

      if (item.jenisSoal == 'pg') {
        final nonEmptyOpts = item.opsiControllers.where((c) => c.text.trim().isNotEmpty).toList();
        if (nonEmptyOpts.length < 2) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Soal #$qNum (Pilihan Ganda) minimal harus memiliki 2 pilihan jawaban!'), backgroundColor: Colors.red),
          );
          return;
        }
        if (item.correctOptionIndex >= item.opsiControllers.length ||
            item.opsiControllers[item.correctOptionIndex].text.trim().isEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Kunci jawaban yang dipilih pada Soal #$qNum tidak boleh kosong!'), backgroundColor: Colors.red),
          );
          return;
        }
      }
    }

    setState(() => _isSubmitting = true);

    try {
      final List<Map<String, dynamic>> soalPayloadList = [];
      int uploadIdx = 0;

      for (var item in _draftList) {
        uploadIdx++;
        String? finalImgVal;

        if (item.gambarFile != null && await item.gambarFile!.exists()) {
          if (mounted) {
            setState(() {
              _submitProgressText = 'Mengunggah gambar soal #$uploadIdx...';
            });
          }

          // 1. Unggah via Multipart (identik dengan cara web, menghindari limit base64 JSON)
          try {
            final uploadRes = await ApiService.postMultipart(
              'guru/upload_soal_gambar',
              files: {'gambar_soal': item.gambarFile!},
            );

            if (uploadRes['success'] == true && uploadRes['data'] is Map) {
              final returnedFilename = uploadRes['data']['filename'] ??
                  uploadRes['data']['gambar'] ??
                  uploadRes['data']['file_gambar'];
              if (returnedFilename != null && returnedFilename.toString().trim().isNotEmpty) {
                finalImgVal = returnedFilename.toString().trim();
              }
            }
          } catch (e) {
            debugPrint('Multipart upload failed for soal #$uploadIdx: $e');
          }

          // 2. Fallback ke base64 jika multipart terkendala
          if (finalImgVal == null || finalImgVal.isEmpty) {
            try {
              final bytes = await item.gambarFile!.readAsBytes();
              final ext = item.gambarFile!.path.split('.').last.toLowerCase();
              final mime = (ext == 'png') ? 'png' : ((ext == 'webp') ? 'webp' : 'jpeg');
              finalImgVal = 'data:image/$mime;base64,${base64Encode(bytes)}';
            } catch (e) {
              debugPrint('Base64 encoding fallback error: $e');
            }
          }
        }

        final bobot = int.tryParse(item.bobotController.text.trim()) ?? 10;
        final List<Map<String, dynamic>> pilihanList = [];

        if (item.jenisSoal == 'pg') {
          for (int optIdx = 0; optIdx < item.opsiControllers.length; optIdx++) {
            final optText = item.opsiControllers[optIdx].text.trim();
            if (optText.isNotEmpty) {
              pilihanList.add({
                'teks': optText,
                'is_benar': optIdx == item.correctOptionIndex ? 1 : 0,
              });
            }
          }
        } else if (item.jenisSoal == 'tf') {
          pilihanList.add({
            'teks': 'Benar',
            'is_benar': item.tfCorrectIsBenar ? 1 : 0,
          });
          pilihanList.add({
            'teks': 'Salah',
            'is_benar': !item.tfCorrectIsBenar ? 1 : 0,
          });
        }

        final pertText = item.pertController.text.trim();
        soalPayloadList.add({
          'jenis_soal': item.jenisSoal,
          'pertanyaan': pertText,
          'soal': pertText,
          'bobot': bobot,
          'gambar_base64': finalImgVal,
          'file_gambar': finalImgVal,
          'image_base64': finalImgVal,
          'gambar': finalImgVal,
          'pilihan': pilihanList,
        });
      }

      int successCount = 0;
      String? lastErrorMessage;

      if (mounted) {
        setState(() {
          _submitProgressText = 'Menyimpan 1 dari ${soalPayloadList.length}...';
        });
      }

      // Root payload supporting both batch and single format
      final Map<String, dynamic> mainBatchBody = {
        'quiz_id': _selectedQuizId,
        'soal_list': soalPayloadList,
      };
      if (soalPayloadList.isNotEmpty) {
        mainBatchBody['pertanyaan'] = soalPayloadList.first['pertanyaan'];
        mainBatchBody['soal'] = soalPayloadList.first['soal'];
        mainBatchBody['jenis_soal'] = soalPayloadList.first['jenis_soal'];
        mainBatchBody['bobot'] = soalPayloadList.first['bobot'];
        mainBatchBody['pilihan'] = soalPayloadList.first['pilihan'];
        mainBatchBody['gambar'] = soalPayloadList.first['gambar'];
        mainBatchBody['gambar_base64'] = soalPayloadList.first['gambar_base64'];
        mainBatchBody['file_gambar'] = soalPayloadList.first['file_gambar'];
        mainBatchBody['image_base64'] = soalPayloadList.first['image_base64'];
      }

      // First attempt: try sending the entire batch in a single request
      final batchRes = await ApiService.post('guru/bank_soal', mainBatchBody);

      // If backend successfully saved all items in batch:
      if (batchRes['success'] == true &&
          batchRes['data'] is Map &&
          (batchRes['data']['total_saved'] ?? 0) >= soalPayloadList.length) {
        successCount = batchRes['data']['total_saved'] ?? soalPayloadList.length;
        final imgFailed = int.tryParse('${batchRes['data']['image_failed'] ?? 0}') ?? 0;
        if (imgFailed > 0) {
          lastErrorMessage = batchRes['message']?.toString();
        }
      } else {
        // If the backend runs code that only saves 1 item per request:
        // If the batch request above already saved item #0:
        int startIndex = 0;
        if (batchRes['success'] == true) {
          successCount = 1;
          startIndex = 1;
        }

        for (int i = startIndex; i < soalPayloadList.length; i++) {
          if (mounted) {
            setState(() {
              _submitProgressText = 'Menyimpan soal ${i + 1} dari ${soalPayloadList.length}...';
            });
          }

          final item = soalPayloadList[i];
          final res = await ApiService.post('guru/bank_soal', {
            'quiz_id': _selectedQuizId,
            'pertanyaan': item['pertanyaan'] ?? '',
            'soal': item['pertanyaan'] ?? '',
            'jenis_soal': item['jenis_soal'] ?? 'pg',
            'bobot': item['bobot'] ?? 10,
            'pilihan': item['pilihan'] ?? [],
            'gambar_base64': item['gambar_base64'],
            'file_gambar': item['gambar_base64'],
            'image_base64': item['gambar_base64'],
            'gambar': item['gambar_base64'],
            'soal_list': [item],
          });

          if (res['success'] == true) {
            successCount++;
          } else {
            lastErrorMessage = res['message'] ?? 'Gagal pada soal #${i + 1}';
          }
        }
      }

      if (mounted) {
        setState(() => _isSubmitting = false);
        if (successCount > 0) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(lastErrorMessage != null && lastErrorMessage.contains('gambar gagal')
                  ? lastErrorMessage
                  : 'Berhasil menyimpan $successCount soal ke Bank Soal! 🎉'),
              backgroundColor: (lastErrorMessage != null && lastErrorMessage.contains('gambar gagal')) ? Colors.orange : Colors.green,
            ),
          );
          Navigator.pop(context, true);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(lastErrorMessage ?? 'Gagal menyimpan soal.'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      appBar: AppBar(
        title: const Text('Buat & Susun Bank Soal', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: Colors.grey.shade200, height: 1),
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(color: Colors.black.withAlpha(12), blurRadius: 10, offset: const Offset(0, -3)),
          ],
        ),
        child: SafeArea(
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.purple.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.purple.shade200),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.format_list_numbered_rounded, size: 16, color: Colors.purple.shade800),
                    const SizedBox(width: 4),
                    Text(
                      '${_draftList.length} Soal',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.purple.shade900),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _isSubmitting ? null : _submitAllQuestions,
                  icon: _isSubmitting
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Icon(Icons.save_rounded, size: 18),
                  label: Text(
                    _isSubmitting
                        ? (_submitProgressText.isNotEmpty ? _submitProgressText : 'Menyimpan...')
                        : 'Simpan Semua Soal (${_draftList.length})',
                    overflow: TextOverflow.ellipsis,
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.purple.shade800,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    elevation: 1,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Quiz Target Selector Card
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.quiz_rounded, size: 18, color: Colors.purple.shade800),
                      const SizedBox(width: 8),
                      const Text(
                        'Target Ujian CBT / Quiz',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.black87),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_quizList.isNotEmpty)
                    DropdownButtonFormField<int>(
                      key: ValueKey(_selectedQuizId),
                      initialValue: _quizList.any((q) => q.id == _selectedQuizId)
                          ? _selectedQuizId
                          : _quizList.first.id,
                      isExpanded: true,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                      items: _quizList.map((q) {
                        return DropdownMenuItem<int>(
                          value: q.id,
                          child: Text(
                            '${q.judul} (${q.namaMapel})',
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedQuizId = val;
                          });
                        }
                      },
                    )
                  else
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8),
                      child: Text('Memuat daftar kuis...', style: TextStyle(color: Colors.grey, fontSize: 12)),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Question Cards List
            ..._draftList.asMap().entries.map((entry) {
              final index = entry.key;
              final item = entry.value;
              return _buildQuestionCard(index, item);
            }),

            const SizedBox(height: 12),

            // "+ Tambah Soal Berikutnya" Button
            OutlinedButton.icon(
              onPressed: _addNewQuestion,
              icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
              label: const Text('Tambah Soal Berikutnya (+)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 50),
                foregroundColor: Colors.purple.shade800,
                side: BorderSide(color: Colors.purple.shade300, width: 1.5),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                backgroundColor: Colors.white,
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildQuestionCard(int index, DraftSoalItem item) {
    final qNum = index + 1;
    final isPg = item.jenisSoal == 'pg';
    final isTf = item.jenisSoal == 'tf';
    final isEssay = item.jenisSoal == 'essay';

    return Container(
      margin: const EdgeInsets.only(bottom: 18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.grey.shade300),
        boxShadow: [
          BoxShadow(color: Colors.black.withAlpha(6), blurRadius: 10, offset: const Offset(0, 3)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Bar (Overflow-safe)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
              border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Flexible(
                  child: Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.purple.shade700,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Text(
                          'Soal #$qNum',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: isPg
                              ? Colors.purple.shade50
                              : (isTf ? Colors.amber.shade50 : Colors.teal.shade50),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isPg
                                ? Colors.purple.shade300
                                : (isTf ? Colors.amber.shade300 : Colors.teal.shade300),
                          ),
                        ),
                        child: Text(
                          isPg ? 'Pilihan Ganda' : (isTf ? 'Benar / Salah' : 'Essay'),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isPg
                                ? Colors.purple.shade900
                                : (isTf ? Colors.amber.shade900 : Colors.teal.shade900),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                if (_draftList.length > 1)
                  IconButton(
                    icon: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 20),
                    onPressed: () => _removeQuestion(index),
                    tooltip: 'Hapus Soal Ini',
                    visualDensity: VisualDensity.compact,
                  ),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Bentuk / Jenis Soal Selector
                const Text('Bentuk / Jenis Soal *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.grey)),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    _buildTypeChip(
                      label: '📝 Pilihan Ganda (PG)',
                      isSelected: isPg,
                      onTap: () => setState(() => item.jenisSoal = 'pg'),
                    ),
                    _buildTypeChip(
                      label: '⚖️ Benar / Salah (True/False)',
                      isSelected: isTf,
                      onTap: () => setState(() => item.jenisSoal = 'tf'),
                    ),
                    _buildTypeChip(
                      label: '✍️ Essay / Uraian',
                      isSelected: isEssay,
                      onTap: () => setState(() => item.jenisSoal = 'essay'),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // 2. Pertanyaan Soal
                const Text('Pertanyaan Soal *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                const SizedBox(height: 6),
                TextField(
                  controller: item.pertController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    hintText: 'Tuliskan isi pertanyaan atau potongan kode...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.all(12),
                  ),
                  style: const TextStyle(fontSize: 13),
                ),
                const SizedBox(height: 14),

                // 3. Lampiran Gambar (Opsional)
                const Text('Lampiran Gambar Soal (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                const SizedBox(height: 6),
                if (item.gambarFile != null)
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade50,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Image.file(
                            item.gambarFile!,
                            height: 150,
                            width: double.infinity,
                            fit: BoxFit.contain,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            TextButton.icon(
                              onPressed: () => _pickImage(item),
                              icon: const Icon(Icons.edit_rounded, size: 14),
                              label: const Text('Ganti Gambar', style: TextStyle(fontSize: 11)),
                            ),
                            const SizedBox(width: 8),
                            TextButton.icon(
                              onPressed: () => setState(() => item.gambarFile = null),
                              icon: const Icon(Icons.delete_outline_rounded, size: 14, color: Colors.red),
                              label: const Text('Hapus', style: TextStyle(fontSize: 11, color: Colors.red)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  )
                else
                  OutlinedButton.icon(
                    onPressed: () => _pickImage(item),
                    icon: const Icon(Icons.add_photo_alternate_outlined, size: 16),
                    label: const Text('Tambah Gambar Soal (Opsional)', style: TextStyle(fontSize: 12)),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      foregroundColor: Colors.purple.shade800,
                      side: BorderSide(color: Colors.purple.shade200),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                const SizedBox(height: 16),

                // 4. Opsi Pilihan Jawaban
                if (isPg) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Pilihan Jawaban *',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppTheme.primaryColor),
                            ),
                            Text(
                              'Klik radio untuk kunci benar',
                              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                      TextButton.icon(
                        onPressed: () {
                          setState(() {
                            item.opsiControllers.add(TextEditingController());
                          });
                        },
                        icon: const Icon(Icons.add_rounded, size: 14),
                        label: const Text('Tambah Opsi', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                        style: TextButton.styleFrom(
                          visualDensity: VisualDensity.compact,
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  ...item.opsiControllers.asMap().entries.map((entry) {
                    final optIdx = entry.key;
                    final optCtrl = entry.value;
                    final optChar = String.fromCharCode(65 + optIdx); // A, B, C...
                    final isCorrect = item.correctOptionIndex == optIdx;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: isCorrect ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isCorrect ? const Color(0xFF10B981) : Colors.grey.shade300,
                          width: isCorrect ? 1.5 : 1.0,
                        ),
                      ),
                      child: Row(
                        children: [
                          InkWell(
                            onTap: () {
                              setState(() {
                                item.correctOptionIndex = optIdx;
                              });
                            },
                            borderRadius: BorderRadius.circular(20),
                            child: Padding(
                              padding: const EdgeInsets.all(4.0),
                              child: Row(
                                children: [
                                  Container(
                                    width: 20,
                                    height: 20,
                                    decoration: BoxDecoration(
                                      color: isCorrect ? const Color(0xFF10B981) : Colors.transparent,
                                      shape: BoxShape.circle,
                                      border: Border.all(
                                        color: isCorrect ? const Color(0xFF10B981) : Colors.grey.shade400,
                                        width: 1.5,
                                      ),
                                    ),
                                    child: isCorrect
                                        ? const Icon(Icons.check, size: 13, color: Colors.white)
                                        : null,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '$optChar.',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 12,
                                      color: isCorrect ? const Color(0xFF065F46) : Colors.black87,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: TextField(
                              controller: optCtrl,
                              decoration: InputDecoration(
                                hintText: 'Teks Pilihan $optChar...',
                                border: InputBorder.none,
                                isDense: true,
                              ),
                              style: const TextStyle(fontSize: 12),
                            ),
                          ),
                          if (item.opsiControllers.length > 2)
                            IconButton(
                              icon: const Icon(Icons.close_rounded, size: 16, color: Colors.grey),
                              onPressed: () {
                                setState(() {
                                  if (item.correctOptionIndex == optIdx && item.correctOptionIndex > 0) {
                                    item.correctOptionIndex--;
                                  }
                                  item.opsiControllers.removeAt(optIdx).dispose();
                                });
                              },
                              tooltip: 'Hapus Opsi',
                              visualDensity: VisualDensity.compact,
                            ),
                        ],
                      ),
                    );
                  }),
                  const SizedBox(height: 14),
                ] else if (isTf) ...[
                  const Text(
                    'Kunci Jawaban Benar (True / False) *',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppTheme.primaryColor),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () => setState(() => item.tfCorrectIsBenar = true),
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: item.tfCorrectIsBenar ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: item.tfCorrectIsBenar ? const Color(0xFF10B981) : Colors.grey.shade300,
                                width: item.tfCorrectIsBenar ? 2 : 1,
                              ),
                            ),
                            child: Column(
                              children: [
                                Icon(
                                  Icons.check_circle_rounded,
                                  color: item.tfCorrectIsBenar ? const Color(0xFF10B981) : Colors.grey.shade400,
                                  size: 24,
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'BENAR (True)',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                    color: item.tfCorrectIsBenar ? const Color(0xFF065F46) : Colors.black87,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: InkWell(
                          onTap: () => setState(() => item.tfCorrectIsBenar = false),
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: !item.tfCorrectIsBenar ? Colors.red.shade50 : Colors.grey.shade50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: !item.tfCorrectIsBenar ? Colors.red.shade400 : Colors.grey.shade300,
                                width: !item.tfCorrectIsBenar ? 2 : 1,
                              ),
                            ),
                            child: Column(
                              children: [
                                Icon(
                                  Icons.cancel_rounded,
                                  color: !item.tfCorrectIsBenar ? Colors.red.shade600 : Colors.grey.shade400,
                                  size: 24,
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'SALAH (False)',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                    color: !item.tfCorrectIsBenar ? Colors.red.shade900 : Colors.black87,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                ] else ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.blue.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.blue.shade200),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.info_outline_rounded, color: Colors.blue.shade700, size: 20),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Text(
                            'Soal Essay tidak memerlukan opsi pilihan. Siswa akan mengetikkan jawaban uraian dan guru mengoreksinya pada menu Koreksi Quiz.',
                            style: TextStyle(fontSize: 11, color: Colors.black87, height: 1.3),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                ],

                // 5. Bobot Skor Nilai (Poin)
                Row(
                  children: [
                    const Expanded(
                      child: Text('Bobot Skor Nilai (Poin) *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    ),
                    const SizedBox(width: 12),
                    SizedBox(
                      width: 75,
                      child: TextField(
                        controller: item.bobotController,
                        keyboardType: TextInputType.number,
                        textAlign: TextAlign.center,
                        decoration: InputDecoration(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                          isDense: true,
                        ),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTypeChip({
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? Colors.purple.shade800 : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? Colors.purple.shade800 : Colors.grey.shade300,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            color: isSelected ? Colors.white : Colors.black87,
          ),
        ),
      ),
    );
  }
}
