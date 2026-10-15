# Update Moshia — 15 Oktober 2026

## Layout publish dan editor — 15 Oktober 2026

- [x] Layout desktop vertikal 430 px dengan background foto/warna, serta mobile selebar layar.
- [x] Cover editable, nama tamu melalui parameter `?to=`, tombol buka, header foto/video sesuai paket, navigasi bawah, musik.
- [x] Tiga belas bagian dapat diurutkan dan ditampilkan/disembunyikan; editor memilih satu bagian melalui **Edit isi**.
- [x] Countdown acara, galeri, cerita, rekening hadiah aktif per rekening, thank you dan footer.
- [x] CAPTCHA matematika berbasis session, berlaku 30 menit dan sekali pakai; moderasi ucapan tetap berlaku, pagination 5 ucapan.
- [x] Suite penuh **207 tes / 2.045 assertions** lulus; build **818 modul**. Fixture browser: desktop 430 px, mobile 390 px tanpa overflow, tombol buka bekerja, 13 tombol Edit isi, tidak ada exception runtime.
- [ ] UAT dengan foto/video/MP3 pengguna pada perangkat nyata dan pemeriksaan deployment/domain.
- Detail: [handoff 15 Oktober](../update/MOSHIAUPDATE-15102026.md). Tidak ada migrasi database baru.


## Permintaan dan implementasi

Hasil publish mengikuti kerangka screenshot: pembuka, header, foto/ayat, pengantin pria dan wanita, acara, cerita, galeri, RSVP, pesan, hadiah, penutup dan footer. Tampilan menggunakan tema Moshia dengan ornamen CSS; tidak menyalin aset floral maupun foto milik referensi.

Susunan tersimpan dalam JSON draft_content/published_content, tanpa tabel atau migration baru. Semua 13 bagian memiliki toggle serta tombol naik/turun. Cover yang aktif menjadi gerbang pembuka; setelah dibuka seluruh bagian mengikuti urutan pengguna. Menonaktifkan bagian tidak menghapus isi. Media bagian tersembunyi tidak disajikan melalui endpoint media publik.

Editor Susunan undangan menyediakan Edit isi per bagian. Pengaturan mempelai dan RSVP menuju tab terkait. Background desktop memiliki foto, warna dan opacity; teks cover dan tautan tamu dapat diedit. Nama dari ?to= hanya label tampilan, bukan autentikasi. Foto/video header tetap mengikuti entitlement paket. Musik berjalan setelah interaksi dan dapat dimatikan; kebijakan autoplay browser tetap berlaku.

RSVP menggunakan CAPTCHA matematika session dengan token acak, dibatasi invitation, kedaluwarsa 30 menit, sekali pakai, maksimal 20 tantangan tersimpan. Rate limit, honeypot dan moderasi tetap digunakan. Ini penghalang bot sederhana, bukan jaminan menolak semua bot. Error form membuka bagian RSVP otomatis. Ucapan approved tampil 5 per halaman; informasi kehadiran tidak diumumkan. Gift mendukung enabled per rekening. Footer: Made with love by Moshia.

Data lama mendapat susunan default, menghormati urutan pengantin dan pesan pembuka lama. Perubahan draft baru menjadi publik setelah Publish.

## Validasi sesi ini

Suite penuh 207 tes / 2045 assertions lulus. Build Vite 818 modul berhasil. Percobaan awal dalam sandbox gagal menulis storage/cache karena lokasi repository sebenarnya di MOSHIA-PROJECT, di luar writable root; pengujian diulang dengan izin sesuai lokasi dan lulus.

Fixture browser lokal tanpa perubahan database: ukuran frame desktop 430 px; mobile 390 px tanpa overflow; cover gate dan tombol buka; editor menampilkan 13 tombol Edit isi; tidak ada exception runtime. Screenshot mobile ditinjau dengan fixture tanpa foto. Pengujian otomatis mencakup urutan, visibilitas, escaping nama tamu, snapshot, pagination, rekening nonaktif dan CAPTCHA wajib/kedaluwarsa/lintas undangan/sekali pakai.

## Batas dan langkah berikutnya

Lakukan UAT dengan foto/video/MP3 asli dan dua paket video, cek editor di akun pengguna, Simpan draft → Preview → Publish. Foto/video aktual dan audio browser belum diuji menyeluruh sesi ini. DNS, HTTPS, konfigurasi domain produksi dan deployment tidak diubah. Tidak menjalankan migrasi/reset database atau mengubah harga/role.

