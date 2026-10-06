# MOSHIA — Handoff diperbarui 6 Oktober 2026

Baca dokumen ini terlebih dahulu di device berikutnya, lalu docs/planning/WEDDING_PACKAGES.md dan CHECKLIST.md. Ini ringkasan keputusan dan status implementasi, bukan transkrip.

## Pindah perangkat — ekspor terbaru 6 Oktober 2026

Gunakan bagian terbaru ini dan pembaruan 6 Oktober di bawah sebagai acuan. Catatan handoff awal di bagian bawah dipertahankan sebagai riwayat, bukan daftar pekerjaan yang harus diulang.

### Database yang dibawa

- **File terbaru: `exports/database/moshia-20261006-072406.sql`** (07:24:06 Asia/Bangkok).
- Ukuran 34.147 byte; 22 tabel. Mencakup struktur/data akun, role, workspace, paket, notifikasi, entitlement, pesanan pembayaran dan undangan.
- Migration lokal sampai **2026_10_05_000009_create_manual_wedding_purchases** sudah Ran. Countdown 24 jam tidak memerlukan migration baru.
- Checksum pendamping: `exports/database/moshia-20261006-072406.sql.sha256`.
- SHA-256: `0900ce752b6acd4e30e3337a07a138908434abe991dab13b8ca3cde59c935948`.
- Export mysqldump selesai dengan exit 0, tanpa warning; penanda selesai dan tabel penting diperiksa. **Restore belum diuji.** Database sumber tidak diubah oleh ekspor.
- Folder `exports/database/` diabaikan Git. Dump mengandung data pribadi/password hash; kirim melalui saluran privat. Jangan unggah ke repository publik.
- `dbmoshia.sql` di root adalah ekspor lama; gunakan file bertanggal di atas untuk perpindahan kali ini. File lama dipertahankan.
- Untuk ekspor ulang dari perangkat ini: `php scripts/export-local-database.php`. Script memakai konfigurasi Laravel aktif untuk database lokal dan menghasilkan file bertanggal baru, tanpa mencetak kredensial. Sesuaikan lokasi mysqldump di script bila XAMPP perangkat baru berbeda.

### Berkas yang wajib ikut

1. **Source lengkap termasuk file baru/untracked.** Banyak perubahan sesi ini belum di-commit/push. Menarik commit lama dari Git saja tidak membawa semua pekerjaan. Jika menyalin manual, bawa seluruh folder project yang diperlukan, termasuk `app/Modules/Wedding`, file Billing baru, komponen countdown, halaman Vue, migration dan tests baru.
2. `composer.lock`, `package-lock.json`, dokumen planning/checklist, workbook terisi dan handoff ini.
3. File SQL terbaru beserta checksum; keduanya tidak ikut Git.
4. `.env` melalui saluran privat. Pertahankan `APP_KEY` bila memakai data lama; sesuaikan koneksi DB dan APP_URL. Jangan menjalankan key:generate untuk salinan data lama tanpa alasan.
5. File upload pada `storage/app` jika ada. Dump SQL tidak memuat upload, konfigurasi SMTP atau aset build. Aset build dapat dibawa dari `public/build` atau dibuat ulang setelah approval.

### Langkah pada device tujuan

