<?php
// pages/mobile_app.php
// Exclusive PWA / Mobile Subdomain Entry Point
// Access via: index.php?route=pages/mobile_app

require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch App Data safely
try {
    $schools = $pdo->query("SELECT * FROM japan_schools ORDER BY created_at DESC LIMIT 10")->fetchAll();
    $programs = $pdo->query("SELECT * FROM class_divisions WHERE status='active' GROUP BY class_name LIMIT 5")->fetchAll();
    $testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    error_log("Mobile App DB Error: " . $e->getMessage());
    $schools = [];
    $programs = [];
    $testimonials = [];
}

// Convert Schools to JSON for Alpine.js real-time search
$schoolsJson = htmlspecialchars(json_encode($schools), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Sheindana App</title>
    
    <!-- PWA & Mobile App Meta Tags -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Sheindana">
    <link rel="apple-touch-icon" href="<?= asset_url('images/icon-192.png') ?>">
    
    <!-- Tailwind CSS & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&family=Noto+Sans+Myanmar:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', 'Noto Sans Myanmar', sans-serif; 
            background-color: #0f172a; /* Deep Slate 900 */
            color: #ffffff;
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior-y: none; /* Prevent pull-to-refresh on native feel */
        }
        
        /* Circuit Chaos Neon Tech Background */
        .app-bg {
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(217, 33, 40, 0.15), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(229, 184, 34, 0.15), transparent 25%),
                linear-gradient(to bottom, #0f172a, #020617);
        }

        /* Native App Scroll Snapping */
        .snap-x-container {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            gap: 1rem;
            padding: 0 1.5rem 1rem 1.5rem;
        }
        .snap-x-container::-webkit-scrollbar { display: none; }
        .snap-card {
            scroll-snap-align: center;
            flex: 0 0 85%;
        }
        .snap-card-testimonial {
            scroll-snap-align: center;
            flex: 0 0 90%;
        }

        /* Bottom Nav Safe Area for iPhones */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 20px); }
        
        /* Google Translate Hiding */
        .goog-te-banner-frame, .goog-te-gadget { display: none !important; }
        .skiptranslate { display: none !important; }
        body { top: 0 !important; }

        [x-cloak] { display: none !important; }
    </style>
