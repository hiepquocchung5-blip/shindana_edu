<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Status Update
if (isset($_GET['action']) && $_GET['action'] == 'mark_contacted' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE enquiries SET status = 'contacted' WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    redirect('admin/enquiries');
}

// 3. Fetch Enquiries
$enquiries = $pdo->query("SELECT * FROM enquiries ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enquiry Leads | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">Student <span class="text-[#D4AF37]">Leads</span></div>
            </div>
            <div class="text-xs font-bold uppercase text-slate-400">
                Total Leads: <?= count($enquiries) ?>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Date</th>
                            <th class="px-8 py-6">Name</th>
                            <th class="px-8 py-6">Contact Details</th>
                            <th class="px-8 py-6">Interest</th>
                            <th class="px-8 py-6">Status</th>
                            <th class="px-8 py-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($enquiries as $lead): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-8 py-6 text-xs font-bold text-slate-400">
                                <?= date('M d, Y', strtotime($lead['created_at'])) ?>
                                <div class="font-normal"><?= date('H:i', strtotime($lead['created_at'])) ?></div>
                            </td>
                            <td class="px-8 py-6 font-black text-slate-900 text-base">
                                <?= htmlspecialchars($lead['full_name']) ?>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-slate-700 flex items-center gap-2">
                                    <i class="fa-regular fa-envelope text-[#D4AF37] text-xs"></i> <?= htmlspecialchars($lead['email']) ?>
                                </div>
                                <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                                    <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i> <?= htmlspecialchars($lead['phone']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase border border-blue-100">
                                    <?= htmlspecialchars($lead['interest']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <?php if($lead['status'] == 'new'): ?>
                                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-[9px] font-black uppercase animate-pulse">New</span>
                                <?php else: ?>
                                    <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-[9px] font-black uppercase">Contacted</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <?php if($lead['status'] == 'new'): ?>
                                    <a href="<?= admin_url('enquiries') ?>&action=mark_contacted&id=<?= $lead['id'] ?>" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-[10px] font-bold uppercase hover:bg-[#D4AF37] hover:text-slate-900 transition">
                                        Mark Done
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs"><i class="fa-solid fa-check-double"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($enquiries)): ?>
                            <tr><td colspan="6" class="px-8 py-12 text-center text-slate-400 italic">No enquiries yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>