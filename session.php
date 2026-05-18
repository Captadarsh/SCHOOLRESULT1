<?php
// ============================================================
//  SMART SCHOOL RESULT & PERFORMANCE MANAGEMENT SYSTEM
//  File    : session.php
//  Purpose : Start/validate session. Used at top of every page.
//  Phase   : 1 — Foundation
//
//  Usage:
//    require_once ROOT . '/session.php';
//    requireLogin();               // Redirect to login if not logged in
//    requireRole(ROLE_ADMIN);      // Only admin can proceed
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// config.php already starts the session.
// This file simply provides a convenient single include point.

/**
 * Get the dashboard URL for the current user's role.
 */
function getDashboardUrl(): string
{
    $role = currentUserRole();
    switch ($role) {
        case ROLE_ADMIN:     return APP_URL . '/admin/dashboard.php';
        case ROLE_TEACHER:   return APP_URL . '/teacher/dashboard.php';
        case ROLE_PRINCIPAL: return APP_URL . '/principal/dashboard.php';
        default:             return APP_URL . '/auth/login.php';
    }
}

/**
 * Get a human-readable role name.
 */
function getRoleName(int $role): string
{
    switch ($role) {
        case ROLE_ADMIN:     return 'Administrator';
        case ROLE_TEACHER:   return 'Teacher';
        case ROLE_PRINCIPAL: return 'Principal';
        default:             return 'Unknown';
    }
}

/**
 * Destroy session (used on logout).
 */
function destroySession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
