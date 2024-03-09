<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEC MAP</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script type="text/javascript" src="js/googlemap.js"></script>
    <style type="text/css">
        .container{
            height: 450px;
        }
        #map{
            width: 100%;
            height: 100%;
            border: 1px solid blue;
        }
    </style>
</head>
<body>
    <div class="container">
        <center><h1>SEC MAP</h1></center>
        <div id="map"></div>
    </div>
</body>
<script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD6scMOeZ5vYTKLisqtHo9RA0bClZ0fi2Y&loading=async&callback=initMap">
</script>
</html>