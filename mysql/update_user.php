<?php
session_start();

require_once('mysql.php');
require_once('../util.php');

if (!hasPermission($conn, $_SESSION['uuid'], 'canEditUsers')) {
    echo 'No permissions.';
    exit();
}

$username = $_POST['username'];
$uuid = get_uuid_with_hypens($username);

if ($uuid == "----") {
    echo 'Invalid Minecraft username.';
    exit();
}

if ($_GET['action'] === 'delete') {
    $stmt = $conn->prepare('DELETE FROM users WHERE uuid = ?');
    $stmt->bind_param('s', $uuid);
    $stmt->execute();

    echo 'User deleted successfully';
    
} else if ($_GET['action'] === 'add') {
    $password = $_POST['password'];
    $canBan = $_POST['canBan'];
    $canUnban = $_POST['canUnban'];
    $canDeletePastBans = $_POST['canDeletePastBans'];
    $canMute = $_POST['canMute'];
    $canUnmute = $_POST['canUnmute'];
    $canDeletePastMutes = $_POST['canDeletePastMutes'];
    $canEditUsers = $_POST['canEditUsers'];

    $stmt = $conn->prepare('INSERT INTO users (uuid, password, canBan, canUnban, canDeletePastBans, canMute, canUnmute, canDeletePastMutes, canEditUsers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssiiiiiii', $uuid, $password, $canBan, $canUnban, $canDeletePastBans, $canMute, $canUnmute, $canDeletePastMutes, $canEditUsers);

    if ($stmt->execute()) {
        echo 'User added successfully';
    } else {
        echo 'Error: ' . $stmt->error;
    }
} else if ($_GET['action'] === 'update') {
    $column = $_POST['column'];
    $value = $_POST['value'];

    $stmt = $conn->prepare("UPDATE users SET $column = ? WHERE uuid = ?");
    $stmt->bind_param('ss', $value, $uuid);
    $stmt->execute();

    echo 'User updated successfully';
} else {
    header('Invalid action');
}

?>