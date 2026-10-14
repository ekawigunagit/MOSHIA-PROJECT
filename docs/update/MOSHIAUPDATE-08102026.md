# MOSHIA — Handoff 8 Oktober 2026

Dokumen lanjutan hari ini. Baca bersama `docs/planning/CHECKLIST.md`, `WEDDING_STUDIO.md` dan `UAT_WEDDING.md`. Riwayat sebelumnya berada di `MOSHIAUPDATE-05102026.md`; progres selanjutnya mengikuti tanggal hari pekerjaan.

## Aturan dokumentasi pengguna

Setiap perubahan/penambahan wajib memperbarui handoff `MOSHIAUPDATE-DDMMYYYY.md` sesuai tanggal hari itu (Asia/Bangkok) dan `docs/planning/CHECKLIST.md`. Untuk hari yang sama gunakan file yang sama. Aturan persisten disimpan di `AGENTS.md`. Hasil pengujian harus sesuai pemeriksaan yang benar-benar dilakukan; perubahan dokumentasi tidak memerlukan build/test aplikasi baru.

## Posisi project

- Laravel/PHP, Vue/Inertia, modular monolith Core → Wedding → produk lain. Project: `D:\XAMPP8212\htdocs\MOSHIA-PROJECT`.
- Fondasi akun/verifikasi email, workspace owner, admin/role, katalog, draft paket, entitlement, notifikasi database tersedia.
- Wedding Studio enam langkah, tiga tema, foto/MP3 privat Core Media, preview, publish, RSVP dan moderasi wishes sudah tersedia. Data bisnis memakai awalan modul; media bersama memakai `core_media`.
- Layout/sidebar produk dan menu akun sudah tersedia; billing profil memuat riwayat pribadi. Produk lain masih Coming Soon. Account Activity dan Notification Settings masih penjelasan, belum fitur audit/preferensi nyata.
- Database perangkat ini terakhir diperiksa: migration sampai `2026_10_08_000011_move_wedding_media_to_core` Ran. Tidak ada migration baru untuk Unpublish atau perubahan keranjang.
- Checklist 54 item: 11 tersedia (S01–S08, W01–W03). W05/C06/W06 dan billing komersial tetap sebagian; jangan menyatakan siap produksi.

## Aturan paket dan pembayaran

- Gold Rp149.000/6 bulan, Emerald Rp249.000/12 bulan, Diamond Rp559.000/12 bulan. Sekali bayar untuk satu undangan/workspace.
- Development: transfer dummy BCA `12345678`, pemilik `MOSHIA CORPORATE`; admin menekan Payment accepted. Midtrans direncanakan, belum diintegrasikan.
- Order belum diterima kedaluwarsa tepat 24 jam dari created_at. Countdown memakai waktu server; penerimaan pesanan expired ditolak. Retry tidak mengulang timer; boleh membuat order baru setelah expired.
- Pembayaran diterima membuka editor. Masa aktif baru dimulai saat Publish. Edit/publish ulang tidak memperpanjangnya.
- Setelah masa aktif habis, konten disimpan dan akses publik ditutup. Pembelian kembali memakai order/periode baru pada undangan yang sama; publik dibuka setelah Publish lagi.
- Simulasi hanya local/testing dengan flag pembayaran development. Domain/video sesuai paket belum seluruhnya dipenuhi; kuota komersial, pajak/refund/grace masih terbuka.

## Perubahan sesi ini

### Kelola workspace: edit nama

- Tombol Kelola profil pada dashboard diganti Kelola workspace, mengarah ke ID workspace yang sedang dipilih. Hanya pemilik melihat tombol dan dapat mengakses endpoint edit/update; membership saja tidak memberi hak mengganti nama.
- Halaman `Workspaces/Edit.vue` memuat nama saat ini, Simpan perubahan, validasi dan tombol kembali ke workspace terkait. Nama wajib diisi maksimal 100 karakter; backend hanya memperbarui kolom name. Nama terbaru tampil pada sidebar, breadcrumb dan dashboard.
- Route GET `/workspaces/{tenant}/edit` serta PATCH `/workspaces/{tenant}` memakai auth/verified, update dibatasi throttle. Tidak menambah penghapusan workspace atau mengubah paket/owner.
- Verifikasi sesi ini: build disetujui berhasil **814 modul**; WorkspaceManagementTest + PlatformTest **9 tes / 117 assertions** lulus dengan SQLite memory. Tes mencakup rename target, penolakan nama tidak valid, owner tidak dapat diubah dari input, serta penolakan non-owner termasuk member. Suite penuh tidak diulang; UAT browser masih pending.
- Berikutnya: periksa alur pilih workspace > Kelola workspace > simpan > kembali dan nama baru di sidebar pada browser.

