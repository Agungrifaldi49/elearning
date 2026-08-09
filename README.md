# E-Learning SMK Muthia Harapan Cicalengka

Website E-Learning & Learning Management System (LMS) Modern, Production Ready, & Fully Responsive untuk **SMK Muthia Harapan Cicalengka**.

---

## 🚀 Teknologi Utama

- **Backend**: PHP Native 8.x (Standard OOP MVC Architecture)
- **Database**: MySQL 8.x / MariaDB (Engine: InnoDB, Collation: UTF8MB4)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), jQuery, Bootstrap 5.3
- **Plugins & Libraries**:
  - **Chart.js** (Grafik & Analytics Dashboard)
  - **DataTables** (Responsive Server & Client-side Tables)
  - **SweetAlert2** (Notifikasi Flash & Dialog Modern)
  - **Bootstrap Icons** (Sistem Ikonografi UI)
  - **CBT Engine** (Engine Ujian Online dengan Timer, Anti-cheat & Fullscreen guard)
  - **AJAX Polling Chat** (Pesan Realtime 1-on-1)

---

## 📁 Struktur Folder Project

```text
elearning-smkmh/
├── assets/
│   ├── css/
│   │   └── style.css            # Styling khusus, variable warna & dark mode
│   ├── js/
│   │   ├── main.js              # Script utama & DataTables init
│   │   ├── cbt.js               # Engine Ujian CBT
│   │   └── chat.js              # AJAX Realtime Chat
│   └── uploads/
│       ├── materi/              # Storage file PDF, Doc, PPT, Video
│       ├── video/
│       ├── tugas/               # File tugas & jawaban siswa
│       ├── profile/             # Foto profil
│       └── sertifikat/
├── config/
│   ├── app.php                  # Konfigurasi global & constants
│   └── database.php             # PDO Database Singleton & Auto-import
├── controllers/
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── GuruController.php
│   ├── SiswaController.php
│   ├── KepsekController.php
│   ├── LandingController.php
│   ├── ChatController.php
│   └── ForumController.php
├── database/
│   ├── schema.sql               # 32 Tabel MySQL InnoDB dengan Foreign Keys
│   └── seeders.sql              # Data awal demo & akun pengguna
├── helpers/
│   ├── AuthHelper.php
│   ├── CaptchaHelper.php
│   ├── FlashHelper.php
│   ├── PdfHelper.php
│   ├── Security.php
│   └── UploadHelper.php
├── middleware/
│   ├── AuthMiddleware.php
│   ├── CSRFMiddleware.php
│   └── RoleMiddleware.php
├── models/
│   ├── AcademicModel.php
│   ├── AbsensiModel.php
│   ├── BaseModel.php
│   ├── CommunicationModel.php
│   ├── ExamModel.php
│   ├── GuruModel.php
│   ├── LearningModel.php
│   ├── ReportModel.php
│   ├── SiswaModel.php
│   └── UserModel.php
├── views/
│   ├── admin/
│   ├── auth/
│   ├── chat/
│   ├── forum/
│   ├── guru/
│   ├── kepsek/
│   ├── landing/
│   ├── layouts/
│   └── siswa/
├── .htaccess
├── index.php                    # Front Controller & Routing
├── login.php                    # Entrypoint Login
├── logout.php                   # Entrypoint Logout
└── README.md
```

---

## 🔑 Akun Demo Pengujian (Quick Access)

Gunakan akun demo berikut untuk menguji 4 Role yang tersedia:

| Role | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Kelola User, Guru, Siswa, Kelas, Jurusan, Backup DB |
| **Guru** | `guru` | `guru123` | Upload Materi, Tugas, Buat Quiz & CBT, Rekap Absensi |
| **Siswa** | `siswa` | `siswa123` | Akses Materi, Kerjakan Tugas, Ikut CBT, Lihat Nilai |
| **Kepala Sekolah** | `kepsek` | `kepsek123` | Monitoring Guru & Siswa, Analytics, Cetak PDF Report |

---

## 🛠️ Panduan Instalasi di XAMPP

1. **Salin Folder Project**:
   Salin folder project ke direktori `htdocs` XAMPP Anda:
   `c:\xampp\htdocs\Elearning_Mhc\` (atau `elearning-smkmh`)

2. **Jalankan Apache & MySQL**:
   Buka **XAMPP Control Panel** dan klik **Start** pada modul **Apache** dan **MySQL**.

3. **Import Database**:
   - Buka browser dan akses `http://localhost/phpmyadmin/`.
   - Buat database baru dengan nama `db_elearning_smkmh`.
   - Import file `database/schema.sql` terlebih dahulu.
   - Import file `database/seeders.sql` untuk memasukkan data awal.
   *(Catatan: Aplikasi ini juga dilengkapi fitur Auto-Import otomatis jika database belum terdeteksi).*

4. **Akses Aplikasi**:
   Buka browser (Chrome / Edge / Firefox) dan akses:
   - **Landing Page**: `http://localhost/Elearning_Mhc/`
   - **Halaman Login**: `http://localhost/Elearning_Mhc/login.php`

---

## 🔒 Fitur Keamanan Production Ready

- **SQL Injection Prevention**: Seluruh query database menggunakan PDO Prepared Statements.
- **XSS Protection**: Sanitasi otomatis pada input form menggunakan `htmlspecialchars` dan sanitizer helper.
- **CSRF Protection**: Token acak unik `csrf_token` pada setiap form POST dan request AJAX.
- **Session Timeout**: Otomatis logout jika user tidak aktif selama 30 menit.
- **Login Rate Limiting**: Batas maksimal 5 kali percobaan login gagal sebelum akun terkunci sementara.
- **Math Captcha**: Verifikasi penjumlahan/perkalian matematika interaktif pada halaman login.

---

## 📄 Panduan Deploy ke Hosting Production

1. Compress seluruh isi folder `c:\xampp\htdocs\Elearning_Mhc` menjadi file `.zip`.
2. Upload dan Extract file `.zip` ke `public_html` hosting cPanel / Plesk Anda.
3. Buat database MySQL di hosting dan import `database/schema.sql` serta `database/seeders.sql`.
4. Sesuaikan kredensial database pada file `config/database.php` (host, dbname, username, password).
5. Pastikan modul `mod_rewrite` Apache telah aktif untuk penanganan URL `.htaccess`.
