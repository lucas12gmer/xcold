<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
    exit;
}

require_once 'mysql.php';
require_once '../util.php';

if (!hasPermission($conn, $_SESSION['uuid'], 'canDeletePastBans')) {
    header("Location: dashboard.php");
    exit();
}

$uuid = $_POST['uuid'];
$startTime = $_POST['startTime'];

// Use parameterized queries to prevent SQL injection attacks
$stmt = $conn->prepare("DELETE FROM unbans WHERE UUID=? AND banTime=?");
$stmt->bind_param("ss", $uuid, $startTime);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM pastBans WHERE UUID=? AND startTime=?");
$stmt->bind_param("ss", $uuid, $startTime);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo "Previous ban deleted.";
} else {
  echo "Error: Ban not found.";
}

$stmt->close();
$conn->close();
?>