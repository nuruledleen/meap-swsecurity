CREATE DATABASE IF NOT EXISTS parking_system;
USE parking_system;

-- Create users table (no changes needed)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create parking_slots table
CREATE TABLE IF NOT EXISTS parking_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_number VARCHAR(50) NOT NULL UNIQUE  -- Slot identifier, e.g., A1, A2, etc.
);

-- Modify bookings table to include slot_id, start_time, and end_time
ALTER TABLE bookings
    ADD COLUMN slot_id INT NOT NULL,              -- Foreign key referencing parking_slots
    ADD COLUMN start_time TIME NOT NULL,          -- Start time for the booking
    ADD COLUMN end_time TIME NOT NULL,            -- End time for the booking
    ADD FOREIGN KEY (slot_id) REFERENCES parking_slots(id) ON DELETE CASCADE;  -- Add foreign key constraint for slot_id

-- Index for the bookings table to improve performance on lookups
CREATE INDEX idx_slot_schedule_status ON bookings (slot_id, parking_date, start_time, status);
