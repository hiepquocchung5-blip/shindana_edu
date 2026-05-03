<?php
// admin/testimonials.php
// CMS Module for managing Landing Page Success Stories

require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Handle Add Testimonial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_testimonial'])) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $placement = trim(filter_input(INPUT_POST, 'placement', FILTER_SANITIZE_STRING));
    $quote = trim(filter_input(INPUT_POST, 'quote', FILTER_SANITIZE_STRING));
    
    // Image Upload Logic
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = '../assets/images/testimonials/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            // Secure random filename
            $new_filename = 'alumni_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO testimonials (name, placement, quote, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $placement, $quote, $new_filename]);
                    redirect('admin/testimonials&msg=' . urlencode("Success story added to the landing page!"));
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to save the uploaded image.";
            }
        } else {
            $error = "Invalid image format. Only JPG, PNG, or WEBP are allowed.";
        }
    } else {
        $error = "A profile image is required.";
    }
}

// 3. Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM testimonials WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $testimony = $stmt->fetch();
    
    if ($testimony) {
        $file_path = '../assets/images/testimonials/' . $testimony['image_path'];
        if (file_exists($file_path)) unlink($file_path); // Clean up server storage
        
        $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$_GET['id']]);
        redirect('admin/testimonials&msg=' . urlencode("Testimonial permanently removed."));
    }
}

// 4. Fetch Existing Testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success Stories | Sheindana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } [x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 text-slate-900" x-data="{ showModal: false, previewUrl: null }">

    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('index') ?>" class="text-slate-400 hover:text-white transition"><i class="fa-solid fa-arrow-left"></i></a>
                <div class="font-black text-xl uppercase tracking-tighter">Success <span class="text-[#D4AF37]">Stories</span></div>
            </div>
            <button @click="showModal = true" class="bg-[#D4AF37] text-slate-900 px-6 py-2 rounded-xl text-xs font-black uppercase hover:bg-white transition flex items-center gap-2 shadow-lg">
                <i class="fa-solid fa-plus"></i> Add Testimonial
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Active Testimonials Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($testimonials as $item): ?>
            <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 relative group hover:shadow-lg transition-shadow">
                
                <!-- Delete Button -->
                <a href="<?= admin_url('testimonials') ?>&action=delete&id=<?= $item['id'] ?>" onclick="return confirm('Remove this testimonial from the public landing page?')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white shadow-sm">
                    <i class="fa-solid fa-trash text-xs"></i>
                </a>

                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-full bg-slate-200 bg-cover bg-center shadow-inner border-2 border-white" style="background-image: url('<?= asset_url('images/testimonials/' . h($item['image_path'])) ?>');"></div>
                    <div>
                        <h4 class="font-black text-slate-900 leading-tight"><?= h($item['name']) ?></h4>
                        <p class="text-[9px] font-bold text-[#D4AF37] uppercase tracking-widest mt-0.5"><?= h($item['placement']) ?></p>
                    </div>
                </div>
                <p class="text-sm text-slate-500 italic leading-relaxed line-clamp-4">"<?= h($item['quote']) ?>"</p>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($testimonials)): ?>
                <div class="col-span-full text-center py-16 bg-white rounded-[32px] border border-slate-100">
                    <i class="fa-regular fa-comment-dots text-5xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">No testimonials added yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- CMS Add Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-slate-900/60" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden relative z-10 max-h-[90vh] overflow-y-auto" @click.away="showModal = false">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white sticky top-0 z-20">
                <h3 class="font-black italic uppercase text-xl">New Story</h3>
                <button @click="showModal = false" class="hover:text-[#D4AF37] focus:outline-none"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                <!-- Image Upload with Live Preview -->
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Profile Photo</label>
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-full border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!previewUrl">
                                <i class="fa-regular fa-image text-slate-400 text-xl"></i>
                            </template>
                        </div>
                        <input type="file" name="image" accept="image/*" required @change="previewUrl = URL.createObjectURL($event.target.files[0])" class="text-sm font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Student Name</label>
                        <input type="text" name="name" required placeholder="e.g. Aung Kyaw" class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Placement / Job</label>
                        <input type="text" name="placement" required placeholder="Osaka IT College" class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Quote / Review</label>
                    <textarea name="quote" rows="4" required placeholder="Write the success story here..." class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all"></textarea>
                </div>

                <button type="submit" name="add_testimonial" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition-all shadow-xl hover:-translate-y-0.5">
                    Publish to Website
                </button>
            </form>
        </div>
    </div>

</body>
</html>