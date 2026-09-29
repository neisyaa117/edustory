<?php

/*
|--------------------------------------------------------------------------
| DAFTAR JENIS ARTIKEL
|--------------------------------------------------------------------------
|
| Satu tempat untuk semua hal tentang jenis artikel:
|
|   - teks di dashboard (ringkas, deskripsi, cocok, siapkan, contoh)
|   - isian form (bagian -> fields)
|   - arahan untuk AI (panduan)
|   - foto_label: judul kotak unggah foto (boleh dihapus)
|
| Pertanyaan di sini mengikuti Google Form EduStory.
| Data sekolah (nama, jenjang, wilayah, alamat) tidak ditanyakan lagi
| karena sudah diisi saat daftar dan ada di halaman profil sekolah.
|
| Mau menambah jenis baru? Salin salah satu blok, ganti slug-nya,
| lalu isi. Dashboard dan form akan ikut menyesuaikan.
|
| Tipe isian ('tipe'):
|   teks      satu baris
|   panjang   beberapa baris
|   pilihan   pilih satu (opsi)
|   banyak    pilih lebih dari satu (opsi)
|   menu      dropdown (opsi)
|   tanggal   pilih tanggal
|
| 'wajib' => true       harus diisi
| 'bantuan'             tulisan kecil di bawah pertanyaan
| 'placeholder'         contoh isian yang pudar di dalam kotak
|
*/

