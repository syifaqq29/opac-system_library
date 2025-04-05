<?php
include('C:\xampp\htdocs\opac-system_library\config.php');

$id_book = $_GET['id_book'] ?? null;

// Fetch the specific borrowed/returned book data
if ($id_book) {
    $query = "
    SELECT 'borrowed' AS type, id_borrowed, id_book, user_id, borrowed_date AS date, due_date, returned_date, status 
    FROM borrowed
    WHERE id_book = '$id_book'
    ";

    $result = mysqli_query($connect, $query);
    $bookData = mysqli_fetch_assoc($result);
} else {
    die("Book ID is required.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Book Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .detail-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .status {
            font-weight: bold;
            color: #28a745;
        }

        @media print {
            body {
                margin: 0;
                background: none;
                color: #000;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Book Borrowing Details</h1>
        <div class="details">
            <div class="detail-item"><strong>Book ID:</strong> <?php echo htmlspecialchars($bookData['id_book']); ?></div>
            <div class="detail-item"><strong>User ID:</strong> <?php echo htmlspecialchars($bookData['user_id']); ?></div>
            <div class="detail-item"><strong>Borrowed Date:</strong> <?php echo htmlspecialchars($bookData['borrowed_date'] ?? '-'); ?></div>
            <div class="detail-item"><strong>Due Date:</strong> <?php echo htmlspecialchars($bookData['due_date'] ?? '-'); ?></div>
            <div class="detail-item"><strong>Returned Date:</strong> <?php echo htmlspecialchars($bookData['returned_date'] ?? '-'); ?></div>
            <div class="detail-item"><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($bookData['status']); ?></span></div>
        </div>
        <button class="no-print" onclick="window.print();" style="margin-top: 20px; padding: 10px 15px; border: none; border-radius: 5px; background: #007bff; color: white; cursor: pointer;">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</body>

</html>