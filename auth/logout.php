<?php
// auth/logout.php
// Secure Session Termination & Routing (Deployment Version)

require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/settings.php';

// 1. Initialize session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Unset all server-side session variables
$_SESSION = array();

// 3. Destroy the session cookie securely across the domain
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Annihilate the session completely
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Logout | <?= h(APP_NAME ?? 'Sheindana') ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-pattern { 
            background-color: #0f172a;
            background-image: radial-gradient(#E5B822 0.75px, transparent 0.75px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="hero-pattern min-h-screen flex items-center justify-center p-4">

    <!-- Interstitial UI Card -->
    <div class="bg-white p-10 rounded-[32px] md:rounded-[40px] shadow-2xl flex flex-col items-center text-center max-w-sm w-full border border-slate-100 relative overflow-hidden animate-in zoom-in duration-500">
        
        <!-- Top Accent Line -->
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#D92128] to-[#E5B822]"></div>
        
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#E5B822] rounded-full blur-[60px] opacity-20 pointer-events-none"></div>
        
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inner text-green-500 border border-slate-100 relative z-10">
            <i class="fa-solid fa-shield-check text-4xl"></i>
        </div>
        
        <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2 tracking-tight relative z-10">Terminated.</h2>
        <p class="text-[10px] font-black text-slate-400 mb-8 uppercase tracking-widest relative z-10">Your session is securely closed.</p>
        
        <!-- Loading Indicator -->
        <div class="flex flex-col items-center gap-3 w-full border-t border-slate-100 pt-6 relative z-10">
            <i class="fa-solid fa-circle-notch fa-spin text-2xl text-[#E5B822]"></i>
            <p class="text-[9px] font-black uppercase text-slate-500 tracking-widest">Routing to Homepage...</p>
        </div>
    </div>

    <!-- Client-Side Cleanup & Seamless Redirect -->
    <script>
        // Deep clean any frontend state to prevent data leaks
        window.localStorage.clear();
        window.sessionStorage.clear();
        
        // Wait 1.5 seconds for visual confirmation, then route to the clean base URL
        setTimeout(function() {
            window.location.replace('<?= base_url() ?>');
        }, 1500);
    </script>

</body>
</html>