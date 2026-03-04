<?php
// Main Front Controller / Router
// This file is the entry point for all requests

// 1. Load Configuration & Helpers
// We use __DIR__ to ensure we are looking relative to the root index.php
$db_file = __DIR__ . '/config/db.php';
$fn_file = __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/settings.php'; 


// Ensure config files exist before loading
if (file_exists($db_file) && file_exists($fn_file)) {
    require_once $db_file;
    require_once $fn_file;
} else {
    // Basic fallback error if config is missing
    die("<b>System Error:</b> Configuration files not found in <code>/config/</code> directory.");
}

// 2. Determine Route
// Default to the landing page if no route is specified
$route = isset($_GET['route']) ? $_GET['route'] : 'pages/landing';

// Security: Clean the route to prevent directory traversal attacks (dots/slashes)
// We allow alphanumeric, underscores, and forward slashes
$route = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $route);

// 3. Construct File Path
// We look for files relative to the project root
$file_path = __DIR__ . '/' . $route . '.php';

// Normalize slashes for OS compatibility (Windows vs Linux)
$file_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $file_path);

// 4. Routing Logic
if (file_exists($file_path)) {
    
    // TRICK: Change working directory to the target file's directory
    // This ensures relative includes (like require '../config/db.php') inside the sub-files still work correctly.
    $original_dir = getcwd();
    chdir(dirname($file_path));
    
    // Include the requested file (The View)
    require basename($file_path);
    
    // Restore working directory (good practice for subsequent operations)
    chdir($original_dir);

} else {
    // 404 Handling
    http_response_code(404);
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif; background:#f8fafc; height:100vh; display:flex; flex-direction:column; justify-content:center; align-items:center;'>";
    echo "<h1 style='color:#D4AF37; font-size:4rem; margin:0; line-height:1;'>404</h1>";
    echo "<h2 style='text-transform:uppercase; font-size:1rem; letter-spacing:2px; color:#64748b; margin-top:10px;'>Page Not Found</h2>";
    echo "<p style='color:#94a3b8; margin-bottom:10px; max-width:400px;'>The requested route <strong>" . htmlspecialchars($route) . "</strong> could not be found.</p>";
    
    // Debugging hint: This shows you EXACTLY where the code expects the file to be.
    // Check if your file exists at this path.
    echo "<p style='color:#cbd5e1; font-size:0.8rem; margin-bottom:30px; font-family:monospace; background:#1e293b; padding:10px; rounded:8px;'>Expected file path:<br> " . htmlspecialchars($file_path) . "</p>";
    
    echo "<a href='" . base_url() . "' style='background:#0f172a; color:white; padding:15px 30px; text-decoration:none; border-radius:12px; font-weight:bold; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; transition:0.3s;'>Return Home</a>";
    echo "</div>";
}
?>