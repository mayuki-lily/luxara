<<<<<<< HEAD
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

/* GET USER INFO */
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

/* GET ATTENDANCE RECORDS */
$attQuery = "SELECT * FROM attendance WHERE account_id = ? ORDER BY date DESC";
$stmt2 = $conn->prepare($attQuery);
$stmt2->bind_param("s", $account_id);
$stmt2->execute();
$attResult = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance</title>

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

/* LAYOUT */
.container {
  display: flex;
  min-height: 100vh;
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
  transition: 0.2s;
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

/* MAIN */
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

.topbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
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

.clock {
  font-weight: 500;
  color: #333;
}

/* DATE TEXT */
#date {
  font-size: 12px;
  color: #777;
  text-align: right;
}

/* TABLE BOX */
.table-container {
  background: white;
  padding: 22px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* TABLE */
table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  text-align: left;
  padding: 14px 12px;
  border-bottom: 1px solid #eee;
}

th {
  background: #f7f8ff;
  color: #5f7cff;
  font-weight: 600;
}

.status {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}

.present { background: #e7f8ee; color: #1aa36f; }
.late { background: #fff4e5; color: #d98b00; }
.absent { background: #ffe7e7; color: #d93025; }

</style>
</head>

<body>

<div class="container">

  <div class="sidebar">
    <div>
      <h2>LUXARA</h2>

      <div class="menu">
        <a href="emp_db.php">Dashboard</a>
        <a class="active" href="emp_attendance.php">Attendance</a>
        <a href="emp_leave.php">Leave Request</a>
        <a href="emp_salary.php">Salary</a>
        <a href="emp_profile.php">Profile</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <!-- MAIN -->
  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1>Attendance</h1>
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

    <div class="table-container">

      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($attResult->num_rows > 0): ?>
            <?php while ($row = $attResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['time_in'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['time_out'] ?? '-'); ?></td>
                <td>
                  <?php
                    $status = $row['status'];
                    $class = strtolower($status);

                    echo "<span class='status $class'>" . htmlspecialchars($status) . "</span>";
                  ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">No attendance records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>

      </table>

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
=======
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

/* GET USER INFO */
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

/* GET ATTENDANCE RECORDS */
$attQuery = "SELECT * FROM attendance WHERE account_id = ? ORDER BY date DESC";
$stmt2 = $conn->prepare($attQuery);
$stmt2->bind_param("s", $account_id);
$stmt2->execute();
$attResult = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance</title>

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

/* LAYOUT */
.container {
  display: flex;
  min-height: 100vh;
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
  transition: 0.2s;
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

/* MAIN */
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

.topbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
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

.clock {
  font-weight: 500;
  color: #333;
}

/* DATE TEXT */
#date {
  font-size: 12px;
  color: #777;
  text-align: right;
}

/* TABLE BOX */
.table-container {
  background: white;
  padding: 22px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* TABLE */
table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  text-align: left;
  padding: 14px 12px;
  border-bottom: 1px solid #eee;
}

th {
  background: #f7f8ff;
  color: #5f7cff;
  font-weight: 600;
}

.status {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}

.present { background: #e7f8ee; color: #1aa36f; }
.late { background: #fff4e5; color: #d98b00; }
.absent { background: #ffe7e7; color: #d93025; }

</style>
</head>

<body>

<div class="container">

  <div class="sidebar">
    <div>
      <h2>LUXARA</h2>

      <div class="menu">
        <a href="emp_db.php">Dashboard</a>
        <a class="active" href="emp_attendance.php">Attendance</a>
        <a href="emp_leave.php">Leave Request</a>
        <a href="emp_salary.php">Salary</a>
        <a href="emp_profile.php">Profile</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <!-- MAIN -->
  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1>Attendance</h1>
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

    <div class="table-container">

      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($attResult->num_rows > 0): ?>
            <?php while ($row = $attResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['time_in'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['time_out'] ?? '-'); ?></td>
                <td>
                  <?php
                    $status = $row['status'];
                    $class = strtolower($status);

                    echo "<span class='status $class'>" . htmlspecialchars($status) . "</span>";
                  ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">No attendance records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>

      </table>

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
>>>>>>> eb79aaab1dfe4fed3e174172b57a4227c0cd9f74
</html>