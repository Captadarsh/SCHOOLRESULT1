<?php
// ============================================================
//  File    : index.php
//  Purpose : Root entry point — redirect to dashboard or login
//  Phase   : 2 — Authentication
// ============================================================

define('ROOT', __DIR__);
require_once ROOT . '/session.php';

if (isLoggedIn()) {
    // Already logged in — send to the right dashboard
    redirect(getDashboardUrl());
} else {
    // Not logged in — go to login page
    redirect(APP_URL . '/auth/login.php');
}
