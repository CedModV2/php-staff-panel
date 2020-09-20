<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    // This function expects a path to a config file.
    function createPDO($filename) {
        $creds = parse_ini_file($filename, true);
        $dsn = "mysql:host=" . $creds['db']['hostname'] . ';dbname=' . $creds['db']['dbname'];
        $db = new PDO($dsn, $creds['db']['username'], $creds['db']['password']);

        return $db;
    }
?>