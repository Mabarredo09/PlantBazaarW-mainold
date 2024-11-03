<?php
include '../conn.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Google reCAPTCHA secret key
    $secretKey = "6LfL8mUqAAAAANpODH758b9EVgK3A5k7dJdd5q4h";
    
    $captcha = $_POST['g-recaptcha-response'];

    // Verify the reCAPTCHA response with Google
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        echo json_encode(['success' => false, 'message' => 'CAPTCHA validation failed.']);
        exit;
    }

    // Sanitize input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    // Validate password (add your password rules here)
    if (strlen($password) < 8 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/\d/", $password)) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long and contain both letters and numbers.']);
        exit;
    }

    // Prepare the SQL statement
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("s", $email);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if a user is found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['user_status'] == 2) {
            echo json_encode(['success' => false, 'message' => 'Your account has been banned.']);
            exit;
        }
        

        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            // Password is correct
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true, 'message' => 'Successfully logged in']);
        } else {
            // Password is incorrect
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
    }
}
?>
