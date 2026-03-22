<?php
// pages/about.php
// Native Web Version of the Company Profile

require_once '../config/functions.php';
require_once '../includes/header.php';
?>

<!-- Hero Section (Immersive Dark Tech / Circuit UI) -->
<header class="bg-slate-900 text-white pt-24 md:pt-32 pb-20 md:pb-28 px-4 sm:px-6 relative overflow-hidden border-b-4 border-[--brand-gold]">
    <!-- Abstract Brand Glows -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(var(--brand-gold) 1px, transparent 1px); background-size: 30px 30px;"></div>
    <div class="absolute top-[-20%] right-[-10%] w-[300px] h-[300px] md:w-[500px] md:h-[500px] bg-[--brand-red] rounded-full blur-[100px] md:blur-[150px] opacity-20 pointer-events-none mix-blend-screen"></div>
    <div class="absolute bottom-[-20%] left-[-10%] w-[250px] h-[250px] md:w-[400px] md:h-[400px] bg-[--brand-gold] rounded-full blur-[90px] md:blur-[120px] opacity-15 pointer-events-none mix-blend-screen"></div>

    <div class="max-w-[1200px] mx-auto relative z-10 animate-in fade-in zoom-in duration-700 flex flex-col md:flex-row gap-8 items-center justify-between text-center md:text-left">
        <div class="flex-1">
            <span class="inline-flex items-center gap-2 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest mb-4 md:mb-6 text-[--brand-gold] shadow-lg backdrop-blur-sm">
                <i class="fa-solid fa-building"></i> Corporate Identity
            </span>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 md:mb-6 leading-tight tracking-tighter">
                株式会社シャインダナー<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-300 to-[--brand-red]"><?= h(ORG_NAME ?? 'Shinedana Co., Ltd.') ?></span>
            </h1>
            <p class="text-slate-400 text-sm md:text-base lg:text-lg max-w-2xl leading-relaxed mx-auto md:mx-0">
                Officially licensed by the Ministry of Labor – Department of Labor, Shine Da Na Overseas Employment Agency and Foreign Language Training Centre provides professional and reliable services for overseas employment and language education.
            </p>
        </div>
        <div class="hidden md:flex w-32 h-32 rounded-full bg-[--brand-gold] items-center justify-center shadow-[0_0_50px_rgba(229,184,34,0.4)] border-4 border-slate-900 overflow-hidden shrink-0">
             <img src="<?= asset_url('images/shine_logo.png') ?>" alt="SHN Logo" class="w-20 h-20 object-contain" onerror="this.style.display='none'">
        </div>
    </div>
</header>

