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

// Fetch listed plants with listing_status = 1
$queryPlants = "SELECT plantname, img1, img2, img3, plantSize, plantcategories, details, region, province, city, barangay, street, price FROM product WHERE listing_status = 1";
$resultPlants = mysqli_query($conn, $queryPlants);

// Fetch the total number of listed plants
$totalListedPlantsQuery = "SELECT COUNT(*) AS total_listed_plants FROM product WHERE listing_status = 1";
$resultTotalListedPlants = mysqli_query($conn, $totalListedPlantsQuery);
$rowTotalListedPlants = mysqli_fetch_assoc($resultTotalListedPlants);
$totalListedPlants = $rowTotalListedPlants['total_listed_plants']; // Get the total number of listed plants

// Fetch the total number of sold plants
$querySoldPlants = "SELECT COUNT(*) AS total_sold_plants FROM product WHERE listing_status = 2";
$resultTotalSoldPlants = mysqli_query($conn, $querySoldPlants);
$rowTotalSoldPlants = mysqli_fetch_assoc($resultTotalSoldPlants);
$totalSoldPlants = $rowTotalSoldPlants['total_sold_plants']; // Get the total number of sold plants

// Fetch sold plants
$queryPlants = "SELECT plantname, img1, img2, img3, plantSize, plantcategories, details, region, province, city, barangay, street, price FROM product WHERE listing_status = 2";
$resultPlants = mysqli_query($conn, $queryPlants);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sold Plants</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* CSS for the page layout and styles */
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
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
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
        }
    </style>
</head>
<body>
    <nav>
        <h2>PlantBazaar</h2>
        <ul>
            <li><a href="admindashboard.php">Users</a></li>
            <li><a href="adminsellerinfo.php">Sellers</a></li>
            <li><a href="sellerapplicant.php">Seller Applicants</a></li>
            <li><a href="listedplants.php">Listed Plants</a></li>
            <li><a class="active" href="soldplants.php">Sold Plants</a></li>
            <li><a href="adminreports.php">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Sold Plants</h1>
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
                <p><strong><?php echo $totalSoldPlants ?></strong></p>
            </div>
            <div class="summary-box">
                <h2>Total Reports</h2>
                <p><strong><?php ; ?></strong></p>
            </div>
        </div>

        <!-- Sold Plants Table -->
        <h2>Sold Plants</h2>
        <table>
            <thead>
                <tr>
                    <th>Plant Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($plant = mysqli_fetch_assoc($resultPlants)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($plant['plantname']); ?></td>
                        <td>
                            <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;" onclick="viewMoreInfo('<?php echo htmlspecialchars(json_encode($plant)); ?>')">View More Info</button>
                        </td>
                        
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

<script>
    function viewMoreInfo(plantData) {
        const plant = JSON.parse(plantData);
        const address = `${plant.region}, ${plant.province}, ${plant.city}, ${plant.barangay}, ${plant.street}`;
        const details = `
            <h2>${plant.plantname}</h2>
            <img src="${plant.img1}" alt="${plant.plantname}" style="width:100px;">
            <img src="${plant.img2}" alt="${plant.plantname}" style="width:100px;">
            <img src="${plant.img3}" alt="${plant.plantname}" style="width:100px;">
            <p><strong>Size:</strong> ${plant.plantSize}</p>
            <p><strong>Category:</strong> ${plant.plantcategories}</p>
            <p><strong>Details:</strong> ${plant.details}</p>
            <p><strong>Price:</strong> $${plant.price}</p>
            <p><strong>Address:</strong> ${address}</p>
        `;
        Swal.fire({
            title: plant.plantname,
            html: details,
            showCloseButton: true,
        });
    }
</script>
</html>
