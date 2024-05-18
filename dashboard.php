<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
}

require_once 'util.php';
require_once 'mysql/mysql.php';

$sql = "select (SELECT COUNT(*) FROM currentBans) as currentBansCount, 
       (SELECT COUNT(*) FROM pastBans) as pastBansCount,
       (SELECT COUNT(*) FROM unbans) as unbansCount";
$result = $conn->query($sql);
$statistics = $result->fetch_assoc();

$currentBans = $statistics["currentBansCount"];
$pastBans = $statistics["pastBansCount"];
$unbans = $statistics["unbansCount"];
$totalBans = $currentBans + $pastBans;


$sql = "SELECT * FROM currentBans ORDER BY startTime DESC LIMIT 3";
$result = $conn->query($sql);

$recentBans = $result->fetch_all(MYSQLI_ASSOC);

$canEditUsers = hasPermission($conn, $_SESSION['uuid'], 'canEditUsers');

$conn->close();

?>
<!DOCTYPE html>
<html>

<head>
    <title>Statistics</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard--style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
</head>

<body>
    <nav>
        <a class="active">Ban Dashboard</a>
        <?php if ($canEditUsers): ?>
            <a href="users.php">Admin panel</a>
        <?php endif; ?>
        <form onsubmit="return false;">
            <input type="text" id="search-input" placeholder="Search for player..." autocomplete="off">
        </form>
        <a href="mysql/logout.php" class="logout">Logout</a>
    </nav>

    <label class="switch">
        <input id="toggler" type="checkbox">
        <span class="slider round"></span>
    </label>

    <div class="content">
        <h2>STATISTICS</h2>
        <div class="stats-container">
            <div class="info-box">
                <div class="info-box-title">Global statistics</div>
                <div class="info-box-stats">
                    <div class="info-box-stat">
                        <div class="info-box-stat-value"><?= $totalBans ?></div>
                        <div class="info-box-stat-label">Total Bans</div>
                    </div>
                    <div class="info-box-stat">
                        <div class="info-box-stat-value"><?= $currentBans ?></div>
                        <div class="info-box-stat-label">Active Bans</div>
                    </div>
                    <div class="info-box-stat">
                        <div class="info-box-stat-value"><?= $unbans ?></div>
                        <div class="info-box-stat-label">Unbans</div>
                    </div>
                    <div class="info-box-stat">
                        <div class="info-box-stat-value"><?= $pastBans ?></div>
                        <div class="info-box-stat-label">Expired Bans</div>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <div class="info-box-title">Recent activity</div>

                <?php if ($recentBans): ?>
                <?php foreach ($recentBans as $ban): ?>
                <hl></hl>
                <div class="activity">
                    <p><span class="player"> <?= (is_uuid_with_hyphens($ban["bannedBy"]) ? get_name($ban["bannedBy"]) : $ban["bannedBy"]) ?></span> banned <a href="baninfo.php?name=<?= get_name($ban["UUID"]) ?>"><?= get_name($ban["UUID"]) ?></a> <span class="date">(<?= format_datetime($ban["startTime"]) ?>)</span></p>
                    <p class="reason">Reason: <?= $ban["reason"] ?></p>
                    <p class="period">Period: <?= print_interval(strtotime($ban["startTime"]), strtotime($ban["endTime"])) ?></p>
                    <p>Remaining Time: <?= print_interval(time(), strtotime($ban["endTime"])) ?></p>
                </div>
                <?php endforeach; ?>
                <?php else:?>
                <p>No recent activity</p>
                <?php endif; ?>
            </div>
        </div>

        <hl></hl>
        <div class="date-range">
            <input type="date" id="from-date" name="from-date" required> -
            <input type="date" id="to-date" name="to-date" required>
        </div>

        <div class="chart-container">
            <canvas id="bansOverTimeChart"></canvas>
        </div>
        <div class="chart-container" style="float:right;">
            <canvas id="banReasonsChart"></canvas>
        </div>
    </div>

    <script type="text/javascript" src="js/dashboard.js"></script>
    <script type="text/javascript" src="js/search.js"></script>

</body>

</html>
