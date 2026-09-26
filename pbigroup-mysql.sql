-- PBI Group — straight MySQL import
-- Generated 2026-08-10 11:39:50 UTC
--
-- HOW TO USE
--   1. cPanel > MySQL Databases: create a database and a user, and add
--      the user to the database with ALL PRIVILEGES.
--   2. cPanel > phpMyAdmin: select that database, open the Import tab,
--      choose this file, click Go.
--   3. Edit config.php with the database name, user and password.
--      You do NOT need install.php at all.
--
-- Sign in afterwards at /admin/login.php
--   email     admin@pbigroup.com
--   password  BankDOM#2026
--   Change both immediately under Settings and Customers.
--
-- Contains 16 tables, 51 settings and one administrator. No customers, no transactions.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- ---------------------------------------------------------------- tables

CREATE TABLE IF NOT EXISTS settings (
        skey VARCHAR(64) PRIMARY KEY,
        svalue TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(60),
        last_name VARCHAR(60),
        email VARCHAR(160) UNIQUE,
        phone VARCHAR(40),
        password VARCHAR(255),
        role VARCHAR(10) DEFAULT 'user',
        status VARCHAR(20) DEFAULT 'pending',
        account_number VARCHAR(20),
        account_type VARCHAR(30) DEFAULT 'Savings',
        currency VARCHAR(5) DEFAULT 'USD',
        balance DECIMAL(18,2) NOT NULL DEFAULT 0,
        pin VARCHAR(255),
        email_verified INTEGER DEFAULT 0,
        verify_token VARCHAR(80),
        reset_token VARCHAR(80),
        reset_expires DATETIME,
        otp_code VARCHAR(10),
        otp_expires DATETIME,
        two_factor INTEGER DEFAULT 0,
        kyc_status VARCHAR(20) DEFAULT 'unverified',
        avatar VARCHAR(255),
        dob VARCHAR(20),
        gender VARCHAR(12),
        occupation VARCHAR(80),
        address TEXT,
        city VARCHAR(60),
        state VARCHAR(60),
        country VARCHAR(60),
        zip VARCHAR(20),
        transfer_locked INTEGER DEFAULT 0,
        lock_reason TEXT,
        last_login DATETIME,
        last_ip VARCHAR(60),
        created_at DATETIME,
        updated_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ref VARCHAR(40),
        direction VARCHAR(10),
        category VARCHAR(30),
        amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        fee DECIMAL(18,2) NOT NULL DEFAULT 0,
        balance_after DECIMAL(18,2) NOT NULL DEFAULT 0,
        description TEXT,
        channel VARCHAR(30),
        counterparty VARCHAR(160),
        status VARCHAR(20) DEFAULT 'completed',
        meta TEXT,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transfers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ref VARCHAR(40),
        kind VARCHAR(20),
        amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        fee DECIMAL(18,2) NOT NULL DEFAULT 0,
        total DECIMAL(18,2) NOT NULL DEFAULT 0,
        beneficiary_name VARCHAR(160),
        beneficiary_account VARCHAR(60),
        bank_name VARCHAR(160),
        bank_address TEXT,
        swift VARCHAR(40),
        routing VARCHAR(40),
        iban VARCHAR(60),
        country VARCHAR(60),
        narration TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT,
        approved_by INTEGER,
        approved_at DATETIME,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS deposits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ref VARCHAR(40),
        amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        method VARCHAR(40),
        proof VARCHAR(255),
        note TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT,
        approved_by INTEGER,
        approved_at DATETIME,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS withdrawals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ref VARCHAR(40),
        amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        fee DECIMAL(18,2) NOT NULL DEFAULT 0,
        method VARCHAR(40),
        destination TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT,
        approved_by INTEGER,
        approved_at DATETIME,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ref VARCHAR(40),
        amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        interest_rate REAL DEFAULT 0,
        tenure_months INTEGER DEFAULT 12,
        monthly_payment DECIMAL(18,2) NOT NULL DEFAULT 0,
        total_repayable DECIMAL(18,2) NOT NULL DEFAULT 0,
        amount_repaid DECIMAL(18,2) NOT NULL DEFAULT 0,
        purpose VARCHAR(120),
        details TEXT,
        employment VARCHAR(60),
        monthly_income DECIMAL(18,2) NOT NULL DEFAULT 0,
        collateral TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT,
        approved_by INTEGER,
        disbursed_at DATETIME,
        due_at DATETIME,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loan_repayments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        loan_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        ref VARCHAR(40),
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        card_type VARCHAR(20),
        brand VARCHAR(20),
        number VARCHAR(30),
        cvv VARCHAR(6),
        expiry VARCHAR(10),
        card_pin VARCHAR(255),
        spend_limit DECIMAL(18,2) NOT NULL DEFAULT 0,
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS beneficiaries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        name VARCHAR(160),
        account_number VARCHAR(60),
        bank_name VARCHAR(160),
        swift VARCHAR(40),
        country VARCHAR(60),
        kind VARCHAR(20) DEFAULT 'domestic',
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER,
        subject VARCHAR(200),
        body TEXT,
        is_read INTEGER DEFAULT 0,
        emailed INTEGER DEFAULT 0,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        doc_type VARCHAR(60),
        id_number VARCHAR(80),
        front VARCHAR(255),
        back VARCHAR(255),
        status VARCHAR(20) DEFAULT 'pending',
        note TEXT,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transfer_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        code_type VARCHAR(20),
        code VARCHAR(40),
        used INTEGER DEFAULT 0,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        actor_id INTEGER,
        actor_name VARCHAR(120),
        action VARCHAR(80),
        entity VARCHAR(40),
        entity_id INTEGER,
        details TEXT,
        ip VARCHAR(60),
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kind VARCHAR(20),
        name VARCHAR(120),
        email VARCHAR(160),
        phone VARCHAR(40),
        subject VARCHAR(200),
        message TEXT,
        meta TEXT,
        status VARCHAR(20) DEFAULT 'new',
        admin_note TEXT,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(160),
        ip VARCHAR(60),
        success INTEGER DEFAULT 0,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------- indexes
CREATE INDEX idx_tx_user ON transactions (user_id);
CREATE INDEX idx_tx_created ON transactions (created_at);
CREATE INDEX idx_tr_user ON transfers (user_id);
CREATE INDEX idx_dep_user ON deposits (user_id);
CREATE INDEX idx_loan_user ON loans (user_id);
CREATE INDEX idx_msg_user ON messages (user_id);

-- -------------------------------------------------------------- settings
-- Identity fields ship blank on purpose. Fill them in with your own real
-- details under Settings > Identity — do not publish invented ones.
INSERT INTO settings (skey, svalue) VALUES
  ('site_name', 'PBI Group'),
  ('site_legal', ''),
  ('tagline', 'Modern Banking for Tomorrow'),
  ('currency_code', 'RUB'),
  ('currency_symbol', '₽'),
  ('support_email', ''),
  ('support_phone', ''),
  ('address', ''),
  ('bik', ''),
  ('swift_code', ''),
  ('corr_account', ''),
  ('inn', ''),
  ('footer_note', ''),
  ('admin_notice', 'Demo version — demonstration banking platform for PBI Group.'),
  ('default_lang', 'ru'),
  ('show_demo_badge', '1'),
  ('legal_notice', ''),
  ('mail_method', 'php'),
  ('smtp_host', ''),
  ('smtp_port', '587'),
  ('smtp_user', ''),
  ('smtp_pass', ''),
  ('smtp_secure', 'tls'),
  ('mail_from', 'no-reply@pbigroup.com'),
  ('mail_from_name', 'PBI Group'),
  ('registration_open', '1'),
  ('verify_email', '1'),
  ('auto_activate', '0'),
  ('welcome_bonus', '0'),
  ('maintenance', '0'),
  ('approve_deposits', '1'),
  ('approve_withdrawals', '1'),
  ('approve_transfers', '1'),
  ('approve_internal', '0'),
  ('require_pin', '1'),
  ('require_otp', '0'),
  ('enable_codes', '0'),
  ('code_sequence', 'cot,imf,tax'),
  ('fee_percent', '0.5'),
  ('fee_flat', '0'),
  ('min_transfer', '1'),
  ('max_transfer', '250000'),
  ('daily_limit', '100000'),
  ('min_deposit', '10'),
  ('loans_enabled', '1'),
  ('loan_rate', '4.5'),
  ('loan_min', '500'),
  ('loan_max', '100000'),
  ('loan_tenures', '3,6,12,24,36,48'),
  ('cards_enabled', '1'),
  ('card_fee', '0');

-- --------------------------------------------------------- administrator
INSERT INTO users (first_name, last_name, email, password, role, status,
  account_number, account_type, currency, balance, email_verified, kyc_status,
  country, created_at) VALUES
  ('Site', 'Administrator', 'admin@pbigroup.com', '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 'admin', 'active', '3061279163', 'Administration',
   'USD', 0, 1, 'verified', '', '2026-08-10 11:39:50');

-- --------------------------------------------------------- custom customer accounts
INSERT INTO users (id, first_name, last_name, email, phone, password, role, status,
  account_number, account_type, currency, balance, pin, email_verified, two_factor,
  kyc_status, avatar, dob, gender, occupation, address, city, state, country, zip,
  transfer_locked, lock_reason, created_at) VALUES
  (24, 'Chaek Jae', 'Wan', 'eqrglobal5@gmail.com', '3455433421', '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 'user', 'active',
   '8857325598', 'Checking Account', 'USD', 285270099.91, '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 1, 0,
   'verified', 'PINIMG202503221311-YDFPR.jpg', '08/21/1972', '', '', '216 Thompson RD', 'Wellford', 'South Carolina', 'United States', '29385',
   1, 'Administrative review pending', '2025-03-22 13:00:00'),
  (25, 'Choi Woo', 'Cheol', 'limjaechoon5050@gmail.com', '+12025368686', '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 'user', 'active',
   '7180047307', 'Fixed Deposit Account', 'USD', 77745189.18, '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 1, 1,
   'verified', 'PINIMG202504201140-6YYP0.jpg', '1942-12-25', 'Male', 'Self Employed', 'Connecticut, USA', 'Seoul', 'Kyonggi-do', 'Korea, South', '06928',
   0, '', '2025-04-20 00:00:00'),
  (26, 'Victoria', 'Meloff', 'realrialiti@gmail.com', '+1(530)235-6784', '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 'user', 'active',
   '7902501075', 'Investment Account', 'USD', 882811.91, '$2y$10$aSO6dHu4NixGhkwb1zc6ceOvy6mzEPq1bpQukmcNodUyjnBMmL4DK', 0, 0,
   'verified', 'PINIMG202505031456-YEJNL.jpeg', '02/23/1985', 'Female', '', '1275 EAST DATE ST APT 208 SN BERNRDNO, CA 92402', 'New Port Richey', 'Florida', 'United States', '34653',
   0, '', '2025-05-03 13:36:00');

-- --------------------------------------------------------- customer authorization codes (COT, IMF)
INSERT INTO transfer_codes (user_id, code_type, code, used, created_at) VALUES
  (24, 'cot', '4321', 0, '2025-03-22 13:00:00'),
  (24, 'imf', '1100', 0, '2025-03-22 13:00:00'),
  (25, 'cot', '00', 0, '2025-04-20 00:00:00'),
  (25, 'imf', '00', 0, '2025-04-20 00:00:00'),
  (26, 'cot', '0987', 0, '2025-05-03 13:36:00'),
  (26, 'imf', '0987', 0, '2025-05-03 13:36:00');

SET FOREIGN_KEY_CHECKS = 1;
