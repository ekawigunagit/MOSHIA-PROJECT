# Ringkasan Project Moshia untuk Melanjutkan di Device Lain

Terakhir diperbarui: 29 September 2026, Asia/Jakarta.

Dokumen ini merangkum percakapan dan kondisi kerja terakhir, bukan transkrip lengkap. Periksa file project dan database pada device tujuan sebelum melanjutkan karena lingkungan dapat berbeda.

## 1. Tujuan project dan preferensi pengguna

- Moshia adalah website induk ekosistem digital.
- Bahasa komunikasi: Bahasa Indonesia, dengan penjelasan sederhana dan bertahap. Pengguna ingin memahami alur perubahan.
- Untuk permintaan implementasi: jelaskan proses terlebih dahulu, lalu langsung kerjakan dalam lingkup permintaan. Tidak perlu meminta konfirmasi umum berulang kali.
- Jika pengguna hanya bertanya arti kode atau gambar, jelaskan terlebih dahulu; jangan menganggapnya sebagai permintaan perubahan data atau implementasi fitur baru.
- **Pengecualian wajib: setiap `npm run dev` dan `npm run build` harus mendapat persetujuan pengguna terlebih dahulu. Gunakan tombol approval tool seperti sebelumnya, bukan hanya pertanyaan dalam chat.** Jangan memberikan izin permanen/prefix baru yang melewati persetujuan per eksekusi. Jika tooling device baru berbeda, tetap tunggu persetujuan eksplisit.
- Jangan mengubah versi Node global sehingga mengganggu project lain. Gunakan versi per project melalui Volta.
- Jangan menimpa perubahan pengguna, menjalankan reset database, atau memberikan role administrator kepada akun tanpa instruksi yang jelas.

## 2. Lingkungan dan stack

- Workspace asal: `D:\XAMPP8212\htdocs\moshia`.
- Windows, PowerShell, VS Code, XAMPP, PHP 8.2, Laravel 12.
- Frontend sekarang: Laravel Breeze authentication + Vue 3 + Inertia 2 + Vite 7.
- CSS sekarang: Tailwind CSS 3 melalui PostCSS, ditambah CSS custom di `resources/css/app.css`.
- Sebelum Breeze, project memakai Tailwind 4 dan Blade. Breeze mengganti frontend dan konfigurasi menjadi Tailwind 3. `@tailwindcss/vite` masih tercantum sebagai dependency, tetapi konfigurasi Vite aktif memakai plugin Laravel dan Vue; jangan menganggap Tailwind 4 masih aktif.
- Spatie Laravel Permission terpasang (`^6.25`); model `User` menggunakan trait `HasRoles`.
- `package.json` mengunci Node melalui Volta:

```json
"volta": {
    "node": "22.23.3"
}
```

- Database lokal memakai MySQL. Detail koneksi/rahasia tidak disalin ke dokumen ini.
- Pengujian memakai SQLite `:memory:` sesuai `phpunit.xml`.

## 3. Riwayat Node dan approval

1. Awalnya Node 20.14.0. Build berhasil tetapi Vite memberi peringatan membutuhkan Node 20.19+ atau 22.12+.
2. Pengguna punya project lain dengan Node lama, sehingga dipilih Volta agar versi terisolasi per project.
3. Moshia dipin ke Node 22.23.3. PowerShell sudah benar, tetapi terminal VS Code sempat memakai PATH lama.
4. Solusi: tutup seluruh VS Code, buka dari PowerShell yang sudah benar memakai `code .`; prioritaskan executable Volta bila diperlukan.
5. Pemeriksaan berikutnya mengonfirmasi Node 22.23.3 dan npm 10.9.9; Volta berada sebelum instalasi Node biasa pada PATH.
6. Build Vue/Inertia terakhir dijalankan melalui permintaan tombol approval dan berhasil, 787 modul ditransformasikan.

Untuk diagnosis pada device baru:

```powershell
node -v
npm -v
where.exe node
volta --version
volta which node
```

`volta install` mengubah versi default; `volta pin` menetapkan versi project. Jangan mengganti default project lain secara sembarangan.

## 4. Desain yang diminta dan sudah diterapkan

Referensi pengguna adalah landing page AIVANCE bertema AI:

