# UAT Wedding — 8 Oktober 2026

Status: ALUR LENGKAP BELUM DIJALANKAN DI BROWSER. Preview fixture desktop/mobile tercatat pada sesi sebelumnya. Gunakan akun pengujian dan rekening dummy development, bukan transfer uang nyata. Tes otomatis terbaru 187/1.640 lulus tidak menggantikan UAT ini.

## Persiapan

- [ ] Buka project dan database yang benar; gunakan APP_ENV=local dengan pembayaran manual development aktif.
- [ ] Siapkan akun pengguna terverifikasi dan superadmin. Gunakan profil browser terpisah agar sesi tidak tertukar.
- [ ] Catat browser, ukuran layar, tanggal dan akun pengujian tanpa password.

## Alur utama

- [ ] Login pengguna. Sidebar memuat Dashboard Workspace, Wedding Invitation, Jastip Manager, Photo Booth System, Restaurant Manager; tidak ada Profil di sidebar.
- [ ] Buka tiga produk selain Wedding: masing-masing menampilkan Product Coming Soon.
- [ ] Buka Wedding tanpa workspace: tombol mengarahkan ke pembuatan workspace.
- [ ] Buat/pilih workspace sendiri; buka Wedding, lihat Gold/Emerald/Diamond beserta harga dan durasi yang benar.
- [ ] Klik Pilih Gold/Emerald/Diamond. Billing menampilkan ringkasan keranjang satu paket (bukan tiga card lagi), total server dan metode transfer BCA; belum membuat order. Ganti paket kembali ke perbandingan; Batal membuka riwayat tanpa order baru.
- [ ] Make payment membuat pesanan lalu membuka billing tanpa parameter package; riwayat/countdown dan instruksi pembayaran tampil. Refresh tidak menggandakan pesanan.
- [ ] Buat pesanan; jumlah sesuai snapshot server dan countdown 24 jam berjalan.
- [ ] Sebelum diterima, pengguna belum dapat mengakses editor/publish.
- [ ] Login superadmin; buka Pembayaran Manual dan terima pesanan pengujian yang benar.
- [ ] Kembali ke pengguna: editor terbuka; masa aktif belum dimulai.
- [ ] Isi data pasangan, tanggal, lokasi dan pesan; simpan draft; buka preview privat.
- [ ] Publish: URL publik bekerja; masa aktif mulai publish pertama.
- [ ] Edit/simpan tanpa publish: preview berubah tetapi halaman publik tetap versi sebelumnya.
- [ ] Publish ulang: halaman publik diperbarui tanpa menggeser akhir masa aktif.
- [ ] Pilih salah satu dari tiga tema; upload foto/MP3, pilih cover/galeri, simpan, periksa preview dan publik setelah Publish.
- [ ] Kirim RSVP/wishes dari pengunjung; owner melihat respons dan menyetujui ucapan sebelum tampil publik.
- [ ] Klik Unpublish dan konfirmasi: halaman undangan, URL media publik dan pengiriman respons ditutup. Preview privat, konten, file serta respons lama tetap tersedia.
- [ ] Publish kembali: tautan sama dibuka, tanggal akhir tidak bergeser. Akun lain/superadmin yang bukan owner tidak bisa Unpublish.
- [ ] Buka akun → Billing: riwayat workspace sendiri muncul; belum dibayar tidak tertulis sebagai dibayar.

## Tampilan dan akses

- [ ] Coba akun pengguna kedua: URL editor/preview/billing workspace akun pertama ditolak.
- [ ] Uji menu akun, logout, informasi akun, security dan tampilan history pembayaran.
- [ ] Dark mode tersimpan setelah refresh; default dashboard terang.
- [ ] Layar mobile: drawer dapat dibuka/ditutup, Escape bekerja, tabel tidak merusak layout, kartu paket bertumpuk.
- [ ] Keyboard: urutan fokus jelas, menu dapat ditutup, tombol tetap terjangkau.
- [ ] Account activity/Notification settings menjelaskan fitur yang belum tersedia, tanpa data palsu.

## Skenario waktu pada lingkungan uji terpisah

- [ ] Order belum dibayar berumur 24 jam tidak dapat diterima; order baru diperbolehkan.
- [ ] Saat ends_at tercapai, akses publik/editor ditolak tetapi data tersimpan.
- [ ] Order reaktivasi baru diterima: editor terbuka, halaman publik tetap tertutup hingga publish baru.
- [ ] Publish reaktivasi menggunakan undangan lama dan periode baru.

Jangan memajukan jam sistem atau mengubah tanggal pesanan nyata. Skenario waktu sudah memiliki tes otomatis; UAT waktu memerlukan fixture/database uji terpisah.

## Catatan hasil

| Skenario | Hasil aktual | Lulus/gagal | Bukti/defect |
| --- | --- | --- | --- |
| Alur utama | Belum diuji | Pending | — |
| Mobile/tema/keyboard | Belum diuji | Pending | — |
| Isolasi akun | Belum diuji melalui browser | Pending | — |
| Expiry/reaktivasi | Tes otomatis lulus; UAT pending | Pending | — |

Sesudah UAT, tutup defect yang ditemukan lalu lengkapi D04, retensi/media C06 serta video/domain sesuai scope. W01/W02 sudah tersedia untuk editor dan tema lokal; tidak perlu dibangun ulang. Jangan mencentang W07 sampai scope rilis terkait benar-benar lulus.
