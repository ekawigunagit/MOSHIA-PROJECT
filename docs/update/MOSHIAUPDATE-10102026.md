# Handoff 10 Oktober 2026

Lokasi handoff harian resmi adalah `docs/update/`, sesuai instruksi pengguna 10 Oktober. Baca bersama [CHECKLIST.md](../planning/CHECKLIST.md). Catatan sebelumnya: [8 Oktober](MOSHIAUPDATE-08102026.md).

Perubahan: loading logo global dan menu My Transaction dengan label workspace serta tautan pembayaran. Build disetujui berhasil 816 modul; pengujian billing 3 tes / 97 assertions lulus. UAT browser masih pending. Lihat dokumen lengkap dan `docs/planning/CHECKLIST.md` untuk melanjutkan.

## Ringkasan produk dibayar pada workspace

- Permintaan: produk setelah pembayaran manual diterima mudah ditemukan di workspace pembelian, dengan Manage untuk edit/simulasi sebelum Publish.
- Dashboard menampilkan bagian Produk Anda di atas Add product. Kartu Wedding berisi paket, nama workspace, nomor pesanan, tanggal pembayaran diterima, status dan masa aktif. Ringkasan memakai pembayaran terbaru yang diterima pada workspace aktif, bukan seluruh akun.
- Manage membuka Wedding Studio yang sudah menyediakan Preview. Akses hanya diberikan untuk owner, order terkini yang masih mengizinkan editor, entitlement tersedia, dan mode development aktif. Paket kedaluwarsa/dicabut tetap dapat memiliki ringkasan pembayaran tetapi tidak diberi Manage. Order pending tidak ditampilkan sebagai produk dibayar.
- Tidak mengubah data undangan, pembayaran, atau tanggal Publish; masa aktif tetap dimulai pada Publish pertama. Tampilan kosong menjelaskan bahwa kartu muncul setelah admin menerima pembayaran.
- Verifikasi sesi ini: WorkspaceProductsTest **1 tes / 62 assertions** lulus; termasuk pending vs accepted, isolasi antar-workspace/akun, akses editor tanpa memulai masa aktif, dan entitlement dicabut. Build setelah persetujuan berhasil **816 modul**; diff whitespace lulus. UAT browser belum dilakukan.
- Berikutnya: UAT pembayaran diterima > pilih workspace > Manage > isi konten > Preview > Publish. Suite penuh tidak diulang.
