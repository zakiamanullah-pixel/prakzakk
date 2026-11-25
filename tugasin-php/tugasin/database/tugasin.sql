-- Import file ini lewat phpMyAdmin (tab "Import") atau jalankan di tab "SQL".
CREATE DATABASE IF NOT EXISTS tugasin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tugasin;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(40)  NOT NULL,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  dibuat_pada   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tugas (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  judul       VARCHAR(100) NOT NULL,
  matkul      VARCHAR(80)  NOT NULL,
  deadline    DATE         NOT NULL,
  prioritas   ENUM('tinggi','sedang','rendah') NOT NULL DEFAULT 'sedang',
  catatan     VARCHAR(400) NOT NULL DEFAULT '',
  selesai     TINYINT(1)   NOT NULL DEFAULT 0,
  dibuat_pada TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_deadline (user_id, deadline),
  CONSTRAINT fk_tugas_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
