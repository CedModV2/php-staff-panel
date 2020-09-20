<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/connect.php");
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/utils.php");
    // Add a new ban to the database.
    function banPlayer($steamid, $bd, $reason1, $modName)
    {

        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
        $db1 = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../../cedmodsite/config/mysqlban.ini"));
        $query1 = $db1 -> prepare(
        "SELECT * FROM ".GetAlias()."frikandelbroodjeplayerstats
        WHERE
        playersteamid = ?"
        );
        $ip = "0.0.0.0";
        $query1 -> execute([$steamid]);
        if ($query1 -> rowCount() == 1) {
        $user = $query1 -> fetch();
        $ip = $user['ip'];
        $username = $user['username'];
        }
        else
        {  
            $ip = "0.0.0.0";
            $username = "";
        }
        $query = $db->prepare(
            "INSERT INTO ".GetAlias()."ban_list_current (playersteamid, username, banduration, reason, unixstamp, adminname, timestamp, ip)
                                VALUES (:steamid64, :username, :duration, :reason, :time_given, :adminname, :timestamp, :ip)"
        );
        $current_time = time();
        date_default_timezone_set('Europe/Amsterdam'); // CET
        $info = getdate();
        $date = $info['mday'];
        $month = $info['mon'];
        $year = $info['year'];
        $hour = $info['hours'];
        $min = $info['minutes'];
        $sec = $info['seconds'];
        $current_date2 = "$year-$month-$date $hour:$min:$sec";
        $added_time = 0;
        $reason2 = "";
        $bd = $bd + $added_time;
        $reason3 = $reason1 . $reason2;
        $query->bindParam(":steamid64", $steamid);
        $query->bindParam(":username", $username);
        $query->bindParam(":duration", $bd, PDO::PARAM_INT);
        $query->bindParam(":reason", $reason3);
        $query->bindParam(":adminname", $modName);
        $query->bindParam(":time_given", $current_time, PDO::PARAM_INT);
        $query->bindParam(":timestamp", $current_date2);
        $query->bindParam(":ip", $ip);

        $query->execute();
        $query = $db->prepare(
            "INSERT INTO ".GetAlias()."ban_logs_all (playersteamid, username, banduration, reason, adminname, timestamp, ip)
                                VALUES (:steamid64, :username, :duration, :reason, :adminname, :timestamp, :ip)"
        );
        $query->bindParam(":steamid64", $steamid);
        $query->bindParam(":username", $username);
        $query->bindParam(":duration", $bd, PDO::PARAM_INT);
        $query->bindParam(":reason", $reason3);
        $query->bindParam(":adminname", $modName);
        $query->bindParam(":timestamp", $current_date2);
        $query->bindParam(":ip", $ip);
        $query->execute();
        createAuditLog($current_time, 'ban', $modName, "Banned " . $steamid . $username . " for '" . $reason1 . "' (" . minToHourMinute($bd) . ")");
    }
    // Delete a ban record from the database.
    function unbanPlayer($steamid, $reason, $modName)
    {
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));

        $query = $db->prepare("DELETE FROM ".GetAlias()."ban_list_current WHERE playersteamid = :steamid64");

        $query->bindParam(":steamid64", $steamid);

        $query->execute();

        createAuditLog(time(), 'unban', $modName, "Unbanned " . $steamid . " for '" . $reason . "'");
    }
    function delBanLog($steamid, $reason, $modName)
    {
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));

        $query = $db->prepare("DELETE FROM ".GetAlias()."ban_logs_all WHERE id = :steamid64");

        $query->bindParam(":steamid64", $steamid);

        $query->execute();

        createAuditLog(time(), 'del_banlog', $modName, "Deleted BanLog " . $steamid . " for '" . $reason . "'");
    }

    // Extend a ban.
    function extendBan($steamid, $addedTime, $reason, $modName)
    {
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
        $user = getBan($steamid);
        $newDuration = $addedTime;

        $query = $db->prepare("UPDATE ".GetAlias()."ban_list_current SET banduration=:duration WHERE playersteamid = :steamid64");

        $query->bindParam(":steamid64", $steamid);
        $query->bindParam(":duration", $newDuration, PDO::PARAM_INT);

        $query->execute();

        createAuditLog(time(), 'extend_ban', $modName, "Extended " . $steamid . "'s ban by " . $addedTime . " minutes for '" . $reason . "'");
    }

    // Change ban reason.
    function changeBanReason($steamid, $newReason, $reason, $modName)
    {
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlban.ini"));
        $username = getSteamName($steamid);

        $query = $db->prepare("UPDATE ".GetAlias()."ban_list_current SET reason=:reason WHERE playersteamid=:steamid64");

        $query->bindParam(":steamid64", $steamid);
        $query->bindParam(":reason", $newReason);

        $query->execute();

        createAuditLog(time(), 'change_ban_reason', $modName, "Changed ban reason of " . $username . " to '" . $newReason . "' because '" . $reason . "'");
    }
