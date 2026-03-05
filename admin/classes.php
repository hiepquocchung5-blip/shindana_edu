<?php
// admin/classes.php
// Advanced Class Management System

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Fetch Helper Data
$branches = $pdo->query("SELECT * FROM branches ORDER BY id ASC")->fetchAll();

// 3. Action Handlers

// --- CREATE CLASS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $name = trim($_POST['class_name']);
    $year = $_POST['academic_year'];
    $section = $_POST['section'];
    $capacity = $_POST['capacity'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $shift = $_POST['shift'];
    $description = trim($_POST['description']);
    $duration = $_POST['duration_text'];
    $icon = $_POST['icon'];

    if (!empty($name)) {
        try {
            $pdo->beginTransaction();
            
            $sql = "INSERT INTO class_divisions (class_name, academic_year, section, capacity, start_time, end_time, shift, description, duration_text, icon) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $year, $section, $capacity, $start, $end, $shift, $description, $duration, $icon]);
            $new_id = $pdo->lastInsertId();

            // Auto-sync to all branches (Default: Visible)
            $sql_pivot = "INSERT INTO class_branch_visibility (class_id, branch_id, is_visible) VALUES (?, ?, 1)";
            $stmt_pivot = $pdo->prepare($sql_pivot);
            foreach ($branches as $branch) {
                $stmt_pivot->execute([$new_id, $branch['id']]);
            }

            $pdo->commit();
            redirect('admin/classes&msg=' . urlencode("Class created and synced successfully."));
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// --- UPDATE CLASS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    $id = $_POST['class_id'];
    $name = trim($_POST['class_name']);
    $year = $_POST['academic_year'];
    $section = $_POST['section'];
    $capacity = $_POST['capacity'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $shift = $_POST['shift'];
    $description = trim($_POST['description']);
    $duration = $_POST['duration_text'];

    try {
        $sql = "UPDATE class_divisions SET class_name=?, academic_year=?, section=?, capacity=?, start_time=?, end_time=?, shift=?, description=?, duration_text=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $year, $section, $capacity, $start, $end, $shift, $description, $duration, $id]);
        
        redirect('admin/classes&msg=' . urlencode("Class details updated."));
    } catch (Exception $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}

// --- DELETE CLASS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM class_divisions WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        redirect('admin/classes&msg=' . urlencode("Class deleted successfully."));
    } catch (Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
}

// --- TOGGLE VISIBILITY ---
if (isset($_GET['action']) && $_GET['action'] == 'toggle') {
    $c_id = $_GET['class'];
    $b_id = $_GET['branch'];
    $state = $_GET['state'] ? 0 : 1; // Toggle logic

    // Upsert logic (Insert if not exists, Update if exists)
    $check = $pdo->prepare("SELECT 1 FROM class_branch_visibility WHERE class_id = ? AND branch_id = ?");
    $check->execute([$c_id, $b_id]);
    
    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE class_branch_visibility SET is_visible = ? WHERE class_id = ? AND branch_id = ?");
        $stmt->execute([$state, $c_id, $b_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO class_branch_visibility (class_id, branch_id, is_visible) VALUES (?, ?, ?)");
        $stmt->execute([$c_id, $b_id, $state]);
    }
    
    redirect('admin/classes');
}

// 4. Fetch Data with Search
$search = $_GET['search'] ?? '';
$query = "
    SELECT c.*, 
    GROUP_CONCAT(CONCAT(v.branch_id, ':', v.is_visible)) as visibility_map 
    FROM class_divisions c
    LEFT JOIN class_branch_visibility v ON c.id = v.class_id
    WHERE c.class_name LIKE ? OR c.academic_year LIKE ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%"]);
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Manager | Shinedana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .toggle-checkbox:checked { right: 0; border-color: #D4AF37; }
        .toggle-checkbox:checked + .toggle-label { background-color: #D4AF37; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900" x-data="{ showCreate: false, showEdit: false, editItem: {} }">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white transition"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <div class="font-black text-xl uppercase tracking-tighter">Class <span class="text-[#D4AF37]">Management</span></div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Global Sync Controller</p>
                </div>
            </div>
            <button @click="showCreate = true" class="bg-[#D4AF37] text-slate-900 px-6 py-2.5 rounded-xl text-xs font-black uppercase hover:bg-white transition flex items-center gap-2 shadow-lg hover:shadow-yellow-500/20">
                <i class="fa-solid fa-plus"></i> New Division
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <!-- Alerts -->
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Toolbar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <div class="relative w-full md:w-96">
                <form method="GET" action="index.php">
                    <input type="hidden" name="route" value="admin/classes">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search classes or year..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-[#D4AF37] transition">
                </form>
            </div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Showing <?= count($classes) ?> Active Divisions
            </div>
        </div>

        <!-- Class Table -->
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Course</th>
                            <th class="px-8 py-6">Schedule & Cap</th>
                            <th class="px-8 py-6">Description</th>
                            <th class="px-8 py-6 text-center">Branch Sync</th>
                            <th class="px-8 py-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($classes as $class): ?>
                            <?php 
                                $vis_array = [];
                                if (!empty($class['visibility_map'])) {
                                    $pairs = explode(',', $class['visibility_map']);
                                    foreach($pairs as $p) {
                                        $parts = explode(':', $p);
                                        if(count($parts)===2) $vis_array[$parts[0]] = $parts[1];
                                    }
                                }
                                
                                // Prepare data for Edit Modal
                                $jsonClass = htmlspecialchars(json_encode($class), ENT_QUOTES, 'UTF-8');
                            ?>
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                        <i class="<?= htmlspecialchars($class['icon'] ?? 'fa-solid fa-book') ?>"></i>
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-900 text-base"><?= htmlspecialchars($class['class_name']) ?></div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                            <?= htmlspecialchars($class['academic_year']) ?> • Sec <?= htmlspecialchars($class['section']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-slate-700">
                                    <?= date('H:i', strtotime($class['start_time'])) ?> - <?= date('H:i', strtotime($class['end_time'])) ?>
                                </div>
                                <div class="flex gap-2 mt-1">
                                    <span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded text-[9px] font-bold uppercase border border-blue-100"><?= htmlspecialchars($class['shift']) ?></span>
                                    <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[9px] font-bold uppercase border border-slate-200">Cap: <?= htmlspecialchars($class['capacity']) ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs text-slate-500 max-w-xs truncate" title="<?= htmlspecialchars($class['description']) ?>">
                                    <?= htmlspecialchars($class['description'] ?: 'No description provided.') ?>
                                </p>
                                <span class="text-[9px] font-bold text-[#D4AF37] uppercase tracking-wide"><?= htmlspecialchars($class['duration_text']) ?></span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-2">
                                    <?php foreach($branches as $branch): ?>
                                        <?php 
                                            $is_on = isset($vis_array[$branch['id']]) && $vis_array[$branch['id']] == 1; 
                                            $style = $is_on 
                                                ? 'bg-slate-900 text-white border-slate-900 shadow-md' 
                                                : 'bg-white text-slate-300 border-slate-200 hover:border-slate-400';
                                        ?>
                                        <a href="<?= admin_url('classes') ?>&action=toggle&class=<?= $class['id'] ?>&branch=<?= $branch['id'] ?>&state=<?= $is_on ? 1 : 0 ?>" 
                                           class="w-8 h-8 rounded-lg border flex items-center justify-center font-black text-[9px] transition hover:-translate-y-0.5 <?= $style ?>"
                                           title="Toggle <?= htmlspecialchars($branch['name']) ?>">
                                            <?= htmlspecialchars($branch['code']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editItem = <?= $jsonClass ?>; showEdit = true" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <a href="<?= admin_url('classes') ?>&action=delete&id=<?= $class['id'] ?>" onclick="return confirm('Delete this class division? This cannot be undone.')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($classes)): ?>
                            <tr><td colspan="5" class="p-8 text-center text-slate-400 font-bold text-sm">No classes found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- CREATE CLASS MODAL -->
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-slate-900/60" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-2xl overflow-hidden relative z-10 max-h-[90vh] overflow-y-auto" @click.away="showCreate = false">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white sticky top-0 z-20">
                <div>
                    <h3 class="font-black italic uppercase text-xl">New Division</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Create & Sync</p>
                </div>
                <button @click="showCreate = false" class="hover:text-[#D4AF37]"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Class Name</label>
                        <input type="text" name="class_name" required placeholder="e.g. JLPT N5" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Academic Year</label>
                        <select name="academic_year" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                            <option>2025-2026</option>
                            <option>2026-2027</option>
                        </select>
                    </div>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Section</label>
                        <select name="section" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none"><option>A</option><option>B</option><option>C</option></select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Capacity</label>
                        <input type="number" name="capacity" value="30" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Shift</label>
                        <select name="shift" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none"><option value="morning">Morning</option><option value="evening">Evening</option></select>
                    </div>
                </div>

                <!-- Timing -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Start Time</label>
                        <input type="time" name="start_time" value="09:00" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">End Time</label>
                        <input type="time" name="end_time" value="12:00" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <!-- Extra Info -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Duration</label>
                        <input type="text" name="duration_text" placeholder="e.g. 6 Months" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Icon Class</label>
                        <input type="text" name="icon" value="fa-solid fa-book" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none text-slate-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none" placeholder="Course details..."></textarea>
                </div>

                <button type="submit" name="create_class" class="w-full bg-[#D4AF37] text-slate-900 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:shadow-lg transition">
                    Sync to All Branches
                </button>
            </form>
        </div>
    </div>

    <!-- EDIT CLASS MODAL -->
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-slate-900/60" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-2xl overflow-hidden relative z-10 max-h-[90vh] overflow-y-auto" @click.away="showEdit = false">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white sticky top-0 z-20">
                <div>
                    <h3 class="font-black italic uppercase text-xl">Edit Division</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID: <span x-text="editItem.id"></span></p>
                </div>
                <button @click="showEdit = false" class="hover:text-[#D4AF37]"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <input type="hidden" name="class_id" :value="editItem.id">
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Class Name</label>
                        <input type="text" name="class_name" :value="editItem.class_name" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Academic Year</label>
                        <input type="text" name="academic_year" :value="editItem.academic_year" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Section</label>
                        <input type="text" name="section" :value="editItem.section" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Capacity</label>
                        <input type="number" name="capacity" :value="editItem.capacity" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Shift</label>
                        <select name="shift" x-model="editItem.shift" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                            <option value="morning">Morning</option>
                            <option value="evening">Evening</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Start Time</label>
                        <input type="time" name="start_time" :value="editItem.start_time" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">End Time</label>
                        <input type="time" name="end_time" :value="editItem.end_time" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Duration</label>
                        <input type="text" name="duration_text" :value="editItem.duration_text" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none" x-text="editItem.description"></textarea>
                </div>

                <button type="submit" name="update_class" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:shadow-lg transition">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

</body>
</html>