<?php
// admin/index.php
// Main Admin Dashboard

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Fetch Dashboard Stats
try {
    $stats = [
        'agents' => $pdo->query("SELECT count(*) FROM agent_user WHERE status='active'")->fetchColumn(),
        'students' => $pdo->query("SELECT count(*) FROM students")->fetchColumn(),
        'classes' => $pdo->query("SELECT count(*) FROM class_divisions WHERE status='active'")->fetchColumn(),
        'pending_apps' => $pdo->query("SELECT count(*) FROM students WHERE status='pending'")->fetchColumn(),
        'new_leads' => $pdo->query("SELECT count(*) FROM enquiries WHERE status='new'")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats = ['agents'=>0, 'students'=>0, 'classes'=>0, 'pending_apps'=>0, 'new_leads'=>0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Console | Sheindana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Top Navbar -->
    <nav class="bg-slate-900 text-white px-6 py-4 sticky top-0 z-50 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Brand -->
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20 text-[#D4AF37] font-black text-sm">SD</div>
                <div>
                    <div class="font-black text-xl uppercase tracking-tighter leading-none">Sheindana<span class="text-[#D4AF37]">.Admin</span></div>
                    <span class="text-[9px] font-bold uppercase text-white/50 tracking-widest">
                        Role: <?= htmlspecialchars($_SESSION['role'] ?? 'Super Admin') ?>
                    </span>
                </div>
            </div>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-6">
                <a href="<?= admin_url('classes') ?>" class="text-xs font-bold uppercase hover:text-[#D4AF37] transition">Classes</a>
                <a href="<?= admin_url('agents') ?>" class="text-xs font-bold uppercase hover:text-[#D4AF37] transition">Agents</a>
                <a href="<?= admin_url('finance') ?>" class="text-xs font-bold uppercase hover:text-[#D4AF37] transition">Finance</a>
                
                <!-- Settings Icon -->
                <a href="<?= admin_url('settings') ?>" class="text-slate-400 hover:text-white transition" title="Settings">
                    <i class="fa-solid fa-gear"></i>
                </a>

                <a href="<?= auth_url('logout') ?>" class="ml-4 bg-[#D4AF37] text-slate-900 px-4 py-2 rounded-lg text-xs font-black uppercase hover:bg-white transition flex items-center gap-2">
                    <i class="fa-solid fa-power-off"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-3xl md:text-4xl font-black uppercase text-slate-900">System Overview</h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Welcome back, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator') ?>.</p>
            </div>
            <div class="text-right mt-4 md:mt-0">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Server Time</span>
                <div class="text-lg font-black font-mono text-slate-700"><?= date('H:i') ?> <span class="text-xs text-slate-400">YGN</span></div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            
            <!-- Student Stats -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute right-[-10px] top-[-10px] text-[80px] text-slate-50 font-black group-hover:text-blue-50 transition pointer-events-none">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Total Students</div>
                    <div class="text-4xl font-black text-slate-900"><?= number_format($stats['students']) ?></div>
                    <?php if($stats['pending_apps'] > 0): ?>
                        <a href="<?= admin_url('students') ?>" class="mt-4 inline-flex items-center gap-1 text-[10px] font-bold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-full animate-pulse hover:bg-orange-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            <?= $stats['pending_apps'] ?> Pending Review
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- New Enquiries -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute right-[-10px] top-[-10px] text-[80px] text-slate-50 font-black group-hover:text-red-50 transition pointer-events-none">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">New Leads</div>
                    <div class="text-4xl font-black text-red-500"><?= number_format($stats['new_leads']) ?></div>
                    <a href="<?= admin_url('enquiries') ?>" class="mt-4 inline-block text-[10px] font-bold text-slate-400 hover:text-slate-900 transition">View Enquiries →</a>
                </div>
            </div>

            <!-- Agent Network -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute right-[-10px] top-[-10px] text-[80px] text-slate-50 font-black group-hover:text-green-50 transition pointer-events-none">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Active Agents</div>
                    <div class="text-4xl font-black text-green-600"><?= number_format($stats['agents']) ?></div>
                    <a href="<?= admin_url('agents') ?>" class="mt-4 inline-block text-[10px] font-bold text-slate-400 hover:text-slate-900 transition">Manage Network →</a>
                </div>
            </div>

            <!-- Classes -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute right-[-10px] top-[-10px] text-[80px] text-slate-50 font-black group-hover:text-yellow-50 transition pointer-events-none">
                    <i class="fa-solid fa-school"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Active Classes</div>
                    <div class="text-4xl font-black text-[#D4AF37]"><?= number_format($stats['classes']) ?></div>
                    <a href="<?= admin_url('classes') ?>" class="mt-4 inline-block text-[10px] font-bold text-slate-400 hover:text-slate-900 transition">Sync Settings →</a>
                </div>
            </div>
        </div>

        <!-- Management Modules Grid -->
        <h3 class="text-xl font-black uppercase italic mb-6 text-slate-800">Management Modules</h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Module 1: Enquiries -->
            <a href="<?= admin_url('enquiries') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">Student Enquiries</h4>
                <p class="text-sm text-slate-500 leading-relaxed mb-4">Manage and track incoming messages from the website landing page.</p>
                <?php if($stats['new_leads'] > 0): ?>
                    <span class="inline-block text-[9px] font-bold bg-red-100 text-red-600 px-3 py-1 rounded-full border border-red-200">
                        <i class="fa-solid fa-bell mr-1"></i> <?= $stats['new_leads'] ?> New
                    </span>
                <?php endif; ?>
            </a>

            <!-- Module 2: Class Divisions -->
            <a href="<?= admin_url('classes') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">Class Divisions</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Create unified classes and toggle visibility across all 5 branches instantly.</p>
            </a>

            <!-- Module 3: Agent Network -->
            <a href="<?= admin_url('agents') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">Agent Network</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Onboard new agents, set commission rates, and manage account status.</p>
            </a>

            <!-- Module 4: Applications -->
            <a href="<?= admin_url('students') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">Applications</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Review incoming student registrations and secure PDF documents from agents.</p>
            </a>

            <!-- Module 5: Pacific Database -->
            <a href="<?= admin_url('japan_schools') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-torii-gate"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">Pacific Database</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Manage Japanese partner institutions visible on the public finder.</p>
            </a>

            <!-- Module 6: Finance Hub -->
            <a href="<?= admin_url('finance') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">Finance Hub</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Generate tax invoices for agents and track payment status.</p>
            </a>

            <!-- Module 7: Staff & Roles (New) -->
            <?php if($_SESSION['role'] === 'Admin'): ?>
            <a href="<?= admin_url('staff') ?>" class="group bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 hover:border-[#D4AF37] transition duration-300">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xl mb-6 group-hover:bg-[#D4AF37] group-hover:text-slate-900 transition shadow-md">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h4 class="font-black text-lg uppercase mb-2 group-hover:text-[#D4AF37] transition">System & Staff</h4>
                <p class="text-sm text-slate-500 leading-relaxed">Manage internal staff accounts, assign roles, and configure system access.</p>
            </a>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>