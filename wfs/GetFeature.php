<?PHP
include 'layer.php';

/*
SERVICE:WFS
REQUEST:GetFeature
VERSION:2.0.0
TYPENAMES:punkte
SRSNAME:EPSG:4326
BBOX:-97.3046803985359503,93.16956757890432073,-7.4367093584813091,199.85857999214076131,EPSG:4326
*/

$layer_name = explode(':', $_GET['TYPENAMES'])[1];
if (!array_key_exists($layer_name, $layers)) {
    header("HTTP/1.1 404 Not Found");
    echo "Layer not found";
    exit;
}
$layer = $layers[$layer_name];


function xml($layer_name, $rs)
{
    header('Content-type: application/xml');
    echo <<<EOF
<?xml version='1.0' encoding='UTF-8'?>
<wfs:FeatureCollection xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/wfs/2.0 http://schemas.opengis.net/wfs/2.0/wfs.xsd http://www.opengis.net/gml/3.2 http://schemas.opengis.net/gml/3.2.1/gml.xsd https://florian-timm.de/gis 
EOF . Config::$url . <<<EOF
wfs/server.php?REQUEST=DescribeFeatureType" xmlns:wfs="http://www.opengis.net/wfs/2.0" timeStamp="2019-02-19T21:02:19Z" xmlns:gml="http://www.opengis.net/gml/3.2" numberMatched="unknown" numberReturned="0">
EOF;
    foreach ($rs as $row) {

        echo '<wfs:member>
            <gis:' . $layer_name . ' xmlns:gis="https://florian-timm.de/gis" gml:id="gis_' . $layer_name . '_' . $row['id'] . '">';

        for ($i = 0; $i < $rs->columnCount() - 2; $i++) {
            $col = $rs->getColumnMeta($i);
            if ($col["native_type"] != "GEOMETRY" && $row[$i] != null) {
                echo '<gis:' . $col['name'] . '>' . $row[$i] . '</gis:' . $col['name'] . '>';
            }
        }
        $geom_txt = $row['geometryWKT'];
        $geom = explode('(', substr($geom_txt, 0, -1));
        if ($geom[0] == "LINESTRING") {
            $geom_type = "LineString";
            $gml_type = "posList";
        } else if ($geom[0] == "POINT") {
            $geom_type = "Point";
            $gml_type = "pos";
        }
        echo '<gis:geom>
                <gml:' . $geom_type . ' gml:id="gis_' . $layer_name . '_' . $row['id'] . '_gis_GEOM" srsName="EPSG:' . $row['geometrySRID'] . '">
                <gml:' . $gml_type . '>' . str_replace(",", " ", $geom[1])  . '</gml:' . $gml_type . '>
                </gml:' . $geom_type . '>
            </gis:geom>';

        echo '  </gis:' . $layer_name . '>
        </wfs:member>';
    }
    echo "</wfs:FeatureCollection>";
}

function json($layer_name, $rs)
{
    header('Content-type: application/json');
    echo '{"type": "FeatureCollection", "features": [';
    /*
    {"type":"Feature","id":"water_areas.23","geometry":{"type":"MultiPolygon","coordinates":[[[[-8894836.86,5381000.5],[-8894825.07,5381032.26],[-8894816.35,5381039.83],[-8894774.3,5381031.02],[-8894716.03,5381026.55],[-8894713.34,5381014.82],[-8894719.1,5381002.37],[-8894742.99,5380977.7],[-8894759.23,5380973.33],[-8894777.12,5380973.53],[-8894795.06,5380979.26],[-8894836.86,5381000.5]]]]},"geometry_name":"the_geom","properties":{"osm_id":76963679,"natural":"natural","waterway":null,"landuse":"reservoir","name":null}}
    */
    $vorher_komma2 = false;
    foreach ($rs as $row) {
        $geom_txt = $row['geometryWKT'];
        if ($geom_txt == "") continue;
        $geom = explode('(', substr($geom_txt, 0, -1));

        if ($vorher_komma2) echo ",";
        echo '{"type":"Feature","id":"gis_' . $layer_name . '_' . $row['id'] . '","geometry":{"type":"';
        if ($geom[0] == "LINESTRING") {
            echo "LineString";
            $gml_type = "posList";
        } else if ($geom[0] == "POINT") {
            echo "Point";
            $gml_type = "pos";
        }

        echo '","coordinates":[[' . str_replace(" ", ",", str_replace(",", "],[", $geom[1]))  . ']]},"geometry_name":"the_geom","properties":{';

        $vorher_komma = false;
        for ($i = 0; $i < $rs->columnCount() - 2; $i++) {
            $col = $rs->getColumnMeta($i);

            if ($col["native_type"] != "GEOMETRY" && $row[$i] != null) {
                if ($vorher_komma) echo ',';
                echo '"' . $col['name'] . '":"' . str_replace('"', '\"', $row[$i]) . '"';
                $vorher_komma = true;
            }
        }
        echo '}}';
        $vorher_komma2 = true;
    }

    echo "]}";
}




try {
    //echo $layer[1];
    $bbox = null;
    $limit = 5000;
    $user = null;

    $table_name = $layer[0];
    $geom = $layer[1];

    if (isset($_GET["BBOX"])) {
        $bbox = explode(",", $_GET["BBOX"]);
    }
    if (isset($_GET["COUNT"])) {
        $limit = $_GET["COUNT"];
    }
    if (isset($_GET["USER"])) {
        $user = $_GET["USER"];
    }

    $sql = 'SELECT *, AsText(:geom) geometryWKT, ST_SRID(:geom) geometrySRID FROM
        :table_name where 1=1 ';
    if ($bbox != null) {
        $sql .= 'AND ST_INTERSECTS(:geom, ST_GEOMFROMTEXT(:bbox)) ';
    }
    if ($user != null) {
        $sql .= 'AND user = :user ';
    }
    $sql .= 'LIMIT :limit';

    $db = Database::getInstance();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':table_name', $table_name);
    $stmt->bindParam(':geom', $geom);   
    if ($bbox != null)
        $stmt->bindParam(':bbox', 'POLYGON((' . $bbox[0] . ' ' . $bbox[1] . ',' . $bbox[0] . ' ' . $bbox[3] . ',' . $bbox[2] . ' ' . $bbox[3] . ',' . $bbox[2] . ' ' . $bbox[1] . ',' . $bbox[0] . ' ' . $bbox[1] . '))');
    if ($user != null)
        $stmt->bindParam(':user', $user);
    $stmt->bindParam(':limit', $limit);
    $stmt->execute();
    $rs = $stmt->fetchAll();

    if ($_GET["outputFormat"] == 'application/json') {
        json($layer_name, $rs);
    } else {
        xml($layer_name, $rs);
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>