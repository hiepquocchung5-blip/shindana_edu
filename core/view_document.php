<?php
// core/view_document.php
// loaded via index.php?route=core/view_document

// 1. Load Config (Relative to this file due to Router's chdir)
require_once '../config/db.php';
require_once '../config/functions.php';

// 2. Enforce Login
requireLogin();

if (isset($_GET['file'])) {
    // Sanitize filename to prevent directory traversal
    $filename = basename($_GET['file']);
    $filepath = '../uploads/documents/' . $filename;

    if (file_exists($filepath)) {
        
        // 3. Strict Permission Check
        
        // If user is an Agent, ensure they OWN this student record
        if ($_SESSION['user_type'] === 'agent') {
            $stmt = $pdo->prepare("SELECT id FROM students WHERE document_path = ? AND agent_id = ?");
            $stmt->execute([$filename, $_SESSION['user_id']]);
            
            if (!$stmt->fetch()) {
                http_response_code(403);
                die("⛔ Access Denied: You do not have permission to view this document.");
            }
        }
        
        // If user is Admin, they pass through automatically.
        // (You could add a specific check here if you have granular admin roles)

        // 4. Serve the PDF
        // Content-Type header tells the browser this is a PDF
        header('Content-Type: application/pdf');
        
        // 'inline' attempts to open in browser; 'attachment' would force download
        header('Content-Disposition: inline; filename="' . $filename . '"');
        
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        
        // Output file data
        readfile($filepath);
        exit;
    } else {
        http_response_code(404);
        die("File not found on server.");
    }
} else {
    die("No file specified.");
}
?>