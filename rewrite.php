<?php
include 'Polyline.php';
require 'login.php';

$stmt = $db->prepare("update strava_activity set updated = 1 where id = ?");

$sql = "SELECT id from strava_activity where gear_id = 'b4694288' and updated = 0 ORDER BY starttime LIMIT 1";

foreach ($db->query($sql) as $row) {
    $act = $row['id'];

    $ch = curl_init('https://www.strava.com/api/v3/activities/'.$act);

    // Returns the data/output as a string instead of raw data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Set your auth headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token
    ));
    
    $data = array(
        "gear_id" => 'none'
    );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));

    // get stringified data/output. See CURLOPT_RETURNTRANSFER
    $data = curl_exec($ch);

    // get info about the request
    //echo curl_getinfo($ch);
    // close curl resource to free up system resources
    curl_close($ch);
    
    //echo $data;
    
    
    $ch = curl_init('https://www.strava.com/api/v3/activities/'.$act);

    // Returns the data/output as a string instead of raw data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Set your auth headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token
    ));
    
    $data = array(
        "gear_id" => 'b4694288'
    );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));

    // get stringified data/output. See CURLOPT_RETURNTRANSFER
    $data = curl_exec($ch);

    // get info about the request
    //echo curl_getinfo($ch);
    // close curl resource to free up system resources
    curl_close($ch);
    
    //echo $data;
    
    $stmt->execute([$act]);
    
}
$db = null;
header("Refresh:0");
?>
