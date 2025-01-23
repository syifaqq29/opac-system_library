<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "your_database_name");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Initialize a variable for the new profile image name
    $new_image_name = null;

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            // Create unique filename
            $new_image_name = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/' . $new_image_name;

            // Attempt to move the uploaded file
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $_SESSION['message'] = "Error uploading file.";
                $_SESSION['message_type'] = "error";
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            $_SESSION['message_type'] = "error";
            header("Location: profile.php");
            exit();
        }
    }

    // Prepare the update query
    $query = "UPDATE users SET full_name = ?, email = ?, phone = ?" . ($new_image_name ? ", profile_image = ?" : "") . " WHERE username = ?";

    // Prepare statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    if ($new_image_name) {
        $stmt->bind_param("sssss", $full_name, $email, $phone, $new_image_name, $username);
    } else {
        $stmt->bind_param("ssss", $full_name, $email, $phone, $username);
    }

    // Execute the query and check for success
    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating profile.";
        $_SESSION['message_type'] = "error";
    }

    // Redirect to profile page
    header("Location: profile.php");
    exit();
}
