# C02/C03 — Pembayaran manual dan lifecycle Wedding

Diperbarui 5 Oktober 2026. **Alur development tersedia. C02/C03 masih SEBAGIAN terhadap checklist lengkap.**

## Keputusan pengguna

- Gateway rencana berikutnya: **Midtrans**. Belum diintegrasikan.
- Development memakai transfer manual **BCA 12345678, MOSHIA CORPORATE**. Ini rekening dummy, bukan tujuan transfer nyata.
- Superadmin menerima pembayaran dengan tombol **Payment accepted** di dashboard admin.
- Masa aktif dimulai saat pengguna menekan **Publish** setelah pembayaran diterima: Gold 6 bulan, Emerald/Diamond 12 bulan.
- Pembelian kembali memakai undangan yang sama; periode baru juga dimulai saat Publish setelah pembayaran baru diterima.

## Cara mencoba

1. Login dengan akun terverifikasi dan buat/pilih workspace sendiri.
2. Dashboard Workspace → card **Wedding Invitation → Lihat paket** → pilih card Gold/Emerald/Diamond → **Make payment** di bawah pilihan paket. Memilih card belum membuat pesanan; detail rekening muncul setelah Make payment. Harga diambil server dari `config/wedding_plans.php`; request harga/paid/status dari browser ditolak. Alur tampilan diperbarui 6 Oktober 2026.
3. Login superadmin → **Pembayaran Manual** → pesanan terkait → **Payment accepted** → konfirmasi. Owner menerima notifikasi lonceng, editor terbuka, tetapi tanggal awal/akhir undangan masih kosong.
4. Owner → halaman pembayaran → **Buka editor Wedding**. Isi nama pasangan, tanggal, lokasi, dan pesan → **Simpan draft** → **Preview privat**.
5. Klik **Publish**. Tautan `/invitation/{slug}` dapat dibuka tanpa login; periode paket mulai sekarang. Publish ulang mempertahankan tanggal akhir.
6. Saat expired, tautan publik ditutup dan data disimpan. Pilih paket lagi, terima pembayaran sebagai admin, lalu Publish untuk periode baru. Slug dan isi undangan tetap sama.

Semua route simulasi termasuk undangan publik dibatasi `APP_ENV=local/testing` dan `MANUAL_DEVELOPMENT_PAYMENTS=true` (default true). Production/staging tetap tertutup, sekalipun flag true. Ganti rekening/provider dan tinjau kebijakan sebelum membuka layanan komersial. Jangan mengubah APP_ENV produksi agar dapat memakai rekening dummy.

## Transisi dan pencatatan

Pembaruan 6 Oktober 2026: batas pembayaran **24 jam sejak pesanan dibuat**. Countdown HH:MM:SS tampil untuk pelanggan dan admin, memakai waktu server. Pada batas tepat 24 jam, order belum dibayar menjadi `payment_expired`; admin tidak dapat menerimanya dan owner harus membuat pesanan baru. Riwayat tetap disimpan. Retry pesanan yang sama tidak mengulang timer; pesanan baru mendapat 24 jam baru. Aturan ini berlaku juga bagi pesanan pending lama berdasarkan created_at asli, tanpa migration/scheduler. Pembayaran yang telah diterima tidak terpengaruh batas ini; waktu persiapan sebelum Publish tetap terpisah.

| Status | Pemicu berikutnya | Hasil |
|---|---|---|
| pending_payment | Superadmin menerima pembayaran | awaiting_publish; paid_at/accepted_by diisi; tanggal publikasi/akhir kosong |
| pending_payment | Owner membatalkan | cancelled; pembayaran terlambat ditolak untuk aktivasi otomatis |
| pending_payment | 24 jam sejak created_at tanpa Payment accepted | payment_expired; tidak dapat diterima/dibatalkan, boleh membuat order baru |
| awaiting_publish | Publish berhasil | active; awal/akhir periode disimpan |
| active | Edit/publish ulang | Periode tetap; konten publik diperbarui hanya saat Publish |
| active | now mencapai ends_at | expired; akses publik/editor ditutup tanpa menghapus konten |
| expired | Order baru dan Payment accepted | Editor terbuka; publik masih ditutup hingga Publish baru |

Pembatalan dibatasi order belum dibayar. Refund/revocation/perpanjangan di tengah masa aktif belum ditentukan. Pembayaran ulang memakai order baru, tidak menimpa riwayat pembayaran/periode lama.

