<?php
// pages/school_details.php
// loaded via index.php?route=pages/school_details

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Get School ID
if (!isset($_GET['id'])) {
    redirect(''); // Redirect to home if no ID
}

// 2. Fetch School Details
$stmt = $pdo->prepare("SELECT * FROM japan_schools WHERE id = ?");
$stmt->execute([$_GET['id']]);
$school = $stmt->fetch();

if (!$school) {
    die("Institution not found in Pacific Database.");
}

// Include Header
require_once '../includes/header.php';
?>

<!-- Header -->
<header class="bg-slate-900 text-white relative overflow-hidden py-20 px-6">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#D4AF37 1px, transparent 1px); background-size: 20px 20px;"></div>
    
    <div class="max-w-5xl mx-auto relative z-10">
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- School Initial Avatar -->
            <div class="w-24 h-24 bg-white rounded-2xl flex items-center justify-center text-4xl font-black text-slate-900 shadow-2xl shrink-0 border-4 border-[#D4AF37]">
                <?= strtoupper(substr($school['school_name'], 0, 1)) ?>
            </div>
            
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">
                        <?= htmlspecialchars($school['type']) ?>
                    </span>
                    <span class="bg-white/10 border border-white/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest backdrop-blur-md">
                        <?= htmlspecialchars($school['region']) ?> Region
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black leading-tight mb-4 tracking-tight">
                    <?= htmlspecialchars($school['school_name']) ?>
                </h1>
                <p class="text-slate-400 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-[#D4AF37]"></i> 
                    <?= htmlspecialchars($school['city']) ?>, Japan
                </p>
            </div>
            
            <div class="hidden md:block text-right">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Established</div>
                <div class="text-4xl font-black text-white"><?= htmlspecialchars($school['est_year']) ?></div>
            </div>
        </div>
    </div>
</header>

<!-- Content -->
<main class="max-w-5xl mx-auto px-6 py-12 -mt-8 relative z-20">
    <div class="grid md:grid-cols-3 gap-8">
        
        <!-- Left Sidebar: Key Stats -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
                <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-6 border-b border-slate-100 pb-4">Key Information</h3>
                
                <div class="space-y-6">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Est. Annual Tuition</div>
                        <div class="text-2xl font-black text-slate-900">
                            ¥<?= number_format($school['tuition_fees']) ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Admission Intake</div>
                        <div class="font-bold text-slate-700 flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-[#D4AF37]"></i> 
                            <?= htmlspecialchars($school['admission_months']) ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Location</div>
                        <div class="text-sm font-medium text-slate-600 leading-relaxed">
                            <?= htmlspecialchars($school['address_line']) ?><br>
                            <?= htmlspecialchars($school['city']) ?>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-50">
                    <a href="<?= htmlspecialchars($school['website']) ?>" target="_blank" class="block w-full bg-slate-50 text-slate-900 text-center py-3 rounded-xl font-bold text-xs uppercase border border-slate-200 hover:bg-slate-900 hover:text-white transition shadow-sm">
                        Visit Official Website <i class="fa-solid fa-arrow-up-right-from-square ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content: Description -->
        <div class="md:col-span-2">
            <div class="bg-white p-10 rounded-3xl shadow-sm border border-slate-100 h-full">
                <h3 class="text-xl font-black italic uppercase text-slate-900 mb-6">About the Institution</h3>
                
                <div class="prose prose-slate max-w-none text-slate-500 leading-relaxed text-sm md:text-base">
                    <?= nl2br(htmlspecialchars($school['description'])) ?>
                </div>

                <!-- Call to Action -->
                <div class="mt-12 bg-slate-900 rounded-2xl p-8 text-white flex flex-col md:flex-row items-center gap-6 shadow-2xl relative overflow-hidden">
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#D4AF37] rounded-full blur-[60px] opacity-20 pointer-events-none"></div>
                    
                    <div class="bg-[#D4AF37] w-12 h-12 rounded-full flex items-center justify-center text-slate-900 font-bold text-xl shrink-0 shadow-lg">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="flex-1 text-center md:text-left relative z-10">
                        <h4 class="font-bold text-lg mb-1">Interested in this school?</h4>
                        <p class="text-sm text-slate-400">Contact a Sheindana Agent to begin your application process today.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Footer Include -->
<?php require_once '../includes/footer.php'; ?>