# MOSHIA — Handoff pindah device, 14 Oktober 2026

Dokumen utama untuk melanjutkan sesi. Baca [CHECKLIST.md](../planning/CHECKLIST.md), [12 Oktober](MOSHIAUPDATE-12102026.md), [10 Oktober](MOSHIAUPDATE-10102026.md) dan [8 Oktober](MOSHIAUPDATE-08102026.md) bila perlu detail historis. Tanggal Asia/Bangkok.

## Hasil audit komunikasi

Handoff 12 Oktober sesuai laporan pengguna: poin 1–3 sudah diuji, poin 4–5 belum dilaporkan selesai. Handoff 10 Oktober terlalu ringkas untuk detail loading/My Transaction; ringkasan berikut melengkapi konteks tanpa menganggap seluruh UAT sudah selesai.

## Keputusan dan fitur yang sudah tersedia

- Laravel/Vue/Inertia; workspace memisahkan proyek dalam satu akun. Satu akun dapat membuat beberapa workspace, satu undangan Wedding per workspace. Berbagi akses anggota masih ditunda.
- Dashboard Workspace menjadi accordion: Tambah Workspace dan daftar nama workspace. Form tambah terpisah; klik nama memilih workspace dan membuka dashboard Add product. Kelola workspace mengedit nama saja (owner-only, wajib, maksimal 100 karakter).
- Breadcrumb pada halaman sidebar/profil/admin dan halaman turunannya. Hover tombol/menu menggunakan gradasi merah Moshia; tombol profil dan tutup popup dibuat kecil.
- Wedding memiliki Gold Rp149.000/6 bulan, Emerald Rp249.000/12 bulan, Diamond Rp559.000/12 bulan. Paket terkait workspace, bukan otomatis untuk semua workspace akun. Fitur video/domain belum seluruhnya dipenuhi.
- Pilihan paket membuka popup keranjang; Make payment membuat pesanan. Pembayaran development manual BCA 12345678, MOSHIA CORPORATE (dummy); admin Payment accepted. Batas pembayaran 24 jam; Midtrans baru rencana.
- Billing profil hanya memiliki Subscription, Payment History dan Payment Method (Manual payment). Subscription termasuk paket dibayar yang menunggu Publish.
- My Transaction pada menu profil menampilkan semua status pesanan milik akun dengan label workspace, paket, nominal dan tanggal. Lanjutkan pembayaran pada pesanan pending membuka billing tenant pesanan, bukan mengikuti workspace sesi yang berbeda. Query dibatasi pemilik, termasuk superadmin; pagination tersedia.
- Produk Anda di dashboard, di atas Add product, menampilkan ringkasan pembayaran Wedding terbaru yang diterima pada workspace tersebut. Manage menuju Wedding Studio jika owner, order terkini masih berlaku, entitlement tersedia dan mode development aktif. Pending tidak muncul sebagai produk dibayar; akses dicabut/kedaluwarsa tidak mendapat Manage.
- Wedding Studio sudah memiliki template, konten, foto/musik, Preview, Publish, RSVP/ucapan serta moderasi. Manage/Preview tidak memulai masa aktif. Masa aktif baru dimulai pada Publish pertama; Unpublish menutup akses publik tanpa menghapus isi atau memperpanjang masa aktif saat Publish ulang.
- Loading logo global pada request navigasi/form Inertia, ring merah 64 px/logo 28 px; notifikasi Axios memakai indikator dalam panel. Checkout memakai loading bersama. Mengikuti start/finish request, tanpa delay buatan; mendukung dark mode/reduced motion. Aksi instan seperti accordion tidak diberi loading buatan. Pemuatan awal sebelum JavaScript/halaman publik non-Inertia tidak tercakup loader global ini.
- Registrasi wajib verifikasi email. Pengguna sebelumnya telah menguji email masuk dan verifikasi berhasil. SMTP Gmail disiapkan, dengan fondasi untuk pengirim domain Moshia; jangan mencetak kredensial.

## Posisi pengujian — lanjutkan dari sini

