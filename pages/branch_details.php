<?php
// pages/branch_details.php
// loaded via index.php?route=pages/branch_details&id=1

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Get Branch ID
if (!isset($_GET['id'])) {
    redirect('');
}
$branch_id = $_GET['id'];

// 2. Fetch Branch Info
$stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ?");
$stmt->execute([$branch_id]);
$branch = $stmt->fetch();

if (!$branch) {
    die("Branch not found.");
}

// 3. Fetch Active Classes at this Branch
// This logic finds classes that are toggled "ON" for this specific branch
$sql = "
    SELECT cd.* FROM class_divisions cd 
    JOIN class_branch_visibility cbv ON cd.id = cbv.class_id 
    WHERE cbv.branch_id = ? AND cbv.is_visible = 1 AND cd.status = 'active'
    ORDER BY cd.start_time ASC
";
$stmt_classes = $pdo->prepare($sql);
$stmt_classes->execute([$branch_id]);
$classes = $stmt_classes->fetchAll();

// Include Header
require_once '../includes/header.php';
?>

<!-- Header -->
<header class="bg-slate-900 text-white relative overflow-hidden py-24 px-6">
    <div class="absolute inset-0 bg-slate-800 opacity-50"></div>
    <!-- Decor -->
    <div class="absolute -right-20 -top-20 w-96 h-96 bg-gold rounded-full blur-[150px] opacity-20"></div>

    <div class="max-w-6xl mx-auto relative z-10 flex flex-col md:flex-row items-center gap-10">
        <div class="w-32 h-32 bg-white rounded-[30px] flex items-center justify-center text-5xl text-gold shadow-2xl shrink-0">
            <i class="fa-solid fa-building"></i>
        </div>
        <div class="text-center md:text-left">
            <span class="bg-gold text-slate-900 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 inline-block">Academic Center</span>
            <h1 class="text-5xl md:text-7xl font-black mb-4 uppercase leading-none"><?= h($branch['name']) ?></h1>
            <p class="text-xl text-slate-300 font-medium flex items-center justify-center md:justify-start gap-3">
                <i class="fa-solid fa-map-pin text-gold"></i> <?= h($branch['address']) ?>
            </p>
        </div>
    </div>
</header>

<!-- Content -->
<main class="max-w-6xl mx-auto px-6 py-16">
    
    <div class="flex flex-col md:flex-row gap-12">
        
        <!-- Sidebar Info -->
        <div class="md:w-1/3 space-y-8">
            <div class="bg-white p-8 rounded-[32px] shadow-lg border border-slate-100 sticky top-24">
                <h3 class="font-black uppercase text-sm text-slate-400 tracking-widest mb-6 border-b pb-4">Contact Center</h3>
                <ul class="space-y-6">
                    <li>
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Reception</div>
                        <div class="font-bold text-slate-900 text-lg"><?= ORG_PHONE ?></div>
                    </li>
                    <li>
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Email</div>
                        <div class="font-bold text-slate-900"><?= ORG_EMAIL ?></div>
                    </li>
                    <li>
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Operating Hours</div>
                        <div class="font-bold text-slate-900">Mon - Sat: 9:00 AM - 5:00 PM</div>
                    </li>
                </ul>
                <a href="https://maps.google.com/?q=<?= urlencode($branch['address']) ?>" target="_blank" class="block w-full bg-slate-900 text-white text-center py-4 rounded-2xl font-black text-xs uppercase mt-8 hover:bg-gold hover:text-slate-900 transition shadow-xl">
                    Get Directions
                </a>
            </div>
        </div>

        <!-- Class List -->
        <div class="md:w-2/3">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-black italic text-slate-900 uppercase">Active Classes</h2>
                    <p class="text-slate-500 text-sm mt-1">Courses currently open for enrollment at this location.</p>
                </div>
                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full"><?= count($classes) ?> Available</span>
            </div>

            <?php if(count($classes) > 0): ?>
                <div class="grid gap-6">
                    <?php foreach($classes as $cls): ?>
                    <div class="bg-white p-8 rounded-[32px] border border-slate-100 hover:border-gold transition shadow-sm hover:shadow-xl group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gold/5 rounded-bl-[100px] pointer-events-none"></div>
                        
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <h3 class="text-2xl font-black text-slate-900 group-hover:text-gold transition"><?= h($cls['class_name']) ?></h3>
                            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-gold group-hover:text-slate-900 transition">
                                <i class="<?= h($cls['icon']) ?>"></i>
                            </div>
                        </div>
                        
                        <p class="text-sm text-slate-500 mb-6 line-clamp-2"><?= h($cls['description']) ?></p>
                        
                        <div class="flex flex-wrap gap-4 pt-4 border-t border-slate-50">
                            <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest">Schedule</span>
                                <span class="font-bold text-sm text-slate-700">
                                    <?= date('h:i A', strtotime($cls['start_time'])) ?> - <?= date('h:i A', strtotime($cls['end_time'])) ?>
                                </span>
                            </div>
                            <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest">Section</span>
                                <span class="font-bold text-sm text-slate-700"><?= h($cls['section']) ?></span>
                            </div>
                            <div class="ml-auto flex items-center">
                                <a href="<?= base_url('index.php?route=pages/class_details&name=' . urlencode($cls['class_name'])) ?>" class="text-xs font-black uppercase text-slate-400 hover:text-gold transition flex items-center gap-2">
                                    View Full Details <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 rounded-[32px] p-16 text-center border-2 border-dashed border-slate-200">
                    <i class="fa-regular fa-calendar-xmark text-5xl text-slate-300 mb-4"></i>
                    <h4 class="text-lg font-bold text-slate-600">No classes found</h4>
                    <p class="text-slate-400 text-sm mt-2">There are currently no classes scheduled at this specific branch.</p>
                    <a href="<?= base_url() ?>" class="inline-block mt-6 text-xs font-bold text-gold uppercase hover:underline">Check other branches</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>