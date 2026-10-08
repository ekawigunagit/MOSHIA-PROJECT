# Aturan kerja Moshia

## Dokumentasi wajib setiap perubahan

Instruksi pengguna, 8 Oktober 2026:

- Setiap perubahan atau penambahan project harus disertai pembaruan `MOSHIAUPDATE-DDMMYYYY.md` di root dan `docs/planning/CHECKLIST.md` sebelum pekerjaan dinyatakan selesai.
- Gunakan tanggal hari kerja saat ini berdasarkan konteks tanggal pengguna dan zona waktu Asia/Bangkok. Contoh 8 Oktober 2026: `MOSHIAUPDATE-08102026.md`.
- Bila handoff hari tersebut belum ada, buat file baru. Bila sudah ada, tambahkan/perbarui progres hari yang sama; pertahankan riwayat yang relevan. Jangan terus mencatat progres baru ke handoff bertanggal lama.
- Catat perubahan, keputusan pengguna, hasil pemeriksaan yang benar-benar dilakukan, batas/pekerjaan tersisa, dan langkah berikutnya. Bedakan hasil pengujian sesi ini dari hasil historis.
- Selaraskan status dan bukti checklist dengan kode aktual. Jangan mencentang item yang baru sebagian selesai. Perubahan kecil/dokumentasi tetap dicatat, tanpa harus menambah item checklist baru.
- Saat melanjutkan sesi, baca handoff dengan tanggal terbaru (tanggal DDMMYYYY, bukan urutan alfabet nama file) dan CHECKLIST.md terlebih dahulu.

## Preferensi yang tetap berlaku

- Jelaskan pekerjaan dalam Bahasa Indonesia.
- Setiap `npm run build` atau `npm run dev` memerlukan persetujuan melalui tool, tanpa persetujuan prefix permanen.
- Jangan reset database, mengubah role akun, mengarang aturan komersial, atau deploy tanpa instruksi.
- Jangan mencetak rahasia `.env` atau data isi dump. Ekspor database lokal disimpan di folder yang diabaikan Git.
