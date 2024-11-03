<?php
session_start();
include '../conn.php'; // Include your connection file

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: adminlogin.php');
    exit();
}

if (isset($_POST['reported_user'])) {
    $reportedUser = $_POST['reported_user'];

    // Query to get images of the reported user
    $query = "SELECT img1, img2, img3, img4, img5, img6 FROM product WHERE seller_id = (SELECT id FROM users WHERE email = ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $reportedUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($row['img' . $i])) {
                $images[] = $row['img' . $i];
            }
        }
    }

    echo json_encode($images); // Return images as JSON
    exit();
}
?>
