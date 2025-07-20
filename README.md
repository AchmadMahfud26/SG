# Smart Garden - Sistem Penyiraman Tanaman Otomatis Berbasis IoT

## Deskripsi
Aplikasi dashboard berbasis web menggunakan PHP dan Bootstrap 5 untuk monitoring sensor dan kontrol pompa air pada sistem penyiraman tanaman otomatis/manual berbasis IoT.

## Fitur
- Monitoring sensor kelembaban tanah, suhu tanah (DS18B20), suhu & kelembaban udara (DHT11)
- Kontrol pompa air dengan mode otomatis/manual
- Dashboard modern dan responsif dengan Bootstrap 5
- Grafik histori kelembaban tanah menggunakan Chart.js
- Endpoint API untuk menerima data sensor dan mengontrol pompa

## Struktur Folder
```
/smartgarden/
├── index.php
├── input_data.php
├── update_status.php
├── set_mode.php
├── monitoring.php
├── /assets/
│   ├── css/
│   └── js/
├── /includes/
│   ├── header.php
│   └── footer.php
└── /config/
    └── db.php
```

## Database
Buat database MySQL bernama `smartgarden` dan jalankan query berikut untuk membuat tabel:

```sql
CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu DATETIME NOT NULL,
    suhu_ds18b20 FLOAT NOT NULL,
    suhu_dht11 FLOAT NOT NULL,
    kelembaban_dht11 FLOAT NOT NULL,
    kelembaban_tanah FLOAT NOT NULL
);

CREATE TABLE kontrol_pompa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('ON', 'OFF') NOT NULL,
    mode ENUM('otomatis', 'manual') NOT NULL,
    waktu DATETIME NOT NULL
);
```

## Cara Penggunaan
1. Sesuaikan konfigurasi database di `config/db.php`
2. Upload seluruh folder ke server web dengan PHP dan MySQL
3. Akses `index.php` untuk melihat dashboard dan kontrol pompa
4. ESP8266/ESP32 mengirim data sensor ke `input_data.php` via HTTP GET/POST
5. Gunakan tombol di dashboard untuk mengubah mode dan status pompa

## Catatan
- Sesuaikan ambang batas kelembaban tanah di `index.php` sesuai kebutuhan
- Pastikan server web dapat menulis ke database MySQL
- Untuk grafik histori, akses `monitoring.php`

## Lisensi
MIT License
