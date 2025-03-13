-- Create the database
CREATE DATABASE IF NOT EXISTS akademi_merdeka;
USE akademi_merdeka;

-- ===============================
-- User Management Tables
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin user
INSERT INTO users (username, password, role) 
VALUES ('admin', 'admin123', 'admin');