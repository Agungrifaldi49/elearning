import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class GabungKelasScreen extends StatefulWidget {
  const GabungKelasScreen({super.key});

  @override
  State<GabungKelasScreen> createState() => _GabungKelasScreenState();
}

class _GabungKelasScreenState extends State<GabungKelasScreen> {
  final _kodeController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _kodeController.dispose();
    super.dispose();
  }

  Future<void> _submitGabung() async {
    final kode = _kodeController.text.trim();
    if (kode.isEmpty) return;

    setState(() => _isSubmitting = true);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final res = await ApiService.post('siswa/gabung_kelas?user_id=$userId', {
      'kode_kelas': kode,
    });

    if (mounted) {
      setState(() => _isSubmitting = false);
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Berhasil bergabung!'), backgroundColor: Colors.indigo),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Kode kelas salah'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Gabung Kelas Baru'),
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Masukkan Kode Kelas',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.indigo),
            ),
            const SizedBox(height: 8),
            const Text(
              'Minta kode kelas dari bapak/ibu guru pengajar untuk bergabung ke kelas pembelajaran.',
              style: TextStyle(color: Colors.grey, fontSize: 13),
            ),
            const SizedBox(height: 20),
            TextField(
              controller: _kodeController,
              decoration: const InputDecoration(
                labelText: 'Kode Kelas (Contoh: KLS-XII-RPL)',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.key),
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _isSubmitting ? null : _submitGabung,
                icon: _isSubmitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.group_add),
                label: const Text('Bergabung ke Kelas'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.indigo,
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
