<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'User') {
    echo "<script>alert('Please login first'); window.location='login.php';</script>";
    exit();
}

// Create upload directory if it doesn't exist
if (!file_exists("uploads/users")) {
    mkdir("uploads/users", 0777, true);
}

// Get the username from the session
$userName = $_SESSION['username'];

// Initialize variables
$id = $fn = $em = $ct = $ad = $cs = $lv = $us = $ps = $profile_image = "";

// Fetch the user's details using their username
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $userName);

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id = $row['id_user'];
    $fn = $row['full_name'];
    $em = $row['email'];
    $ct = $row['contact'];
    $ad = $row['address'];
    $cs = $row['course'];
    $lv = $row['level'];
    $us = $row['username'];
    $ps = $row['password'];
    $profile_image = $row['profile_image'];
} else {
    error_log("No data found for username: " . $userName);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Escape inputs to prevent SQL injection
    $fn = mysqli_real_escape_string($conn, $_POST['full_name']);
    $em = mysqli_real_escape_string($conn, $_POST['email']);
    $ct = mysqli_real_escape_string($conn, $_POST['contact']);
    $ad = mysqli_real_escape_string($conn, $_POST['address']);
    $cs = mysqli_real_escape_string($conn, $_POST['course']);
    $lv = mysqli_real_escape_string($conn, $_POST['level']);
    $us = mysqli_real_escape_string($conn, $_POST['username']);
    $ps = mysqli_real_escape_string($conn, $_POST['password']);

    // Handle profile image upload
    $new_profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        $file_size = $_FILES['profile_image']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB limit

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_name = time() . '_' . $_FILES['profile_image']['name'];
            $upload_path = "uploads/users/" . $file_name;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $new_profile_image = $file_name;

                // Delete old profile image if exists
                if (!empty($profile_image) && file_exists("uploads/users/" . $profile_image)) {
                    unlink("uploads/users/" . $profile_image);
                }
            } else {
                echo "<script>alert('Failed to upload image.');</script>";
            }
        } else {
            if (!in_array($file_type, $allowed_types)) {
                echo "<script>alert('Invalid file type. Please upload JPG, PNG or GIF.');</script>";
            } else {
                echo "<script>alert('File size too large. Maximum size is 5MB.');</script>";
            }
        }
    }

    // Prepare the update query
    $updateQuery = "UPDATE users SET 
                    full_name=?, 
                    email=?, 
                    contact=?, 
                    address=?, 
                    course=?, 
                    level=?, 
                    username=?, 
                    password=?";

    // Add profile image to update if one was uploaded
    if ($new_profile_image !== '') {
        $updateQuery .= ", profile_image=?";
    }

    $updateQuery .= " WHERE username=?";

    $updateStmt = $conn->prepare($updateQuery);

    if (!$updateStmt) {
        error_log("Update prepare failed: " . $conn->error);
        die("Update prepare failed: " . $conn->error);
    }

    if ($new_profile_image !== '') {
        $updateStmt->bind_param("ssssssssss", $fn, $em, $ct, $ad, $cs, $lv, $us, $ps, $new_profile_image, $userName);
    } else {
        $updateStmt->bind_param("sssssssss", $fn, $em, $ct, $ad, $cs, $lv, $us, $ps, $userName);
    }

    if ($updateStmt->execute()) {
        // Update the session username if it was changed
        if ($us !== $userName) {
            $_SESSION['username'] = $us;
        }
        echo "<script>alert('Profile updated successfully!'); window.location='myprofile.php';</script>";
    } else {
        error_log("Update failed: " . $updateStmt->error);
        echo "<script>alert('Failed to update profile.'); window.location='myprofile.php';</script>";
    }
}


$wishlist = [];

if ($conn) {
    // Fetch wishlist data for the logged-in user using username to get user_id first
    $userIdQuery = "SELECT id_user FROM users WHERE username = ?";
    $userStmt = $conn->prepare($userIdQuery);
    $userStmt->bind_param("s", $userName);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userRow = $userResult->fetch_assoc()) {
        $userId = $userRow['id_user'];

        // Now fetch the wishlist items using the user_id
        $query = "SELECT books.title, books.author, books.image_url, books.availability 
            FROM wishlist
            JOIN books ON wishlist.book_id = books.id_book
            WHERE wishlist.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $wishlist[] = $row;
        }

        $stmt->close();
    }
    $userStmt->close();
}