1. Siapkan PHP 8.2, MySQL/MariaDB yang kompatibel dan Node sesuai pin Volta project. Jalankan `composer install` dan `npm ci` dari lockfile bila dependensi belum tersedia.
2. Backup database tujuan bila sudah berisi data. **Buat database kosong baru** melalui phpMyAdmin, pilih database tersebut lalu **Import** file SQL terbaru. Dump berisi DROP TABLE: jangan impor langsung ke database berisi data penting. Tidak memakai migrate:fresh.
3. Sesuaikan `.env`: DB_HOST/PORT/DATABASE/USERNAME/PASSWORD dan APP_URL. Untuk mencoba simulasi rekening dummy gunakan **APP_ENV=local** dan **MANUAL_DEVELOPMENT_PAYMENTS=true**; jangan mengubah environment server produksi demi membuka simulasi.
4. Jalankan `php artisan optimize:clear`, lalu `php artisan migrate:status`. Setelah import terbaru, migration sampai 000009 seharusnya Ran. Jalankan `php artisan migrate` hanya jika ada migration pending.
5. Jalankan `php artisan test`. Acuan terakhir **172 tes / 1.389 assertions**, SQLite in-memory; ini hasil perangkat asal, bukan otomatis hasil perangkat tujuan.
6. Build/dev harus meminta **approval tombol tool setiap kali**, sesuai preferensi pengguna. Build terakhir sudah disetujui dan berhasil: **802 modul**. Belum ada UAT browser lengkap.
7. UAT: login owner/admin → pilih workspace → card Wedding **Lihat paket** → pilih Gold/Emerald/Diamond → **Make payment** → countdown 24 jam → admin **Payment accepted** → editor → simpan → preview privat → **Publish**. Cek lintas akun/workspace, mobile/tema, expiry dan reaktivasi.

Waktu pesanan tidak direset saat pindah perangkat. Pesanan yang sudah berumur 24 jam tanpa penerimaan admin akan kedaluwarsa; buat pesanan baru untuk mencoba. Pembayaran yang sudah diterima tetap membuka persiapan editor, sementara masa aktif undangan baru dimulai saat Publish.

### Lanjutkan pekerjaan berikutnya

Alur manual, countdown dan editor teks dasar sudah tersedia; jangan dibangun ulang. Prioritas berikut: UAT alur ini, lalu scope Wedding D04 serta media/storage/kuota untuk C06/W01/W02. Midtrans masih rencana gateway, belum terintegrasi. Template lengkap, media, video/domain, invoice, webhook, unpublish, RSVP dan kesiapan produksi tetap terbuka.

Prompt pembuka di device tujuan:

> Baca MOSHIAUPDATE-05102026.md, khususnya pembaruan 6 Oktober 2026, serta docs/planning/WEDDING_LIFECYCLE.md dan CHECKLIST.md. Periksa source dan hasil import exports/database/moshia-20261006-072406.sql. Pembayaran manual, countdown 24 jam dan editor Wedding dasar sudah dibuat; lanjutkan dari UAT dan pekerjaan checklist berikutnya. Jangan reset database atau ulangi fitur yang tersedia. Setiap npm run build/dev wajib approval tool.

## Pembaruan tampilan — 6 Oktober 2026

Verifikasi setelah penambahan batas pembayaran: **172 tes / 1.389 assertions** lulus, pemeriksaan batas countdown JavaScript lulus, build setelah persetujuan berhasil (**802 modul**). UAT browser manual masih belum dilakukan.

Pembayaran memiliki batas **24 jam sejak created_at pesanan**, termasuk pesanan pending yang sudah ada. Countdown HH:MM:SS mengikuti waktu server pada halaman pelanggan/admin. Tepat saat batas tercapai, status menjadi payment_expired, tombol batal/Payment accepted disembunyikan dan backend menolak penerimaan atau pembatalan pesanan expired. Owner dapat membuat order baru; retry order pending tidak memperpanjang batas. Pembayaran yang sudah diterima tetap membuka editor dan masa aktif undangan tetap dihitung sejak Publish. Deadline dihitung dari created_at, sehingga tidak membutuhkan migration atau scheduler. Berbeda dari expired masa aktif undangan.

Dashboard user kini membuka paket melalui tombol **Lihat paket** pada card Wedding Invitation. Bagian terpisah Paket & tagihan di bawah dashboard dihapus. Halaman paket menampilkan tiga card Gold/Emerald/Diamond yang dipilih dengan radio, lalu satu tombol **Make payment** di bawahnya. Pemilihan card tidak membuat pesanan; Make payment membuat pesanan dan menampilkan rekening dummy. Alur Payment accepted dan masa aktif sejak Publish tetap berlaku.

## Pembaruan sesi lanjutan — 5 Oktober 2026

