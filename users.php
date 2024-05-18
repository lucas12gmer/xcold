<?php


session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
    header("Location: index.php");
}

require_once('util.php');
require_once('mysql/mysql.php');

if (!hasPermission($conn, $_SESSION['uuid'], 'canEditUsers')) {
    header("Location: dashboard.php");
}

$stmt = $conn->prepare('SELECT * FROM users');
$stmt->execute();
$result = $stmt->get_result();

$users = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>User Permissions</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="js/users.js"></script>

    <link rel="stylesheet" href="css/users-style.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a class="active">Admin panel</a>
        <form onsubmit="return false;">
            <input type="text" id="search-input" placeholder="Search for player..." autocomplete="off">
        </form>
        <a href="mysql/logout.php" class="logout">Logout</a>
    </nav>

    <label class="switch">
        <input id="toggler" type="checkbox">
        <span class="slider round"></span>
    </label>

    <div class="content" style="max-width:2000px">
        <h2>User Permissions</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Ban players</th>
                    <th>Unban players</th>
                    <th>Delete ban history</th>
                    <th>Mute players</th>
                    <th>Unmute players</th>
                    <th>Delete mute history</th>
                    <th>Edit accounts</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>


                <?php if ($users): ?>
                <?php foreach ($users as $user):
                    $name = get_name($user['uuid']); ?>
                <tr>
                    <td><?= $name ?></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canBan" <?= $user['canBan'] ? 'checked' : '' ?>></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canUnban" <?= $user['canUnban'] ? 'checked' : '' ?>></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canDeletePastBans" <?= $user['canDeletePastBans'] ? 'checked' : '' ?>></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canMute" <?= $user['canMute'] ? 'checked' : '' ?>></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canUnmute" <?= $user['canUnmute'] ? 'checked' : '' ?>></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canDeletePastMutes" <?= $user['canDeletePastMutes'] ? 'checked' : '' ?>></td>
                    <td><input type="checkbox" class="update" data-username="<?= $name ?>" data-column="canEditUsers" <?= $user['canEditUsers'] ? 'checked' : '' ?>></td>
                    <td><i class="delete-btn" data-username="<?= $name ?>"></i></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <hl></hl>
        <h2>Add User</h2>
        <form id="addUserForm">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username">
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
            </div>
            <div>
                <label for="canBan">Ban players</label>
                <input type="checkbox" id="canBan" name="canBan">
            </div>
            <div>
                <label for="canUnban">Unban players</label>
                <input type="checkbox" id="canUnban" name="canUnban">
            </div>
            <div>
                <label for="canDeletePastBans">Delete ban history</label>
                <input type="checkbox" id="canDeletePastBans" name="canDeletePastBans">
            </div>
            <div>
                <label for="canMute">Mute players</label>
                <input type="checkbox" id="canMute" name="canMute">
            </div>
            <div>
                <label for="canUnmute">Unmute players</label>
                <input type="checkbox" id="canUnmute" name="canUnmute">
            </div>
            <div>
                <label for="canDeletePastMutes">Delete mute history</label>
                <input type="checkbox" id="canDeletePastMutes" name="canDeletePastMutes">
            </div>
            <div>
                <label for="canEditUsers">Edit accounts</label>
                <input type="checkbox" id="canEditUsers" name="canEditUsers">
            </div>
            <div>
                <button type="submit">Add User</button>
            </div>
        </form>
    </div>

    <script src="js/search.js"></script>
</body>

</html>
