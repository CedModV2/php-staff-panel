<!DOCTYPE html>
<html>

<head>
  <?php
  require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
  Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
  session_start();

  $title = "Home";

  include "../../elements/title.php";
  include "../../elements/metadata.php";
  ?>
</head>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes. In case if your CURL is slow and is loading too much (Can be IPv6 problem)
$username = $_SESSION["username"];
error_reporting(E_ERROR | E_PARSE);

define('OAUTH2_CLIENT_ID', '670733348280336405');
define('OAUTH2_CLIENT_SECRET', 'RhvW3XaPu-21T146qxrxqNmqVRk8Wn3C');
$redirurl = "https://frikandelbroodjeserver.nl/php/discordverify.php";
$authorizeURL = 'https://discordapp.com/api/oauth2/authorize';
$tokenURL = 'https://discordapp.com/api/oauth2/token';
$apiURLBase = 'https://discordapp.com/api/users/@me';
$apiURL2 = 'https://discordapp.com/api/users/@me/guilds';
$ip = $_SERVER['REMOTE_ADDR'];
session_start();

// Start the login process by sending the user to Discord's authorization page
if (get('action') == 'login') {
  $params = array(
    'client_id' => OAUTH2_CLIENT_ID,
    'redirect_uri' => $redirurl,
    'response_type' => 'code',
    'scope' => 'identify guilds'
  );

  // Redirect the user to Discord's authorization page
  header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params));
  die();
}


// When Discord redirects the user back here, there will be a "code" and "state" parameter in the query string
if (get('code')) {

  // Exchange the auth code for a token
  $token = apiRequest($tokenURL, array(
    "grant_type" => "authorization_code",
    'client_id' => OAUTH2_CLIENT_ID,
    'client_secret' => OAUTH2_CLIENT_SECRET,
    'redirect_uri' => $redirurl,
    'code' => get('code')
  ));
  $logout_token = $token->access_token;
  $_SESSION['access_token'] = $token->access_token;


  header('Location: ' . $_SERVER['PHP_SELF']);
}

if (session('access_token')) {
  $user = apiRequest($apiURLBase);
  $guilds = apiRequest($apiURL2);
  echo '<h3>Logged In</h3>';
  echo '<h4>Welcome, ' . $user->username . '</h4>';
  echo '<pre>';
  print_r($user);
  echo '</pre>';
  $ids = array();
  $ip = $_SERVER['REMOTE_ADDR'];
  $sucess = false;
  foreach ($guilds as $obj) {
    array_push($ids, '|' . $obj->id . '|');
  }
  if (in_array("|589191346246516757|", $ids)) {
    echo "You are in the FrikanDiscord server";
    $link = mysqli_connect("localhost", "frikanhub", "HUHDGEguFGYEDFGEYT", "login");
    if ($link === false) {
      die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    $sql = "UPDATE frikandelbroodjeusers SET discordid = $user->id WHERE username = '$username'";
    if ($link->query($sql) === TRUE) {
    } else {
      echo "Error: " . $sql . "<br>" . $link->error;
    }
    $sql = "UPDATE frikandelbroodjeusers SET disabled = 0 WHERE username = '$username'";
    if ($link->query($sql) === TRUE) {
    } else {
      echo "Error: " . $sql . "<br>" . $link->error;
    }
    $sucess = true;
  } else {
    echo "You are not in the FrikanDiscord server";
  }
  if ($sucess) {
    header('Location: https://frikandelbroodjeserver.nl/php/auth/login.php');
  }
} else {
  echo '<h3>Not logged in</h3>';
  echo '<p><a href="?action=login">Log In</a></p>';
}


if (get('action') == 'logout') {
  // This must to logout you, but it didn't worked(

  $params = array(
    'access_token' => $logout_token
  );

  // Redirect the user to Discord's revoke page
  header('Location: https://discordapp.com/api/oauth2/token/revoke' . '?' . http_build_query($params));
  die();
}

function apiRequest($url, $post = FALSE, $headers = array())
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $response = curl_exec($ch);


  if ($post)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

  $headers[] = 'Accept: application/json';

  if (session('access_token'))
    $headers[] = 'Authorization: Bearer ' . session('access_token');

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $response = curl_exec($ch);
  return json_decode($response);
}

function get($key, $default = NULL)
{
  return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function session($key, $default = NULL)
{
  return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

?>

<body>
  <?php include "../../elements/navbar.php"; ?>
  <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">

  </div>
  <?php include "../../elements/footer.php"; ?>
</body>

</html>
