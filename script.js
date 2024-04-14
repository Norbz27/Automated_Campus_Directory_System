document.getElementById("getStartedBtn").addEventListener("click", function() {
    window.location.href = "room_navigation.php";
});

function showNavigationPage() {
    document.querySelector('.landing-page').style.display = 'none';
    document.querySelector('.navigation-page').style.display = 'block';
}

