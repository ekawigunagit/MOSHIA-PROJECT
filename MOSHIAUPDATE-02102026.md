## Pembaruan 5 Oktober 2026 — aturan draft Wedding

Bagian ini mengoreksi catatan lama tentang paket yang hanya memiliki nama/deskripsi.

### Keputusan pengguna

- Gold Rp149.000 / 6 bulan; Emerald Rp249.000 / 12 bulan; Diamond Rp559.000 / 12 bulan.
- Rupiah utuh, sekali bayar untuk satu undangan pada satu workspace Wedding. Undangan tambahan memakai workspace tambahan dan pembelian paket tersendiri; akun tetap sama.
- Masa aktif dimulai saat publish pertama, bukan saat pembayaran; edit/publish ulang tidak mengulang masa aktif.
- Kedaluwarsa menutup akses publik tetapi mempertahankan data. Pembayaran kembali diperlukan untuk mengaktifkan undangan lama.
- Semua paket: template standard. Emerald/Diamond: request video header. Diamond: pembelian domain .com, pemeriksaan manual, persetujuan pelanggan, lalu pemrosesan tim.
- Kolaborasi/undangan anggota ditunda. Aturan satu undangan tidak diterapkan ke produk lain.

### Implementasi tahap ini

- config/wedding_plans.php menyimpan tiga pilihan resmi; harga tidak diterima dari browser.
- Migration 2026_10_05_000008 menambahkan core_plans.commercial_terms (JSON nullable). Draft lama tetap tanpa aturan hingga admin memilih paket.
- Admin → Draft Paket → Buat/Edit → pilih produk Wedding → pilih Gold/Emerald/Diamond → simpan. Nama/deskripsi tetap dapat diedit; pilihan tanpa aturan harga tetap tersedia.
- Model menyimpan snapshot aturan; edit dengan pilihan yang sama mempertahankan snapshot. Berpindah pilihan mengambil aturan pilihan baru. Mengosongkan pilihan menghapus aturan draft; mengganti produk harus mengosongkan pilihan Wedding.
- Validasi menolak pilihan Wedding pada produk lain dan input commercial_terms/price_amount langsung. Status tetap draft; tidak menciptakan entitlement atau mempublikasikan paket.
- Tidak otomatis membuat tiga baris paket atau mengimpor workbook ke database. Admin memilih aturan saat membuat/mengedit draft.
- Aturan masa aktif, satu undangan, reaktivasi, video dan domain baru tersimpan sebagai aturan draft. Penegakan lifecycle membutuhkan subscription/pembayaran dan modul Wedding; belum diimplementasikan pada tahap ini.

### Verifikasi dan lanjutan

- Suite: 132 tes lulus / 1.052 assertions (SQLite in-memory).
- Migration 000008 sukses di database lokal; npm run build sukses setelah approval, 796 modul.
- Tampilan browser dan alur pengguna manual belum diverifikasi.
- C01 SEBAGIAN: editor aturan Wedding tersedia; aktivasi/status komersial dan kebijakan yang belum diputuskan tetap terbuka.
- D02 SEBAGIAN: harga/durasi/model bayar/batas undangan jelas; trial, storage/media/kuota lain, pajak/refund/grace serta waktu mulai reaktivasi belum diputuskan. Jangan mengasumsikan gratis, unlimited atau tanpa pajak.
- Domain premium/batas biaya, biaya dan periode perpanjangan domain belum ditentukan; hanya ekstensi .com yang disepakati.
- Selanjutnya: rancang lifecycle pembayaran → akses editor sebelum publish → masa aktif sejak publish pertama. Jangan memulai ends_at saat pembayaran. Sepakati reaktivasi dan provider pembayaran sebelum aktivasi komersial.

---
# MOSHIA — Handoff dan status terbaru

**Diperbarui: 2 Oktober 2026, Asia/Jakarta (UTC+7).**

Dokumen ini adalah rangkuman keputusan percakapan dan hasil implementasi, bukan transkrip kata demi kata. Bagian **Status terkini** di awal adalah acuan utama; bagian riwayat di bawah dipertahankan agar alasan perubahan tidak hilang. Pernyataan lama tentang fitur yang belum ada tidak berlaku jika sudah diperbarui di bagian terkini.

Nama file handoff sekarang: **MOSHIAUPDATE-02102026.md**, tanpa spasi. Pengguna telah mengganti nama file lama; jangan membuat ulang nama lama. Workspace aktif adalah `D:\XAMPP8212\htdocs\MOSHIA-PROJECT`.

## Status terkini — baca bagian ini terlebih dahulu

### A. Arah project dan cara bekerja

- Moshia adalah platform modular monolith Laravel: Core bersama dan empat produk terpisah, bukan microservices sejak awal.
- Blueprint utama: http://72.62.240.106:8787/moshia-ZquktHzrKNCnAat1/
- Urutan yang disetujui: **selesaikan Core, lalu Wedding MVP, Jastip, Photo Booth, Restaurant**.
- Stack dipertahankan: Laravel 12/PHP 8.2, Breeze, Vue 3, Inertia 2, Tailwind 3, Vite, MySQL, Spatie Permission. Node 22.23.3 dipin melalui Volta untuk tidak mengganggu project lain.
- Desain mengikuti referensi awal hitam-merah, Instrument Sans, robot/logo SVG sementara, responsif, tema terang/gelap, menu mobile, tombol kembali ke atas.
- Jelaskan alur dalam Bahasa Indonesia sebelum implementasi, lalu lanjutkan pekerjaan yang diminta. Pertanyaan penjelasan bukan izin otomatis mengubah akun/database.
- **Setiap `npm run build` / `npm run dev` wajib approval lewat tombol tool.** Jangan mengganti dengan perintah lain untuk melewati persetujuan atau membuat izin permanen baru.
- Jangan reset database, memberikan role, mengubah harga, atau menimpa perubahan pengguna tanpa instruksi yang sesuai.
- Jangan memasukkan password, App Password Gmail, APP_KEY, atau isi rahasia `.env` ke dokumen/Git.
- Izin filesystem harus mengikuti lingkungan sesi aktif. Pada sesi terakhir, folder project baru masih memerlukan approval karena writable root tool menunjuk folder lama; catatan izin dari device lain bukan otorisasi otomatis.

### B. Implementasi yang sudah ada

| Area | Kondisi aktual |
| --- | --- |
| Website utama | Landing responsif, tema, hero, katalog empat produk, tombol kembali ke atas |
| Identity | Registrasi/login/logout, profil/password, verifikasi email wajib, redirect menurut role dan intended URL yang diizinkan |
| Email | Gmail SMTP dan email verifikasi bermerek sudah dikerjakan di device lain; pengiriman nyata tidak diuji ulang pada device lokal ini |
| Superadmin | Akun ekawigunawork@gmail.com adalah superadmin menurut pemeriksaan device sebelumnya; dashboard `/admin` berbeda dari dashboard pelanggan `/dashboard` |
| Admin pengguna | Daftar/filter/pagination, edit nama/email, assignment role yang sudah tersedia, policy server, pembatasan perubahan role sendiri |
| Katalog | Empat produk berbasis `core_products`, editor admin, ID numerik terpisah dari slug string, status/visibility/urutan |
| Workspace | Pembuatan, keanggotaan pemilik, pemilihan workspace tervalidasi server, akun baru mendapat workspace awal |
| Keamanan penghapusan | Pemilik workspace dan superadmin terakhir ditolak saat menghapus akun; relasi owner memakai restrict, bukan cascade |
| Entitlement | Relasi `product_id` ke produk, tenant scope/keanggotaan, status aktif/trial dan waktu berlaku; tanpa bypass superadmin |
| Draft Paket | Form admin memilih produk, nama dan deskripsi; selalu draft; bukan penjualan aktif |
| Notifikasi | Lonceng database per akun, badge belum dibaca, daftar/pagination, tandai satu/semua dibaca, refresh berkala dan penanganan error |

