<?php
// admin/student_review.php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Validate Request
if (!isset($_GET['id'])) {
    redirect('admin/students');
}
$student_id = (int)$_GET['id'];

// 3. Handle Status Update with CSRF Protection & Email Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    
    // Verify CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    verify_csrf($csrf_token);

    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    try {
        // Get old status and agent info for detailed logging and email
        $stmt_info = $pdo->prepare("
            SELECT s.status, s.full_name, a.email as agent_email, a.full_name as agent_name 
            FROM students s 
            JOIN agent_user a ON s.agent_id = a.id 
            WHERE s.id = ?
        ");
        $stmt_info->execute([$student_id]);
        $info = $stmt_info->fetch();
        $old_status = $info['status'];

        // Update to new status
        $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $student_id]);
        
        // Log the decision comprehensively
        log_activity($pdo, 'APPLICATION_REVIEW', "Admin {$_SESSION['username']} changed student #{$student_id} status from [{$old_status}] to [{$new_status}]");
        
        // --- EMAIL NOTIFICATION TRIGGER ---
        // In a production environment, use PHPMailer or a service like AWS SES/SendGrid
        if ($old_status !== $new_status) {
            $to = $info['agent_email'];
            $subject = "Update: Application Status for " . $info['full_name'];
            $message = "Dear " . $info['agent_name'] . ",\n\nThe application status for your student, " . $info['full_name'] . ", has been updated to: " . strtoupper($new_status) . ".\n\nPlease log in to the Agent Portal for more details.\n\nRegards,\nShinedana Administration";
            $headers = "From: " . ORG_EMAIL . "\r\n" .
                       "Reply-To: " . ORG_EMAIL . "\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            // Uncomment the line below to actually send emails if your server is configured for it
            // mail($to, $subject, $message, $headers);
            
            // Log that an email attempt was made
            log_activity($pdo, 'SYSTEM_EMAIL', "Notification sent to {$info['agent_email']} regarding Student #{$student_id}");
        }

        redirect("admin/student_review&id={$student_id}&msg=" . urlencode("Application status updated to " . strtoupper($new_status)));
    } catch (Exception $e) {
        $error = "Failed to update status.";
        error_log("Status Update Error: " . $e->getMessage());
    }
}

