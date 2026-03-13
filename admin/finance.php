<?php
// admin/finance.php
// Advanced Finance Hub: Invoice Generation & Tracking

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Invoice Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_invoice'])) {
    $agent_id = filter_input(INPUT_POST, 'agent_id', FILTER_VALIDATE_INT);
    $descriptions = $_POST['desc'] ?? []; // Array
    $amounts = $_POST['amount'] ?? [];    // Array
    
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
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, agent_id, total_amount, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$inv_num, $agent_id, $total, $_SESSION['user_id']]);
        $invoice_id = $pdo->lastInsertId();

        // Insert Items
        $stmt_item = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)");
        for ($i = 0; $i < count($descriptions); $i++) {
            $desc = trim($descriptions[$i]);
            $amt = (float)$amounts[$i];
            if (!empty($desc)) {
                $stmt_item->execute([$invoice_id, $desc, $amt]);
            }
        }

        $pdo->commit();
        
        // FIXED ROUTING: Use the custom redirect helper instead of direct header()
        log_activity($pdo, 'INVOICE_CREATED', "Admin {$_SESSION['username']} created invoice {$inv_num} for Agent ID {$agent_id}");
        redirect('admin/finance&view=' . $invoice_id);
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

    if ($view_invoice) {
        $stmt_items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmt_items->execute([$_GET['view']]);
        $view_items = $stmt_items->fetchAll();
    }
}

