<?php
session_start();
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize response
$response = array('status' => '', 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'sendOtp') {
        $email = $_POST['email'];
    
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid email format.';
            echo json_encode($response);
            exit;
        }
    
        $otp = rand(100000, 999999);  // Generate a 6-digit OTP
        $_SESSION['otp'] = $otp;       // Store OTP in session
    
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'plantbazaar21@gmail.com';   // Your email
            $mail->Password = 'izyf vnfq tkwi rkjt';            // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $mail->setFrom('plantbazaar21@gmail.com', 'PlantBazaar'); // Fixed domain
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP code is <b>$otp</b>";
    
            $mail->send();
            $response['status'] = 'success';
            $response['message'] = 'OTP sent to your email.';
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = "Error: {$mail->ErrorInfo}";
        }
        echo json_encode($response);
        exit;
    }
 
}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup with OTP Verification</title>
</head>
<body>
    <!-- Signup Form -->
    <div id="signupModal">
        <h2>Sign Up</h2>
        <form id="signupForm">
            <label>Email:</label>
            <input type="email" id="signupEmail" name="email" placeholder="Email" required>
            <button type="button" id="sendOtpButton">Send OTP</button>

            <div class="form-group">
                <label for="otpInput">Enter OTP:</label>
                <input type="text" id="otpInput" name="otp" placeholder="Enter OTP" required>
            </div>

            <button type="button" id="verifyOtpButton">Verify OTP & Sign Up</button>
        </form>
    </div>

    <div id="message"></div>

    <!-- JavaScript for AJAX Requests -->
    <script>
        document.getElementById('sendOtpButton').addEventListener('click', function() {
            const email = document.getElementById('signupEmail').value;
            if (!email) {
                alert('Please enter your email first.');
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=sendOtp&email=${email}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('message').innerText = data.message;
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('verifyOtpButton').addEventListener('click', function() {
            const otp = document.getElementById('otpInput').value;
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=verifyOtp&otp=${otp}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('message').innerText = data.message;
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>
