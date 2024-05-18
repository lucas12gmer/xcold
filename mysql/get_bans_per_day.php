<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
}

include 'mysql.php';

// retrieve fromDate and toDate from POST request
$fromDate = $_POST['fromDate'];
$toDate = $_POST['toDate'];

// generate array of all dates between $fromDate and $toDate
$dateRange = date_range($fromDate, $toDate);

// query database for bans over time
$sql = "SELECT DATE(startTime) AS date, COUNT(*) as amount FROM
(SELECT DATE(startTime) AS startTime FROM pastBans WHERE startTime BETWEEN '$fromDate' AND '$toDate' 
 UNION ALL 
 SELECT DATE(startTime) AS startTime FROM currentBans WHERE startTime BETWEEN '$fromDate' AND '$toDate') 
 AS amounts GROUP BY DATE(startTime)";

$result = $conn->query($sql);

// format data for line chart
$data = array_fill(0, count($dateRange), 0);
$labels = $dateRange;
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $index = array_search($row['date'], $dateRange);
    if ($index !== false) {
      $data[$index] = $row['amount'];
    }
  }
}

// close database connection
$conn->close();

// return data as JSON
echo json_encode(array('data' => $data, 'labels' => $labels));

// helper function to generate array of all dates between $start and $end
function date_range($start, $end) {
  $startDate = new DateTime($start);
  $endDate = new DateTime($end);
  $interval = new DateInterval('P1D');
  $dateRange = new DatePeriod($startDate, $interval, $endDate);
  $dates = array();
  foreach ($dateRange as $date) {
    $dates[] = $date->format('Y-m-d');
  }
  return $dates;
}

?>