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

<!-- Hero Section with Immersive Brand Glows -->
<header class="bg-slate-900 text-white relative overflow-hidden py-24 px-6 border-b-4 border-[#D92128]">
    <!-- Abstract Tech/Circuit Background Pattern -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#E5B822 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="absolute top-[-50%] right-[-10%] w-[600px] h-[600px] bg-[#D92128] rounded-full blur-[150px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-20%] left-[-10%] w-[400px] h-[400px] bg-[#E5B822] rounded-full blur-[120px] opacity-15 pointer-events-none mix-blend-screen"></div>
    
    <div class="max-w-[1200px] mx-auto relative z-10 animate-in fade-in slide-in-from-bottom-8 duration-700">
        
        <!-- Breadcrumb & Badges -->
        <div class="flex flex-wrap items-center gap-3 mb-8">
            <a href="<?= route('pages/schools') ?>" class="text-slate-400 hover:text-white transition text-xs font-bold uppercase tracking-widest"><i class="fa-solid fa-arrow-left mr-1"></i> Directory</a>
            <span class="text-slate-600">/</span>
            <span class="bg-[#D92128] text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-[0_0_15px_rgba(217,33,40,0.5)]">
                <?= htmlspecialchars($school['type']) ?>
            </span>
            <span class="bg-white/10 border border-white/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest backdrop-blur-md">
                <?= htmlspecialchars($school['region']) ?> Region
            </span>
        </div>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- School Initial Avatar -->
            <div class="w-24 h-24 md:w-32 md:h-32 bg-white rounded-[2rem] flex items-center justify-center text-5xl md:text-6xl font-black text-slate-900 shadow-2xl shrink-0 border-4 border-[#E5B822] transform -rotate-3 hover:rotate-0 transition-transform">
                <?= strtoupper(substr($school['school_name'], 0, 1)) ?>
            </div>
            
            <div class="flex-1">
                <h1 class="text-4xl md:text-6xl font-black leading-tight mb-4 tracking-tighter">
                    <?= htmlspecialchars($school['school_name']) ?>
                </h1>
                <p class="text-slate-400 text-lg md:text-xl flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-location-dot text-[#E5B822]"></i> 
                    <?= htmlspecialchars($school['city']) ?>, Japan
                </p>
            </div>
            
            <div class="hidden md:block text-right bg-white/5 border border-white/10 p-6 rounded-3xl backdrop-blur-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Established</div>
                <div class="text-5xl font-black text-[#E5B822]"><?= htmlspecialchars($school['est_year']) ?></div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Area with Alpine.js Tabs & Lightbox -->
