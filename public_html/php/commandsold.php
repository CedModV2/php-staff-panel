<!DOCTYPE html>

<html>

<head>
    <?php
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    session_start();
    $title = "Commands";

    include "../../elements/title.php";
    include "../../elements/metadata.php";
    include "../../scripts/utils.php";

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
    if (!$_SESSION["permlevelfrikandelbroodje"] >= 2) {
        include '../static/403.php';
        exit();
    }

    ?>
</head>

<body>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/navbar.php"); ?>
    <div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
        <h2>Commands</h2><br>
        <hr>
        <div class="row">
            <div class="col-md">
                <div id="UserColum">
                </div>
                <hr>
            </div>
            <div class="col-md"><select class="form-control" name="server" id="server">
                    <?php foreach (GetServers() as $key => $value)
                        echo '<option value="'.$value.'">'.$key.'</option>>'?>
                </select>
                <label for="username">Command </label><br>
                <input class="form-control" type="text" name="command" id="command" required>
                <button class="btn btn-secondary" type="button" onclick="SendCommand()">Send</button>
                <div id="ResultColum">
                </div>
            </div>
        </div>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>
<script>
    function PlayerList() {
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.info(this.response)
                var obj = JSON.parse(this.response);
                obj = obj.message.toString().replace("\n", "<br>");
                var arrStr = obj.split(/[()]/);
                for (index = 0; index < arrStr.length; ++index) {
                    obj = obj.replace("(" + arrStr[index] + ")", '<element onclick=\'SendCommandArgs("REQUEST_DATA SHORT-PLAYER '+arrStr[index]+'")\'><u><b>('+arrStr[index]+')</b></u></element>');
                }
                document.getElementById("UserColum").innerHTML = obj;
                console.info(obj);
            }
            else if (this.status != 200 && this.readyState == 4)
            {
                document.getElementById("UserColum").innerHTML = this.response
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
            'user': localStorage.getItem('username'),
            'action': 'custom',
            'command': 'PLAYERLISTCOLORED SILENT'
        };
        var sel = document.getElementById('server');
        var first = "scripts/getserver.php";
        xhttp.open("POST", first.concat("?server=", sel.value));
        xhttp.send(JSON.stringify(filterOptions));
    }
    function SendCommand() {
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.info(this.response)
                var obj = JSON.parse(this.response);
                obj = obj.message.toString().replace("\n", "<br>");
                document.getElementById("ResultColum").innerHTML = obj;
                console.info(obj);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    customClass: "bg-light",
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'success',
                    title: 'Command sent successfully'
                })
            }
            else if (this.status != 200 && this.readyState == 4)
            {
                document.getElementById("ResultColum").innerHTML = this.response
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
            'user': localStorage.getItem('username'),
            'action': 'custom',
            'command': document.getElementById('command').value
        };
        var sel = document.getElementById('server');
        var first = "scripts/getserver.php";
        xhttp.open("POST", first.concat("?server=", sel.value));
        xhttp.send(JSON.stringify(filterOptions));
    }
    function SendCommandArgs(args) {
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.info(this.response)
                var obj = JSON.parse(this.response);
                obj = obj.message.toString().replace("\n", "<br>");
                document.getElementById("ResultColum").innerHTML = obj;
                console.info(obj);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    customClass: "bg-light",
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'success',
                    title: 'Command sent successfully'
                })
            }
            else if (this.status != 200 && this.readyState == 4)
            {
                document.getElementById("ResultColum").innerHTML = this.response
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
            'user': localStorage.getItem('username'),
            'action': 'custom',
            'command': args
        };
        var sel = document.getElementById('server');
        var first = "scripts/getserver.php";
        xhttp.open("POST", first.concat("?server=", sel.value));
        xhttp.send(JSON.stringify(filterOptions));
    }
    window.onload = PlayerList();
    window.setInterval(function(){
        PlayerList();
    }, 5000);
</script>
</html>
