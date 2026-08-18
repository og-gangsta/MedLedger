-- ============================================================
-- ICT243 Web Programming Assignment
-- Pharmacy Inventory Management System
-- Database schema + seed data
-- ============================================================

CREATE DATABASE IF NOT EXISTS pharmacy_inventory
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE pharmacy_inventory;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    username      VARCHAR(50)   NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('admin','pharmacist') NOT NULL DEFAULT 'pharmacist',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicines (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    brand         VARCHAR(120) DEFAULT NULL,
    category_id   INT DEFAULT NULL,
    batch_no      VARCHAR(60)  DEFAULT NULL,
    quantity      INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    unit_price    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date   DATE DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_medicines_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default login: username = admin
-- Password is NOT stored in plain text anywhere in this repo. To set your
-- own, generate a hash with:
--   php -r "echo password_hash('your-password-here', PASSWORD_DEFAULT);"
-- then replace the hash string below before importing this file.
INSERT INTO users (full_name, username, password_hash, role) VALUES
('System Administrator', 'admin', '$2b$12$EMyKaOWOgAn2CFskVOq1a.R1Vwf.ZdKO/eW1JgYMkClKCKTdazp3e', 'admin');

INSERT INTO categories (name, description) VALUES
('Analgesic', 'Pain relief medication'),
('Antibiotic', 'Treats bacterial infections'),
('Antihistamine', 'Treats allergy symptoms'),
('Antacid', 'Relieves indigestion and heartburn'),
('Vitamin & Supplement', 'Nutritional supplements');

INSERT INTO medicines (name, brand, category_id, batch_no, quantity, reorder_level, unit_price, expiry_date) VALUES
('Paracetamol 500mg', 'Panadol', 1, 'B-2026-011', 250, 50, 0.15, '2027-03-01'),
('Amoxicillin 500mg', 'Amoxil', 2, 'B-2026-045', 40, 30, 0.45, '2026-09-15'),
('Cetirizine 10mg', 'Zyrtec', 3, 'B-2026-078', 8, 20, 0.30, '2027-01-20'),
('Omeprazole 20mg', 'Losec', 4, 'B-2025-133', 60, 25, 0.55, '2026-08-10'),
('Vitamin C 1000mg', 'Redoxon', 5, 'B-2026-091', 120, 30, 0.20, '2027-06-30');
