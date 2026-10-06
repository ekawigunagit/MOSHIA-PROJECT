# Checklist Moshia

Diperbarui: **5 Oktober 2026**. Versi mudah dibaca dari checklist v1.2 (54 item).

**Fokus:** verifikasi akun → workspace → paket → Payment accepted → editor/preview → Publish → masa aktif. Rekening dummy development; Midtrans menyusul.

**Berbagi akses dan undangan anggota ditunda** sesuai arahan terbaru. Workspace awal dikelola pemilik sendiri.

## Progres terbaru — pembayaran manual dan Wedding dasar

Pembayaran manual development, editor teks, preview privat, Publish, expiry dan reaktivasi tersedia. C02/C03 tetap sebagian. Lihat WEDDING_LIFECYCLE.md untuk cara mencoba dan batas implementasi.

## Cara memakai

- [x] = sudah dikerjakan / tersedia pada kode atau artefak project. Untuk item pengujian, centang hanya setelah pengujian tersebut selesai.
- [ ] = belum dikerjakan, menunggu keputusan, ditunda, atau masih perlu verifikasi sesuai status item.
- **TERSEDIA** = fitur sudah dibuat, tetapi perlu diperiksa di device tujuan. Jangan dibangun ulang.
- **BELUM** = masih perlu dikerjakan.
- **TUNGGU KEPUTUSAN** = perlu aturan atau data dari pemilik project.
- **PERLU VERIFIKASI** = perlu bukti pengujian.
- **DITUNDA / OPSIONAL** = bukan fokus saat ini.

**Status saat ini: 9 item tersedia (S01-S08 dan W03) dicentang; 45 belum dicentang.** Rincian: 9 sebagian, 15 belum, 6 menunggu keputusan, 10 perlu verifikasi, 1 ditunda, 4 opsional. Ini jumlah item, bukan persentase project. Hasil terbaru: 168 tes / 1.311 assertions; build 800 modul. UAT belum dilakukan.

Di editor VS Code, ganti [ ] menjadi [x] setelah pekerjaan pada item selesai. Pengujian device dan kesiapan rilis dicatat terpisah pada V01–V04 dan R01–R06. Buka preview Markdown dengan **Ctrl+Shift+V**. Buka **Detail dan bukti** bila membutuhkan syarat lengkap tiap item.

**Mulai:** UAT alur manual → scope Wedding D04/storage/kuota → template/media → Midtrans sandbox dan domain sesuai fase. Kolaborasi anggota tetap ditunda.

---

## Hasil pemeriksaan terbaru — 5 Oktober 2026

- Tes otomatis: **168 passed, 1.311 assertions**, SQLite in-memory. Mencakup pembayaran manual, batas expiry, reaktivasi, isolasi tenant dan rollback.
- Database lokal: migration **2026_10_05_000009** berhasil pada MySQL lokal. Tidak melakukan reset/restore database.
- Runtime: Node **v22.23.3**, PHP **8.2.12**; vendor tersedia dan tidak ada config cache Laravel.
- Git: perubahan source/dokumen belum di-commit atau push.
- V01–V04 tetap perlu verifikasi penuh: build setelah approval berhasil (800 modul), tetapi UAT browser/mobile, email nyata tahap ini dan restore backup belum dilakukan.
- Berikutnya: UAT alur manual lalu scope template/media Wedding dan Midtrans sandbox. Harga tetap snapshot server; trial/kuota/pajak/refund/grace belum ditetapkan.
## 1. Baseline dan verifikasi device

