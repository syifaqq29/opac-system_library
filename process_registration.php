<?php
session_start(); // Start the session
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $course = $_POST['course'];
    $level = $_POST['level'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Insert the account data into the database
    $query = "INSERT INTO users (full_name, email, contact, address, course, level, username, password) 
              VALUES ('$full_name', '$email', '$contact', '$address', '$course', '$level', '$username', '$password')";

    if (mysqli_query($connect, $query)) {
        // Set session for the user
        $_SESSION['username'] = $username;
        $_SESSION['userType'] = 'User';

        // Redirect to home_user.php
        header('Location: http://localhost:8080/opac-system_library/user/home_user.php');
        exit();
    } else {
        echo "<script>alert('Failed to create account: " . mysqli_error($connect) . "');</script>";
        echo "<script>window.location.href = 'http://localhost:8080/opac-system_library/user/register.php';</script>";
        exit();
    }
}
