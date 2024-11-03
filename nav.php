<?php
include 'conn.php';
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

        // Check if email already exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $response['status'] = 'error';
            $response['message'] = 'Email already exists.';
            echo json_encode($response);
            exit;
        }

        // Generate OTP
        $otp = rand(100000, 999999);  // Generate a 6-digit OTP
        $_SESSION['otp'] = $otp;       // Store OTP in session

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'plantbazaar21@gmail.com';   // Your email
            $mail->Password = 'izyf vnfq tkwi rkjt';            // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('plantbazaar21@gmail.com', 'PlantBazaar');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP code is <b>$otp</b>";

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
    // if (empty($profilePic)) {
    //     $profilePic = 'plant-bazaar.jpg';  // Path to a default profile picture
    // }

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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
    function checkPasswordRequirements() {
        const password = document.getElementById("signupPassword").value;

        console.log("Current password:", password); // Log the current password value

        // Check length
        const lengthValid = password.length >= 8;
        document.getElementById("length").classList.toggle("valid", lengthValid);
        document.getElementById("length").classList.toggle("invalid", !lengthValid);
        console.log("Length valid:", lengthValid); // Log length check result

        // Check uppercase
        const uppercaseValid = /[A-Z]/.test(password);
        document.getElementById("uppercase").classList.toggle("valid", uppercaseValid);
        document.getElementById("uppercase").classList.toggle("invalid", !uppercaseValid);
        console.log("Uppercase valid:", uppercaseValid); // Log uppercase check result

        // Check lowercase
        const lowercaseValid = /[a-z]/.test(password);
        document.getElementById("lowercase").classList.toggle("valid", lowercaseValid);
        document.getElementById("lowercase").classList.toggle("invalid", !lowercaseValid);
        console.log("Lowercase valid:", lowercaseValid); // Log lowercase check result

        // Check number
        const numberValid = /\d/.test(password);
        document.getElementById("number").classList.toggle("valid", numberValid);
        document.getElementById("number").classList.toggle("invalid", !numberValid);
        console.log("Number valid:", numberValid); // Log number check result

        // Check special character
        const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        document.getElementById("special").classList.toggle("valid", specialValid);
        document.getElementById("special").classList.toggle("invalid", !specialValid);
        console.log("Special character valid:", specialValid); // Log special character check result
    }

    // Event listener to check password requirements on each input
    document.getElementById("signupPassword").addEventListener("input", checkPasswordRequirements);
});

    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="jquery.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="ph_address.js"></script>
    <script src="address.js"></script>
    <title>Plant-Bazaar</title>
</head>


    <div class="header">
        <nav class="navigation">
            <div class="logo">
                <span class="plant">PLANT</span>
                <p class="bazaar">-BAZAAR</p>
                <i class="fa-solid fa-spa"></i>
            </div>
            <div class="nav1">
                <a href="index" id="home">Home</a>
                <a href="plantCategories">Plants Categories</a>
                <a href="#" >About</a>
                <a href="#">Contact Us</a>
                <?php if ($isLoggedIn): ?>
                <a href="#" id="chats">Chats</a>
                <?php endif;?> 
            </div>
            <div class="login-signup">
                <?php if ($isLoggedIn): ?>
                    <!-- Show Profile Picture if user is logged in -->
                    <a href="#" class="profile-link">
                        <img src="ProfilePictures/<?php echo $profilePic; ?>" alt="Profile" class="profile-pic">
                    </a>
                    
                <?php else: ?>
                    <!-- Show Login button if user is not logged in -->
                    <a href="#" id="loginLink">Login</a>
                <?php endif; ?>
            </div>
        </nav>
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>

    <div class="dropdown-menu">
        <?php if ($isLoggedIn): ?>
            <a href="#" class="profile-link">
                        <img src="ProfilePictures/<?php echo $profilePic; ?>" alt="Profile" class="profile-pic">
            </a>
            <a><p>Hello, <?php echo $firstname . ' ' . $lastname; ?></p> </a>
        <?php endif;?>
        <a href="#" id="home1">Home</a>
        <a href="#" id="about1">About</a>
        <a href="#">Contact</a>
        <?php if ($isLoggedIn): ?>
            <a href="#" id="logoutLink">Logout</a>
        <?php else:?>
        <a href="#" id="loginLink1">Login</a>
        <?php endif;?>
    </div>

    <div class="dropdown-profile">
   <?php
    if ($isLoggedIn) {
        echo'<p>Hello, ' . $firstname . ' ' . $lastname . '</p>';
    }?>
    <?php if ($isSeller): ?>
        <a href="Seller/seller_dashboard">Seller Dashboard</a> <!-- Change the link as needed for the seller's dashboard -->
    <?php else: ?>
        <a href="applySeller.php" id="sellerApply" class="sellerApply">Be A Seller</a> <!-- Link to becoming a seller -->
    <?php endif; ?>
    <a href="editprofile.php">Edit Profile</a>
    <a href="#" id="logoutLink">Logout</a>
