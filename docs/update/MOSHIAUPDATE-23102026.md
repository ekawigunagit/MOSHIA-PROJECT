# Update Moshia — 23 Oktober 2026

## Penyempurnaan tampilan dashboard
- Permintaan: menyesuaikan tombol, ukuran font dan ergonomi mengikuti referensi dashboard, mempertahankan merah Moshia.
- Ditambahkan resources/css/dashboard-polish.css, diimpor setelah interactions.css. Scope .moshia-console untuk layout admin/user.
- Tombol utama gradasi merah (#e52238 → #b9082a), sudut 10 px, font 14 px, min-height 44 px, bayangan ringan. Tombol sekunder putih/surface, fokus keyboard jelas, disabled tanpa gerakan.
- Kartu radius 14 px; label/input/tabel lebih konsisten; sidebar aktif aksen merah; spacing aksi wrap. Input mobile 16 px dan kontrol susunan minimum 44 px; reduced motion didukung.
- Tidak mengubah proses bisnis, role, database, harga, isi menu, atau template publish Wedding.
## Pemeriksaan sesi ini
- Build Vite berhasil: 821 modul.
- Fixture editor desktop ditinjau melalui screenshot; CSS computed mengonfirmasi font tombol 14 px, radius 10 px, gradasi pada light/dark. Mobile 390 px tanpa overflow horizontal, input 16 px, tanpa exception runtime.
- Fixture bukan UAT seluruh halaman admin: review akun admin/pengguna serta tabel berisi data nyata masih perlu dilakukan. Backend tests tidak diulang untuk perubahan CSS ini.
- Belum deploy. Folder aktual D:/XAMPP8212/htdocs/MOSHIA-PROJECT. Riwayat sebelumnya di handoff 15 Oktober.
