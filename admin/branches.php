<?php
// admin/branches.php
// Strict Read/Update (RU) Branch Management (Fixed 5 Nodes)

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Update (No Create, No Delete allowed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branch'])) {
    
    // CSRF Protection (if you have it enabled in your forms, good practice)
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if(function_exists('verify_csrf')) verify_csrf($csrf_token);

    $id = filter_input(INPUT_POST, 'branch_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $address = trim($_POST['address']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($name) && !empty($code)) {
        try {
            $sql = "UPDATE branches SET name = ?, code = ?, address = ?, is_active = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $code, $address, $is_active, $id]);
            
            log_activity($pdo, 'UPDATE_BRANCH', "Admin {$_SESSION['username']} updated branch node: $code");
            redirect('admin/branches&msg=' . urlencode("Network Node '$code' updated successfully."));
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Branch Name and Code cannot be empty.";
    }
}

// 3. Fetch Fixed Branches
$stmt = $pdo->query("SELECT * FROM branches ORDER BY id ASC");
$branches = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Network Nodes | Sheindana Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        /* Custom Toggle Switch CSS */
        .toggle-checkbox:checked { right: 0; border-color: #D4AF37; }
        .toggle-checkbox:checked + .toggle-label { background-color: #D4AF37; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900" x-data="{ showEdit: false, editData: {} }">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-white hover:text-slate-900 transition shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="leading-none">
                    <div class="font-black text-xl uppercase tracking-tighter">Branch <span class="text-[#D4AF37]">Network</span></div>
                    <div class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">System locked to 5 Nodes</div>
                </div>
            </div>
            <div class="bg-slate-800 border border-slate-700 px-4 py-2 rounded-lg text-[10px] font-black uppercase text-slate-400 flex items-center gap-2">
                <i class="fa-solid fa-lock text-[#D4AF37]"></i> Structure Locked
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <!-- Alerts -->
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-bold mb-8 flex items-center gap-2 shadow-sm animate-pulse">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 p-4 rounded-xl text-sm font-bold mb-8 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Informational Banner -->
        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm mb-10 flex flex-col md:flex-row items-center gap-6 justify-between overflow-hidden relative">
            <div class="absolute -right-10 -top-10 text-[120px] text-slate-50 pointer-events-none z-0"><i class="fa-solid fa-network-wired"></i></div>
            <div class="relative z-10 max-w-2xl">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Active Academic Centers</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">The system architecture is strictly restricted to these <?= count($branches) ?> unified locations. You may update addresses and operational status, but you cannot create or delete branches.</p>
            </div>
            <div class="relative z-10 text-center bg-slate-50 px-6 py-4 rounded-2xl border border-slate-200 shrink-0">
                <div class="text-4xl font-black text-[#D4AF37]"><?= count($branches) ?></div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Nodes</div>
            </div>
        </div>

        <!-- Branches Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php foreach($branches as $branch): ?>
                <?php $jsonBranch = htmlspecialchars(json_encode($branch), ENT_QUOTES, 'UTF-8'); ?>
                <div class="bg-white rounded-[32px] shadow-lg border border-slate-100 overflow-hidden relative group hover:-translate-y-1 transition-transform duration-300">
                    
                    <!-- Decorative Top Border -->
                    <div class="h-2 w-full <?= $branch['is_active'] ? 'bg-[#D4AF37]' : 'bg-slate-300' ?>"></div>
                    
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 rounded-2xl <?= $branch['is_active'] ? 'bg-slate-900 text-[#D4AF37]' : 'bg-slate-100 text-slate-400' ?> flex items-center justify-center text-2xl shadow-sm">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            
                            <div class="text-right">
                                <?php if($branch['is_active']): ?>
                                    <span class="inline-flex items-center gap-1.5 bg-green-50 border border-green-100 text-green-600 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Online
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 text-slate-500 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Offline
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h3 class="text-2xl font-black text-slate-900 mb-1"><?= htmlspecialchars($branch['name']) ?></h3>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">Node Code: <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-600 border border-slate-200"><?= htmlspecialchars($branch['code']) ?></span></div>
                        
                        <div class="flex items-start gap-3 text-sm font-medium text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100 min-h-[80px]">
                            <i class="fa-solid fa-location-dot mt-1 text-[#D92128]"></i>
                            <span class="leading-relaxed"><?= htmlspecialchars($branch['address']) ?></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-8 py-5 border-t border-slate-50 bg-slate-50/50 flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">ID: BR-<?= str_pad($branch['id'], 4, '0', STR_PAD_LEFT) ?></span>
                        <button @click="editData = <?= $jsonBranch ?>; showEdit = true" class="text-xs font-black uppercase tracking-widest text-[#D4AF37] hover:text-slate-900 transition flex items-center gap-2 focus:outline-none">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Node
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Edit Branch Modal (Alpine.js) -->
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-slate-900/60" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden relative z-10 max-h-[90vh] overflow-y-auto" @click.away="showEdit = false">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white sticky top-0 z-20">
                <div>
                    <h3 class="font-black italic uppercase text-xl">Edit Node Configuration</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Branch ID: <span x-text="editData.id"></span></p>
                </div>
                <button @click="showEdit = false" class="hover:text-[#D4AF37] focus:outline-none"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <!-- CSRF Token (If you use the helper) -->
                <?php if(function_exists('csrf_token')): ?>
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <?php endif; ?>
                
                <input type="hidden" name="branch_id" :value="editData.id">
                
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Branch Name</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Node Code (Shortcode)</label>
                    <input type="text" name="code" x-model="editData.code" required maxlength="10" class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all uppercase">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Full Physical Address</label>
                    <textarea name="address" x-model="editData.address" rows="3" required class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all"></textarea>
                </div>

                <!-- Toggle Switch for Status -->
                <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <div>
                        <div class="text-xs font-black text-slate-900 uppercase tracking-wide">Operational Status</div>
                        <div class="text-[10px] font-bold text-slate-400 mt-0.5">Toggle to set branch offline/online.</div>
                    </div>
                    
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="is_active" id="toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer z-10" :checked="editData.is_active == 1"/>
                        <label for="toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-300 cursor-pointer"></label>
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="button" @click="showEdit = false" class="w-1/3 bg-slate-100 text-slate-600 py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="update_branch" class="w-2/3 bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Node
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>