</div>

  <!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Login</h2>
        <form method="POST" action="" id="loginForm">
            <input type="email" id="loginEmail" placeholder="Email" required>
            <div class="error-label" style="display: none;"></div>
            <input type="password" id="loginPassword" placeholder="Password" required>
            <div class="error-label" style="display: none;"></div>
            <div class="g-recaptcha" data-sitekey="6LfL8mUqAAAAAB5RfKDVCgiEFCNPJ7Y1emjO3E9D"></div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="#" id="signupLink">Sign Up</a></p>
    </div>
</div>


<div class="about-us" id="aboutUs" style="display: none;">
        <?php include 'aboutUs.php'; ?>
</div><!-- Signup Modal -->
<div class="modal" id="modalOverlay"></div> <!-- Overlay for blur effect -->
<div id="signupModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>

        <h2>Sign Up</h2>
        <form id="signupForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="signupEmail">Email</label>
                <input type="email" id="signupEmail" name="email" placeholder="Email" required>
                <p id="message"></p>
                <button type="button" id="sendOtpButton">Send OTP</button>
                <div id="otpTimer" style="display:none;">
                    OTP expires in <span id="countdown">120</span> seconds
                </div>
            </div>
            <div class="form-group">
                <label for="otpInput">Enter OTP:</label>
                <input type="text" id="otpInput" name="otp" placeholder="Enter OTP" required>
            </div>

            <div class="form-group">
    <label for="signupPassword">Password</label>
    <div style="position: relative; display: flex; align-items: center;">
        <input type="password" id="signupPassword" name="password" placeholder="Password" required
               style="padding-right: 30px; width: 100%;">
        <i id="togglePassword" class="fas fa-eye"
         ></i>
    </div>
    
    <!-- Password requirements messages -->
    <h6 id="message_header" class="message_header" style="display: none;">Recommended password can include:</h6>
    <p id="length" class="message" style="display: none;">At least 8 characters</p>
    <p id="uppercase" class="message" style="display: none;">At least one uppercase letter</p>
    <p id="lowercase" class="message" style="display: none;">At least one lowercase letter</p>
    <p id="number" class="message" style="display: none;">At least one number</p>
    <p id="special" class="message" style="display: none;">At least one special character</p>
