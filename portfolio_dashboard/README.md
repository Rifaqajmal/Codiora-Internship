# Portfolio Management Dashboard

Codiora Full Stack Development internship — Week 6 task: **Final Portfolio Management System Development**.

A full-stack PHP/MySQL admin dashboard for managing a personal developer portfolio — skills, projects, profile, and a public live preview page — now feature-complete, polished, and optimized for production.

## 🔗 Live Deployment

**Live public portfolio:** http://rifaqportfolio.gamer.free/preview.php
**Admin login:** http://rifaqportfolio.gamer.free/login.php *(credentials not published — see Security Note below)*
**Hosting:** InfinityFree (free PHP + MySQL hosting)

## Project Overview

Built entirely with **plain PHP, MySQL, and Bootstrap 5** — no frameworks — across 6 weeks of iterative development. What started as a Week 2 admin dashboard evolved into a mini CMS (Week 3), gained notifications and password management (Week 4), was deployed to production (Week 5), and is now a fully polished, production-optimized system (Week 6) with social links, resume downloads, file cleanup on disk, scroll animations, empty states, and database indexing.

## Features

**Authentication & Security**
- Session-based login/logout with `password_hash()` / `password_verify()`
- Change Password with current-password verification and reuse prevention
- Every protected route guarded via `includes/auth.php`
- Public preview page intentionally requires no login

**Dashboard**
- Live statistics (Total Skills, Total Projects, Categories, Completed Projects)
- Recent Projects table and Recent Activities feed
- User Statistics panel (account age, total logins, last login)
- Real-time notifications with unread badge, auto-generated from every tracked action

**Profile Management**
- Update personal info and About section
- Profile image upload (validated: JPG/PNG/WEBP, max 2MB) — old image auto-deleted from disk on replace
- Resume upload (PDF, max 5MB) — powers the Download Resume button on the public preview
- Social links: LinkedIn, GitHub, Twitter/X
- Change password

**Project & Skill Management**
- Full CRUD for Projects, Skills, and Categories
- Project image upload, technology tags, status badges — old image auto-deleted from disk on replace or project delete
- Project Details modal, pagination (6 per page)
- Search + filter by title, category, status, and technology (filters preserved across pages)

**Public Live Preview**
- No-login portfolio page pulling live from the database
- Client-side category filtering, validated contact form
- Social links + Download Resume button in the hero section
- Scroll-reveal animations on each section (respects `prefers-reduced-motion`)
- Open Graph tags + meta description for link-preview quality
- Accessible: skip-to-content link, semantic landmarks, ARIA labels, keyboard-navigable

**Production Readiness (Week 5)**
- Environment-variable-based DB configuration (`includes/config.php`)
- Shared `head.php` / `footer.php` partials — eliminated duplicated markup across every page
- Auto-dismissing toast notifications (`includes/flash.php`) replacing static alert banners
- Global loading spinner on form submits and navigation (`assets/js/app.js`)
- Branded SVG favicon
- Deployed to a public host with a live, working database connection

**Final Polish (Week 6)**
- Reusable empty-state partial (`includes/empty_state.php`) replacing plain "no data" text across Dashboard, Skills, and Projects
- Automatic uploaded-file cleanup (`includes/file_helper.php`) — deleting a project or replacing a profile/project image removes the old file from `assets/uploads/`, so disk usage doesn't grow unbounded
- Scroll-reveal (`fade-in-up`) animations, hover/transition polish on cards and buttons, all with `prefers-reduced-motion` support
- Database indexes on frequently filtered/searched columns (`migration_week6.sql`) for faster search, filtering, and dashboard queries
- Full pass for unused code, consistent comments, and folder organization

## Technologies Used

- **Backend:** PHP 8.3 (procedural), MySQLi with prepared statements
- **Database:** MySQL
- **Frontend:** Bootstrap 5, Bootstrap Icons, vanilla JavaScript
- **Hosting:** InfinityFree (production), XAMPP (local development)

