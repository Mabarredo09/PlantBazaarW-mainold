<?php
session_start();
include '../conn.php'; // Include your connection file

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: adminlogin.php');
    exit();
}

// Fetch the total number of users
$query = "SELECT COUNT(id) AS total_users FROM users";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$totalUsers = $row['total_users']; // Get the total number of users

// Fetch the total number of sellers
$querySellers = "SELECT COUNT(seller_id) AS total_sellers FROM sellers";
$resultSellers = mysqli_query($conn, $querySellers);
$rowSellers = mysqli_fetch_assoc($resultSellers);
$totalSellers = $rowSellers['total_sellers']; // Get the total number of sellers

// Fetch the total number of pending seller applicants
$queryPendingApplicants = "SELECT COUNT(applicantID) AS total_pending_applicants FROM seller_applicant WHERE status = 'pending'";
$resultPendingApplicants = mysqli_query($conn, $queryPendingApplicants);
$rowPendingApplicants = mysqli_fetch_assoc($resultPendingApplicants);
$totalPendingApplicants = $rowPendingApplicants['total_pending_applicants']; // Get the total number of pending applicants

// Fetch the total number of reported users
$queryReportedUsers = "SELECT COUNT(DISTINCT reported_user) AS total_reported_users FROM reports WHERE status = 'pending'";
$resultReportedUsers = mysqli_query($conn, $queryReportedUsers);
$rowReportedUsers = mysqli_fetch_assoc($resultReportedUsers);
$totalReportedUsers = $rowReportedUsers['total_reported_users']; // Get the total number of reported users

// Handle approve/reject actions for reports
if (isset($_POST['action_report'])) {
    $reportId = $_POST['reportID'];

    if ($_POST['action_report'] === 'approve') {
        // Approve: Archive the user before deleting
        $archiveUserQuery = "INSERT INTO reported_user_archive (user_id, firstname, lastname, email)
                              SELECT u.id, u.firstname, u.lastname, u.email 
                              FROM users u
                              JOIN reports r ON u.id = r.user_id
                              WHERE r.report_id = ?";
        $stmt = mysqli_prepare($conn, $archiveUserQuery);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);

        // Delete the user account
        $deleteUserQuery = "DELETE FROM users WHERE id = (SELECT user_id FROM reports WHERE report_id = ?)";
        $stmt = mysqli_prepare($conn, $deleteUserQuery);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);

        // Send email notification to the user about account deletion
        // First, get the user's email for the notification
        $emailQuery = "SELECT u.email FROM users u JOIN reports r ON u.id = r.user_id WHERE r.report_id = ?";
        $stmt = mysqli_prepare($conn, $emailQuery);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);
        $resultEmail = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($resultEmail);
        $userEmail = $user['email'];

        // Prepare the email content
        $subject = "Account Deletion Notification";
        $message = "Dear user, your account has been deleted due to violation of our terms of service. If you have any questions, please contact support.";
        $headers = "From: support@plantbazaar.com"; // Change to your support email

        // Send email
        mail($userEmail, $subject, $message, $headers);

        // Update report status to approved
        $updateReport = "UPDATE reports SET status = 'approved' WHERE report_id = ?";
        $stmt = mysqli_prepare($conn, $updateReport);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);

        echo "<script>
                Swal.fire('Success!', 'Report Approved and User Deleted!', 'success')
                    .then(() => location.reload());
              </script>";
    } elseif ($_POST['action_report'] === 'reject') {
        // Reject the report: Just delete the report entry from the reports table
        $deleteReport = "DELETE FROM reports WHERE reported_user_id = ?";
        $stmt = mysqli_prepare($conn, $deleteReport);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);

        echo "<script>
                Swal.fire('Rejected', 'Report has been removed.', 'info')
                    .then(() => location.reload());
              </script>";
    }
}

// Fetch reports
$queryReports = "SELECT r.reported_user, r.id, u.firstname, u.lastname, u.email, r.report_reason, r.proof_img_1, r.proof_img_2, r.proof_img_3, r.proof_img_4, r.proof_img_5, r.proof_img_6
                 FROM reports r
                 JOIN users u ON r.id = u.id
                 WHERE r.status = 'pending'";
$resultReports = mysqli_query($conn, $queryReports);

