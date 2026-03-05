<?php 
require_once __DIR__ . '/../config/functions.php'; 

// Check active session to dynamically alter the CTA button
$isLoggedIn = isset($_SESSION['user_id']);
$dashboardLink = auth_url('login');
if ($isLoggedIn) {
    $dashboardLink = ($_SESSION['user_type'] === 'admin') ? admin_url('index') : agent_url('index');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= h(APP_NAME) ?> | <?= h(APP_TAGLINE) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js for Interactive UI -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Noto+Sans+Myanmar:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <style>
        :root {
            /* Extracted strictly from the provided SHN Logo (Ignoring Green) */
            --brand-gold: #E5B822; 
            --brand-red: #D92128;
            --slate-900: #0f172a;
        }
        
        /* Utility Classes based on new brand colors */
        .text-brand-gold { color: var(--brand-gold); }
        .text-brand-red { color: var(--brand-red); }
        .bg-brand-gold { background-color: var(--brand-gold); }
        .bg-brand-red { background-color: var(--brand-red); }
        
        /* Mega Menu Dropdown */
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dropdown-wrapper:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* HIDE DEFAULT GOOGLE TRANSLATE UI COMPLETELY */
        .goog-te-banner-frame, .goog-te-gadget { display: none !important; }
        .skiptranslate { display: none !important; }
        body { top: 0 !important; }
        
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased text-slate-900 flex flex-col min-h-screen" 
      x-data="{ mobileMenuOpen: false, scrolled: false, langOpen: false }"
      :class="mobileMenuOpen ? 'overflow-hidden' : ''"
      @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Hidden Google Translate Element (Required for API) -->
    <div id="google_translate_element"></div>

    <!-- Fixed Header Wrapper -->
    <header class="fixed w-full z-50 transition-all duration-300 flex flex-col"
            :class="scrolled ? 'shadow-xl' : 'shadow-sm'">
        
        <!-- Corporate Micro-Header (Top Bar) - Hidden on Mobile, Shows on Tablet (md) -->
        <div class="bg-slate-900 text-white/80 py-1.5 px-4 sm:px-6 lg:px-12 text-[9px] md:text-[10px] font-bold uppercase tracking-widest hidden md:flex justify-between items-center z-50 relative transition-all duration-300"
             :class="scrolled ? 'h-0 opacity-0 overflow-hidden py-0' : 'h-auto opacity-100'">
            <div class="flex gap-4 md:gap-6">
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', ORG_PHONE) ?>" class="hover:text-white transition flex items-center"><i class="fa-solid fa-phone text-[--brand-gold] mr-1.5"></i> <?= h(ORG_PHONE) ?></a>
                <a href="mailto:<?= h(ORG_EMAIL) ?>" class="hover:text-white transition flex items-center"><i class="fa-solid fa-envelope text-[--brand-red] mr-1.5"></i> <?= h(ORG_EMAIL) ?></a>
            </div>
            <div class="flex gap-4">
                <a href="#" class="hover:text-[--brand-gold] transition">Careers</a>
                <div class="w-px h-3 bg-white/20 my-auto"></div>
                <a href="#" class="hover:text-[--brand-gold] transition">News & Events</a>
            </div>
        </div>

        <!-- Main Global Navigation -->
        <nav class="border-b border-slate-100 w-full z-40 relative transition-all duration-300"
             :class="scrolled ? 'bg-white/95 backdrop-blur-xl py-2 md:py-3' : 'bg-white/90 backdrop-blur-md py-3 md:py-4'">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-12 flex justify-between items-center">
                
                <!-- Brand Logo (Optimized for 320px-425px) -->
                <a href="<?= base_url() ?>" class="flex items-center gap-2.5 sm:gap-3 group z-50 shrink-0">
                    <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-[--brand-gold] flex items-center justify-center shadow-lg group-hover:-rotate-12 transition-transform relative overflow-hidden border-2 border-white shrink-0">
                        <img src="<?= asset_url('images/shine_logo.png') ?>" alt="SHN" class="w-full h-full object-cover relative z-10" onerror="this.style.display='none'">
                        <!-- Fallback if image fails -->
                        <span class="absolute inset-0 flex items-center justify-center text-[--brand-red] font-black text-xs sm:text-sm italic tracking-tighter" style="z-index: 1;">SHN</span>
                    </div>
                    <div class="leading-tight shrink-0">
                        <span class="block font-black uppercase text-slate-900 tracking-tight text-base sm:text-lg group-hover:text-[--brand-red] transition-colors">
                            Shinedana<span class="text-[--brand-gold]">.com</span>
                        </span>
                        <span class="block text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase tracking-widest hidden sm:block">
                            <?= h(APP_TAGLINE) ?>
                        </span>
                    </div>
                </a>

                <!-- Desktop Menu (Hidden below 1024px) -->
                <div class="hidden lg:flex items-center gap-6 xl:gap-8 h-full">
                    <!-- Dropdown Wrapper -->
                    <div class="relative py-4 dropdown-wrapper cursor-pointer h-full flex items-center">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-[--brand-red] transition flex items-center gap-1.5">
                            Programs <i class="fa-solid fa-chevron-down text-[9px] opacity-50"></i>
                        </span>
                        
                        <!-- Dropdown Content -->
                        <div class="dropdown-menu absolute top-[100%] left-1/2 -translate-x-1/2 w-[400px] bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 grid grid-cols-2 gap-2">
                            <a href="<?= base_url('index.php?route=pages/landing#programs') ?>" class="p-3 rounded-xl hover:bg-slate-50 group/item transition">
                                <div class="text-[10px] font-black text-[--brand-red] uppercase tracking-widest mb-1"><i class="fa-solid fa-language mr-1"></i> Language</div>
                                <div class="text-sm font-bold text-slate-900 group-hover/item:text-[--brand-gold] transition">JLPT N5 - N1</div>
                            </a>
                            <a href="<?= base_url('index.php?route=pages/landing#programs') ?>" class="p-3 rounded-xl hover:bg-slate-50 group/item transition">
                                <div class="text-[10px] font-black text-[--brand-red] uppercase tracking-widest mb-1"><i class="fa-solid fa-building-columns mr-1"></i> University Prep</div>
                                <div class="text-sm font-bold text-slate-900 group-hover/item:text-[--brand-gold] transition">EJU Courses</div>
                            </a>
                            <a href="<?= base_url('index.php?route=pages/landing#programs') ?>" class="p-3 rounded-xl hover:bg-slate-50 group/item transition col-span-2 mt-2 bg-slate-900 text-white flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">View All</div>
                                    <div class="text-sm font-black uppercase text-[--brand-gold]">Academic Curriculum</div>
                                </div>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <a href="<?= base_url('index.php?route=pages/landing#branches') ?>" class="text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-[--brand-red] transition">Network</a>
                    <a href="<?= route('pages/schools') ?>" class="text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-[--brand-red] transition">Pacific DB</a>
                </div>

                <!-- Utilities (Translate + Login + Hamburger) -->
                <div class="flex items-center gap-3 sm:gap-4 z-50">
                    
                    <!-- Custom Language Switcher (Visible on Tablet/Desktop) -->
                    <div class="hidden md:block relative" @click.away="langOpen = false">
                        <button @click="langOpen = !langOpen" class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-2 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 hover:border-slate-300 transition focus:outline-none focus:ring-2 focus:ring-[--brand-gold]/30">
                            <i class="fa-solid fa-globe text-[--brand-gold]"></i>
                            <span id="current-lang-display">🇬🇧 EN</span>
                            <i class="fa-solid fa-chevron-down text-slate-400"></i>
                        </button>
                        
                        <div x-show="langOpen" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 mt-2 w-36 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden">
                            <button onclick="changeLanguage('en')" class="w-full flex items-center gap-2 text-left px-4 py-3 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:text-[--brand-red] transition border-b border-slate-50">
                                <span class="text-sm">🇬🇧</span> English
                            </button>
                            <button onclick="changeLanguage('ja')" class="w-full flex items-center gap-2 text-left px-4 py-3 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:text-[--brand-red] transition border-b border-slate-50 font-sans">
                                <span class="text-sm">🇯🇵</span> 日本語 (JP)
                            </button>
                            <button onclick="changeLanguage('my')" class="w-full flex items-center gap-2 text-left px-4 py-3 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:text-[--brand-red] transition font-mm">
                                <span class="text-sm">🇲🇲</span> မြန်မာ (MM)
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Auth Button (Visible on Tablet/Desktop) -->
                    <a href="<?= $dashboardLink ?>" class="hidden md:flex bg-slate-900 text-white px-5 lg:px-6 py-2.5 rounded-full text-[10px] lg:text-xs font-black uppercase hover:bg-[--brand-red] transition shadow-xl items-center gap-2 transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-[--brand-red]/30">
                        <?php if($isLoggedIn): ?>
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div> Console
                        <?php else: ?>
                            <i class="fa-solid fa-lock text-[--brand-gold] text-[10px]"></i> Portal
                        <?php endif; ?>
                    </a>

                    <!-- Mobile/Tablet Hamburger -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-slate-900 focus:outline-none w-10 h-10 sm:w-11 sm:h-11 flex items-center justify-center bg-slate-50 hover:bg-slate-100 rounded-full transition-colors active:scale-95 border border-slate-200">
                        <i :class="mobileMenuOpen ? 'fa-solid fa-xmark text-[--brand-red] text-xl' : 'fa-solid fa-bars-staggered text-lg'"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer (Full screen height offset for mobile scroll) -->
            <div x-show="mobileMenuOpen" x-cloak 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="absolute top-full left-0 w-full bg-white/95 backdrop-blur-3xl border-b border-slate-100 shadow-2xl lg:hidden h-[calc(100vh-64px)] overflow-y-auto pb-24">
                <div class="flex flex-col px-4 sm:px-6 py-6 space-y-5">
                    
                    <div class="space-y-2">
                        <a href="<?= base_url('index.php?route=pages/landing#programs') ?>" @click="mobileMenuOpen = false" class="flex justify-between items-center text-base sm:text-lg font-black uppercase tracking-widest text-slate-900 border border-slate-100 bg-white p-4 rounded-2xl hover:border-[--brand-gold] transition active:scale-[0.98]">
                            <span class="flex items-center gap-3"><i class="fa-solid fa-graduation-cap text-[--brand-red]"></i> Programs</span> <i class="fa-solid fa-chevron-right text-slate-300 text-sm"></i>
                        </a>
                        <a href="<?= base_url('index.php?route=pages/landing#branches') ?>" @click="mobileMenuOpen = false" class="flex justify-between items-center text-base sm:text-lg font-black uppercase tracking-widest text-slate-900 border border-slate-100 bg-white p-4 rounded-2xl hover:border-[--brand-gold] transition active:scale-[0.98]">
                            <span class="flex items-center gap-3"><i class="fa-solid fa-map-location-dot text-[--brand-red]"></i> Network</span> <i class="fa-solid fa-chevron-right text-slate-300 text-sm"></i>
                        </a>
                        <a href="<?= route('pages/schools') ?>" @click="mobileMenuOpen = false" class="flex justify-between items-center text-base sm:text-lg font-black uppercase tracking-widest text-slate-900 border border-slate-100 bg-white p-4 rounded-2xl hover:border-[--brand-gold] transition active:scale-[0.98]">
                            <span class="flex items-center gap-3"><i class="fa-solid fa-database text-[--brand-red]"></i> Pacific DB</span> <i class="fa-solid fa-chevron-right text-slate-300 text-sm"></i>
                        </a>
                    </div>
                    
                    <!-- Mobile Language Switcher (Prominent for 425px) -->
                    <div class="pt-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-2">Select Language</p>
                        <div class="grid grid-cols-3 gap-2 sm:gap-3">
                            <button onclick="changeLanguage('en'); mobileMenuOpen=false;" class="flex flex-col items-center justify-center gap-2 bg-slate-50 border border-slate-200 p-3 sm:p-4 rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:border-[--brand-gold] hover:bg-white transition shadow-sm active:scale-95">
                                <span class="text-xl sm:text-2xl drop-shadow-sm">🇬🇧</span> EN
                            </button>
                            <button onclick="changeLanguage('ja'); mobileMenuOpen=false;" class="flex flex-col items-center justify-center gap-2 bg-slate-50 border border-slate-200 p-3 sm:p-4 rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:border-[--brand-gold] hover:bg-white transition shadow-sm active:scale-95">
                                <span class="text-xl sm:text-2xl drop-shadow-sm">🇯🇵</span> JP
                            </button>
                            <button onclick="changeLanguage('my'); mobileMenuOpen=false;" class="flex flex-col items-center justify-center gap-2 bg-slate-50 border border-slate-200 p-3 sm:p-4 rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:border-[--brand-gold] hover:bg-white transition font-mm shadow-sm active:scale-95">
                                <span class="text-xl sm:text-2xl drop-shadow-sm">🇲🇲</span> MM
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Login -->
                    <div class="pt-4 border-t border-slate-100 mt-4">
                        <a href="<?= $dashboardLink ?>" class="bg-slate-900 text-white w-full py-4 sm:py-5 rounded-2xl text-sm font-black uppercase shadow-lg flex items-center justify-center gap-3 active:scale-[0.98] transition-transform">
                            <?php if($isLoggedIn): ?>
                                <i class="fa-solid fa-gauge-high text-[--brand-gold]"></i> Enter Console
                            <?php else: ?>
                                <i class="fa-solid fa-lock text-[--brand-gold]"></i> Access Secure Portal
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <!-- Mobile Contact Info (Helpful for 425px bottom screen) -->
                    <div class="pt-4 text-center md:hidden">
                        <a href="mailto:<?= h(ORG_EMAIL) ?>" class="text-[10px] font-bold text-slate-400 hover:text-[--brand-red] transition"><i class="fa-solid fa-envelope mr-1"></i> <?= h(ORG_EMAIL) ?></a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Spacer to offset the fixed header dynamically based on screen size -->
    <div class="h-[64px] sm:h-[72px] md:h-[104px]"></div>

    <!-- Custom Google Translate Logic - FIXED FOR BULLETPROOF SWITCHING -->
    <script type="text/javascript">
        // Initialize Google Translate
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,ja,my', 
                autoDisplay: false
            }, 'google_translate_element');
        }

        // Custom JS to trigger Google Translate via native events and cookies
        function changeLanguage(langCode) {
            // 1. Always set the cookie robustly for both the root path and domain
            document.cookie = "googtrans=/en/" + langCode + "; path=/;";
            document.cookie = "googtrans=/en/" + langCode + "; path=/; domain=" + window.location.hostname;
            
            // 2. Try to dispatch the event on the hidden select field created by Google Translate
            var selectField = document.querySelector('select.goog-te-combo');
            const langMap = { 'en': '🇬🇧 EN', 'ja': '🇯🇵 JP', 'my': '🇲🇲 MM' };
            
            if (selectField) {
                selectField.value = langCode;
                // Dispatch event with bubbles true so Google's listener catches it perfectly
                selectField.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Update UI Display safely
                const displayElem = document.getElementById('current-lang-display');
                if(displayElem) displayElem.innerText = langMap[langCode] || langCode.toUpperCase();
            } else {
                // 3. Fallback: If Google Translate hasn't injected the box yet, reload the page to force the cookie check
                window.location.reload();
            }
        }
        
        // Auto-update UI based on current cookie on initial load
        window.addEventListener('DOMContentLoaded', (event) => {
            const match = document.cookie.match(/googtrans=\/en\/([a-z]{2})/);
            if(match && match[1]) {
                const langMap = { 'en': '🇬🇧 EN', 'ja': '🇯🇵 JP', 'my': '🇲🇲 MM' };
                const displayElem = document.getElementById('current-lang-display');
                if (displayElem && langMap[match[1]]) {
                    displayElem.innerText = langMap[match[1]];
                }
            }
        });
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>