# 🕋 Quran Telegram Bot

Bot Telegram yang memungkinkan pengguna untuk mengakses Al-Quran, mencari ayat, mendengarkan bacaan, melihat tafsir, dan fitur lainnya langsung dari aplikasi Telegram.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Fitur

- 📖 Melihat daftar surah Al-Quran
- 🔖 Membaca ayat-ayat Al-Quran
- 🔍 Mencari ayat berdasarkan kata kunci
- 🎙️ Mendengarkan audio bacaan ayat
- 📚 Membaca tafsir surah
- 🔢 Melihat informasi juz
- 🎲 Mendapatkan ayat acak
- 📅 Ayat pilihan hari ini
- 🕌 Jadwal sholat (simulasi)
- 📱 Antarmuka tombol dan menu interaktif

## Persyaratan

- PHP 7.0+
- Server dengan dukungan HTTPS (wajib untuk webhook Telegram)
- Bot Telegram (didaftarkan melalui BotFather)
- Akses ke API Google Sheets untuk data Al-Quran

## Penginstalan

### 1. Siapkan Bot Telegram
1. Mulai chat dengan [@BotFather](https://t.me/BotFather) di Telegram
2. Kirim perintah `/newbot`
3. Ikuti instruksi untuk membuat bot baru
4. Catat token API bot yang diberikan

### 2. Persiapkan Server
1. Pastikan server Anda mendukung HTTPS
2. Upload file `telegram_webhook.php` ke server
3. Edit file untuk mengganti `YOUR_TELEGRAM_BOT_TOKEN` dengan token bot Anda:
   ```php
   $botToken = 'YOUR_TELEGRAM_BOT_TOKEN';
   ```

### 3. Konfigurasi Webhook
Ada beberapa cara untuk mengatur webhook:

#### Menggunakan Parameter URL
1. Akses URL webhook di browser dengan parameter `setwebhook`:
   ```
   https://domain.com/path/telegram_webhook.php?setwebhook
   ```
2. Anda akan melihat pesan konfirmasi jika berhasil

#### Menggunakan API Telegram Langsung
1. Akses URL API Telegram berikut pada browser:
   ```
   https://api.telegram.org/bot{TOKEN}/setWebhook?url=https://domain.com/path/telegram_webhook.php
   ```
   Ganti `{TOKEN}` dengan token bot Anda.

### 4. Verifikasi Instalasi
1. Periksa status webhook:
   ```
   https://domain.com/path/telegram_webhook.php?webhookinfo
   ```
2. Mulai chat dengan bot Anda di Telegram dan kirim perintah `/start` atau `/help`

## Penggunaan

### Perintah yang Tersedia
- `/start` - Mulai menggunakan bot
- `/help` - Menampilkan bantuan
- `/surahlist` - Menampilkan daftar semua surah
- `/surah {nomor}` - Menampilkan info surah (contoh: `/surah 1`)
- `/ayat {surah}:{ayat}` - Menampilkan ayat tertentu (contoh: `/ayat 1:1`)
- `/juz {nomor}` - Menampilkan ayat pertama dari juz tertentu
- `/cari {kata kunci}` - Mencari ayat berdasarkan kata kunci
- `/audio {surah}:{ayat}` - Mendapatkan audio ayat tertentu
- `/tafsir {surah}` - Menampilkan tafsir surah
- `/random` - Menampilkan ayat acak dari Al-Quran
- `/today` - Menampilkan ayat pilihan hari ini
- `/menu` - Menampilkan menu interaktif

### Tombol Interaktif
Bot ini mendukung tombol interaktif (inline keyboard) untuk navigasi yang lebih mudah. Ketik `/menu` untuk mengakses menu utama.

## Pemecahan Masalah

### Webhook Tidak Berfungsi
1. Pastikan URL server Anda menggunakan HTTPS dengan sertifikat valid
2. Verifikasi bahwa token bot telah dimasukkan dengan benar
3. Periksa file log error di server Anda

### Reset Webhook
Untuk menghapus webhook dan mengatur ulang:
```
https://domain.com/path/telegram_webhook.php?deletewebhook
```
Kemudian atur webhook kembali seperti dijelaskan sebelumnya.

### Log dan Debugging
Bot secara otomatis membuat file log:
- `telegram.txt` - Mencatat semua data masuk dari Telegram
- `telegram_respon.txt` - Mencatat respons yang dikirim ke Telegram
- `error.log` - Mencatat kesalahan PHP

## Pengembangan Lebih Lanjut

Beberapa ide untuk pengembangan masa depan:
- Dukungan untuk lebih banyak bahasa
- Fitur bookmark ayat favorit
- Dukungan untuk pilihan qari (pembaca) yang berbeda
- Integrasi dengan API jadwal sholat yang sebenarnya
- Notifikasi harian untuk ayat dan hadits

## Kredit dan Lisensi

Bot ini menggunakan API Google Sheets untuk data Al-Quran. Terima kasih kepada semua kontributor data Al-Quran digital.

Dikembangkan dengan ❤️ untuk komunitas Muslim.

Lisensi MIT. Lihat file `LICENSE` untuk informasi lebih lanjut.
```
