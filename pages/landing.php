<?php 
// pages/landing.php
// This file is loaded by index.php via the Router
// Current working directory is /pages due to router logic

require_once '../config/db.php'; 
require_once '../config/functions.php';

try {
    // 1. Fetch Yangon Branches (Active Only)
    $stmt_branches = $pdo->query("SELECT * FROM branches WHERE is_active = 1");
    $branches = $stmt_branches->fetchAll();

    // 2. Fetch Academic Programs (Classes)
    // We GROUP BY class_name to show unique programs. 
    $stmt_programs = $pdo->query("SELECT class_name, MAX(description) as description, MAX(duration_text) as duration_text, MAX(icon) as icon FROM class_divisions WHERE status = 'active' GROUP BY class_name");
    $programs = $stmt_programs->fetchAll();
} catch (PDOException $e) {
    // Silent fail for UI
    $branches = [];
    $programs = [];
}

// Include Header (Adjusted path for 'pages' folder)
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<header class="hero-pattern min-h-[90vh] flex items-center relative px-6 lg:px-12 overflow-hidden pt-20">
    <div class="max-w-7xl mx-auto w-full grid lg:grid-cols-2 gap-16 relative z-10 items-center">
        <div class="space-y-8 animate-in fade-in zoom-in duration-700">
            <div class="inline-flex items-center gap-3 bg-white/5 border border-white/10 px-6 py-3 rounded-full backdrop-blur-md">
                <span class="text-gold text-[10px] font-black uppercase tracking-[0.2em]">Verified Education Partner</span>
                <div class="h-4 w-px bg-white/20"></div>
                <span class="text-white/60 text-[10px] font-bold">Yangon <i class="fa-solid fa-arrow-right mx-1 text-gold"></i> Japan</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white leading-[0.9] tracking-tight">
                Future <br>
                <span class="text-gradient-gold">Focused.</span>
            </h1>
            
            <p class="text-slate-400 text-lg max-w-lg leading-relaxed font-light">
                The centralized ecosystem for Myanmar's top 5 Academic Centers. Direct integration with Japan's premium institutions.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="#programs" class="bg-gold text-slate-900 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] transition-shadow">
                    Explore Classes
                </a>
                <a href="#finder" class="bg-white/5 text-white border border-white/10 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-white/10 transition-colors">
                    Pacific Finder
                </a>
            </div>
        </div>

        <!-- Hero Stats -->
        <div class="hidden lg:block relative">
            <div class="absolute -inset-1 bg-gradient-to-r from-gold to-yellow-600 rounded-[40px] blur opacity-25"></div>
            <div class="bg-slate-900/80 backdrop-blur-xl p-10 rounded-[40px] border border-white/10 relative">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">Network Status</h3>
                <div class="space-y-4">
                    <?php if(isset($branches) && count($branches) > 0): ?>
                        <?php foreach($branches as $branch): ?>
                        <!-- Linked Branch Card -->
                        <a href="<?= base_url('index.php?route=pages/branch_details&id=' . $branch['id']) ?>" class="block">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5 hover:border-gold/50 hover:bg-white/10 transition duration-300 group cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_10px_#22c55e]"></div>
                                    <div>
                                        <div class="font-bold text-sm text-white group-hover:text-gold transition"><?= h($branch['name']) ?></div>
                                        <div class="text-[10px] text-slate-500 font-bold uppercase"><?= h($branch['code']) ?> • Active</div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-600 text-xs group-hover:text-gold transition"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-slate-500 text-xs italic">No active branches found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Why Choose Us -->
