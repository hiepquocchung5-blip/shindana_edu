<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Fetch Logs (Limit 100)
$stmt = $pdo->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs | Sheindana</title>
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
                <div class="font-black text-xl uppercase tracking-tighter">System <span class="text-[#D4AF37]">Logs</span></div>
            </div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Last 100 Events
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[800px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Timestamp</th>
                            <th class="px-8 py-6">User Role</th>
                            <th class="px-8 py-6">Action Type</th>
                            <th class="px-8 py-6">Details</th>
                            <th class="px-8 py-6 text-right">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($logs as $log): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-8 py-4 text-xs font-bold text-slate-500 font-mono">
                                <?= date('M d H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td class="px-8 py-4">
                                <?php if($log['user_role'] === 'Admin'): ?>
                                    <span class="bg-slate-900 text-white px-2 py-1 rounded text-[10px] font-bold uppercase">Admin</span>
                                <?php elseif($log['user_role'] === 'agent'): ?>
                                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-[10px] font-bold uppercase">Agent</span>
                                <?php else: ?>
                                    <span class="bg-slate-100 text-slate-500 px-2 py-1 rounded text-[10px] font-bold uppercase">System</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-4">
                                <?php 
                                    $actionColor = 'text-slate-600';
                                    if(strpos($log['action'], 'LOGIN') !== false) $actionColor = 'text-green-600';
                                    if(strpos($log['action'], 'DELETE') !== false) $actionColor = 'text-red-600';
                                    if(strpos($log['action'], 'UPDATE') !== false) $actionColor = 'text-blue-600';
                                    if(strpos($log['action'], 'FAILED') !== false) $actionColor = 'text-red-600 font-black';
                                ?>
                                <span class="font-bold text-xs uppercase <?= $actionColor ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-4 text-sm text-slate-600 truncate max-w-xs" title="<?= htmlspecialchars($log['details']) ?>">
                                <?= htmlspecialchars($log['details']) ?>
                            </td>
                            <td class="px-8 py-4 text-right text-xs font-mono text-slate-400">
                                <?= htmlspecialchars($log['ip_address']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="5" class="px-8 py-12 text-center text-slate-400 italic">No activity recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>