</head>
<!-- Initialize Alpine State with Real-Time Search Data -->
<body class="app-bg h-screen w-screen overflow-hidden flex flex-col" 
      x-data="{ 
          appLoaded: false, 
          currentTab: 'home', 
          showLangModal: false,
          searchQuery: '',
          schoolsList: <?= $schoolsJson ?>
      }">

    <!-- Native App Splash Screen (Fades out after 800ms) -->
    <div x-show="!appLoaded" 
         x-init="setTimeout(() => appLoaded = true, 800)"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-110"
         class="fixed inset-0 z-[200] bg-slate-900 flex flex-col items-center justify-center">
        <div class="w-24 h-24 bg-[#E5B822] rounded-3xl flex items-center justify-center text-slate-900 font-black text-4xl shadow-[0_0_40px_rgba(229,184,34,0.5)] animate-bounce relative">
            SD
            <div class="absolute inset-0 rounded-3xl border-4 border-[#E5B822] animate-ping opacity-50"></div>
        </div>
        <h1 class="text-white font-black mt-8 text-xl tracking-widest uppercase">Sheindana</h1>
        <div class="mt-4 w-32 h-1 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full bg-[#D92128] animate-pulse w-full"></div>
        </div>
    </div>

    <!-- Hidden Google Translate Element -->
    <div id="google_translate_element"></div>

    <!-- App Header -->
    <header class="pt-safe px-6 py-4 flex justify-between items-center bg-slate-900/80 backdrop-blur-xl border-b border-white/5 z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#E5B822] flex items-center justify-center text-slate-900 font-black text-[10px] shadow-[0_0_15px_rgba(229,184,34,0.5)]">
                SD
            </div>
            <div>
                <h1 class="text-sm font-black uppercase tracking-widest leading-none">Sheindana</h1>
                <p class="text-[8px] text-[#D92128] font-bold tracking-widest uppercase">Mobile Portal</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Language Switcher Trigger -->
            <button @click="showLangModal = true" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center border border-white/10 text-[#E5B822] active:scale-95 transition-transform shadow-sm">
                <i class="fa-solid fa-globe text-sm"></i>
            </button>
            <!-- Notifications -->
            <button class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center relative active:scale-95 transition-transform shadow-sm">
                <i class="fa-regular fa-bell text-sm"></i>
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-[#D92128] animate-pulse shadow-[0_0_8px_#D92128]"></span>
            </button>
        </div>
    </header>

    <!-- Scrollable Main Content -->
    <main class="flex-1 overflow-y-auto pb-24 relative z-0">
        
        <!-- HOME TAB -->
        <div x-show="currentTab === 'home'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="h-full">
            
            <!-- Greeting -->
            <div class="px-6 py-6">
                <h2 class="text-2xl font-black mb-1">Discover <span class="text-[#E5B822]">Japan.</span></h2>
                <p class="text-xs text-slate-400 font-medium">Explore premium institutions & language courses.</p>
            </div>

            <!-- Spotlight: Japan Schools (Swipeable Tinder/Netflix style cards) -->
            <?php if(count($schools) > 0): ?>
            <div class="mb-6">
                <div class="px-6 flex justify-between items-end mb-4">
                    <h3 class="text-sm font-black uppercase tracking-widest border-l-2 border-[#D92128] pl-2">Pacific Database</h3>
                    <button @click="currentTab = 'schools'" class="text-[9px] text-[#E5B822] uppercase font-bold tracking-widest active:opacity-50">View All</button>
                </div>
                
                <div class="snap-x-container">
                    <?php foreach($schools as $school): ?>
                    <div class="snap-card bg-white/5 border border-white/10 rounded-3xl p-5 backdrop-blur-sm relative overflow-hidden flex flex-col min-h-[180px] active:scale-[0.98] transition-transform" @click="window.location.href='<?= route('pages/school_details&id=' . $school['id']) ?>'">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#D92128] rounded-full blur-[50px] opacity-20 pointer-events-none"></div>
                        
                        <div class="flex justify-between items-start mb-auto relative z-10">
                            <span class="bg-white/10 text-white px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border border-white/20 shadow-sm"><?= h($school['type']) ?></span>
                            <div class="w-10 h-10 rounded-xl bg-[#E5B822] flex items-center justify-center font-black text-slate-900 text-lg shadow-lg">
                                <?= strtoupper(substr($school['school_name'], 0, 1)) ?>
                            </div>
                        </div>
                        
                        <div class="relative z-10 mt-6">
                            <h4 class="font-black text-lg leading-tight mb-1 truncate"><?= h($school['school_name']) ?></h4>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-[#D92128]"></i> <?= h($school['city']) ?>, <?= h($school['region']) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Academic Programs List -->
            <?php if(count($programs) > 0): ?>
            <div class="px-6 mb-8">
                <h3 class="text-sm font-black uppercase tracking-widest border-l-2 border-[#E5B822] pl-2 mb-4">Academic Programs</h3>
                <div class="space-y-3">
                    <?php foreach($programs as $program): ?>
                    <div class="bg-slate-800/50 border border-slate-700 p-4 rounded-2xl flex items-center gap-4 active:scale-95 transition-transform cursor-pointer shadow-sm" @click="window.location.href='<?= route('pages/class_details&name=' . urlencode($program['class_name'])) ?>'">
                        <div class="w-12 h-12 rounded-xl bg-slate-900 flex items-center justify-center text-[#E5B822] text-xl shadow-inner border border-slate-800">
                            <i class="<?= h($program['icon'] ?? 'fa-solid fa-book') ?>"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-sm text-white"><?= h($program['class_name']) ?></h4>
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest mt-0.5">
                                <?= h($program['duration_text']) ?> • <?= h($program['shift']) ?>
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- DYNAMIC TESTIMONIALS (CMS Driven - Swipeable) -->
            <?php if(count($testimonials) > 0): ?>
            <div class="mb-8">
                <div class="px-6 mb-4">
                    <h3 class="text-sm font-black uppercase tracking-widest border-l-2 border-[#D92128] pl-2">Alumni Success</h3>
                </div>
                
                <div class="snap-x-container">
                    <?php foreach($testimonials as $testimony): ?>
                    <div class="snap-card-testimonial bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-3xl p-6 shadow-xl flex flex-col justify-between min-h-[160px] relative overflow-hidden">
                        <!-- Decorative Quote Icon -->
                        <div class="absolute top-4 right-4 text-4xl text-white/5 font-serif">"</div>
                        
                        <p class="text-slate-300 text-xs italic leading-relaxed mb-6 flex-1 relative z-10">
                            "<?= h($testimony['quote']) ?>"
                        </p>
                        
                        <div class="flex items-center gap-3 relative z-10 border-t border-slate-700 pt-4">
                            <div class="w-10 h-10 rounded-full bg-slate-700 bg-cover bg-center border-2 border-slate-600 shrink-0 shadow-sm" style="background-image: url('<?= asset_url('images/testimonials/' . h($testimony['image_path'])) ?>');"></div>
                            <div class="overflow-hidden">
                                <h4 class="font-bold text-white text-xs leading-tight truncate"><?= h($testimony['name']) ?></h4>
                                <p class="text-[8px] text-[#E5B822] font-black uppercase tracking-widest mt-0.5 truncate"><?= h($testimony['placement']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- App Exclusive Consultation Card -->
            <div class="px-6 pb-6">
                <div class="bg-gradient-to-br from-[#D92128] to-red-900 rounded-3xl p-6 relative overflow-hidden shadow-2xl">
                    <div class="absolute -right-6 -bottom-6 text-6xl text-white/10"><i class="fa-solid fa-headset"></i></div>
                    <h3 class="font-black text-lg mb-1 relative z-10">Need Guidance?</h3>
                    <p class="text-xs text-white/80 mb-4 relative z-10 max-w-[200px]">Speak to an agent immediately via our mobile hotline.</p>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', ORG_PHONE ?? '') ?>" class="inline-flex items-center justify-center gap-2 bg-white text-red-900 px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg active:scale-95 transition relative z-10 w-full md:w-auto">
                        <i class="fa-solid fa-phone"></i> Call Admissions
                    </a>
                </div>
            </div>

        </div>

        <!-- SCHOOLS TAB (Full Directory with Real-Time Alpine Search) -->
        <div x-show="currentTab === 'schools'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="p-6 h-full flex flex-col" style="display: none;">
            <h2 class="text-2xl font-black mb-6 shrink-0">Partner <span class="text-[#D92128]">Institutions</span></h2>
            
            <div class="relative mb-6 shrink-0">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" x-model="searchQuery" placeholder="Search schools by name or city..." class="w-full bg-slate-800 border border-slate-700 text-white text-xs px-10 py-3.5 rounded-xl outline-none focus:border-[#E5B822] transition placeholder-slate-500 shadow-inner">
                <!-- Clear Search Button -->
                <button x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Real-Time Filtered Grid -->
            <div class="grid grid-cols-2 gap-4 flex-1 overflow-y-auto pb-4">
                <template x-for="school in schoolsList.filter(s => s.school_name.toLowerCase().includes(searchQuery.toLowerCase()) || s.city.toLowerCase().includes(searchQuery.toLowerCase()) || s.region.toLowerCase().includes(searchQuery.toLowerCase()))" :key="school.id">
                    <a :href="'<?= base_url('index.php?route=pages/school_details&id=') ?>' + school.id" class="bg-white/5 border border-white/10 rounded-2xl p-4 flex flex-col justify-between aspect-square active:scale-95 transition-transform shadow-md block">
                        <div class="w-8 h-8 rounded-lg bg-[#E5B822] text-slate-900 flex items-center justify-center font-black mb-2 shadow-md" x-text="school.school_name.substring(0, 1).toUpperCase()"></div>
                        <div>
                            <h4 class="font-black text-xs leading-tight mb-1 truncate text-white" x-text="school.school_name"></h4>
                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest truncate">
                                <span x-text="school.city"></span>, <span x-text="school.region"></span>
                            </p>
                        </div>
                    </a>
                </template>

                <!-- Empty State -->
                <div x-show="schoolsList.filter(s => s.school_name.toLowerCase().includes(searchQuery.toLowerCase()) || s.city.toLowerCase().includes(searchQuery.toLowerCase())).length === 0" class="col-span-2 flex flex-col items-center justify-center py-12 opacity-50">
                    <i class="fa-solid fa-magnifying-glass-minus text-4xl mb-3"></i>
                    <p class="text-xs font-black uppercase tracking-widest">No schools found</p>
                </div>
            </div>
        </div>

        <!-- PORTAL TAB (Agent / Auth Entry) -->
        <div x-show="currentTab === 'portal'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="p-6 h-full flex flex-col justify-center" style="display: none;">
            <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-8 text-center relative overflow-hidden shadow-2xl">
                <div class="absolute inset-0 bg-[radial-gradient(#E5B822_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
                <div class="w-20 h-20 bg-slate-900 rounded-full flex items-center justify-center text-3xl text-[#E5B822] border-2 border-slate-700 mx-auto mb-6 relative z-10 shadow-lg">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <h2 class="text-xl font-black mb-2 relative z-10">Agent Portal Access</h2>
                <p class="text-xs text-slate-400 mb-8 relative z-10">Secure mobile gateway for verified partners and staff.</p>
                <a href="<?= auth_url('login') ?>" class="block w-full bg-[#E5B822] text-slate-900 py-3.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-[0_0_20px_rgba(229,184,34,0.3)] active:scale-95 transition relative z-10">
                    Authenticate Identity
                </a>
            </div>
        </div>

    </main>

    <!-- Native App Bottom Tab Navigation -->
    <nav class="fixed bottom-0 w-full bg-slate-900/95 backdrop-blur-2xl border-t border-white/10 pb-safe z-50">
        <div class="flex justify-around items-center px-2 pt-2 pb-1">
            
            <button @click="currentTab = 'home'; searchQuery = ''" class="flex flex-col items-center gap-1 p-2 w-16 transition-colors active:scale-95" :class="currentTab === 'home' ? 'text-[#E5B822]' : 'text-slate-500 hover:text-slate-300'">
                <i class="text-xl" :class="currentTab === 'home' ? 'fa-solid fa-house' : 'fa-light fa-house'"></i>
                <span class="text-[9px] font-bold tracking-widest uppercase">Home</span>
            </button>
            
            <button @click="currentTab = 'schools'" class="flex flex-col items-center gap-1 p-2 w-16 transition-colors active:scale-95" :class="currentTab === 'schools' ? 'text-[#D92128]' : 'text-slate-500 hover:text-slate-300'">
                <i class="text-xl" :class="currentTab === 'schools' ? 'fa-solid fa-magnifying-glass-location' : 'fa-light fa-magnifying-glass-location'"></i>
                <span class="text-[9px] font-bold tracking-widest uppercase">Finder</span>
            </button>
            
            <button @click="currentTab = 'portal'" class="flex flex-col items-center gap-1 p-2 w-16 transition-colors active:scale-95" :class="currentTab === 'portal' ? 'text-[#E5B822]' : 'text-slate-500 hover:text-slate-300'">
                <i class="text-xl" :class="currentTab === 'portal' ? 'fa-solid fa-user-shield' : 'fa-light fa-user'"></i>
                <span class="text-[9px] font-bold tracking-widest uppercase">Portal</span>
            </button>

        </div>
    </nav>

    <!-- Language Switcher Bottom Sheet Modal -->
    <div x-show="showLangModal" x-cloak class="fixed inset-0 z-[100] flex items-end justify-center bg-slate-900/80 backdrop-blur-sm" @click.self="showLangModal = false">
        <div x-show="showLangModal" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="translate-y-full opacity-0" 
             x-transition:enter-end="translate-y-0 opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="translate-y-0 opacity-100" 
             x-transition:leave-end="translate-y-full opacity-0" 
             class="bg-slate-800 w-full rounded-t-[32px] p-6 border-t border-white/10 shadow-2xl pb-safe">
            
            <div class="w-12 h-1 bg-slate-600 rounded-full mx-auto mb-6"></div>
            
            <h3 class="text-lg font-black text-white mb-4 uppercase tracking-widest text-center">Select Language</h3>
            
            <div class="space-y-3">
                <button @click="changeLanguage('en'); showLangModal = false;" class="w-full flex items-center justify-between bg-slate-900/50 border border-slate-700 px-6 py-4 rounded-2xl active:bg-slate-700 transition shadow-sm">
                    <span class="flex items-center gap-3 font-bold text-sm text-white"><span class="text-xl">🇬🇧</span> English</span>
                    <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                </button>
                <button @click="changeLanguage('ja'); showLangModal = false;" class="w-full flex items-center justify-between bg-slate-900/50 border border-slate-700 px-6 py-4 rounded-2xl active:bg-slate-700 transition font-sans shadow-sm">
                    <span class="flex items-center gap-3 font-bold text-sm text-white"><span class="text-xl">🇯🇵</span> 日本語 (JP)</span>
                    <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                </button>
                <button @click="changeLanguage('my'); showLangModal = false;" class="w-full flex items-center justify-between bg-slate-900/50 border border-slate-700 px-6 py-4 rounded-2xl active:bg-slate-700 transition font-mm shadow-sm">
                    <span class="flex items-center gap-3 font-bold text-sm text-white"><span class="text-xl">🇲🇲</span> မြန်မာ (MM)</span>
                    <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                </button>
            </div>
            
            <button @click="showLangModal = false" class="w-full mt-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white transition active:opacity-50">Cancel</button>
        </div>
    </div>

    <!-- PWA & Google Translate Scripts -->
    <script type="text/javascript">
        // Google Translate Init
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,ja,my', 
                autoDisplay: false
            }, 'google_translate_element');
        }

        // Custom UI App Language Switcher
        function changeLanguage(langCode) {
            document.cookie = "googtrans=/en/" + langCode + "; path=/;";
            document.cookie = "googtrans=/en/" + langCode + "; path=/; domain=" + window.location.hostname;
            
            var selectField = document.querySelector('select.goog-te-combo');
            if (selectField) {
                selectField.value = langCode;
                selectField.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                window.location.reload();
            }
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <!-- Service Worker Registration for PWA / APK Output -->
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('<?= base_url("sw.js") ?>')
            .then(registration => {
              console.log('PWA ServiceWorker registered with scope:', registration.scope);
            })
            .catch(error => {
              console.error('ServiceWorker registration failed:', error);
            });
        });
      }
    </script>

</body>
</html>