# Wedding Studio — 8 Oktober 2026

## Tersedia pada kode

Editor enam langkah: template, pasangan, acara, foto/musik, RSVP/wishes, serta preview/publish. Tiga tema awal: Classic Ivory, Garden Sage dan Midnight Gold. Form mencakup nama dan orang tua pasangan, foto pasangan, tanggal/jam/zona waktu, lokasi/tautan peta, resepsi opsional, cerita, cover, galeri dan musik MP3.

Simpan draft memperbarui preview privat. Publish menyalin draft menjadi versi publik; edit berikutnya tidak langsung mengubah versi publik. Masa aktif mulai pada publish pertama dan tidak bertambah saat publish ulang. Masa aktif habis menutup akses publik dengan data tetap tersimpan.

Media disimpan privat melalui Core Media. URL publik hanya melayani file yang tercantum dalam snapshot undangan aktif. Respons tamu memiliki validasi, pembatasan frekuensi dan ID submission untuk mencegah duplikasi pengiriman. Owner melihat RSVP dan menyetujui wishes sebelum ditampilkan publik. Musik menggunakan kontrol pemutar, tidak autoplay.

## Cara review di aplikasi

1. Login akun pemilik workspace yang terverifikasi.
2. Buka Wedding Invitation melalui sidebar atau tombol Lihat paket di dashboard.
3. Pilih paket, buat order development; superadmin melakukan Payment accepted.
4. Masuk editor Wedding, isi minimal nama pasangan, tanggal dan lokasi.
5. Pilih template, unggah foto/MP3, pilih cover/galeri, aktifkan RSVP/wishes lalu simpan draft.
6. Buka preview, periksa hasilnya, kemudian publish.
7. Buka URL publik tanpa login, kirim RSVP dan wishes; kembali ke editor untuk moderasi.
8. Ubah draft, pastikan publik baru berubah setelah publish ulang.

Rekening masih dummy development. Jangan menerima pembayaran nyata berdasarkan konfigurasi ini.

## Batas teknis dan pekerjaan tersisa

- Foto JPG/PNG/WebP maksimal 5 MB dan dimensi 8000 piksel; MP3 maksimal 15 MB.
- Maksimal 12 foto galeri dan 50 file per workspace/koleksi. Ini batas teknis awal, bukan kuota komersial paket.
- Penghapusan file tidak terpakai, retensi dan S3/CDN belum tersedia.
- Pengelolaan daftar tamu dengan undangan personal belum tersedia; RSVP publik belum memverifikasi identitas pengirim.
- Domain .com manual, subdomain HTTPS, video header sesuai paket, Midtrans dan unpublish manual belum selesai.
- Template berupa tiga tema konfigurasi, belum ada pengelolaan katalog template oleh admin.

## Bukti verifikasi

Suite Laravel: 185 tes / 1.611 assertions lulus. Meliputi isolasi tenant, file invalid, pemisahan draft/published, expiry, moderasi dan preservasi data migrasi media. Migration 000010 dan 000011 berhasil di database lokal.

Build frontend terakhir berhasil (807 modul). Preview browser dengan fixture editor diuji pada desktop dan mobile: tidak ditemukan error runtime atau horizontal overflow. Ini bukan UAT login–upload–pembayaran–publish dengan data nyata. Lanjutkan UAT manual di atas.

Lihat [arsitektur dan tabel](MODULAR_MONOLITH.md) serta [checklist](CHECKLIST.md).