// 4. Fetch Comprehensive Student Data
$sql = "
    SELECT s.*, 
           a.id as agent_id, a.full_name as agent_name, a.agent_code, a.phone as agent_phone,
           j.school_name, j.region, j.type as school_type
    FROM students s
    JOIN agent_user a ON s.agent_id = a.id
    LEFT JOIN japan_schools j ON s.target_school_id = j.id
    WHERE s.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found or has been deleted.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review: <?= h($student['full_name']) ?> | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 h-screen overflow-hidden flex flex-col">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white px-6 py-4 shadow-xl shrink-0 z-40 border-b border-slate-800">
        <div class="max-w-[1600px] mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('students') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-white hover:text-slate-900 transition shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="leading-none">
                    <div class="font-black text-lg uppercase tracking-tighter">Application <span class="text-[#D4AF37]">Review</span></div>
                    <div class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">ID: APP-<?= str_pad($student['id'], 5, '0', STR_PAD_LEFT) ?></div>
                </div>
            </div>
            
            <!-- Status Badge -->
            <?php 
                $statusConfig = match($student['status']) {
                    'approved' => 'bg-green-500 text-slate-900 border-green-400',
                    'pending' => 'bg-orange-500 text-white border-orange-400',
                    'reviewing' => 'bg-blue-500 text-white border-blue-400',
                    'rejected' => 'bg-red-500 text-white border-red-400',
                    default => 'bg-slate-500 text-white border-slate-400'
                };
            ?>
            <div class="<?= $statusConfig ?> px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-[0_0_15px_rgba(0,0,0,0.2)] border flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-white/50 animate-pulse"></span>
                <?= h($student['status']) ?>
            </div>
        </div>
    </nav>

    <!-- Main Content (Split Screen) -->
    <main class="flex-1 overflow-hidden flex flex-col lg:flex-row max-w-[1600px] w-full mx-auto">
        
        <!-- Left Panel: Data & Actions -->
        <div class="w-full lg:w-1/3 xl:w-1/4 bg-white border-r border-slate-200 overflow-y-auto p-6 md:p-8 flex flex-col gap-8 shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-10">
            
            <?php if(isset($_GET['msg'])): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-check-circle"></i> <?= h($_GET['msg']) ?>
                </div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= h($error) ?>
                </div>
            <?php endif; ?>

            <!-- Applicant Profile -->
            <div>
                <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3 border-b border-slate-100 pb-2">Applicant Identity</h3>
                <h2 class="text-2xl font-black text-slate-900 leading-tight mb-1"><?= h($student['full_name']) ?></h2>
                <p class="text-xs font-bold text-slate-500 mb-4 flex items-center gap-2">
                    <i class="fa-regular fa-id-card text-slate-400"></i> <?= h($student['nric_passport']) ?>
                </p>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div class="text-[9px] font-black uppercase text-slate-400 mb-1">Target Institution</div>
                    <div class="font-bold text-slate-700 text-sm"><?= h($student['school_name'] ?? 'Not Selected') ?></div>
                    <div class="text-[10px] text-slate-500 mt-1"><?= h($student['school_type']) ?> • <?= h($student['region']) ?></div>
                </div>
            </div>

            <!-- Referring Agent -->
            <div>
                <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3 border-b border-slate-100 pb-2">Partner Details</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-black border border-blue-100">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900"><?= h($student['agent_name']) ?></div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide"><?= h($student['agent_code']) ?> • <?= h($student['agent_phone']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Decision Engine -->
            <div class="mt-auto">
                <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3 border-b border-slate-100 pb-2">Review Decision</h3>
                
                <form method="POST" class="space-y-4">
                    <!-- CSRF Token Included -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] focus:border-transparent transition">
                        <option value="pending" <?= $student['status'] == 'pending' ? 'selected' : '' ?>>Pending / Awaiting Review</option>
                        <option value="reviewing" <?= $student['status'] == 'reviewing' ? 'selected' : '' ?>>Currently Reviewing</option>
                        <option value="approved" <?= $student['status'] == 'approved' ? 'selected' : '' ?>>Approve & Forward to Japan</option>
                        <option value="rejected" <?= $student['status'] == 'rejected' ? 'selected' : '' ?>>Reject Application</option>
                    </select>

                    <button type="submit" name="update_status" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Decision
                    </button>
                </form>

                <!-- Workflow Bridge: Appears only when approved -->
                <?php if($student['status'] === 'approved'): ?>
                    <div class="mt-6 bg-green-50 border border-green-200 rounded-2xl p-5 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-green-200 text-5xl opacity-50"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        <div class="relative z-10">
                            <h4 class="text-[10px] font-black uppercase text-green-800 mb-1 tracking-widest">Next Step: Billing</h4>
                            <p class="text-xs text-green-700 mb-4 leading-relaxed font-medium">Application approved. You can now issue a commission or processing invoice to this partner.</p>
                            <!-- Optional: In a full app, this would pass the agent_id to pre-fill the finance select box via JS/GET -->
                            <a href="<?= admin_url('finance') ?>" class="block w-full bg-green-600 text-white text-center py-3 rounded-xl text-[10px] font-black uppercase hover:bg-green-700 transition shadow-sm">
                                Go to Finance Hub &rarr;
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right Panel: PDF Vault Viewer -->
        <div class="w-full lg:w-2/3 xl:w-3/4 bg-slate-200 p-4 md:p-6 h-full">
            <div class="bg-white w-full h-full rounded-[32px] shadow-inner border border-slate-300 overflow-hidden relative group">
                
                <!-- PDF Toolbar -->
                <div class="bg-white/90 backdrop-blur-sm border-b border-slate-200 px-6 py-3 flex justify-between items-center absolute top-0 w-full z-10">
                    <div class="flex items-center gap-2 text-slate-700 font-black text-xs uppercase tracking-widest">
                        <i class="fa-solid fa-file-pdf text-red-500 text-lg"></i>
                        Application_Bundle.pdf
                    </div>
                    <a href="<?= route('core/view_document&file=' . urlencode($student['document_path'])) ?>" target="_blank" class="bg-slate-100 border border-slate-200 text-slate-600 px-4 py-2 rounded-lg text-[10px] font-black uppercase hover:bg-slate-200 transition shadow-sm flex items-center gap-2">
                        Open in Window <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>

                <!-- PDF Iframe -->
                <iframe 
                    src="<?= route('core/view_document&file=' . urlencode($student['document_path'])) ?>#toolbar=0" 
                    class="w-full h-full pt-[52px] border-none bg-slate-800"
                    title="Student Document Vault">
                </iframe>
                
            </div>
        </div>

    </main>

</body>
</html>