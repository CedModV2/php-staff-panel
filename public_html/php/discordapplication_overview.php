<?php     require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
use RestCord\DiscordClient;
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php"); ?>
<!DOCTYPE html>

<html>

<head>
    <?php
session_start();
    $game = "discord";
$title = "$game Application overview";

include "../../elements/title.php";
include "../../elements/metadata.php";
require_once "../../scripts/connect.php";
include "../../scripts/utils.php";
include "../../scripts/actions.php";
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
if ($_SESSION["permlevelfrikandelbroodje"] <= 3)
{
    include '../static/403.php';
    exit();
}
if (!in_array($game, $_SESSION['perms'])) {
    include '../static/403.php';
    exit();
}
    $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));
if (isset($_POST['submitdeny']))
{
     $discord = new DiscordClient(['token' => '']);
    try {
        $dm = $discord->user->createDm(array('recipient_id' => (int)$_POST['discordid']));
        $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
            "title" => "Staff Application",
            "type" => "rich",
            "description" => "Je staff applicatie is afgewezen.",
            "url" => "",
            "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),
            "color" => hexdec("FF0000"),
            "fields" => [
                [
                    "name" => "Reason",
                    "value" => $_POST['denyReason'],
                    "inline" => false
                ]
            ]
        ]));
    }
    catch (Exception $ex)
    {
        failmodal("An error has occured, make sure the applicant has DM's enabled and didnt block the bot: " . $ex->getMessage(), 30, "true", "true", 500);
    }
    $query = $db->prepare("DELETE FROM ".$game."_mod_apps WHERE discordid = :steamid64");

    $query->bindParam(":steamid64", $_POST['discordid']);

    $query->execute();

}

if (isset($_POST['submitapprove']))
{
     $discord = new DiscordClient(['token' => '']);
    try {
        $dm = $discord->user->createDm(array('recipient_id' => (int)$_POST['discordid']));
        $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
            "title" => "Staff Application",
            "type" => "rich",
            "description" => "Je staff applicatie is geacepteerd! Je zal binnenkort een DM krijgen van de desbetreffende manager.",
            "url" => "",
            "thumbnail" => ['url' => 'https://cdn.discordapp.com/emojis/525057528371871748.png?v=1'],
            "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),
            "color" => hexdec("008000"),
            "fields" => []
        ]));
    }
    catch (Exception $ex)
    {
        failmodal("An error has occured, make sure the applicant has DM's enabled and didnt block the bot: " . $ex->getMessage(), 30, "true", "true", 500);
    }
    $query = $db->prepare("DELETE FROM ".$game."_mod_apps WHERE discordid = :steamid64");

    $query->bindParam(":steamid64", $_POST['discordid']);

    $query->execute();
}
    $query = $db->prepare("SELECT * FROM ".$game."_mod_apps");
    $query->execute();

    $bans = $query->fetchAll();
?>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <h2><?php echo $game; ?> Application overview</h2>
        <hr>
        <div class="row">
            <div id="banColumn" class="col-md">
                <?php
foreach ($bans as $ban)
{
        echo generateApplicationCard($ban, $game);
}
?>
            </div>
        </div>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>