- Latar hampir hitam, aksen merah, teks putih/abu-abu.
- Font sans-serif menyerupai referensi; dipakai Instrument Sans dari Bunny Fonts.
- Header, navigasi, tombol bulat, hero dua kolom.
- Judul: “Better Together. Beyond Tomorrow.”
- Robot sementara, panel analitik melayang, cincin/platform merah, empat kartu ekosistem.
- Produk: Digital Products, Smart Solutions, Creative & Technology, Connected Ecosystem.
- Bagian nilai, pengenalan Moshia, eksplorasi, ajakan bergabung, footer.
- Tampilan responsif, navigasi mobile, dark/light theme dengan localStorage, fokus keyboard, reduced-motion.
- Gambar dan ikon sementara akan diganti pengguna nanti.

Asset lokal:

- `public/images/moshia-mark.svg`: logo sementara.
- `public/images/moshia-bot.svg`: robot sementara.

Implementasi awal ada di Blade, kemudian dibangun ulang ke Vue setelah pengguna memasang Breeze. **Halaman aktif sekarang adalah `resources/js/Pages/Welcome.vue`, bukan file Blade welcome lama atau `public/index.php`.**

### File frontend penting

| File | Peran |
| --- | --- |
| `resources/js/Pages/Welcome.vue` | Landing page Moshia, menu mobile, bagian ekosistem, tautan auth |
| `resources/css/app.css` | Tailwind directives dan styling Moshia termasuk responsive/auth |
| `resources/js/Components/MoshiaIcon.vue` | Ikon SVG bersama |
| `resources/js/Components/ThemeToggle.vue` | Pergantian tema dan penyimpanan preferensi |
| `resources/js/Components/ScrollToTop.vue` | Tombol kembali ke header |
| `resources/js/Layouts/GuestLayout.vue` | Kerangka login, daftar, reset password, verifikasi, konfirmasi password |
| `resources/js/Layouts/AuthenticatedLayout.vue` | Kerangka halaman akun dengan navigasi dashboard/profil/logout |
| `resources/js/Pages/Dashboard.vue` | Sambutan akun dan tautan ekosistem/profil |
| `resources/js/Pages/Profile/Edit.vue` | Form profil bawaan Breeze dengan tema bersama |
| `resources/js/Components/{PrimaryButton,SecondaryButton,TextInput,InputLabel,Checkbox}.vue` | Kontrol Breeze disesuaikan tema |
| `resources/js/app.js` | Bootstrap Inertia; warna progress merah; fallback nama Moshia |
| `resources/views/app.blade.php` | Root Inertia, font, favicon, meta theme-color |
| `tailwind.config.js` | Font Instrument Sans dan konfigurasi Tailwind 3 |

Tombol kembali ke atas:

- Tombol merah bulat di kanan bawah.
- Muncul setelah scroll lebih dari 300 piksel, hilang saat kembali ke atas.
- Klik menggulir ke posisi 0; fokus diarahkan ke brand pada header.
- Mematuhi reduced-motion dan membersihkan event listener saat komponen Vue dilepas.

Login/registrasi tetap memakai proses Breeze. Halaman depan mengarah ke login jika belum masuk, atau dashboard jika sudah masuk. CTA bergabung mengarah ke register.

## 5. Middleware dan route

`bootstrap/app.php` mempertahankan middleware web Inertia, kemudian mendaftarkan:

```php
$middleware->alias([
    // 'subscribed' => \App\Core\Subscriptions\Http\Middleware\EnsureSubscriptionActive::class,
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
]);
```

- Alias adalah nama pendek untuk middleware; mendaftarkannya belum otomatis membatasi semua halaman.
- Alias dipakai pada route, misalnya `->middleware(['auth', 'role:super-admin'])`.
- `subscribed` masih dikomentari. Kelas `EnsureSubscriptionActive` belum ditemukan; pemeriksaan subscription belum diimplementasikan.

`routes/web.php` saat terakhir diperiksa:

- `/` merender Inertia `Welcome` dengan properti keberadaan route login/register.
- `/dashboard` merender `Dashboard`, middleware `auth` dan `verified`.
- Route profil edit/update/delete dilindungi `auth`.
- Grup `['auth', 'role:super-admin']` sudah ada tetapi **masih kosong**. Belum ada halaman admin/App Registry di dalamnya.
- Memuat `routes/auth.php` dari Breeze.

Role `super-admin` tidak otomatis berarti bypass seluruh permission; konfigurasi tersebut belum dibuat.

## 6. Error registrasi dan perbaikannya

Error yang benar-benar ditampilkan pengguna:

```text
Spatie\Permission\Exceptions\RoleDoesNotExist
There is no role named `user` for guard `web`.
```

