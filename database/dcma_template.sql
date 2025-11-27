-- Dynamic Class Management Application Database

-- Create the database
CREATE DATABASE IF NOT EXISTS dcma;
USE dcma;

-- users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('student','lecturer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- classes table
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_code` varchar(20) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `lecturer_id` int(11) NOT NULL,
  `max_students` int(11) NOT NULL DEFAULT 30,
  `schedule_day` varchar(20) DEFAULT NULL,
  `schedule_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `status` enum('open','closed','archived') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_code` (`class_code`),
  KEY `lecturer_id` (`lecturer_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- enrollments table
CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','dropped','completed') NOT NULL DEFAULT 'enrolled',
  `grade` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_class` (`student_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- attendance table
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `notes` text DEFAULT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollment_date` (`enrollment_id`,`attendance_date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
-- Lecturers (password: password123)
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('mwangi_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Mwangi Kamau', 'mwangi@university.ac.ke', 'lecturer'),
('odhiambo_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Odhiambo Omondi', 'odhiambo@university.ac.ke', 'lecturer');

-- Students (password: password123)
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('akinyi_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Akinyi Okoro', 'akinyi@student.ac.ke', 'student'),
('wambua_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Wambua Mutisya', 'wambua@student.ac.ke', 'student'),
('chebet_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chebet Kirui', 'chebet@student.ac.ke', 'student');

-- Classes
INSERT INTO `classes` (`class_code`, `class_name`, `description`, `lecturer_id`, `max_students`, `schedule_day`, `schedule_time`, `room`) VALUES
('ICT101', 'Introduction to ICT', 'A foundational course on Information and Communication Technology.', 1, 50, 'Monday', '10:00:00', 'Room 101'),
('KISW202', 'Advanced Kiswahili', 'A course on advanced Kiswahili grammar and literature.', 2, 30, 'Tuesday', '14:00:00', 'Room 202'),
('DEV303', 'Development Studies', 'A course on the principles of development studies.', 1, 40, 'Wednesday', '11:00:00', 'Room 303');

-- Enrollments
INSERT INTO `enrollments` (`student_id`, `class_id`, `grade`) VALUES
(3, 1, 85.50),
(4, 1, 92.00),
(5, 1, 78.25),
(3, 2, 88.00),
(4, 3, 95.00);
