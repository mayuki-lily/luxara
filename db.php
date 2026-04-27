<<<<<<< HEAD
<?php
$host = "localhost";
$dbname = "luxara_db";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
=======
<?php
$host = "localhost";
$dbname = "luxara_db";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
>>>>>>> eb79aaab1dfe4fed3e174172b57a4227c0cd9f74
?>