Keempat produk masih katalog/roadmap. Belum ada alur operasional Wedding, Jastip, Photo Booth atau Restaurant yang lengkap. Subscription, checkout, invoice, kuota konsumsi, audit platform dan integrasi provider juga belum selesai.

### C. Penjelasan terakhir kepada pengguna: tabel draft paket

Draft paket berada di **`core_plans`**:

| Kolom | Arti |
| --- | --- |
| `id` | ID paket |
| `product_id` | Relasi ke `core_products.id` |
| `name` | Nama paket |
| `description` | Deskripsi paket |
| `status` | Saat ini `draft` |
| `created_at`, `updated_at` | Waktu pembuatan/pembaruan |

Harga, trial, dan kuota belum menjadi kolom aktif. Pengguna secara eksplisit meminta tetap memakai draft karena harga akan disusun sendiri. **Excel tidak otomatis diimpor ke `core_plans`.** Jangan mengaktifkan harga/pembelian sebelum data dan aturan ditinjau bersama pengguna.

### D. Excel yang sudah diberikan

File: [exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx](exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx)

- Snapshot saat ekspor: **0 draft paket dan 4 produk**. Ini kondisi saat ekspor, bukan jaminan jumlah saat membuka dokumen di device lain.
- Sheet `Panduan`: cara pengisian dan batas penggunaan workbook.
- Sheet `Draft Database`: data asli draft, saat itu kosong selain header.
- Sheet `Isian Paket`: empat baris TEMPLATE, satu per produk. Identitas produk diisi dari database; nama paket dan harga kosong untuk pengguna tentukan.
- Sheet `Kuota`: isi batas, satuan, periode dan catatan dengan mengacu `Ref Isian` yang sama.
- Sel kuning adalah masukan pengguna; tersedia dropdown, validasi angka, filter, freeze header dan wrap text.
- Kolom isian mencakup nama/deskripsi, cara bayar, mata uang, harga, masa aktif, trial, fitur/dukungan, pajak, catatan dan status keputusan.
- Baris TEMPLATE bukan paket yang sudah dibuat di database. Angka kosong bukan nol/gratis/unlimited.
- Belum ada persetujuan memilih sekali bayar, bulanan, tahunan, atau per penggunaan. Pengguna mengisi workbook terlebih dahulu.
- Struktur XML/ZIP workbook, empat baris produk, dan harga kosong sudah diperiksa; belum diverifikasi visual di Microsoft Excel.
- Exporter ulang: `scripts/export-draft-plans.ps1`, menggunakan PHP lokal dan .NET ZIP, tanpa dependency tambahan. Menghasilkan file baru bertimestamp Asia/Jakarta. Simpan workbook yang sudah diisi, jangan menggantinya dengan ekspor baru.
- Windows sempat memblokir script; exporter dijalankan dengan ExecutionPolicy khusus proses setelah approval, tanpa mengubah kebijakan Windows permanen.

### E. Lonceng notifikasi: perilaku dan batasnya

- Tabel Laravel standar: `notifications`, melalui relasi Notifiable pada User.
- Migration: `2026_10_02_000007_create_notifications_table.php`.
- Modul: `app/Modules/Core/Notification/`.
- Action `RecordNotification` menulis notifikasi database dalam transaksi aktivitas.
- Sumber sekarang: membuat workspace → pemilik; membuat/memperbarui draft paket → admin yang melakukan aksi.
- Tidak mengirim email/WhatsApp tambahan, tidak membuat notifikasi contoh, dan tidak mengisi ulang aktivitas lama.
- Akun lama dapat melihat daftar kosong sampai melakukan aktivitas baru yang didukung.
- Endpoint: GET `/notifications`, PATCH `/notifications/{uuid}/read`, PATCH `/notifications/read-all`.
- Seluruh query dibatasi ke akun login, termasuk superadmin; mutasi memakai CSRF web dan throttle.
- Daftar 10 item/halaman, badge unread, baca satu/semua, empty/loading/error/retry, Escape/klik luar, pengelolaan fokus, responsive light/dark.
- Refresh saat mount/buka panel/navigasi, tab kembali terlihat, dan setiap 60 detik selama tab terlihat. **Polling, bukan WebSocket push.**
- Penghapusan akun yang diizinkan membersihkan notifikasi akun itu di transaksi yang sama.
- Belum ada fitur hapus notifikasi individual, broadcast admin, preferensi kanal, atau integrasi email/WhatsApp umum; tidak diminta pada tahap ini.
- Dokumentasi: [docs/NOTIFICATIONS.md](docs/NOTIFICATIONS.md).

### F. Masalah lintas device yang telah diselesaikan

1. **Migration MySQL error 1553**: unique index `(tenant_id, product_slug)` masih menopang foreign key tenant sehingga tidak bisa dihapus langsung. Migration 000005 sudah diperbaiki dengan index tenant independen, constraint baru sebelum penghapusan lama, serta pemeriksaan kondisi parsial agar dapat dilanjutkan setelah kolom `product_id` terlanjur dibuat. Rollback dan pemetaan data turut diuji. Slug tidak dikenal tetap menghentikan proses, tidak dihapus diam-diam.
2. Migration 000005 dan 000006 berhasil diterapkan pada MySQL lokal sebagai batch 5, tanpa `migrate:fresh` atau reset data.
3. **Build lokal tertinggal dari source device lain**: manifest belum memuat halaman admin Pengguna, Produk dan Draft Paket. Build dengan approval sudah menyelaraskan aset; seluruh halaman Vue diperiksa hadir dalam manifest saat audit tersebut.
4. **Warna profil tidak konsisten**: kartu/form/modal mengikuti token tema; ThemeToggle menyinkronkan kelas `dark`, `data-theme`, dan preferensi tersimpan.
5. **Migration notifikasi**: 000007 berhasil, batch 6. Pemeriksaan terakhir seluruh migration 000000–000007 yang tersedia berstatus Ran, tidak ada pending.

Nomor batch dan kondisi database ini hanya hasil lokal terakhir. Device tujuan perlu memeriksa database miliknya sendiri.

### G. Hasil verifikasi terakhir

- Setelah perbaikan migration dan progres device lain: **121 tes, 954 assertions lulus**.
- Setelah implementasi notifikasi: **128 tes, 990 assertions lulus**; mencakup isolasi antar-akun, idempotensi tandai dibaca, transaksi notifikasi, sumber aktivitas, dan cleanup akun.
- Migration notifikasi sukses pada MySQL lokal.
- Build terakhir setelah approval sukses: **796 modul ditransformasikan**.
- `git diff --check` dan pemeriksaan struktur workbook berhasil pada tahap pengerjaan.
- Pengujian otomatis menggunakan SQLite in-memory; bukan bukti seluruh concurrency MySQL sudah diuji.
- Browser end-to-end, tampilan Excel, email nyata pada device ini, dan integrasi provider belum diuji menyeluruh. Jangan mengklaim platform siap produksi penuh.
- Pembuatan update dokumen ini tidak menjalankan build/dev atau mengirim email.

### H. Tugas berikutnya yang tepat

1. Pengguna mengisi workbook paket dan kuota. Tinjau hasilnya sebelum memperluas skema paket.
2. Sepakati harga, cara bayar, mata uang, masa aktif, trial, kuota dan pajak; jangan mengarang nilai untuk mengisi kekosongan.
3. Lengkapi editor aturan paket, lalu subscription workspace dan hubungan lifecycle-nya dengan entitlement.
4. Pilih provider pembayaran; implementasikan verifikasi webhook, idempotency, invoice dan aktivasi akses berdasarkan pembayaran terverifikasi.
5. Lengkapi audit, media/upload, operasional queue/notifikasi sesuai kebutuhan Core.
6. Lanjut Wedding MVP setelah fondasi akses/paket yang diperlukan siap, lalu produk lain sesuai blueprint.

