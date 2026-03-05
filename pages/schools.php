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
    error_log("Schools directory query failed: " . $e->getMessage());
}

// Include Header
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<header class="bg-slate-900 text-white pt-24 md:pt-32 pb-20 md:pb-28 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Abstract Brand Glows -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(var(--brand-gold) 1px, transparent 1px); background-size: 30px 30px;"></div>
    <div class="absolute top-[-20%] left-[-10%] w-[300px] h-[300px] md:w-[500px] md:h-[500px] bg-[--brand-red] rounded-full blur-[100px] md:blur-[150px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[250px] h-[250px] md:w-[400px] md:h-[400px] bg-[--brand-gold] rounded-full blur-[90px] md:blur-[120px] opacity-15 pointer-events-none mix-blend-screen"></div>

    <div class="max-w-[1600px] mx-auto relative z-10 text-center animate-in fade-in zoom-in duration-700">
        <span class="inline-flex items-center gap-2 bg-white/5 border border-white/10 px-4 py-2.5 md:py-2 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-widest mb-4 md:mb-6 text-[--brand-gold] shadow-lg backdrop-blur-sm">
            <i class="fa-solid fa-satellite-dish"></i> Official Network
        </span>
        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black mb-4 md:mb-6 leading-tight tracking-tighter">
            Pacific <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-300 to-[--brand-red]">Database</span>
        </h1>
        <p class="text-slate-400 text-sm sm:text-base lg:text-lg max-w-2xl mx-auto leading-relaxed px-4 sm:px-0">
            Explore our curated network of premium Japanese Language Schools, Universities, and Vocational Institutes across Tokyo, Osaka, and Fukuoka.
        </p>
    </div>
</header>

<!-- Main Directory with Real-Time Alpine.js Filtering -->
<!-- scroll-mt-32 ensures hash links work well with the sticky header if needed later -->
<main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 scroll-mt-32" x-data="{ filter: 'All', search: '' }">
    
    <!-- Advanced Toolbar -->
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4 md:gap-6 mb-10 md:mb-12 bg-white p-4 md:p-6 rounded-[24px] md:rounded-[32px] shadow-xl border border-slate-100 relative z-20 -mt-16 md:-mt-24">
        
        <!-- Live Search -->
        <div class="w-full lg:w-2/5 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 md:left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Search institutions by name..." class="w-full bg-slate-50 border border-slate-100 pl-11 md:pl-12 pr-4 py-3.5 md:py-4 rounded-xl md:rounded-2xl text-sm md:text-base font-bold outline-none focus:ring-2 focus:ring-[--brand-gold]/50 focus:bg-white focus:border-[--brand-gold] transition-all">
        </div>
        
        <!-- Region Filters (Scrollable horizontally on mobile with hidden scrollbar for better UX) -->
        <div class="flex bg-slate-50 p-1.5 rounded-xl md:rounded-2xl shadow-inner border border-slate-100 overflow-x-auto w-full lg:w-auto snap-x snap-mandatory [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
            <template x-for="type in ['All', 'Tokyo', 'Osaka', 'Fukuoka', 'Other']" :key="type">
                <button @click="filter = type" 
                        :class="filter === type ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50'"
                        class="snap-center px-6 md:px-8 py-3 rounded-lg md:rounded-xl text-xs md:text-sm font-black uppercase tracking-widest transition-all whitespace-nowrap flex-1 lg:flex-none focus:outline-none focus:ring-2 focus:ring-[--brand-gold]/50 min-w-[100px]"
                        x-text="type"></button>
            </template>
        </div>
    </div>

    <!-- Results Grid -->
    <?php if(!empty($schools)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6 md:gap-8 relative min-h-[300px]">
            <?php foreach($schools as $school): ?>
            <!-- Alpine visibility logic mixed with PHP data -->
            <div x-show="(filter === 'All' || filter === '<?= h($school['region']) ?>') && ('<?= strtolower(h($school['school_name'])) ?>'.includes(search.toLowerCase()) || search === '')" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 absolute"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="school-card bg-white rounded-[24px] md:rounded-[32px] overflow-hidden border border-slate-100 hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] hover:border-[--brand-gold] hover:-translate-y-2 transition-all duration-300 group flex flex-col h-full relative focus-within:ring-4 focus-within:ring-[--brand-gold]/50">
                
                <!-- Hover Gradient Top Border -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[--brand-red] to-[--brand-gold] opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="p-5 sm:p-6 md:p-8 flex-grow flex flex-col">
                    <div class="flex justify-between items-start mb-4 md:mb-6">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-50 rounded-xl md:rounded-2xl flex items-center justify-center text-xl md:text-2xl font-black text-[--brand-gold] group-hover:bg-slate-900 transition-colors shadow-sm border border-slate-100 shrink-0">
                            <?= strtoupper(substr($school['school_name'], 0, 1)) ?>
                        </div>
                        <span class="bg-[--brand-red]/10 text-[--brand-red] border border-[--brand-red]/20 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest shadow-sm text-center ml-3">
                            <?= h($school['type']) ?>
                        </span>
                    </div>
                    
                    <h3 class="text-lg md:text-xl font-black leading-tight mb-3 text-slate-900 group-hover:text-[--brand-red] transition-colors">
                        <?= h($school['school_name']) ?>
                    </h3>
                    
                    <div class="space-y-2 text-xs md:text-sm font-bold text-slate-500 mt-auto bg-slate-50/50 p-3.5 md:p-4 rounded-xl md:rounded-2xl border border-slate-50">
                        <p class="flex items-center gap-3"><i class="fa-solid fa-location-dot text-[--brand-gold] w-4 text-center"></i> <span class="truncate"><?= h($school['city']) ?>, <span class="text-slate-900"><?= h($school['region']) ?></span></span></p>
                        <p class="flex items-center gap-3"><i class="fa-regular fa-calendar w-4 text-center text-slate-400"></i> Est. <?= h($school['est_year']) ?></p>
                        <p class="flex items-center gap-3"><i class="fa-solid fa-yen-sign w-4 text-center text-slate-400"></i> ~<?= number_format($school['tuition_fees'] ?? 0) ?> JPY / Yr</p>
                    </div>
                </div>
                
                <div class="p-4 md:p-5 border-t border-slate-100 mt-auto bg-white">
                    <!-- Interactive Link taking up the bottom section -->
                    <a href="<?= route('pages/school_details&id=' . $school['id']) ?>" class="flex items-center justify-center gap-2 w-full bg-slate-50 text-slate-900 py-3.5 md:py-4 rounded-xl text-xs font-black uppercase tracking-widest group-hover:bg-slate-900 group-hover:text-white focus:outline-none focus:bg-slate-900 focus:text-white transition-colors active:scale-[0.98]">
                        View Profile <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Empty State for Live Search (Alpine Logic) -->
        <script>
            document.addEventListener('alpine:initialized', () => {
                Alpine.effect(() => {
                    setTimeout(() => {
                        const cards = document.querySelectorAll('.school-card');
                        const hiddenCards = document.querySelectorAll('.school-card[style*="display: none"]');
                        const emptyState = document.getElementById('empty-search-state');
                        if (cards.length === hiddenCards.length && cards.length > 0) {
                            emptyState.style.display = 'block';
                        } else {
                            emptyState.style.display = 'none';
                        }
                    }, 50); // slight delay for Alpine transitions
                });
            });
        </script>
        
        <div id="empty-search-state" style="display: none;" class="text-center py-16 md:py-24 bg-white rounded-[24px] md:rounded-[40px] border border-slate-100 shadow-sm mt-8 relative overflow-hidden">
             <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNFMUI4MjIiIGZpbGwtb3BhY2l0eT0iMC4yIi8+PC9zdmc+')] opacity-50"></div>
             <div class="relative z-10 px-4">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl md:text-3xl border border-slate-100">
                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                </div>
                <h3 class="text-lg md:text-xl font-black text-slate-900 uppercase tracking-tight">No matching institutions</h3>
                <p class="text-slate-500 mt-2 font-medium text-sm">Try adjusting your search term for "<span x-text="search" class="text-slate-900 font-bold"></span>" or changing the region.</p>
                <button @click="search = ''; filter = 'All'" class="mt-6 text-xs font-black uppercase text-[--brand-red] hover:underline focus:outline-none p-2">Clear Search Filters</button>
             </div>
        </div>

    <?php else: ?>
        <!-- Empty State for Empty Database (No records fetched from PHP) -->
        <div class="text-center py-16 md:py-24 bg-white rounded-[24px] md:rounded-[40px] border border-slate-100 shadow-sm px-4">
            <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl md:text-3xl border border-slate-100">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <h3 class="text-lg md:text-xl font-black text-slate-900 uppercase tracking-tight">Database Empty</h3>
            <p class="text-slate-500 mt-2 font-medium text-sm">No partner schools have been listed in the system yet.</p>
        </div>
    <?php endif; ?>
