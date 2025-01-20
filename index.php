<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perpustakaan Al-Hazen</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    /* Reset and Base Styles */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      overflow-x: hidden;
      transition: filter 0.3s ease;
      line-height: 1.6;
      /* Smooth transition for blur effect */
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

    /* Main Content Styles */
    .main-content {
      max-width: 1500px;
      margin: 20px auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr 300px;
      gap: 20px;
    }

    .info-container,
    .subject-table,
    .content,
    .login-panel {
      background: white;
      padding: 20px;
      border-radius: 4px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .count {
      font-size: 4.5rem;
      font-weight: bold;
      color: #262160;
      margin: 1rem 0;
    }

    .quote {
      font-family: 'Georgia', serif;
      font-style: italic;
      font-size: 1.5rem;
      color: #262160;
      text-align: center;
      margin: 20px 0;
      padding: 10px;
      background-color: #f5f5f5;
      border-left: 4px solid #262160;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .quote-author {
      font-size: 1.2rem;
      font-weight: bold;
      color: #333;
      margin-top: 10px;
    }

    /* Schedule Styles */
    .schedule-container {
      max-width: 1000px;
      margin: 20px auto;
    }

    .button-group {
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    /* Schedule Button Styles */
    .schedule-button {
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      background-color: #262160;
      color: white;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    /* Schedule Button Active State */
    .schedule-button.active {
      background-color: #2b2a88;
      color: white;
    }

    /* Schedule Button Hover Effect */
    .schedule-button:hover {
      background-color: #3a3a9e;
      transform: scale(1.05);
    }

    /* Schedule Button Focus Effect */
    .schedule-button:focus {
      outline: none;
      box-shadow: 0 0 8px rgba(38, 33, 96, 0.6);
    }

    /* Schedule Button Disabled State */
    .schedule-button:disabled {
      background-color: #999;
      cursor: not-allowed;
    }

    .schedule-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .schedule-table th,
    .schedule-table td {
      padding: 12px;
      text-align: left;
      border: 1px solid #ddd;
    }

    .schedule-table th {
      background-color: #262160;
      color: white;
    }

    .content {
      transition: filter 0.3s ease;
      /* Smooth transition for blur effect */
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      /* Semi-transparent */
      display: none;
      /* Hide by default */
      z-index: 999;
      /* Just below the popup */
    }

    /* Login Panel Styles */
    .login-panel {
      background: white;
      padding: 20px;
      border-radius: 4px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 100%;
      text-align: center;
    }

    .login-header h4 {
      font-size: 1.8rem;
      color: #262160;
      margin: 0 0 8px;
      font-weight: 600;
    }

    .login-subtitle {
      color: #666;
      font-size: 0.9rem;
      margin: 0;
    }

    .login-buttons {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 20px;
    }

    .login-button {
      display: flex;
      align-items: center;
      padding: 15px;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      background: white;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      text-align: left;
    }

    .login-button:hover {
      background: #f8f9ff;
      border-color: #262160;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(38, 33, 96, 0.1);
    }

    .button-icon {
      font-size: 1.5rem;
      margin-right: 15px;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f5f6fa;
      border-radius: 8px;
    }

    /* Popup Overlay and Content */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      /* Hide by default */
      z-index: 999;
      /* Just below the popup */
    }

    .popup {
      display: none;
      /* Hide by default */
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 35px;
      border-radius: 15px;
      width: 400px;
      max-width: 90vw;
      z-index: 1000;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .popup-content h2 {
      margin-bottom: 20px;
      font-size: 1.8rem;
      color: #262160;
    }

    /* Close Button */
    .close-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 30px;
      height: 30px;
      background: #f5f6fa;
      border: none;
      border-radius: 50%;
      font-size: 1.2rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .close-btn:hover {
      background: #e0e0e0;
    }

    /* Input Field Group */
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }

    .form-group input:focus {
      border-color: #262160;
      box-shadow: 0 0 0 3px rgba(38, 33, 96, 0.1);
      outline: none;
    }

    /* Toggle Password Icon */
    .toggle-password {
      position: absolute;
      top: 50%;
      /* Vertically centers the icon */
      right: 10px;
      /* Adjust horizontal positioning */
      transform: translateY(-50%);
      /* Fixes vertical alignment */
      cursor: pointer;
      font-size: 1.2rem;
      color: #666;
    }

    .toggle-password:hover {
      color: #262160;
    }

    /* Submit Button */
    .login-submit-btn {
      width: 100%;
      padding: 12px;
      background: #262160;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .login-submit-btn:hover {
      background: #1f1c4d;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(38, 33, 96, 0.2);
    }

    .forgot-password-link {
      display: block;
      margin-top: 10px;
      color: #262160;
      text-align: right;
      font-size: 0.9rem;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .forgot-password-link:hover {
      color: #1f1c4d;
      text-decoration: underline;
    }

    /* Footer Styles */
    .modern-footer {
      background-color: #262160;
      color: white;
      padding: 60px 0 30px;
      margin-top: 40px;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 40px;
    }

    .footer-section {
      padding: 0 15px;
    }

    .footer-section h3 {
      color: white;
      font-size: 1.2rem;
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 50px;
      height: 2px;
      background-color: #fff;
    }

    .footer-section p,
    .footer-section a {
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.6;
      margin-bottom: 10px;
    }

    .footer-section a {
      text-decoration: none;
      display: block;
      transition: color 0.3s ease;
    }

    .footer-section a:hover {
      color: white;
    }

    .contact-info {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }

    .contact-info i {
      margin-right: 10px;
      width: 20px;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .social-links a {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background-color 0.3s ease;
    }

    .social-links a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .map-container {
      width: 100%;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 20px;
    }

    .map-container iframe {
      width: 100%;
      height: 200px;
      border: none;
    }

    .footer-bottom {
      text-align: center;
      margin-top: 30px;
    }

    .footer-bottom p {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.9rem;
    }

    /* Utility Classes */
    .hidden {
      display: none;
    }

    .clearfix {
      clear: both;
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

  <!-- Main Content -->
  <main class="main-content">
    <!-- Info Container -->
    <div class="info-container">
      <h1>Total Number of Books</h1>
      <p>(copies) up to the year</p>
      <h1 id="count-display" class="count">0</h1>
      <br>

      <!-- Subject Table -->
      <div class="subject-table">
        <table>
          <thead>
            <tr>
              <th>SUBJECT MATTER/FIELD</th>
              <th>SUBJECT MATTER/FIELD</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>000 - General</td>
              <td>500 - Pure Science</td>
            </tr>
            <tr>
              <td>100 - Philosophy</td>
              <td>600 - Technology</td>
            </tr>
            <tr>
              <td>200 - Religion</td>
              <td>700 - Art, Sports & Recreation</td>
            </tr>
            <tr>
              <td>300 - Social Science</td>
              <td>800 - Literature</td>
            </tr>
            <tr>
              <td>400 - Language</td>
              <td>900 - Geography and History</td>
            </tr>
          </tbody>
        </table>
      </div>
      <br>
    </div>

    <!-- Content Section -->
    <div class="content">
      <!-- Quote of the Day Section -->
      <div class="quote-container">
        <div id="quote-of-the-day" class="quote">
          "Your only limit is your mind."
        </div>
      </div>


      <!-- Schedule Container -->
      <div class="schedule-container">
        <div class="button-group">
          <button class="schedule-button active" onclick="showSchedule('lecture')">LECTURE</button>
          <button class="schedule-button" onclick="showSchedule('exam')">EXAM</button>
          <button class="schedule-button" onclick="showSchedule('semester')">SEMESTER</button>
        </div>

        <!-- Schedule Tables -->
        <table class="schedule-table exam-schedule" id="exam-schedule">
          <!-- Exam Schedule Content -->
          <thead>
            <tr>
              <th>SERVICES</th>
              <th>OPENING HOURS</th>
              <th>CIRCULATION COUNTER</th>
              <th>REFERENCE DESK</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>SUNDAY-THURSDAY</td>
              <td>8:00am - 9:30pm</td>
              <td>8:00am - 9:30pm</td>
              <td>8:00am - 9:30pm</td>
            </tr>
            <tr>
              <td>FRIDAY</td>
              <td>8:00am - 12:15pm <br> 2:45pm - 4:30pm</td>
              <td>8:00am - 12:15pm <br> 2:45pm - 4:30pm</td>
              <td>CLOSED</td>
            </tr>
            <tr>
              <td>SATURDAY</td>
              <td>8:00am - 4:30pm</td>
              <td>8:00am - 4:30pm</td>
              <td>CLOSED</td>
            </tr>
          </tbody>
        </table>

        <table class="schedule-table lecture-schedule hidden" id="lecture-schedule">
          <!-- Lecture Schedule Content -->
          <thead>
            <tr>
              <th>SERVICES</th>
              <th>OPENING HOURS</th>
              <th>CIRCULATION COUNTER</th>
              <th>REFERENCE DESK</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>SUNDAY-THURSDAY</td>
              <td>8:00am - 9:30pm</td>
              <td>8:00am - 9:30pm</td>
              <td>8:00am - 9:30pm</td>
            </tr>
            <tr>
              <td>FRIDAY</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
            </tr>
            <tr>
              <td>SATURDAY</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
            </tr>
          </tbody>
        </table>

        <table class="schedule-table semester-schedule hidden" id="semester-schedule">
          <!-- Semester Schedule Content -->
          <thead>
            <tr>
              <th>SERVICES</th>
              <th>OPENING HOURS</th>
              <th>CIRCULATION COUNTER</th>
              <th>REFERENCE DESK</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>SUNDAY-THURSDAY</td>
              <td>9:00am - 5:30pm</td>
              <td>9:00am - 5:30pm</td>
              <td>9:00am - 5:30pm</td>
            </tr>
            <tr>
              <td>FRIDAY</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
            </tr>
            <tr>
              <td>SATURDAY</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
              <td>CLOSED</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Overlay -->
    <div id="overlay" class="overlay" onclick="hidePopup()"></div>
    <!-- Content Wrapper -->
    <div class="login-panel">
      <div class="login-header">
        <h4>Login Portal</h4>
        <p class="login-subtitle">Select your account type</p>
      </div>
      <div class="login-buttons">
        <button class="login-button admin-btn" type="button" onclick="showPopup('Admin')">
          <span class="button-icon"><i class="fas fa-user-shield"></i></span>
          <div class="button-text">
            <span class="button-title">Admin Login</span>
            <span class="button-desc">For library staff</span>
          </div>
        </button>
        <button class="login-button user-btn" type="button" onclick="showPopup('User')">
          <span class="button-icon"><i class="fas fa-user-graduate"></i></span>
          <div class="button-text">
            <span class="button-title">User Login</span>
            <span class="button-desc">For students & faculty</span>
          </div>
        </button>
      </div>
    </div>
    </div>

    <!-- Login Popup -->
    <div id="loginPopup" class="popup" role="dialog" aria-labelledby="popup-title" aria-modal="true">
      <button type="button" class="close-btn" onclick="hidePopup()" aria-label="Close popup">×</button>
      <h2 id="popup-title">Login</h2>
      <form id="loginForm" action="login_process.php" method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group password-group">
          <input id="password" type="password" name="password" placeholder="Password" required>
          <span class="toggle-password" onclick="togglePassword()">
            <i id="eyeIcon" class="fas fa-eye-slash"></i>
          </span>
        </div>
        <a id="forgotPasswordLink" class="forgot-password-link" style="display: none;" href="recovery_password.php">Forgot Password?</a>
        <input type="hidden" id="userType" name="userType" value="">
        <button type="submit" class="login-submit-btn">Login</button>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="modern-footer">
    <div class="footer-container">
      <div class="footer-section">
        <h3>About Us</h3>
        <p>Perpustakaan Al-Hazen is dedicated to providing quality resources and services to support learning, teaching, and research needs of our community.</p>
        <div class="social-links">
          <a href="https://www.facebook.com/profile.php?id=100005941068125"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="footer-section">
        <h3>Contact Info</h3>
        <div class="contact-info">
          <i class="fas fa-map-marker-alt"></i>
          <p>Jalan Kajang-dengkil, Batu 24, 43000 Dengkil, Selangor</p>
        </div>
        <div class="contact-info">
          <i class="fas fa-phone"></i>
          <p>+60 3-8768 1111</p>
        </div>
        <div class="contact-info">
          <i class="fas fa-envelope"></i>
          <p>info@perpustakaan-alhazen.edu.my</p>
        </div>
      </div>

      <div class="footer-section">
        <h3>Quick Links</h3>
        <a href="#">Home</a>
        <a href="#">Library Catalog</a>
        <a href="#">E-Resources</a>
        <a href="#">Research Guides</a>
        <a href="#">Library Services</a>
      </div>
    </div>

    <div class="map-container">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.7060019169876!2d101.72446527449983!3d2.9007907546301506!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cdc83f748b3aa3%3A0x6727e45b4e8e3654!2sSepang%20Vocational%20College!5e0!3m2!1sen!2smy!4v1733905405383!5m2!1sen!2smy"></iframe>
    </div>

    <div class="footer-bottom">
      <p>© 2024 Perpustakaan Al-Hazen. All rights reserved.</p>
    </div>
  </footer>
  <!-- Scripts -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script>
    function toggleAdvancedSearch() {
      const advancedSearch = document.querySelector('.advanced-search');
      const toggleText = document.getElementById('toggleText');
      advancedSearch.classList.toggle('show');
      toggleText.textContent = advancedSearch.classList.contains('show') ?
        'Hide Advanced Search' :
        'Show Advanced Search';
    }

    // Counter Animation
    const targetCount = 280898;
    const countDisplay = document.getElementById('count-display');
    let currentCount = 0;

    function animateCount() {
      if (currentCount < targetCount) {
        currentCount += Math.ceil(targetCount / 280);
        countDisplay.textContent = currentCount.toLocaleString();
        requestAnimationFrame(animateCount);
      } else {
        countDisplay.textContent = targetCount.toLocaleString();
      }
    }

    setTimeout(() => {
      animateCount();
    }, 500);

    // Schedule Functions
    function showSchedule(type) {
      // Hide all schedules
      document.getElementById('exam-schedule').classList.add('hidden');
      document.getElementById('lecture-schedule').classList.add('hidden');
      document.getElementById('semester-schedule').classList.add('hidden');

      // Show selected schedule
      document.getElementById(`${type}-schedule`).classList.remove('hidden');

      // Update button states
      const buttons = document.querySelectorAll('.schedule-button');
      buttons.forEach(button => {
        button.classList.remove('active');
        if (button.textContent.toLowerCase() === type.toUpperCase()) {
          button.classList.add('active');
        }
      });
    }

    function showPopup(userType) {
      document.getElementById('overlay').style.display = 'block';
      document.getElementById('loginPopup').style.display = 'block';
      document.getElementById('userType').value = userType;
      document.getElementById('popup-title').innerText = userType + ' Login';
    }

    function hidePopup() {
      document.getElementById('overlay').style.display = 'none';
      document.getElementById('loginPopup').style.display = 'none';
    }

    function handleFormSubmit(event) {
      event.preventDefault();
      alert('Login submitted for ' + document.getElementById('userType').value + '!');
      hidePopup();
    }

    // Array of quotes
    const quotes = [
      '"Your only limit is your mind."',
      '"Success is the sum of small efforts, repeated day in and day out."',
      '"Dream big and dare to fail."',
      '"The only way to do great work is to love what you do."',
      '"Believe you can and you\'re halfway there."'
    ];

    // Function to change quote every 5 seconds with fade effect
    let currentQuoteIndex = 0;

    function changeQuote() {
      const quoteElement = document.getElementById('quote-of-the-day');

      // Add fade class to initiate fade animation
      quoteElement.classList.add('fade');

      setTimeout(() => {
        // Change the quote text after fade animation
        currentQuoteIndex = (currentQuoteIndex + 1) % quotes.length;
        quoteElement.textContent = quotes[currentQuoteIndex];

        // Remove the fade class to reset the animation
        quoteElement.classList.remove('fade');
      }, 2500); // Half of the fade-in/out time (2.5 seconds)
    }

    // Change the quote every 5 seconds
    setInterval(changeQuote, 5000);

    // Login status variable (replace with your actual login logic)
    let isLoggedIn = false; // Set to true if the user is logged in

    // Function to show a login alert
    function showLoginAlert(event) {
      if (!isLoggedIn) {
        event.preventDefault(); // Prevent navigation
        alert("Please log in to access this feature."); // Alert message
      }
    }

    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const eyeIcon = document.getElementById("eyeIcon");

      if (passwordInput.type === "password") {
        passwordInput.type = "text"; // Show password
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
      } else {
        passwordInput.type = "password"; // Hide password
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
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

    function showPopup(userType) {
      document.getElementById('userType').value = userType;
      const forgotPasswordLink = document.getElementById('forgotPasswordLink');

      if (userType === 'User') {
        forgotPasswordLink.style.display = 'block'; // Show link for User
      } else {
        forgotPasswordLink.style.display = 'none'; // Hide link for Admin
      }

      // Show the popup (add your popup showing logic here)
      document.getElementById('loginPopup').style.display = 'block';
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