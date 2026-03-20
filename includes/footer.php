<!-- Footer -->
<footer class="bg-slate-900 text-white py-16 border-t border-slate-800 mt-auto relative overflow-hidden">
    
    <!-- Abstract Background Glow -->
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-[--brand-gold] rounded-full blur-[150px] opacity-10 pointer-events-none"></div>
    <div class="absolute top-0 left-0 w-64 h-64 bg-[--brand-red] rounded-full blur-[120px] opacity-10 pointer-events-none"></div>

    <div class="max-w-[1600px] mx-auto px-6 lg:px-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12 relative z-10">
        
        <!-- Brand Column -->
        <div class="col-span-1 lg:col-span-2">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center font-black text-[--brand-gold] text-sm border border-white/20 shadow-lg">SD</div>
                <span class="font-black text-2xl uppercase tracking-tight"><?= h(APP_NAME ?? 'Shinedana.com') ?></span>
            </div>
            <p class="text-slate-400 text-sm max-w-md leading-relaxed font-medium mb-6">
                Bridging the gap between Myanmar potential and Japanese opportunity. Five unified Academic Centers providing world-class language education and direct overseas placement.
            </p>
            <div class="flex gap-4">
                <a href="#" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:bg-[--brand-gold] hover:text-slate-900 transition-all transform hover:-translate-y-1"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:bg-[--brand-gold] hover:text-slate-900 transition-all transform hover:-translate-y-1"><i class="fa-brands fa-viber"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div>
            <h4 class="text-xs font-black uppercase text-[--brand-gold] tracking-widest mb-6 flex items-center gap-2">
                <div class="w-4 h-px bg-[--brand-gold]"></div> Quick Links
            </h4>
            <ul class="space-y-4 text-sm text-slate-400 font-bold">
                <li><a href="<?= route('pages/about') ?>" class="hover:text-white hover:translate-x-2 inline-block transition-transform"><i class="fa-solid fa-chevron-right text-[10px] text-[--brand-red] mr-2"></i> Company Profile</a></li>
                <li><a href="<?= base_url('index.php?route=pages/landing#programs') ?>" class="hover:text-white hover:translate-x-2 inline-block transition-transform"><i class="fa-solid fa-chevron-right text-[10px] text-[--brand-red] mr-2"></i> Academic Programs</a></li>
                <li><a href="<?= route('pages/schools') ?>" class="hover:text-white hover:translate-x-2 inline-block transition-transform"><i class="fa-solid fa-chevron-right text-[10px] text-[--brand-red] mr-2"></i> Japan Partners</a></li>
                <li><a href="<?= auth_url('login') ?>" class="hover:text-white hover:translate-x-2 inline-block transition-transform"><i class="fa-solid fa-chevron-right text-[10px] text-[--brand-red] mr-2"></i> Agent Portal</a></li>
            </ul>
        </div>

        <!-- Dynamic Contact Info -->
        <div>
            <h4 class="text-xs font-black uppercase text-[--brand-gold] tracking-widest mb-6 flex items-center gap-2">
                <div class="w-4 h-px bg-[--brand-gold]"></div> Contact HQ
            </h4>
            <ul class="space-y-5 text-sm text-slate-400 font-medium">
                <li class="flex items-start gap-3 group">
                    <i class="fa-solid fa-phone mt-1 text-[--brand-red] group-hover:scale-110 transition-transform"></i> 
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', ORG_PHONE ?? '') ?>" class="hover:text-white transition"><?= h(ORG_PHONE ?? '+81 90 3100 5888') ?></a>
                </li>
                <li class="flex items-start gap-3 group">
                    <i class="fa-solid fa-envelope mt-1 text-[--brand-red] group-hover:scale-110 transition-transform"></i> 
                    <a href="mailto:<?= h(ORG_EMAIL ?? 'info@shinedana.com') ?>" class="hover:text-white transition"><?= h(ORG_EMAIL ?? 'info@shinedana.com') ?></a>
                </li>
                <li class="flex items-start gap-3 group">
                    <i class="fa-solid fa-location-dot mt-1 text-[--brand-red] group-hover:scale-110 transition-transform"></i> 
                    <span class="leading-relaxed"><?= h(ORG_ADDRESS ?? '114-0013 Tokyo To Kita Ku Higashi Tabata 1-12-18 Tabata Manshon 1006') ?></span>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="max-w-[1600px] mx-auto px-6 lg:px-12 text-center pt-8 border-t border-white/10 text-slate-500 text-[10px] font-black uppercase tracking-widest relative z-10">
        &copy; <?= date('Y') ?> <?= h(ORG_NAME ?? 'Shinedana Global Education Co., Ltd.') ?> All Rights Reserved.
    </div>
</footer>

<!-- Main JS -->
<script src="<?= asset_url('js/main.js') ?>"></script>
</body>
</html>