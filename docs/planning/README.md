# Dokumen perencanaan Moshia — 5 Oktober 2026

## Aturan paket Wedding terbaru

Baca [WEDDING_PACKAGES.md](WEDDING_PACKAGES.md): keputusan harga/durasi/domain serta implementasi form draft. Migration 000008 dan build sudah berhasil; 132 tes/1.052 assertions lulus. Catatan dan DOCX lama mengenai paket tanpa aturan harga bersifat historis.

## Checklist untuk penggunaan sehari-hari

Buka **[CHECKLIST.md](CHECKLIST.md)** untuk 54 item dengan kotak centang dan detail yang dapat dibuka sesuai kebutuhan. Delapan item S01–S08 sudah dicentang karena tersedia pada kode/artefak project. Centang menandai pekerjaan yang sudah dikerjakan; pengujian dan kesiapan rilis tetap dicatat terpisah. Fitur berstatus TERSEDIA tidak perlu dibangun ulang.

**Fokus terbaru:** pengguna → paket → pembayaran → akses produk → dashboard Wedding → customize → publish. Berbagi akses dan undangan anggota **ditunda**. Penyesuaian D01/C09 ada di checklist Markdown; dokumen Word v1.2 masih menyimpan rencana sebelumnya.


- [PRD v1.2](Moshia_PRD_v1.2_2026-10-05.docx): panduan status aktual, keputusan, data, dependency dan gate; spesifikasi target v1.1 dipertahankan setelah bagian A–I.
- [Checklist v1.2](MOSHIACHEKLIST.docx): 54 item dengan status, PRD terkait, dependency, penerimaan, bukti, PIC dan tanggal.
- Handoff implementasi: ../../MOSHIAUPDATE-02102026.md.
- Workbook paket: ../../exports/MOSHIA_DRAFT_PAKET_2026-10-02_194603.xlsx.

Mulai dari V01–V04 (baseline device), lalu keputusan D02 dari workbook dan D01/D03/D04 sesuai scope. Lanjutkan C01 beserta dependensinya. Jangan membangun ulang S01–S08 yang sudah tersedia.

TERSEDIA berarti implementasi ditemukan, bukan UAT produksi lulus. Status dan hasil tes historis dipisahkan dari verifikasi terbaru. Billing komersial dan dashboard operasional produk belum tersedia; harga tetap draft. Rekomendasi sesi login bersama dan contract internal tidak berarti SSO/API terpisah sudah diimplementasikan.

Dokumen sumber di Downloads dipertahankan. Pembaruan hanya menyentuh dokumen: tidak menjalankan build/dev, migration, tes aplikasi atau deployment; tidak mengubah harga, database atau role.

Validasi keluaran: struktur ZIP/XML, bagian wajib dan jumlah item diperiksa oleh generator. Tampilan halaman belum diverifikasi di Microsoft Word/LibreOffice; tinjau page break dan tabel saat membuka Word.

Bawa dua DOCX, handoff, source/lockfiles dan workbook yang sudah diisi ke device berikutnya. Rahasia konfigurasi tidak boleh disimpan di dokumen/Git.
## Handoff device — 5 Oktober 2026

Baca [MOSHIAUPDATE-05102026.md](../../MOSHIAUPDATE-05102026.md). Checklist Word terbaru: [MOSHIACHEKLIST.docx](MOSHIACHEKLIST.docx). Dump dbmoshia.sql di root disimpan lokal dan tidak ikut Git; bawa secara privat. Backup checklist Word sebelum pembaruan: MOSHIACHEKLIST.before-20261005.docx. Struktur Word diperiksa; tata letak visual dan restore database belum diuji.
