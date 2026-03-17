-- ==============================================================================
-- SHEINDANA GLOBAL EDUCATION ECOSYSTEM - MASTER DATABASE SETUP SCRIPT
-- ==============================================================================
-- WARNING: This will drop the existing database and recreate it from scratch.
-- Do not run this on a production database unless you want to wipe all data!

DROP DATABASE IF EXISTS sheindana_db;
CREATE DATABASE sheindana_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sheindana_db;

-- ======================================================
-- 1. BRANCHES (YANGON ACADEMIC CENTERS)
-- ======================================================
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE,
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO branches (name, code, address) VALUES 
('Kamayut HQ', 'KMY', '123 University Avenue, Kamayut Township, Yangon'),
('Hlaing Center', 'HLN', '45 Insein Road, Hlaing Township, Yangon'),
('Tamwe Center', 'TMW', '88 Banyar Dala Road, Tamwe Township, Yangon'),
('Sanchaung Center', 'SNC', '12 Padonmar Street, Sanchaung Township, Yangon'),
('Bahan Executive', 'BHN', '99 Kabar Aye Pagoda Road, Bahan Township, Yangon');

-- ======================================================
-- 2. SYSTEM STAFF (ADMINS)
-- ======================================================
CREATE TABLE adm_usr (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Staff_Myanmar', 'Staff_Japan', 'Finance_mm', 'Finance_jp') NOT NULL,
    branch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Passwords are set to: admin123
INSERT INTO adm_usr (username, password_hash, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'Admin'),
('finance_mgr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Daw Aye Aye', 'Finance_mm');

-- ======================================================
-- 3. AGENT NETWORK (PARTNERS)
-- ======================================================
CREATE TABLE agent_user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    agent_type ENUM('internal','external','partner','freelancer') DEFAULT 'external',
    commission_type ENUM('percentage','fixed','none') DEFAULT 'percentage',
    commission_value DECIMAL(10,2) DEFAULT 10.00,
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    is_verified TINYINT(1) DEFAULT 1, -- Set to 1 so you don't have to reset password immediately for testing
    joined_at DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Passwords are set to: agent123
INSERT INTO agent_user (agent_code, full_name, email, password_hash, phone, agent_type, commission_type, commission_value) VALUES 
('AG-1001', 'Aung Kyaw', 'aung@agent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'internal', 'fixed', 50000.00),
('AG-1002', 'Mya Mya', 'mya@partner.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09987654321', 'partner', 'percentage', 15.00),
('AG-1003', 'Kyaw Zin', 'kyaw@freelance.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09555666777', 'freelancer', 'fixed', 30000.00);

-- ======================================================
-- 4. UNIFIED CLASS DIVISIONS & SYNC
-- ======================================================
CREATE TABLE class_divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(100) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    section VARCHAR(5),
    capacity INT DEFAULT 30,
    start_time TIME,
    end_time TIME,
    shift ENUM('morning', 'evening'),
    description TEXT,
    duration_text VARCHAR(50),
    icon VARCHAR(50) DEFAULT 'fa-solid fa-book',
    status ENUM('active', 'completed', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO class_divisions (class_name, academic_year, section, capacity, start_time, end_time, shift, description, duration_text, icon) VALUES 
('JLPT N5 Foundation', '2025-2026', 'A', 30, '09:00:00', '12:00:00', 'morning', 'Essential grammar and hiragana mastery for beginners. Gateway to Japan.', '6 Months', 'fa-solid fa-language'),
('JLPT N4 Pre-Intermediate', '2025-2026', 'B', 25, '13:00:00', '16:00:00', 'evening', 'Advanced daily conversation and kanji preparation for part-time work.', '6 Months', 'fa-solid fa-pen-nib'),
('JLPT N3 Business Ready', '2025-2026', 'A', 20, '09:00:00', '12:00:00', 'morning', 'Bridge to fluency. Required for most vocational colleges in Tokyo.', '8 Months', 'fa-solid fa-briefcase'),
('EJU University Prep', '2025-2026', 'C', 15, '17:00:00', '20:00:00', 'evening', 'Specialized Math, Science & Japan & the World prep for University entrance.', '1 Year', 'fa-solid fa-building-columns');

CREATE TABLE class_branch_visibility (
    class_id INT,
    branch_id INT,
    is_visible BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (class_id, branch_id),
    FOREIGN KEY (class_id) REFERENCES class_divisions(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- Sync classes to branches (Make N5 available everywhere, EJU only at Kamayut)
INSERT INTO class_branch_visibility (class_id, branch_id, is_visible) VALUES 
(1, 1, 1), (1, 2, 1), (1, 3, 1), (1, 4, 1), (1, 5, 1), -- N5 everywhere
(2, 1, 1), (2, 2, 1), (2, 3, 0), (2, 4, 1), (2, 5, 0), -- N4 some places
(3, 1, 1), (3, 5, 1),                                  -- N3 limited
(4, 1, 1);                                             -- EJU Kamayut HQ only

-- ======================================================
-- 5. PACIFIC DATABASE (JAPAN SCHOOLS)
-- ======================================================
CREATE TABLE japan_schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255) NOT NULL,
    region ENUM('Tokyo', 'Osaka', 'Fukuoka', 'Other') NOT NULL,
    type ENUM('Language School', 'University', 'Vocational') NOT NULL,
    website VARCHAR(255),
    address_line TEXT,
    city VARCHAR(100),
    est_year INT,
    tuition_fees DECIMAL(15,2) DEFAULT 0.00,
    admission_months VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO japan_schools (school_name, region, type, website, address_line, city, est_year, tuition_fees, admission_months, description) VALUES 
('Tokyo Kokusai Academy', 'Tokyo', 'Language School', 'https://example.com', '1-2-3 Shinjuku-ku', 'Shinjuku', 1985, 750000.00, 'April, October', 'A premier language institute located in the heart of Tokyo. High success rate for EJU placements.'),
('Osaka Technical Institute', 'Osaka', 'Vocational', 'https://example.com', '4-5 Namba', 'Namba', 2001, 1200000.00, 'April', 'Specialized IT and Engineering vocational college offering direct industry placements post-graduation.'),
('Fukuoka Global University', 'Fukuoka', 'University', 'https://example.com', '7-8 Hakata', 'Hakata', 1960, 950000.00, 'April, September', 'Comprehensive university with an extensive international student support program and dormitory facilities.');

-- ======================================================
-- 6. STUDENT REGISTRATIONS & APPLICATIONS
-- ======================================================
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    nric_passport VARCHAR(50),
    target_school_id INT,
    document_path VARCHAR(255),
    status ENUM('pending', 'reviewing', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agent_user(id),
    FOREIGN KEY (target_school_id) REFERENCES japan_schools(id) ON DELETE SET NULL
);

INSERT INTO students (agent_id, full_name, nric_passport, target_school_id, status) VALUES 
(1, 'Thandar Hlaing', '12/KMY(N)123456', 1, 'approved'),
(2, 'Kyaw Swar', 'MDY-987654', 2, 'reviewing'),
(1, 'May Myat', '14/BGN(N)111222', 3, 'pending');

-- ======================================================
-- 7. PUBLIC ENQUIRIES (LEADS)
-- ======================================================
CREATE TABLE enquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    interest VARCHAR(100),
    status ENUM('new', 'contacted', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO enquiries (full_name, email, phone, interest, status) VALUES 
('Zaw Ye', 'zaw@gmail.com', '09444555666', 'University Placement (EJU)', 'new'),
('Su Su', 'su@yahoo.com', '09777888999', 'Language Course (JLPT Prep)', 'contacted');

-- ======================================================
-- 8. FINANCE & INVOICING
-- ======================================================
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    agent_id INT NOT NULL,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    currency CHAR(3) DEFAULT 'MMK',
    status ENUM('draft', 'sent', 'paid', 'cancelled') DEFAULT 'draft',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agent_user(id) ON DELETE CASCADE
);

CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) DEFAULT 0.00,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Seed an example invoice
INSERT INTO invoices (id, invoice_number, agent_id, total_amount, status) VALUES 
(1, 'INV-2026-A8F29', 1, 200000.00, 'sent');

INSERT INTO invoice_items (invoice_id, description, amount) VALUES 
(1, 'Commission: Placement for Thandar Hlaing', 150000.00),
(1, 'Document Translation Bonus', 50000.00);

-- ======================================================
-- 9. SYSTEM ACTIVITY LOGS
-- ======================================================
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_role VARCHAR(50),
    action VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO system_logs (user_id, user_role, action, details, ip_address) VALUES 
(1, 'Admin', 'SYSTEM_INIT', 'Database seeded successfully', '127.0.0.1');

-- END OF SCRIPT