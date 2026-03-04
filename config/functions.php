<?php
// config/functions.php
// Core Helper Functions for Sheindana.edu

// 1. Load Global Settings
// Ensure settings.php exists before requiring
if (file_exists(__DIR__ . '/settings.php')) {
    require_once __DIR__ . '/settings.php';
}

// 2. Auto-Detect Base URL
// This automatically finds the folder path relative to htdocs/www
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the project directory path relative to document root
    $projectDir = str_replace(
        str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), 
        '', 
        str_replace('\\', '/', dirname(__DIR__))
    );
    
    define('BASE_URL', $protocol . "://" . $host . $projectDir . '/');
}

// =================================================================
// 3. ROUTING HELPER FUNCTIONS
// =================================================================

// Returns the absolute base path (e.g., http://localhost/sheindana/)
function base_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

// Generates a dynamic route URL (e.g., index.php?route=auth/login)
function route($path = '') {
    // Remove .php extension to keep URLs clean
    $clean_path = str_replace('.php', '', $path);
    return base_url('index.php?route=' . ltrim($clean_path, '/'));
}

function admin_url($path = '') {
    // Converts 'classes.php' to 'index.php?route=admin/classes'
    return route('admin/' . $path);
}

function agent_url($path = '') {
    // Converts 'index.php' to 'index.php?route=agent/index'
    return route('agent/' . $path);
}

function auth_url($path = '') {
    // Converts 'login.php' to 'index.php?route=auth/login'
    return route('auth/' . $path);
}

function asset_url($path = '') {
    // Assets (CSS/JS/Img) are still direct files, not routes
    return base_url('assets/' . ltrim($path, '/'));
}

// Helper to redirect to a route
function redirect($path) {
    // Check if $path is a full URL or a route
    if (strpos($path, 'http') === 0) {
        $url = $path;
    } else {
        $url = route($path);
    }

    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "<script>window.location.href='$url';</script>";
    }
    exit();
}

// =================================================================
// 4. SECURITY & AUTH HELPER FUNCTIONS
// =================================================================

// Ensure user is logged in
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        redirect('auth/login');
    }
}

// Ensure user is an Admin
function requireAdmin() {
    requireLogin();
    // Allow 'admin' type or specific roles like 'Admin', 'Staff_Myanmar'
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        die("ACCESS DENIED: Administrator privileges required.");
    }
}

// Ensure user is an Agent
function requireAgent() {
    requireLogin();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
        die("ACCESS DENIED: Agent portal only.");
    }
}

// XSS Protection: HTML Special Chars
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// =================================================================
// 5. SYSTEM LOGGING
// =================================================================

/**
 * Logs system activity to the database.
 * * @param PDO $pdo The database connection object
 * @param string $action The action code (e.g., 'LOGIN_SUCCESS', 'CREATE_USER')
 * @param string $details Detailed description of the event
 */
function log_activity($pdo, $action, $details = '') {
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user_id = $_SESSION['user_id'] ?? null;
        // Determine role based on session data
        $role = $_SESSION['role'] ?? ($_SESSION['user_type'] ?? 'System/Guest');
        $ip = $_SERVER['REMOTE_ADDR'];

        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, user_role, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $role, $action, $details, $ip]);
    } catch (Exception $e) {
        // Silent fail to not disrupt user flow, but log to server error log
        error_log("System Logging Failed: " . $e->getMessage());
    }
}
?>