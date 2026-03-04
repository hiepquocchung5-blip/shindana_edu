<?php 
// Ensure dependencies are loaded (Handles both Direct Access & Router)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= h(APP_NAME) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
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
            <!-- Decorative Blur -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-600 rounded-full blur-[120px] opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-yellow-400 rounded-full blur-[100px] opacity-10 pointer-events-none"></div>

            <!-- Logo -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 backdrop-blur rounded-xl flex items-center justify-center border border-white/20 text-gold font-black text-sm">
                    SD
                </div>
                <div class="font-black tracking-tight text-lg uppercase">
                    Sheindana<span class="text-gold">.edu</span>
                </div>
            </div>

            <!-- Content -->
            <div class="relative z-10 my-12">
                <h2 class="text-4xl md:text-5xl font-black mb-6 leading-tight">
                    Welcome <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-500">Back.</span>
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                    Access the centralized management ecosystem for Myanmar's top Academic Centers. Secure, Unified, and Efficient.
                </p>
            </div>

            <!-- Footer Info -->
            <div class="relative z-10 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex justify-between items-center">
                <span>© <?= date('Y') ?> Global System</span>
                <span>V 2.0</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="md:w-1/2 p-12 flex flex-col justify-center bg-white relative">
            
            <div class="max-w-sm mx-auto w-full">
                <div class="mb-10">
                    <h3 class="text-2xl font-black text-slate-900 mb-2">Portal Access</h3>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Please enter your credentials</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3 animate-pulse">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <!-- Form points to the router with route=auth/process_login -->
                <form action="<?= base_url('index.php?route=auth/process_login') ?>" method="POST" class="space-y-6">
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Username / Agent Code</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input type="text" name="username" required 
                                class="w-full bg-slate-50 border border-slate-100 text-slate-900 text-sm rounded-2xl focus-ring-gold block w-full pl-10 p-4 outline-none transition-all font-bold placeholder-slate-300" 
                                placeholder="Enter ID">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" name="password" required 
                                class="w-full bg-slate-50 border border-slate-100 text-slate-900 text-sm rounded-2xl focus-ring-gold block w-full pl-10 p-4 outline-none transition-all font-bold placeholder-slate-300" 
                                placeholder="••••••••">
                        </div>
                        <div class="text-right mt-2">
                            <a href="#" class="text-[10px] font-bold text-slate-400 hover:text-gold transition">Forgot Password?</a>
                        </div>
                    </div>

                    <button type="submit" name="login_btn" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gold hover:text-slate-900 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                        Secure Login
                    </button>

                </form>

                <div class="mt-10 text-center border-t border-slate-50 pt-8">
                    <p class="text-xs font-bold text-slate-400">Not a partner yet?</p>
                    <a href="<?= base_url() ?>" class="inline-block mt-2 text-xs font-black text-slate-900 hover:text-gold transition border-b-2 border-transparent hover:border-gold pb-0.5">
                        Return to Homepage
                    </a>
                </div>
            </div>
        </div>

    </div>

</body>
</html>