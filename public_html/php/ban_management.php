<?php require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php"); ?>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
session_start();

$title = "Ban Management";
$steamiderr = "";
require_once "../../scripts/connect.php";
include "../../scripts/utils.php";
include "../../scripts/actions.php";
    $returnnohtml = false;
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
else if (!$_SESSION["permlevelfrikandelbroodje"] >= 1)
{
    include '../static/403.php';
    exit();
}
    if (isset($_POST['submitBan'])) {
        $returnnohtml = true;
        if (getBan($_POST['steamid'] . $_POST['usertype']) == null)
        {
            $bd = $_POST['banMinutes'] + $_POST['banHours'] * 60 + $_POST['banDays'] * 24 * 60;
            $reason2 = "";
            if (!empty($_POST['reason'])) {
                foreach ($_POST['reason'] as $reason4)
                {
                    if ($reason4 != '..') {
                        $reason2 = $reason2 . " " . $reason4;
                        $bd = $bd + GetBanReasons()[$reason4];
                    }
                }
            }
            if(empty($_POST['reasoncustom'])) {
                $customreason = ""; }
            else { $customreason = $_POST['reasoncustom'];}
            $reason1 = $reason2." ".$customreason;
            if (empty($bd))
            {
                print_r(json_encode(array("success" => "false", "Ban duration can not be 0!")));
                exit();
            }
            if ($reason1 == "" || $reason1 == " ")
            {
                print_r(json_encode(array("success" => "false", "Ban reason can not be empty!")));
                exit();
            }
            if (!empty($bd) && $reason1 != "" && $reason1 != " ") {
                banPlayer($_POST['steamid'] . $_POST['usertype'], $bd, $reason1, $_SESSION['username']);
                $result = "";
                if ($_POST['server'] != "None")
                {
                    $url = $_POST['server'];
                    $kickreason = "[CEDMOD.Bansystem.RemoteBanHandler]\nYou have been banned remotely. \n for the reason: " . $reason1 . " \n Ban duration: " . minToHourMinute($bd) . " \n You were banned on(CET Time): " . GetTime() . " \n";
                    //create a new cURL resource
                    $ch = curl_init($url);

                    //setup request to send json via POST
                    $data = array(
                        'key' => $key,
                        'user' => $_SESSION['username'],
                        'action' => 'kicksteamid',
                        'steamid' => $_POST['steamid'] . $_POST['usertype'],
                        'reason' => $kickreason
                    );
                    $payload = json_encode($data);

                    //attach encoded JSON string to the POST fields
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                    //set the content type to application/json
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

                    //return response instead of outputting
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    //execute the POST request
                    $result = "Response from server: " . curl_exec($ch);
                    $result = str_replace('"', "'", $result);

                    //close cURL resource
                    curl_close($ch);
                }
                print_r(json_encode(array("success" => "true", "message" => $_POST['steamid'] . $_POST['usertype'] . " Has been banned for<br>" . minToHourMinute($bd) . "<br>for the reason: " . $reason1 . " " . $result)));
                exit();
            }
        }
        else
        {
            print_r(json_encode(array("success" => "false", "message" => "Specified steamid (".$_POST['steamid'] . $_POST['usertype'] .") is already banned!")));
            exit();
        }
        exit();
    }
    include "../../elements/title.php";
    include "../../elements/metadata.php";
    if (isset($_POST['submitUnban']))
    {
        $steamid = $_POST['steamid64'];
        $reason = $_POST['reason'];
        unbanPlayer($steamid, $reason, $_SESSION['username']);
        successmodal($steamid." Has been unbanned for the folowing reason: ".$reason, 5, "false", "false");
    }

    if (isset($_POST['submitAddTime']))
    {
        $addedTime = ($_POST['addDays'] * 24 * 60) + ($_POST['addHours'] * 60) + $_POST['addMinutes'];

        extendBan($_POST['steamid64'], $addedTime, $_POST['modifyReason'], $_SESSION['username']);
        successmodal($_POST['steamid64']."'s ban has been modified to <br>".$_POST['addDays']." Days,<br>".$_POST['addHours']." Hours,<br>".$_POST['addMinutes']." Minutes<br> for the folowing reason: ".$_POST['modifyReason'], 5, "false", "false");
    }

    if (isset($_POST['submitNewReason']))
    {
        changeBanReason($_POST['steamid64'], $_POST['newReason'], $_POST['modifyReason'], $_SESSION['username']);
        successmodal($_POST['steamid64']."'s ban reason has been changed to: ".$_POST['newReason']."<br> For the folowing reason: ".$_POST['modifyReason'], 5, "false", "false");
    }
    if (isset($_POST['submitDelLog']))
    {
        $steamid = $_POST['steamid64'];
        $reason = $_POST['reason'];
        delBanLog($steamid, $reason, $_SESSION['username']);
        successmodal('Ban log: '.$steamid." Has been removed for the folowing reason: ".$reason, 5, "false", "false");
    }