## Folder Structure
portfolio_dashboard/
├── index.php                  # Router: redirects to dashboard or login
├── login.php / logout.php
├── dashboard.php               # Stats, recent activity, notifications
├── profile.php                 # Personal info, social links, resume, image upload, change password
├── skills.php                  # Skills CRUD
├── projects.php                # Projects + categories CRUD, search/filter, pagination
├── preview.php                 # Public live portfolio page (no login)
├── notifications_read.php      # Marks notifications as read
├── database.sql                # Fresh-install schema
├── migration_week3.sql         # activity_log table + technology column
├── migration_week4.sql         # notifications table
├── migration_week6.sql         # social links, resume column, performance indexes
├── INTEGRATION_TESTING.md      # Test report
├── includes/
│   ├── config.php              # Environment-based DB credentials (safe local fallback only — see Security Note)
│   ├── db.php                  # DB connection (reads config.php)
│   ├── auth.php                # Session/auth guard
│   ├── head.php                # Shared <head> partial
│   ├── footer.php               # Shared closing scripts + Bootstrap JS bundle
│   ├── flash.php                # Auto-dismissing toast notifications
│   ├── empty_state.php          # Reusable "no data yet" UI block
│   ├── file_helper.php          # deleteUploadedFile() - removes old uploads from disk
│   ├── sidebar.php / header.php
│   ├── log_activity.php         # Activity logging (also triggers notifications)
│   └── notifications.php        # Notification creation helper
└── assets/
├── css/style.css, css/preview.css
├── js/app.js                # Loading spinner + scroll-reveal
├── js/preview.js            # Project filter, contact form validation, scroll-reveal
├── favicon.svg
└── uploads/

## Installation (Local — XAMPP)

1. Copy the `portfolio_dashboard` folder into `htdocs`.
2. Start Apache and MySQL in XAMPP.
3. Import `database.sql` in phpMyAdmin, then `migration_week3.sql`, `migration_week4.sql`, then `migration_week6.sql`.
4. Visit `http://localhost/portfolio_dashboard/setup.php` once — this creates a default admin account (`admin` / `admin123`) for **local testing only**.
5. Log in at `http://localhost/portfolio_dashboard/login.php`, then immediately go to **Profile → Change Password** and set your own password.
6. **Delete `setup.php`** after first use.

## Deployment (Production — InfinityFree)

1. Create a free hosting account and MySQL database on InfinityFree.
2. Import your schema via phpMyAdmin (combine `database.sql` + all migrations, or use a single combined SQL file with the `CREATE DATABASE`/`USE` lines removed, since InfinityFree databases already exist and are pre-selected in phpMyAdmin).
3. Update `includes/config.php` **on the live server only** with your live MySQL host, username, password, and database name — get the exact values from InfinityFree's "MySQL Databases" panel, not the account password (they can differ). **Never commit this version to GitHub.**
4. Upload all project files directly into `htdocs` (not nested in a subfolder) via File Manager or FTP.
5. Visit `yoursite.com/setup.php` once to seed the admin user, **immediately change the password**, then delete `setup.php` from the server.
6. Test all core flows (see `INTEGRATION_TESTING.md`).

## 🔒 Security Note

This repository is public. To keep the live deployment secure:
- `includes/config.php` in this repo contains only safe local fallback values (`localhost` / `root` / empty password) — the real production credentials live only on the InfinityFree server itself and are never committed here.
- The default `admin` / `admin123` login created by `setup.php` is meant to be **changed immediately** after first login, on any environment (local or live) you care about securing. It is intentionally **not listed here** to avoid publishing real, working credentials for a live, internet-accessible site.
- `setup.php` should be deleted from any live server immediately after the first admin account is created.

## Screenshots

See LinkedIn post and `INTEGRATION_TESTING.md` for testing screenshots covering: dashboard, notifications, profile/change password, project management, and the authentication redirect check.
