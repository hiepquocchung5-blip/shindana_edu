<?php 
// pages/landing.php
// Main Entry Point for Sheindana.edu

require_once '../config/db.php'; 
require_once '../config/functions.php';

// 1. Safe Database Fetching (Crash-Proof Architecture)
$branches = [];
$programs = [];
$featured_schools = [];
$testimonials = [];

try {
    // Fetch all branches safely and filter in PHP to avoid 'is_active' vs 'status' SQL column errors
    $stmt_branches = $pdo->query("SELECT * FROM branches");
    $raw_branches = $stmt_branches->fetchAll();
    
    $branches = array_filter($raw_branches, function($b) {
        if (isset($b['is_active'])) return $b['is_active'] == 1;
        if (isset($b['status'])) return $b['status'] === 'active';
        return true; 
    });
} catch (PDOException $e) { 
    error_log("Branches query failed: " . $e->getMessage()); 
}

try {
    // Fetch top 3 featured schools for the landing page showcase
    $stmt_schools = $pdo->query("
        SELECT id, school_name, region, type, city 
        FROM japan_schools 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $featured_schools = $stmt_schools->fetchAll();
} catch (PDOException $e) {
    error_log("Schools showcase query failed: " . $e->getMessage());
}

try {
    // Fetch top 3 dynamic testimonials for the Success Stories block
    $stmt_testi = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 3");
    $testimonials = $stmt_testi->fetchAll();
} catch (PDOException $e) {
    error_log("Testimonials query failed: " . $e->getMessage());
}

// Include Header (brings in our --brand-gold and --brand-red variables)
require_once '../includes/header.php';
?>

<style>
    html { scroll-behavior: smooth; }
    .terminal-scroll::-webkit-scrollbar { width: 4px; }
    .terminal-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 4px; }
    .terminal-scroll::-webkit-scrollbar-thumb { background: var(--brand-gold); border-radius: 4px; }
    @media (max-width: 768px) {
        .mobile-snap-x {
            display: flex; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: none; gap: 1rem; padding-bottom: 0.5rem; width: 100%; 
        }
        .mobile-snap-x::-webkit-scrollbar { display: none; }
        .mobile-snap-card { flex: 0 0 85%; scroll-snap-align: center; }
    }
</style>

