<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
session_start();
if (empty($_SESSION["isstaff"]))
{
    http_response_code(401);
    die("unauthorized");
}
if ($_SESSION["isstaff"])
{
    $plaintext = "youcantrustme:".$_SESSION["steamid"]."@steam:".$_SESSION["username"];
$password = $key;
$method = 'aes-256-cbc';

// Must be exact 32 chars (256 bit)
$password = substr(hash('sha256', $password, true), 0, 32);

// IV must be exact 16 chars (128 bit)
$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
$encrypted = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
die ($encrypted);
}