Jangan membuat ulang pemisahan dashboard, admin pengguna/katalog, verifikasi wajib, atau lonceng: fitur tersebut sudah ada. Draft Paket sekarang juga sudah ada; yang belum adalah aturan komersial dan billing.

### I. File untuk dibawa dan diperiksa pada device lain

- Dokumen utama ini, source terbaru, migration, tes, `composer.lock`, `package-lock.json`, dan workbook Excel terutama jika sudah diisi.
- Modul baru notifikasi, komponen NotificationBell, perubahan PlanController/CreateWorkspace/DeleteAccount, migration 000007, dan tes NotificationTest.
- Perbaikan migration 000005 serta EntitlementProductRelationTest jangan tertinggal.
- `git status` saat penyiapan dokumen masih menunjukkan file modified/untracked. Nama dokumen lama tercatat deleted dan nama baru untracked. Belum dilakukan commit/push otomatis.
- `.env`, database dan upload tidak otomatis berpindah dengan Git. Pindahkan rahasia secara aman; jangan menempelkannya di chat/dokumen.
- Jika memindahkan database berisi data terenkripsi, pertahankan APP_KEY dengan aman dan sesuaikan APP_URL untuk verifikasi email.
- Buka folder project yang benar sebagai workspace; pasang dependensi dari lockfile dan gunakan Volta sesuai pin.
- Cek `php artisan migrate:status`, lalu migration normal pada database yang benar jika pending; jangan `migrate:fresh`.
- Build/dev hanya setelah approval tool. Build artifacts lokal mungkin tidak ikut Git sehingga device baru perlu build setelah persetujuan.
- Uji login superadmin/pelanggan, verifikasi, admin paket, buat workspace, lonceng, tema, dan tampilan mobile.
- `docs/ARCHITECTURE.md` serta ringkasan lama dapat memuat paragraf historis yang belum diselaraskan (misalnya slug entitlement, cascade owner, notifikasi belum tersedia). Gunakan kode aktual dan bagian terkini dokumen ini sebagai acuan; jangan membatalkan perbaikan berdasarkan catatan lama.

### J. Pesan pembuka untuk sesi/device berikutnya

> Baca MOSHIAUPDATE-02102026.md bagian Status terkini, lalu periksa kode dan database yang sedang aktif. Project Moshia memakai Laravel/Breeze/Vue/Inertia, Core dahulu lalu Wedding. Dashboard superadmin/pelanggan, admin pengguna/katalog, verifikasi email wajib, Draft Paket, perlindungan akun, entitlement product_id, dan lonceng notifikasi sudah dibuat. Migration lokal terakhir sudah sampai 000007; suite terakhir 128 tes/990 assertions lulus. Excel paket ada di exports dan pengguna akan mengisi harga; jangan mengaktifkan harga atau mengimpor Excel otomatis. Pertahankan perubahan yang belum di-commit, jelaskan proses, dan gunakan tombol approval setiap npm run build/dev. Lanjutkan dari data workbook/permintaan terbaru pengguna, bukan membangun ulang fitur yang sudah selesai.

---

# Riwayat percakapan dan implementasi sebelumnya

Bagian di bawah adalah catatan bertahap yang dipertahankan. Jika bertentangan, bagian Status terkini di atas berlaku sebagai ringkasan terbaru.

# UPDATE MOSHIA

Tanggal update: **2 Oktober 2026 (Asia/Bangkok, UTC+7)**.

Dokumen ini mencatat implementasi aktual, pekerjaan yang masih tertunda, dan konteks untuk melanjutkan pengembangan. Hasil tes berasal dari pengerjaan yang dijelaskan di bawah; tidak ada pengujian ulang seluruh fitur hanya untuk memperbarui dokumen ini. Dokumen ini menggantikan ringkasan lama sebagai acuan progres terbaru.

## 1. Ringkasan posisi project

Moshia sudah memiliki website utama, autentikasi dengan verifikasi email wajib, dashboard berbeda untuk superadmin/pelanggan, pengelolaan pengguna dan penugasan role melalui admin, serta fondasi Core untuk workspace, katalog produk, dan pemeriksaan hak akses produk. Gmail SMTP sudah berjalan dan email verifikasi sudah diberi desain Moshia.

**Posisi roadmap: Fase 1 — Moshia Core. Moshia belum menjadi platform SaaS lengkap.** Admin memiliki ringkasan platform, pengelolaan pengguna/role, serta editor katalog empat produk berbasis database. Pembuatan/penghapusan produk, paket, subscription, pembayaran, dan fungsi bisnis keempat produk belum diimplementasikan.

Urutan yang disetujui pengguna: **Core dahulu, lalu Wedding MVP**, kemudian Jastip, Photo Booth, dan Restaurant.

- Folder aktif: `D:\XAMPP8212\htdocs\MOSHIA-PROJECT`.
- Folder lama `D:\XAMPP8212\htdocs\moshia` bukan lagi folder project aktif.
- Blueprint: http://72.62.240.106:8787/moshia-ZquktHzrKNCnAat1/
- Catatan arsitektur: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).
- Panduan email: [docs/EMAIL.md](docs/EMAIL.md).
- Pengguna menegaskan blueprint pada tautan tersebut sebagai kerangka utama semua pengembangan. Blueprint sudah dibaca dalam sesi ini; urutan lanjutannya tetap Core → Wedding → Jastip → Photo Booth → Restaurant.

## 2. Stack dan aturan kerja

| Bagian | Kondisi saat ini |
| --- | --- |
| Backend | Laravel 12, PHP 8.2 |
| Authentication | Laravel Breeze |
| Frontend | Vue 3 + Inertia 2 |
| Styling | Tailwind CSS 3 + CSS custom Moshia |
| Bundler | Vite 7 |
| Node | Volta pin `22.23.3` di `package.json` |
| Database lokal | MySQL; koneksi terakhir sudah berhasil |
| Role/permission | Spatie Laravel Permission |
| Pengujian | PHPUnit; SQLite in-memory untuk tes |

Preferensi pengguna yang harus dipertahankan:

1. Jelaskan alur perubahan dengan Bahasa Indonesia yang sederhana sebelum menerapkan.
2. Untuk permintaan implementasi yang jelas, lanjutkan pengerjaan tanpa konfirmasi umum berulang.
3. **Setiap `npm run build` dan `npm run dev` wajib meminta approval melalui tombol tool terlebih dahulu.** Jangan menjalankannya otomatis atau melewati approval melalui perintah lain.
4. Pertahankan desain hitam-merah dan pekerjaan pengguna yang sudah ada.
5. Jangan mengganti Node global karena ada project lain dengan kebutuhan versi berbeda.
6. Pertanyaan tentang arti kode/gambar dijawab sebagai penjelasan, bukan otomatis mengubah data.
7. Jangan mengubah role akun, mereset database, atau menimpa perubahan pengguna tanpa dasar instruksi yang jelas.

Workspace aktif dan writable root sesi ini sudah benar: `D:\XAMPP8212\htdocs\MOSHIA-PROJECT`. Catatan lama tentang writable root yang masih menunjuk folder `moshia` tidak lagi berlaku. Jangan menyimpan App Password, password database, atau APP_KEY di dokumen maupun Git.

## 3. Website utama dan desain

### Sudah dikerjakan