</div>



            <div class="form-group">
                <label for="signupFirstName">First Name</label>
                <input type="text" id="signupFirstName" name="firstname" placeholder="First Name" required>
            </div>

            <div class="form-group">
                <label for="signupLastName">Last Name</label>
                <input type="text" id="signupLastName" name="lastname" placeholder="Last Name" required>
            </div>

            <div class="form-group">
                <label for="signupGender">Gender</label>
                <select id="signupGender" name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="signupPhoneNumber">Phone Number</label>
                <input type="text" id="signupPhoneNumber" name="phonenumber" 
                       placeholder="Phone Number" 
                       maxlength="11" required 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <small>Must be exactly 11 digits.</small>
            </div>

            <div class="form-group">
            <label for="region">Region:</label>
            <select id="region" name="region" class="region-select">
                <option value="" disabled selected>Select Region</option>
            </select>
            </div>

            <div class="form-group">
            <label for="city">City/Municipality:</label>
            <select id="city" name="city" class="city-select">
                <option value="" disabled selected>Select City/Municipality</option>
            </select>
            </div>


            <div class="form-group">
                <label for="signupProfilePicture">Profile Picture</label>
                <input type="file" id="signupProfilePicture" name="profilePicture" accept="image/*">
            </div>

            <!-- Submit Button -->
            <button type="submit" id="signupButton" disabled>Sign Up</button>
        </form>
        <p>Already have an account? <a href="#" id="loginLink">Login</a></p>
    </div>
</div>
</html>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       
     function isNumber(event) {
    const char = String.fromCharCode(event.which);
    if (!/[0-9]/.test(char)) {
        event.preventDefault(); // Prevent the default action if not a number
    }
}

const passwordInput = document.getElementById("signupPassword");
    const messageHeader = document.getElementById("message_header");
    const messages = document.querySelectorAll(".message");
    

    // Show the recommendations when the password field is in focus
    passwordInput.addEventListener("focus", () => {
        messageHeader.style.display = "block";
        messages.forEach(message => message.style.display = "block");
    });

    // Hide the recommendations when the password field loses focus
    passwordInput.addEventListener("blur", () => {
        messageHeader.style.display = "none";
        messages.forEach(message => message.style.display = "none");
        
    });
document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordField = document.getElementById("signupPassword");

    // Toggle the type attribute
    const isPasswordVisible = passwordField.type === "password";
    passwordField.type = isPasswordVisible ? "text" : "password";
    
    // Toggle the icon class for the eye (show/hide)
    this.classList.toggle("fa-eye-slash", isPasswordVisible);
    this.classList.toggle("fa-eye", !isPasswordVisible);
});

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('signupPhoneNumber');
    input.addEventListener('keypress', isNumber);
});
    const otpInput = document.getElementById('otpInput');
    const signupButton = document.getElementById('signupButton');

    // Initially disable the signup button
    signupButton.disabled = true;

    // Event listener for the OTP input field
    otpInput.addEventListener('input', function() {
        // Check if OTP input is empty
        if (otpInput.value.trim() === '') {
            signupButton.disabled = true; // Disable button if empty
        } else {
            signupButton.disabled = false; // Enable button if not empty
        }
    });

    // document.getElementById("chats").addEventListener("click", function() {
        
    // });
    // Function to handle clicking on the username
    $(document).on('click', '.chat-seller', function() {
    let userId = $(this).closest('.seller').data('user-id'); // Get user ID from the closest seller div

    // Fetch and display messages for this user
    // display_messages(userId);

    // Update the user status to 1 when chatting
    $.ajax({
        url: 'ajax/update_users_status.php', // Endpoint to update user status
        method: 'POST',
        data: {
            user_id: userId, // Send the user ID to update
            status: 1 // Set the status to 1
        },
        success: function(response) {
            console.log('User status updated successfully:', response);
        },
        error: function(xhr, status, error) {
            console.error('Error updating user status:', error);
        }
    });
});


   

   document.addEventListener('DOMContentLoaded', function() {

    $(document).on('click', '.chat-seller', function() {
    let sellerEmail = $(this).data('email');
    console.log(`Chat Seller button clicked: Seller Email=${sellerEmail}`); // Log the seller email

    // Redirect to chat page with seller email as a query parameter
    window.location.href = `chat_upgrade/chat.php?seller_email=${encodeURIComponent(sellerEmail)}`;
});

    // Check if the profile link exists (only when the user is logged in)
    const profileLink = document.querySelector('.profile-link');
    const chatsLink =document.getElementById('chats');
    
    if (profileLink) {
        profileLink.addEventListener('click', function() {
            const dropdownMenu = document.querySelector('.dropdown-profile');
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
            } else {
                console.error("Dropdown menu not found");
            }
        });
    } else {
        console.log("Profile link not found - User may not be logged in");
    }

    if (chatsLink) {
        chatsLink.addEventListener('click', function() {
            window.location.href = "chat_upgrade/index.php";
        });
    } else {
        console.log("Profile link not found - User may not be logged in");
    }

    

$(document).ready(function() {
    
    document.querySelectorAll('.chat-seller').forEach(button => {
    button.addEventListener('click', function() {
        // Get seller email from the data attribute
        const sellerEmail = this.getAttribute('data-email');

        // Redirect to chat.php with the seller email as a query parameter
        window.location.href = `chat_upgrade/chat.php?seller_email=${encodeURIComponent(sellerEmail)}`;
    });
});


});



    // Hamburger menu functionality
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            const dropdownMenu = document.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
            } else {
                console.error("Dropdown menu not found");
            }
        });
    } else {
        console.error("Hamburger menu not found");
    }
}); 

  
            
            // End of AJAX Fetching of newly listed plants
            $('#viewDetailsModal .close').on('click', function() {
            $('#viewDetailsModal').modal('hide');
            });

