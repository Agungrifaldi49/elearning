import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/materi_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../services/file_service.dart';
import '../../theme/app_theme.dart';

class GuruMateriTab extends StatefulWidget {
  const GuruMateriTab({super.key});

  @override
  State<GuruMateriTab> createState() => _GuruMateriTabState();
}

class _GuruMateriTabState extends State<GuruMateriTab> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedTypeFilter = 'semua'; // 'semua', 'pdf', 'video'
  String _selectedMapel = 'Semua';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      final guruProvider = Provider.of<GuruProvider>(context, listen: false);
      guruProvider.fetchMateri(user.id);
      if (guruProvider.mapelList.isEmpty || guruProvider.kelasList.isEmpty) {
        guruProvider.fetchTugas(user.id);
      }
    }
  }

  void _showMateriDetailModal(MateriModel m) {
    final isVideo = m.jenisFile == 'video' || (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty);
    final fileUrl = (m.filePath != null && m.filePath!.startsWith('http'))
        ? m.filePath!
        : ApiService.getFileUrl('assets/uploads/materi/${m.filePath ?? ''}');
    final targetOpenUrl = (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty)
        ? m.youtubeUrl!
        : fileUrl;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            top: 24,
            left: 20,
            right: 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Drag Handle
              Center(
                child: Container(
                  width: 44,
                  height: 5,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Title Header Box
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      gradient: isVideo
                          ? LinearGradient(colors: [Colors.red.shade600, Colors.red.shade900])
                          : LinearGradient(colors: [Colors.blue.shade600, Colors.blue.shade900]),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: (isVideo ? Colors.red : Colors.blue).withAlpha(60),
                          blurRadius: 8,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Icon(
                      isVideo ? Icons.play_circle_fill_rounded : Icons.picture_as_pdf_rounded,
                      color: Colors.white,
                      size: 28,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryColor.withAlpha(25),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            m.namaMapel,
                            style: const TextStyle(
                              fontSize: 11,
                              color: AppTheme.primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          m.judul,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, height: 1.2),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Meta Details Grid
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          const Icon(Icons.class_rounded, size: 16, color: Colors.blueGrey),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              "Kelas: ${m.namaKelas ?? 'Semua Kelas'}",
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today_rounded, size: 16, color: Colors.blueGrey),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              m.createdAt.length >= 10 ? m.createdAt.substring(0, 10) : m.createdAt,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              // Keterangan / Ringkasan
              const Text(
                '📝 Keterangan & Modul Ringkasan:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              const SizedBox(height: 6),
              Container(
                width: double.infinity,
                constraints: const BoxConstraints(maxHeight: 140),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: SingleChildScrollView(
                  child: SelectableText(
                    m.deskripsi.isEmpty ? 'Tidak ada keterangan tambahan.' : m.deskripsi,
                    style: TextStyle(color: Colors.grey.shade900, height: 1.4, fontSize: 13),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Action Buttons
              if (isVideo) ...[
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: () => FileService.openFileOrUrl(context, targetOpenUrl),
                    icon: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 22),
                    label: const Text(
                      'Tonton Video Learning (YouTube)',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red.shade700,
                      foregroundColor: Colors.white,
                      elevation: 2,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
              ],

              if (m.filePath != null && m.filePath!.isNotEmpty) ...[
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          FileService.showInAppPreview(
                            context,
                            fileUrl,
                            m.filePath ?? 'Dokumen Materi PDF',
                          );
                        },
                        icon: const Icon(Icons.visibility_rounded, size: 18),
                        label: const Text('Lihat di App'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primaryColor,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => FileService.openFileOrUrl(context, fileUrl, preferInApp: false),
                        icon: const Icon(Icons.download_rounded, size: 18),
                        label: const Text('Unduh File'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.primaryColor,
                          side: const BorderSide(color: AppTheme.primaryColor),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  void _showAddMateriModal() {
    final guruProvider = Provider.of<GuruProvider>(context, listen: false);
    final mapelList = guruProvider.mapelList;
    final kelasList = guruProvider.kelasList;

    final judulController = TextEditingController();
    final deskripsiController = TextEditingController();
    final mediaController = TextEditingController();

    int selectedMapel = mapelList.isNotEmpty ? (int.tryParse(mapelList[0]['id'].toString()) ?? 1) : 1;
    int selectedKelas = kelasList.isNotEmpty ? (int.tryParse(kelasList[0]['id'].toString()) ?? 1) : 1;
    String jenisFile = 'pdf';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          return Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
            ),
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              top: 24,
              left: 20,
              right: 20,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 44,
                      height: 5,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withAlpha(25),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.post_add_rounded, color: AppTheme.primaryColor, size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Text(
                          'Upload Materi Pembelajaran',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),

                  // Judul
                  TextField(
                    controller: judulController,
                    decoration: InputDecoration(
                      labelText: 'Judul Modul / Materi',
                      prefixIcon: const Icon(Icons.title_rounded),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Dropdown Mapel & Kelas
                  Row(
                    children: [
                      Expanded(
                        child: DropdownButtonFormField<int>(
                          initialValue: selectedMapel,
                          decoration: InputDecoration(
                            labelText: 'Mata Pelajaran',
                            prefixIcon: const Icon(Icons.book_rounded, size: 20),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                          ),
                          isExpanded: true,
                          items: mapelList.map<DropdownMenuItem<int>>((m) {
                            final idVal = int.tryParse(m['id'].toString()) ?? 1;
                            final nameVal = m['nama_mapel'] ?? m['nama'] ?? 'Mapel';
                            return DropdownMenuItem<int>(
                              value: idVal,
                              child: Text(nameVal, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12)),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) setModalState(() => selectedMapel = val);
                          },
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: DropdownButtonFormField<int>(
                          initialValue: selectedKelas,
                          decoration: InputDecoration(
                            labelText: 'Target Kelas',
                            prefixIcon: const Icon(Icons.class_rounded, size: 20),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                          ),
                          isExpanded: true,
                          items: kelasList.map<DropdownMenuItem<int>>((k) {
                            final idVal = int.tryParse(k['id'].toString()) ?? 1;
                            final nameVal = k['nama_kelas'] ?? k['nama'] ?? 'Kelas';
                            return DropdownMenuItem<int>(
                              value: idVal,
                              child: Text(nameVal, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12)),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) setModalState(() => selectedKelas = val);
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Segmented Type Selector
                  Row(
                    children: [
                      Expanded(
                        child: ChoiceChip(
                          label: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.picture_as_pdf_rounded, size: 16),
                              SizedBox(width: 6),
                              Text('Dokumen PDF'),
                            ],
                          ),
                          selected: jenisFile == 'pdf',
                          onSelected: (sel) {
                            if (sel) setModalState(() => jenisFile = 'pdf');
                          },
                          selectedColor: Colors.blue.shade100,
                          labelStyle: TextStyle(
                            color: jenisFile == 'pdf' ? Colors.blue.shade900 : Colors.grey.shade700,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ChoiceChip(
                          label: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.play_circle_fill_rounded, size: 16),
                              SizedBox(width: 6),
                              Text('Video YouTube'),
                            ],
                          ),
                          selected: jenisFile == 'video',
                          onSelected: (sel) {
                            if (sel) setModalState(() => jenisFile = 'video');
                          },
                          selectedColor: Colors.red.shade100,
                          labelStyle: TextStyle(
                            color: jenisFile == 'video' ? Colors.red.shade900 : Colors.grey.shade700,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Media Link / File Path
                  TextField(
                    controller: mediaController,
                    decoration: InputDecoration(
                      labelText: jenisFile == 'video' ? 'Link YouTube / URL Video' : 'File Path / Link Drive Berkas',
                      hintText: jenisFile == 'video' ? 'https://youtube.com/watch?v=...' : 'assets/uploads/materi/modul.pdf',
                      prefixIcon: Icon(jenisFile == 'video' ? Icons.ondemand_video_rounded : Icons.attachment_rounded),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Deskripsi
                  TextField(
                    controller: deskripsiController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      labelText: 'Deskripsi / Ringkasan Modul',
                      prefixIcon: const Icon(Icons.description_rounded),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  const SizedBox(height: 20),

                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
                        final messenger = ScaffoldMessenger.of(context);
                        final nav = Navigator.of(context);
                        if (user != null) {
                          final ok = await Provider.of<GuruProvider>(context, listen: false).createMateri(
                            user.id,
                            judulController.text,
                            deskripsiController.text,
                            selectedMapel,
                            selectedKelas,
                            jenisFile,
                            mediaController.text,
                          );
                          nav.pop();
                          messenger.showSnackBar(
                            SnackBar(
                              content: Text(ok ? 'Materi berhasil diterbitkan!' : 'Gagal publish materi'),
                              backgroundColor: ok ? AppTheme.secondaryColor : Colors.red,
                            ),
                          );
                        }
                      },
                      icon: const Icon(Icons.send_rounded, size: 20),
                      label: const Text('Terbitkan Materi Modul', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final guruProvider = Provider.of<GuruProvider>(context);
    final allMateri = guruProvider.materiList;

    // Filter materi List
    final filteredMateri = allMateri.where((m) {
      final matchesSearch = _searchQuery.isEmpty ||
          m.judul.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          m.namaMapel.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (m.namaKelas ?? '').toLowerCase().contains(_searchQuery.toLowerCase());

      final isVideo = m.jenisFile == 'video' || (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty);
      bool matchesType = true;
      if (_selectedTypeFilter == 'pdf') matchesType = !isVideo;
      if (_selectedTypeFilter == 'video') matchesType = isVideo;

      bool matchesMapel = _selectedMapel == 'Semua' || m.namaMapel.trim().toLowerCase() == _selectedMapel.trim().toLowerCase();

      return matchesSearch && matchesType && matchesMapel;
    }).toList();

    // Stats
    final totalMateriCount = allMateri.length;
    int pdfCount = 0;
    int videoCount = 0;
    for (var m in allMateri) {
      if (m.jenisFile == 'video' || (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty)) {
        videoCount++;
      } else {
        pdfCount++;
      }
    }

    // Dynamic Mapels Filter
    final mapelSet = <String>{'Semua'};
    for (var m in allMateri) {
      if (m.namaMapel.isNotEmpty) mapelSet.add(m.namaMapel);
    }
    if (!mapelSet.contains(_selectedMapel)) {
      _selectedMapel = 'Semua';
    }

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      color: AppTheme.primaryColor,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 🚀 EXECUTIVE GLASSMORPHIC HERO BANNER
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B), Color(0xFF2563EB)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withAlpha(50),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(30),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white.withAlpha(40)),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.auto_stories_rounded, color: Colors.blueAccent, size: 14),
                              SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  'E-Learning Modul Ajar',
                                  style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton.icon(
                        onPressed: _showAddMateriModal,
                        icon: const Icon(Icons.add_rounded, size: 16),
                        label: const Text('Buat Modul', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue.shade600,
                          foregroundColor: Colors.white,
                          elevation: 4,
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    '📚 Ruang Materi & Modul Ajar',
                    style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Publikasikan e-book PDF, ringkasan KBM, dan video interaktif untuk siswa.',
                    style: TextStyle(color: Colors.blue.shade100, fontSize: 12),
                  ),
                  const SizedBox(height: 18),

                  // Stats Badges Row
                  Row(
                    children: [
                      Expanded(
                        child: _buildHeroStatBadge(
                          'Total Modul',
                          '$totalMateriCount',
                          Icons.folder_special_rounded,
                          Colors.lightBlueAccent,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _buildHeroStatBadge(
                          'Dokumen PDF',
                          '$pdfCount',
                          Icons.picture_as_pdf_rounded,
                          Colors.amberAccent,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _buildHeroStatBadge(
                          'Video Learn',
                          '$videoCount',
                          Icons.play_circle_fill_rounded,
                          Colors.redAccent,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Search Input Field
            TextField(
              controller: _searchController,
              onChanged: (val) => setState(() => _searchQuery = val),
              decoration: InputDecoration(
                hintText: 'Cari modul, judul, atau mapel...',
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
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
              ),
            ),
            const SizedBox(height: 12),

            // Type Filter Chips (Semua, PDF, Video)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildTypeChip('Semua Jenis', 'semua', Icons.grid_view_rounded),
                  const SizedBox(width: 8),
                  _buildTypeChip('📄 Berkas PDF', 'pdf', Icons.picture_as_pdf_rounded),
                  const SizedBox(width: 8),
                  _buildTypeChip('🎥 Video Learning', 'video', Icons.play_circle_fill_rounded),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // Mapel Filter Chips
            if (mapelSet.length > 1)
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: mapelSet.map((mName) {
                    final isSel = _selectedMapel == mName;
                    return Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: FilterChip(
                        selected: isSel,
                        label: Text(mName),
                        labelStyle: TextStyle(
                          fontSize: 11,
                          fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                          color: isSel ? Colors.blue.shade900 : Colors.grey.shade800,
                        ),
                        backgroundColor: Colors.grey.shade100,
                        selectedColor: Colors.blue.shade100,
                        onSelected: (val) {
                          setState(() => _selectedMapel = mName);
                        },
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    );
                  }).toList(),
                ),
              ),
            const SizedBox(height: 16),

            // List Header Count
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Daftar Modul Diterbitkan (${filteredMateri.length})',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                if (filteredMateri.length != allMateri.length)
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _searchController.clear();
                        _searchQuery = '';
                        _selectedTypeFilter = 'semua';
                        _selectedMapel = 'Semua';
                      });
                    },
                    child: const Text('Reset Filter', style: TextStyle(fontSize: 12)),
                  ),
              ],
            ),
            const SizedBox(height: 10),

            // Materi List Container
            guruProvider.isLoading
                ? const Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: Center(child: CircularProgressIndicator()),
                  )
                : filteredMateri.isEmpty
                    ? Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(32),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade50,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Column(
                          children: [
                            Icon(Icons.library_books_rounded, size: 48, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            Text(
                              'Belum Ada Modul Materi',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Klik "+ Buat Modul" di atas untuk mempublikasikan materi baru.',
                              style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                              textAlign: TextAlign.center,
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: filteredMateri.length,
                        itemBuilder: (context, index) {
                          final m = filteredMateri[index];
                          return _buildMateriCard(m);
                        },
                      ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroStatBadge(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
      decoration: BoxDecoration(
        color: Colors.white.withAlpha(20),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white.withAlpha(30)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(
            value,
            style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
          ),
          Text(
            label,
            style: TextStyle(color: Colors.blue.shade100, fontSize: 10),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildTypeChip(String label, String typeKey, IconData icon) {
    final isSelected = _selectedTypeFilter == typeKey;
    return ChoiceChip(
      selected: isSelected,
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: isSelected ? AppTheme.primaryColor : Colors.grey.shade600),
          const SizedBox(width: 6),
          Text(label),
        ],
      ),
      selectedColor: AppTheme.primaryColor.withAlpha(30),
      backgroundColor: Colors.grey.shade100,
      labelStyle: TextStyle(
        fontSize: 11,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        color: isSelected ? AppTheme.primaryColor : Colors.grey.shade800,
      ),
      onSelected: (sel) {
        if (sel) setState(() => _selectedTypeFilter = typeKey);
      },
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    );
  }

  Widget _buildMateriCard(MateriModel m) {
    final isVideo = m.jenisFile == 'video' || (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty);
    final fileUrl = (m.filePath != null && m.filePath!.startsWith('http'))
        ? m.filePath!
        : ApiService.getFileUrl('assets/uploads/materi/${m.filePath ?? ''}');
    final targetOpenUrl = (m.youtubeUrl != null && m.youtubeUrl!.isNotEmpty)
        ? m.youtubeUrl!
        : fileUrl;

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
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(20),
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () => _showMateriDetailModal(m),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Meta Row
                Row(
                  children: [
                    // Type Icon
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        gradient: isVideo
                            ? LinearGradient(colors: [Colors.red.shade600, Colors.red.shade800])
                            : LinearGradient(colors: [Colors.blue.shade600, Colors.blue.shade800]),
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: (isVideo ? Colors.red : Colors.blue).withAlpha(50),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Icon(
                        isVideo ? Icons.play_circle_fill_rounded : Icons.picture_as_pdf_rounded,
                        color: Colors.white,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),

                    // Mapel & Target Kelas
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Flexible(
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: AppTheme.primaryColor.withAlpha(20),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    m.namaMapel,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.primaryColor,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade100,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  m.namaKelas ?? 'Semua Kelas',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.grey.shade700,
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            m.judul,
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                // Description Preview
                if (m.deskripsi.isNotEmpty) ...[
                  Text(
                    m.deskripsi,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.3),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 12),
                ],

                const Divider(height: 1),
                const SizedBox(height: 10),

                // Interactive Action Buttons Row
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          if (isVideo) {
                            FileService.openFileOrUrl(context, targetOpenUrl);
                          } else {
                            FileService.showInAppPreview(
                              context,
                              targetOpenUrl,
                              m.filePath ?? 'Dokumen PDF',
                            );
                          }
                        },
                        icon: Icon(
                          isVideo ? Icons.play_arrow_rounded : Icons.visibility_rounded,
                          size: 14,
                        ),
                        label: Text(isVideo ? 'Tonton Video' : 'Lihat PDF'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isVideo ? Colors.red.shade700 : AppTheme.primaryColor,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 6),
                          textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                          visualDensity: VisualDensity.compact,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _showMateriDetailModal(m),
                        icon: const Icon(Icons.info_outline_rounded, size: 14),
                        label: const Text('Detail Modul'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.primaryColor,
                          side: const BorderSide(color: AppTheme.primaryColor),
                          padding: const EdgeInsets.symmetric(vertical: 6),
                          textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                          visualDensity: VisualDensity.compact,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
