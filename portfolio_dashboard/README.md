# Portfolio Management Dashboard

Codiora Full Stack Development Internship — 8-week project (Complete).

A full-stack PHP/MySQL admin dashboard for managing a personal developer portfolio — skills, projects, profile, messages inbox, and a public live preview page — built iteratively over 8 weeks and deployed to production.

## 🔗 Live Demo

| | |
|---|---|
| **Admin Dashboard** | http://rifaqportfolio.gamer.free/login.php |
| **Public Portfolio** | http://rifaqportfolio.gamer.free/preview.php |
| **GitHub Repository** | https://github.com/Rifaqajmal/Codiora-Internship |
| **LinkedIn Post** | https://www.linkedin.com/posts/rifaq-ajmal-4b5a513b3_php-webdevelopment-internship-activity-7488473364362133505-2w4c |
| **Hosting** | InfinityFree (free PHP + MySQL) |

## Project Overview

Built entirely with **plain PHP 8.3, MySQL, and Bootstrap 5** — no frameworks — across 8 weeks of iterative development:

- **Week 1** — Orientation & environment setup
- **Week 2** — Admin dashboard: login, profile, skills CRUD, projects CRUD
- **Week 3** — Mini CMS: activity log, public preview page, categories, pagination
- **Week 4** — Notifications system, change password, auth hardening
- **Week 5** — Production deployment, environment config, shared partials, flash messages
- **Week 6** — File cleanup, empty states, social links, resume upload, scroll animations, DB indexes
- **Week 7** — Full accessibility audit, ARIA labels, form labelling, heading hierarchy, security cleanup
- **Week 8** — Final upgrade: dark/light mode, Chart.js analytics, messages inbox, profile completeness bar, full visual redesign

## Week-by-Week Progress

### Week 1 — Orientation & Environment Setup
- Installed and configured XAMPP, VS Code, Git
- Studied the tech stack: PHP (procedural), MySQL, Bootstrap 5, vanilla JavaScript
- Set up local project folder inside `htdocs` and explored phpMyAdmin

### Week 2 — Foundation: Admin Dashboard
- Session-based login/logout (`password_hash` / `password_verify`)
- Admin sidebar, topbar, statistics cards
- Profile Management: personal info, About section, profile image upload
- Skills CRUD and Projects CRUD with search and filter

### Week 3 — Mini CMS
- `activity_log` table and activity tracking
- `preview.php` — public portfolio page with category filtering and contact form
- Recent Activities feed, User Statistics panel on dashboard
- Full Category CRUD, pagination (6 per page)

### Week 4 — Production-Style Enhancements
- Full Notifications system with bell icon, unread badge, dropdown
- `logActivity()` as single call site for activity log + notifications
- Change Password in profile with current-password verification

### Week 5 — Production Deployment & Optimization
- Environment-variable-based DB config (`includes/config.php`)
- Shared partials: `head.php`, `footer.php`, `flash.php`
- Global loading spinner and scroll-reveal animations
- Deployed to InfinityFree live hosting

### Week 6 — Final Polish & Production Hardening
- `deleteUploadedFile()` for safe file cleanup on replace/delete
- Reusable `empty_state.php` partial
- Social links (LinkedIn, GitHub, Twitter/X) on profile + preview
- Resume PDF upload with Download Resume button on public portfolio
- DB indexes for performance

### Week 7 — Final Testing, Accessibility & Documentation
- Full accessibility audit: `<label for="">`, `aria-label`, `role="progressbar"`, `aria-current`
- Semantic `<nav>` with `aria-label="Main navigation"`
- Correct heading hierarchy on preview.php
- Security cleanup: removed exposed credentials from login.php
- Full README and INTEGRATION_TESTING.md update

### Week 8 — Final Project: Portfolio Management System (COMPLETE)
- **Dark / Light Mode** — toggle button on all pages, preference saved to localStorage, anti-flash script, CSS variable token system
- **Chart.js Analytics Dashboard** — doughnut chart (Projects by Status) + horizontal bar chart (Skills Proficiency), auto-updates on theme toggle
- **Messages Inbox** — real contact form on preview.php submits via AJAX to `contact_submit.php`, stores in `messages` DB table, admin inbox with unread badge, mark-as-read, delete, pagination
- **Profile Completeness Bar** — 10-field progress bar with color (red/yellow/green), checklist, and "Complete Your Profile" CTA
- **Full Visual Redesign** — Inter Google Font, glassmorphism login page, gradient sidebar, gradient stat card icons, richer dark mode colors, custom scrollbar, animated project card overlays, sticky blurred navbar on public portfolio
- **migration_week8.sql** — `messages` table added

## Features

### Authentication & Security
- Session-based login/logout with `password_hash()` / `password_verify()`
- Change password with current-password verification and reuse prevention
- Every protected route guarded via `includes/auth.php`
- Environment-variable-based DB credentials — no real credentials in version control

### Dashboard (Week 8 Enhanced)
- Live statistics: Total Skills, Total Projects, Categories, Completed Projects
- **Chart.js doughnut** — Projects by Status breakdown
- **Chart.js horizontal bar** — Skills Proficiency chart
- Recent Projects table and Recent Activities feed
- **Profile Completeness bar** — 10-field tracker with color-coded progress
- User Statistics panel (account age, total logins, last login)

### Dark / Light Mode (Week 8)
- Toggle button in admin topbar and floating button on public portfolio
- CSS variable token system (`--bg`, `--panel-bg`, `--text`, etc.)
- Anti-flash script in `<head>` reads localStorage before first paint
- Charts re-render colors on theme change
- Preference persists across all pages and sessions

