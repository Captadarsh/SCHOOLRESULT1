# 🏫 Smart School Result & Performance Management System
**Version:** 1.0.0 | **Language:** PHP + MySQL | **Database:** school_db

---

## 📋 Table of Contents
1. [Project Overview](#overview)
2. [Requirements](#requirements)
3. [Installation Steps](#installation)
4. [Default Login Credentials](#credentials)
5. [Folder Structure](#structure)
6. [All 65 Files Explained](#files)
7. [How the System Works](#workflow)
8. [User Roles](#roles)
9. [Database Tables](#database)
10. [Pass/Fail Rule](#passfail)
11. [Grade Scale](#grades)
12. [AJAX Cascade Flow](#ajax)
13. [CSV Import Format](#csv)
14. [Troubleshooting](#troubleshooting)

---

## 1. Project Overview <a name="overview"></a>

A complete school result and performance management system built in PHP and MySQL. It handles student marks entry, automatic grade calculation, class rankings, and executive analytics dashboards for three user roles.

**Key Features:**
- 3 user roles: Admin, Teacher, Principal
- Smart 4-step AJAX marks entry (Class → Section → Student → Subjects)
- Auto-calculation of percentage, grade (A+ to F), pass/fail (33% rule), rank
- 10+ Chart.js analytics charts with live slicers and filters
- CSV/Excel bulk marks upload and export
- Printable student result cards
- Configurable grade scale and session management
- Complete audit log of all actions
- Mobile-responsive design

---

## 2. Requirements <a name="requirements"></a>

| Requirement       | Version / Notes                          |
|-------------------|------------------------------------------|
| PHP               | 7.4 or higher (8.x recommended)         |
| MySQL             | 5.7 or higher (8.x recommended)         |
| Web Server        | Apache (XAMPP recommended for local)     |
| Browser           | Chrome, Firefox, Edge (modern versions)  |
| Composer          | Optional (for PhpSpreadsheet .xlsx support) |

**Install XAMPP (recommended for local development):**
- Download from: https://www.apachefriends.org
- Includes Apache + MySQL + PHP all in one

---

## 3. Installation Steps <a name="installation"></a>

### Step 1 — Copy files
```
Copy the entire school_result folder to:
  Windows: C:\xampp\htdocs\school_result
  Mac/Linux: /Applications/XAMPP/htdocs/school_result
```

### Step 2 — Start XAMPP
```
Open XAMPP Control Panel
Click START next to Apache
Click START next to MySQL
Both must show green "Running"
```

### Step 3 — Create database
```
Open browser → go to: http://localhost/phpmyadmin
Click "New" in left sidebar
Database name: school_db
Collation: utf8mb4_unicode_ci
Click Create
```

### Step 4 — Import SQL
```
In phpMyAdmin → click school_db on left
Click the "Import" tab at the top
Click "Choose File" → select school.sql
Click "Go"
You should see: 12 tables created
```

### Step 5 — Test database connection
```
Open browser → go to: http://localhost/school_result/db.php
You should see: "Connected to school_db successfully. Tables found: 12"
If you see an error, check Step 3 and 4 again.
```

### Step 6 — Open the system
```
Go to: http://localhost/school_result/
The login page should appear.
```

### Step 7 — Optional: Install Composer libraries
For full Excel (.xlsx) import support (CSV works without this):
```bash
cd C:\xampp\htdocs\school_result
composer require phpoffice/phpspreadsheet
composer require tecnickcom/tcpdf
```

---

## 4. Default Login Credentials <a name="credentials"></a>

| Role      | Username    | Password       |
|-----------|-------------|----------------|
| Admin     | admin       | Admin@123      |
| Principal | principal   | Principal@123  |

> ⚠️ **Change these passwords immediately after first login via Admin → Manage Teachers → Reset Password**

---

## 5. Folder Structure <a name="structure"></a>

```
school_result/
│
├── index.php                    ← Entry point (redirects to login or dashboard)
├── config.php                   ← Database credentials, app settings, grade scale
├── db.php                       ← PDO database connection (singleton)
├── functions.php                ← All shared helper functions (30+ functions)
├── session.php                  ← Session management and role redirect
├── school.sql                   ← Complete database schema + seed data
│
├── auth/
│   ├── login.php                ← Login page (all 3 roles use this)
│   ├── logout.php               ← Destroys session, redirects to login
│   └── auth_check.php           ← Role-based page guard (included in every page)
│
├── admin/                       ← 17 files for administrator
│   ├── dashboard.php
│   ├── manage_classes.php / add_class.php
│   ├── manage_subjects.php / add_subject.php
│   ├── manage_exams.php / add_exam.php
│   ├── manage_teachers.php / add_teacher.php / edit_teacher.php
│   ├── manage_students.php / add_student.php / edit_student.php
│   ├── assign_teacher_class.php ← Access control (permissions)
│   ├── reports.php
│   ├── export_report.php
│   └── settings.php
│
├── teacher/                     ← 6 files for teacher
│   ├── dashboard.php
│   ├── enter_marks.php          ← 4-step AJAX marks entry
│   ├── view_marks.php
│   ├── upload_marks.php         ← CSV/Excel bulk upload
│   ├── import_csv.php           ← Processes uploaded file
│   └── my_reports.php
│
├── principal/                   ← 5 files for principal
│   ├── dashboard.php            ← 10+ charts analytics dashboard
│   ├── analytics.php            ← Deep subject/student drill-down
│   ├── topper_list.php          ← Ranked student list with medals
│   ├── performance_report.php   ← Printable executive report
│   └── export_all.php           ← Full data export
│
├── ajax/                        ← 9 JSON API endpoints
│   ├── get_sections.php
│   ├── get_students.php
│   ├── get_subjects.php
│   ├── get_analytics.php
│   ├── get_rankings.php
│   ├── get_trends.php
│   ├── get_grade_dist.php
│   ├── save_marks.php
│   └── import_excel.php
│
├── includes/                    ← 8 shared components
│   ├── header.php
│   ├── sidebar.php
│   ├── navbar.php
│   ├── footer.php
│   ├── alerts.php
│   ├── grade_calculator.php     ← GradeCalculator class
│   ├── excel_handler.php        ← ExcelHandler class (CSV import/export)
│   └── pdf_generator.php        ← PdfGenerator class (result cards)
│
├── assets/
│   ├── css/
│   │   ├── style.css            ← Global base stylesheet (833 lines)
│   │   ├── dashboard.css        ← Dashboard components (495 lines)
│   │   ├── responsive.css       ← Mobile/tablet responsive (187 lines)
│   │   └── charts.css           ← Analytics charts styles (290 lines)
│   ├── js/
│   │   ├── main.js              ← Global JS (sidebar, alerts, modals)
│   │   ├── ajax_handler.js      ← 4-step AJAX cascade + live marks calc
│   │   ├── marks_form.js        ← Form validation, keyboard nav
│   │   ├── charts.js            ← All 10 Chart.js renderers
│   │   ├── filters.js           ← Dashboard slicer/filter logic
│   │   ├── dashboard.js         ← Dashboard init, table renderers
│   │   └── export.js            ← CSV export, print, clipboard helpers
│   ├── uploads/                 ← Uploaded CSV/Excel files (auto-created)
│   └── exports/                 ← Generated export files (auto-created)
│
└── vendor/                      ← Composer packages (after optional install)
```

---

## 6. All 65 Files Explained <a name="files"></a>

### Root Files (6)
| File | Purpose |
|------|---------|
| `index.php` | Entry point — redirects logged-in users to their dashboard, others to login |
| `config.php` | All configuration: DB credentials, pass threshold (33%), roles, file paths |
| `db.php` | PDO connection to school_db — call `getPDO()` anywhere to get the connection |
| `functions.php` | 30+ shared functions: grade calc, pass/fail, hash password, flash messages, CSRF |
| `session.php` | Session start, role name helper, `destroySession()` for logout |
| `school.sql` | Creates all 12 tables and seeds: grades, sessions, admin/principal accounts, classes, subjects |

### Auth Files (3)
| File | Purpose |
|------|---------|
| `auth/login.php` | Split-panel login form with CSRF, bcrypt verify, session regeneration, audit log |
| `auth/logout.php` | Logs the logout action, destroys session, redirects with success message |
| `auth/auth_check.php` | Include at top of any page with `$ALLOWED_ROLES` to enforce role-based access |

### Admin Files (17)
| File | Purpose |
|------|---------|
| `admin/dashboard.php` | KPI banner, quick actions, system alerts, recent marks, activity log |
| `admin/manage_classes.php` | List classes + sections with inline add, edit, toggle modals |
| `admin/add_class.php` | Add class with bulk section setup using dynamic JS rows |
| `admin/manage_subjects.php` | List subjects with class filter, edit modal, delete protection |
| `admin/add_subject.php` | Add subject — loads existing subjects via AJAX when class selected |
| `admin/manage_exams.php` | List exams with type badges, session/class filter, 2 modals |
| `admin/add_exam.php` | Full exam form with type guide card and duplicate check |
| `admin/manage_teachers.php` | Teacher list with access count, marks count, reset password modal |
| `admin/add_teacher.php` | Teacher form with password strength bar, auto-username, redirects to access control |
| `admin/edit_teacher.php` | Edit profile, optional password change, account info card |
| `admin/manage_students.php` | Student list with triple filter (session/class/section) |
| `admin/add_student.php` | Student form with AJAX section loader, Save & Add Another |
| `admin/edit_student.php` | Edit student — reloads sections via AJAX, shows marks summary |
| `admin/assign_teacher_class.php` | Full access control — grant/revoke/revoke-all, clickable badges |
| `admin/reports.php` | Student results by class/section/exam — ranked table, KPIs |
| `admin/export_report.php` | Export UI + direct CSV download with 19 columns |
| `admin/settings.php` | Session management, grade scale editor, audit log viewer |

### Teacher Files (6)
| File | Purpose |
|------|---------|
| `teacher/dashboard.php` | Welcome, assigned classes, recent marks, KPI cards |
| `teacher/enter_marks.php` | 4-step wizard: Class→Section→Student→Subjects with step indicator |
| `teacher/view_marks.php` | View/search/filter entered marks with export |
| `teacher/upload_marks.php` | Drag-drop CSV upload zone with template download |
| `teacher/import_csv.php` | Processes CSV, row-by-row result table (saved/skipped/failed) |
| `teacher/my_reports.php` | Personal analytics: 6 KPIs, grade chart, subject bar chart, export |

### Principal Files (5)
| File | Purpose |
|------|---------|
| `principal/dashboard.php` | 10 charts + KPI banner + 3 tables + 5-filter slicer panel |
| `principal/analytics.php` | Subject deep-dive, student result table, class drill-down |
| `principal/topper_list.php` | Top-3 medal cards + full ranked table + CSV export |
| `principal/performance_report.php` | Executive report — printable, class/subject/teacher/grade tables |
| `principal/export_all.php` | Export with filter form, preview count, quick export buttons |

### AJAX Files (9)
| File | Returns |
|------|---------|
| `ajax/get_sections.php` | JSON sections for a class (teacher gets only assigned ones) |
| `ajax/get_students.php` | JSON students for class+section+session |
| `ajax/get_subjects.php` | JSON subjects for a class (with existing marks if student+exam given) |
| `ajax/get_analytics.php` | JSON with 10 analytics datasets for principal dashboard |
| `ajax/get_rankings.php` | JSON ranked students with grade and pass/fail |
| `ajax/get_trends.php` | JSON monthly trends (avg%, pass count, fail count) |
| `ajax/get_grade_dist.php` | JSON grade distribution with colours |
| `ajax/save_marks.php` | Saves marks, calculates grade/pass-fail, recalculates ranks, returns JSON |
| `ajax/import_excel.php` | AJAX CSV/Excel import endpoint — returns JSON result summary |

---

## 7. How the System Works <a name="workflow"></a>

### Admin workflow
```
Login → Dashboard → Add Classes/Sections/Subjects/Exams
      → Add Teachers (auto-redirect to access control)
      → Assign teacher to class+section
      → Add Students to classes
      → View Reports / Export Data
```

### Teacher workflow
```
Login → Dashboard → Enter Marks
      → Select Class (only assigned classes shown)
      → Section loads automatically via AJAX
      → Student list loads
      → Subject list loads with any existing marks
      → Enter marks → live grade/percentage shown per subject
      → Save → ranks recalculated → result summary shown
```

### Principal workflow
```
Login → Analytics Dashboard
      → 10 charts load automatically from school_db
      → Use slicers to filter by class/section/exam
      → Charts update live without page reload
      → View Topper List → Export CSV
      → Print Performance Report
```

---

## 8. User Roles <a name="roles"></a>

| Role | DB Value | Access |
|------|----------|--------|
| Admin | 1 | Full access — all CRUD, settings, all reports |
| Teacher | 2 | Only assigned classes — marks entry only |
| Principal | 3 | Read-only analytics — no data entry |

---

## 9. Database Tables <a name="database"></a>

| Table | Purpose |
|-------|---------|
| `users` | Admin, teacher, principal accounts with bcrypt passwords |
| `sessions` | Academic years (2024-2025, 2025-2026 etc.) |
| `classes` | Class 1 to Class 12 |
| `sections` | Sections A/B/C linked to each class |
| `students` | Student records linked to class+section+session |
| `subjects` | Subjects linked to a class with total/passing marks |
| `exams` | Exam events (Unit Test, Mid Term, Final etc.) |
| `marks` | Core table — one row per student/subject/exam with auto-calculated percentage |
| `teacher_class_access` | Which teacher can access which class+section+session |
| `grades_config` | Configurable grade thresholds (A+=90-100, A=80-89 etc.) |
| `attendance` | Monthly attendance for attendance vs performance chart |
| `audit_log` | Every login, mark entry, export, access change |

---

## 10. Pass/Fail Rule <a name="passfail"></a>

```
FAIL  → overall percentage < 33%
PASS  → overall percentage >= 33%
```

This threshold is set in `config.php`:
```php
define('PASS_PERCENTAGE', 33);
```

To change the pass threshold, edit this one line in `config.php`.
The rule is enforced in **two places**:
- PHP: `GradeCalculator::passOrFail()` in `includes/grade_calculator.php`
- JavaScript: `getPassFail()` in `assets/js/ajax_handler.js`

---

## 11. Grade Scale <a name="grades"></a>

Default grade scale (editable from Admin → Settings → Grade Scale):

| Grade | Min % | Max % | GPA | Remark |
|-------|-------|-------|-----|--------|
| A+    | 90    | 100   | 10.0 | Outstanding |
| A     | 80    | 89    | 9.0  | Excellent |
| B+    | 70    | 79    | 8.0  | Very Good |
| B     | 60    | 69    | 7.0  | Good |
| C     | 50    | 59    | 6.0  | Average |
| D     | 33    | 49    | 5.0  | Below Average |
| F     | 0     | 32    | 0.0  | Fail |

---

## 12. AJAX Cascade Flow <a name="ajax"></a>

The teacher marks entry form uses a 4-level cascade:

```
Teacher selects CLASS
  ↓ AJAX → ajax/get_sections.php?class_id=X
    Returns: only sections this teacher is assigned to
  ↓
Teacher selects SECTION
  ↓ AJAX → ajax/get_students.php?class_id=X&section_id=Y
    Returns: active students in this section
  ↓
Teacher selects STUDENT
  ↓ AJAX → ajax/get_subjects.php?class_id=X&student_id=Z&exam_id=E
    Returns: subjects WITH any existing marks pre-filled
  ↓
Teacher enters MARKS
  → Live preview: percentage, grade, pass/fail per subject
  → Click Save
  ↓ AJAX POST → ajax/save_marks.php
    Saves to marks table
    Recalculates ranks for class+section+exam
    Returns: result summary (total, percentage, grade, rank)
```

---

## 13. CSV Import Format <a name="csv"></a>

Download the template from Teacher → Upload Marks → Download Template.

Required columns:
```
roll_number, subject_code, marks_obtained
```

Optional columns:
```
student_name, subject_name, total_marks, remarks
```

Example CSV:
```csv
roll_number,student_name,subject_code,subject_name,marks_obtained,total_marks,remarks
2024001,Rahul Sharma,MATH10,Mathematics,78,100,Good performance
2024001,Rahul Sharma,ENG10,English,82,100,
2024002,Priya Patel,MATH10,Mathematics,91,100,Excellent
```

Rules:
- First row must be the header
- `roll_number` must match exactly what is in the database
- `subject_code` must match a subject assigned to the class
- `marks_obtained` must be a number between 0 and total_marks
- Blank rows are skipped automatically

---

## 14. Troubleshooting <a name="troubleshooting"></a>

### "Database Connection Failed"
- Check XAMPP: both Apache and MySQL must be green/running
- Confirm database `school_db` exists in phpMyAdmin
- Confirm `school.sql` was imported (12 tables should exist)
- Check `config.php` — DB_USER should be `root`, DB_PASS should be `''` (blank)

### Login page shows but login fails
- Check that `school.sql` was imported — it contains the seeded admin account
- Username is `admin` (lowercase), password is `Admin@123` (capital A)

### Sections don't load when teacher selects a class
- Check that the teacher has been assigned to that class via Admin → Access Control
- Open browser console (F12) — check for AJAX errors
- Confirm `ajax/get_sections.php` exists

### Charts don't show on principal dashboard
- Check that marks have been entered (charts only show when data exists)
- Check browser console for JavaScript errors
- Confirm Chart.js is loading from CDN (requires internet connection)

### CSV import shows all rows skipped
- Roll numbers in CSV must exactly match roll numbers in the database
- Subject codes must match subjects assigned to the selected class
- Select the correct class and section before uploading

### PHP errors showing on screen
- This is normal in development — errors are enabled in `config.php`
- For production: set `ini_set('display_errors', 0)` in `config.php`

---

## Security Notes

- All passwords stored as bcrypt hashes (never plain text)
- CSRF tokens on all POST forms
- Session regeneration on every login
- Role-based access enforced on every page via `auth_check.php`
- All user input sanitized with `sanitize()` before display
- Parameterized PDO queries prevent SQL injection
- Teacher access to classes enforced at both PHP and SQL level

---

## File Change Log

| Phase | Files Added | What was built |
|-------|------------|----------------|
| 1 | 6 | Database schema + connection + config + functions |
| 2 | 5 | Login + logout + auth guard + base CSS |
| 3 | 9 | Full UI shell: header, sidebar, navbar, footer, alerts, CSS, JS |
| 4 | 6 | Admin: Classes, Sections, Subjects, Exams — full CRUD |
| 5 | 7 | Admin: Teachers, Students, Access Control |
| 6 | 14 | Teacher panel: AJAX marks entry, upload, reports, all endpoints |
| 7 | 13 | Principal: 10-chart analytics dashboard, topper list, reports, export |
| 8 | 7 | Admin dashboard (full), reports, export, settings, PDF, export.js, Excel import |
| **Total** | **65** | **Complete system** |

---

## Support

- Database: `school_db` (never rename this)
- PHP: All files use `ROOT` constant to build paths correctly
- URLs: Update `APP_URL` in `config.php` if deploying to a live server
- Timezone: Set to `Asia/Kolkata` in `config.php` — change if needed

---

*SmartSchool Result System v1.0.0 — Built with PHP + MySQL + Chart.js*
