<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Invoice Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_invoice'])) {
    $agent_id = $_POST['agent_id'];
    $descriptions = $_POST['desc']; // Array
    $amounts = $_POST['amount'];    // Array
    
    // Calculate Total
    $total = 0;
    foreach ($amounts as $amt) {
        $total += (float)$amt;
    }

    // Generate Invoice Number (INV-YEAR-RANDOM)
    $inv_num = 'INV-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

    try {
        $pdo->beginTransaction();

        // Insert Invoice
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, agent_id, total_amount) VALUES (?, ?, ?)");
        $stmt->execute([$inv_num, $agent_id, $total]);
        $invoice_id = $pdo->lastInsertId();

        // Insert Items
        $stmt_item = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)");
        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i])) {
                $stmt_item->execute([$invoice_id, $descriptions[$i], $amounts[$i]]);
            }
        }

        $pdo->commit();
        header("Location: finance.php?view=" . $invoice_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to generate invoice: " . $e->getMessage();
    }
}

// 3. Fetch Data for Views
$agents = $pdo->query("SELECT id, full_name, agent_code FROM agent_user WHERE status='active'")->fetchAll();

// View Specific Invoice Logic
$view_invoice = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT i.*, a.full_name, a.email, a.phone, a.agent_code FROM invoices i JOIN agent_user a ON i.agent_id = a.id WHERE i.id = ?");
    $stmt->execute([$_GET['view']]);
    $view_invoice = $stmt->fetch();

    $stmt_items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $stmt_items->execute([$_GET['view']]);
    $view_items = $stmt_items->fetchAll();
}

