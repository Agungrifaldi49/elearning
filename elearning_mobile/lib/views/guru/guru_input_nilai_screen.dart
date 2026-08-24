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

  @override
  void dispose() {
    _siswaIdController.dispose();
    _mapelIdController.dispose();
    _tugasController.dispose();
    _utsController.dispose();
    _uasController.dispose();
    super.dispose();
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
            content: Text(res['message'] ?? 'Nilai berhasil disimpan!'),
            backgroundColor: Colors.teal,
          ),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal menyimpan nilai'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Input & Edit Nilai Siswa'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Form Penilaian Siswa',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.teal,
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _siswaIdController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'ID Siswa',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.person),
                ),
                validator: (val) => val == null || val.isEmpty ? 'ID Siswa wajib diisi' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _mapelIdController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'ID Mata Pelajaran',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.book),
                ),
                validator: (val) => val == null || val.isEmpty ? 'ID Mapel wajib diisi' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _tugasController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Nilai Tugas (Bobot 30%)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.assignment),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nilai Tugas wajib diisi' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _utsController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Nilai UTS (Bobot 30%)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.description),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nilai UTS wajib diisi' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _uasController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Nilai UAS (Bobot 40%)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.grade),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nilai UAS wajib diisi' : null,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: _isSubmitting ? null : _submitNilai,
                  icon: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Icon(Icons.save_rounded),
                  label: const Text('Simpan Nilai Siswa', style: TextStyle(fontSize: 16)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.teal,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
