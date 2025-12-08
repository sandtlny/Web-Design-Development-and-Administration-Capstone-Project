-- ===========================================================
-- Dynamic Class Management Application - Database Schema
-- ===========================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS dcma;
USE dcma;

-- ===========================================================
-- USERS TABLE
-- ===========================================================
-- Fields:
-- id, username, password (hashed), full_name, email, role
-- Role: 'student' or 'lecturer'
-- ===========================================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('student','lecturer','admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================================
-- CLASSES TABLE
-- ===========================================================
-- Fields:
-- id, class_code, class_name, description, lecturer_id, schedule, room, capacity
-- lecturer_id → FK to users(id)
-- ===========================================================

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_code VARCHAR(20) NOT NULL UNIQUE,
    class_name VARCHAR(100) NOT NULL,
    description TEXT,
    lecturer_id INT NOT NULL,
    schedule VARCHAR(100),
    room VARCHAR(50),
    capacity INT DEFAULT 40,

    FOREIGN KEY (lecturer_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===========================================================
-- ENROLLMENTS TABLE
-- ===========================================================
-- Fields:
-- id, student_id, class_id, enrollment_date, grade
-- Ensures a student cannot enroll twice in the same class
-- ===========================================================

CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade VARCHAR(5) DEFAULT NULL,

    UNIQUE(student_id, class_id),

    FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (class_id) REFERENCES classes(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===========================================================
-- ATTENDANCE TABLE
-- ===========================================================
-- Fields:
-- id, student_id, class_id, status, notes, date_marked
-- Status options: Present / Absent / Late
-- ===========================================================

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    status ENUM('Present','Absent','Late') NOT NULL,
    notes TEXT,
    date_marked TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (class_id) REFERENCES classes(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===========================================================
-- SAMPLE DATA
-- ===========================================================

-- -----------------------------------------------------------
-- Insert Lecturers
-- Password for all sample accounts = '12345' (hashed)
-- -----------------------------------------------------------

INSERT INTO users (username, password, full_name, email, role) VALUES
('lecturer_john',  '$2y$10$KXjvPmYgE1m8yV2.xxxhashedexample', 'John Mwangi', 'john@example.com', 'lecturer'),
('lecturer_sarah', '$2y$10$KXjvPmYgE1m8yV2.xxxhashedexample', 'Sarah Otieno', 'sarah@example.com', 'lecturer');

-- -----------------------------------------------------------
-- Insert Students
-- -----------------------------------------------------------

INSERT INTO users (username, password, full_name, email, role) VALUES
('student1', '$2y$10$KXjvPmYgE1m8yV2.xxxhashedexample', 'Brian Kamau', 'brian@example.com', 'student'),
('student2', '$2y$10$KXjvPmYgE1m8yV2.xxxhashedexample', 'Grace Wanjiru', 'grace@example.com', 'student'),
('student3', '$2y$10$KXjvPmYgE1m8yV2.xxxhashedexample', 'Kevin Ouma', 'kevin@example.com', 'student');

-- -----------------------------------------------------------
-- Insert Classes
-- -----------------------------------------------------------

INSERT INTO classes (class_code, class_name, description, lecturer_id, schedule, room, capacity) VALUES
('CS101', 'Introduction to Programming', 'Basics of programming logic and Python.', 1, 'Mon & Wed 10AM - 12PM', 'Lab 1', 50),
('CS202', 'Database Systems', 'Relational databases, SQL, and normalization.', 1, 'Tue & Thu 2PM - 4PM', 'Room 204', 45),
('CS303', 'Web Development', 'HTML, CSS, JS & PHP fundamentals.', 2, 'Fri 9AM - 12PM', 'Tech Lab', 40);

-- -----------------------------------------------------------
-- Sample Enrollments
-- -----------------------------------------------------------

INSERT INTO enrollments (student_id, class_id, grade) VALUES
(3, 1, 'B+'),
(4, 1, NULL),
(5, 2, 'A'),
(3, 3, NULL),
(4, 3, 'B');

-- -----------------------------------------------------------
-- Sample Attendance
-- -----------------------------------------------------------

INSERT INTO attendance (student_id, class_id, status, notes) VALUES
(3, 1, 'Present', 'Attended full session'),
(4, 1, 'Absent', 'Was sick'),
(5, 2, 'Present', 'On time'),
(3, 3, 'Late', 'Arrived 15 minutes late');