<section class="py-24 bg-slate-900 text-white relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
        <div class="absolute right-0 bottom-0 w-96 h-96 bg-gold rounded-full blur-[100px]"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center mb-16">
            <span class="text-[10px] font-black uppercase text-gold tracking-[0.2em] mb-3 block">Sheindana Advantage</span>
            <h2 class="text-4xl font-black italic">WHY CHOOSE <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-yellow-200">US?</span></h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-12">
            <div class="text-center group">
                <div class="w-20 h-20 bg-white/10 rounded-[32px] flex items-center justify-center mx-auto mb-6 text-3xl text-gold group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="text-xl font-black mb-4">Unified Curriculum</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Standardized Japanese language training across all 5 branches, ensuring quality education regardless of location.</p>
            </div>
            <div class="text-center group">
                <div class="w-20 h-20 bg-white/10 rounded-[32px] flex items-center justify-center mx-auto mb-6 text-3xl text-gold group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-passport"></i>
                </div>
                <h3 class="text-xl font-black mb-4">Visa Support</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Expert guidance on COE applications and student visa processing with a 98% success rate history.</p>
            </div>
            <div class="text-center group">
                <div class="w-20 h-20 bg-white/10 rounded-[32px] flex items-center justify-center mx-auto mb-6 text-3xl text-gold group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <h3 class="text-xl font-black mb-4">Direct Placement</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Exclusive partnerships with top universities and vocational colleges in Tokyo, Osaka, and Fukuoka.</p>
            </div>
        </div>
    </div>
</section>

<!-- Academic Programs (Database Driven) -->
<section id="programs" class="py-24 px-6 max-w-7xl mx-auto">
    <div class="text-center mb-16">
        <span class="text-[10px] font-black uppercase text-gold tracking-[0.2em] mb-3 block">Unified Curriculum</span>
        <h2 class="text-4xl font-black italic text-slate-900">ACADEMIC <span class="text-slate-400">PROGRAMS</span></h2>
    </div>

    <?php if(isset($programs) && count($programs) > 0): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($programs as $program): ?>
            <!-- Linked Program Card -->
            <a href="<?= base_url('index.php?route=pages/class_details&name=' . urlencode($program['class_name'])) ?>" class="block h-full">
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 hover:border-gold hover:-translate-y-2 transition-all duration-300 shadow-sm hover:shadow-xl group flex flex-col h-full cursor-pointer relative overflow-hidden">
                    <!-- Hover Effect Background -->
                    <div class="absolute inset-0 bg-gold/5 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                    
                    <div class="relative z-10 w-14 h-14 bg-slate-900 text-white rounded-2xl flex items-center justify-center text-xl font-black mb-6 group-hover:bg-gold transition-colors shrink-0">
                        <i class="<?= h($program['icon']) ?>"></i>
                    </div>
                    <h3 class="relative z-10 text-xl font-black mb-2 text-slate-900 leading-tight">
                        <?= h($program['class_name']) ?>
                    </h3>
                    <p class="relative z-10 text-xs text-slate-500 mb-6 leading-relaxed flex-grow">
                        <?= h($program['description']) ?>
                    </p>
                    <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-50 flex justify-between items-center">
                        <span><i class="fa-regular fa-clock mr-1 text-gold"></i> <?= h($program['duration_text']) ?></span>
                        <span class="text-gold group-hover:translate-x-1 transition"><i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12 bg-slate-50 rounded-[32px] border border-slate-100">
            <p class="text-slate-400 font-bold text-sm">No active academic programs available at the moment.</p>
        </div>
    <?php endif; ?>
</section>

<!-- Upcoming Events (Static) -->
<section class="py-24 bg-white px-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12">
            <div>
                <span class="text-[10px] font-black uppercase text-gold tracking-[0.2em] mb-3 block">Calendar</span>
                <h2 class="text-4xl font-black italic text-slate-900">UPCOMING <span class="text-slate-400">EVENTS</span></h2>
            </div>
            <a href="#" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-gold transition mt-4 md:mt-0">View All Events →</a>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="group bg-slate-50 p-8 rounded-[40px] border border-slate-100 hover:border-gold transition flex items-start gap-6">
                <div class="bg-white p-4 rounded-2xl text-center min-w-[80px] shadow-sm">
                    <span class="block text-2xl font-black text-slate-900">15</span>
                    <span class="text-[10px] font-black uppercase text-slate-400">NOV</span>
                </div>
                <div>
                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest mb-3 inline-block">Seminar</span>
                    <h3 class="text-xl font-black text-slate-900 mb-2 group-hover:text-gold transition">Study in Japan 2026</h3>
                    <p class="text-xs text-slate-500 mb-4">Join representatives from Tokyo Kokusai Academy for a free consultation at Kamayut HQ.</p>
                    <span class="text-[10px] font-bold text-slate-400 uppercase"><i class="fa-regular fa-clock mr-1"></i> 10:00 AM - 4:00 PM</span>
                </div>
            </div>
            <div class="group bg-slate-50 p-8 rounded-[40px] border border-slate-100 hover:border-gold transition flex items-start gap-6">
                <div class="bg-white p-4 rounded-2xl text-center min-w-[80px] shadow-sm">
                    <span class="block text-2xl font-black text-slate-900">01</span>
                    <span class="text-[10px] font-black uppercase text-slate-400">DEC</span>
                </div>
                <div>
                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest mb-3 inline-block">Deadline</span>
                    <h3 class="text-xl font-black text-slate-900 mb-2 group-hover:text-gold transition">April Intake Closing</h3>
                    <p class="text-xs text-slate-500 mb-4">Final submission date for April 2026 intake documents for language schools.</p>
                    <span class="text-[10px] font-bold text-slate-400 uppercase"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Urgent</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Pacific Finder Section (AJAX Powered) -->