Folder kerja aktual: D:/XAMPP8212/htdocs/MOSHIA-PROJECT. Perubahan sesi sebelumnya masih ada; jangan membuang perubahan yang belum di-commit.

## Alur pengisian per bagian — 15 Oktober 2026

- [x] Navigasi utama menjadi Pilih template → Susunan undangan → Preview & publish. Memilih template langsung membuka daftar.
- [x] Edit isi membuka satu detail dan menyembunyikan daftar; tersedia kembali ke susunan, toggle tampil, dan bagian berikutnya mengikuti urutan pengguna.
- [x] Pengantin pria/wanita diedit terpisah; Save the Date dan detail acara disatukan. Musik tersedia dari daftar. Isian tetap ada saat berpindah; Simpan draft tetap diperlukan untuk penyimpanan server.
- [x] Build terbaru 818 modul berhasil. Fixture browser memverifikasi daftar setelah template, detail cover, isian bertahan, pengantin terpisah dan acara dalam satu detail; tanpa exception runtime.
- Catatan: 207 tes / 2045 assertions di atas merupakan hasil sesi sebelumnya; perubahan kali ini hanya editor dan dokumentasi. UAT upload/publish dengan akun pengguna tetap perlu dilakukan.

## Preview kanan per bagian — 15 Oktober 2026

- [x] Preview kanan mengikuti bagian yang sedang diedit: cover, header, ayat, masing-masing pengantin, acara/resepsi, cerita, galeri, RSVP, ucapan, gift, thank you, footer dan musik.
- [x] Isian teks dan media terpilih bersifat reaktif tanpa menyimpan draft; bagian nonaktif diberi keterangan tersembunyi saat publish. Video/audio memakai kontrol manual, form RSVP preview nonaktif.
- [x] Build 820 modul berhasil. Fixture browser memverifikasi perpindahan section, perubahan judul Thank you langsung terlihat, serta pemberitahuan bagian tersembunyi; tidak ada exception runtime.
- Preview kanan merupakan representasi bagian, bukan render lengkap halaman publish. Media terlihat setelah upload berhasil/pemilihan media; pemeriksaan file asli di perangkat pengguna tetap diperlukan. Tidak ada perubahan backend/migrasi pada tahap ini.

## Preview seluruh susunan — 15 Oktober 2026

- [x] Pada daftar susunan dan tahap review, preview kanan menampilkan semua section dengan Tampil dicentang, sesuai urutan form, dalam satu area scroll. Checkbox dan perubahan urutan langsung memperbarui preview.
- [x] Saat Edit isi tetap memakai preview satu bagian; pemilihan template memakai preview cover. Jika semua checkbox dimatikan tersedia pesan kosong.
- [x] Build 820 modul berhasil. Fixture browser memeriksa 13 section, menghilangkan cover saat unchecked, serta memindahkan quote sebelum header. Alur edit per bagian tetap berjalan tanpa exception runtime.
- Representasi preview editor tetap memakai komponen Vue; Preview privat digunakan untuk meninjau render publish lengkap. Tidak ada perubahan database/backend.

## Slug undangan kustom — 15 Oktober 2026

- [x] Cover menyediakan input slug, Cek ketersediaan, dan Simpan slug terpisah dari draft. Contoh aditsella; 3–36 karakter huruf/angka/tanda hubung, dinormalisasi lowercase dan trim. Nama rute inti tertentu tidak dapat dipakai.
- [x] Endpoint check/save dilindungi auth, verified, policy update, editorOrder dan throttle. Validasi unique mengabaikan undangan sendiri tetapi mencakup undangan lain, termasuk draft/nonaktif. Unique index database tetap mencegah benturan bersamaan dan ditangani sebagai error validasi.
- [x] Rute lokal /invitation/{slug} dan domain invitation menerima slug kustom serta UUID lama. Nama tamu ?to= tetap tersedia. Save slug langsung mengganti URL; URL lama tidak diarahkan dan tidak berlaku lagi. Pemeriksaan tersedia bukan reservasi; server memeriksa ulang ketika menyimpan.
- [x] Tes Wedding 27 tes / 342 assertions lulus; build 820 modul berhasil. Tes mencakup normalisasi, duplikat, format, batas panjang, akses workspace lain, URL baru saat publish dan URL lama 404.
- Tidak ada migration baru: panjang dibatasi 36 sesuai kolom UUID/CHAR(36) pada MySQL saat ini. Deploy/DNS tidak diubah. Interaksi UI dengan akun pengguna masih perlu review.
