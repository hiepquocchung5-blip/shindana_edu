<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
set_security_headers();
requireAgent();
$agent_id = $_SESSION['user_id'];

// 2. Fetch Japan Schools for Dropdown
$stmt = $pdo->query("SELECT id, school_name, region FROM japan_schools ORDER BY school_name ASC");
$schools = $stmt->fetchAll();

// 3. Handle Form Submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    verify_csrf($csrf_token);

    $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
    $nric = trim(filter_input(INPUT_POST, 'nric', FILTER_SANITIZE_STRING));
    $school_id = filter_input(INPUT_POST, 'school_id', FILTER_VALIDATE_INT);
    
    // File Upload Logic
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $allowed_mimes = ['application/pdf'];
        $allowed_exts = ['pdf'];
        
        $filename = $_FILES['document']['name'];
        $filesize = $_FILES['document']['size'];
        $tmp_name = $_FILES['document']['tmp_name'];
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Strict MIME check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);

        if (!in_array($ext, $allowed_exts) || !in_array($mime, $allowed_mimes)) {
            $error = "Security Guard: Only valid PDF files are allowed.";
            log_activity($pdo, 'UPLOAD_DENIED', "Agent {$_SESSION['agent_code']} attempted to upload invalid file type: $mime");
        } elseif ($filesize > 5000000) { 
            $error = "File size exceeds the 5MB maximum limit.";
        } else {
            // Secure hashing for filename to prevent overwrites & traversal
            $new_filename = bin2hex(random_bytes(8)) . '_DOC_' . time() . ".pdf";
            $upload_dir = '../uploads/documents/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                try {
                    $sql = "INSERT INTO students (agent_id, full_name, nric_passport, target_school_id, document_path) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$agent_id, $full_name, $nric, $school_id, $new_filename]);
                    
                    // Log action
                    log_activity($pdo, 'STUDENT_REGISTER', "Agent {$_SESSION['agent_code']} submitted app for: $full_name");
                    
                    // Redirect directly to the dashboard route
                    redirect('agent/index&msg=' . urlencode("Application for {$full_name} successfully submitted."));
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                    // Cleanup uploaded file on DB failure
                    if(file_exists($upload_dir . $new_filename)) unlink($upload_dir . $new_filename);
                }
            } else {
                $error = "System Error: Failed to secure the document on the server.";
            }
        }
    } else {
        $error = "A valid PDF Document Bundle is required for registration.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student | <?= h(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        .hero-pattern { background-image: radial-gradient(#D4AF37 0.5px, transparent 0.5px); background-size: 16px 16px; }
    </style>
</head>
<body class="text-slate-900 min-h-screen flex flex-col">

    <nav class="bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-4 sticky top-0 z-40 shadow-sm">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= agent_url('index') ?>" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="leading-none">
                    <span class="block font-black uppercase text-slate-900 tracking-tight">Student Registration</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Secure Intake Form</span>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100">
                <i class="fa-solid fa-shield-check text-green-500 text-xs"></i>
                <span class="text-[10px] font-black uppercase text-slate-500 tracking-widest">Encrypted</span>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-6 md:p-12 w-full flex-1">
        <div class="bg-white rounded-[40px] shadow-2xl overflow-hidden border border-slate-100 relative">
            <!-- Header Banner -->
            <div class="bg-slate-900 p-8 md:p-12 text-white relative overflow-hidden hero-pattern">
                <div class="absolute inset-0 bg-slate-900/90"></div>
                <div class="relative z-10">
                    <h1 class="text-3xl md:text-4xl font-black italic uppercase tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 mb-2">New Application</h1>
                    <p class="text-[#D4AF37] text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Submit Candidate Profile
                    </p>
                </div>
            </div>

            <!-- Form Area -->
            <div class="p-8 md:p-12 bg-white">
                <?php if($error): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl text-sm font-bold mb-8 flex items-center gap-3 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i> <?= h($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-8" x-data="{ fileName: null, fileSize: null, dragging: false }">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Identity Section -->
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100 pb-2">Candidate Identity</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 ml-1">Full Legal Name</label>
                                <input type="text" name="full_name" required placeholder="e.g. Maung Maung" class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 ml-1">NRIC / Passport No.</label>
                                <input type="text" name="nric" required placeholder="e.g. 12/KMY(N)000000" class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- Institutional Routing -->
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100 pb-2">Academic Routing</h3>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 ml-1">Target Institution</label>
                            <div class="relative">
                                <select name="school_id" class="w-full bg-slate-50 border border-slate-200 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:border-transparent ring-[#D4AF37] transition-all appearance-none cursor-pointer">
                                    <option value="" disabled selected>Select an institution from the Pacific DB...</option>
                                    <?php foreach($schools as $school): ?>
                                        <option value="<?= $school['id'] ?>">
                                            <?= htmlspecialchars($school['school_name']) ?> (<?= htmlspecialchars($school['region']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Document Vault -->
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100 pb-2">Document Vault</h3>
                        
                        <div class="relative group"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0].name; fileSize = ($refs.fileInput.files[0].size / 1024 / 1024).toFixed(2) + ' MB'">
                             
                            <input type="file" name="document" x-ref="fileInput" accept=".pdf" required 
                                   @change="fileName = $event.target.files[0].name; fileSize = ($event.target.files[0].size / 1024 / 1024).toFixed(2) + ' MB'"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                   
                            <div :class="dragging ? 'bg-yellow-50 border-[#D4AF37] scale-[1.02]' : 'bg-slate-50 border-slate-200 hover:border-slate-300'" 
                                 class="border-2 border-dashed p-10 rounded-[32px] flex flex-col items-center justify-center text-center transition-all duration-300">
                                
                                <!-- Default View -->
                                <div x-show="!fileName" class="flex flex-col items-center pointer-events-none">
                                    <div class="w-16 h-16 bg-white shadow-sm border border-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400 transition-colors group-hover:text-[#D4AF37]">
                                        <i class="fa-solid fa-file-arrow-up text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-700 uppercase tracking-wide">Drop PDF Bundle Here</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-2 tracking-widest uppercase">Click to Browse • Max Size 5MB</p>
                                </div>

                                <!-- File Selected View -->
                                <div x-show="fileName" x-cloak class="flex flex-col items-center pointer-events-none">
                                    <div class="w-16 h-16 bg-green-100 border border-green-200 rounded-full flex items-center justify-center mb-4 text-green-600 shadow-inner">
                                        <i class="fa-solid fa-file-pdf text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-900" x-text="fileName"></p>
                                    <p class="text-xs font-bold text-green-600 mt-2 bg-green-50 px-3 py-1 rounded-full border border-green-100" x-text="fileSize"></p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Submit Action -->
                    <div class="pt-6">
                        <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 flex items-center justify-center gap-3">
                            <i class="fa-solid fa-paper-plane"></i> Submit Application to HQ
                        </button>
                        <p class="text-center text-[10px] text-slate-400 font-bold mt-4 uppercase tracking-widest">
                            <i class="fa-solid fa-lock mr-1"></i> Data encrypted securely in transit
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>