<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
    exit();
}


require_once 'mysql.php';
require_once '../util.php';

if (!hasPermission($conn, $_SESSION['uuid'], 'canBan')) {
    header("Location: dashboard.php");
    exit();
}

$uuid = get_uuid_with_hypens($_POST['name']);

if (empty($uuid)) {
    echo 'Error: UUID not found.';
    exit();
}

$period = (int) $_POST['period'];
$periodSelect = $_POST['period-select'];

if ($period < 0) {
    echo 'Error: Period cannot be negative.';
    exit();
}

if (!in_array($periodSelect, ['minutes', 'hours', 'days', 'weeks', 'months', 'years', 'permanent'])) {
    echo 'Error: Invalid period select value.';
    exit();
}

$reason = $_POST['reason'];

if (empty($reason)) {
    echo 'Error: Reason is required.';
    exit();
}

$check_ban_stmt = $conn->prepare("SELECT COUNT(*) FROM currentBans WHERE UUID = ? AND endTime > NOW()");
$check_ban_stmt->bind_param('s', $uuid);
$check_ban_stmt->execute();
$check_ban_stmt->bind_result($ban_count);
$check_ban_stmt->fetch();

if ($ban_count > 0) {
    echo 'Error: Player already banned.';
    $check_ban_stmt->close();
    $conn->close();
    exit();
}

$check_ban_stmt->close();

switch ($periodSelect) {
    case 'seconds':
        $endTime = date('Y-m-d H:i:s', time() + $period);
        break;
    case 'minutes':
        $endTime = date('Y-m-d H:i:s', time() + ($period * 60));
        break;
    case 'hours':
        $endTime = date('Y-m-d H:i:s', time() + ($period * 60 * 60));
        break;
    case 'days':
        $endTime = date('Y-m-d H:i:s', time() + ($period * 60 * 60 * 24));
        break;
    case 'weeks':
        $endTime = date('Y-m-d H:i:s', time() + ($period * 60 * 60 * 24 * 7));
        break;
    case 'months':
        $endTime = date('Y-m-d H:i:s', strtotime("+{$period} months"));
        break;
    case 'years':
        $endTime = date('Y-m-d H:i:s', strtotime("+{$period} years"));
        break;
    case 'permanent':
        $endTime = date('Y-m-d H:i:s', strtotime("+500 years"));
        break;
    default:
       $endTime = date('Y-m-d H:i:s', strtotime("+500 years"));
        break;
}

$stmt = $conn->prepare("INSERT INTO currentBans (UUID, bannedBy, reason, startTime, endTime) VALUES (?, ?, ?, NOW(), ?)");
$stmt->bind_param('ssss', $uuid, $_SESSION['uuid'], $reason, $endTime);

if ($stmt->execute()) {
    echo 'Player banned successfully.';
} else {
    echo 'Error: ' . $stmt->error;
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
$bannedByUuid = $_SESSION['uuid'];
$bannedBy = get_name($bannedByUuid);

$data = "ban,$name,$bannedByUuid,$bannedBy,$reason,$endTime";
$dataLength = strlen($data);

$packet = pack('c', $dataLength >> 8) . pack('c', $dataLength & 0xff) . $data;

socket_write($socket, $packet, strlen($packet));

socket_close($socket);
?>