- Landing page mengikuti referensi visual awal: latar gelap, aksen merah, kartu, tombol membulat, dan font Instrument Sans.
- Hero dengan robot SVG sementara serta panel analitik dekoratif.
- Layout responsif dan menu mobile.
- Pergantian tema terang/gelap dan penyimpanan preferensi di localStorage.
- Tombol kembali ke atas muncul setelah scroll lebih dari 300 piksel; mendukung reduced-motion.
- Landing page memakai katalog produk dari backend yang sama dengan dashboard dan admin.
- Produk yang ditampilkan: Wedding Invitation, Jastip Manager, Photo Booth, Restaurant.
- Halaman authentication dan profil disesuaikan dengan nuansa Moshia.

### Perbaikan visual terakhir

Screenshot profil menunjukkan kartu putih pada tema gelap, label kurang terbaca, dan judul tetap memakai warna bawaan Breeze.

Perbaikan yang diterapkan:

- Kartu profil menggunakan `--surface`, `--line`, dan teks menggunakan `--text`/`--muted`.
- Form informasi profil, password, dan hapus akun memakai warna sesuai tema.
- Modal hapus akun mengikuti warna tema yang sama.
- ThemeToggle menyinkronkan kelas `dark` dan atribut `data-theme` agar Tailwind dan CSS custom konsisten.
- Penyimpanan preferensi tema menangani kondisi localStorage tidak tersedia.
- Build setelah perbaikan berhasil.

Pemeriksaan visual langsung di browser untuk seluruh ukuran layar dan interaksi belum dilakukan secara menyeluruh. Ilustrasi dan logo masih sementara; aset final akan diganti pengguna.

## 4. Akun, autentikasi, dan role

### Sudah dikerjakan

- Registrasi, login, logout, profil, perubahan password, reset password, dan penghapusan akun melalui Breeze.
- Trait `HasRoles` pada model User.
- Role `user` dan `super-admin` disiapkan dengan RoleSeeder untuk guard `web`.
- Alias middleware `role` dan `permission` aktif di `bootstrap/app.php`.
- Registrasi otomatis memberikan role `user` dan membuat workspace pertama.
- Pembuatan akun, assignment role, serta workspace berada dalam transaksi database.
- Kegagalan di transaksi tidak meninggalkan akun yang tersimpan sebagian.

### Error yang sudah ditangani

`There is no role named user for guard web` disebabkan role belum tersedia.

Perbaikan: RoleSeeder menggunakan `Role::findOrCreate`, dipanggil DatabaseSeeder, dan sudah pernah dijalankan pada database lokal. Seeder role dapat dijalankan berulang tanpa duplikasi.

Untuk menyiapkan role saja:

```powershell
php artisan db:seed --class=RoleSeeder
```

Jangan menganggap seluruh DatabaseSeeder aman dijalankan berulang: seeder tersebut juga masih membuat akun contoh melalui factory.

### Verifikasi email wajib — sudah aktif

- Model User mengimplementasikan `MustVerifyEmail`.
- Alur: daftar → akun, role user, dan workspace dibuat → login dalam status belum terverifikasi → halaman verifikasi → klik tautan email → akses dashboard.
- Jadi akun disimpan sebelum verifikasi, tetapi dashboard, admin, dan endpoint pembuatan/pemilihan workspace belum dapat digunakan. Bukan alur OTP sebelum penyimpanan akun.
- Middleware `product.access` juga menolak akun belum terverifikasi meskipun sudah memiliki entitlement.
- Profil tetap tersedia untuk memperbaiki alamat email. Perubahan email menghapus status verifikasi sebelumnya; pengguna dapat meminta tautan baru.
- Tautan verifikasi ditandatangani, terikat pada ID akun/email, dan berlaku 60 menit secara default. Tautan kedaluwarsa, diubah, atau milik akun lain ditolak.
- Jika tautan dibuka saat logout, login akun yang sesuai dapat melanjutkan ke tautan verifikasi valid tersebut.
- Redirect setelah verifikasi mengikuti role dan tujuan yang diizinkan, tanpa redirect ke situs luar.
- Registrasi serta endpoint verifikasi/kirim ulang diberi throttle. Kirim ulang dibatasi 6 kali per menit.
- Kegagalan transport email tidak membatalkan akun yang sudah dibuat: akun tetap belum terverifikasi, pengguna melihat pesan error dan dapat kirim ulang.
- Tidak ada backfill atau verifikasi massal akun lama. Pemeriksaan sebelum aktivasi menemukan 0 akun dan 0 superadmin belum terverifikasi. Ini hasil pemeriksaan saat itu, bukan jaminan jumlah sekarang.

### Batas implementasi Identity

- Pengelolaan admin mencakup data pengguna dan assignment role yang sudah ada; pembuatan/penghapusan definisi role serta editor permission belum dibuat.
- Tidak ada bypass otomatis semua permission, keanggotaan workspace, atau entitlement untuk superadmin.
- Suspend/hapus akun lain melalui admin dan audit perubahan belum dibuat.

## 5. Superadmin versus pelanggan

Akun **ekawigunawork@gmail.com** sudah diperiksa pada database lokal: **ID 1, memiliki role `super-admin`**. Ketika pengguna meminta menjadikannya superadmin, role tersebut ternyata sudah ada sehingga tidak dilakukan assignment tambahan. Periksa ulang jika berpindah database/device.

### Kondisi sekarang

- `/dashboard` adalah dashboard pelanggan: workspace, profil, katalog, dan status hak akses.
- Superadmin tetap dapat membuka workspace miliknya melalui menu **Workspace Saya**.
- Default login akun terverifikasi: superadmin → `/admin`, pelanggan → `/dashboard`. Akun belum terverifikasi → `/verify-email`.
- `DashboardDestination` menyatukan penentuan tujuan login, kunjungan login/register oleh akun yang sudah masuk, tautan dashboard landing, dan redirect verifikasi.
- Tujuan sebelumnya dibatasi ke halaman lokal yang dikenali dan boleh diakses: dashboard, profil, admin sesuai role, daftar/edit pengguna sesuai policy, serta tautan verifikasi valid milik akun tersebut. URL eksternal/tidak dikenal kembali ke tujuan default. Route produk baru perlu aturan tujuan yang sesuai.
- `/admin` sudah ada dan dibatasi middleware `auth`, `verified`, serta `role:super-admin`.
- `/admin` menampilkan jumlah pengguna, workspace, produk dalam katalog, serta tautan ke pengelolaan pengguna.
- Menu desktop/mobile memakai sumber navigasi bersama: **Dashboard Admin**, **Pengguna & Role**, **Workspace Saya**, dan **Profil** bagi superadmin; pelanggan melihat dashboard workspace dan profil.

### Pengelolaan pengguna & role — sudah dibuat

- `GET /admin/users`: daftar dengan pagination 15 pengguna per halaman, pencarian nama/email, dan filter role.
- `GET /admin/users/{user}/edit`: form nama, email, serta pilihan role.
- `PATCH /admin/users/{user}`: menyimpan data dan assignment role dalam transaksi; throttle 30 per menit.
- Dilindungi `auth`, `verified`, `role:super-admin`, dan `UserPolicy`.
- Role hanya boleh berasal dari role guard `web` yang tersedia; akun lain wajib memiliki setidaknya satu role. Nama role tidak dibuat otomatis dari input.
- Admin tidak dapat mengubah role akun sendiri, tetapi dapat memperbarui nama/email sendiri. Pembatasan ini juga diperiksa di server.
- UI meminta konfirmasi jika role berubah dan menampilkan role sebelum/sesudah serta dampak super-admin.
- Action mengunci baris role super-admin dan memeriksa ulang role aktor dari database di dalam transaksi, untuk menghindari izin dari data role yang sudah usang. Tes SQLite tidak membuktikan perilaku concurrency row lock MySQL.
- Email baru harus unik dan membatalkan status verifikasi lama. Password, ID akun, serta status verifikasi tidak dapat diubah lewat input tambahan form ini.
- Payload daftar/detail hanya mengekspos kolom yang diperlukan; password dan token tidak dikirim.
- Penugasan role tidak memberikan akses otomatis ke workspace atau produk pelanggan.

