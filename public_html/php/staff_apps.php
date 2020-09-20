<!DOCTYPE html>

<html>

<head>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    use RestCord\DiscordClient;
    session_start();
    $title = "Staff apps";

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
    $steamid_err = "";
    $_SESSION['blacklisted'] = false;
    if (empty($_SESSION['gamesubmitted']))
    {
        $_SESSION['gamesubmitted'] = "false";
    }
    if ($_SESSION['gamesubmitted'] == "blacklisted")
    {
        $_SESSION['gamesubmitted'] = "false";
    }
    $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));

    $query = $db -> prepare("SELECT * FROM staff_app_blacklist WHERE discordid LIKE :discordid");
    $query -> bindParam(":discordid", $_SESSION['discordid']);
    $query -> execute();
    if ($query -> rowCount() > 0) {
        $_SESSION['blacklisted'] = true;
        $ban = $query->fetch();
        $_SESSION['blacklistedreason'] = $ban['reason'];
        $_SESSION['gamesubmitted'] = "blacklisted";
    }
     $discord = new DiscordClient(['token' => '']);
    try {
        $userds = $discord->guild->getGuildMember(array("guild.id" => 589191346246516757, "user.id" => (int)$_SESSION['discordid']));
    }
    catch (Exception $ex)
    {
        if ($ex->getMessage() == "There was an error executing the getGuildMember command: Client error: `GET https://discord.com/api/v6/guilds/675279655762132994/members/".$_SESSION['discordid']."` resulted in a `404 Not Found` response: ")
        {
            $_SESSION['blacklisted'] = true;
            $_SESSION['blacklistedreason'] = "Niet in de frikandiscord";
            $_SESSION['gamesubmitted'] = "blacklisted";
        }
    }
    if (in_array(597203634576424970, $userds->roles))
    {
        $_SESSION['blacklisted'] = true;
        $_SESSION['blacklistedreason'] = "Je kan geen applicatie maken terwijl je stille willem hebt";
        $_SESSION['gamesubmitted'] = "blacklisted";
    }
    if (in_array(705522307988193364, $userds->roles))
    {
        $_SESSION['blacklisted'] = true;
        $_SESSION['blacklistedreason'] = "Je kan geen applicatie maken terwijl je RAID hebt";
        $_SESSION['gamesubmitted'] = "blacklisted";
    }
    if (isset($_POST['nextgame'])) {
        $_SESSION['gamesubmitted'] = $_POST['game'];
    }
    if (isset($_POST['submitdiscord'])) {
        $_SESSION['gamesubmitted'] = "done";
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));

        $query = $db -> prepare("INSERT INTO discord_mod_apps
                            (age, why, about, discordid)
                            VALUES
                            (:age, :why, :about, :discordid)
                        ");
        $query -> bindParam(":steam", $_POST['steamlink']);
        $query -> bindParam(":age", $_POST['age']);
        $query -> bindParam(":why", $_POST['why']);
        $query -> bindParam(":about", $_POST['about']);
        $query -> bindParam(":discordid", $_SESSION['discordid']);

        $query -> execute();
         $discord = new DiscordClient(['token' => '']);
        $channel = $discord->channel->createMessage(array("channel.id" => 677651341614776330, "content" => "New Discord application from ".$_SESSION['discordid']));
        try {
            $dm = $discord->user->createDm(array('recipient_id' => (int)$_SESSION['discordid']));
            $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
                // Set the title for your embed
                "title" => "Staff application",
                "type" => "rich",
                "description" => "Je applicatie is ontvangen en zal spoedig bekeken worden door de desbetreffende manager.",
                "url" => "",
                "thumbnail" => ['url' => 'https://cdn.discordapp.com/emojis/525057528371871748.png?v=1'],
                "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),
                "color" => hexdec("008000"),
                "fields" => []
            ]));
            successmodal("Application has been submitted", 5, "false", "false");
        }
        catch (Exception $ex)
        {
            failmodal("An error has occured, make sure you have DM's enabled and didnt block FrikanBot
#9404: " . $ex->getMessage(), 30, "true", "true", 500);
        }
    }
    if (isset($_POST['submitscpsl'])) {
        $_SESSION['gamesubmitted'] = "done";
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));

        $query = $db -> prepare("INSERT INTO scpsl_mod_apps
                            (steam, age, why, about, discordid)
                            VALUES
                            (:steam, :age, :why, :about, :discordid)
                        ");

        $query -> bindParam(":steam", $_POST['steamlink']); 
        $query -> bindParam(":age", $_POST['age']);
        $query -> bindParam(":why", $_POST['why']);
        $query -> bindParam(":about", $_POST['about']);
        $query -> bindParam(":discordid", $_SESSION['discordid']);

        $query -> execute();
         $discord = new DiscordClient(['token' => '']);
        $channel = $discord->channel->createMessage(array("channel.id" => 677651341614776330, "content" => "New SCP:SL application from ".$_SESSION['discordid']));
        try {
            $dm = $discord->user->createDm(array('recipient_id' => (int)$_SESSION['discordid']));
            $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
                // Set the title for your embed
                "title" => "Staff application",
                "type" => "rich",
                "description" => "Je applicatie is ontvangen en zal spoedig bekeken worden door de desbetreffende manager.",
                "url" => "",
                "thumbnail" => ['url' => 'https://cdn.discordapp.com/emojis/525057528371871748.png?v=1'],
                "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),
                "color" => hexdec("008000"),
                "fields" => []
            ]));
            successmodal("Application has been submitted", 5, "false", "false");
        }
        catch (Exception $ex)
        {
            failmodal("An error has occured, make sure you have DM's enabled and didnt block FrikanBot
#9404: " . $ex->getMessage(), 30, "true", "true", 500);
        }
    }
    if (isset($_POST['submitdev'])) {
        $_SESSION['gamesubmitted'] = "done";
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));

        $query = $db -> prepare("INSERT INTO dev_mod_apps
                            (steam, age, why, about, discordid)
                            VALUES
                            (:steam, :age, :why, :about, :discordid)
                        ");

        $query -> bindParam(":steam", $_POST['steamlink']); 
        $query -> bindParam(":age", $_POST['age']);
        $query -> bindParam(":why", $_POST['why']);
        $query -> bindParam(":about", $_POST['about']);
        $query -> bindParam(":discordid", $_SESSION['discordid']);

        $query -> execute();
         $discord = new DiscordClient(['token' => '']);
        $channel = $discord->channel->createMessage(array("channel.id" => 677651341614776330, "content" => "New Dev application from ".$_SESSION['discordid']));
        try {
            $dm = $discord->user->createDm(array('recipient_id' => (int)$_SESSION['discordid']));
            $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
                // Set the title for your embed
                "title" => "Staff application",
                "type" => "rich",
                "description" => "Je applicatie is ontvangen en zal spoedig bekeken worden door de desbetreffende manager.",
                "url" => "",
                "thumbnail" => ['url' => 'https://cdn.discordapp.com/emojis/525057528371871748.png?v=1'],
                "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),
                "color" => hexdec("008000"),
                "fields" => []
            ]));
            successmodal("Application has been submitted", 5, "false", "false");
        }
        catch (Exception $ex)
        {
            failmodal("An error has occured, make sure you have DM's enabled and didnt block FrikanBot
#9404: " . $ex->getMessage(), 30, "true", "true", 500);
        }
    }
    if (isset($_POST['submitgmanager'])) {
        $_SESSION['gamesubmitted'] = "done";
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));

        $query = $db -> prepare("INSERT INTO gmanager_mod_apps
                            (steam, age, why, about, discordid)
                            VALUES
                            (:steam, :age, :why, :about, :discordid)
                        ");

        $query -> bindParam(":steam", $_POST['steamlink']);
        $query -> bindParam(":age", $_POST['age']);
        $query -> bindParam(":why", $_POST['why']);
        $query -> bindParam(":about", $_POST['about']);
        $query -> bindParam(":discordid", $_SESSION['discordid']);

        $query -> execute();
         $discord = new DiscordClient(['token' => '']);
        $channel = $discord->channel->createMessage(array("channel.id" => 677651341614776330, "content" => "New Manager application from ".$_SESSION['discordid']));
        try {
            $dm = $discord->user->createDm(array('recipient_id' => (int)$_SESSION['discordid']));
            $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
                // Set the title for your embed
                "title" => "Staff application",
                "type" => "rich",
                "description" => "Je applicatie is ontvangen en zal spoedig bekeken worden door de desbetreffende manager.",
                "url" => "",
                "thumbnail" => ['url' => 'https://cdn.discordapp.com/emojis/525057528371871748.png?v=1'],
                "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),
                "color" => hexdec("008000"),
                "fields" => []
            ]));
            successmodal("Application has been submitted", 5, "false", "false");
        }
        catch (Exception $ex)
        {
            failmodal("An error has occured, make sure you have DM's enabled and didnt block FrikanBot
#9404: " . $ex->getMessage(), 30, "true", "true", 500);
        }
    }
    ?>

