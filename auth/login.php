<?php 
// auth/login.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/settings.php'; 

// Enforce Security Headers
set_security_headers();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') redirect('admin/index');
    if ($_SESSION['user_type'] === 'agent') redirect('agent/index');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Portal | <?= h(APP_NAME) ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
        .focus-ring-gold:focus { box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2); border-color: #D4AF37; }
        .hero-pattern { 
            background-color: #0f172a;
            background-image: radial-gradient(#D4AF37 0.5px, transparent 0.5px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Side: Brand & Visuals -->
        <div class="md:w-1/2 hero-pattern relative p-12 flex flex-col justify-between text-white overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-600 rounded-full blur-[120px] opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-yellow-400 rounded-full blur-[100px] opacity-10 pointer-events-none"></div>

            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 backdrop-blur rounded-xl flex items-center justify-center border border-white/20 text-gold font-black text-sm">SD</div>
                <div class="font-black tracking-tight text-lg uppercase">Sheindana<span class="text-gold">.edu</span></div>
            </div>

            <div class="relative z-10 my-12">
                <h2 class="text-4xl md:text-5xl font-black mb-6 leading-tight">
                    Secure <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-500">Access.</span>
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                    Encrypted portal for authorized staff and registered partner agents.
                </p>
            </div>

            <div class="relative z-10 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex justify-between items-center">
                <span>© <?= date('Y') ?> Global System</span>
                <span><i class="fa-solid fa-shield-halved text-green-500"></i> CSRF Protected</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="md:w-1/2 p-12 flex flex-col justify-center bg-white relative">
            <div class="max-w-sm mx-auto w-full">
                <div class="mb-10">
                    <h3 class="text-2xl font-black text-slate-900 mb-2">Login to Portal</h3>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Enter your credentials</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3 animate-pulse">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= route('auth/process_login') ?>" method="POST" class="space-y-6">
                    <!-- CRITICAL: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Username / Agent ID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fa-regular fa-user"></i></div>
                            <input type="text" name="username" required autocomplete="username"
                                class="w-full bg-slate-50 border border-slate-100 text-slate-900 text-sm rounded-2xl focus-ring-gold block pl-10 p-4 outline-none transition-all font-bold placeholder-slate-300" 
                                placeholder="e.g. AG-001 or admin">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-lock"></i></div>
                            <input type="password" name="password" required autocomplete="current-password"
                                class="w-full bg-slate-50 border border-slate-100 text-slate-900 text-sm rounded-2xl focus-ring-gold block pl-10 p-4 outline-none transition-all font-bold placeholder-slate-300" 
                                placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" name="login_btn" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gold hover:text-slate-900 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                        Authenticate
                    </button>
                </form>

                <div class="mt-10 text-center border-t border-slate-50 pt-8">
                    <a href="<?= base_url() ?>" class="inline-block mt-2 text-xs font-black text-slate-400 hover:text-gold transition pb-0.5">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Return to Homepage
                    </a>
                </div>
            </div>
        </div>

    </div>

</body>
</html>