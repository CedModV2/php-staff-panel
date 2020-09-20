<!DOCTYPE html>

<html>

<head>
    <?php
    $title = "Register";
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/title.php");
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/metadata.php");
    include realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/check_records.php");
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/connect.php");
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/utils.php");

    // Define variables and initialize with empty values
    $username = $password = $confirm_password = "";
    $username_err = $password_err = $confirm_password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter a username.";
            failmodal("Please enter a username.", 30, "true", "true", 500);
        } else {
            // Prepare a select statement
            $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqllogin.ini"));
            $query = $db -> prepare("SELECT id FROM frikandelbroodjeusers WHERE username = ?");
            $username = trim($_POST["username"]);

            $query -> execute([$username]);

            if ($query -> rowCount() == 1) {
                $username_err = "<font color='red'>This username is already taken.</color>";
                failmodal("<font color='red'>This username is already taken.</color>", 30, "true", "true", 500);
            }
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "<font color='red'>Please enter a password.</color>";
            failmodal("<font color='red'>Please enter a password.</color>", 30, "true", "true", 500);
        } elseif (strlen(trim($_POST["password"])) < 6) {
            $password_err = "<font color='red'>Password must have atleast 6 characters.</color>";
            failmodal("<font color='red'>Password must have atleast 6 characters.</color>", 30, "true", "true", 500);
        } else {
            $password = trim($_POST["password"]);
        }

        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "<font color='red'>Please confirm password.</color>";
            failmodal("<font color='red'>Please confirm password.</color>", 30, "true", "true", 500);
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "<font color='red'>Password did not match.</color>";
                failmodal("<font color='red'>Password did not match.</color>", 30, "true", "true", 500);
            }
        }

        // Check input errors before inserting in database
        if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

            // Prepare an insert statement
            $query = $db -> prepare("INSERT INTO frikandelbroodjeusers (username, password) VALUES (?, ?)");

            // Bind variables to the prepared statement as parameters
            $query -> execute([$username, password_hash($password, PASSWORD_DEFAULT)]);

            header("location: ./login.php");
        }
    }
    ?>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <h2>Register user</h2><br>
        <hr>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <div class="form-group">
                <label for="username">Username </label>
                <input class="form-control" type="text" name="username" required value=<?php echo $username; ?>><br>
            </div>
            <div class="form-group">
                <label for="password">Password </label>
                <input class="form-control" type="password" name="password" required><br>
            </div>
            <div class="form-group">
                <label for="repeatPassword">Repeat password </label>
                <input class="form-control" type="password" name="confirm_password" required><br>
            </div>
            <div class="btn-group" role="group">
                <button class="btn btn-primary" type="submit" name="submitRegister">Register</button>
                <button class="btn btn-secondary" type="button" onclick="location.href = '/php/auth/login.php'">I already have an account</button>
            </div>
        </form>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>

</html>