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
    die("Course not found in the global registry.");
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

// Extract unique branches and shifts for the Alpine filters
$unique_branches = [];
$unique_shifts = [];
foreach ($schedules as $sch) {
    $unique_branches[$sch['branch_code']] = $sch['branch_name'];
    $unique_shifts[$sch['shift']] = ucfirst($sch['shift']);
}

// Include Header
require_once '../includes/header.php';
?>

<!-- Hero Section (Immersive Circuit Design) -->
<header class="bg-slate-900 text-white py-24 px-6 relative overflow-hidden border-b-4 border-[--brand-red]">
    <!-- Abstract Tech Background Pattern -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(var(--brand-gold) 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="absolute top-[-20%] right-[-10%] w-[500px] h-[500px] bg-[--brand-red] rounded-full blur-[150px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-30%] left-[-10%] w-[400px] h-[400px] bg-[--brand-gold] rounded-full blur-[120px] opacity-15 pointer-events-none mix-blend-screen"></div>
    
    <div class="max-w-[1200px] mx-auto relative z-10 animate-in fade-in slide-in-from-bottom-8 duration-700">
        
        <!-- Breadcrumb & Badges -->
        <div class="flex flex-wrap items-center gap-3 mb-8">
            <a href="<?= base_url('#programs') ?>" class="text-slate-400 hover:text-white transition text-xs font-bold uppercase tracking-widest"><i class="fa-solid fa-arrow-left mr-1"></i> Programs</a>
            <span class="text-slate-600">/</span>
            <span class="bg-[--brand-red]/20 border border-[--brand-red]/50 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-[0_0_15px_rgba(217,33,40,0.3)]">
                <?= h($course_info['academic_year']) ?> Intake
            </span>
            <span class="bg-white/5 border border-white/10 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest backdrop-blur-md">
                <i class="fa-regular fa-clock mr-1 text-[--brand-gold]"></i> <?= h($course_info['duration_text']) ?>
            </span>
        </div>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- Icon Avatar -->
            <div class="w-24 h-24 md:w-32 md:h-32 bg-slate-800 rounded-[2rem] flex items-center justify-center text-4xl md:text-5xl text-[--brand-gold] shadow-2xl shrink-0 border border-white/10 transform -rotate-3 hover:rotate-0 transition-transform">
                <i class="<?= h($course_info['icon'] ?? 'fa-solid fa-book-open') ?>"></i>
            </div>
            
            <div class="flex-1">
                <h1 class="text-4xl md:text-6xl font-black leading-tight mb-4 tracking-tighter">
                    <?= h($course_info['class_name']) ?>
                </h1>
                <p class="text-slate-400 text-lg max-w-2xl leading-relaxed font-medium">
                    <?= h($course_info['description']) ?>
                </p>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Area: Global Schedules (Alpine Powered) -->
<main class="max-w-[1200px] mx-auto px-6 py-16 -mt-8 relative z-20" x-data="{ branchFilter: 'all', shiftFilter: 'all' }">
    
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6 bg-white p-6 rounded-[32px] shadow-lg border border-slate-100">
        <div>
            <h2 class="text-3xl font-black italic text-slate-900 uppercase">Available <span class="text-[--brand-gold]">Schedules</span></h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Find a branch and time that suits your learning journey.</p>
        </div>
        
        <!-- Interactive Filters -->
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-48">
                <select x-model="branchFilter" class="w-full bg-slate-50 border border-slate-200 text-slate-900 py-3 pl-4 pr-10 rounded-xl text-xs font-black uppercase tracking-widest outline-none focus:border-[--brand-gold] transition appearance-none cursor-pointer">
                    <option value="all">All Branches</option>
                    <?php foreach($unique_branches as $code => $name): ?>
                        <option value="<?= h($code) ?>"><?= h($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
            </div>
            
            <div class="relative w-full sm:w-48">
                <select x-model="shiftFilter" class="w-full bg-slate-50 border border-slate-200 text-slate-900 py-3 pl-4 pr-10 rounded-xl text-xs font-black uppercase tracking-widest outline-none focus:border-[--brand-gold] transition appearance-none cursor-pointer">
                    <option value="all">All Shifts</option>
                    <?php foreach($unique_shifts as $shift => $label): ?>
                        <option value="<?= h($shift) ?>"><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
            </div>
        </div>
    </div>

    <?php if(count($schedules) > 0): ?>
        <div class="grid gap-6 relative">
            <?php foreach($schedules as $sch): ?>
            <!-- Card is filtered based on Alpine models -->
            <div x-show="(branchFilter === 'all' || branchFilter === '<?= h($sch['branch_code']) ?>') && (shiftFilter === 'all' || shiftFilter === '<?= h($sch['shift']) ?>')"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white p-6 md:p-8 rounded-[32px] border border-slate-100 hover:border-[--brand-gold] transition-all duration-300 shadow-sm hover:shadow-xl group relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-6 schedule-card">
                
                <!-- Hover Glow -->
                <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-[--brand-red] to-[--brand-gold] opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <!-- Branch Info -->
                <div class="flex items-start gap-5 flex-1 pl-2">
                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-[--brand-gold] text-2xl shadow-sm border border-slate-100 group-hover:bg-slate-900 transition-colors">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <span class="inline-block bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest mb-1 border border-slate-200">
                            Node: <?= h($sch['branch_code']) ?>
                        </span>
                        <h4 class="font-black text-xl text-slate-900 leading-tight group-hover:text-[--brand-red] transition-colors">
                            <?= h($sch['branch_name']) ?>
                        </h4>
                        <p class="text-xs font-bold text-slate-400 mt-1"><?= h($sch['branch_address']) ?></p>
                    </div>
                </div>

                <!-- Logistics Grid -->
                <div class="grid grid-cols-2 md:flex md:flex-wrap gap-4 md:gap-8 items-center w-full md:w-auto bg-slate-50 md:bg-transparent p-4 md:p-0 rounded-2xl md:rounded-none border border-slate-100 md:border-none">
                    <div>
                        <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Daily Timing</span>
                        <div class="font-black text-slate-700 text-sm">
                            <?= date('h:i A', strtotime($sch['start_time'])) ?> - <?= date('h:i A', strtotime($sch['end_time'])) ?>
                        </div>
                    </div>
                    <div>
                        <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Session Shift</span>
                        <span class="bg-white md:bg-slate-100 px-3 py-1 rounded-lg text-[10px] font-black uppercase text-slate-600 border border-slate-200 shadow-sm md:shadow-none">
                            <?= h($sch['shift']) ?>
                        </span>
                    </div>
                    <div>
                        <span class="block text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Group Section</span>
                        <span class="text-xl font-black text-[--brand-gold]"><?= h($sch['section']) ?></span>
                    </div>
                </div>

                <!-- Action -->
                <div class="w-full md:w-auto shrink-0 mt-4 md:mt-0">
                    <a href="<?= base_url('index.php?route=pages/landing#enquiry') ?>" 
                       onclick="sessionStorage.setItem('prefill_interest', 'Language Course'); sessionStorage.setItem('prefill_course', '<?= h($course_info['class_name']) ?> at <?= h($sch['branch_name']) ?> (<?= h(ucfirst($sch['shift'])) ?>)');" 
                       class="flex items-center justify-center gap-2 w-full bg-slate-900 text-white px-8 py-4 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-[--brand-gold] hover:text-slate-900 transition-colors shadow-lg transform hover:-translate-y-1">
                        Enroll Here <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- No Results Fallback JS logic -->
        <script>
            // A simple observer to show a message if all cards are hidden by Alpine
            document.addEventListener('alpine:initialized', () => {
                Alpine.effect(() => {
                    setTimeout(() => {
                        const cards = document.querySelectorAll('.schedule-card');
                        const hiddenCards = document.querySelectorAll('.schedule-card[style*="display: none"]');
                        const emptyState = document.getElementById('empty-filter-state');
                        if (cards.length === hiddenCards.length) {
                            emptyState.style.display = 'block';
                        } else {
                            emptyState.style.display = 'none';
                        }
                    }, 50);
                });
            });
        </script>
        <div id="empty-filter-state" style="display: none;" class="bg-white rounded-[40px] p-16 text-center border border-slate-100 shadow-sm relative overflow-hidden mt-6">
            <i class="fa-solid fa-filter-circle-xmark text-5xl text-slate-300 mb-4"></i>
            <h4 class="text-xl font-black text-slate-900 mb-2 tracking-tight">No Matches Found</h4>
            <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">We couldn't find any schedules matching your selected branch and shift combination.</p>
            <button @click="branchFilter = 'all'; shiftFilter = 'all'" class="text-xs font-black uppercase text-[--brand-red] hover:underline">
                Clear Filters
            </button>
        </div>

    <?php else: ?>
        <div class="bg-white rounded-[40px] p-16 text-center border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNFMUI4MjIiIGZpbGwtb3BhY2l0eT0iMC4yIi8+PC9zdmc+')] opacity-50"></div>
            <div class="relative z-10">
                <i class="fa-regular fa-calendar-xmark text-6xl text-slate-200 mb-6"></i>
                <h4 class="text-2xl font-black text-slate-900 mb-2 tracking-tight">Schedules Updating</h4>
                <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">There are currently no active timetables broadcasted for this specific curriculum across our network.</p>
                <a href="<?= base_url('#programs') ?>" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition">
                    <i class="fa-solid fa-arrow-left"></i> View Other Courses
                </a>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Floating Contextual Help (Alpine.js) -->
<div x-data="{ showHelp: false }" class="fixed bottom-8 right-8 z-50">
    <button @click="showHelp = !showHelp" class="w-14 h-14 bg-[--brand-red] text-white rounded-full shadow-[0_0_20px_rgba(217,33,40,0.4)] flex items-center justify-center text-2xl hover:scale-110 transition-transform">
        <i class="fa-solid fa-headset" x-show="!showHelp"></i>
        <i class="fa-solid fa-xmark" x-show="showHelp" x-cloak></i>
    </button>
    
    <div x-show="showHelp" x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="absolute bottom-20 right-0 w-80 bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 overflow-hidden">
        
        <div class="absolute top-0 right-0 w-24 h-24 bg-[--brand-gold] rounded-full blur-[40px] opacity-20 pointer-events-none"></div>
        
        <h4 class="font-black text-slate-900 mb-1 relative z-10">Need Guidance?</h4>
        <p class="text-xs text-slate-500 mb-4 relative z-10">Our academic advisors can help you choose the right section based on your current JLPT level.</p>
        
        <a href="tel:<?= preg_replace('/[^0-9+]/', '', ORG_PHONE) ?>" class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100 hover:border-[--brand-gold] transition group relative z-10">
            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-[--brand-red] shadow-sm group-hover:bg-[--brand-red] group-hover:text-white transition">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div>
                <div class="text-[9px] font-black uppercase text-slate-400">Call Support</div>
                <div class="font-bold text-slate-900 text-sm"><?= h(ORG_PHONE) ?></div>
            </div>
        </a>
    </div>
</div>

<!-- JS to handle pre-filling the form if they click Enroll -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(window.location.hash === '#enquiry') {
            const interest = sessionStorage.getItem('prefill_interest');
            const course = sessionStorage.getItem('prefill_course');
            
            if(interest) {
                const select = document.querySelector('select[name="interest"]');
                if(select) select.value = interest;
            }
            if(course) {
                console.log("User interested in:", course);
                // Note: If you add a "notes" or "course requested" field to your form in landing.php, 
                // you would populate it here using document.querySelector('input[name="course"]').value = course;
            }
            
            // Clean up
            sessionStorage.removeItem('prefill_interest');
            sessionStorage.removeItem('prefill_course');
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>