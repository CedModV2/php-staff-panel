<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
use RestCord\DiscordClient;
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php"); ?>
<!DOCTYPE html>

<html>

<head>
    <?php
session_start();

$title = "Appeal overview";

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
    $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));
if (isset($_POST['submitdeny']))
{
    try
    {
    $discord = new DiscordClient(['token' => '']);
    $dm = $discord->user->createDm(array('recipient_id' => (int)$_POST['discordid']));
    $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
        // Set the title for your embed
        "title" => "Ban appeal",

        // The type of your embed, will ALWAYS be "rich"
        "type" => "rich",

        // A description for your embed
        "description" => "Your ban appeal has been denied.",

        // The URL of where your title will be a link to
        "url" => "",
        "thumbnail" => ['url' => 'https://cdn.discordapp.com/attachments/617823370368778241/723210623730581565/ohnononononono.png'],
        /* A timestamp to be displayed below the embed, IE for when an an article was posted
 * This must be formatted as ISO8601
 */
        "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),

        // The integer color to be used on the left side of the embed
        "color" => hexdec("FF0000"),
        // Field array of objects
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
        failmodal("Appeal was accepted, but An error has occured, the user may not have DM's enabled or blocked the bot: " . $ex->getMessage(), 30, "true", "true", 500);
    }
    $query = $db->prepare("DELETE FROM banappeal WHERE userid = :steamid64");

    $query->bindParam(":steamid64", $_POST['steamid64']);

    $query->execute();
    successmodal("Appeal denied", 5, "false", "false", 500);
    createAuditLog(time(), 'reject_appeal', $_SESSION['username'], "Rejected appeal for: ".$_POST['discordid']." Reason: ".$_POST['denyReason']);
}

if (isset($_POST['submitapprove']))
{
    try {
        $discord = new DiscordClient(['token' => '']);
        $dm = $discord->user->createDm(array('recipient_id' => (int)$_POST['discordid']));
        $channel = $discord->channel->createMessage(array('channel.id' => $dm->id, 'embed' => [
            // Set the title for your embed
            "title" => "Ban appeal",

            // The type of your embed, will ALWAYS be "rich"
            "type" => "rich",

            // A description for your embed
            "description" => "Your ban appeal has been approved.",

            // The URL of where your title will be a link to
            "url" => "",

            "thumbnail" => ['url' => 'https://cdn.discordapp.com/emojis/525057528371871748.png?v=1'],
            /* A timestamp to be displayed below the embed, IE for when an an article was posted
     * This must be formatted as ISO8601
     */
            "timestamp" => date('Y-m-d\TH:i:s.Z\Z', time()),

            // The integer color to be used on the left side of the embed
            "color" => hexdec("008000"),
            // Field array of objects
            "fields" => []
        ]));
    }
    catch (Exception $ex)
    {
        failmodal("Appeal was denied, but An error has occured, the user may not have DM's enabled or blocked the bot: " . $ex->getMessage(), 30, "true", "true", 500);
    }
    $query = $db->prepare("DELETE FROM banappeal WHERE userid = :steamid64");

    $query->bindParam(":steamid64", $_POST['steamid64']);

    $query->execute();
    unbanPlayer($_POST['steamid64'], "Appeal accepted", $_SESSION['username']);

    createAuditLog(time(), 'accept_appeal', $_SESSION['username'], "Accepted appeal for: ".$_POST['discordid']);
    successmodal("Appeal approved", 5, "false", "false", 500);
}
    $query = $db->prepare("SELECT * FROM banappeal");
    $query->execute();

    $bans = $query->fetchAll();
?>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <h2>Appeal overview</h2>
        <hr>
        <div class="row">
            <div id="banColumn" class="col-md">
                <?php
foreach ($bans as $ban)
{
        echo generateAppealCard($ban);
}
?>
            </div>
        </div>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>
