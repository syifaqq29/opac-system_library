<?php
include('C:\xampp\htdocs\opac-system_library\config.php');

$id_book = $_GET['id_book'] ?? null;

// Fetch the specific book data
if ($id_book) {
    $query = "
        SELECT id_book, title, author, isbn, subject, issn, series, call_number, image_url
        FROM books
        WHERE id_book = ?
    ";

    // Prepare the statement
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_book);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bookData = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
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

        .image-container {
            text-align: center;
            margin: 20px 0;
        }

        .book-cover {
            width: 200px;
            /* Set the desired width */
            height: 300px;
            /* Set the desired height */
            object-fit: cover;
            /* Ensure the aspect ratio is maintained */
            border-radius: 5px;
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
        <h1>Book Details</h1>

        <div class="image-container">
            <img src="<?php echo htmlspecialchars($bookData['image_url'] ?? 'default-image.jpg'); ?>" alt="Book Cover" class="book-cover">
        </div>

        <div class="details">
            <div class="detail-item"><strong>Book ID:</strong> <?php echo htmlspecialchars($bookData['id_book'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Title:</strong> <?php echo htmlspecialchars($bookData['title'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Author:</strong> <?php echo htmlspecialchars($bookData['author'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>ISBN:</strong> <?php echo htmlspecialchars($bookData['isbn'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Subject:</strong> <?php echo htmlspecialchars($bookData['subject'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>ISSN:</strong> <?php echo htmlspecialchars($bookData['issn'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Series:</strong> <?php echo htmlspecialchars($bookData['series'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Call Number:</strong> <?php echo htmlspecialchars($bookData['call_number'] ?? 'N/A'); ?></div>
        </div>
        <button class="no-print" onclick="window.print();" style="margin-top: 20px; padding: 10px 15px; border: none; border-radius: 5px; background: #007bff; color: white; cursor: pointer;">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</body>

</html>