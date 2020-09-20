<!DOCTYPE html>

<html>

<head>
    <?php
    session_start();
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    $title = "Rules";
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/title.php");
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/metadata.php");
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/pastebin.php");
    ?>
</head>

<body>
    <?php
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php");
    ?>
    <div id="content-container" class="mt-5 p-5 min-vh-100">
        <?php
            echo getRulesHTML();
        ?>
    </div>
</body>
<style type="text/css">
    #content-container {
        background: url(../../static/rules_bg.jpg) no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;

        color: white;
        font-weight: 600;

        white-space: pre;
    }
</style>

</html>