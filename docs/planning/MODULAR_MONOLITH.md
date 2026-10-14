# Arsitektur dan penamaan tabel Moshia

Keputusan pemilik project, 8 Oktober 2026: satu aplikasi Laravel modular monolith. Core dipakai bersama; Wedding dikembangkan sekarang. Jastip, Photo Booth dan Restaurant tetap Coming Soon.

## Pembagian modul

| Bagian | Lokasi saat ini | Tanggung jawab |
| --- | --- | --- |
| Core | app/Modules/Core | Identity, Tenancy, Catalog, Billing, Entitlement, Notification, Media |
| Wedding | app/Modules/Products/Wedding | Editor undangan, template, preview/publish, RSVP dan wishes |
| Produk berikutnya | Belum diimplementasikan | Jastip, PhotoBooth, Restaurant |

User/auth masih memakai model app/Models/User dan controller Breeze. Sejak 14 Oktober, Wedding berada pada namespace App\Modules\Products\Wedding. Satu sesi login digunakan seluruh modul. API antar-server dan SSO terpisah belum diperlukan untuk deployment satu aplikasi ini.

## Aturan nama tabel

Awalan menunjukkan modul pemilik data, bukan pengguna yang mengakses data.

| Awalan | Contoh tabel aktual |
| --- | --- |
| core_ | core_tenants, core_tenant_user, core_products, core_plans, core_purchase_orders, core_entitlements, core_media, core_banks |
| wedding_ | wedding_invitations, wedding_responses |
| jastip_ | Disiapkan sebagai konvensi, belum ada tabel |
| photobooth_ | Disiapkan sebagai konvensi, belum ada tabel |
| restaurant_ | Disiapkan sebagai konvensi, belum ada tabel |

Tabel bawaan framework/paket masih menggunakan nama standar: users, password_reset_tokens, sessions, notifications, roles, permissions, model_has_roles, model_has_permissions, role_has_permissions, cache, cache_locks, jobs, job_batches, failed_jobs, migrations. Ini pengecualian kompatibilitas yang eksplisit. Jika nanti diseragamkan, perlukan migrasi beserta perubahan model, konfigurasi dan relasi; jangan rename manual di database.

Data bisnis baru wajib memakai awalan modul. Pembelian Wedding masuk core_purchase_orders karena pembayaran merupakan kemampuan bersama. Media masuk core_media, ditandai tenant_id dan collection (saat ini wedding), sementara konten undangannya tetap wedding_invitations.

## Migrasi media

Migration 000011 mengganti wedding_media menjadi core_media dan menambahkan collection=wedding. ID, path file dan referensi JSON draft/published dipertahankan. File lama tidak dipindah. Rollback ditolak jika sudah ada koleksi produk lain agar datanya tidak hilang.

Core menangani validasi dan penyimpanan media privat. Wedding menentukan hak akses editor dan apakah sebuah media boleh tampil pada undangan yang masih aktif. core_media menggunakan storage lokal; dukungan S3/CDN dan kebijakan pembersihan belum selesai.

## Batas implementasi

ManualWeddingBilling saat ini masih merupakan adapter development yang menghubungkan Core Billing dengan model Wedding. Sebelum menambah produk berbayar berikutnya, pisahkan pemenuhan produk melalui kontrak/event internal. Jangan menganggap semua ketergantungan antarmodul telah terpisah sempurna.

Pembayaran production, domain/subdomain HTTPS, invoice komersial dan kuota paket masih mengikuti checklist. Tidak membuat tabel kosong untuk produk yang belum dikerjakan.

## Standar modul setelah audit 14 Oktober 2026

- Namespace produk: App\Modules\Products\Wedding. Controller/request tetap di Http; model, services, routes dan providers terpisah. Folder Actions/Policies/Jobs dibuat saat ada implementasi; tes tetap tests/Feature agar mengikuti PHPUnit yang ada.
- Route privat memakai wedding.* dengan URL workspaces/{tenant}/wedding. Route publik tetap invitation/{slug}. Prefix sudah mencegah bentrok; nama contoh pada gambar bukan kewajiban untuk mengganti tautan existing.
- Izin produk memakai Gate abilities yang terdaftar di WeddingServiceProvider: wedding.invitations.view/update/publish, wedding.media.upload, wedding.responses.moderate. Semua memakai aturan owner dan entitlement. Ini bukan UI pengaturan permission granular Spatie; berbagi anggota belum dikerjakan. Gate dan pemeriksaan layanan melindungi akses HTTP serta mutasi yang sudah ada.
- Unggahan baru: workspaces/{tenant_id}/wedding/{generated_filename}. Database menyimpan path aktual sehingga media lama tetap dibaca. Subfolder invitations/{id}/gallery dapat ditambah bila kebutuhan lifecycle media memerlukannya; belum memindahkan file lama.
- Queue saat job tersedia: wedding-render, wedding-notification, jastip-notification, photobooth-render, photobooth-print, restaurant-notification. Saat ini belum ada job produk yang menggunakan nama ini; jangan menganggap worker sudah aktif.
- Produk lain belum dibuat; prefix jastip.*, photobooth.*, restaurant.* digunakan ketika route/izin produknya diimplementasikan.

## Target deployment dari saran pengguna (belum diterapkan)

Rancangan VPS: Nginx, PHP-FPM, MySQL, Redis, Supervisor/systemd untuk worker, scheduler cron, HTTPS, dan object storage jika siap. Versi PHP target pada gambar 8.3 perlu diuji dengan lockfile serta ekstensi di server; lingkungan lokal saat ini tidak di-upgrade.

Susunan release: /var/www/moshia/current menunjuk releases/{release}, dengan shared/.env dan shared/storage. Backup database/media, verifikasi konfigurasi rahasia, dependensi dari lockfile, pengujian, approval build dan rencana rollback harus disiapkan saat deployment diminta. Node dipakai untuk build aset; runtime aplikasi tetap Laravel/PHP. Worker harus mendengarkan nama queue yang benar-benar digunakan job; scheduler dan worker production belum dikonfigurasi di sesi ini.

197 tes / 1.907 assertions lulus setelah refactor; belum merupakan pengujian VPS atau UAT browser.
