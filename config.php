<?php
// ============================================================
//  SMART SCHOOL RESULT & PERFORMANCE MANAGEMENT SYSTEM
//  File     : config.php
//  Purpose  : Central configuration — DB credentials, app settings
//  Phase    : 1 — Foundation
//  IMPORTANT: Database name is ALWAYS school_db — never change it.
// ============================================================

// -----------------------------------------------------------
// DATABASE CONFIGURATION
// -----------------------------------------------------------
define('DB_HOST',     'localhost');
define('DB_NAME',     'school_db');     // << NEVER CHANGE THIS
define('DB_USER',     'root');          // Default XAMPP user
define('DB_PASS',     '');              // Default XAMPP password (blank)
define('DB_CHARSET',  'utf8mb4');

// -----------------------------------------------------------
// APPLICATION SETTINGS
// -----------------------------------------------------------
define('APP_NAME',    'SmartSchool Result System');
define('APP_VERSION', '1.0.0');
define('APP_URL',     'http://localhost/school_result');  // Change if deployed
define('APP_LOGO',    APP_URL . '/assets/img/logo.png');

// -----------------------------------------------------------
// PASS / FAIL RULE
// A student FAILS if overall percentage < 33%
// A student PASSES if overall percentage >= 33%
// -----------------------------------------------------------
define('PASS_PERCENTAGE', 33);   // Minimum % to pass overall

// -----------------------------------------------------------
// SESSION SECURITY
// -----------------------------------------------------------
define('SESSION_LIFETIME', 7200);   // 2 hours in seconds
define('SESSION_NAME',     'school_sess');

// -----------------------------------------------------------
// USER ROLES (match users.role column)
// -----------------------------------------------------------
define('ROLE_ADMIN',      1);
define('ROLE_TEACHER',    2);
define('ROLE_PRINCIPAL',  3);

// -----------------------------------------------------------
// FILE UPLOAD SETTINGS
// -----------------------------------------------------------
define('UPLOAD_DIR',      __DIR__ . '/assets/uploads/');
define('EXPORT_DIR',      __DIR__ . '/assets/exports/');
define('MAX_FILE_SIZE',   5 * 1024 * 1024);   // 5 MB
define('ALLOWED_EXCEL',   ['xlsx', 'xls', 'csv']);

// -----------------------------------------------------------
// GRADE SCALE (used as fallback if grades_config table is empty)
// -----------------------------------------------------------
define('GRADE_SCALE', [
    ['label' => 'A+', 'min' => 90,  'max' => 100, 'gpa' => 10.0, 'remark' => 'Outstanding'],
    ['label' => 'A',  'min' => 80,  'max' => 89,  'gpa' => 9.0,  'remark' => 'Excellent'],
    ['label' => 'B+', 'min' => 70,  'max' => 79,  'gpa' => 8.0,  'remark' => 'Very Good'],
    ['label' => 'B',  'min' => 60,  'max' => 69,  'gpa' => 7.0,  'remark' => 'Good'],
    ['label' => 'C',  'min' => 50,  'max' => 59,  'gpa' => 6.0,  'remark' => 'Average'],
    ['label' => 'D',  'min' => 33,  'max' => 49,  'gpa' => 5.0,  'remark' => 'Below Average'],
    ['label' => 'F',  'min' => 0,   'max' => 32,  'gpa' => 0.0,  'remark' => 'Fail'],
]);

// -----------------------------------------------------------
// TIMEZONE
// -----------------------------------------------------------
date_default_timezone_set('Asia/Kolkata');

// -----------------------------------------------------------
// ERROR REPORTING
// Set to 0 on production server
// -----------------------------------------------------------
ini_set('display_errors', 1);
error_reporting(E_ALL);

// -----------------------------------------------------------
// START SESSION SECURELY
// -----------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => false,          // Set true on HTTPS
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}
