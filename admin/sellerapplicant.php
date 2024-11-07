<?php
session_start();
include '../conn.php'; // Include connection file

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: adminlogin.php');
    exit();
}

// Handle approve/reject actions
if (isset($_POST['action'])) {
    $applicantId = $_POST['applicantID'];

    if ($_POST['action'] === 'approve') {
        // Approve: Insert into sellers table and update status
        $sql = "INSERT INTO sellers (user_id) 
                SELECT user_id FROM seller_applicant WHERE applicantID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $applicantId);
        mysqli_stmt_execute($stmt);

        $update = "UPDATE seller_applicant SET status = 'approved' WHERE applicantID = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, 'i', $applicantId);
        mysqli_stmt_execute($stmt);

        echo "<script>
                Swal.fire('Success!', 'Application Approved!', 'success')
                    .then(() => location.reload());
              </script>";
    } elseif ($_POST['action'] === 'reject') {
        // Insert the rejected application into the 'seller_applicant_archive' table
        $archive = "INSERT INTO seller_applicant_archive (applicantID, user_id, validid, selfieValidid)
                    SELECT sa.applicantID, sa.user_id, sa.validid, sa.selfieValidid
                    FROM seller_applicant sa
                    WHERE sa.applicantID = ?";
    
        $stmt = mysqli_prepare($conn, $archive);
        mysqli_stmt_bind_param($stmt, 'i', $applicantId);
    
        // Execute the archive insertion
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error: " . mysqli_error($conn); // Debugging in case of failure
        }
    
        // Delete the application from the original 'seller_applicant' table
        $delete = "DELETE FROM seller_applicant WHERE applicantID = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, 'i', $applicantId);
        mysqli_stmt_execute($stmt);
    
        // Notify the user of successful rejection
        echo "<script>
                Swal.fire('Rejected', 'Application has been archived.', 'info')
                    .then(() => location.reload());
              </script>";
    }
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


// Fetch pending applications
$queryApplications = "SELECT sa.applicantID, u.firstname, u.lastname, sa.validid, sa.selfieValidid, u.email
                      FROM seller_applicant sa 
                      JOIN users u ON sa.user_id = u.id 
                      WHERE sa.status = 'pending'";
$resultApplications = mysqli_query($conn, $queryApplications);

// Fetch listed plants with listing_status = 1
$queryPlants = "SELECT plantname, img1, img2, img3, plantSize, plantcategories, details, region, province, city, barangay, street, price FROM product WHERE listing_status = 1";
$resultPlants = mysqli_query($conn, $queryPlants);

// Fetch the total number of listed plants
$totalListedPlantsQuery = "SELECT COUNT(*) AS total_listed_plants FROM product WHERE listing_status = 1";
$resultTotalListedPlants = mysqli_query($conn, $totalListedPlantsQuery);
$rowTotalListedPlants = mysqli_fetch_assoc($resultTotalListedPlants);
$totalListedPlants = $rowTotalListedPlants['total_listed_plants']; // Get the total number of listed plants

// Fetch listed plants with listing_status = 2 (sold plants)
$querySoldPlants = "SELECT plantname, img1, img2, img3, plantSize, plantcategories, details, region, province, city, barangay, street, price FROM product WHERE listing_status = 2";
$resultSoldPlants = mysqli_query($conn, $querySoldPlants);

// Fetch the total number of sold plants
$totalSoldPlantsQuery = "SELECT COUNT(*) AS total_sold_plants FROM product WHERE listing_status = 2";
$resultTotalSoldPlants = mysqli_query($conn, $totalSoldPlantsQuery);
$rowTotalSoldPlants = mysqli_fetch_assoc($resultTotalSoldPlants);
$totalSoldPlants = $rowTotalSoldPlants['total_sold_plants']; // Get the total number of sold plants
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seller Applicant</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('https://www.transparenttextures.com/patterns/leaf.png'); /* Subtle leaf pattern */
            background-color: #e0f7fa; /* Light background color for a cozy feel */
            margin: 0;
            padding: 0;
            color: #333;
        }
        nav {
            background-color: #4C8C4A; /* Dark green color */
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
            font-family: 'Georgia', serif; /* A more elegant font */
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
            margin-left: 220px; /* Space for the sidebar */
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
            font-family: 'Georgia', serif; /* A more elegant font */
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .summary-box {
            background-color: #ffffff; /* White background for summary boxes */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 30%;
            text-align: center;
            margin: 0 10px; /* Horizontal margin for spacing */
            margin-bottom: 20px; /* Vertical margin for spacing */
        }
        .summary-box h2 {
            color: #4C8C4A; /* Dark green color */
        }
        table {
            width: calc(100% - 220px); /* Adjusted width to leave space for the sidebar */
            margin-left: 220px; /* Shifting the table to the right by 220px to avoid overlap */
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4C8C4A; /* Dark green color */
            color: white;
        }
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid;
            width: 500px;
            height: 400px;
            border-radius: 10px; /* Rounded corners for modal */
        }
        #modalImage {
            width: 500px;
            height: 400px;
            object-fit: cover;
            border-radius: 10px; /* Rounded corners for modal image */
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
                <p><strong><?php echo $totalListedPlants ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Sold Plants</h2>
                <p><strong><?php echo $totalSoldPlants  ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Reports</h2>
                <p><strong><?php ; ?></strong></p>
            </div>
        </div>
        </div>
    <table>
        <tr>
            <th>Name</th>
            <th>Valid ID</th>
            <th>Selfie with ID</th>
            <th>Action</th>
        </tr>
       <?php while ($row = mysqli_fetch_assoc($resultApplications)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
        <td>
            <a href="#" onclick="showModal('<?php echo '../sellerapplicants/' . $row['email'] . '/' . htmlspecialchars($row['validid']); ?>')">
                
                <img src="<?php echo '../sellerApplicants/' . $row['email'] . '/' . htmlspecialchars($row['validid']); ?>" alt="Valid ID" width="100" height="100">
            </a>
        </td>
        <td>
            <a href="#" onclick="showModal('<?php echo '../sellerapplicants/' . $row['email'] . '/' . htmlspecialchars($row['selfieValidid']); ?>')">
                <img src="<?php echo '../sellerApplicants/' . $row['email'] . '/' . htmlspecialchars($row['selfieValidid']); ?>" alt="Selfie with ID" width="100" height="100">
            </a>
        </td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="applicantID" value="<?php echo htmlspecialchars($row['applicantID']); ?>">
                <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
            </form>
        </td>
        
    </tr>
<?php } ?>
    </table>

    <!-- Modal for fullscreen image display -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImage" src="" alt="Full Image">
        </div>
    </div>


 <style>
    body {
    font-family: Verdana, Geneva, Tahoma, sans-serif;
}
    .approve-btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 10px;
        border-radius: 5px;
        cursor: pointer;
        outline: none; /* Remove black outline on focus */
    }
    .reject-btn {
        background-color: #f44336;
        color: white;
        padding: 10px 10px;
        border-radius: 5px;
        cursor: pointer;
        outline: none; /* Remove black outline on focus */
    }

    
</style>

                        </form>
                    </td>
                </tr>
        </table>
    </div>
</body>
</html>

<script>
        function showModal(imageSrc) {
            document.getElementById("modalImage").src = imageSrc;
            document.getElementById("imageModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
        }
        
    </script>