// Fetch the total number of users, sellers, applicants, and reports (already fetched in previous steps)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* CSS (same as the previous one you provided) */
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            background-image: url('https://www.transparenttextures.com/patterns/leaf.png');
            background-color: #e0f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        nav {
            background-color: #4C8C4A;
            color: white;
            padding: 10px;
            position: fixed;
            height: 100%;
            width: 200px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }
        nav h2 {
            color: white;
            margin: 0;
            padding: 10px 0;
            font-family: 'Georgia', serif;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }
        nav li {
            margin: 10px 0;
        }
        nav li a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }
        nav li a:hover {
            background-color: limegreen;
        }
        .container {
            margin-left: 220px;
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-family: 'Georgia', serif;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .summary-box {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 30%;
            text-align: center;
            margin: 0 10px;
            margin-bottom: 20px;
        }
        .summary-box h2 {
            color: #4C8C4A;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            overflow: hidden;
        }
        th {
            background-color: #4C8C4A;
            color: white;
        }

        /* Modal CSS */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0, 0, 0); /* Fallback color */
            background-color: rgba(0, 0, 0, 0.9); /* Black w/ opacity */
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }#imageModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

#imageModal img {
    max-width: 90%;
    max-height: 90%;
}

#imageModal span {
    color: white;
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 30px;
    cursor: pointer;
}/* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    word-break: break-all;
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}.description-link{
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
    width: 100px;
    margin-left:20% ;

}

    </style>
</head>
<body>
    <nav>
        <h2>PlantBazaar</h2>
        <ul>
            <li><a class="active" href="admindashboard.php">Users</a></li>
            <li><a href="adminsellerinfo.php">Sellers</a></li>
            <li><a href="sellerapplicant.php">Seller Applicants</a></li>
            <li><a href="listedplants.php">Listed Plants</a></li>
            <li><a href="soldplants.php">Sold Plants</a></li>
            <li><a href="adminreports.php">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
    <div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0, 0, 0, 0.8); z-index:1000; justify-content:center; align-items:center;">
    <span onclick="closeModal()" style="color:white; position:absolute; top:20px; right:30px; font-size:30px; cursor:pointer;">&times;</span>
    <img id="modalImage" src="" alt="Proof Image" style="max-width:90%; max-height:90%; margin:auto; display:block;">
    <button id="prevBtn" style="position:absolute; left:10px; top:50%; transform:translateY(-50%);">Previous</button>
    <button id="nextBtn" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">Next</button>
</div>
<!-- Modal Structure -->
<div id="descriptionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Description</h2>
        <p id="modalDescription"></p>
    </div>
</div>

        <div class="header">
            <h1>Admin Dashboard</h1>
        </div>

        <div class="summary">
            <div class="summary-box">
                <h2>Total Users</h2>
                <p><strong><?php echo $totalUsers; ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Sellers</h2>
                <p><strong><?php echo $totalSellers; ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Applicants</h2>
                <p><strong><?php echo $totalPendingApplicants; ?></strong></p>
            </div>
        </div>

        <div class="summary">
            <div class="summary-box">
                <h2>Total Listed Plants</h2>
                <p><strong><?php  ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Sold Plants</h2>
                <p><strong><?php  ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Reports</h2>
                <p><strong><?php ; ?></strong></p>
            </div>
        </div>

        <!-- Reported Users Table -->
        <h2>Reported Users</h2>
<?php 
$query = "SELECT * FROM reports"; // Adjust this query as needed to suit your requirements
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo '<table>';
    echo '<tr>';
    echo '<th>User ID</th>';
    echo '<th>Reported User</th>';
    echo '<th>Proof Images</th>'; // Column for proof images
    echo '<th>Description</th>';
    echo '<th>Reported Reason</th>';
    echo '<th>Status</th>';
    echo '<th>Action</th>';
    echo '</tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['reported_user'] . '</td>';
        
        // Display proof images
        echo '<td>';
        $imagePaths = []; // Initialize an array to hold the image paths
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($row["proof_img_$i"])) {
                // Build the correct image path
                $imagePath = '../chat_upgrade/ajax/uploads/proof_images/' . htmlspecialchars($row["proof_img_$i"]);
                // Store the image path in the array
                $imagePaths[] = $imagePath;
        
                // Create a clickable image
                echo '<a href="#" onclick="openImageModal(' . htmlspecialchars(json_encode($imagePaths)) . ', ' . ($i - 1) . '); return false;">';
                echo '<img src="' . $imagePath . '" alt="Proof Image" style="max-width: 100px; max-height: 100px; margin-right: 5px;">';
                echo '</a>';
            }
        }
        echo '</td>'; // End of proof images cell
        
        // Description link
        echo '<td><a href="#" class="description-link" onclick="openDescriptionModal(' . htmlspecialchars(json_encode($row['description'])) . '); return false;">' . htmlspecialchars($row['description']) . '</a></td>';
        
        echo '<td>' . $row['report_reason'] . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td>';
        echo '<button class="approve-btn" data-id="' . $row['id'] . '">Approve</button>';
        echo '<button class="reject-btn" data-id="' . $row['id'] . '">Reject</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'No reported users found.';
}
mysqli_close($conn);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.approve-btn').click(function() {
        var id = $(this).data('id');
        $.ajax({
            url: '../Ajax/handle_reports.php',
            type: 'POST',
            data: { action: 'approve', id: id },
            success: function(response) {
                alert(response);
                location.reload(); // Refresh the page to see changes
            },
            error: function() {
                alert('An error occurred while processing the request.');
            }
        });
    });

    $('.reject-btn').click(function() {
        var id = $(this).data('id');
        $.ajax({
            url: '../Ajax/handle_reports.php',
            type: 'POST',
            data: { action: 'reject', id: id },
            success: function(response) {
                alert(response);
                location.reload(); // Refresh the page to see changes
            },
            error: function() {
                alert('An error occurred while processing the request.');
            }
        });
    });
});

let currentImageIndex = 0; // To track the current image index
let currentImagePaths = []; // To store the current image paths

function openImageModal(imagePaths, index) {
    currentImagePaths = imagePaths; // Save the image paths for navigation
    currentImageIndex = index; // Set the current index
    document.getElementById('modalImage').src = currentImagePaths[currentImageIndex]; // Set the initial image
    document.getElementById('imageModal').style.display = 'block'; // Show the image modal
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none'; // Hide the image modal
}

function changeImage(direction) {
    currentImageIndex += direction; // Update index based on direction
    if (currentImageIndex < 0) {
        currentImageIndex = currentImagePaths.length - 1; // Loop to last image
    } else if (currentImageIndex >= currentImagePaths.length) {
        currentImageIndex = 0; // Loop to first image
    }
    document.getElementById('modalImage').src = currentImagePaths[currentImageIndex]; // Update image source
}

function openDescriptionModal(description) {
    document.getElementById('modalDescription').innerText = description; // Set the description text
    document.getElementById('descriptionModal').style.display = 'block'; // Show the description modal
}

function closeDescriptionModal() {
    document.getElementById('descriptionModal').style.display = 'none'; // Hide the description modal
}

// Close modals when clicking outside of them
window.onclick = function(event) {
    const imageModal = document.getElementById('imageModal');
    const descriptionModal = document.getElementById('descriptionModal');
    if (event.target == imageModal) {
        closeImageModal();
    } else if (event.target == descriptionModal) {
        closeDescriptionModal();
    }
}
function closeModal() {
    const modal = document.getElementById('imageModal'); // Change this to your modal ID
    if (modal) {
        modal.style.display = 'none'; // Hides the modal
    }
}


</script>

</body>
</html>

<style>
    .approve-btn, .reject-btn {
    padding: 10px 10px; /* Add padding for size */
    font-size: 14px; /* Font size */
    color: white; /* White text */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor */
    transition: background-color 0.3s ease, transform 0.1s ease; /* Smooth color and scale transition */
    margin: 5px; /* Add space between buttons */
}

.approve-btn {
    background-color: #28a745; /* Green for approve */
}

.approve-btn:hover {
    background-color: #218838; /* Darker green on hover */
}

.approve-btn:active {
    background-color: #1e7e34; /* Even darker green on click */
    transform: scale(0.98); /* Slightly shrink on click */
}

.reject-btn {
    background-color: #dc3545; /* Red for reject */
}

.reject-btn:hover {
    background-color: #c82333; /* Darker red on hover */
}

.reject-btn:active {
    background-color: #bd2130; /* Even darker red on click */
    transform: scale(0.98); /* Slightly shrink on click */
}

.approve-btn:focus, .reject-btn:focus {
    outline: none; /* Remove outline */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Light shadow for focus */
}

</style>