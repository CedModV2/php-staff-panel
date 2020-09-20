<?php
session_start();
if (!isset($_SESSION['loggedinf']) || !isset($_SESSION['steamid'])) {
    http_response_code(401);
    $ar = array("success" => "false", "message" => "Unauthorized");
    print_r(json_encode($ar));
    die();
}
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/connect.php");
include_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/utils.php");
include_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/actions.php");

$options = json_decode(file_get_contents('php://input'));

$db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
if (!empty($options->Stats))
{
    $url = "https://test.cedmod.nl/api/getbans.php?alias=".GetAlias()."&limit=unlimited";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_USERAGENT, "CedMod Community management webpanel" . $domain);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, GetAPIKey().":".GetAPIKey());
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Alias: '.GetAlias(),
        'Port: 7777',
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    header("Content-Type: ".$info["content_type"]);
    http_response_code($info['http_code']);
    $bans = (array) json_decode($result, true);
    $arr = array();
    if ($bans["success"] == "true")
    {
        $bans1 = $bans['message'];
        foreach ($bans1 as $ban) {
            if (getBanTimeLeft($ban) == 0)
            {
                unbanPlayer($ban["playersteamid"], "Expired", "Server");
                continue;
            }
            if (!empty($arr[$ban['adminname']]))
                $arr[$ban['adminname']]++;
            else
                $arr[$ban['adminname']] = 1;
        }
    }
    print_r(json_encode($arr));
    exit();
}
if (!empty($options->Fetch))
{
    if (empty($options->Limit))
        $url = "https://test.cedmod.nl/api/getbans.php?alias=".GetAlias()."&limit=unlimited";
    else
        $url = "https://test.cedmod.nl/api/getbans.php?alias=".GetAlias()."&limit=".$options->Limit;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_USERAGENT, "CedMod Community management webpanel" . $domain);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, GetAPIKey().":".GetAPIKey());
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Alias: '.GetAlias(),
        'Port: 7777',
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    header("Content-Type: ".$info["content_type"]);
    http_response_code($info['http_code']);
    $bans = (array) json_decode($result, true);

    if ($bans["success"] == "true")
    {
        $bans1 = $bans['message'];
        foreach ($bans1 as $ban) {
            if (getBanTimeLeft($ban) == 0)
            {
                unbanPlayer($ban["playersteamid"], "Expired", "Server");
                continue;
            }
            if ($ban['ip'] == "0.0.0.0") {
                $db1 = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
                $query1 = $db1->prepare(
                    "SELECT * FROM antivpn_clear
        WHERE
        userid = ? AND server LIKE '".GetAlias()."' LIMIT 1"
                );
                $ip = "0.0.0.0";
                $query1->execute([$ban['playersteamid']]);
                if ($query1->rowCount() == 1) {
                    $user = $query1->fetch();
                    $ip = $user['ip'];
                    $query = $db->prepare("UPDATE " . GetAlias() . "ban_list_current SET ip=:ip WHERE playersteamid=:steamid64 ORDER BY id DESC");
                    $query->bindParam(":steamid64", $ban['playersteamid']);
                    $query->bindParam(":ip", $ip);
                    $query->execute();
                    $ban["ip"] = $ip;
                }
            }
            if (empty($ban["username"])) {
                $username = "Unable to resolve username.";
                if (explode("@", $ban['playersteamid'])[1] == "discord") {
                    $context = stream_context_create(["http" => ["method" => "GET", "header" => "Authorization: Bot NjY3ODUzNTc4MjE1NDg5NTc2.XscakQ.ijyh9jO1SLSLDSRBRTYJkCYxFLU"]]);
                    $file = @file_get_contents('https://discordapp.com/api/users/' . explode("@", $ban['playersteamid'])[0], true, $context);
                    $file = json_decode($file, true);
                    if (!empty($file['username'])) {
                        $username = $file['username'] . "#" . $file['discriminator'];
                    }
                }
                if (explode("@", $ban['playersteamid'])[1] == "steam") {
                    $xml = simplexml_load_file("https://steamcommunity.com/profiles/" . explode("@", $ban['playersteamid'])[0] . "/?xml=1");
                    if (!empty($xml)) {
                        if ($xml->steamID != "")
                            $username = $xml->steamID;
                    }
                }
                if (explode("@", $ban['playersteamid'])[1] == "northwood") {
                    $username = explode("@", $ban['playersteamid'])[0];
                }
                if (empty($username))
                    $username = "Unable to resolve username.";
                $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
                $query = $db->prepare("UPDATE " . GetAlias() . "ban_list_current SET username=:username WHERE playersteamid=:steamid64 ORDER BY id DESC");
                $query->bindParam(":steamid64", $ban['playersteamid']);
                $query->bindParam(":username", $username);
                $query->execute();
                $ban["username"] = $username;
            }
            echo generateBanCard($ban);
        }
    }
    else
        echo $bans["message"];
}
else {
    $options->steamID = str_replace(' ', '%20', $options->steamID);
    $options->nickname = str_replace(' ', '%20', $options->nickname);
    $options->modname = str_replace(' ', '%20', $options->modname);
    $url = "https://test.cedmod.nl/api/getban.php?alias=".GetAlias()."&id=".$options->steamID."&name=".$options->nickname."&aname=".$options->modname;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_USERAGENT, "CedMod Community management webpanel" . $domain);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, GetAPIKey().":".GetAPIKey());
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Alias: '.GetAlias(),
        'Port: 7777',
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    header("Content-Type: ".$info["content_type"]);
    http_response_code($info['http_code']);
    $bans = (array) json_decode($result, true);
    if ($bans["success"] == "true") {
        $bans1 = $bans['message'];
        foreach ($bans1 as $ban) {
            if (getBanTimeLeft($ban) == 0)
            {
                unbanPlayer($ban["playersteamid"], "Expired", "Server");
                continue;
            }
            if ($ban['ip'] == "0.0.0.0") {
                $db1 = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
                $query1 = $db1->prepare(
                    "SELECT * FROM antivpn_clear
        WHERE
        userid = ? AND server LIKE '".GetAlias()."' LIMIT 1"
                );
                $ip = "0.0.0.0";
                $query1->execute([$ban['playersteamid']]);
                if ($query1->rowCount() == 1) {
                    $user = $query1->fetch();
                    $ip = $user['ip'];
                    $query = $db->prepare("UPDATE " . GetAlias() . "ban_list_current SET ip=:ip WHERE playersteamid=:steamid64 ORDER BY id DESC");
                    $query->bindParam(":steamid64", $ban['playersteamid']);
                    $query->bindParam(":ip", $ip);
                    $query->execute();
                    $ban["ip"] = $ip;
                }
            }
            if (empty($ban["username"])) {
                $username = "Unable to resolve username.";
                if (explode("@", $ban['playersteamid'])[1] == "discord") {
                    $context = stream_context_create(["http" => ["method" => "GET", "header" => "Authorization: Bot NjY3ODUzNTc4MjE1NDg5NTc2.XscakQ.ijyh9jO1SLSLDSRBRTYJkCYxFLU"]]);
                    $file = @file_get_contents('https://discordapp.com/api/users/' . explode("@", $ban['playersteamid'])[0], true, $context);
                    $file = json_decode($file, true);
                    if (!empty($file['username'])) {
                        $username = $file['username'] . "#" . $file['discriminator'];
                    }
                }
                if (explode("@", $ban['playersteamid'])[1] == "steam") {
                    $xml = simplexml_load_file("https://steamcommunity.com/profiles/" . explode("@", $ban['playersteamid'])[0] . "/?xml=1");
                    if (!empty($xml)) {
                        if ($xml->steamID != "")
                            $username = $xml->steamID;
                    }
                }
                if (explode("@", $ban['playersteamid'])[1] == "northwood") {
                    $username = explode("@", $ban['playersteamid'])[0];
                }
                if (empty($username))
                    $username = "Unable to resolve username.";
                $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
                $query = $db->prepare("UPDATE " . GetAlias() . "ban_list_current SET username=:username WHERE playersteamid=:steamid64 ORDER BY id DESC");
                $query->bindParam(":steamid64", $ban['playersteamid']);
                $query->bindParam(":username", $username);
                $query->execute();
                $ban["username"] = $username;
            }
            echo generateBanCard($ban);
        }
    }
    else
        echo $bans["message"];
}