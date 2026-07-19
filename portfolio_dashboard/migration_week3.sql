-- migration_week3.sql
-- Run this ONLY if your database already exists from Week 2.
-- It safely adds the new Week 3 table without touching existing data.
-- If you're setting up fresh, just use database.sql instead (already includes this table).

USE portfolio_dashboard;

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add technology column to existing projects table (safe to re-run: check first in phpMyAdmin if it errors)
ALTER TABLE projects ADD COLUMN technology VARCHAR(150) AFTER project_image;
