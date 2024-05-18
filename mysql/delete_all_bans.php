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

// Use parameterized queries to prevent SQL injection attacks
$stmt = $conn->prepare("DELETE FROM unbans WHERE UUID=?");
$stmt->bind_param("s", $uuid);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM pastBans WHERE UUID=?");
$stmt->bind_param("s", $uuid);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo "Bans cleared.";
} else {
  echo "Error: No bans found.";
}

$stmt->close();
$conn->close();
?>