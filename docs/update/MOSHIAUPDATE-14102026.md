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

## Detail pasangan dan urutan tampilan — 14 Oktober 2026 (Asia/Jakarta)

- Permintaan pengguna: Pasangan Pertama (Pengantin Pria), Pasangan Kedua (Pengantin Wanita); nama orang tua dipisah menjadi Nama Lengkap Bapak dan Nama Lengkap Ibu untuk masing-masing pasangan.
- Tombol Tukar posisi atas–bawah mengubah partner_order tanpa menukar identitas, foto atau data keluarga. Urutan diterapkan ke form, ringkasan, preview, judul/hero, detail pasangan dan penutup undangan.
- Data tersimpan dalam JSON draft_content/published_content yang sudah ada; tidak perlu migrasi database. Draft mengubah preview setelah disimpan; publik berubah setelah Publish.
- Field keluarga lama tetap disimpan dan tersedia sebagai catatan yang dapat diedit/dikosongkan. Tampilan lama menjadi fallback jika kedua nama orang tua belum diisi. Tidak menebak siapa bapak/ibu dari teks lama.
- Validasi server: urutan dari allowlist, nama bapak/ibu opsional maksimal 200 karakter. Error nama pasangan dibersihkan saat pengguna mengedit field terkait.
- Bukti sesi ini: WeddingEditorExperienceTest 14 tes / 166 assertions lulus; termasuk urutan draft/published, escaping, validasi dan kompatibilitas data lama. npm run build setelah persetujuan berhasil (816 modul). Bukan suite penuh atau UAT browser.
- Review berikutnya: Wedding Studio > Pasangan > isi bapak/ibu > Tukar posisi > Simpan draft > Preview > Publish. Pastikan data tiap pasangan tetap sesuai; lanjutkan UAT 4–5.

## Penyelarasan arsitektur modular — 14 Oktober 2026 (Asia/Jakarta)

- Audit saran gambar: namespace, prefix route/tabel, izin produk, storage, queue dan rancangan VPS.
- Wedding dipindah ke app/Modules/Products/Wedding; seluruh import PHP, route loader dan tes diperbarui. Http/Controllers dan Http/Requests dipertahankan; tests/Feature tetap mengikuti konfigurasi PHPUnit.
- WeddingServiceProvider mendaftarkan Gate abilities wedding.invitations.view/update/publish, wedding.media.upload dan wedding.responses.moderate. Middleware can dipasang pada route privat; owner/verified/entitlement tetap diperiksa oleh layanan yang sama. Tidak memberi role/permission baru ke akun database; pembagian izin anggota lewat Spatie masih ditunda bersama fitur kolaborasi.
- Route wedding.* sudah terpisah; URL/nama existing dipertahankan agar navigasi/tautan tetap kompatibel. Tidak memaksa contoh wedding.invitations.index untuk editor satu undangan.
- Nama tabel core_/wedding_ dipertahankan. Tidak menambah tabel contoh yang belum diperlukan. Unggahan baru menuju workspaces/{tenant_id}/wedding/; lokasi lama tetap dibaca melalui path record core_media.
- Queue produk belum memiliki job sehingga nama queue hanya standar untuk implementasi berikutnya, belum worker aktif. Infrastruktur VPS belum diubah/deploy.
- Suite sesi ini: 197 tes / 1.907 assertions lulus. Diff whitespace lulus. Tidak menjalankan ulang npm build/dev karena tidak mengubah frontend pada refactor ini. Build 816 modul sebelumnya berasal dari perubahan detail pasangan.
- Core ManualWeddingBilling masih adapter spesifik Wedding; pemisahan fulfillment melalui kontrak/event internal tetap utang arsitektur sebelum produk berbayar kedua.

- Verifikasi tambahan: 11 route Wedding terdaftar ke namespace baru. Autoload Composer berhasil diperbarui offline dengan PHP project (6.727 classes), tanpa plugin/script; percobaan awal yang tidak selesai dihentikan.

## Upload foto langsung pada pasangan — 14 Oktober 2026

