<?php
session_start();
if (!isset($_SESSION['loggedinf']) || !isset($_SESSION['steamid'])) {
    http_response_code(401);
    $ar = array("success" => "false", "message" => "Unauthorized");
    print_r(json_encode($ar));
    die();
}
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
if (empty($_GET['url']))
{
    $ar = array("success" => false, "error" => "missing arguments");
    $json = json_encode($ar);
    print_r($json);
    die();
}
$url1 = $_GET['url'];
$url1 = str_replace(' ', '%20', $url1);
$url = "https://test.cedmod.nl/" . $url1;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_USERAGENT, "CedMod Community management webpanel" . $domain);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, GetAPIKey().":".GetAPIKey());
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Alias: '.GetAlias(),
    'Port: 7777',
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$result = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
header("Content-Type: ".$info["content_type"]);
print_r($result);
http_response_code($info['http_code']);