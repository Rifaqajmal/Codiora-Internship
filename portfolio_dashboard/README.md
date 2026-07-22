# Portfolio Management Dashboard

Codiora Full Stack Development Internship — 7-week project.

A full-stack PHP/MySQL admin dashboard for managing a personal developer portfolio — skills, projects, profile, and a public live preview page — built iteratively over 7 weeks and deployed to production.

## 🔗 Live Demo

| | |
|---|---|
| **Admin Dashboard** | http://rifaqportfolio.gamer.free/login.php |
| **Public Portfolio** | http://rifaqportfolio.gamer.free/preview.php |
| **GitHub Repository** | https://github.com/Rifaqajmal/Codiora-Internship |
| **Hosting** | InfinityFree (free PHP + MySQL) |

## Project Overview

Built entirely with **plain PHP 8.3, MySQL, and Bootstrap 5** — no frameworks — across 7 weeks of iterative development:

- **Week 2** — Admin dashboard: login, profile, skills CRUD, projects CRUD
- **Week 3** — Mini CMS: activity log, public preview page, categories, pagination
- **Week 4** — Notifications system, change password, auth hardening
- **Week 5** — Production deployment, environment config, shared partials, flash messages
- **Week 6** — File cleanup, empty states, social links, resume upload, scroll animations, DB indexes
- **Week 7** — Accessibility audit, full form labelling, ARIA attributes, heading hierarchy, security cleanup

## Features

### Authentication & Security
- Session-based login/logout with `password_hash()` / `password_verify()`
- Change password with current-password verification and reuse prevention
- Every protected route guarded via `includes/auth.php`
- Environment-variable-based DB credentials — no real credentials in version control
- Public preview page intentionally requires no login

### Dashboard
- Live statistics: Total Skills, Total Projects, Categories, Completed Projects
- Recent Projects table and Recent Activities feed
- User Statistics panel (account age, total logins, last login)
- Real-time notification bell with unread badge, auto-generated from every tracked action

### Profile Management
- Update personal info (name, title, email, phone, location) and About section
- Profile image upload — validated (JPG/PNG/WEBP, max 2MB), old image auto-deleted on replace
- Resume upload (PDF, max 5MB) — powers the Download Resume button on the public portfolio
- Social links: LinkedIn, GitHub, Twitter/X — rendered as buttons on the live preview

### Project & Skill Management
- Full CRUD for Projects, Skills, and Categories
- Project image upload with auto-delete on replace or project deletion
- Project Details modal, status badges, technology tags
- Pagination (6 per page) with search + filter by title, category, status, and technology

### Public Portfolio (preview.php)
- No-login page pulling live data from the database
- Client-side category filter, validated contact form
- Social links and Download Resume button in the hero section
- Scroll-reveal fade-in animations (respects `prefers-reduced-motion`)
- Open Graph + Twitter Card meta tags for social link previews

### Accessibility (Week 7)
- All form inputs have associated `<label for="">` attributes
- ARIA roles: `role="progressbar"` on skill bars, `aria-label` on all icon-only buttons
- Semantic navigation: `<nav aria-label="Main navigation">` with `aria-current="page"`
- Notification bell button has dynamic `aria-label` showing unread count
- Correct heading hierarchy on public portfolio (h1 → h2 → h3)
- `aria-hidden="true"` on all decorative icons throughout

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 (procedural), MySQLi with prepared statements |
| Database | MySQL |
| Frontend | Bootstrap 5.3, Bootstrap Icons 1.11, vanilla JavaScript |
| Hosting (production) | InfinityFree |
| Hosting (local) | XAMPP |

## Database Design

### Entity Relationship Summary

```
users ──< profile
users ──< skills
users ──< projects >── categories
users ──< categories
users ──< activity_log
users ──< notifications
```

### Tables

**users**
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| username | VARCHAR(50) UNIQUE | |
| password | VARCHAR(255) | bcrypt hash |
| created_at | DATETIME | |

**profile**
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | |
| full_name | VARCHAR(100) | |
| job_title | VARCHAR(100) | |
| email | VARCHAR(100) | |
| phone | VARCHAR(30) | |
| location | VARCHAR(100) | |
| about_text | TEXT | |
| profile_image | VARCHAR(255) | filename in assets/uploads/ |
| linkedin_url | VARCHAR(255) | added Week 6 |
| github_url | VARCHAR(255) | added Week 6 |
| twitter_url | VARCHAR(255) | added Week 6 |
| resume_file | VARCHAR(255) | PDF filename, added Week 6 |

**skills**
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | |
| skill_name | VARCHAR(100) | Indexed |
| proficiency | INT | 0–100 |
| category | VARCHAR(100) | Indexed |

**projects**
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | |
| title | VARCHAR(150) | Indexed |
| description | TEXT | |
| category_id | INT FK → categories.id | NULL = Uncategorized |
| project_link | VARCHAR(255) | |
| github_link | VARCHAR(255) | |
| project_image | VARCHAR(255) | filename in assets/uploads/ |
| technology | VARCHAR(255) | comma-separated, Indexed |
| status | ENUM('Completed','In Progress','Planned') | |
| created_at | DATETIME | |

**categories**
| Column | Type |
|---|---|
| id | INT PK AUTO_INCREMENT |
| user_id | INT FK → users.id |
| category_name | VARCHAR(100) |

**activity_log**
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | Indexed (with created_at) |
| activity_type | VARCHAR(50) | e.g. login, project_added |
| description | VARCHAR(255) | |
| created_at | DATETIME | |

**notifications**
| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT FK → users.id | Indexed (with is_read) |
| message | VARCHAR(255) | |
| is_read | TINYINT(1) | 0 = unread |
| created_at | DATETIME | |

## CRUD Operations Reference

