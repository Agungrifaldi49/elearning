import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';

class KartuDigitalScreen extends StatelessWidget {
  const KartuDigitalScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.currentUser;
    final details = user?.details;
    final isSiswa = user?.isSiswa ?? true;

    final nama = user?.fullName.isNotEmpty == true
        ? user!.fullName
        : (details?['nama_lengkap'] ?? 'Pengguna');
    final nomorId = isSiswa
        ? (details?['nis'] ?? user?.username ?? 'NIS-001')
        : (details?['nip'] ?? user?.username ?? 'NIP-001');
    final subTitle = isSiswa
        ? '${details?['nama_kelas'] ?? 'Kelas'} • ${details?['nama_jurusan'] ?? 'SMK'}'
        : (details?['email'] ?? user?.email ?? 'Guru E-Learning');

    return Scaffold(
      appBar: AppBar(
        title: Text(isSiswa ? 'Kartu Pelajar Digital' : 'Kartu Guru Digital'),
        backgroundColor: isSiswa ? Colors.indigo : Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            // Virtual ID Card
            Card(
              elevation: 8,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
              ),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(20),
                  gradient: LinearGradient(
                    colors: isSiswa
                        ? [Colors.indigo.shade800, Colors.blue.shade600]
                        : [Colors.teal.shade800, Colors.teal.shade500],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.school_rounded, color: Colors.white, size: 28),
                            SizedBox(width: 8),
                            Text(
                              'SMK MUTHIA HARAPAN',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                letterSpacing: 1,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            isSiswa ? 'KARTU PELAJAR' : 'KARTU GURU',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Avatar
                    CircleAvatar(
                      radius: 40,
                      backgroundColor: Colors.white,
                      child: CircleAvatar(
                        radius: 37,
                        backgroundColor: Colors.grey.shade200,
                        child: Icon(
                          isSiswa ? Icons.person_rounded : Icons.person_pin_rounded,
                          size: 48,
                          color: isSiswa ? Colors.indigo : Colors.teal,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    Text(
                      nama,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subTitle,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.9),
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        'ID: $nomorId',
                        style: TextStyle(
                          color: isSiswa ? Colors.indigo.shade900 : Colors.teal.shade900,
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Visual QR Code Simulation Container
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Icon(
                            Icons.qr_code_2_rounded,
                            size: 110,
                            color: Colors.grey.shade900,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Scan QR untuk Presensi / Verifikasi',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey.shade700,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
