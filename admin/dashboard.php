<?php
session_start();

// Include necessary files
include 'config.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'Admin') {
    echo "Please log in to access this page.";
    exit();
}

$userName = $_SESSION['username'];

// Process form submission for adding borrowed books
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $id_book = mysqli_real_escape_string($connect, $_POST['id_book']);
    $user_id = mysqli_real_escape_string($connect, $_POST['user_id']);
    $borrowed_date = mysqli_real_escape_string($connect, $_POST['borrowed_date']);
    $due_date = mysqli_real_escape_string($connect, $_POST['due_date']);

    // Set initial status as Borrowed
    $status = 'Borrowed';

    // Insert query
    $insert_query = "INSERT INTO borrowed (id_book, user_id, borrowed_date, due_date, status) 
                    VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connect, $insert_query);
    mysqli_stmt_bind_param($stmt, "sssss", $id_book, $user_id, $borrowed_date, $due_date, $status);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Book borrowed successfully!');
            window.location.href = 'dashboard.php';
        </script>";
    } else {
        echo "<script>
            alert('Error adding borrowed book: " . mysqli_error($connect) . "');
            window.location.href = 'dashboard.php';
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// Query to get total users
$users_query = "SELECT COUNT(*) as id_user FROM users";
$users_result = mysqli_query($connect, $users_query);
$total_users = mysqli_fetch_assoc($users_result)['id_user'];

// Query to get total books (distinct books that have been borrowed)
$books_query = "SELECT COUNT(DISTINCT id_book) as total_books FROM books";
$books_result = mysqli_query($connect, $books_query);
$total_books = mysqli_fetch_assoc($books_result)['total_books'];

// Query to get currently borrowed books (status = 'Borrowed')
$borrowed_query = "SELECT COUNT(*) as borrowed_books FROM borrowed WHERE status = 'Borrowed'";
$borrowed_result = mysqli_query($connect, $borrowed_query);
$borrowed_books = mysqli_fetch_assoc($borrowed_result)['borrowed_books'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            height: 100vh;
        }

        .container {
            display: flex;
            width: 100%;
            transition: margin-left 0.3s ease-in-out;
            margin: 0;
            /* Add this line */
            padding: 0;
            /* Add this line */
            max-width: none;
            /* Add this line to override Bootstrap's max-width */
        }

        .sidebar {
            width: 280px;
            background: #262160;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 20px;
            position: fixed;
            height: 100vh;
            z-index: 999;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }

        .sidebar.shrink {
            width: 70px;
            padding: 20px 10px;
        }

        .sidebar img {
            width: 100%;
            height: auto;
            margin-bottom: 30px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar.shrink img {
            width: 0;
            height: 0;
            margin: 0;
            opacity: 0;
        }

        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 40px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .sidebar.shrink h2 {
            font-size: 0;
            margin: 0;
            opacity: 0;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 15px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .sidebar.shrink a {
            justify-content: center;
            padding: 15px 0;
        }

        .sidebar a i {
            font-size: 20px;
            min-width: 25px;
            text-align: center;
        }

        .sidebar a .text {
            transition: opacity 0.3s;
            font-size: 16px;
        }

        .sidebar.shrink a .text {
            display: none;
        }

        .sidebar a:hover {
            background: #8983cf;
            color: #262160;
        }

        .sidebar a.active {
            background: #8983cf;
            color: #262160;
        }

        .main-content {
            margin-left: 280px;
            /* Changed from 320px */
            flex: 1;
            padding: 20px;
            transition: all 0.3s ease;
            margin-top: 20px;
            width: calc(100% - 280px);
            /* Add this line */
        }

        .main-content.shrink {
            margin-left: 70px;
            /* Changed from 90px */
            width: calc(100% - 70px);
            /* Add this line */
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .left-section {
            display: flex;
            align-items: center;
        }

        .toggle-btn {
            cursor: pointer;
            font-size: 24px;
            color: #262160;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            color: #262160;
            background-color: #f0f2f5;
        }

        .icons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .icon-wrapper {
            display: flex;
            align-items: center;
            padding: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 5px;
        }

        .icon-wrapper:hover {
            color: #262160;
            background-color: #f0f2f5;
        }

        .notification-icon {
            font-size: 20px;
            color: #262160;
        }

        .user-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #262160;
        }

        .user-icon i {
            font-size: 24px;
        }

        .user-icon span {
            font-weight: 500;
        }

        .user-icon i {
            font-size: 24px;
        }

        .user-icon span {
            font-weight: 500;
        }

        .toggle-btn {
            cursor: pointer;
            font-size: 24px;
            color: #2b2f5c;
            margin-right: 20px;
        }

        .toggle-btn:hover {
            color: #262160;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card h3 {
            margin: 0;
            color: #262160;
        }

        .card p {
            font-size: 2em;
            color: #262160;
        }

        .dashboard-row {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 20px;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        #dashboard-section {
            display: block;
            /* Show dashboard by default */
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        .chart-container {
            max-width: 48%;
        }

        .notifications {
            max-width: 48%;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #262160;
        }

        .notifications ul {
            list-style: none;
            padding: 0;
        }

        .notifications li {
            margin-bottom: 16px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }

        .show-all {
            display: block;
            text-align: right;
            color: #262160;
            text-decoration: none;
            margin-top: 10px;
        }

        .show-all:hover {
            text-decoration: underline;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease;
        }

        .modal-content {
            border-radius: 0.3rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #262160;
            padding: 1rem;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .modal-profile-settings .modal-content {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            max-width: 500px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-profile-settings .modal-header {
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
            font-size: 1.25rem;
            text-align: center;
        }

        .modal-profile-settings .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .modal-profile-settings .form-control {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .modal-profile-settings .modal-footer {
            display: flex;
            padding-top: 15px;
        }


        .profile-header {
            margin-bottom: 30px;
        }

        .profile-header h3 {
            color: #2b2f5c;
            margin: 15px 0;
            font-size: 1.5rem;
        }

        .modal {
            display: none;
            /* Initially hidden */
        }

        .modal.show {
            display: flex;
            /* Show when the modal has the 'show' class */
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #262160;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 12px;
            color: #666;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #8983cf;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .input-wrapper input:focus {
            border-color: #262160;
            box-shadow: 0 0 0 2px rgba(43, 47, 92, 0.1);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .button-group button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .update-btn {
            background-color: #262160;
            color: white;
        }

        .update-btn:hover {
            background-color: #8983cf;
            transform: translateY(-1px);
        }

        .logout-btn {
            background-color: #262160;
            color: white;
        }

        .logout-btn:hover {
            background-color: #8983cf;
            transform: translateY(-1px);
        }

        /* Custom dropdown styles */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-btn {
            padding: 6px 12px;
            background-color: #262160;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
            /* Make button full width */
        }

        .action-btn:hover {
            background-color: #8983cf;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 4px;
        }

        .dropdown-content a,
        .dropdown-content button {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            background-color: transparent;
            /* Ensure buttons inherit background */
            border: none;
            /* Remove button border */
            cursor: pointer;
            /* Change cursor */
            text-align: center;
            /* Center text */
            width: 100%;
            /* Make links full width */
        }

        .dropdown-content a:hover,
        .dropdown-content button:hover {
            background-color: #f1f1f1;
            /* Hover effect */
        }

        .action-dropdown:hover .dropdown-content {
            display: block;
        }

        .footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            background: #262160;
            color: white;
        }

        .modal-footer .btn-primary {
            background-color: #262160;
            border-color: #262160;
            color: white;
        }

        .modal-footer .btn-primary:hover {
            background-color: #8983cf;
            border-color: #8983cf;
        }

        .modal-footer .btn-danger {
            background-color: #262160;
            border-color: #262160;
            color: white;
        }

        .modal-footer .btn-danger:hover {
            background-color: #8983cf;
            border-color: #8983cf;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="container">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <img src="logo.png" alt="Library Logo">
                <h2>Library Admin</h2>
                <a href="#" class="nav-link active" data-section="dashboard-section">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="text">Dashboard</span>
                </a>
                <a href="#" class="nav-link" data-section="borrow-return-section">
                    <i class="fas fa-book"></i>
                    <span class="text">Borrow & Return</span>
                </a>
                <a href="#" class="nav-link" data-section="manage-books-section">
                    <i class="fas fa-cog"></i>
                    <span class="text">Manage Books</span>
                </a>
                <a href="#" class="nav-link" data-section="manage-users-section">
                    <i class="fas fa-users"></i>
                    <span class="text">Manage Users</span>
                </a>
            </div>

            <div class="main-content" id="mainContent">
                <div class="top-bar">
                    <div class="left-section">
                        <div class="toggle-btn" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </div>
                    </div>
                    <div class="icons">
                        <div class="icon-wrapper notification-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="icon-wrapper user-icon">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($userName); ?></span>
                        </div>
                    </div>
                </div>

                <div id="dashboard-section" class="content-section active">
                    <!-- Dashboard content -->
                    <div class="dashboard">
                        <div class="card">
                            <h3>Total Users</h3>
                            <p><?php echo $total_users; ?></p>
                        </div>
                        <div class="card">
                            <h3>Total Books</h3>
                            <p><?php echo $total_books; ?></p>
                        </div>
                        <div class="card">
                            <h3>Borrowed Books</h3>
                            <p><?php echo $borrowed_books; ?></p>
                        </div>
                    </div>

                    <div class="dashboard-row">
                        <div class="chart-container card">
                            <h3 class="section-title"><i class="fas fa-chart-bar"></i> Bar Chart</h3>
                            <canvas id="barChart"></canvas>
                        </div>

                        <div class="notifications card">
                            <h3 class="section-title"><i class="fas fa-bell"></i> Notifications</h3>
                            <ul>
                                <li><strong>New comment</strong> - 21 days ago</li>
                                <li><strong>New comment</strong> - 21 days ago</li>
                                <li><strong>New comment</strong> - 21 days ago</li>
                            </ul>
                            <a href="#" class="show-all">Show all</a>
                        </div>
                    </div>
                </div>

                <!-- Borrow & Return Section -->
                <div id="borrow-return-section" class="content-section">
                    <h2>Borrow & Return</h2>
                    <div class="card">
                        <?php
                        include('C:\xampp\htdocs\opac-system_library\config.php');

                        // Fetch borrowed and returned books from the database
                        $query = "
    SELECT id_borrowed, id_book, user_id, borrowed_date, due_date, returned_date, status 
    FROM borrowed
    ORDER BY borrowed_date ASC
";

                        $result = mysqli_query($connect, $query);

                        if ($result) {
                            echo "<table class='table table-hover table-striped table-bordered'>";
                            echo "<thead>
            <tr>
                <th>No</th>
                <th>Book ID</th>
                <th>User ID</th>
                <th>Borrowed Date</th>
                <th>Due Date</th>
                <th>Returned Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
          </thead>
          <tbody>";

                            if (mysqli_num_rows($result) > 0) {
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $i++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['id_book'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['user_id'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['borrowed_date'] ?? $row['date'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['due_date'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['returned_date'] ?? '-') . "</td>";
                                    echo "<td>";
                                    echo "<span class='badge " . ($row['status'] === 'Returned' ? 'bg-success' : 'bg-warning') . "'>";
                                    echo htmlspecialchars($row['status'] ?? 'Unknown');
                                    echo "</span></td>";
                                    echo "<td>";
                                    echo "<div class='action-dropdown'>";
                                    echo "<button class='action-btn'>Action</button>";
                                    echo "<div class='dropdown-content'>";
                                    echo "<button class='action-btn edit-borrowed-btn' 
                  data-id-borrowed='" . htmlspecialchars($row['id_borrowed'] ?? '0') . "'
                  data-book-id='" . htmlspecialchars($row['id_book'] ?? '0') . "'
                  data-user-id='" . htmlspecialchars($row['user_id'] ?? '0') . "'
                  data-borrowed-date='" . htmlspecialchars($row['date'] ?? '0000-00-00') . "'
                  data-due-date='" . htmlspecialchars($row['due_date'] ?? '0000-00-00') . "'
                  data-status='" . htmlspecialchars($row['status'] ?? 'Unknown') . "'>Edit</button>";
                                    echo "<a href='print_borrowed.php?id_book=" . htmlspecialchars($row['id_book'] ?? '0') . "' target='_blank'>Print</a>";
                                    echo "<a href='delete_borrowed.php?id_book=" . htmlspecialchars($row['id_book'] ?? '0') . "' onclick=\"return confirm('Are you sure you want to delete this data?')\">Delete</a>";
                                    echo "</div>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No records available</td></tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<div class='text-center'>Error fetching records: " . mysqli_error($connect) . "</div>";
                        }
                        ?>

                        <!-- Button to open the modal -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBorrowedBookModal">
                            Add Borrowed Book
                        </button>
                    </div>
                </div>

                <!-- Manage Books Section -->
                <div id="manage-books-section" class="content-section">
                    <h2>Manage Books</h2>
                    <div class="card">
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Book ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Subject</th>
                                    <th>Call Number</th> <!-- Added this line -->
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($connect, "SELECT * FROM books ORDER BY id_book ASC");
                                if (mysqli_num_rows($result) > 0) {
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $i++ . "</td>";
                                        echo "<td>" . htmlspecialchars($row['id_book']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['isbn']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['call_number']) . "</td>";
                                        echo "<td><img src='" . htmlspecialchars($row['image_url']) . "' alt='Book Cover' width='50'></td>";
                                        echo "<td>";
                                        echo "<div class='action-dropdown'>";
                                        echo "<button class='action-btn'>Action</button>";
                                        echo "<div class='dropdown-content'>";
                                        echo "<button class='action-btn edit-book-btn' 
    data-book-id='" . htmlspecialchars($row['id_book']) . "'
    data-title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "'
    data-author='" . htmlspecialchars($row['author'], ENT_QUOTES) . "'
    data-isbn='" . htmlspecialchars($row['isbn']) . "'
    data-subject='" . htmlspecialchars($row['subject'], ENT_QUOTES) . "'
    data-issn='" . htmlspecialchars($row['issn']) . "'
    data-series='" . htmlspecialchars($row['series'], ENT_QUOTES) . "'
    data-call-number='" . htmlspecialchars($row['call_number']) . "'>Edit</button>";
                                        echo "<a href='print_book.php?id_book=" . $row['id_book'] . "'>Print</a>";
                                        echo "<a href='delete_book.php?id_book=" . $row['id_book'] . "' onclick=\"return confirm('Are you sure you want to delete this book?')\">Delete</a>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center'>No books available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Add Book Button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                            Add Book
                        </button>
                    </div>
                </div>

                <!-- Manage Users Section -->
                <div id="manage-users-section" class="content-section">
                    <h2>User List</h2>
                    <div class="card">
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Level</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($connect, "SELECT * FROM users ORDER BY id_user ASC");
                                if (mysqli_num_rows($result) > 0) {
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $i++ . "</td>";
                                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['level']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                                        echo "<td>";
                                        echo "<div class='action-dropdown'>";
                                        echo "<button class='action-btn'>Action</button>";
                                        echo "<div class='dropdown-content'>";
                                        echo "<a href='print_user.php?id_user=" . $row['id_user'] . "'>Print</a>";
                                        echo "<a href='delete_user.php?id_user=" . $row['id_user'] . "' onclick=\"return confirm('Are you sure you want to delete this user?')\">Delete</a>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No records available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>








                <!--  Admin Profile Modal -->
                <?php
                // Handle POST request to update user details
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userName = $_POST['username'] ?? '';
                    $ps = $_POST['password'] ?? '';

                    // Validate input
                    if ($userName) {
                        // Prepare the update statement
                        if ($ps !== '') {
                            // If a new password is provided, update both username and password
                            $stmt = $connect->prepare("UPDATE admin SET username=?, password=? WHERE username=?");
                            $Password = $ps;
                            $stmt->bind_param("sss", $userName, $Password, $_SESSION['username']);
                        } else {
                            // If no new password is provided, only update the username
                            $stmt = $connect->prepare("UPDATE admin SET username=? WHERE username=?");
                            $stmt->bind_param("ss", $userName, $_SESSION['username']);
                        }

                        // Execute the statement
                        if ($stmt->execute()) {
                            // Update the session variable if the username was changed
                            if ($ps !== '') {
                                $_SESSION['username'] = $userName; // Update session if username changed
                            }
                            echo "<script>alert('Updated Successfully!'); window.location='dashboard.php';</script>";
                        } else {
                            echo "<script>alert('Update Failed: " . $stmt->error . "'); window.location='dashboard.php';</script>";
                        }
                        $stmt->close();
                    }
                }
                ?>

                <div class="modal modal-profile-settings" id="profileSettingsModal" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Profile Settings</h5>
                                <button type="button" class="btn-close" aria-label="Close" onclick="closeModal()"></button>
                            </div>
                            <form id="updateProfileForm" method="POST" action="">
                                <div class="modal-body">
                                    <div class="alert" role="alert" style="display: none;"></div>
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter Username" value="<?php echo htmlspecialchars($userName); ?>" required>
                                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter New Password">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-danger" onclick="window.location.href='logout.php'">Log Out</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const profileModal = document.getElementById('profileSettingsModal');
                        const updateForm = document.getElementById('updateProfileForm');
                        const alertBox = profileModal.querySelector('.alert');

                        // Open modal
                        document.querySelector('.user-icon').onclick = function() {
                            profileModal.style.display = 'flex'; // Ensure the modal is displayed
                        };

                        // Close modal
                        document.querySelector('#profileSettingsModal .btn-close').onclick = function() {
                            closeModal();
                        };

                        // Close on outside click
                        window.onclick = function(event) {
                            if (event.target === profileModal) {
                                closeModal();
                            }
                        };

                        function closeModal() {
                            profileModal.style.display = 'none'; // Hide the modal
                            alertBox.style.display = 'none'; // Hide the alert box
                        }
                    });
                </script>






                <!------------------------------------------------------------------------------------------------------------- Modal Structure (Add Borrowed) -------------------------------------------------------------------------------------------------------------->

                <div class="modal fade" id="addBorrowedBookModal" tabindex="-1" aria-labelledby="addBorrowedBookModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addBorrowedBookModalLabel">Add Borrowed Book</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="borrowBookForm" method="POST" onsubmit="return validateForm()">
                                    <input type="hidden" name="submit" value="1">

                                    <div class="mb-3">
                                        <label for="id_book" class="form-label">Book ID</label>
                                        <select class="form-select" id="id_book" name="id_book" required>
                                            <option value="">Select Book</option>
                                            <?php
                                            $book_query = "SELECT id_book, title FROM books WHERE id_book NOT IN (SELECT id_book FROM borrowed WHERE status = 'Borrowed')";
                                            $book_result = mysqli_query($connect, $book_query);
                                            while ($book = mysqli_fetch_assoc($book_result)) {
                                                echo "<option value='" . htmlspecialchars($book['id_book']) . "'>" .
                                                    htmlspecialchars($book['id_book'] . ' - ' . $book['title']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">User ID</label>
                                        <select class="form-select" id="user_id" name="user_id" required>
                                            <option value="">Select User</option>
                                            <?php
                                            $user_query = "SELECT id_user, full_name FROM users";
                                            $user_result = mysqli_query($connect, $user_query);
                                            while ($user = mysqli_fetch_assoc($user_result)) {
                                                echo "<option value='" . htmlspecialchars($user['id_user']) . "'>" .
                                                    htmlspecialchars($user['id_user'] . ' - ' . $user['full_name']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="borrowed_date" class="form-label">Borrowed Date</label>
                                        <input type="date" class="form-control" id="borrowed_date" name="borrowed_date" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">Due Date</label>
                                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                                    </div>

                                    <div class="modal-footer px-0 pb-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Add Borrowed Book</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function validateForm() {
                        const borrowedDate = new Date(document.getElementById('borrowed_date').value);
                        const dueDate = new Date(document.getElementById('due_date').value);
                        const today = new Date();

                        // Set hours to 0 for accurate date comparison
                        today.setHours(0, 0, 0, 0);

                        if (borrowedDate < today) {
                            alert('Borrowed date cannot be in the past');
                            return false;
                        }

                        if (dueDate <= borrowedDate) {
                            alert('Due date must be after the borrowed date');
                            return false;
                        }

                        return true;
                    }

                    // Set default dates when modal opens
                    document.getElementById('addBorrowedBookModal').addEventListener('show.bs.modal', function() {
                        const today = new Date();
                        const twoWeeksLater = new Date(today);
                        twoWeeksLater.setDate(today.getDate() + 14);

                        document.getElementById('borrowed_date').value = today.toISOString().split('T')[0];
                        document.getElementById('due_date').value = twoWeeksLater.toISOString().split('T')[0];
                    });
                </script>

                <!------------------------------------------------------------------------------------------------------------- Modal Structure (Edit Borrowed) ------------------------------------------------------------------------------------------------------------->

                <?php
                // Place this at the top of your dashboard.php file, after the session_start()

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_borrowed_submit'])) {
                    $id_borrowed = mysqli_real_escape_string($connect, $_POST['id_borrowed']);
                    $returned_date = mysqli_real_escape_string($connect, $_POST['returned_date']);
                    $due_date = mysqli_real_escape_string($connect, $_POST['due_date']);
                    $status = mysqli_real_escape_string($connect, $_POST['status']);

                    // Update the borrowed record
                    $update_query = "UPDATE borrowed SET 
                    due_date = ?,
                    returned_date = ?,
                    status = ?
                    WHERE id_borrowed = ?";

                    $stmt = mysqli_prepare($connect, $update_query);
                    // Correct the binding to include id_borrowed
                    mysqli_stmt_bind_param($stmt, "sssi", $due_date, $returned_date, $status, $id_borrowed);

                    if (mysqli_stmt_execute($stmt)) {
                        // If status changed to Returned, add record to returned table
                        // Get the book details
                        $book_query = "SELECT id_book, user_id FROM borrowed WHERE id_borrowed = ?";
                        $stmt_book = mysqli_prepare($connect, $book_query);
                        mysqli_stmt_bind_param($stmt_book, "i", $id_borrowed);
                        mysqli_stmt_execute($stmt_book);
                        $result = mysqli_stmt_get_result($stmt_book);
                        $book_data = mysqli_fetch_assoc($result);

                        echo "<script>
                            alert('Borrowed book record updated successfully!');
                            window.location.href = 'dashboard.php';
                        </script>";
                    } else {
                        echo "<script>
                            alert('Error updating borrowed book record: " . mysqli_error($connect) . "');
                            window.location.href = 'dashboard.php';
                        </script>";
                    }
                    mysqli_stmt_close($stmt);
                }
                ?>

                <div class="modal fade" id="editBorrowedBookModal" tabindex="-1" aria-labelledby="editBorrowedBookModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editBorrowedBookModalLabel">Edit Borrowed Book</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editBorrowBookForm" method="POST">
                                    <input type="hidden" name="edit_borrowed_submit" value="1">
                                    <input type="hidden" name="id_borrowed" id="edit_id_borrowed">

                                    <div class="mb-3">
                                        <label for="edit_id_book" class="form-label">Book ID</label>
                                        <input type="text" class="form-control" id="edit_id_book" name="id_book" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_user_id" class="form-label">User ID</label>
                                        <input type="text" class="form-control" id="edit_user_id" name="user_id" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_borrowed_date" class="form-label">Borrowed Date</label>
                                        <input type="date" class="form-control" id="edit_borrowed_date" name="borrowed_date" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_due_date" class="form-label">Due Date</label>
                                        <input type="date" class="form-control" id="edit_due_date" name="due_date" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_returned_date" class="form-label">Returned Date</label>
                                        <input type="date" class="form-control" id="edit_returned_date" name="returned_date">
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_status" class="form-label">Status</label>
                                        <select class="form-select" id="edit_status" name="status" required>
                                            <option value="Borrowed">Borrowed</option>
                                            <option value="Returned">Returned</option>
                                        </select>
                                    </div>

                                    <div class="modal-footer px-0 pb-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Borrowed Book</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Handle edit button clicks
                        const editButtons = document.querySelectorAll('.edit-borrowed-btn');

                        editButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                // Get data from button attributes
                                const idBorrowed = this.getAttribute('data-id-borrowed');
                                const bookId = this.getAttribute('data-book-id');
                                const userId = this.getAttribute('data-user-id');
                                const borrowedDate = this.getAttribute('data-borrowed-date');
                                const dueDate = this.getAttribute('data-due-date');
                                const status = this.getAttribute('data-status');

                                // Populate modal fields
                                document.getElementById('edit_id_borrowed').value = idBorrowed;
                                document.getElementById('edit_id_book').value = bookId;
                                document.getElementById('edit_user_id').value = userId;
                                document.getElementById('edit_borrowed_date').value = borrowedDate;
                                document.getElementById('edit_due_date').value = dueDate;
                                document.getElementById('edit_status').value = status;

                                // Show modal
                                const editModal = new bootstrap.Modal(document.getElementById('editBorrowedBookModal'));
                                editModal.show();
                            });
                        });

                        // Form validation
                        document.getElementById('editBorrowBookForm').addEventListener('submit', function(e) {
                            const dueDate = new Date(document.getElementById('edit_due_date').value);
                            const borrowedDate = new Date(document.getElementById('edit_borrowed_date').value);

                            if (dueDate <= borrowedDate) {
                                e.preventDefault();
                                alert('Due date must be after the borrowed date');
                                return false;
                            }

                            return true;
                        });
                    });
                </script>

                <!------------------------------------------------------------------------------------------------------------- Modal Structure (Add Books) -------------------------------------------------------------------------------------------------------------->

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book_submit'])) {
                    $id_book = mysqli_real_escape_string($connect, $_POST['id_book'] ?? '');
                    $title = mysqli_real_escape_string($connect, $_POST['title'] ?? '');
                    $author = mysqli_real_escape_string($connect, $_POST['author'] ?? '');
                    $isbn = mysqli_real_escape_string($connect, $_POST['isbn'] ?? '');
                    $subject = mysqli_real_escape_string($connect, $_POST['subject'] ?? '');
                    $issn = mysqli_real_escape_string($connect, $_POST['issn'] ?? '');
                    $series = mysqli_real_escape_string($connect, $_POST['series'] ?? '');
                    $call_number = mysqli_real_escape_string($connect, $_POST['call_number'] ?? '');

                    // Handle image upload
                    $image_url = '';
                    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === 0) {
                        $upload_dir = 'uploads/books/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $file_extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
                        $file_name = $id_book . '_' . time() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;

                        if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_path)) {
                            $image_url = $target_path;
                        }
                    }

                    // Check if book ID already exists
                    $check_query = "SELECT id_book FROM books WHERE id_book = ?";
                    $check_stmt = mysqli_prepare($connect, $check_query);
                    mysqli_stmt_bind_param($check_stmt, "s", $id_book);
                    mysqli_stmt_execute($check_stmt);
                    mysqli_stmt_store_result($check_stmt);

                    if (mysqli_stmt_num_rows($check_stmt) > 0) {
                        echo "<script>alert('Book ID already exists!'); window.location.href = 'dashboard.php';</script>";
                    } else {
                        // Insert new book
                        $insert_query = "INSERT INTO books (id_book, title, author, isbn, subject, issn, series, call_number, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($connect, $insert_query);
                        mysqli_stmt_bind_param($stmt, "sssssssss", $id_book, $title, $author, $isbn, $subject, $issn, $series, $call_number, $image_url);

                        if (mysqli_stmt_execute($stmt)) {
                            echo "<script>alert('Book added successfully!'); window.location.href = 'dashboard.php';</script>";
                        } else {
                            echo "<script>alert('Error adding book: " . mysqli_error($connect) . "'); window.location.href = 'dashboard.php';</script>";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    mysqli_stmt_close($check_stmt);
                }
                ?>

                <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addBookModalLabel">Add Book</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addBookForm" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="add_book_submit" value="1">

                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="author" class="form-label">Author</label>
                                        <input type="text" class="form-control" id="author" name="author" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="subject" name="subject" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="issn" class="form-label">ISSN</label>
                                        <input type="text" class="form-control" id="issn" name="issn" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="series" class="form-label">Series</label>
                                        <input type="text" class="form-control" id="series" name="series" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="call_number" class="form-label">Call Number</label>
                                        <input type="text" class="form-control" id="call_number" name="call_number" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="book_image" class="form-label">Book Cover Image</label>
                                        <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*">
                                    </div>

                                    <div class="modal-footer px-0 pb-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Add Book</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.getElementById('addBookForm').addEventListener('submit', function(e) {
                        const idBook = document.getElementById('id_book').value;
                        const isbn = document.getElementById('isbn').value;

                        // Basic ISBN validation (13 or 10 digits)
                        const isbnRegex = /^(?:\d{10}|\d{14})$/;
                        if (!isbnRegex.test(isbn)) {
                            e.preventDefault();
                            alert('Please enter a valid 10 or 13 digit ISBN');
                            return false;
                        }

                        return true;
                    });
                </script>

                <!------------------------------------------------------------------------------------------------------------- Modal Structure (Edit Books) ------------------------------------------------------------------------------------------------------------->

                <?php
                require_once 'config.php';

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    try {
                        // Input validation and sanitization
                        $original_record_id = mysqli_real_escape_string($connect, $_POST['original_record_id']);
                        $title = mysqli_real_escape_string($connect, $_POST['title']);
                        $author = mysqli_real_escape_string($connect, $_POST['author']);
                        $isbn = mysqli_real_escape_string($connect, $_POST['isbn']);
                        $subject = mysqli_real_escape_string($connect, $_POST['subject']);
                        $issn = mysqli_real_escape_string($connect, $_POST['issn']);
                        $series = mysqli_real_escape_string($connect, $_POST['series']);
                        $call_number = mysqli_real_escape_string($connect, $_POST['call_number']);

                        // Validate required fields
                        if (empty($title) || empty($author)) {
                            throw new Exception("Title and Author are required fields.");
                        }

                        // Prepare base update query
                        $update_query = "UPDATE books SET 
            title = ?, 
            author = ?, 
            isbn = ?, 
            subject = ?, 
            issn = ?, 
            series = ?, 
            call_number = ?";

                        $params = [$title, $author, $isbn, $subject, $issn, $series, $call_number];
                        $types = "sssssss";

                        // Handle image upload
                        if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = 'uploads/books/';
                            if (!file_exists($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }

                            // Validate file type and size
                            $max_file_size = 5 * 1024 * 1024; // 5MB
                            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

                            if ($_FILES['book_image']['size'] > $max_file_size) {
                                throw new Exception("File is too large. Maximum size is 5MB.");
                            }

                            $file_type = mime_content_type($_FILES['book_image']['tmp_name']);
                            if (!in_array($file_type, $allowed_types)) {
                                throw new Exception("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
                            }

                            $file_extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
                            $file_name = uniqid() . '.' . $file_extension;
                            $target_path = $upload_dir . $file_name;

                            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_path)) {
                                $update_query .= ", image_url = ?";
                                $params[] = $target_path;
                                $types .= "s";
                            } else {
                                throw new Exception("Failed to upload image");
                            }
                        }

                        // Add WHERE clause to update specific book
                        $update_query .= " WHERE id_book = ?";
                        $params[] = $original_record_id;
                        $types .= "s";

                        // Prepare and execute the statement
                        $stmt = mysqli_prepare($connect, $update_query);
                        if (!$stmt) {
                            throw new Exception("Database prepare error: " . mysqli_error($connect));
                        }

                        mysqli_stmt_bind_param($stmt, $types, ...$params);

                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception("Update failed: " . mysqli_stmt_error($stmt));
                        }

                        mysqli_stmt_close($stmt);

                        // Successful update alert
                        echo "<script>
                alert('Book Updated Successfully!');
                window.location.href = 'dashboard.php';
              </script>";
                        exit();
                    } catch (Exception $e) {
                        // Error alert with specific message
                        echo "<script>
                alert('Error: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
                        exit();
                    }
                }
                ?>

                <!-- Edit Book Modal -->
                <div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editBookModalLabel">Edit Book Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editBookForm" action="" method="POST" enctype="multipart/form-data">
                                    <!-- Hidden input for the original record ID -->
                                    <input type="hidden" name="original_record_id" id="edit_original_record_id">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_title" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="edit_title" name="title" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_author" class="form-label">Author</label>
                                            <input type="text" class="form-control" id="edit_author" name="author" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_isbn" class="form-label">ISBN</label>
                                            <input type="text" class="form-control" id="edit_isbn" name="isbn">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="edit_subject" name="subject">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_issn" class="form-label">ISSN</label>
                                            <input type="text" class="form-control" id="edit_issn" name="issn">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_series" class="form-label">Series</label>
                                            <input type="text" class="form-control" id="edit_series" name="series">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_call_number" class="form-label">Call Number</label>
                                            <input type="text" class="form-control" id="edit_call_number" name="call_number">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="edit_book_image" class="form-label">Book Cover Image</label>
                                            <input type="file" class="form-control" id="edit_book_image" name="book_image" accept="image/*">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Book</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript for Populating Modal -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const editBookButtons = document.querySelectorAll('.edit-book-btn');
                        const editModal = document.getElementById('editBookModal');

                        editBookButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                // Populate modal fields
                                document.getElementById('edit_original_record_id').value = this.dataset.bookId;
                                document.getElementById('edit_title').value = this.dataset.title;
                                document.getElementById('edit_author').value = this.dataset.author;
                                document.getElementById('edit_isbn').value = this.dataset.isbn;
                                document.getElementById('edit_subject').value = this.dataset.subject;
                                document.getElementById('edit_issn').value = this.dataset.issn;
                                document.getElementById('edit_series').value = this.dataset.series;
                                document.getElementById('edit_call_number').value = this.dataset.callNumber;

                                // Show the modal
                                var modal = new bootstrap.Modal(editModal);
                                modal.show();
                            });
                        });
                    });
                </script>

                <!-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->



                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Clean up any existing modal backdrops
                        const removeExtraBackdrops = () => {
                            const backdrops = document.querySelectorAll('.modal-backdrop');
                            if (backdrops.length > 1) {
                                Array.from(backdrops).slice(1).forEach(backdrop => backdrop.remove());
                            }
                        };

                        // Handle modal show/hide events
                        const modal = document.getElementById('addBorrowedBookModal');
                        modal.addEventListener('show.bs.modal', removeExtraBackdrops);
                        modal.addEventListener('hidden.bs.modal', removeExtraBackdrops);

                        // Reset form on modal close
                        modal.addEventListener('hidden.bs.modal', function() {
                            document.getElementById('borrowBookForm').reset();
                        });
                    });
                </script>



                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    function toggleSidebar() {
                        const sidebar = document.getElementById('sidebar');
                        const mainContent = document.getElementById('mainContent');
                        sidebar.classList.toggle('shrink');
                        mainContent.classList.toggle('shrink');
                    }

                    const ctx = document.getElementById('barChart').getContext('2d');
                    const barChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['January', 'February', 'March', 'April', 'May', 'June'],
                            datasets: [{
                                label: 'Books Borrowed',
                                data: [120, 150, 180, 100, 90, 200],
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)',
                                    'rgba(255, 159, 64, 0.6)',
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 206, 86, 0.6)'
                                ],
                                borderColor: [
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)',
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    document.addEventListener('DOMContentLoaded', function() {
                        const navLinks = document.querySelectorAll('.nav-link');

                        navLinks.forEach(link => {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();

                                // Remove active class from all links
                                navLinks.forEach(l => l.classList.remove('active'));

                                // Hide all sections
                                document.querySelectorAll('.content-section').forEach(section => {
                                    section.style.display = 'none';
                                    section.classList.remove('active');
                                });

                                // Add active class to clicked link
                                this.classList.add('active');

                                // Show corresponding section
                                const sectionId = this.getAttribute('data-section');
                                const targetSection = document.getElementById(sectionId);
                                if (targetSection) {
                                    targetSection.style.display = 'block';
                                    targetSection.classList.add('active');
                                }
                            });
                        });
                    });
                </script>
                <br>
                <div class="footer">
                    &copy; 2024 Library Management System
                </div>
</body>

</html>