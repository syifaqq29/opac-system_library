<?php
include('C:\xampp\htdocs\opac-system_library\config.php');

$id_user = $_GET['id_user'] ?? null;

// Fetch the specific user data
if ($id_user) {
    $query = "
        SELECT id_user, full_name, level, email, contact
        FROM users
        WHERE id_user = ?
    ";

    // Prepare the statement
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userData = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
} else {
    die("User ID is required.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print User Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
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
        <h1>User Details</h1>

        <div class="details">
            <div class="detail-item"><strong>User ID:</strong> <?php echo htmlspecialchars($userData['id_user'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Name:</strong> <?php echo htmlspecialchars($userData['full_name'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Level:</strong> <?php echo htmlspecialchars($userData['level'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Email:</strong> <?php echo htmlspecialchars($userData['email'] ?? 'N/A'); ?></div>
            <div class="detail-item"><strong>Contact:</strong> <?php echo htmlspecialchars($userData['contact'] ?? 'N/A'); ?></div>
        </div>
        <button class="no-print" onclick="window.print();" style="margin-top: 20px; padding: 10px 15px; border: none; border-radius: 5px; background: #007bff; color: white; cursor: pointer;">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</body>

</html>