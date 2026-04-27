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

/* Check if admin */
$check = $conn->prepare("SELECT role, full_name FROM users WHERE account_id = ?");
$check->bind_param("s", $account_id);
$check->execute();
$res = $check->get_result();
$user = $res->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$query = "SELECT account_id, full_name, email, role, created_at 
          FROM users 
          WHERE role = 'employee'
          ORDER BY created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Employees</title>

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
  color: #333;
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
  flex: 1;
}

.menu a {
  text-decoration: none;
  color: #555;
  padding: 10px 14px;
  border-radius: 10px;
  transition: 0.2s ease;
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
  margin-top: auto;
  border-radius: 10px;
  transition: 0.2s ease;
}

.logout:hover {
  background: #ffecec;
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

.date {
  font-size: 12px;
  color: #666;
}

.clock {
  font-weight: 500;
  color: #333;
}

.card {
  background: #fff;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  border-radius: 10px;
  overflow: hidden;
}

thead {
  background: #f1f4ff;
}

th {
  text-align: left;
  padding: 14px;
  font-size: 13px;
  color: #444;
}

td {
  padding: 14px;
  border-bottom: 1px solid #eee;
  font-size: 13px;
}

tr:hover {
  background: #fafbff;
}

.badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 12px;
  background: #5f7cff;
  color: white;
}

@media (max-width: 900px) {
  .sidebar {
    width: 200px;
  }

  th, td {
    font-size: 12px;
    padding: 10px;
  }

  .topbar {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
</head>

<body>

<div class="container">

    <div class="sidebar">
        <h2>LUXARA ADMIN</h2>

        <div class="menu">
            <a href="admin_db.php">Dashboard</a>
            <a class="active" href="manage_employees.php">Employees</a>
            <a href="attendance_records.php">Attendance</a>
            <a href="leave_requests.php">Leave Requests</a>
            <a href="salary_management.php">Salary</a>
        </div>

        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="main">

    <div class="topbar">

        <div class="topbar-left">
            <h1>Employees</h1>
            <p>Manage employee records</p>
        </div>

        <div class="topbar-right">

            <div class="notification">
            <i class="fa-solid fa-bell"></i>
            </div>

            <div style="text-align:right; line-height:1.2;">
            <div class="date" id="date"></div>
            <div class="clock" id="clock"></div>
            </div>

        </div>

    </div>

        <div class="card">

            <h3>Employee List</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['account_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge"><?php echo $row['role']; ?></span></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No employees found.</td>
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

/* Check if admin */
$check = $conn->prepare("SELECT role, full_name FROM users WHERE account_id = ?");
$check->bind_param("s", $account_id);
$check->execute();
$res = $check->get_result();
$user = $res->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$query = "SELECT account_id, full_name, email, role, created_at 
          FROM users 
          WHERE role = 'employee'
          ORDER BY created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Employees</title>

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
  color: #333;
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
  flex: 1;
}

.menu a {
  text-decoration: none;
  color: #555;
  padding: 10px 14px;
  border-radius: 10px;
  transition: 0.2s ease;
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
  margin-top: auto;
  border-radius: 10px;
  transition: 0.2s ease;
}

.logout:hover {
  background: #ffecec;
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

.date {
  font-size: 12px;
  color: #666;
}

.clock {
  font-weight: 500;
  color: #333;
}

.card {
  background: #fff;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  border-radius: 10px;
  overflow: hidden;
}

thead {
  background: #f1f4ff;
}

th {
  text-align: left;
  padding: 14px;
  font-size: 13px;
  color: #444;
}

td {
  padding: 14px;
  border-bottom: 1px solid #eee;
  font-size: 13px;
}

tr:hover {
  background: #fafbff;
}

.badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 12px;
  background: #5f7cff;
  color: white;
}

@media (max-width: 900px) {
  .sidebar {
    width: 200px;
  }

  th, td {
    font-size: 12px;
    padding: 10px;
  }

  .topbar {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
</head>

<body>

<div class="container">

    <div class="sidebar">
        <h2>LUXARA ADMIN</h2>

        <div class="menu">
            <a href="admin_db.php">Dashboard</a>
            <a class="active" href="manage_employees.php">Employees</a>
            <a href="attendance_records.php">Attendance</a>
            <a href="leave_requests.php">Leave Requests</a>
            <a href="salary_management.php">Salary</a>
        </div>

        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="main">

    <div class="topbar">

        <div class="topbar-left">
            <h1>Employees</h1>
            <p>Manage employee records</p>
        </div>

        <div class="topbar-right">

            <div class="notification">
            <i class="fa-solid fa-bell"></i>
            </div>

            <div style="text-align:right; line-height:1.2;">
            <div class="date" id="date"></div>
            <div class="clock" id="clock"></div>
            </div>

        </div>

    </div>

        <div class="card">

            <h3>Employee List</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['account_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge"><?php echo $row['role']; ?></span></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No employees found.</td>
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