### Accordion workspace dan halaman Tambah Workspace

- Dashboard Workspace/Workspace Saya di sidebar menjadi accordion berisi Tambah Workspace dan nama workspace milik/keanggotaan akun. Daftar dibagikan melalui props Inertia agar tersedia juga di halaman profil/produk/admin.
- GET `/workspaces/create` (auth + verified) menampilkan form nama workspace tersendiri. POST pembuatan lama tetap menetapkan owner dari akun login, memilih workspace baru dan mengarahkan ke dashboard.
- Memilih nama memakai POST pemilihan workspace yang memeriksa membership, kemudian membuka dashboard Add product untuk workspace aktif. Form/dropdown lama di dashboard dihapus. Wedding Add product membuka paket; Kelola undangan tersedia saat memiliki akses dan pembayaran development aktif. Produk lain tetap sesuai roadmap, tidak diaktifkan otomatis.
- Breadcrumb ditambah nama workspace/Tambah Workspace; label produk diperbaiki menggunakan prop title. Accordion memiliki aria-expanded/aria-controls, indikator aktif dan dukungan mobile melalui drawer yang ada.
- Verifikasi sesi ini: build disetujui berhasil **813 modul**; **29 tes / 287 assertions** (DashboardDestinationTest + PlatformTest) lulus memakai SQLite memory. Percobaan tes awal gagal karena halaman baru belum tercatat di manifest; sesudah build tes lulus. Tidak reset database lokal. Pemeriksaan visual browser belum dilakukan, suite penuh tidak diulang.
- Berikutnya: UAT accordion, tambah/pilih beberapa workspace, lalu Add product sampai checkout; aturan satu undangan per workspace dan aktivasi paket tetap berlaku.

### Breadcrumb seluruh menu sidebar dan profil

- Komponen bersama `ConsoleBreadcrumb.vue` dipasang di `MoshiaLayout.vue`: Dashboard Workspace, empat produk, keranjang/pesanan, Wedding Studio, semua halaman profil termasuk tiga submenu Billing, serta dashboard/menu admin dan halaman edit/buat paket.
- Breadcrumb dimulai ikon Beranda, parent dapat diklik, halaman aktif memakai aria-current. Label mengikuti halaman dan query submenu saat navigasi Inertia. Dark mode, pembungkusan pada layar kecil, fokus keyboard dan hover merah gradasi tersedia. Breadcrumb lokal Billing dihapus agar tidak ganda.
- Dark mode dan Log out tetap aksi, bukan halaman sehingga tidak memiliki breadcrumb tersendiri.
- Pemeriksaan sesi ini: build setelah persetujuan berhasil **812 modul**, `git diff --check` lulus (hanya peringatan normalisasi CRLF pada file Billing yang sudah berubah). Backend tidak diuji ulang untuk perubahan tampilan ini; belum UAT browser.
- Berikutnya: verifikasi visual/perpindahan breadcrumb dari sidebar, profil dan halaman edit admin pada desktop/mobile. Tidak mengubah status penyelesaian fitur komersial.

### Navigasi Billing disederhanakan

- Pada halaman Billing, baris Account information/Billing/Security/Account activity/Notification settings dihilangkan. Hanya tiga submenu Subscription, Payment History dan Payment Method yang tampil.
- Ditambahkan breadcrumb ikon Beranda > Billing > submenu aktif dan judul halaman mengikuti submenu. Pengaturan akun di halaman lain tetap tersedia.
- File: `resources/js/Pages/Profile/Edit.vue`. Build setelah persetujuan berhasil **811 modul**. Suite backend tidak diulang karena perubahan navigasi tampilan; UAT browser masih perlu dilakukan. Langkah berikutnya: periksa perpindahan ketiga tab dan breadcrumb di browser.

### Billing akun: Subscription, Payment History, Payment Method

Build setelah approval berhasil **811 modul**; diff whitespace lulus. Suite penuh tidak diulang, hasil sesi ini memakai regresi billing terarah.

