<?php
// Include database connection
include_once 'db_con/db.php';

if (isset($_POST['save_edit'])) {
    $edit_location_label = $_POST['edit_location_label'];
    $location_id = $_POST['edit_location_id'];
    $edit_location_img = $_POST['edit_location_img'];

    if (!empty($_FILES['adprofile']['name'])) {
        $targetDir = "assets/images/";
        $targetFile = $targetDir . basename($_FILES['adprofile']['name']);

        if (move_uploaded_file($_FILES['adprofile']['tmp_name'], $targetFile)) {
            $roomImage = $_FILES['adprofile']['name'];
        } else {
            $res = [
                'status' => 405,
                'message' => 'Error uploading image'
            ];
            echo json_encode($res);
            exit;
        }
    } else {
        $roomImage = $edit_location_img; // Use the existing image if no new image is uploaded
    }

    $query = "UPDATE tbl_locations SET label = ?, location_image = ? WHERE location_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "ssi", $edit_location_label, $roomImage, $location_id);
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
                'message' => 'Location is updated successfully',
                'affected_rows' => $affectedRows
            ];
        } else {
            $res = [
                'status' => 404,
                'message' => 'Location is not updated successfully'
            ];
        }
    }

    mysqli_stmt_close($stmt);

    echo json_encode($res);
}