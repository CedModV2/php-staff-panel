<?php
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php");
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/connect.php");
//require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../elements/metadata.php");

    // Gets steam username of user with steamID64 $id.
    // WARNING: might take a long time to request.
    function getSteamName($id) {
      $xml = simplexml_load_file("http://steamcommunity.com/profiles/$id/?xml=1");//link to user xml
      if(!empty($xml)) {
        $username = $xml->steamID;
          return $username;
      }
    }

    // Converts an amount of minutes to a more readable time format.
    function minToHourMinute($time) {
      $sec = ($time * 60);
      $date1 = new DateTime("@0");
      $date2 = new DateTime("@$sec");
      $interval =  date_diff($date1, $date2);
      return $interval->format('%yY %mM %dD %hh %im %ss');
    }

    // Returns the amount of minutes this ban has left.
    function getBanTimeLeft($ban) {
        if ($ban['unixstamp'] + $ban['banduration'] * 60 < time()) {
            return 0;
        } else {
            return (($ban['unixstamp'] + ($ban['banduration'] * 60)) - time()) / 60;
        }
    }

    // Returns a specific ban with id 'steamid'
    function getBan($steamid) {
        $url = "https://test.cedmod.nl/api/getban.php?alias=".GetAlias()."&id=".$steamid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_USERAGENT, "CedMod Community management webpanel");
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
        http_response_code($info['http_code']);
        $bans = (array) json_decode($result, true);
        if ($bans["success"] == "true") {

        if (!empty($bans['message'])) {
          return $bans['message'][0];
        } else {
          return null;
        }
        }
    }

    // Create a new audit log.
    function createAuditLog($time, $action, $modName, $information) {
        $db = createPDO(realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/mysqlauditlog.ini"));

        $query = $db -> prepare("INSERT INTO autilog
                            (timestamp, action, user, parameters)
                            VALUES
                            (:time, :action, :moderator, :parameters)
                        ");

        $query -> bindParam(":time", $time, PDO::PARAM_INT);
        $query -> bindParam(":action", $action);
        $query -> bindParam(":moderator", $modName);
        $query -> bindParam(":parameters", $information);

        $query -> execute();
    }
function GetTime()
{
    date_default_timezone_set('Europe/Amsterdam'); // CET
    $info = getdate();
    $date = $info['mday'];
    $month = $info['mon'];
    $year = $info['year'];
    $hour = $info['hours'];
    $min = $info['minutes'];
    $sec = $info['seconds'];
    return "$year-$month-$date $hour:$min:$sec";
}
function generateApplicationapprove($ban, $game) {
    return '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#approveModal'. $ban['id'] .'">Approve application</button>
      <div class="modal fade" id="approveModal'. $ban['id'] .'" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="approveModalLabel'. $ban['id'] .''. $ban['id'] .'">Unban '. $ban['id'] .'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/'.$game.'application_overview.php">
                <div class="form-group">
                  <label for="discordid" class="col-form-label">Discord id</label>
                  <input type="text" class="form-control steamid" name="discordid" id="discordid" value="'. $ban['discordid'] .'" readonly>
                </div>
              </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitapprove" value="Approve">
              </form>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
}
function generateApplicationdeny($ban, $game) {
    return '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#denyModal'. $ban['id'] .'">Deny application</button>
      <div class="modal fade" id="denyModal'. $ban['id'] .'" tabindex="-1" role="dialog" aria-labelledby="denyModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="denyModalLabel'. $ban['id'] .''. $ban['id'] .'">Unban '. $ban['id'] .'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/'.$game.'application_overview.php">
                <div class="form-group">
                  <label for="discordid" class="col-form-label">Discord id</label>
                  <input type="text" class="form-control steamid" name="discordid" id="discordid" value="'. $ban['discordid'] .'" readonly>
                </div>
                 <div class="form-group">
                  <label for="unbanReason" class="col-form-label">Reason for unban</label>
                  <input type="text" class="form-control" name="denyReason" id="denyReason" maxlength="140">
                </div>
              </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitdeny" value="Deny">
              </form>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
}
function generateapprove($ban, $appeal) {
    return '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#approveModal'. $ban['id'] .'">Approve appeal</button>
      <div class="modal fade" id="approveModal'. $ban['id'] .'" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="approveModalLabel'. $ban['id'] .''. $ban['id'] .'">Unban '. $ban['username'] .'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/appeal_overview.php">
                <div class="form-group">
                  <label for="discordid" class="col-form-label">Discord id</label>
                  <input type="text" class="form-control steamid" name="discordid" id="discordid" value="'. $appeal['discordid'] .'" readonly>
                </div>
                <div class="form-group">
                  <label for="steamid64" class="col-form-label">Discord id</label>
                  <input type="text" class="form-control steamid" name="steamid64" id="steamid" value="'. $appeal['userid'] .'" readonly>
                </div>
              </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitapprove" value="Approve">
              </form>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
}
function generatedeny($ban, $appeal) {
    return '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#denyModal'. $ban['id'] .'">Deny appeal</button>
      <div class="modal fade" id="denyModal'. $ban['id'] .'" tabindex="-1" role="dialog" aria-labelledby="denyModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="denyModalLabel'. $ban['id'] .''. $ban['id'] .'">Unban '. $ban['username'] .'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/appeal_overview.php">
                <div class="form-group">
                  <label for="discordid" class="col-form-label">Discord id</label>
                  <input type="text" class="form-control steamid" name="discordid" id="discordid" value="'. $appeal['discordid'] .'" readonly>
                </div>
                <div class="form-group">
                  <label for="steamid64" class="col-form-label">Discord id</label>
                  <input type="text" class="form-control steamid" name="steamid64" id="steamid" value="'. $appeal['userid'] .'" readonly>
                </div>
                 <div class="form-group">
                  <label for="unbanReason" class="col-form-label">Reason for unban</label>
                  <input type="text" class="form-control" name="denyReason" id="denyReason" maxlength="140">
                </div>
              </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitdeny" value="Deny">
              </form>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
}
function generateAppealCard($appeal) {

    $ban = getBan($appeal['userid']);
    $steamid = $ban['playersteamid'];
    $id = $ban['id'];
        return '<div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.$id.'>
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.$id.'" aria-expanded="false" aria-controls="collapseOne'.$id.'">
          <h5 class="card-title">'. $appeal['discordid'] .' ('.$ban['username'].' |'. $steamid .') Case#'.$ban['id'].'</h5>
        </button>
      </h5>
    </div>

    <div id="collapseOne'.$id.'" class="collapse " aria-labelledby="headingOne'.$id.'" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $appeal['discordid'] .' ('.$ban['username'].' |'. $steamid .')</h5>
		  <h6 class="card-subtitle mb-2 text-muted">Ip: '. $ban['ip']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
          <p class="card-text">Appeal Reason: '. $appeal['appealreason'] .'</p>
          '. generateapprove($ban, $appeal).generatedeny($ban, $appeal).'
      </div>
    </div>
  </div>
</div>';
}
function generateApplicationCard($application, $game) {
    $context = stream_context_create(["http" => ["method" => "GET", "header" => "Authorization: Bot NjY3ODUzNTc4MjE1NDg5NTc2.XscakQ.ijyh9jO1SLSLDSRBRTYJkCYxFLU"]]);

    $file = @file_get_contents('https://discordapp.com/api/users/' . $application['discordid'], true, $context);
    $file = json_decode($file, true);
    if ($http_response_header[0] == "HTTP/1.1 404 Not Found")
    {
        $username = "NaN";
    }
    else {
        if (!empty($file['username'])) {
            $username = $file['username']."#".$file['discriminator'];
        }
    }
    $steamid = $application['steam'];
    $id = $application['id'];
    return '<div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.$id.'>
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.$id.'" aria-expanded="false" aria-controls="collapseOne'.$id.'">
          <h5 class="card-title">'. $application['discordid'] .'('. $username. ')</h5>
        </button>
      </h5>
    </div>

    <div id="collapseOne'.$id.'" class="collapse " aria-labelledby="headingOne'.$id.'" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $application['discordid'] .'('. $username. ')</h5>
          <h6 class="card-subtitle mb-2 text-muted">Steam URL: '. $application['steam']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Age: '. $application['age']. '</h6>
          <p class="card-text">Application Reason: '. $application['why'] .'</p>
          <p class="card-text">About: '. $application['about'] .'</p>
          '. generateApplicationapprove($application, $game).generateApplicationdeny($application, $game).'
      </div>
    </div>
  </div>
</div>';
}

    function generateBanLogCard($ban) {
        $steamid = $ban['playersteamid'];
        $id = $ban['id'];
        if ($_SESSION["permlevelfrikandelbroodje"] >= 4) {
            return '<div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.$id.'>
      <h5 class="mb-0">
        <element class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.$id.'" aria-expanded="false" aria-controls="collapseOne'.$id.'">
          <h5 class="card-title">'. $ban['username'] .' ('. $steamid .') Case#'.$ban['id'].'</h5>
        </element>
      </h5>
    </div>

    <div id="collapseOne'.$id.'" class="collapse " aria-labelledby="headingOne'.$id.'" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $ban['username'] .' (<a href="https://steamid.io/lookup/'.explode("@", $ban['playersteamid'])[0].'" target="_blank">'.explode("@", $ban['playersteamid'])[0].'</a>)</h5>
        <h6 class="card-subtitle mb-2 text-muted">UserID: '.$steamid. '</h6>
		  <h6 class="card-subtitle mb-2 text-muted">Ip: '. $ban['ip']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
          '. generateDelLogModal($ban).'
      </div>
    </div>
  </div>
  
</div>'; } if ($_SESSION["permlevelfrikandelbroodje"] >= 3) {
            return '
      <div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.$id.'">
      <h5 class="mb-0">
        <element class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.$id.'" aria-expanded="false" aria-controls="collapseOne'.$id.'">
          <h5 class="card-title">'. $ban['username'] .' ('. $steamid .')  Case#'.$ban['id'].'</h5>
        </element>
      </h5>
    </div>

    <div id="collapseOne'.$id.'" class="collapse" aria-labelledby="headingOne'.$id.'" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $ban['username'] .' (<a href="https://steamid.io/lookup/'.explode("@", $ban['playersteamid'])[0].'" target="_blank">'.explode("@", $ban['playersteamid'])[0].'</a>)</h5>
        <h6 class="card-subtitle mb-2 text-muted">UserID: '.$steamid. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
          '. generateDelLogModal($ban).'
      </div>
    </div>
  </div>
  
</div>
      '; } if ($_SESSION["permlevelfrikandelbroodje"] <= 2) {
            return '
      <div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.$id.'">
      <h5 class="mb-0">
        <element class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.$id.'" aria-expanded="false" aria-controls="collapseOne'.$id.'">
          <h5 class="card-title">'. $ban['username'] .' ('. $steamid .')  Case#'.$ban['id'].'</h5>
        </element>
      </h5>
    </div>

    <div id="collapseOne'.$id.'" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $ban['username'] .' (<a href="https://steamid.io/lookup/'.explode("@", $ban['playersteamid'])[0].'" target="_blank">'.explode("@", $ban['playersteamid'])[0].'</a>)</h5>
        <h6 class="card-subtitle mb-2 text-muted">UserID: '.$steamid. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
      </div>
    </div>
  </div>
  
</div>
      '; }
    }
    function generateBanCard($ban) {
      $steamid = $ban['playersteamid'];
	        if ($_SESSION["permlevelfrikandelbroodje"] >= 4) {
	            return '<div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.explode("@", $ban['playersteamid'])[0].'>
      <h5 class="mb-0">
        <element class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.explode("@", $ban['playersteamid'])[0].'" aria-expanded="false" aria-controls="collapseOne'.explode("@", $ban['playersteamid'])[0].'">
          <h5 class="card-title">'. $ban['username'] .' ('. $steamid .') Case#'.$ban['id'].'</h5>
        </element>
      </h5>
    </div>

    <div id="collapseOne'.explode("@", $ban['playersteamid'])[0].'" class="collapse " aria-labelledby="headingOne'.explode("@", $ban['playersteamid'])[0].'" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $ban['username'] .' (<a href="https://steamid.io/lookup/'.explode("@", $ban['playersteamid'])[0].'" target="_blank">'.explode("@", $ban['playersteamid'])[0].'</a>)</h5>
        <h6 class="card-subtitle mb-2 text-muted">UserID: '.$steamid. '</h6>
		  <h6 class="card-subtitle mb-2 text-muted">Ip: '. $ban['ip']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <h6 class="card-subtitle my-2 text-primary">'. minToHourMinute(getBanTimeLeft($ban)) .' left</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
          '. generateModifyBanModal($ban) . generateUnbanModal($ban).'
      </div>
    </div>
  </div>
  
</div>'; } if ($_SESSION["permlevelfrikandelbroodje"] >= 3) {
      return '
      <div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.explode("@", $ban['playersteamid'])[0].'">
      <h5 class="mb-0">
        <element class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.explode("@", $ban['playersteamid'])[0].'" aria-expanded="false" aria-controls="collapseOne'.explode("@", $ban['playersteamid'])[0].'">
          <h5 class="card-title">'. $ban['username'] .' ('. $steamid .') Case#'.$ban['id'].'</h5>
        </element>
      </h5>
    </div>

    <div id="collapseOne'.$steamid.'" class="collapse" aria-labelledby="headingOne'.explode("@", $ban['playersteamid'])[0].'" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $ban['username'] .' (<a href="https://steamid.io/lookup/'.explode("@", $ban['playersteamid'])[0].'" target="_blank">'.explode("@", $ban['playersteamid'])[0].'</a>)</h5>
        <h6 class="card-subtitle mb-2 text-muted">UserID: '.$steamid. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <h6 class="card-subtitle my-2 text-primary">'. minToHourMinute(getBanTimeLeft($ban)) .' left</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
          '. generateModifyBanModal($ban) . generateUnbanModal($ban).'
      </div>
    </div>
  </div>
  
</div>
      '; } if ($_SESSION["permlevelfrikandelbroodje"] <= 2) {
            return '
      <div id="accordion">
  <div class="card bg-custom">
    <div class="card-header" id="headingOne'.explode("@", $ban['playersteamid'])[0].'">
      <h5 class="mb-0">
        <element class="btn btn-link" data-toggle="collapse" data-target="#collapseOne'.explode("@", $ban['playersteamid'])[0].'" aria-expanded="false" aria-controls="collapseOne'.explode("@", $ban['playersteamid'])[0].'">
          <h5 class="card-title">'. $ban['username'] .' ('. $steamid .') Case#'.$ban['id'].'</h5>
        </element>
      </h5>
    </div>

    <div id="collapseOne'.explode("@", $ban['playersteamid'])[0].'" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">
        <h5 class="card-title">'. $ban['username'] .' (<a href="https://steamid.io/lookup/'.explode("@", $ban['playersteamid'])[0].'" target="_blank">'.explode("@", $ban['playersteamid'])[0].'</a>)</h5>
        <h6 class="card-subtitle mb-2 text-muted">UserID: '.$steamid. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned on: '. $ban['timestamp']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">Banned by: '. $ban['adminname']. '</h6>
          <h6 class="card-subtitle mb-2 text-muted">'. minToHourMinute($ban['banduration']) .' total</h6>
          <h6 class="card-subtitle my-2 text-primary">'. minToHourMinute(getBanTimeLeft($ban)) .' left</h6>
          <p class="card-text">Reason: '. $ban['reason'] .'</p>
      </div>
    </div>
  </div>
  
</div>
      '; }
  }

    function generateDelLogModal($ban) {
      return '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#unbanModal'. $ban['id'] .'">Remove BanLog</button>
      <div class="modal fade" id="unbanModal'. $ban['id'] .'" tabindex="-1" role="dialog" aria-labelledby="unbanModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="unbanModalLabel'. $ban['id'] .''. $ban['id'] .'">Unban '. $ban['username'] .'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/ban_management.php">
                <div class="form-group">
                  <label for="steamid" class="col-form-label">BanLog ID</label>
                  <input type="text" class="form-control steamid" name="steamid64" id="steamid" value="'. $ban['id'] .'" readonly>
                </div>
                <div class="form-group">
                  <label for="unbanReason" class="col-form-label">Reason for removal</label>
                  <input type="text" class="form-control" name="reason" id="unbanReason" maxlength="140">
              </div>
              <input type="submit" class="btn btn-success" name="submitDelLog" value="Remove">
              </form>
              </div>
              <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
    }
    function generateUnbanModal($ban) {
      $steamid = $ban['playersteamid'];

      return '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#unbanModal'. explode("@", $ban['playersteamid'])[0] .'">Unban player</button>
      <div class="modal fade" id="unbanModal'. explode("@", $ban['playersteamid'])[0] .'" tabindex="-1" role="dialog" aria-labelledby="unbanModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="unbanModalLabel'. explode("@", $ban['playersteamid'])[0] .'">Unban '. $ban['username'] .'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/ban_management.php">
                <div class="form-group">
                  <label for="steamid" class="col-form-label">steamID64</label>
                  <input type="text" class="form-control steamid" name="steamid64" id="steamid" value="'. $steamid .'" readonly>
                </div>
                <div class="form-group">
                  <label for="unbanReason" class="col-form-label">Reason for unban</label>
                  <input type="text" class="form-control" name="reason" id="unbanReason" maxlength="140">
                </div>
              <div class="modal-footer">
                <input type="submit" class="btn btn-success" name="submitUnban" value="Unban">
              </div>
              </form>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
    }


    function generateModifyBanModal($ban) {
      $steamid = $ban['playersteamid'];

      return '<button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#modifyModal'. explode("@", $ban['playersteamid'])[0] .'">Modify ban</button>
      <div class="modal fade" id="modifyModal'. explode("@", $ban['playersteamid'])[0] .'" tabindex="-1" role="dialog" aria-labelledby="modifyModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content bg-light">
          <div class="modal-header">
            <h5 class="modal-title" id="modifyModalLabel'. explode("@", $ban['playersteamid'])[0] .'">Modify '. $ban['username'] .'\'s ban</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <form method="post" action="/php/ban_management.php">
                <div class="form-group">
                  <label for="steamid" class="col-form-label">steamID64</label>
                  <input type="text" class="form-control steamid" name="steamid64" id="steamid" value="'. $steamid .'" readonly>
                </div>
                <div class="form-group">
                  <label class="h5" for="banDetails">Ban details</label>
                  <hr>
                  <p class="text-muted">'. minToHourMinute($ban['banduration']) .' total</p>
                  <p class="text-primary">'. minToHourMinute(getBanTimeLeft($ban)) .' left</p><br>
                  <div class="row">
                    <div class="col-sm">
                      <input class="btn btn-warning form-control" type="submit" name="submitAddTime" value="Set Duration">
                    </div>
                    <div class="col-sm">
                      <label class="position-absolute" style="bottom: 45px;" for="addDays">Days</label>
                      <input class="form-control" type="number" name="addDays" min="0" value="0">
                    </div>
                    <div class="col-sm">
                      <label class="position-absolute" style="bottom: 45px;" for="addHours">Hours</label>
                      <input class="form-control" type="number" name="addHours" min="0" value="0">
                    </div>
                    <div class="col-sm">
                      <label class="position-absolute" style="bottom: 45px;" for="addMinutes">Minutes</label>
                      <input class="form-control" type="number" name="addMinutes" min="0" value="0">
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="oldReason" class="col-form-label">Original reason</label>
                  <input type="text" class="form-control steamid" name="oldReason" id="oldReason" value="'. $ban['reason'] .'" readonly>
                </div>
                <div class="form-group">
                  <label for="newReason" class="col-form-label">New reason</label>
                  <input type="text" class="form-control" name="newReason" id="newReason" maxlength="500" placeholder="Leave empty to use old reason">
                </div>
                <hr>
                <div class="form-group">
                  <label for="modifyReason" class="col-form-label">Reason for modification</label>
                  <input type="text" class="form-control" name="modifyReason" id="modifyReason" maxlength="140" placeholder="Why was this ban changed?" required>
                </div>
                <input type="submit" class="btn btn-info" name="submitNewReason" value="Change ban reason">
              </form>
              </div>
              <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
      </div>
      </div>';
    }
    function successmodal($message, $time = 5, $outsideclick = "false", $confirmbutton = "false", $loaddelay = 500)
    {
        $time = $time * 1000;
        echo '<script>
$(document).ready(function() {
    swal.fire({
      icon: "success",
      customClass: "bg-light",
      allowOutsideClick: '.$outsideclick.',
      showConfirmButton: '.$confirmbutton.',
      onOpen: () => {
      const content = "'.$message.'"
      if (content) {
          Swal.showLoading()
          setTimeout(() => {           
          Swal.hideLoading()
          Swal.getContent().innerHTML = content }, '.$loaddelay.');
      }
  },
      html: "",
      timer: '.$time.',
      timerProgressBar: true,
    });
})
</script>';
    }
function failmodal($message, $time = 10, $outsideclick = "false", $confirmbutton = "false", $loaddelay = 500)
{
    $time = $time * 1000;
    echo '<script>
$(document).ready(function() {
    swal.fire({
      icon: "error",
      customClass: "bg-light",
      allowOutsideClick: '.$outsideclick.',
      showConfirmButton: '.$confirmbutton.',
      onOpen: () => {
      const content = "'.$message.'"
      if (content) {
          Swal.showLoading()
          setTimeout(() => {           
          Swal.hideLoading()
          Swal.getContent().innerHTML = content }, '.$loaddelay.');
      }
  },
      html: "",
      timer: '.$time.',
      timerProgressBar: true,
    });
})
</script>';
}
function LoginPrompt($registerfirst = "false")
{
    $outsideclick = "false";
    $confirmbutton = "false";
    $loaddelay = 500;
    echo '<script>
$(document).ready(function() {
    if ("'.$registerfirst.'" == "false")
        LoginPrompt()   
    else
        RegisterPrompt()
})
function RegisterPrompt()
{
      swal.fire({
      icon: "question",
      customClass: "bg-light",
      allowOutsideClick: '.$outsideclick.',
      showConfirmButton: '.$confirmbutton.',
      onOpen: () => {
      const content = \'<h2>Register</h2><br><hr><form method="post" action="/php/auth/register.php"><div class="form-group"><label for="username">Username </label><input class="form-control" type="text" name="username" required><br></div><div class="form-group"><label for="password">Password </label><input class="form-control" type="password" name="password" required><br></div><div class="form-group"><label for="repeatPassword">Repeat password </label><input class="form-control" type="password" name="confirm_password" required><br></div><div class="btn-group" role="group"><button class="btn btn-primary" type="submit" name="submitRegister">Register</button><button class="btn btn-secondary" type="button" onclick="LoginPrompt()">I already have an account</button></div></form>\'
      if (content) {
          document.getElementsByClassName("container-fluid")[0].innerHTML = "Login required";
          Swal.showLoading()
          setTimeout(() => {           
          Swal.hideLoading()
          Swal.getContent().innerHTML = content }, '.$loaddelay.');
      }
  },
      html: "",
    });
}
function LoginPrompt()
{
      swal.fire({
      icon: "question",
      customClass: "bg-light",
      allowOutsideClick: '.$outsideclick.',
      showConfirmButton: '.$confirmbutton.',
      onOpen: () => {
      const content = \'<h2>Login</h2><br><hr><form method="post" action="/php/auth/login.php"><div class="form-group"><label for="username">Username </label><input class="form-control" type="text" name="username" required><br></div><div class="form-group"><label for="password">Password </label><input class="form-control" type="password" name="password" required><br></div><div class="btn-group" role="group"><button class="btn btn-primary" type="submit" name="submitLogin">Login</button><button class="btn btn-secondary" type="button" onclick="RegisterPrompt()">I do not have an account yet</button></div></form>\'
      if (content) {
          document.getElementsByClassName("container-fluid")[0].innerHTML = "Login required";
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
}
    function getDetails($ip, $port)
    {
        $json = file_get_contents('https://kigen.co/scpsl/getinfo.php?ip='.$ip.'&port='.$port);
        $obj = json_decode($json);
        return $obj->players;
    }
