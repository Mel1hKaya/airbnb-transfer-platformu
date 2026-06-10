-- ============================================================
-- Airbnb Transfer Takip Sistemi - Veritabanı SQL Dosyası
-- Veritabanı: airbnb_transfer
-- ============================================================

CREATE DATABASE IF NOT EXISTS airbnb_transfer
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE airbnb_transfer;

-- ------------------------------------------------------------
-- Tablo 1: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,           -- password_hash() ile saklanacak
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tablo 2: transfers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transfers (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED    NOT NULL,                -- hangi kullanıcı ekledi
    customer_name       VARCHAR(150)    NOT NULL,
    customer_phone      VARCHAR(20)     NOT NULL,
    flight_no           VARCHAR(20)     NOT NULL,
    pickup_location     VARCHAR(255)    NOT NULL,
    dropoff_location    VARCHAR(255)    NOT NULL,
    passenger_count     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    transfer_datetime   DATETIME        NOT NULL,
    price               DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    notes               TEXT            NULL,
    status              ENUM('bekliyor','tamamlandı','iptal') NOT NULL DEFAULT 'bekliyor',
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_transfers_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Test kullanıcısı (şifre: Test1234)
-- password_hash('Test1234', PASSWORD_DEFAULT) ile üretilmiştir
-- ------------------------------------------------------------
INSERT INTO users (name, email, password) VALUES
(
    'Admin Kullanıcı',
    'admin@airbnb-transfer.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.'
);

-- ------------------------------------------------------------
-- Örnek transfer kayıtları
-- ------------------------------------------------------------
INSERT INTO transfers
    (user_id, customer_name, customer_phone, flight_no, pickup_location, dropoff_location, passenger_count, transfer_datetime, price, notes, status)
VALUES
(1, 'Ahmet Yılmaz',  '+90 532 111 2233', 'TK1234', 'İstanbul Havalimanı', 'Sultanahmet Oteli',    2, DATE_ADD(NOW(), INTERVAL  2 HOUR),  850.00, 'VIP müşteri, güleryüzlü karşılanacak.', 'bekliyor'),
(1, 'Emily Johnson', '+1 212 555 0198',  'AA789',  'Sabiha Gökçen Hav.',  'Kadıköy Airbnb Ev',   1, DATE_ADD(NOW(), INTERVAL  5 HOUR),  650.00, 'İngilizce konuşuyor.',                 'bekliyor'),
(1, 'Klaus Müller',  '+49 30 12345678',  'LH456',  'İstanbul Havalimanı', 'Beşiktaş Marina',      3, DATE_SUB(NOW(), INTERVAL  1 DAY),   1200.00, NULL,                                  'tamamlandı'),
(1, 'Sophie Martin', '+33 6 12 34 56 78','AF321',  'Sabiha Gökçen Hav.',  'Taksim Meydanı',       2, DATE_SUB(NOW(), INTERVAL  3 DAY),   750.00, 'Bagaj fazlası var.',                   'tamamlandı'),
(1, 'Marco Rossi',   '+39 06 1234 5678', 'AZ567',  'İstanbul Havalimanı', 'Fatih Eski Şehir Ev', 4, DATE_SUB(NOW(), INTERVAL  6 HOUR),  950.00, 'İptal nedeni: uçuş gecikti.',          'iptal');
