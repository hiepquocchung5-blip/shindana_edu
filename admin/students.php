<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Status Updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = $_GET['action'] == 'approve' ? 'approved' : ($_GET['action'] == 'reject' ? 'rejected' : 'pending');
    
    // Secure update
    $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $_GET['id']])) {
        // Redirect to avoid re-submission
        header("Location: students.php?msg=Application " . $status);
        exit();
    }
}

// 3. Fetch Students with Agent & School Details
// We use LEFT JOIN for japan_schools in case a school was deleted but the record remains
$sql = "
    SELECT s.*, 
           a.full_name as agent_name, a.agent_code,
           j.school_name
    FROM students s
    JOIN agent_user a ON s.agent_id = a.id
    LEFT JOIN japan_schools j ON s.target_school_id = j.id
    ORDER BY s.created_at DESC
";
$students = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Applications | Sheindana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">Student <span class="text-[#D4AF37]">Review</span></div>
            </div>
            <div class="text-xs font-bold uppercase text-slate-400">
                Total Applications: <?= count($students) ?>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-blue-100 text-blue-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-info-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Candidate Info</th>
                            <th class="px-8 py-6">Referring Agent</th>
                            <th class="px-8 py-6">Target Institution</th>
                            <th class="px-8 py-6">Document</th>
                            <th class="px-8 py-6">Current Status</th>
                            <th class="px-8 py-6 text-right">Decision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($students as $student): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-900 text-lg"><?= htmlspecialchars($student['full_name']) ?></div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                    NRIC: <?= htmlspecialchars($student['nric_passport']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold"><?= htmlspecialchars($student['agent_name']) ?></div>
                                <div class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded inline-block">
                                    <?= htmlspecialchars($student['agent_code']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-bold text-slate-600">
                                <?= htmlspecialchars($student['school_name'] ?? 'Unknown School') ?>
                            </td>
                            <td class="px-8 py-6">
                                <a href="../uploads/documents/<?= htmlspecialchars($student['document_path']) ?>" target="_blank" class="text-red-500 font-black text-xs hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-file-pdf"></i> View PDF
                                </a>
                            </td>
                            <td class="px-8 py-6">
                                <?php 
                                    $statusClass = match($student['status']) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-orange-100 text-orange-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        'reviewing' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                ?>
                                <span class="<?= $statusClass ?> px-3 py-1 rounded-full text-[9px] font-black uppercase">
                                    <?= htmlspecialchars($student['status']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <?php if($student['status'] == 'pending' || $student['status'] == 'reviewing'): ?>
                                    <div class="flex justify-end gap-2">
                                        <a href="?action=approve&id=<?= $student['id'] ?>" class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-600 hover:text-white flex items-center justify-center transition" title="Approve">
                                            <i class="fa-solid fa-check"></i>
                                        </a>
                                        <a href="?action=reject&id=<?= $student['id'] ?>" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition" title="Reject" onclick="return confirm('Reject this application?')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[10px] text-slate-300 font-bold uppercase">Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($students) === 0): ?>
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center text-slate-400 font-bold uppercase text-sm">
                                No applications found.
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