<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $account_id = $_POST['account_id'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $users = $result->fetch_assoc();

        if (password_verify($password, $users['password'])) {

            $_SESSION['account_id'] = $users['account_id'];
            $_SESSION['full_name'] = $users['full_name'];
            $_SESSION['role'] = $users['role'];

            $role = strtolower(trim($users['role']));

            if ($role === 'admin') {
                header("Location: admin_db.php");
                exit();
            } elseif ($role === 'employee') {
                header("Location: emp_db.php");
                exit();
            } else {
                $error = "Unauthorized role detected.";
            }

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "Account ID not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
}

body::before {
    content: "";
    position: absolute;
    inset: 0;
    background: url("bg.jpg");
    background-size: cover;
    background-position: center;
    z-index: -1;
}

.container {
    width: 360px;
    padding: 30px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.85);
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    text-align: center;
}

.container h2 {
    margin-bottom: 8px;
}

.container p {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
}

.input-group {
    margin-bottom: 15px;
    text-align: left;
}

.input-group label {
    font-size: 13px;
    color: #333;
}

.input-group input {
    width: 100%;
    padding: 11px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 10px;
    outline: none;
    transition: 0.3s;
}

.input-group input:focus {
    border-color: #5f7cff;
    box-shadow: 0 0 0 2px rgba(95,124,255,0.2);
}

.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 40px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 70%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #777;
}

.toggle-password:hover {
    color: #333;
}

.options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    margin-bottom: 15px;
}

.options a {
    color: #5f7cff;
    text-decoration: none;
}

.remember {
    display: flex;
    align-items: center;
    gap: 6px;
}

.remember input[type="checkbox"] {
    accent-color: #5f7cff;
}

.btn {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #5f7cff, #7a8cff);
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    margin-bottom: 10px;
    transition: 0.3s;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.google-btn {
    background: #fff;
    color: #333;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.google-btn:hover {
    background: #f5f5f5;
}

.signup {
    font-size: 12px;
    margin-top: 5px;
}

.signup a {
    color: #5f7cff;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="container">
    <h2>Welcome Back!</h2>
    <p>Please enter your details.</p>

    <?php if (!empty($error)) : ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        
        <div class="input-group">
            <label>Account ID</label>
            <input type="text" name="account_id" placeholder="Enter your Account ID" required>
        </div>

        <div class="input-group password-wrapper">
            <label>Password</label>
            <input type="password" name="password" id="password" placeholder="Enter Password" required>
            <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
        </div>

        <div class="options">
            <label class="remember">
                <input type="checkbox"> Remember me
            </label>
            <a href="#">Forgot password?</a>
        </div>

        <button class="btn" type="submit">Log in</button>
    </form>
</div>

<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

</body>
</html>