// Fetch Recent Invoices List
$recent_invoices = $pdo->query("SELECT i.*, a.full_name FROM invoices i JOIN agent_user a ON i.agent_id = a.id ORDER BY i.created_at DESC LIMIT 15")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Hub | <?= h(APP_NAME ?? 'Sheindana') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Noto+Sans+Myanmar:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Premium Print Styles */
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .print-container { box-shadow: none; border: none; max-width: 100%; width: 100%; padding: 20px; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Topbar (Hidden on Print) -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl no-print border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <!-- FIXED ROUTING: admin_url helper -->
                <a href="<?= admin_url('index') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-white hover:text-slate-900 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="leading-none">
                    <div class="font-black text-xl uppercase tracking-tighter">Finance <span class="text-[#D4AF37]">Hub</span></div>
                    <div class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">Invoicing System</div>
                </div>
            </div>
            <button onclick="document.getElementById('newInvoiceModal').showModal()" class="bg-[#D4AF37] text-slate-900 px-6 py-2.5 rounded-xl text-xs font-black uppercase hover:bg-white transition flex items-center gap-2 shadow-lg hover:shadow-yellow-500/20 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus"></i> New Invoice
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 p-4 rounded-xl text-sm font-bold mb-8 flex items-center gap-2 shadow-sm no-print">
                <i class="fa-solid fa-circle-exclamation"></i> <?= h($error) ?>
            </div>
        <?php endif; ?>

        <?php if($view_invoice): ?>
            <!-- ========================================== -->
            <!-- INVOICE VIEW MODE                          -->
            <!-- ========================================== -->
            <div class="max-w-4xl mx-auto bg-white p-10 md:p-16 rounded-[40px] shadow-2xl print-container relative border border-slate-100 overflow-hidden">
                <!-- Watermark -->
                <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none z-0">
                    <div class="text-[150px] font-black italic">SD</div>
                </div>

                <!-- Print Header -->
                <div class="flex justify-between items-start mb-12 border-b border-slate-200 pb-8 relative z-10">
                    <div>
                        <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center border-2 border-[#D4AF37] mb-4 text-[#D4AF37] font-black text-xl shadow-md">SD</div>
                        <h1 class="text-3xl font-black uppercase text-slate-900 tracking-tight">Tax Invoice</h1>
                        <p class="text-xs font-black text-slate-500 uppercase mt-1 tracking-widest"><?= h(ORG_NAME ?? 'Sheindana Global Education Co., Ltd.') ?></p>
                        <p class="text-xs text-slate-400 font-medium mt-1 max-w-xs leading-relaxed"><?= h(ORG_ADDRESS ?? 'Kamayut HQ, Yangon, Myanmar') ?></p>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Invoice No.</div>
                        <div class="text-2xl font-black text-[#D4AF37] mb-3"><?= htmlspecialchars($view_invoice['invoice_number']) ?></div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date Issued</div>
                        <div class="text-sm font-bold text-slate-900"><?= date('d F Y', strtotime($view_invoice['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Bill To Section -->
                <div class="mb-12 relative z-10">
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 w-full md:w-1/2">
                        <div class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-3">Bill To (Partner Agent)</div>
                        <h2 class="text-xl font-black text-slate-900 mb-1"><?= htmlspecialchars($view_invoice['full_name']) ?></h2>
                        <div class="flex flex-col gap-1 mt-2">
                            <p class="text-xs font-bold text-slate-500"><i class="fa-solid fa-id-badge w-4 text-[#D4AF37]"></i> <?= htmlspecialchars($view_invoice['agent_code']) ?></p>
                            <p class="text-xs font-bold text-slate-500"><i class="fa-regular fa-envelope w-4 text-[#D4AF37]"></i> <?= htmlspecialchars($view_invoice['email']) ?></p>
                            <p class="text-xs font-bold text-slate-500"><i class="fa-solid fa-phone w-4 text-[#D4AF37]"></i> <?= htmlspecialchars($view_invoice['phone']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="relative z-10">
                    <table class="w-full text-left mb-12">
                        <thead class="border-b border-slate-200 bg-slate-50/50">
                            <tr>
                                <th class="py-4 px-4 text-[10px] font-black uppercase text-slate-500 tracking-widest">Description</th>
                                <th class="py-4 px-4 text-[10px] font-black uppercase text-slate-500 tracking-widest text-right">Amount (<?= h(APP_CURRENCY ?? 'MMK') ?>)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($view_items as $item): ?>
                            <tr>
                                <td class="py-5 px-4 text-sm font-bold text-slate-700"><?= htmlspecialchars($item['description']) ?></td>
                                <td class="py-5 px-4 text-sm font-black text-slate-900 text-right tracking-tight"><?= number_format($item['amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Total & Sign Off -->
                <div class="flex flex-col md:flex-row justify-between items-end border-t-2 border-slate-200 pt-8 relative z-10">
                    <div class="text-slate-400 text-xs font-medium w-full md:w-1/2 mb-6 md:mb-0">
                        Please make all payments payable to:<br>
                        <strong class="text-slate-600 block mt-1"><?= h(ORG_NAME ?? 'Sheindana Education') ?></strong>
                        If you have any questions concerning this invoice, contact our finance department.
                    </div>
                    <div class="text-right bg-slate-50 p-6 rounded-3xl border border-slate-100 w-full md:w-1/2 md:max-w-xs">
                        <div class="text-[10px] font-black uppercase text-slate-500 tracking-widest mb-1">Total Payable</div>
                        <div class="text-3xl font-black text-slate-900 tracking-tighter">
                            <?= number_format($view_invoice['total_amount']) ?> 
                            <span class="text-sm text-[#D4AF37] ml-1"><?= h(APP_CURRENCY ?? 'MMK') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (No Print) -->
                <div class="absolute top-12 right-12 no-print flex flex-col gap-3">
                    <button onclick="window.print()" class="bg-slate-900 text-white w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-lg hover:-translate-y-1 transform" title="Print / Save PDF">
                        <i class="fa-solid fa-print"></i>
                    </button>
                    <!-- FIXED ROUTING: admin_url helper -->
                    <a href="<?= admin_url('finance') ?>" class="bg-slate-100 text-slate-500 w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition hover:-translate-y-1 transform" title="Close Invoice">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- ========================================== -->
            <!-- DASHBOARD VIEW                             -->
            <!-- ========================================== -->
            
            <!-- Quick Stats -->
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-[#D4AF37] rounded-full blur-[50px] opacity-10 group-hover:opacity-20 transition"></div>
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Total Invoiced (All Time)</div>
                    <div class="text-4xl font-black text-slate-900 tracking-tighter">
                        <?php 
                        $sum = $pdo->query("SELECT SUM(total_amount) FROM invoices WHERE status != 'cancelled'")->fetchColumn(); 
                        echo number_format($sum ?? 0);
                        ?> <span class="text-lg text-[#D4AF37] ml-1"><?= h(APP_CURRENCY ?? 'MMK') ?></span>
                    </div>
                </div>
                
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Invoices Generated</div>
                    <div class="text-4xl font-black text-slate-900 tracking-tighter">
                        <?php echo $pdo->query("SELECT count(*) FROM invoices")->fetchColumn(); ?>
                    </div>
                </div>
                
                <!-- Quick Navigation to Agent Payouts -->
                <a href="<?= admin_url('payouts') ?>" class="bg-slate-900 p-8 rounded-[32px] shadow-xl border border-slate-800 relative overflow-hidden group flex flex-col justify-center items-center text-center transform hover:scale-[1.02] transition">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px]"></div>
                    <i class="fa-solid fa-money-bill-transfer text-3xl text-[#D4AF37] mb-3 relative z-10"></i>
                    <h3 class="text-white font-black uppercase tracking-widest text-sm relative z-10">Agent Payout Ledger</h3>
                    <p class="text-slate-400 text-xs mt-2 relative z-10">Calculate commissions</p>
                </a>
            </div>

            <!-- List -->
            <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black uppercase text-xs tracking-widest text-slate-500">Recent Transactions</h3>
                    <span class="bg-white border border-slate-200 text-[9px] font-black uppercase text-slate-400 px-3 py-1 rounded-full">Last 15</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[800px]">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                            <tr>
                                <th class="px-8 py-5">Invoice #</th>
                                <th class="px-8 py-5">Partner / Agent</th>
                                <th class="px-8 py-5">Date Issued</th>
                                <th class="px-8 py-5 text-right">Total Amount</th>
                                <th class="px-8 py-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($recent_invoices as $inv): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="px-8 py-5">
                                    <div class="font-mono font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-lg inline-block text-xs border border-slate-200 group-hover:border-[#D4AF37] transition">
                                        <?= htmlspecialchars($inv['invoice_number']) ?>
                                    </div>
                                </td>
                                <td class="px-8 py-5 font-bold text-slate-700"><?= htmlspecialchars($inv['full_name']) ?></td>
                                <td class="px-8 py-5 text-slate-500 text-xs font-bold uppercase tracking-wide"><?= date('M d, Y', strtotime($inv['created_at'])) ?></td>
                                <td class="px-8 py-5 text-right font-black text-slate-900 tracking-tight">
                                    <?= number_format($inv['total_amount']) ?> <span class="text-[9px] text-[#D4AF37]"><?= h(APP_CURRENCY ?? 'MMK') ?></span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <!-- FIXED ROUTING: admin_url helper -->
                                    <a href="<?= admin_url('finance') ?>&view=<?= $inv['id'] ?>" class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-blue-600 hover:text-white transition shadow-sm">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_invoices)): ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-16 text-center text-slate-400">
                                        <i class="fa-solid fa-receipt text-4xl mb-3 opacity-30"></i>
                                        <p class="font-bold text-sm uppercase tracking-widest">No invoices generated yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Create Invoice Modal (Native HTML Dialog styling via Tailwind) -->
    <dialog id="newInvoiceModal" class="p-0 rounded-[40px] shadow-2xl w-full max-w-2xl backdrop:bg-slate-900/60 backdrop:backdrop-blur-sm border-none m-auto top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="margin: 0; position: fixed;">
        <div class="bg-white w-[90vw] sm:w-[600px] max-h-[90vh] flex flex-col relative overflow-hidden">
            <!-- Header -->
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white shrink-0">
                <div>
                    <h3 class="font-black italic uppercase text-xl tracking-tight">New Invoice</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Generate Billing Document</p>
                </div>
                <button onclick="document.getElementById('newInvoiceModal').close()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 text-white/50 hover:text-[#D4AF37] transition focus:outline-none">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Form Body (Alpine.js logic for dynamic rows) -->
            <div class="overflow-y-auto p-8 flex-1" x-data="{ items: [{id: 1, desc: '', amount: ''}] }">
                <form method="POST" id="invoiceForm" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 ml-1">Select Partner / Agent</label>
                        <div class="relative">
                            <select name="agent_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all appearance-none cursor-pointer">
                                <option value="" disabled selected>Select an agent...</option>
                                <?php foreach($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>">
                                        <?= htmlspecialchars($agent['full_name']) ?> (<?= htmlspecialchars($agent['agent_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2 mb-2">
                            <label class="block text-[10px] font-black uppercase text-slate-500">Line Items</label>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Amount (<?= h(APP_CURRENCY ?? 'MMK') ?>)</span>
                        </div>
                        
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="flex gap-3 items-start group">
                                <div class="flex-1 relative">
                                    <input type="text" name="desc[]" x-model="item.desc" placeholder="Service Description" required 
                                           class="w-full bg-slate-50 border border-slate-200 p-3.5 rounded-xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all">
                                </div>
                                <div class="w-32 relative">
                                    <input type="number" name="amount[]" x-model="item.amount" placeholder="0.00" min="0" step="any" required 
                                           class="w-full bg-slate-50 border border-slate-200 p-3.5 rounded-xl font-black text-sm text-right outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all font-mono">
                                </div>
                                <button type="button" @click="if(items.length > 1) items.splice(index, 1)" 
                                        class="mt-1 w-10 h-10 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition"
                                        :class="{'opacity-50 cursor-not-allowed': items.length === 1}"
                                        :disabled="items.length === 1">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="items.push({id: Date.now(), desc: '', amount: ''})" 
                                class="mt-2 text-[10px] font-black text-[#D4AF37] hover:text-slate-900 uppercase tracking-widest flex items-center gap-1 transition">
                            <i class="fa-solid fa-plus-circle"></i> Add Another Item
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Action -->
            <div class="p-6 md:p-8 bg-slate-50 border-t border-slate-200 shrink-0">
                <button type="submit" form="invoiceForm" name="create_invoice" class="w-full bg-slate-900 text-white py-4.5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-invoice"></i> Generate & Save Invoice
                </button>
            </div>
        </div>
    </dialog>

</body>
</html>