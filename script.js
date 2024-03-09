document.getElementById("getStartedBtn").addEventListener("click", function() {
    window.location.href = "navigation_input_page.php";
});

function showNavigationPage() {
    document.querySelector('.landing-page').style.display = 'none';
    document.querySelector('.navigation-page').style.display = 'block';
}

