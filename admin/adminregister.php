<?php
include '../conn.php'; // Include your connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $username, $password);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Admin registered successfully!'); window.location.href='adminlogin.php';</script>";
    } else {
        echo "<script>alert('Registration failed!');</script>";
    }
}
?>

<html lang="en">
<head>
    <title>Admin Register</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('https://www.transparenttextures.com/patterns/leaf.png'); /* Subtle leaf texture */
            background-color: #e0f7fa; /* Soft, nature-inspired background color */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-container {
            background-color: #ffffff; /* Clean white background for the form */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow for a floating effect */
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        h2 {
            margin-bottom: 30px;
            color: #4C8C4A; /* Dark green for plant theme */
            font-family: 'Georgia', serif; /* Elegant font for headings */
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            background-color: #f9f9f9; /* Light background for input fields */
        }
        button {
            width: 100%;
            padding: 15px;
            background-color: #4C8C4A; /* Plant-themed green color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: limegreen; /* Slightly lighter green for hover effect */
        }
        .register-container::before {
            content: '';
            background-image: url('https://www.transparenttextures.com/patterns/giftly.png'); /* Subtle plant pattern */
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Admin Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
