# Aplikasi Absensi Pegawai (Sync ke Google Sheets)

Aplikasi Laravel untuk absensi pegawai: login → tanda tangan digital sekali di awal →
klik "Absen Masuk"/"Absen Pulang" → otomatis tercatat di tab Google Sheet pegawai
tersebut (tanggal, jam, tanda tangan). Absen otomatis nonaktif di akhir pekan &
hari libur nasional Indonesia.

## Yang sudah dibuat di sini
- Model & migration: `Pegawai`, `Absensi`
- `HolidayService` — cek hari kerja (5 hari) + libur nasional (API Nager.Date)
- `GoogleSheetsService` — tulis jam masuk/pulang & tempel tanda tangan ke Sheets
- `AbsensiController`, `ProfileController` — logic absen & simpan signature
- View: dashboard (tombol absen + jam realtime), halaman signature pad
- `routes/web.php`

**Belum dibuat** (perlu digenerate via Laravel Breeze, karena butuh `composer install`
yang tidak bisa dijalankan di sandbox ini): halaman login, register, migration
bawaan Laravel (users, sessions, dll), scaffolding auth. Ini standar Breeze, tinggal
di-generate begitu project di-setup di komputer kamu.

## Langkah Setup

### 1. Buat project Laravel baru & salin file ini
```bash
composer create-project laravel/laravel absensi-app
cd absensi-app
# lalu copy-kan semua folder/file dari attendance-app/ (app, database, routes, resources, config, .env.example)
# ke project baru ini, timpa file yang namanya sama.
```

### 2. Install dependency tambahan
```bash
composer require google/apiclient revolution/laravel-google-sheets
composer require laravel/breeze --dev
php artisan breeze:install blade
```
Breeze akan generate login/register/migration users bawaan. Setelah itu, **ganti**
guard auth default dari model `User` ke model `Pegawai` di `config/auth.php`:
```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'pegawais'],
],
'providers' => [
    'pegawais' => ['driver' => 'eloquent', 'model' => App\Models\Pegawai::class],
],
```

### 3. Setup Google Cloud (Service Account)
1. Buka https://console.cloud.google.com → buat project baru
2. Aktifkan **Google Sheets API** dan **Google Drive API** (menu "APIs & Services")
3. Buat **Service Account** → buat key baru format JSON → download
4. Simpan file JSON itu di `storage/app/google/service-account.json`
5. Buka file JSON, cari `client_email` (formatnya `xxx@xxx.iam.gserviceaccount.com`)
6. Buka spreadsheet kamu → tombol **Share** → tempel email itu → beri akses **Editor**

### 4. Siapkan tab "TEMPLATE" di spreadsheet
Karena tiap pegawai butuh tab sendiri dengan layout sama seperti template asli
(Daftar Hadir Tenaga Kerja), duplikat salah satu tab yang ada, kosongkan isinya
(NAMA, BULAN, AREA KERJA, dan semua data absen), lalu rename tab itu jadi
persis **`TEMPLATE`**. Sistem akan otomatis menduplikat tab ini untuk pegawai baru.

### 5. Konfigurasi `.env`
Copy `.env.example` ke `.env`, isi `GOOGLE_SHEETS_SPREADSHEET_ID` (sudah terisi
sesuai ID spreadsheet kamu saat ini), lalu jalankan:
```bash
php artisan key:generate
php artisan migrate
php artisan storage:link   # penting! biar file tanda tangan bisa diakses via URL publik
```

### 6. Jalankan
```bash
php artisan serve
```

## Catatan penting
- **Timezone**: set `APP_TIMEZONE=Asia/Jakarta` di `.env` (sudah ada di contoh).
  Kalau ada pegawai di area kerja WITA/WIT, bisa ditambah field timezone per pegawai nanti.
- **Tanda tangan**: disimpan sebagai file PNG publik di `storage/app/public/signatures/`,
  ditempel ke sheet lewat formula `=IMAGE(url)`. Pastikan `APP_URL` di `.env` bisa
  diakses dari luar (atau pakai domain production) supaya Google Sheets bisa
  mengambil gambarnya — kalau di-hosting di `localhost`, formula IMAGE() tidak akan
  tampil sampai di-deploy ke domain publik.
- **Data libur nasional** diambil dari API publik https://date.nager.at (gratis,
  tanpa API key) dan di-cache 1 hari. Kalau mau data lebih presisi (cuti bersama versi
  SKB 3 Menteri), datanya bisa diganti manual di `HolidayService`.
- Admin untuk menambah akun pegawai belum dibuatkan (bisa ditambah lewat
  `php artisan tinker` dulu untuk MVP, atau saya bisa buatkan halaman admin
  kalau dibutuhkan).

## Struktur folder
```
app/
  Models/Pegawai.php, Absensi.php
  Http/Controllers/AbsensiController.php, ProfileController.php
  Services/GoogleSheetsService.php, HolidayService.php
database/migrations/
routes/web.php
resources/views/
  layouts/app.blade.php
  dashboard/index.blade.php
  profile/signature.blade.php
config/services.php  (snippet — gabungkan ke config/services.php Laravel)
```
