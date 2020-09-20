<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
if($_GET["link"] == "gmodmanager")
{
        echo "<script>window.location.replace('https://cloud.cedmod.nl/index.php/apps/richdocuments/public?fileId=%7Bfile_id%7D&shareToken=g6LQ9qlBKJPlpbR');</script>";
}
if($_GET["link"] == "developer")
{
    echo "<script>window.location.replace('https://cloud.cedmod.nl/index.php/apps/richdocuments/public?fileId=%7Bfile_id%7D&shareToken=PryCDJXZyDGexSn');</script>";
}
?>