// Login form validation
$("#loginForm").submit(function(event) {
    event.preventDefault();

    var recaptchaResponse = grecaptcha.getResponse();

    // Check if the reCAPTCHA is completed
    if (recaptchaResponse === "") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Please complete the reCAPTCHA."
            });
            return; // Exit if reCAPTCHA is not completed
        }

    var email = $("#loginEmail").val();
    var password = $("#loginPassword").val();



    $.ajax({
        url: "Ajax/login.php",
        type: "POST",
        data: {email: email, password: password,'g-recaptcha-response': recaptchaResponse},
        dataType: 'json',
        success: function(response) {
            console.log("Response: " + JSON.stringify(response));
            if (response.success) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.message,
                    showConfirmButton: false,
                    timer: 3000
                });
                // Reload page after 1.5 seconds
                setTimeout(function() {
                    location.reload();
                }, 3000);
            } else {
                if (response.message === 'Email not found') {
                    $("#loginEmail").addClass("error");
                    $("#loginEmail").next(".error-label").text(response.message).show();
                } else if (response.message === 'Invalid password') {
                    $("#loginPassword").addClass("error");
                    $("#loginPassword").next(".error-label").text(response.message).show();
                } else {
                    $("#loginEmail").addClass("error");
                    $("#loginPassword").addClass("error");
                    $(".error-label").text(response.message).show();
                }
            }
        },
        error: function(xhr, status, error) {
            if (xhr.status === 200) {
        // Login was successful, but response body is not in expected format
        console.log("Login successful, but response body is not in expected format");
        // You can also try to parse the response body as text or HTML
        }else{
            console.error("Error: " + status + " - " + error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "An unexpected login error occurred. Please try again later.",
            });
            $("#loginEmail").addClass("error");
            $("#loginPassword").addClass("error");
            $(".error-label").text("An unexpected error occurred").show();
        }
    }
    });
});

