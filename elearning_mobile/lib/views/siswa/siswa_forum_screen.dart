import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../models/forum_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../services/profanity_service.dart';
import '../../theme/app_theme.dart';
import 'forum_detail_screen.dart';

class SiswaForumScreen extends StatefulWidget {
  const SiswaForumScreen({super.key});

  @override
  State<SiswaForumScreen> createState() => _SiswaForumScreenState();
}

class _SiswaForumScreenState extends State<SiswaForumScreen> {
  List<ForumModel> _topics = [];
  List<ForumModel> _filteredTopics = [];
  bool _isLoading = false;
  String _selectedFilter = 'semua'; // 'semua', 'public', 'private'

  Future<void> _pickTopicImage(
    BuildContext context,
    Function(Uint8List bytes, String filename) onImageSelected,
  ) async {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (bCtx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Pilih Sumber Foto/Gambar',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 14),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2563EB).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.photo_library_rounded, color: Color(0xFF2563EB)),
                ),
                title: const Text('Buka Galeri Foto', style: TextStyle(fontWeight: FontWeight.w600)),
                subtitle: const Text('Pilih foto dari galeri penyimpanan', style: TextStyle(fontSize: 12)),
                onTap: () async {
                  Navigator.pop(bCtx);
                  try {
                    final picker = ImagePicker();
                    final picked = await picker.pickImage(
                      source: ImageSource.gallery,
                      imageQuality: 70,
                      maxWidth: 1024,
                      maxHeight: 1024,
                    );
                    if (picked != null) {
                      final bytes = await picked.readAsBytes();
                      onImageSelected(bytes, picked.name);
                    }
                  } catch (e) {
                    debugPrint('Error picking gallery image: $e');
                  }
                },
              ),
              const SizedBox(height: 6),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF10B981).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.camera_alt_rounded, color: Color(0xFF10B981)),
                ),
                title: const Text('Ambil dari Kamera', style: TextStyle(fontWeight: FontWeight.w600)),
                subtitle: const Text('Ambil foto langsung melalui kamera', style: TextStyle(fontSize: 12)),
                onTap: () async {
                  Navigator.pop(bCtx);
                  try {
                    final picker = ImagePicker();
                    final picked = await picker.pickImage(
                      source: ImageSource.camera,
                      imageQuality: 70,
                      maxWidth: 1024,
                      maxHeight: 1024,
                    );
                    if (picked != null) {
                      final bytes = await picked.readAsBytes();
                      onImageSelected(bytes, picked.name);
                    }
                  } catch (e) {
                    debugPrint('Error picking camera image: $e');
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showImageViewer(BuildContext context, String imageUrl) {
    final cleanUrl = ApiService.getFileUrl(imageUrl);
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.black.withValues(alpha: 0.95),
        insetPadding: EdgeInsets.zero,
        child: Stack(
          children: [
            Center(
              child: InteractiveViewer(
                child: Image.network(
                  cleanUrl,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.broken_image_rounded, color: Colors.white70, size: 60),
                      SizedBox(height: 8),
                      Text('Gagal memuat gambar', style: TextStyle(color: Colors.white70)),
                    ],
                  ),
                ),
              ),
            ),
            Positioned(
              top: 40,
              right: 20,
              child: IconButton(
                icon: const Icon(Icons.close_rounded, color: Colors.white, size: 30),
                onPressed: () => Navigator.pop(context),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    _loadForum();
  }

  Future<void> _loadForum() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    setState(() => _isLoading = true);

    final params = user != null ? {'user_id': user.id.toString()} : null;
    final res = await ApiService.get('forum/list', params: params);

    if (mounted) {
      List<ForumModel> list = [];
      if (res['success'] == true && res['data'] is List) {
        list = (res['data'] as List).map((e) => ForumModel.fromJson(e)).toList();
      }

      // Fallback topic if server list is empty to ensure UI is never blank
      if (list.isEmpty) {
        list = [
          ForumModel(
            id: 1,
            userId: user?.id ?? 1,
            judul: 'Selamat Datang di Forum Komunitas SMK Muthia Harapan Cicalengka',
            konten: 'Media diskusi resmi KBM, absensi QR Code, jadwal pelajaran, dan CBT Online SMK Muthia Harapan Cicalengka. Ketuk tombol + Topik Baru di kanan bawah untuk memulai diskusi!',
            kategori: 'Umum',
            visibility: 'public',
            targetNamaKelas: 'Semua Kelas',
            fullName: (user?.fullName.isNotEmpty == true) ? user!.fullName : 'Admin E-Learning',
            avatar: 'default_avatar.png',
            avatarUrl: user?.fullAvatarUrl,
            roleName: (user?.roleName.isNotEmpty == true) ? user!.roleName : 'Admin',
            totalKomentar: 0,
            createdAt: 'Baru Saja',
          )
        ];
      }

      setState(() {
        _topics = list;
        _applyFilter();
        _isLoading = false;
      });
    }
  }

  void _applyFilter() {
    final filter = _selectedFilter.toLowerCase().trim();
    if (filter == 'public') {
      _filteredTopics = _topics.where((t) => t.visibility.toLowerCase().trim() != 'private').toList();
    } else if (filter == 'private') {
      _filteredTopics = _topics.where((t) => t.visibility.toLowerCase().trim() == 'private').toList();
    } else {
      _filteredTopics = List.from(_topics);
    }
  }

  void _showNewTopicDialog() {
    final judulController = TextEditingController();
    final kontenController = TextEditingController();
    String visibility = 'public';
    String kategori = 'Umum';
    Uint8List? topicImageBytes;
    String? topicImageName;
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) => StatefulBuilder(
        builder: (sheetContext, setSheetState) {
          final isDark = Theme.of(sheetContext).brightness == Brightness.dark;

          return Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(sheetContext).size.height * 0.92,
            ),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.3),
                  blurRadius: 20,
                  offset: const Offset(0, -5),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Drag handle
                Center(
                  child: Container(
                    margin: const EdgeInsets.only(top: 10, bottom: 6),
                    width: 44,
                    height: 5,
                    decoration: BoxDecoration(
                      color: isDark ? Colors.white24 : Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),

                // Header
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.add_comment_rounded, color: AppTheme.primaryColor, size: 22),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Buat Topik Diskusi Baru',
                              style: TextStyle(
                                fontSize: 16.5,
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : const Color(0xFF0F172A),
                              ),
                            ),
                            Text(
                              'Mulai diskusi terbuka atau khusus kelas',
                              style: TextStyle(
                                fontSize: 11.5,
                                color: isDark ? Colors.grey.shade400 : Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        color: isDark ? Colors.white70 : Colors.grey.shade600,
                        onPressed: isSubmitting ? null : () => Navigator.pop(sheetContext),
                      ),
                    ],
                  ),
                ),

                const Divider(height: 1),

                // Scrollable Form Content (with viewInsets bottom padding to completely eliminate overflow)
                Expanded(
                  child: SingleChildScrollView(
                    padding: EdgeInsets.fromLTRB(
                      20,
                      16,
                      20,
                      MediaQuery.of(sheetContext).viewInsets.bottom + 24,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // 1. Visibility selector
                        Text(
                          'Akses Keterbukaan Diskusi',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Expanded(
                              child: InkWell(
                                onTap: () => setSheetState(() => visibility = 'public'),
                                borderRadius: BorderRadius.circular(12),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                                  decoration: BoxDecoration(
                                    color: visibility == 'public'
                                        ? (isDark ? const Color(0xFF064E3B).withValues(alpha: 0.35) : const Color(0xFFD1FAE5))
                                        : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(
                                      color: visibility == 'public'
                                          ? const Color(0xFF10B981)
                                          : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                                      width: visibility == 'public' ? 1.8 : 1,
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.public_rounded,
                                        size: 16,
                                        color: visibility == 'public' ? const Color(0xFF059669) : Colors.grey,
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        '🌐 Publik (Semua)',
                                        style: TextStyle(
                                          color: visibility == 'public'
                                              ? (isDark ? const Color(0xFFA7F3D0) : const Color(0xFF047857))
                                              : (isDark ? Colors.grey.shade400 : Colors.grey.shade700),
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: InkWell(
                                onTap: () => setSheetState(() => visibility = 'private'),
                                borderRadius: BorderRadius.circular(12),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                                  decoration: BoxDecoration(
                                    color: visibility == 'private'
                                        ? (isDark ? const Color(0xFF78350F).withValues(alpha: 0.35) : const Color(0xFFFEF3C7))
                                        : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(
                                      color: visibility == 'private'
                                          ? const Color(0xFFF59E0B)
                                          : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                                      width: visibility == 'private' ? 1.8 : 1,
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.lock_rounded,
                                        size: 16,
                                        color: visibility == 'private' ? const Color(0xFFD97706) : Colors.grey,
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        '🔒 Kelas Saya',
                                        style: TextStyle(
                                          color: visibility == 'private'
                                              ? (isDark ? const Color(0xFFFDE68A) : const Color(0xFFB45309))
                                              : (isDark ? Colors.grey.shade400 : Colors.grey.shade700),
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 16),

                        // 2. Kategori Diskusi
                        Text(
                          'Kategori Diskusi',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569),
                          ),
                        ),
                        const SizedBox(height: 6),
                        DropdownButtonFormField<String>(
                          initialValue: kategori,
                          dropdownColor: isDark ? const Color(0xFF1E293B) : Colors.white,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: const BorderSide(color: AppTheme.primaryColor, width: 1.8),
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            filled: true,
                            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                          ),
                          items: ['Umum', 'Tanya Jawab KBM', 'Diskusi Tugas', 'Pengumuman Kelas', 'Ujian & CBT']
                              .map((cat) => DropdownMenuItem(
                                    value: cat,
                                    child: Text(cat, style: const TextStyle(fontSize: 13)),
                                  ))
                              .toList(),
                          onChanged: (val) {
                            if (val != null) setSheetState(() => kategori = val);
                          },
                        ),

                        const SizedBox(height: 16),

                        // 3. Judul Topik Diskusi
                        Text(
                          'Judul Pertanyaan / Diskusi',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569),
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextField(
                          controller: judulController,
                          maxLength: 150,
                          style: TextStyle(
                            fontSize: 14,
                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                          ),
                          decoration: InputDecoration(
                            hintText: 'Misal: Bagaimana cara menyelesaikan soal matriks no 3?',
                            hintStyle: TextStyle(
                              color: isDark ? Colors.grey.shade500 : Colors.grey.shade400,
                              fontSize: 13,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: const BorderSide(color: AppTheme.primaryColor, width: 1.8),
                            ),
                            filled: true,
                            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            counterText: '',
                          ),
                        ),

                        const SizedBox(height: 16),

                        // 4. Isi Diskusi Detail
                        Text(
                          'Isi Pertanyaan / Penjelasan Lengkap',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569),
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextField(
                          controller: kontenController,
                          minLines: 3,
                          maxLines: 7,
                          style: TextStyle(
                            fontSize: 13.5,
                            height: 1.4,
                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                          ),
                          decoration: InputDecoration(
                            hintText: 'Tuliskan detail pertanyaan, kendala, atau pembahasan yang ingin didiskusikan...',
                            hintStyle: TextStyle(
                              color: isDark ? Colors.grey.shade500 : Colors.grey.shade400,
                              fontSize: 13,
                            ),
                            alignLabelWithHint: true,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: const BorderSide(color: AppTheme.primaryColor, width: 1.8),
                            ),
                            filled: true,
                            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                            contentPadding: const EdgeInsets.all(14),
                          ),
                        ),

                        const SizedBox(height: 16),

                        // 5. Lampiran Gambar (Opsional)
                        Text(
                          'Lampiran Foto / Gambar (Opsional)',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569),
                          ),
                        ),
                        const SizedBox(height: 8),

                        if (topicImageBytes != null) ...[
                          Container(
                            decoration: BoxDecoration(
                              color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                              ),
                            ),
                            child: Stack(
                              children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(16),
                                  child: Image.memory(
                                    topicImageBytes!,
                                    height: 160,
                                    width: double.infinity,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => Container(
                                      height: 120,
                                      color: Colors.grey.shade200,
                                      child: const Center(
                                        child: Text('Gagal menampilkan preview', style: TextStyle(color: Colors.grey)),
                                      ),
                                    ),
                                  ),
                                ),
                                Positioned(
                                  top: 8,
                                  right: 8,
                                  child: Material(
                                    color: Colors.black.withValues(alpha: 0.7),
                                    shape: const CircleBorder(),
                                    child: InkWell(
                                      customBorder: const CircleBorder(),
                                      onTap: () => setSheetState(() {
                                        topicImageBytes = null;
                                        topicImageName = null;
                                      }),
                                      child: const Padding(
                                        padding: EdgeInsets.all(6),
                                        child: Icon(Icons.close_rounded, color: Colors.white, size: 18),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 8),
                          OutlinedButton.icon(
                            onPressed: isSubmitting
                                ? null
                                : () => _pickTopicImage(sheetContext, (bytes, name) {
                                      setSheetState(() {
                                        topicImageBytes = bytes;
                                        topicImageName = name;
                                      });
                                    }),
                            icon: const Icon(Icons.edit_rounded, size: 16),
                            label: const Text('Ganti Gambar'),
                            style: OutlinedButton.styleFrom(
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              minimumSize: const Size(double.infinity, 40),
                            ),
                          ),
                        ] else ...[
                          InkWell(
                            onTap: isSubmitting
                                ? null
                                : () => _pickTopicImage(sheetContext, (bytes, name) {
                                      setSheetState(() {
                                        topicImageBytes = bytes;
                                        topicImageName = name;
                                      });
                                    }),
                            borderRadius: BorderRadius.circular(14),
                            child: Container(
                              width: double.infinity,
                              padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(
                                  color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                              ),
                              child: Column(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.add_photo_alternate_rounded, size: 26, color: AppTheme.primaryColor),
                                  ),
                                  const SizedBox(height: 8),
                                  const Text(
                                    'Lampirkan Foto atau Gambar Soal',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Mendukung Kamera & Galeri (JPG, PNG)',
                                    style: TextStyle(fontSize: 11, color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],

                        const SizedBox(height: 24),

                        // 6. Submit Button
                        Container(
                          width: double.infinity,
                          height: 48,
                          decoration: BoxDecoration(
                            gradient: isSubmitting
                                ? LinearGradient(colors: [Colors.grey.shade400, Colors.grey.shade500])
                                : const LinearGradient(
                                    colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                            borderRadius: BorderRadius.circular(14),
                            boxShadow: isSubmitting
                                ? []
                                : [
                                    BoxShadow(
                                      color: const Color(0xFF2563EB).withValues(alpha: 0.35),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                          ),
                          child: Material(
                            color: Colors.transparent,
                            child: InkWell(
                              onTap: isSubmitting
                                  ? null
                                  : () async {
                                      final user = Provider.of<AuthProvider>(sheetContext, listen: false).currentUser;
                                      final judul = judulController.text.trim();
                                      final konten = kontenController.text.trim();

                                      if (user == null) {
                                        ScaffoldMessenger.of(sheetContext).showSnackBar(
                                          const SnackBar(content: Text('Silakan login terlebih dahulu!'), backgroundColor: Colors.orange),
                                        );
                                        return;
                                      }

                                      if (judul.isEmpty || konten.isEmpty) {
                                        ScaffoldMessenger.of(sheetContext).showSnackBar(
                                          const SnackBar(content: Text('Judul dan isi pertanyaan wajib diisi!'), backgroundColor: Colors.orange),
                                        );
                                        return;
                                      }

                                      setSheetState(() => isSubmitting = true);

                                      try {
                                        String? base64Image;
                                        if (topicImageBytes != null) {
                                          final ext = topicImageName?.split('.').last.toLowerCase() ?? 'jpg';
                                          base64Image = 'data:image/$ext;base64,${base64Encode(topicImageBytes!)}';
                                        }

                                        final bodyData = <String, dynamic>{
                                          'user_id': user.id,
                                          'judul': judul,
                                          'konten': konten,
                                          'kategori': kategori,
                                          'visibility': visibility,
                                        };
                                        if (base64Image != null) {
                                          bodyData['gambar_base64'] = base64Image;
                                        }

                                        final res = await ApiService.post('forum/create', bodyData);

                                        if (res['success'] == true) {
                                          if (sheetContext.mounted) {
                                            Navigator.pop(sheetContext);
                                          }
                                          if (mounted) {
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              SnackBar(
                                                content: Text(res['message'] ?? 'Topik diskusi berhasil diterbitkan!'),
                                                backgroundColor: const Color(0xFF10B981),
                                              ),
                                            );
                                            await _loadForum();
                                          }
                                        } else {
                                          if (sheetContext.mounted) {
                                            setSheetState(() => isSubmitting = false);
                                            ScaffoldMessenger.of(sheetContext).showSnackBar(
                                              SnackBar(
                                                content: Text(res['message'] ?? 'Gagal menerbitkan topik diskusi.'),
                                                backgroundColor: Colors.red,
                                              ),
                                            );
                                          }
                                        }
                                      } catch (e) {
                                        if (sheetContext.mounted) {
                                          setSheetState(() => isSubmitting = false);
                                          ScaffoldMessenger.of(sheetContext).showSnackBar(
                                            SnackBar(
                                              content: Text('Terjadi kesalahan: $e'),
                                              backgroundColor: Colors.red,
                                            ),
                                          );
                                        }
                                      }
                                    },
                              borderRadius: BorderRadius.circular(14),
                              child: Center(
                                child: isSubmitting
                                    ? const Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          SizedBox(
                                            width: 20,
                                            height: 20,
                                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                          ),
                                          SizedBox(width: 10),
                                          Text(
                                            'Menerbitkan Diskusi...',
                                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                                          ),
                                        ],
                                      )
                                    : const Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.send_rounded, color: Colors.white, size: 18),
                                          SizedBox(width: 8),
                                          Text(
                                            'Terbitkan Diskusi Sekarang',
                                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                                          ),
                                        ],
                                      ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  void _showForumDetailBottomSheet(ForumModel forum) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ForumDetailScreen(
          forum: forum,
          onCommentAdded: _loadForum,
        ),
      ),
    );
  }

  void _confirmDeleteTopic(ForumModel forum) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.delete_forever_rounded, color: Colors.red, size: 24),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'Hapus Diskusi?',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Apakah Anda yakin ingin menghapus diskusi ini?',
              style: TextStyle(
                fontSize: 13.5,
                color: isDark ? Colors.grey.shade300 : Colors.grey.shade800,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
              ),
              child: Text(
                '"${forum.judul}"',
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 12.5,
                  fontStyle: FontStyle.italic,
                  color: isDark ? Colors.grey.shade300 : Colors.grey.shade700,
                ),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Topik serta seluruh komentar tanggapan di dalamnya akan dihapus secara permanen dari forum.',
              style: TextStyle(fontSize: 12, color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: Text(
              'Batal',
              style: TextStyle(
                color: isDark ? Colors.grey.shade400 : Colors.grey.shade600,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(dialogCtx);
              await _deleteTopic(forum);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              elevation: 0,
            ),
            child: const Text('Hapus Sekarang', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Future<void> _deleteTopic(ForumModel forum) async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    final scaffoldMessenger = ScaffoldMessenger.of(context);

    // Optimistic delete
    setState(() {
      _topics.removeWhere((t) => t.id == forum.id);
      _applyFilter();
    });

    try {
      final res = await ApiService.post('forum/delete', {
        'user_id': user.id,
        'forum_id': forum.id,
      });

      if (res['success'] == true) {
        scaffoldMessenger.showSnackBar(
          const SnackBar(
            content: Text('Diskusi berhasil dihapus.'),
            backgroundColor: Color(0xFF10B981),
          ),
        );
      } else {
        scaffoldMessenger.showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal menghapus diskusi'),
            backgroundColor: Colors.red,
          ),
        );
        _loadForum();
      }
    } catch (e) {
      scaffoldMessenger.showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
      _loadForum();
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final currentUser = Provider.of<AuthProvider>(context).currentUser;
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Forum Diskusi Komunitas',
          style: TextStyle(
            color: isDark ? Colors.white : const Color(0xFF0F172A),
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 1,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showNewTopicDialog,
        backgroundColor: AppTheme.primaryColor,
        icon: const Icon(Icons.add_comment_rounded, color: Colors.white),
        label: const Text('Topik Baru', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: _loadForum,
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                border: Border(
                  bottom: BorderSide(
                    color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                    width: 1,
                  ),
                ),
              ),
              child: Row(
                children: [
                  Text(
                    'Filter Akses:',
                    style: TextStyle(
                      color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569),
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip('Semua Topik', 'semua'),
                          const SizedBox(width: 8),
                          _buildFilterChip('🌐 Public', 'public'),
                          const SizedBox(width: 8),
                          _buildFilterChip('🔒 Kelas Saya', 'private'),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredTopics.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.forum_outlined, size: 64, color: Colors.indigo.shade200),
                              const SizedBox(height: 12),
                              const Text('Belum ada topik diskusi pada kategori ini.', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                          itemCount: _filteredTopics.length,
                          itemBuilder: (context, index) {
                            final f = _filteredTopics[index];
                            final bool isPrivate = f.visibility.toLowerCase().trim() == 'private';
                            final String roleLower = f.roleName.toLowerCase();
                            Color roleColor = Colors.green;
                            if (roleLower.contains('admin')) roleColor = Colors.purple;
                            if (roleLower.contains('guru')) roleColor = Colors.blue;

                            return Container(
                              margin: const EdgeInsets.only(bottom: 16),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.05),
                                    blurRadius: 14,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: Material(
                                color: Colors.transparent,
                                borderRadius: BorderRadius.circular(20),
                                child: InkWell(
                                  onTap: () => _showForumDetailBottomSheet(f),
                                  borderRadius: BorderRadius.circular(20),
                                  child: Padding(
                                    padding: const EdgeInsets.all(18),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Container(
                                              padding: const EdgeInsets.all(2),
                                              decoration: BoxDecoration(
                                                shape: BoxShape.circle,
                                                gradient: LinearGradient(
                                                  colors: [roleColor.withValues(alpha: 0.8), roleColor],
                                                ),
                                              ),
                                              child: CircleAvatar(
                                                radius: 18,
                                                backgroundColor: Colors.white,
                                                backgroundImage: (f.avatarUrl != null && f.avatarUrl!.isNotEmpty)
                                                    ? NetworkImage(f.avatarUrl!)
                                                    : null,
                                                child: (f.avatarUrl == null || f.avatarUrl!.isEmpty)
                                                    ? Text(
                                                        f.fullName.isNotEmpty ? f.fullName[0].toUpperCase() : 'U',
                                                        style: TextStyle(color: roleColor, fontWeight: FontWeight.bold, fontSize: 14),
                                                      )
                                                    : null,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                children: [
                                                  Text(
                                                    f.fullName,
                                                    style: TextStyle(
                                                      fontWeight: FontWeight.bold,
                                                      fontSize: 14,
                                                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                                                    ),
                                                  ),
                                                  const SizedBox(height: 2),
                                                  Text(
                                                    "${f.roleName} • ${f.createdAt}",
                                                    style: TextStyle(
                                                      fontSize: 11,
                                                      color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                            Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                                  decoration: BoxDecoration(
                                                    color: isPrivate
                                                        ? (isDark ? const Color(0xFF78350F).withValues(alpha: 0.3) : const Color(0xFFFEF3C7))
                                                        : (isDark ? const Color(0xFF064E3B).withValues(alpha: 0.3) : const Color(0xFFD1FAE5)),
                                                    borderRadius: BorderRadius.circular(20),
                                                    border: Border.all(
                                                      color: isPrivate
                                                          ? (isDark ? const Color(0xFFD97706) : const Color(0xFFFBBF24))
                                                          : (isDark ? const Color(0xFF059669) : const Color(0xFF34D399)),
                                                      width: 0.8,
                                                    ),
                                                  ),
                                                  child: Text(
                                                    isPrivate ? '🔒 ${f.targetNamaKelas}' : '🌐 Public',
                                                    style: TextStyle(
                                                      color: isPrivate
                                                          ? (isDark ? const Color(0xFFFDE68A) : const Color(0xFFB45309))
                                                          : (isDark ? const Color(0xFFA7F3D0) : const Color(0xFF047857)),
                                                      fontSize: 11,
                                                      fontWeight: FontWeight.bold,
                                                    ),
                                                  ),
                                                ),
                                                if (currentUser != null && (currentUser.id == f.userId || currentUser.roleName.toLowerCase().contains('admin'))) ...[
                                                  const SizedBox(width: 4),
                                                  Theme(
                                                    data: Theme.of(context).copyWith(
                                                      highlightColor: Colors.transparent,
                                                      splashColor: Colors.transparent,
                                                    ),
                                                    child: PopupMenuButton<String>(
                                                      icon: Icon(
                                                        Icons.more_vert_rounded,
                                                        size: 20,
                                                        color: isDark ? Colors.grey.shade400 : Colors.grey.shade600,
                                                      ),
                                                      padding: EdgeInsets.zero,
                                                      constraints: const BoxConstraints(),
                                                      tooltip: 'Opsi Diskusi',
                                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                                      color: isDark ? const Color(0xFF1E293B) : Colors.white,
                                                      elevation: 4,
                                                      onSelected: (val) {
                                                        if (val == 'delete') {
                                                          _confirmDeleteTopic(f);
                                                        }
                                                      },
                                                      itemBuilder: (ctx) => [
                                                        PopupMenuItem(
                                                          value: 'delete',
                                                          child: Row(
                                                            children: [
                                                              Container(
                                                                padding: const EdgeInsets.all(6),
                                                                decoration: BoxDecoration(
                                                                  color: Colors.red.withValues(alpha: 0.1),
                                                                  shape: BoxShape.circle,
                                                                ),
                                                                child: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 18),
                                                              ),
                                                              const SizedBox(width: 10),
                                                              const Text(
                                                                'Hapus Diskusi',
                                                                style: TextStyle(
                                                                  color: Colors.red,
                                                                  fontWeight: FontWeight.bold,
                                                                  fontSize: 13,
                                                                ),
                                                              ),
                                                            ],
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 14),
                                        Text(
                                          ProfanityService.filter(f.judul),
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 15.5,
                                            height: 1.3,
                                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                                          ),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          ProfanityService.filter(f.konten),
                                          maxLines: 3,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            fontSize: 13,
                                            color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF475569),
                                            height: 1.45,
                                          ),
                                        ),
                                        if (f.gambarUrl != null && f.gambarUrl!.isNotEmpty) ...[
                                          const SizedBox(height: 12),
                                          GestureDetector(
                                            onTap: () => _showImageViewer(context, f.gambarUrl!),
                                            child: Stack(
                                              alignment: Alignment.bottomRight,
                                              children: [
                                                ClipRRect(
                                                  borderRadius: BorderRadius.circular(16),
                                                  child: Container(
                                                    constraints: const BoxConstraints(maxHeight: 220),
                                                    width: double.infinity,
                                                    decoration: BoxDecoration(
                                                      color: Colors.grey.shade100,
                                                      borderRadius: BorderRadius.circular(16),
                                                      border: Border.all(color: Colors.grey.shade200),
                                                    ),
                                                    child: Image.network(
                                                      f.gambarUrl!,
                                                      fit: BoxFit.contain,
                                                      errorBuilder: (_, __, ___) => const Padding(
                                                        padding: EdgeInsets.all(20),
                                                        child: Row(
                                                          mainAxisAlignment: MainAxisAlignment.center,
                                                          children: [
                                                            Icon(Icons.image_not_supported_outlined, color: Colors.grey),
                                                            SizedBox(width: 8),
                                                            Text('Gagal memuat gambar', style: TextStyle(color: Colors.grey, fontSize: 12)),
                                                          ],
                                                        ),
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                                Container(
                                                  margin: const EdgeInsets.all(10),
                                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                                  decoration: BoxDecoration(
                                                    color: Colors.black.withValues(alpha: 0.75),
                                                    borderRadius: BorderRadius.circular(20),
                                                  ),
                                                  child: const Row(
                                                    mainAxisSize: MainAxisSize.min,
                                                    children: [
                                                      Icon(Icons.zoom_in, color: Colors.white, size: 14),
                                                      SizedBox(width: 4),
                                                      Text('Tap perbesar', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                                                    ],
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                        const SizedBox(height: 14),
                                        Container(
                                          padding: const EdgeInsets.only(top: 14),
                                          decoration: BoxDecoration(
                                            border: Border(
                                              top: BorderSide(
                                                color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                                                width: 1,
                                              ),
                                            ),
                                          ),
                                          child: Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4.5),
                                                decoration: BoxDecoration(
                                                  color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                                                  borderRadius: BorderRadius.circular(8),
                                                  border: Border.all(
                                                    color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                                                    width: 0.8,
                                                  ),
                                                ),
                                                child: Text(
                                                  '🏷️ ${f.kategori}',
                                                  style: TextStyle(
                                                    color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.w600,
                                                  ),
                                                ),
                                              ),
                                              Row(
                                                children: [
                                                  Row(
                                                    children: [
                                                      Icon(
                                                        Icons.chat_bubble_outline_rounded,
                                                        size: 14,
                                                        color: isDark ? Colors.white60 : const Color(0xFF64748B),
                                                      ),
                                                      const SizedBox(width: 5),
                                                      Text(
                                                        '${f.totalKomentar} Balasan',
                                                        style: TextStyle(
                                                          fontSize: 11.5,
                                                          color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF475569),
                                                          fontWeight: FontWeight.w600,
                                                        ),
                                                      ),
                                                    ],
                                                  ),
                                                  const SizedBox(width: 10),
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                                    decoration: BoxDecoration(
                                                      gradient: const LinearGradient(
                                                        colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
                                                      ),
                                                      borderRadius: BorderRadius.circular(10),
                                                      boxShadow: [
                                                        BoxShadow(
                                                          color: const Color(0xFF2563EB).withValues(alpha: 0.3),
                                                          blurRadius: 6,
                                                          offset: const Offset(0, 2),
                                                        ),
                                                      ],
                                                    ),
                                                    child: const Row(
                                                      mainAxisSize: MainAxisSize.min,
                                                      children: [
                                                        Icon(Icons.reply_rounded, size: 14, color: Colors.white),
                                                        SizedBox(width: 5),
                                                        Text(
                                                          'Tanggapi',
                                                          style: TextStyle(
                                                            color: Colors.white,
                                                            fontSize: 11.5,
                                                            fontWeight: FontWeight.bold,
                                                          ),
                                                        ),
                                                      ],
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
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppTheme.primaryColor,
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
      elevation: isSelected ? 2 : 0,
      pressElevation: 0,
      side: BorderSide(
        color: isSelected
            ? AppTheme.primaryColor
            : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
        width: 1,
      ),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      labelStyle: TextStyle(
        color: isSelected
            ? Colors.white
            : (isDark ? const Color(0xFFE2E8F0) : const Color(0xFF334155)),
        fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
        fontSize: 12,
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedFilter = value;
            _applyFilter();
          });
        }
      },
    );
  }
}
