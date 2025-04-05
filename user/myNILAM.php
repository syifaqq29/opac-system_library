<?php
include 'config.php';
session_start();

// Get the username from the session
$userName = $_SESSION['username'];
$wishlist = [];
$favorites = [];

if ($conn) {
    // Get user ID based on the username
    $userIdQuery = "SELECT id_user FROM users WHERE username = ?";
    $userStmt = $conn->prepare($userIdQuery);
    $userStmt->bind_param("s", $userName);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userRow = $userResult->fetch_assoc()) {
        $userId = $userRow['id_user'];
        $_SESSION['user_id'] = $userId; // Added this line to store user_id in session

        // Fetch wishlist items for the user
        $wishlistQuery = "SELECT 
                        books.id_book,
                        books.title, 
                        books.author, 
                        books.image_url,
                        books.availability,
                        CASE 
                            WHEN reserved.id_book IS NOT NULL THEN 'Reserved'
                            ELSE 'Available'
                        END as reserve_status
                      FROM wishlist
                      JOIN books ON wishlist.book_id = books.id_book
                      LEFT JOIN reserved ON books.id_book = reserved.id_book AND reserved.user_id = ?
                      WHERE wishlist.user_id = ?";
        $stmt = $conn->prepare($wishlistQuery);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $wishlist[] = $row;
        }

        // Fetch favorite items for the user
        $favoritesQuery = "SELECT books.title, books.author, books.image_url, books.availability, favorites.added_at 
                        FROM favorites
                        JOIN books ON favorites.book_id = books.id_book
                        WHERE favorites.user_id = ?
                        ORDER BY favorites.added_at DESC";
        $stmt = $conn->prepare($favoritesQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }

        // Fetch reserved books for the logged-in user
        $reservedQuery = $conn->prepare("
        SELECT books.*, reserved.id_reserved, reserved.reserved_date 
        FROM books 
        INNER JOIN reserved ON books.id_book = reserved.id_book 
        WHERE reserved.user_id = ?
    ");
        $reservedQuery->bind_param("i", $userId); // Changed to use userId directly
        $reservedQuery->execute();
        $reservedBooks = $reservedQuery->get_result();

        // Fetch borrowed books for the logged-in user
        $borrowedQuery = $conn->prepare("
        SELECT books.*, borrowed.id_borrowed, borrowed.borrowed_date 
        FROM books 
        INNER JOIN borrowed ON books.id_book = borrowed.id_book 
        WHERE borrowed.user_id = ?
    ");
        $borrowedQuery->bind_param("i", $userId); // Changed to use userId directly
        $borrowedQuery->execute();
        $borrowedBooks = $borrowedQuery->get_result();
    }

    $userStmt->close();
}