### Target berikutnya

| Superadmin platform | Pelanggan/pengguna |
| --- | --- |
| Dashboard utama admin | Dashboard workspace |
| Mengelola pengguna dan role | Mengelola akun dan anggota workspace |
| Mengelola produk dan paket | Memilih produk dan paket |
| Memantau pembayaran/langganan platform | Melihat tagihan/langganan sendiri |
| Mengelola pengaturan dan audit | Menggunakan produk sesuai hak akses |

Pemisahan dashboard, pengelolaan pengguna/assignment role, dan editor katalog sudah diimplementasikan. Tabel di atas adalah target platform keseluruhan; pengelolaan anggota workspace, paket, pembayaran, settings, dan audit masih tertunda.

## 6. Workspace / Tenancy

Penjelasan yang sudah diberikan kepada pengguna: akun adalah identitas untuk login; workspace adalah ruang untuk data/aktivitas pribadi, bisnis, atau tim. Workspace aktif menentukan konteks kerja. Satu akun dapat bergabung dengan beberapa workspace. Dashboard Admin mengelola platform, sedangkan Workspace Saya mengelola ruang kerja milik akun. Contoh penggunaan Wedding/Jastip di workspace masih rencana karena produk belum operasional.

### Sudah dikerjakan

- Tabel `core_tenants` dan `core_tenant_user`.
- Pembuatan workspace dan keanggotaan pemilik secara atomik.
- Akun baru mendapat workspace awal.
- Akun lama dapat membuat workspace dari dashboard; tidak dilakukan backfill massal otomatis.
- Pemilihan workspace melalui endpoint server.
- Workspace aktif diperiksa ulang terhadap keanggotaan, bukan mempercayai ID session saja.
- Pengguna tidak dapat memilih workspace akun lain atau menyuplai owner workspace lewat input form.
- Endpoint pembuatan workspace memakai throttle.

### Belum dikerjakan

- Undangan dan pengelolaan anggota.
- Role/permission dalam workspace.
- Pengalihan kepemilikan dan pengaturan workspace.
- Kebijakan penghapusan/pemindahan data sebelum ada kolaborasi atau produk berbayar.

Catatan: relasi pemilik saat ini memakai cascade deletion. Sebelum peluncuran berbayar, perlu aturan eksplisit untuk penghapusan akun pemilik agar data bisnis tidak ikut terhapus tanpa proses yang tepat.

## 7. Katalog dan hak akses produk

### Sudah dikerjakan

- Registri empat produk sekarang berada di tabel `core_products`; `config/moshia.php` hanya menyimpan pilihan status/ikon editor.
- Service ProductCatalog sebagai sumber bersama untuk landing, dashboard, dan admin.
- Data awal empat produk tetap `planned` (Segera hadir), dengan identitas `wedding`, `jastip`, `photobooth`, dan `restaurant`. Admin dapat memilih `hidden` (Disembunyikan); belum ada status produk aktif/diluncurkan.
- `/admin/products` menampilkan katalog termasuk produk tersembunyi, dengan halaman edit `/admin/products/{product}/edit` dan penyimpanan PATCH `/admin/products/{product}`.
- Admin terverifikasi dapat mengubah nama, ringkasan, deskripsi, ikon, urutan, serta status tampilan. Dilindungi middleware admin, ProductPolicy, validasi panjang/ikon/status/urutan, dan throttle penyimpanan 30 per menit.
- ID angka, slug produk, serta fase roadmap tidak bisa diubah lewat form/API agar referensi entitlement tetap stabil.
- Urutan lebih kecil tampil lebih dahulu; nilai sama diurutkan berdasarkan slug. Editor menyediakan pratinjau konten.
- Produk tersembunyi tidak ditampilkan di landing/dashboard pelanggan, tetapi tetap terlihat dan dihitung di admin. Menyembunyikan kartu tidak mencabut entitlement; ProductCatalog::find tetap mengenali ID tersembunyi.
- Landing dan dashboard menyediakan pesan jika seluruh produk disembunyikan. Menu Katalog Produk tersedia di desktop/mobile, dan intended redirect admin mendukung editor katalog.
- Tabel `core_entitlements`.
- Contract ProductAccess dan implementasi database.
- Pemeriksaan keanggotaan workspace, produk, status `active`/`trial`, tanggal mulai dan akhir.
- Alias middleware `product.access` untuk route produk yang akan dibangun.
- Superadmin tidak otomatis mendapat entitlement produk dan tidak melewati pemeriksaan keanggotaan.

### Belum dikerjakan

- Pembuatan/penghapusan produk baru dan lifecycle peluncuran. Editor saat ini mengelola empat produk roadmap yang sudah ada, bukan membuat fitur bisnis hanya dengan menambah entri.
- Endpoint pemberian entitlement oleh billing/admin yang terotorisasi.
- Kuota pemakaian dan pencatatan konsumsi atomik.
- Hubungan entitlement dengan paket dan subscription.
- Route bisnis produk yang menggunakan pemeriksaan akses tersebut.

Entitlement adalah fondasi akses, **bukan bukti pembayaran sudah terintegrasi atau produk sudah dapat digunakan**.

### Pembaruan identitas produk: ID angka + slug

- Sesuai pilihan pengguna, core_products sekarang menggunakan id BIGINT UNSIGNED AUTO_INCREMENT sebagai primary key dan slug VARCHAR(50) dengan unique index.
- Migration 2026_10_02_000003_separate_product_id_and_slug mengubah ID string lama menjadi slug dan menambahkan ID angka. Migration awal tetap dipertahankan agar perangkat/database lama dapat di-upgrade normal.
- Migration sudah diterapkan pada MySQL lokal. Konten katalog (termasuk timestamp/status/urutan) dibandingkan sebelum/sesudah dan tetap sama; isi entitlement juga tetap sama.
- Hasil ID lokal: 1 = jastip, 2 = photobooth, 3 = restaurant, 4 = wedding. Nomor dapat berbeda pada database lain; jangan hardcode nomor berdasarkan contoh ini.
- Model memakai ID angka; binding URL editor tetap berdasarkan slug, sehingga /admin/products/wedding/edit masih valid. Anchor landing dan ProductAccess juga memakai slug.
- core_entitlements.product_slug tetap dipertahankan pada perubahan ini; belum dimigrasikan menjadi foreign key product_id. Relasi numerik dapat ditambahkan pada tahap billing berikutnya dengan migration tersendiri.
- ID dan slug tidak dapat diedit dari form katalog. Urutan tampilan tetap ditentukan sort_order, kemudian slug, bukan angka ID.
- Seluruh suite setelah perubahan: **99 tes lulus, 801 assertions**. Tes SQLite mencakup ID otomatis, keunikan slug, dan migrasi maju/mundur dengan konten yang telah diedit.
- Build setelah approval berhasil (794 modul). Tampilan langsung di browser belum diperiksa ulang setelah pemisahan ID/slug.

## 8. Struktur modul saat ini

