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
document.addEventListener("DOMContentLoaded", function () {
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