if (!$returnnohtml) {  ?>
    <!DOCTYPE html>

<html>

<head>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <ul class="nav nav-tabs border-0">
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss active border border-secondary border-bottom-0" data-toggle="tab" href="#issue">Issue Ban</a>
            </li>
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss border border-secondary border-bottom-0" onclick="LoadBanTab();" data-toggle="tab" href="#overview">Ban Overview</a>
            </li>
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss border border-secondary border-bottom-0" onclick="LoadLogTab();" data-toggle="tab" href="#logs">Ban Logs</a>
            </li>
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss border border-secondary border-bottom-0" onclick="loadBanStatistics();" data-toggle="tab" href="#overviewstats">Ban Statistics</a>
            </li>
            <li class="nav-item nav-tabcss">
                <a class="nav-link nav-tabcss border border-secondary border-bottom-0" onclick="loadLogStatistics();" data-toggle="tab" href="#logstats">Ban Log Statistics</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active border-top border-secondary" id="issue">
                <h2>Ban interface</h2>
                <hr>
                <form method="post" id="banform">
                <div class="row">
                    <div class="col-md">
                            <div class="form-group">
                                <label for="steamid">steamID64 (<a href='https://steamid.io/' target="_blank">https://steamid.io/</a>)</label>
                                <div class="input-group mb-3">
                                    <input id="steamid" class="form-control type="text" name="steamid" required>
                                    <div class="input-group-append">
                                        <button type="button" class="form-control" onclick="filterLogsIssue()">Check prior offences</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                Ban duration<br>
                                <div class="row">
                                    <div class="col">
                                        <label for="banDays">Days </label>
                                        <input class="form-control col w-70" type="number" name="banDays" value="0" min="0" required>
                                    </div>
                                    <div class="col">
                                        <label for="banHours">Hours </label>
                                        <input class="form-control col w-70" type="number" name="banHours" value="0" min="0" required>
                                    </div>
                                    <div class="col">
                                        <label for="banMinutes">Minutes </label>
                                        <input class="form-control col w-70" type="number" name="banMinutes" value="0" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <label for="reason">Reason <i>Select 1 or more</i> A minimum of 1 reason is required<br><i>Reason | Duration</i></label>
                            <select multiple class="form-control" style=" height:370px;" name="reason[]" id="reason">
                                <?php foreach (GetBanReasons() as $key => $value)
                                    echo '<option value="'.$key.'">'.$key.' | '.minToHourMinute($value).'</option>>'?>
                            </select>
                            <div class="form-group">
                                <label for="reasoncustom">Custom Reason</label>
                                <input class="form-control" type="text" name="reasoncustom" placeholder="Max. 140 chars"><br>
                            </div>
                            <p><?php echo $steamiderr; ?></p>
                            <input class="btn btn-danger" type="submit" value="Ban user"><input hidden type="number" name="submitBan" value="1" required>
                    </div>
                    <div id="banLogColumnIssueMain" class="col-md">
                        <div id="banLogColumnIssueServerSelector">
                            <label for="usertype">Select the platform that the user is playing on</label>
                            <select class="form-control" name="usertype" id="usertype">
                                <option value="@steam">Steam</option>
                                <option value="@discord">Discord</option>
                            </select>
                            <label for="server">Select the server that the user is on</label>
                            <select class="form-control" name="server" id="server">
                                <option value="None">Player is not on server</option>
                                <?php foreach (GetServers() as $key => $value)
                                    echo '<option value="'.$value.'">'.$key.'</option>'?>
                            </select>
                        </div>
                        <div id="banLogColumnIssue" class="col-md"></div>
                    </div>
                </div>
            </div>
            </form>
            <div class="tab-pane fade border-top border-secondary" id="overview">
        <h2>Ban overview</h2>
        <hr>
        <div class="row">
            <div id="banColumn" class="col-md">
            </div>
            <div class="col-md">
                <div class="card bg-custom">
                    <div class="card-body">
                    <form id="filterForm">
                        <h4 class="card-title">Filter options</h4>
                        <div class="form-group">
                            <input class="form-control" id="steamIDFilter" type="text" placeholder="Search by steamID64">
                        </div>
                        <div class="form-group">
                            <input class="form-control" id="nameFilter" type="text" placeholder="Search by nickname">
                        </div>
                        <div class="form-group">
                            <input class="form-control" id="modFilter" type="text" placeholder="Search for bans by a certain moderator">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="filterBans()">Filter</button>
                        <button type="button" class="btn btn-primary" onclick="viewAll()">Load all bans</button>
                    </form>
                    </div>
                </div>
            </div>
        </div>
            </div>
            <div class="tab-pane fade border-top border-secondary" id="logs">
                <h2>Ban Logs</h2>
                <hr>
                <div class="row">
                    <div class="col-md" id="banLogColumn">
                    </div>
                    <div class="col-md">
                        <div class="card bg-custom">
                            <div class="card-body">
                                <h4 class="card-title">Filter options</h4>
                                <div class="form-group">
                                    <input class="form-control" id="steamIDFilterLogs" type="text" placeholder="Search for a steamID64">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" id="nameFilterLogs" type="text" placeholder="Search by nickname">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" id="modFilterLogs" type="text" placeholder="Search for bans by a certain moderator">
                                </div>
                                <button type="button" class="btn btn-primary" onclick="filterLogs()">Filter</button>
                                <button type="button" class="btn btn-primary" onclick="viewAllLogs()">Load all ban logs</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade border-top border-secondary" id="overviewstats">
                <canvas id="banstats"></canvas>
            </div>
            <div class="tab-pane fade border-top border-secondary" id="logstats">
                <canvas id="banlogstats"></canvas>
            </div>
        </div>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
    <script>
        const form = document.getElementById( "banform" );

        // ...and take over its submit event.
        form.addEventListener( "submit", function ( event ) {
            event.preventDefault();

            SubmitBan();
        });
        function SubmitBan() {
            const XHR = new XMLHttpRequest();
            var Toast = Swal.mixin({
                position: 'center',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 5,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            // Bind the FormData object and the form element
            const FD = new FormData( form );

            // Set up our request
            XHR.open( "POST", "<?php
                $url = "";
                $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                $url.= $_SERVER['HTTP_HOST'];
                $host = $_SERVER['HTTP_HOST'];
                $url.= $_SERVER['REQUEST_URI'];
                echo "$protocol$url" ?>" );

            // The data sent is what the user provided in the form
            XHR.send( FD );
            XHR.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    var res = JSON.parse(this.response);
                    Swal.disableLoading();
                    Swal.resumeTimer();
                    Toast = Swal.mixin({
                        position: 'center',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        timer: 5000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                    })
                    if (res.success == "true") {
                        Toast.fire({
                            icon: 'success',
                            html: res.message
                        })
                    }
                    else
                    {
                        Toast.fire({
                            icon: 'error',
                            html: res.message
                        })
                    }
                } else if (this.status != 200 && this.readyState == 4) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            }
        }
        var loadedlogsstats = false;
        function loadLogStatistics() {
            if (!loadedlogsstats) {
                var xhttp = new XMLHttpRequest();
                const Toast = Swal.mixin({
                    position: 'center',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    timer: 5,
                    customClass: "bg-light",
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        Swal.stopTimer();
                        Swal.enableLoading();
                        Swal.disableButtons();
                    }
                })

                Toast.fire({
                    icon: 'info',
                    title: '<p>Please wait.</p>'
                })
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        loadedlogsstats = true;
                        Swal.disableLoading();
                        Swal.resumeTimer();
                        var as = JSON.parse(this.response);
                        var ctx = document.getElementById('banlogstats').getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(as),
                                datasets: [{
                                    label: '# of bans',
                                    data: Object.values(as),
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.2)',
                                        'rgba(54, 162, 235, 0.2)',
                                        'rgba(255, 206, 86, 0.2)',
                                        'rgba(75, 192, 192, 0.2)',
                                        'rgba(153, 102, 255, 0.2)',
                                        'rgba(255, 159, 64, 0.2)',
                                        'rgba(245, 19, 132, 0.2)',
                                        'rgba(24, 152, 265, 0.2)',
                                        'rgba(65, 216, 90, 0.2)',
                                        'rgba(15, 122, 150, 0.2)',
                                        'rgba(25, 162, 215, 0.2)',
                                        'rgba(245, 659, 64, 0.2)',
                                        'rgba(155, 99, 132, 0.2)',
                                        'rgba(54, 16, 235, 0.2)',
                                        'rgba(255, 206, 6, 0.2)',
                                        'rgba(715, 19, 192, 0.2)',
                                        'rgba(153, 102, 755, 0.2)',
                                        'rgba(5, 129, 64, 0.2)',
                                        'rgba(225, 19, 122, 0.2)',
                                        'rgba(264, 12, 25, 0.2)',
                                        'rgba(61, 116, 50, 0.2)',
                                        'rgba(5, 22, 50, 0.2)',
                                        'rgba(5, 62, 15, 0.2)',
                                        'rgba(45, 59, 64, 0.2)'

                                    ],
                                    borderWidth: 0.6
                                }]
                            },
                        });
                    } else if (this.status != 200 && this.readyState == 4) {
                        document.getElementById("banlogstats").innerHTML = this.response
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 10000,
                            customClass: "bg-light",
                            timerProgressBar: true,
                            onOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        })

                        Toast.fire({
                            icon: 'error',
                            title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                        })
                    }
                };
                var filterOptions = {
                    'Stats': 'True'
                };

                xhttp.open("POST", "/php/scripts/filter_ban_logs.php");
                xhttp.send(JSON.stringify(filterOptions));
            }
        }
        var loadedbanstats = false;
        function loadBanStatistics() {
            if (!loadedbanstats) {
                var xhttp = new XMLHttpRequest();
                const Toast = Swal.mixin({
                    position: 'center',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    timer: 5,
                    customClass: "bg-light",
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        Swal.stopTimer();
                        Swal.enableLoading();
                        Swal.disableButtons();
                    }
                })

                Toast.fire({
                    icon: 'info',
                    title: '<p>Please wait.</p>'
                })
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        loadedbanstats = true;
                        Swal.disableLoading();
                        Swal.resumeTimer();
                        var as = JSON.parse(this.response);
                        var ctx = document.getElementById('banstats').getContext('2d');
                        var myChartLogs = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(as),
                                datasets: [{
                                    label: '# of bans',
                                    data: Object.values(as),
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.2)',
                                        'rgba(54, 162, 235, 0.2)',
                                        'rgba(255, 206, 86, 0.2)',
                                        'rgba(75, 192, 192, 0.2)',
                                        'rgba(153, 102, 255, 0.2)',
                                        'rgba(255, 159, 64, 0.2)',
                                        'rgba(245, 19, 132, 0.2)',
                                        'rgba(24, 152, 265, 0.2)',
                                        'rgba(65, 216, 90, 0.2)',
                                        'rgba(15, 122, 150, 0.2)',
                                        'rgba(25, 162, 215, 0.2)',
                                        'rgba(245, 659, 64, 0.2)',
                                        'rgba(155, 99, 132, 0.2)',
                                        'rgba(54, 16, 235, 0.2)',
                                        'rgba(255, 206, 6, 0.2)',
                                        'rgba(715, 19, 192, 0.2)',
                                        'rgba(153, 102, 755, 0.2)',
                                        'rgba(5, 129, 64, 0.2)',
                                        'rgba(225, 19, 122, 0.2)',
                                        'rgba(264, 12, 25, 0.2)',
                                        'rgba(61, 116, 50, 0.2)',
                                        'rgba(5, 22, 50, 0.2)',
                                        'rgba(5, 62, 15, 0.2)',
                                        'rgba(45, 59, 64, 0.2)'

                                    ],
                                    borderWidth: 0.6
                                }]
                            },
                        });
                    } else if (this.status != 200 && this.readyState == 4) {
                        document.getElementById("banstats").innerHTML = this.response
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 10000,
                            customClass: "bg-light",
                            timerProgressBar: true,
                            onOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        })

                        Toast.fire({
                            icon: 'error',
                            title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                        })
                    }
                };
                var filterOptions = {
                    'Stats': 'True'
                };

                xhttp.open("POST", "/php/scripts/filter_bans.php");
                xhttp.send(JSON.stringify(filterOptions));
            }
        }
        function filterBans() {
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                position: 'center',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 5,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banColumn").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();


                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banColumn").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };

            var filterOptions = {
                'steamID': document.getElementById("steamIDFilter").value,
                'nickname': document.getElementById("nameFilter").value,
                'modname': document.getElementById("modFilter").value
            };

            xhttp.open("POST", "/php/scripts/filter_bans.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
        function viewAll() {
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                position: 'center',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 5,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banColumn").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();
                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banColumn").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };

            var filterOptions = {
                'steamID': '',
                'nickname': '',
                'modname': '',
                'Fetch': 'True'
            };

            xhttp.open("POST", "/php/scripts/filter_bans.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
        function filterLogs() {
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                position: 'center',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 5,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banLogColumn").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();
                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banLogColumn").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };

            var filterOptions = {
                'steamID': document.getElementById("steamIDFilterLogs").value,
                'nickname': document.getElementById("nameFilterLogs").value,
                'modname': document.getElementById("modFilterLogs").value
            };

            xhttp.open("POST", "/php/scripts/filter_ban_logs.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
        function filterLogsIssue() {
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                position: 'center',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 5,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banLogColumnIssue").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();
                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banLogColumnIssue").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };
            var sel = document.getElementById('usertype');
            var filterOptions = {
                'steamID': document.getElementById("steamid").value + sel.value,
                'nickname': '',
                'modname': ''
            };

            xhttp.open("POST", "/php/scripts/filter_ban_logs.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
        function viewAllLogs() {
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                position: 'center',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 5,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banLogColumn").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();
                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banLogColumn").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };

            var filterOptions = {
                'steamID': '',
                'nickname': '',
                'modname': '',
                'Fetch': 'True'
            };

            xhttp.open("POST", "/php/scripts/filter_ban_logs.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
        var LoadedLogs = false;
        function LoadLogTab() {
            if (LoadedLogs)
                return;
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 100,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banLogColumn").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();
                    LoadedLogs = true;
                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banLogColumn").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };

            var filterOptions = {
                'steamID': '',
                'nickname': '',
                'Limit': '10',
                'Fetch': 'True'
            };

            xhttp.open("POST", "/php/scripts/filter_ban_logs.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
        var LoadedBans = false;
        function LoadBanTab() {
            if (LoadedBans)
                return;
            var xhttp = new XMLHttpRequest();
            const Toast = Swal.mixin({
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                timer: 100,
                customClass: "bg-light",
                timerProgressBar: true,
                onOpen: (toast) => {
                    Swal.stopTimer();
                    Swal.enableLoading();
                    Swal.disableButtons();
                }
            })

            Toast.fire({
                icon: 'info',
                title: '<p>Please wait.</p>'
            })
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("banColumn").innerHTML = this.response;
                    Swal.disableLoading();
                    Swal.resumeTimer();
                    LoadedBans = true;
                }
                else if (this.status != 200 && this.readyState == 4)
                {
                    document.getElementById("banColumn").innerHTML = this.response
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        customClass: "bg-light",
                        timerProgressBar: true,
                        onOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong, response code: ' + this.status + ' ' + this.statusText
                    })
                }
            };

            var filterOptions = {
                'steamID': '',
                'nickname': '',
                'Limit': '10',
                'Fetch': 'True'
            };

            xhttp.open("POST", "/php/scripts/filter_bans.php");
            xhttp.send(JSON.stringify(filterOptions));
        }
    </script>
</body>

</html>
<?php }?>
