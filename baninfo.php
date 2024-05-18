<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
}

require_once 'util.php';
require_once 'mysql/mysql.php';

$name = $_GET['name'] ?? '';
$uuid = get_uuid_with_hypens($name);

$canBan = hasPermission($conn, $_SESSION['uuid'], 'canBan');
$canUnban = hasPermission($conn, $_SESSION['uuid'], 'canUnban');
$canDeletePastBans = hasPermission($conn, $_SESSION['uuid'], 'canDeletePastBans');

$stmt = $conn->prepare("SELECT * FROM banTemplates ORDER BY id");
$stmt->execute();
$result = $stmt->get_result();

$banTemplates = $result->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM currentBans WHERE UUID = ?");
$stmt->bind_param("s", $uuid);
$stmt->execute();
$result = $stmt->get_result();

$currentBan = $result->fetch_assoc();

if ($currentBan) {
    if (strtotime($currentBan['endTime']) < time()) {
        $stmt = $conn->prepare("INSERT INTO pastBans (UUID, bannedBy, reason, startTime, endTime) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $uuid, $currentBan['bannedBy'], $currentBan['reason'], $currentBan['startTime'], $currentBan['endTime']);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM currentBans WHERE UUID = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        
        $currentBan = false;
    }    
}


$stmt = $conn->prepare("SELECT pb.UUID, pb.bannedBy, pb.reason, pb.startTime, pb.endTime, (u.unbannedBy IS NOT NULL) isUnbanned, u.unbannedBy, u.unbanReason, u.unbanTime FROM pastBans pb LEFT JOIN unbans u ON pb.UUID = u.UUID AND pb.startTime = u.banTime WHERE pb.UUID = ? ORDER BY pb.startTime DESC");
$stmt->bind_param("s", $uuid);
$stmt->execute();
$result = $stmt->get_result();

$pastBans = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Player Bans</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/baninfo--style.css">
</head>

<body>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Admin panel</a>
        <form onsubmit="return false;">
            <input type="text" id="search-input" placeholder="Search for player..." autocomplete="off">
        </form>
        <a href="mysql/logout.php" class="logout">Logout</a>
    </nav>

    <label class="switch">
        <input id="toggler" type="checkbox">
        <span class="slider round"></span>
    </label>

    <div id="info-box"></div>

    <form id="unbanformsubmit" action="mysql/unban_player.php" method="post">
        <div id="unbanform">
            <p>unban player</p>
            <input type="text" name="name" value="<?= $name ?>" required autocomplete="off" readonly>
            <input type="text" name="reason" placeholder="Unban Reason" required autocomplete="off">
            <button>UNBAN PLAYER</button>
        </div>
    </form>

    <form id="banformsubmit" action="mysql/ban_player.php" method="post">
        <div id="banform">
            <p>ban player</p>
            <input type="text" name="name" value="<?= $name ?>" required autocomplete="off" readonly>
            <select id="template-select" name="template-select">
                <option value="">Select Ban Template</option>
                <?php if ($banTemplates): ?>
                <?php foreach ($banTemplates as $template): ?>
                <option value="<?= $template["id"] ?>" data-period="<?= $template["period"] ?>" data-period-select="<?= $template["period-select"] ?>"><?= $template["time"] . " - " . $template["reason"] ?></option>
                <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <input type="number" id="period" name="period" placeholder="Period" style="width: 150px" required autocomplete="off">
            <select id="period-select" name="period-select">
                <option value="minutes">Minutes</option>
                <option value="hours">Hours</option>
                <option value="days">Days</option>
                <option value="weeks">Weeks</option>
                <option value="months">Months</option>
                <option value="years">Years</option>
                <option value="permanent">Permanent</option>
            </select>
            <input type="text" id="reason" name="reason" placeholder="Ban Reason" required autocomplete="off">
            <button>BAN PLAYER</button>
        </div>
    </form>

    <div class="content">
        <?php if ($currentBan && $canUnban): ?>
        <button id="unban-button">Unban player</button>
        <?php elseif(!$currentBan && $canBan): ?>
        <button id="ban-button">Ban player</button>
        <?php endif; ?>

        <h2><?= $name ?></h2>
        <img src="data:image/png;base64, <?= get_head64($name) ?> ">
        <h2>Current Ban</h2>

        <?php if ($currentBan): ?>
        <div class="ban">
            <p class="ban-date">Banned on <?= format_datetime($currentBan["startTime"]) ?></p>
            <p>Banned by: <?= is_uuid_with_hyphens($currentBan["bannedBy"]) ? get_name($currentBan["bannedBy"]) : $currentBan["bannedBy"] ?></p>
            <p>Reason: <?= $currentBan["reason"] ?></p>
            <p>Period: <?= print_interval(strtotime($currentBan["startTime"]), strtotime($currentBan["endTime"])) ?></p>
            <p>Remaining Time: <?= print_interval(time(), strtotime($currentBan["endTime"])) ?></p>
        </div>
        <?php else: ?>
        <h3>Player is not currently banned</h3>
        <?php endif; ?>

        <hl></hl>
        <?php if ($pastBans && $canDeletePastBans): ?>
        <button id="clear-button" uuid="<?= get_uuid_with_hypens($_GET["name"]) ?>">Clear ban history</button>
        <?php endif; ?>

        <h2>Past Bans</h2>

        <?php if ($pastBans): ?>
        <?php foreach ($pastBans as $ban): ?>
        <div class="ban">
            <?php if ($canDeletePastBans): ?>
            <i class="delete-btn" uuid="<?= $ban["UUID"] ?>" startTime="<?= $ban["startTime"] ?>"></i>
            <?php endif; ?>
            <p class="ban-date">Banned on <?= format_datetime($ban["startTime"]) ?></p>
            <p>Banned by: <?= is_uuid_with_hyphens($ban["bannedBy"]) ? get_name($ban["bannedBy"]) : $ban["bannedBy"] ?></p>
            <p>Reason: <?= $ban["reason"] ?></p>
            <p>Period: <?= print_interval(strtotime($ban["startTime"]), strtotime($ban["endTime"])) ?> </p>
            <?php if($ban["isUnbanned"] == true): ?>
            <p class="unbanned"> Unbanned on <?= format_datetime($ban["unbanTime"]) ?></p>
            <p>Unbanned by: <?= (is_uuid_with_hyphens($ban["unbannedBy"]) ? get_name($ban["unbannedBy"]) : $ban["unbannedBy"]) ?></p>
            <p>Unban reason: <?= $ban["unbanReason"] ?></p>
            <?php else:?>
            <p class="not-unbanned">Not unbanned</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <h3>Player has no previous bans</h3>
        <?php endif; ?>
    </div>

    <script type="text/javascript" src="js/search.js"></script>
    <script type="text/javascript" src="js/baninfo.js"></script>
</body>

</html>