```text
app/Modules/Core/
├── Catalog/
│   ├── ProductCatalog.php
│   ├── DashboardDestination.php
│   ├── Models/Product.php
│   ├── Policies/ProductPolicy.php
│   ├── Http/Requests/UpdateProductRequest.php
│   ├── Http/Controllers/ProductController.php
│   ├── Http/Controllers/DashboardController.php
│   └── Routes/web.php
├── Tenancy/
│   ├── Models/Tenant.php
│   ├── Actions/CreateWorkspace.php
│   ├── CurrentWorkspace.php
│   └── Http/Controllers/WorkspaceController.php
├── Entitlement/
│   ├── Contracts/ProductAccess.php
│   ├── Models/Entitlement.php
│   ├── Services/DatabaseProductAccess.php
│   └── Http/Middleware/EnsureProductAccess.php
└── Identity/
    ├── Actions/UpdatePlatformUser.php
    ├── Policies/UserPolicy.php
    ├── Http/Controllers/UserController.php
    ├── Http/Requests/UpdateUserRequest.php
    └── Routes/web.php
```

Identity admin sudah memiliki modul sendiri. Model User dan controller autentikasi Breeze tetap di lokasi lama untuk kompatibilitas; tidak dipindahkan hanya untuk menyamakan nama folder.

Target selanjutnya pada blueprint:

```text
app/Modules/Core/{Billing,Notification,Media,Audit}
app/Modules/Products/{Wedding,Jastip,PhotoBooth,Restaurant}
app/Modules/Integrations/{Payments,WhatsApp,AI,DNS,Printing}
```

Path target tersebut adalah rencana, bukan klaim semua modul sudah dibuat. Produk harus menggunakan tenant scope dan contract Core; jangan mengakses tabel domain produk lain secara langsung.

## 9. Fitur Core yang belum lengkap

| Area | Pekerjaan tertunda |
| --- | --- |
| Billing | Plan, harga, subscription, invoice, lifecycle pembatalan/perpanjangan |
| Payment | Pemilihan provider, checkout, verifikasi webhook, idempotency, rekonsiliasi |
| Notification | Penyimpanan notifikasi, daftar, status dibaca, email/WhatsApp via queue |
| Media | Upload tervalidasi, storage privat, signed URL, object storage/CDN |
| Audit | Pencatatan aktivitas penting, pencarian dan tampilan admin |
| Settings | Pengaturan platform dan provider |
| Domain | Subdomain/custom domain, verifikasi kepemilikan, integrasi DNS |
| Operations | Redis/worker/scheduler produksi, monitoring, backup dan restore |

NotificationBell sudah ada sebagai komponen ikon, tetapi belum menjadi sistem notifikasi lengkap. Tidak ada checkout nyata, pembelian paket, atau pengiriman WhatsApp dalam implementasi saat ini.

### Email Gmail SMTP dan desain verifikasi — sudah berjalan

- Pengirim sementara: **moshiaprojectapp@gmail.com**, nama pengirim **Moshia**.
- `.env` lokal menggunakan `MAIL_MAILER=smtp`, `MAIL_SCHEME=smtp`, `MAIL_HOST=smtp.gmail.com`, dan `MAIL_PORT=587` (STARTTLS).
- `MAIL_USERNAME` dan `MAIL_FROM_ADDRESS` menggunakan alamat pengirim tersebut. `MAIL_URL=null` agar tidak menimpa konfigurasi individual.
- `MAIL_PASSWORD` berisi App Password yang diisi pengguna langsung di `.env`. **Jangan menyalin atau menampilkan nilainya.** Kredensial tidak disimpan dalam source maupun dokumen ini.
- SMTP timeout 15 detik dan TLS diwajibkan melalui `MAIL_TIMEOUT`/`MAIL_REQUIRE_TLS`.
- Pengguna sempat tidak bisa membuka App Password. Setelah mengikuti proses akun Google, pengguna berhasil mendapatkan dan memasukkannya. Tidak ada perubahan keamanan akun Google yang dilakukan agent.
- Diagnosis SMTP menemukan konfigurasi masih menunjuk `127.0.0.1:2525`; dikoreksi kembali ke Gmail tanpa menimpa App Password. Cache konfigurasi dibersihkan dan **autentikasi SMTP berhasil**.
- Pengguna kemudian mengonfirmasi **berhasil mendaftar akun, menerima email verifikasi, dan melakukan verifikasi**. Ini pengujian nyata oleh pengguna sebelum perubahan desain email terbaru.
- Pengiriman masih sinkron, belum via queue. Jika transport gagal, tersedia pesan error dan kirim ulang.
- Migrasi ke email domain Moshia disiapkan melalui konfigurasi SMTP: ubah host, port, scheme, kredensial, dan alamat pengirim sesuai provider. Kode registrasi tidak perlu diganti. SPF/DKIM/DMARC dan pengujian provider domain tetap diperlukan saat migrasi.
- Setelah mengubah `.env`, jalankan `php artisan config:clear`. Tidak perlu build frontend untuk perubahan konfigurasi email.

Desain email terbaru:

- Latar `#09090c`, panel `#111114`, aksen/tombol `#f02b35`, teks putih/abu-abu, identitas **MOSHIA.**, dan tagline **Better Together. Beyond Tomorrow.**
- Sapaan Bahasa Indonesia, tombol **Verifikasi email saya**, masa berlaku, tautan cadangan, dan keterangan jika penerima tidak mendaftar.
- HTML memakai layout tabel, CSS inline, font fallback Arial/Helvetica, serta versi teks biasa. Tidak bergantung pada gambar eksternal.
- `AppServiceProvider` mengarahkan notifikasi VerifyEmail ke template khusus. Perubahan ini khusus email verifikasi; desain reset password belum disesuaikan.
- HTML dan plain text sudah dirender melalui mailer array tanpa pengiriman email, termasuk pemeriksaan escaping nama penerima. Tautan verifikasi asli tetap ditandatangani.
- Preview lokal: `storage/app/previews/moshia-verification-email.html`, memakai nama dan URL contoh, bukan tautan akun nyata. File preview di storage mungkin tidak ikut Git/pindah perangkat.
- **Desain baru belum diperiksa di inbox Gmail/Outlook atau seluruh klien email.** Jangan menyamakan keberhasilan rendering dengan pengujian visual lintas klien.
- Panduan lengkap: `docs/EMAIL.md`. Template Blade email tidak memerlukan `npm run build`.

## 10. Status produk

| Produk | Status | Alur yang akan dibangun |
| --- | --- | --- |
| Wedding Invitation | Katalog/roadmap | Template → edit data → preview → publish; dilanjutkan tamu, RSVP, media, domain |
| Jastip Manager | Katalog/roadmap | Workspace → trip → order → kurs/status → WhatsApp |
| Photo Booth | Katalog/roadmap | Event → capture → frame → render → print; AI setelah dasar stabil |
| Restaurant | Katalog/roadmap | Menu/meja/order → kitchen → POS/payment/struk → antrean/WhatsApp |

Keempatnya belum memiliki alur bisnis operasional lengkap. Wedding menjadi MVP produk pertama setelah fondasi Core yang diperlukan selesai.

## 11. Migration dan hasil verifikasi

Migration `2026_10_02_000001_create_core_workspace_tables` sudah dijalankan pada database lokal dan berstatus **Ran (batch 3)**. Tabel yang ditambahkan:

- `core_tenants`
- `core_tenant_user`
- `core_entitlements`

Migration katalog `2026_10_02_000002_create_core_products_table` juga sudah dijalankan pada MySQL lokal. Tabel `core_products` diisi empat produk awal, seluruhnya `planned`, dengan urutan 10/20/30/40. Jumlah baris akun, workspace, keanggotaan, dan entitlement diperiksa sebelum/sesudah dan tetap sama. Migration normal berikutnya tidak menimpa edit katalog yang sudah tersimpan.

Koneksi MySQL sempat ditolak karena layanan belum tersambung; pengguna mengaktifkannya dan migration berhasil. Tidak dilakukan reset database atau penghapusan data lama.

Riwayat validasi:

