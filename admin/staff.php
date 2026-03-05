<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check (Must be logged in)
requireAdmin();

// 2. Role Check (Must be Super Admin)
if ($_SESSION['role'] !== 'Admin') {
    redirect('admin/index&error=' . urlencode("Access Denied: Super Admin privileges required."));
}

// 3. Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    
    // Basic Validation
    if (empty($username) || empty($password) || empty($full_name)) {
        $error = "All fields are required.";
    } else {
        // Hash Password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO adm_usr (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hash, $full_name, $role]);
            $success = "Staff member created successfully.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username already exists.";
            } else {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// 4. Handle Delete Staff
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // Prevent Self-Deletion
    if ($_GET['id'] == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM adm_usr WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        redirect('admin/staff&msg=' . urlencode("Staff member removed."));
    }
}

// 5. Fetch Staff List
$staff_list = $pdo->query("SELECT * FROM adm_usr ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900" x-data="{ showModal: false }">

    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">System <span class="text-[#D4AF37]">Staff</span></div>
            </div>
            <button @click="showModal = true" class="bg-[#D4AF37] text-slate-900 px-6 py-2 rounded-xl text-xs font-black uppercase hover:bg-white transition">
                + New Staff
            </button>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6 md:p-12">
        
        <?php if(isset($success) || isset($_GET['msg'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success ?? $_GET['msg']) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="px-8 py-6">Staff Details</th>
                        <th class="px-8 py-6">System Role</th>
                        <th class="px-8 py-6 text-right">Created</th>
                        <th class="px-8 py-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach($staff_list as $staff): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-8 py-6">
                            <div class="font-black text-slate-900 text-lg"><?= htmlspecialchars($staff['full_name']) ?></div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">@<?= htmlspecialchars($staff['username']) ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <?php 
                                $roleColor = match($staff['role']) {
                                    'Admin' => 'bg-slate-900 text-white',
                                    'Finance_mm', 'Finance_jp' => 'bg-green-100 text-green-700',
                                    default => 'bg-blue-50 text-blue-600'
                                };
                            ?>
                            <span class="<?= $roleColor ?> px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                <?= htmlspecialchars($staff['role']) ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right text-xs font-bold text-slate-400">
                            <?= date('M d, Y', strtotime($staff['created_at'])) ?>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <?php if($staff['id'] != $_SESSION['user_id']): ?>
                                <a href="<?= admin_url('staff') ?>&action=delete&id=<?= $staff['id'] ?>" onclick="return confirm('Are you sure? This cannot be undone.')" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white inline-flex items-center justify-center transition">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-[10px] text-slate-300 font-bold uppercase">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Create Staff Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden relative z-10">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white">
                <h3 class="font-black italic uppercase text-xl">New Staff Member</h3>
                <button @click="showModal = false" class="hover:text-[#D4AF37]"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Full Name</label>
                    <input type="text" name="full_name" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">System Role</label>
                    <select name="role" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                        <option value="Admin">Super Administrator</option>
                        <option value="Staff_Myanmar">Myanmar Branch Staff</option>
                        <option value="Staff_Japan">Japan Branch Staff</option>
                        <option value="Finance_mm">Finance (MMK)</option>
                        <option value="Finance_jp">Finance (JPY)</option>
                    </select>
                </div>

                <button type="submit" name="add_staff" class="w-full bg-[#D4AF37] text-slate-900 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:shadow-lg transition">
                    Create Account
                </button>
            </form>
        </div>
    </div>

</body>
</html>