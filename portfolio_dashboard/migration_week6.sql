-- migration_week6.sql
-- Run this ONLY if your database already exists from Week 2-5.
-- Safely adds Week 6 columns and indexes without touching existing data.
-- If you're setting up fresh, use database.sql (already includes everything below).

USE portfolio_dashboard;

-- ---- New profile fields: social links + resume (Professional Contact Experience) ----
ALTER TABLE profile
    ADD COLUMN linkedin_url VARCHAR(255) DEFAULT NULL AFTER location,
    ADD COLUMN github_url VARCHAR(255) DEFAULT NULL AFTER linkedin_url,
    ADD COLUMN twitter_url VARCHAR(255) DEFAULT NULL AFTER github_url,
    ADD COLUMN resume_file VARCHAR(255) DEFAULT NULL AFTER twitter_url;

-- ---- Performance indexes (API Optimization / Query Performance) ----
-- Speeds up projects.php search (title LIKE / description LIKE) and technology filter
ALTER TABLE projects ADD INDEX idx_projects_title (title);
ALTER TABLE projects ADD INDEX idx_projects_technology (technology);
ALTER TABLE projects ADD INDEX idx_projects_status (status);

-- Speeds up skills.php ordering/grouping by category
ALTER TABLE skills ADD INDEX idx_skills_category (category);

-- Speeds up dashboard "Recent Activities" feed and login-count queries
ALTER TABLE activity_log ADD INDEX idx_activity_user_created (user_id, created_at);
ALTER TABLE activity_log ADD INDEX idx_activity_type (activity_type);

-- Speeds up header.php's unread-count query, which runs on every page load
ALTER TABLE notifications ADD INDEX idx_notifications_user_read (user_id, is_read);
