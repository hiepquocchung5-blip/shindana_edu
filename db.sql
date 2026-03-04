-- 1. Create Database
CREATE DATABASE IF NOT EXISTS sheindana_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sheindana_db;

-- 2. Branches (The 5 Yangon Centers)
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL, -- e.g., "Kamayut HQ"
    code VARCHAR(10) UNIQUE,    -- e.g., "KMY"
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- Seed Data: 5 Academic Centers
INSERT INTO branches (name, code, address) VALUES 
('Kamayut HQ', 'KMY', 'Kamayut Township, Yangon'),
('Hlaing Center', 'HLN', 'Hlaing Township, Yangon'),
('Tamwe Center', 'TMW', 'Tamwe Township, Yangon'),
('Sanchaung Center', 'SNC', 'Sanchaung Township, Yangon'),
('Bahan Executive', 'BHN', 'Bahan Township, Yangon');

-- 3. Admin Users (Staff Roles)
CREATE TABLE adm_usr (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('Admin', 'Staff_Myanmar', 'Staff_Japan', 'Finance_mm', 'Finance_jp') NOT NULL,
    branch_id INT NULL, -- If staff is tied to specific branch
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- Seed Data: Super Admin (Pass: admin123)
INSERT INTO adm_usr (username, password_hash, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'Admin');

-- 4. Agent Users (Partners)
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
    joined_at DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data: Agent (Pass: agent123)
INSERT INTO agent_user (agent_code, full_name, email, password_hash, agent_type, commission_value) VALUES 
('AG-001', 'Aung Kyaw', 'aung@agent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'internal', 15.00);

-- 5. Class Divisions (Unified)
CREATE TABLE class_divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(100) NOT NULL, -- "JLPT N5"
    academic_year VARCHAR(20) NOT NULL, -- "2025-2026"
    section VARCHAR(5), -- "A"
    capacity INT DEFAULT 30,
    start_time TIME,
    end_time TIME,
    shift ENUM('morning', 'evening'),
    status ENUM('active', 'completed', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Class Branch Visibility (Pivot Table for Toggles)
-- This allows one class to be visible in Kamayut but hidden in Tamwe
CREATE TABLE class_branch_visibility (
    class_id INT,
    branch_id INT,
    is_visible BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (class_id, branch_id),
    FOREIGN KEY (class_id) REFERENCES class_divisions(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- 7. Japan Schools (Pacific Finder Data)
CREATE TABLE japan_schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255) NOT NULL,
    region ENUM('Tokyo', 'Osaka', 'Fukuoka', 'Other') NOT NULL,
    type ENUM('Language School', 'University', 'Vocational') NOT NULL,
    website VARCHAR(255),
    est_year INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO japan_schools (school_name, region, type, est_year) VALUES 
('Tokyo Kokusai Academy', 'Tokyo', 'Language School', 1985),
('Osaka Technical Institute', 'Osaka', 'Vocational', 2001);