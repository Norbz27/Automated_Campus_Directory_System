<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../admin/css/admin-css.css">
    <link rel="stylesheet" href="../admin/css/header.css">
</head>
<body>
  <?php
    include_once '../admin/header.php';
  ?>
  <div class="container-fluid">
    <form class="mx-auto" action="../admin">
      <h4 class="text-center" style="color: black;">Login</h4>
      <div class="form-group mb-3 mt-5">
        <label for="exampleInputEmail1">User name</label>
        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
      </div>
      <div class="form-group mb-3">
        <label for="exampleInputPassword1">Password</label>
        <input type="password" class="form-control" id="exampleInputPassword1">
      </div>
      <div id="emailHelp" class="form-text">
        Forgot password?
      </div>
      <button type="submit" class="btn btn-primary mt-5">Login</button>
    </form>
  </div>
</body>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.min.js"></script>
</html>