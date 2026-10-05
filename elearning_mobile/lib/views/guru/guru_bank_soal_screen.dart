import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../models/quiz_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import 'guru_tambah_soal_screen.dart';

class GuruBankSoalScreen extends StatefulWidget {
  final QuizModel? quiz;

  const GuruBankSoalScreen({super.key, this.quiz});

  @override
  State<GuruBankSoalScreen> createState() => _GuruBankSoalScreenState();
}

class _GuruBankSoalScreenState extends State<GuruBankSoalScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedJenis = 'Semua';
  int _selectedQuizId = 0; // 0 = Semua Quiz

  List<dynamic> _soalList = [];
  bool _isLoading = true;

  final List<String> _jenisList = ['Semua', 'PG', 'Essay', 'True/False'];

  @override
  void initState() {
    super.initState();
    if (widget.quiz != null) {
      _selectedQuizId = widget.quiz!.id;
    }
    _loadSoalData();
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text.toLowerCase().trim();
      });
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final gp = Provider.of<GuruProvider>(context, listen: false);
      if (gp.quizList.isEmpty) {
        gp.fetchQuizList();
      }
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadSoalData() async {
    setState(() => _isLoading = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final params = <String, String>{};
    if (user != null) {
      params['user_id'] = '${user.id}';
    }
    if (_selectedQuizId > 0) {
      params['quiz_id'] = '$_selectedQuizId';
    }

    try {
      final res = await ApiService.get('guru/bank_soal', params: params);
      if (mounted) {
        if (res['success'] == true && res['data'] is List) {
          setState(() {
            _soalList = res['data'];
            _isLoading = false;
          });
        } else {
          setState(() {
            _soalList = [];
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _deleteSoal(int soalId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Soal Pertanyaan?'),
        content: const Text('Soal dan kunci pilihan jawaban terkait akan dihapus secara permanen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final res = await ApiService.post('guru/bank_soal', {
        'action': 'delete',
        'soal_id': soalId,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Soal berhasil dihapus!'),
            backgroundColor: res['success'] == true ? Colors.green : Colors.red,
          ),
        );
        _loadSoalData();
      }
    }
  }

  Future<void> _openTambahSoalScreen() async {
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    if (guruProvider.quizList.isEmpty) {
      await guruProvider.fetchQuizList();
    }
    final quizList = guruProvider.quizList;

    int currentQuizId = _selectedQuizId > 0
        ? _selectedQuizId
        : (quizList.isNotEmpty ? quizList.first.id : (widget.quiz?.id ?? 0));

    if (!mounted) return;
    final res = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => GuruTambahSoalScreen(
          quizId: currentQuizId,
          quizList: quizList,
        ),
      ),
    );

    if (res == true) {
      _loadSoalData();
      guruProvider.fetchQuizList();
    }
  }

  void _showAddSoalModal() {
    _openTambahSoalScreen();
  }

  String _stripHtml(String? text) {
    if (text == null || text.isEmpty) return '';
    return text
        .replaceAll(RegExp(r'<[^>]*>'), ' ')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&quot;', '"')
        .replaceAll('&#39;', "'")
        .replaceAll(RegExp(r'\s+'), ' ')
        .trim();
  }

  String? _getQuestionImageUrl(Map<String, dynamic> s) {
    final possibleUrl = (s['file_gambar_url'] ?? s['gambar_url'] ?? '').toString().trim();
    if (possibleUrl.isNotEmpty && possibleUrl.toLowerCase() != 'null') {
      return ApiService.getFileUrl(possibleUrl);
    }
    final rawGambar = (s['gambar'] ?? '').toString().trim();
    if (rawGambar.isEmpty || rawGambar.toLowerCase() == 'null') return null;

    if (rawGambar.startsWith('http://') || rawGambar.startsWith('https://')) {
      return ApiService.getFileUrl(rawGambar);
    }

    final clean = rawGambar.replaceAll(RegExp(r'^/+'), '');
    if (clean.startsWith('assets/')) {
      return ApiService.getFileUrl(clean);
    } else if (clean.startsWith('uploads/')) {
      return ApiService.getFileUrl('assets/$clean');
    }

    return ApiService.getFileUrl('assets/uploads/soal/$clean');
  }

  void _previewImage(String url) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(12),
        child: Stack(
          alignment: Alignment.topRight,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Container(
                color: Colors.black,
                child: InteractiveViewer(
                  panEnabled: true,
                  minScale: 0.5,
                  maxScale: 4.0,
                  child: Image.network(
                    url,
                    fit: BoxFit.contain,
                    loadingBuilder: (c, child, progress) {
                      if (progress == null) return child;
                      return const SizedBox(
                        height: 250,
                        child: Center(
                          child: CircularProgressIndicator(color: Colors.white),
                        ),
                      );
                    },
                    errorBuilder: (c, err, st) => Container(
                      padding: const EdgeInsets.all(24),
                      color: Colors.white,
                      child: const Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.broken_image, size: 48, color: Colors.grey),
                          SizedBox(height: 8),
                          Text('Gagal memuat gambar', style: TextStyle(color: Colors.black87)),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
            Positioned(
              top: 10,
              right: 10,
              child: CircleAvatar(
                backgroundColor: Colors.black54,
                child: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white, size: 20),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _openEditSoalModal(Map<String, dynamic> s) {
    final soalId = s['id'];
    final pertController = TextEditingController(text: _stripHtml(s['pertanyaan']));
    final bobotController = TextEditingController(text: (s['bobot'] ?? 10).toString());

    String rawJenis = (s['jenis_soal'] ?? 'pg').toString().toLowerCase();
    String currentJenis = (rawJenis == 'tf' || rawJenis == 'true/false') ? 'tf' : (rawJenis == 'essay' ? 'essay' : 'pg');

    // Existing image
    final existingImageUrl = _getQuestionImageUrl(s);
    bool hapusGambarExisting = false;
    File? newPickedImage;

    // Parse options
    List<Map<String, dynamic>> choices = [];
    final rawPilihan = s['pilihan'];
    if (rawPilihan is List && rawPilihan.isNotEmpty) {
      for (final p in rawPilihan) {
        if (p is Map) {
          final teks = _stripHtml((p['teks_pilihan'] ?? p['teks'] ?? '').toString());
          final isBnr = (p['is_benar'] == 1 || p['is_benar'] == true || p['is_benar'] == '1');
          choices.add({
            'controller': TextEditingController(text: teks),
            'is_benar': isBnr,
          });
        }
      }
    }

    if (choices.isEmpty) {
      if (currentJenis == 'pg') {
        choices = [
          {'controller': TextEditingController(text: ''), 'is_benar': true},
          {'controller': TextEditingController(text: ''), 'is_benar': false},
          {'controller': TextEditingController(text: ''), 'is_benar': false},
          {'controller': TextEditingController(text: ''), 'is_benar': false},
        ];
      } else if (currentJenis == 'tf') {
        choices = [
          {'controller': TextEditingController(text: 'Benar (True)'), 'is_benar': true},
          {'controller': TextEditingController(text: 'Salah (False)'), 'is_benar': false},
        ];
      }
    }

    // Ensure at least one choice is true if PG or TF
    if ((currentJenis == 'pg' || currentJenis == 'tf') && choices.isNotEmpty) {
      if (!choices.any((c) => c['is_benar'] == true)) {
        choices[0]['is_benar'] = true;
      }
    }

    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return Container(
              height: MediaQuery.of(context).size.height * 0.88,
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                children: [
                  // Drag Handle & Header
                  Container(
                    padding: const EdgeInsets.fromLTRB(20, 12, 12, 12),
                    decoration: BoxDecoration(
                      border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
                    ),
                    child: Column(
                      children: [
                        Center(
                          child: Container(
                            width: 40,
                            height: 4,
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: Colors.grey.shade300,
                              borderRadius: BorderRadius.circular(2),
                            ),
                          ),
                        ),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: Colors.blue.shade50,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Icon(Icons.edit_note_rounded, color: Colors.blue.shade700, size: 22),
                            ),
                            const SizedBox(width: 12),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Edit Butir Soal',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.black87),
                                  ),
                                  Text(
                                    'Perbarui teks pertanyaan, jenis soal, opsi & gambar',
                                    style: TextStyle(fontSize: 11, color: Colors.grey),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.close_rounded),
                              onPressed: () => Navigator.pop(modalCtx),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  // Form Content (Scrollable)
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Jenis Soal Selector
                          const Text('Jenis Soal', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              _buildModalTypeChip(
                                label: 'Pilihan Ganda',
                                selected: currentJenis == 'pg',
                                color: Colors.blue,
                                onTap: () {
                                  setModalState(() {
                                    currentJenis = 'pg';
                                    if (choices.length < 2) {
                                      choices = [
                                        {'controller': TextEditingController(text: ''), 'is_benar': true},
                                        {'controller': TextEditingController(text: ''), 'is_benar': false},
                                      ];
                                    }
                                  });
                                },
                              ),
                              const SizedBox(width: 8),
                              _buildModalTypeChip(
                                label: 'True / False',
                                selected: currentJenis == 'tf',
                                color: Colors.purple,
                                onTap: () {
                                  setModalState(() {
                                    currentJenis = 'tf';
                                    choices = [
                                      {'controller': TextEditingController(text: 'Benar (True)'), 'is_benar': true},
                                      {'controller': TextEditingController(text: 'Salah (False)'), 'is_benar': false},
                                    ];
                                  });
                                },
                              ),
                              const SizedBox(width: 8),
                              _buildModalTypeChip(
                                label: 'Essay',
                                selected: currentJenis == 'essay',
                                color: Colors.orange,
                                onTap: () {
                                  setModalState(() {
                                    currentJenis = 'essay';
                                  });
                                },
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),

                          // Bobot Poin
                          const Text('Bobot Poin', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                          const SizedBox(height: 6),
                          TextField(
                            controller: bobotController,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              hintText: 'Contoh: 10',
                              filled: true,
                              fillColor: Colors.grey.shade50,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                              focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.primaryColor, width: 1.5)),
                              suffixText: 'Poin',
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Pertanyaan
                          const Text('Teks Pertanyaan Soal *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                          const SizedBox(height: 6),
                          TextField(
                            controller: pertController,
                            maxLines: 4,
                            decoration: InputDecoration(
                              hintText: 'Tuliskan butir pertanyaan di sini...',
                              filled: true,
                              fillColor: Colors.grey.shade50,
                              contentPadding: const EdgeInsets.all(14),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                              focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.primaryColor, width: 1.5)),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Image Section
                          const Text('Lampiran Gambar Soal (Opsional)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                          const SizedBox(height: 8),

                          // Display new picked image
                          if (newPickedImage != null) ...[
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.grey.shade300),
                              ),
                              child: Row(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: Image.file(
                                      newPickedImage!,
                                      width: 60,
                                      height: 60,
                                      fit: BoxFit.cover,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  const Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text('Gambar Baru Dipilih', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.green)),
                                        Text('Akan menggantikan gambar lama', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                                    tooltip: 'Batalkan Gambar Baru',
                                    onPressed: () {
                                      setModalState(() {
                                        newPickedImage = null;
                                      });
                                    },
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 10),
                          ] else if (existingImageUrl != null && !hapusGambarExisting) ...[
                            // Display existing image preview
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.grey.shade300),
                              ),
                              child: Row(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: Image.network(
                                      existingImageUrl,
                                      width: 60,
                                      height: 60,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => Container(
                                        width: 60,
                                        height: 60,
                                        color: Colors.grey.shade200,
                                        child: const Icon(Icons.broken_image, size: 24, color: Colors.grey),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  const Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text('Gambar Saat Ini', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.black87)),
                                        Text('Tersimpan di server', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                                    tooltip: 'Hapus Gambar',
                                    onPressed: () {
                                      setModalState(() {
                                        hapusGambarExisting = true;
                                      });
                                    },
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 10),
                          ] else if (hapusGambarExisting) ...[
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.red.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.red.shade200),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.info_outline, color: Colors.red.shade700, size: 20),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Gambar akan dihapus saat disimpan.',
                                      style: TextStyle(fontSize: 12, color: Colors.red.shade800, fontWeight: FontWeight.w600),
                                    ),
                                  ),
                                  TextButton(
                                    onPressed: () {
                                      setModalState(() {
                                        hapusGambarExisting = false;
                                      });
                                    },
                                    child: const Text('Batal', style: TextStyle(fontSize: 12)),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 10),
                          ],

                          // Image picker button
                          OutlinedButton.icon(
                            onPressed: () async {
                              final picker = ImagePicker();
                              final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
                              if (picked != null) {
                                setModalState(() {
                                  newPickedImage = File(picked.path);
                                  hapusGambarExisting = false;
                                });
                              }
                            },
                            icon: const Icon(Icons.image_outlined, size: 18),
                            label: Text(
                              newPickedImage != null || (existingImageUrl != null && !hapusGambarExisting)
                                  ? 'Ganti Gambar Soal'
                                  : '+ Unggah Gambar Soal',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppTheme.primaryColor,
                              side: const BorderSide(color: AppTheme.primaryColor),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Choices Section (For PG & TF)
                          if (currentJenis == 'pg') ...[
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text('Opsi Jawaban & Kunci Benar *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                                if (choices.length < 5)
                                  TextButton.icon(
                                    onPressed: () {
                                      setModalState(() {
                                        choices.add({
                                          'controller': TextEditingController(text: ''),
                                          'is_benar': false,
                                        });
                                      });
                                    },
                                    icon: const Icon(Icons.add_circle_outline, size: 16),
                                    label: const Text('Tambah Opsi', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            ...choices.asMap().entries.map((entry) {
                              final idx = entry.key;
                              final c = entry.value;
                              final label = String.fromCharCode(65 + idx); // A, B, C, D, E
                              final isBenar = c['is_benar'] == true;

                              return Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: isBenar ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: isBenar ? const Color(0xFF10B981) : Colors.grey.shade300,
                                    width: isBenar ? 1.5 : 1.0,
                                  ),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.center,
                                  children: [
                                    GestureDetector(
                                      onTap: () {
                                        setModalState(() {
                                          for (var o in choices) {
                                            o['is_benar'] = false;
                                          }
                                          c['is_benar'] = true;
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                        decoration: BoxDecoration(
                                          color: isBenar ? const Color(0xFF10B981) : Colors.grey.shade300,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Text(
                                              label,
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 12,
                                                color: isBenar ? Colors.white : Colors.black87,
                                              ),
                                            ),
                                            if (isBenar) ...[
                                              const SizedBox(width: 4),
                                              const Icon(Icons.check, size: 14, color: Colors.white),
                                            ],
                                          ],
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: TextField(
                                        controller: c['controller'] as TextEditingController,
                                        decoration: InputDecoration(
                                          hintText: 'Teks opsi $label...',
                                          isDense: true,
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                                          border: InputBorder.none,
                                        ),
                                      ),
                                    ),
                                    if (choices.length > 2)
                                      IconButton(
                                        icon: const Icon(Icons.close_rounded, size: 18, color: Colors.red),
                                        visualDensity: VisualDensity.compact,
                                        onPressed: () {
                                          setModalState(() {
                                            final wasBenar = c['is_benar'] == true;
                                            choices.removeAt(idx);
                                            if (wasBenar && choices.isNotEmpty) {
                                              choices[0]['is_benar'] = true;
                                            }
                                          });
                                        },
                                      ),
                                  ],
                                ),
                              );
                            }),
                            const Text(
                              '💡 Ketuk label huruf (A, B, C...) untuk menetapkan kunci jawaban yang benar.',
                              style: TextStyle(fontSize: 11, color: Colors.grey),
                            ),
                          ] else if (currentJenis == 'tf') ...[
                            const Text('Pilih Kunci Jawaban Benar (True / False) *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                            const SizedBox(height: 10),
                            Row(
                              children: [
                                Expanded(
                                  child: GestureDetector(
                                    onTap: () {
                                      setModalState(() {
                                        if (choices.length >= 2) {
                                          choices[0]['is_benar'] = true;
                                          choices[1]['is_benar'] = false;
                                        }
                                      });
                                    },
                                    child: Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: (choices.isNotEmpty && choices[0]['is_benar'] == true) ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(
                                          color: (choices.isNotEmpty && choices[0]['is_benar'] == true) ? const Color(0xFF10B981) : Colors.grey.shade300,
                                          width: (choices.isNotEmpty && choices[0]['is_benar'] == true) ? 1.5 : 1.0,
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(
                                            (choices.isNotEmpty && choices[0]['is_benar'] == true) ? Icons.check_circle_rounded : Icons.radio_button_unchecked,
                                            size: 18,
                                            color: (choices.isNotEmpty && choices[0]['is_benar'] == true) ? const Color(0xFF10B981) : Colors.grey,
                                          ),
                                          const SizedBox(width: 8),
                                          const Text('Benar (True)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: GestureDetector(
                                    onTap: () {
                                      setModalState(() {
                                        if (choices.length >= 2) {
                                          choices[0]['is_benar'] = false;
                                          choices[1]['is_benar'] = true;
                                        }
                                      });
                                    },
                                    child: Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: (choices.length >= 2 && choices[1]['is_benar'] == true) ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(
                                          color: (choices.length >= 2 && choices[1]['is_benar'] == true) ? const Color(0xFF10B981) : Colors.grey.shade300,
                                          width: (choices.length >= 2 && choices[1]['is_benar'] == true) ? 1.5 : 1.0,
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(
                                            (choices.length >= 2 && choices[1]['is_benar'] == true) ? Icons.check_circle_rounded : Icons.radio_button_unchecked,
                                            size: 18,
                                            color: (choices.length >= 2 && choices[1]['is_benar'] == true) ? const Color(0xFF10B981) : Colors.grey,
                                          ),
                                          const SizedBox(width: 8),
                                          const Text('Salah (False)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ] else ...[
                            Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: Colors.orange.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.orange.shade200),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.edit_note_rounded, size: 24, color: Colors.orange.shade800),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      'Soal Essay tidak memiliki pilihan jawaban. Penilaian akan dilakukan oleh Guru secara manual pada menu Koreksi Quiz.',
                                      style: TextStyle(fontSize: 12, color: Colors.orange.shade900),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                          const SizedBox(height: 24),
                        ],
                      ),
                    ),
                  ),

                  // Bottom Save Action Bar
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      boxShadow: [
                        BoxShadow(color: Colors.black.withAlpha(12), blurRadius: 10, offset: const Offset(0, -4)),
                      ],
                    ),
                    child: SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                final textPert = pertController.text.trim();
                                if (textPert.isEmpty) {
                                  ScaffoldMessenger.of(ctx).showSnackBar(
                                    const SnackBar(content: Text('Teks pertanyaan wajib diisi'), backgroundColor: Colors.red),
                                  );
                                  return;
                                }

                                if (currentJenis == 'pg') {
                                  bool allHaveText = choices.every((c) => (c['controller'] as TextEditingController).text.trim().isNotEmpty);
                                  if (!allHaveText) {
                                    ScaffoldMessenger.of(ctx).showSnackBar(
                                      const SnackBar(content: Text('Semua teks pilihan jawaban harus diisi'), backgroundColor: Colors.red),
                                    );
                                    return;
                                  }
                                }

                                setModalState(() => isSubmitting = true);

                                try {
                                  final Map<String, dynamic> payload = {
                                    'action': 'edit',
                                    'soal_id': soalId,
                                    'pertanyaan': textPert,
                                    'jenis_soal': currentJenis,
                                    'bobot': int.tryParse(bobotController.text.trim()) ?? 10,
                                    'hapus_gambar': hapusGambarExisting ? 1 : 0,
                                  };

                                  if (newPickedImage != null) {
                                    final bytes = await newPickedImage!.readAsBytes();
                                    final b64 = base64Encode(bytes);
                                    final ext = newPickedImage!.path.split('.').last.toLowerCase();
                                    payload['gambar_base64'] = 'data:image/$ext;base64,$b64';
                                  }

                                  if (currentJenis == 'pg' || currentJenis == 'tf') {
                                    payload['pilihan'] = choices.map((c) => {
                                      'teks': (c['controller'] as TextEditingController).text.trim(),
                                      'is_benar': c['is_benar'] == true ? 1 : 0,
                                    }).toList();
                                  }

                                  final res = await ApiService.post('guru/bank_soal', payload);
                                  if (res['success'] == true) {
                                    if (ctx.mounted) {
                                      Navigator.pop(ctx);
                                      ScaffoldMessenger.of(ctx).showSnackBar(
                                        const SnackBar(content: Text('Butir Soal berhasil diperbarui!'), backgroundColor: Colors.green),
                                      );
                                      _loadSoalData();
                                    }
                                  } else {
                                    setModalState(() => isSubmitting = false);
                                    if (ctx.mounted) {
                                      ScaffoldMessenger.of(ctx).showSnackBar(
                                        SnackBar(content: Text(res['message'] ?? 'Gagal memperbarui soal'), backgroundColor: Colors.red),
                                      );
                                    }
                                  }
                                } catch (e) {
                                  setModalState(() => isSubmitting = false);
                                  if (ctx.mounted) {
                                    ScaffoldMessenger.of(ctx).showSnackBar(
                                      SnackBar(content: Text('Terjadi kesalahan: $e'), backgroundColor: Colors.red),
                                    );
                                  }
                                }
                              },
                        icon: isSubmitting
                            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.save_rounded, size: 20),
                        label: Text(isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan Soal', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue.shade700,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildModalTypeChip({
    required String label,
    required bool selected,
    required MaterialColor color,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: selected ? color.shade50 : Colors.grey.shade50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: selected ? color : Colors.grey.shade300, width: selected ? 1.5 : 1.0),
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: selected ? FontWeight.bold : FontWeight.w600,
              color: selected ? color.shade800 : Colors.grey.shade700,
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final quizList = guruProvider.quizList;

    // Filter soal list
    final filteredSoal = _soalList.where((s) {
      final jns = (s['jenis_soal'] ?? 'pg').toString().toLowerCase();
      final matchesJenis = _selectedJenis == 'Semua' ||
          (_selectedJenis == 'PG' && jns == 'pg') ||
          (_selectedJenis == 'Essay' && jns == 'essay') ||
          (_selectedJenis == 'True/False' && (jns == 'tf' || jns == 'true/false'));

      final qTitle = (s['judul_quiz'] ?? '').toString().toLowerCase();
      final pert = (s['pertanyaan'] ?? '').toString().toLowerCase();
      final matchesSearch = _searchQuery.isEmpty || qTitle.contains(_searchQuery) || pert.contains(_searchQuery);

      return matchesJenis && matchesSearch;
    }).toList();

    final totalPG = _soalList.where((e) => (e['jenis_soal'] ?? 'pg').toString().toLowerCase() == 'pg').length;
    final totalEssay = _soalList.length - totalPG;

    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      appBar: AppBar(
        title: const Text('Bank Soal CBT Guru', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddSoalModal,
        backgroundColor: Colors.purple.shade800,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_task_rounded),
        label: const Text('Tambah Soal', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: _loadSoalData,
        color: AppTheme.primaryColor,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 🚀 EXECUTIVE HERO BANNER CARD
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                gradient: AppTheme.guruGradient,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0F172A).withAlpha(60),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.amber,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.inventory_2_rounded, size: 14, color: Colors.black87),
                              SizedBox(width: 4),
                              Text(
                                'Repository Bank Soal',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.black87),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(30),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            '${_soalList.length} Total Soal',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Kelola Bank Soal CBT',
                      style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: -0.5),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Kelola pertanyaan, pilihan ganda, kunci jawaban, dan bobot skor secara presisi.',
                      style: TextStyle(fontSize: 12, color: Colors.white.withAlpha(200), height: 1.4),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // 📊 KPI STATS STRIP
              Row(
                children: [
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Total Soal',
                      value: '${_soalList.length} Soal',
                      icon: Icons.assignment_rounded,
                      iconColor: Colors.purple.shade700,
                      bgColor: Colors.purple.shade50,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Soal PG',
                      value: '$totalPG Soal',
                      icon: Icons.fact_check_rounded,
                      iconColor: Colors.teal.shade700,
                      bgColor: Colors.teal.shade50,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _buildKpiCard(
                      title: 'Essay / TF',
                      value: '$totalEssay Soal',
                      icon: Icons.edit_note_rounded,
                      iconColor: Colors.blue.shade700,
                      bgColor: Colors.blue.shade50,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // 🎛️ QUIZ DROPDOWN FILTER & SEARCH BAR
              if (quizList.isNotEmpty) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.grey.shade300),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<int>(
                      value: _selectedQuizId,
                      isExpanded: true,
                      icon: const Icon(Icons.keyboard_arrow_down_rounded),
                      items: [
                        const DropdownMenuItem<int>(
                          value: 0,
                          child: Row(
                            children: [
                              Icon(Icons.collections_bookmark_rounded, size: 18, color: AppTheme.primaryColor),
                              SizedBox(width: 8),
                              Text('Semua Quiz CBT', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                            ],
                          ),
                        ),
                        ...quizList.map((q) {
                          return DropdownMenuItem<int>(
                            value: q.id,
                            child: Text(
                              '${q.judul} (${q.namaMapel})',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedQuizId = val;
                          });
                          _loadSoalData();
                        }
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 12),
              ],

              // 🔍 SEARCH INPUT
              TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Cari pertanyaan soal atau judul kuis...',
                  prefixIcon: const Icon(Icons.search_rounded),
                  suffixIcon: _searchQuery.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear_rounded),
                          onPressed: () => _searchController.clear(),
                        )
                      : null,
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                ),
              ),
              const SizedBox(height: 12),

              // 🏷️ TYPE FILTER CHIPS
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: _jenisList.map((jns) {
                    final isSel = _selectedJenis == jns;
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        selected: isSel,
                        label: Text(jns),
                        labelStyle: TextStyle(
                          fontSize: 12,
                          fontWeight: isSel ? FontWeight.bold : FontWeight.w500,
                          color: isSel ? Colors.white : Colors.black87,
                        ),
                        selectedColor: Colors.purple.shade800,
                        backgroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                          side: BorderSide(color: isSel ? Colors.purple.shade800 : Colors.grey.shade300),
                        ),
                        onSelected: (selected) {
                          setState(() {
                            _selectedJenis = jns;
                          });
                        },
                      ),
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 16),

              // 📑 SOAL LIST CARDS
              if (_isLoading)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 40),
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (filteredSoal.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Column(
                      children: [
                        Icon(Icons.assignment_late_outlined, size: 54, color: Colors.grey.shade400),
                        const SizedBox(height: 12),
                        const Text(
                          'Belum Ada Soal di Bank Soal',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _searchQuery.isNotEmpty
                              ? 'Tidak ditemukan soal sesuai pencarian "$_searchQuery".'
                              : 'Klik "+ Tambah Soal" di bawah untuk mengisi bank soal.',
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                )
              else
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: filteredSoal.length,
                  itemBuilder: (context, index) {
                    final s = filteredSoal[index];
                    final soalId = int.parse(s['id'].toString());
                    final rawJenis = (s['jenis_soal'] ?? 'pg').toString().toLowerCase();
                    final isTF = rawJenis == 'tf' || rawJenis == 'true/false';
                    final isEssay = rawJenis == 'essay';

                    final jenisLabel = isTF
                        ? '⚖️ Benar/Salah'
                        : (isEssay ? '✍️ Essay' : '📝 Pilihan Ganda');
                    final jenisBgColor = isTF
                        ? Colors.purple.shade50
                        : (isEssay ? Colors.orange.shade50 : Colors.blue.shade50);
                    final jenisTextColor = isTF
                        ? Colors.purple.shade800
                        : (isEssay ? Colors.orange.shade900 : Colors.blue.shade800);
                    final accentColor = isTF
                        ? Colors.purple.shade700
                        : (isEssay ? Colors.orange.shade700 : Colors.indigo.shade700);

                    final quizTitle = s['judul_quiz'] ?? 'Ujian CBT';
                    final mapelName = s['nama_mapel'] ?? '';
                    final bobot = s['bobot'] ?? 10;
                    final List<dynamic> pilihans = s['pilihan'] is List ? s['pilihan'] : [];

                    // Gambar URL
                    final imageUrl = _getQuestionImageUrl(s);

                    return Container(
                      margin: const EdgeInsets.only(bottom: 14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withAlpha(8),
                            blurRadius: 12,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Accent top bar
                            Container(
                              height: 4,
                              width: double.infinity,
                              color: accentColor,
                            ),

                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Header Badges Row (Overflow-safe)
                                  Row(
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
                                                color: Colors.purple.shade50,
                                                borderRadius: BorderRadius.circular(20),
                                              ),
                                              child: Text(
                                                'Soal #${index + 1}',
                                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.purple.shade800),
                                              ),
                                            ),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                              decoration: BoxDecoration(
                                                color: jenisBgColor,
                                                borderRadius: BorderRadius.circular(20),
                                              ),
                                              child: Text(
                                                jenisLabel,
                                                style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: jenisTextColor),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: Colors.amber.shade50,
                                              borderRadius: BorderRadius.circular(20),
                                              border: Border.all(color: Colors.amber.shade300),
                                            ),
                                            child: Text(
                                              '$bobot Poin',
                                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                            ),
                                          ),
                                          const SizedBox(width: 2),
                                          IconButton(
                                            icon: Icon(Icons.edit_note_rounded, color: Colors.blue.shade700, size: 22),
                                            onPressed: () => _openEditSoalModal(s),
                                            visualDensity: VisualDensity.compact,
                                            tooltip: 'Edit Soal',
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 20),
                                            onPressed: () => _deleteSoal(soalId),
                                            visualDensity: VisualDensity.compact,
                                            tooltip: 'Hapus Soal',
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Quiz Title & Mapel Tag
                                  Text(
                                    "$quizTitle ${mapelName.isNotEmpty ? '• $mapelName' : ''}",
                                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                                  ),
                                  const SizedBox(height: 6),

                                  // Pertanyaan
                                  Text(
                                    _stripHtml(s['pertanyaan'] ?? ''),
                                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.black87, height: 1.4),
                                  ),

                                   // Optional Image Display
                                   if (imageUrl != null && imageUrl.isNotEmpty) ...[
                                     const SizedBox(height: 12),
                                     GestureDetector(
                                       onTap: () => _previewImage(imageUrl),
                                       child: Stack(
                                         children: [
                                           ClipRRect(
                                             borderRadius: BorderRadius.circular(12),
                                             child: Container(
                                               decoration: BoxDecoration(
                                                 border: Border.all(color: Colors.grey.shade200),
                                                 borderRadius: BorderRadius.circular(12),
                                               ),
                                               child: Image.network(
                                                 imageUrl,
                                                 height: 170,
                                                 width: double.infinity,
                                                 fit: BoxFit.cover,
                                                 loadingBuilder: (ctx, child, progress) {
                                                   if (progress == null) return child;
                                                   return Container(
                                                     height: 170,
                                                     color: Colors.grey.shade50,
                                                     child: const Center(
                                                       child: CircularProgressIndicator(strokeWidth: 2),
                                                     ),
                                                   );
                                                 },
                                                 errorBuilder: (ctx, err, stack) => Container(
                                                   height: 60,
                                                   color: Colors.grey.shade100,
                                                   alignment: Alignment.center,
                                                   child: const Row(
                                                     mainAxisAlignment: MainAxisAlignment.center,
                                                     children: [
                                                       Icon(Icons.broken_image_rounded, size: 16, color: Colors.grey),
                                                       SizedBox(width: 6),
                                                       Text('Gagal memuat gambar soal', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                                     ],
                                                   ),
                                                 ),
                                               ),
                                             ),
                                           ),
                                           Positioned(
                                             top: 8,
                                             right: 8,
                                             child: Container(
                                               padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                               decoration: BoxDecoration(
                                                 color: Colors.black.withAlpha(165),
                                                 borderRadius: BorderRadius.circular(20),
                                               ),
                                               child: const Row(
                                                 mainAxisSize: MainAxisSize.min,
                                                 children: [
                                                   Icon(Icons.zoom_in_rounded, color: Colors.white, size: 14),
                                                   SizedBox(width: 4),
                                                   Text('Perbesar', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                                                 ],
                                               ),
                                             ),
                                           ),
                                         ],
                                       ),
                                     ),
                                   ],
                                  const SizedBox(height: 12),

                                  // Options List for PG / TF
                                  if (pilihans.isNotEmpty) ...[
                                    Text(
                                      isTF ? 'Opsi Pernyataan & Kunci:' : 'Pilihan Jawaban & Kunci:',
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey),
                                    ),
                                    const SizedBox(height: 6),
                                    Column(
                                      children: pilihans.asMap().entries.map((entry) {
                                        final idx = entry.key;
                                        final opt = entry.value;
                                        final optLabel = String.fromCharCode(65 + idx); // A, B, C, D
                                        final isBenar = (opt['is_benar'] ?? 0) == 1 || (opt['is_benar'] ?? false) == true;
                                        final teks = opt['teks_pilihan'] ?? opt['teks'] ?? '';

                                        return Container(
                                          margin: const EdgeInsets.only(bottom: 6),
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: isBenar ? const Color(0xFFECFDF5) : Colors.grey.shade50,
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(
                                              color: isBenar ? const Color(0xFF10B981) : Colors.grey.shade200,
                                              width: isBenar ? 1.5 : 1.0,
                                            ),
                                          ),
                                          child: Row(
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(
                                                  color: isBenar ? const Color(0xFF10B981) : Colors.grey.shade300,
                                                  borderRadius: BorderRadius.circular(8),
                                                ),
                                                child: Text(
                                                  optLabel,
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 11,
                                                    color: isBenar ? Colors.white : Colors.black87,
                                                  ),
                                                ),
                                              ),
                                              const SizedBox(width: 8),
                                              Expanded(
                                                child: Text(
                                                  teks,
                                                  style: TextStyle(
                                                    fontSize: 12,
                                                    fontWeight: isBenar ? FontWeight.bold : FontWeight.normal,
                                                    color: isBenar ? const Color(0xFF064E3B) : Colors.black87,
                                                  ),
                                                ),
                                              ),
                                              if (isBenar)
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: const Color(0xFF10B981),
                                                    borderRadius: BorderRadius.circular(6),
                                                  ),
                                                  child: const Row(
                                                    mainAxisSize: MainAxisSize.min,
                                                    children: [
                                                      Icon(Icons.check, size: 12, color: Colors.white),
                                                      SizedBox(width: 2),
                                                      Text('KUNCI BENAR', style: TextStyle(fontSize: 9, color: Colors.white, fontWeight: FontWeight.bold)),
                                                    ],
                                                  ),
                                                ),
                                            ],
                                          ),
                                        );
                                      }).toList(),
                                    ),
                                  ] else if (isEssay) ...[
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                      decoration: BoxDecoration(
                                        color: Colors.orange.shade50,
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: Colors.orange.shade200),
                                      ),
                                      child: Row(
                                        children: [
                                          Icon(Icons.edit_note_rounded, size: 18, color: Colors.orange.shade800),
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: Text(
                                              'Soal Uraian / Essay: Jawaban diisi deskriptif oleh siswa dan dinilai mandiri oleh guru.',
                                              style: TextStyle(fontSize: 11, color: Colors.orange.shade900, height: 1.3),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 10, offset: const Offset(0, 2)),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  value,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
