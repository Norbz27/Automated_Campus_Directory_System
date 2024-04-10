<?php 
include_once 'header.php';

include_once 'db_con/db.php';

$sql = "SELECT name, username, password FROM tbl_account";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $username = $row['username'];
    $password = $row['password'];
    // Hide password
    $hidden_password = str_repeat("*", strlen($password));
} else {
    echo "0 results";
}

$conn->close();
?>

<!--  Header End -->
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Profile -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <br>
                            <!-- Display fetched user information -->
                            <h1><b><?php echo $name; ?></b></h1>
                            <h6 class="mb-5">ADMIN</h6>
                            <h6 class="mb-2"><b>User Credentials</b></h6>
                            <span>Username: <?php echo $username; ?></span> <br>
                            <span>Password: <?php echo $hidden_password; ?></span>
                            <br><br>
                            <!-- Button to trigger the modal -->
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal"><i class="ti ti-pencil"></i></button>
                        </div>
                        <div class="col-md-6">
                            <!-- Profile Picture-->
                            <img src="assets/images/profile/user-1.jpg" alt="" width="250" height="250" class="rounded-circle">
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Profile -->
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit User Information</h5>
            </div>
            <div class="modal-body">
                <!-- Form to edit user information -->
                <form action="update_user.php" method="post">
                    <div class="form-group mb-2">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
                    </div>
                    <div class="form-group mb-2">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>">
                    </div>
                    <div class="form-group mb-2">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo $password; ?>">
                    </div>
                    
                    <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Save Changes</button>
                     </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once 'footer.php'; ?>
