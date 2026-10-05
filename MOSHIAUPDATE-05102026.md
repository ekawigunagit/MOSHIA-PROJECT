# MOSHIA — Handoff 5 Oktober 2026

Baca dokumen ini terlebih dahulu di device berikutnya, lalu docs/planning/WEDDING_PACKAGES.md dan CHECKLIST.md. Ini ringkasan keputusan dan status implementasi, bukan transkrip.

## Ringkasan project

Laravel 12/PHP 8.2, Breeze, Vue 3/Inertia 2, Tailwind, MySQL, Spatie Permission; Volta Node 22.23.3. Modular monolith: Core dahulu → Wedding → Jastip → Photo Booth → Restaurant. Project aktif D:\XAMPP8212\htdocs\MOSHIA-PROJECT; folder lama moshia bukan project aktif.

Sudah tersedia: landing hitam-merah/tema/responsif; auth/verifikasi/profil; dashboard admin dan pelanggan; admin pengguna/katalog; workspace pemilik; perlindungan owner dan superadmin terakhir; entitlement product_id; draft paket; lonceng database per akun dan workbook paket.

Lonceng memiliki pagination/read/read-all/polling 60 detik, bukan WebSocket. Aktivitas workspace dan draft paket menghasilkan notifikasi; bukan broadcast atau pipeline WhatsApp.

## Keputusan percakapan

- Dashboard operasional masing-masing produk akan terpisah di dalam aplikasi Laravel yang sama.
- Login cukup sekali dengan sesi web bersama; tidak perlu SSO khusus/API HTTP antar modul sekarang. Gunakan service contract/event internal. API/OIDC dipertimbangkan jika aplikasi dipisahkan nanti.
- Alur target: register/login → verifikasi → workspace → paket → pembayaran terverifikasi → akses editor → customize/preview → publish → masa aktif undangan.
- Pengunjung undangan publik tidak perlu login.
- Berbagi akses/undangan anggota ditunda. Fokus pengguna mengelola workspace sendiri.
- Satu workspace Wedding untuk satu undangan; undangan tambahan membeli paket pada workspace tambahan, tetap akun yang sama.
- Paket sekali bayar: Gold Rp149.000/6 bulan; Emerald Rp249.000/12 bulan; Diamond Rp559.000/12 bulan.
- Semua paket template standard; Emerald/Diamond request video header; Diamond termasuk pembelian domain .com saja.
- Domain diproses manual: permintaan nama → pemeriksaan ketersediaan → persetujuan pelanggan → pembelian/pemrosesan tim.
- Masa aktif sejak publish pertama; edit/publish ulang tidak mengulang masa aktif.
- Kedaluwarsa menutup akses publik tetapi mempertahankan data; pembayaran kembali diperlukan untuk mengaktifkan undangan lama.
- Biaya domain premium/batas biaya dan perpanjangan domain belum ditetapkan. Jangan mengasumsikan semua domain premium sudah tercakup.
- Trial, kuota media/storage, pajak/refund/grace dan waktu mulai masa aktif reaktivasi belum ditetapkan.

## Implementasi terakhir

Form Admin → Draft Paket → Buat/Edit kini memiliki pilihan Gold/Emerald/Diamond ketika memilih produk Wedding.
Config wedding_plans.php adalah sumber aturan resmi. Kolom JSON nullable core_plans.commercial_terms menyimpan snapshot. Harga/aturan langsung dari browser ditolak; hanya preset Wedding pada produk Wedding yang diterima.
Edit preset yang sama mempertahankan snapshot; ganti preset mengambil aturan baru; kosongkan pilihan menghapus aturan draft.
Semua tetap draft, tidak memberikan entitlement dan tidak ditampilkan sebagai paket aktif pada landing. Tidak otomatis membuat tiga baris paket atau mengimpor workbook.
Aturan satu undangan/expiry/domain/video belum menjadi proses bisnis yang berjalan: baru aturan draft. Subscription, payment, invoice, quota dan modul Wedding belum dibangun.

File utama:
- config/wedding_plans.php
- app/Modules/Core/Billing/Actions/DraftPlanAttributes.php
- app/Modules/Core/Billing/Models/Plan.php
- app/Modules/Core/Billing/Http/Requests/SavePlanRequest.php
- app/Modules/Core/Billing/Http/Controllers/PlanController.php
- resources/js/Pages/Admin/Plans/Form.vue
- database/migrations/2026_10_05_000008_add_commercial_terms_to_core_plans.php
- tests/Feature/Core/WeddingDraftTermsTest.php

