<?php
// ============================================================
//  SMART SCHOOL RESULT & PERFORMANCE MANAGEMENT SYSTEM
//  File    : functions.php
//  Purpose : Shared helper functions used across all phases
//  Phase   : 1 — Foundation
// ============================================================

require_once __DIR__ . '/db.php';

// ============================================================
// 1. GRADE & RESULT CALCULATION
// ============================================================

/**
 * Calculate grade label from percentage.
 * Reads from grades_config table; falls back to config.php constant.
 */
function calculateGrade(float $percentage): string
{
    $pdo  = getPDO();
    $stmt = $pdo->query("SELECT grade_label, min_percent, max_percent
                         FROM grades_config
                         ORDER BY min_percent DESC");
    $grades = $stmt->fetchAll();

    if (!empty($grades)) {
        foreach ($grades as $g) {
            if ($percentage >= $g['min_percent'] && $percentage <= $g['max_percent']) {
                return $g['grade_label'];
            }
        }
    }

    // Fallback to config constant
    foreach (GRADE_SCALE as $g) {
        if ($percentage >= $g['min'] && $percentage <= $g['max']) {
            return $g['label'];
        }
    }
    return 'F';
}

/**
 * Determine Pass or Fail based on overall percentage.
 * Rule: Fail if percentage < 33, Pass if >= 33.
 */
function calculatePassFail(float $percentage): string
{
    return ($percentage >= PASS_PERCENTAGE) ? 'Pass' : 'Fail';
}

/**
 * Calculate percentage from obtained and total marks.
 */
function calculatePercentage(float $obtained, float $total): float
{
    if ($total <= 0) return 0.0;
    return round(($obtained / $total) * 100, 2);
}

/**
 * Get grade GPA points for a given grade label.
 */
function getGradeGPA(string $gradeLabel): float
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare("SELECT gpa_points FROM grades_config WHERE grade_label = ?");
    $stmt->execute([$gradeLabel]);
    $row = $stmt->fetch();
    return $row ? (float) $row['gpa_points'] : 0.0;
}

/**
 * Calculate and update rank_position for all students
 * in the same class, section, exam, session combination.
 * Called after marks are saved.
 */
function recalculateRanks(int $classId, int $sectionId, int $examId, int $sessionId): void
{
    $pdo = getPDO();

    // Get total obtained marks per student for this exam
    $sql = "SELECT student_id, SUM(marks_obtained) AS total_obtained
            FROM marks
            WHERE class_id   = :class_id
              AND section_id = :section_id
              AND exam_id    = :exam_id
              AND session_id = :session_id
            GROUP BY student_id
            ORDER BY total_obtained DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':class_id'   => $classId,
        ':section_id' => $sectionId,
        ':exam_id'    => $examId,
        ':session_id' => $sessionId,
    ]);
    $students = $stmt->fetchAll();

    $rank = 1;
    foreach ($students as $s) {
        $upd = $pdo->prepare("UPDATE marks SET rank_position = ?
                              WHERE student_id = ?
                                AND exam_id    = ?
                                AND class_id   = ?
                                AND section_id = ?
                                AND session_id = ?");
        $upd->execute([
            $rank,
            $s['student_id'],
            $examId,
            $classId,
            $sectionId,
            $sessionId,
        ]);
        $rank++;
    }
}

// ============================================================
// 2. AUTHENTICATION HELPERS
// ============================================================

/**
 * Hash a plain-text password.
 */
