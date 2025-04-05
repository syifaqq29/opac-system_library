<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'User') {
  echo "Please log in to access this page.";
  exit();
}

// Get the username from the session
$userName = $_SESSION['username'];
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
  <title>FAQ</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style type="text/css">
    body {
      font-family: 'Arial', sans-serif;
      line-height: 1.6;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
      color: #333;
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

    .no-results {
      text-align: center;
      padding: 20px;
      color: #666;
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 30px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1,
    h2,
    h3 {
      color: #262160;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
    }

    .facility-description {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 40px auto;
      color: #555;
      font-size: 1.1em;
      line-height: 1.6;
      padding: 0 20px;
    }

    .Guide {
      display: flex;
      justify-content: center;
      /* Center buttons horizontally */
      align-items: center;
      color: #555;
    }

    .guide-button {
      background-color: #262160;
      /* Green background */
      color: white;
      /* White text */
      border: none;
      /* No borders */
      border-radius: 5px;
      /* Rounded corners */
      padding: 10px 20px;
      /* Some padding */
      cursor: pointer;
      /* Pointer cursor on hover */
      font-size: 16px;
      /* Bigger text */
      margin: 0 10px;
      /* Space between buttons */
      transition: background-color 0.3s ease;
      /* Transition for hover effect */
    }

    .guide-button:hover {
      background-color: #938dd8;
      /* Darker color on hover */
    }

    footer {
      text-align: center;
      padding: 20px 0;
      text-decoration: none;
      color: #262160;
      font-size: 0.9em;
      width: 100%;
    }

    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }
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
    <img src="banner.png" alt="KVS library Banner">
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

  <div class="container">

    <header>
      <h1>PSS AlHazen Book Support Guide</h1>
    </header>

    <div class="Guide">
      <button class="guide-button" onclick="showSection('beginner-guide')">OPAC User Guide</button>
      <button class="guide-button" onclick="showSection('searching-books')">Searching for Books</button>
      <button class="guide-button" onclick="showSection('account-issues')">Account Issues</button>
    </div>

    <section id="beginner-guide">
      <h2>Welcome to PSSAlHazen's OPAC User Guide!</h2>
      <p>This guide will explain how to use the OPAC system to search for and reserve books online, along with information on managing your account.</p>
      <h3>Frequently Asked Questions</h3>
      <h4>Q: What is an OPAC system?</h4>
      <p>A: An OPAC (Online Public Access Catalog) is a database of materials held by a library that allows users to search for and locate books, journals, and other resources online.</p>
      <h4>Q: How do I search for a book using OPAC?</h4>
      <p>A: To search for a book, visit the OPAC homepage, enter the title or author in the search bar, and browse the results. You can filter by availability or format.</p>
      <h4>Q: Can I reserve a book through OPAC?</h4>
      <p>A: Yes, if a book is checked out or unavailable, you can place a reservation by logging into your account and selecting the reservation option.</p>
    </section>

    <section id="searching-books">
      <h2>Searching for Books</h2>
      <h4>Q: How can I effectively search for books in the OPAC?</h4>
      <p>A: Use the following tips to enhance your search:</p>
      <ol>
        <li>Use specific keywords, such as the exact title or author's name.</li>
        <li>Utilize filters for publication date, format, or subject.</li>
        <li>Check the availability status of each title listed.</li>
        <li>If you cannot find the book, check the spelling or try alternative titles.</li>
      </ol>
      <h4>Q: What should I do if the book I want is not available?</h4>
      <p>A: If a book is not available, you can check if it's on hold or consider requesting it through interlibrary loan services.</p>
    </section>

    <section id="account-issues">
      <h2>Account Issues</h2>
      <h4>Q: I forgot my password. What should I do?</h4>
      <p>A: Go to the password recovery page, enter your email address, and follow the instructions sent to your email to reset your password.</p>
      <h4>Q: How can I update my account information?</h4>
      <p>A: To update your account details, log in to your OPAC account and navigate to the profile section. Here, you can edit your personal information.</p>
      <h4>Q: Why can't I access my account?</h4>
      <p>A: If you're having trouble accessing your account, please contact your library's support team for assistance.</p>
    </section>

    <footer>
      © 2024 Perpustakaan Al-Hazen. All rights reserved.
      <p><a href="privacy_policy.php">Privacy Policy</a> | <a href="terms_conditions.php">Terms & Conditions</a></p>
    </footer>

    <script>
      function toggleAdvancedSearch() {
        const advancedSearch = document.querySelector('.advanced-search');
        const toggleText = document.getElementById('toggleText');
        advancedSearch.classList.toggle('show');
        toggleText.textContent = advancedSearch.classList.contains('show') ?
          'Hide Advanced Search' :
          'Show Advanced Search';
      }

      function showSection(sectionId) {
        const sections = document.querySelectorAll('section');
        sections.forEach(section => {
          section.style.display = 'none';
        });
        const selectedSection = document.getElementById(sectionId);
        selectedSection.style.display = 'block';
      }

      // Show the beginner guide by default
      showSection('beginner-guide');

      // Login status variable (replace with your actual login logic)
      let isLoggedIn = false; // Set to true if the user is logged in

      // Function to show a login alert
      function showLoginAlert(event) {
        if (!isLoggedIn) {
          event.preventDefault(); // Prevent navigation
          alert("Please log in to access this feature."); // Alert message
        }
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