- Empat tes RegistrationTest pernah lulus untuk perbaikan role.
- Pada implementasi Core, suite awal menghasilkan 33 lulus dan 1 gagal karena manifest Vite belum memuat halaman admin baru.
- Setelah build, ketujuh tes PlatformTest lulus, termasuk tes yang sebelumnya gagal; 66 assertions pada run tersebut.
- Validasi sintaks PHP dan komponen Vue yang berubah berhasil.
- Build setelah perubahan Core berhasil.
- Build setelah perbaikan tema profil berhasil.
- Pemisahan dashboard: seluruh suite **55 tes lulus, 316 assertions**. Dua kegagalan awal disebabkan manifest Vite belum memuat halaman admin; setelah build dan pengujian ulang semuanya lulus.
- Pengelolaan pengguna/role: seluruh suite **69 tes lulus, 471 assertions**, termasuk 14 tes baru; build berhasil setelah approval.
- Verifikasi email wajib: seluruh suite **79 tes lulus, 553 assertions**. Build halaman registrasi/verifikasi berhasil setelah approval, 792 modul ditransformasikan.
- Gmail SMTP: koneksi dan autentikasi berhasil setelah konfigurasi diperbaiki. Pengguna mengonfirmasi alur registrasi → inbox → verifikasi berhasil secara nyata.
- Setelah desain email terbaru: **14 tes terkait registrasi/verifikasi lulus, 94 assertions**, serta rendering HTML/plain text melalui mailer array berhasil. Tidak ada email nyata dikirim oleh pemeriksaan rendering tersebut.
- Pada tahap desain email, hanya tes terkait yang diulang dan build tidak diperlukan. Seluruh suite kemudian dijalankan lagi pada tahap katalog berikutnya.
- Tidak ada reset database atau pemberian role massal selama tahap dashboard/admin/verifikasi email. Migration baru hanya ditambahkan pada tahap katalog berikutnya seperti dijelaskan di atas.
- Tahap katalog database/admin: seluruh suite **97 tes lulus, 794 assertions**, termasuk 18 skenario katalog (akses, validasi, konsistensi tampilan, urutan, visibility, kestabilan entitlement, dan redirect). Build setelah approval berhasil, 794 modul ditransformasikan. Tampilan editor katalog belum diperiksa langsung di browser.
- `git diff --check` sebelumnya lulus. Tampilan dashboard/admin dan email terbaru belum diuji visual menyeluruh; jangan menyatakan seluruh fitur diuji end-to-end.

## 12. File penting untuk melanjutkan

| File/folder | Kegunaan |
| --- | --- |
| `routes/web.php` | Landing, profil, pemuatan route Core/auth |
| `app/Modules/Core/Catalog/Routes/web.php` | Dashboard, workspace, admin |
| `bootstrap/app.php` | Alias middleware |
| `app/Providers/AppServiceProvider.php` | Binding ProductAccess, pendaftaran UserPolicy, template notifikasi verifikasi |
| `app/Models/User.php` | Auth, MustVerifyEmail, roles, relasi tenant |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Registrasi atomik + role + workspace |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Login dan redirect saat ini |
| `app/Modules/Core/Catalog/DashboardDestination.php` | Tujuan berdasarkan verifikasi/role dan validasi intended URL |
| `app/Modules/Core/Identity/` | Controller, request, policy, action, dan route pengelolaan pengguna |
| `app/Http/Controllers/Auth/{EmailVerificationPromptController,EmailVerificationNotificationController,VerifyEmailController}.php` | Halaman, kirim ulang, dan pemrosesan verifikasi |
| `database/seeders/RoleSeeder.php` | Role user/super-admin untuk web |
| `config/moshia.php` | Pilihan ikon/status editor; konten produk ada di database |
| `app/Modules/Core/Catalog/{Models,Policies,Http}/` | Model produk, policy, request, controller editor katalog |
| `database/migrations/2026_10_02_000002_create_core_products_table.php` | Tabel katalog dan snapshot empat produk awal |
| `resources/js/Pages/Admin/Products/{Index,Edit}.vue` | Daftar dan editor katalog dengan pratinjau |
| `tests/Feature/Core/ProductManagementTest.php` | Pengujian pengelolaan katalog dan konsistensi data |
| `resources/js/Layouts/MoshiaLayout.vue` | Sidebar dan kerangka dashboard/profil/admin |
| `resources/js/Pages/Dashboard.vue` | Dashboard pelanggan/workspace |
| `resources/js/Pages/Admin/Index.vue` | Ringkasan admin saat ini |
| `resources/js/Pages/Admin/Users/{Index,Edit}.vue` | Daftar/filter/pagination pengguna dan edit identitas/role |
| `resources/js/Pages/Auth/{Register,VerifyEmail}.vue` | Informasi kewajiban verifikasi, kirim ulang, dan koreksi email |
| `resources/views/emails/verify-email.blade.php` | Email verifikasi HTML bertema Moshia |
| `resources/views/emails/verify-email-text.blade.php` | Versi teks biasa email verifikasi |
| `config/mail.php`, `.env.example` | Konfigurasi SMTP lintas provider, TLS, timeout; tanpa kredensial nyata |
| `resources/js/Pages/Welcome.vue` | Landing page |
| `resources/js/Pages/Profile/` | Form profil/password/hapus akun |
| `resources/js/Components/ThemeToggle.vue` | Sinkronisasi tema |
| `resources/js/Components/ProductCatalog.vue` | Kartu produk dashboard/admin |
| `resources/js/Components/Modal.vue` | Modal sesuai tema |
| `resources/css/app.css` | CSS Moshia dan token warna |
| `tests/Feature/Core/PlatformTest.php` | Tes workspace, katalog, admin, entitlement |
| `tests/Feature/Core/UserManagementTest.php` | Akses admin, data pengguna, role, validasi, dan pemeriksaan ulang aktor |
| `tests/Feature/Auth/DashboardDestinationTest.php` | Redirect berdasarkan role dan penolakan intended URL tidak aman |
| `tests/Feature/Auth/{RegistrationTest,EmailVerificationTest,RequiredEmailVerificationTest}.php` | Registrasi/verifikasi wajib, tautan, resend, kegagalan SMTP, dan akses |
| `docs/ARCHITECTURE.md` | Batas modul dan roadmap teknis |
| `docs/EMAIL.md` | Setup Gmail, migrasi email domain, batas pengiriman dan pengujian |

## 13. Prioritas lanjutan

1. Periksa tampilan email verifikasi baru di inbox; tinjau dashboard/admin pada desktop dan mobile jika diperlukan.
2. Katalog database dan edit empat produk melalui admin sudah dibuat. Lanjutan Core yang disarankan: pengelolaan paket produk; detail paket/harga belum disepakati.
3. Sepakati role anggota workspace, kebijakan kepemilikan/penghapusan, paket/harga, trial, kuota, mata uang, serta provider pembayaran.
4. Lengkapi billing, subscription, entitlement dari pembayaran terverifikasi, invoice dan audit.
5. Lengkapi notifikasi, media, queue, kebutuhan operasional minimum, serta migrasi email domain saat siap.
6. Bangun satu Wedding vertical slice: template → isi konten → preview → publish, sampai bisa dipakai dan diuji end-to-end.
7. Lanjutkan Jastip, Photo Booth, Restaurant sesuai roadmap. Pembuatan definisi role/permission dan pengelolaan anggota masih pekerjaan tersendiri, bukan fitur yang otomatis selesai dengan assignment role admin.

Permintaan terakhir pengguna adalah memisahkan ID produk menjadi angka dan slug menjadi string. Perubahan sudah diterapkan melalui migration baru tanpa mengubah konten katalog atau entitlement. Editor katalog sudah dibuat; paket dan billing belum dikerjakan.

## 14. Catatan melanjutkan dari device lain

