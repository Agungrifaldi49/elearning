import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class GuruBankSoalScreen extends StatefulWidget {
  const GuruBankSoalScreen({super.key});

  @override
  State<GuruBankSoalScreen> createState() => _GuruBankSoalScreenState();
}

class _GuruBankSoalScreenState extends State<GuruBankSoalScreen> {
  final _pertanyaanController = TextEditingController();
  final _bobotController = TextEditingController(text: '10');
  bool _isSubmitting = false;

  @override
  void dispose() {
    _pertanyaanController.dispose();
    _bobotController.dispose();
    super.dispose();
  }

  Future<void> _submitSoal() async {
    final pt = _pertanyaanController.text.trim();
    if (pt.isEmpty) return;

    setState(() => _isSubmitting = true);
    final res = await ApiService.post('guru/bank_soal', {
      'quiz_id': 1,
      'pertanyaan': pt,
      'bobot': int.tryParse(_bobotController.text.trim()) ?? 10,
    });

    if (mounted) {
      setState(() => _isSubmitting = false);
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Soal berhasil disimpan!'), backgroundColor: Colors.teal),
        );
        _pertanyaanController.clear();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Gagal menyimpan soal'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bank Soal CBT Guru'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Tambah Soal Baru',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.teal),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _pertanyaanController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Pertanyaan / Teks Soal',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _bobotController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Bobot Nilai (Default: 10)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _isSubmitting ? null : _submitSoal,
                icon: _isSubmitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.add_task),
                label: const Text('Simpan ke Bank Soal'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
