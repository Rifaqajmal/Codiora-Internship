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

## 8. Week 7 — Accessibility, Security & Final System Testing

### Complete System Testing (End-to-End User Flow)

| Test Case | Expected Result | Status |
|---|---|---|
| Login with valid credentials | Redirects to dashboard, session active | ✅ Pass |
| Dashboard loads all panels | Stats, Recent Projects, Activities, Skills by Category, User Statistics all render | ✅ Pass |
| Profile update (personal info) | Changes saved and reflected immediately | ✅ Pass |
| Profile update (About section) | Changes saved and visible on public preview | ✅ Pass |
| Add a new skill | Skill appears in list with correct proficiency bar | ✅ Pass |
| Edit a skill | Updated values shown immediately | ✅ Pass |
| Delete a skill | Skill removed after confirmation prompt | ✅ Pass |
| Add a new project | Project card appears with image, category, status badge | ✅ Pass |
| Edit a project | Updated details reflected on card and public preview | ✅ Pass |
| Delete a project | Project removed, image deleted from disk | ✅ Pass |
| Search projects | Results filtered correctly, URL params preserved | ✅ Pass |
| Logout | Session destroyed, redirected to login | ✅ Pass |
| Unauthorized route access | `dashboard.php` without session redirects to `login.php` | ✅ Pass |

### Accessibility — Form Labels

| Test Case | Expected Result | Status |
|---|---|---|
| `login.php` — username + password inputs | Each input has matching `<label for="">` | ✅ Pass |
| `profile.php` — Personal Information form | All 8 fields (name, title, email, phone, location, LinkedIn, GitHub, Twitter) have `for`/`id` pairs | ✅ Pass |
| `profile.php` — About textarea | `<label for="about_text">` present | ✅ Pass |
| `profile.php` — Profile image file input | `<label for="profile_image_file">` present | ✅ Pass |
| `profile.php` — Resume file input | `<label for="resume_file_input">` present | ✅ Pass |
| `profile.php` — Change Password form | All 3 password fields have `for`/`id` pairs | ✅ Pass |
| `skills.php` — Add Skill modal | Skill Name, Category, Proficiency all have `for`/`id` pairs | ✅ Pass |
| `skills.php` — Edit Skill modal | All fields have `for`/`id` pairs | ✅ Pass |
| `projects.php` — Search/filter form | All 4 filter fields have `for`/`id` pairs | ✅ Pass |
| `projects.php` — Add Project modal | All 8 fields have `for`/`id` pairs | ✅ Pass |
| `projects.php` — Edit Project modal | All fields have `for`/`id` pairs | ✅ Pass |
| `projects.php` — Category management modal | Each inline category input has unique `for`/`id` | ✅ Pass |
| `preview.php` — Contact form | Name, Email, Message all have `for`/`id` pairs | ✅ Pass |

### Accessibility — ARIA & Semantics

| Test Case | Expected Result | Status |
|---|---|---|
| Sidebar nav wrapped in `<nav>` | `<nav aria-label="Main navigation">` present | ✅ Pass |
| Active sidebar link | `aria-current="page"` on current page link | ✅ Pass |
| Notification bell button | `aria-label="Notifications (N unread)"` dynamically set | ✅ Pass |
| Notification badge | `aria-hidden="true"` (count is already in button label) | ✅ Pass |
| All decorative icons | `aria-hidden="true"` on all `<i class="bi ...">` icons | ✅ Pass |
| Skill bars — admin pages | `role="progressbar"` with `aria-valuenow/min/max` and `aria-label` including % | ✅ Pass |
| Skill bars — public preview | Same ARIA attributes present | ✅ Pass |
| Icon-only buttons (edit, delete, view) | Each has descriptive `aria-label` e.g. "Edit PHP", "Delete MySQL" | ✅ Pass |
| Project details modal image | `alt` attribute set dynamically via JS when modal opens | ✅ Pass |
| Skip-to-content link | `<a href="#main-content">` present on `preview.php` | ✅ Pass |
| Section landmarks on preview.php | All sections use `aria-labelledby` pointing to their heading | ✅ Pass |

### Accessibility — Heading Hierarchy

| Test Case | Expected Result | Status |
|---|---|---|
| `preview.php` — top-level heading | Single `<h1>` for full name | ✅ Pass |
| `preview.php` — section headings | About, Skills, Projects, Contact use `<h2>` | ✅ Pass |
| `preview.php` — skill category headings | Each category uses `<h3>` (was incorrectly `<h5>` before) | ✅ Pass |
| `preview.php` — project card titles | Each project uses `<h3 class="h6">` | ✅ Pass |
| Admin pages — panel headings | Consistent `<h5>` for panel titles throughout | ✅ Pass |

### Security Checks

| Test Case | Expected Result | Status |
|---|---|---|
| `login.php` default credentials hint | "Default: admin / admin123" text removed from page | ✅ Pass |
| `README.md` credentials exposure | No working credentials anywhere in README | ✅ Pass |
| Prepared statements | All SQL queries use `bind_param()` — verified across all files | ✅ Pass |
| `htmlspecialchars()` on all output | No raw user data rendered to HTML | ✅ Pass |
| File upload validation | Extension + size checked before `move_uploaded_file()` | ✅ Pass |
| `setup.php` on live server | File absent from production server | ✅ Pass |

### Meta Tags (preview.php)

| Test Case | Expected Result | Status |
|---|---|---|
| Open Graph title + description | `og:title` and `og:description` tags present | ✅ Pass |
| Open Graph image | `og:image` populated with profile image URL | ✅ Pass |
| Twitter Card type | `twitter:card` = `summary_large_image` present | ✅ Pass |
| Twitter title + description + image | All 3 Twitter Card tags populated | ✅ Pass |
| Meta description | `<meta name="description">` present | ✅ Pass |

### Responsive Testing

| Device / Breakpoint | Test Area | Status |
|---|---|---|
| Mobile (375px) | Login page centered, full-width card | ✅ Pass |
| Mobile (375px) | Admin sidebar collapses, topbar visible | ✅ Pass |
| Mobile (375px) | Project cards stack to single column | ✅ Pass |
| Mobile (375px) | Public portfolio hero, skills, projects readable | ✅ Pass |
| Tablet (768px) | Project cards in 2-column grid | ✅ Pass |
| Tablet (768px) | Profile page 2-column layout intact | ✅ Pass |
| Laptop (1024px) | Full sidebar + main content layout | ✅ Pass |
| Desktop (1440px) | No overflow, consistent spacing | ✅ Pass |

---

## Final Summary

All features tested across 7 weeks — authentication, full CRUD (skills, projects, categories), dashboard analytics, notifications, file upload and cleanup, search and filtering, social links, resume download, scroll animations, and a complete accessibility pass — were verified on both the local XAMPP environment and the live InfinityFree deployment. No blocking issues found in Week 7 testing.

**Total test cases:** 80+
**Status:** All Pass ✅
