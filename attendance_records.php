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

$roleQuery = "SELECT full_name, role FROM users WHERE account_id = ?";
$stmt = $conn->prepare($roleQuery);
$stmt->bind_param("s", $account_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user || $user['role'] !== 'admin') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$sql = "SELECT 
            a.id,
            a.date,
            a.time_in,
            a.time_out,
            a.status,
            CASE 
                WHEN u.full_name IS NULL OR TRIM(u.full_name) = '' 
                THEN '—'
                ELSE u.full_name
            END AS full_name
        FROM attendance a
        LEFT JOIN users u ON a.account_id = u.account_id
        ORDER BY a.date DESC, a.time_in DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Records</title>

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
  overflow-y: auto;
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

.card {
  background: #fff;
  padding: 18px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #5f7cff;
  color: #fff;
}

.status {
  padding: 5px 10px;
  border-radius: 8px;
  color: #fff;
  font-size: 12px;
}

.present {
  background: #2ecc71;
}

.late {
  background: #f39c12;
}

.absent {
  background: #e74c3c;
}
</style>
</head>

<body>

<div class="container">

  <div class="sidebar">
    <div>
      <h2>LUXARA ADMIN</h2>

      <div class="menu">
        <a href="admin_db.php">Dashboard</a>
        <a href="manage_employees.php">Employees</a>
        <a class="active" href="attendance_records.php">Attendance</a>
        <a href="leave_requests.php">Leave Requests</a>
        <a href="salary_management.php">Salary</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <div class="main">

    <div class="topbar">

      <div class="topbar-left">
        <h1>Attendance Records</h1>
        <p>Manage attendance records.</p>
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

    <div class="card">

      <table>
        <tr>
          <th>ID</th>
          <th>Employee Name</th>
          <th>Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Status</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['full_name']); ?></td>
          <td><?php echo $row['date']; ?></td>
          <td><?php echo $row['time_in']; ?></td>
          <td><?php echo $row['time_out']; ?></td>
          <td>
            <span class="status <?php echo strtolower($row['status']); ?>">
              <?php echo $row['status']; ?>
            </span>
          </td>
        </tr>
        <?php } ?>

      </table>

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

$roleQuery = "SELECT full_name, role FROM users WHERE account_id = ?";
$stmt = $conn->prepare($roleQuery);
$stmt->bind_param("s", $account_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user || $user['role'] !== 'admin') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$sql = "SELECT 
            a.id,
            a.date,
            a.time_in,
            a.time_out,
            a.status,
            CASE 
                WHEN u.full_name IS NULL OR TRIM(u.full_name) = '' 
                THEN '—'
                ELSE u.full_name
            END AS full_name
        FROM attendance a
        LEFT JOIN users u ON a.account_id = u.account_id
        ORDER BY a.date DESC, a.time_in DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Records</title>

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
  overflow-y: auto;
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

.card {
  background: #fff;
  padding: 18px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #5f7cff;
  color: #fff;
}

.status {
  padding: 5px 10px;
  border-radius: 8px;
  color: #fff;
  font-size: 12px;
}

.present {
  background: #2ecc71;
}

.late {
  background: #f39c12;
}

.absent {
  background: #e74c3c;
}
</style>
</head>

<body>

<div class="container">

  <div class="sidebar">
    <div>
      <h2>LUXARA ADMIN</h2>

      <div class="menu">
        <a href="admin_db.php">Dashboard</a>
        <a href="manage_employees.php">Employees</a>
        <a class="active" href="attendance_records.php">Attendance</a>
        <a href="leave_requests.php">Leave Requests</a>
        <a href="salary_management.php">Salary</a>
      </div>
    </div>

    <a class="logout" href="logout.php">Logout</a>
  </div>

  <div class="main">

    <div class="topbar">

      <div class="topbar-left">
        <h1>Attendance Records</h1>
        <p>Manage attendance records.</p>
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

    <div class="card">

      <table>
        <tr>
          <th>ID</th>
          <th>Employee Name</th>
          <th>Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Status</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['full_name']); ?></td>
          <td><?php echo $row['date']; ?></td>
          <td><?php echo $row['time_in']; ?></td>
          <td><?php echo $row['time_out']; ?></td>
          <td>
            <span class="status <?php echo strtolower($row['status']); ?>">
              <?php echo $row['status']; ?>
            </span>
          </td>
        </tr>
        <?php } ?>

      </table>

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
>>>>>>> eb79aaab1dfe4fed3e174172b57a4227c0cd9f74
</html>