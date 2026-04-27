<?php
session_start();
include "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $role       = "employee";

    $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/";

    if (
        empty($first_name) || empty($last_name) ||
        empty($username) || empty($email) ||
        empty($department) || empty($password)
    ) {
        $error = "Please fill in all fields";
    }

    elseif (!preg_match($password_pattern, $password)) {
        $error = "Password must contain letters and numbers and must be 8–20 characters long.";
    }

    elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    }

    else {

        // Check duplicate email
        $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Email already exists";
        }

        else {

            // Check duplicate username
            $checkUser = $conn->prepare("SELECT username FROM users WHERE username = ?");
            $checkUser->bind_param("s", $username);
            $checkUser->execute();
            $user_result = $checkUser->get_result();

            if ($user_result->num_rows > 0) {
                $error = "Username already exists";
            }

            else {

                // Full name
                $full_name = $first_name . " " . $last_name;

                // Generate Employee ID
                $prefix = "EMP";

                $sql = "SELECT account_id 
                        FROM users 
                        WHERE account_id LIKE ? 
                        ORDER BY CAST(SUBSTRING(account_id, 4) AS UNSIGNED) DESC 
                        LIMIT 1";

                $stmt = $conn->prepare($sql);
                $like = $prefix . "%";
                $stmt->bind_param("s", $like);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    $last_id = $row['account_id'];
                    $number = (int) substr($last_id, 3);
                    $number++;
                } else {
                    $number = 1;
                }

                $account_id = $prefix . str_pad($number, 3, "0", STR_PAD_LEFT);

                // Secure password (better than MD5)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $insert = $conn->prepare("
                    INSERT INTO users 
                    (account_id, full_name, username, email, password, role, department) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $insert->bind_param(
                    "sssssss",
                    $account_id,
                    $full_name,
                    $username,
                    $email,
                    $hashed_password,
                    $role,
                    $department
                );

                if ($insert->execute()) {
                    $success = "Account created! Your ID is: " . $account_id;
                } else {
                    $error = "Something went wrong. Try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up</title>

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
    width: 420px;
    padding: 30px;
    border-radius: 20px;
    background: rgba(255,255,255,0.9);
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.container h2 {
    text-align: center;
    margin-bottom: 10px;
}

.container p {
    text-align: center;
    font-size: 14px;
    margin-bottom: 20px;
    color: #666;
}

.form-row {
    display: flex;
    gap: 10px;
}

.input-group {
    margin-bottom: 15px;
    width: 100%;
}

.input-group label {
    font-size: 13px;
    color: #333;
}

.input-group input,
.input-group select {
    width: 100%;
    padding: 11px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 10px;
    outline: none;
}

.btn {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #5f7cff, #7a8cff);
    color: white;
    cursor: pointer;
}

.login-link {
    text-align: center;
    margin-top: 15px;
    font-size: 13px;
    color: #666;
}

.login-link a {
    color: #5f7cff;
    text-decoration: none;
    margin-left: 5px;
    transition: 0.2s ease;
}

.login-link a:hover {
    color: #3f5eff;
}

.error {
    color: red;
    font-size: 13px;
    margin-bottom: 10px;
    text-align: center;
}

.success {
    color: green;
    font-size: 13px;
    margin-bottom: 10px;
    text-align: center;
}
</style>
</head>

<body>

<div class="container">
    <h2>Create Account</h2>
    <p>Fill in your details</p>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-row">
            <div class="input-group">
                <label>First Name</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="input-group">
                <label>Last Name</label>
                <input type="text" name="last_name" required>
            </div>
        </div>

        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Department</label>
            <select name="department" required>
                <option value="">Select Department</option>
                <option>HR</option>
                <option>IT</option>
                <option>Finance</option>
                <option>Operations</option>
            </select>
        </div>

        <div class="form-row">
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
        </div>

        <button class="btn" type="submit">Sign Up</button>

        <div class="login-link">
            Already have an account?<a href="login.php">Login</a>
        </div>
    </form>

</div>

</body>
</html>
