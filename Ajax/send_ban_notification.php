<?php
// Include the Composer autoload file
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send the ban notification email
function sendBanNotification($userEmail) {
    $mail = new PHPMailer(true); // Create a new PHPMailer instance

    try {
        // Server settings
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'plantbazaar21@gmail.com'; // Your SMTP username
        $mail->Password = 'izyf vnfq tkwi rkjt'; // Your SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('plantbazaar21@gmail.com', 'PlantBazaar'); // Sender's email and name
        $mail->addAddress($userEmail); // Add recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Account Banned Notification';
        $mail->Body = 'Your account has been banned due to violation of our policies. Please contact support for more details.';
        $mail->AltBody = 'Your account has been banned due to violation of our policies. Please contact support for more details.';

        // Send the email
        $mail->send(); // This will throw an exception if something goes wrong

        return ['success' => true, 'message' => 'Email sent successfully.']; // Success response
    } catch (Exception $e) {
        // Handle error
        return ['success' => false, 'message' => $e->getMessage()]; // Error response with the error message
    }
}

?>
