# Integration Testing Report — Portfolio Management System

**Project:** Portfolio Management Dashboard
**Live URL:** http://rifaqportfolio.gamer.free/login.php
**Tested by:** Rifaq Ajmal
**Date:** July 2026
**Environment:** InfinityFree (production) + XAMPP (local)

---

## 1. Authentication

| Test Case | Steps | Expected Result | Status |
|---|---|---|---|
| Valid login | Enter `admin` / `admin123` | Redirects to dashboard, session created | ✅ Pass |
| Invalid login | Enter wrong password | Shows "Invalid username or password" | ✅ Pass |
| Session persistence | Log in, navigate between pages | Stays logged in across all pages | ✅ Pass |
| Logout | Click Logout | Session destroyed, redirected to login | ✅ Pass |
| Unauthorized access | Visit `dashboard.php` directly while logged out (incognito) | Redirects to `login.php` | ✅ Pass |
| Change password (correct current) | Profile → Change Password → correct current password + valid new password | Success toast, password updated | ✅ Pass |
| Change password (wrong current) | Enter incorrect current password | Error toast: "Current password is incorrect" | ✅ Pass |
| Change password (mismatch) | New password ≠ confirm password | Error toast: passwords do not match | ✅ Pass |

## 2. CRUD Operations

### Skills
| Test Case | Expected Result | Status |
|---|---|---|
| Add skill | New skill appears in list with correct proficiency bar | ✅ Pass |
| Edit skill | Changes reflected immediately | ✅ Pass |
| Delete skill | Skill removed, confirmation prompt shown before deletion | ✅ Pass |

### Projects
| Test Case | Expected Result | Status |
|---|---|---|
| Add project | New project appears with image, category, technology badges | ✅ Pass |
| Edit project | Changes saved and reflected | ✅ Pass |
| Delete project | Project removed after confirmation | ✅ Pass |
| Project Details modal | Clicking a project shows full details, tech badges, links | ✅ Pass |

### Categories
| Test Case | Expected Result | Status |
|---|---|---|
| Add category | New category available in project dropdown | ✅ Pass |
| Edit category | Renamed category reflected across existing projects | ✅ Pass |
| Delete category | Associated projects fall back to "Uncategorized" | ✅ Pass |

## 3. Dashboard

| Test Case | Expected Result | Status |
|---|---|---|
| Statistics cards | Total Skills / Projects / Categories / Completed counts match DB | ✅ Pass |
| Recent Projects table | Shows last 5 projects, correct order | ✅ Pass |
| Recent Activities feed | Logs every tracked action (login, add/edit/delete, password change) | ✅ Pass |
| Notifications bell | Unread badge count accurate, dropdown lists recent notifications | ✅ Pass |
| Mark all as read | Badge clears, notifications marked read in DB | ✅ Pass |
| User Statistics panel | Account age, total logins, last login all accurate | ✅ Pass |

## 4. Image Upload

| Test Case | Expected Result | Status |
|---|---|---|
| Valid profile image (JPG/PNG/WEBP, <2MB) | Uploads and displays immediately | ✅ Pass |
| Oversized image (>2MB) | Rejected with error message | ✅ Pass |
| Invalid file type (e.g. .pdf) | Rejected: "Only JPG, JPEG, PNG, WEBP files are allowed" | ✅ Pass |
| Project image upload | Uploads and shows on project card + preview page | ✅ Pass |

## 5. Search & Filters

| Test Case | Expected Result | Status |
|---|---|---|
| Search by project title | Filters results, preserved in URL (GET params) | ✅ Pass |
| Filter by category | Only matching projects shown | ✅ Pass |
| Filter by status | Only matching projects shown | ✅ Pass |
| Filter by technology | Only matching projects shown | ✅ Pass |
| Combined filters | All filters apply together correctly | ✅ Pass |
| Pagination with filters | Filters preserved across pages (6 per page) | ✅ Pass |
| Public preview category filter | Client-side JS filtering works without page reload | ✅ Pass |

## 6. Cross-cutting / Production Checks

| Test Case | Expected Result | Status |
|---|---|---|
| Production DB connection | `config.php` connects to live InfinityFree MySQL | ✅ Pass |
| Toast notifications (flash.php) | Auto-dismiss after 4–5 seconds, correct color/icon per type | ✅ Pass |
| Global loading spinner | Appears briefly on form submit / navigation | ✅ Pass |
| Responsive layout | Sidebar collapses to icon-only on tablet (≤992px) and mobile (≤768px) | ✅ Pass |
| Favicon | Displays in browser tab across all pages | ✅ Pass |
| Public live preview (no login) | Loads correctly for unauthenticated visitors | ✅ Pass |
| setup.php removed post-deployment | File deleted from live server after admin seed | ✅ Pass |

## 7. Week 6 — Final Polish & File Management

| Test Case | Expected Result | Status |
|---|---|---|
| Empty state — no projects | Styled empty-state block with icon + "Add Project" button shown instead of plain text | ✅ Pass |
| Empty state — no skills | Styled empty-state block with icon + "Add Skill" button shown | ✅ Pass |
| Empty state — no recent activity | Styled empty-state block shown on dashboard | ✅ Pass |
| Empty state — filtered search with no matches | Distinct "No projects match your filters" message (vs. "No projects yet") | ✅ Pass |
| Replace profile image | Old image file removed from `assets/uploads/`, new image saved and displayed | ✅ Pass |
| Replace project image | Old image file removed from `assets/uploads/`, new image saved | ✅ Pass |
| Delete project | Associated image file removed from disk, not just the DB row | ✅ Pass |
| Delete project with default image | No file deletion attempted (placeholder is protected) | ✅ Pass |
| Resume upload (valid PDF, <5MB) | Uploads, "View Current Resume" button appears in Profile | ✅ Pass |
| Resume upload (non-PDF) | Rejected: "Resume must be a PDF file" | ✅ Pass |
| Resume upload (oversized) | Rejected: "Resume must be smaller than 5MB" | ✅ Pass |
| Download Resume button on public preview | Appears only when a resume has been uploaded; downloads the file | ✅ Pass |
| Social links (LinkedIn/GitHub/Twitter) | Saved from Profile, rendered as buttons on public preview only when filled in | ✅ Pass |
| Scroll-reveal animations | Dashboard panels and preview sections fade/slide in on scroll | ✅ Pass |
| Reduced motion preference | Animations disabled instantly when OS-level "reduce motion" is on | ✅ Pass |
| Database indexes applied | `migration_week6.sql` runs cleanly on an existing Week 5 database | ✅ Pass |
| Search/filter query speed | No noticeable slowdown on `projects.php` search after indexing | ✅ Pass |

---

## Summary

All core modules — authentication, CRUD operations across Skills/Projects/Categories, dashboard analytics, notifications, image/file upload and cleanup, search/filtering, social links, resume download, and scroll animations — were tested on both the local XAMPP environment and the live InfinityFree deployment. No blocking issues were found. One environment-specific issue was identified and resolved during deployment (see note below).

**Issue encountered during deployment:** Initial live-site testing returned HTTP 500 errors on all PHP pages. Root cause was isolated using a temporary diagnostic script, which revealed a MySQL access-denied error — the InfinityFree account password and the actual MySQL database password were different values, despite appearing identical in the hosting panel. Corrected `includes/config.php` with the verified MySQL password from the "MySQL Databases" panel resolved the issue immediately.
