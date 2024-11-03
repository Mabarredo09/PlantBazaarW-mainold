<?php
// Include the database connection file
include '../conn.php';
session_start();

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $plantid = $_POST['plantid'];
    $plantname = $_POST['editplantname'];
    $price = $_POST['editPrice'];
    $plantdetails = $_POST['editplantdetails'];
    $plantcategories = $_POST['editPlantcategories'];
    $plantsize = $_POST['editPlantSize'];
    $plantcolor = isset($_POST['editPlantColor']) ? $_POST['editPlantColor'] : ''; // Check if it exists
    $region = $_POST['editregion'];
    $province = $_POST['editprovince'];
    $city = $_POST['editcity'];
    $barangay = $_POST['editbarangay'];
    $street = $_POST['editstreet'];

    // Retrieve the existing image names from the database
    $query = "SELECT img1, img2, img3 FROM product WHERE plantid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $plantid); // Use 's' for string binding
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $existingImg1 = $row['img1'];
    $existingImg2 = $row['img2'];
    $existingImg3 = $row['img3'];

    // Check if the image fields are empty
    $img1 = !empty($_FILES['img1']['name']) ? $_FILES['img1']['name'] : $existingImg1;
    $img2 = !empty($_FILES['img2']['name']) ? $_FILES['img2']['name'] : $existingImg2;
    $img3 = !empty($_FILES['img3']['name']) ? $_FILES['img3']['name'] : $existingImg3;

    // Check if the plant ID is valid
    $query = "SELECT * FROM product WHERE plantid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $plantid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Error: Plant ID is invalid.";
        exit;
    }

    // Update the plant data (removed 'location' from the query)
    $query = "UPDATE product SET plantname = ?, price = ?, details = ?, plantcategories = ?, plantsize = ?, plantcolor = ?, region = ?, province = ?, city = ?, barangay = ?, street = ?, img1 = ?, img2 = ?, img3 = ? WHERE plantid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdsssssssssssss", $plantname, $price, $plantdetails, $plantcategories, $plantsize, $plantcolor, $region, $province, $city, $barangay, $street, $img1, $img2, $img3, $plantid);

    if (!$stmt->execute()) {
        echo "Error: Unable to update plant data.";
        exit;
    }

    // Upload images
    $target_dir = "../Products/" . $_SESSION['email'] . "/";
    foreach (['img1', 'img2', 'img3'] as $imgKey) {
        if (!empty($_FILES[$imgKey]['name'])) {
            $target_file = $target_dir . basename($_FILES[$imgKey]['name']);
            if (!move_uploaded_file($_FILES[$imgKey]['tmp_name'], $target_file)) {
                echo "Error uploading file $imgKey.";
                exit;
            }
        }
    }

    echo "Plant data updated successfully.";
    exit;
}
?>