Terjadi di `RegisteredUserController` saat `$user->assignRole('user')` setelah akun dibuat.

Perbaikan yang sudah dilakukan:

1. Membuat `database/seeders/RoleSeeder.php`.
2. Seeder membersihkan permission cache, lalu memakai `Role::findOrCreate($name, 'web')` untuk `user` dan `super-admin` sehingga aman dijalankan berulang.
3. `DatabaseSeeder` memanggil `RoleSeeder`.
4. Menjalankan `php artisan db:seed --class=RoleSeeder` pada database lokal; berhasil.
5. Pembuatan user dan assignment role dibungkus `DB::transaction()` dalam `app/Http/Controllers/Auth/RegisteredUserController.php`.
6. Event `Registered` dan login dilakukan setelah transaksi berhasil.
7. Tes registrasi ditambah untuk assignment role user, bukan super-admin, seeder berulang, dan rollback ketika role belum tersedia.

**Penting:** transaksi mencegah akun parsial, tetapi tidak membuat role secara otomatis pada setiap request. Database baru tetap perlu migration dan RoleSeeder.

`DatabaseSeeder` juga masih membuat `Test User` dengan email `test@example.com` melalui factory. Karena itu, untuk hanya menyiapkan role gunakan `--class=RoleSeeder`, bukan menjalankan seluruh DatabaseSeeder berulang pada database berisi data.

File terkait:

- `config/permission.php`
- `database/migrations/2026_09_28_071933_create_permission_tables.php`
- `database/seeders/RoleSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Models/User.php`
- `tests/Feature/Auth/RegistrationTest.php`

### Pemeriksaan terakhir pada 29 September

- Role `user` guard `web`: tersedia.
- Role `super-admin` guard `web`: tersedia.
- Jumlah akun tanpa role: 0.
- Empat tes registrasi lulus (11 assertions).
- Log Laravel terakhir masih error 28 September; tidak ditemukan entri error lebih baru saat pemeriksaan tersebut.
- Error lama lain di log: koneksi MySQL ditolak, salah ketik `cache:celar`, tabel cache belum ada, dan RoleSeeder belum ditemukan sebelum dibuat. Jangan menganggap semua entri lama masih merupakan masalah aktif.

Jangan menganggap data ini otomatis ada di device tujuan. Status role akun tertentu, termasuk siapa super-admin, belum diverifikasi dalam percakapan ini.

## 7. Penjelasan Tinker yang sudah diberikan

Pengguna belum memahami instruksi memberi role melalui Tinker. Sudah dijelaskan:

- `php artisan tinker` membuka terminal interaktif Laravel.
- Tanda `>>>` pada panduan tidak perlu diketik.
- `User::first()` mengambil akun pertama, belum tentu akun pengguna.
- Pilih akun melalui email, periksa bahwa ditemukan dan benar, baru beri role jika diminta.

Contoh edukasi, **bukan perintah yang telah dijalankan untuk memberikan hak admin**:

```php
$user = App\Models\User::where('email', 'email-anda@example.com')->first();
$user; // Periksa identitas; jika null, akun tidak ditemukan.
$user->assignRole('super-admin');
$user->hasRole('super-admin');
```

Role harus tersedia dahulu. `roles` menyimpan definisi role; `model_has_roles` menghubungkan akun dan role. Registrasi memberi role user lewat kode controller, bukan otomatis hanya karena Spatie dipasang.

## 8. Percakapan terakhir: gambar “Step 7 — Base Layout / Dashboard”

Pengguna mengirim gambar instruksi:

| File pada panduan | Tujuan |
| --- | --- |
| `Layouts/MoshiaLayout.vue` | File baru `resources/js/Layouts/MoshiaLayout.vue` |
| `Components/NotificationBell.vue` | File baru `resources/js/Components/NotificationBell.vue` |
| `Pages/Dashboard.vue` | Mengganti `resources/js/Pages/Dashboard.vue` bawaan Breeze |

Pengguna lalu meminta melanjutkan “error kemarin”. Pemeriksaan menunjukkan error role sudah tertangani. Pengguna memperjelas bahwa ia **ingin memahami arti gambar**.

Penjelasan terakhir yang diberikan:

- Gambar adalah petunjuk lokasi penempatan file, bukan error.
- Layout menjadi kerangka header/menu/konten; NotificationBell adalah komponen lonceng; Dashboard adalah isi halaman setelah login.
- “File baru” berarti membuat file lalu mengisi kode yang disediakan.
- “Timpa” berarti mengganti isi file, tetapi dashboard Moshia sudah dikustomisasi sehingga perlu menggabungkan perubahan dengan hati-hati.
- Gambar hanya memuat nama/lokasi file, **tidak memuat source code**. Membuat file kosong tidak menghasilkan fitur.
- `AuthenticatedLayout.vue` saat ini sudah menjalankan fungsi layout; nama `MoshiaLayout.vue` bukan kewajiban Laravel.

**Status:** `MoshiaLayout.vue` dan `NotificationBell.vue` belum dibuat, kode sumber dari panduan belum diberikan, dan sistem notifikasi belum diimplementasikan. Jangan menyatakan langkah Step 7 selesai atau mengasumsikan API notifikasi sudah ada.

Permintaan terbaru adalah membuat dokumen SUMMARY ini untuk berpindah device. Tidak ada permintaan implementasi Step 7 setelah penjelasan tersebut.

## 9. Verifikasi yang telah dijalankan

- Implementasi Blade awal: build sukses dan 2 tes Laravel lulus.
- Migrasi desain Vue/Inertia: 30 komponen Vue dan sintaks CSS divalidasi; 25 tes Laravel lulus saat itu.
- Build Vue/Inertia setelah approval: berhasil tanpa error.
- Alias middleware: `php -l bootstrap/app.php` dan pemuatan daftar route berhasil.
- Perbaikan role/registrasi: 4 tes RegistrationTest lulus; diulang 29 September dan tetap lulus.
- Belum dilakukan pemeriksaan visual browser menyeluruh setelah migrasi Vue/Inertia. Keberhasilan build/tes bukan bukti semua interaksi sudah diuji di browser.
- Build tidak perlu dijalankan hanya untuk perubahan middleware, seeder, atau dokumen ini.

## 10. Memindahkan project ke device lain

**Masih ada banyak perubahan belum di-commit dan file untracked pada workspace asal.** Menyalin SUMMARY saja atau clone commit lama tidak membawa implementasi terbaru. Periksa `git status`, lalu simpan/pindahkan source code serta file baru yang relevan. Jangan melakukan commit/push otomatis tanpa lingkup yang disepakati.

Langkah penyiapan yang perlu dipertimbangkan pada device baru:

1. Bawa seluruh perubahan source, `composer.lock`, dan `package-lock.json`.
2. Pasang PHP dan ekstensi yang sesuai, Composer, serta Volta. Pastikan project menggunakan pin Node 22.23.3.
3. Pasang dependensi dari lockfile dengan `composer install` dan `npm ci`.
4. Siapkan `.env` secara lokal. Jangan memasukkan password, token, atau APP_KEY ke SUMMARY/Git. Untuk database yang dipindahkan, pertahankan APP_KEY asal dengan aman bila ada data terenkripsi; jangan menggantinya sembarangan.
5. Database tidak otomatis ikut berpindah bersama source. Pilih restore database lama atau database baru sesuai tujuan pengguna.
6. Untuk database baru, jalankan migration normal dan `php artisan db:seed --class=RoleSeeder`. Jangan gunakan `migrate:fresh` untuk database berisi data yang perlu dipertahankan.
7. Verifikasi tes dan konfigurasi route. Sebelum menjalankan `npm run build` atau `npm run dev`, minta persetujuan melalui tombol approval.
8. Cek halaman utama, registrasi/login, dashboard, profil, tema, menu mobile, dan scroll-to-top di browser.

Catatan lingkungan: Git pernah menolak repository karena perbedaan kepemilikan akun Windows sandbox. Pemeriksaan menggunakan `git -c safe.directory=D:/XAMPP8212/htdocs/moshia ...` per perintah, tanpa mengubah konfigurasi global. Tidak perlu menerapkan workaround ini jika device baru tidak mengalami masalah tersebut.

## 11. Pesan awal yang dapat dipakai pada percakapan baru

> Baca SUMMARY.md dan periksa kondisi project Moshia saat ini. Pertahankan desain hitam-merah dan stack Breeze + Vue + Inertia. Jelaskan alur sebelum menerapkan perubahan. Setiap npm run dev/build harus memakai tombol approval terlebih dahulu. Error role user telah diperbaiki dengan RoleSeeder dan transaksi registrasi; verifikasi database di device ini. Langkah MoshiaLayout/NotificationBell dari panduan belum diimplementasikan karena hanya gambar lokasi file yang diberikan. Jangan menimpa dashboard yang sudah dikustomisasi atau memberi role admin tanpa instruksi.