$favorites = [];

if ($conn) {
    // Fetch wishlist data for the logged-in user using username to get user_id first
    $userIdQuery = "SELECT id_user FROM users WHERE username = ?";
    $userStmt = $conn->prepare($userIdQuery);
    $userStmt->bind_param("s", $userName);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userRow = $userResult->fetch_assoc()) {
        $userId = $userRow['id_user'];

        // Fetch the favorite items using the user_id
        $query = "SELECT books.title, books.author, books.image_url, books.availability, favorites.added_at 
              FROM favorites
              JOIN books ON favorites.book_id = books.id_book
              WHERE favorites.user_id = ?
              ORDER BY favorites.added_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }

        $stmt->close();
    }
    $userStmt->close();
}

function toggleFavorite($bookId, $userId)
{
    global $conn;

    // Check if already favorited
    $checkQuery = "SELECT * FROM favorites WHERE book_id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $bookId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Remove from favorites
        $deleteQuery = "DELETE FROM favorites WHERE book_id = ? AND user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("ii", $bookId, $userId);
        return [
            'success' => $stmt->execute(),
            'action' => 'removed',
            'message' => 'Removed from favorites'
        ];
    } else {
        // Add to favorites
        $insertQuery = "INSERT INTO favorites (book_id, user_id, added_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $bookId, $userId);
        return [
            'success' => $stmt->execute(),
            'action' => 'added',
            'message' => 'Added to favorites'
        ];
    }
}

// Function to toggle wishlist status
function toggleWishlist($bookId, $userId)
{
    global $conn;

    // Check if already in wishlist
    $checkQuery = "SELECT * FROM wishlist WHERE book_id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $bookId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Remove from wishlist
        $deleteQuery = "DELETE FROM wishlist WHERE book_id = ? AND user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("ii", $bookId, $userId);
        return [
            'success' => $stmt->execute(),
            'action' => 'removed',
            'message' => 'Removed from wishlist'
        ];
    } else {
        // Add to wishlist
        $insertQuery = "INSERT INTO wishlist (book_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $bookId, $userId);
        return [
            'success' => $stmt->execute(),
            'action' => 'added',
            'message' => 'Added to wishlist'
        ];
    }
}

