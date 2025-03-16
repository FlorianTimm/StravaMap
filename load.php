<?php
include 'Polyline.php';
require 'login.php';

$per_page = 100;
$max_pages = 20;


$stmt = $db->prepare("replace into strava_activity (id, user, distance, elevation, type, gear_id, max_speed, avg_watts,avg_heartrate, max_heartrate, avg_speed, starttime, avg_cadence, startpoint, endpoint, polyline, moving_time, elapsed_time, name, commute, athlete_count) values(?,?,?,?,?,?,?,?,?,?,?,?,?,ST_GeomFromText(?,4326),ST_GeomFromText(?,4326),ST_GeomFromText(?,4326),?,?,?,?,?)");

for ($j = 1; $j <= $max_pages; $j++) {
    //setup the request, you can also use CURLOPT_URL
    $ch = curl_init('https://www.strava.com/api/v3/athlete/activities?page='.$j.'&per_page='.$per_page);

    // Returns the data/output as a string instead of raw data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Set your auth headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
    ));

    // get stringified data/output. See CURLOPT_RETURNTRANSFER
    $data = curl_exec($ch);

    // get info about the request
    $info = curl_getinfo($ch);
    // close curl resource to free up system resources
    curl_close($ch);

    $activities = json_decode($data, true);
    try {
        
        //var_dump($activities);


        foreach ($activities as $act) {
            //var_dump($act);
            //echo $act['id'].'<p />';
            $stmt->bindParam(1, $act["id"]);
            $stmt->bindParam(2, $act["athlete"]["id"]);
            $stmt->bindParam(3, $act["distance"]);
            $stmt->bindParam(4, $act["total_elevation_gain"]);
            $stmt->bindParam(5, $act["type"]);
            $stmt->bindParam(6, $act["gear_id"]);
            $stmt->bindParam(7, $act["max_speed"]);
            $stmt->bindParam(8, $act["average_watts"]);
            $stmt->bindParam(9, $act["average_heartrate"]);
            $stmt->bindParam(10, $act["max_heartrate"]);
            $stmt->bindParam(11, $act["average_speed"]);
            $stmt->bindParam(12, $act["start_date"]);
            $stmt->bindParam(13, $act["average_cadence"]);
            
            $start = null;
            if (array_key_exists("start_latlng", $act)) {
                $start="POINT(" . $act["start_latlng"][1] . " " .$act["start_latlng"][0] . ")";
            }
            $stmt->bindParam(14, $start);
            
            $ende = null;
            if (array_key_exists("end_latlng", $act)) {
                $ende = "POINT(" . $act["end_latlng"][1] . " " .$act["end_latlng"][0] . ")";
            }
            //echo $ende;
            $stmt->bindParam(15, $ende);
            
            $poly = null;
            if (array_key_exists("map", $act)) {
                $poly_array = Polyline::decode($act["map"]["summary_polyline"]);
                $poly = "LINESTRING(";
                for($i = 0; $i < count($poly_array); $i += 2) {
                    $poly .= $poly_array[$i+1] . " " . $poly_array[$i] . ",";
                }
                $poly = substr($poly, 0, -1) . ")";
            }
            $stmt->bindParam(16, $poly);
            $stmt->bindParam(17, $act["moving_time"]);
            $stmt->bindParam(18, $act["elapsed_time"]);
            $stmt->bindParam(19, $act["name"]);
            $stmt->bindParam(20, $act["commute"]);
			$stmt->bindParam(21, $act["athlete_count"]);
			
            if (!$stmt->execute()) {
                var_dump ($stmt->errorInfo());
            };
            
        }

        if (count($activities) < 100) {
            break;
        }
        

    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
$db = null;
?>
