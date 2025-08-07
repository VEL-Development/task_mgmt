-- Create task_statuses table
CREATE TABLE IF NOT EXISTS task_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    group_status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL,
    color VARCHAR(7) DEFAULT '#6366f1',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default statuses
INSERT INTO task_statuses (name, group_status, color, sort_order) VALUES
('Pending', 'pending', '#f59e0b', 1),
('In Progress', 'in_progress', '#3b82f6', 2),
('On Hold', 'pending', '#64748b', 3),
('Under UAT', 'in_progress', '#8b5cf6', 4),
('Testing Pending UAT', 'in_progress', '#06b6d4', 5),
('Completed', 'completed', '#10b981', 6),
('Cancelled', 'cancelled', '#ef4444', 7);

-- Add status_id column to tasks table
ALTER TABLE tasks ADD COLUMN status_id INT DEFAULT NULL;
ALTER TABLE tasks ADD FOREIGN KEY (status_id) REFERENCES task_statuses(id);

-- Update existing tasks to use new status system
UPDATE tasks SET status_id = (SELECT id FROM task_statuses WHERE group_status = tasks.status LIMIT 1);