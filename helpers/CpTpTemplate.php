<?php
/**
 * Helper Repositori Template Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP)
 * Standar Kurikulum Merdeka (Keputusan BSKAP Kemendikbudristek) untuk SMK
 * E-Learning SMK Muthia Harapan Cicalengka
 */

class CpTpTemplate {

    /**
     * Mengembalikan seluruh paket template CP & TP terstruktur
     */
    public static function getAllTemplates() {
        return [
            // =========================================================================
            // 1. PEMROGRAMAN WEB DAN PERANGKAT BERGERAK (PPLG / RPL - FASE F)
            // =========================================================================
            [
                'id' => 'tpl_rpl_pwpb_f',
                'judul' => 'Pemrograman Web dan Perangkat Bergerak (Fase F - Kelas XI & XII)',
                'bidang' => 'Teknologi Informasi / Rekayasa Perangkat Lunak',
                'fase_kode' => 'F',
                'keywords' => ['web', 'bergerak', 'pemrograman web', 'mobile', 'pwpb', 'rpl', 'pplg'],
                'deskripsi_singkat' => 'Paket kurikulum lengkap client-side, server-side MVC, REST API, dan pengembangan aplikasi mobile.',
                'cp_items' => [
                    [
                        'elemen' => 'Pemrograman Web Sisi Klien (Client-Side)',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu menerapkan dan mengimplementasikan bahasa pemrograman sisi klien (HTML5, CSS3, JavaScript modern/ES6+, serta CSS Framework) untuk membangun antarmuka web yang responsif, interaktif, dinamis, serta memenuhi kaidah UX/UI dan standar W3C.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Struktur Semantik HTML5 & Responsive Layout CSS',
                                'deskripsi' => "1. Menganalisis elemen semantik HTML5 dan struktur dokumen web standar.\n2. Mengimplementasikan tata letak responsif menggunakan CSS Grid dan Flexbox.\n3. Mengintegrasikan CSS framework modern untuk antarmuka pengguna adaptif pada berbagai perangkat."
                            ],
                            [
                                'materi_pokok' => 'Interaktivitas DOM & JavaScript Modern (ES6+)',
                                'deskripsi' => "1. Memahami manipulasi Document Object Model (DOM) menggunakan JavaScript.\n2. Mengimplementasikan asynchronous request (Fetch API/AJAX) untuk pertukaran data JSON secara dinamis.\n3. Melakukan validasi formulir sisi klien secara efektif dan informatif."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Pemrograman Web Sisi Server & Arsitektur MVC',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merancang, memprogram, menguji, dan mengamankan aplikasi web berbasis sisi server (server-side scripting) menggunakan arsitektur Model-View-Controller (MVC), pengelolaan session/autentikasi, serta interaksi basis data relasional.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Arsitektur Aplikasi Web MVC & Routing',
                                'deskripsi' => "1. Memahami konsep arsitektur Model-View-Controller (MVC) dalam rekayasa aplikasi web.\n2. Merancang sistem perutean (routing), controller, dan templating view.\n3. Mengelola manajemen sesi, cookies, otentikasi login, serta hak akses multi-peran pengguna."
                            ],
                            [
                                'materi_pokok' => 'Operasi CRUD Basis Data & Keamanan Web',
                                'deskripsi' => "1. Mengimplementasikan operasi Create, Read, Update, Delete (CRUD) menggunakan PDO/ORM.\n2. Menerapkan pengamanan terhadap kerentanan web utama (SQL Injection, CSRF, dan XSS).\n3. Menguji fungsionalitas dan performa aplikasi web sebelum tahap deployment."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Integrasi Web Service & RESTful API',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merancang, mendokumentasikan, membangun, serta mengonsumsi Web Service berbasis REST API terstandar dengan format serialisasi data JSON dan mekanisme otentikasi token (JWT/Bearer).',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Perancangan Endpoint RESTful API & JSON',
                                'deskripsi' => "1. Merancang endpoint API sesuai prinsip HTTP method (GET, POST, PUT, DELETE) dan status code.\n2. Membangun respon data JSON terstruktur dan penanganan pesan kesalahan sistem.\n3. Menerapkan autentikasi stateless menggunakan token akses/JWT."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Pemrograman Aplikasi Perangkat Bergerak (Mobile)',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merancang antarmuka, menyusun kode program logika bisnis, dan mempublikasikan aplikasi perangkat bergerak (Android/iOS) menggunakan framework modern serta mengintegrasikannya dengan layanan API eksternal.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Dasar Pengembangan Aplikasi Mobile & Komponen UI',
                                'deskripsi' => "1. Menyiapkan lingkungan pengembangan perangkat lunak bergerak (SDK/Framework).\n2. Membangun tata letak UI aplikasi mobile dengan komponen navigasi dan input pengguna.\n3. Mengonsumsi endpoint REST API untuk memuat dan memperbarui data secara dinamis pada perangkat mobile."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 2. PEMODELAN PERANGKAT LUNAK (PPLG / RPL - FASE F)
            // =========================================================================
            [
                'id' => 'tpl_rpl_ppl_f',
                'judul' => 'Pemodelan Perangkat Lunak & Rekayasa Sistem (Fase F - Kelas XI & XII)',
                'bidang' => 'Teknologi Informasi / Rekayasa Perangkat Lunak',
                'fase_kode' => 'F',
                'keywords' => ['pemodelan', 'perangkat lunak', 'ppl', 'uml', 'analisis', 'rpl', 'pplg'],
                'deskripsi_singkat' => 'Paket analisis kebutuhan perangkat lunak, diagram UML, arsitektur data, dan prototyping antarmuka.',
                'cp_items' => [
                    [
                        'elemen' => 'Analisis Kebutuhan Sistem & Metodologi Pengembangan',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu mengidentifikasi, mengklasifikasi, dan mendokumentasikan kebutuhan fungsional dan non-fungsional sistem perangkat lunak serta memilih metodologi pengembangan perangkat lunak (Agile/Scrum/Waterfall) yang relevan.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Elisitasi Kebutuhan & Spesifikasi Perangkat Lunak (SRS)',
                                'deskripsi' => "1. Melakukan wawancara dan observasi elisitasi kebutuhan calon pengguna sistem.\n2. Menyusun dokumen spesifikasi kebutuhan perangkat lunak fungsional dan non-fungsional.\n3. Menentukan model siklus hidup pengembangan sistem (SDLC) yang optimal untuk proyek."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Pemodelan Sistem Berorientasi Objek (UML)',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merancang diagram pemodelan berorientasi objek menggunakan Unified Modeling Language (UML) yang mencakup Use Case Diagram, Activity Diagram, Class Diagram, dan Sequence Diagram secara konsisten dan terintegrasi.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Diagram Perilaku Sistem (Use Case & Activity Diagram)',
                                'deskripsi' => "1. Mengidentifikasi aktor dan use case skenario interaksi sistem.\n2. Merumuskan alur proses bisnis perangkat lunak ke dalam Activity Diagram.\n3. Memvalidasi konsistensi use case deskripsi dengan kebutuhan bisnis."
                            ],
                            [
                                'materi_pokok' => 'Diagram Struktur & Interaksi (Class & Sequence Diagram)',
                                'deskripsi' => "1. Merancang Class Diagram beserta atribut, method, relasi asosiasi, agregasi, dan inheritance.\n2. Memetakan pertukaran pesan antar objek melalui Sequence Diagram.\n3. Mentransformasikan diagram kelas ke dalam skema basis data relasional normalisasi."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Perancangan Antarmuka Pengguna (UI/UX) & Prototyping',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merancang wireframe, mockup visual, dan prototype interaktif berbasis pedoman pengalaman pengguna (UX) dan desain antarmuka pengguna (UI) modern.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Wireframing & Desain Antarmuka Interaktif',
                                'deskripsi' => "1. Membuat rancangan kerangka awal antarmuka (wireframe/sketsa).\n2. Membangun prototype interaktif resolusi tinggi (high-fidelity).\n3. Melakukan uji keterpakaian (usability testing) prototype dengan pengguna."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 3. ADMINISTRASI INFRASTRUKTUR JARINGAN (TKJ / TJKT - FASE F)
            // =========================================================================
            [
                'id' => 'tpl_tkj_aij_f',
                'judul' => 'Administrasi Infrastruktur Jaringan (Fase F - Kelas XI & XII)',
                'bidang' => 'Teknologi Informasi / Teknik Komputer & Jaringan',
                'fase_kode' => 'F',
                'keywords' => ['jaringan', 'infrastruktur', 'aij', 'tkj', 'tjkt', 'routing', 'cisco', 'mikrotik'],
                'deskripsi_singkat' => 'Paket konfigurasi VLAN, Routing Dinamis, Quality of Service, Firewall, dan Manajemen Server.',
                'cp_items' => [
                    [
                        'elemen' => 'Konfigurasi VLAN & Routing Dinamis',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merencanakan, mengonfigurasi, dan mengevaluasi Virtual Local Area Network (VLAN), inter-VLAN routing, serta protokol routing dinamis (OSPF/BGP) pada perangkat jaringan router dan switch terkelola.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Virtual LAN (VLAN) & Trunking 802.1Q',
                                'deskripsi' => "1. Merencanakan segmentasi jaringan menggunakan VLAN ID.\n2. Mengonfigurasi switchport access dan trunking IEEE 802.1Q.\n3. Mengimplementasikan Inter-VLAN routing menggunakan Router-on-a-Stick maupun Layer 3 Switch."
                            ],
                            [
                                'materi_pokok' => 'Protokol Routing Statis & Dinamis (OSPF)',
                                'deskripsi' => "1. Menganalisis tabel routing dan menentukan jalur transmisi data.\n2. Mengonfigurasi routing dinamis Open Shortest Path First (OSPF) multi-area.\n3. Melakukan troubleshooting konvergensi rute dan kegagalan link gateway."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Manajemen Bandwidth & Keamanan Jaringan (Firewall)',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merancang kebijakan manajemen bandwidth (Simple Queue/Queue Tree), Network Address Translation (NAT), dan firewall filtering untuk menjamin performa serta keamanan infrastruktur jaringan lokal.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Quality of Service (QoS) & Bandwidth Management',
                                'deskripsi' => "1. Menghitung alokasi bandwidth optimal untuk berbagai layanan (web, video, voice).\n2. Mengonfigurasi limitasi kecepatan internet dengan Queue bertingkat.\n3. Memprioritaskan paket data krusial untuk mencegah latensi tinggi."
                            ],
                            [
                                'materi_pokok' => 'Firewall Filtering, Mangle, & NAT Protection',
                                'deskripsi' => "1. Mengonfigurasi rule Firewall Filter untuk memblokir akses ilegal dan port rentan.\n2. Mengimplementasikan Source NAT (Masquerade) dan Destination NAT (Port Forwarding).\n3. Menganalisis log serangan dan melakukan hardening keamanan router."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 4. DESAIN GRAFIS PERCETAKAN (DKV / MULTIMEDIA - FASE F)
            // =========================================================================
            [
                'id' => 'tpl_dkv_dgp_f',
                'judul' => 'Desain Grafis Percetakan (Fase F - Kelas XI & XII)',
                'bidang' => 'Seni & Ekonomi Kreatif / Desain Komunikasi Visual',
                'fase_kode' => 'F',
                'keywords' => ['desain', 'grafis', 'percetakan', 'dgp', 'dkv', 'multimedia', 'illustrator', 'photoshop'],
                'deskripsi_singkat' => 'Paket tata letak tipografi, ilustrasi vektor, olah citra raster, dan teknik pra-cetak (pre-press).',
                'cp_items' => [
                    [
                        'elemen' => 'Tata Letak Tipografi & Desain Publikasi',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu menerapkan prinsip tata letak (grid, keseimbangan, ritme, kontras) serta hierarki tipografi dalam menyusun karya publikasi cetak komersial maupun editorial.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Prinsip Nirmana & Hierarki Visual Publikasi',
                                'deskripsi' => "1. Mengidentifikasi anatomi tipografi dan kesesuaian font dengan pesan komunikasi visual.\n2. Merancang tata letak halaman brosur, majalah, dan poster dengan sistem grid modular.\n3. Memilih paduan warna psikologis untuk meningkatkan daya tarik produk."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Pengolahan Gambar Vektor & Raster Pra-Cetak',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu membuat karya ilustrasi vektor presisi tinggi, mengolah foto/raster, serta menyiapkan file siap cetak (color profile CMYK, bleed, registration marks, dan resolusi minimum 300 DPI).',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Ilustrasi Vektor & Manipulasi Digital',
                                'deskripsi' => "1. Memproduksi grafis vektor presisi untuk logo, ikon, dan kemasan produk.\n2. Melakukan manipulasi citra bitmap, masking, dan koreksi warna profesional.\n3. Menggabungkan elemen vektor dan bitmap secara harmonis."
                            ],
                            [
                                'materi_pokok' => 'Teknik Pra-Cetak (Pre-Press & Color Separation)',
                                'deskripsi' => "1. Mengonversi profil warna dari RGB ke standar percetakan CMYK/Spot Color.\n2. Mengatur ukuran bleed, crop mark, dan margin keselamatan potong.\n3. Menghasilkan output format PDF/X untuk proses cetak offset dan digital printing."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 5. BAHASA INDONESIA (FASE E & F)
            // =========================================================================
            [
                'id' => 'tpl_umum_bind_ef',
                'judul' => 'Bahasa Indonesia (Fase E & F - Jenjang SMK)',
                'bidang' => 'Mata Pelajaran Umum',
                'fase_kode' => 'E',
                'keywords' => ['bahasa indonesia', 'indonesia', 'bind', 'laporan', 'teks', 'sastra'],
                'deskripsi_singkat' => 'Penguasaan teks laporan hasil observasi, eksposisi, teks negosiasi, resensi, dan karya ilmiah populer.',
                'cp_items' => [
                    [
                        'elemen' => 'Menyimak dan Membaca Teks Informasi & Kritis',
                        'deskripsi' => 'Peserta didik mampu mengevaluasi informasi berupa gagasan, pikiran, pandangan, arahan atau pesan dari berbagai jenis teks (deskripsi, laporan hasil observasi, eksposisi, dan eksplanasi) untuk menemukan makna tersurat dan tersirat secara kritis.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Teks Laporan Hasil Observasi (LHO) & Teks Prosedur',
                                'deskripsi' => "1. Mengidentifikasi struktur teks LHO dan kaidah kebahasaan ilmiah objektif.\n2. Menganalisis gagasan pokok dan fakta pendukung dari hasil pengamatan lingkungan kerja/kejuruan.\n3. Menyusun ringkasan informasi yang valid dan akurat dari sumber teks yang disimak."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Menulis & Menyajikan Gagasan Ilmiah / Kejuruan',
                        'deskripsi' => 'Peserta didik mampu menulis gagasan, pikiran, pandangan, arahan atau pesan tertulis untuk berbagai tujuan secara logis, kritis, dan kreatif dalam bentuk teks negosiasi, artikel opini, proposal kerja, dan laporan kegiatan kejuruan dengan kaidah PUEBI/EYD.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Penyusunan Proposal Kerja & Surat Niaga/Dinas',
                                'deskripsi' => "1. Merumuskan latar belakang, tujuan, dan rincian teknis proposal kegiatan.\n2. Mengaplikasikan kaidah tata bahasa baku dan format surat resmi kedinasan.\n3. Menyunting teks secara cermat dengan mengacu pada Ejaan Bahasa Indonesia yang Disempurnakan (EYD)."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 6. MATEMATIKA TERAPAN (FASE E & F)
            // =========================================================================
            [
                'id' => 'tpl_umum_mat_ef',
                'judul' => 'Matematika Terapan (Fase E & F - Jenjang SMK)',
                'bidang' => 'Mata Pelajaran Umum',
                'fase_kode' => 'E',
                'keywords' => ['matematika', 'matematika terapan', 'mtk', 'aljabar', 'statistika', 'kalkulus'],
                'deskripsi_singkat' => 'Paket bilangan berpangkat, barisan & deret, fungsi kuadrat, trigonometri terapan, dan statistika data.',
                'cp_items' => [
                    [
                        'elemen' => 'Bilangan, Barisan, dan Deret',
                        'deskripsi' => 'Peserta didik mampu menerapkan konsep eksponen, logaritma, barisan dan deret aritmetika serta geometri dalam menyelesaikan permasalahan kontekstual di bidang teknologi dan ekonomi bisnis.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Eksponen, Bentuk Akar, dan Logaritma',
                                'deskripsi' => "1. Mengoperasikan sifat-sifat eksponen dan bentuk akar dalam penyederhanaan aljabar.\n2. Menggunakan logaritma untuk menyelesaikan pemodelan pertumbuhan dan peluruhan.\n3. Menyelesaikan masalah kontekstual perhitungan bunga majemuk dan anuitas."
                            ],
                            [
                                'materi_pokok' => 'Barisan dan Deret Aritmetika & Geometri',
                                'deskripsi' => "1. Menentukan suku ke-n dan jumlah n suku pertama barisan aritmetika.\n2. Menganalisis pola deret geometri hingga dan tak hingga pada masalah dunia nyata.\n3. Memprediksi estimasi produksi dan pertumbuhan data berkala."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Statistika & Analisis Data Terapan',
                        'deskripsi' => 'Peserta didik mampu menyajikan, menginterpretasi data numerik/kategori, menghitung ukuran pemusatan (mean, median, modus) serta ukuran penyebaran data untuk pengambilan keputusan berbasis data.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Ukuran Pemusatan dan Penyebaran Data Berkelompok',
                                'deskripsi' => "1. Menyajikan data acak ke dalam tabel distribusi frekuensi dan histogram visual.\n2. Menghitung nilai rata-rata hitung, median, dan modus pada data berkelompok.\n3. Menganalisis variansi dan simpangan baku untuk mengukur tingkat dispersi data performa."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 7. KECERDASAN BUATAN & CLOUD COMPUTING (FASE F)
            // =========================================================================
            [
                'id' => 'tpl_ai_cloud_f',
                'judul' => 'Kecerdasan Buatan & Cloud Computing (Fase F - Konsentrasi Keahlian)',
                'bidang' => 'Teknologi Informasi / Kecerdasan Buatan & Cloud',
                'fase_kode' => 'F',
                'keywords' => ['kecerdasan buatan', 'cloud', 'ai', 'machine learning', 'cloud computing', 'mp-ai-01'],
                'deskripsi_singkat' => 'Paket machine learning dasar, computer vision/NLP, deployment cloud instance, dan serverless computing.',
                'cp_items' => [
                    [
                        'elemen' => 'Dasar Kecerdasan Buatan & Pembelajaran Mesin (Machine Learning)',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu memahami konsep data preprocessing, merancang model machine learning sederhana (klasifikasi dan regresi) dengan Python, serta mengevaluasi akurasi model menggunakan matriks evaluasi standar.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Data Preprocessing & Exploratory Data Analysis (EDA)',
                                'deskripsi' => "1. Membersihkan missing value, outlier, dan melakukan feature scaling data mentah.\n2. Memvisualisasikan korelasi data menggunakan library Python (Pandas/Matplotlib).\n3. Membagi dataset menjadi data latih (train) dan data uji (test)."
                            ],
                            [
                                'materi_pokok' => 'Pelatihan Model Supervised Learning & Evaluasi Metrik',
                                'deskripsi' => "1. Melatih model klasifikasi (Decision Tree/Random Forest/KNN).\n2. Mengukur performa model menggunakan Confusion Matrix, Precision, Recall, dan F1-Score.\n3. Melakukan fine-tuning hyperparameter untuk mengoptimalkan hasil prediksi."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Infrastruktur Cloud & Containerization Deployment',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu mengonfigurasi Virtual Machine pada penyedia Cloud (AWS/GCP/Azure/VPS), memaketkan aplikasi menggunakan Docker Container, dan menerapkan otomasi continuous deployment sederhana.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Containerization Aplikasi dengan Docker',
                                'deskripsi' => "1. Membuat Dockerfile dan mengemas environment aplikasi web/AI.\n2. Mengelola container lifecycle, port binding, dan volume data persistent.\n3. Menggunakan Docker Compose untuk orkestrasi multi-layanan (App + Database)."
                            ],
                            [
                                'materi_pokok' => 'Deployment Cloud Compute & Pengaturan Domain/SSL',
                                'deskripsi' => "1. Menyiapkan Virtual Private Server (VPS) berbasis sistem operasi Linux.\n2. Mengonfigurasi reverse proxy Nginx dan sertifikat keamanan gratis Let's Encrypt SSL.\n3. Memonitor performa CPU, memori, dan uptime instance cloud secara berkelanjutan."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 8. TEKNIK DAN BISNIS SEPEDA MOTOR (TBSM / OTOMOTIF - FASE F)
            // =========================================================================
            [
                'id' => 'tpl_otomotif_tbsm_f',
                'judul' => 'Teknik & Bisnis Sepeda Motor (TBSM - Fase F)',
                'bidang' => 'Teknologi & Rekayasa / Teknik Otomotif',
                'fase_kode' => 'F',
                'keywords' => ['tbsm', 'sepeda motor', 'otomotif', 'mesin', 'kelistrikan', 'sasis', 'bengkel'],
                'deskripsi_singkat' => 'Paket perawatan dan perbaikan mesin sepeda motor, sistem kelistrikan, sasis suspensi, dan sistem injeksi EFI.',
                'cp_items' => [
                    [
                        'elemen' => 'Perawatan & Perbaikan Mesin Sepeda Motor (Engine)',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu mendiagnosis gangguan, membongkar, memeriksa komponen, memperbaiki, dan menyetel mekanisme mesin 4-langkah, kepala silinder, blok silinder, serta sistem pelumasan dan pendinginan sesuai SOP pabrikan.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Mekanisme Katup & Kepala Silinder',
                                'deskripsi' => "1. Mengukur celah kerenggangan katup menggunakan feeler gauge sesuai spesifikasi standar.\n2. Melakukan skir katup dan pemeriksaan kebocoran ruang bakar.\n3. Memeriksa keausan poros nok (camshaft) dan rantai keteng (timing chain)."
                            ],
                            [
                                'materi_pokok' => 'Sistem Bahan Bakar Injeksi Elektronik (Electronic Fuel Injection / PGM-FI)',
                                'deskripsi' => "1. Menganalisis cara kerja sensor-sensor EFI (TPS, EOT/ECT, IAT, MAP, O2 Sensor).\n2. Membaca dan mereset kode kerusakan (DTC) menggunakan Diagnostic Scanner tool.\n3. Menguji tekanan pompa bahan bakar (fuel pump) dan pembersihan injektor."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Sistem Kelistrikan & Pengapian Sepeda Motor',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu merangkai, menguji, dan melacak gangguan pada sistem pengapian elektronik, sistem pengisian (alternator & kiprok), starter listrik, serta sistem penerangan dan sinyal lampu sepeda motor.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Sistem Pengisian Baterai & Pengapian Sepeda Motor',
                                'deskripsi' => "1. Menguji tegangan pengisian pada putaran mesin rendah dan tinggi menggunakan multimeter.\n2. Mendiagnosis kerusakan komponen regulator rectifier (kiprok) dan spul pengisian.\n3. Mengukur celah busi dan kekuatan percikan bunga api koil pengapian."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 9. PROJEK KREATIF DAN KEWIRAUSAHAAN (PKK - FASE F)
            // =========================================================================
            [
                'id' => 'tpl_kejuruan_pkk_f',
                'judul' => 'Projek Kreatif dan Kewirausahaan (PKK - Fase F)',
                'bidang' => 'Semua Bidang Kejuruan SMK',
                'fase_kode' => 'F',
                'keywords' => ['pkk', 'kewirausahaan', 'kreatif', 'bisnis', 'wirausaha', 'omset', 'produk'],
                'deskripsi_singkat' => 'Paket ideasi produk kejuruan, prototype barang/jasa, pemasaran digital, dan laporan laba-rugi.',
                'cp_items' => [
                    [
                        'elemen' => 'Ideasi, Desain Produk, dan Pembuatan Prototype',
                        'deskripsi' => 'Pada akhir fase F, peserta didik mampu mengidentifikasi peluang pasar berbasis potensi kejuruan, menyusun rencana usaha (business model canvas), membuat desain kemasan, serta menghasilkan prototype produk barang/jasa yang bernilai jual tinggi.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Business Model Canvas (BMC) & Analisis Pasar',
                                'deskripsi' => "1. Mengidentifikasi nilai keunggulan produk (value proposition) dan target segmen konsumen.\n2. Menghitung estimasi Harga Pokok Produksi (HPP) dan Break Even Point (BEP).\n3. Menyusun desain kemasan ramah lingkungan dan legalitas izin usaha dasar."
                            ],
                            [
                                'materi_pokok' => 'Pemasaran Digital (Digital Marketing) & Penjualan',
                                'deskripsi' => "1. Membuat materi konten promosi visual dan copywriting yang persuasif.\n2. Memanfaatkan kanal marketplace, media sosial bisnis, dan e-commerce untuk menjangkau pembeli.\n3. Mengelola interaksi pelayanan pelanggan (customer service) dan ulasan produk."
                            ]
                        ]
                    ]
                ]
            ],

            // =========================================================================
            // 10. INFORMATIKA (FASE E - KELAS X)
            // =========================================================================
            [
                'id' => 'tpl_dasar_informatika_e',
                'judul' => 'Informatika (Fase E - Kelas X)',
                'bidang' => 'Teknologi Informasi / Umum Fase E',
                'fase_kode' => 'E',
                'keywords' => ['informatika', 'komputer dasar', 'berpikir komputasional', 'algoritma', 'tik'],
                'deskripsi_singkat' => 'Paket dasar komputasional, sistem komputer, jaringan internet, analisis data, dan pemrograman dasar.',
                'cp_items' => [
                    [
                        'elemen' => 'Berpikir Komputasional (Computational Thinking)',
                        'deskripsi' => 'Pada akhir fase E, peserta didik mampu menerapkan strategi algoritmik standar untuk menghasilkan beberapa solusi persoalan dengan data diskrit bervolume tidak kecil pada kehidupan sehari-hari maupun implementasinya dalam program komputer.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Pilar Berpikir Komputasional & Optimasi Solusi',
                                'deskripsi' => "1. Menerapkan 4 pilar komputasi (Dekomposisi, Pengenalan Pola, Abstraksi, Algoritma).\n2. Memecahkan persoalan optimasi penjadwalan dan rute terpendek secara terstruktur.\n3. Mengidentifikasi struktur data tumpukan (stack) dan antrean (queue)."
                            ]
                        ]
                    ],
                    [
                        'elemen' => 'Teknologi Informasi dan Komunikasi (TIK) & Analisis Data',
                        'deskripsi' => 'Pada akhir fase E, peserta didik mampu memanfaatkan perkakas aplikasi pengolah kata, lembar kerja, presentasi, serta mengolah data dalam jumlah besar secara terintegrasi dan menghasilkan visualisasi informasi informatif.',
                        'tp_items' => [
                            [
                                'materi_pokok' => 'Integrasi Aplikasi Perkantoran & Pengolahan Spreadsheet Lanjutan',
                                'deskripsi' => "1. Mengintegrasikan data antar aplikasi perkantoran (Mail Merge, Object Linking & Embedding).\n2. Menggunakan fungsi logika, lookup (VLOOKUP/XLOOKUP), dan formula statistik tingkat lanjut.\n3. Membuat diagram analitik dan pivot table untuk visualisasi kesimpulan data."
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Dapatkan satu template berdasarkan ID
     */
    public static function getTemplateById($id) {
        $all = self::getAllTemplates();
        foreach ($all as $tpl) {
            if ($tpl['id'] === $id) return $tpl;
        }
        return null;
    }

    /**
     * Rekomendasikan template berdasarkan kemiripan nama mata pelajaran
     */
    public static function getRecommendedTemplatesForMapel($namaMapel) {
        $namaMapelLower = strtolower($namaMapel);
        $all = self::getAllTemplates();
        $matched = [];
        $others = [];

        foreach ($all as $tpl) {
            $score = 0;
            foreach ($tpl['keywords'] as $kw) {
                if (stripos($namaMapelLower, $kw) !== false) {
                    $score += 2;
                }
            }
            if ($score > 0) {
                $tpl['match_score'] = $score;
                $matched[] = $tpl;
            } else {
                $others[] = $tpl;
            }
        }

        // Urutkan matched berdasarkan skor kecocokan tertinggi
        usort($matched, function($a, $b) {
            return ($b['match_score'] ?? 0) <=> ($a['match_score'] ?? 0);
        });

        return array_merge($matched, $others);
    }

    /**
     * Dapatkan daftar datar (flat) semua Capaian Pembelajaran dari seluruh template
     * Cocok untuk dropdown "Pilih Cepat Template CP" di Modal Add CP
     */
    public static function getFlatCpTemplates() {
        $all = self::getAllTemplates();
        $flatList = [];

        foreach ($all as $tpl) {
            foreach ($tpl['cp_items'] as $idx => $cp) {
                $flatList[] = [
                    'tpl_id' => $tpl['id'],
                    'tpl_judul' => $tpl['judul'],
                    'fase_kode' => $tpl['fase_kode'],
                    'elemen' => $cp['elemen'],
                    'deskripsi' => $cp['deskripsi'],
                    'tp_count' => count($cp['tp_items']),
                    'sample_tp' => !empty($cp['tp_items'][0]) ? $cp['tp_items'][0]['materi_pokok'] : '',
                    'full_tp_items' => $cp['tp_items']
                ];
            }
        }

        return $flatList;
    }
}
