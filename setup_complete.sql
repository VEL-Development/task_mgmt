-- Complete database setup for TaskFlow Pro
-- Run this script to set up all required tables and data

-- Create database (uncomment if needed)
-- CREATE DATABASE task_mgmt;
-- USE task_mgmt;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks table with enhanced fields
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to INT,
    created_by INT NOT NULL,
    start_date DATE,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Task notes table
CREATE TABLE IF NOT EXISTS task_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    is_private TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Task attachments table
CREATE TABLE IF NOT EXISTS task_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Task audit log table
CREATE TABLE IF NOT EXISTS task_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    field_name VARCHAR(100),
    old_value TEXT,
    new_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT IGNORE INTO users (username, password, full_name, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@taskflow.com');

-- Insert sample users for testing
INSERT IGNORE INTO users (username, password, full_name, email) VALUES 
('john.doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'john@taskflow.com'),
('jane.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'jane@taskflow.com'),
('mike.wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Wilson', 'mike@taskflow.com');

-- Insert sample tasks for demonstration
INSERT IGNORE INTO tasks (title, description, status, priority, assigned_to, created_by, start_date, due_date) VALUES 
('Setup Development Environment', 'Configure local development environment with all necessary tools and dependencies', 'completed', 'high', 2, 1, '2024-01-01', '2024-01-05'),
('Design User Interface', 'Create wireframes and mockups for the main dashboard and task management interface', 'in_progress', 'medium', 3, 1, '2024-01-03', '2024-01-10'),
('Implement Authentication System', 'Build secure login/logout functionality with session management', 'pending', 'high', 2, 1, '2024-01-05', '2024-01-12'),
('Database Schema Design', 'Design and implement the complete database schema for task management', 'completed', 'urgent', 1, 1, '2024-01-02', '2024-01-04'),
('API Documentation', 'Create comprehensive API documentation for all endpoints', 'pending', 'low', 4, 1, '2024-01-08', '2024-01-15');

-- Insert sample notes
INSERT IGNORE INTO task_notes (task_id, user_id, note, is_private) VALUES 
(1, 1, 'Environment setup completed successfully. All tools are working as expected.', 0),
(2, 3, 'Initial wireframes are ready for review. Focusing on user experience.', 0),
(2, 1, 'Great progress on the UI design. The mockups look professional.', 0),
(3, 2, 'Started working on the authentication module. Using PHP sessions for now.', 0),
(4, 1, 'Database schema is complete and tested. Ready for implementation.', 0);

-- Insert sample audit entries
INSERT IGNORE INTO task_audit (task_id, user_id, action, field_name, old_value, new_value) VALUES 
(1, 1, 'created', NULL, NULL, NULL),
(1, 1, 'updated', 'status', 'pending', 'in_progress'),
(1, 1, 'updated', 'status', 'in_progress', 'completed'),
(2, 1, 'created', NULL, NULL, NULL),
(2, 1, 'updated', 'status', 'pending', 'in_progress'),
(3, 1, 'created', NULL, NULL, NULL),
(4, 1, 'created', NULL, NULL, NULL),
(4, 1, 'updated', 'status', 'pending', 'completed'),
(5, 1, 'created', NULL, NULL, NULL);

-- Create indexes for better performance
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_created_by ON tasks(created_by);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_task_notes_task_id ON task_notes(task_id);
CREATE INDEX idx_task_attachments_task_id ON task_attachments(task_id);
CREATE INDEX idx_task_audit_task_id ON task_audit(task_id);

-- Default password for all sample users is 'password'
-- You can login with:
-- Username: admin, Password: password
-- Username: john.doe, Password: password
-- Username: jane.smith, Password: password
-- Username: mike.wilson, Password: password