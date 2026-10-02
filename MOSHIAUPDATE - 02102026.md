# UPDATE MOSHIA

Tanggal update: **2 Oktober 2026 (Asia/Bangkok, UTC+7)**.

Dokumen ini mencatat implementasi aktual, pekerjaan yang masih tertunda, dan konteks untuk melanjutkan pengembangan. Hasil tes berasal dari pengerjaan yang dijelaskan di bawah; tidak ada pengujian ulang seluruh fitur hanya untuk memperbarui dokumen ini. Dokumen ini menggantikan ringkasan lama sebagai acuan progres terbaru.

## 1. Ringkasan posisi project

Moshia sudah memiliki website utama, autentikasi dengan verifikasi email wajib, dashboard berbeda untuk superadmin/pelanggan, pengelolaan pengguna dan penugasan role melalui admin, serta fondasi Core untuk workspace, katalog produk, dan pemeriksaan hak akses produk. Gmail SMTP sudah berjalan dan email verifikasi sudah diberi desain Moshia.

**Posisi roadmap: Fase 1 — Moshia Core. Moshia belum menjadi platform SaaS lengkap.** Admin memiliki ringkasan platform dan pengelolaan pengguna/role. CRUD produk, paket, subscription, pembayaran, dan fungsi bisnis keempat produk belum diimplementasikan.

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

Pemisahan dashboard dan pengelolaan pengguna/assignment role sudah diimplementasikan. Tabel di atas adalah target platform keseluruhan; pengelolaan anggota workspace, produk/paket, pembayaran, settings, dan audit masih tertunda.

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

- Registri empat produk di `config/moshia.php`.
- Service ProductCatalog sebagai sumber bersama untuk landing, dashboard, dan admin.
- Status semua produk masih `planned`, ditampilkan sebagai segera hadir pada dashboard/admin.
- Tabel `core_entitlements`.
- Contract ProductAccess dan implementasi database.
- Pemeriksaan keanggotaan workspace, produk, status `active`/`trial`, tanggal mulai dan akhir.
- Alias middleware `product.access` untuk route produk yang akan dibangun.
- Superadmin tidak otomatis mendapat entitlement produk dan tidak melewati pemeriksaan keanggotaan.

### Belum dikerjakan

- CRUD katalog melalui admin.
- Endpoint pemberian entitlement oleh billing/admin yang terotorisasi.
- Kuota pemakaian dan pencatatan konsumsi atomik.
- Hubungan entitlement dengan paket dan subscription.
- Route bisnis produk yang menggunakan pemeriksaan akses tersebut.

Entitlement adalah fondasi akses, **bukan bukti pembayaran sudah terintegrasi atau produk sudah dapat digunakan**.

## 8. Struktur modul saat ini

```text
app/Modules/Core/
├── Catalog/
│   ├── ProductCatalog.php
│   ├── DashboardDestination.php
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
- Seluruh suite belum diulang setelah perubahan khusus desain email; tes terkait di atas sudah lulus. Build tidak dijalankan untuk template email karena tidak diperlukan.
- Tidak ada migration baru, reset database, atau pemberian role massal selama tahap dashboard/admin/verifikasi email ini.
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
| `config/moshia.php` | Katalog produk bersama |
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
2. Lanjutan Core yang disarankan: pengelolaan katalog produk dan paket melalui admin. Ini belum diimplementasikan dan belum ada keputusan detail paket/harga.
3. Sepakati role anggota workspace, kebijakan kepemilikan/penghapusan, paket/harga, trial, kuota, mata uang, serta provider pembayaran.
4. Lengkapi billing, subscription, entitlement dari pembayaran terverifikasi, invoice dan audit.
5. Lengkapi notifikasi, media, queue, kebutuhan operasional minimum, serta migrasi email domain saat siap.
6. Bangun satu Wedding vertical slice: template → isi konten → preview → publish, sampai bisa dipakai dan diuji end-to-end.
7. Lanjutkan Jastip, Photo Booth, Restaurant sesuai roadmap. Pembuatan definisi role/permission dan pengelolaan anggota masih pekerjaan tersendiri, bukan fitur yang otomatis selesai dengan assignment role admin.

Permintaan terakhir pengguna adalah **memperbarui dokumen progres ini**, setelah desain email disesuaikan. Jangan otomatis menganggap pekerjaan katalog/billing sudah diminta atau selesai.

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

> Baca "MOSHIAUPDATE - 02102026.md", docs/ARCHITECTURE.md, dan docs/EMAIL.md. Project aktif ada di MOSHIA-PROJECT dan seluruh pengembangan mengacu blueprint utama. Pemisahan dashboard, admin pengguna/assignment role, verifikasi email wajib, dan Gmail SMTP sudah berjalan. Pengguna sudah menguji registrasi serta verifikasi; desain email Moshia terbaru sudah dirender dan diuji terkait, tetapi belum diperiksa di inbox. Pertahankan desain hitam-merah, stack, dan perubahan yang belum di-commit. Jelaskan alur sebelum implementasi; setiap npm run build/dev wajib approval tool. Jangan menampilkan rahasia .env, mereset database, atau memberikan role tanpa instruksi. Prioritas Core selanjutnya adalah katalog/paket, lalu billing dan Wedding MVP; periksa kondisi aktual sebelum melanjutkan.
