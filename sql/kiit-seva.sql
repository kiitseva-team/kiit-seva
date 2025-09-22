-- KIIT SEVA Database Schema
CREATE DATABASE IF NOT EXISTS kiit_seva;
USE kiit_seva;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'staff', 'admin') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    department VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Teachers table for booking system
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    department VARCHAR(50),
    designation VARCHAR(50),
    office_location VARCHAR(100),
    availability_hours VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Time slots for teacher booking
CREATE TABLE time_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT,
    date DATE,
    start_time TIME,
    end_time TIME,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    teacher_id INT,
    slot_id INT,
    purpose TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    meeting_date DATE,
    meeting_time TIME,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES time_slots(id) ON DELETE CASCADE
);

-- Vehicles table for tracking
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(20) UNIQUE NOT NULL,
    vehicle_type ENUM('bus', 'staff_vehicle', 'maintenance') NOT NULL,
    route_name VARCHAR(100),
    driver_name VARCHAR(100),
    driver_contact VARCHAR(15),
    capacity INT,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicle routes and stops
CREATE TABLE vehicle_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    stop_name VARCHAR(100),
    stop_order INT,
    estimated_time TIME,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Vehicle location history
CREATE TABLE vehicle_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    category ENUM('general', 'academic', 'transport', 'facilities', 'food', 'other') NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    is_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    admin_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password, role, full_name, department) VALUES 
('admin', 'admin@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 'IT');

-- Insert sample teachers
INSERT INTO users (username, email, password, role, full_name, phone, department) VALUES
('dr.sharma', 'sharma@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Dr. Rajesh Sharma', '9876543210', 'Computer Science'),
('prof.patel', 'patel@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Prof. Anita Patel', '9876543211', 'Electronics'),
('dr.kumar', 'kumar@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Dr. Amit Kumar', '9876543212', 'Mechanical');

-- Insert teacher details
INSERT INTO teachers (user_id, department, designation, office_location, availability_hours) VALUES
(2, 'Computer Science', 'Professor', 'CS Block, Room 301', 'Mon-Fri: 10:00-17:00'),
(3, 'Electronics', 'Associate Professor', 'EC Block, Room 205', 'Mon-Fri: 09:00-16:00'),
(4, 'Mechanical', 'Assistant Professor', 'ME Block, Room 102', 'Mon-Fri: 11:00-18:00');

-- Insert sample vehicles
INSERT INTO vehicles (vehicle_number, vehicle_type, route_name, driver_name, driver_contact, capacity, latitude, longitude, status) VALUES
('KL-07-1234', 'bus', 'Campus to Bhubaneswar Route 1', 'Ramesh Singh', '9876501234', 45, 20.3554, 85.8315, 'active'),
('KL-07-5678', 'bus', 'Campus to Railway Station', 'Suresh Kumar', '9876501235', 40, 20.3564, 85.8325, 'active'),
('KL-07-9012', 'staff_vehicle', 'Staff Shuttle Service', 'Mahesh Patra', '9876501236', 8, 20.3574, 85.8335, 'active');

-- Insert sample routes and stops
INSERT INTO vehicle_routes (vehicle_id, stop_name, stop_order, estimated_time, latitude, longitude) VALUES
(1, 'KIIT Campus Gate', 1, '08:00:00', 20.3554, 85.8315),
(1, 'Patia Square', 2, '08:15:00', 20.3500, 85.8200),
(1, 'Jaydev Vihar', 3, '08:30:00', 20.3400, 85.8100),
(1, 'Master Canteen', 4, '08:45:00', 20.2954, 85.8245),
(2, 'KIIT Campus Gate', 1, '09:00:00', 20.3564, 85.8325),
(2, 'Khandagiri', 2, '09:20:00', 20.2500, 85.7800),
(2, 'Railway Station', 3, '09:45:00', 20.2515, 85.8180);

-- Insert sample time slots for teachers
INSERT INTO time_slots (teacher_id, date, start_time, end_time, is_available) VALUES
(1, CURDATE() + INTERVAL 1 DAY, '10:00:00', '11:00:00', TRUE),
(1, CURDATE() + INTERVAL 1 DAY, '14:00:00', '15:00:00', TRUE),
(1, CURDATE() + INTERVAL 2 DAY, '11:00:00', '12:00:00', TRUE),
(2, CURDATE() + INTERVAL 1 DAY, '09:00:00', '10:00:00', TRUE),
(2, CURDATE() + INTERVAL 2 DAY, '15:00:00', '16:00:00', TRUE),
(3, CURDATE() + INTERVAL 1 DAY, '13:00:00', '14:00:00', TRUE);

-- Insert sample student
INSERT INTO users (username, email, password, role, full_name, phone, department) VALUES
('student1', 'student1@kiit.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Arjun Patel', '9876543220', 'Computer Science');