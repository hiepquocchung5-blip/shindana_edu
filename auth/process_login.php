<?php
// auth/process_login.php
// Production-Ready Authentication Handler with Advanced Role & Security filtering

// 1. Dependency Loading
// Use __DIR__ to ensure absolute paths relative to this file
$db_path = __DIR__ . '/../config/db.php';
$fn_path = __DIR__ . '/../config/functions.php';

if (file_exists($db_path) && file_exists($fn_path)) {
    require_once $db_path;
    require_once $fn_path;
} else {
    // Fallback if accessed in a weird context
    die("System Error: Configuration files missing.");
}

// Ensure session is active with secure parameters
if (session_status() === PHP_SESSION_NONE) {
    // SECURITY: Force secure cookie params before starting session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // ini_set('session.cookie_secure', 1); // Uncomment if using HTTPS
    session_start();
}

// 2. Request Method & CSRF Protection (Basic)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['login_btn'])) {
    redirect('auth/login');
    exit();
}

// 3. Input Sanitization
// Using filter_input for an extra layer of safety against null bytes/invalid chars
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = trim($_POST['password'] ?? '');

// Validation
if (empty($username) || empty($password)) {
    redirect('auth/login&error=' . urlencode("Credentials required."));
    exit();
}

// 4. SECURITY: BRUTE FORCE PROTECTION (Rate Limiting)
// Check system_logs for failed attempts from this IP in the last 15 minutes
try {
    $ip = $_SERVER['REMOTE_ADDR'];
    $time_window = date('Y-m-d H:i:s', time() - (15 * 60)); // 15 minutes ago
    
    $stmt_limit = $pdo->prepare("SELECT count(*) FROM system_logs WHERE ip_address = ? AND action = 'LOGIN_FAILED' AND created_at > ?");
    $stmt_limit->execute([$ip, $time_window]);
    $failed_attempts = $stmt_limit->fetchColumn();

    if ($failed_attempts >= 5) {
        // Log the lockout event
        log_activity($pdo, 'LOGIN_LOCKOUT', "IP blocked due to excessive failures: $ip");
        // Delay execution significantly to annoy attackers
        sleep(3); 
        redirect('auth/login&error=' . urlencode("Too many failed attempts. Please try again in 15 minutes."));
        exit();
    }
} catch (Exception $e) {
    // Continue if logging fails, don't break login
    error_log("Rate Limit Check Failed: " . $e->getMessage());
}

// 5. Security: Timing Attack Mitigation
// Delays execution to make brute-forcing significantly slower
usleep(rand(300000, 800000)); // 300ms - 800ms

try {
    // =========================================================
    // CHECK 1: ADMIN / STAFF TABLES
    // =========================================================
    // Fetches role and branch_id to filter access immediately upon login if needed
    // NOTE: SQL Injection is prevented here by using prepared statements ($stmt->execute)
    $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role, branch_id FROM adm_usr WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // SECURITY: Prevent Session Fixation
        session_regenerate_id(true);

        // Populate Admin Session
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['full_name'] = $admin['full_name'];
        $_SESSION['user_type'] = 'admin'; // Global identifier for internal staff
        $_SESSION['role'] = $admin['role']; // Granular role: 'Admin', 'Staff_Myanmar', 'Finance_mm', etc.
        $_SESSION['branch_id'] = $admin['branch_id']; // For branch-specific staff logic
        $_SESSION['logged_in_at'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

        // Log Successful Admin Login
        log_activity($pdo, 'LOGIN_SUCCESS', "Staff login: {$admin['username']} ({$admin['role']})");

        // Role-Based Redirects (Optional filtering)
        // You can redirect different roles to different starting pages here if needed.
        // For now, all go to the main dashboard which adapts to the role.
        redirect('admin/index');
        exit();
    }

    // =========================================================
    // CHECK 2: AGENT TABLES
    // =========================================================
    // Agents login via Email OR Agent Code (e.g. AG-8821)
    $stmt = $pdo->prepare("SELECT id, agent_code, full_name, password_hash, status, is_verified, agent_type FROM agent_user WHERE email = ? OR agent_code = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($agent && password_verify($password, $agent['password_hash'])) {
        
        // Status Check: Strictly block suspended or inactive agents
        if ($agent['status'] !== 'active') {
            log_activity($pdo, 'LOGIN_BLOCKED', "Inactive agent attempted login: {$agent['agent_code']} [Status: {$agent['status']}]");
            redirect('auth/login&error=' . urlencode("Account is " . htmlspecialchars($agent['status']) . ". Contact support."));
            exit();
        }

        // SECURITY: Prevent Session Fixation
        session_regenerate_id(true);

        // Populate Agent Session
        $_SESSION['user_id'] = $agent['id'];
        $_SESSION['username'] = $agent['agent_code'];
        $_SESSION['full_name'] = $agent['full_name'];
        $_SESSION['user_type'] = 'agent';
        $_SESSION['agent_code'] = $agent['agent_code'];
        $_SESSION['agent_type'] = $agent['agent_type']; // 'internal', 'external', etc.
        $_SESSION['is_verified'] = $agent['is_verified']; // Used to trigger password reset
        $_SESSION['logged_in_at'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

        // Log Successful Agent Login
        log_activity($pdo, 'LOGIN_SUCCESS', "Agent login: {$agent['agent_code']}");

        // Redirect to Agent Dashboard
        // Note: The dashboard (agent/index.php) handles the mandatory password update check
        redirect('agent/index');
        exit();
    }

    // =========================================================
    // FAILURE HANDLER
    // =========================================================
    // Log failed attempt with IP for security auditing and Rate Limiting
    log_activity($pdo, 'LOGIN_FAILED', "Failed login attempt for username: $username");
    
    // Generic error message to prevent Username Enumeration
    redirect('auth/login&error=' . urlencode("Invalid credentials provided."));
    exit();

} catch (PDOException $e) {
    // Log detailed DB error to server logs, show generic message to user
    error_log("Login Critical Error: " . $e->getMessage());
    redirect('auth/login&error=' . urlencode("Service temporarily unavailable."));
    exit();
}
?>