- [ ] **V01 — Pastikan source dan dependensi** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: CORE 01–06 | Bergantung pada: Tidak ada

  Penerimaan / tindakan: Buka folder aktual; cek perubahan lokal, lockfile dan pin Volta; bawa workbook yang sudah diisi. Jangan menimpa perubahan belum di-commit.

  Bukti awal / lokasi: MOSHIAUPDATE-02102026.md; package.json; composer.lock; package-lock.json.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **V02 — Periksa database dan migration** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: CORE 01/03/04 | Bergantung pada: V01

  Penerimaan / tindakan: Konfirmasi database target dan backup; periksa migrate:status; jalankan migration normal hanya jika diperlukan. Tidak menggunakan migrate:fresh. Uji perbaikan index tenant dan migrasi parsial sesuai kebutuhan.

  Bukti awal / lokasi: database/migrations/2026_10_02_000005_link_entitlements_to_product_ids.php; EntitlementProductRelationTest.php; sampai 000007 menurut hasil historis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **V03 — Regresi fitur yang tersedia** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: CORE 01–05 | Bergantung pada: V01–V02

  Penerimaan / tindakan: Jalankan suite sesuai lingkungan pengujian; catat hasil terbaru. UAT login admin/pelanggan, verifikasi, workspace, katalog, draft paket, lonceng, profil/tema dan mobile. Email nyata diverifikasi terpisah.

  Bukti awal / lokasi: tests/Feature/Core; tests/Feature/Auth; hasil historis bukan hasil run saat ini.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **V04 — Aset frontend sesuai source** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: CORE 02 | Bergantung pada: V01

  Penerimaan / tindakan: Jika perlu build/dev, minta approval tombol tool untuk setiap perintah. Verifikasi halaman baru dan tampilan browser sesudahnya.

  Bukti awal / lokasi: Build historis 796 modul; bukan bukti aset device target terkini.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## 2. Fondasi tersedia — pertahankan dan verifikasi

- [x] **S01 — Auth, verifikasi dan profil** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 01 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Login/redirect benar, email wajib untuk akses terkait; reset password dan profil berfungsi.

  Bukti awal / lokasi: app/Http/Controllers/Auth; resources/js/Pages/Auth; DashboardDestination.php.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S02 — Workspace dan batas tenant** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 01/04 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Pembuatan/pemilihan workspace milik pengguna; akses workspace lain ditolak; keanggotaan owner tersedia.

  Bukti awal / lokasi: app/Modules/Core/Tenancy; tests/Feature/Core/PlatformTest.php.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S03 — Dashboard Core dan katalog** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 02 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Admin /admin dan pelanggan /dashboard terpisah; editor pengguna/produk terlindungi; katalog empat produk konsisten.

  Bukti awal / lokasi: resources/js/Pages/Admin; app/Modules/Core/Catalog; UserManagementTest.php; ProductManagementTest.php.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S04 — Perlindungan akun** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 01/06 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Owner workspace dan superadmin terakhir tidak dapat dihapus; perubahan role sendiri dibatasi; kasus penolakan tidak merusak data.

  Bukti awal / lokasi: Identity/Actions/DeleteAccount.php; UpdatePlatformUser.php; AccountDeletionSafetyTest.php.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S05 — Draft paket** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 03 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Nama/deskripsi dan preset Wedding dapat diedit; snapshot aturan dari server, input harga langsung ditolak. Draft tidak aktif otomatis; checkout development memakai preset terpisah.

  Bukti awal / lokasi: core_plans; Billing/Http/Requests/SavePlanRequest.php; DraftPlanTest.php.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S06 — Entitlement dasar** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 04 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Membership, product_id, status active/trial dan waktu berlaku diperiksa; superadmin tidak otomatis bypass.

  Bukti awal / lokasi: Entitlement/Services/DatabaseProductAccess.php; EnsureProductAccess.php; EntitlementProductRelationTest.php.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S07 — Lonceng database** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 05 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Daftar/badge/read/read-all per akun; pagination, polling dan error handling; tidak bocor lintas akun; aktivitas workspace/paket menghasilkan notifikasi.

  Bukti awal / lokasi: NotificationTest.php; docs/NOTIFICATIONS.md; resources/js/Components/NotificationBell.vue.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **S08 — Workbook perencanaan paket** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: CORE 03 / D02 | Bergantung pada: V01–V03

  Penerimaan / tindakan: Workbook empat produk tersedia; isian Wedding pada docs/planning/Paket Product Moshia.xlsx. Produk lain TBC; belum ada import otomatis.

  Bukti awal / lokasi: exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx; scripts/export-draft-plans.ps1.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## 3. Keputusan sebelum implementasi terkait

