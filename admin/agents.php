<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Add Agent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_agent'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $type = $_POST['agent_type'];
    $comm_type = $_POST['commission_type'];
    $comm_val = $_POST['commission_value'];
    
    // Generate Unique Agent Code
    $agent_code = 'AG-' . rand(1000, 9999);
    $password_hash = password_hash('agent123', PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO agent_user (agent_code, full_name, email, phone, password_hash, agent_type, commission_type, commission_value, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$agent_code, $full_name, $email, $phone, $password_hash, $type, $comm_type, $comm_val]);
        $success = "Agent $full_name ($agent_code) onboarded successfully.";
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Email address already registered.";
        } else {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// 3. Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $current_status = $_GET['status'];
    $new_status = ($current_status == 'active') ? 'suspended' : 'active';
    
    $stmt = $pdo->prepare("UPDATE agent_user SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $_GET['id']]);
    
    redirect('admin/agents&msg=' . urlencode("Agent status updated."));
}

// 4. Fetch Agents
$agents = $pdo->query("SELECT * FROM agent_user ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Management | Sheindana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900" x-data="{ showModal: false }">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">Partner <span class="text-[#D4AF37]">Network</span></div>
            </div>
            <button @click="showModal = true" class="bg-[#D4AF37] text-slate-900 px-6 py-2 rounded-xl text-xs font-black uppercase hover:bg-white transition">
                + Onboard Agent
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($success)): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?= $success ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-blue-100 text-blue-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-info-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Agent Table -->
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Agent Profile</th>
                            <th class="px-8 py-6">Contact Info</th>
                            <th class="px-8 py-6">Role Type</th>
                            <th class="px-8 py-6">Setup Status</th>
                            <th class="px-8 py-6">Account</th>
                            <th class="px-8 py-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($agents as $agent): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-900 text-lg"><?= htmlspecialchars($agent['full_name']) ?></div>
                                <div class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded inline-block uppercase tracking-wider">
                                    ID: <?= htmlspecialchars($agent['agent_code']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-slate-600 flex items-center gap-2">
                                    <i class="fa-regular fa-envelope text-slate-400 text-xs"></i> <?= htmlspecialchars($agent['email']) ?>
                                </div>
                                <div class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                    <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i> <?= htmlspecialchars($agent['phone']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full uppercase text-[10px] font-black tracking-wide border border-blue-100">
                                    <?= htmlspecialchars($agent['agent_type']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <?php if($agent['is_verified']): ?>
                                    <span class="text-[10px] font-black uppercase text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100">
                                        <i class="fa-solid fa-check-circle mr-1"></i> Verified
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] font-black uppercase text-orange-500 bg-orange-50 px-2 py-1 rounded border border-orange-100">
                                        <i class="fa-solid fa-hourglass-start mr-1"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6">
                                <?php if($agent['status'] == 'active'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[9px] font-black uppercase flex items-center gap-1 w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Active
                                    </span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[9px] font-black uppercase flex items-center gap-1 w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Suspended
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <!-- Edit Button -->
                                    <a href="<?= admin_url('edit_agent') ?>&id=<?= $agent['id'] ?>" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition" title="Edit Details">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>

                                    <!-- Toggle Button -->
                                    <a href="<?= admin_url('agents') ?>&action=toggle&id=<?= $agent['id'] ?>&status=<?= $agent['status'] ?>" 
                                       class="w-8 h-8 rounded-lg border flex items-center justify-center transition hover:scale-110 <?= $agent['status'] == 'active' ? 'border-red-200 text-red-500 hover:bg-red-50' : 'border-green-200 text-green-500 hover:bg-green-50' ?>"
                                       title="<?= $agent['status'] == 'active' ? 'Suspend Account' : 'Activate Account' ?>">
                                        <i class="fa-solid fa-power-off text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Create Agent Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-2xl overflow-hidden relative z-10">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white">
                <div>
                    <h3 class="font-black italic uppercase text-xl">Onboard New Agent</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Default Password: agent123</p>
                </div>
                <button @click="showModal = false" class="hover:text-[#D4AF37]"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <!-- Form Content -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Full Name</label>
                        <input type="text" name="full_name" required placeholder="e.g. Aung Kyaw" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Agent Type</label>
                        <select name="agent_type" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                            <option value="internal">Internal Staff</option>
                            <option value="external">External Partner</option>
                            <option value="freelancer">Freelancer</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Email</label>
                        <input type="email" name="email" required placeholder="email@example.com" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Phone</label>
                        <input type="text" name="phone" placeholder="09..." class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Commission Type</label>
                        <select name="commission_type" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed (MMK)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Value</label>
                        <input type="number" name="commission_value" required value="10" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <button type="submit" name="add_agent" class="w-full bg-[#D4AF37] text-slate-900 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:shadow-lg transition">
                    Create Account
                </button>
            </form>
        </div>
    </div>

</body>
</html>