<!-- Main Profile Content -->
<main class="bg-slate-50 relative z-20">

    <!-- 1. Who We Are & Introduction -->
    <section class="py-16 md:py-24 px-4 sm:px-6 max-w-[1200px] mx-auto relative">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            
            <!-- Left: Text Content -->
            <div class="space-y-6">
                <span class="text-[10px] font-black uppercase text-[--brand-red] tracking-[0.2em] flex items-center gap-2">
                    <div class="w-8 h-px bg-[--brand-red]"></div> Who We Are
                </span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                    Connecting Myanmar Workers with <span class="text-transparent bg-clip-text bg-gradient-to-r from-[--brand-gold] to-[--brand-red]">Trusted Global Employers.</span>
                </h2>
                <div class="prose prose-sm md:prose-base prose-slate text-slate-600 font-medium">
                    <p>
                        Shine Da Na Overseas Employment Agency is an officially licensed agency authorized to send skilled workers overseas, particularly to Japan. Our agency is committed to providing reliable and legal overseas employment opportunities for Myanmar citizens.
                    </p>
                    <p>
                        We primarily focus on connecting Myanmar workers with direct employment opportunities in Japan, while also offering job placements in Singapore, Malaysia, UAE, Laos, and Thailand. Through strong partnerships and negotiations with overseas companies, we provide access to a wide range of career opportunities across multiple industries.
                    </p>
                </div>
            </div>

            <!-- Right: 2 Dummy Images Overlapping -->
            <div class="relative h-[400px] md:h-[500px] hidden sm:block">
                <!-- Image 1 -->
                <img src="https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=800&q=80" 
                     alt="Campus 1" 
                     class="absolute top-0 right-0 w-3/4 h-[250px] md:h-[350px] object-cover rounded-3xl shadow-2xl border-4 border-white z-10 hover:scale-105 transition-transform duration-500">
                <!-- Image 2 -->
                <img src="https://shinedana.com/assets/images/shine_about_2.png" 
                     alt="Campus 2" 
                     class="absolute bottom-0 left-0 w-2/3 h-[200px] md:h-[300px] object-cover rounded-3xl shadow-2xl border-4 border-white z-20 hover:scale-105 transition-transform duration-500">
                
                <!-- Decorative Accent -->
                <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-[--brand-gold] rounded-full blur-[40px] opacity-40 z-0"></div>
            </div>
            
            <!-- Mobile Fallback Image -->
            <div class="sm:hidden w-full">
                <img src="https://shinedana.com/assets/images/shine_about_2.png" 
                     alt="Campus" 
                     class="w-full h-[250px] object-cover rounded-3xl shadow-xl border-4 border-white">
            </div>
        </div>
    </section>

    <!-- 2. Industries & Placements (Dark Grid) -->
    <section class="bg-slate-900 text-white py-16 md:py-24 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNFMUI4MjIiIGZpbGwtb3BhY2l0eT0iMC4yIi8+PC9zdmc+')] opacity-20"></div>
        
        <div class="max-w-[1200px] mx-auto relative z-10">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-3xl md:text-5xl font-black uppercase italic tracking-tight mb-4">Industry <span class="text-[--brand-red]">Placements</span></h2>
                <p class="text-slate-400 font-medium max-w-2xl mx-auto">We collaborate with reputable companies across various sectors to recruit skilled workers who possess the required qualifications and valid certificates.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 md:gap-12">
                <!-- Japan Industries -->
                <div class="bg-white/5 border border-white/10 rounded-[32px] p-8 md:p-10 backdrop-blur-sm hover:border-[--brand-gold] transition-colors">
                    <div class="flex items-center gap-4 mb-6 border-b border-white/10 pb-4">
                        <div class="w-12 h-12 rounded-xl bg-[--brand-red] flex items-center justify-center text-white text-2xl shadow-lg"><i class="fa-solid fa-torii-gate"></i></div>
                        <h3 class="text-xl font-black uppercase tracking-widest text-[--brand-gold]">Japan Sectors</h3>
                    </div>
                    <ul class="grid sm:grid-cols-2 gap-y-3 gap-x-4 text-sm text-slate-300 font-medium">
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Care Worker (Nursing)</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Industrial Mfg.</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Electrical & Electronics</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Construction Industry</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Auto Repair & Maint.</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Aviation Industry</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Accommodation & Hosp.</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Agriculture</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Fishery & Aquaculture</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> F&B Manufacturing</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Food Service Industry</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-red] mt-1 text-[10px]"></i> Motor Vehicle Transport</li>
                    </ul>
                </div>

                <!-- Other Countries -->
                <div class="bg-white/5 border border-white/10 rounded-[32px] p-8 md:p-10 backdrop-blur-sm hover:border-[--brand-gold] transition-colors">
                    <div class="flex items-center gap-4 mb-6 border-b border-white/10 pb-4">
                        <div class="w-12 h-12 rounded-xl bg-[--brand-gold] flex items-center justify-center text-slate-900 text-2xl shadow-lg"><i class="fa-solid fa-earth-asia"></i></div>
                        <h3 class="text-xl font-black uppercase tracking-widest text-[--brand-gold]">Global Sectors</h3>
                    </div>
                    <p class="text-xs text-slate-400 mb-4 font-bold uppercase tracking-widest">Singapore • Malaysia • UAE • Laos • Thailand</p>
                    <ul class="grid sm:grid-cols-2 gap-y-3 gap-x-4 text-sm text-slate-300 font-medium">
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-gold] mt-1 text-[10px]"></i> Factory & Mfg.</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-gold] mt-1 text-[10px]"></i> Hospitality & Hotels</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-gold] mt-1 text-[10px]"></i> Retail and Sales</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-gold] mt-1 text-[10px]"></i> Logistics & Warehouse</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-gold] mt-1 text-[10px]"></i> Construction</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check text-[--brand-gold] mt-1 text-[10px]"></i> Service Industry</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-plus text-[--brand-gold] mt-1 text-[10px]"></i> Other skilled sectors</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Mission, Vision, Values -->
    <section class="py-16 md:py-24 px-4 sm:px-6 max-w-[1200px] mx-auto">
        <div class="grid md:grid-cols-3 gap-6 md:gap-8">
            <!-- Mission -->
            <div class="bg-white p-8 md:p-10 rounded-[32px] border border-slate-100 shadow-xl relative overflow-hidden group hover:-translate-y-2 transition-transform">
                <div class="absolute top-0 left-0 w-full h-2 bg-[--brand-red]"></div>
                <div class="w-14 h-14 bg-red-50 text-[--brand-red] rounded-2xl flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform"><i class="fa-solid fa-bullseye"></i></div>
                <h3 class="text-2xl font-black text-slate-900 mb-4">Our Mission</h3>
                <p class="text-sm text-slate-500 font-medium leading-relaxed">
                    To create safe, responsible, and legal overseas employment opportunities for Myanmar workers while helping international employers recruit skilled, reliable, and dedicated employees.
                </p>
            </div>
            <!-- Vision -->
            <div class="bg-white p-8 md:p-10 rounded-[32px] border border-slate-100 shadow-xl relative overflow-hidden group hover:-translate-y-2 transition-transform">
                <div class="absolute top-0 left-0 w-full h-2 bg-[--brand-gold]"></div>
                <div class="w-14 h-14 bg-yellow-50 text-[--brand-gold] rounded-2xl flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform"><i class="fa-solid fa-eye"></i></div>
                <h3 class="text-2xl font-black text-slate-900 mb-4">Our Vision</h3>
                <p class="text-sm text-slate-500 font-medium leading-relaxed">
                    To become one of Myanmar’s most trusted overseas employment agencies by empowering workers, supporting employers, and contributing to sustainable social and economic development.
                </p>
            </div>
            <!-- Values -->
            <div class="bg-white p-8 md:p-10 rounded-[32px] border border-slate-100 shadow-xl relative overflow-hidden group hover:-translate-y-2 transition-transform">
                <div class="absolute top-0 left-0 w-full h-2 bg-slate-900"></div>
                <div class="w-14 h-14 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform"><i class="fa-solid fa-gem"></i></div>
                <h3 class="text-2xl font-black text-slate-900 mb-4">Our Values</h3>
                <ul class="space-y-3 text-sm text-slate-500 font-medium">
                    <li><strong class="text-slate-900">Integrity</strong> – Fair, honest, and transparent procedures.</li>
                    <li><strong class="text-slate-900">Responsibility</strong> – Protecting the safety and rights of workers.</li>
                    <li><strong class="text-slate-900">Professionalism</strong> – High standards in recruitment and training.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 4. CEO Message (Director Photo 1x1) -->
    <section class="bg-slate-100 py-16 md:py-24 px-4 sm:px-6 border-y border-slate-200">
        <div class="max-w-[1000px] mx-auto bg-white rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row">
            
            <!-- Left: 1x1 Dummy Photo of Director -->
            <div class="md:w-2/5 relative h-[350px] md:h-auto shrink-0 bg-slate-200">
                <!-- Using a professional businessman unsplash image for the 1x1 dummy -->
                <img src="https://shinedana.com/assets/images/cmpf_dir_pf.png" 
                     alt="MR. SHINE WAI YAN ZAW" 
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6">
                    <h4 class="text-white font-black text-xl tracking-tight">Mr. Shine Wai Yan Zaw</h4>
                    <p class="text-[--brand-gold] text-[10px] font-black uppercase tracking-widest">Managing Director (CEO)</p>
                </div>
            </div>

            <!-- Right: Message -->
            <div class="md:w-3/5 p-8 md:p-12 flex flex-col justify-center relative">
                <i class="fa-solid fa-quote-right absolute top-8 right-8 text-6xl text-slate-100"></i>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-6 relative z-10">Message from the CEO</h2>
                <div class="prose prose-sm text-slate-600 font-medium leading-relaxed relative z-10 space-y-4">
                    <p>
                        Shine Da Na Overseas Employment Agency was established with the vision of creating the best overseas employment opportunities for Myanmar citizens, particularly for basic and skilled workers, while contributing to the improvement of their social and economic well-being.
                    </p>
                    <p>
                        Our goal is not only to assist employers and job seekers in finding suitable employment opportunities abroad, but also to ensure that Myanmar workers have access to safe, legal, and reliable overseas employment. We strive to create transparent and structured processes that protect the interests of both workers and employers.
                    </p>
                    <p>
                        We would like to sincerely express our gratitude to our valued partners, dedicated employees, and all job seekers who have placed their trust in Shine Da Na. Your support and cooperation are the foundation of our success.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Visa & Employment Services -->
    <section class="py-16 md:py-24 px-4 sm:px-6 max-w-[1200px] mx-auto">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-[10px] font-black uppercase text-[--brand-red] tracking-[0.2em] mb-2 block">Core Offerings</span>
            <h2 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">Visa & Employment <span class="text-transparent bg-clip-text bg-gradient-to-r from-[--brand-gold] to-[--brand-red]">Support</span></h2>
        </div>

        <div class="grid sm:grid-cols-2 gap-6 md:gap-8">
            <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-lg hover:border-[--brand-gold] transition-colors">
                <h3 class="text-xl font-black text-slate-900 mb-3"><i class="fa-solid fa-passport text-[--brand-red] mr-2"></i> Japan Training Visa Program</h3>
                <p class="text-sm text-slate-600 font-medium leading-relaxed mb-4">
                    Comprehensive services including Japanese language education (beginner to advanced) and technical skills training (caregiving, food mfg, hospital support, construction). We guide candidates through interview prep, documentation, orientation, and travel arrangements.
                </p>
            </div>

            <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-lg hover:border-[--brand-gold] transition-colors">
                <h3 class="text-xl font-black text-slate-900 mb-3"><i class="fa-solid fa-briefcase text-[--brand-gold] mr-2"></i> Work Visa Support</h3>
                <p class="text-sm text-slate-600 font-medium leading-relaxed mb-4">
                    Professional assistance for employment in Japan, Malaysia, Singapore, Thailand, UAE, and Qatar. Support includes documentation, visa application, job preparation, and interview coaching to maximize employment success.
                </p>
            </div>

            <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-lg hover:border-[--brand-gold] transition-colors">
                <h3 class="text-xl font-black text-slate-900 mb-3"><i class="fa-solid fa-star text-[--brand-red] mr-2"></i> Specified Skilled Worker (Tokutei Ginou)</h3>
                <p class="text-sm text-slate-600 font-medium leading-relaxed mb-4">
                    Full support for candidates applying to work in Japan for up to 5 years across 12 specialized industries. We provide language preparation, technical skill exam training, and application processing.
                </p>
            </div>

            <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-lg hover:border-[--brand-gold] transition-colors">
                <h3 class="text-xl font-black text-slate-900 mb-3"><i class="fa-solid fa-user-graduate text-[--brand-gold] mr-2"></i> Student Visa (Language School)</h3>
                <p class="text-sm text-slate-600 font-medium leading-relaxed mb-4">
                    Opportunities to improve Japanese proficiency before progressing to higher education or employment in Japan. We offer complete support including school selection, application processing, and document preparation.
                </p>
            </div>
        </div>
    </section>

    <!-- 6. Training & Development (Languages) -->
    <section class="bg-slate-900 text-white py-16 md:py-24 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-[--brand-gold] rounded-full blur-[150px] opacity-10"></div>

        <div class="max-w-[1200px] mx-auto relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6 border-b border-white/10 pb-8">
                <div>
                    <span class="text-[10px] font-black uppercase text-[--brand-gold] tracking-[0.2em] mb-2 block">Language Academy</span>
                    <h2 class="text-3xl md:text-5xl font-black text-white italic">TRAINING & <span class="text-[--brand-red]">DEVELOPMENT</span></h2>
                </div>
                <div class="md:text-right max-w-sm">
                    <p class="text-sm text-slate-400 font-medium">We operate our own training institute to prepare candidates for successful overseas careers with job-specific skills and soft skills.</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Japanese -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-[--brand-red] text-white flex items-center justify-center font-black mb-4">JP</div>
                    <h3 class="text-lg font-black mb-4">Japanese</h3>
                    <ul class="space-y-2 text-xs text-slate-300 font-medium">
                        <li>• N5, N4, N3 Classes</li>
                        <li>• Kaiwa & Interview Training</li>
                        <li>• Tokutei Building Cleaning</li>
                        <li>• Tokutei Kaigo Class</li>
                        <li>• Tokutei Agriculture</li>
                        <li>• Tokutei Food Service & Mfg.</li>
                        <li>• Tokutei Taxi/Truck, Hotel, Const.</li>
                    </ul>
                </div>

                <!-- Korean -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black mb-4">KR</div>
                    <h3 class="text-lg font-black mb-4">Korean</h3>
                    <ul class="space-y-2 text-xs text-slate-300 font-medium">
                        <li>• Beginner to Advanced (TOPIK)</li>
                        <li>• Speaking & Conversation</li>
                        <li>• Daily communication practice</li>
                        <li>• Reading & Writing (Hangul)</li>
                        <li>• Korean Culture & Etiquette</li>
                        <li>• Workplace communication prep</li>
                    </ul>
                </div>

                <!-- English -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-slate-600 text-white flex items-center justify-center font-black mb-4">EN</div>
                    <h3 class="text-lg font-black mb-4">English</h3>
                    <ul class="space-y-2 text-xs text-slate-300 font-medium">
                        <li>• General English (Beg. to Adv.)</li>
                        <li>• Speaking & Pronunciation</li>
                        <li>• Listening Comprehension</li>
                        <li>• Reading & Writing skills</li>
                    </ul>
                </div>

                <!-- Chinese -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-[--brand-gold] text-slate-900 flex items-center justify-center font-black mb-4">CN</div>
                    <h3 class="text-lg font-black mb-4">Chinese</h3>
                    <ul class="space-y-2 text-xs text-slate-300 font-medium">
                        <li>• Beginner to Advanced Levels</li>
                        <li>• HSK Preparation included</li>
                        <li>• Reading & Writing</li>
                        <li>• Simplified Chinese characters</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>