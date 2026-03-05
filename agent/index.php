<?php
// agent/index.php
// Production-Ready Agent Portal

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/settings.php'; 

// 1. Security & Verification Check
set_security_headers();
requireAgent();
$agent_id = $_SESSION['user_id'];

// Fetch Agent Details & Setup Status
$stmt = $pdo->prepare("SELECT * FROM agent_user WHERE id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();

if (!$agent) {
    redirect('auth/logout');
}

// Force password reset on first login
if ($agent['is_verified'] == 0) {
    // If using routing, this should ideally redirect to a setup route
    require_once __DIR__ . '/confirm_agent_password.php'; 
    exit();
}

// 2. Fetch Financials & Stats
// Get total finalized invoices to calculate actual revenue
$stmt_rev = $pdo->prepare("
    SELECT COUNT(id) as total_invoices, COALESCE(SUM(total_amount), 0) as total_revenue 
    FROM invoices 
    WHERE agent_id = ? AND status != 'cancelled' AND status != 'draft'
");
$stmt_rev->execute([$agent_id]);
$finance = $stmt_rev->fetch();

// Dynamic Math Engine for Commissions
$est_commission = 0;
if ($agent['commission_type'] == 'percentage') {
    $est_commission = ($agent['commission_value'] / 100) * $finance['total_revenue'];
} elseif ($agent['commission_type'] == 'fixed') {
    $est_commission = $agent['commission_value'] * $finance['total_invoices'];
}

// Get Referral Stats
$stats = [
    'referrals' => $pdo->query("SELECT count(*) FROM students WHERE agent_id = $agent_id")->fetchColumn(),
    'pending'   => $pdo->query("SELECT count(*) FROM students WHERE agent_id = $agent_id AND status = 'pending'")->fetchColumn()
];

// 3. Fetch Recent Students
$sql_students = "SELECT s.*, j.school_name 
                 FROM students s 
                 LEFT JOIN japan_schools j ON s.target_school_id = j.id 
                 WHERE s.agent_id = ? 
                 ORDER BY s.created_at DESC LIMIT 10";
$stmt_st = $pdo->prepare($sql_students);
$stmt_st->execute([$agent_id]);
$students = $stmt_st->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Hub | <?= h(APP_NAME) ?></title>
    
    <!-- Tailwind & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-900 antialiased">

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center border-2 border-[#D4AF37] shadow-md">
                    <span class="text-white font-black text-xs">SD</span>
                </div>
                <div class="leading-tight">
                    <span class="block font-black uppercase text-slate-900 tracking-tight text-sm">Shinedana<span class="text-[#D4AF37]">.Partner</span></span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Agent Portal</span>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="hidden md:block text-right">
                    <div class="text-xs font-black uppercase text-slate-900"><?= h($agent['full_name']) ?></div>
                    <div class="text-[10px] text-slate-400 font-bold tracking-widest bg-slate-100 px-2 py-0.5 rounded inline-block mt-0.5">
                        <i class="fa-solid fa-id-badge mr-1"></i> <?= h($agent['agent_code']) ?>
                    </div>
                </div>
                <a href="<?= auth_url('logout') ?>" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors shadow-sm" title="Secure Logout">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard -->
    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <!-- Action Feedback -->
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-2xl text-sm font-bold mb-8 flex items-center gap-3 shadow-sm animate-pulse">
                <i class="fa-solid fa-circle-check text-lg"></i> <?= h($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Header & Primary Actions -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
            <div>
                <h1 class="text-3xl md:text-4xl font-black uppercase italic text-slate-900 tracking-tight">My Dashboard</h1>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="bg-slate-900 text-white px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest shadow-sm">Type: <?= h($agent['agent_type']) ?></span>
                    <span class="bg-[#D4AF37]/20 border border-[#D4AF37]/30 text-[#D4AF37] px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest shadow-sm">
                        Rate: <?= floatval($agent['commission_value']) ?><?= $agent['commission_type'] == 'percentage' ? '%' : ' Fixed' ?>
                    </span>
                </div>
            </div>
            
            <a href="<?= agent_url('register') ?>" class="group bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase hover:bg-[#D4AF37] hover:text-slate-900 transition-all shadow-xl flex items-center justify-center gap-3 w-full md:w-auto transform hover:-translate-y-1">
                <i class="fa-solid fa-user-plus group-hover:rotate-12 transition-transform"></i> New Registration
            </a>
        </div>

        <!-- Performance Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Total Referrals -->
            <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:border-blue-200 transition-colors">
                <div class="absolute -right-4 -bottom-4 text-8xl text-slate-50 group-hover:text-blue-50 transition-colors pointer-events-none z-0"><i class="fa-solid fa-users"></i></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Total Referrals</p>
                    <h4 class="text-5xl font-black text-slate-900"><?= number_format($stats['referrals']) ?></h4>
                </div>
            </div>
            
            <!-- Pending Review -->
            <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:border-orange-200 transition-colors">
                <div class="absolute -right-4 -bottom-4 text-8xl text-slate-50 group-hover:text-orange-50 transition-colors pointer-events-none z-0"><i class="fa-solid fa-file-signature"></i></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Under Review</p>
                    <h4 class="text-5xl font-black text-orange-500"><?= number_format($stats['pending']) ?></h4>
                </div>
            </div>
            
            <!-- Estimated Commission -->
            <div class="bg-slate-900 p-8 rounded-[32px] shadow-2xl border border-slate-800 relative overflow-hidden transform hover:scale-[1.02] transition-transform">
                <!-- Circuit-like visual flair -->
                <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px]"></div>
                <div class="absolute -right-4 -bottom-4 text-8xl text-white/5 pointer-events-none z-0"><i class="fa-solid fa-wallet"></i></div>
                
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-[#D4AF37] tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#D4AF37] animate-pulse"></span> Est. Commission
                    </p>
                    <div class="flex items-baseline gap-2 text-white">
                        <h4 class="text-4xl font-black"><?= number_format($est_commission) ?></h4>
                        <span class="text-xs font-bold text-white/50 uppercase">MMK</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Registration History -->
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-black uppercase text-sm tracking-tight text-slate-800">Recent Registrations</h3>
                <span class="text-[10px] font-bold bg-white border border-slate-200 text-slate-400 px-3 py-1 rounded-full uppercase tracking-wide">Latest 10</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[800px]">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5">Applicant Details</th>
                            <th class="px-8 py-5">Target Institution</th>
                            <th class="px-8 py-5">Documents</th>
                            <th class="px-8 py-5">Status</th>
                            <th class="px-8 py-5 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($students as $student): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="font-bold text-slate-900 text-base"><?= h($student['full_name']) ?></div>
                                <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wide flex items-center gap-1 mt-1">
                                    <i class="fa-regular fa-id-card"></i> <?= h($student['nric_passport']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-xs font-bold text-slate-600 uppercase tracking-tight">
                                    <?= h($student['school_name'] ?? 'Institution Not Assigned') ?>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <a href="<?= route('core/view_document&file=' . urlencode($student['document_path'])) ?>" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black text-red-500 bg-red-50 border border-red-100 px-3 py-1.5 rounded-lg uppercase tracking-wide hover:bg-red-500 hover:text-white transition-colors">
                                    <i class="fa-solid fa-file-pdf"></i> View Bundle
                                </a>
                            </td>
                            <td class="px-8 py-5">
                                <?php 
                                    $statusConfig = match($student['status']) {
                                        'approved' => ['bg-green-100 text-green-700 border-green-200', 'fa-check'],
                                        'pending' => ['bg-orange-100 text-orange-700 border-orange-200', 'fa-hourglass-half'],
                                        'reviewing' => ['bg-blue-100 text-blue-700 border-blue-200', 'fa-magnifying-glass'],
                                        'rejected' => ['bg-red-100 text-red-700 border-red-200', 'fa-xmark'],
                                        default => ['bg-slate-100 text-slate-600 border-slate-200', 'fa-circle-info']
                                    };
                                ?>
                                <span class="<?= $statusConfig[0] ?> border px-3 py-1 rounded-full text-[9px] font-black uppercase flex items-center gap-1.5 w-fit">
                                    <i class="fa-solid <?= $statusConfig[1] ?>"></i> <?= h($student['status']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right text-xs font-bold text-slate-400">
                                <?= date('M d, Y', strtotime($student['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($students)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fa-regular fa-folder-open text-5xl mb-4 opacity-30"></i>
                                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">No registrations found</p>
                                        <p class="text-xs mt-1">Start by adding your first student referral.</p>
                                        <a href="<?= agent_url('register') ?>" class="mt-4 text-[10px] font-black uppercase text-[#D4AF37] hover:underline">Register Now &rarr;</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>