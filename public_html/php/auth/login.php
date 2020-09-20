<!DOCTYPE html>

<html>

<head>
    <?php
    session_start();
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    use RestCord\DiscordClient;

    // Include config file
    $title = "Login";
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/title.php");
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/metadata.php");
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/check_records.php");
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/connect.php");
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/utils.php");

    // Define variables and initialize with empty values
    $username = $password = "";
    $username_err = $password_err = "";
    if (isset($_GET['login'])){
        require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/steamauth/openid.php");
        try {
            require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/steamauth/SteamConfig.php");
            $openid = new LightOpenID($steamauth['domainname']);

            if(!$openid->mode) {
                $openid->identity = 'https://steamcommunity.com/openid';
                header('Location: ' . $openid->authUrl());
            } elseif ($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                if($openid->validate()) {
                    $id = $openid->identity;
                    $ptn = "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
                    preg_match($ptn, $id, $matches);

                    $_SESSION['steamid'] = $matches[1];
                    $link = mysqli_connect("localhost", "frikanhub", "HUHDGEguFGYEDFGEYT", "login");
                    if ($link === false) {
                        die("ERROR: Could not connect. " . mysqli_connect_error());
                    }
                    $sql = "UPDATE frikandelbroodjeusers SET steamid = ".$matches[1]." WHERE username = '".$_SESSION["username"]."'";
                    if ($link->query($sql) === TRUE) {
                    } else {
                        echo "Error: " . $sql . "<br>" . $link->error;
                    }
                    if (!headers_sent()) {
                        header('Location: '.$steamauth['loginpage']);
                        exit;
                    } else {
                        ?>
                        <script type="text/javascript">
                            window.location.href="<?=$steamauth['loginpage']?>";
                        </script>
                        <noscript>
                            <meta http-equiv="refresh" content="0;url=<?=$steamauth['loginpage']?>" />
                        </noscript>
                        <?php
                        exit;
                    }
                } else {
                    echo "User is not logged in.\n";
                }
            }
        } catch(ErrorException $e) {
            echo $e->getMessage();
        }
    }
    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Check if username is empty
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter username.";
        } else {
            $username = trim($_POST["username"]);
        }

        // Check if password is empty
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Validate credentials
        if (empty($username_err) && empty($password_err)) {
            // Prepare a query
            $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqllogin.ini"));
            $query = $db -> prepare("SELECT * FROM frikandelbroodjeusers WHERE username = ?");

            // Bind variables to the prepared statement as parameters
            $query -> execute([$username]);

            // Check if username exists, if yes then verify password
            if ($query -> rowCount() == 1) {
                // Store result
                $user = $query -> fetch();

                if (password_verify($password, $user['password'])) {
                    $isDisabled = $user['disabled'];
                    $isDisabledAdmin = $user['disabledadmin'];

                    // Password is correct, so start a new session
                    if ($isDisabled == 1) {
                        $_SESSION['username'] = $user["username"];
                        failmodal("<font color='red'>This account is disabled go to <a href='https://frikandelbroodjeserver.nl/php/discordverify.php?uname=$username&action=login'>This Link</a> if you didnt activate your account yet.</font>", 30, "false", "false", 500);
                    }
                    if ($isDisabledAdmin == 1) {
                        session_destroy();
                        failmodal("<font color='red'>This account is disabled by an admin.</font>)", 30, "true", "true", 500);
                    }
                    $continue = true;
                    if (empty($user["discordid"]))
                        $continue = false;
                    if ($continue) {
                        $discord = new DiscordClient(['token' => '']);
                        try {
                            $userds = $discord->guild->getGuildMember(array("guild.id" => 589191346246516757, "user.id" => (int)$user['discordid']));
                        } catch (Exception $ex) {
                            if ($ex->getMessage() == "There was an error executing the getGuildMember command: Client error: `GET https://discord.com/api/v6/guilds/675279655762132994/members/" . $user['discordid'] . "` resulted in a `404 Not Found` response: ") {
                            }
                        }
                        $_SESSION['isstaff'] = false;
                        if (in_array(598619026234802206, $userds->roles)) {
                            $_SESSION['isstaff'] = true;
                        }
                        if (empty($user["steamid"])) {
                            if ($_SESSION['isstaff']) {
                                $continue = false;
                                $buttonstyle = "square";
                                $button['rectangle'] = "01";
                                $button['square'] = "02";
                                $_SESSION["username"] = $user['username'];
                                $button = "<a href='?login'><img src='https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_" . $button[$buttonstyle] . ".png'></a>";
                                failmodal("Your account is not linked to steam: " . $button . " <br>This is a requirment for staff accounts", 30, "false", "false", 500);
                            }
                        }
                        $discord = new DiscordClient(['token' => '']);
                        try {
                            $userds = $discord->guild->getGuildMember(array("guild.id" => 589191346246516757, "user.id" => (int)$user['discordid']));
                        } catch (Exception $ex) {
                            if ($ex->getMessage() == "There was an error executing the getGuildMember command: Client error: `GET https://discord.com/api/v6/guilds/675279655762132994/members/" . $user['discordid'] . "` resulted in a `404 Not Found` response: ") {
                            }
                        }
                        $_SESSION['isstaff'] = false;
                        if (in_array(598619026234802206, $userds->roles)) {
                            $_SESSION['isstaff'] = true;
                        }
                        if ($isDisabled == 0 && $isDisabledAdmin == 0 && $continue) {
                            // Store data in session variables
                            if (!empty($user["steamid"])) {
                                $_SESSION['steamid'] = $user["steamid"];
                            } else {
                                $_SESSION['steamid'] = "NaN";
                            }
                            $_SESSION["loggedinf"] = true;
                            $_SESSION["id"] = $user['id'];
                            $_SESSION["username"] = $user['username'];
                            $_SESSION["permlevelfrikandelbroodje"] = $user['permissionlevel'];
                            $_SESSION["disabled"] = $user['disabled'];
                            $_SESSION["disabledadmin"] = $user['disabledadmin'];
                            $_SESSION["discordid"] = $user['discordid'];
                            $_SESSION["perms"] = explode(',', $user['extraperms']);
                            $cookie_name = "permlevelfrikandelbroodje";
                            $cookie_value = $_SESSION["permlevelfrikandelbroodje"];
                            setcookie($cookie_name, $cookie_value, "0", "/", ".frikandelbroodjeserver.nl", "true", "false"); // 86400 = 1 day
                            // Redirect user to welcome page
                            $url = "";
                            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                            $url .= $_SERVER['HTTP_HOST'];
                            $host = $_SERVER['HTTP_HOST'];
                            $url .= $_SERVER['REQUEST_URI'];
                            echo '<script>
                                    var url = localStorage.getItem(\'location\');
                                    if (url == null) { 
                                        url = "' . $protocol . $host . '/php/dashboard.php";
                                    }
                                    localStorage.setItem(\'location\', "' . $protocol . $host . '/php/dashboard.php");
                                    localStorage.setItem("username", "' . $_SESSION["username"] . '/");
                                    window.location.replace(url)</script>';
                            exit();
                        }
                    }
                } else {
                    // Display an error message if password is not valid
                    failmodal("<font color='red'>The specified username and password do not match our record. Did you enter them correctly?</font>", 30, "true", "true", 500);
                }
            } else {
                // Display an error message if username doesn't exist
                failmodal("<font color='red'>The specified username and password do not match our record. Did you enter them correctly?</font>", 30, "true", "true", 500);
            }
        }
    }
    ?>
</head>

<body>
<?php include "../../../elements/navbar.php"; ?>
<div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
    <h2>Login</h2><br>
    <hr>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <div class="form-group">
            <label for="username">Username </label>
            <input class="form-control" type="text" name="username" required value=<?php echo $username; ?>><br>
            <?php echo $username_err; ?>
        </div>
        <div class="form-group">
            <label for="password">Password </label>
            <input class="form-control" type="password" name="password" required value=<?php echo $password; ?>><br>
            <?php echo $password_err; ?>
        </div>
        <div class="btn-group" role="group">
            <button class="btn btn-primary" type="submit" name="submitLogin">Login</button>
            <button class="btn btn-secondary" type="button" onclick="location.href = '/php/auth/register.php'">I don't have an account yet</button>
        </div>
    </form>
</div>
<?php include "../../../elements/footer.php"; ?>
</body>

</html>