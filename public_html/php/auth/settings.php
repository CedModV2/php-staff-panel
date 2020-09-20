<!DOCTYPE html>

<html>

<head>
  <?php
  session_start();
  require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
  Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
  if (!isset($_SESSION['loggedinf'])) {
      $url = "";
      $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
      $url.= $_SERVER['HTTP_HOST'];
      $host = $_SERVER['HTTP_HOST'];
      $url.= $_SERVER['REQUEST_URI'];
      echo "<script>localStorage.setItem('location', '".$protocol."$url'); 
        //window.location.replace('".$protocol."$host/php/auth/login.php');</script>";
      LoginPrompt();
      include '../static/403.php';
      exit();
  }
  $title = "Dashboard";

  include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/title.php");
  include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/metadata.php");
  require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/utils.php");
  require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config.php");
  $new_password = "";
  $new_password_err = "";
  $confirm_password_err = "";
  $param_term = "";
  /* Attempt MySQL server connection. Assuming you are running MySQL
        server with default setting (user 'kek' with no password) */
  $link = mysqli_connect("localhost", "frikanhub", "HUHDGEguFGYEDFGEYT", "login");

  // Check connection
  if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
  }
  if (isset($_POST['submitPasswdChange'])) {
    $new_password = $_POST["newpass"];
    // Prepare a select statement
    $sql = "SELECT id FROM frikandelbroodjeusers WHERE username LIKE ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_term);

      // Set parameters
      if ($_SESSION['permlevelfrikandelbroodje'] >= 4) {
        $param_term = $_POST["uname"];
      } else {
        $param_term = $_SESSION['username'];
      }

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        // Check number of rows in the result set
        if (mysqli_num_rows($result) == 1) {
          // Fetch result rows as an associative array
          while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $sqlid = $row["id"];
          }
          $sql = "UPDATE frikandelbroodjeusers SET password = ? WHERE id = ?";

          if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $sqlid;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
              if (!$_SESSION['permlevelfrikandelbroodje'] >= 4) {
                header('Location: ./logout.php');
              }
            } else {
              echo "Oops! Something went wrong. Please try again later.";
            }
          }
        } else {
          if (mysqli_num_rows($result) == 0) {
            echo "The Specified user could not be found";
          } else {
            if (mysqli_num_rows($result) > 1) {
              echo "Multiple search results please enter the username in more detail";
            }
          }
        }
      } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
      }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    $current_time = time();
    createAuditLog($current_time, 'edit_pass', $_SESSION['username'], "Changed Password for " . $_POST["uname"]);
  }
  if (isset($_POST['submitAccDelChange'])) {
    $id = $_POST["uname"];
    $sql = "SELECT id FROM frikandelbroodjeusers WHERE username LIKE ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_term);

      // Set parameters
      if ($_SESSION['permlevelfrikandelbroodje'] >= 4) {
        $param_term = $_POST["uname"];
      } else {
        $param_term = $_SESSION['username'];
      }

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        // Check number of rows in the result set
        if (mysqli_num_rows($result) == 1) {
          // Fetch result rows as an associative array
          while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $sqlid = $row["id"];
          }
          $sql = "DELETE FROM frikandelbroodjeusers WHERE id = ?";
          if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $sqlid);
            if (mysqli_stmt_execute($stmt)) {
              if (!$_SESSION['permlevelfrikandelbroodje'] >= 4) {
                header('Location: ./logout.php');
              }
            } else {
              echo "Oops! Something went wrong. Please try again later.";
            }
          }
        } else {
          if (mysqli_num_rows($result) == 0) {
            echo "The Specified user could not be found";
          } else {
            if (mysqli_num_rows($result) > 1) {
              echo "Multiple search results please enter the username in more detail";
            }
          }
        }
      } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
      }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    mysqli_close($link);
  }
  if (isset($_POST['submitAccpermChange'])) {
    $param_permlevel = $_POST["permlevel"];
    // Prepare a select statement
    $sql = "SELECT id FROM frikandelbroodjeusers WHERE username LIKE ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_term);

      // Set parameters
      $param_term = $_POST["uname"];

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        // Check number of rows in the result set
        if (mysqli_num_rows($result) == 1) {
          // Fetch result rows as an associative array
          while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $sqlid = $row["id"];
          }
          $sql = "UPDATE frikandelbroodjeusers SET permissionlevel = ? WHERE id = ?";
          if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $param_permlevel, $sqlid);
            if (mysqli_stmt_execute($stmt)) {
              echo "Success";
            } else {
              echo "Oops! Something went wrong. Please try again later.";
            }
          }
        } else {
          if (mysqli_num_rows($result) == 0) {
            echo "The Specified user could not be found";
          } else {
            if (mysqli_num_rows($result) > 1) {
              echo "Multiple search results please enter the username in more detail";
            }
          }
        }
      } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
      }
    }
  }
  if (isset($_POST['submitdisableChange'])) {
    echo $param_permlevel;
    $param_permlevel = $_POST["permlevel"];
    // Prepare a select statement
    $sql = "SELECT id FROM frikandelbroodjeusers WHERE username LIKE ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_term);

      // Set parameters
      $param_term = $_POST["uname"];
      echo $param_term;
      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        // Check number of rows in the result set
        if (mysqli_num_rows($result) == 1) {
          // Fetch result rows as an associative array
          while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $sqlid = $row["id"];
          }
          $sql = "UPDATE frikandelbroodjeusers SET disabledadmin = ? WHERE id = ?";
          if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $param_permlevel, $sqlid);
            if (mysqli_stmt_execute($stmt)) {
              echo "Success";
            } else {
              echo "Oops! Something went wrong. Please try again later.";
            }
          }
        } else {
          if (mysqli_num_rows($result) == 0) {
            echo "The Specified user could not be found";
          } else {
            if (mysqli_num_rows($result) > 1) {
              echo "Multiple search results please enter the username in more detail";
            }
          }
        }
      } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
      }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    date_default_timezone_set('Europe/Amsterdam'); // CET
    $info = getdate();
    $date = $info['mday'];
    $month = $info['mon'];
    $year = $info['year'];
    $hour = $info['hours'];
    $min = $info['minutes'];
    $sec = $info['seconds'];
    $current_date = "$year-$month-$date $hour:$min:$sec";
    $aname = $_SESSION["username"];
    $uname = $_POST["uname"];
    $newlevel = $_POST["permlevel"];
    $current_time = time();
    createAuditLog($current_time, 'edit_disabled', $aname, "Edited disabled status to: " . $newlevel . " for " . $uname);
  }
  ?>
