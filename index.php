<?PHP
require_once "class/Database.class.php";

$login = false;
$name = '';
$user = 0;

$db = Database::getConnection();

if (isset($_GET['code'])) {
  $code = $_GET['code'];


  $url = 'https://www.strava.com/oauth/token';
  $data = array('client_id' => Config::$strava_client, 'client_secret' => Config::$strava_secret, 'code' => $code, 'grant_type' => 'authorization_code');
  $options = array(
    'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'POST',
      'content' => http_build_query($data),
    )
  );

  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  $answer = json_decode($result, true);

  $token = $answer["access_token"];

  try {
    $sql = 'REPLACE INTO strava_authkey (user, code, access, refresh, expire) 
        VALUES(' . $answer["athlete"]["id"] . ', "' .
      $code . '","' .
      $answer["access_token"] . '","' .
      $answer["refresh_token"] . '",FROM_UNIXTIME(' .
      $answer["expires_at"] . '))';
    $db->exec($sql);

    $sql = 'REPLACE INTO strava_user (id, firstname, lastname, city) 
        VALUES(' . $answer["athlete"]["id"] . ', "' .
      $answer["athlete"]["firstname"] . '","' .
      $answer["athlete"]["lastname"] . '","' .
      $answer["athlete"]["city"] . '")';
    $db->exec($sql);

    $name = $answer["athlete"]["firstname"] . ' ' . $answer["athlete"]["lastname"];
    $user = $answer["athlete"]["id"];
    $db = null;
  } catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
  setcookie("strava_authkey", $code, time() + $answer["expires_in"]);
  $login = true;
} elseif (isset($_COOKIE['strava_authkey'])) {
  $login = true;

  try {
    $sql = 'SELECT firstname, lastname, u.id user FROM strava_authkey a left join strava_user u on a.user = u.id where code = "' . $_COOKIE['strava_authkey'] . '"';
    foreach ($db->query($sql) as $row) {
      $name = $row['firstname'] . " " . $row['lastname'];
      $user = $row['user'];
    }

    $db = null;
  } catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
}
$db = null;

?>


<!doctype html>
<html lang="de">

<head>
  <link rel="stylesheet" href="openlayers/ol.css" type="text/css">
  <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <style>
    body,
    html,
    .map,
    #ausblenden {
      height: 100%;
      width: 100%;
      padding: 0px;
      margin: 0px;
    }

    #login {
      position: absolute;
      top: 100px;
      left: 50%;
      width: 200px;
      height: 180px;
      background-color: rgba(255, 255, 255, 0.8);
      margin-left: -100px;
      text-align: center;
      padding: 30px;
      font-family: sans-serif;
    }

    #login a {
      position: absolute;
      bottom: 20px;
      left: 50%;
      margin-left: -96px;
      width: 193px;

    }

    #ausblenden {
      background-color: rgba(0, 0, 0, 0.3);
      position: absolute;
      top: 0px;
      left: 0px;
    }

    .wait,
    .wait * {
      cursor: wait !important;
    }

    #info {
      position: absolute;
      top: 20px;
      right: 10px;
      background-color: rgba(255, 255, 255, 0.8);
      padding: 30px;
      font-family: sans-serif;
    }
  </style>
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
    <input type="checkbox" id="athlete_count" checked onchange="filter()">Teilnehmer > 1
    <br />
    <span id='gfi'></span>
  </div>

  <a href="http://strava.com" style="position: absolute; bottom: 5px; right: 5px;"><img src="api_logo_pwrdBy_strava_stack_light.png" alt="powered by Strava" /></a>
  <div id="ausblenden">
    <div>
      <div id="login">

        <?php
        if ($login) {
          echo "Hallo " . $name . "<br />";
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




      <script type="text/javascript">
        var vectorLayer = null;
        var map = new ol.Map({
          target: 'map',
          layers: [
            new ol.layer.Tile({
              source: new ol.source.OSM(),
              opacity: 0.6
            })
          ],
          view: new ol.View({
            center: ol.proj.fromLonLat([10, 53.8]),
            zoom: 6
          })
        });

        function daten_laden() {
          document.getElementById("laden").disabled = true;
          document.body.className = 'wait';
          var xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              document.getElementById("login").style.display = 'none';

              load_geo();
            }
          };
          xhttp.open("GET", "load.php", true);
          xhttp.send();
        }

        function styleFilter (feature) {
          let von = document.getElementById("von").value;
          let bis = document.getElementById("bis").value;
          let ride = document.getElementById("ride").checked;
          let walk = document.getElementById("walk").checked;
          let hike = document.getElementById("hike").checked;
          let run = document.getElementById("run").checked;
          let other = document.getElementById("other").checked;
          let athlete_count = document.getElementById("athlete_count").checked;

          console.log(von);

          if (!ride && feature.get('type') == 'Ride') return false;
          if (!walk && feature.get('type') == 'Walk') return false;
          if (!hike && feature.get('type') == 'Hike') return false;
          if (!run && feature.get('type') == 'Run') return false;
          if (!other && feature.get('type') != 'Ride' && feature.get('type') != 'Walk' && feature.get('type') != 'Hike' && feature.get('type') != 'Run') return false;
          if (von && feature.get('starttime') < von) return false;
          if (bis && feature.get('starttime') > bis) return false;

          if (athlete_count && feature.get('athlete_count') < 2) return false;

          return true;
        }

        function filter () {
          if (vectorLayer)
          vectorLayer.getSource().changed();
        }

        function load_geo() {

          var xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {

              console.log(this);
              var vectorSource = new ol.source.Vector({
                features: (new ol.format.GeoJSON()).readFeatures(this.responseText, {
                  dataProjection: 'EPSG:4326',
                  featureProjection: 'EPSG:3857'
                })
              });

              vectorLayer = new ol.layer.Vector({
                source: vectorSource,
                style: function(feature, resolution) {
                  if (!styleFilter(feature)) {
                    return null;
                  }
                  let typ = feature.get('type');
                  let color = "red";
                  switch (typ) {
                    case "Ride":
                      color = 'red';
                      break;
                    case "Walk":
                      color = 'orange';
                      break;
                    case "Hike":
                      color = 'blue';
                      break;
                    case "Run":
                      color = 'cyan';
                      break;
                    default:
                      color = 'grey';
                  }

                  return new ol.style.Style({
                    stroke: new ol.style.Stroke({
                      color: color,
                      width: 2
                    })
                  })
                },
              });

              let select = new ol.interaction.Select({
                layers: [vectorLayer],
                hitTolerance: 10
              });
              select.on("select", function(event) {
                if (event.selected.length == 0) {
                  document.getElementById("gfi").innerHTML = "";
                  return;
                }
                let auswahl = event.selected[0];
                let props = auswahl.getProperties();
                console.log(props);
                let info = "<h2>" + props.name + "</h2>";
                if (props.starttime)
                  info += "<p>Datum: " + props.starttime + "</p>";
                if (props.elapsed_time)
                  info += "<p>Dauer: " + new Date(props.elapsed_time * 1000).toISOString().substring(11, 16) + "</p>";
                if (props.athlete_count)
                  info += "<p>Teilnehmer: " + props.athlete_count + "</p>";
                if (props.type)
                  info += "<p>Art: " + props.type + "</p>";
                if (props.distance)
                  info += "<p>Strecke: " + Math.round(props.distance/1000,1) + " km</p>";
                if (props.avg_speed)
                  info += "<p>Geschwindigkeit: " + Math.round(props.avg_speed*3.6,1) + " km/h</p>";
                if (props.elevation)
                  info += "<p>Anstieg: " + props.elevation + " m</p>";
                if (props.description)
                  info += "<p>" + props.description + "</p>";
                info += "<p><a target='_strava' href='https://www.strava.com/activities/" + auswahl.get("id") + "'>Auf Strava anzeigen</a></p>";
                document.getElementById("gfi").innerHTML = info;
              });
              map.addLayer(vectorLayer);
              map.addInteraction(select);
              document.body.className = "";
              document.getElementById("ausblenden").style.display = "none";
              document.getElementById("login").style.display = "none";
            }
          };
          xhttp.open("GET", "./wfs/?SERVICE=WFS&REQUEST=GetFeature&TYPENAMES=gis:linien&outputFormat=application/json&user=<?php echo $user ?>", true);
          xhttp.send();
        }
      </script>
</body>

</html>