- [ ] **D01 — Aturan pemilik workspace (kolaborasi ditunda)** · TUNGGU KEPUTUSAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: TUNGGU KEPUTUSAN | PRD: D01 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Tetapkan batas dan izin pemilik workspace untuk alur awal. Undangan anggota, berbagi akses dan transfer owner ditunda; jangan menjadi blocker pembayaran/Wedding.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D02 — Paket dan aturan komersial** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: D02 | Bergantung pada: S08

  Penerimaan / tindakan: Owner mengisi workbook: harga/mata uang/model bayar/durasi/trial/kuota/unit/reset/pajak; sepakati expiry, grace/refund dan nasib undangan terbit. Kosong bukan nol.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D03 — Provider sesuai fase** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: D03 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Tetapkan payment, storage dan DNS serta kanal notifikasi yang dibutuhkan Wedding; kontrak adapter dan kegagalan. Pemilihan AI/printer tidak menghambat Wedding.

  Bukti awal / lokasi: Midtrans dipilih sebagai rencana gateway; development manual. Keputusan storage/DNS dan konfigurasi gateway masih terbuka. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D04 — Scope Wedding MVP** · TUNGGU KEPUTUSAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: TUNGGU KEPUTUSAN | PRD: D04 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Tetapkan field konten, template awal, aturan preview/publish/unpublish; putuskan apakah guest/RSVP/wishes wajib rilis pertama.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D05 — Aturan Jastip** · TUNGGU KEPUTUSAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: TUNGGU KEPUTUSAN | PRD: D05 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Sepakati formula kurs, fee, pembulatan dan transisi Find/Paid/Sent sebelum fase Jastip.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D06 — Perangkat Photo Booth** · TUNGGU KEPUTUSAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: TUNGGU KEPUTUSAN | PRD: D06 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Tetapkan printer/OS pilot, pairing, acknowledgment, retry dan reprint; consent/retensi/biaya AI sebelum fitur AI.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D07 — Aturan Restaurant** · TUNGGU KEPUTUSAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: TUNGGU KEPUTUSAN | PRD: D07 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Tetapkan tax, service, diskon, void/refund, split bill dan perangkat; pisahkan payment pelanggan dari subscription Moshia.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **D08 — Target operasi** · TUNGGU KEPUTUSAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: TUNGGU KEPUTUSAN | PRD: D08 | Bergantung pada: Sesuai fase

  Penerimaan / tindakan: Tetapkan PIC/jadwal, staging, target beban, retensi/consent, RPO/RTO, backup, monitoring serta budget server. Baseline kapasitas PRD bukan jaminan.

  Bukti awal / lokasi: Keputusan tertulis product owner; kolom PIC wajib diisi, jangan menetapkan harga/provider secara otomatis.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## 4. Sisa Core — kerjakan sesuai dependency