<section id="finder" class="py-24 px-6 max-w-7xl mx-auto bg-slate-900 rounded-[60px] mb-20 text-white relative overflow-hidden" 
         x-data="{ 
            filter: 'all', 
            schools: [],
            loading: true,
            async fetchSchools() {
                this.loading = true;
                try {
                    const response = await fetch('<?= base_url('api/search_schools.php') ?>?region=' + this.filter);
                    const json = await response.json();
                    this.schools = json.data;
                } catch(e) {
                    console.error('Error fetching schools:', e);
                }
                this.loading = false;
            }
         }"
         x-init="fetchSchools()">
    
    <div class="absolute top-0 right-0 w-1/2 h-full bg-white/5 skew-x-12 pointer-events-none"></div>
    <div class="relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 px-8 pt-8">
            <div>
                 <h2 class="text-4xl font-black italic text-white">PACIFIC <span class="text-red-500">FINDER</span></h2>
                 <p class="text-slate-400 mt-2">Live Database of Partner Institutions</p>
            </div>
            
            <!-- Filter Buttons -->
            <div class="flex bg-white/10 p-1 rounded-full border border-white/10 backdrop-blur-sm mt-6 md:mt-0">
                <button @click="filter = 'all'; fetchSchools()" :class="filter === 'all' ? 'bg-white text-slate-900' : 'text-slate-400 hover:text-white'" class="px-6 py-2 rounded-full text-xs font-black uppercase transition-all">All</button>
                <button @click="filter = 'Tokyo'; fetchSchools()" :class="filter === 'Tokyo' ? 'bg-white text-slate-900' : 'text-slate-400 hover:text-white'" class="px-6 py-2 rounded-full text-xs font-black uppercase transition-all">Tokyo</button>
                <button @click="filter = 'Osaka'; fetchSchools()" :class="filter === 'Osaka' ? 'bg-white text-slate-900' : 'text-slate-400 hover:text-white'" class="px-6 py-2 rounded-full text-xs font-black uppercase transition-all">Osaka</button>
            </div>
        </div>
        
        <!-- School Grid -->
        <div class="px-8 pb-8 min-h-[400px]">
            
            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center items-center h-64">
                <i class="fa-solid fa-circle-notch fa-spin text-4xl text-gold"></i>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && schools.length === 0" class="flex justify-center items-center h-32 text-slate-500">
                No institutions found for this region.
            </div>

            <!-- Results -->
            <div x-show="!loading && schools.length > 0" class="grid md:grid-cols-3 gap-8">
                <template x-for="school in schools" :key="school.id">
                    <div class="bg-white rounded-[32px] overflow-hidden border border-slate-100 hover:scale-[1.02] transition duration-300 group shadow-2xl">
                        <div class="h-48 bg-slate-800 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-60"></div>
                            <div class="absolute top-4 left-4 bg-red-600 text-white text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg" x-text="school.type"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                 <div class="text-[10px] font-black uppercase text-white/60 tracking-widest">Est. <span x-text="school.est_year"></span></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-black leading-tight mb-1 text-slate-900" x-text="school.school_name"></h3>
                            <p class="text-xs font-bold text-slate-400 mb-6 flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-red-500"></i> <span x-text="school.region"></span> Region
                            </p>
                            <a :href="'<?= base_url('index.php?route=pages/school_details&id=') ?>' + school.id" class="block w-full py-3 rounded-xl border-2 border-slate-100 text-xs font-black uppercase text-center text-slate-900 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition">
                                View Profile
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-24 bg-white px-6">
    <div class="max-w-4xl mx-auto" x-data="{ active: 1 }">
        <div class="text-center mb-16">
            <span class="text-[10px] font-black uppercase text-gold tracking-[0.2em] mb-3 block">Common Questions</span>
            <h2 class="text-4xl font-black italic text-slate-900">FAQ</h2>
        </div>

        <div class="space-y-4">
            <div class="border border-slate-100 rounded-3xl overflow-hidden">
                <button @click="active = (active === 1 ? null : 1)" class="w-full flex justify-between items-center p-6 bg-slate-50 hover:bg-slate-100 transition text-left">
                    <span class="font-bold text-slate-900">What are the requirements for the student visa?</span>
                    <i :class="active === 1 ? 'fa-minus' : 'fa-plus'" class="fa-solid text-gold text-sm"></i>
                </button>
                <div x-show="active === 1" x-collapse class="p-6 text-sm text-slate-500 leading-relaxed border-t border-slate-100">
                    To apply for a student visa, you typically need to have completed 12 years of education (High School graduate), pass the JLPT N5 or equivalent, and provide financial sponsorship documentation.
                </div>
            </div>
            <div class="border border-slate-100 rounded-3xl overflow-hidden">
                <button @click="active = (active === 2 ? null : 2)" class="w-full flex justify-between items-center p-6 bg-slate-50 hover:bg-slate-100 transition text-left">
                    <span class="font-bold text-slate-900">Can I work part-time in Japan?</span>
                    <i :class="active === 2 ? 'fa-minus' : 'fa-plus'" class="fa-solid text-gold text-sm"></i>
                </button>
                <div x-show="active === 2" x-collapse class="p-6 text-sm text-slate-500 leading-relaxed border-t border-slate-100">
                    Yes, international students in Japan can work up to 28 hours per week with a "Permission to Engage in Activity other than that Permitted under the Status of Residence Previously Granted."
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact/Enquiry Form -->
<section class="py-24 bg-slate-50 px-6">
    <div class="max-w-7xl mx-auto bg-slate-900 rounded-[60px] p-8 md:p-20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-1/2 h-full bg-white/5 skew-x-12 pointer-events-none"></div>
        
        <div class="grid md:grid-cols-2 gap-16 relative z-10 items-center">
            <div class="text-white">
                <span class="bg-gold text-slate-900 px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest mb-6 inline-block">Get Started</span>
                <h2 class="text-4xl md:text-5xl font-black mb-6 leading-tight">Start Your Journey <br>To Japan Today.</h2>
                <p class="text-slate-400 mb-8 max-w-md">Fill out the form and our admissions team will contact you within 24 hours for a free consultation.</p>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-gold"><i class="fa-solid fa-phone"></i></div>
                        <span class="font-bold"><?= ORG_PHONE ?></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-gold"><i class="fa-solid fa-envelope"></i></div>
                        <span class="font-bold"><?= ORG_EMAIL ?></span>
                    </div>
                </div>
            </div>
            
            <form action="<?= base_url('index.php?route=pages/save_enquiry') ?>" method="POST" class="bg-white p-8 md:p-12 rounded-[40px] shadow-2xl space-y-4">
                <h3 class="text-xl font-black text-slate-900 mb-6 uppercase italic">Quick Enquiry</h3>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded-xl text-xs font-bold"><?= htmlspecialchars($_GET['msg']) ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="bg-red-100 text-red-700 p-3 rounded-xl text-xs font-bold"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <input type="text" name="full_name" required placeholder="Full Name" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-gold transition">
                <input type="email" name="email" required placeholder="Email Address" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-gold transition">
                <input type="text" name="phone" placeholder="Phone Number" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-gold transition">
                <select name="interest" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-gold transition text-slate-500">
                    <option value="General">Select Interest</option>
                    <option value="Language Course">Language Course (N5-N1)</option>
                    <option value="University">University Placement</option>
                    <option value="Visa Info">Student Visa Info</option>
                </select>
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-gold hover:text-slate-900 transition shadow-lg mt-4">Send Enquiry</button>
            </form>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>