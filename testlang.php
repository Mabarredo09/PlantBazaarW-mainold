<?php   
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
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/stylesignup.css">
    <script src="antiInspect.js"></script>
    <title>Login</title>
</head>
<script>
let timeleft = 60;
let timeinterval;

function updatetimer() {
    timeleft--;
    if (timeleft >= 0) {
        document.getElementById('otpbtn').disabled=true;
        document.getElementById('timer').textContent = `Time remaining: ${timeleft} seconds remaining`;
    }
    else{
        clearInterval(timeinterval);
        document.getElementById('otpbtn').disabled=false;
        document.getElementById('timer').textContent = 'Time expired';
    }
}
function getOTP() {
    const email = document.getElementsByName('email')[0].value;
            if (email) {
                fetch('sendemail.php?email='+email)
                    .then(response => {
                        if (response.ok) {
                            Swal.fire({
                            icon: 'success',
                            title: 'Your OTP has been sent',
                            text: 'OTP has been sent to your email. Please check your inbox',
                            confirmButtonText: 'OK'
                        });
                            document.getElementById('otptxt').innerText="Resend OTP";
                            document.getElementById('timecontainer').style.display='block';
                            timeleft = 60;
                            updatetimer();
                            timeinterval = setInterval(updatetimer, 1000);
                            // alert('OTP has been sent to your email. Please check your inbox.');
                        } else {
                            alert('Failed to send OTP. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error',
                    text: 'Please enter your email',
                    showConfirmButton: false,
                    timer: 2500
                })
            }
}

function verifyOTP() {
    const enteredOTP = document.getElementById('otp').value;
    const formData = new FormData();
    formData.append('otp', enteredOTP);

    fetch('verify_otp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to the next page or perform any other action upon successful verification
                        Swal.fire({
                            icon: 'success',
                            title: 'Successfully Verified',
                            text: 'OTP has been successfully verified',
                            confirmButtonText: 'OK'
                        });
            document.getElementById('signup').disabled=false;
        } else {
            // Display error message
                        Swal.fire({
                            icon: 'warning',
                            title: 'Try Again',
                            text: 'The code did not match',
                            confirmButtonText: 'OK'
                        });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle fetch error
        alert('An error occurred while verifying OTP. Please try again later.');
    });
}


document.getElementById('phonenumber').addEventListener('input', function(event) {
    let phoneNumber = event.target.value;
    if (phoneNumber.length > 11) {
        event.target.value = phoneNumber.slice(0, 11);
    }
});
// otp remaining

// Function to get a cookie by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}


</script>
<body>
    <nav>
        <label class="logo">PlantBazaar</label>
    <ul>
        <li><a href="index">Home</a></li>
        <li><a href="#">Plant Categories</a></li>
        <li><a href="#">Following</a></li>
        <li><a href="#">Be a Seller</a></li>
    </ul>
    </nav>
    <div class="form-container">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="login-container">
        <h1>Sign-Up</h1>
        <br>
        <p>Enter your information below to proceed. If you already have an account, please log in instead.</p>
        <br><br> 
        <div class="input-box input-flex">
            <input class="size1" type="text" name="fname" id="firstname" value="<?php echo isset($_POST['fname']) ? $_POST['fname'] : ''; ?>" value="<?php echo $_POST['fname'] ?>" title="Your First Name Here" placeholder="First Name" pattern="^([a-zA-Z\.\-\s]+)$" required>
            <input class="size2" type="text" name="lname" id="lastname" value="<?php echo isset($_POST['lname']) ? $_POST['lname'] : ''; ?>" value="<?php echo $_POST['lname'] ?>" title="Your Last Name Here" placeholder="Last Name" pattern="^([a-zA-Z\.\-\s]+)$" required>
        </div>
        <div class="input-box">
        <input type="email" name="email" id="email"  value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" value="<?php echo $_POST['email'] ;?>" title="Email" placeholder="Enter Email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" required>
        </div>
        <span id="email-error" class="error-message"></span>
        <div class="gender">
            <label>Sex</label><br>
            <input type="radio" name="gender" title="Male" id="male" value="male" required>
            <label>Male</label><br>    
            <input type="radio" name="gender" title="Female" id="female" value="female">
            <label>Female</label><br>
        </div>
        <div class="dob">
            <label>Date of Birth</label><br>
            <input type="date" id="dob" name="dob" value="<?php echo isset($_POST['dob']) ? $_POST['dob'] : ''; ?>" value="<?php echo $_POST['dob'] ;?>" pattern="^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$">
        </div>
        <div class="input-box input-flex">
            <select class="Phonenumber">
                <option value="PH">+63</option>
            </select>
            <input type="number" name="phonenumber" id="phonenumber" value="<?php echo isset($_POST['phonenumber']) ? $_POST['phonenumber'] : ''; ?>" value="<?php echo $_POST['phonenumber'] ;?>"  placeholder="Enter Phone Number" pattern="^([0-9]{11})" required >
            <span id="phonenumber-error"></span>
        </div>
        <div class="input-box">
            <input type="text" name="address" id="address" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>" value="<?php echo $_POST['address'] ;?>" placeholder="Enter Home Address" pattern="^([a-zA-Z0-9\.\-\s]+)$">
        </div>
        <div class="input-box">
            <input type="password" name="password" id="password" placeholder="Enter Password"  pattern="^(?=.*\d)(?=.*[a-zA-Z]).{8,}$">
        </div>
        <div class="input-box">
            <input type="password" name="confirmpassword" id="confirmpassword" placeholder="Confirm Password" pattern="^(?=.*\d)(?=.*[a-zA-Z]).{8,}$">
        </div>
        <div class="input-box input-flex">
        <input type="number" name="otp" id="otp" placeholder="Enter OTP">
        <button type="button" class="otpbtn" onclick="getOTP()" id="otpbtn"><span id="otptxt">Get OTP</span></button>
        </div>
        <div id="timecontainer" style="display: none;">
        <div id="timer">Time remaining: 60 seconds</div>
        </div>
        <button type="button" class="verifyotpbtn" id="verifyotpbtn" onclick="verifyOTP()"><span>Verify OTP</span></button>
        <br>
        <button class="login-btn" name="signup" id="signup" disabled>Sign-Up</button>
        <div class="bottom-text">
        <p>Already Have an Account? <a href="samplelogin1">Log-in Instead</a>.</p>
        </div>
    </form>
    </div>
    <br>
    
    <footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section about">
                <h3>About Plant Bazaar</h3>
                <p>Here at Plant Bazaar, we believe in the power of plants to enhance our living spaces, improve our well-being, and connect us with nature. Whether you're looking for indoor plants to brighten up your home or outdoor plants to transform your garden, we've got you covered.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section contact">
                <h3>Contact Information</h3>
                <p><i class="fas fa-map-marker-alt"></i>  General Luna St, Poblacion East II, Aliaga, N.E</p>
                <p><i class="fas fa-envelope"></i> info@plantbazaar.com</p>
                <p><i class="fas fa-phone"></i> 093932355551</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Plant Bazaar. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
<script>
    document.getElementById('phonenumber').addEventListener('input', function(event) {
    let phoneNumber = event.target.value;
    if (phoneNumber.length > 11) {
        event.target.value = phoneNumber.slice(0, 11);
    }
});
</script>
<?php
if(isset($_POST['signup'])){
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $phonenumber = $_POST['phonenumber'];
    $address= $_POST['address'];
    $password = $_POST['password'];
    $password2 = $_POST['confirmpassword'];

    

    if(!isset($_POST['fname']) || !isset($_POST['lname']) || !isset($_POST['email']) || !isset($_POST['phonenumber'])|| !isset($_POST['gender']) || !isset($_POST['address']) || !isset($_POST['password']) || !isset($_POST['confirmpassword'])){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'All fields are required!',
        });
        </script>";
        exit();
    }

    if(!preg_match("/^[a-zA-Z\s]+$/", $firstname) || !preg_match("/^[a-zA-Z\s]+$/", $lastname)){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid name format!',
        });
        </script>";
        exit();
    }
     

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid email format!',
        });
        </script>";
        exit();
    }

    $check_email_query = "SELECT * FROM users WHERE email = '$email'";
    $check_email_result = mysqli_query($connection, $check_email_query);
    if(mysqli_num_rows($check_email_result) > 0){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Email already exists!',
        });
        </script>";
        exit();
    }

    if(!preg_match("/^(?=.*\d)(?=.*[A-Za-z])[A-Za-z\d]{8,}$/", $password)){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Password must contain at least 8 characters, including one letter and one number!',
        });
        </script>";
        exit();
    }
    if($gender != "male" && $gender && "female" && $gender != "other"){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid gender format!',
        });
        </script>";
        exit();
    }

    if(!preg_match("/^\d{11}$/", $phonenumber)){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid phone number format!',
        });
        </script>";
        exit();
    }

    if($password != $password2){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Passwords do not match!',
        });
        </script>";
        exit();
    }
    else{
    $salt="sobranghabanasalt";
    $hashed_password = sha1($salt.$password.$salt);
    $insert = "INSERT INTO users (id,firstname, lastname, email,gender, phonenumber, address, password) VALUES ('','$firstname', '$lastname', '$email','$gender', '$phonenumber', '$address', '$hashed_password')";
    $check_insert = mysqli_query($something, $insert);
    if(!$check_insert){
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Sign-Up Failed',
            confirmButtonText: 'OK'
        });
        </script>";
    }else{

        echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Sign-Up Successful',
            confirmButtonText: 'OK'
        });
        </script>";
        session_destroy();
        header("refresh:3; url=samplelogin1.php");
        ob_end_flush();
              exit();
              
    }
}
}
?>
</html>