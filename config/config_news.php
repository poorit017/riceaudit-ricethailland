<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "news";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn ->connect_error) {
    die("Connection failed: " . $conn_news ->connect_error);
}
?>
