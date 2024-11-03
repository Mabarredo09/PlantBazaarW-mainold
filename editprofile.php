<?php
include 'conn.php';
session_start();

// Start output buffering
ob_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

// Retrieve the user's data from the database
$email = $_SESSION['email'];
$query = "SELECT id, firstname, lastname, phonenumber, region, city, proflePicture FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $userId = $user['id'];
    $profilePicture = $user['proflePicture'];
    $firstname = $user['firstname'];
    $lastname = $user['lastname'];
    $phonenumber = $user['phonenumber'];
    $region = $user['region'];
    $city = $user['city'];
} else {
    echo 'Error retrieving user data';
    exit;
}

$updated = false;

// Handle form submission
if (isset($_POST['submit'])) {
    $newFirstname = $_POST['firstname'];
    $newLastname = $_POST['lastname'];
    $newPhoneNumber = $_POST['phonenumber'];
    $newRegion = $_POST['region'] ? $_POST['region'] : $region;
    $newCity = $_POST['city'] ? $_POST['city'] : $city;

    // Profile picture upload
    if (isset($_FILES['profile-picture']) && $_FILES['profile-picture']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile-picture']['tmp_name'];
        $fileName = $_FILES['profile-picture']['name'];
        $fileSize = $_FILES['profile-picture']['size'];
        $fileType = $_FILES['profile-picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Specify allowed file types
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Set upload file path
            $uploadFileDir = 'ProfilePictures/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Update profile picture in the database
                $query = "UPDATE users SET proflePicture = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $fileName, $userId);
                $stmt->execute();
                $stmt->close();
                $profilePicture = $fileName; // Update local variable to display new image
                $updated = true; // Set updated flag to true for profile picture change
            } else {
                echo "Error moving the uploaded file.";
            }
        } else {
            echo "Upload failed. Allowed file types: " . implode(', ', $allowedfileExtensions);
        }
    }

    // Check if any other data has changed
    if ($newFirstname != $firstname || $newLastname != $lastname || $newPhoneNumber != $phonenumber || $newRegion != $region || $newCity != $city) {
        // Update the user's data in the database
        $query = "UPDATE users SET firstname = ?, lastname = ?, phonenumber = ?, region = ?, city = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $newFirstname, $newLastname, $newPhoneNumber, $newRegion, $newCity, $userId);

        if ($stmt->execute()) {
            $updated = true; // Set updated flag to true for other data changes
        } else {
            echo "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }
}

ob_end_flush();
?>

<?php include 'nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="editprofile.css">
    <!-- SweetAlert Library -->
    <script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <nav>
                <ul>
                    <li><a href="editprofile.php">Edit Profile</a></li>
                    <li><a href="changepassword.php">Change Password</a></li>
                </ul>
            </nav>
        </div>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <label for="profile-picture">Profile Picture:</label>
                <div class="profile-picture-upload">
                    <img src="ProfilePictures/<?php echo $profilePicture; ?>" alt="Profile Picture" id="preview-image">
                    <input type="file" id="profile-picture" name="profile-picture" accept="image/*">
                    <button type="button" id="change-profile-pic">Change Profile Picture</button>
                </div>
                <label for="firstname">Firstname:</label>
                <input type="text" id="firstname" name="firstname" pattern="[a-zA-Z\s]+" value="<?php echo $firstname; ?>" required><br><br>
                <label for="lastname">Lastname:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo $lastname; ?>" required><br><br>
                <label for="phonenumber">Phone Number:</label>
                <input type="tel" id="phonenumber" name="phonenumber" maxlength="10" pattern="[9][0-9]{9}" title="Please enter a valid phone number" value="<?php echo $phonenumber; ?>" required>
                <p class="note">Format: 9XXXXXXXX</p><br><br>
                
                <label for="region">Region:</label>
                <select id="regionSelect" name="region" onchange="populateCities()">
                    <option value="">Select a Region</option>
                </select>

                <label for="city">City:</label>
                <select id="citySelect" name="city">
                    <option value="">Select a City</option>
                </select>

                <input type="submit" name="submit" id="submit" value="Update Profile" disabled>
            </form>
        </div>
    </div>

    <!-- SweetAlert for profile update confirmation -->
    <?php if ($updated): ?>
        <script>
            Swal.fire({
                title: "Profile Updated!",
                text: "Your profile has been updated successfully",
                icon: "success",
                button: "Ok",
                timer: 3000
            }).then(function() {
                window.location.href = "index.php";
            });
        </script>
    <?php endif; ?>
</body>
<script src="ph-address.js"></script>
<script>
    const profilePictureInput = document.getElementById('profile-picture');
    const previewImage = document.getElementById('preview-image');

    profilePictureInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('change-profile-pic').addEventListener('click', function() {
        document.getElementById('profile-picture').click();
    });

    document.getElementById('phonenumber').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        if (e.target.value.startsWith('0')) {
            e.target.value = e.target.value.slice(1);
        }
    });

    const regionSelect = document.getElementById('regionSelect');
    const citySelect = document.getElementById('citySelect');

    // Existing region and city from the database
    const currentRegion = "<?php echo $region; ?>";
    const currentCity = "<?php echo $city; ?>";

    // Populate the region dropdown
    Object.keys(edit_philippinesData).forEach(region => {
        const option = document.createElement('option');
        option.value = region;
        option.textContent = region;
        if (region === currentRegion) {
            option.selected = true; // Set the saved region as selected
        }
        regionSelect.appendChild(option);
    });

    // Populate the cities based on selected region
    function populateCities() {
        citySelect.innerHTML = '<option value="">Select a City</option>'; // Clear existing options
        const selectedRegion = regionSelect.value;
        
        if (selectedRegion) {
            const cities = edit_philippinesData[selectedRegion];
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                if (city === currentCity) {
                    option.selected = true; // Set the saved city as selected
                }
                citySelect.appendChild(option);
            });
        }
    }

    // Initial population of cities
    populateCities();

    const inputs = document.querySelectorAll("input, select");
    inputs.forEach(input => {
        input.addEventListener("input", () => {
            const formChanged = Array.from(inputs).some(
                i => i.value !== i.defaultValue || profilePictureInput.files.length > 0
            );
            document.getElementById("submit").disabled = !formChanged;
        });
    });
</script>
</html>
