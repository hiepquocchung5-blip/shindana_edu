<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAgent();
$agent_id = $_SESSION['user_id'];

// 2. Fetch Japan Schools for Dropdown
$stmt = $pdo->query("SELECT id, school_name FROM japan_schools ORDER BY school_name ASC");
$schools = $stmt->fetchAll();

// 3. Handle Form Submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $nric = trim($_POST['nric']);
    $school_id = $_POST['school_id'];
    
    // File Upload Logic
    if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
        $allowed = ['pdf'];
        $filename = $_FILES['document']['name'];
        $filetype = $_FILES['document']['type'];
        $filesize = $_FILES['document']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate PDF
        if (!in_array($ext, $allowed)) {
            $error = "Only PDF files are allowed.";
        } elseif ($filesize > 5000000) { // 5MB Limit
            $error = "File size exceeds 5MB limit.";
        } else {
            // Generate Secure Hash Filename
            $new_filename = uniqid('DOC-', true) . "." . $ext;
            $upload_dir = '../uploads/documents/';
            
            // Ensure directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_dir . $new_filename)) {
                // Insert into Database
                $sql = "INSERT INTO students (agent_id, full_name, nric_passport, target_school_id, document_path) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$agent_id, $full_name, $nric, $school_id, $new_filename])) {
                    header("Location: index.php?msg=Student Registered Successfully");
                    exit();
                } else {
                    $error = "Database error. Please try again.";
                }
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    } else {
        $error = "Please upload a student profile PDF.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student | Shinedana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">

    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-slate-400 hover:text-slate-900 transition">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <span class="font-bold uppercase tracking-tight ml-4">Student Registration</span>
            </div>
            <div class="w-8 h-8 bg-[#D4AF37] rounded-lg flex items-center justify-center font-black text-white text-xs">AG</div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto p-6 md:p-12">
        <div class="bg-white rounded-[40px] shadow-xl overflow-hidden border border-slate-100">
            <div class="bg-slate-900 p-8 md:p-12 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h1 class="text-3xl font-black italic uppercase">New Application</h1>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-2">Submit candidate for Japan review</p>
                </div>
                <!-- Decorative Element -->
                <div class="absolute -right-10 -bottom-10 text-[150px] text-white opacity-5 pointer-events-none">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
            </div>

            <div class="p-8 md:p-12">
                <?php if($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-8">
                    <!-- Personal Info -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Student Full Name</label>
                            <input type="text" name="full_name" required placeholder="e.g. Maung Maung" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">NRIC / Passport No.</label>
                            <input type="text" name="nric" required placeholder="e.g. 12/KMY(N)000000" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition">
                        </div>
                    </div>

                    <!-- School Selection -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Target Institution</label>
                        <select name="school_id" class="w-full bg-slate-50 p-4 rounded-2xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37] transition appearance-none cursor-pointer">
                            <?php foreach($schools as $school): ?>
                                <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['school_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Document Bundle (PDF Only)</label>
                        <div class="relative group">
                            <input type="file" name="document" accept=".pdf" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="border-2 border-dashed border-slate-200 p-10 rounded-[32px] flex flex-col items-center justify-center text-center transition group-hover:bg-slate-50 group-hover:border-[#D4AF37]">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400 group-hover:bg-yellow-50 group-hover:text-[#D4AF37] transition">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Click or Drag PDF Here</p>
                                <p class="text-[10px] text-slate-400 mt-1">Max Size: 5MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#D4AF37] hover:text-slate-900 transition shadow-xl transform hover:-translate-y-1">
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>