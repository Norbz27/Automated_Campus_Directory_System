<?php
include_once 'admin/db_con/db.php';

if(isset($_POST['query'])) {
    $search = $_POST['query'];
    $sql = "SELECT b.label, f.name, r.room_name 
            FROM tbl_rooms r 
            LEFT JOIN tbl_floors f ON r.floor_id = f.floor_id 
            LEFT JOIN tbl_building b ON f.building_id = b.building_id 
            WHERE r.room_name LIKE '%$search%'
            OR f.name LIKE '%$search%'
            OR b.label LIKE '%$search%'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo '<p>'.$row['label'].' - '.$row['name'].' - '.$row['room_name'].'</p>';
        }
    } else {
        echo '<p>No results found</p>';
    }
}
$conn->close();
?>
