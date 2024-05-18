<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
}

include 'mysql.php';

// retrieve fromDate and toDate from POST request
$fromDate = $_POST['fromDate'];
$toDate = $_POST['toDate'];

// construct SQL query
$sql = "SELECT reason, COUNT(*) as count FROM (SELECT reason FROM pastBans WHERE startTime BETWEEN '$fromDate' AND '$toDate' UNION ALL SELECT reason FROM currentBans WHERE startTime BETWEEN '$fromDate' AND '$toDate') AS reasons GROUP BY reason ORDER BY count DESC";

$result = $conn->query($sql);

$labels = [];
$data = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        array_push($labels, $row['reason']);
        array_push($data, $row['count']);
    }
}

// close connection
$conn->close();

// return data as JSON
echo json_encode(array('labels' => $labels, 'data' => $data));

?>