All operations are form-based (POST) with prepared statements. There are no REST API endpoints — this is a traditional server-rendered PHP application.

| Page | Operation | Method | Action |
|---|---|---|---|
| login.php | Authenticate | POST | Verifies bcrypt password, starts session |
| profile.php | Update info | POST | Updates name, title, email, phone, location, social links |
| profile.php | Update about | POST | Updates about_text |
| profile.php | Upload image | POST (multipart) | Validates + moves file, deletes old |
| profile.php | Upload resume | POST (multipart) | Validates PDF, deletes old |
| profile.php | Change password | POST | Verifies current, hashes new |
| skills.php | Add skill | POST | Inserts row, logs activity |
| skills.php | Edit skill | POST | Updates row, logs activity |
| skills.php | Delete skill | POST | Deletes row, logs activity |
| projects.php | Add project | POST (multipart) | Inserts row + optional image |
| projects.php | Edit project | POST (multipart) | Updates row, replaces image if provided |
| projects.php | Delete project | POST | Deletes row + image file from disk |
| projects.php | Add category | POST | Inserts category row |
| projects.php | Edit category | POST | Updates category name |
| projects.php | Delete category | POST | Deletes category (projects become Uncategorized) |
| projects.php | Search/filter | GET | Filters by title, category, status, technology |
| preview.php | View portfolio | GET | Public read-only, no auth |
| notifications_read.php | Mark all read | GET | Sets is_read = 1 for all user notifications |

## Folder Structure

```
portfolio_dashboard/
├── index.php                    # Redirects to dashboard or login
├── login.php / logout.php
├── dashboard.php                # Stats, recent projects, activity feed
├── profile.php                  # Personal info, image, resume, password
├── skills.php                   # Skills CRUD
├── projects.php                 # Projects + categories CRUD, search, pagination
├── preview.php                  # Public portfolio (no login required)
├── notifications_read.php       # Marks all notifications as read
├── setup.php                    # One-time admin seed (delete after use)
├── database.sql                 # Fresh-install schema (all weeks included)
├── migration_week3.sql          # activity_log + technology column
├── migration_week4.sql          # notifications table
├── migration_week6.sql          # Social links, resume column, DB indexes
├── INTEGRATION_TESTING.md       # Full test report
├── includes/
│   ├── config.php               # Environment-based DB credentials (local fallbacks only in repo)
│   ├── db.php                   # MySQLi connection
│   ├── auth.php                 # Session guard (redirects to login if not authenticated)
│   ├── head.php                 # Shared <head> partial
│   ├── footer.php               # Shared closing scripts + Bootstrap JS
│   ├── flash.php                # Auto-dismissing toast messages
│   ├── empty_state.php          # Reusable "no data yet" UI block
│   ├── file_helper.php          # deleteUploadedFile() — removes old uploads from disk
│   ├── sidebar.php              # Admin sidebar navigation
│   ├── header.php               # Topbar with notification bell
│   ├── log_activity.php         # Writes to activity_log AND notifications in one call
│   └── notifications.php        # Notification insertion helper
└── assets/
    ├── css/style.css            # Admin dashboard styles
    ├── css/preview.css          # Public portfolio styles + animations
    ├── js/app.js                # Loading spinner + scroll-reveal (admin)
    ├── js/preview.js            # Category filter, contact form validation, scroll-reveal
    ├── favicon.svg
    └── uploads/                 # User-uploaded images and PDFs (gitignored except .gitkeep)
```

## Installation Guide (Local — XAMPP)

1. Copy the `portfolio_dashboard` folder into your XAMPP `htdocs` directory.
2. Start Apache and MySQL in XAMPP Control Panel.
3. Open **phpMyAdmin** and create a database named `portfolio_dashboard`.
4. Import `database.sql` — this creates all tables with the latest schema (no need to run migrations on a fresh install).
5. Visit `http://localhost/portfolio_dashboard/setup.php` to seed the admin user.
6. **Immediately delete `setup.php`** after the first visit.
7. Log in at `http://localhost/portfolio_dashboard/login.php`.
8. Change the default password via Profile → Change Password.

> **For existing local databases:** Run `migration_week3.sql`, `migration_week4.sql`, and `migration_week6.sql` in order instead of re-importing `database.sql`.

## Deployment Guide (Production — InfinityFree)

1. Create a free account at [infinityfree.com](https://infinityfree.com) and add a hosting account.
2. In the hosting control panel, create a MySQL database and note the **host**, **database name**, **username**, and **password** (these are separate from your InfinityFree account password).
3. Open **phpMyAdmin** via the control panel, select your database, go to the SQL tab, and import `database.sql`.
4. Edit `includes/config.php` on the server with your live MySQL credentials. **Do not commit this file with real credentials.**
5. Upload all project files to the `htdocs` folder via File Manager or FTP.
6. Visit `yoursite.com/setup.php` once to create the admin user, then **delete setup.php immediately**.
7. Log in and change the default password right away.
8. Test all flows using `INTEGRATION_TESTING.md` as a checklist.

## 🔒 Security Notes

- `includes/config.php` in this repository contains only local fallback values. Real production credentials live only on the server and are never committed.
- `setup.php` must be deleted from the live server after first use — it is not present on the production server.
- All SQL queries use prepared statements with `bind_param()` — no raw string interpolation anywhere.
- Uploaded files are validated by extension and size before being moved to `assets/uploads/`.
- All user-supplied output is escaped with `htmlspecialchars()` before rendering.

## Screenshots

Screenshots covering dashboard, profile management, skills, projects, public portfolio (mobile + desktop), and notifications are included in the LinkedIn post submitted for Week 5/6 evaluation.

---

*Built by Rifaq Ajmal — BS Computer Science, UET Mardan | Codiora Full Stack Internship 2025*
