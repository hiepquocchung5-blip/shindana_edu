<?php
// agent/confirm_agent_password.php
// Loaded by agent/index.php if is_verified == 0

// Handle Password Update Submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $agent_id = $_SESSION['user_id'];

    if (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        // Update Password and Set Verified Status
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("UPDATE agent_user SET password_hash = ?, is_verified = 1 WHERE id = ?");
            if ($stmt->execute([$new_hash, $agent_id])) {
                // UPDATED: Use redirect helper to keep the route active
                redirect('agent/index&msg=' . urlencode("Password updated! Welcome to your dashboard."));
            } else {
                $error = "Failed to update password.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup Account | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center p-6">

    <div class="bg-white max-w-md w-full rounded-[40px] p-10 shadow-2xl relative overflow-hidden">
        <!-- Decorative Circle -->
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#D4AF37] rounded-full opacity-20 blur-xl"></div>

        <div class="relative z-10">
            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-2xl mb-6">
                🔒
            </div>
            
            <h1 class="text-2xl font-black uppercase text-slate-900 mb-2">Security Setup</h1>
            <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                Welcome to the Partner Network. For your security, please update your default password to activate your account.
            </p>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-xs font-bold mb-6 flex items-center gap-2">
                    <span>⚠️</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Form Action: Empty submits to the current URL (index.php?route=agent/index), which is exactly what we want -->
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">New Password</label>
                    <input type="password" name="new_password" required class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition" placeholder="••••••••">
                </div>

                <button type="submit" name="update_password" class="w-full bg-[#D4AF37] text-slate-900 py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:shadow-lg hover:bg-yellow-400 transition mt-2">
                    Activate Account
                </button>
            </form>
        </div>
    </div>

</body>
</html>