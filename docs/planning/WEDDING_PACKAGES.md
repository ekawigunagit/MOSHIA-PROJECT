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
- Catatan di atas adalah hasil tahap draft awal; pembaruan berikut menggantikan status lifecycle/provider/reaktivasi tersebut.

## Lanjutan 5 Oktober 2026 — pembayaran manual development

Pengguna memilih Midtrans sebagai rencana gateway. Selama development gunakan rekening dummy BCA 12345678 atas nama MOSHIA CORPORATE dan tombol **Payment accepted** oleh superadmin. Masa aktif baru dimulai saat owner menekan Publish, termasuk pembelian kembali setelah expired.

Alur manual sekarang tersedia: order bersnapshot dari preset server → penerimaan admin → entitlement editor → simpan/preview privat → Publish → expiry → order reaktivasi pada undangan yang sama. Draft admin tetap draft; simulasi memakai preset development terpisah dan hanya terbuka pada local/testing. Tidak ada aktivasi layanan komersial produksi.

Lihat [WEDDING_LIFECYCLE.md](WEDDING_LIFECYCLE.md) untuk petunjuk, batas fitur, schema, aturan akses dan pekerjaan berikutnya. Editor teks dasar/preview/URL publik lokal tersedia; media/template lengkap/video/domain, Midtrans dan invoice belum tersedia.

Trial/kuota/pajak/refund/grace dan biaya domain masih terbuka. Waktu mulai reaktivasi sudah diputuskan: Publish setelah pembayaran diterima. Riwayat periode lama dipertahankan, slug dan konten undangan tidak diganti.
