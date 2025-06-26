-- Create database with proper charset and collation
CREATE DATABASE IF NOT EXISTS izam_ecommerce
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the user
GRANT ALL PRIVILEGES ON izam_ecommerce.* TO 'izam_user'@'%';
FLUSH PRIVILEGES;

-- Use the database
USE izam_ecommerce;