// AJAX request handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['username'])) {  // Change this line
        echo json_encode(['success' => false, 'message' => 'Please log in first']);
        exit;
    }

    $bookId = $_POST['bookId'];
    $userId = $_SESSION['user_id'];  // This will now be available

    if ($_POST['action'] === 'toggleFavorite') {
        echo json_encode(toggleFavorite($bookId, $userId));
        exit;
    } elseif ($_POST['action'] === 'toggleWishlist') {
        echo json_encode(toggleWishlist($bookId, $userId));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Al-Hazen</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
            transition: filter 0.3s ease;
            line-height: 1.6;
            /* Smooth transition for blur effect */
        }

        .nav-container {
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
        }

        /* Top Navigation */
        .top-nav {
            background: #262160;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(38, 33, 96, 0.2);
            z-index: 200;
        }

        .top-nav-logo {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .top-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .top-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .top-nav-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        #favouriteLink i,
        #bookmarkLink i {
            color: #fff;
            /* Make the heart red */
            font-size: 20px;
            /* Adjust icon size */
        }

        /* Banner */
        .banner {
            width: 100%;
            height: 180px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Guide Navigation */
        .guide-nav {
            position: relative;
            background: rgba(38, 33, 96, 0.9);
            padding: 15px 0;
            display: flex;
            justify-content: center;
            gap: 30px;
            backdrop-filter: blur(5px);
            z-index: 100;
        }

        .guide-item {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .guide-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .guide-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
            display: inline-block;
            z-index: 200;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            top: 100%;
            margin-top: 4px;
        }

        .dropdown-content a {
            color: #262160;
            padding: 12px 16px;
            display: block;
            border-radius: 0;
        }

        .dropdown-content a:hover {
            background-color: rgba(38, 33, 96, 0.1);
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropbtn {
            display: flex;
            align-items: center;
        }

        /* Arrow styles */
        .dropbtn::after {
            content: "▼";
            font-size: 10px;
            margin-left: 6px;
        }

        .dropdown:hover .dropbtn::after {
            content: "▲";
            /* Change to up arrow when hovered */
        }

        /* Added focus state to keep the dropdown open when clicked */
        .dropdown:focus-within .dropdown-content {
            display: block;
        }

        /* To keep dropdown open when clicked */
        .dropdown-content {
            display: none;
            visibility: hidden;
        }

        .dropdown:hover .dropdown-content,
        .dropdown:focus-within .dropdown-content {
            display: block;
            visibility: visible;
        }


        /* Search Section Styles */
        .search-section {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-container {
            display: flex;
            gap: 10px;
            background: white;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .search-dropdown,
        .search-input,
        .search-button {
            padding: 8px;
        }

        .search-dropdown {
            min-width: 150px;
            border: 1px solid #ddd;
        }

        .search-input {
            flex-grow: 1;
            border: 1px solid #ddd;
        }

        .search-button {
            background: #262160;
            color: white;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
        }

        .pro-navigation {
            background: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            border-bottom: 1px solid #eee;
        }

        .pro-navigation a {
            color: #0288d1;
            text-decoration: none;
            padding: 5px 10px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pro-navigation a:hover {
            color: #01579b;
            background: rgba(2, 136, 209, 0.1);
            border-radius: 4px;
        }

        .pro-navigation a.active {
            background: rgba(2, 136, 209, 0.1);
            border-radius: 4px;
        }

        .pro-navigation i {
            font-size: 1.1em;
        }

        .content {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 30px auto;
            font-family: Arial, sans-serif;
        }

        .title {
            text-align: center;
        }

        .icon-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }

        .profile-icon {
            width: 80px;
            /* Set width */
            height: 80px;
            /* Set height */
            border-radius: 50%;
            /* Make it circular */
            object-fit: cover;
            /* Cover the area without distortion */
        }

        /* Rest of the styles remain the same */
        .header {
            margin-bottom: 20px;
        }

        .h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2f3380;
            margin: 0;
        }

        .description {
            color: #555;
            font-size: 16px;
            margin-top: 5px;
        }

        .label-main {
            display: block;
            color: #444;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .input-text {
            width: calc(100% - 20px);
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .input-text:focus {
            border-color: #2f3380;
            outline: none;
        }

        .custom-dropdown {
            margin-bottom: 10px;
        }

        .button {
            width: 100%;
            padding: 12px;
            background-color: #2f3380;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .separator {
            border-bottom: solid 1px #f0f0f7;
            margin: 20px 0;
        }

        .button:hover {
            background-color: #1c1f4d;
            transform: translateY(-2px);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-row div {
            display: flex;
            flex-direction: column;
        }

        /* Footer Styles */
        .modern-footer {
            background-color: #262160;
            color: white;
            padding: 60px 0 30px;
            margin-top: 40px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .footer-section {
            padding: 0 15px;
        }

        .footer-section h3 {
            color: white;
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: #fff;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .footer-section a {
            text-decoration: none;
            display: block;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: white;
        }

        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .contact-info i {
            margin-right: 10px;
            width: 20px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }

        .social-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .map-container {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .map-container iframe {
            width: 100%;
            height: 200px;
            border: none;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 30px;
        }

        .footer-bottom p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Utility Classes */
        .hidden {
            display: none;
        }

        .clearfix {
            clear: both;
        }

        /* Search Section Styles */
        .search-section {
            max-width: 1600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Rest of your existing styles... */

        .simple-search {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .simple-search select {
            min-width: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .simple-search input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-button {
            padding: 10px 20px;
            background: #262160;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background: #1a1640;
        }

        .advanced-search {
            display: none;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .advanced-search.show {
            display: block;
        }

        .advanced-search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .search-field label {
            font-weight: 500;
            color: #444;
        }

        .search-field input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-toggle {
            background: none;
            border: none;
            color: #262160;
            cursor: pointer;
            padding: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-toggle:hover {
            text-decoration: underline;
        }

        /* Advanced Search Styles */
        .advanced-search {
            display: none;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .advanced-search.show {
            display: block;
        }

        .advanced-search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .search-field label {
            font-weight: 500;
            color: #444;
        }

        .search-field input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-toggle {
            background: none;
            border: none;
            color: #262160;
            cursor: pointer;
            padding: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-toggle:hover {
            text-decoration: underline;
        }

        /* Results Styles */
        .search-results {
            margin-top: 20px;
        }

        /* Section styles */
        section {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Sidebar styles */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            z-index: 2000;
            top: 0;
            right: -250px;
            background-color: #262160;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 25px;
            color: white;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .wishlist-items {
            padding: 10px 15px;
        }

        .wishlist-items .wishlist-item {
            margin-bottom: 15px;
            color: white;
        }

        .wishlist-items img {
            width: 50px;
            height: auto;
            display: block;
            margin-bottom: 5px;
        }

        .closebtn {
            font-size: 36px;
            margin-left: 15px;
        }

        .sidebar.open {
            right: 0;
        }

        .wishlist-items p {
            margin: 0;
        }

        /* Additional Styles for Items */
        .favorites-items,
        .wishlist-items {
            margin-top: 10px;
        }

        .favorites-items p,
        .wishlist-items p {
            color: white;
            margin: 0;
        }

        .share-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }

        .remove-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .remove-btn:hover {
            background-color: #cc0000;
            transform: translateY(-1px);
        }

        .remove-btn i {
            font-size: 14px;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85rem;
        }

        .status-badge.available {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .status-badge.unavailable {
            background-color: rgba(255, 87, 34, 0.2);
            color: #FF5722;
        }

        .status-badge.reserved {
            background-color: rgba(255, 193, 7, 0.2);
            /* Amber shade */
            color: #FFC107;
            /* Amber color */
        }

        /* Favorites specific styles */
        .favorite-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .favorite-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .favorite-image img {
            width: 80px;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .favorite-details h4 {
            color: white;
            margin: 0 0 8px 0;
        }

        .added-date {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }

        /* Wishlist specific styles */
        .wishlist-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .wishlist-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .wishlist-image {
            width: 80px;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .wishlist-details h4 {
            color: white;
            margin: 0 0 8px 0;
        }

        .empty-message {
            color: white;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }

        hr {
            border: 0.5px solid rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }

        /* Favorites Popup styles */
        .favorites-popup {
            position: absolute;
            /* Position relative to the nearest positioned ancestor */
            top: 50px;
            /* Adjust this to position below the heart icon */
            right: 30px;
            /* Adjust this to position near the heart icon */
            width: 300px;
            /* Width of the popup */
            background-color: #fff;
            /* Background color */
            color: white;
            /* Text color */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            /* Shadow effect */
            padding: 20px;
            /* Padding */
            z-index: 2000;
            /* Sit on top of other content */
            opacity: 0;
            /* Start hidden */
            visibility: hidden;
            /* Start hidden */
            transition: opacity 0.5s ease, visibility 0.5s ease;
            /* Fade effect */
            border-radius: 8px;
            /* Rounded corners */
        }

        /* Show favorites popup */
        .favorites-popup.open {
            opacity: 1;
            /* Fully visible */
            visibility: visible;
            /* Make it visible */
        }

        /* Close button */
        .closebtn {
            cursor: pointer;
            /* Pointer cursor for close button */
            font-size: 20px;
            /* Font size */
        }

        /* Additional Styles for Items */
        .favorites-items {
            margin-top: 10px;
            /* Space above items */
        }

        .favorites-items p {
            color: white;
            /* Text color */
            margin: 0;
            /* Remove margin */
        }

        .toggle-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .toggle-btn {
            padding: 10px 20px;
            border: none;
            background-color: #f0f0f0;
            color: #333;
            cursor: pointer;
            margin: 5px;
        }

        .toggle-btn.active {
            background-color: #007bff;
            color: #fff;
        }

        .book-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .book-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 200px;
            padding: 10px;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }

        .book-image {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .book-info {
            text-align: left;
            margin-bottom: 10px;
        }

        .book-actions {
            display: flex;
            justify-content: space-around;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .read-btn {
            background-color: #28a745;
            color: white;
        }

        .review-btn {
            background-color: #007bff;
            color: white;
        }

        /* Favorites Popup styles */
        .favorites-popup {
            position: absolute;
            top: 60px;
            right: 20px;
            width: 350px;
            background-color: #262160;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .favorites-popup.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .favorites-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .favorites-header h2 {
            color: white;
            margin: 0;
            font-size: 1.2rem;
        }

        .favorites-items {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }

        .favorites-items::-webkit-scrollbar {
            width: 6px;
        }

        .favorites-items::-webkit-scrollbar-track {
            background: transparent;
        }

        .favorites-items::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .favorite-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .favorite-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .favorite-image img {
            width: 70px;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .favorite-details {
            flex: 1;
        }

        .favorite-details h4 {
            color: white;
            margin: 0 0 5px 0;
            font-size: 1rem;
        }

        .favorite-details p {
            color: #ccc;
            margin: 3px 0;
            font-size: 0.9rem;
        }

        .status span {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8rem;
        }

        .status span.available {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .status span.unavailable {
            background-color: rgba(255, 87, 34, 0.2);
            color: #FF5722;
        }

        .added-date {
            font-size: 0.8rem !important;
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .no-favorites {
            text-align: center;
            color: white;
            padding: 20px;
            font-style: italic;
        }

        .closebtn {
            color: white;
            font-size: 24px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .closebtn:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Sidebar styles */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            z-index: 2000;
            top: 0;
            right: -250px;
            background-color: #262160;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 25px;
            color: white;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .wishlist-items {
            padding: 10px 15px;
        }

        .wishlist-items .wishlist-item {
            margin-bottom: 15px;
            color: white;
        }

        .wishlist-items img {
            width: 50px;
            height: auto;
            display: block;
            margin-bottom: 5px;
        }

        .closebtn {
            font-size: 36px;
            margin-left: 15px;
        }

        .sidebar.open {
            right: 0;
        }

        .wishlist-items p {
            margin: 0;
        }

        /* Additional Styles for Items */
        .favorites-items,
        .wishlist-items {
            margin-top: 10px;
        }

        .favorites-items p,
        .wishlist-items p {
            color: white;
            margin: 0;
        }

        .share-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="top-nav">
        <div class="top-nav-logo">
            <a href="home_user.php">Home</a>
            <div class="dropdown">
                <a href="#" class="dropbtn">Guide</a>
                <div class="dropdown-content">
                    <a href="register.php">Register</a>
                    <a href="facility.php">Facility</a>
                    <a href="FAQ.php">FAQ</a>
                </div>
            </div>
            <a href="http://localhost:8080/opac-system_library/app-files/index.html">PSS Explorer 360</a>
        </div>
        <div class="top-nav-actions">
            <a href="#"><?php echo htmlspecialchars($userName); ?>'s Account</a>
            <a href="sign_out.php">Sign Out
                <i class="fas fa-sign-out"></i>
            </a>
            <a href="javascript:void(0)" id="favouriteLink" onclick="toggleFavoritesPopup()">
                <i class="fas fa-heart"></i>
            </a>
            <a href="javascript:void(0)" id="bookmarkLink" onclick="toggleSidebar()">
                <i class="fas fa-bookmark"></i>
            </a>
        </div>
    </nav>

    <!-- Favorites Popup -->
    <div id="favoritesPopup" class="favorites-popup">
        <div class="favorites-header">
            <h2>My Favorites</h2>
            <a href="javascript:void(0)" class="closebtn" onclick="toggleFavoritesPopup()">&times;</a>
        </div>
        <div id="favoritesItems" class="favorites-items">
            <?php if (!empty($favorites)): ?>
                <?php foreach ($favorites as $item): ?>
                    <div class="favorite-item">
                        <div class="favorite-image">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="favorite-details">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p class="author">By: <?php echo htmlspecialchars($item['author']); ?></p>
                            <p class="status">
                                Status:
                                <span class="<?php echo strtolower($item['availability']); ?>">
                                    <?php echo htmlspecialchars($item['availability']); ?>
                                </span>
                            </p>
                            <p class="added-date">Added: <?php echo date('M d, Y', strtotime($item['added_at'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-favorites">No favorites added yet</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Update the wishlist section in the sidebar -->
    <div id="sidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar()">&times;</a>
        <h2 style="color: white; padding: 0 15px;">My Wishlist</h2>
        <div id="wishlistItems" class="wishlist-items">
            <?php if (!empty($wishlist)): ?>
                <?php foreach ($wishlist as $item): ?>
                    <div class="wishlist-item">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($item['title']); ?>"
                                class="wishlist-image">
                        <?php endif; ?>
                        <div class="wishlist-details">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p>By: <?php echo htmlspecialchars($item['author']); ?></p>
                            <p>Status:
                                <span class="status-badge <?php echo strtolower($item['availability']); ?>">
                                    <?php echo htmlspecialchars($item['availability']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">Your wishlist is empty</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Banner -->
    <div class="banner">
        <img src="Banner.png" alt="KVS library Banner">
    </div>

    <!-- Add  translate button to appear -->
    <div id="google_translate_element" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;"></div>

    <!-- Search Form -->
    <div class="search-section">
        <!-- Simple Search Form - Updated with action -->
        <form id="simpleSearchForm" method="POST" action="search.php">
            <div class="simple-search">
                <select name="searchCategory" class="search-dropdown">
                    <option value="title">Title</option>
                    <option value="author">Author</option>
                    <option value="subject">Subject</option>
                    <option value="isbn">ISBN</option>
                    <option value="issn">ISSN</option>
                    <option value="series">Series</option>
                    <option value="call_number">Call number</option>
                </select>
                <input type="text" name="searchTerm" placeholder="Search...">
                <button type="submit" class="search-button" name="simple_search">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>

        <button class="search-toggle" onclick="toggleAdvancedSearch()">
            <i class="fas fa-sliders-h"></i>
            <span id="toggleText">Show Advanced Search</span>
        </button>

        <!-- Advanced Search Form - Updated with action -->
        <form id="advancedSearchForm" method="POST" action="search.php" class="advanced-search">
            <div class="advanced-search-grid">
                <div class="search-field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter title">
                </div>
                <div class="search-field">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" placeholder="Enter author">
                </div>
                <div class="search-field">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="Enter subject">
                </div>
                <div class="search-field">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" placeholder="Enter ISBN">
                </div>
                <div class="search-field">
                    <label for="issn">ISSN</label>
                    <input type="text" id="issn" name="issn" placeholder="Enter ISSN">
                </div>
                <div class="search-field">
                    <label for="series">Series</label>
                    <input type="text" id="series" name="series" placeholder="Enter series">
                </div>
                <div class="search-field">
                    <label for="call_number">Call Number</label>
                    <input type="text" id="call_number" name="call_number" placeholder="Enter call number">
                </div>
            </div>
            <button type="submit" class="search-button" name="advanced_search">
                <i class="fas fa-search"></i> Advanced Search
            </button>
        </form>
    </div>

    <div class="pro-navigation">
        <a href="home_user.php" onclick="navigateToNilam(event)"><i class=" fas fa-book"></i>My Library</a>
        <a href="myprofile.php" class="active"><i class="fas fa-user"></i>My Profile</a>
        <a href="myNILAM.php" onclick="navigateToNilam(event)"><i class="fas fa-tasks"></i>My NILAM</a>
    </div>

    <div class="content">
        <div class="title">
            <div class="icon-container">
                <?php
                $default_image = 'path/to/default/user-icon.png';
                $profile_image_path = !empty($profile_image) ? "uploads/users/" . $profile_image : $default_image;
                ?>
                <img src="<?php echo htmlspecialchars($profile_image_path); ?>" alt="Profile Icon" class="profile-icon" />
            </div>
            <header class="header">
                <h1 class="h1">My Profile</h1>
                <p class="description">You can update your profile here</p>
            </header>
        </div>

        <main>
            <form action="myprofile.php" method="POST" id="survey-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div>
                        <label for="name" class="label-main">Name</label>
                        <input id="name" class="input-text" type="text" name="full_name"
                            value="<?php echo htmlspecialchars($fn); ?>" placeholder="Enter your name" required>
                    </div>
                    <div>
                        <label for="email" class="label-main">Email</label>
                        <input id="email" class="input-text" type="email" name="email"
                            value="<?php echo htmlspecialchars($em); ?>" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="contact" class="label-main">Contact</label>
                        <input id="contact" class="input-text" type="text" name="contact"
                            value="<?php echo htmlspecialchars($ct); ?>" placeholder="Enter your contact number">
                    </div>
                    <div>
                        <label for="address" class="label-main">Address</label>
                        <input id="address" class="input-text" type="text" name="address"
                            value="<?php echo htmlspecialchars($ad); ?>" placeholder="Enter your address">
                    </div>
                </div>

                <div class="custom-dropdown">
                    <label for="dropdown" class="label-main">Your Course</label>
                    <select id="dropdown" name="course" class="input-text" required>
                        <option value="">Select your course</option>
                        <option value="KPD" <?php echo ($cs == 'KPD') ? 'selected' : ''; ?>>KPD</option>
                        <option value="KSK" <?php echo ($cs == 'KSK') ? 'selected' : ''; ?>>KSK</option>
                        <option value="MTA" <?php echo ($cs == 'MTA') ? 'selected' : ''; ?>>MTA</option>
                        <option value="MPP" <?php echo ($cs == 'MPP') ? 'selected' : ''; ?>>MPP</option>
                        <option value="HSK" <?php echo ($cs == 'HSK') ? 'selected' : ''; ?>>HSK</option>
                        <option value="HFD" <?php echo ($cs == 'HFD') ? 'selected' : ''; ?>>HFD</option>
                    </select>
                </div>

                <div class="form-row">
                    <div>
                        <label for="level" class="label-main">Level</label>
                        <input id="level" class="input-text" type="text" name="level"
                            value="<?php echo htmlspecialchars($lv); ?>" placeholder="Enter your level">
                    </div>
                    <div>
                        <label for="profile_image" class="label-main">Profile Image</label>
                        <input id="profile_image" class="input-text" type="file" name="profile_image"
                            accept="image/jpeg,image/png,image/gif">
                    </div>
                </div>

                <hr class="separator">

                <div class="form-row">
                    <div>
                        <label for="username" class="label-main">Username</label>
                        <input id="username" class="input-text" type="text" name="username"
                            value="<?php echo htmlspecialchars($us); ?>" placeholder="Enter username" required>
                    </div>
                    <div>
                        <label for="password" class="label-main">Password</label>
                        <input id="password" class="input-text" type="text" name="password"
                            value="<?php echo htmlspecialchars($ps); ?>" placeholder="Enter password" required>
                    </div>
                </div>

                <button id="submit" class="button" type="submit">Update Profile</button>
            </form>
        </main>
    </div>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Perpustakaan Al-Hazen is dedicated to providing quality resources and services to support learning, teaching, and research needs of our community.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Contact Info</h3>
                <div class="contact-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>Jalan Kajang-dengkil, Batu 24, 43000 Dengkil, Selangor</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone"></i>
                    <p>+60 3-8768 1111</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <p>info@perpustakaan-alhazen.edu.my</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="#">Home</a>
                <a href="#">Library Catalog</a>
                <a href="#">E-Resources</a>
                <a href="#">Research Guides</a>
                <a href="#">Library Services</a>
            </div>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.7060019169876!2d101.72446527449983!3d2.9007907546301506!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cdc83f748b3aa3%3A0x6727e45b4e8e3654!2sSepang%20Vocational%20College!5e0!3m2!1sen!2smy!4v1733905405383!5m2!1sen!2smy"></iframe>
        </div>

        <div class="footer-bottom">
            <p>© 2024 Perpustakaan Al-Hazen. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Section Toggle Function
        function toggleSection(section) {
            const allBooks = document.querySelectorAll('.book-card');
            const toggleButtons = document.querySelectorAll('.toggle-btn');

            if (section === 'all') {
                allBooks.forEach(book => book.style.display = 'block');
            } else {
                allBooks.forEach(book => {
                    if (book.dataset.category === section) {
                        book.style.display = 'block';
                    } else {
                        book.style.display = 'none';
                    }
                });
            }
        };

        // Initialize: Show all books
        toggleSection('all');

        function toggleAdvancedSearch() {
            const advancedSearch = document.querySelector('.advanced-search');
            const toggleText = document.getElementById('toggleText');
            advancedSearch.classList.toggle('show');
            toggleText.textContent = advancedSearch.classList.contains('show') ?
                'Hide Advanced Search' :
                'Show Advanced Search';
        }

        function toggleFavoritesPopup() {
            const popup = document.getElementById("favoritesPopup");
            popup.classList.toggle("open");

            if (popup.classList.contains("open")) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = 'auto';
            }
        }

        // Close favorites popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById("favoritesPopup");
            const favouriteLink = document.getElementById("favouriteLink");

            if (!popup.contains(event.target) && event.target !== favouriteLink && !event.target.closest('#favouriteLink')) {
                popup.classList.remove("open");
                document.body.style.overflow = 'auto';
            }
        });

        // Close favorites popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById("favoritesPopup");
            const favouriteLink = document.getElementById("favouriteLink");

            if (!popup.contains(event.target) && event.target !== favouriteLink && !event.target.closest('#favouriteLink')) {
                popup.classList.remove("open");
                document.body.style.overflow = 'auto';
            }
        });

        // Stop propagation on popup click to prevent closing
        document.getElementById("favoritesPopup").addEventListener('click', function(event) {
            event.stopPropagation();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open");

            if (sidebar.classList.contains("open")) {
                loadCollections(); // Load fresh data when opening
                document.body.classList.add("sidebar-open");
            } else {
                document.body.classList.remove("sidebar-open");
            }
        }

        function toggleSection(category) {
            // Get all buttons and remove the 'active' class
            const buttons = document.querySelectorAll('.toggle-btn');
            buttons.forEach(button => button.classList.remove('active'));

            // Add the 'active' class to the clicked button
            document.querySelector(`.toggle-btn[onclick="toggleSection('${category}')"]`).classList.add('active');

            // Get all book cards and toggle visibility based on the category
            const bookCards = document.querySelectorAll('.book-card');
            bookCards.forEach(card => {
                card.style.display = card.dataset.category === category ? 'block' : 'none';
            });
        }

        function toggleFavoritesPopup() {
            const popup = document.getElementById("favoritesPopup");
            popup.classList.toggle("open");

            if (popup.classList.contains("open")) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = 'auto';
            }
        }

        // Close favorites popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById("favoritesPopup");
            const favouriteLink = document.getElementById("favouriteLink");

            if (!popup.contains(event.target) && event.target !== favouriteLink && !event.target.closest('#favouriteLink')) {
                popup.classList.remove("open");
                document.body.style.overflow = 'auto';
            }
        });

        // Close favorites popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById("favoritesPopup");
            const favouriteLink = document.getElementById("favouriteLink");

            if (!popup.contains(event.target) && event.target !== favouriteLink && !event.target.closest('#favouriteLink')) {
                popup.classList.remove("open");
                document.body.style.overflow = 'auto';
            }
        });

        // Stop propagation on popup click to prevent closing
        document.getElementById("favoritesPopup").addEventListener('click', function(event) {
            event.stopPropagation();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open");

            if (sidebar.classList.contains("open")) {
                loadCollections(); // Load fresh data when opening
                document.body.classList.add("sidebar-open");
            } else {
                document.body.classList.remove("sidebar-open");
            }
        }

        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,ms', // English and Malay
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>

</html>