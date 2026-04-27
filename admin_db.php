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

$roleQuery = "SELECT full_name, role FROM users WHERE account_id = ?";
$stmt = $conn->prepare($roleQuery);
$stmt->bind_param("s", $account_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$full_name = $user['full_name'] ?? 'Admin';

$empQuery = "SELECT COUNT(*) as total_employees FROM users WHERE role = 'employee'";
$empResult = $conn->query($empQuery);
$emp = $empResult->fetch_assoc();

$attQuery = "SELECT 
    COUNT(*) as total_attendance,
    COALESCE(SUM(status = 'Late'), 0) as total_late,
    COALESCE(SUM(status = 'Absent'), 0) as total_absent
FROM attendance";

$attResult = $conn->query($attQuery);
$att = $attResult->fetch_assoc();

$leaveQuery = "SELECT COUNT(*) as total_leave FROM leave_requests";
$leaveResult = $conn->query($leaveQuery);
$leave = $leaveResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

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

.container {
  display: flex;
  height: 100vh;
}

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
  font-size: 22px;
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
      <h2>LUXARA ADMIN</h2>

      <div class="menu">
        <a class="active" href="admin_db.php">Dashboard</a>
        <a href="manage_employees.php">Employees</a>
        <a href="attendance_records.php">Attendance</a>
        <a href="leave_requests.php">Leave Requests</a>
        <a href="salary_management.php">Salary</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <div class="main">

    <div class="topbar">

      <div class="topbar-left">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars(trim($full_name)) . '!' ?></p>
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
        <h3>Total Employees</h3>
        <h2><?php echo $emp['total_employees'] ?? 0; ?></h2>
      </div>

      <div class="card">
        <h3>Total Attendance</h3>
        <p><?php echo $att['total_attendance'] ?? 0; ?> records</p>
      </div>

      <div class="card">
        <h3>Late Employees</h3>
        <p><?php echo $att['total_late'] ?? 0; ?></p>
      </div>

      <div class="card">
        <h3>Absent Employees</h3>
        <p><?php echo $att['total_absent'] ?? 0; ?></p>
      </div>

      <div class="card">
        <h3>Leave Requests</h3>
        <p><?php echo $leave['total_leave'] ?? 0; ?> pending</p>
      </div>

    </div>

  </div>

</div>

<script>
function updateClock() {
    const now = new Date();
    let h = now.getHours();
    let m = now.getMinutes();
    let s = now.getSeconds();

    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;

    document.getElementById("clock").innerHTML =
        `${h}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')} ${ampm}`;
}

function updateDate() {
    const now = new Date();
    document.getElementById("date").innerHTML =
        now.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
}

updateDate();
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
