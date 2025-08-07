-- Create user_settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email_notifications TINYINT(1) DEFAULT 1,
    due_date_reminders TINYINT(1) DEFAULT 1,
    status_updates TINYINT(1) DEFAULT 1,
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    tasks_per_page INT DEFAULT 10,
    default_view VARCHAR(20) DEFAULT 'grid',
    show_profile TINYINT(1) DEFAULT 1,
    activity_tracking TINYINT(1) DEFAULT 1,
    session_timeout INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_settings (user_id)
);