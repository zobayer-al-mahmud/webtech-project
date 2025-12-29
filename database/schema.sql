CREATE DATABASE IF NOT EXISTS university_events;
USE university_events;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_role ENUM('admin', 'organizer', 'student') NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Test users (password format: RoleName@123)
INSERT INTO users (username, email, password_hash, full_name, user_role) VALUES
('admin', 'admin@university.edu', '$2y$10$OvEYJctig9NpPDMNIgTsv.Fdn7hZgQvLmsb6bLsi2vHgY5x9Wd6e2', 'System Administrator', 'admin'),
('organizer', 'organizer@university.edu', '$2y$10$KXQrTdtI/iDB3ku6wz5G7uApniZfPnZiE6FwZxo6cdQvp/DdGeewO', 'John Organizer', 'organizer'),
('student', 'student@university.edu', '$2y$10$Ws2im9yHrnR5.YOiJM6J6.leMawNJTHrUUEjmN0QAD/.Dfq/d4anO', 'Jane Student', 'student')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);