- Bawa seluruh source terbaru dan lockfile; jangan hanya membawa dokumen ini.
- Database, file upload, `.env`, dan rahasia tidak otomatis ikut melalui Git.
- Gunakan `.env` lokal yang tepat dan pertahankan APP_KEY dengan aman bila memindahkan data terenkripsi.
- Hindari `migrate:fresh` untuk database yang perlu dipertahankan.
- Gunakan Volta sesuai pin Node, kemudian dependency dari lockfile.
- Tetap minta tombol approval sebelum build/dev.
- Periksa perubahan pengguna yang belum di-commit sebelum mengedit.
- `git status` saat pembaruan ini menunjukkan banyak file modified dan untracked, termasuk modul Identity, halaman admin pengguna, template email, dokumen email, dan tes baru. Jangan menganggap semuanya sudah tersimpan dalam commit.
- `package-lock.json` sudah modified sebelum pekerjaan sesi ini; pertahankan perubahan pengguna tersebut.
- Tidak ada commit/push otomatis dalam sesi ini. Salin/simpan seluruh source relevan, bukan hanya file ringkasan.
- Gmail SMTP aktif hanya pada `.env` lokal yang dikonfigurasi. Pastikan kredensial dipindahkan dengan aman atau buat App Password baru pada device tujuan; jangan menganggap `.env.example` sudah dapat mengirim email.
- Pertahankan APP_KEY dan sesuaikan APP_URL untuk tautan verifikasi pada lingkungan tujuan.
- SUMMARY.md lama terdeteksi dihapus sebelum pengerjaan Core; dokumen ini tidak memulihkan file tersebut atau membatalkan penghapusan pengguna.

Pesan pembuka yang dapat digunakan:

> Baca "MOSHIAUPDATE-02102026.md", docs/ARCHITECTURE.md, dan docs/EMAIL.md. Project aktif ada di MOSHIA-PROJECT dan seluruh pengembangan mengacu blueprint utama. Pemisahan dashboard, admin pengguna/assignment role, katalog database/editor admin, verifikasi email wajib, dan Gmail SMTP sudah berjalan. Pengguna sudah menguji registrasi serta verifikasi; desain email Moshia terbaru sudah dirender dan diuji terkait, tetapi belum diperiksa di inbox. Katalog menggunakan core_products; config/moshia.php hanya pilihan ikon/status. Pertahankan desain hitam-merah, stack, dan perubahan yang belum di-commit. Jelaskan alur sebelum implementasi; setiap npm run build/dev wajib approval tool. Jangan menampilkan rahasia .env, mereset database, atau memberikan role tanpa instruksi. Prioritas Core selanjutnya adalah paket, lalu billing dan Wedding MVP; periksa kondisi aktual sebelum melanjutkan.

## 15. Update sinkronisasi device lokal — 2 Oktober 2026 (Asia/Jakarta)

Bagian ini memperbarui status historis di atas berdasarkan kode yang dibawa dari device lain:

- Perlindungan penghapusan pemilik workspace dan superadmin terakhir sudah diimplementasikan dan tesnya lulus.
- Draft Paket admin sudah tersedia: memilih produk, nama, dan deskripsi. Harga, trial, kuota, publish, serta pembelian belum diaktifkan.
- Entitlement sekarang memakai foreign key product_id numerik, bukan product_slug.
- Migration 000005 sempat gagal pada MySQL error 1553 karena unique index lama masih menopang foreign key tenant_id. Kolom product_id sudah terbentuk dari percobaan parsial.
- Migration diperbaiki dengan index tenant_id independen, pemasangan constraint pengganti sebelum menghapus yang lama, serta pemeriksaan kolom/index/foreign key agar bisa dilanjutkan setelah kegagalan parsial. Jalur rollback juga diperbaiki. Slug tidak dikenal tetap menghentikan migrasi; tidak dibuang diam-diam.
- Seluruh suite lokal lulus: 121 tes, 954 assertions. Termasuk tes pengulangan/pemulihan migration, pemetaan data lama, Draft Paket, dan perlindungan akun. Tes SQLite tidak menggantikan pengujian concurrency MySQL.
- Migration 000005 dan 000006 berhasil diterapkan di MySQL lokal (batch 5). Seluruh migration berstatus Ran. Tidak menjalankan migrate:fresh atau mereset database.
- Tidak ada perubahan frontend pada perbaikan migration ini; npm build/dev tidak dijalankan.
- SMTP Gmail dan tampilan inbox tidak diuji ulang pada device ini. Jangan menganggap konfigurasi rahasia dari device lain otomatis terbawa.
- Folder aktif tetap MOSHIA-PROJECT. Berbeda dari catatan lingkungan device sebelumnya, tool sesi lokal ini masih membatasi writable workspace ke lokasi lama sehingga perubahan file meminta approval filesystem.

Prioritas berikutnya: lanjutkan Core dari Draft Paket yang sudah ada; sepakati harga, trial, kuota, mata uang, dan provider sebelum mengaktifkan billing/pembelian. Pemisahan dashboard dan admin pengguna/katalog sudah selesai sebelumnya, jangan dibuat ulang dari ringkasan lama.
## 16. Lonceng notifikasi dan Excel draft paket — 2 Oktober 2026

- Lonceng sudah berfungsi: daftar 10 notifikasi per halaman, badge belum dibaca, tandai satu/semua dibaca, pagination, empty/loading/error state dan retry, tutup dengan Escape/klik luar, tema terang/gelap dan tampilan mobile.
- Data disimpan di tabel notifications. Endpoint memeriksa akun pemilik pada setiap query; superadmin tidak boleh membaca/menandai notifikasi akun lain. Tidak ada email/WhatsApp tambahan dari fitur ini.
- Sumber notifikasi: pembuatan workspace untuk pemiliknya, serta pembuatan/perubahan draft paket untuk admin yang melakukan aksi. Pencatatan berada dalam transaksi aktivitas; tidak ada notifikasi palsu atau backfill aktivitas lama.
- Pemutakhiran dilakukan saat membuka panel/navigasi dan polling setiap 60 detik selama tab terlihat. Ini bukan WebSocket push.
- Penghapusan akun yang diizinkan juga membersihkan notifikasi akun tersebut di dalam transaksi.
- Migration 000007_create_notifications_table berhasil diterapkan pada database lokal.
- Seluruh suite lulus: 128 tes, 990 assertions. Build setelah approval berhasil; 796 modul ditransformasikan. Tampilan browser belum diperiksa langsung.
- Dokumentasi teknis: docs/NOTIFICATIONS.md.
- Ekspor: exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx. Terdapat sheet Panduan, Draft Database, Isian Paket, Kuota.
- Saat ekspor, database memiliki 0 draft paket dan 4 produk. Sheet Draft Database kosong selain header; Isian Paket berisi satu TEMPLATE per produk, bukan paket yang dibuat di database. Nama paket, harga, mata uang, trial, durasi dan kuota dibiarkan untuk pengguna tentukan.
- Workbook memiliki header, filter, freeze row, sel input kuning, dropdown serta validasi angka. Struktur XML/ZIP, empat baris produk dan harga kosong sudah diperiksa; belum dibuka secara visual di Microsoft Excel.
- Harga tetap belum diaktifkan di aplikasi. File Excel tidak mengimpor data secara otomatis dan tidak membuat paket database.
- Exporter dapat digunakan ulang: scripts/export-draft-plans.ps1 (Windows PowerShell + PHP lokal). Mengambil snapshot produk/draft terkini, menghasilkan file baru bertimestamp Asia/Jakarta, dan tidak menimpa workbook yang sudah diisi.
- Prioritas berikutnya: pengguna mengisi workbook, kemudian tinjau model penagihan/harga/durasi/trial/kuota sebelum memperluas Draft Paket dan billing.