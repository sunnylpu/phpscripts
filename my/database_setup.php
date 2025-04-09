-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS calendar_events;

-- Use the calendar_events database
USE calendar_events;

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    color VARCHAR(20) DEFAULT '#4F46E5',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Example events (optional)
INSERT INTO events (title, start_date, end_date, color, description) VALUES
('Team Meeting', '2025-04-10 10:00:00', '2025-04-10 11:30:00', '#4F46E5', 'Weekly team sync meeting'),
('Project Deadline', '2025-04-15 00:00:00', '2025-04-15 23:59:59', '#EF4444', 'Final submission for Q2 project'),
('Lunch with Client', '2025-04-12 12:30:00', '2025-04-12 14:00:00', '#10B981', 'Lunch meeting with potential client');