Migration lama 000005 sudah menangani MySQL1553/index tenant dan kondisi parsial. Jangan menggantinya dengan migrate:fresh.

## Hasil verifikasi

132 tes / 1.052 assertions lulus pada SQLite in-memory. Migration 000008 sukses pada MySQL lokal, batch 7. Build setelah approval sukses, 796 modul. git diff --check tidak menemukan error whitespace. UAT browser, email nyata, concurrency MySQL dan restore dump belum diuji pada tahap ini.

## Dokumen dan checklist

- docs/planning/MOSHIACHEKLIST.docx: checklist Word terbaru; nama ini dipilih pengguna.
- docs/planning/CHECKLIST.md: 54 item; S01–S08 dicentang karena tersedia. C01 dan D02 sebagian selesai; lainnya mengikuti status.
- docs/planning/Paket Product Moshia.xlsx: sumber paket yang diisi pengguna. Produk selain Wedding masih TBC.
- docs/planning/WEDDING_PACKAGES.md: detail aturan dan implementasi.
- docs/planning/Moshia_PRD_v1.2_2026-10-05.docx: spesifikasi target; status lama mengenai draft tanpa aturan harga sudah diperbarui oleh handoff ini.
- MOSHIAUPDATE-02102026.md: riwayat lama dipertahankan.
- exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx: template ekspor awal, bukan sumber harga terbaru.

## Database dan pindah device

dbmoshia.sql di root project merupakan ekspor struktur dan data database lokal. Dump menyertakan akun/password hash dan data aplikasi; disimpan lokal serta diabaikan Git. Tidak mengandung konfigurasi .env, file upload atau aset build. Pemeriksaan ekspor meliputi exit code, struktur tabel dan penanda selesai; belum diuji restore.

1. Bawa source terbaru, lockfiles, dokumen, workbook yang diisi, dbmoshia.sql serta file upload jika ada. Perubahan sesi ini belum di-commit/push otomatis.
2. Siapkan PHP 8.2, Composer, MySQL/MariaDB kompatibel, dan Node sesuai Volta. Instal dependensi dari lockfile.
3. Pindahkan .env secara aman di luar Git; pertahankan APP_KEY jika memakai data terenkripsi. Sesuaikan DB_HOST/DB_PORT/DB_DATABASE, APP_URL dan konfigurasi lokal.
4. Buat database kosong tujuan. Import dbmoshia.sql via phpMyAdmin → pilih database → Import. Jangan impor ke database berisi data penting tanpa backup: dump berisi DROP TABLE untuk pemulihan.
5. Jalankan php artisan migrate:status dan php artisan migrate jika ada pending. Jangan migrate:fresh.
6. php artisan optimize:clear bila konfigurasi lama terbawa; jalankan tes. Build/dev harus meminta approval tool setiap kali.
7. Cek login admin/pelanggan, workspace, lonceng, draft paket Wedding dan tampilan mobile/tema.

Database dan .env tidak otomatis berpindah melalui Git. Gunakan saluran privat untuk dump/rahasia. Jangan kirim dump ke repo publik.

## Tahap berikutnya

Lanjut C02: rancang lifecycle pembelian → akses editor sebelum publish → masa aktif sejak publish pertama. Pembayaran tidak boleh langsung memulai masa aktif undangan. Sepakati reaktivasi dan provider sebelum membangun aktivasi komersial. Jangan membangun ulang fitur S01–S08 atau mengaktifkan paket dengan asumsi aturan bisnis yang belum ditetapkan.

## Preferensi kerja

Jelaskan proses dalam Bahasa Indonesia sebelum implementasi. Setiap npm run build/dev harus approval tombol tool. Jangan reset database, mengubah role, mengarang harga atau deploy tanpa instruksi. Pada sesi ini writable root masih menunjuk folder lama sehingga penulisan folder aktif membutuhkan approval; cek izin lingkungan device baru.

Pesan pembuka sesi berikutnya:
“Baca MOSHIAUPDATE-05102026.md dan docs/planning/CHECKLIST.md. Lanjut dari draft aturan Wedding yang sudah dibuat; paket belum aktif untuk dijual. Core dahulu, kolaborasi anggota ditunda. Periksa database/source aktual sebelum perubahan; approval setiap build/dev.”