// Fetch Recent Invoices List
$recent_invoices = $pdo->query("SELECT i.*, a.full_name FROM invoices i JOIN agent_user a ON i.agent_id = a.id ORDER BY i.created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Hub | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Noto+Sans+Myanmar:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .print-container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Topbar (Hidden on Print) -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl no-print">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">Finance <span class="text-[#D4AF37]">Hub</span></div>
            </div>
            <button onclick="document.getElementById('newInvoiceModal').showModal()" class="bg-[#D4AF37] text-slate-900 px-6 py-2 rounded-xl text-xs font-black uppercase hover:bg-white transition">
                + Create Invoice
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if($view_invoice): ?>
            <!-- INVOICE VIEW MODE -->
            <div class="max-w-3xl mx-auto bg-white p-12 rounded-[20px] shadow-2xl print-container relative">
                <!-- Print Header -->
                <div class="flex justify-between items-start mb-12 border-b-2 border-slate-100 pb-8">
                    <div>
                        <div class="w-12 h-12 bg-slate-900 rounded-xl flex items-center justify-center border-2 border-[#D4AF37] mb-4 text-[#D4AF37] font-black">SD</div>
                        <h1 class="text-3xl font-black uppercase text-slate-900 tracking-tight">Tax Invoice</h1>
                        <p class="text-xs font-bold text-slate-400 uppercase mt-1">Shinedana Global Education Co., Ltd.</p>
                        <p class="text-xs text-slate-400">Kamayut HQ, Yangon, Myanmar</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-slate-500 uppercase">Invoice No.</div>
                        <div class="text-xl font-black text-slate-900 mb-2"><?= htmlspecialchars($view_invoice['invoice_number']) ?></div>
                        <div class="text-sm font-bold text-slate-500 uppercase">Date Issued</div>
                        <div class="text-sm font-bold text-slate-900"><?= date('d M Y', strtotime($view_invoice['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Bill To -->
                <div class="mb-12">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Bill To</div>
                    <h2 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($view_invoice['full_name']) ?></h2>
                    <p class="text-sm text-slate-500">Agent Code: <?= htmlspecialchars($view_invoice['agent_code']) ?></p>
                    <p class="text-sm text-slate-500"><?= htmlspecialchars($view_invoice['email']) ?></p>
                </div>

                <!-- Items -->
                <table class="w-full text-left mb-12">
                    <thead class="border-b-2 border-slate-900">
                        <tr>
                            <th class="py-3 text-xs font-black uppercase text-slate-900">Description</th>
                            <th class="py-3 text-xs font-black uppercase text-slate-900 text-right">Amount (MMK)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($view_items as $item): ?>
                        <tr>
                            <td class="py-4 text-sm font-bold text-slate-600"><?= htmlspecialchars($item['description']) ?></td>
                            <td class="py-4 text-sm font-bold text-slate-900 text-right"><?= number_format($item['amount']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Total -->
                <div class="flex justify-end border-t-2 border-slate-100 pt-6">
                    <div class="text-right">
                        <div class="text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Total Payable</div>
                        <div class="text-4xl font-black text-[#D4AF37]"><?= number_format($view_invoice['total_amount']) ?> <span class="text-sm text-slate-400">MMK</span></div>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="absolute top-12 right-12 no-print">
                    <button onclick="window.print()" class="bg-slate-900 text-white w-12 h-12 rounded-full flex items-center justify-center hover:bg-[#D4AF37] transition shadow-lg" title="Print Invoice">
                        <i class="fa-solid fa-print"></i>
                    </button>
                    <a href="finance.php" class="bg-slate-100 text-slate-400 w-12 h-12 rounded-full flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition mt-2" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- DASHBOARD VIEW -->
            
            <!-- Quick Stats -->
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Total Invoiced</div>
                    <div class="text-3xl font-black text-[#D4AF37]">
                        <?php 
                        $sum = $pdo->query("SELECT SUM(total_amount) FROM invoices")->fetchColumn(); 
                        echo number_format($sum ?? 0);
                        ?> MMK
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-black uppercase text-xs tracking-widest text-slate-500">Recent Transactions</h3>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-4">Invoice #</th>
                            <th class="px-8 py-4">Agent</th>
                            <th class="px-8 py-4">Date</th>
                            <th class="px-8 py-4 text-right">Amount</th>
                            <th class="px-8 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($recent_invoices as $inv): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-8 py-4 font-mono font-bold text-slate-500"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                            <td class="px-8 py-4 font-bold"><?= htmlspecialchars($inv['full_name']) ?></td>
                            <td class="px-8 py-4 text-slate-500 text-xs font-bold"><?= date('M d, Y', strtotime($inv['created_at'])) ?></td>
                            <td class="px-8 py-4 text-right font-black"><?= number_format($inv['total_amount']) ?></td>
                            <td class="px-8 py-4 text-center">
                                <a href="?view=<?= $inv['id'] ?>" class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase hover:underline">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>

    <!-- Create Invoice Modal (Native HTML Dialog) -->
    <dialog id="newInvoiceModal" class="p-0 rounded-[40px] shadow-2xl w-full max-w-2xl backdrop:bg-slate-900/50">
        <div class="bg-slate-900 p-8 flex justify-between items-center text-white">
            <h3 class="font-black italic uppercase text-xl">New Invoice</h3>
            <button onclick="document.getElementById('newInvoiceModal').close()" class="hover:text-[#D4AF37]"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        
        <form method="POST" class="p-8" x-data="{ items: [1] }">
            <div class="mb-6">
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Select Agent</label>
                <select name="agent_id" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    <?php foreach($agents as $agent): ?>
                        <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['full_name']) ?> (<?= htmlspecialchars($agent['agent_code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-4 mb-6">
                <label class="block text-[10px] font-black uppercase text-slate-400">Line Items</label>
                
                <template x-for="i in items">
                    <div class="flex gap-4">
                        <input type="text" name="desc[]" placeholder="Description" required class="flex-1 bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        <input type="number" name="amount[]" placeholder="Amount" required class="w-32 bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </template>

                <button type="button" @click="items.push(items.length + 1)" class="text-xs font-bold text-[#D4AF37] hover:underline uppercase">+ Add Item</button>
            </div>

            <button type="submit" name="create_invoice" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition">
                Generate Invoice
            </button>
        </form>
    </dialog>

</body>
</html>