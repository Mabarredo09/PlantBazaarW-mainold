<?php
session_start();
include '../conn.php'; // Include your connection file

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: adminlogin.php');
    exit();
}

// Initialize the search term to avoid the undefined variable warning
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Get search term from URL (GET request)

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

// Search functionality integration
$queryUsers = "SELECT id, firstname, lastname FROM users WHERE LOWER(firstname) LIKE LOWER('%$searchTerm%') OR LOWER(lastname) LIKE LOWER('%$searchTerm%')";
$resultUsers = mysqli_query($conn, $queryUsers);

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
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <style>
        body {
    font-family: Verdana, Geneva, Tahoma, sans-serif;
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
    border: 1px solid #888;
    width: 80%;
    border-radius: 10px; /* Rounded corners for modal */
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
button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
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

        <h2>Users List</h2>
        <form method="get" action="admindashboard.php">
            <input type="text" name="search" placeholder="Search by name" value="<?php echo $searchTerm; ?>" />
            <button type="submit">Search</button>
        </form>
        <table>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($resultUsers)) { ?>
                <tr>
                    <td><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></td>
                    <td>
                        <button class="view-info-btn" onclick="showModal(<?php echo $row['id']; ?>)">View Info</button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Modal for User Info -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>User Information</h2>
            <div id="userDetails"></div>
        </div>
    </div>

    <script>
        function showModal(userId) {
            // Fetch user data using AJAX
            fetch(`fetchuserinfo.php?id=${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    const userDetails = `
                        <p><strong>Email:</strong> ${data.email}</p>
                        <p><strong>Gender:</strong> ${data.gender}</p>
                        <p><strong>Phone Number:</strong> ${data.phoneNumber}</p>
                        <p><strong>Region:</strong> ${data.region}</p>
                        <p><strong>City:</strong> ${data.city}</p>
                    `;
                    document.getElementById('userDetails').innerHTML = userDetails;
                    document.getElementById('userModal').style.display = "block"; // Show the modal
                })
                .catch(error => {
                    console.error('Error fetching user info:', error);
                    alert('Failed to fetch user information. Please try again later.');
                });
        }

        function closeModal() {
            document.getElementById('userModal').style.display = "none"; // Hide the modal
        }

        // Close modal when clicking outside of the modal content
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>