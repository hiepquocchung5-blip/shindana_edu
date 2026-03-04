<?php
// auth/process_login.php
// Master-Level Authentication Handler

// 1. Dependency Loading
$db_path = __DIR__ . '/../config/db.php';
$fn_path = __DIR__ . '/../config/functions.php';

if (file_exists($db_path) && file_exists($fn_path)) {
    require_once $db_path;
    require_once $fn_path;
} else {
    die("System Error: Configuration files missing.");
}

// Ensure session is active with secure parameters
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // ini_set('session.cookie_secure', 1); // Enable this in production with HTTPS
    session_start();
}

// 2. Request Method & CSRF Protection
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['login_btn'])) {
    redirect('auth/login');
    exit();
}

// Validate CSRF Token
$csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
if (!function_exists('verify_csrf')) {
    die("Security Error: CSRF validation function missing.");
}
verify_csrf($csrf_token);

// 3. Input Sanitization
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    redirect('auth/login&error=' . urlencode("Credentials required."));
    exit();
}

// 4. Rate Limiting (Brute Force Protection)
try {
    $ip = $_SERVER['REMOTE_ADDR'];
    $time_window = date('Y-m-d H:i:s', time() - (15 * 60)); // Last 15 minutes
    
    $stmt_limit = $pdo->prepare("SELECT count(*) FROM system_logs WHERE ip_address = ? AND action = 'LOGIN_FAILED' AND created_at > ?");
    $stmt_limit->execute([$ip, $time_window]);
    $failed_attempts = $stmt_limit->fetchColumn();

    if ($failed_attempts >= 5) {
        log_activity($pdo, 'LOGIN_LOCKOUT', "IP blocked due to excessive failures: $ip");
        sleep(3); // Annoyance delay for automated attacks
        redirect('auth/login&error=' . urlencode("Too many failed attempts. Please try again in 15 minutes."));
        exit();
    }
} catch (Exception $e) {
    error_log("Rate Limit Check Failed: " . $e->getMessage());
}

// 5. Timing Attack Mitigation
usleep(rand(300000, 800000)); // Delay between 300ms and 800ms

try {
    // =========================================================
    // CHECK 1: ADMIN / STAFF TABLES
    // =========================================================
    $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role, branch_id FROM adm_usr WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['full_name'] = $admin['full_name'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['role'] = $admin['role'];
        $_SESSION['branch_id'] = $admin['branch_id'];
        $_SESSION['logged_in_at'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

        log_activity($pdo, 'LOGIN_SUCCESS', "Staff login: {$admin['username']} ({$admin['role']})");
        redirect('admin/index');
        exit();
    }

    // =========================================================
    // CHECK 2: AGENT TABLES
    // =========================================================
    $stmt = $pdo->prepare("SELECT id, agent_code, full_name, password_hash, status, is_verified, agent_type FROM agent_user WHERE email = ? OR agent_code = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($agent && password_verify($password, $agent['password_hash'])) {
        
        if ($agent['status'] !== 'active') {
            log_activity($pdo, 'LOGIN_BLOCKED', "Inactive agent attempted login: {$agent['agent_code']}");
            redirect('auth/login&error=' . urlencode("Account is " . htmlspecialchars($agent['status']) . ". Contact support."));
            exit();
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $agent['id'];
        $_SESSION['username'] = $agent['agent_code'];
        $_SESSION['full_name'] = $agent['full_name'];
        $_SESSION['user_type'] = 'agent';
        $_SESSION['agent_code'] = $agent['agent_code'];
        $_SESSION['agent_type'] = $agent['agent_type'];
        $_SESSION['is_verified'] = $agent['is_verified'];
        $_SESSION['logged_in_at'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

        log_activity($pdo, 'LOGIN_SUCCESS', "Agent login: {$agent['agent_code']}");
        redirect('agent/index');
        exit();
    }

    // =========================================================
    // FAILURE HANDLER
    // =========================================================
    log_activity($pdo, 'LOGIN_FAILED', "Failed login attempt for username: $username");
    redirect('auth/login&error=' . urlencode("Invalid credentials provided."));
    exit();

} catch (PDOException $e) {
    error_log("Login Critical Error: " . $e->getMessage());
    redirect('auth/login&error=' . urlencode("Service temporarily unavailable."));
    exit();
}
?>