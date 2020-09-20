<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
    function getRulesHTML() {
        $data = file_get_contents(GetPastebin());

        return convertRichToHTML($data);
    }

    function convertRichToHTML($rich) {
        // Replace color tags.
        $newData = preg_replace('/<color=([^>]+)>/ix', '<span style="color:$1">', $rich);
        $newData = preg_replace('/<\/color>/ix', '</span>', $newData);

        // Replace alignment tags.
        $newData = preg_replace('/<align="([^>]+)">([^\n]*)[\n]?/ix', '<div align="$1">$2</div>', $newData);

        // Replace sizing tags.
        $newData = preg_replace('/<size=([0-9]+)%>/ix', '<span style="font-size:$1%">', $newData);
        $newData = preg_replace('/<\/size>/ix', '</span>', $newData);

        // Replace link tags.
        $newData = preg_replace('/<link="([^>]*)">/ix', '<a href="$1">', $newData);
        $newData = preg_replace('/<\/link>/ix', '</a>', $newData);

        return $newData;
    }
?>