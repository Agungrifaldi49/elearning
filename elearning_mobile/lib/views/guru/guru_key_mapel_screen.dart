import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../theme/app_theme.dart';

class GuruKeyMapelScreen extends StatefulWidget {
  const GuruKeyMapelScreen({super.key});

  @override
  State<GuruKeyMapelScreen> createState() => _GuruKeyMapelScreenState();
}

class _GuruKeyMapelScreenState extends State<GuruKeyMapelScreen> {
  bool _isLoading = true;
  List<dynamic> _keysList = [];
  List<dynamic> _filteredKeys = [];
  List<dynamic> _mapelList = [];
  List<dynamic> _classList = [];

  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadKeyMapelData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadKeyMapelData() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final data = await Provider.of<GuruProvider>(context, listen: false).fetchKeyMapelData(user.id);

    if (mounted) {
      setState(() {
        _keysList = data['keys'] as List? ?? [];
        _mapelList = data['mapel_list'] as List? ?? [];
        _classList = data['classes'] as List? ?? [];
        _applySearchFilter();
        _isLoading = false;
      });
    }
  }

  void _applySearchFilter() {
    final query = _searchController.text.trim().toLowerCase();
    if (query.isEmpty) {
      setState(() => _filteredKeys = List.from(_keysList));
      return;
    }

    setState(() {
      _filteredKeys = _keysList.where((k) {
        final mapel = (k['nama_mapel'] ?? '').toString().toLowerCase();
        final keyStr = (k['enrollment_key'] ?? k['passcode'] ?? '').toString().toLowerCase();
        final kelas = (k['nama_kelas'] ?? '').toString().toLowerCase();
        return mapel.contains(query) || keyStr.contains(query) || kelas.contains(query);
      }).toList();
    });
  }

  String _generateRandomKey(String mapelName) {
    final rand = Random();
    String prefix = 'MPL';
    if (mapelName.trim().isNotEmpty) {
      final words = mapelName.trim().split(' ');
      if (words.length >= 2) {
        prefix = '${words[0][0]}${words[1][0]}'.toUpperCase();
      } else if (mapelName.length >= 3) {
        prefix = mapelName.substring(0, 3).toUpperCase();
      }
    }
    final num = rand.nextInt(900) + 100;
    return '$prefix-$num';
  }

  void _showEditKeyModal({Map<String, dynamic>? item}) {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    int? selectedMapelId = item != null ? int.tryParse((item['mapel_id'] ?? 0).toString()) : (_mapelList.isNotEmpty ? int.tryParse((_mapelList[0]['id'] ?? 0).toString()) : null);
    int? selectedKelasId = item != null ? int.tryParse((item['kelas_id'] ?? 0).toString()) : null;

    final keyController = TextEditingController(
      text: (item != null ? (item['enrollment_key'] ?? item['passcode'] ?? '') : '').toString(),
    );

    bool isSaving = false;

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
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Icon(
                        item != null ? Icons.edit_note_rounded : Icons.key_rounded,
                        color: AppTheme.primaryColor,
                        size: 26,
                      ),
                      const SizedBox(width: 10),
                      Text(
                        item != null ? 'Perbarui Key Mapel' : 'Buat Key Mapel Baru',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Mapel Selector Dropdown
                  const Text('Mata Pelajaran', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int>(
                        value: selectedMapelId,
                        isExpanded: true,
                        hint: const Text('Pilih Mata Pelajaran'),
                        items: _mapelList.map((m) {
                          final mid = int.parse((m['id'] ?? 0).toString());
                          final mname = (m['nama_mapel'] ?? 'Mapel').toString();
                          return DropdownMenuItem<int>(
                            value: mid,
                            child: Text(mname),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            setModalState(() {
                              selectedMapelId = val;
                              if (keyController.text.isEmpty) {
                                final selectedMapelObj = _mapelList.firstWhere((element) => int.parse((element['id'] ?? 0).toString()) == val, orElse: () => {});
                                keyController.text = _generateRandomKey((selectedMapelObj['nama_mapel'] ?? '').toString());
                              }
                            });
                          }
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Kelas Selector Dropdown (Optional)
                  const Text('Kelas / Rombel (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int?>(
                        value: selectedKelasId,
                        isExpanded: true,
                        hint: const Text('Semua Kelas'),
                        items: [
                          const DropdownMenuItem<int?>(
                            value: null,
                            child: Text('Semua Kelas'),
                          ),
                          ..._classList.map((c) {
                            final cid = int.parse((c['id'] ?? 0).toString());
                            final cname = (c['nama_kelas'] ?? 'Kelas').toString();
                            return DropdownMenuItem<int?>(
                              value: cid,
                              child: Text(cname),
                            );
                          }),
                        ],
                        onChanged: (val) {
                          setModalState(() {
                            selectedKelasId = val;
                          });
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Key Code Input & Generate Button Row
                  const Text('Kode Key Mapel (Passcode)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: keyController,
                          textCapitalization: TextCapitalization.characters,
                          style: const TextStyle(fontWeight: FontWeight.bold, letterSpacing: 1.5),
                          decoration: InputDecoration(
                            hintText: 'Contoh: MAT-101',
                            filled: true,
                            fillColor: Colors.grey.shade50,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.grey.shade300),
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton.icon(
                        onPressed: () {
                          final mObj = _mapelList.firstWhere((e) => int.parse((e['id'] ?? 0).toString()) == selectedMapelId, orElse: () => {});
                          final newKey = _generateRandomKey((mObj['nama_mapel'] ?? 'MPL').toString());
                          setModalState(() {
                            keyController.text = newKey;
                          });
                        },
                        icon: const Icon(Icons.casino_rounded, size: 16),
                        label: const Text('Acak', style: TextStyle(fontSize: 12)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.amber.shade800,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Submit Save Button
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: isSaving
                          ? null
                          : () async {
                              final keyText = keyController.text.trim();
                              if (selectedMapelId == null || selectedMapelId! <= 0) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Pilih mata pelajaran terlebih dahulu.')),
                                );
                                return;
                              }
                              if (keyText.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Kode Key Mapel tidak boleh kosong.')),
                                );
                                return;
                              }

                              setModalState(() => isSaving = true);

                              final ok = await Provider.of<GuruProvider>(context, listen: false).updateKeyMapel(
                                user.id,
                                selectedMapelId!,
                                keyText,
                                kelasId: selectedKelasId,
                              );

                              if (!context.mounted) return;

                              Navigator.pop(context);

                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(ok
                                      ? 'Kode Key Mapel berhasil diperbarui!'
                                      : 'Gagal memperbarui Key Mapel.'),
                                  backgroundColor: ok ? AppTheme.primaryColor : Colors.red,
                                ),
                              );

                              if (ok) {
                                _loadKeyMapelData();
                              }
                            },
                      icon: isSaving
                          ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Icon(Icons.check_circle_rounded, color: Colors.white),
                      label: Text(
                        isSaving ? 'Menyimpan Key...' : 'Simpan Key Mapel',
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kode Key Mapel Kelas Virtual'),
        backgroundColor: Colors.amber.shade900,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Muat Ulang Key Mapel',
            onPressed: _loadKeyMapelData,
          ),
        ],
      ),
      body: Column(
        children: [
          // Header Hero Banner
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Colors.amber.shade900, Colors.orange.shade800],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(24),
                bottomRight: Radius.circular(24),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.key_rounded, color: Colors.white, size: 22),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        '🔑 Kode Key Mapel Pembelajaran',
                        style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                const Text(
                  'Berikan kode key mapel ini kepada siswa agar mereka terdaftar di mata pelajaran Anda & otomatis muncul di Input Absensi.',
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
                const SizedBox(height: 12),

                // Search Bar
                TextField(
                  controller: _searchController,
                  onChanged: (_) => _applySearchFilter(),
                  style: const TextStyle(color: Colors.white, fontSize: 13),
                  decoration: InputDecoration(
                    hintText: 'Cari Nama Mapel, Kode Key, atau Kelas...',
                    hintStyle: const TextStyle(color: Colors.white60),
                    prefixIcon: const Icon(Icons.search, color: Colors.white60),
                    filled: true,
                    fillColor: Colors.white.withValues(alpha: 0.18),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                ),
              ],
            ),
          ),

          // Main Content List
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadKeyMapelData,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredKeys.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.vpn_key_off_rounded, size: 54, color: Colors.grey.shade400),
                              const SizedBox(height: 12),
                              Text(
                                'Belum ada Kode Key Mapel.',
                                style: TextStyle(color: Colors.grey.shade600),
                              ),
                              const SizedBox(height: 14),
                              ElevatedButton.icon(
                                onPressed: () => _showEditKeyModal(),
                                icon: const Icon(Icons.add, color: Colors.white),
                                label: const Text('Buat Key Mapel Pertama'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.primaryColor,
                                  foregroundColor: Colors.white,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(14),
                          itemCount: _filteredKeys.length,
                          itemBuilder: (context, index) {
                            final item = _filteredKeys[index];
                            final mapelName = (item['nama_mapel'] ?? 'Mapel').toString();
                            final kodeMapel = (item['kode_mapel'] ?? '').toString();
                            final keyStr = (item['enrollment_key'] ?? item['passcode'] ?? '-').toString();
                            final className = (item['nama_kelas'] ?? 'Semua Kelas').toString();
                            final totalSiswa = int.parse((item['total_siswa'] ?? 0).toString());

                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              elevation: 2,
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: Colors.amber.shade50,
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(color: Colors.amber.shade200),
                                          ),
                                          child: Icon(Icons.class_rounded, color: Colors.amber.shade900, size: 24),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                mapelName,
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                'Kode: $kodeMapel • Target: $className',
                                                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                              ),
                                            ],
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: Colors.blue.shade50,
                                            border: Border.all(color: Colors.blue.shade200),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              const Icon(Icons.people_alt_rounded, size: 12, color: Colors.blue),
                                              const SizedBox(width: 4),
                                              Text(
                                                '$totalSiswa Siswa',
                                                style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 12),

                                    // Key Box with Copy Button
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                      decoration: BoxDecoration(
                                        color: Colors.grey.shade100,
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: Colors.grey.shade300),
                                      ),
                                      child: Row(
                                        children: [
                                          const Icon(Icons.key_rounded, size: 18, color: Colors.amber),
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: SelectableText(
                                              keyStr,
                                              style: TextStyle(
                                                fontSize: 15,
                                                fontWeight: FontWeight.bold,
                                                letterSpacing: 1.5,
                                                color: Colors.grey.shade900,
                                              ),
                                            ),
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.copy_rounded, size: 18, color: AppTheme.primaryColor),
                                            tooltip: 'Salin Kode Key Mapel',
                                            onPressed: () {
                                              Clipboard.setData(ClipboardData(text: keyStr));
                                              ScaffoldMessenger.of(context).showSnackBar(
                                                SnackBar(
                                                  content: Text('Kode Key Mapel "$keyStr" berhasil disalin!'),
                                                  duration: const Duration(seconds: 2),
                                                ),
                                              );
                                            },
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(height: 10),

                                    // Edit Key Button
                                    Align(
                                      alignment: Alignment.centerRight,
                                      child: TextButton.icon(
                                        onPressed: () => _showEditKeyModal(item: item),
                                        icon: const Icon(Icons.edit_rounded, size: 16),
                                        label: const Text('Perbarui Key', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                        style: TextButton.styleFrom(
                                          foregroundColor: Colors.amber.shade900,
                                          backgroundColor: Colors.amber.shade50,
                                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showEditKeyModal(),
        backgroundColor: Colors.amber.shade900,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Tambah / Perbarui Key', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
    );
  }
}
