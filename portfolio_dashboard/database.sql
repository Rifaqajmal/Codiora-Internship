-- Portfolio Management Dashboard Database
-- Import this file in phpMyAdmin or via: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS portfolio_dashboard;
USE portfolio_dashboard;

-- Users table (Authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Profile table (Personal Info + About + Image)
CREATE TABLE IF NOT EXISTS profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    job_title VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    location VARCHAR(100),
    linkedin_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    twitter_url VARCHAR(255) DEFAULT NULL,
    resume_file VARCHAR(255) DEFAULT NULL,
    about_text TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency INT DEFAULT 50, -- 0-100
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_skills_category (category)
);

-- Categories table (for Projects)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    category_id INT,
    project_link VARCHAR(255),
    github_link VARCHAR(255),
    project_image VARCHAR(255) DEFAULT 'default_project.png',
    technology VARCHAR(150),
    status ENUM('Completed','In Progress','Planned') DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_projects_title (title),
    INDEX idx_projects_technology (technology),
    INDEX idx_projects_status (status)
);

-- Activity Log table (Week 3 - tracks logins, project updates, profile changes)
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL, -- 'login', 'project_added', 'project_updated', 'project_deleted', 'profile_updated', 'skill_added', 'skill_updated', 'skill_deleted', 'category_added', 'category_updated', 'category_deleted'
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity_user_created (user_id, created_at),
    INDEX idx_activity_type (activity_type)
);

-- Notifications table (Week 4 - bell icon dropdown)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read)
);

-- NOTE: Admin user is created automatically by setup.php (run it once in browser)
-- This generates a proper bcrypt hash for password: admin123
-- Profile and sample categories are also seeded by setup.php after the admin user is created.
