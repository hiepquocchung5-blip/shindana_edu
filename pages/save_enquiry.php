<?php
// pages/save_enquiry.php
// Loaded via form action: index.php?route=pages/save_enquiry

require_once '../config/db.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $interest = $_POST['interest'] ?? 'General';

    if (!empty($name) && !empty($email)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO enquiries (full_name, email, phone, interest) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $interest]);
            
            // Redirect back to home with success message using a query parameter
            // Note: In a real app, you might use a session flash message.
            redirect('pages/landing&msg=' . urlencode("Thank you! Your enquiry has been sent."));
        } catch (PDOException $e) {
            // Log error internally, show generic message
            error_log($e->getMessage());
            redirect('pages/landing&error=' . urlencode("System error. Please try again."));
        }
    } else {
        redirect('pages/landing&error=' . urlencode("Name and Email are required."));
    }
} else {
    // If accessed directly via GET, go home
    redirect('');
}
?>