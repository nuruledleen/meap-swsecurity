CREATE DATABASE IF NOT EXISTS parking_system;
USE parking_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_plate VARCHAR(10) NOT NULL,
    slot_number VARCHAR(5) NOT NULL,
    parking_date DATE NOT NULL,
    parking_time TIME NOT NULL,
    status ENUM('ACTIVE', 'CANCELLED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slot_schedule_status (slot_number, parking_date, parking_time, status)
);