- [ ] **C01 — Aturan paket dan editor komersial** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: CORE 03 | Bergantung pada: D02; S05

  Penerimaan / tindakan: Migrasi data aman; validasi harga tanpa floating point, durasi/kuota/status sesuai keputusan; draft lama tidak aktif otomatis; uji input invalid dan hak admin.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C02 — Lifecycle subscription** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: CORE 03/04 | Bergantung pada: C01; D02

  Penerimaan / tindakan: Subscription per workspace dan snapshot paket; aktivasi/perpanjangan/expiry/cancel konsisten dengan entitlement; tetapkan dan uji transisi invalid serta waktu batas.

  Bukti awal / lokasi: WeddingPurchase + ManualWeddingBilling; periode tersimpan, expiry dan reaktivasi sejak Publish; tests/Feature/Core/ManualWeddingBillingTest.php. Refund/operasi dan concurrency MySQL belum selesai. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C03 — Checkout, payment dan invoice** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: CORE 03 | Bergantung pada: C01–C02; D03

  Penerimaan / tindakan: Adapter gateway, transaksi dan invoice sesuai kebijakan; nilai dihitung server; status pending/failed tidak memberikan akses; tampilkan billing pelanggan.

  Bukti awal / lokasi: Order/checkout manual development BCA 12345678 MOSHIA CORPORATE; Payment accepted oleh superadmin. Midtrans/invoice belum diimplementasikan. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C04 — Webhook terverifikasi dan idempoten** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: CORE 03 | Bergantung pada: C03

  Penerimaan / tindakan: Uji valid/invalid, duplikat, event terlambat/out-of-order, concurrency dan timeout pada database target; satu payment tidak menggandakan akses/invoice.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C05 — Trial provisioning dan quota atomik** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: CORE 04 | Bergantung pada: D02; C01–C02

  Penerimaan / tindakan: Definisi unit/periode/reset; uji limit boundary dan request bersamaan, rollback kegagalan, tenant scope serta job; bedakan trial lifecycle dari pemeriksaan status trial yang sudah ada.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C06 — Media bersama** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: CORE 05 | Bergantung pada: D03; D08; aturan quota

  Penerimaan / tindakan: Validasi MIME/ukuran, kepemilikan tenant, upload storage, signed URL privat dan penghapusan/retensi; uji file invalid, gagal upload dan akses silang.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C07 — Notifikasi provider dan queue** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: CORE 05 | Bergantung pada: D03; D08

  Penerimaan / tindakan: Pertahankan lonceng yang ada; tambahkan email/WA hanya sesuai scope; retry terbatas, deduplication, timeout dan visibility failed job; uji pengiriman nyata di staging.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C08 — Audit, settings dan domain mapping** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: CORE 06 | Bergantung pada: D01; D03; D08

  Penerimaan / tindakan: Audit actor/tenant/object/action/time untuk billing, izin dan publish; settings tervalidasi, domain unik dan terverifikasi sesuai scope; tanpa secret pada log.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C09 — Kolaborasi workspace** · DITUNDA

  <details>
  <summary>Detail dan bukti</summary>

  Status: DITUNDA | PRD: CORE 01 | Dilanjutkan hanya saat diprioritaskan kembali.

  Penerimaan / tindakan: Tidak dikerjakan sekarang sesuai arahan pengguna. Undangan anggota, peran anggota dan transfer owner ditinjau kembali bila menjadi prioritas.

  Bukti awal / lokasi: Tambahkan tautan implementasi, migration dan hasil tes saat dikerjakan.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **C10 — Kontrak dashboard produk** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: CORE 02/04 | Bergantung pada: S02; S06; D01

  Penerimaan / tindakan: Route/layout produk dalam aplikasi yang sama; sesi login bersama; setiap aksi memeriksa workspace + entitlement + policy; uji pergantian workspace dan tab berbeda. Tidak perlu SSO server/API internal HTTP.

  Bukti awal / lokasi: Route Wedding memakai sesi bersama, owner/workspace dan entitlement; tes lintas tenant tersedia. Kontrak produk lain belum diimplementasikan. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## 5. Wedding MVP

- [ ] **W01 — Dashboard dan template** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: WED 01 | Bergantung pada: C10; D04

  Penerimaan / tindakan: Dashboard Wedding hanya untuk workspace berhak; template aktif menghasilkan draft milik tenant; template nonaktif ditolak.

  Bukti awal / lokasi: Editor/dashboard Wedding dasar dengan satu tampilan; pemilihan template belum tersedia. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **W02 — Editor konten dan media** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: WED 02 | Bergantung pada: W01; C06

  Penerimaan / tindakan: Data pasangan/event/lokasi/tema/media sesuai field final; validasi server dan sanitasi; simpan serta tampilkan kembali tanpa script pengguna.

  Bukti awal / lokasi: Nama pasangan, tanggal, lokasi dan pesan tersedia; validasi server serta escaping. Upload media dan scope konten lengkap belum. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [x] **W03 — Preview privat** · TERSEDIA

  <details>
  <summary>Detail dan bukti</summary>

  Status: TERSEDIA | PRD: WED 03 | Bergantung pada: W02

  Penerimaan / tindakan: Preview hanya untuk pihak berwenang atau mekanisme terbatas yang disepakati; draft tidak bocor; data tersimpan tampil benar.

  Bukti awal / lokasi: Preview privat owner terverifikasi, konten draft terpisah dari publik; pengujian penolakan lintas tenant lulus. UAT browser masih terbuka. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **W04 — Publish subdomain HTTPS** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: WED 03 | Bergantung pada: W03; C05; C08; D03

  Penerimaan / tindakan: Subdomain unik, entitlement/kuota dicek; publish gagal tidak dicatat sukses; alamat publik menampilkan versi yang benar.

  Bukti awal / lokasi: Belum ada modul bisnis Wedding pada source yang diperiksa.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **W05 — Lifecycle dan kegagalan publish** · SEBAGIAN

  <details>
  <summary>Detail dan bukti</summary>

  Status: SEBAGIAN | PRD: WED 03 | Bergantung pada: W04; D02

  Penerimaan / tindakan: Uji publish ulang/unpublish, subdomain bentrok, upload gagal, expiry dan akses lintas tenant; konsistensi quota saat gagal/concurrent.

  Bukti awal / lokasi: Publish ulang mempertahankan periode, expiry menutup publik, pembelian ulang memakai undangan lama. Unpublish, DNS, quota dan concurrency belum diuji. Detail: WEDDING_LIFECYCLE.md.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **W06 — Guest, RSVP dan wishes** · OPSIONAL

  <details>
  <summary>Detail dan bukti</summary>

  Status: OPSIONAL | PRD: WED 04 | Bergantung pada: D04; W04

  Penerimaan / tindakan: Hanya jika masuk scope: respons tervalidasi/rate-limited, daftar tamu tetap privat, owner melihat respons undangannya.

  Bukti awal / lokasi: Belum ada modul bisnis Wedding pada source yang diperiksa.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **W07 — UAT Wedding end-to-end** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: WED 01–03; CORE 01–06 | Bergantung pada: C01–C08; W01–W05; R01–R06

  Penerimaan / tindakan: Daftar → bayar → entitlement → konten → preview → publish pada staging; uji gagal/akses ilegal; catat persetujuan owner sebelum rilis.

  Bukti awal / lokasi: Belum ada modul bisnis Wedding pada source yang diperiksa.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **W08 — Builder lanjutan dan custom domain** · OPSIONAL

  <details>
  <summary>Detail dan bukti</summary>

  Status: OPSIONAL | PRD: WED 05 | Bergantung pada: MVP stabil; keputusan scope

  Penerimaan / tindakan: Builder lanjutan/otomasi domain opsional P2. Domain .com manual paket Diamond tetap harus dipenuhi pada C08/W04 sebelum penawaran komersial; bukan manfaat opsional.

  Bukti awal / lokasi: Belum ada modul bisnis Wedding pada source yang diperiksa.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## 6. Produk berikutnya

