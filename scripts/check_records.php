<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    require_once "connect.php";

    // This function is used to check if a row exists in a table with a certain index value. Useful for values
    // that are meant to be unique.
    function userExists($username) {
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/config/mysqllogin.ini"));

        $query = $db -> prepare("SELECT * FROM user WHERE username = :username");

        $query -> bindParam(":username", $username);

        $query -> execute();

        if ($query -> rowCount() > 0) {
            return true;
        }

        return false;
    }
?>