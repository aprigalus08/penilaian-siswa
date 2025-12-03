<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    $data  = mysqli_fetch_assoc($query);

    if ($data) {
        $_SESSION['admin'] = $data;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Admin</title>
    <style>
        body {
            background: #e9eef2;
            font-family: Arial;
        }
        .box {
            width: 350px;
            margin: 120px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid #aaa;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
        .error {
            background: #ffb3b3;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            color: #700;
        }
    </style>
</head>
<body>

<div class="box">
    <h2 align="center">Login Admin</h2>

    <?php if (!empty($error)) { ?>
        <div class="error"><?= $error ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button name="login">Login</button>
    </form>
</div>

</body>
</html>