</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <?php if ($_SESSION['gamesubmitted'] == "false" && $_SESSION['isstaff'] == false) { ?>
            <h2>Staff applications</h2>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <div class="form-group">
                    <label for="form-check">For which position do you want to apply for</label>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="discord" value="discord" disabled name="game">
                        <label class="form-check-label" for="discord">Discord Moderator (Closed)</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="scpsl" value="scpsl" disabled name="game">
                        <label class="form-check-label" for="scpsl">SCP:SL Moderator (Closed)</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="dev" value="dev" name="game">
                        <label class="form-check-label" for="dev">Developer <a href="https://frikandelbroodjeserver.nl/link.php?link=developer
" target="_blank">https://frikandelbroodjeserver.nl/link.php?link=developer
                            </a></label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="gmanager" value="gmanager" disabled name="game">
                        <label class="form-check-label" for="gmanager">Gmod Manager (Closed)</label>
                    </div>
                    <?php echo $steamid_err; ?>
                </div>
                <input class="btn btn-primary" type="submit" name="nextgame" value="Next">
            </form>
        <?php } if ($_SESSION['gamesubmitted'] == "false" && $_SESSION['isstaff'] == true) { ?>
        <h2>Internal Staff applications</h2>
        <hr>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <div class="form-group">
                <label for="form-check">For which position do you want to apply for</label>
                <div class="form-check">
                    <input type="radio" class="form-check-input" id="discord" value="discord" disabled name="game">
                    <label class="form-check-label" for="discord">Discord Moderator (Closed)</label>
                </div>
                <div class="form-check">
                    <input type="radio" class="form-check-input" id="scpsl" value="scpsl" disabled name="game">
                    <label class="form-check-label" for="scpsl">SCP:SL Moderator (Closed)</label>
                </div>
                <div class="form-check">
                    <input type="radio" class="form-check-input" id="dev" value="dev" name="game">
                    <label class="form-check-label" for="dev">Developer <a href="https://frikandelbroodjeserver.nl/link.php?link=developer
" target="_blank">https://frikandelbroodjeserver.nl/link.php?link=developer
                        </a></label>
                </div>
                <div class="form-check">
                    <input type="radio" class="form-check-input" id="gmanager" value="gmanager" disabled name="game">
                    <label class="form-check-label" for="gmanager">Gmod Manager (Closed)</label>
                </div>
                <?php echo $steamid_err; ?>
            </div>
            <input class="btn btn-primary" type="submit" name="nextgame" value="Next">
        </form>
        <?php } if ($_SESSION['blacklisted'] == "true") { ?>
        <h2>Blacklisted</h2>
        <hr>
            <p>You have been blacklisted due to the folowing reason:</p>
            <p><?php echo $_SESSION['blacklistedreason']; ?></p>
        <?php } ?>
        <?php if ($_SESSION['gamesubmitted'] == "discord") { ?>
            <h2><?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'Internal';} ?> Discord Moderator Application</h2>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="age">Je leeftijd (je moet 15 jaar of ouder zijn).</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="15"';} ?> class="form-control" type="number"  name="age" min="15" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">De <u>link</u> naar je steam profiel.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="steamlink" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label for="steamid">Waarom wil je Discord Mod worden.</label>
                    <input class="form-control" type="text" name="why" required><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">Vertel iets over jezelf.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="about" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <?php echo $steamid_err; ?>
                </div>
                <input class="btn btn-primary" type="submit" name="submitdiscord" value="Next">
            </form>
        <?php } ?>
        <?php if ($_SESSION['gamesubmitted'] == "scpsl") { ?>
            <h2><?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'Internal';} ?> SCP:SL Moderator Application</h2>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="age">Je leeftijd (je moet 15 jaar of ouder zijn).</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="15"';} ?> class="form-control" type="number"  name="age" min="15" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">De <u>link</u> naar je steam profiel.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="steamlink" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label for="steamid">Waarom wil je moderator worden.</label>
                    <input class="form-control" type="text" name="why" required><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">Vertel iets over jezelf.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="about" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <?php echo $steamid_err; ?>
                </div>
                <input class="btn btn-primary" type="submit" name="submitscpsl" value="Next">
            </form>
        <?php } ?>
        <?php if ($_SESSION['gamesubmitted'] == "dev") { ?>
            <h2><?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'Internal';} ?> Developer Application</h2>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="age">Je leeftijd (je moet 15 jaar of ouder zijn).</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="15"';} ?> class="form-control" type="number"  name="age" min="15" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">De <u>link</u> naar je steam profiel.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="steamlink" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label for="steamid">Welke programmeertalen ken je?</label>
                    <input class="form-control" type="text" name="why" required><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">Vertel iets over jezelf.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="about" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <?php echo $steamid_err; ?>
                </div>
                <input class="btn btn-primary" type="submit" name="submitdev" value="Next">
            </form>
        <?php } ?>
        <?php if ($_SESSION['gamesubmitted'] == "gmanager") { ?>
            <h2><?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'Internal';} ?> Gmod Manager Application</h2>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="age">Je leeftijd (je moet 15 jaar of ouder zijn).</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="15"';} ?> class="form-control" type="number"  name="age" min="15" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">De <u>link</u> naar je steam profiel.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="steamlink" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <label for="steamid">Waarom wil je Gmod Manager worden.</label>
                    <input class="form-control" type="text" name="why" required><br>
                </div>
                <div class="form-group">
                    <label <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden';} ?> for="steamid">Vertel iets over jezelf.</label>
                    <input <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 1) {echo 'hidden value="NaN (internal application)"';} ?> class="form-control" type="text" name="about" <?php if ($_SESSION['permlevelfrikandelbroodje'] == 0) {echo 'required';} ?>><br>
                </div>
                <div class="form-group">
                    <?php echo $steamid_err; ?>
                </div>
                <input class="btn btn-primary" type="submit" name="submitgmanager" value="Next">
            </form>
        <?php } ?>
        <?php if ($_SESSION['gamesubmitted'] == "done") { ?>
            <h2>Done</h2>
            <hr>
            <p>Your application has been submitted.</p>
        <?php } ?>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>