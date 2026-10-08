# Layout dashboard — 8 Oktober 2026

Layout admin/pelanggan kini memakai sidebar abu-abu, kartu putih, aksen merah Moshia, ikon navigasi dan menu akun kanan atas sesuai referensi. Isi Dashboard.vue dan Admin/Index.vue serta navigasi yang tersedia dipertahankan. Sidebar menjadi drawer di layar kecil; Escape, klik luar, fokus dan navigasi keyboard didukung.

Menu akun: Account information, Billing, Security, Account activity, Notification settings, Dark mode dan logout. Profile/Edit memakai bagian query section. Billing memuat riwayat PurchaseOrder hanya dari workspace milik akun tersebut, termasuk untuk superadmin; paginasi 10. Security memakai form password/penghapusan yang sudah ada.

Account activity dan pengaturan kanal notifikasi belum diimplementasikan; halaman menjelaskan status sebenarnya tanpa data contoh atau tombol simpan palsu. Lonceng tetap berfungsi seperti sebelumnya.

Tema dashboard default terang dan preferensinya tersimpan pada localStorage moshia-dashboard-theme. CSS baru resources/css/console.css dibatasi ke shell dashboard. ThemeToggle landing tidak diubah.

Verifikasi: 174 tes / 1.431 assertions lulus, termasuk isolasi billing. Build berhasil. Tidak ada perubahan skema/migration atau data akun. Tampilan browser dan interaksi mobile belum diuji manual pada sesi ini.