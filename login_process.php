<?php
session_start();
include 'config.php'; // Include database connection

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    // Verify the user credentials based on user type
    if ($userType === 'Admin') {
        $query = "SELECT * FROM admin WHERE username = ?";
    } else {
        // Simplified query using only the username
        $query = "SELECT * FROM users WHERE username = ?";
    }

    // Prepare the query
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        die('Query Error: ' . mysqli_error($connect));
    }

    // Bind parameters and execute
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Simple password comparison for both Admin and User
        if ($password === $row['password']) {
            // Set session variables
            $_SESSION['username'] = $row['username'];
            $_SESSION['userType'] = $userType;
            $_SESSION['profile_image'] = $row['profile_image'] ??
                ($userType === 'Admin' ? 'uploads/admin_default.png' : 'uploads/user_default.png');

            // Redirect based on user type
            if ($userType === 'Admin') {
                header("Location: http://localhost:8080/opac-system_library/admin/dashboard.php");
            } else {
                // Redirect to home_user.php with the username
                header("Location: http://localhost:8080/opac-system_library/user/home_user.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid username or password'); window.history.back();</script>";
        exit();
    }
}