<main class="max-w-[1200px] mx-auto px-6 py-12 relative z-20" x-data="{ tab: 'overview', activeImage: null }">
    <div class="grid lg:grid-cols-12 gap-10">
        
        <!-- Left Sidebar: Key Stats & Quick Info -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white p-8 rounded-[32px] shadow-xl border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#E5B822] rounded-full blur-[60px] opacity-10 pointer-events-none group-hover:opacity-20 transition-opacity"></div>
                
                <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-6 border-b border-slate-100 pb-4">Key Information</h3>
                
                <div class="space-y-6 relative z-10">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Est. Annual Tuition</div>
                        <div class="text-3xl font-black text-slate-900">
                            ¥<?= number_format($school['tuition_fees']) ?>
                        </div>
                        <div class="text-[9px] font-bold text-[#D92128] uppercase mt-1">First Year Base</div>
                    </div>
                    
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Admission Intake</div>
                        <div class="font-bold text-slate-700 flex items-center gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <i class="fa-regular fa-calendar text-[#E5B822]"></i> 
                            <?= htmlspecialchars($school['admission_months']) ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Official Address</div>
                        <div class="text-sm font-medium text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <?= htmlspecialchars($school['address_line']) ?><br>
                            <span class="text-slate-900 font-bold"><?= htmlspecialchars($school['city']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-50">
                    <a href="<?= htmlspecialchars($school['website']) ?>" target="_blank" class="flex items-center justify-center gap-2 w-full bg-slate-900 text-white text-center py-4 rounded-xl font-black text-xs uppercase hover:bg-[#E5B822] hover:text-slate-900 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-globe"></i> Visit Website <i class="fa-solid fa-arrow-up-right-from-square ml-1 opacity-50"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Main Content: Tabs -->
        <div class="lg:col-span-8">
            
            <!-- Tab Navigation -->
            <div class="flex gap-2 overflow-x-auto pb-4 mb-6 scrollbar-hide">
                <button @click="tab = 'overview'" 
                        :class="tab === 'overview' ? 'bg-slate-900 text-[#E5B822] shadow-md' : 'bg-white text-slate-500 hover:text-slate-900 border border-slate-200'"
                        class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-circle-info mr-1"></i> Overview
                </button>
                <button @click="tab = 'admissions'" 
                        :class="tab === 'admissions' ? 'bg-slate-900 text-[#E5B822] shadow-md' : 'bg-white text-slate-500 hover:text-slate-900 border border-slate-200'"
                        class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-file-signature mr-1"></i> Admissions
                </button>
                <button @click="tab = 'gallery'" 
                        :class="tab === 'gallery' ? 'bg-slate-900 text-[#E5B822] shadow-md' : 'bg-white text-slate-500 hover:text-slate-900 border border-slate-200'"
                        class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-images mr-1"></i> Campus Life
                </button>
            </div>

            <!-- Tab Content: Overview -->
            <div x-show="tab === 'overview'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white p-8 md:p-10 rounded-[32px] shadow-sm border border-slate-100 h-full">
                
                <h3 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-3">
                    <div class="w-8 h-1 bg-[#D92128]"></div> About the Institution
                </h3>
                
                <div class="prose prose-slate prose-lg max-w-none text-slate-500 leading-relaxed font-medium">
                    <?= nl2br(htmlspecialchars($school['description'])) ?>
                </div>

                <!-- Mini Grid Features -->
                <div class="grid grid-cols-2 gap-4 mt-10 border-t border-slate-100 pt-8">
                    <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-[#E5B822] shadow-sm text-xl"><i class="fa-solid fa-train-subway"></i></div>
                        <div>
                            <div class="text-[10px] font-black uppercase text-slate-400">Location</div>
                            <div class="font-bold text-slate-900">City Center Accessible</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-[#D92128] shadow-sm text-xl"><i class="fa-solid fa-handshake-angle"></i></div>
                        <div>
                            <div class="text-[10px] font-black uppercase text-slate-400">Support</div>
                            <div class="font-bold text-slate-900">Student Visa Assistance</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Admissions -->
            <div x-show="tab === 'admissions'" style="display: none;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white p-8 md:p-10 rounded-[32px] shadow-sm border border-slate-100 h-full">
                
                <h3 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-3">
                    <div class="w-8 h-1 bg-[#E5B822]"></div> Enrollment Process
                </h3>
                
                <ul class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-[#E5B822] before:via-slate-200 before:to-transparent">
                    <li class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-[#E5B822] text-slate-900 font-black text-sm shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">1</div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-slate-100 bg-slate-50 shadow-sm">
                            <h4 class="font-bold text-slate-900">Initial Consultation</h4>
                            <p class="text-xs text-slate-500 mt-1">Speak with a Shinedana agent to verify eligibility.</p>
                        </div>
                    </li>
                    <li class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-slate-200 text-slate-500 font-black text-sm shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">2</div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-slate-100 bg-white">
                            <h4 class="font-bold text-slate-900">Document Preparation</h4>
                            <p class="text-xs text-slate-500 mt-1">Submit passport, transcripts, and financial proofs securely via our portal.</p>
                        </div>
                    </li>
                    <li class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-slate-200 text-slate-500 font-black text-sm shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">3</div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-slate-100 bg-white">
                            <h4 class="font-bold text-slate-900">COE Application</h4>
                            <p class="text-xs text-slate-500 mt-1">HQ submits your file to Japanese immigration on your behalf.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Tab Content: Gallery -->
            <div x-show="tab === 'gallery'" style="display: none;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white p-8 md:p-10 rounded-[32px] shadow-sm border border-slate-100 h-full">
                
                <h3 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-3">
                    <div class="w-8 h-1 bg-[#D92128]"></div> Campus & Facilities
                </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Dynamic Placeholder Gallery (Replace src with real DB images later) -->
                    <div @click="activeImage = 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=800&q=80'" 
                         class="aspect-square bg-slate-100 rounded-2xl overflow-hidden cursor-pointer group relative">
                        <img src="https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-[#D92128]/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                        </div>
                    </div>
                    
                    <div @click="activeImage = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=800&q=80'" 
                         class="aspect-square bg-slate-100 rounded-2xl overflow-hidden cursor-pointer group relative">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-[#D92128]/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                        </div>
                    </div>

                    <div @click="activeImage = 'https://images.unsplash.com/photo-1503899036084-c55cdd92a805?auto=format&fit=crop&w=800&q=80'" 
                         class="aspect-square bg-slate-100 rounded-2xl overflow-hidden cursor-pointer group relative md:col-span-1 col-span-2">
                        <img src="https://images.unsplash.com/photo-1503899036084-c55cdd92a805?auto=format&fit=crop&w=800&q=80" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-[#D92128]/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Floating Action Callout -->
    <div class="mt-12 bg-gradient-to-r from-slate-900 to-slate-800 rounded-[32px] p-8 md:p-12 text-white flex flex-col md:flex-row justify-between items-center gap-8 shadow-2xl relative overflow-hidden border border-slate-700">
        <!-- Abstract Shapes -->
        <div class="absolute -top-24 -left-24 w-64 h-64 bg-[#D92128] rounded-full blur-[80px] opacity-30 pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-[#E5B822] rounded-full blur-[80px] opacity-20 pointer-events-none"></div>
        
        <div class="relative z-10 text-center md:text-left">
            <span class="inline-block bg-white/10 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest text-[#E5B822] mb-3 border border-white/10">Next Steps</span>
            <h3 class="text-3xl md:text-4xl font-black mb-2 tracking-tight">Secure Your Placement</h3>
            <p class="text-slate-400 max-w-lg text-sm md:text-base">Our authorized agents are ready to assist you with enrollment and visa processing for <?= htmlspecialchars($school['school_name']) ?>.</p>
        </div>
        
        <div class="relative z-10 shrink-0 w-full md:w-auto">
            <a href="<?= base_url('index.php?route=pages/landing#enquiry') ?>" class="flex items-center justify-center gap-3 bg-[#E5B822] text-slate-900 px-10 py-5 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-white transition-all transform hover:-translate-y-1 shadow-[0_0_30px_rgba(229,184,34,0.3)] w-full">
                Apply Now <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Alpine.js Lightbox Modal -->
    <div x-show="activeImage" style="display: none;" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/95 backdrop-blur-xl" 
         @click="activeImage = null" 
         @keydown.escape.window="activeImage = null"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <!-- Close Button -->
        <button class="absolute top-6 right-6 md:top-10 md:right-10 text-white/50 hover:text-[#E5B822] text-4xl transition-colors focus:outline-none">
            <i class="fa-solid fa-xmark"></i>
        </button>
        
        <!-- Image Container -->
        <img :src="activeImage" class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl object-contain border border-white/10" @click.stop>
    </div>

</main>

<!-- Footer Include -->
<?php require_once '../includes/footer.php'; ?>