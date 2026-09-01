import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class GuruInputNilaiScreen extends StatefulWidget {
  const GuruInputNilaiScreen({super.key});

  @override
  State<GuruInputNilaiScreen> createState() => _GuruInputNilaiScreenState();
}

class _GuruInputNilaiScreenState extends State<GuruInputNilaiScreen> {
  final _formKey = GlobalKey<FormState>();
  final _siswaIdController = TextEditingController();
  final _mapelIdController = TextEditingController();
  final _tugasController = TextEditingController();
  final _utsController = TextEditingController();
  final _uasController = TextEditingController();
  bool _isSubmitting = false;

  double _finalScore = 0.0;

  @override
  void initState() {
    super.initState();
    _tugasController.addListener(_calculateScore);
    _utsController.addListener(_calculateScore);
    _uasController.addListener(_calculateScore);
  }

  @override
  void dispose() {
    _siswaIdController.dispose();
    _mapelIdController.dispose();
    _tugasController.dispose();
    _utsController.dispose();
    _uasController.dispose();
    super.dispose();
  }

  void _calculateScore() {
    final t = double.tryParse(_tugasController.text.trim()) ?? 0.0;
    final u1 = double.tryParse(_utsController.text.trim()) ?? 0.0;
    final u2 = double.tryParse(_uasController.text.trim()) ?? 0.0;
    setState(() {
      _finalScore = (t * 0.3) + (u1 * 0.3) + (u2 * 0.4);
    });
  }

  String _getPredikat(double score) {
    if (score >= 85) return 'A (Sangat Baik)';
    if (score >= 75) return 'B (Baik)';
    if (score >= 65) return 'C (Cukup)';
    return 'D (Perlu Bimbingan)';
  }

  Color _getPredikatColor(double score) {
    if (score >= 85) return const Color(0xFF10B981);
    if (score >= 75) return Colors.blue;
    if (score >= 65) return Colors.amber.shade800;
    return Colors.red;
  }

  Future<void> _submitNilai() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final userId = auth.currentUser?.id ?? 0;

    final res = await ApiService.post('guru/input_nilai?user_id=$userId', {
      'siswa_id': int.tryParse(_siswaIdController.text.trim()) ?? 0,
      'mapel_id': int.tryParse(_mapelIdController.text.trim()) ?? 0,
      'nilai_tugas': double.tryParse(_tugasController.text.trim()) ?? 0,
      'nilai_uts': double.tryParse(_utsController.text.trim()) ?? 0,
      'nilai_uas': double.tryParse(_uasController.text.trim()) ?? 0,
    });

    if (mounted) {
      setState(() => _isSubmitting = false);
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Nilai siswa berhasil disimpan!'),
            backgroundColor: const Color(0xFF0D9488),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal menyimpan nilai siswa'),
            backgroundColor: Colors.red.shade700,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final predikatStr = _getPredikat(_finalScore);
    final predikatColor = _getPredikatColor(_finalScore);

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Input & Edit Nilai Siswa',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            Text(
              'Entri Penilaian Rapor & Evaluasi KBM',
              style: TextStyle(fontSize: 11, color: Colors.white70),
            ),
          ],
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF0F172A), Color(0xFF0D9488), Color(0xFF14B8A6)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Hero Header Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0D9488), Color(0xFF14B8A6), Color(0xFF2DD4BF)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0D9488).withValues(alpha: 0.3),
                      blurRadius: 15,
                      offset: const Offset(0, 6),
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
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.assessment_rounded, color: Colors.white, size: 14),
                              SizedBox(width: 6),
                              Text(
                                'FORM PENILAIAN RESMI',
                                style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                              ),
                            ],
                          ),
                        ),
                        const Icon(Icons.school_rounded, color: Colors.white, size: 28),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Input Nilai Akademik Siswa',
                      style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Masukkan bobot nilai Tugas (30%), UTS (30%), dan UAS (40%) untuk menghitung estimasi nilai akhir rapor secara otomatis.',
                      style: TextStyle(color: Colors.white80, fontSize: 12, height: 1.4),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Live Calculator Score Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: predikatColor.withValues(alpha: 0.3), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: predikatColor.withValues(alpha: 0.1),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(Icons.calculate_rounded, color: predikatColor, size: 30),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Kalkulasi Nilai Akhir (Estimasi)',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 2),
                          Row(
                            children: [
                              Text(
                                _finalScore.toStringAsFixed(1),
                                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: predikatColor),
                              ),
                              const SizedBox(width: 10),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: predikatColor.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  predikatStr,
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: predikatColor),
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
              const SizedBox(height: 20),

              const Text(
                'Data Target Siswa & Mapel',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
              ),
              const SizedBox(height: 10),

              // ID Siswa
              TextFormField(
                controller: _siswaIdController,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'ID Siswa / Target Siswa',
                  hintText: 'Contoh: 15',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  prefixIcon: const Icon(Icons.person_rounded, color: Color(0xFF0D9488)),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFF0D9488), width: 1.5),
                  ),
                ),
                validator: (val) => val == null || val.isEmpty ? 'ID Siswa wajib diisi' : null,
              ),
              const SizedBox(height: 12),

              // ID Mapel
              TextFormField(
                controller: _mapelIdController,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'ID Mata Pelajaran',
                  hintText: 'Contoh: 3',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  prefixIcon: const Icon(Icons.book_rounded, color: Color(0xFF0D9488)),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFF0D9488), width: 1.5),
                  ),
                ),
                validator: (val) => val == null || val.isEmpty ? 'ID Mapel wajib diisi' : null,
              ),
              const SizedBox(height: 20),

              const Text(
                'Komponen Penilaian (Skala 0 - 100)',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
              ),
              const SizedBox(height: 10),

              // Nilai Tugas
              TextFormField(
                controller: _tugasController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: 'Nilai Tugas (Bobot 30%)',
                  hintText: 'Masukkan angka 0 - 100',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  prefixIcon: const Icon(Icons.assignment_rounded, color: Colors.blue),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.blue, width: 1.5),
                  ),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nilai Tugas wajib diisi' : null,
              ),
              const SizedBox(height: 12),

              // Nilai UTS
              TextFormField(
                controller: _utsController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: 'Nilai UTS (Bobot 30%)',
                  hintText: 'Masukkan angka 0 - 100',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  prefixIcon: const Icon(Icons.description_rounded, color: Colors.indigo),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.indigo, width: 1.5),
                  ),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nilai UTS wajib diisi' : null,
              ),
              const SizedBox(height: 12),

              // Nilai UAS
              TextFormField(
                controller: _uasController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: 'Nilai UAS (Bobot 40%)',
                  hintText: 'Masukkan angka 0 - 100',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  prefixIcon: const Icon(Icons.grade_rounded, color: Colors.purple),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.purple, width: 1.5),
                  ),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nilai UAS wajib diisi' : null,
              ),
              const SizedBox(height: 28),

              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: _isSubmitting ? null : _submitNilai,
                  icon: _isSubmitting
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                        )
                      : const Icon(Icons.save_rounded, size: 22),
                  label: Text(
                    _isSubmitting ? 'Menyimpan Nilai...' : 'Simpan Nilai Siswa',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0D9488),
                    foregroundColor: Colors.white,
                    elevation: 4,
                    shadowColor: const Color(0xFF0D9488).withValues(alpha: 0.4),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}
