<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Fetch Agent Data
if (!isset($_GET['id'])) {
    redirect('admin/agents');
}
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM agent_user WHERE id = ?");
$stmt->execute([$id]);
$agent = $stmt->fetch();

if (!$agent) {
    die("Agent profile not found.");
}

// 3. Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Update Profile ---
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $type = $_POST['agent_type'];
        $comm_type = $_POST['commission_type'];
        $comm_val = $_POST['commission_value'];

        try {
            // Check for duplicate email (excluding current user)
            $check = $pdo->prepare("SELECT id FROM agent_user WHERE email = ? AND id != ?");
            $check->execute([$email, $id]);
            if($check->rowCount() > 0) {
                $error = "This email is already used by another agent.";
            } else {
                $sql = "UPDATE agent_user SET full_name=?, email=?, phone=?, agent_type=?, commission_type=?, commission_value=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $phone, $type, $comm_type, $comm_val, $id]);
                
                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM agent_user WHERE id = ?");
                $stmt->execute([$id]);
                $agent = $stmt->fetch();
                
                $success = "Profile updated successfully.";
            }
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }

    // --- Reset Password ---
    if (isset($_POST['reset_password'])) {
        $new_pass = $_POST['new_password'];
        if (strlen($new_pass) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE agent_user SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $id]);
            $success = "Password has been manually reset.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Agent | Sheindana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('agents') ?>" class="text-slate-400 hover:text-white transition"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <div class="font-black text-xl uppercase tracking-tighter">Edit <span class="text-[#D4AF37]">Profile</span></div>
            </div>
            <div class="text-xs font-bold uppercase text-slate-400 hidden md:block">
                Editing: <?= htmlspecialchars($agent['agent_code']) ?>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto p-6 md:p-12">
        
        <?php if(isset($success)): ?>
            <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2 shadow-sm animate-pulse">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-3 gap-8">
            
            <!-- Left Column: Main Edit Form -->
            <div class="md:col-span-2 space-y-8">
                <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
                    <div class="bg-slate-50/50 p-8 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="text-xl font-black text-slate-900">Agent Details</h2>
                        <?php if($agent['status'] == 'active'): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">Active User</span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">Suspended</span>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="p-8 space-y-6">
                        <!-- Contact Info -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Identity & Contact</h4>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Full Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($agent['full_name']) ?>" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Email Address</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($agent['email']) ?>" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Phone Number</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($agent['phone']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                                </div>
                            </div>
                        </div>

                        <!-- Commercial Terms -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Commercial Agreement</h4>
                            
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Agent Classification</label>
                                <select name="agent_type" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                                    <option value="internal" <?= $agent['agent_type'] == 'internal' ? 'selected' : '' ?>>Internal Staff</option>
                                    <option value="external" <?= $agent['agent_type'] == 'external' ? 'selected' : '' ?>>External Partner</option>
                                    <option value="freelancer" <?= $agent['agent_type'] == 'freelancer' ? 'selected' : '' ?>>Freelancer</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Commission Type</label>
                                    <select name="commission_type" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                                        <option value="percentage" <?= $agent['commission_type'] == 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                        <option value="fixed" <?= $agent['commission_type'] == 'fixed' ? 'selected' : '' ?>>Fixed Amount (MMK)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Value</label>
                                    <input type="number" step="0.01" name="commission_value" value="<?= htmlspecialchars($agent['commission_value']) ?>" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center gap-4">
                            <button type="submit" name="update_profile" class="flex-1 bg-slate-900 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-lg">
                                Save Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Right Column: Security & Actions -->
            <div class="space-y-6">
                
                <!-- Security Card -->
                <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
                    <div class="bg-red-50 p-6 border-b border-red-100">
                        <h3 class="font-black text-red-900 text-sm uppercase flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i> Security Action
                        </h3>
                    </div>
                    <div class="p-6">
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            Manually reset the agent's password if they are locked out. They will be required to change it again on next login if verification is enabled.
                        </p>
                        <form method="POST">
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">New Password</label>
                            <input type="text" name="new_password" placeholder="Enter new password" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-red-200 mb-4">
                            <button type="submit" name="reset_password" onclick="return confirm('Are you sure you want to manually reset this password?')" class="w-full bg-red-100 text-red-700 py-3 rounded-xl font-bold text-xs uppercase hover:bg-red-600 hover:text-white transition">
                                Reset Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-slate-900 rounded-[32px] shadow-xl p-6 text-white">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Account Metadata</div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Created</span>
                            <span class="font-bold"><?= date('M d, Y', strtotime($agent['created_at'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Last Active</span>
                            <span class="font-bold"><?= $agent['last_active_at'] ? date('M d, Y', strtotime($agent['last_active_at'])) : 'Never' ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Verification</span>
                            <span class="font-bold text-[#D4AF37]"><?= $agent['is_verified'] ? 'Completed' : 'Pending' ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>