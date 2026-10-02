# Email verifikasi Moshia

Status 2 Oktober 2026: Gmail SMTP telah dikonfigurasi. Pengguna mengonfirmasi
registrasi, penerimaan email, dan verifikasi berhasil sebelum perubahan desain email.
Template verifikasi sekarang memakai warna Moshia (#09090c, #111114, #f02b35),
CSS inline, tombol aksi, tautan cadangan, dan versi teks biasa. Template berada di
`resources/views/emails/verify-email*.blade.php`; perubahan template tidak
memerlukan build Vite. Tampilan template baru di inbox belum diverifikasi.

Registrasi membuat akun dan workspace secara atomik, lalu mengirim tautan verifikasi.
Akun langsung login dengan status belum terverifikasi, tetapi dashboard, admin,
pembuatan/pemilihan workspace memerlukan email terverifikasi. Profil tetap dapat
diakses agar pengguna dapat memperbaiki email, lalu meminta tautan baru.

Tautan ditandatangani, terikat pada ID akun dan email, dan kedaluwarsa setelah 60
menit secara default. Membuka tautan saat logout memerlukan login akun yang sama.
Role dan tujuan login yang diizinkan tetap diperiksa setelah verifikasi.

## Gmail sementara

1. Buat Gmail khusus Moshia dan aktifkan Verifikasi 2 Langkah.
2. Buat App Password untuk Moshia melalui https://myaccount.google.com/apppasswords.
   Gunakan App Password, bukan password login Gmail. Opsi ini bergantung pada
   kebijakan dan pengaturan akun Google.
3. Isi `.env` lokal menggunakan konfigurasi berikut. Nilai contoh harus diganti;
   jangan commit kredensial atau menyalin App Password ke chat.

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamat-gmail-moshia@gmail.com
MAIL_PASSWORD="APP_PASSWORD_ANDA"
MAIL_FROM_ADDRESS="alamat-gmail-moshia@gmail.com"
MAIL_FROM_NAME="Moshia"
MAIL_TIMEOUT=15
MAIL_REQUIRE_TLS=true
```

`smtp` pada port 587 memakai STARTTLS. Jangan mematikan verifikasi sertifikat TLS.
Pastikan tidak ada `MAIL_URL` lama yang menimpa konfigurasi SMTP individual.
Pastikan `APP_URL` sesuai alamat aplikasi yang dapat dibuka penerima email.
Untuk pengujian lokal, buka tautan pada perangkat yang dapat mengakses server lokal.

4. Jalankan `php artisan config:clear` setelah mengubah `.env`.
5. Uji dengan registrasi akun uji melalui browser, buka email, dan verifikasi bahwa
   dashboard baru dapat dibuka setelah tautan valid diklik. Pengujian otomatis
   memakai notification fake / mailer array dan tidak membuktikan pengiriman inbox.

Untuk pengujian tanpa pengiriman, `.env` dapat memakai `MAIL_MAILER=log`. Mode ini
menulis isi email ke log lokal, tidak mengirim email ke inbox. Tautan dalam log
bersifat sensitif; gunakan hanya untuk pengujian lokal, jangan dipublikasikan.

## Beralih ke email domain Moshia

Gunakan mailbox/provider SMTP domain yang sudah disiapkan. Ubah `MAIL_HOST`,
`MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, dan
`MAIL_FROM_ADDRESS` sesuai provider; pertahankan `MAIL_MAILER=smtp` dan
`MAIL_FROM_NAME=Moshia`. Untuk TLS langsung di port 465 gunakan `MAIL_SCHEME=smtps`.
Kode registrasi dan verifikasi tidak perlu diganti. Ikuti petunjuk provider untuk
verifikasi domain, SPF/DKIM/DMARC, lalu lakukan pengujian pengiriman nyata.

Email saat ini dikirim sinkron dengan timeout SMTP. Jika transport gagal, akun
tetap tersimpan dan login, tetapi tetap belum terverifikasi; halaman menampilkan
pesan kegagalan dan menyediakan kirim ulang. Pengiriman via queue dan pemantauan
delivery/bounce merupakan pengembangan berikutnya.

## Akun lama

Tidak ada perubahan massal pada `email_verified_at`. Akun yang sudah terverifikasi
tetap dapat mengakses aplikasi. Akun lama yang belum terverifikasi mengikuti aturan
verifikasi yang sama. Perubahan alamat email membatalkan verifikasi sebelumnya.

Referensi Google:
- https://support.google.com/mail/answer/7104828
- https://support.google.com/accounts/answer/185833