- Billing pada menu akun sekarang memiliki tiga submenu, default Subscription; navigasi memakai query billing_tab sehingga refresh/pagination mempertahankan tampilan.
- Subscription memuat order sudah dibayar yang belum kedaluwarsa/dibatalkan dengan entitlement aktif pada workspace milik user. Termasuk paket menunggu Publish, dilabeli jelas; periode tetap dimulai pada Publish. Ada tautan Kelola undangan pada mode development.
- Payment History berupa tabel ID pesanan, Service, Workspace, Paid at, Amount dan Status; tetap memuat pesanan pending/expired/cancelled dan tidak menyebutnya sudah dibayar. Tidak membuat ID gateway/subscription palsu.
- Payment Method hanya menampilkan **Manual payment**, sesuai instruksi; tidak menambah kartu, saldo atau refund flow.
- Query dibatasi pemilik workspace, termasuk ketika pengguna adalah superadmin. Subscription dan history memiliki pagination. File: ProfileController.php, Profile/Edit.vue, ProfileBillingHistoryTest.php.
- Verifikasi backend sesi ini: **3 tes / 72 assertions** lulus (riwayat/isolation/subscription aktif, pending Publish, unpaid, expired, revoked dan akun lain). UAT browser masih pending.

### Hover merah gradasi pada tombol dan menu

Verifikasi: build setelah approval berhasil **811 modul**, pemeriksaan diff whitespace lulus.

- Ditambahkan `resources/css/interactions.css`, dimuat setelah stylesheet halaman untuk menyamakan hover tombol, CTA, pilihan paket, navigasi/sidebar, menu akun dan tab profil. Berlaku pada tema terang/gelap.
- Hover memakai gradasi merah Moshia dengan teks/ikon putih. Profil/tombol tutup memakai token gradasi yang sama. Status focus keyboard tetap; tombol disabled/aria-disabled dan backdrop drawer dikecualikan.
- Perubahan CSS saja; tidak mengubah flow, data, atau aturan pembayaran. Suite backend tidak diulang; UAT visual browser tetap pending.

### Tombol tutup dan profil lebih ringkas

Verifikasi: build setelah approval berhasil **810 modul**, diff whitespace lulus.

- Tombol × keranjang menjadi 32 px dengan ikon SVG 16 px dan sudut membulat. Tombol profil menjadi 36 px dengan ikon 18 px; bingkai merah tebal/halo permanen dihilangkan.
- Hover memakai gradasi merah, ikon putih dan bayangan halus; profil yang terbuka memakai tampilan aktif yang sama. Focus keyboard tetap jelas, reduced-motion didukung, tombol tutup disabled saat pembayaran diproses.
- Perubahan visual pada Billing/Index.vue dan console.css, tanpa perubahan backend. UAT browser belum dilakukan; tidak menjalankan ulang suite backend.

### Loading checkout berlogo Moshia

Verifikasi sesi ini: build disetujui dan berhasil, **810 modul**; pemeriksaan whitespace diff lulus.

- Saat Make payment sedang diproses, isi popup digantikan indikator kompak: lingkaran merah berputar 64 px dengan logo Moshia 28 px dan teks Memproses pesanan.
- Mengikuti form.processing asli, tanpa penundaan buatan. Setelah sukses popup tertutup; bila gagal, isi form dan pesan error tampil kembali. Penutupan popup tetap dibatasi selama submit.
- Komponen `MoshiaLoading.vue` menggunakan aset SVG Moshia yang sudah ada, status aksesibel dan prefers-reduced-motion. Tidak mengubah backend atau timer 24 jam. UAT browser belum dilakukan; suite backend tidak diulang untuk perubahan visual ini.

### Keranjang popup — perubahan terbaru

Verifikasi: build setelah approval berhasil, **808 modul**; git diff --check tanpa error whitespace. Belum UAT browser popup.

- Ringkasan keranjang Billing kini tampil sebagai dialog modal otomatis saat URL membawa paket valid. Riwayat pesanan berada di belakang popup.
- Tombol ×, Batal, Escape dan klik backdrop menutup popup dan membuka billing tanpa parameter paket; tidak membuat pesanan. Ganti paket kembali ke perbandingan.
- Native dialog menjaga fokus di popup dan membuat latar tidak interaktif; scroll halaman dikunci selama popup terbuka dan dipulihkan ketika ditutup/navigasi. Isi popup dapat di-scroll pada mobile, dengan dukungan dark mode.
- Selama Make payment diproses, aksi tutup dinonaktifkan. Sukses checkout menghilangkan pilihan paket sehingga popup ditutup dan riwayat ditampilkan. Error validasi tetap di popup.
- Perubahan frontend saja; backend harga, order dan countdown tetap. Suite backend tidak diulang untuk perubahan tampilan ini; UAT browser popup masih perlu dilakukan.

