<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Security Check
requireAdmin();

// 2. Fetch Data
if (!isset($_GET['id'])) {
    redirect('admin/japan_schools');
}
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM japan_schools WHERE id = ?");
$stmt->execute([$id]);
$school = $stmt->fetch();

if (!$school) {
    die("School not found.");
}

// 3. Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_school'])) {
    $name = trim($_POST['school_name']);
    $region = $_POST['region'];
    $type = $_POST['type'];
    $year = $_POST['est_year'];
    $tuition = $_POST['tuition_fees'];
    $admission = $_POST['admission_months'];
    $web = $_POST['website'];
    $address = $_POST['address_line'];
    $city = $_POST['city'];
    $desc = $_POST['description'];

    $sql = "UPDATE japan_schools SET 
            school_name=?, region=?, type=?, est_year=?, tuition_fees=?, 
            admission_months=?, website=?, address_line=?, city=?, description=? 
            WHERE id=?";
            
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $region, $type, $year, $tuition, $admission, $web, $address, $city, $desc, $id])) {
        redirect('admin/japan_schools&msg=' . urlencode("School details updated successfully."));
    } else {
        $error = "Failed to update school.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit School | Sheindana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="bg-slate-900 text-white p-6 sticky top-0 z-40 shadow-xl">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="<?= admin_url('japan_schools') ?>" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <div class="font-black text-xl uppercase tracking-tighter">Edit <span class="text-[#D4AF37]">Partner</span></div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto p-6 md:p-12">
        <div class="bg-white rounded-[40px] shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-slate-50 p-8 border-b border-slate-100">
                <h2 class="text-2xl font-black text-slate-900">Editing: <?= htmlspecialchars($school['school_name']) ?></h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">ID: #<?= $school['id'] ?></p>
            </div>

            <form method="POST" class="p-8 space-y-8">
                
                <!-- Core Details -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Core Details</h4>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">School Name</label>
                        <input type="text" name="school_name" value="<?= htmlspecialchars($school['school_name']) ?>" required class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Type</label>
                            <select name="type" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                                <option value="Language School" <?= $school['type'] == 'Language School' ? 'selected' : '' ?>>Language School</option>
                                <option value="University" <?= $school['type'] == 'University' ? 'selected' : '' ?>>University</option>
                                <option value="Vocational" <?= $school['type'] == 'Vocational' ? 'selected' : '' ?>>Vocational</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Established Year</label>
                            <input type="number" name="est_year" value="<?= htmlspecialchars($school['est_year']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Location</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Region</label>
                            <select name="region" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none focus:ring-2 ring-[#D4AF37]">
                                <option value="Tokyo" <?= $school['region'] == 'Tokyo' ? 'selected' : '' ?>>Tokyo</option>
                                <option value="Osaka" <?= $school['region'] == 'Osaka' ? 'selected' : '' ?>>Osaka</option>
                                <option value="Fukuoka" <?= $school['region'] == 'Fukuoka' ? 'selected' : '' ?>>Fukuoka</option>
                                <option value="Other" <?= $school['region'] == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">City</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($school['city']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Full Address</label>
                        <input type="text" name="address_line" value="<?= htmlspecialchars($school['address_line']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <!-- Financials -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Financials</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Tuition (JPY)</label>
                            <input type="number" name="tuition_fees" value="<?= htmlspecialchars($school['tuition_fees']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Intake Months</label>
                            <input type="text" name="admission_months" value="<?= htmlspecialchars($school['admission_months']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Website</label>
                        <input type="url" name="website" value="<?= htmlspecialchars($school['website']) ?>" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Description</label>
                    <textarea name="description" rows="5" class="w-full bg-slate-50 p-3 rounded-xl font-bold text-sm outline-none"><?= htmlspecialchars($school['description']) ?></textarea>
                </div>

                <div class="flex gap-4">
                    <a href="<?= admin_url('japan_schools') ?>" class="w-1/3 bg-slate-200 text-slate-600 py-4 rounded-xl font-black uppercase text-xs text-center hover:bg-slate-300 transition">Cancel</a>
                    <button type="submit" name="update_school" class="w-2/3 bg-[#D4AF37] text-slate-900 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:shadow-lg transition">
                        Update School Details
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>