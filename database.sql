-- Struktur tabel users untuk login admin
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

-- Tabel sensor_data (jika belum ada)
CREATE TABLE sensor_data (
  id INT NOT NULL AUTO_INCREMENT,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  suhu_ds18b20 FLOAT,
  suhu_dht11 FLOAT,
  kelembaban_dht11 FLOAT,
  kelembaban_tanah FLOAT,
  PRIMARY KEY (id)
);

-- Tabel kontrol_pompa (jika belum ada)
CREATE TABLE kontrol_pompa (
  id INT NOT NULL AUTO_INCREMENT,
  status ENUM('ON', 'OFF') NOT NULL,
  mode ENUM('otomatis', 'manual') NOT NULL,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
