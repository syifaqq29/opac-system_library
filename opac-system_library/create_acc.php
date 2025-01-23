<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style type="text/css">
        /* Navigation and Banner Styling */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
            padding-bottom: 15px;
            height: 100vh;
            justify-content: center;
            align-items: center;
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

        :root {
            --primary-color: #4070f4;
            --secondary-color: #265df2;
            --background-gradient: linear-gradient(135deg, #667eea, #764ba2);
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .form-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
            padding: 25px;
        }

        header {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .progress-bar {
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-bar .progress {
            height: 100%;
            background-color: var(--primary-color);
            width: 0;
            transition: width 0.3s ease;
        }

        .form {
            position: relative;
            margin-top: 16px;
            min-height: 490px;
            overflow: hidden;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        .input-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .input-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .input-field input,
        .input-field select {
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-field input:focus {
            outline: 2px solid var(--primary-color);
        }

        button {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .input-field {
                min-width: 100%;
            }

            .buttons {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body class="bg-light">

    <!-- Navigation -->
    <nav class="top-nav">
        <div class="top-nav-logo">
            <a href="index.php">Home</a>
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
            <a href="create_acc.php">Create Account</a>
            <a href="#" id="favouriteLink">
                <i class="fas fa-heart"></i>
            </a>
            <a href="#" id="bookmarkLink">
                <i class="fas fa-bookmark"></i>
            </a>
        </div>
    </nav>

    <!-- Add  translate button to appear -->
    <div id="google_translate_element" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;"></div>

    <!-- Banner -->
    <div class="banner">
        <img src="banner.png" alt="KVS library Banner">
    </div>

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

    <!-- Registration Form -->
    <div class="form-wrapper">
        <div class="container">
            <header>Registration</header>
            <div class="progress-bar" aria-hidden="true">
                <div class="progress" id="progress"></div>
            </div>
            <form action="process_registration.php" method="POST">
                <!-- First Section -->
                <div class="form-section active" id="section1">
                    <div class="fields">
                        <div class="input-field">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
                        </div>
                        <div class="input-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="input-field">
                            <label for="contact">Contact</label>
                            <input type="tel" id="contact" name="contact" placeholder="Enter mobile number" required>
                        </div>
                        <div class="input-field">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" placeholder="Enter your address" required>
                        </div>
                        <div class="input-field">
                            <label for="course">Course</label>
                            <select id="course" name="course" required>
                                <option value="" disabled selected>Choose your course</option>
                                <option value="Course 1">KPD</option>
                                <option value="Course 2">KSK</option>
                                <option value="Course 3">MTA</option>
                                <option value="Course 4">MPP</option>
                                <option value="Course 4">HSK</option>
                                <option value="Course 4">HFD</option>
                                <!-- Add more courses as needed -->
                            </select>
                        </div>
                        <div class="input-field">
                            <label for="level">Level</label>
                            <input type="text" id="level" name="level" placeholder="Enter your level" required>
                        </div>
                    </div>
                    <button type="button" class="nextBtn">Next</button>
                </div>

                <!-- Second Section -->
                <div class="form-section" id="section2">
                    <div class="fields">
                        <div class="input-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter username" required>
                        </div>
                        <div class="input-field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="button" class="backBtn">Back</button>
                        <button type="submit">Submit</button>
                    </div>
                </div>
            </form>
            <footer>
                © 2024 Perpustakaan Al-Hazen. All rights reserved.
                <p><a href="privacy_policy.php">Privacy Policy</a> | <a href="terms_conditions.php">Terms & Conditions</a></p>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="C:\xampp\htdocs\opac-system_library\alertlogin.js"></script>
    <script>
        function toggleAdvancedSearch() {
            const advancedSearch = document.querySelector('.advanced-search');
            const toggleText = document.getElementById('toggleText');
            advancedSearch.classList.toggle('show');
            toggleText.textContent = advancedSearch.classList.contains('show') ?
                'Hide Advanced Search' :
                'Show Advanced Search';
        }

        // Form validation
        const nextBtn = document.querySelector('.nextBtn');
        const backBtn = document.querySelector('.backBtn');
        const formSections = document.querySelectorAll('.form-section');
        const progressBar = document.getElementById('progress');

        let currentSection = 0;

        // Function to update visibility and progress bar
        function updateSections() {
            formSections.forEach((section, index) => {
                section.classList.toggle('active', index === currentSection);
            });
            updateProgressBar();
        }

        function updateProgressBar() {
            const progressPercentage = ((currentSection + 1) / formSections.length) * 100;
            progressBar.style.width = `${progressPercentage}%`;
        }

        // Add event listener for "Next" button
        nextBtn.addEventListener('click', () => {
            const inputs = formSections[currentSection].querySelectorAll('input');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    isValid = false;
                    input.reportValidity();
                }
            });

            if (isValid) {
                if (currentSection < formSections.length - 1) {
                    currentSection++;
                    updateSections();
                }
            }
        });

        // Add event listener for "Back" button
        backBtn.addEventListener('click', () => {
            if (currentSection > 0) {
                currentSection--;
                updateSections();
            }
        });

        // Initialize sections and progress bar
        updateSections();

        // Login status variable (replace with your actual login logic)
        let isLoggedIn = false; // Set to true if the user is logged in

        // Function to show a login alert
        function showLoginAlert(event) {
            if (!isLoggedIn) {
                event.preventDefault(); // Prevent navigation
                alert("Please log in to access this feature."); // Alert message
            }
        }

        // Attach the alert to specific links
        document.addEventListener("DOMContentLoaded", function() {
            // Favorite link
            const favouriteLink = document.getElementById("favouriteLink"); // No #
            if (favouriteLink) {
                favouriteLink.addEventListener("click", showLoginAlert);
            }

            // Bookmark link
            const bookmarkLink = document.getElementById("bookmarkLink"); // No #
            if (bookmarkLink) {
                bookmarkLink.addEventListener("click", showLoginAlert);
            }
        });

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