<!-- Hero Section -->
<section class="bg-slate-900 min-h-[90vh] flex items-center relative px-4 sm:px-6 lg:px-12 overflow-hidden py-24 md:py-32">
    <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(var(--brand-gold) 1px, transparent 1px); background-size: 40px 40px;"></div>
    <div class="absolute top-[-10%] right-[-5%] w-[300px] h-[300px] md:w-[600px] md:h-[600px] bg-[--brand-red] rounded-full blur-[120px] md:blur-[180px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] md:w-[700px] md:h-[700px] bg-[--brand-gold] rounded-full blur-[120px] md:blur-[180px] opacity-10 pointer-events-none mix-blend-screen"></div>

    <div class="max-w-[1600px] mx-auto w-full grid lg:grid-cols-2 gap-12 lg:gap-16 relative z-10 items-center">
        
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-10 duration-1000 mt-8 md:mt-0">
            <div class="inline-flex items-center gap-3 bg-white/5 border border-white/10 px-4 md:px-5 py-2 md:py-2.5 rounded-full backdrop-blur-md shadow-lg">
                <span class="text-[--brand-gold] text-[9px] md:text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[--brand-red] animate-pulse"></span> Licensed Agency
                </span>
                <div class="h-4 w-px bg-white/20"></div>
                <span class="text-white/80 text-[9px] md:text-[10px] font-bold uppercase tracking-widest">Myanmar <i class="fa-solid fa-arrow-right mx-1 text-[--brand-red]"></i> Global</span>
            </div>
            
            <h1 class="text-4xl sm:text-5xl md:text-7xl lg:text-[5.5rem] font-black text-white leading-[1.1] md:leading-[0.95] tracking-tighter">
                Accelerate <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[--brand-gold] via-[#FFF] to-[--brand-red]">Your Future.</span>
            </h1>
            
            <p class="text-slate-400 text-sm md:text-lg max-w-xl leading-relaxed font-medium">
                Shine Da Na connects Myanmar professionals with direct employment opportunities in Japan, UAE, Singapore, and beyond. Master the language and secure your career.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-2 md:pt-4">
                <a href="#about" class="w-full sm:w-auto bg-[--brand-gold] text-slate-900 px-6 md:px-8 py-3.5 md:py-4 rounded-xl md:rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest hover:bg-white hover:shadow-[0_0_40px_rgba(229,184,34,0.5)] active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-[--brand-gold] transition-all flex items-center justify-center gap-2 transform hover:-translate-y-1">
                    Who We Are <i class="fa-solid fa-arrow-down"></i>
                </a>
                <a href="#programs" class="w-full sm:w-auto bg-white/5 text-white border border-white/10 px-6 md:px-8 py-3.5 md:py-4 rounded-xl md:rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest hover:bg-[--brand-red] hover:border-[--brand-red] hover:shadow-[0_0_40px_rgba(217,33,40,0.4)] active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-[--brand-red] transition-all flex items-center justify-center gap-2 transform hover:-translate-y-1 backdrop-blur-sm">
                    View Programs
                </a>
            </div>
        </div>

        <div class="relative group w-full overflow-hidden sm:overflow-visible" id="branches">
            <div class="absolute -inset-1 bg-gradient-to-r from-[--brand-red] to-[--brand-gold] rounded-[24px] md:rounded-[40px] blur opacity-25 group-hover:opacity-50 transition duration-1000 hidden sm:block"></div>
            <div class="bg-slate-900/80 backdrop-blur-xl p-5 md:p-10 rounded-[24px] md:rounded-[40px] border border-white/10 relative shadow-2xl transform lg:rotate-1 lg:hover:rotate-0 transition-transform duration-500 w-full max-w-[100vw]">
                
                <div class="absolute -top-4 -right-4 md:-top-6 md:-right-6 bg-[--brand-red] text-white w-14 h-14 md:w-20 md:h-20 rounded-full flex flex-col items-center justify-center shadow-[0_0_30px_rgba(217,33,40,0.6)] transform rotate-12 z-20 border-4 border-slate-900">
                    <span class="text-lg md:text-2xl font-black leading-none"><?= count($branches) > 0 ? count($branches) : 0 ?></span>
                    <span class="text-[6px] md:text-[8px] font-black uppercase tracking-widest">Centers</span>
                </div>
                
                <h3 class="text-[9px] md:text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 md:mb-8 flex items-center gap-2">
                    <i class="fa-solid fa-server text-[--brand-gold]"></i> Network Status
                </h3>
                
                <div class="mobile-snap-x md:block md:space-y-4 md:max-h-[360px] md:overflow-y-auto terminal-scroll md:pr-2">
                    <?php if(isset($branches) && count($branches) > 0): ?>
                        <?php foreach($branches as $branch): ?>
                        <a href="<?= route('pages/branch_details&id=' . ($branch['id'] ?? '')) ?>" class="mobile-snap-card flex items-center justify-between p-3 md:p-4 bg-white/5 rounded-xl md:rounded-2xl border border-white/5 hover:border-[--brand-gold]/50 hover:bg-white/10 active:bg-white/20 focus:outline-none focus:ring-2 focus:ring-[--brand-gold] transition-all duration-300 group/item cursor-pointer">
                            <div class="flex items-center gap-3 md:gap-4 w-full">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-slate-800 flex items-center justify-center text-[--brand-gold] text-sm md:text-base group-hover/item:text-slate-900 group-hover/item:bg-[--brand-gold] transition-colors shadow-inner shrink-0">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div class="overflow-hidden flex-1">
                                    <div class="font-bold text-xs sm:text-sm text-white group-hover/item:text-[--brand-gold] transition-colors truncate w-full block"><?= h($branch['name'] ?? $branch['branch_name'] ?? 'Center') ?></div>
                                    <div class="text-[8px] sm:text-[10px] text-slate-500 font-bold uppercase tracking-widest truncate w-full block mt-0.5"><?= h($branch['code'] ?? 'ACTIVE') ?> • Synced</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 md:gap-3 shrink-0 pl-2">
                                <span class="text-[--brand-gold] transition-all duration-300 transform sm:-translate-x-2 sm:opacity-0 group-hover/item:translate-x-0 group-hover/item:opacity-100">
                                    <i class="fa-solid fa-chevron-right text-xs md:text-sm"></i>
                                </span>
                                <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-[--brand-gold] shadow-[0_0_12px_rgba(229,184,34,0.8)] animate-pulse"></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-slate-500 text-xs italic p-4 bg-white/5 rounded-2xl w-full">Network booting up or no branches found in database...</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Summary Section (NEW) -->
<section id="about" class="py-16 md:py-24 px-4 sm:px-6 max-w-[1600px] mx-auto relative scroll-mt-32">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
        <!-- Left Side: Image / Graphics -->
        <div class="relative order-2 lg:order-1">
            <div class="absolute -top-10 -left-10 w-64 h-64 bg-[--brand-gold] rounded-full blur-[80px] opacity-20 hidden md:block"></div>
            <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-[--brand-red] rounded-full blur-[80px] opacity-10 hidden md:block"></div>
            
            <div class="grid grid-cols-2 gap-4 relative z-10">
                <div class="space-y-4">
                    <div class="bg-white rounded-[32px] p-6 shadow-xl border border-slate-100 transform hover:-translate-y-2 transition-transform duration-300">
                        <i class="fa-solid fa-earth-americas text-4xl text-[--brand-gold] mb-4"></i>
                        <h4 class="font-black text-slate-900 text-lg mb-1">Global Reach</h4>
                        <p class="text-xs font-bold text-slate-500">Japan, UAE, Singapore, Malaysia & More.</p>
                    </div>
                    <div class="bg-slate-900 rounded-[32px] p-6 shadow-xl transform translate-x-4 md:translate-x-8 hover:-translate-y-2 transition-transform duration-300">
                        <i class="fa-solid fa-language text-4xl text-[--brand-red] mb-4"></i>
                        <h4 class="font-black text-white text-lg mb-1">Language Ops</h4>
                        <p class="text-xs font-bold text-slate-400">Japanese, Chinese, English, Korean mastery.</p>
                    </div>
                </div>
                <div class="bg-white rounded-[32px] p-8 shadow-xl border border-slate-100 flex flex-col justify-center mt-8 hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-[50px] md:text-[80px] font-black text-[--brand-gold] leading-none mb-2 tracking-tighter">100<span class="text-3xl text-[--brand-red]">%</span></h3>
                    <h4 class="font-black text-slate-900 text-lg uppercase tracking-tight">Legal & Safe</h4>
                    <p class="text-xs font-bold text-slate-500 mt-2 leading-relaxed">Officially licensed by the Ministry of Labor - Department of Labor.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Text Content -->
        <div class="order-1 lg:order-2">
            <span class="text-[9px] md:text-[10px] font-black uppercase text-[--brand-red] tracking-[0.2em] mb-2 md:mb-3 flex items-center gap-2">
                <div class="w-6 md:w-8 h-px bg-[--brand-red]"></div> Who We Are
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight leading-tight mb-6">
                株式会社シャインダナー <br>
                <span class="text-slate-400 font-light italic">Shinedana Co., Ltd.</span>
            </h2>
            
            <p class="text-sm md:text-base text-slate-600 font-medium leading-relaxed mb-6">
                Officially licensed by the Ministry of Labor, Shine Da Na Overseas Employment Agency and Foreign Language Training Centre provides professional and reliable services for overseas employment and language education.
            </p>
            <p class="text-sm md:text-base text-slate-600 font-medium leading-relaxed mb-10">
                Our mission is to help Myanmar workers build a better future by providing safe, legal, and transparent opportunities, along with the training and guidance needed to succeed internationally.
            </p>

            <a href="<?= route('pages/about') ?>" class="inline-flex items-center gap-3 bg-slate-900 text-white px-8 py-4 rounded-xl md:rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest hover:bg-[--brand-gold] hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-[--brand-gold]/50 transition-colors shadow-xl group active:scale-[0.98]">
                Read Company Profile <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</section>

<!-- Academic Programs -->
<section id="programs" class="py-16 md:py-24 px-4 sm:px-6 max-w-[1600px] mx-auto relative scroll-mt-32">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 md:mb-16 gap-4 md:gap-6">
        <div>
            <span class="text-[9px] md:text-[10px] font-black uppercase text-[--brand-red] tracking-[0.2em] mb-2 md:mb-3 flex items-center gap-2">
                <div class="w-6 md:w-8 h-px bg-[--brand-red]"></div> Unified Curriculum
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight">ACADEMIC <span class="text-slate-400 font-light italic">PROGRAMS</span></h2>
        </div>
        <div class="text-xs md:text-sm font-bold text-slate-500 max-w-sm text-left md:text-right border-l-2 md:border-l-0 md:border-r-2 border-[--brand-gold] pl-3 md:pl-0 md:pr-4 py-1 md:py-0">
            World-class JLPT, Tokutei, Chinese & Korean preparation, standardized across our network.
        </div>
    </div>

    <?php if(isset($programs) && count($programs) > 0): ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-8">
            <?php foreach($programs as $program): ?>
            <a href="<?= route('pages/class_details&name=' . urlencode($program['class_name'])) ?>" class="block h-full group active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-[--brand-gold]/50 rounded-[20px] md:rounded-[32px] transition-all">
                <div class="bg-white p-5 md:p-8 rounded-[20px] md:rounded-[32px] border border-slate-100 group-hover:border-[--brand-gold] group-hover:-translate-y-2 transition-all duration-300 shadow-sm group-hover:shadow-2xl flex flex-col h-full relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[--brand-red] to-[--brand-gold] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute top-4 right-4 md:top-8 md:right-8 text-slate-200 group-hover:text-[--brand-gold] transition-colors text-base md:text-xl transform group-hover:translate-x-1 group-hover:-translate-y-1">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>

                    <div class="relative z-10 w-10 h-10 md:w-14 md:h-14 bg-slate-50 text-slate-400 rounded-lg md:rounded-2xl flex items-center justify-center text-lg md:text-2xl font-black mb-4 md:mb-8 group-hover:bg-slate-900 group-hover:text-[--brand-gold] transition-colors shrink-0 shadow-sm border border-slate-100">
                        <i class="<?= h($program['icon'] ?? 'fa-solid fa-book-open') ?>"></i>
                    </div>
                    
                    <h3 class="relative z-10 text-lg md:text-2xl font-black mb-2 md:mb-3 text-slate-900 leading-tight group-hover:text-[--brand-red] transition-colors pr-6">
                        <?= h($program['class_name']) ?>
                    </h3>
                    
                    <p class="relative z-10 text-xs md:text-sm text-slate-500 mb-4 md:mb-8 leading-relaxed flex-grow">
                        <?= h($program['description'] ?? 'Comprehensive syllabus designed for maximum retention and JLPT success. Tailored for ambitious students.') ?>
                    </p>
                    
                    <div class="relative z-10 text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest pt-3 md:pt-5 border-t border-slate-100 flex justify-between items-center">
                        <span class="flex items-center gap-1 md:gap-1.5"><i class="fa-regular fa-clock text-[--brand-gold]"></i> <?= h($program['duration_text'] ?? '6 Months') ?></span>
                        <span class="bg-slate-100 px-2 py-1 rounded text-slate-600 border border-slate-200 group-hover:bg-[--brand-gold]/10 group-hover:border-[--brand-gold]/30 group-hover:text-[--brand-gold] transition-colors"><?= h($program['shift']) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 md:py-20 bg-slate-50 rounded-[20px] md:rounded-[40px] border-2 border-dashed border-slate-200 mx-4 md:mx-0">
            <i class="fa-solid fa-graduation-cap text-3xl md:text-5xl text-slate-300 mb-4"></i>
            <p class="text-slate-500 font-bold text-xs md:text-sm uppercase tracking-widest">Academic Roster Updating</p>
        </div>
    <?php endif; ?>
</section>

<!-- Pacific Finder Teaser & School Showcase -->
<section class="py-16 md:py-24 bg-slate-900 text-white px-4 sm:px-6 relative overflow-hidden">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[100px] sm:text-[200px] md:text-[400px] font-black text-white pointer-events-none select-none opacity-[0.03] italic">SHN</div>

    <div class="max-w-[1400px] mx-auto text-center relative z-10">
        <div class="max-w-4xl mx-auto">
            <div class="inline-flex items-center justify-center w-14 h-14 md:w-20 md:h-20 bg-white rounded-full mb-4 md:mb-8 shadow-[0_0_40px_rgba(255,255,255,0.1)]">
                <img src="<?= asset_url('images/shine_logo.png') ?>" alt="SHN Logo" class="w-8 h-8 md:w-12 md:h-12 object-contain" onerror="this.style.display='none'">
            </div>
            
            <span class="block text-[8px] md:text-[10px] font-black uppercase text-[--brand-gold] tracking-[0.2em] mb-2 md:mb-4">The Pacific Institutional Database</span>
            
            <h2 class="text-3xl sm:text-4xl md:text-6xl font-black mb-4 md:mb-6 tracking-tight uppercase italic leading-tight">
                Discover Your Perfect <br class="hidden sm:block">Campus in <span class="text-[--brand-red]">Japan</span>
            </h2>
            
            <p class="text-slate-400 text-xs md:text-lg mb-6 md:mb-10 leading-relaxed max-w-3xl mx-auto">
                Why limit your choices? Explore our meticulously curated directory of premium Japanese institutions. From intensive Language Academies in bustling Tokyo to specialized IT Colleges in Osaka, we seamlessly manage the entire pipeline.
            </p>
        </div>

        <?php if(count($featured_schools) > 0): ?>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mt-8 md:mt-16 mb-6 md:mb-12 relative z-10 text-left">
                <?php foreach($featured_schools as $school): ?>
                <a href="<?= route('pages/school_details&id=' . $school['id']) ?>" class="bg-white/5 border border-white/10 rounded-[20px] md:rounded-[32px] p-4 md:p-6 hover:bg-white/10 hover:border-[--brand-gold] focus:outline-none focus:ring-4 focus:ring-[--brand-gold]/50 transition-all duration-300 group flex flex-col h-full active:scale-[0.98]">
                    <div class="flex justify-between items-start mb-3 md:mb-6">
                        <div class="w-10 h-10 md:w-14 md:h-14 rounded-lg md:rounded-2xl bg-[--brand-gold] text-slate-900 flex items-center justify-center font-black text-lg md:text-2xl shadow-[0_0_20px_rgba(229,184,34,0.3)] group-hover:-rotate-6 transition-transform">
                            <?= strtoupper(substr($school['school_name'], 0, 1)) ?>
                        </div>
                        <span class="bg-[--brand-red]/20 text-white border border-[--brand-red]/50 px-2 md:px-3 py-1 rounded-full text-[7px] md:text-[9px] font-black uppercase tracking-widest shadow-sm text-center">
                            <?= h($school['type']) ?>
                        </span>
                    </div>
                    
                    <h3 class="text-base md:text-xl font-black text-white mb-1.5 md:mb-2 group-hover:text-[--brand-gold] transition-colors leading-tight line-clamp-2" title="<?= h($school['school_name']) ?>">
                        <?= h($school['school_name']) ?>
                    </h3>
                    
                    <p class="text-slate-400 text-[9px] md:text-xs font-bold mb-4 md:mb-8 flex items-center gap-2 flex-grow">
                        <i class="fa-solid fa-location-dot text-[--brand-red]"></i> <?= h($school['city']) ?>, <?= h($school['region']) ?>
                    </p>
                    
                    <div class="block w-full text-center py-2 md:py-3 rounded-lg md:rounded-xl border border-white/20 text-white text-[8px] md:text-[10px] font-black uppercase tracking-widest group-hover:bg-[--brand-gold] group-hover:text-slate-900 group-hover:border-[--brand-gold] transition-colors mt-auto">
                        View Profile
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <a href="<?= route('pages/schools') ?>" class="flex sm:inline-flex w-full sm:w-auto items-center justify-center gap-3 bg-[--brand-red] text-white px-6 md:px-10 py-3.5 md:py-5 rounded-xl md:rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest hover:bg-[--brand-gold] hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-[--brand-red] transition-colors shadow-xl hover:shadow-[--brand-gold]/30 group mt-2 md:mt-4 active:scale-[0.98]">
            Explore All Partner Schools <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>
</section>

<!-- Contact / Enquiry Form -->
<section id="enquiry" class="py-16 md:py-24 bg-slate-50 px-4 sm:px-6 scroll-mt-32">
    <div class="max-w-[1400px] mx-auto grid lg:grid-cols-2 gap-10 md:gap-16 items-center">
        
        <!-- Left: Info & Trust signals -->
        <div>
            <span class="text-[9px] md:text-[10px] font-black uppercase text-[--brand-red] tracking-[0.2em] mb-2 md:mb-3 flex items-center gap-2">
                <div class="w-6 md:w-8 h-px bg-[--brand-red]"></div> Admissions Team
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-6xl font-black text-slate-900 leading-tight mb-3 md:mb-6 tracking-tighter">
                Take the First <br>
                Step <span class="text-[--brand-gold]">Today.</span>
            </h2>
            <p class="text-slate-500 text-xs md:text-lg mb-6 md:mb-10 max-w-md leading-relaxed font-medium">
                Have questions about program placements, tuition estimates, or navigating the student visa process? Leave us a quick message, and our specialized agents will connect with you within 24 hours to map out your journey.
            </p>
            
            <div class="space-y-3 md:space-y-4">
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', ORG_PHONE ?? '') ?>" class="flex items-center gap-3 md:gap-6 p-3 md:p-6 rounded-xl md:rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-[--brand-red] focus:outline-none focus:ring-2 focus:ring-[--brand-red] transition cursor-pointer active:scale-[0.98] group">
                    <div class="w-10 h-10 md:w-14 md:h-14 rounded-full bg-slate-50 flex items-center justify-center text-[--brand-red] text-base md:text-xl shrink-0 border border-slate-100 group-hover:bg-[--brand-red] group-hover:text-white transition">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                    <span class="text-[10px] font-black uppercase text-[--brand-gold] tracking-[0.2em] mb-3 block">Alumni Network</span>
                    <h2 class="text-3xl md:text-5xl font-black text-white italic">WALL OF <span class="text-transparent bg-clip-text bg-gradient-to-r from-[--brand-gold] to-yellow-200">EXCELLENCE</span></h2>
                </div>
                <button class="text-white border border-white/20 px-6 py-3 rounded-full text-xs font-bold uppercase hover:bg-white hover:text-slate-900 transition w-full md:w-auto">View All 500+ Alumni</button>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($testimonials as $testimony): ?>
                <div class="bg-white/5 border border-white/10 p-8 rounded-[32px] backdrop-blur-sm hover:-translate-y-2 transition-transform duration-300 group">
                    <div class="flex items-center gap-4 mb-6">
                        <!-- Dynamic Image fetched from our public assets folder -->
                        <div class="w-14 h-14 rounded-full bg-slate-700 bg-cover bg-center border-2 border-white/10 shadow-inner group-hover:border-[--brand-gold] transition-colors" style="background-image: url('<?= asset_url('images/testimonials/' . h($testimony['image_path'])) ?>');"></div>
                        <div>
                            <h4 class="text-white font-bold text-lg leading-tight"><?= h($testimony['name']) ?></h4>
                            <p class="text-[10px] text-[--brand-gold] uppercase font-black tracking-widest mt-1"><?= h($testimony['placement']) ?></p>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm italic leading-relaxed font-medium">"<?= h($testimony['quote']) ?>"</p>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($testimonials)): ?>
                    <div class="col-span-3 text-center py-12 border border-dashed border-white/10 rounded-[32px]">
                        <i class="fa-regular fa-comment-dots text-4xl text-slate-500 mb-3"></i>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Alumni success stories will be updated shortly.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- UPCOMING EVENTS -->
                </div>
                
            <?php endif; ?>

            <form action="<?= route('pages/save_enquiry') ?>" method="POST" class="space-y-3 md:space-y-5 relative z-10">
                <div>
                    <label class="block text-[8px] md:text-[10px] font-black uppercase text-slate-500 mb-1 md:mb-2 ml-1">Full Name</label>
                    <input type="text" name="full_name" required placeholder="e.g. Mg Mg" class="w-full bg-slate-50 border border-slate-200 text-slate-900 p-3 md:p-4 rounded-lg md:rounded-2xl font-bold text-[10px] md:text-sm outline-none focus:ring-2 focus:ring-[--brand-gold]/50 focus:border-[--brand-gold] transition-all">
                </div>
                
                <div class="grid sm:grid-cols-2 gap-3 md:gap-5">
                    <div>
                        <label class="block text-[8px] md:text-[10px] font-black uppercase text-slate-500 mb-1 md:mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required placeholder="mail@example.com" class="w-full bg-slate-50 border border-slate-200 text-slate-900 p-3 md:p-4 rounded-lg md:rounded-2xl font-bold text-[10px] md:text-sm outline-none focus:ring-2 focus:ring-[--brand-gold]/50 focus:border-[--brand-gold] transition-all">
                    </div>
                    <div>
                        <label class="block text-[8px] md:text-[10px] font-black uppercase text-slate-500 mb-1 md:mb-2 ml-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="09..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 p-3 md:p-4 rounded-lg md:rounded-2xl font-bold text-[10px] md:text-sm outline-none focus:ring-2 focus:ring-[--brand-gold]/50 focus:border-[--brand-gold] transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[8px] md:text-[10px] font-black uppercase text-slate-500 mb-1 md:mb-2 ml-1">Area of Interest</label>
                    <div class="relative">
                        <select name="interest" class="w-full bg-slate-50 border border-slate-200 text-slate-900 p-3 md:p-4 rounded-lg md:rounded-2xl font-bold text-[10px] md:text-sm outline-none focus:ring-2 focus:ring-[--brand-gold]/50 focus:border-[--brand-gold] transition-all appearance-none cursor-pointer">
                            <option value="General">General Inquiry</option>
                            <option value="Language Course">Language Course (JLPT Prep)</option>
                            <option value="University">University Placement (EJU)</option>
                            <option value="Vocational">Vocational & Specialized Institutes</option>
                            <option value="Visa Info">Student Visa & COE Processing</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 md:right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[10px] md:text-base"></i>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-3.5 md:py-5 rounded-lg md:rounded-2xl font-black uppercase text-[9px] md:text-xs tracking-widest hover:bg-[--brand-gold] hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-[--brand-gold]/50 transition-all mt-3 md:mt-6 shadow-xl transform hover:-translate-y-1 active:scale-[0.98] flex items-center justify-center gap-2">
                    Submit Request <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>