</head>

<body>
  <?php
  include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php");
  ?>
  <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
    <ul class="nav flex-column">
      <li class="nav-item">
        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#SettingPasswdModal">Change password</button>
        <div class="modal fade" id="SettingPasswdModal" tabindex="-1" role="dialog" aria-labelledby="SettingPasswdModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content bg-light">
              <div class="modal-header">
                <h5 class="modal-title" id="SettingPasswdModalLabel'. $steamid .''. $steamid .'">Unban </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                  <div class="form-group">
                    <label for="steamid" class="col-form-label">Edditing password for</label>
                    <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 4) {
                      echo ' <input type="text" class="form-control steamid" name="uname" id="oldReason">';
                    } else {
                      echo ' <input type="text" class="form-control steamid" name="uname" id="oldReason" value="' . $_SESSION['username'] . '" readonly>';
                    }  ?>

                  </div>
                  <div class="form-group">
                    <label for="new_password" class="col-form-label">Enter Password</label>
                    <input type="password" class="form-control" name="newpass" required id="unbanReason" maxlength="140">
                  </div>
              </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitPasswdChange" value="Submit">
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      </li>
      <li class="nav-item">
        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#SettingDeleteAccModal">Delete Account</button>
        <div class="modal fade" id="SettingDeleteAccModal" tabindex="-1" role="dialog" aria-labelledby="SettingDeleteAccLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content bg-light">
              <div class="modal-header">
                <h5 class="modal-title" id="SettingDeleteAccLabel'. $steamid .''. $steamid .'">Delete Account (This can not be undone.)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                  <div class="form-group">
                    <label for="new_password" class="col-form-label">Delete account for</label>
                    <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 4) {
                      echo ' <input type="text" class="form-control steamid" name="uname" id="oldReason">';
                    } else {
                      echo ' <input type="text" class="form-control steamid" name="uname" id="oldReason" value="' . $_SESSION['username'] . '" readonly>';
                    }  ?>
                  </div>
              </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitAccDelChange" value="Submit">
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      </li>
      <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 4) { ?>
        <li class="nav-item">
          <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#SettingpermModal">Change Permissionlevel</button>
          <div class="modal fade" id="SettingpermModal" tabindex="-1" role="dialog" aria-labelledby="SettingpermModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content bg-light">
                <div class="modal-header">
                  <h5 class="modal-title" id="SettingpermModalLabel'. $steamid .''. $steamid .'">Unban </h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                    <div class="form-group">
                      <label for="steamid" class="col-form-label">Edditing permissionlevel for</label>
                      <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 4) {
                        echo ' <input type="text" class="form-control steamid" name="uname" id="uname">';
                      } else {
                        echo ' <input type="text" class="form-control steamid" name="uname" id="uname" value="' . $_SESSION['username'] . '" readonly>';
                      }  ?>
                      <?php echo $new_password_err; ?>
                    </div>
                    <div class="form-group">
                      <label for="permlevel" class="col-form-label">Permission Level</label>
                      <input type="number" class="form-control" name="permlevel" required id="permlevel" maxlength="140">
                      <?php echo $confirm_password_err; ?>
                    </div>
                </div>
                <div class="modal-footer">
                  <input type="submit" class="btn btn-success" name="submitAccpermChange" value="Submit">
                  </form>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div><br>
          <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#SettingdisabledModal">Change Disabled Status</button>
          <div class="modal fade" id="SettingdisabledModal" tabindex="-1" role="dialog" aria-labelledby="SettingdisabledModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content bg-light">
                <div class="modal-header">
                  <h5 class="modal-title" id="SettingdisabledModalLabel'. $steamid .''. $steamid .'">Unban </h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                    <div class="form-group">
                      <label for="steamid" class="col-form-label">Editing disabled status for</label>
                      <?php if ($_SESSION['permlevelfrikandelbroodje'] >= 4) {
                        echo ' <input type="text" class="form-control steamid" name="uname" id="uname">';
                      } else {
                        echo ' <input type="text" class="form-control steamid" name="uname" id="uname" value="' . $_SESSION['username'] . '" readonly>';
                      }  ?>
                      <?php echo $new_password_err; ?>
                    </div>
                    <div class="form-group">
                      <label for="permlevel" class="col-form-label">Disabled status</label>
                      <input type="number" class="form-control" name="permlevel" required id="permlevel" max="1">
                      <?php echo $confirm_password_err; ?>
                    </div>
                </div>
                <div class="modal-footer">
                  <input type="submit" class="btn btn-success" name="submitdisableChange" value="Submit">
                  </form>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
          <br><a class="btn btn-secondary" href="/php/all_accounts.php">All Accounts</a>
        </li><?php } ?>
    </ul>
  </div>

  <body oncontextmenu="return false;">
    <script>
      document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
      });
      document.addEventListener('keydown', function() {
        if (event.keyCode == 123) {
          alert("This function has been disabled");
          return false;
        } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {
          alert("This function has been disabled");
          return false;
        } else if (event.ctrlKey && event.keyCode == 85) {
          alert("This function has been disabled");
          return false;
        }
      }, false);

      if (document.addEventListener) {
        document.addEventListener('contextmenu', function(e) {
          alert("This function has been disabled");
          e.preventDefault();
        }, false);
      } else {
        document.attachEvent('oncontextmenu', function() {
          alert("This function has been disabled");
          window.event.returnValue = false;
        });
      }
    </script>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
  </body>

</html>