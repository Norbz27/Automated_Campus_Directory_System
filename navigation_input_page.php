<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Input Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navigation-page-container">
        <h1>Navigation Input</h1>
        <div class="navigation-page" style="display: none;">
            <h2>Select a Location or Enter a Specific Destination:</h2>
            <select id="locations-dropdown">
                <option value="">Select a Location</option>
                <option value="registrar">Registrar</option>
                <option value="finance">Finance</option>
                <option value="guidance">Guidance Office</option>
                <option value="OSAS">Office of Student Affairs and Services</option>
                <option value="GS">Grade School Office</option>
                <option value="MO">Montessori Office</option>
                <option value="HS">High School Office</option>
                <option value="CEA">Engineering Department Office</option>
                <option value="CEA">Architecture Department Office</option>
                <option value="CAMS">Medical Department Office</option>
                <option value="CBE">Accountacy/Business Administration Department Office</option>
                <option value="CIT">Information Technology Department Office</option>                 
                <option value="CME">Maritime Department Office</option>
                <option value="MTC">Maritime Training Center</option>
                <option value="tvet">TVET Office</option>
                <option value="avr">AVR</option>
                <option value="MIS">Management Information System</option>
                <option value="library">Library</option>
                <option value="cafeteria">Cafeteria</option>
                <option value="lecture-hall">Amphitheater</option>
                <option value="cr">Comfort Room</option>
            </select>
            <input type="text" id="manual-input" placeholder="Enter a location or room number/name">
            <!-- Assigning the navigate() function to the button's onclick attribute -->
            <button onclick="navigate()">Navigate</button>
        </div>
    </div>

    <script>
        function showNavigationPage() {
            var navigationPage = document.querySelector('.navigation-page');
            navigationPage.style.display = 'block';
        }

        function navigate() {
            var selectedLocation = document.getElementById('locations-dropdown').value;
            var manualInput = document.getElementById('manual-input').value;
            
            // You can handle navigation based on the selected location or manual input here
            console.log("Selected Location: " + selectedLocation);
            console.log("Manual Input: " + manualInput);
            // Add your navigation logic here...
        }
    </script>

    <script>
        // Calling the function to show the navigation page immediately upon loading
        showNavigationPage();
    </script>
</body>
</html>
