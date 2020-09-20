<!DOCTYPE html>

<html>
<link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">
<style>
    ul#console {
        list-style-type: none;
        font-family: 'Roboto Mono', monospace;
        font-size: 14px;
        line-height: 25px;
        padding-left: 5px;
    }
    ul#console li {
        border-bottom: solid 1px #80808038;
    }
    div.scroll {
        background-color: #fed9ff;
        width: 600px;
        height: 150px;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 20px;
    }
</style>
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
            <div class="col-9 vh-100" style="height: 77%;">
                <div class="form-control scroll" onmouseenter="MouseHovering = true" onmouseleave="MouseHovering=false" style="height: 70%; width: auto; border-bottom-style: none;" id="ResultColum">
                        <ul id="console"></ul>
                </div>
                <div class="form-control" style=" position:relative; height:70px; bottom: 7px;">
                    <div class="input-group" style="bottom: -10px;">
                        <input class="form-control" type="text" name="command" id="command" required>
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="sendcommand" type="button" onclick="SendCommand()">Send</button>
                            <button class="btn btn-secondary" id="sendreconnect" type="button" onclick="WebSocketStart()">Reconnect</button>
                            <select class="form-control" onchange="WebSocketStart()" name="server" id="server">
                                <?php foreach (GetServers() as $key => $value)
                                    echo '<option value="'.$value.'">'.$key.'</option>>'?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-control" style="height: 77%;" id="UserColum">
                    <p>No players</p>
                </div>
            </div>
        </div>
    </div>
    <?php include realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/footer.php"); ?>
