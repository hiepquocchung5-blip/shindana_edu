<?php
// pages/save_enquiry.php
// Loaded via form action: index.php?route=pages/save_enquiry

// Removed db.php since we are bypassing the database entirely
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize incoming form data
    $name = trim($_POST['full_name'] ?? '');
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $phone = trim($_POST['phone'] ?? 'Not provided');
    $interest = trim($_POST['interest'] ?? 'General Inquiry');

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        // 2. Configure Email Parameters
        $to = "info@shinedana.com"; 
        $subject = "New Website Enquiry: " . $interest;
        
        // 3. Build the Email Body
        $message = "You have received a new consultation request from the Shinedana website.\n\n";
        $message .= "========================================\n";
        $message .= "APPLICANT DETAILS\n";
        $message .= "========================================\n";
        $message .= "Full Name:  {$name}\n";
        $message .= "Email:      {$email}\n";
        $message .= "Phone:      {$phone}\n";
        $message .= "Interest:   {$interest}\n";
        $message .= "========================================\n\n";
        $message .= "Note: You can reply directly to this email to contact the student.";

        // 4. Set Headers (Important for Reply-To functionality)
        $headers = "From: noreply@shinedana.com\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // 5. Send Email and Redirect
        if (mail($to, $subject, $message, $headers)) {
            // Success: Send them back to the landing page with a success UI pulse
            redirect('pages/landing&msg=' . urlencode("Thank you! Your request has been sent directly to our admissions team."));
        } else {
            // Server failure (e.g. SMTP not configured on your hosting panel)
            error_log("Mail failure: Could not send enquiry from $email");
            redirect('pages/landing&error=' . urlencode("System error sending email. Please try contacting us directly."));
        }
        
    } else {
        // Validation failure
        redirect('pages/landing&error=' . urlencode("A valid Name and Email are required."));
    }
} else {
    // If accessed directly via URL, kick them back to home
    redirect('');
}
?>