- [ ] **J01 — Trip, customer dan order** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: JAS 01 | Bergantung pada: Core siap; Wedding gate

  Penerimaan / tindakan: Domain Jastip bertenant, relasi trip/customer benar dan akses silang ditolak.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **J02 — Kurs dan riwayat status** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: JAS 02 | Bergantung pada: J01; D05

  Penerimaan / tindakan: Snapshot formula/kurs dapat direproduksi; transisi status valid dan riwayat tersedia.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **J03 — WhatsApp dan UAT Jastip** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: JAS 03 | Bergantung pada: J02; C07

  Penerimaan / tindakan: Event tidak terkirim ganda ketika retry; alur sampai Sent lulus dan kegagalan provider tercatat.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **P01 — Event, sesi dan capture** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: PHO 01 | Bergantung pada: Fase Jastip; D06

  Penerimaan / tindakan: Capture link scoped, kedaluwarsa/cabut bekerja; hasil milik sesi/tenant benar.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **P02 — Frame dan render** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: PHO 02 | Bergantung pada: P01

  Penerimaan / tindakan: Queue render dapat dilacak, output sesuai template, retry gagal aman.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **P03 — Print agent dan UAT** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: PHO 03 | Bergantung pada: P02; D06

  Penerimaan / tindakan: HTTPS dan autentikasi perangkat; job booth scoped; acknowledgment; uji offline/reconnect/reprint tanpa cetak ulang otomatis; printer pilot lulus.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **P04 — AI Photo** · OPSIONAL

  <details>
  <summary>Detail dan bukti</summary>

  Status: OPSIONAL | PRD: PHO P2 | Bergantung pada: Capture-print stabil; D03/D06/D08

  Penerimaan / tindakan: Opsional setelah consent, credit/biaya, quota dan retensi disetujui; timeout provider tidak merusak capture biasa.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **T01 — Restaurant 5A–5B** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: RES 01–02 | Bergantung pada: Core siap; urutan roadmap; D07

  Penerimaan / tindakan: Cabang/meja/menu/order/bill lalu kitchen/status; total server dan isolasi cabang/tenant diuji.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **T02 — Restaurant 5C–5D** · BELUM

  <details>
  <summary>Detail dan bukti</summary>

  Status: BELUM | PRD: RES 03–04 | Bergantung pada: T01; D07

  Penerimaan / tindakan: POS/payment/receipt lalu antrean/WA; transaksi duplikat tidak menutup bill dua kali; nomor antrean konsisten.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **T03 — Restaurant 5E** · OPSIONAL

  <details>
  <summary>Detail dan bukti</summary>

  Status: OPSIONAL | PRD: RES 05 | Bergantung pada: T02; keputusan scope

  Penerimaan / tindakan: Laporan/inventory opsional; definisi stok/penyesuaian dan laporan disepakati sebelum dibangun.

  Bukti awal / lokasi: Requirement target PRD; belum ada implementasi operasional.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## 7. Gate operasi — wajib setiap rilis yang relevan

