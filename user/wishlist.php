<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'User') {
    echo json_encode(['success' => false, 'message' => 'Please log in to access this page.']);
    exit();
}

// Get the user ID from the session
$userId = $_SESSION['user_id'];

// Handle CRUD operations based on the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch wishlist items for the logged-in user
        $wishlistQuery = $connect->prepare("
            SELECT books.id_book, books.title, books.image_url 
            FROM books 
            INNER JOIN wishlist ON books.id_book = wishlist.book_id 
            WHERE wishlist.user_id = ?
        ");
        $wishlistQuery->bind_param("i", $userId);
        $wishlistQuery->execute();
        $wishlistResult = $wishlistQuery->get_result();

        // Prepare the response
        $wishlistItems = [];
        while ($row = $wishlistResult->fetch_assoc()) {
            $wishlistItems[] = $row;
        }

        echo json_encode(['success' => true, 'wishlist' => $wishlistItems]);
        break;

    case 'POST':
        // Add a book to the wishlist
        $bookId = $_POST['book_id'];
        $wishlistInsert = $connect->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
        $wishlistInsert->bind_param("ii", $userId, $bookId);
        if ($wishlistInsert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Book added to wishlist.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add book to wishlist.']);
        }
        break;

    case 'DELETE':
        // Remove a book from the wishlist
        parse_str(file_get_contents("php://input"), $_DELETE);
        $bookId = $_DELETE['book_id'];
        $wishlistDelete = $connect->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
        $wishlistDelete->bind_param("ii", $userId, $bookId);
        if ($wishlistDelete->execute()) {
            echo json_encode(['success' => true, 'message' => 'Book removed from wishlist.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove book from wishlist.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        break;
}
