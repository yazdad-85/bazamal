# Website Bazar Amal Terintegrasi 2.0

E-commerce amal untuk lembaga pendidikan & masyarakat umum.

## Stack

- Laravel 13 + Livewire 4 + Blade + Tailwind CSS
- Auth admin: Laravel Breeze
- Database: SQLite (default lokal) / MySQL (production)
- Pembayaran: dummy (verifikasi manual)
- WhatsApp: link `wa.me` (tanpa API)

## Setup

```bash
cp .env.example .env
php artisan key:generate
# Pastikan database/database.sqlite ada, atau set DB_* untuk MySQL
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

### MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bazar_amal
DB_USERNAME=root
DB_PASSWORD=
```

### WhatsApp & branding

Default WA masih bisa dari `.env` (`ADMIN_WHATSAPP`), tetapi setelah seed lebih baik diatur di:

**Admin → Pengaturan** (`/admin/settings`)

- Nama website & logo
- WA Bendahara Inti
- WA per lembaga (MTs, SMP, MA, SMA, SMK)
- Info rekening, gambar QRIS, dan tampilan total donasi

## Akun Admin

Saat pertama kali `migrate --seed`, isi `ADMIN_EMAIL` dan `ADMIN_INITIAL_PASSWORD` (minimal 12 karakter) di `.env`. Seeder tidak menimpa password akun yang sudah ada.

Sebelum dibuka ke internet:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://...`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_ENCRYPT=true`
- `TRUSTED_PROXIES` diisi jika ada reverse proxy

Akun admin terakhir tidak bisa dihapus. Ganti password lewat profil setelah masuk.

## Fitur

- Etalase **Menu Bazar** (pesan sampai 30 November 2026) dan **Infak & Sedekah** (tetap dibuka)
- Keranjang session + smart checkout Siswa/Umum
- Metode ambil: ambil di lokasi bazar, atau kirim ke alamat (hanya Menu Bazar, tanpa ongkir, diantar tim panitia)
- Bayar: QRIS (gambar statis, dicek panitia) / transfer + bukti / tunai
- Admin: CRUD produk, filter pesanan, ubah status, export CSV
- Pengguna: semua akun panitia mengurus seluruh pesanan
- SEO: judul, deskripsi, Open Graph, sitemap, dan robots. Halaman admin, login, keranjang, dan pesanan tidak diindeks
