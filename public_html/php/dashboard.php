<!DOCTYPE html>

<html>

<head>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    session_start();

    $title = "Dashboard";
    include_once "../../scripts/utils.php";
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
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/title.php");
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/metadata.php");
    ?>
</head>

<body>
    <?php
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php");
    ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <ul class="nav flex-column">
            <?php if (empty($_SESSION["permlevelfrikandelbroodje"])) {
                $permlevel = 0;
            } else {
                $permlevel = $_SESSION["permlevelfrikandelbroodje"];
            }
            if ($permlevel >= 1 && $_SESSION["loggedinf"]) {
            ?>
                <li class="nav-item">
                    <a class="nav-link" href="ban_management.php">Ban Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="audit_log.php">Audit Log</a>
                </li>
            <?php } ?>
            <?php if (empty($_SESSION["loggedinf"])) {
                $loggedin = false;
            } else {
                $loggedin = $_SESSION["loggedinf"];
            } if ($loggedin) { ?>

                <li class="nav-item">
                    <a class="nav-link" href="/php/unban_form.php">Ban Appeals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/php/staff_apps.php">Staff Applications</a>
                </li>
            <?php } ?>
        </ul>
    </div>

</body>

</html>