- [ ] **R01 — Keamanan dan isolasi** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: Nonfungsional / D08 / Gate rilis | Bergantung pada: D08; fitur pada rilis terkait

  Penerimaan / tindakan: Policy, tenant scope, abuse/rate limit, file invalid, session/CSRF, quota dan job context diuji; seluruh defect kritis ditutup.

  Bukti awal / lokasi: Belum diverifikasi pada audit dokumen ini; wajib bukti staging/operasi.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **R02 — Staging, deploy dan rollback** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: Nonfungsional / D08 / Gate rilis | Bergantung pada: D08; fitur pada rilis terkait

  Penerimaan / tindakan: Konfigurasi/database/storage staging terisolasi; worker/scheduler pulih setelah restart; migrasi dan prosedur rollback diuji.

  Bukti awal / lokasi: Belum diverifikasi pada audit dokumen ini; wajib bukti staging/operasi.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **R03 — Backup dan restore** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: Nonfungsional / D08 / Gate rilis | Bergantung pada: D08; fitur pada rilis terkait

  Penerimaan / tindakan: Backup luar server; restore drill nyata; catat RPO/RTO aktual terhadap D08, bukan sekadar keberadaan backup.

  Bukti awal / lokasi: Belum diverifikasi pada audit dokumen ini; wajib bukti staging/operasi.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **R04 — Kinerja dan kapasitas** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: Nonfungsional / D08 / Gate rilis | Bergantung pada: D08; fitur pada rilis terkait

  Penerimaan / tindakan: Beban, spesifikasi dan hasil p95 dicatat; web tetap stabil saat job aktif; ambang scale dari metrik. Capture-print wajib diuji bersama trafik web sebelum rilis Photo Booth.

  Bukti awal / lokasi: Belum diverifikasi pada audit dokumen ini; wajib bukti staging/operasi.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **R05 — Monitoring dan recovery** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: Nonfungsional / D08 / Gate rilis | Bergantung pada: D08; fitur pada rilis terkait

  Penerimaan / tindakan: Error, backlog, failed jobs, kapasitas disk/database dan alert dapat ditelusuri; runbook incident tersedia; retry/idempotency diuji.

  Bukti awal / lokasi: Belum diverifikasi pada audit dokumen ini; wajib bukti staging/operasi.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

- [ ] **R06 — Persetujuan rilis dan handoff** · PERLU VERIFIKASI

  <details>
  <summary>Detail dan bukti</summary>

  Status: PERLU VERIFIKASI | PRD: Nonfungsional / D08 / Gate rilis | Bergantung pada: D08; fitur pada rilis terkait

  Penerimaan / tindakan: Kriteria PRD terkait lulus; PIC/tanggal/bukti UAT tersedia; secret tidak masuk dokumen; sumber dan workbook terbaru tersedia di device tujuan. Tidak otomatis deploy setelah checklist selesai.

  Bukti awal / lokasi: Belum diverifikasi pada audit dokumen ini; wajib bukti staging/operasi.

  PIC: __________ · Tanggal: __________ · Bukti/hasil: __________

  </details>

## Referensi

- [PRD lengkap](Moshia_PRD_v1.2_2026-10-05.docx)
- [Checklist Word v1.2](MOSHIACHEKLIST.docx) — diselaraskan dengan checklist Markdown pada 5 Oktober 2026.
- [Status implementasi / handoff](../../MOSHIAUPDATE-05102026.md)
- [Pembayaran manual dan lifecycle Wedding](WEDDING_LIFECYCLE.md)
- [Excel paket untuk diisi](../../exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx)

**Aturan kerja:** setiap npm run build / npm run dev tetap memerlukan approval tombol tool. Jangan reset database, mengaktifkan harga, mengimpor workbook atau deploy hanya karena tercantum di checklist.
