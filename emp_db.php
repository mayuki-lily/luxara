<?php
session_start();

if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "luxara_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$account_id = $_SESSION['account_id'];

$userQuery = "SELECT * FROM users WHERE account_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $account_id);
$stmt->execute();
$userResult = $stmt->get_result();
$users = $userResult->fetch_assoc();

if (!$users) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$full_name = $users['full_name'] ?? 'Employee';

$attQuery = "SELECT 
    COUNT(*) as total_attendance,
    COALESCE(SUM(status = 'Late'), 0) as total_late,
    COALESCE(SUM(status = 'Absent'), 0) as total_absent
FROM attendance
WHERE account_id = ?";

$stmt2 = $conn->prepare($attQuery);
$stmt2->bind_param("s", $account_id);
$stmt2->execute();
$attResult = $stmt2->get_result();
$att = $attResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", sans-serif;
}

body {
  background: #f4f6fb;
  font-size: 14px;
}

h1 { font-size: 22px; }
h2 { font-size: 18px; }
h3 { font-size: 16px; }
p  { font-size: 13px; }

.container {
  display: flex;
  height: 100vh;
}

/* SIDEBAR */
.sidebar {
  width: 250px;
  background: #fff;
  box-shadow: 2px 0 20px rgba(0,0,0,0.05);
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.sidebar h2 {
  color: #5f7cff;
  margin-bottom: 30px;
  font-size: 20px;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.menu a {
  text-decoration: none;
  color: #555;
  padding: 10px 14px;
  border-radius: 10px;
}

.menu a:hover {
  background: #eef1ff;
  color: #5f7cff;
}

.menu .active {
  background: #5f7cff;
  color: white;
}

.logout {
  color: #ff5c5c;
  text-decoration: none;
  padding: 10px 14px;
}

.main {
  flex: 1;
  padding: 25px;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
}

.topbar-left h1 {
  margin-bottom: 5px;
}

.topbar-left p {
  color: #666;
  font-size: 13px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.clock {
  font-weight: 500;
  color: #333;
}

.notification {
  font-size: 18px;
  color: #5f7cff;
  cursor: pointer;
  position: relative;
}

.notification::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 8px;
  height: 8px;
  background: red;
  border-radius: 50%;
}

.cards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.card {
  background: white;
  padding: 18px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

.gradient {
  background: #5f7cff;
  color: white;
}
</style>
</head>

<body>

<div class="container">

  <div class="sidebar">
    <div>
      <h2>LUXARA</h2>

      <div class="menu">
        <a class="active" href="emp_dashboard.php">Dashboard</a>
        <a href="emp_attendance.php">Attendance</a>
        <a href="emp_leave.php">Leave Request</a>
        <a href="emp_salary.php">Salary</a>
        <a href="emp_profile.php">Profile</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <div class="main">

    <div class="topbar">

      <div class="topbar-left">
        <h1>Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($full_name); ?></p>
      </div>

      <div class="topbar-right">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
        </div>

        <div style="text-align:right; line-height:1.2;">
          <div style="font-size:12px; color:#666;" id="date"></div>
          <div class="clock" id="clock"></div>
        </div>
      </div>

    </div>

    <div class="cards">

      <div class="card gradient">
        <h3>Attendance</h3>
        <p>Total Records: <?php echo $att['total_attendance'] ?? 0; ?></p>
        <p>Late: <?php echo $att['total_late'] ?? 0; ?></p>
        <p>Absent: <?php echo $att['total_absent'] ?? 0; ?></p>
      </div>

      <div class="card">
        <h3>Leave Request</h3>
        <p>Apply or track leave status</p>
      </div>

      <div class="card">
        <h3>Salary</h3>
        <p>Check monthly payroll</p>
      </div>

      <div class="card">
        <h3>Profile</h3>
        <p>Update your information</p>
      </div>

    </div>

  </div>

</div>

<script>
function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    let ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;

    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    document.getElementById("clock").innerHTML =
        hours + ":" + minutes + ":" + seconds + " " + ampm;
}

function updateDate() {
    const now = new Date();

    const options = { year: 'numeric', month: 'long', day: 'numeric' };

    document.getElementById("date").innerHTML =
        now.toLocaleDateString('en-US', options);
}

updateDate();
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>