<!DOCTYPE html>

<html>

<head>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
        session_start();

        $title = "All Accounts";

        include "../../elements/title.php";
        include "../../elements/metadata.php";
        include "../../scripts/connect.php";
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
        if (!$_SESSION["permlevelfrikandelbroodje"] >= 4)
        {
            include '../static/403.php';
            exit();
        }
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqllogin.ini"));

        $query = $db -> prepare("SELECT * FROM frikandelbroodjeusers ORDER BY id ASC");
        $query -> execute();

        $logs = $query -> fetchAll();

        function generateAuditLog($log) {

            switch ($log['permissionlevel']) {
                case "1":
                    $color = "#7387c3";
                break;
                case "2":
                    $color = "#7387c3";
                break;
                case "3":
                    $color = "red";
                break;
                case "4":
                    $color = "red";
                break;
                default:
                    $color = "table-light";
            }

            return "
                <tr>
                <td style='background-color:" . $color . " !important;' class='tabelcolor'>
                " .  $log['username'] . "
                </td>
                <td class='tabelcolor'>
                " .  $log['permissionlevel'] . "
                </td>
                <td class='tabelcolor'>
                " .  $log['disabled'] . "
                </td>
                <td class='tabelcolor'>
                " .  $log['disabledadmin'] . "
                </td>
                <td class='tabelcolor'>
                " .  $log['discordid'] . "
                </td>
                </tr>
            ";
        }
    ?>
</head>

<body>
<?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
<div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
    <table class="table">
        <thead class="thead-light">
            <tr class="table-secondary">
                <th style="width: 18%" class="tablebegin" scope="col">UserName</th>
                <th style="width: 18%" class="tablebegin" scope="col">Permission Level</th>
                <th style="width: 18%" class="tablebegin" scope="col">IsDisabled</th>
                <th style="width: 18%" class="tablebegin" scope="col">IsDisabledByAdmin</th>
                <th style="width: 18%" class="tablebegin" scope="col">DiscordID</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $log = array();

            foreach ($logs as $log) {
                echo generateAuditLog($log);
            }
        ?>
        </tbody>
    </table>
</div>
<?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>