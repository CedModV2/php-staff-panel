<!DOCTYPE html>

<html>

<head>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    session_start();

    $title = "Audit log";

    include "../../elements/title.php";
    include "../../elements/metadata.php";
    include "../../scripts/connect.php";
    include "../../scripts/utils.php";

    $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));
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
    if (!$_SESSION["permlevelfrikandelbroodje"] >= 1) {
        include '../static/403.php';
        exit();;
    }
    $query = $db->prepare("SELECT * FROM autilog WHERE action != 'command' ORDER BY id DESC");
    $query->execute();

    $logs = $query->fetchAll();
    $query = $db->prepare("SELECT * FROM autilog WHERE action = 'command' ORDER BY id DESC");
    $query->execute();

    $commandlogs = $query->fetchAll();

    function generateAuditLog($log)
    {

        switch ($log['action']) {
            case "reject_appeal":
            case "ban":
                $color = "red";
                break;
            case "del_banlog":
            case "accept_appeal":
            case "unban":
                $color = "green";
                break;
            case "extend_ban":
                $color = "orange";
                break;
            case "change_ban_reason":
                $color = "#7387c3";
                break;
            default:
                $color = "gray";
        }

        return "
                <tr class='row'>
                <td class='col-2 tabelcolor'>
                " .  date("Y-m-d H:i:s", $log['timestamp']) . "
                </td>
                <td style='background-color:" . $color . " !important;' class='col-2 tabelcolor'>
                " .  $log['action'] . "
                </td>
                <td class='col-2 tabelcolor'>
                " .  $log['user'] . "
                </td>
                <td class='col tabelcolor'>
                " .  $log['parameters'] . "
                </td>
                </tr>
            ";
    }
    ?>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <ul class="nav nav-tabs border-0">
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss active border border-secondary border-bottom-0" data-toggle="tab" href="#issue">Audit Log</a>
            </li>
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss border border-secondary border-bottom-0" data-toggle="tab" href="#overview">Command Log</a>
            </li>
        </ul>
        <div class="tab-content">
        <div class="tab-pane active border-top border-secondary" id="issue">
        <table class="table row">
            <thead class=" col-12 thead-light">
                <tr class="row table-secondary">
                    <th  class="col-2 tablebegin"  scope="col">datetime</th>
                    <th  class="col-2 tablebegin" scope="col">action</th>
                    <th  class="col-2 tablebegin" scope="col">moderator</th>
                    <th  class="col tablebegin" scope="col">parameters</th>
                </tr>
            </thead>
            <tbody class="col-12">
                <?php
                $log = array();

                foreach ($logs as $log) {
                    echo generateAuditLog($log);
                }
                ?>
            </tbody>
        </table>
        </div>
        <div class="tab-pane fade border-top border-secondary" id="overview">
            <table class="table row">
                <thead class=" col-12 thead-light">
                <tr class="row table-secondary">
                    <th  class="col-2 tablebegin"  scope="col">datetime</th>
                    <th  class="col-2 tablebegin" scope="col">action</th>
                    <th  class="col-2 tablebegin" scope="col">moderator</th>
                    <th  class="col tablebegin" scope="col">parameters</th>
                </tr>
                </thead>
                <tbody class="col-12">
                <?php
                $clog = array();

                foreach ($commandlogs as $clog) {
                    echo generateAuditLog($clog);
                }
                ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>