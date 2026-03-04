<?php
// pages/class_details.php
// loaded via index.php?route=pages/class_details&name=JLPT+N5...

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Get Class Name
if (!isset($_GET['name'])) {
    redirect(''); 
}
$class_name = urldecode($_GET['name']);

// 2. Fetch General Course Info
$stmt_info = $pdo->prepare("SELECT * FROM class_divisions WHERE class_name = ? LIMIT 1");
$stmt_info->execute([$class_name]);
$course_info = $stmt_info->fetch();

if (!$course_info) {
    die("Course not found.");
}

// 3. Fetch Active Schedules Across Branches
// This joins class_divisions -> class_branch_visibility -> branches
$sql = "
    SELECT cd.*, b.name as branch_name, b.code as branch_code, b.address as branch_address 
    FROM class_divisions cd 
    JOIN class_branch_visibility cbv ON cd.id = cbv.class_id 
    JOIN branches b ON cbv.branch_id = b.id 
    WHERE cd.class_name = ? AND cbv.is_visible = 1 AND cd.status = 'active'
    ORDER BY b.id, cd.start_time
";
$stmt_schedules = $pdo->prepare($sql);
$stmt_schedules->execute([$class_name]);
$schedules = $stmt_schedules->fetchAll();

// Include Header
require_once '../includes/header.php';
?>

<!-- Header -->
<header class="bg-slate-900 text-white py-24 px-6 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#D4AF37 1px, transparent 1px); background-size: 30px 30px;"></div>
    
    <div class="max-w-5xl mx-auto relative z-10">
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 px-4 py-2 rounded-full mb-6 backdrop-blur-md">
            <span class="text-gold text-[10px] font-black uppercase tracking-widest"><?= h($course_info['academic_year']) ?></span>
            <div class="h-3 w-px bg-white/20"></div>
            <span class="text-white/80 text-[10px] font-bold uppercase tracking-widest"><i class="fa-regular fa-clock mr-1"></i> <?= h($course_info['duration_text']) ?></span>
        </div>
        
        <h1 class="text-5xl md:text-6xl font-black mb-6 leading-tight"><?= h($course_info['class_name']) ?></h1>
        <p class="text-slate-400 text-lg max-w-2xl leading-relaxed">
            <?= h($course_info['description']) ?>
        </p>
    </div>
</header>

<!-- Schedules Content -->
<main class="max-w-5xl mx-auto px-6 py-16 -mt-8 relative z-20">
    <div class="bg-white rounded-[40px] shadow-2xl border border-slate-100 overflow-hidden">
        <div class="p-8 md:p-12 border-b border-slate-50 bg-slate-50/50">
            <h3 class="text-xl font-black italic text-slate-900 uppercase">Available Schedules</h3>
            <p class="text-sm text-slate-500 mt-2">Find a branch and time that suits your schedule.</p>
        </div>

        <?php if(count($schedules) > 0): ?>
            <div class="divide-y divide-slate-100">
                <?php foreach($schedules as $sch): ?>
                <div class="p-8 md:p-10 hover:bg-slate-50 transition group">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        
                        <!-- Branch Info -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-slate-900 rounded-xl flex items-center justify-center text-gold text-xl shadow-lg group-hover:scale-110 transition">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-lg text-slate-900"><?= h($sch['branch_name']) ?></h4>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?= h($sch['branch_address']) ?></p>
                            </div>
                        </div>

                        <!-- Time & Section -->
                        <div class="flex flex-wrap gap-4 md:gap-8 items-center">
                            <div>
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Time</span>
                                <div class="font-bold text-slate-700">
                                    <?= date('h:i A', strtotime($sch['start_time'])) ?> - <?= date('h:i A', strtotime($sch['end_time'])) ?>
                                </div>
                            </div>
                            <div>
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Shift</span>
                                <span class="bg-slate-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase text-slate-600 border border-slate-200">
                                    <?= h($sch['shift']) ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Section</span>
                                <span class="text-xl font-black text-gold"><?= h($sch['section']) ?></span>
                            </div>
                        </div>

                        <!-- Action -->
                        <a href="#enquiry" onclick="document.querySelector('select[name=interest]').value='Language Course'; document.querySelector('textarea[name=message]').value='Inquiry for <?= h($course_info['class_name']) ?> at <?= h($sch['branch_name']) ?>';" class="bg-slate-900 text-white px-6 py-3 rounded-xl text-xs font-black uppercase hover:bg-gold hover:text-slate-900 transition shadow-lg">
                            Apply Now
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <p class="text-slate-400 font-bold">No active schedules found for this course currently.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Enquiry Form Section (Reused Style) -->
<section id="enquiry" class="py-24 px-6 bg-slate-50">
    <!-- ... (Standard Enquiry Form included in Footer or reuse component) ... -->
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl font-black uppercase text-slate-900 mb-6">Need more info?</h2>
        <p class="text-slate-500 mb-8">Our team can help you choose the right course level.</p>
        <a href="<?= base_url('#contact') ?>" class="inline-block bg-gold text-slate-900 px-8 py-4 rounded-full font-black uppercase text-xs hover:shadow-xl transition">Contact Admissions</a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>