// Clear error label when correct email is inputted
$("#loginEmail").on("keyup", function() {
    var email = $(this).val();
    if (email !== "") {
        $(this).removeClass("error");
        $(this).next(".error-label").hide();
    }
});
$("#signupForm").submit(function(event) {
    event.preventDefault(); // Prevent default form submission

    // Create a FormData object
    var formData = new FormData();
    
    // Append form data
    formData.append('profilePicture', $('#signupProfilePicture')[0].files[0]); // Get the file input
    formData.append('email', $("#signupEmail").val());
    formData.append('password', $("#signupPassword").val());
    formData.append('firstname', $("#signupFirstName").val());
    formData.append('lastname', $("#signupLastName").val());
    formData.append('gender', $("#signupGender").val());
    formData.append('phonenumber', $("#signupPhoneNumber").val());
    formData.append('region', $("#region").val());
    formData.append('city', $("#city").val());

    // Debugging: Log formData content
    console.log("FormData entries:");
    for (var pair of formData.entries()) {
        console.log(pair[0]+ ', ' + pair[1]);
    }

    $.ajax({
        url: "Ajax/register.php",
        type: "POST",
        data: formData,
        processData: false, // Prevent jQuery from processing the data
        contentType: false, // Prevent jQuery from setting the content-type
        success: function(response) {
            console.log("Response: " + response);
            if (response.trim() === "success") {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Successfully Registered",
                    showConfirmButton: true,
                    timer: 3000
                });
                // Reload page after 3 seconds
                setTimeout(function() {
                    location.reload();
                }, 3000);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "An unexpected error occurred. Please try again later."
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + status + " - " + error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "An unexpected error occurred. Please try again later."
            });
        }
    });
});




// Logout AJAX
$(document).on('click', '#logoutLink', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'Ajax/logout.php', // Path to your logout.php file
            type: 'POST',
            success: function(response) {
                if (response.trim() === "success") {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Successfully Logged out',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    // Reload page after 3 seconds
                    setTimeout(function() {
                        location.reload();
                        window.location.href = " ";
                    }, 3000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Logout Failed',
                        text: 'Please try again.',
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: " + status + " - " + error);
                Swal.fire({
                icon: "error",
                title: "Error",
                text: "An unexpected error occurred. Please try again later."
            });
            }
        });
    });

    // Add event listener to view-details buttons

        // Get the modals
    var loginModal = document.getElementById("loginModal");
    var signupModal = document.getElementById("signupModal");

    // Get the links to open the modals
    var signupLink = document.getElementById("signupLink");
    var loginLink = document.getElementById("loginLink");

    // Get the links inside modals (Login inside Signup modal and vice versa)
    var loginLinkInSignupModal = document.querySelector("#signupModal #loginLink");

    // Get the <span> elements that close the modals
    var closeButtons = document.getElementsByClassName("close");

    // Function to open the login modal
    function openLoginModal() {
        signupModal.style.display = "none";
        loginModal.style.display = "block";
    }

    // Function to open the signup modal
    function openSignupModal() {
        loginModal.style.display = "none";
        signupModal.style.display = "block";
    }

    // When the user clicks the signup link, open the signup modal
    signupLink.onclick = function(event) {
        event.preventDefault();
        openSignupModal();
        loginForm.reset();
    };

    // When the user clicks the login link, open the login modal
    loginLink.onclick = function(event) {
        event.preventDefault();
        openLoginModal();
        signupForm.reset();
    };

    loginLink1.onclick = function(event) {
        event.preventDefault();
        openLoginModal();
        signupForm.reset();
    };

    // When the user clicks the login link inside the signup modal, switch to the login modal
    loginLinkInSignupModal.onclick = function(event) {
        event.preventDefault();
        openLoginModal();
        signupForm.reset();
    };

    // Close the modals when clicking the close (x) buttons
    for (var i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            loginModal.style.display = "none";
            signupModal.style.display = "none";
        };
    }


    
// Get the dropdown-profile div


// Get the sellerApply button
var sellerApplyButton = document.querySelector(".sellerApply");

// Add event listener to the sellerApply button
sellerApplyButton.addEventListener("click", function() {
    console.log("Seller apply button clicked");
  // Get the seller application modal
  var sellerApplicationModal = document.getElementById("sellerApplicationModal");
  // Show the modal
  sellerApplicationModal.style.display = "block";
});

// Get the seller application modal
var sellerApplicationModal = document.getElementById("sellerApplicationModal");

