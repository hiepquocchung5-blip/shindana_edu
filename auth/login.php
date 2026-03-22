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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Secure Portal | <?= h(APP_NAME ?? 'Sheindana') ?></title>
    
    <!-- Tailwind & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .text-gold { color: #E5B822; }
        .bg-gold { background-color: #E5B822; }
        
        /* Immersive 3D Elements */
        .input-3d {
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .input-3d:focus {
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05), 0 0 0 4px rgba(229, 184, 34, 0.2);
            border-color: #E5B822;
        }
        .btn-3d {
            border-bottom-width: 4px;
            border-color: #020617; /* slate-950 */
            transition: all 0.15s ease;
        }
        .btn-3d:hover {
            border-color: #b48e1b; /* darker gold */
        }
        .btn-3d:active {
            border-bottom-width: 0px;
            transform: translateY(4px);
        }

        .hero-pattern { 
            background-color: #0f172a;
            background-image: radial-gradient(#E5B822 0.75px, transparent 0.75px);
            background-size: 24px 24px;
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 md:p-8 overflow-x-hidden">

    <div class="w-full max-w-5xl bg-white rounded-[32px] md:rounded-[40px] shadow-[0_20px_60px_rgba(0,0,0,0.1)] overflow-hidden flex flex-col lg:flex-row min-h-auto lg:min-h-[600px] border border-slate-100">
        
        <!-- Left Side: Brand, Visuals & Features -->
        <div class="w-full lg:w-1/2 hero-pattern relative p-8 sm:p-10 lg:p-12 flex flex-col justify-between text-white overflow-hidden shrink-0">
            <!-- Abstract 3D Glows -->
            <div class="absolute top-0 right-0 w-64 lg:w-96 h-64 lg:h-96 bg-yellow-600 rounded-full blur-[100px] lg:blur-[120px] opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-48 lg:w-64 h-48 lg:h-64 bg-red-600 rounded-full blur-[80px] lg:blur-[100px] opacity-20 pointer-events-none"></div>

            <!-- Logo & Brand -->
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 lg:w-14 lg:h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 shadow-[0_0_30px_rgba(229,184,34,0.3)] shrink-0 overflow-hidden p-2">
                    <!-- Shinedana Logo -->
                    <img src="<?= asset_url('images/shine_logo.png') ?>" alt="SHN Logo" class="w-full h-full object-contain" onerror="this.style.display='none'">
                    <!-- Fallback if image fails -->
                    <span class="absolute inset-0 flex items-center justify-center text-gold font-black text-sm" style="z-index: -1;">SD</span>
                </div>
                <div class="font-black tracking-tight text-lg lg:text-xl uppercase">Shinedana<span class="text-gold">.com</span></div>
            </div>

            <!-- Title & Features (Hidden on very small screens to save space, visible on sm and up) -->
            <div class="relative z-10 my-8 lg:my-12">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-4 lg:mb-6 leading-tight tracking-tighter">
                    Secure <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-500 drop-shadow-sm">Access.</span>
                </h2>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-sm font-medium mb-6">
                    Encrypted gateway for authorized staff members and verified partner agents.
                </p>
                
                <!-- Portal Features -->
                <ul class="space-y-4 hidden sm:block">
                    <li class="flex items-center gap-3 text-xs lg:text-sm font-bold text-slate-300">
                        <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center border border-white/10 shadow-inner text-gold"><i class="fa-solid fa-file-shield"></i></div>
                        Encrypted Document Vault
                    </li>
                    <li class="flex items-center gap-3 text-xs lg:text-sm font-bold text-slate-300">
                        <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center border border-white/10 shadow-inner text-gold"><i class="fa-solid fa-chart-line"></i></div>
                        Live Commission Tracking
                    </li>
                    <li class="flex items-center gap-3 text-xs lg:text-sm font-bold text-slate-300">
                        <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center border border-white/10 shadow-inner text-gold"><i class="fa-solid fa-bolt"></i></div>
                        Instant Application Sync
                    </li>
                </ul>
            </div>

            <div class="relative z-10 text-[9px] lg:text-[10px] font-bold text-slate-500 uppercase tracking-widest flex justify-between items-center mt-4 lg:mt-0 border-t border-white/10 pt-4 lg:border-none lg:pt-0">
                <span>© <?= date('Y') ?> Global System</span>
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-green-500"></i> AES-256 Encrypted</span>
            </div>
        </div>

        <!-- Right Side: Login Form with Alpine.js -->
        <div class="w-full lg:w-1/2 p-8 sm:p-10 lg:p-12 flex flex-col justify-center bg-white relative z-20" x-data="{ showPassword: false, isSubmitting: false }">
            <div class="max-w-sm mx-auto w-full">
                <div class="mb-8 lg:mb-10 text-center lg:text-left">
                    <h3 class="text-2xl lg:text-3xl font-black text-slate-900 mb-2 tracking-tight">System Login</h3>
                    <p class="text-slate-400 text-[10px] lg:text-xs font-bold uppercase tracking-widest">Provide your credentials</p>
                </div>

                <!-- Display Errors Inline -->
                <?php if(!empty($error)): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3 animate-in slide-in-from-top-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
                        <span class="leading-snug"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- 3D Interactive Form -->
                <form action="<?= route('auth/login') ?>" method="POST" class="space-y-5" @submit="isSubmitting = true">
                    <!-- CRITICAL: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div>
                        <label class="block text-[9px] lg:text-[10px] font-black uppercase text-slate-500 mb-2 ml-1">Username / Agent ID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fa-regular fa-user"></i></div>
                            <input type="text" name="username" required autocomplete="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                class="input-3d w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-2xl block pl-11 p-4 outline-none font-bold placeholder-slate-300" 
                                placeholder="e.g. AG-001 or admin">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] lg:text-[10px] font-black uppercase text-slate-500 mb-2 ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-lock"></i></div>
                            <!-- Dynamic Input Type bound to Alpine variable -->
                            <input :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                class="input-3d w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-2xl block pl-11 pr-12 p-4 outline-none font-bold placeholder-slate-300" 
                                placeholder="••••••••">
                            
                            <!-- Interactive Eye Toggle -->
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-gold transition-colors focus:outline-none z-10">
                                <i class="fa-solid text-lg" :class="showPassword ? 'fa-eye-slash text-gold' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tactile 3D Submit Button -->
                    <div class="pt-2">
                        <button type="submit" name="login_btn" :disabled="isSubmitting"
                            class="btn-3d w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-[10px] lg:text-xs uppercase tracking-widest hover:bg-gold hover:text-slate-900 shadow-xl flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Authenticate Session</span>
                            <span x-show="isSubmitting" x-cloak><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Verifying...</span>
                        </button>
                    </div>
                </form>

                <div class="mt-8 lg:mt-10 text-center border-t border-slate-100 pt-6 lg:pt-8">
                    <a href="<?= base_url() ?>" class="inline-flex items-center justify-center gap-2 text-[9px] lg:text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-gold transition group">
                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center group-hover:bg-yellow-50 group-hover:-translate-x-1 transition-all"><i class="fa-solid fa-arrow-left"></i></div>
                        Return to Homepage
                    </a>
                </div>
            </div>
        </div>

    </div>

</body>
</html>