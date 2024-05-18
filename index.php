<?php

session_start();
   
   if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
       header("Location: dashboard.php");
       exit();
   }
?>



<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ban Managment - Login</title>
    <link rel="stylesheet" href="css/login--style.css">
</head>

<body>
    <div class="login-page">
        <?php
            if (isset($_SESSION['pw']) && $_SESSION['pw'] == false) {
                echo '<div class="alert warning"> Benutzername oder Passwort ist inkorrekt. </div>';
                $_SESSION['pw'] = null;
                
            }
            ?>
        <div class="form">
            <div class="tab-content">
                <div id="login">
                    <form action="mysql/login.php" method="post">
                        <input type="text" name="login_username" placeholder="benutzername" required autocomplete="off">
                        <input type="password" name="login_password" placeholder="passwort" required autocomplete="off">
                        <button>login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