Pengguna pada 12 Oktober melaporkan poin 1–3 sudah dikerjakan tanpa melaporkan masalah:
1. Buat dua workspace, ganti nama, berpindah dan periksa konteks.
2. Add product, pilih paket, keranjang, pesanan/riwayat/countdown.
3. Payment accepted, Subscription, editor tersedia sebelum masa aktif berjalan.

Masih menunggu pengguna:
4. Workspace > Produk Anda > Manage > template/konten/foto/musik > Preview > Publish; cek awal masa aktif sesuai paket.
5. Buka tautan publik di ponsel, kirim RSVP/ucapan, Unpublish lalu Publish kembali; cek akses tertutup saat Unpublish dan tanggal akhir tidak bertambah.

Laporan pengguna bukan pengujian ulang agen atau bukti semua edge case/timeout 24 jam nyata. Jangan mengulang pembangunan fitur yang sudah ada. Tangani masalah dari uji poin 4–5 terlebih dahulu. Invoice/gateway, domain/video, kuota komersial, restore dan kesiapan produksi belum dianggap selesai; Account activity/Notification settings masih terbatas.

## Bukti historis, bukan pengujian baru hari ini

- 10 Oktober: build 816 modul; ProfileBillingHistoryTest 3 tes/97 assertions; WorkspaceProductsTest 1 tes/62 assertions lulus.
- 8 Oktober: WorkspaceManagementTest + PlatformTest 9 tes/117 assertions lulus. Suite penuh 187 tes/1.640 assertions berasal dari tahap sebelumnya, bukan seluruh source terkini.
- 14 Oktober: membaca handoff/checklist, memeriksa git status, source loader/menu/transaksi/ringkasan produk dan konfigurasi lokasi file. Perubahan sesi ini dokumentasi saja; tidak menjalankan build/test aplikasi, export/import database, commit/push atau deploy. Diff whitespace diperiksa setelah edit.

## Yang perlu dibawa ke device lain

1. Source lengkap termasuk file baru: git status saat audit masih memiliki modified/untracked, khususnya docs/update/, GlobalLoading.vue dan WorkspaceProductsTest.php. Pemindahan handoff dari root terlihat sebagai penghapusan file lama dan folder baru. Jika memakai Git, pastikan perubahan serta file baru ikut commit/push; sesi ini belum melakukannya.
2. Database terbaru harus dibawa terpisah. Sesi ini tidak mengekspor database dan tidak memastikan dump lama mencakup transaksi/UAT terakhir. Gunakan dump SQL selesai, bukan .partial; ekspor lokal disimpan di exports/database/ yang diabaikan Git. Backup database tujuan sebelum menggantinya; jangan reset database untuk sekadar melanjutkan.
3. Bawa file unggahan selain database: storage/app/private (disk lokal, foto/musik) dan storage/app/public bila digunakan. Dump SQL tidak membawa file media.
4. Pindahkan konfigurasi .env secara privat, pertahankan APP_KEY untuk data yang dipindah; sesuaikan koneksi database/APP_URL dan konfigurasi SMTP di device tujuan. Jangan memasukkan rahasia ke Git/chat.
5. Dependensi vendor/node_modules dan public/build diabaikan Git. Device tujuan perlu dependensi dari lockfile dan aset build yang sesuai. Setiap npm run build/dev tetap meminta persetujuan tool. Periksa status migrasi dan konfigurasi development payment sebelum pengujian; jangan memakai migrate:fresh/reset.

## Aturan lanjutan

Bahasa Indonesia. Setiap perubahan memperbarui docs/update/MOSHIAUPDATE-DDMMYYYY.md sesuai tanggal Asia/Bangkok serta docs/planning/CHECKLIST.md. Baca handoff terbaru berdasarkan tanggal, bukan urutan alfabet. Tidak reset DB, mengubah role, mengarang aturan komersial atau deploy tanpa instruksi. Tidak mencentang item parsial.

Prompt awal device tujuan: “Baca AGENTS.md, docs/update/MOSHIAUPDATE-14102026.md dan docs/planning/CHECKLIST.md. Poin UAT 1–3 sudah saya uji; lanjutkan pendampingan poin 4–5, jangan membangun ulang fitur yang sudah tersedia.”
