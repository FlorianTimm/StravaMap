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
      display: none;
      position: absolute;
      top: 20px;
      right: 10px;
      background-color: rgba(255, 255, 255, 0.8);
    }
  </style>
  <script src="openlayers/ol.js"></script>
  <title>StavaAPI Test</title>
</head>

<body>
  <div id="map" class="map"></div>
  <div id='info'></div>

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

              var vectorLayer = new ol.layer.Vector({
                source: vectorSource,
                style: function(feature, resolution) {
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
                  document.getElementById("info").style.display = "none";
                  return;
                }
                let auswahl = event.selected[0];
                let props = auswahl.getProperties();
                let info = "<h2>" + props.name + "</h2>";
                info += "<p>" + props.type + "</p>";
                info += "<p>" + props.distance + "</p>";
                info += "<p>" + props.avg_speed + "m/s</p>";
                info += "<p>" + props.elevation + "m</p>";
                info += "<p>" + props.description + "</p>";
                info += "<p>" + "https://www.strava.com/activities/" + auswahl.get("id") + "</p>";
                document.getElementById("info").innerHTML = info;
                document.getElementById("info").style.display = "";
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