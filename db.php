<?php
// ============================================================
//  SMART SCHOOL RESULT & PERFORMANCE MANAGEMENT SYSTEM
//  File    : db.php
//  Purpose : PDO database connection — singleton pattern
//  Phase   : 1 — Foundation
//  Usage   : require_once 'db.php';  then use $pdo
// ============================================================

require_once __DIR__ . '/config.php';

/**
 * Returns a single shared PDO instance for school_db.
 * All PHP files call getPDO() — never create a new PDO directly.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,       // Always school_db
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Show a clean error — never expose credentials
            http_response_code(500);
            die('
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Database Error</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f8f9fa;display:flex;
         align-items:center;justify-content:center;height:100vh;margin:0}
    .box{background:#fff;border:1px solid #dee2e6;border-radius:8px;
         padding:40px;text-align:center;max-width:480px}
    h2{color:#dc3545;margin:0 0 12px}
    p{color:#6c757d;font-size:14px;line-height:1.6}
    code{background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:13px}
  </style>
</head>
<body>
  <div class="box">
    <h2>&#9888; Database Connection Failed</h2>
    <p>Could not connect to <code>school_db</code> on <code>localhost</code>.</p>
    <p>Please make sure:<br>
       1. XAMPP is running (Apache + MySQL both green)<br>
       2. The database <code>school_db</code> exists<br>
       3. You have imported <code>school.sql</code></p>
  </div>
</body>
</html>');
        }
    }

    return $pdo;
}

// -----------------------------------------------------------
// Quick connection test when this file is accessed directly
// (Remove or comment out on production)
// -----------------------------------------------------------
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT COUNT(*) as tbl FROM information_schema.tables
                             WHERE table_schema = '" . DB_NAME . "'");
        $row  = $stmt->fetch();
        echo '<div style="font-family:Arial;padding:20px;color:green;">
              &#10003; Connected to <strong>' . DB_NAME . '</strong> successfully.<br>
              Tables found: <strong>' . $row['tbl'] . '</strong></div>';
    } catch (Exception $e) {
        echo '<div style="color:red;padding:20px;">Connection failed: ' . $e->getMessage() . '</div>';
    }
}