- `core_purchase_orders`: ID numerik, tenant/product, snapshot paket, metode pembayaran, paid_at, accepted_by, first_published_at, ends_at, cancelled_at.
- `wedding_invitations`: ID numerik terpisah dari slug UUID string; tenant_id unik untuk satu undangan/workspace. Konten draft dan publik terpisah. `published_order_id` menunjuk periode yang benar-benar dipublikasikan.
- `WeddingPurchase` adalah model immutable untuk aturan transisi. `ManualWeddingBilling` adalah adapter transaksi untuk alur manual; belum merupakan adapter gateway Midtrans.
- Urutan lock pada mutasi: tenant → order/undangan. Unique tenant_id menjaga satu undangan/workspace. Klik order paket sama yang masih pending mengembalikan order lama; klik Payment accepted ulang tidak mengubah tanggal, entitlement, atau menggandakan notifikasi.
- Paket draft admin tetap draft dan tidak otomatis dijual. Simulasi memakai tiga preset development yang sudah disetujui pengguna, bukan aktivasi/import otomatis tabel core_plans.
- Harga adalah rupiah utuh dari snapshot server. Pajak, invoice resmi, biaya layanan atau refund belum ditetapkan; simulasi tidak menambahkan aturan komersial tersebut.

## Waktu, akses, dan keamanan

- Timestamp UTC; bulan kalender tanpa overflow. 31 Agustus + 6 bulan → 28/29 Februari, pada jam UTC sama. Konvensi ini perlu masuk UAT.
- Batas akhir eksklusif: `now == ends_at` sudah expired. Guard request memeriksa waktu langsung tanpa menunggu scheduler.
- Owner terverifikasi dan membership wajib pada setiap route workspace. Superadmin tidak otomatis boleh mengedit undangan milik pelanggan. Tenant diambil dari URL yang diperiksa, bukan semata workspace sesi; tab berbeda tidak mengubah target.
- Payment accepted mengaktifkan entitlement editor dengan starts_at=paid_at dan ends_at kosong. Publish mengisi ends_at dari periode undangan. Public guard juga memeriksa published_order_id, status publikasi dan entitlement; pembayaran reaktivasi saja tidak membuka publikasi lama.
- Publik hanya menampilkan snapshot published_content. Preview privat memakai draft_content; simpan/edit tidak bocor ke publik. HTML pengguna di-escape, panjang/input tanggal divalidasi, respons no-store dan noindex.
- Publish konten kosong gagal sebelum perubahan periode. Simpan periode, snapshot publik dan entitlement terjadi dalam satu transaksi. Tidak ada pekerjaan DNS/storage eksternal pada tahap ini.
- Penerimaan pembayaran menyimpan admin/tanggal dan notifikasi owner dalam transaksi; kegagalan notifikasi membatalkan seluruh perubahan. Audit umum lengkap C08 masih belum selesai.

## Batas tahap ini

- Editor teks dan satu tampilan undangan dasar. Belum ada pemilihan template, media upload, video header, RSVP, unpublish, atau subdomain HTTPS/custom domain.
- Pembelian domain .com tetap bagian keputusan Diamond dan akan diproses manual; pemenuhan domain/video belum dibangun.
- Midtrans, verifikasi webhook/idempotensi provider, invoice, scheduler expiry/notifikasi, kuota/trial, pajak/refund/grace belum tersedia.
- Keamanan input, tenant isolation, double-submit, expiry, reaktivasi, snapshot privat/publik dan rollback diuji otomatis di SQLite. Belum uji concurrency MySQL nyata, browser/mobile UAT, DNS atau restore dump.

## Checklist dan pengujian

- C02 SEBAGIAN: alur periode manual tersimpan dan terhubung entitlement; lifecycle komersial lain/operasi masih terbuka.
- C03 SEBAGIAN: order/checkout manual development dan penerimaan admin tersedia; Midtrans/invoice belum.
- D03 SEBAGIAN: Midtrans dipilih; keputusan storage/DNS masih terbuka.
- C10, W01, W02, W05 SEBAGIAN. W03 preview privat TERSEDIA. W04 subdomain HTTPS belum; URL publik lokal hanya dasar pengujian.
- Suite: **168 tes / 1.311 assertions**, SQLite in-memory. Migration `000009` berhasil pada MySQL lokal. Hasil frontend dicatat pada handoff/checklist setelah build selesai.

## Urutan berikutnya

1. UAT manual: dua akun, workspace, order → Payment accepted → draft → preview → Publish → reaktivasi. Catat tampilan mobile/tema.
2. Sepakati scope Wedding berikutnya D04 dan storage/kuota; lanjut template/media W01/W02 dan C06.
3. Persiapkan integrasi Midtrans sandbox C03/C04 setelah akun/kredensial tersedia; pertahankan harga server, snapshot, transaksi dan verifikasi sebelum entitlement.
4. Domain/DNS W04 dan pemenuhan Diamond, lalu kesiapan operasi sebelum rilis. Jangan menganggap build/tes sebagai persetujuan deployment.