**Bagian ini menggantikan status belum ada payment/lifecycle/Wedding pada catatan awal di bawah.**

Keputusan pengguna: Midtrans sebagai rencana gateway; development memakai rekening dummy BCA **12345678 — MOSHIA CORPORATE**. Superadmin menerima pembayaran lewat tombol **Payment accepted**. Masa aktif 6/12 bulan baru dimulai saat owner menekan **Publish**, termasuk reaktivasi setelah expired.

Sudah dibuat:
- Halaman paket/pesanan per workspace, snapshot harga server, pembatalan order belum dibayar dan riwayat.
- Admin Pembayaran Manual, konfirmasi Payment accepted, pencatatan admin/waktu dan notifikasi owner. Klik ganda tidak menggandakan efek.
- Entitlement editor setelah penerimaan pembayaran; ends_at masih kosong hingga Publish.
- Editor Wedding dasar (nama pasangan/tanggal/lokasi/pesan), preview privat, serta snapshot publik terpisah pada `/invitation/{slug}` tanpa login pengunjung.
- Satu undangan/workspace dengan ID angka dan slug UUID string. Masa aktif dihitung sejak Publish; edit/publish ulang tidak memperpanjang periode.
- Expiry menutup publik/editor tanpa menghapus data. Order baru setelah expired membuka editor setelah dibayar, tetapi publik tetap tertutup sampai Publish periode baru. Slug/konten dan riwayat periode lama dipertahankan.
- Transaksi/row lock tenant, pemeriksaan owner/membership/entitlement, validasi/escaping dan guard expiry pada request.

Route simulasi hanya terbuka pada APP_ENV local/testing dan MANUAL_DEVELOPMENT_PAYMENTS=true (default true); tertutup pada production/staging. Rekening dummy tidak untuk transfer nyata. Draft paket admin tetap draft; simulasi memakai preset development config/wedding_plans.php yang telah disetujui, tidak mengimpor workbook/mengaktifkan tabel draft.

Verifikasi perangkat ini: **168 tes / 1.311 assertions** lulus pada SQLite in-memory; migration **2026_10_05_000009_create_manual_wedding_purchases** berhasil pada MySQL lokal; **npm run build disetujui dan berhasil, 800 modul**. Tidak reset database, mengubah role, commit/push atau deploy. UAT browser/mobile, concurrency MySQL dan restore dump belum diuji.

Checklist: **9 tersedia (S01-S08, W03), 9 sebagian, 15 belum, 6 menunggu keputusan, 10 perlu verifikasi, 1 ditunda, 4 opsional**. C02/C03/C10, D03, W01/W02/W05 sebagian; W03 preview privat tersedia. Word dan Markdown diselaraskan; file before-20261005 dipertahankan.

Belum tersedia: Midtrans/webhook, invoice, kuota/trial, pajak/refund/grace, pilihan template/media/video, unpublish, RSVP, subdomain/HTTPS/custom domain. Diamond tetap mencakup domain .com manual, tetapi pemenuhannya belum dibuat. Editor saat ini satu tampilan teks dasar untuk menguji alur.

Langkah berikutnya: UAT alur manual → sepakati scope Wedding D04 dan storage/kuota → template/media C06/W01/W02 → Midtrans sandbox C03/C04 dan domain sesuai fase. Jangan membangun ulang pembayaran manual yang tersedia. Baca **docs/planning/WEDDING_LIFECYCLE.md** untuk petunjuk, batas dan aturan waktu.

File utama baru: ManualWeddingBilling.php, PurchaseOrder.php, Billing/Domain/WeddingPurchase.php, ManualPaymentController.php, Modules/Wedding, config/billing.php, migration 000009, Pages/Billing/Index.vue, Pages/Admin/Payments/Index.vue, Pages/Wedding/Edit.vue, tests/Feature/Core/ManualWeddingBillingTest.php dan tests/Unit/WeddingPurchaseTest.php.

## Catatan handoff awal (historis; status terbaru ada di atas)

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
