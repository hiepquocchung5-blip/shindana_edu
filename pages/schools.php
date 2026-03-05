<?php
// pages/schools.php
// loaded via index.php?route=pages/schools

require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch All Active Schools
try {
    $stmt = $pdo->query("SELECT * FROM japan_schools ORDER BY region ASC, school_name ASC");
    $schools = $stmt->fetchAll();
} catch (PDOException $e) {
    $schools = [];
}

// Include Header
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<header class="bg-slate-900 text-white pt-24 pb-20 px-6 relative overflow-hidden">
    <!-- Abstract Brand Glows -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#E5B822 1px, transparent 1px); background-size: 30px 30px;"></div>
    <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-[#D92128] rounded-full blur-[150px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[400px] h-[400px] bg-[#E5B822] rounded-full blur-[120px] opacity-15 pointer-events-none mix-blend-screen"></div>

    <div class="max-w-[1600px] mx-auto relative z-10 text-center animate-in fade-in zoom-in duration-700">
        <span class="inline-flex items-center gap-2 bg-white/5 border border-white/10 px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest mb-6 text-[#E5B822] shadow-lg backdrop-blur-sm">
            <i class="fa-solid fa-satellite-dish"></i> Official Network
        </span>
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 leading-tight tracking-tighter">
            Pacific <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-300 to-[#D92128]">Database</span>
        </h1>
        <p class="text-slate-400 text-base md:text-lg max-w-2xl mx-auto leading-relaxed">
            Explore our curated network of premium Japanese Language Schools, Universities, and Vocational Institutes across Tokyo, Osaka, and Fukuoka.
        </p>
    </div>
</header>

<!-- Main Directory with Real-Time Alpine.js Filtering -->
<main class="max-w-[1600px] mx-auto px-6 py-16" x-data="{ filter: 'All', search: '' }">
    
    <!-- Advanced Toolbar -->
    <div class="flex flex-col lg:flex-row justify-between items-center gap-6 mb-12 bg-white p-4 rounded-3xl shadow-lg border border-slate-100 relative z-20 -mt-24">
        
        <!-- Live Search -->
        <div class="w-full lg:w-1/3 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Search institutions by name..." class="w-full bg-slate-50 border border-slate-100 pl-12 pr-4 py-4 rounded-2xl text-sm font-bold focus:outline-none focus:ring-2 ring-[#E5B822]/50 focus:bg-white transition-all">
        </div>
        
        <!-- Region Filters -->
        <div class="flex bg-slate-50 p-1.5 rounded-2xl shadow-inner border border-slate-100 overflow-x-auto w-full lg:w-auto">
            <template x-for="type in ['All', 'Tokyo', 'Osaka', 'Fukuoka']" :key="type">
                <button @click="filter = type" 
                        :class="filter === type ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50'"
                        class="px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap flex-1 lg:flex-none"
                        x-text="type"></button>
            </template>
        </div>
    </div>

    <!-- Results Grid -->
    <?php if(!empty($schools)): ?>
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
            <?php foreach($schools as $school): ?>
            <!-- Alpine visibility logic mixed with PHP data -->
            <div x-show="(filter === 'All' || filter === '<?= h($school['region']) ?>') && ('<?= strtolower(h($school['school_name'])) ?>'.includes(search.toLowerCase()) || search === '')" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-[32px] overflow-hidden border border-slate-100 hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] hover:-translate-y-2 transition-all duration-300 group flex flex-col h-full relative">
                
                <!-- Hover Gradient Top Border -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#D92128] to-[#E5B822] opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="p-8 flex-grow">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-2xl font-black text-[#E5B822] group-hover:bg-slate-900 transition-colors shadow-sm">
                            <?= strtoupper(substr($school['school_name'], 0, 1)) ?>
                        </div>
                        <span class="bg-[#D92128]/10 text-[#D92128] border border-[#D92128]/20 px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest">
                            <?= h($school['type']) ?>
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-black leading-tight mb-3 text-slate-900 group-hover:text-[#D92128] transition-colors">
                        <?= h($school['school_name']) ?>
                    </h3>
                    
                    <div class="space-y-3 text-xs font-bold text-slate-500 mb-6 bg-slate-50/50 p-4 rounded-2xl border border-slate-50">
                        <p class="flex items-center gap-3"><i class="fa-solid fa-location-dot text-[#E5B822] w-4 text-center"></i> <?= h($school['city']) ?>, <span class="text-slate-900"><?= h($school['region']) ?></span></p>
                        <p class="flex items-center gap-3"><i class="fa-regular fa-calendar w-4 text-center text-slate-400"></i> Est. <?= h($school['est_year']) ?></p>
                        <p class="flex items-center gap-3"><i class="fa-solid fa-yen-sign w-4 text-center text-slate-400"></i> ~<?= number_format($school['tuition_fees'] ?? 0) ?> JPY / Yr</p>
                    </div>
                </div>
                
                <div class="p-4 border-t border-slate-100 mt-auto bg-white">
                    <a href="<?= route('pages/school_details&id=' . $school['id']) ?>" class="flex items-center justify-center gap-2 w-full bg-slate-50 text-slate-900 py-4 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-colors">
                        View Profile <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Empty State for Live Search -->
        <div x-show="search !== '' && !document.querySelector('[x-show]:not([style*=\'display: none\'])')" x-cloak class="text-center py-24 bg-white rounded-[40px] border border-slate-100 shadow-sm mt-8">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
                <i class="fa-solid fa-magnifying-glass-minus"></i>
            </div>
            <h3 class="text-xl font-black text-slate-900 uppercase">No matching institutions</h3>
            <p class="text-slate-500 mt-2 font-medium">Try adjusting your search term for "<span x-text="search" class="text-slate-900 font-bold"></span>".</p>
            <button @click="search = ''; filter = 'All'" class="mt-6 text-xs font-black uppercase text-[#D92128] hover:underline">Clear Search Filters</button>
        </div>

    <?php else: ?>
        <!-- Empty State for Empty Database -->
        <div class="text-center py-24 bg-white rounded-[40px] border border-slate-100 shadow-sm">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <h3 class="text-xl font-black text-slate-900 uppercase">Database Empty</h3>
            <p class="text-slate-500 mt-2 font-medium">No partner schools have been listed yet.</p>
        </div>
    <?php endif; ?>
</main>

<!-- Call to Action Section -->
<section class="bg-slate-900 py-24 px-6 text-center text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#D92128] rounded-full blur-[120px] opacity-20 pointer-events-none"></div>
    <div class="max-w-3xl mx-auto relative z-10">
        <h2 class="text-3xl md:text-5xl font-black italic uppercase mb-6 tracking-tight">Ready to <span class="text-[#E5B822]">apply?</span></h2>
        <p class="text-slate-400 mb-10 leading-relaxed text-lg">Our authorized agents are standing by to guide you through the COE process and enrollment for any of our verified partner institutions in Japan.</p>
        <a href="<?= base_url('index.php?route=pages/landing#enquiry') ?>" class="inline-flex items-center gap-3 bg-[#D92128] text-white px-10 py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#E5B822] hover:text-slate-900 hover:shadow-[0_0_30px_rgba(229,184,34,0.4)] transition-all transform hover:-translate-y-1">
            <i class="fa-solid fa-paper-plane"></i> Contact Admissions
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>