function hashPassword(string $plain): string
{
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a plain password against a stored hash.
 */
function verifyPassword(string $plain, string $hash): bool
{
    return password_verify($plain, $hash);
}

/**
 * Check if a user is logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current logged-in user's role.
 */
function currentUserRole(): ?int
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user's ID.
 */
function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's full name.
 */
function currentUserName(): string
{
    return $_SESSION['user_name'] ?? 'Unknown';
}

/**
 * Redirect to login if not authenticated.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require a specific role; redirect with error if unauthorized.
 */
function requireRole(int ...$allowedRoles): void
{
    requireLogin();
    if (!in_array(currentUserRole(), $allowedRoles, true)) {
        $_SESSION['flash_error'] = 'You do not have permission to access that page.';
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

// ============================================================
// 3. INPUT SANITIZATION & VALIDATION
// ============================================================

/**
 * Sanitize a string for safe display (prevent XSS).
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate that a value is a positive integer.
 */
function isPositiveInt($value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value > 0;
}

/**
 * Validate marks: must be numeric, >= 0 and <= total.
 */
function isValidMarks($marks, float $total): bool
{
    if (!is_numeric($marks)) return false;
    $m = (float) $marks;
    return $m >= 0 && $m <= $total;
}

// ============================================================
// 4. DATABASE QUERY HELPERS
// ============================================================

/**
 * Fetch a single row by ID from any table.
 */
function fetchById(string $table, int $id): ?array
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE id = ?");
    $stmt->execute([$id]);
    $row  = $stmt->fetch();
    return $row ?: null;
}

/**
 * Fetch all active rows from a table, ordered by a column.
 */
function fetchAll(string $table, string $orderBy = 'id', string $direction = 'ASC'): array
{
    $pdo  = getPDO();
    $stmt = $pdo->query("SELECT * FROM `{$table}` WHERE is_active = 1
                         ORDER BY `{$orderBy}` {$direction}");
    return $stmt->fetchAll();
}

/**
 * Get current active session ID from sessions table.
 */
function getCurrentSessionId(): ?int
{
    $pdo  = getPDO();
    $stmt = $pdo->query("SELECT id FROM sessions WHERE is_current = 1 LIMIT 1");
    $row  = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

/**
 * Get current active session name.
 */
function getCurrentSessionName(): string
{
    $pdo  = getPDO();
    $stmt = $pdo->query("SELECT session_name FROM sessions WHERE is_current = 1 LIMIT 1");
    $row  = $stmt->fetch();
    return $row ? $row['session_name'] : 'N/A';
}

// ============================================================
// 5. FLASH MESSAGE HELPERS
// ============================================================

/**
 * Set a flash success message (shown once, then cleared).
 */
function setFlashSuccess(string $message): void
{
    $_SESSION['flash_success'] = $message;
}

/**
 * Set a flash error message.
 */
function setFlashError(string $message): void
{
    $_SESSION['flash_error'] = $message;
}

/**
 * Retrieve and clear flash messages.
 * Returns ['success' => '...', 'error' => '...'] or empty strings.
 */
function getFlashMessages(): array
{
    $msgs = [
        'success' => $_SESSION['flash_success'] ?? '',
        'error'   => $_SESSION['flash_error']   ?? '',
    ];
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    return $msgs;
}

// ============================================================
// 6. AUDIT LOG HELPER
// ============================================================

/**
 * Write an entry to the audit_log table.
 */
function auditLog(string $action, string $tableName = '', int $recordId = 0): void
{
    try {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, ip_address)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            currentUserId(),
            $action,
            $tableName,
            $recordId ?: null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Exception $e) {
        // Silently fail — audit log should never break the main flow
    }
}

// ============================================================
// 7. FORMATTING HELPERS
// ============================================================

/**
 * Format a number to 2 decimal places with a % sign.
 */
function formatPercent(float $value): string
{
    return number_format($value, 2) . '%';
}

/**
 * Return a Bootstrap badge class based on pass/fail status.
 */
function getStatusBadge(string $status): string
{
    return $status === 'Pass'
        ? '<span class="badge bg-success">Pass</span>'
        : '<span class="badge bg-danger">Fail</span>';
}

/**
 * Return a Bootstrap badge class for a grade label.
 */
function getGradeBadge(string $grade): string
{
    $map = [
        'A+' => 'success',
        'A'  => 'success',
        'B+' => 'primary',
        'B'  => 'primary',
        'C'  => 'warning',
        'D'  => 'secondary',
        'F'  => 'danger',
    ];
    $color = $map[$grade] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . sanitize($grade) . '</span>';
}

/**
 * Generate a CSRF token and store in session.
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token.
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
