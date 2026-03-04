<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Form Submission (Add School)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_school'])) {
    $name = trim($_POST['school_name']);
    $region = $_POST['region'];
    $type = $_POST['type'];
    $year = $_POST['est_year'];
    $tuition = $_POST['tuition_fees'];
    $admission = $_POST['admission_months'];
    $web = $_POST['website'];
    $address = $_POST['address_line'];
    $city = $_POST['city'];
    $desc = $_POST['description'];

    if (!empty($name)) {
        $sql = "INSERT INTO japan_schools (school_name, region, type, est_year, tuition_fees, admission_months, website, address_line, city, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $region, $type, $year, $tuition, $admission, $web, $address, $city, $desc])) {
            redirect('admin/japan_schools&msg=' . urlencode("New partner added successfully."));
        } else {
            $error = "Failed to add school.";
        }
    } else {
        $error = "School name is required.";
    }
}

// 3. Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM japan_schools WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    redirect('admin/japan_schools');
}

// 4. Dynamic Search & Filter Logic
$search = $_GET['search'] ?? '';
$region_filter = $_GET['region'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Start Query Construction
$sql = "SELECT * FROM japan_schools WHERE 1=1";
$params = [];

// Apply Search
if (!empty($search)) {
    $sql .= " AND (school_name LIKE ? OR city LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Apply Region Filter
if (!empty($region_filter)) {
    $sql .= " AND region = ?";
    $params[] = $region_filter;
}

// Apply Type Filter
if (!empty($type_filter)) {
    $sql .= " AND type = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY created_at DESC";

// Execute Query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schools = $stmt->fetchAll();
$count = count($schools);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Japan Partners | Sheindana</title>
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
                <div class="font-black text-xl uppercase tracking-tighter">Pacific <span class="text-[#D4AF37]">Database</span></div>
            </div>
            <button @click="showModal = true" class="bg-[#D4AF37] text-slate-900 px-6 py-2 rounded-xl text-xs font-black uppercase hover:bg-white transition">
                + Add Partner
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Toolbar -->
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 mb-8">
            <form method="GET" action="index.php" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="route" value="admin/japan_schools">
                
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1 ml-1">Search</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="School Name or City..." class="w-full bg-slate-50 border border-slate-200 pl-10 pr-4 py-3 rounded-xl text-sm font-bold focus:outline-none focus:border-[#D4AF37]">
                    </div>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1 ml-1">Region</label>
                    <select name="region" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm font-bold focus:outline-none focus:border-[#D4AF37]">
                        <option value="">All Regions</option>
                        <option value="Tokyo" <?= $region_filter == 'Tokyo' ? 'selected' : '' ?>>Tokyo</option>
                        <option value="Osaka" <?= $region_filter == 'Osaka' ? 'selected' : '' ?>>Osaka</option>
                        <option value="Fukuoka" <?= $region_filter == 'Fukuoka' ? 'selected' : '' ?>>Fukuoka</option>
                        <option value="Other" <?= $region_filter == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1 ml-1">Type</label>
                    <select name="type" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm font-bold focus:outline-none focus:border-[#D4AF37]">
                        <option value="">All Types</option>
                        <option value="Language School" <?= $type_filter == 'Language School' ? 'selected' : '' ?>>Language School</option>
                        <option value="University" <?= $type_filter == 'University' ? 'selected' : '' ?>>University</option>
                        <option value="Vocational" <?= $type_filter == 'Vocational' ? 'selected' : '' ?>>Vocational</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-[#D4AF37] hover:text-slate-900 transition flex-1 md:flex-none">
                        Filter
                    </button>
                    <a href="<?= admin_url('japan_schools') ?>" class="bg-slate-100 text-slate-500 px-4 py-3 rounded-xl font-bold text-sm hover:bg-slate-200 transition" title="Reset Filters">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Result Counter -->
        <div class="mb-4 text-xs font-bold text-slate-400 uppercase tracking-widest flex justify-between items-center px-2">
            <span>Found <?= $count ?> Institution(s)</span>
            <?php if(!empty($search) || !empty($region_filter)): ?>
                <span class="text-[#D4AF37]">Filtered Results Active</span>
            <?php endif; ?>
        </div>

        <!-- Schools Table -->
        <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1200px]">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Institution Details</th>
                            <th class="px-8 py-6">Location</th>
                            <th class="px-8 py-6">Type & Intake</th>
                            <th class="px-8 py-6">Est. Costs (JPY)</th>
                            <th class="px-8 py-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($schools as $school): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-900 text-lg"><?= htmlspecialchars($school['school_name']) ?></div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-1">Est. <?= htmlspecialchars($school['est_year']) ?></div>
                                <a href="<?= htmlspecialchars($school['website']) ?>" target="_blank" class="text-[10px] font-bold text-blue-500 hover:underline">
                                    <i class="fa-solid fa-link mr-1"></i> Official Website
                                </a>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-slate-700"><?= htmlspecialchars($school['city']) ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($school['address_line']) ?></div>
                                <span class="inline-block mt-2 bg-slate-100 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wide">
                                    <?= htmlspecialchars($school['region']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <?php
                                    $typeColor = match($school['type']) {
                                        'University' => 'text-purple-600 bg-purple-100',
                                        'Vocational' => 'text-orange-600 bg-orange-100',
                                        default => 'text-blue-600 bg-blue-100'
                                    };
                                ?>
                                <span class="<?= $typeColor ?> px-3 py-1 rounded-full text-[10px] font-black uppercase mb-2 inline-block">
                                    <?= htmlspecialchars($school['type']) ?>
                                </span>
                                <div class="text-[10px] font-bold text-slate-500">
                                    <i class="fa-regular fa-calendar mr-1"></i> <?= htmlspecialchars($school['admission_months']) ?>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-bold text-slate-700">
                                ¥<?= number_format($school['tuition_fees']) ?>
                                <span class="block text-[9px] text-slate-400 font-normal">Per Year (Est)</span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <!-- Edit Button -->
                                    <a href="<?= admin_url('edit_japan_school') ?>&id=<?= $school['id'] ?>" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white inline-flex items-center justify-center transition" title="Edit School">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <!-- Delete Button -->
                                    <a href="<?= admin_url('japan_schools') ?>&action=delete&id=<?= $school['id'] ?>" onclick="return confirm('Are you sure you want to remove this school from the database?')" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white inline-flex items-center justify-center transition" title="Delete">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($schools) === 0): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-school-circle-xmark text-4xl mb-4 opacity-50"></i>
                                        <p class="text-sm font-bold uppercase tracking-wide">No partners found.</p>
                                        <p class="text-xs mt-1">Try adjusting your filters or search terms.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add School Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-2xl overflow-hidden relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white sticky top-0 z-20">
                <h3 class="font-black italic uppercase text-xl">New Partner</h3>
                <button @click="showModal = false" class="hover:text-[#D4AF37]"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <!-- Core Details -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Core Details</h4>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">School Name</label>
                        <input type="text" name="school_name" required placeholder="e.g. Tokyo Kokusai Academy" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Type</label>
                            <select name="type" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                                <option value="Language School">Language School</option>
                                <option value="University">University</option>
                                <option value="Vocational">Vocational</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Established Year</label>
                            <input type="number" name="est_year" placeholder="1985" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Location</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Region</label>
                            <select name="region" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                                <option value="Tokyo">Tokyo</option>
                                <option value="Osaka">Osaka</option>
                                <option value="Fukuoka">Fukuoka</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">City</label>
                            <input type="text" name="city" placeholder="e.g. Shinjuku" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Full Address</label>
                        <input type="text" name="address_line" placeholder="1-2-3 Street Name, Building Name" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <!-- Financials & Intake -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Costs & Admission</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Est. Annual Tuition (JPY)</label>
                            <input type="number" name="tuition_fees" placeholder="750000" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Intake Months</label>
                            <input type="text" name="admission_months" placeholder="April, October" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Website URL</label>
                        <input type="url" name="website" placeholder="https://" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Short Description</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none"></textarea>
                </div>

                <button type="submit" name="add_school" class="w-full bg-[#D4AF37] text-slate-900 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:shadow-lg transition">
                    Add to Pacific Database
                </button>
            </form>
        </div>
    </div>

</body>
</html>