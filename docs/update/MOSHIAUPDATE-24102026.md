# Update Moshia — 24 Oktober 2026

Lanjutan [23 Oktober](MOSHIAUPDATE-23102026.md), baca bersama [CHECKLIST.md](../planning/CHECKLIST.md).

## Konsistensi dashboard dan Manage product

- Pengguna melaporkan sidebar Manage berbeda dari dashboard dan meminta desain menu/font diterapkan merata.
- Pemeriksaan source: Manage Wedding sudah memakai MoshiaLayout dan stylesheet dashboard-polish global. Namun kondisi menu workspace aktif hanya mengenali Dashboard, sehingga highlight hilang di Manage.
- MoshiaLayout kini mempertahankan highlight workspace pada editor Wedding, billing workspace dan edit workspace. ID workspace dari halaman diprioritaskan atas session agar navigasi langsung ke workspace lain tidak menandai workspace yang salah.
- Dashboard polish menyamakan font keluarga, ukuran/tinggi menu sidebar/profil/tab, breadcrumb dan teks kontrol editor. Sidebar/menu utama 14 px, submenu workspace dan teks bantuan 13 px; tombol tetap minimal 44 px. Grid editor diberi min-width 0 agar isi tidak memaksa kolom melebar.
- Berlaku pada halaman dashboard admin/pengguna melalui layout bersama; preview artistik/halaman undangan publik mempertahankan tipografi template. Tidak mengubah backend, aturan pembayaran atau database.

## Pemeriksaan dan berikutnya

### Penanda aktif tanpa garis kiri

- Sesuai arahan terbaru, garis merah inset di kiri menu aktif/sidebar/submenu dan bagian susunan terpilih dihapus. Penanda memakai latar merah transparan 14% pada tema terang dan 23% pada tema gelap. Border bagian terpilih memakai warna netral; hover dan fokus keyboard tetap tersedia.
- Perubahan hanya dashboard-polish.css. Build setelah persetujuan berhasil **821 modul**; diff whitespace diperiksa. Tidak mengulang tes backend; UAT visual browser masih pending. Langkah berikutnya: review keterbacaan status aktif di light/dark.

### Penyelarasan panel Manage undangan sesuai screenshot

- Tombol Edit isi memakai komponen kelas button-primary gradasi merah Moshia; tombol urutan memakai button-outline dengan target 44 px. Daftar susunan memakai kartu radius 14 px, nomor bagian dan aksen aktif merah dengan teks 14 px.
- Checkbox editor diberi color/background checked merah serta focus ring merah secara eksplisit untuk mengganti biru bawaan plugin forms (accent-color saja tidak cukup). Bar langkah aktif mengikuti gradasi Moshia.
- Preview mendapat bingkai surface/border/radius dashboard dan judul konsisten; konten preview tetap memakai tema undangan agar berguna meninjau hasil. Kontrol dapat membungkus pada layar sempit; dark mode memakai token layout bersama.
- Build setelah persetujuan berhasil **821 modul**. Diff whitespace diperiksa. Backend tidak diubah atau dites ulang; visual browser desktop/mobile/dark mode belum diverifikasi sesi ini. Berikutnya: review daftar susunan, checkbox, Edit isi dan preview dengan akun pengguna.

- Pemeriksaan source dan diff whitespace dilakukan. Build setelah persetujuan berhasil **821 modul**. Tidak menjalankan tes backend untuk perubahan tampilan ini.
- Belum UAT visual browser sesi ini. Lakukan refresh setelah build lalu bandingkan Dashboard > Manage > Billing > profil/admin, termasuk mobile dan dark mode, dengan data pengguna.
- Pengujian undangan foto/video/MP3 asli sampai Publish dan pengalaman tamu masih mengikuti daftar sebelumnya; jangan menganggap hasil fixture 23 Oktober sebagai UAT sesi ini.