return [

    /* ------------------------------------------------------------------ */
    'profil' => [
        'nama'      => 'Profil',
        'ikon'      => 'fa-chalkboard-user',
        'warna'     => 'purple-bg',
        'ringkas'   => 'Kenalkan sosok dan cerita di balik sekolah.',
        'deskripsi' => 'Profil menceritakan satu orang di lingkungan sekolah: guru, siswa, kepala sekolah, orang tua, atau alumni. Isinya bukan daftar jabatan, melainkan gambaran manusianya: apa yang membuatnya istimewa, apa yang sudah ia berikan, dan kejadian yang membuat orang lain mengingatnya.',
        'cocok'     => 'Memperkenalkan guru baru, berterima kasih kepada tenaga pendidik yang sudah lama mengabdi, atau mengenang alumni yang berprestasi.',
        'siapkan'   => [
            'Nama dan peran tokoh di sekolah',
            'Dua atau tiga hal yang membuatnya berbeda dari yang lain',
            'Satu kejadian berkesan yang pernah ia lakukan',
            'Prestasi atau pesan untuk pembaca (boleh dilewati)',
        ],
        'contoh'    => [
            'Bu Siti dan Kebun Kecil di Belakang Kelas 5',
            'Dua Puluh Tahun Pak Darto Membuka Gerbang Sekolah',
        ],
        'panduan'   => 'Tulis sebagai profil naratif tentang satu orang. Mulai dari satu gambaran atau kejadian yang menghidupkan sosoknya, bukan dari daftar jabatan. Sebut kontribusi dan kelebihannya lewat contoh nyata dari data, bukan lewat pujian umum.',
        'foto_label' => 'Foto tokoh',
        'bagian'    => [
            [
                'judul'  => 'Siapa yang mau diceritakan?',
                'info'   => 'Pilih orang yang menjadi tokoh dalam cerita.',
                'fields' => [
                    [
                        'nama' => 'tokoh', 'label' => 'Siapa yang ingin diceritakan?',
                        'tipe' => 'pilihan', 'wajib' => true,
                        'opsi' => [
                            'Guru'           => '👩‍🏫 Guru',
                            'Siswa'          => '🧒 Siswa',
                            'Kepala Sekolah' => '👨‍💼 Kepala Sekolah',
                            'Orang Tua'      => '👨‍👩‍👧 Orang Tua',
                            'Alumni'         => '🎓 Alumni',
                            'Lainnya'        => '🌟 Lainnya',
                        ],
                    ],
                ],
            ],
            [
                'judul'  => 'Kenalan dengan tokohnya',
                'info'   => 'Informasi dasar tentang tokoh.',
                'fields' => [
                    ['nama' => 'nama_tokoh', 'label' => 'Nama tokoh', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Ibu Siti'],
                    ['nama' => 'jabatan', 'label' => 'Jabatan atau peran di sekolah', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Guru Kelas 5'],
                    ['nama' => 'sejak', 'label' => 'Sejak kapan berada di sekolah ini?', 'tipe' => 'teks', 'placeholder' => 'Contoh: Tahun 2020'],
                ],
            ],
            [
                'judul'  => 'Ceritakan tentang tokohnya',
                'info'   => 'Tidak perlu formal. Tulis saja dengan bahasa sehari-hari.',
                'fields' => [
                    ['nama' => 'latar_belakang', 'label' => 'Ceritakan latar belakang tokoh tersebut', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Ceritakan sedikit tentang tokoh ini...'],
                    ['nama' => 'keistimewaan', 'label' => 'Apa yang membuat tokoh ini istimewa?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Misalnya: selalu membantu siswa, kreatif dalam mengajar, ramah...'],
                    ['nama' => 'kontribusi', 'label' => 'Kontribusi atau hal yang paling berkesan', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Ceritakan hal yang pernah dilakukan dan paling berkesan...'],
                ],
            ],
            [
                'judul'  => 'Cerita penutup',
                'info'   => 'Tambahkan pencapaian, pengalaman, atau pesan.',
                'fields' => [
                    ['nama' => 'prestasi', 'label' => 'Prestasi atau pencapaian', 'tipe' => 'panjang', 'placeholder' => 'Misalnya: pernah mendapat penghargaan...', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'pengalaman', 'label' => 'Pengalaman menarik', 'tipe' => 'panjang', 'placeholder' => 'Ada cerita lucu, mengharukan, atau berkesan?', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'pesan', 'label' => 'Pesan untuk siswa, orang tua, atau masyarakat', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'kegiatan' => [
        'nama'      => 'Kegiatan',
        'ikon'      => 'fa-champagne-glasses',
        'warna'     => 'orange-bg',
        'ringkas'   => 'Ceritakan kegiatan seru dan bermakna.',
        'deskripsi' => 'Kegiatan merekam satu peristiwa yang sudah berlangsung di sekolah, misalnya upacara, lomba, pentas seni, kunjungan, atau kerja bakti. Artikel menjawab apa yang terjadi, siapa yang terlibat, bagaimana suasananya, dan apa yang tersisa setelah acara selesai.',
        'cocok'     => 'Laporan singkat setelah acara, dokumentasi kegiatan rutin, atau berbagi momen yang ingin dikenang warga sekolah.',
        'siapkan'   => [
            'Nama kegiatan, tanggal, tempat, dan penyelenggaranya',
            'Siapa saja yang ikut serta dan apa tujuan kegiatan',
            'Urutan acara atau hal-hal yang dilakukan',
            'Satu momen yang paling berkesan dan manfaat yang dirasakan',
        ],
        'contoh'    => [
            'Lomba Kebersihan Kelas Berakhir dengan Kejutan dari Kelas 2',
            'Sehari Menjadi Petani: Kunjungan Kelas B ke Sawah Warga',
        ],
        'panduan'   => 'Tulis sebagai berita kegiatan yang enak dibaca. Jawab apa, siapa, kapan, di mana, dan bagaimana suasananya, lalu tutup dengan manfaat atau perasaan setelah kegiatan itu. Jangan menambah acara atau peserta yang tidak disebut.',
        'foto_label' => 'Foto kegiatan',
        'bagian'    => [
            [
                'judul'  => 'Tentang kegiatannya',
                'info'   => 'Informasi pokok yang menjadi kerangka berita.',
                'fields' => [
                    ['nama' => 'nama_kegiatan', 'label' => 'Nama kegiatan', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Lomba Mewarnai Hari Kemerdekaan'],
                    ['nama' => 'tanggal', 'label' => 'Tanggal kegiatan', 'tipe' => 'tanggal', 'wajib' => true],
                    ['nama' => 'tempat', 'label' => 'Tempat kegiatan', 'tipe' => 'teks', 'placeholder' => 'Contoh: Halaman sekolah'],
                    ['nama' => 'penyelenggara', 'label' => 'Penyelenggara', 'tipe' => 'teks', 'placeholder' => 'Contoh: Panitia HUT RI sekolah'],
                    ['nama' => 'peserta', 'label' => 'Siapa saja yang mengikuti kegiatan?', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: 45 siswa kelas 1 sampai 3, guru, dan wali murid'],
                ],
            ],
            [
                'judul'  => 'Tujuan dan jalannya kegiatan',
                'info'   => 'Ceritakan apa yang terjadi dari awal sampai akhir.',
                'fields' => [
                    ['nama' => 'tujuan', 'label' => 'Apa tujuan kegiatan?', 'tipe' => 'panjang', 'placeholder' => 'Mengapa kegiatan ini diadakan?'],
                    ['nama' => 'dilakukan', 'label' => 'Apa saja yang dilakukan?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Permainan, lomba, atau tahapan kegiatan...'],
                    ['nama' => 'jalannya', 'label' => 'Bagaimana jalannya kegiatan?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Urutan acara dan suasananya dari awal sampai akhir...'],
                    ['nama' => 'momen', 'label' => 'Momen paling menarik atau berkesan', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Kejadian lucu, mengharukan, atau tak terduga...'],
                ],
            ],
            [
                'judul'  => 'Setelah kegiatan',
                'info'   => 'Apa yang tertinggal setelah acara selesai?',
                'fields' => [
                    ['nama' => 'hasil', 'label' => 'Hasil atau manfaat kegiatan', 'tipe' => 'panjang', 'placeholder' => 'Misalnya: siswa lebih berani tampil, kelas lebih kompak...'],
                    ['nama' => 'perasaan', 'label' => 'Apa yang dirasakan setelah kegiatan?', 'tipe' => 'panjang', 'placeholder' => 'Perasaan siswa, guru, atau orang tua...', 'bantuan' => 'Tulis hanya yang benar-benar dirasakan atau dikatakan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'pohon-bercerita' => [
        'nama'      => 'Pohon Bercerita',
        'ikon'      => 'fa-tree',
        'warna'     => 'green-bg',
        'ringkas'   => 'Bagikan cerita tentang pohon atau tanaman yang berkesan.',
        'deskripsi' => 'Pohon Bercerita mengangkat satu pohon atau tanaman di lingkungan sekolah yang menyimpan kenangan: pohon tua di halaman, tanaman yang ditanam angkatan tertentu, atau sudut kebun yang selalu ramai. Artikel bercerita lewat pohon itu, tentang asal-usulnya, siapa yang menanam dan sering singgah, dan apa artinya bagi warga sekolah.',
        'cocok'     => 'Menuliskan sejarah kecil sekolah, mengenang pohon yang ditanam bersama, atau menunjukkan bahwa sudut sederhana pun punya cerita.',
        'siapkan'   => [
            'Nama pohon atau tanaman dan letaknya di sekolah',
            'Sejak kapan ada dan siapa yang menanamnya',
            'Satu atau dua kenangan orang yang pernah beraktivitas di sekitarnya',
            'Perubahan yang terjadi sejak pohon ditanam',
        ],
        'contoh'    => [
            'Pohon Mangga yang Menyaksikan Tiga Angkatan Lulus',
            'Pohon Ketapang di Halaman dan Tempat Kami Belajar di Bawah Teduhnya',
        ],
        'panduan'   => 'Tulis sebagai kisah tentang sebuah pohon atau tanaman, dengan pohon itu sebagai tokoh utama. Gunakan detail yang ada di data (letak, kebiasaan orang di sekitarnya, perubahan dari waktu ke waktu). Kenangan hanya boleh berasal dari data; jangan menciptakan tokoh atau peristiwa.',
        'foto_label' => 'Foto pohon',
        'bagian'    => [
            [
                'judul'  => 'Pohonnya',
                'info'   => 'Perkenalkan pohon atau tanaman yang akan diceritakan.',
                'fields' => [
                    ['nama' => 'nama_pohon', 'label' => 'Nama pohon atau tanaman', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Pohon beringin'],
                    ['nama' => 'lokasi', 'label' => 'Lokasi pohon di sekolah', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Halaman depan, dekat gerbang'],
                    ['nama' => 'sejak', 'label' => 'Sejak kapan pohon tersebut ada?', 'tipe' => 'teks', 'placeholder' => 'Contoh: Sejak tahun 1998'],
                    ['nama' => 'penanam', 'label' => 'Siapa yang menanam?', 'tipe' => 'teks', 'placeholder' => 'Contoh: Angkatan pertama sekolah'],
                ],
            ],
            [
                'judul'  => 'Cerita dan kenangan',
                'info'   => 'Bagian yang membuat pohon ini punya cerita.',
                'fields' => [
                    ['nama' => 'penting', 'label' => 'Mengapa pohon ini penting?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Apa artinya pohon ini bagi sekolah?'],
                    ['nama' => 'kenangan', 'label' => 'Apa cerita atau kenangan yang berkaitan dengan pohon ini?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Cerita guru, siswa, atau alumni yang pernah menghabiskan waktu di sini...'],
                    ['nama' => 'aktivitas', 'label' => 'Siapa saja yang sering beraktivitas di sekitar pohon?', 'tipe' => 'panjang', 'placeholder' => 'Misalnya: siswa bermain saat istirahat, guru mengajar di bawahnya...'],
                ],
            ],
            [
                'judul'  => 'Dari dulu sampai sekarang',
                'info'   => 'Perubahan dan pesan dari cerita ini.',
                'fields' => [
                    ['nama' => 'perubahan', 'label' => 'Perubahan apa yang terjadi sejak pohon ditanam?', 'tipe' => 'panjang', 'placeholder' => 'Misalnya: dulu kecil, kini rindang dan jadi tempat berteduh...'],
                    ['nama' => 'pesan', 'label' => 'Pesan yang ingin disampaikan melalui cerita ini', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'anak-hebat' => [
        'nama'      => 'Anak Hebat',
        'ikon'      => 'fa-star',
        'warna'     => 'yellow-bg',
        'ringkas'   => 'Ceritakan pengalaman dan kisah anak inspiratif.',
        'deskripsi' => 'Anak Hebat menceritakan satu anak yang menginspirasi. Hebat di sini tidak harus juara: bisa karena tekun, jujur, berani mencoba, atau bangkit setelah jatuh. Artikel menonjolkan prosesnya, bukan hanya hasil, dan menyebut siapa yang ikut mendukung.',
        'cocok'     => 'Mengapresiasi siswa yang menunjukkan perubahan, keberanian, atau ketekunan, sekaligus memberi contoh baik bagi teman-temannya.',
        'siapkan'   => [
            'Nama dan kelas anak (izin orang tua sebaiknya sudah ada)',
            'Hal yang membuatnya istimewa, dan bagaimana awal ceritanya',
            'Tantangan yang pernah dihadapi dan cara ia mengatasinya',
            'Siapa yang mendukungnya: guru, teman, atau keluarga',
        ],
        'contoh'    => [
            'Rafi Belajar Membaca Pelan-Pelan, Kini Ia Membacakan Cerita untuk Adik Kelas',
            'Nayla dan Tabungan Kecil untuk Membeli Buku',
        ],
        'panduan'   => 'Tulis sebagai kisah inspiratif tentang seorang anak. Fokus pada proses dan sikapnya. Jangan membandingkan dengan anak lain, jangan menyebut alamat rumah atau data pribadi, dan cukup sebut nama panggilan serta kelas. Nada apresiatif, tidak menggurui.',
        'foto_label' => 'Foto siswa',
        'bagian'    => [
            [
                'judul'  => 'Kenalan dengan anaknya',
                'info'   => 'Cukup nama panggilan dan kelas. Pastikan orang tua sudah mengizinkan.',
                'fields' => [
                    ['nama' => 'nama_anak', 'label' => 'Nama siswa', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Rafi', 'bantuan' => 'Nama panggilan sudah cukup.'],
                    ['nama' => 'kelas', 'label' => 'Kelas', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Kelas 3 atau TK B'],
                    ['nama' => 'topik', 'label' => 'Apa yang ingin diceritakan tentang siswa ini?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Garis besar cerita: tentang ketekunan, keberanian, prestasi...'],
                ],
            ],
            [
                'judul'  => 'Yang membuatnya hebat',
                'info'   => 'Ceritakan dengan contoh kejadian.',
                'fields' => [
                    ['nama' => 'istimewa', 'label' => 'Apa yang membuat anak ini istimewa?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Sifat, kebiasaan, atau hal yang ia lakukan...'],
                    ['nama' => 'awal', 'label' => 'Bagaimana awal ceritanya?', 'tipe' => 'panjang', 'placeholder' => 'Bagaimana semua ini dimulai?'],
                    ['nama' => 'tantangan', 'label' => 'Tantangan yang pernah dihadapi', 'tipe' => 'panjang', 'placeholder' => 'Apa yang sulit baginya?'],
                    ['nama' => 'cara', 'label' => 'Bagaimana cara menghadapinya?', 'tipe' => 'panjang', 'placeholder' => 'Apa yang ia lakukan untuk mengatasinya?'],
                    ['nama' => 'dukungan', 'label' => 'Siapa yang memberikan dukungan?', 'tipe' => 'panjang', 'placeholder' => 'Guru, teman, atau keluarga yang membantu...'],
                ],
            ],
            [
                'judul'  => 'Pencapaian dan pelajaran',
                'info'   => 'Penutup cerita.',
                'fields' => [
                    ['nama' => 'prestasi', 'label' => 'Prestasi atau pencapaian', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'pelajaran', 'label' => 'Hal yang dipelajari dari pengalaman tersebut', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'pesan', 'label' => 'Pesan untuk siswa lainnya', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'tips-trik' => [
        'nama'      => 'Tips & Trik',
        'ikon'      => 'fa-lightbulb',
        'warna'     => 'blue-bg',
        'ringkas'   => 'Bagikan tips sederhana yang bermanfaat.',
        'deskripsi' => 'Tips & Trik berisi cara praktis yang sudah dicoba di sekolah dan berhasil. Bisa untuk mengajar, mengelola kelas, mengajak anak gemar membaca, atau menjaga kebersihan. Artikel disusun dari masalah, langkah-langkah, lalu hasilnya, supaya sekolah lain bisa langsung mencoba.',
        'cocok'     => 'Berbagi cara kerja yang sederhana dan murah, terutama yang lahir dari keterbatasan di lapangan.',
        'siapkan'   => [
            'Topik dan masalah yang ingin dipecahkan',
            'Tiga langkah utama yang dilakukan, berurutan',
            'Tips tambahan dan kesalahan yang sebaiknya dihindari',
            'Apa hasilnya setelah dicoba',
        ],
        'contoh'    => [
            'Lima Menit Cerita Pagi: Cara Kami Membuat Kelas Lebih Tenang',
            'Kotak Tanya di Pojok Kelas untuk Anak yang Malu Bertanya',
        ],
        'panduan'   => 'Tulis sebagai artikel tips yang praktis. Buka dengan masalahnya, lalu langkah-langkah dalam daftar bernomor (pakai langkah pertama, kedua, dan ketiga dari data), lalu tips tambahan, kesalahan yang perlu dihindari, dan hasil yang dilaporkan. Jangan menambah langkah atau klaim hasil yang tidak ada di data. Kalimat perintah boleh dipakai pada langkah.',
        'foto_label' => 'Foto pendukung',
        'bagian'    => [
            [
                'judul'  => 'Topik dan masalah',
                'info'   => 'Mulai dari masalah yang ingin dipecahkan.',
                'fields' => [
                    ['nama' => 'topik', 'label' => 'Topik yang ingin dibahas', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Membuat anak semangat membaca'],
                    ['nama' => 'masalah', 'label' => 'Masalah yang sering terjadi', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Apa yang dulu sulit atau tidak berjalan?'],
                    ['nama' => 'siapa', 'label' => 'Siapa yang biasanya mengalami masalah tersebut?', 'tipe' => 'teks', 'placeholder' => 'Contoh: Siswa kelas rendah, guru baru'],
                ],
            ],
            [
                'judul'  => 'Caranya',
                'info'   => 'Tulis langkah demi langkah.',
                'fields' => [
                    ['nama' => 'cara', 'label' => 'Bagaimana cara mengatasinya?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Gambaran umum caranya...'],
                    ['nama' => 'langkah_1', 'label' => 'Langkah pertama', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'langkah_2', 'label' => 'Langkah kedua', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'langkah_3', 'label' => 'Langkah ketiga', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'tambahan', 'label' => 'Tips tambahan', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'kesalahan', 'label' => 'Kesalahan yang sebaiknya dihindari', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
            [
                'judul'  => 'Hasilnya',
                'info'   => 'Apa yang terjadi setelah dicoba?',
                'fields' => [
                    ['nama' => 'hasil', 'label' => 'Hasil setelah tips diterapkan', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Perubahan pada siswa, kelas, atau suasana belajar...'],
                    ['nama' => 'pesan', 'label' => 'Pesan untuk pembaca', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'program-unggulan' => [
        'nama'      => 'Program Unggulan',
        'ikon'      => 'fa-school',
        'warna'     => 'coral-bg',
        'ringkas'   => 'Kenalkan program menarik dari sekolah.',
        'deskripsi' => 'Program Unggulan memperkenalkan satu program yang menjadi ciri sekolah, misalnya gerakan literasi, kebun sekolah, pembiasaan ibadah, atau kelas seni. Artikel menjelaskan tujuan, cara menjalankan, siapa yang terlibat, dan apa yang sudah berubah berkat program itu.',
        'cocok'     => 'Memperkenalkan sekolah kepada orang tua calon murid, atau mendokumentasikan program yang sudah berjalan lama.',
        'siapkan'   => [
            'Nama program, sejak kapan berjalan, dan tujuannya',
            'Siapa saja yang terlibat dan kegiatan utamanya',
            'Hal yang membuat program ini unik',
            'Hasil dan manfaat yang sudah terlihat',
        ],
        'contoh'    => [
            'Kebun Sekolah: Tempat Kelas 4 Belajar Menghitung Sambil Menyiram',
            'Jumat Membaca, Dua Tahun Berjalan',
        ],
        'panduan'   => 'Tulis sebagai pengenalan program yang jelas dan konkret. Jelaskan latar belakang, tujuan, cara kerja, dan hasilnya dengan contoh dari data. Hindari kata promosi yang berlebihan; biarkan detail yang membuktikan.',
        'foto_label' => 'Foto program',
        'bagian'    => [
            [
                'judul'  => 'Programnya',
                'info'   => 'Perkenalkan program secara singkat.',
                'fields' => [
                    ['nama' => 'nama_program', 'label' => 'Nama program', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Jumat Membaca'],
                    ['nama' => 'sejak', 'label' => 'Sejak kapan program berjalan?', 'tipe' => 'teks', 'placeholder' => 'Contoh: Januari 2024'],
                    ['nama' => 'latar', 'label' => 'Latar belakang dibuatnya program', 'tipe' => 'panjang', 'placeholder' => 'Masalah atau kebutuhan yang mendorong program ini...'],
                    ['nama' => 'tujuan', 'label' => 'Apa tujuan program?', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'pihak', 'label' => 'Siapa saja yang terlibat?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Contoh: Seluruh siswa kelas 1 sampai 6, guru pendamping, orang tua'],
                ],
            ],
            [
                'judul'  => 'Cara menjalankan',
                'info'   => 'Bagaimana program berjalan di lapangan.',
                'fields' => [
                    ['nama' => 'pelaksanaan', 'label' => 'Bagaimana program dijalankan?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Jadwal, tahapan, dan siapa yang mendampingi...'],
                    ['nama' => 'kegiatan_utama', 'label' => 'Apa kegiatan utama dalam program?', 'tipe' => 'panjang'],
                    ['nama' => 'keunikan', 'label' => 'Apa keunikan program ini?', 'tipe' => 'panjang', 'placeholder' => 'Hal khas yang tidak ada di sekolah lain...'],
                ],
            ],
            [
                'judul'  => 'Hasil',
                'info'   => 'Apa yang sudah berubah?',
                'fields' => [
                    ['nama' => 'hasil', 'label' => 'Hasil yang sudah dicapai', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'manfaat', 'label' => 'Manfaat bagi siswa', 'tipe' => 'panjang'],
                    ['nama' => 'rencana', 'label' => 'Rencana pengembangan ke depan', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'prestasi-inovasi' => [
        'nama'      => 'Prestasi & Inovasi',
        'ikon'      => 'fa-trophy',
        'warna'     => 'purple-bg',
        'ringkas'   => 'Bagikan prestasi dan karya sekolah.',
        'deskripsi' => 'Prestasi & Inovasi mengabarkan capaian sekolah: juara lomba, karya siswa, atau cara baru yang dibuat guru dan sekolah. Artikel tidak berhenti pada pengumuman kemenangan. Ia menceritakan persiapannya, kerja keras di baliknya, dan apa artinya bagi sekolah.',
        'cocok'     => 'Mengumumkan hasil lomba, memperkenalkan karya atau alat buatan siswa, atau menceritakan inovasi kecil yang berdampak.',
        'siapkan'   => [
            'Nama prestasi atau inovasi, tingkat, dan waktunya',
            'Siapa peraihnya atau pembuatnya',
            'Proses persiapan dan tantangan yang dihadapi',
            'Dukungan yang diterima dan harapan ke depan',
        ],
        'contoh'    => [
            'Dari Barang Bekas ke Tingkat Kabupaten: Karya Kelas 6 Lolos Final',
            'Enam Minggu Latihan Sore Berbuah Juara Dua Lomba Cerdas Cermat',
        ],
        'panduan'   => 'Tulis sebagai kabar prestasi atau inovasi yang menyertakan proses di baliknya. Sebut tingkat dan waktu persis seperti di data. Jangan membesar-besarkan capaian atau menambah penghargaan yang tidak disebut.',
        'foto_label' => 'Dokumentasi',
        'bagian'    => [
            [
                'judul'  => 'Capaiannya',
                'info'   => 'Apa yang diraih atau dibuat?',
                'fields' => [
                    ['nama' => 'nama_capaian', 'label' => 'Nama prestasi atau inovasi', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Juara 2 Lomba Cerdas Cermat'],
                    ['nama' => 'pelaku', 'label' => 'Siapa yang meraih atau membuat?', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Nama dan kelas, atau nama tim'],
                    [
                        'nama' => 'tingkat', 'label' => 'Tingkat prestasi', 'tipe' => 'menu',
                        'opsi' => ['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'],
                        'bantuan' => 'Boleh dikosongkan bila tidak sesuai.',
                    ],
                    ['nama' => 'nama_lomba', 'label' => 'Nama lomba atau kegiatan', 'tipe' => 'teks', 'placeholder' => 'Contoh: Lomba Cerdas Cermat Tingkat Kabupaten'],
                    ['nama' => 'waktu', 'label' => 'Kapan pencapaian tersebut diperoleh?', 'tipe' => 'teks', 'placeholder' => 'Contoh: Agustus 2026'],
                ],
            ],
            [
                'judul'  => 'Di balik capaian',
                'info'   => 'Bagian yang membuat pembaca peduli.',
                'fields' => [
                    ['nama' => 'proses', 'label' => 'Bagaimana proses persiapannya?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Latihan, percobaan, dan siapa yang membimbing...'],
                    ['nama' => 'tantangan', 'label' => 'Tantangan yang dihadapi', 'tipe' => 'panjang'],
                    ['nama' => 'hasil', 'label' => 'Hasil yang diperoleh', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'istimewa', 'label' => 'Apa yang membuat prestasi atau inovasi ini istimewa?', 'tipe' => 'panjang'],
                ],
            ],
            [
                'judul'  => 'Dukungan dan harapan',
                'info'   => 'Siapa yang membantu dan apa yang diharapkan?',
                'fields' => [
                    ['nama' => 'dukungan', 'label' => 'Dukungan dari sekolah atau orang tua', 'tipe' => 'panjang'],
                    ['nama' => 'harapan', 'label' => 'Harapan setelah pencapaian', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'suara-hati' => [
        'nama'      => 'Suara Hati',
        'ikon'      => 'fa-comments',
        'warna'     => 'pink-bg',
        'ringkas'   => 'Cerita dari siswa, guru, orang tua, dan lainnya.',
        'deskripsi' => 'Suara Hati adalah tulisan personal dalam sudut pandang orang pertama. Siswa, guru, orang tua, atau alumni menceritakan pengalaman dan perasaannya dengan kata-kata sendiri. Artikel menjaga suara penuturnya: hangat, jujur, dan tidak dibuat terlalu resmi.',
        'cocok'     => 'Refleksi guru di akhir semester, kesan orang tua terhadap sekolah, atau cerita siswa tentang hal yang ia pelajari.',
        'siapkan'   => [
            'Siapa yang bercerita dan hubungannya dengan sekolah',
            'Pengalaman paling berkesan dan mengapa itu berarti',
            'Perubahan yang dirasakan dan hal yang paling disyukuri',
            'Harapan untuk sekolah dan pesan untuk orang lain',
        ],
        'contoh'    => [
            'Hari Pertama Anak Saya Berangkat Sendiri ke Sekolah',
            'Yang Saya Pelajari dari Kelas yang Tidak Pernah Diam',
        ],
        'panduan'   => 'Tulis dalam sudut pandang orang pertama (saya/aku) atas nama penutur. Pertahankan suara dan perasaan yang ada di data; jangan menambah kejadian atau perasaan yang tidak disebutkan. Bahasa boleh santai, kalimat boleh pendek.',
        'foto_label' => 'Foto pendukung',
        'bagian'    => [
            [
                'judul'  => 'Siapa yang bercerita?',
                'info'   => 'Perkenalkan diri penutur.',
                'fields' => [
                    [
                        'nama' => 'penutur', 'label' => 'Saya adalah...', 'tipe' => 'pilihan', 'wajib' => true,
                        'opsi' => ['Siswa', 'Guru', 'Orang tua', 'Kepala sekolah', 'Alumni', 'Lainnya'],
                    ],
                    ['nama' => 'hubungan', 'label' => 'Apa hubungan Anda dengan sekolah?', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Orang tua murid kelas 2', 'bantuan' => 'Nama boleh diganti sebutan, misalnya "seorang guru".'],
                ],
            ],
            [
                'judul'  => 'Ceritanya',
                'info'   => 'Tulis sebagaimana penutur akan menceritakannya.',
                'fields' => [
                    ['nama' => 'tema', 'label' => 'Apa yang ingin diceritakan?', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Tema atau kejadian yang ingin diceritakan...'],
                    ['nama' => 'pengalaman', 'label' => 'Pengalaman paling berkesan', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Apa yang terjadi? Tulis dengan kata-katamu sendiri...'],
                    ['nama' => 'makna', 'label' => 'Apa yang membuat pengalaman tersebut berarti?', 'tipe' => 'panjang', 'placeholder' => 'Senang, haru, cemas, bangga...'],
                    ['nama' => 'perubahan', 'label' => 'Perubahan yang dirasakan', 'tipe' => 'panjang'],
                ],
            ],
            [
                'judul'  => 'Harapan dan pesan',
                'info'   => 'Penutup cerita.',
                'fields' => [
                    ['nama' => 'disyukuri', 'label' => 'Hal yang paling disyukuri', 'tipe' => 'panjang'],
                    ['nama' => 'harapan', 'label' => 'Harapan untuk sekolah', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                    ['nama' => 'pesan', 'label' => 'Pesan untuk orang lain', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'kekompakan-komunitas' => [
        'nama'      => 'Kekompakan Komunitas',
        'ikon'      => 'fa-handshake',
        'warna'     => 'orange-bg',
        'ringkas'   => 'Ceritakan kerja sama dan kebersamaan.',
        'deskripsi' => 'Kekompakan Komunitas menceritakan saat sekolah, orang tua, warga, dan pihak lain bekerja bersama: gotong royong membangun, patungan membeli perlengkapan, atau bergantian menjaga kegiatan. Artikel memperlihatkan siapa berbuat apa, dan bagaimana kebersamaan itu terasa.',
        'cocok'     => 'Mendokumentasikan gotong royong, berterima kasih kepada warga dan orang tua, atau mengajak pihak lain ikut terlibat.',
        'siapkan'   => [
            'Nama kegiatan kolaborasi, tanggal, dan tempatnya',
            'Siapa saja yang terlibat dan peran masing-masing',
            'Bagaimana kerja sama berjalan',
            'Hasil, manfaat, dan momen yang terasa hangat',
        ],
        'contoh'    => [
            'Sabtu Pagi, Cat Baru, dan Tiga Puluh Tangan yang Membantu',
            'Warga Menyumbang Bambu, Guru Membuat Rak Buku',
        ],
        'panduan'   => 'Tulis sebagai cerita kebersamaan yang menyebut peran tiap pihak dengan jelas. Tunjukkan kerja sama lewat kejadian nyata dari data. Jangan menyebut jumlah orang, dana, atau bantuan yang tidak ada di data.',
        'foto_label' => 'Foto kegiatan',
        'bagian'    => [
            [
                'judul'  => 'Kerja samanya',
                'info'   => 'Apa yang dikerjakan bersama?',
                'fields' => [
                    ['nama' => 'nama_kegiatan', 'label' => 'Nama kegiatan atau kolaborasi', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Kerja bakti memperbaiki pagar sekolah'],
                    ['nama' => 'pihak', 'label' => 'Pihak yang terlibat', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Guru, orang tua, warga, pemerintah desa, dan lainnya'],
                    ['nama' => 'tanggal', 'label' => 'Tanggal kegiatan', 'tipe' => 'tanggal'],
                    ['nama' => 'tempat', 'label' => 'Tempat kegiatan', 'tipe' => 'teks'],
                ],
            ],
            [
                'judul'  => 'Latar dan tujuan',
                'info'   => 'Mengapa kerja sama ini dilakukan?',
                'fields' => [
                    ['nama' => 'latar', 'label' => 'Latar belakang kegiatan', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Kebutuhan atau masalah yang melatarbelakangi...'],
                    ['nama' => 'tujuan', 'label' => 'Apa tujuan kolaborasi?', 'tipe' => 'panjang'],
                    ['nama' => 'peran', 'label' => 'Apa peran masing-masing pihak?', 'tipe' => 'panjang', 'wajib' => true],
                ],
            ],
            [
                'judul'  => 'Jalannya dan hasilnya',
                'info'   => 'Apa yang terjadi dan apa yang tersisa?',
                'fields' => [
                    ['nama' => 'proses', 'label' => 'Bagaimana proses kerja samanya?', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'menarik', 'label' => 'Hal menarik selama kegiatan', 'tipe' => 'panjang'],
                    ['nama' => 'hasil', 'label' => 'Hasil yang dicapai', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'manfaat', 'label' => 'Manfaat bagi sekolah atau masyarakat', 'tipe' => 'panjang'],
                    ['nama' => 'momen', 'label' => 'Momen paling berkesan', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ */
    'tujuh-kebiasaan' => [
        'nama'      => '7 Kebiasaan Anak Indonesia Hebat',
        'ikon'      => 'fa-seedling',
        'warna'     => 'green-bg',
        'ringkas'   => 'Ceritakan kebiasaan baik yang diterapkan anak.',
        'deskripsi' => 'Artikel ini menceritakan bagaimana anak-anak di sekolah membiasakan tujuh kebiasaan baik: bangun pagi, beribadah, berolahraga, makan sehat dan bergizi, gemar belajar, bermasyarakat, dan tidur cepat. Pilih satu atau beberapa kebiasaan, lalu ceritakan bagaimana sekolah dan keluarga membantu anak menjalankannya setiap hari.',
        'cocok'     => 'Melaporkan pembiasaan di kelas, berbagi cara guru dan orang tua mendampingi anak, atau menunjukkan perubahan kecil yang mulai terlihat.',
        'siapkan'   => [
            'Nama siswa atau kelas dan kebiasaan mana yang diceritakan',
            'Kegiatan dan cara sekolah membiasakannya',
            'Perubahan yang mulai terlihat pada anak',
            'Kesulitan yang dihadapi dan siapa yang mendampingi',
        ],
        'contoh'    => [
            'Sarapan Bersama di Kelas 1: Awal Baru untuk Pagi yang Lebih Segar',
            'Absen Bangun Pagi dan Anak-Anak yang Kini Datang Lebih Awal',
        ],
        'panduan'   => 'Tulis sebagai cerita pembiasaan yang konkret. Hubungkan kebiasaan yang dipilih dengan kejadian nyata di sekolah dari data. Nada positif dan mengajak, tanpa menggurui. Jangan menambah data tentang perubahan perilaku yang tidak disebut.',
        'foto_label' => 'Foto kegiatan',
        'bagian'    => [
            [
                'judul'  => 'Kebiasaan yang diceritakan',
                'info'   => 'Pilih satu atau lebih.',
                'fields' => [
                    ['nama' => 'nama_siswa', 'label' => 'Nama siswa atau kelas', 'tipe' => 'teks', 'wajib' => true, 'placeholder' => 'Contoh: Kelas 2 dan kelas 3'],
                    [
                        'nama' => 'kebiasaan', 'label' => 'Kebiasaan yang ingin diceritakan', 'tipe' => 'banyak', 'wajib' => true,
                        'opsi' => [
                            'Bangun pagi',
                            'Beribadah',
                            'Berolahraga',
                            'Makan sehat dan bergizi',
                            'Gemar belajar',
                            'Bermasyarakat',
                            'Tidur cepat',
                        ],
                    ],
                ],
            ],
            [
                'judul'  => 'Cara membiasakan',
                'info'   => 'Bagaimana sekolah dan keluarga membantu anak?',
                'fields' => [
                    ['nama' => 'kegiatan', 'label' => 'Ceritakan kegiatan yang dilakukan', 'tipe' => 'panjang', 'wajib' => true, 'placeholder' => 'Kegiatan harian atau aturan kelas...'],
                    ['nama' => 'cara', 'label' => 'Bagaimana kebiasaan tersebut diterapkan?', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'pendamping', 'label' => 'Siapa yang mendampingi?', 'tipe' => 'panjang', 'placeholder' => 'Guru, orang tua, atau pihak lain...'],
                ],
            ],
            [
                'judul'  => 'Tantangan dan hasil',
                'info'   => 'Apa yang sulit dan apa yang berubah?',
                'fields' => [
                    ['nama' => 'tantangan', 'label' => 'Tantangan yang dihadapi', 'tipe' => 'panjang'],
                    ['nama' => 'perubahan', 'label' => 'Perubahan yang dirasakan', 'tipe' => 'panjang', 'wajib' => true],
                    ['nama' => 'hasil', 'label' => 'Hasil atau pencapaian', 'tipe' => 'panjang'],
                    ['nama' => 'pesan', 'label' => 'Pesan untuk siswa atau orang tua', 'tipe' => 'panjang', 'bantuan' => 'Boleh dikosongkan.'],
                ],
            ],
        ],
    ],

];
