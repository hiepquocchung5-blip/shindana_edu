<!-- Footer -->
    <footer class="bg-slate-900 text-white py-12 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-8 mb-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center font-black text-slate-900 text-xs">SD</div>
                    <span class="font-black text-lg uppercase">Shinedana.com</span>
                </div>
                <p class="text-slate-400 text-sm max-w-sm leading-relaxed">
                    Bridging the gap between Myanmar potential and Japanese opportunity. Five unified Academic Centers in Yangon providing world-class language education.
                </p>
            </div>
            <div>
                <h4 class="text-xs font-black uppercase text-[#D4AF37] tracking-widest mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="<?= base_url('#programs') ?>" class="hover:text-white transition">Academic Programs</a></li>
                    <li><a href="<?= base_url('#finder') ?>" class="hover:text-white transition">Find Schools</a></li>
                    <li><a href="<?= auth_url('login.php') ?>" class="hover:text-white transition">Agent Portal</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-black uppercase text-[#D4AF37] tracking-widest mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><i class="fa-solid fa-phone mr-2 text-slate-600"></i> +95 9 123 456 789</li>
                    <li><i class="fa-solid fa-envelope mr-2 text-slate-600"></i> info@Shinedana.com.mm</li>
                    <li><i class="fa-solid fa-location-dot mr-2 text-slate-600"></i> Kamayut HQ, Yangon</li>
                </ul>
            </div>
        </div>
        <div class="text-center pt-8 border-t border-slate-800 text-slate-500 text-xs font-bold uppercase tracking-widest">
            &copy; <?= date('Y') ?> Shinedana Global Education Co., Ltd. All Rights Reserved.
        </div>
    </footer>

    <!-- Main JS -->
    <script src="<?= asset_url('js/main.js') ?>"></script>
</body>
</html>