- Label Foto Pengantin Pria/Foto Pengantin Wanita dan input upload langsung pada tahap Pasangan tersedia. Pilih file lalu klik Unggah; JPG/PNG/WebP maksimal 5 MB mengikuti validasi Core Media.
- Form upload masing-masing pasangan memiliki progres/error sendiri. ID media hasil upload dikirim lewat session flash dan props Inertia, lalu dipilih otomatis pada pasangan terkait tanpa menghapus isian draft. Urutan pria/wanita tidak memengaruhi target foto.
- Pilihan foto lama tetap tersedia. Foto baru tetap privat; Simpan draft menyimpan pilihan, Publish memperbarui publik. Upload sendiri tidak mengubah snapshot.
- Verifikasi sesi ini: WeddingEditorExperienceTest 15 tes / 176 assertions lulus; build disetujui berhasil 816 modul. UAT upload browser belum dilakukan. Review: Pasangan > pilih file > Unggah Foto Pengantin Pria > Simpan draft > Preview.

## Detail acara dan resepsi opsional — 14 Oktober 2026

- Nama acara utama default Pemberkatan Pernikahan, dapat diubah (misalnya Akad Nikah). Resepsi memakai checkbox Tambahkan resepsi/acara kedua; form disembunyikan dan tidak tampil pada undangan saat nonaktif.
- Acara kedua memiliki nama (default Resepsi), tanggal, jam, nama tempat/alamat, dan tautan peta sendiri. Saat aktif nama/tanggal/tempat wajib diisi. Nama maksimal 100 karakter; peta hanya HTTP/HTTPS.
- Isian resepsi tetap disimpan saat nonaktif. Data lama tanpa flag mengikuti keberadaan tanggal resepsi. Perubahan disimpan dalam JSON existing tanpa migration. Simpan draft memperbarui preview; Publish memperbarui publik.
- Tes Wedding 17 tes / 201 assertions lulus, termasuk default, legacy, toggle, validasi dan snapshot. Build disetujui berhasil 816 modul. UAT browser pending.

## Susunan undangan lengkap, Gift dan video — 14 Oktober 2026

- Tab Foto & musik disusun ulang menjadi Susunan undangan dengan sembilan bagian sesuai permintaan: cover, header foto/video, foto Wedding/ayat, Save the date, Our Story Love dinamis (default Perkenalan/Pacaran/Engagement), RSVP existing, Wedding Gift, Thank you page, footer Made with love - Moshia Invitation. Galeri dan musik tetap tersedia.
- Upload per bagian otomatis memilih media terkait. Video Emerald/Diamond dibatasi pada upload, save dan publish; Gold foto saja. Batas video teknis 30 MB disesuaikan dengan PHP lokal 40M; bukan kuota/harga komersial.
- core_banks dan enam bank awal ditambahkan melalui migration 000012, berhasil dijalankan lokal. Superadmin memiliki menu Bank Wedding Gift untuk menambah bank. Rekening hadiah menyimpan bank/nomor/nama pemilik dan snapshot nama bank; tidak terkait pembayaran paket.
- Konfigurasi WEDDING_PUBLIC_DOMAIN opsional disediakan; domain akan memakai slug UUID existing, route media/RSVP dan redirect tautan lama. .env, DNS/HTTPS dan server tidak diubah. Mode development payment masih berlaku.
- Data baru memakai JSON draft/published existing; referensi media baru ikut otorisasi publik, sementara draft tetap privat. Cerita lama menjadi fallback/migrasi editor. Rincian: ../planning/INVITATION_SECTIONS.md.
- Bukti sesi: suite 204 tes / 2.005 assertions; build disetujui 818 modul; browser fixture desktop/mobile tanpa error runtime, tambah/hapus cerita dan dropdown diperiksa; perbaikan lebar mobile diperiksa ulang. Situs referensi tidak dapat dibuka oleh alat penelusuran.
- Lanjutkan review dengan data sendiri: upload cover/header, foto/ayat, isi cerita, Gift, penutup → Simpan draft → Preview → Publish. UAT video nyata/mobile dan domain production belum selesai. Bawa migration, source, DB terbaru dan file storage saat pindah device.
