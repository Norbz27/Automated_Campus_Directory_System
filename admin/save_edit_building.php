<?php
// Include database connection
include_once 'db_con/db.php';

if (isset($_POST['save_edit'])) {
    $building_label = $_POST['building_label'];
    $building_id = $_POST['building_id'];
    $edit_building_img = $_POST['edit_building_img'];

    if (!empty($_FILES['adprofile_building']['name'])) {
        $targetDir = "assets/images/";
        $targetFile = $targetDir . basename($_FILES['adprofile_building']['name']);

        if (move_uploaded_file($_FILES['adprofile_building']['tmp_name'], $targetFile)) {
            $roomImage = $_FILES['adprofile_building']['name'];
        } else {
            $res = [
                'status' => 405,
                'message' => 'Error uploading image'
            ];
            echo json_encode($res);
            exit;
        }
    } else {
        $roomImage = $edit_building_img; // Use the existing image if no new image is uploaded
    }

    $query = "UPDATE tbl_building SET label = ?, building_image = ? WHERE building_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "ssi", $building_label, $roomImage, $building_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_errno($stmt) != 0) {
        $res = [
            'status' => 500,
            'message' => 'Error in query: ' . mysqli_stmt_error($stmt)
        ];
    } else {
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        if ($affectedRows > 0) {
            $res = [
                'status' => 200,
                'message' => 'Building location is updated successfully',
                'affected_rows' => $affectedRows
            ];
        } else {
            $res = [
                'status' => 404,
                'message' => 'Building location is not updated successfully'
            ];
        }
    }

    mysqli_stmt_close($stmt);

    echo json_encode($res);
}