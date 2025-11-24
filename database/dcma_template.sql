

-- Create the database if not exist
CREATE DATABASE IF NOT EXISTS dcma;
USE dcma;

----------------------------------------------------------
-- USERS TABLE (supports login / authentication system)
----------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('student', 'lecturer', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

----------------------------------------------------------
-- CLASSES TABLE
----------------------------------------------------------
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    lecturer_id INT NOT NULL,
    schedule VARCHAR(100) NOT NULL,
    room VARCHAR(50),
    capacity INT DEFAULT 40,
    FOREIGN KEY (lecturer_id) REFERENCES users(user_id)
);

----------------------------------------------------------
-- ENROLLMENTS TABLE (students join classes)
----------------------------------------------------------
CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    status ENUM('enrolled', 'dropped') DEFAULT 'enrolled',
    grade VARCHAR(5),
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    UNIQUE(student_id, class_id)
);

----------------------------------------------------------
-- ATTENDANCE TABLE
----------------------------------------------------------
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);

----------------------------------------------------------
-- SAMPLE DATA
----------------------------------------------------------

-- Insert lecturers
INSERT INTO users (username, password, full_name, email, role)
VALUES
('lecturer_john', 'pass123', 'John Mwangi', 'john.mwangi@dcma.ac.ke', 'lecturer'),
('lecturer_sara', 'pass123', 'Sara Wanjiku', 'sara.wanjiku@dcma.ac.ke', 'lecturer');

-- Insert students
INSERT INTO users (username, password, full_name, email, role)
VALUES
('student_amos', 'pass123', 'Amos Kiptoo', 'amos.kiptoo@dcma.ac.ke', 'student'),
('student_linda', 'pass123', 'Linda Achieng', 'linda.achieng@dcma.ac.ke', 'student'),
('student_kevin', 'pass123', 'Kevin Otieno', 'kevin.otieno@dcma.ac.ke', 'student');

-- Insert classes
INSERT INTO classes (class_code, name, description, lecturer_id, schedule, room, capacity)
VALUES
('CS101', 'Intro to Programming', 'Basics of programming with Python.', 1, 'Mon 10:00-12:00', 'Lab 1', 40),
('CS202', 'Database Systems', 'Relational database design and SQL.', 2, 'Wed 14:00-17:00', 'Room 204', 35),
('CS303', 'Software Engineering', 'SDLC, UML, and Agile methodologies.', 2, 'Fri 09:00-11:00', 'Room 210', 50);

-- Sample enrollments
INSERT INTO enrollments (student_id, class_id, status, grade)
VALUES
(3, 1, 'enrolled', NULL),
(4, 1, 'enrolled', NULL),
(5, 2, 'enrolled', NULL),
(3, 3, 'enrolled', NULL);

-- Sample attendance
INSERT INTO attendance (student_id, class_id, attendance_date, status)
VALUES
(3, 1, '2025-11-01', 'present'),
(3, 1, '2025-11-02', 'late'),
(4, 1, '2025-11-01', 'absent'),
(5, 2, '2025-11-01', 'present');


