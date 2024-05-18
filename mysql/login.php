<?php


session_start();

if (empty($_POST['login_username']) || empty($_POST['login_password'])) {
    header("../index.php");
    exit();
}

require_once('../util.php');
$uuid = get_uuid_with_hypens($_POST['login_username']);
$login_password = $_POST['login_password'];

require_once('mysql.php');

if ($_POST['login_username'] == 'admin' && $_POST['login_password'] == 'admin') {
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $_SESSION['logged_in'] = true;
        $_SESSION['uuid'] = 'admin';
        header("Location: ../dashboard.php");
        exit();
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE uuid=? AND password=?");
$stmt->bind_param("ss", $uuid, $login_password);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $_SESSION['logged_in'] = true;
    $_SESSION['uuid'] = $uuid;
    header("Location: ../dashboard.php");
} else {
    $_SESSION['pw'] = false;
    header("Location: ../index.php");
}

?>
