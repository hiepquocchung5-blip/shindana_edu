<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAgent();

$agent_id = $_SESSION['user_id'];

// 2. Verification Gate
$stmt = $pdo->prepare("SELECT is_verified, full_name, agent_code FROM agent_user WHERE id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();

if (!$agent) {
    // UPDATED: Use auth_url helper
    redirect('auth/logout');
}

// If not verified, load lock screen
if ($agent['is_verified'] == 0) {
    require 'confirm_agent_password.php';
    exit();
}

// 3. Dashboard Logic
$agent_name = $agent['full_name'];
$agent_code = $agent['agent_code'];

$stats = [
    'referrals' => $pdo->query("SELECT count(*) FROM students WHERE agent_id = $agent_id")->fetchColumn(),
    'pending'   => $pdo->query("SELECT count(*) FROM students WHERE agent_id = $agent_id AND status = 'pending'")->fetchColumn(),
    'approved_count' => $pdo->query("SELECT count(*) FROM students WHERE agent_id = $agent_id AND status = 'approved'")->fetchColumn()
];

$est_commission = $stats['approved_count'] * 100000; 

$sql = "SELECT s.*, j.school_name 
        FROM students s 
        JOIN japan_schools j ON s.target_school_id = j.id 
        WHERE s.agent_id = ? 
        ORDER BY s.created_at DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$agent_id]);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Portal | Sheindana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
    </style>
</head>
<body class="text-slate-900">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center border-2 border-[#D4AF37] shadow-md">
                    <span class="text-white font-black text-xs">SD</span>
                </div>
                <div class="leading-tight">
                    <span class="block font-black uppercase text-slate-900 tracking-tight text-sm">Sheindana<span class="text-gold">.Agent</span></span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Partner Portal</span>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="hidden md:block text-right">
                    <div class="text-xs font-black uppercase text-slate-900"><?= htmlspecialchars($agent_name) ?></div>
                    <div class="text-[10px] text-slate-400 font-bold tracking-widest bg-slate-100 px-2 py-0.5 rounded inline-block">
                        ID: <?= htmlspecialchars($agent_code) ?>
                    </div>
                </div>
                <!-- UPDATED: Logout Link -->
                <a href="<?= auth_url('logout') ?>" class="text-slate-400 hover:text-red-500 transition" title="Logout">
                    <i class="fa-solid fa-power-off text-lg"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-2xl text-sm font-bold mb-8 flex items-center gap-3 shadow-sm animate-pulse">
                <i class="fa-solid fa-circle-check text-lg"></i> 
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Header Action -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black uppercase italic text-slate-900">My Dashboard</h1>
                <p class="text-slate-500 text-sm mt-1">Track your student applications and earnings.</p>
            </div>
            <!-- UPDATED: Register Link -->
            <a href="<?= agent_url('register') ?>" class="group bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase hover:bg-gold hover:text-slate-900 transition shadow-xl flex items-center gap-2">
                <i class="fa-solid fa-user-plus group-hover:scale-110 transition-transform"></i> New Registration
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:border-blue-200 transition">
                <div class="absolute -right-6 -top-6 text-[100px] text-slate-50 group-hover:text-blue-50 transition pointer-events-none">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Total Students</p>
                    <h4 class="text-5xl font-black text-slate-900"><?= $stats['referrals'] ?></h4>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:border-orange-200 transition">
                <div class="absolute -right-6 -top-6 text-[100px] text-slate-50 group-hover:text-orange-50 transition pointer-events-none">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Under Review</p>
                    <h4 class="text-5xl font-black text-orange-500"><?= $stats['pending'] ?></h4>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group hover:border-green-200 transition">
                <div class="absolute -right-6 -top-6 text-[100px] text-slate-50 group-hover:text-green-50 transition pointer-events-none">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Est. Earnings</p>
                    <div class="flex items-baseline gap-2">
                        <h4 class="text-4xl font-black text-green-600"><?= number_format($est_commission) ?></h4>
                        <span class="text-xs font-bold text-slate-400">MMK</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="bg-white rounded-[40px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                <h3 class="font-black uppercase text-sm italic tracking-tight text-slate-800">Registration History</h3>
                <span class="text-[10px] font-bold bg-white border border-slate-200 text-slate-400 px-3 py-1 rounded-full uppercase tracking-wide">Last 10 Records</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[800px]">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5">Applicant Details</th>
                            <th class="px-8 py-5">Target Institution</th>
                            <th class="px-8 py-5">Documents</th>
                            <th class="px-8 py-5">Application Status</th>
                            <th class="px-8 py-5 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(count($students) > 0): ?>
                            <?php foreach($students as $student): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-slate-900 text-base"><?= htmlspecialchars($student['full_name']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wide flex items-center gap-1">
                                        <i class="fa-regular fa-id-card"></i> <?= htmlspecialchars($student['nric_passport']) ?>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-xs font-bold text-slate-600 uppercase tracking-tight">
                                        <?= htmlspecialchars($student['school_name']) ?>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <!-- UPDATED: View PDF link uses route to core/view_document -->
                                    <a href="<?= base_url('index.php?route=core/view_document&file=' . urlencode($student['document_path'])) ?>" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black text-red-500 bg-red-50 border border-red-100 px-3 py-1.5 rounded-lg uppercase tracking-wide hover:bg-red-500 hover:text-white transition">
                                        <i class="fa-solid fa-file-pdf"></i> View PDF
                                    </a>
                                </td>
                                <td class="px-8 py-5">
                                    <?php 
                                        $statusClass = match($student['status']) {
                                            'approved' => 'bg-green-100 text-green-700 border-green-200',
                                            'pending' => 'bg-orange-100 text-orange-700 border-orange-200',
                                            'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                            'reviewing' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            default => 'bg-slate-100 text-slate-600 border-slate-200'
                                        };
                                        $statusIcon = match($student['status']) {
                                            'approved' => 'fa-check',
                                            'pending' => 'fa-hourglass',
                                            'rejected' => 'fa-xmark',
                                            default => 'fa-circle'
                                        };
                                    ?>
                                    <span class="<?= $statusClass ?> border px-3 py-1 rounded-full text-[9px] font-black uppercase flex items-center gap-1.5 w-fit">
                                        <i class="fa-solid <?= $statusIcon ?>"></i> <?= htmlspecialchars($student['status']) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right text-xs font-bold text-slate-400">
                                    <?= date('M d, Y', strtotime($student['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fa-regular fa-folder-open text-4xl mb-4 opacity-50"></i>
                                        <p class="text-sm font-bold uppercase tracking-wide">No students registered yet.</p>
                                        <p class="text-xs mt-1">Click "New Registration" to start.</p>
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