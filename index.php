<?PHP
require_once "class/Login.class.php";
$login = Login::getInstance();
?>
<!doctype html>
<html lang="de">

<head>
  <link rel="stylesheet" href="openlayers/ol.css" type="text/css">
  <link rel="stylesheet" href="css/style.css" type="text/css">
  <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <script src="openlayers/ol.js"></script>
  <title>StavaAPI Test</title>
</head>

<body>
  <div id="map" class="map"></div>
  <div id='info'>
    <input type="date" id="von" onchange="filter()">
    <input type="date" id="bis" onchange="filter()">
    <br />
    <input type="checkbox" id="ride" checked onchange="filter()">Ride
    <input type="checkbox" id="walk" checked onchange="filter()">Walk
    <input type="checkbox" id="hike" checked onchange="filter()">Hike
    <input type="checkbox" id="run" checked onchange="filter()">Run
    <input type="checkbox" id="other" checked onchange="filter()">Other
    <br />
    <input type="checkbox" id="athlete_count" onchange="filter()">Teilnehmer > 1
    <br />
    <span id='gfi'></span>
  </div>

  <a href="http://strava.com" style="position: absolute; bottom: 5px; right: 5px;"><img src="api_logo_pwrdBy_strava_stack_light.png" alt="powered by Strava" /></a>
  <div id="ausblenden">
    <div>
      <div id="login">

        <?php
        if ($login->isLoggedin()) {
          echo "Hallo " . $login->getUserName() . "<br />";
          //echo "Bei der ersten Verwendung oder wenn neue hinzukamen:<br />";
          echo '<input type="button" id="laden" value="Daten von Strava laden" onclick="daten_laden()" >';
          //echo "Wenn Daten bereits geladen wurden:<br />";
          echo '<input type="button" id="laden" value="Daten aus DB laden" onclick="load_geo()" >';
        } else {
          echo "Um Daten auf der Karte anzeigen zu können, müssen Sie sich bei Strava zuerst einloggen:";
          echo "<a href='https://www.strava.com/oauth/authorize?client_id=" . Config::$strava_client . "&response_type=code&scope=activity:read_all,profile:read_all,activity:write&redirect_uri=" . Config::$url;
          echo "index.php'><img src='btn_strava_connectwith_orange.png' alt='Login with Strava' /></a>";
        }
        ?>
      </div>

      <script type="text/javascript" src="js/script.js"></script>
</body>

</html>