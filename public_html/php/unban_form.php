<!DOCTYPE html>

<html>

<head>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    session_start();

    $title = "Ban Appeal";

    include "../../elements/title.php";
    include "../../elements/metadata.php";
    include "../../scripts/utils.php";

    if (!isset($_SESSION['loggedinf'])) {
        $url = "";
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url.= $_SERVER['HTTP_HOST'];
        $host = $_SERVER['HTTP_HOST'];
        $url.= $_SERVER['REQUEST_URI'];
        echo "<script>localStorage.setItem('location', '".$protocol."$url'); 
        //window.location.replace('".$protocol."$host/php/auth/login.php');</script>";
        LoginPrompt();
        include '../static/403.php';
        exit();
    }
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
    $_SESSION['banappealisvalid'] = false;
    if (empty($_SESSION['sentappeal']))
        $_SESSION['sentappeal'] = false;
    if (empty($_SESSION['discordplay']))
        $_SESSION['discordplay'] = false;
    if (isset($_POST['usingdiscord']) && $_SESSION['discordplay'] == false)
        $_SESSION['discordplay'] = true;
    if (isset($_POST['usingsteam']) && $_SESSION['discordplay'] == true)
        $_SESSION['discordplay'] = false;
    if ($_SESSION['discordplay'] == true) {
        $steamid_err = "";
        $ban = getBan($_SESSION['discordid'] . "@discord");
        if (empty($ban)) {
            failmodal("<font color='red'>You are not banned.</font>", 30, "true", "true", 500);
        } else {
            $info = getdate();
            $date = $info['mday'];
            $month = $info['mon'];
            $year = $info['year'];
            $hour = $info['hours'];
            $min = $info['minutes'];
            $sec = $info['seconds'];
            $current_date = "$year-$month-$date $hour:$min:$sec";
            $time2 = strtotime($current_date);
            $unixstamp = $ban["unixstamp"];
            $temp1 = $time2 - $unixstamp;
            $temp2 = $ban["banduration"] * 60;
            $temp3 = $temp1 - $temp2;
            $temp3 = str_replace("-", "", $temp3);
            $remainingtime = $temp3;
            $remainingtime = $remainingtime / 60;
            $remainingtime = round($remainingtime, 2);
            if ($time2 - $unixstamp > $ban["banduration"] * 60) {
                failmodal("<font color='red'>Your ban has already expired</font>", 30, "true", "true", 500);
            } else {
                if ($ban['banduration'] <= 240) {
                    failmodal("<font color='red'>You may not appeal bans that have a duration smaller then 4 hours, your duration (" . minToHourMinute(getBanTimeLeft($ban)) . ").</font>", 30, "true", "true", 500);
                } else {
                    if ($ban['appealstate'] == 1) {
                        failmodal("<font color='red'>You may only appeal once per ban.</font>", 30, "true", "true", 500);
                    } else {
                        $_SESSION['banappealisvalid'] = true;
                    }
                }
            }
        }
    }
    if ($_SESSION['steamid'] != "NaN" && $_SESSION['discordplay'] == false) {
        $steamid_err = "";
        $ban = getBan($_SESSION['steamid'] . "@steam");
        $_SESSION['discordplay'] = false;
        if (empty($ban)) {
            failmodal("<font color='red'>You are not banned.</font>", 30, "true", "true", 500);
        } else {
            $info = getdate();
            $date = $info['mday'];
            $month = $info['mon'];
            $year = $info['year'];
            $hour = $info['hours'];
            $min = $info['minutes'];
            $sec = $info['seconds'];
            $current_date = "$year-$month-$date $hour:$min:$sec";
            $time2 = strtotime($current_date);
            $unixstamp = $ban["unixstamp"];
            $temp1 = $time2 - $unixstamp;
            $temp2 = $ban["banduration"] * 60;
            $temp3 = $temp1 - $temp2;
            $temp3 = str_replace("-", "", $temp3);
            $remainingtime = $temp3;
            $remainingtime = $remainingtime / 60;
            $remainingtime = round($remainingtime, 2);
            if ($time2 - $unixstamp > $ban["banduration"] * 60) {
                failmodal("<font color='red'>Your ban has already expired</font>", 30, "true", "true", 500);
            } else {
                if ($ban['banduration'] <= 240) {
                    failmodal("<font color='red'>You may not appeal bans that have a duration smaller then 4 hours, your duration (" . minToHourMinute(getBanTimeLeft($ban)) . ").</font>", 30, "true", "true", 500);
                } else {
                    if ($ban['appealstate'] == 1) {
                        failmodal("<font color='red'>You may only appeal once per ban.</font>", 30, "true", "true", 500);
                    } else {
                        $_SESSION['banappealisvalid'] = true;
                    }
                }
            }
        }
    }
    else
    {
        if ($_SESSION['steamid'] == "NaN") {
            $_SESSION['banappealisvalid'] = false;
            $buttonstyle = "square";
            $button['rectangle'] = "01";
            $button['square'] = "02";
            $button = "<a href='?login'><img src='https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_" . $button[$buttonstyle] . ".png'></a>";
            failmodal("Your account is not linked to steam: " . $button . " <br><p>Did you play using discord at the time of your ban? Click here:<form method='post' action='".$_SERVER['PHP_SELF']."'><input class='btn btn-danger' type='submit' name='usingdiscord' value='I was playing using discord'></form></p>", 30, "true", "true", 500);
        }
    }
    if (isset($_POST['submit'])) {
        $url = "https://discordapp.com/api/webhooks/680744038701596766/WgeJp15FV8gU4oNU5E9QkE2lUOG0wWruAJDL2uhsdKBPqbPIfu-2Nq5py7p5RbhOm6NE";
        $hookObject = json_encode([
            /*
     * The general "message" shown above your embeds <@&680745139475578941>
     */
            "content" => "<@&680745139475578941> https://frikandelbroodjeserver.nl/php/appeal_overview.php",
            /*
     * The username shown in the message
     */
            "username" => "Ban Logger",
            /*
     * The image location for the senders image
     */
            "avatar_url" => "",
            /*
     * Whether or not to read the message in Text-to-speech
     */
            "tts" => false,
            /*
     * File contents to send to upload a file
     */
            // "file" => "",
            /*
     * An array of Embeds
     */
            "embeds" => [
                /*
         * Our first embed
         */
                [
                    // Set the title for your embed
                    "title" => "Unban request",

                    // The type of your embed, will ALWAYS be "rich"
                    "type" => "rich",

                    // A description for your embed
                    "description" => "",

                    // The URL of where your title will be a link to
                    "url" => "",

                    /* A timestamp to be displayed below the embed, IE for when an an article was posted
             * This must be formatted as ISO8601
             */
                    "timestamp" => "2018-03-10T19:15:45-05:00",

                    // The integer color to be used on the left side of the embed
                    "color" => hexdec("FFFFFF"),
                    // Field array of objects
                    "fields" => [
                        // Field 1
                        [
                            "name" => "userid of banned player",
                            "value" => $_POST['steamid'],
                            "inline" => false
                        ],
                        [
                            "name" => "Nickname of banned player",
                            "value" => $_POST['nickname'],
                            "inline" => false
                        ],
                        // Field 2
                        [
                            "name" => "Ban reason",
                            "value" => $_POST['reason'],
                            "inline" => true
                        ],
                        [
                            "name" => "Ban Duration",
                            "value" => $_POST['banduration'],
                            "inline" => true
                        ],
                        [
                            "name" => "Banned on (CET Time)",
                            "value" => $_POST['timestamp'],
                            "inline" => true
                        ],
                        [
                            "name" => "discord id/username",
                            "value" => $_SESSION["discordid"]. " <@".$_SESSION["discordid"].">",
                            "inline" => true
                        ],
                        [
                            "name" => "Unban Reason",
                            "value" => $_POST['unbanreason'],
                            "inline" => true
                        ],
                        // Field 3
                        [
                            "name" => "Admin Name",
                            "value" => $_POST['adminname'],
                            "inline" => true
                        ]
                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $hookObject,
            CURLOPT_HTTPHEADER => [
                "Length" => strlen($hookObject),
                "Content-Type: application/json"
            ],
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));

        $query = $db->prepare("UPDATE frikandelbroodjeban_list_current SET appealstate=:state WHERE playersteamid = :steamid64");

        $query->bindParam(":steamid64", $_POST['steamid']);
        $state = "1";
        $query->bindParam(":state", $state);

        $query->execute();
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));
                $query = $db->prepare("INSERT INTO banappeal (discordid, userid, appealreason)
                                VALUES (:discordid, :userid, :appealreason)");
        $query->bindParam(":discordid", $_SESSION["discordid"]);
        $query->bindParam(":userid", $_POST['steamid']);
        $query->bindParam(":appealreason", $_POST['unbanreason']);
        $query->execute();
        $_SESSION['sentappeal'] = true;
        successmodal("Your ban appeal has been sent and will be reviewed by staff.", 5, "false", "false");
    }
    ?>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <?php if ($_SESSION['sentappeal'] == true) { ?>
            <h2>Ban Appeal</h2>
            <hr>
            <p>Your ban appeal has been recieved and will be reviewed by staff, please wait patiently</p>
        <?php } ?>
        <?php if ($_SESSION['banappealisvalid'] == false && $_SESSION['sentappeal'] == false) { ?>
            <h2>Ban Appeal</h2>
            <hr>
            <p>You are not eligible for a ban appeal, this can have multiple reasons: Not logged in with steam, ban is shorter than 4 hours, you are not banned, you have already sent an appeal, server staff have marked your ban as unappealable. refresh this page and look at the popup message</p>
            <?php if ($_SESSION['discordplay'] == false) { ?>
            <p>Did you play using discord at the time of your ban? Click here:<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"><input class="btn btn-danger" type="submit" name="usingdiscord" value="I was playing using discord"></form></p>
            <?php } ?>
            <?php if ($_SESSION['discordplay'] == true) { ?>
                <p>Did you play using steam at the time of your ban? Click here:<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"><input class="btn btn-danger" type="submit" name="usingsteam" value="I was playing using steam"></form></p>
                <?php } ?>
        <?php } ?>
        <?php if ($_SESSION['banappealisvalid'] == true && $_SESSION['sentappeal'] == false) { ?>
            <h2>Ban Appeal</h2>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <?php echo '
        <h5 class="card-title">' . $ban['username'] . ' (' . $ban['playersteamid'] . ')</h5>
            <h6 class="card-subtitle mb-2 text-muted">Banned on: ' . $ban['timestamp'] . '</h6>
            <h6 class="card-subtitle mb-2 text-muted">Banned by: ' . $ban['adminname'] . '</h6>
            <h6 class="card-subtitle mb-2 text-muted">' . minToHourMinute($ban['banduration']) . ' total</h6>
            <h6 class="card-subtitle my-2 text-primary">' . minToHourMinute(getBanTimeLeft($ban)) . ' left</h6>
            <p class="card-text">Reason: ' . $ban['reason'] . '</p>
            '; if (empty($ban['username'])) { $nickname = "NaN"; } else { $nickname = $ban['username']; }?>
                <div class="form-group">
                    <label for="unbanreason">Why should we unban you</label>
                    <textarea class="form-control" rows="5" type="text" name="unbanreason" required></textarea><br>
                    <?php echo $steamid_err; ?>
                </div>
                <input class="btn btn-danger" type="submit" name="submit" value="Submit">
                <input class="form-control" type="text" name="steamid" readonly hidden value="<?php echo $ban['playersteamid'] ?>"><br>
                <input class="form-control" type="text" name="nickname" readonly hidden value="<?php echo $nickname ?>"><br>
                <input class="form-control" type="text" name="timestamp" readonly hidden value="<?php echo $ban['timestamp'] ?>"><br>
                            <input class="form-control" type="text" name="adminname" readonly hidden value="<?php echo $ban['adminname'] ?>"><br>
                            <input class="form-control" type="text" name="banduration" readonly hidden value="<?php echo minToHourMinute($ban['banduration']) ?>"><br>
                            <input class="form-control" type="text" name="reason" readonly hidden value="<?php echo $ban['reason'] ?>"><br>
            </form>
        <?php } ?>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>
