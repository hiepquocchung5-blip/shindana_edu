<?php require_once __DIR__ . '/../config/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheindana.edu | Global Ecosystem</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Noto+Sans+Myanmar:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Noto Sans Myanmar', sans-serif; background: #f8fafc; }
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
        .border-gold { border-color: #D4AF37; }
        .hover-gold:hover { color: #D4AF37; }
    </style>
</head>
<body class="antialiased text-slate-900 flex flex-col min-h-screen">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            
            <!-- Logo -->
            <a href="<?= base_url() ?>" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center border-2 border-[#D4AF37] shadow-lg group-hover:scale-105 transition-transform">
                    <span class="text-white font-black text-xs">SD</span>
                </div>
                <div class="leading-tight">
                    <span class="block font-black uppercase text-slate-900 tracking-tight text-lg group-hover:text-[#D4AF37] transition-colors">Sheindana<span class="text-gold">.edu</span></span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Global Ecosystem</span>
                </div>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8">
                <a href="<?= base_url('#programs') ?>" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover-gold transition">Programs</a>
                <a href="<?= base_url('#branches') ?>" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover-gold transition">Network</a>
                <a href="<?= base_url('#finder') ?>" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover-gold transition">Pacific DB</a>
            </div>

            <!-- Utilities (Translate + Login) -->
            <div class="flex items-center gap-4">
                <!-- Google Translate Widget Container -->
                <div id="google_translate_element" class="hidden md:block"></div>

                <a href="<?= auth_url('login.php') ?>" class="bg-slate-900 text-white px-6 py-2.5 rounded-full text-xs font-black uppercase hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-xl flex items-center gap-2">
                    <i class="fa-solid fa-lock text-[10px]"></i> Portal
                </a>
            </div>
        </div>
    </nav>