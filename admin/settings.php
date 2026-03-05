<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// 2. Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    
    if (!empty($full_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE adm_usr SET full_name = ? WHERE id = ?");
            if ($stmt->execute([$full_name, $user_id])) {
                $_SESSION['full_name'] = $full_name; // Update session immediately
                $success = "Profile details updated successfully.";
            } else {
                $error = "Failed to update profile.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Full Name cannot be empty.";
    }
}

// 3. Handle Password Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Fetch current user hash
    $stmt = $pdo->prepare("SELECT password_hash FROM adm_usr WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_pass, $user['password_hash'])) {
        if ($new_pass === $confirm_pass) {
            if (strlen($new_pass) >= 6) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE adm_usr SET password_hash = ? WHERE id = ?");
                $update->execute([$new_hash, $user_id]);
                $success = "Security credentials updated.";
            } else {
                $error = "New password must be at least 6 characters.";
            }
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">My <span class="text-[#D4AF37]">Settings</span></div>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto p-6 md:p-12">
        
        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- User Identity Card -->
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden mb-8">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-900 rounded-full flex items-center justify-center text-white text-2xl font-black">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900"><?= htmlspecialchars($_SESSION['full_name']) ?></h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                        <span class="bg-slate-200 px-2 py-0.5 rounded text-slate-600">@<?= htmlspecialchars($_SESSION['username']) ?></span>
                        <span class="ml-2 text-[#D4AF37]"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Update Form -->
        <div class="bg-white rounded-[32px] shadow-lg border border-slate-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-slate-50">
                <h3 class="text-sm font-black uppercase text-slate-400 tracking-widest">General Information</h3>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Full Display Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name']) ?>" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                </div>
                <button type="submit" name="update_profile" class="w-full bg-slate-100 text-slate-600 py-3 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-slate-200 transition">
                    Save Profile
                </button>
            </form>
        </div>
            
        <!-- Security Update Form -->
        <div class="bg-white rounded-[32px] shadow-lg border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50">
                <h3 class="text-sm font-black uppercase text-slate-400 tracking-widest">Security</h3>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Current Password</label>
                    <input type="password" name="current_password" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">New Password</label>
                        <input type="password" name="new_password" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Confirm New</label>
                        <input type="password" name="confirm_password" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                </div>

                <button type="submit" name="update_password" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-lg">
                    Update Password
                </button>
            </form>
        </div>
    </main>

</body>
</html>