</main>

<!-- Call to Action Section -->
<section class="bg-slate-900 py-16 md:py-24 px-4 sm:px-6 lg:px-8 text-center text-white relative overflow-hidden mt-12 md:mt-24">
    <!-- Immersive Background Orbs -->
    <div class="absolute top-0 right-0 w-48 h-48 md:w-64 md:h-64 bg-[--brand-red] rounded-full blur-[80px] md:blur-[120px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 md:w-64 md:h-64 bg-[--brand-gold] rounded-full blur-[80px] md:blur-[120px] opacity-10 pointer-events-none"></div>
    
    <div class="max-w-3xl mx-auto relative z-10">
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black italic uppercase mb-4 md:mb-6 tracking-tight leading-tight">Ready to <span class="text-[--brand-gold]">apply?</span></h2>
        <p class="text-slate-400 mb-8 md:mb-10 leading-relaxed text-sm md:text-lg max-w-xl mx-auto">
            Our authorized agents are standing by to guide you through the COE process and enrollment for any of our verified partner institutions in Japan.
        </p>
        <a href="<?= base_url('index.php?route=pages/landing#enquiry') ?>" class="inline-flex items-center justify-center gap-3 w-full sm:w-auto bg-[--brand-red] text-white px-8 md:px-10 py-4 md:py-5 rounded-xl md:rounded-2xl font-black text-xs md:text-sm uppercase tracking-widest hover:bg-[--brand-gold] hover:text-slate-900 hover:shadow-[0_0_30px_rgba(229,184,34,0.4)] focus:outline-none focus:ring-4 focus:ring-[--brand-gold]/50 transition-all transform hover:-translate-y-1 active:scale-[0.98]">
            <i class="fa-solid fa-paper-plane"></i> Contact Admissions
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>