</body>
<script>
    var disablepopups = false;
    var MouseHovering = false;
    var IsConnected = false;
    window.setInterval(function() {
        if (!MouseHovering) {
            var elem = document.getElementById('ResultColum');
            elem.scrollTop = elem.scrollHeight;
        }
    }, 100);
    var ws = null;
    function log(txt){
        var d = new Date();
        txt = "[" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "] " + txt
        var newLine = document.createElement("li");
        newLine.innerHTML = (typeof txt === 'string') ? txt : JSON.stringify(txt, null, 4);
        document.querySelector('#console').appendChild(newLine);
    }
    var requesteddata = false;
    function WebSocketStart() {
        document.getElementById('command').disabled = true
        document.getElementById('sendcommand').disabled = true
        document.getElementById('sendreconnect').disabled = false
        if ("WebSocket" in window) {
            // Let us open a web socket
            if (ws != null) ws.close();
            var ws1 = new WebSocket('wss://srv1.cedmod.nl:' + document.getElementById("server").value.split(':')[1]);
            ws1.onopen = function() {
                ws = ws1
                document.getElementById('command').disabled = false
                document.getElementById('sendcommand').disabled = false
                document.getElementById('sendreconnect').disabled = true
                // Web Socket is connected, send data using send()
                var xhttp = new XMLHttpRequest();
                log("<font color='#006400'>Connection established</font>")
                IsConnected = true;
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        ws1.send("CMAUTHENTICATE " + this.response)
                    }
                }
                xhttp.open("GET", "/php/scripts/getkey.php")
                xhttp.send()
            };
            ws1.onmessage = function (evt) {
                var received_msg = evt.data;
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        received_msg = this.response
                        received_msg = JSON.parse(received_msg)
                        received_msg = received_msg.jsonstring;
                        received_msg = received_msg.toString().replace("\n", "<br>");
                        if (received_msg.includes("playerlistcolored:PLAYER_LIST"))
                        {
                            received_msg = received_msg.replace("playerlistcolored:PLAYER_LIST#", "")
                            var arrStr = received_msg.split(/[()]/);
                            for (index = 0; index < arrStr.length; ++index) {
                                received_msg = received_msg.replace("(" + arrStr[index] + ")", '<element onclick=\'SendCommandArgs('+arrStr[index]+')\'><u><b>('+arrStr[index]+')</b></u></element>');
                            }
                            if (received_msg != "<br>")
                                document.getElementById("UserColum").innerHTML = received_msg;
                            return
                                document.getElementById("UserColum").innerHTML = "No players"
                        }
                        if (received_msg.includes(") Used command: bc") && !disablepopups)
                        {
                            var e = received_msg.split("Used command: bc ")
                            e = e[1].split(" ");
                            var Toast = Swal.mixin({
                                position: 'center',
                                timer: e[0] * 1000,
                                customClass: "bg-light",
                                timerProgressBar: true,
                            })

                            Toast.fire({
                                title: "Broadcast",
                                icon: 'info',
                                html: e.join(" ").replace(e[0], "")
                            })
                        }
                        if (received_msg.includes(") Used command: @") && !disablepopups)
                        {
                            var e = received_msg.split("] ")
                            var Toast = Swal.mixin({
                                position: 'center',
                                timer: 7000,
                                customClass: "bg-light",
                                timerProgressBar: true,
                            })
                            var txt = e.join(" ").replace("Used command: @", ":")
                            var arrStr = txt.split(/[()]/);
                            for (index = 0; index < arrStr.length; ++index) {
                                txt = txt.replace("(" + arrStr[index] + ")", '');
                            }
                            Toast.fire({
                                title: "Admin chat",
                                icon: 'info',
                                html: txt
                            })
                        }
                        if (received_msg.includes("Teamkill ⚠:") && !disablepopups)
                        {
                            var Toast = Swal.mixin({
                                position: 'center',
                                timer: 15000,
                                customClass: "bg-light",
                                timerProgressBar: true,
                            })
                            Toast.fire({
                                title: "Admin chat",
                                icon: 'info',
                                html: received_msg
                            })
                        }
                        if (received_msg.includes("REQUEST_DATA:PLAYER#") && requesteddata)
                        {
                            var Toast = Swal.mixin({
                                position: 'center',
                                customClass: "bg-light",
                            })
                            Toast.fire({
                                title: "Request data",
                                icon: 'info',
                                html: received_msg
                            })
                            requesteddata = false;
                        }
                        log(received_msg)
                    }
                }
                xhttp.open("POST", "/php/scripts/sanitize.php")
                xhttp.send(received_msg)
                console.log(received_msg)
            };

            ws1.onclose = function() {
                IsConnected = false;
                log("<font color='red'>Connection lost</font>")
                document.getElementById('command').disabled = true
                document.getElementById('sendcommand').disabled = true
                document.getElementById('sendreconnect').disabled = false
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    customClass: "bg-light",
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'info',
                    title: 'Connection was closed by the remote host'
                })
            };

        } else {

            // The browser doesn't support WebSocket
            alert("WebSocket NOT supported by your Browser!");
        }
    }
    window.onload = WebSocketStart();
    function SendCommand() {
        if (!IsConnected)
            return;
        var xhttp = new XMLHttpRequest();

        log("<font color='#808080'>> " + document.getElementById('command').value + "</font>")
        var sel = document.getElementById('server');
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var filterOptions = {
                    'user': localStorage.getItem('username'),
                    'action': 'custom',
                    'key': this.response,
                    'command': document.getElementById('command').value
                };
                ws.send(JSON.stringify(filterOptions));
            }
        }
        xhttp.open("GET", "/php/scripts/getkey.php")
        xhttp.send()
    }
    function SendCommandArgs(args) {
        if (!IsConnected)
            return;
        var xhttp = new XMLHttpRequest();
        var sel = document.getElementById('server');
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                Swal.fire({
                    title: 'Execute command on player: ' + args,
                    showDenyButton: true,
                    showCancelButton: true,
                    customClass: "bg-light",
                    confirmButtonText: `kick/ban`,
                    denyButtonText: `Request-Data`,
                }).then((result) => {
                    disablepopups = true;
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'kick/ban '+args,
                            html: '<div class="form-group">\n' +
                                '                                Ban duration<br>\n' +
                                '                                <div class="row">\n' +
                                '                                    <div class="col">\n' +
                                '                                        <label for="banDays">Days </label>\n' +
                                '                                        <input class="form-control col w-70" type="number" id="banDays" value="0" min="0" required>\n' +
                                '                                    </div>\n' +
                                '                                    <div class="col">\n' +
                                '                                        <label for="banHours">Hours </label>\n' +
                                '                                        <input class="form-control col w-70" type="number" id="banHours" value="0" min="0" required>\n' +
                                '                                    </div>\n' +
                                '                                    <div class="col">\n' +
                                '                                        <label for="banMinutes">Minutes </label>\n' +
                                '                                        <input class="form-control col w-70" type="number" id="banMinutes" value="0" min="0" required>\n' +
                                '                                    </div>\n' +
                                '                                </div>\n' +
                                '                            </div>' +
                                '<div class="form-group">\n' +
                                '                                <label for="reasoncustom">Reason</label>\n' +
                                '                                <input class="form-control" type="text" id="reasoncustom" placeholder="Reason"><br>\n' +
                                '                            </div>',
                            icon: 'warning',
                            showCancelButton: true,
                            customClass: "bg-light",
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Execute'
                        }).then((result) => {
                            disablepopups = false;
                            /* Read more about isConfirmed, isDenied below */
                            if (result.isConfirmed) {
                                var days = document.getElementById("banDays").value;
                                var hours = document.getElementById("banHours").value;
                                var minutes = document.getElementById("banMinutes").value;
                                var reason = document.getElementById("reasoncustom").value;
                                var total = Number(minutes) + (Number(hours) * 60) + (Number(days) * 24 * 60);
                                console.log(total)
                                if (!reason)
                                {
                                    Swal.fire({
                                        title: 'kick/ban',
                                        html: 'Reason cannot be empty',
                                        icon: 'error',
                                        customClass: "bg-light"
                                    })
                                    return
                                }
                                var filterOptions = {
                                    'user': localStorage.getItem('username'),
                                    'action': 'custom',
                                    'key': this.response,
                                    'command': 'ban '+args+' '+total+' '+reason
                                };
                                ws.send(JSON.stringify(filterOptions));
                            }
                        })
                    } else if (result.isDenied) {
                        disablepopups = false;
                        var filterOptions = {
                            'user': localStorage.getItem('username'),
                            'action': 'custom',
                            'key': this.response,
                            'command': 'request_data short-player '+args
                        };
                        requesteddata = true;
                        ws.send(JSON.stringify(filterOptions));
                    }
                })
            }
        }
        xhttp.open("GET", "/php/scripts/getkey.php")
        xhttp.send()
    }
    function SendKeepAlive() {
        if (!IsConnected)
            return;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                ws.send("STAYALIVE");
            }
        }
        xhttp.open("GET", "/php/scripts/getkey.php")
        xhttp.send()
    }
    window.setInterval(function(){
        if (!IsConnected)
            return;
        SendKeepAlive();
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    var filterOptions = {
                        'user': localStorage.getItem('username'),
                        'action': 'custom',
                        'key': this.response,
                        'command': "PLAYERLISTCOLORED SILENT"
                    };
                    ws.send(JSON.stringify(filterOptions));
                }
            }
            xhttp.open("GET", "/php/scripts/getkey.php")
            xhttp.send()
    }, 4000);
</script>
</html>
