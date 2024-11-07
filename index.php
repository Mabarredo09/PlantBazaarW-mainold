<?php
include 'conn.php';
session_start();

// Check if a user is logged in
$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$profilePic = ''; // Placeholder for the profile picture
$isSeller = false; // Flag to check if the user is a seller

if ($isLoggedIn) {
    $email = $_SESSION['email'];

    // Query to get the profile picture from the database
    $query = "SELECT id, proflePicture, firstname, lastname FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $profilePic = $user['proflePicture'];  // Assuming you store the path to the profile picture
        $userId = $user['id'];
        $firstname = $user['firstname'];
        $lastname = $user['lastname'];
    }

    // If no profile picture is available, use a default image
    if (empty($profilePic)) {
        $profilePic = 'ProfilePictures/Default-Profile-Picture.png';  // Path to a default profile picture
    }

    // Query to check if the user is a seller
    $sellerQuery = "SELECT seller_id FROM sellers WHERE user_id = '$userId'";
    $sellerResult = mysqli_query($conn, $sellerQuery);

    if ($sellerResult && mysqli_num_rows($sellerResult) > 0) {
        $isSeller = true; // User is a seller
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="jquery.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6Lcv5mUqAAAAABNZ9eLdrYxpn8OWSacrmhefh9I3"></script>
    <script src="notif.js"></script>
    <title>Plant-Bazaar</title>
</head>

<body>
<?php include 'nav.php';?>
    <div class="newly-listed" id="newlyListed">
        <p class="newly-header">
            Newly Listed Plants
        </p>
        <div class="newly-contents" id="newly-contents">
            <!-- Products -->
        </div>
        <div id="pagination">
        <!-- Pagination buttons will be dynamically inserted here -->

    </div>
    </div>
    <?php include 'footer.php'?>
    <script>
$(document).ready(function() {
    // Fetch Newly Listed Plants via AJAX
    $.ajax({
        url: 'Ajax/fetch_newly_listed.php',
        type: 'GET',
        success: function(response) {
            try {
                let plants = response;
                let currentUserEmail = <?php echo json_encode($isLoggedIn ? $email : ''); ?>; // Get logged-in user's email

                if (plants.error) {
                    alert(plants.error); // Show error message if any
                    return;
                }

                // Group plants by location
                let plantsByLocation = {};
                plants.forEach(function(product) {
                    if (!plantsByLocation[product.city]) {
                        plantsByLocation[product.city] = [];
                    }
                    plantsByLocation[product.city].push(product);
                });

                let contentHtml = '';
                let locationsHtml = `
                    <div class="plant-location">
                        <button class="location-btn" data-location="all">Show All</button>
                    </div>`;

                for (let location in plantsByLocation) {
                    // Add plant items to contentHtml
                    plantsByLocation[location].forEach(function(product) {
                        let imgPath = `Products/${product.seller_email}/${product.img1}`;

                        let createdAt = new Date(product.createdAt); // Convert to Date object
                        let currentTime = new Date();
                        let timeDiffInHours = Math.floor((currentTime - createdAt) / (1000 * 60 * 60));
                        let timeDiffInDays = Math.floor(timeDiffInHours / 24);
                        let timeDiffInWeeks = Math.floor(timeDiffInDays / 7);

                        // Display time ago based on the time difference
                        let timeAgoText = '';
                        if (timeDiffInHours < 1) {
                            timeAgoText = 'Just now';
                        } else if (timeDiffInHours < 24) {
                            timeAgoText = `${timeDiffInHours} hours ago`;
                        } else if (timeDiffInDays < 7) {
                            timeAgoText = `${timeDiffInDays} days ago`;
                        } else {
                            timeAgoText = `${timeDiffInWeeks} weeks ago`;
                        }

                        // Show "Chat Seller" button if the logged-in user is not the seller
                        let chatSellerButton = '';
                        if (currentUserEmail && currentUserEmail !== product.seller_email) {
                            chatSellerButton = `<button class="chat-seller" data-email="${product.seller_email}">Chat Seller</button>`;
                            
                        }
                      

                        contentHtml += `
                            <div class="plant-item" data-location="${product.city}">
                                <div class="plant-image">
                                    <img src="${imgPath}" alt="${product.plantname}" onerror="this.onerror=null; this.src='placeholder.jpg';">
                                </div>
                                <p>${product.plantname}</p>
                                <p>Price: ₱${product.price}</p>
                                <p>Category: ${product.plantcategories}</p>
                                <p style="font-size: 12px;">Posted: ${timeAgoText}</p>
                                <div class="plant-item-buttons">
                                    <button class="view-details" data-id="${product.plantid}" data-email="${product.seller_email}">View Details</button>
                                    ${chatSellerButton}
                                    <style>
                                    /* Styles for View Details Button */
                                    .view-details {
                                        background-color: darkgreen; /* Soft green background */
                                        color: white; /* White text */
                                        font-size: 13px; /* Font size */
                                        font-weight: bold; /* Bold font */
                                        padding: 10px 10px; /* Padding for height and width */
                                        border: none; /* Remove default border */
                                        border-radius: 10px; /* Rounded corners */
                                        cursor: pointer; /* Pointer cursor on hover */
                                        transition: background-color 0.3s ease, transform 0.1s ease; /* Smooth transition for color and scale */
                                        margin-left: 15px;
                                        
                                    }

                                    /* Hover effect */
                                    .view-details:hover {
                                        background-color: #4CAF50; /* Slightly darker green on hover */
                                    }

                                    /* Active state */
                                    .view-details:active {
                                        background-color: #3A7; /* Even darker green when clicked */
                                        transform: scale(0.98); /* Slightly shrink button on click */
                                    }

                                    /* Focus state */
                                    .view-details:focus {
                                        outline: none; /* Remove outline on focus */
                                        box-shadow: 0 0 5px rgba(90, 160, 144, 0.5); /* Light green shadow when focused */
                                    }
                                    </style>
                                                    </div>
                            </div>`;
                    });

                    // Add location buttons to locationsHtml
                    locationsHtml += `
                        <div class="plant-location">
                            <button class="location-btn" data-location="${location}">
                                ${location}
                            </button>
                        </div>`;
                }

                $('#newly-contents').html(contentHtml);
                $('#locations').html(locationsHtml);

                // Add event listeners to location buttons to filter plants
                $('.location-btn').on('click', function() {
                    let location = $(this).data('location');
                    console.log(`Button clicked: Location=${location}`); // Log the location

                    if (location === 'all') {
                        $('.plant-item').show();
                    } else {
                        $('.plant-item').each(function() {
                            if ($(this).data('location') === location) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    }
                });

                // Add event listeners to view-details and chat-seller buttons
                $(document).on('click', '.view-details', function() {
                    let plantId = $(this).data('id');
                    let sellerEmail = $(this).data('email');

                    console.log(`View Details button clicked: Plant ID=${plantId}`); // Log the plant ID.
                    console.log(`Chat Seller button clicked: Seller Email=${sellerEmail}`); // Log the seller email

                    // Create a hidden form and submit it
                    let form = $('<form>', {
                        action: 'viewdetails?plant=' + plantId,
                        method: 'GET'
                    }).append($('<input>', {
                        type: 'hidden',
                        name: 'plantId',
                        value: plantId
                    })).append($('<input>', {
                        type: 'hidden',
                        name: 'sellerEmail',
                        value: sellerEmail
                    }));

                    $('body').append(form);
                    form.submit();
                });

                $(document).on('click', '.chat-seller', function() {
                    let sellerEmail = $(this).data('email');
                    console.log(`Chat Seller button clicked: Seller Email=${sellerEmail}`); // Log the seller email
                    window.location.href = `chat_upgrade/chat.php?seller_email=${encodeURIComponent(sellerEmail)}`;
                });
            } catch (error) {
                console.error('Error parsing JSON:', error);
                console.log('Response received:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
});
</script>

</body>

</html>
