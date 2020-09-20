<?php
session_start();
if (!isset($_SESSION['loggedinf']) || !isset($_SESSION['steamid'])) {
    http_response_code(401);
    $ar = array("success" => "false", "message" => "Unauthorized");
    print_r(json_encode($ar));
    die();
}

function convertRichToHTML($rich) {
    $rich = json_encode(array("jsonstring" => $rich));
    // Replace color tags.
    $newData = preg_replace('/<color=([^>]+)>/ix', '<h8 style=\"color:$1\">', $rich);
    $newData = str_replace("<\/color>", '</h8>', $newData);

    // Replace alignment tags.
    $newData = preg_replace('/<align="([^>]+)">([^\n]*)[\n]?/ix', '<div align="$1">$2</div>', $newData);

    // Replace sizing tags.
    $newData = preg_replace('/<size=([0-9]+)%>/ix', '<span style="font-size:$1%">', $newData);
    $newData = preg_replace('/<\/size>/ix', '</span>', $newData);

    // Replace link tags.
    $newData = preg_replace('/<link="([^>]*)">/ix', '<a href="$1">', $newData);
    $newData = preg_replace('/<\/link>/ix', '</a>', $newData);

    //
    $newData = str_replace('\r', "<br>", $newData);
    $newData = str_replace('\n', "<br>", $newData);
    $newdata = json_decode($newData, true);
    print_r($newData);
}
echo convertRichToHTML(file_get_contents('php://input'));