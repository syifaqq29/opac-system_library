<?php
include 'config.php'; // Include your database configuration file

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Fetch the latest message
$sql = "SELECT id, message FROM mesej ORDER BY created_at DESC LIMIT 1";
$result = $connect->query($sql);
$message = "";

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Ensure 'message' key exists before accessing it
    if (isset($row['message'])) {
        $message = $row['message'];
    }
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap");
        @import url("https://fonts.googleapis.com/css2?family=Pangolin&display=swap");

        html {
            scroll-behavior: smooth;
            /* Menambah kesan smooth scroll */
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: orange;
            background-image: url('image/formbg.jpg');
            /* Kekalkan gambar asal banner */
            color: #fff;
            margin: 0;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        /* Navbar Section */
        .navbar {
            font-family: "Poppins", sans-serif;
            /* Menggunakan font Poppins */
            margin: 0;
            /* Menghapuskan margin */
            padding: 0.5rem 1rem;
            /* Ruang dalam navbar */
            background-color: #d35400;
            /* Oren gelap */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            /* Bayang-bayang */
            border-radius: 0;
            /* Tiada sudut bulat */
            color: #fff;
            /* Warna teks dalam navbar */
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            color: #fff;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        .navbar-brand:hover {
            color: #f1c40f;
        }

        .search {
            display: flex;
            align-items: center;
            height: 40px;
            border-radius: 70px;
            background: #F3E3C3;
            /* peach */
            padding: 0 10px;
            margin-left: 1px;
        }

        .search-txt {
            border: none;
            background: none;
            color: #483C32;
            /* dark brown */
            outline: none;
            width: 100px;
            transition: width 0.4s ease-in-out;
            font-size: 14px;
            font-family: "Poppins", sans-serif;
        }

        .search:hover .search-txt {
            width: 150px;
        }

        .search-btn {
            color: white;
            background-color: #e67e22;
            /* Oren gelap */
            border-radius: 50%;
            height: 25px;
            width: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s ease;
            border: none;
            /* Remove border around the button */
        }

        .search-btn i {
            font-size: 14px;
            /* Adjust the size of the search icon */
        }

        .search:hover .search-btn {
            background-color: #f39c12;
            /* Oren lebih terang */
        }

        .search-txt:focus {
            width: 150px;
        }


        .search:hover .search-btn {
            background-color: #f39c12;
            /* Oren lebih terang */
        }

        .nav-link {
            color: #fff;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: #c0392b;
            /* Oren lebih gelap */
            color: #f1c40f;
            /* Teks kuning */
            border-radius: 0.3rem;
        }

        .nav-link.active {
            background-color: #e67e22;
            /* Oren gelap */
            color: #fff;
        }

        .dropdown-menu {
            background-color: #fff;
            color: #000;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .dropdown-item:hover {
            background-color: #f39c12;
            color: #fff;
        }

        .popup {
            display: none;
            position: fixed;
            top: 80px;
            /* Position below navbar */
            right: 20px;
            /* Position from right side */
            transform: none;
            /* Remove the transform */
            width: 300px;
            /* Slightly smaller width */
            background-color: #fef1e6;
            border: 2px solid #f39c12;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            padding: 15px;
            z-index: 1000;
            border-radius: 8px;
            animation: slideIn 0.3s ease-out;
        }

        .popup-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f39c12;
        }

        .popup-header .fa-bell {
            color: #d35400;
            font-size: 20px;
            margin-right: 10px;
        }

        .popup-header h5 {
            margin: 0;
            color: #d35400;
            font-size: 16px;
            font-weight: 600;
        }

        .popup-content {
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .close-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s ease;
            float: right;
            font-size: 14px;
        }

        .close-btn:hover {
            background-color: #c0392b;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="logo">
                e-Aduan DM
            </a>
            <!-- Search Bar -->
            <div class="search">
                <input class="search-txt" type="text" placeholder="Search">
                <button class="search-btn">

                </button>
            </div>


            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Utama</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Tentang Kami
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
                            <li><a class="dropdown-item" href="#">Info Korporat</a></li>
                            <li><a class="dropdown-item" href="visimisi.php">Visi & Misi</a></li>
                            <li><a class="dropdown-item" href="infopage.php">Jadual</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#">Hubung</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Display Section -->
    <?php if ($message): ?>
        <div class="popup" id="messagePopup">
            <div class="popup-header">
                <i class="fas fa-bell"></i>
                <h5>Notification</h5>
            </div>
            <div class="popup-content">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
            <button class="close-btn" onclick="closePopup()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <script>
            // Wait for a short delay before showing the popup
            setTimeout(function() {
                document.getElementById('messagePopup').style.display = 'block';
            }, 500);

            function closePopup() {
                const popup = document.getElementById('messagePopup');
                popup.style.opacity = '0';
                popup.style.transform = 'translateX(100px)';
                setTimeout(function() {
                    popup.style.display = 'none';
                }, 300);
            }
        </script>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>