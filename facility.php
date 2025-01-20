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
      flex: 1;
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

    .step {
      margin: 20px 0;
      padding: 15px;
      border-left: 4px solid #262160;
      background-color: #f0f9ff;
    }

    .step-title {
      font-weight: bold;
      color: #007bff;
    }

    ul {
      padding-left: 20px;
    }

    li {
      margin-bottom: 10px;
    }

    a {
      color: #007bff;
      text-decoration: none;
      transition: color 0.3s;
    }

    .note {
      background: #eaf7ff;
      border-left: 4px solid #262160;
      padding: 15px;
      margin: 20px 0;
      font-style: italic;
    }

    @media (max-width: 576px) {
      .container h1 {
        font-size: 2rem;
      }
    }

    footer {
      text-align: center;
      padding: 20px 0;
      text-decoration: none;
      color: #262160;
      font-size: 0.9em;
      width: 100%;
    }

    /* Add media query for responsiveness */
    @media (max-width: 1300px) {
      .container {
        max-width: 90%;
        /* Makes it responsive for smaller screens */
        margin: 30px auto;
      }
    }

    .image-gallery {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      /* Increased from 15px */
      padding: 20px;
      /* Increased from 10px */
      max-width: 1200px;
      /* Added max-width for better layout control */
      margin: 0 auto;
      /* Center the gallery */
    }

    /* Image gallery hover effects */
    .image-gallery div {
      position: relative;
      overflow: hidden;
      cursor: pointer;
      flex: 1 1 calc(33.33% - 40px);
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(38, 33, 96, 0.2);
      transition: transform 0.3s ease-in-out;
      min-height: 300px;
    }

    .image-gallery div img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 12px;
      transition: all 0.3s ease-in-out;
    }

    /* Enhanced hover effects */
    .image-gallery div:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(38, 33, 96, 0.3);
    }

    .image-gallery div:hover img {
      transform: scale(1.1);
      filter: brightness(0.7);
    }

    .image-gallery div::after {
      content: attr(data-title);
      position: absolute;
      bottom: 20px;
      left: 20px;
      color: white;
      font-size: 1.4rem;
      background-color: rgba(0, 0, 0, 0.7);
      padding: 8px 15px;
      border-radius: 6px;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    .image-gallery div:hover::after {
      opacity: 1;
    }

    /* Facility description styles */
    .facility-description {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 40px auto;
      color: #555;
      font-size: 1.1em;
      line-height: 1.6;
      padding: 0 20px;
    }

    .image-gallery div::after {
      content: attr(data-title);
      position: absolute;
      bottom: 20px;
      left: 20px;
      color: white;
      font-size: 1.4rem;
      padding: 8px 15px;
      /* Increased padding */
      border-radius: 6px;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    .image-gallery div:nth-child(odd) {
      flex: 1 1 calc(45% - 40px);
      /* Increased from 40% */
    }

    .image-gallery div:nth-child(even) {
      flex: 1 1 calc(35% - 40px);
      /* Increased from 30% */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .image-gallery div {
        flex: 1 1 calc(50% - 30px);
        min-height: 250px;
        /* Adjusted for smaller screens */
      }
    }

    @media (max-width: 576px) {
      .image-gallery div {
        flex: 1 1 100%;
        min-height: 200px;
        /* Adjusted for mobile */
      }
    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <nav class="top-nav">
    <div class="top-nav-logo">
      <a href="index.php">Home</a>
      <div class="dropdown">
        <a href="#" class="dropbtn">Guide</a>
        <div class="dropdown-content">
          <a href="register.php">Register</a>
          <a href="facillity.php">Facillity</a>
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

  <div class="container">
    <h1>Facillity</h1>
    <div class="facility-description">
      Welcome to Al-Hazen Library's state-of-the-art facilities. Our spaces are thoughtfully designed to support learning,
      collaboration, and innovation. From our Business Hub to our Media Room, each area is equipped with modern amenities
      to enhance your library experience. Explore our various spaces and find your perfect spot for study, research, or relaxation.
    </div>
    <div class="image-gallery">
      <div data-title="Business Hub">
        <img src="uploads/thumbnail/m1.jpg" alt="Image 1">
      </div>
      <div data-title="Studio Al - Hazen">
        <img src="uploads/thumbnail/m2.jpg" alt="Image 2">
      </div>
      <div data-title="Laman Membaca">
        <img src="uploads/thumbnail/m3.jpg" alt="Image 3">
      </div>
      <div data-title="Chit Chat Corner">
        <img src="uploads/thumbnail/m4.jpg" alt="Image 4">
      </div>
      <div data-title="Pusat Akses">
        <img src="uploads/thumbnail/m5.jpg" alt="Image 5">
      </div>
      <div data-title="Ruang Utama">
        <img src="uploads/thumbnail/m6.jpg" alt="Image 6">
      </div>
      <div data-title="Ruang Media">
        <img src="uploads/thumbnail/m7.jpg" alt="Image 7">
      </div>
      <div data-title="Laman Digital">
        <img src="m8.jpg" alt="Image 8">
      </div>
      <div data-title="Ruang Perbincangan">
        <img src="uploads/thumbnail/m9.jpg" alt="Image 9">
      </div>
      <div data-title="Bilik AV">
        <img src="uploads/thumbnail/m10.jpg" alt="Image 10">
      </div>
    </div>
    <footer>
      <p>&copy; 2024 PSSAlHazen.my | All Rights Reserved</p>
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