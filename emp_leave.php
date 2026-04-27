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

/* USER */
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_leave'])) {

    $start = $_POST['date_from'] ?? '';
    $end = $_POST['date_to'] ?? '';
    $reason = $_POST['reason'] ?? '';

    $insert = "INSERT INTO leave_requests (account_id, date_from, date_to, reason, status, created_at)
               VALUES (?, ?, ?, ?, 'Pending', NOW())";

    $stmt2 = $conn->prepare($insert);
    $stmt2->bind_param("isss", $account_id, $start, $end, $reason);
    $stmt2->execute();
}

$leaveStats = "SELECT 
    COUNT(*) as total,
    COALESCE(SUM(status='Pending'),0) as pending,
    COALESCE(SUM(status='Approved'),0) as approved,
    COALESCE(SUM(status='Rejected'),0) as rejected
FROM leave_requests
WHERE account_id = ?";

$stmt3 = $conn->prepare($leaveStats);
$stmt3->bind_param("s", $account_id);
$stmt3->execute();
$stats = $stmt3->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Leave</title>

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

input, textarea {
  width: 100%;
  padding: 10px;
  margin-top: 8px;
  border-radius: 10px;
  border: 1px solid #ddd;
}

button {
  margin-top: 10px;
  padding: 10px 14px;
  background: #5f7cff;
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
}

.stat {
  margin-top: 8px;
  color: #555;
}
</style>
</head>

<body>

<div class="container">

  <div class="sidebar">
    <div>
      <h2>LUXARA</h2>

      <div class="menu">
        <a href="emp_dashboard.php">Dashboard</a>
        <a href="emp_attendance.php">Attendance</a>
        <a class="active" href="emp_leave.php">Leave Request</a>
        <a href="emp_salary.php">Salary</a>
        <a href="emp_profile.php">Profile</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <div class="main">

    <div class="topbar">

      <div class="topbar-left">
        <h1>Leave Request</h1>
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

      <div class="card">
        <h3>Request Leave</h3>

        <form method="POST">
          <label>Start Date</label>
          <input type="date" name="date_from" required>

          <label>End Date</label>
          <input type="date" name="date_to" required>

          <label>Reason</label>
          <textarea name="reason" rows="3" required></textarea>

          <button type="submit" name="submit_leave">Submit</button>
        </form>
      </div>


      <div class="card">
        <h3>Leave Overview</h3>

        <div class="stat">Total: <?= $stats['total'] ?? 0 ?></div>
        <div class="stat">Pending: <?= $stats['pending'] ?? 0 ?></div>
        <div class="stat">Approved: <?= $stats['approved'] ?? 0 ?></div>
        <div class="stat">Rejected: <?= $stats['rejected'] ?? 0 ?></div>
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
    h = h % 12;
    h = h ? h : 12;

    m = m < 10 ? '0' + m : m;
    s = s < 10 ? '0' + s : s;

    document.getElementById("clock").innerHTML =
        h + ":" + m + ":" + s + " " + ampm;
}

function updateDate() {
    const now = new Date();
    document.getElementById("date").innerHTML =
        now.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
}

updateDate();
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
