<?php
// pages/about.php
// Main Entry Point for "About Us / Company Profile"

require_once '../config/functions.php';
require_once '../includes/header.php';
?>

<!-- Hero Section (Immersive Dark Tech / Circuit UI) -->
<header class="bg-slate-900 text-white pt-24 md:pt-32 pb-20 md:pb-28 px-4 sm:px-6 relative overflow-hidden border-b-4 border-[--brand-gold]">
    <!-- Abstract Brand Glows -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(var(--brand-gold) 1px, transparent 1px); background-size: 30px 30px;"></div>
    <div class="absolute top-[-20%] right-[-10%] w-[300px] h-[300px] md:w-[500px] md:h-[500px] bg-[--brand-red] rounded-full blur-[100px] md:blur-[150px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-20%] left-[-10%] w-[250px] h-[250px] md:w-[400px] md:h-[400px] bg-[--brand-gold] rounded-full blur-[90px] md:blur-[120px] opacity-15 pointer-events-none mix-blend-screen"></div>

    <div class="max-w-[1200px] mx-auto relative z-10 animate-in fade-in zoom-in duration-700 flex flex-col md:flex-row gap-8 items-center justify-between">
        <div class="text-center md:text-left">
            <span class="inline-flex items-center gap-2 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest mb-4 md:mb-6 text-[--brand-gold] shadow-lg backdrop-blur-sm">
                <i class="fa-solid fa-building"></i> Corporate Identity
            </span>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 md:mb-6 leading-tight tracking-tighter">
                株式会社シャインダナー<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-300 to-[--brand-red]">Shinedana Co., Ltd.</span>
            </h1>
            <p class="text-slate-400 text-sm md:text-base lg:text-lg max-w-2xl leading-relaxed">
                Empowering Myanmar professionals to excel globally through trusted overseas placement and world-class language education.
            </p>
        </div>
        <div class="hidden md:flex w-32 h-32 rounded-full bg-[--brand-gold] items-center justify-center shadow-[0_0_50px_rgba(229,184,34,0.4)] border-4 border-slate-900 overflow-hidden shrink-0">
             <img src="<?= asset_url('images/shine_logo.png') ?>" alt="SHN Logo" class="w-20 h-20 object-contain" onerror="this.style.display='none'">
        </div>
    </div>
</header>

<!-- Main Profile Viewer -->
<main class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12 md:py-16 -mt-8 relative z-20">
    
    <div class="grid lg:grid-cols-12 gap-8 md:gap-12">
        
        <!-- Left Column: Core Info & Stats -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white p-8 rounded-[32px] shadow-xl border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-[--brand-gold] rounded-full blur-[60px] opacity-10 pointer-events-none group-hover:opacity-20 transition-opacity"></div>
                
                <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-6 border-b border-slate-100 pb-4">Our Operations</h3>
                
                <div class="space-y-6 relative z-10">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1"><i class="fa-solid fa-briefcase text-[--brand-red] mr-1"></i> Employment Networks</div>
                        <div class="font-black text-slate-900 text-sm leading-snug">
                            Japan, UAE, Singapore, Malaysia, Laos, Thailand
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1"><i class="fa-solid fa-language text-[--brand-gold] mr-1"></i> Language Academy</div>
                        <div class="font-bold text-slate-700 text-sm bg-slate-50 p-3 rounded-xl border border-slate-100 flex flex-wrap gap-2 mt-2">
                            <span class="bg-white px-2 py-1 rounded text-[10px] font-black uppercase text-slate-600 border shadow-sm">Japanese</span>
                            <span class="bg-white px-2 py-1 rounded text-[10px] font-black uppercase text-slate-600 border shadow-sm">Chinese</span>
                            <span class="bg-white px-2 py-1 rounded text-[10px] font-black uppercase text-slate-600 border shadow-sm">English</span>
                            <span class="bg-white px-2 py-1 rounded text-[10px] font-black uppercase text-slate-600 border shadow-sm">Korean</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1"><i class="fa-solid fa-stamp text-[--brand-red] mr-1"></i> Accreditation</div>
                        <div class="text-sm font-black text-slate-600 leading-relaxed bg-red-50 text-red-700 p-4 rounded-xl border border-red-100">
                            Officially Licensed by the Ministry of Labor - Department of Labor.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Button -->
            <a href="<?= asset_url('docs/COMPANY_PROFILE.pdf') ?>" download class="flex items-center justify-center gap-3 w-full bg-slate-900 text-white py-4 md:py-5 rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest hover:bg-[--brand-gold] hover:text-slate-900 transition-all shadow-xl hover:shadow-[0_0_30px_rgba(229,184,34,0.3)] transform hover:-translate-y-1 active:scale-[0.98]">
                <i class="fa-solid fa-cloud-arrow-down text-lg"></i> Download PDF Brochure
            </a>
        </div>

        <!-- Right Column: Interactive PDF Viewer / Embed -->
        <div class="lg:col-span-8 h-full min-h-[600px] flex flex-col">
            <div class="bg-white p-2 md:p-4 rounded-[32px] shadow-xl border border-slate-100 flex-grow flex flex-col relative overflow-hidden">
                <!-- Mac-like Window Header -->
                <div class="bg-slate-50 border-b border-slate-100 px-4 py-3 rounded-t-[24px] flex items-center gap-2 mb-2 shrink-0">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="ml-4 text-[9px] font-black uppercase text-slate-400 tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-red-500"></i> SHINEDANA_COMPANY_PROFILE.pdf
                    </div>
                </div>

                <!-- PDF iFrame Viewer -->
                <div class="flex-grow bg-slate-200 rounded-b-[24px] overflow-hidden relative">
                    
                    <!-- Fallback / Loading State if PDF is missing or slow -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 z-0 px-6 text-center">
                        <i class="fa-solid fa-file-pdf text-6xl text-slate-300 mb-4"></i>
                        <h4 class="font-black text-slate-700 uppercase tracking-widest mb-2">Document Viewer</h4>
                        <p class="text-xs font-bold">If the document does not load, please ensure the PDF is uploaded to:<br> <code class="bg-slate-300 text-slate-800 px-2 py-1 rounded mt-2 inline-block">assets/docs/COMPANY_PROFILE.pdf</code></p>
                        <a href="<?= asset_url('docs/COMPANY_PROFILE.pdf') ?>" target="_blank" class="mt-6 bg-slate-900 text-white px-6 py-2.5 rounded-full text-[10px] font-black uppercase hover:bg-[--brand-gold] hover:text-slate-900 transition">Try Direct Link</a>
                    </div>

                    <!-- The Actual Embed (covers fallback if successful) -->
                    <iframe 
                        src="<?= asset_url('docs/COMPANY_PROFILE.pdf') ?>#view=FitH&toolbar=0" 
                        class="relative z-10 w-full h-full min-h-[600px] border-none"
                        title="Company Profile PDF">
                    </iframe>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>