# Arsitektur dan penamaan tabel Moshia

Keputusan pemilik project, 8 Oktober 2026: satu aplikasi Laravel modular monolith. Core dipakai bersama; Wedding dikembangkan sekarang. Jastip, Photo Booth dan Restaurant tetap Coming Soon.

## Pembagian modul

| Bagian | Lokasi saat ini | Tanggung jawab |
| --- | --- | --- |
| Core | app/Modules/Core | Identity, Tenancy, Catalog, Billing, Entitlement, Notification, Media |
| Wedding | app/Modules/Wedding | Editor undangan, template, preview/publish, RSVP dan wishes |
| Produk berikutnya | Belum diimplementasikan | Jastip, PhotoBooth, Restaurant |

User/auth masih memakai model app/Models/User dan controller Breeze. Products adalah pengelompokan arsitektur; folder Wedding saat ini tidak dipindah massal. Satu sesi login digunakan seluruh modul. API antar-server dan SSO terpisah belum diperlukan untuk deployment satu aplikasi ini.

## Aturan nama tabel

Awalan menunjukkan modul pemilik data, bukan pengguna yang mengakses data.

| Awalan | Contoh tabel aktual |
| --- | --- |
| core_ | core_tenants, core_tenant_user, core_products, core_plans, core_purchase_orders, core_entitlements, core_media |
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
