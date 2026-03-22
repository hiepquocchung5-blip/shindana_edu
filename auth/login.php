<?php 
// auth/login.php
// Master-Level Single-Page Authentication Handler & UI

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/settings.php'; 

// Enforce Security Headers
set_security_headers();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') redirect('admin/index');
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'agent') redirect('agent/index');
}

$error = '';

// =========================================================
// INLINE LOGIN PROCESSOR (Hidden from URL)
// =========================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_btn'])) {
    
    // 1. Validate CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        
        // 2. Input Sanitization
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = "Both username and password are required.";
        } else {
            
            // 3. Rate Limiting (Brute Force Protection)
            try {
                $ip = $_SERVER['REMOTE_ADDR'];
                $time_window = date('Y-m-d H:i:s', time() - (15 * 60)); // Last 15 minutes
                
                $stmt_limit = $pdo->prepare("SELECT count(*) FROM system_logs WHERE ip_address = ? AND action = 'LOGIN_FAILED' AND created_at > ?");
                $stmt_limit->execute([$ip, $time_window]);
                $failed_attempts = $stmt_limit->fetchColumn();

                if ($failed_attempts >= 5) {
                    log_activity($pdo, 'LOGIN_LOCKOUT', "IP blocked due to excessive failures: $ip");
                    sleep(3); // Annoyance delay
                    $error = "Too many failed attempts. Please try again in 15 minutes.";
                } else {
                    
                    // 4. Timing Attack Mitigation
                    usleep(rand(300000, 800000)); // Delay between 300ms and 800ms

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
                    } else {
                        
                        // =========================================================
                        // CHECK 2: AGENT TABLES
                        // =========================================================
                        $stmt = $pdo->prepare("SELECT id, agent_code, full_name, password_hash, status, is_verified, agent_type FROM agent_user WHERE email = ? OR agent_code = ? LIMIT 1");
                        $stmt->execute([$username, $username]);
                        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($agent && password_verify($password, $agent['password_hash'])) {
                            
                            if ($agent['status'] !== 'active') {
                                log_activity($pdo, 'LOGIN_BLOCKED', "Inactive agent attempted login: {$agent['agent_code']}");
                                $error = "Account is " . htmlspecialchars($agent['status']) . ". Please contact support.";
                            } else {
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
                            }
                        } else {
                            // FAILURE HANDLER
                            log_activity($pdo, 'LOGIN_FAILED', "Failed login attempt for username: $username");
                            $error = "Invalid credentials provided.";
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Login Critical Error: " . $e->getMessage());
                $error = "Service temporarily unavailable.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Portal | <?= h(APP_NAME ?? 'Sheindana') ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .text-gold { color: #E5B822; }
        .bg-gold { background-color: #E5B822; }
        .focus-ring-gold:focus { box-shadow: 0 0 0 2px rgba(229, 184, 34, 0.2); border-color: #E5B822; }
        .hero-pattern { 
            background-color: #0f172a;
            background-image: radial-gradient(#E5B822 0.5px, transparent 0.5px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px] border border-slate-100">
        
        <!-- Left Side: Brand & Visuals -->
        <div class="md:w-1/2 hero-pattern relative p-10 md:p-12 flex flex-col justify-between text-white overflow-hidden">
            <!-- Abstract Glows -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-600 rounded-full blur-[120px] opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-red-600 rounded-full blur-[100px] opacity-20 pointer-events-none"></div>

            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 backdrop-blur rounded-xl flex items-center justify-center border border-white/20 text-gold font-black text-sm shadow-lg">SD</div>
                <div class="font-black tracking-tight text-lg uppercase">Shinedana<span class="text-gold">.com</span></div>
            </div>

            <div class="relative z-10 my-12">
                <h2 class="text-4xl md:text-5xl font-black mb-6 leading-tight tracking-tighter">
                    Secure <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-500">Access.</span>
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm font-medium">
                    Encrypted gateway for authorized staff members and verified partner agents.
                </p>
            </div>

            <div class="relative z-10 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex justify-between items-center">
                <span>© <?= date('Y') ?> Global System</span>
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-green-500"></i> AES-256 Encrypted</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="md:w-1/2 p-10 md:p-12 flex flex-col justify-center bg-white relative z-20">
            <div class="max-w-sm mx-auto w-full">
                <div class="mb-10">
                    <h3 class="text-2xl md:text-3xl font-black text-slate-900 mb-2 tracking-tight">System Login</h3>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Provide your credentials</p>
                </div>

                <!-- Display Errors Inline (No URL params) -->
                <?php if(!empty($error)): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3 animate-in slide-in-from-top-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Form submits to ITSELF cleanly -->
                <form action="<?= route('auth/login') ?>" method="POST" class="space-y-5">
                    <!-- CRITICAL: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Username / Agent ID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fa-regular fa-user"></i></div>
                            <!-- Retain username on failed attempt -->
                            <input type="text" name="username" required autocomplete="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-2xl focus-ring-gold block pl-11 p-4 outline-none transition-all font-bold placeholder-slate-300" 
                                placeholder="e.g. AG-001 or admin">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-lock"></i></div>
                            <input type="password" name="password" required autocomplete="current-password"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-2xl focus-ring-gold block pl-11 p-4 outline-none transition-all font-bold placeholder-slate-300" 
                                placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" name="login_btn" class="w-full bg-slate-900 text-white py-4 mt-2 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gold hover:text-slate-900 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 active:scale-[0.98]">
                        Authenticate Session
                    </button>
                </form>

                <div class="mt-10 text-center border-t border-slate-50 pt-8">
                    <a href="<?= base_url() ?>" class="inline-block mt-2 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-gold transition pb-0.5 group">
                        <i class="fa-solid fa-arrow-left mr-1 group-hover:-translate-x-1 transition-transform"></i> Return to Homepage
                    </a>
                </div>
            </div>
        </div>

    </div>

</body>
</html>