-- database.sql
-- Script untuk membuat database dan tabel yang dibutuhkan Smart Garden

CREATE DATABASE IF NOT EXISTS `smartgarden` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smartgarden`;

CREATE TABLE IF NOT EXISTS `sensor_data` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `waktu` DATETIME NOT NULL,
    `suhu_ds18b20` FLOAT NOT NULL,
    `suhu_dht11` FLOAT NOT NULL,
    `kelembaban_dht11` FLOAT NOT NULL,
    `kelembaban_tanah` FLOAT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kontrol_pompa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `status` ENUM('ON', 'OFF') NOT NULL,
    `mode` ENUM('otomatis', 'manual') NOT NULL,
    `waktu` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
