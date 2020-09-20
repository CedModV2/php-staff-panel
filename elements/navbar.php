<?php require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
$outsideclick = "false";
$confirmbutton = "false";
$loaddelay = 500;
echo '<script>
function RegisterPromptNav()
{
      swal.fire({
      icon: "question",
      customClass: "bg-light",
      showConfirmButton: '.$confirmbutton.',
      onOpen: () => {
      const content = \'<h2>Register</h2><br><hr><form method="post" action="/php/auth/register.php"><div class="form-group"><label for="username">Username </label><input class="form-control" type="text" name="username" required><br></div><div class="form-group"><label for="password">Password </label><input class="form-control" type="password" name="password" required><br></div><div class="form-group"><label for="repeatPassword">Repeat password </label><input class="form-control" type="password" name="confirm_password" required><br></div><div class="btn-group" role="group"><button class="btn btn-primary" type="submit" name="submitRegister">Register</button><button class="btn btn-secondary" type="button" onclick="LoginPromptNav()">I already have an account</button></div></form>\'
      if (content) {
          Swal.showLoading()
          setTimeout(() => {           
          Swal.hideLoading()
          Swal.getContent().innerHTML = content }, '.$loaddelay.');
      }
  },
      html: "",
    });
}
function LoginPromptNav()
{
      swal.fire({
      icon: "question",
      customClass: "bg-light",
      showConfirmButton: '.$confirmbutton.',
      onOpen: () => {
      const content = \'<h2>Login</h2><br><hr><form method="post" action="/php/auth/login.php"><div class="form-group"><label for="username">Username </label><input class="form-control" type="text" name="username" required><br></div><div class="form-group"><label for="password">Password </label><input class="form-control" type="password" name="password" required><br></div><div class="btn-group" role="group"><button class="btn btn-primary" type="submit" name="submitLogin">Login</button><button class="btn btn-secondary" type="button" onclick="RegisterPromptNav()">I do not have an account yet</button></div></form>\'
      if (content) {
          Swal.showLoading()
          setTimeout(() => {           
          Swal.hideLoading()
          Swal.getContent().innerHTML = content }, '.$loaddelay.');
      }
  },
      html: "",
    });
}
</script>';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="/index.php">
        <img src="/static/Frikandelbroodje.png" width="32" height="32" class="mb-1 text-primary">
        <?php echo $name ?>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav w-100">
            <li class="nav-item">
                <a class="nav-link" href="/index.php">
                    <img src="/static/icons/house-fill.svg" alt="" width="32" height="32" title="Home">
                </a>
            </li>
            <?php
            // Check if the current user is logged in.
            if (isset($_SESSION['loggedinf']) && $_SESSION['loggedinf']) { ?>
                <li class="nav-item <?php if ($title == "Dashboard") {
                    echo "active";
                } ?>">
                    <a class="nav-link my-1" href="/php/dashboard.php">Dashboard</a>
                </li>
            <?php } ?>
            <?php
            if (empty($_SESSION["permlevelfrikandelbroodje"])) {
                $permlevel = 0;
            } else {
                $permlevel = $_SESSION["permlevelfrikandelbroodje"];
            }

            if ($permlevel >= 1 && $_SESSION["loggedinf"]) {
                ?>
                <li class="nav-item my-1 dropdown">
                    <a class="nav-link dropdown-toggle
        href=" #" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;">
                    Staff Tools
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/php/ban_management.php">Ban Management</a>
                        <?php if ($permlevel >= 2) { ?>
                            <a class="dropdown-item" href="/php/commands.php">Server Commands</a>
                        <?php } ?>
                        <?php if ($permlevel >= 4) { ?>
                            <a class="dropdown-item" href="/php/appeal_overview.php">Appeal overview</a>
                        <?php }
                        if (empty($_SESSION["perms"])) {
                        $perms = "None";
                        } else {
                        $perms = $_SESSION["perms"];
                        }

                        ?>
                            <?php if (in_array("scpsl", $perms)) { ?>
                                <a class="dropdown-item" href="/php/scpslapplication_overview.php">SCP:SL Applications</a>
                            <?php } ?>
                        <?php if (in_array("dev", $perms)) { ?>
                            <a class="dropdown-item" href="/php/devapplication_overview.php">Dev Applications</a>
                        <?php } ?>
                        <?php if (in_array("discord", $perms)) { ?>
                            <a class="dropdown-item" href="/php/discordapplication_overview.php">Discord Applications</a>
                        <?php } ?>
                        <?php if (in_array("gmanager", $perms)) { ?>
                            <a class="dropdown-item" href="/php/gmanagerapplication_overview.php">Manager Applications</a>
                        <?php } ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/php/audit_log.php">Audit log</a>
                    </div>
                </li>
            <?php } ?>
            <li class="nav-item my-1 dropdown">
                <a class="nav-link dropdown-toggle
        href=" #" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;">
                Info
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="/php/info/rules.php">SCP:SL Rules</a>
                </div>
            </li>
            <?php
            // Check if the current user is logged in.
            if (isset($_SESSION['loggedinf']) && $_SESSION['loggedinf']) { ?>
                <li class="nav-item my-1 dropdown ml-auto mr-5">
                    <a class="nav-link dropdown-toggle
        <?php
                    if ($title == "Settings") {
                        echo "active";
                    }
                    ?> href=" #" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo $_SESSION['username'] ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="/php/auth/settings.php">Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/php/auth/logout.php">Logout</a>
                    </div>
                </li>
            <?php } else { ?>
                <li class="nav-item ml-auto <?php if ($title == "Login") {
                    echo "active";
                } ?>">
                    <a class="nav-link my-1" onclick="LoginPromptNav()">Login</a>
                </li>
                <li class="nav-item <?php if ($title == "Register") {
                    echo "active";
                } ?>">
                    <a class="nav-link my-1" onclick="RegisterPromptNav()">Register</a>
                </li>
            <?php } ?> <div class="nav-link">

                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="darkSwitch">
                    <label class="custom-control-label" for="darkSwitch">Dark Mode</label>
                </div>

                <script src="/static/darkmode.js"></script>

            </div>
        </ul>
    </div>
</nav>