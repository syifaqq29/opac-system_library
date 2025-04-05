<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results - Perpustakaan Al-Hazen</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <antArtifact identifier="book-interactions-css" type="application/vnd.ant.code" language="css" title="Book Interactions Styling">
    <style>
      body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        margin: 0;
        padding: 0;
        background-color: #f5f5f5;
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

      /* Results Styles */
      /* Reset z-index stacking context */
      .search-results-container {
        position: relative;
        z-index: 1;
        isolation: isolate;
      }

      /* Book card styles with improved positioning */
      .book-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
        overflow: hidden;
        display: flex;
        transition: transform 0.2s;
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 100%;
      }

      /* Prevent overflow issues */
      .book-info {
        flex: 1;
        padding: 1.5rem;
        min-width: 0;
        /* Prevents flex item from overflowing */
        overflow: hidden;
      }

      /* Maintain cover image aspect ratio */
      .book-cover {
        width: 200px;
        min-width: 200px;
        /* Prevent shrinking */
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .cover-image {
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: 4px;
      }

      /* Prevent text overflow */
      .book-title {
        margin: 0 0 1rem 0;
        color: #262160;
        font-size: 1.4em;
        white-space: normal;
        overflow-wrap: break-word;
        word-wrap: break-word;
      }

      .book-details p {
        margin: 0.5rem 0;
        color: #666;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: normal;
        overflow-wrap: break-word;
        word-wrap: break-word;
      }

      /* Ensure icons stay aligned */
      .book-details i {
        color: #262160;
        width: 20px;
        flex-shrink: 0;
      }

      /* Availability badge */
      .availability {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: bold;
        margin-top: 1rem;
        position: relative;
        z-index: 3;
      }

      /* Responsive adjustments */
      @media (max-width: 768px) {
        .book-card {
          flex-direction: column;
        }

        .book-cover {
          width: 100%;
          max-width: 200px;
          margin: 0 auto;
        }

        .book-info {
          width: 100%;
        }
      }

      /* Prevent popup interference */
      .book-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        z-index: -1;
      }

      /* Ensure proper stacking with sidebars */
      .search-results-container {
        position: relative;
        z-index: 1;
        margin-left: auto;
        margin-right: auto;
        width: 100%;
        max-width: 1200px;
        padding: 0 1rem;
        box-sizing: border-box;
      }


      .book-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
      }

      .book-actions {
        display: flex;
        gap: 0.5rem;
      }

      .action-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .action-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }

      .action-btn i {
        font-size: 1.2rem;
      }

      .fa-bookmark {
        color: #262160;
      }

      .fa-heart {
        color: #ff4081;
      }

      /* Toast notification */
      .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        z-index: 1000;
        animation: fadeInOut 3s ease-in-out;
      }

      @keyframes fadeInOut {
        0% {
          opacity: 0;
          transform: translateY(20px);
        }

        10% {
          opacity: 1;
          transform: translateY(0);
        }

        90% {
          opacity: 1;
          transform: translateY(0);
        }

        100% {
          opacity: 0;
          transform: translateY(-20px);
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
                style="width: 80px; height: auto; margin-bottom: 10px;">
            <?php endif; ?>
            <h4 style="color: white; margin: 5px 0;"><?php echo htmlspecialchars($item['title']); ?></h4>
            <p style="color: #ccc; margin: 3px 0;">By: <?php echo htmlspecialchars($item['author']); ?></p>
            <p style="color: #ccc; margin: 3px 0;">
              Status:
              <span style="color: <?php echo $item['availability'] == 'Available' ? '#4CAF50' : '#FF5722'; ?>">
                <?php echo htmlspecialchars($item['availability']); ?>
              </span>
            </p>
            <hr style="border: 0.5px solid rgba(255, 255, 255, 0.1); margin: 10px 0;">
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color: white; text-align: center; padding: 20px;">Your wishlist is empty</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Banner -->
  <div class="banner">
    <img src="Banner.png" alt="KVS library Banner">
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

  <div class="search-results-container">
    <?php
    include 'config.php';
    function displayBook($book)
    {
      global $conn;

      $img = !empty($book['image_url']) ? htmlspecialchars($book['image_url']) : 'placeholder-image.jpg';
      $bookId = htmlspecialchars($book['id_book']);

      $isBookmarked = isset($_SESSION['user_id']) ? checkBookmarkStatus($bookId, $_SESSION['user_id']) : false;
      $isFavorited = isset($_SESSION['user_id']) ? checkFavoriteStatus($bookId, $_SESSION['user_id']) : false;

      $bookmarkClass = $isBookmarked ? 'fas' : 'far';
      $favoriteClass = $isFavorited ? 'fas' : 'far';

      return "
        <div class='book-card'>
            <div class='book-cover'>
                <img src='{$img}' alt='Cover of {$book['title']}' class='cover-image'>
            </div>
            <div class='book-info'>
                <div class='book-header'>
                    <h3 class='book-title'>" . htmlspecialchars($book['title']) . "</h3>
                    <div class='book-actions'>
                        <button class='action-btn bookmark-btn' data-book-id='{$bookId}' 
                                onclick='toggleWishlist({$bookId})'>
                            <i class='{$bookmarkClass} fa-bookmark'></i>
                        </button>
                        <button class='action-btn favorite-btn' data-book-id='{$bookId}' 
                                onclick='toggleFavorite({$bookId})'>
                            <i class='{$favoriteClass} fa-heart'></i>
                        </button>
                    </div>
                </div>
                <div class='book-details'>
                    <p><i class='fas fa-user'></i> " . htmlspecialchars($book['author']) . "</p>
                    <p><i class='fas fa-bookmark'></i> " . htmlspecialchars($book['subject']) . "</p>
                    <p><i class='fas fa-barcode'></i> ISBN: " . htmlspecialchars($book['isbn']) . "</p>
                    <p><i class='fas fa-hashtag'></i> ISSN: " . htmlspecialchars($book['issn']) . "</p>
                    <p><i class='fas fa-layer-group'></i> Series: " . htmlspecialchars($book['series']) . "</p>
                    <p><i class='fas fa-map-marker-alt'></i> Call Number: " . htmlspecialchars($book['call_number']) . "</p>
                    <p class='availability " . (strtolower($book['availability']) === 'available' ? 'available' : 'unavailable') . "'>
                        " . htmlspecialchars($book['availability']) . "
                    </p>
                </div>
            </div>
        </div>";
    }


    $hasResults = false;
    $searchPerformed = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $searchPerformed = true;
      $conditions = array();
      $params = array();
      $types = "";

      // Handle simple search
      if (isset($_POST['searchTerm']) && isset($_POST['searchCategory'])) {
        $category = $_POST['searchCategory'];
        $term = $_POST['searchTerm'];
        $conditions[] = "$category LIKE ?";
        $params[] = "%$term%";
        $types .= "s";

        echo "<div class='search-summary'>
                    <h2>Search Results for: " . htmlspecialchars($term) . " in " . htmlspecialchars($category) . "</h2>
                </div>";
      }

      // Handle advanced search
      $advancedFields = ['title', 'author', 'subject', 'isbn', 'issn', 'series', 'call_number'];
      $usedFields = array();
      foreach ($advancedFields as $field) {
        if (!empty($_POST[$field])) {
          $conditions[] = "$field LIKE ?";
          $params[] = "%{$_POST[$field]}%";
          $types .= "s";
          $usedFields[] = "$field: " . htmlspecialchars($_POST[$field]);
        }
      }

      if (!empty($usedFields)) {
        echo "<div class='search-summary'>
                    <h2>Advanced Search Results</h2>
                    <p>Searching for: " . implode(", ", $usedFields) . "</p>
                </div>";
      }

      if (count($conditions) > 0) {
        $query = "SELECT * FROM books WHERE " . implode(" AND ", $conditions);
        $stmt = $connect->prepare($query);

        if (!empty($params)) {
          $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
          $hasResults = true;
          while ($book = $result->fetch_assoc()) {
            echo displayBook($book);
          }
        }

        $stmt->close();
      }
    }

    if ($searchPerformed && !$hasResults) {
      echo "<div class='no-results'>
                <i class='fas fa-search' style='font-size: 3rem; color: #666; margin-bottom: 1rem;'></i>
                <h2>No Results Found</h2>
                <p>Try adjusting your search criteria or try different keywords.</p>
            </div>";
    }

    $connect->close();

    function checkBookmarkStatus($bookId, $userId)
    {
      global $connect;
      $stmt = $connect->prepare("SELECT * FROM wishlist WHERE book_id = ? AND user_id = ?");
      $stmt->bind_param("ii", $bookId, $userId);
      $stmt->execute();
      $result = $stmt->get_result();
      return $result->num_rows > 0;
    }

    function checkFavoriteStatus($bookId, $userId)
    {
      global $connect;
      $stmt = $connect->prepare("SELECT * FROM favorites WHERE book_id = ? AND user_id = ?");
      $stmt->bind_param("ii", $bookId, $userId);
      $stmt->execute();
      $result = $stmt->get_result();
      return $result->num_rows > 0;
    }
    ?>
  </div>
  <script>
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

    function toggleFavorite(bookId) {
      fetch('search.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=toggleFavorite&bookId=${bookId}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Toggle the heart icon
            const heartIcon = document.querySelector(`.favorite-btn[data-book-id="${bookId}"] i`);
            if (data.action === 'added') {
              heartIcon.classList.remove('far');
              heartIcon.classList.add('fas');
              showToast('Added to favorites');
            } else {
              heartIcon.classList.remove('fas');
              heartIcon.classList.add('far');
              showToast('Removed from favorites');
            }
            // Refresh the favorites popup if it exists
            if (document.getElementById('favoritesItems')) {
              location.reload();
            }
          } else {
            showToast(data.message);
          }
        })
        .catch(error => {
          showToast('An error occurred');
          console.error('Error:', error);
        });
    }

    function toggleWishlist(bookId) {
      fetch('search.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=toggleWishlist&bookId=${bookId}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Toggle the bookmark icon
            const bookmarkIcon = document.querySelector(`.bookmark-btn[data-book-id="${bookId}"] i`);
            if (data.action === 'added') {
              bookmarkIcon.classList.remove('far');
              bookmarkIcon.classList.add('fas');
              showToast('Added to wishlist');
            } else {
              bookmarkIcon.classList.remove('fas');
              bookmarkIcon.classList.add('far');
              showToast('Removed from wishlist');
            }
            // Refresh the wishlist sidebar if it exists
            if (document.getElementById('wishlistItems')) {
              location.reload();
            }
          } else {
            showToast(data.message);
          }
        })
        .catch(error => {
          showToast('An error occurred');
          console.error('Error:', error);
        });
    }

    function showToast(message) {
      // Remove existing toast if any
      const existingToast = document.querySelector('.toast');
      if (existingToast) {
        existingToast.remove();
      }

      // Create and show new toast
      const toast = document.createElement('div');
      toast.className = 'toast';
      toast.textContent = message;
      document.body.appendChild(toast);

      // Remove toast after animation
      setTimeout(() => {
        toast.remove();
      }, 3000);
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