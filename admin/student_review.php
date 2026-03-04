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

// 3. Handle Status Update (with CSRF consideration if added globally)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $student_id]);
        
        // Log the decision
        log_activity($pdo, 'APPLICATION_REVIEW', "Admin {$_SESSION['username']} marked student #{$student_id} as {$new_status}");
        
        redirect("admin/student_review&id={$student_id}&msg=" . urlencode("Application status updated to " . strtoupper($new_status)));
    } catch (Exception $e) {
        $error = "Failed to update status.";
    }
}

// 4. Fetch Comprehensive Student Data
$sql = "
    SELECT s.*, 
           a.full_name as agent_name, a.agent_code, a.phone as agent_phone,
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
    <title>Review: <?= h($student['full_name']) ?> | Sheindana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 h-screen overflow-hidden flex flex-col">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white px-6 py-4 shadow-xl shrink-0 z-40">
        <div class="max-w-[1600px] mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('students') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-white hover:text-slate-900 transition">
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
                    'approved' => 'bg-green-500 text-slate-900',
                    'pending' => 'bg-orange-500 text-white',
                    'reviewing' => 'bg-blue-500 text-white',
                    'rejected' => 'bg-red-500 text-white',
                    default => 'bg-slate-500 text-white'
                };
            ?>
            <div class="<?= $statusConfig ?> px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-white/50 animate-pulse"></span>
                <?= h($student['status']) ?>
            </div>
        </div>
    </nav>

    <!-- Main Content (Split Screen) -->
    <main class="flex-1 overflow-hidden flex flex-col lg:flex-row max-w-[1600px] w-full mx-auto w-full">
        
        <!-- Left Panel: Data & Actions -->
        <div class="w-full lg:w-1/3 xl:w-1/4 bg-white border-r border-slate-200 overflow-y-auto p-6 md:p-8 flex flex-col gap-8">
            
            <?php if(isset($_GET['msg'])): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-xl text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> <?= h($_GET['msg']) ?>
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
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-black">
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
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl font-bold text-sm outline-none focus:border-[#D4AF37] transition">
                        <option value="pending" <?= $student['status'] == 'pending' ? 'selected' : '' ?>>Pending / Awaiting Review</option>
                        <option value="reviewing" <?= $student['status'] == 'reviewing' ? 'selected' : '' ?>>Currently Reviewing</option>
                        <option value="approved" <?= $student['status'] == 'approved' ? 'selected' : '' ?>>Approve & Forward to Japan</option>
                        <option value="rejected" <?= $student['status'] == 'rejected' ? 'selected' : '' ?>>Reject Application</option>
                    </select>

                    <button type="submit" name="update_status" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Decision
                    </button>
                </form>
            </div>

        </div>

        <!-- Right Panel: PDF Vault Viewer -->
        <div class="w-full lg:w-2/3 xl:w-3/4 bg-slate-200 p-4 md:p-6 h-full">
            <div class="bg-white w-full h-full rounded-[32px] shadow-inner border border-slate-300 overflow-hidden relative">
                
                <!-- PDF Toolbar -->
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-3 flex justify-between items-center absolute top-0 w-full z-10">
                    <div class="flex items-center gap-2 text-slate-500 font-bold text-xs">
                        <i class="fa-solid fa-file-pdf text-red-500 text-base"></i>
                        Document_Bundle.pdf
                    </div>
                    <a href="<?= route('core/view_document&file=' . urlencode($student['document_path'])) ?>" target="_blank" class="bg-white border border-slate-200 text-slate-600 px-4 py-1.5 rounded-lg text-[10px] font-black uppercase hover:bg-slate-50 transition shadow-sm">
                        Open in New Tab
                    </a>
                </div>

                <!-- PDF Iframe -->
                <iframe 
                    src="<?= route('core/view_document&file=' . urlencode($student['document_path'])) ?>#toolbar=0" 
                    class="w-full h-full pt-12 border-none bg-slate-800"
                    title="Student Document Vault">
                </iframe>
                
            </div>
        </div>

    </main>

</body>
</html>