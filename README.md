# EduStory (generator-sekolah)

Guru memilih jenis artikel, mengisi form di web ini, lalu AI menulis artikelnya.
Hasilnya tampil di halaman artikel, bisa disalin, dan punya jalan pintas ke klipaa.

## Pasang (urutannya penting)

1. **Database.** Buka phpMyAdmin, pilih database EduStory, tab SQL, lalu jalankan
   isi `database/schema.sql` satu kali.

2. **Pengaturan.** Isi lewat Vercel (Settings > Environment Variables) atau, di komputer
   sendiri, salin `config/lokal.example.php` menjadi `config/lokal.php`.

   | Nama | Isi |
   |---|---|
   | `DB_PASS` | password database (wajib) |
   | `DB_HOST`, `DB_USER`, `DB_NAME` | hanya bila berbeda dari bawaan |
   | `AI_PROVIDER` | `gemini` (bawaan) atau `anthropic` |
   | `AI_API_KEY` | kunci API penyedia yang dipilih (wajib) |
   | `AI_MODEL` | opsional; kosong = model bawaan di `lib/ai.php` (Gemini: `gemini-3.6-flash`) |
   | `KLIPAA_URL_ARTIKEL` | link menu artikel klipaa, tulis `{kode_desa}` di tempat kodenya |
   | `BATAS_ARTIKEL_HARIAN` | opsional, bawaan 10 artikel per akun per hari |

3. **Deploy** seperti biasa. `Dockerfile.vercel` sudah memasang ekstensi gambar (GD)
   dan menaikkan batas unggah foto.

## Peta file

- `config/jenis-artikel.php` : 10 jenis artikel (deskripsi, isian form, arahan untuk AI). Edit di sini.
- `dashboard.php` : pilih jenis + daftar artikel
- `buat.php` : form (dibuat otomatis dari konfigurasi di atas)
- `proses/proses-artikel.php` : menyimpan isian dan foto
- `proses/generate.php` : memanggil AI
- `artikel.php` : hasil, tombol salin, foto, link klipaa
- `lib/ai.php` : penyusun perintah dan klien AI
- `gambar.php` : menampilkan foto dari database

## Data wilayah (kode Kemendagri)

Pilihan provinsi sampai desa/kelurahan dibaca dari berkas di `assets/wilayah/`
(tanpa layanan luar), lewat `assets/js/data-wilayah.js`. Kodenya kode Kemendagri
tanpa titik, misalnya Desa Cikembulan, Kadungora, Garut = `3205102003`
(di Kemendagri ditulis `32.05.10.2003`). Yang disimpan di tabel `sekolah`
adalah kode ini pada kolom `kode_provinsi`, `kode_kabupaten`, `kode_kecamatan`,
dan `kode_desa`.

Sumber data: Kepmendagri No. 300.2.2-2138 Tahun 2025 lewat
https://github.com/cahyadsn/wilayah (lisensi MIT). Untuk memperbarui, buat ulang
berkas di `assets/wilayah/` dari `db/wilayah.sql` repositori itu.

Akun yang mendaftar sebelum ini mungkin menyimpan kode lama (kode BPS dari
emsifa). Buka **Profil sekolah**, periksa pilihannya, lalu simpan; kodenya
otomatis diganti.

## Menyambung ke API klipaa nanti

Artikel tersimpan di tabel `artikel` (judul, isi) dan foto di `artikel_gambar`
(alamat foto: `gambar.php?t=<token>`). Tambahkan satu fungsi kirim di `lib/` yang dipanggil
setelah `generate.php` selesai, atau dari tombol di `artikel.php`.