// Handle book reservation (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookId = $_POST['book_id'];
    $userId = $_SESSION['user_id'];

    // Set the response header to JSON
    header('Content-Type: application/json');

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if the book is already reserved
        $checkReserved = $conn->prepare("SELECT id_book FROM reserved WHERE id_book = ?");
        $checkReserved->bind_param("i", $bookId);
        $checkReserved->execute();
        $result = $checkReserved->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('This book is already reserved');
        }

        // Check book availability
        $checkAvailable = $conn->prepare("SELECT availability FROM books WHERE id_book = ?");
        $checkAvailable->bind_param("i", $bookId);
        $checkAvailable->execute();
        $availResult = $checkAvailable->get_result();

        if ($availResult->num_rows === 0) {
            throw new Exception('Book not found');
        }

        $book = $availResult->fetch_assoc();
        if ($book['availability'] !== 'Available') {
            throw new Exception('This book is not available for reservation');
        }

        // Insert reservation
        $insertReserve = $conn->prepare("INSERT INTO reserved (id_book, user_id, reserved_date) VALUES (?, ?, NOW())");
        $insertReserve->bind_param("ii", $bookId, $userId);
        if (!$insertReserve->execute()) {
            throw new Exception('Failed to create reservation');
        }

        // Update book availability
        $updateBook = $conn->prepare("UPDATE books SET availability = 'Reserved' WHERE id_book = ?");
        $updateBook->bind_param("i", $bookId);
        if (!$updateBook->execute()) {
            throw new Exception('Failed to update book status');
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Book reserved successfully']);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_reservation'])) {
    $reservationId = $_POST['reservation_id'];
    $userId = $_SESSION['user_id']; // Get the current user's ID

    // Start transaction
    $conn->begin_transaction();

    try {
        // First, get the book ID from the reservation
        $getBookQuery = $conn->prepare("
          SELECT id_book 
          FROM reserved 
          WHERE id_reserved = ? AND user_id = ?
      ");
        $getBookQuery->bind_param("ii", $reservationId, $userId);
        $getBookQuery->execute();
        $bookResult = $getBookQuery->get_result();

        if ($bookResult->num_rows === 0) {
            throw new Exception('Reservation not found');
        }

        $bookData = $bookResult->fetch_assoc();
        $bookId = $bookData['id_book'];

        // Delete the reservation
        $deleteReserve = $conn->prepare("
          DELETE FROM reserved 
          WHERE id_reserved = ? AND user_id = ?
      ");
        $deleteReserve->bind_param("ii", $reservationId, $userId);
        if (!$deleteReserve->execute()) {
            throw new Exception('Failed to remove reservation');
        }

        // Update book availability back to 'Available'
        $updateBook = $conn->prepare("
          UPDATE books 
          SET availability = 'Available' 
          WHERE id_book = ?
      ");
        $updateBook->bind_param("i", $bookId);
        if (!$updateBook->execute()) {
            throw new Exception('Failed to update book status');
        }

        $conn->commit();

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Reservation removed successfully']);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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

        :root {
            --primary-color: #262160;
            --secondary-color: #4CAF50;
            --background-color: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        /* Container and Card Layout */
        .container {
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 1200px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 10px;
        }

        /* Stats Grid */
        .stats-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            flex: 1;
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card i {
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 30px;
            font-weight: bold;
            color: #333;
        }

        .blue {
            background: #cce7ff;
        }

        .green {
            background: #e8f7e8;
        }

        .purple {
            background: #f1e6f7;
        }

        /* Form Section */
        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Form Grid */
        .form-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .input-group {
            flex: 1;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            min-height: 100px;
        }

        button.btn {
            background: #262160;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button.btn:hover {
            background: #1a1640;
        }

        /* Books List */
        .books-list {
            margin-top: 40px;
        }

        .books-list #booksList {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-top: 10px;
        }


        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .stat-card {
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card.blue {
            background-color: #E3F2FD;
        }

        .stat-card.green {
            background-color: #E8F5E9;
        }

        .stat-card.purple {
            background-color: #F3E5F5;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .form-section {
            padding: 1.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .input-group {
            margin-bottom: 1rem;
        }


        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #1a174d;
        }

        .books-list {
            padding: 1.5rem;
        }

        .book-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: box-shadow 0.3s;
        }

        .book-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .book-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.5rem;
        }

        .moral-tag {
            background: #E3F2FD;
            color: #1976D2;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
        }

        .reflection-text {
            color: #666;
            margin-top: 0.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 1rem;
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

        .reserve-btn:hover {
            background-color: #45a049 !important;
        }

        .reserve-btn:disabled {
            background-color: #cccccc !important;
            cursor: not-allowed;
        }

        /* Sidebar styles */
        .sidebar {
            height: 100%;
            width: 250px;
            /* Width of the sidebar */
            position: fixed;
            /* Stay in place */
            z-index: 2000;
            /* Sit on top of the page content */
            top: 0;
            right: -250px;
            /* Hide sidebar off-screen to the right */
            background-color: #262160;
            /* Sidebar background */
            overflow-x: hidden;
            /* Disable horizontal scroll */
            transition: 0.3s;
            /* Transition for opening/closing */
            padding-top: 60px;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 25px;
            /* Font size */
            color: white;
            /* Text color */
            display: block;
            /* Block elements */
            transition: 0.3s;
            /* Transition */
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            /* Hover effect */
        }

        .wishlist-items {
            padding: 10px 15px;
            /* Padding for items */
        }

        /* You can style the items further as needed */
        .wishlist-items p {
            color: white;
            /* Text color */
            margin: 0;
            /* Remove margin */
        }

        /* Close button */
        .closebtn {
            font-size: 36px;
            /* Close button size */
            margin-left: 15px;
            /* Margin */
        }

        /* Show sidebar */
        .sidebar.open {
            right: 0;
            /* Show sidebar */
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

        /* Utility Classes */
        .hidden {
            display: none;
        }

        .clearfix {
            clear: both;
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
        <a href="home_user.php" onclick="navigateToLibrary(event)"><i class="fas fa-book"></i>My Library</a>
        <a href="myprofile.php" onclick="navigateToProfile(event)"><i class="fas fa-user"></i>My Profile</a>
        <a href="myNILAM.php" class="active"><i class="fas fa-tasks"></i>My NILAM</a>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">
                    <i class="fas fa-book-reader"></i>
                    NILAM Reading Tracker
                </h1>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <i class="fas fa-books fa-2x"></i>
                    <div class="stat-value" id="totalBooks">0</div>
                    <div>Total Books</div>
                </div>
                <div class="stat-card green">
                    <i class="fas fa-check-circle fa-2x"></i>
                    <div class="stat-value" id="completedBooks">0</div>
                    <div>Completed</div>
                </div>
                <div class="stat-card purple">
                    <i class="fas fa-hourglass-half fa-2x"></i>
                    <div class="stat-value" id="inProgressBooks">0</div>
                    <div>In Progress</div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">Add New Book</h2>
                <form id="bookForm">
                    <div class="form-grid">
                        <div class="input-group">
                            <input type="text" id="bookTitle" placeholder="Book Title" required>
                        </div>
                        <div class="input-group">
                            <input type="text" id="bookAuthor" placeholder="Author" required>
                        </div>
                        <div class="input-group">
                            <select id="moralValue" required>
                                <option value="">Select Moral Value</option>
                                <option value="Responsibility">Responsibility</option>
                                <option value="Honesty">Honesty</option>
                                <option value="Perseverance">Perseverance</option>
                                <option value="Kindness">Kindness</option>
                                <option value="Respect">Respect</option>
                                <option value="Cooperation">Cooperation</option>
                                <option value="Leadership">Leadership</option>
                                <option value="Empathy">Empathy</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <textarea id="reflection" placeholder="Your Reflection and Moral Lessons Learned..." required></textarea>
                    </div>
                    <button type="submit" class="btn">Add to NILAM Journal</button>
                </form>
            </div>

            <div class="books-list">
                <h2 class="section-title">My Reading Journey</h2>
                <div id="booksList"></div>
            </div>
        </div>
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


    <script>
        class NILAMTracker {
            constructor() {
                this.books = JSON.parse(localStorage.getItem('nilamBooks')) || [];
                this.form = document.getElementById('bookForm');
                this.booksList = document.getElementById('booksList');

                this.initializeEventListeners();
                this.updateDisplay();
            }

            initializeEventListeners() {
                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.addBook();
                });
            }

            addBook() {
                const book = {
                    id: Date.now(),
                    title: document.getElementById('bookTitle').value,
                    author: document.getElementById('bookAuthor').value,
                    moralValue: document.getElementById('moralValue').value,
                    reflection: document.getElementById('reflection').value,
                    dateAdded: new Date().toISOString(),
                    status: 'in-progress'
                };

                this.books.push(book);
                this.saveToLocalStorage();
                this.updateDisplay();
                this.form.reset();
            }

            createBookElement(book) {
                const bookCard = document.createElement('div');
                bookCard.className = 'book-card';
                bookCard.innerHTML = `
                    <div class="book-header">
                        <div>
                            <h3>${book.title}</h3>
                            <p>by ${book.author}</p>
                        </div>
                        <span class="moral-tag">${book.moralValue}</span>
                    </div>
                    <p class="reflection-text">${book.reflection}</p>
                    <div style="margin-top: 1rem;">
                        <button onclick="nilamTracker.toggleStatus('${book.id}')" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                            ${book.status === 'completed' ? 'Mark In Progress' : 'Mark Complete'}
                        </button>
                        <button onclick="nilamTracker.deleteBook('${book.id}')" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem; background-color: #dc3545; margin-left: 0.5rem;">
                            Delete
                        </button>
                    </div>
                `;
                return bookCard;
            }

            updateStats() {
                document.getElementById('totalBooks').textContent = this.books.length;
                document.getElementById('completedBooks').textContent =
                    this.books.filter(book => book.status === 'completed').length;
                document.getElementById('inProgressBooks').textContent =
                    this.books.filter(book => book.status === 'in-progress').length;
            }

            updateDisplay() {
                this.booksList.innerHTML = '';
                this.books.forEach(book => {
                    this.booksList.appendChild(this.createBookElement(book));
                });
                this.updateStats();
            }

            toggleStatus(id) {
                const book = this.books.find(b => b.id === parseInt(id));
                if (book) {
                    book.status = book.status === 'completed' ? 'in-progress' : 'completed';
                    this.saveToLocalStorage();
                    this.updateDisplay();
                }
            }

            deleteBook(id) {
                if (confirm('Are you sure you want to delete this book?')) {
                    this.books = this.books.filter(b => b.id !== parseInt(id));
                    this.saveToLocalStorage();
                    this.updateDisplay();
                }
            }

            saveToLocalStorage() {
                localStorage.setItem('nilamBooks', JSON.stringify(this.books));
            }
        }

        // Initialize the NILAM tracker
        const nilamTracker = new NILAMTracker();

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

        function reserveBook(bookId) {
            if (confirm('Do you want to reserve this book?')) {
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `book_id=${bookId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Book reserved successfully!');
                            // Refresh the page to show updated status
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to reserve book');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while reserving the book');
                    });
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