### Messages Inbox (Week 8)
- Contact form on `preview.php` submits via AJAX (no page reload)
- Spinner + success/error feedback on form submission
- Messages stored in `messages` DB table
- Admin inbox at `messages.php` with unread count badge in sidebar
- Mark as read, mark all as read, delete with confirm

### Profile Management
- Update personal info, About section, social links (LinkedIn, GitHub, Twitter/X)
- Profile image upload (JPG/PNG/WEBP, max 2MB), old image auto-deleted
- Resume upload (PDF, max 5MB) — Download Resume button on live portfolio

### Project & Skill Management
- Full CRUD for Projects, Skills, and Categories
- Project image upload with auto-delete on replace or deletion
- Search + filter by title, category, status, and technology
- Pagination (6 per page)

### Public Portfolio (preview.php)
- No-login page pulling live data from database
- Client-side category filter with animated project cards
- Image overlay on hover with Live/GitHub buttons
- Sticky navbar with backdrop blur
- Animated skill bars on scroll
- Real AJAX contact form → Messages Inbox
- Social links + Download Resume in hero section
- Dark/light mode with floating toggle button
- Scroll-reveal fade-in animations (respects `prefers-reduced-motion`)

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 (procedural), MySQLi with prepared statements |
| Database | MySQL |
| Frontend | Bootstrap 5.3, Bootstrap Icons 1.11, vanilla JavaScript |
| Charts | Chart.js 4.4 |
| Fonts | Inter (Google Fonts) |
| Hosting (production) | InfinityFree |
| Hosting (local) | XAMPP |
| Editor | Visual Studio Code |
| Version Control | Git + GitHub |

## Database Design

### Tables (8 total)

**users** — Authentication  
**profile** — Personal info, image, resume, social links  
**skills** — Skill name, proficiency %, category  
**projects** — Title, description, image, links, technology, status  
**categories** — Project categories  
**activity_log** — All tracked user actions  
**notifications** — Bell icon notifications  
**messages** — Contact form submissions (Week 8)

### messages table (Week 8)
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | |
| sender_name | VARCHAR(100) | |
| sender_email | VARCHAR(150) | |
| message | TEXT | |
| is_read | TINYINT(1) | 0 = unread |
| created_at | TIMESTAMP | |

## Folder Structure

```
portfolio_dashboard/
├── index.php, login.php, logout.php
├── dashboard.php                # Stats, charts, completeness bar, activity
├── profile.php                  # Personal info, image, resume, password
├── skills.php                   # Skills CRUD
├── projects.php                 # Projects + categories CRUD, search, pagination
├── preview.php                  # Public portfolio (no login required)
├── messages.php                 # Messages inbox (Week 8)
├── contact_submit.php           # AJAX contact form handler (Week 8)
├── notifications_read.php
├── setup.php
├── database.sql                 # Fresh-install schema
├── migration_week3.sql
├── migration_week4.sql
├── migration_week6.sql
├── migration_week8.sql          # messages table (Week 8)
├── INTEGRATION_TESTING.md
├── includes/
│   ├── config.php               # Environment-based DB credentials
│   ├── db.php, auth.php
│   ├── head.php                 # Anti-flash dark mode script (Week 8)
│   ├── footer.php, flash.php
│   ├── empty_state.php, file_helper.php
│   ├── sidebar.php              # Messages link + unread badge (Week 8)
│   ├── header.php               # Dark mode toggle button (Week 8)
│   ├── log_activity.php, notifications.php
└── assets/
    ├── css/style.css            # Full redesign with dark mode tokens (Week 8)
    ├── css/preview.css          # Full redesign with dark mode tokens (Week 8)
    ├── js/app.js                # Dark mode toggle + loading spinner (Week 8)
    ├── js/preview.js            # AJAX contact form + dark mode (Week 8)
    ├── favicon.svg
    └── uploads/
```

## Installation Guide (Local — XAMPP)

1. Copy `portfolio_dashboard` folder into XAMPP `htdocs`
2. Start Apache and MySQL in XAMPP Control Panel
3. Create database `portfolio_dashboard` in phpMyAdmin
4. Import `database.sql` then run `migration_week8.sql`
5. Visit `http://localhost/portfolio_dashboard/setup.php` to seed admin user
6. **Delete `setup.php` immediately after**
7. Log in at `http://localhost/portfolio_dashboard/login.php`
8. Change default password via Profile → Change Password

## Deployment Guide (Production — InfinityFree)

1. Create account at [infinityfree.com](https://infinityfree.com)
2. Create MySQL database and note credentials
3. Import `database.sql` then run `migration_week8.sql` via phpMyAdmin SQL tab
4. Edit `includes/config.php` on server with live credentials
5. Upload all files to `htdocs` via File Manager
6. Run `setup.php` once then delete it
7. Test all flows

## 🔒 Security Notes

- `config.php` in repo contains only local fallback values — never real credentials
- All SQL uses prepared statements with `bind_param()` — no raw interpolation
- Uploaded files validated by extension and size before `move_uploaded_file()`
- All output escaped with `htmlspecialchars()`
- `setup.php` must be deleted from live server after first use

---

## Screenshots

### Login Page
![Login](screenshots/login.png)

### Dashboard (Light Mode)
![Dashboard Light](screenshots/dashboard-light.png)

### Dashboard (Dark Mode)
![Dashboard Dark](screenshots/dashboard-dark.png)

### Projects Management
![Projects](screenshots/projects.png)

### Messages Inbox
![Messages](screenshots/messages.png)

### Public Portfolio — Hero
![Preview Hero](screenshots/preview-hero.png)

### Public Portfolio — Dark Mode
![Preview Dark](screenshots/preview-dark.png)

---

*Built by Rifaq Ajmal — BS Computer Science, UET Mardan | Codiora Full Stack Internship 2026*