### W05 — Unpublish

- Owner terverifikasi dapat menekan Unpublish dan mengonfirmasi pada langkah Preview & publish.
- Menutup halaman undangan, media publik dan pengiriman RSVP/wishes melalui guard `PublishedInvitation`.
- Data draft/published, file, respons tamu, first_published_at dan periode order tetap disimpan. published_at menjadi kosong untuk menandai tidak sedang dipublikasikan.
- Preview privat tetap tersedia; masa aktif terus berjalan. Publish kembali memakai akhir periode yang sama. Superadmin yang bukan owner dan anggota lain ditolak. Aksi memakai transaksi/lock tenant dan undangan; pengulangan aman.
- W05 tetap sebagian: DNS/quota/concurrency dan kegagalan layanan eksternal belum terverifikasi.

### Checkout berbentuk keranjang

- Halaman perbandingan `/products/wedding` → pilih paket → `/workspaces/{tenant}/billing?package=...` menampilkan satu paket terpilih, metode pembayaran dan total.
- Billing tidak lagi mengulang tiga card paket. Ganti paket kembali ke perbandingan; Batal membuka riwayat tanpa membuat order.
- Make payment membuat order dengan harga server, lalu redirect ke billing tanpa parameter package: instruksi pembayaran, countdown dan riwayat terlihat. GET pilihan paket tidak membuat order.
- Tidak menambahkan kupon, pajak, recurring atau kartu kredit dari gambar referensi.
- File: `resources/js/Pages/Billing/Index.vue`, `ManualPaymentController.php`, serta regresi `ManualWeddingBillingTest`.

### Dokumentasi

- Checklist Word/Markdown diselaraskan dengan source saat audit W05; skenario UAT diperluas.
- Dibuat `AGENTS.md` dan handoff harian ini untuk menjalankan aturan dokumentasi terbaru pengguna.
- `docs/planning/CHECKLIST.md` tetap acuan status aktif dan diperbarui setiap perubahan, termasuk flow keranjang.

## Bukti verifikasi

- Setelah Unpublish: suite penuh **187 tes / 1.640 assertions**, SQLite in-memory; build disetujui dan berhasil, **807 modul**.
- Setelah perubahan keranjang: tes terkait **21 tes / 379 assertions** (ManualWeddingBillingTest + ProductPlansTest), build disetujui dan berhasil, **807 modul**. Suite penuh belum dijalankan ulang sesudah penambahan satu tes checkout ini.
- Dokumen Word/Markdown saat audit cocok: 54 item, 11 dicentang. Whitespace diff diperiksa.
- UAT alur nyata di browser, concurrency MySQL, restore dump dan pemenuhan domain belum selesai. Preview fixture desktop/mobile dari sesi sebelumnya bukan UAT transaksi lengkap.
- Perubahan dokumentasi/aturan terbaru tidak menjalankan build atau suite aplikasi ulang.

## Lanjutan

1. UAT `docs/planning/UAT_WEDDING.md`: perbandingan paket → keranjang → pembayaran/admin → Studio/media → preview → publish → respons/moderasi → Unpublish/Publish kembali; uji akun berbeda serta mobile/tema.
2. Tutup defect UAT, lengkapi retensi/pengelolaan media C06 serta scope video/domain. Jangan membangun ulang Studio, media atau RSVP yang sudah ada.
3. Midtrans sandbox/webhook/invoice mengikuti fase setelah konfigurasi/kredensial tersedia. Jangan mengasumsikan aturan pajak/refund/trial/kuota.
4. Setiap perubahan berikutnya: update handoff bertanggal hari itu dan CHECKLIST.md; build/dev tetap wajib approval tool setiap perintah.

## Pindah perangkat

Bawa source lengkap termasuk file baru/untracked, dokumen, lockfiles, `.env` melalui saluran privat, database terbaru dan upload privat di `storage/app`. Jangan mengubah APP_KEY ketika menggunakan data lama tanpa alasan.

Ekspor `exports/database/moshia-20261006-101641.sql` adalah snapshot 6 Oktober sebelum perubahan Studio/Media. Jangan menganggapnya memuat data terbaru 8 Oktober. Jika diperlukan, ekspor ulang dengan `php scripts/export-local-database.php`; import ke database kosong atau backup target terlebih dahulu. Jangan migrate:fresh. Tidak ada ekspor baru pada perubahan dokumentasi ini.
