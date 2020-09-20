<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'kek' with no password) */
define('DB_SERVER', 'X');
define('DB_USERNAME', 'X');
define('DB_PASSWORD', 'X');
define('DB_NAME', 'login');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>