// Get the close button
var closeButton = sellerApplicationModal.querySelector(".close");
$(document).ready(function() {
    // Add event listener to the sellerApply button
    $('#sellerApply').on('click', function(event) {
        event.preventDefault();  // Prevent the default link behavior (if it's a link)
        console.log("Seller apply button clicked");  // Log a message when the button is clicked
        $('#sellerApplicationModal').show();  // Show the modal
    });
    
    // Add event listener to close button
    $('#sellerApplicationModal .close').on('click', function() {
        console.log("Close button clicked");  // Log a message when the close button is clicked
        $('#sellerApplicationModal').hide();  // Hide the modal
    });
});

// Add event listener to the sellerApply button
document.getElementById("sellerApply").addEventListener("click", function(event) {
    event.preventDefault();  // Prevent the default link behavior (if it's a link)
    console.log("Seller apply button clicked");  // Log a message when the button is clicked
    $('#sellerApplicationModal').show();  // Show the modal
});
// Add event listener to close button
closeButton.onclick = function() {
  sellerApplicationModal.style.display = "none";
};

    // Add event listener to apply button
        document.getElementById("sellerApplicationForm").addEventListener("submit", function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: "POST",
            url: "apply_as_seller.php",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
            console.log("Seller application form submitted successfully!");
            console.log(response);
            },
            error: function(xhr, status, error) {
            console.log("Error submitting seller application form:");
            console.log(xhr.responseText);
            }
        });
        });
 </script>
 <script>
    $(document).ready(function() {
    let timer; // Variable to hold the timer
    const countdownTime = 120; // Countdown time in seconds

    // Function to start the countdown
    function startTimer(duration) {
        let timerDisplay = $('#countdown');
        let endTime = Date.now() + duration * 1000; // Calculate expiration time
        $('#otpTimer').show(); // Show the timer
        $('#sendOtpButton').prop('disabled', true); // Disable the send OTP button
        $('#otpInput').prop('disabled', false); // Enable OTP input field
        $('#signupButton').prop('disabled', false); // Enable the sign-up button

        timer = setInterval(function() {
            let remainingTime = Math.round((endTime - Date.now()) / 1000); // Calculate remaining time
            if (remainingTime <= 0) {
                clearInterval(timer);
                $('#otpTimer').hide();
                $('#sendOtpButton').prop('disabled', false); // Enable the send OTP button
                $('#otpInput').prop('disabled', true); // Disable OTP input field
                $('#signupButton').prop('disabled', true); // Disable sign-up button
                Swal.fire({
                    icon: 'error',
                    title: 'OTP Expired!',
                    text: 'Please request a new OTP.',
                });
                localStorage.removeItem('otpExpiration'); // Clear the expiration from localStorage
                return;
            }
            timerDisplay.text(remainingTime); // Update the countdown display
        }, 1000);

        // Store expiration time in localStorage
        localStorage.setItem('otpExpiration', endTime);
    }

    // Check for existing timer on page load
    function checkTimer() {
        let expirationTime = localStorage.getItem('otpExpiration');
        if (expirationTime) {
            let remainingTime = Math.round((expirationTime - Date.now()) / 1000);
            if (remainingTime > 0) {
                startTimer(remainingTime); // Start timer with remaining time
            } else {
                localStorage.removeItem('otpExpiration'); // Clear the expired item
                $('#otpInput').prop('disabled', true); // Disable OTP input
                $('#signupButton').prop('disabled', true); // Disable sign-up button
            }
        }
    }

    // Call checkTimer on page load
    checkTimer();

    // Send OTP button click event
    $('#sendOtpButton').on('click', function() {
        const email = $('#signupEmail').val();

        $.ajax({
            type: 'POST',
            url: 'sentOtp.php', // Update this to your PHP script path
            data: {
                action: 'sendOtp',
                email: email
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                    });
                    startTimer(countdownTime); // Start the timer
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: response.message,
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Something went wrong. Please try again later.',
                });
            }
        });
    });

    // Sign up form submission event
    $('#signupForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Handle sign-up logic here (e.g., validate OTP, submit form data, etc.)
        // You might need to send another AJAX request to validate the OTP and save the user details.
    });
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