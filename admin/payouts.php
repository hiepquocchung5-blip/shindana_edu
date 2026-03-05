<?php
// admin/payouts.php
// Advanced Finance: Agent Commission Calculation Engine

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Fetch Agent Commission Data & Revenue
// Joins agent data with their generated invoice totals
$sql = "
    SELECT 
        a.id, a.agent_code, a.full_name, a.agent_type, 
        a.commission_type, a.commission_value,
        COUNT(i.id) as total_invoices,
        COALESCE(SUM(i.total_amount), 0) as total_revenue
    FROM agent_user a
    LEFT JOIN invoices i ON a.id = i.agent_id AND i.status != 'cancelled'
    WHERE a.status = 'active'
    GROUP BY a.id
    ORDER BY total_revenue DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$agents = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Payouts | Shinedana Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white transition"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <div class="font-black text-xl uppercase tracking-tighter">Agent <span class="text-[#D4AF37]">Payouts</span></div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Financial Hub</p>
                </div>
            </div>
            <a href="<?= admin_url('finance') ?>" class="bg-slate-800 text-white px-6 py-2.5 rounded-xl text-xs font-black uppercase hover:bg-slate-700 transition flex items-center gap-2 border border-slate-700">
                <i class="fa-solid fa-file-invoice"></i> Invoices
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-black uppercase italic text-slate-900">Commission Ledger</h1>
                <p class="text-slate-500 text-sm mt-1">Calculated in real-time based on finalized invoices.</p>
            </div>
        </div>

        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Partner Details</th>
                            <th class="px-8 py-6">Commission Structure</th>
                            <th class="px-8 py-6 text-right">Generated Revenue</th>
                            <th class="px-8 py-6 text-right">Total Owed (MMK)</th>
                            <th class="px-8 py-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($agents as $agent): 
                            
                            // Dynamic Math Engine
                            $owed = 0;
                            if ($agent['commission_type'] == 'percentage') {
                                $owed = ($agent['commission_value'] / 100) * $agent['total_revenue'];
                            } elseif ($agent['commission_type'] == 'fixed') {
                                // Assuming fixed value is per invoice/student
                                $owed = $agent['commission_value'] * $agent['total_invoices'];
                            }

                        ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-900 text-lg"><?= htmlspecialchars($agent['full_name']) ?></div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-500">
                                        <?= htmlspecialchars($agent['agent_code']) ?>
                                    </span>
                                    <span class="text-[9px] font-bold uppercase text-[#D4AF37]"><?= htmlspecialchars($agent['agent_type']) ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <?php if($agent['commission_type'] == 'percentage'): ?>
                                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                        <?= floatval($agent['commission_value']) ?>% Cut
                                    </span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                        <?= number_format($agent['commission_value']) ?> Fixed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="font-black text-slate-700 text-base"><?= number_format($agent['total_revenue']) ?></div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Across <?= $agent['total_invoices'] ?> Invoices</div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="font-black text-green-600 text-xl"><?= number_format($owed) ?></div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <?php if($owed > 0): ?>
                                    <button class="bg-[#D4AF37] text-slate-900 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:shadow-lg transition">
                                        Issue Payout
                                    </button>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-slate-300 uppercase">Settled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>