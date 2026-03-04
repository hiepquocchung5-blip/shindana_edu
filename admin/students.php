<?php
// admin/students.php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Quick Status Updates (Fallback if not using review panel)
if (isset($_GET['action']) && isset($_GET['id'])) {
    // Basic CSRF protection could be added here if this route is used directly
    $status = match($_GET['action']) {
        'approve' => 'approved',
        'reject' => 'rejected',
        default => 'pending'
    };
    
    $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $_GET['id']])) {
        log_activity($pdo, 'QUICK_STATUS_UPDATE', "Admin {$_SESSION['username']} marked student #{$_GET['id']} as {$status}");
        redirect('admin/students&msg=' . urlencode("Application marked as " . strtoupper($status)));
    }
}

// 3. Dynamic Search & Filter Logic
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

$sql = "
    SELECT s.*, 
           a.full_name as agent_name, a.agent_code,
           j.school_name
    FROM students s
    JOIN agent_user a ON s.agent_id = a.id
    LEFT JOIN japan_schools j ON s.target_school_id = j.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.full_name LIKE ? OR s.nric_passport LIKE ? OR a.agent_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter !== 'all') {
    $sql .= " AND s.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Applications | Sheindana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-white hover:text-slate-900 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="leading-none">
                    <div class="font-black text-xl uppercase tracking-tighter">Student <span class="text-[#D4AF37]">Review</span></div>
                    <div class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">Application Pipeline</div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-blue-100 border border-blue-200 text-blue-800 p-4 rounded-2xl text-sm font-bold mb-6 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-info text-lg"></i> <?= h($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Toolbar -->
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 mb-8">
            <form method="GET" action="index.php" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="route" value="admin/students">
                
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Search Pipeline</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?= h($search) ?>" placeholder="Applicant Name, NRIC, or Agent Code..." class="w-full bg-slate-50 border border-slate-200 pl-11 pr-4 py-3 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 ring-[#D4AF37] transition">
                    </div>
                </div>

                <div class="w-full md:w-64">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Status Filter</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 ring-[#D4AF37] transition appearance-none cursor-pointer">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Applications</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="reviewing" <?= $status_filter === 'reviewing' ? 'selected' : '' ?>>Currently Reviewing</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition flex-1 md:flex-none shadow-md">
                        Filter
                    </button>
                    <?php if(!empty($search) || $status_filter !== 'all'): ?>
                        <a href="<?= admin_url('students') ?>" class="bg-slate-100 text-slate-500 px-4 py-3 rounded-xl font-bold text-sm hover:bg-slate-200 transition flex items-center justify-center" title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Meta Stats -->
        <div class="flex justify-between items-center mb-4 px-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Showing <?= count($students) ?> Result(s)
            </span>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Candidate Info</th>
                            <th class="px-8 py-6">Referring Agent</th>
                            <th class="px-8 py-6">Target Institution</th>
                            <th class="px-8 py-6 text-center">Document</th>
                            <th class="px-8 py-6">Status</th>
                            <th class="px-8 py-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($students as $student): ?>
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-900 text-base mb-1"><?= h($student['full_name']) ?></div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide flex items-center gap-1">
                                    <i class="fa-regular fa-id-card"></i> <?= h($student['nric_passport']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-slate-700"><?= h($student['agent_name']) ?></div>
                                <div class="text-[9px] font-black text-slate-500 bg-slate-100 px-2 py-0.5 rounded inline-block mt-1 tracking-widest">
                                    <?= h($student['agent_code']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-bold text-slate-600 text-xs">
                                <?= h($student['school_name'] ?? 'Not Selected') ?>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <?php if(!empty($student['document_path'])): ?>
                                    <a href="<?= route('core/view_document&file=' . urlencode($student['document_path'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm" title="View PDF Bundle">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs italic">Missing</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6">
                                <?php 
                                    $statusConfig = match($student['status']) {
                                        'approved' => ['bg-green-100 text-green-700 border-green-200', 'fa-check'],
                                        'pending' => ['bg-orange-100 text-orange-700 border-orange-200 animate-pulse', 'fa-hourglass-half'],
                                        'reviewing' => ['bg-blue-100 text-blue-700 border-blue-200', 'fa-magnifying-glass'],
                                        'rejected' => ['bg-red-100 text-red-700 border-red-200', 'fa-xmark'],
                                        default => ['bg-slate-100 text-slate-600 border-slate-200', 'fa-circle-info']
                                    };
                                ?>
                                <span class="<?= $statusConfig[0] ?> border px-3 py-1 rounded-full text-[9px] font-black uppercase flex items-center gap-1.5 w-fit">
                                    <i class="fa-solid <?= $statusConfig[1] ?>"></i> <?= h($student['status']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <!-- Deep link to the detailed review panel we built earlier -->
                                <a href="<?= admin_url('student_review') ?>&id=<?= $student['id'] ?>" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    Review File <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($students)): ?>
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i class="fa-regular fa-folder-open text-4xl mb-4 opacity-50"></i>
                                    <p class="text-sm font-bold uppercase tracking-wide">No applications found in this view.</p>
                                    <?php if(!empty($search) || $status_filter !== 'all'): ?>
                                        <a href="<?= admin_url('students') ?>" class="mt-2 text-xs text-[#D4AF37] font-bold hover:underline">Clear filters to see all records</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>