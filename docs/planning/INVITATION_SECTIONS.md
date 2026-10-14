# Susunan undangan Wedding — 14 Oktober 2026

## Cara mengisi

Wedding Studio > **Susunan undangan** menggantikan tab Foto & musik.

1. **Foto cover/sampul**: upload/pilih foto pembuka.
2. **Background header**: upload foto; Emerald/Diamond dapat memilih video MP4/WebM. Gold hanya foto.
3. **Foto Wedding**: upload foto dan tulis ayat/kata-kata sendiri.
4. **Save the date**: judul dan kata-kata; tanggal, lokasi, countdown dan resepsi mengikuti Detail acara.
5. **Our Story Love**: tiga bagian awal Perkenalan, Pacaran, Engagement. Setiap bagian memiliki judul, foto dan tulisan; bisa ditambah/dihapus.
6. **RSVP**: memakai pengaturan dan moderasi yang sudah tersedia.
7. **Wedding Gift**: aktifkan, tambah rekening, pilih bank, isi nomor dan nama pemilik. Nomor rekening disimpan sebagai teks agar nol di depan tidak hilang.
8. **Thank you page**: background, judul, kata-kata dan nama pengantin otomatis sesuai urutan pasangan.
9. **Footer**: Made with love - Moshia Invitation.

Galeri tambahan dan musik MP3 tetap tersedia setelah bagian utama. Simpan draft memperbarui preview; Publish memperbarui snapshot publik. Perubahan draft tidak langsung mengubah undangan terbit.

## Bank dan media

Tabel core_banks berisi Bank BCA, Bank Mandiri, Bank BNI, Bank Permata, Bank CIMB, Bank Jago. Superadmin menambah bank melalui **Bank Wedding Gift** pada sidebar. Ini katalog bank untuk hadiah tamu, bukan rekening pembayaran paket. Nama bank disalin ke snapshot undangan saat menyimpan, sehingga perubahan katalog tidak mengubah undangan terbit.

Semua foto/video/MP3 disimpan privat di Core Media. Hanya media dalam snapshot undangan aktif yang dapat diakses publik. Validasi server memeriksa tenant, koleksi, jenis media dan aturan paket. Gold ditolak saat upload, pemilihan maupun publish video. Publish memeriksa paket saat ini agar pergantian paket tidak melewati pembatasan.

Batas teknis: gambar 5 MB, MP3 15 MB, video 30 MB, 50 file per workspace/koleksi, galeri 12, cerita 12, rekening 6. Batas ini merupakan perlindungan teknis, bukan keputusan kuota komersial. PHP CLI device diperiksa: upload_max_filesize/post_max_size 40M; konfigurasi web server tujuan tetap harus diperiksa. Video tidak ditranscode; gunakan codec yang didukung browser (umumnya MP4 H.264 atau WebM). Penghapusan bagian hanya menghapus referensi dari draft, tidak menghapus file fisik.

## Domain undangan

Konfigurasi opsional di environment server:
WEDDING_PUBLIC_DOMAIN=invitation.moshia.id

Tanpa konfigurasi itu, URL lokal /invitation/{slug} tetap digunakan. Jika diaktifkan, URL kanonis menjadi https://invitation.moshia.id/{slug} pada deployment HTTPS, dengan slug UUID yang sama. Tautan lama dialihkan ke domain kanonis dan query dipertahankan. Route media/RSVP mengikuti domain tersebut.

DNS, HTTPS, virtual host ke public Laravel, APP_URL/proxy dan konfigurasi session harus disiapkan pada server. Sesi ini tidak mengaktifkan domain, mengedit .env, atau deploy. Pembatasan development payment masih berlaku; konfigurasi domain tidak otomatis mengaktifkan penjualan/undangan production.

## Verifikasi dan handoff

- Migration 2026_10_14_000012_create_core_banks_table berhasil di database lokal; tidak reset DB.
- Suite penuh: 204 tes / 2.005 assertions lulus. Mencakup snapshot konten/Gift, validasi media lintas tenant, pembatasan video, bank superadmin dan URL domain/redirect.
- Build disetujui: 818 modul berhasil.
- Fixture browser desktop/mobile: sembilan bagian, cerita 3→4→3, pilihan video dan bank; tidak ada error runtime. Lebar form mobile diperbaiki dan diperiksa ulang.
- Belum UAT upload video nyata, playback lintas perangkat, pembayaran/publish domain production, DNS/HTTPS.
- Situs referensi pengguna tidak dapat diakses lewat alat penelusuran; implementasi mengikuti daftar kebutuhan dan screenshot, bukan klaim salinan visual situs tersebut.
