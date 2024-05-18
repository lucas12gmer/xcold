<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
}

require_once 'mysql.php';
require_once '../util.php';

if (!hasPermission($conn, $_SESSION['uuid'], 'canUnban')) {
    header("Location: dashboard.php");
    exit();
}

$uuid = get_uuid_with_hypens($_POST['name']);

// Check if the player is banned
$currentBansQuery = "SELECT * FROM currentBans WHERE UUID = ?";
$stmt = $conn->prepare($currentBansQuery);
$stmt->bind_param("s", $uuid);
$stmt->execute();
$currentBansResult = $stmt->get_result();

if ($currentBansResult->num_rows == 0) {
    echo 'Error: Player not banned.';
} else {
    $currentBan = $currentBansResult->fetch_assoc();
    $stmt = $conn->prepare("DELETE FROM currentBans WHERE UUID = ?");
    $stmt->bind_param("s", $uuid);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO pastBans (UUID, bannedBy, reason, startTime, endTime) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $currentBan['UUID'], $currentBan['bannedBy'], $currentBan['reason'], $currentBan['startTime'], $currentBan['endTime']);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO unbans (UUID, unbannedBy, unbanReason, banTime, unbanTime) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $currentBan['UUID'], $_SESSION['uuid'], $_POST['reason'], $currentBan['startTime']);
    $stmt->execute();

    
    echo 'Player unbanned successfully.';
}

$stmt->close();
$conn->close();

//send packet to bungeecord server
require_once('../settings.php');

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) {
    die("Unable to create socket: " . socket_strerror(socket_last_error()));
}

if (!socket_connect($socket, $host, $port)) {
    die("Unable to connect to socket: " . socket_strerror(socket_last_error()));
}

$name = $_POST['name'];
$unbannedByUuid = $_SESSION['uuid'];
$unbannedBy = get_name($unbannedByUuid);
$reason = $_POST['reason'];
$data = "unban,$name,$unbannedByUuid,$unbannedBy,$reason";
$dataLength = strlen($data);

$packet = pack('c', $dataLength >> 8) . pack('c', $dataLength & 0xff) . $data;

socket_write($socket, $packet, strlen($packet));

socket_close($socket);
?>