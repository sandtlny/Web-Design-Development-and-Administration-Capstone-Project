-- Dynamic Class Management Application Database
-- Completed database schema

-- Create the database
CREATE DATABASE IF NOT EXISTS dcma;
USE dcma;

---------------------------------------------------------
-- USERS TABLE (REPLACES FIRST TODO)
---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('student', 'lecturer') NOT NULL
);

---------------------------------------------------------
-- CLASSES TABLE (REPLACES SECOND TODO)
---------------------------------------------------------
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    lecturer_id INT NOT NULL,
    schedule VARCHAR(100),
    room VARCHAR(50),
    capacity INT DEFAULT 30,
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

---------------------------------------------------------
-- ENROLLMENTS TABLE (REPLACES THIRD TODO)
---------------------------------------------------------
CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    grade VARCHAR(5),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);

---------------------------------------------------------
-- ATTENDANCE TABLE (REPLACES FOURTH TODO)
---------------------------------------------------------
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    notes TEXT,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id)
);

---------------------------------------------------------
-- SAMPLE DATA (REPLACES FIFTH TODO)
---------------------------------------------------------

-- Lecturers
INSERT INTO users (username, password, full_name, email, role) VALUES
('lecturer_john', 'password123', 'John Mwangi', 'john@uni.ac.ke', 'lecturer'),
('lecturer_sara', 'password123', 'Sara Wanjiru', 'sara@uni.ac.ke', 'lecturer');

-- Students
INSERT INTO users (username, password, full_name, email, role) VALUES
('student_ian', 'password123', 'Ian Kiptoo', 'ian@student.com', 'student'),
('student_mary', 'password123', 'Mary Achieng', 'mary@student.com', 'student'),
('student_peter', 'password123', 'Peter Otieno', 'peter@student.com', 'student');

-- Classes
INSERT INTO classes (class_code, name, description, lecturer_id, schedule, room, capacity) VALUES
('CS101', 'Intro to Programming', 'Basics of programming in Python', 1, 'Mon & Wed 10:00–12:00', 'Room A1', 40),
('CS201', 'Database Systems', 'Database design and SQL', 2, 'Tue & Thu 2:00–4:00', 'Room B3', 35),
('CS301', 'Web Development', 'HTML, CSS, JavaScript and PHP', 1, 'Fri 9:00–12:00', 'Lab 2', 30);

-- Enrollments
INSERT INTO enrollments (student_id, class_id, status, grade) VALUES
(3, 1, 'active', NULL),
(4, 1, 'active', NULL),
(5, 2, 'active', NULL),
(3, 3, 'active', NULL);

-- Attendance Records
INSERT INTO attendance (enrollment_id, date, status, notes) VALUES
(1, '2025-01-10', 'present', 'On time'),
(1, '2025-01-11', 'late', 'Arrived 10 minutes late'),
(2, '2025-01-10', 'absent', 'Sick leave'),
(3, '2025-01-10', 'present', ''),
(4, '2025-01-12', 'present', 'Good participation');
