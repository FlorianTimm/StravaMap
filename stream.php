<?php
include 'Polyline.php';
require 'login.php';

$stmt = $db->prepare("replace into strava_points (activity,sort,point,distance,time,altitude,velocity,heartrate,cadence,watts,moving,grade,temp) values(?,?,ST_GeomFromText(?,4326),?,?,?,?,?,?,?,?,?,?)");
$delete = $db->prepare("delete from strava_points where activity = ?");

$id = 0;


$sql = 'SELECT id FROM strava_activity where user = ' . $user . ' and polyline is not null and not id in (SELECT activity from strava_points) ORDER BY starttime DESC LIMIT 1;';
//echo $sql;
foreach ($db->query($sql) as $row) {
    $id = $row['id'];

    //setup the request, you can also use CURLOPT_URL
    $ch = curl_init('https://www.strava.com/api/v3/activities/' . $id . '/streams?keys=latlng,distance,altitude,time,cadence,heartrate,temp,watts,velocity_smooth,moving,grade_smooth&key_by_type=false');

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

    $stream = json_decode($data, true);
    //var_dump($stream);

    try {

        //var_dump($activities);
        $delete->execute([$id]);


        for ($i = 0; $i < $stream["time"]["original_size"]; $i++) {
            // Strava: latlng,distance,altitude,time,cadence,heartrate,temp,watts,velocity_smooth,moving,grade_smooth
            // DB: activity,sort,point,distance,time,altitude,velocity,heartrate,cadence,watts,moving,grade
            $stmt->bindParam(1, $id);
            $stmt->bindParam(2, $i);
            $start = null;
            if (array_key_exists("latlng", $stream)) {
                $start = "POINT(" . $stream["latlng"]['data'][$i][1] . " " . $stream["latlng"]['data'][$i][0] . ")";
            }
            $stmt->bindParam(3, $start);
            $stmt->bindParam(4, $stream["distance"]['data'][$i]);
            $stmt->bindParam(5, $stream["time"]['data'][$i]);
            $stmt->bindParam(6, $stream["altitude"]['data'][$i]);
            $stmt->bindParam(7, $stream["velocity_smooth"]['data'][$i]);
            $stmt->bindParam(8, $stream["heartrate"]['data'][$i]);
            $stmt->bindParam(9, $stream["cadence"]['data'][$i]);
            $stmt->bindParam(10, $stream["watts"]['data'][$i]);
            $stmt->bindParam(11, $stream["moving"]['data'][$i]);
            $stmt->bindParam(12, $stream["grade_smooth"]['data'][$i]);
            $stmt->bindParam(13, $stream["temp"]['data'][$i]);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
$db = null;
header("Refresh:0");


/*
UPDATE strava_points p
INNER JOIN strava_activity AS a ON p.activity = a.id
SET p.timestamp = DATE_ADD(a.starttime, INTERVAL time second)
-- where clause can go here*/

/*
SET group_concat_max_len=500000;
create table strava_detail1 as SELECT activity, ST_GeomFromText(concat('LINESTRING(',GROUP_CONCAT(c ORDER by sort ASC),')'),4326) polyline FROM (SELECT activity, sort, concat (x(point),' ', y(point)) c from strava_points p order by sort) a GROUP BY activity;

UPDATE strava_activity AS a
INNER JOIN strava_detail1 as d ON d.activity = a.id
SET a.polyline_detail = d.polyline
*/
?>