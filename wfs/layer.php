<?php
require_once "../class/Database.class.php";
$db = Database::getInstance();

$layers = [
    "punkte"=>["strava_points","point"],
    "linien"=>["strava_activity","polyline"],
    "linien_detail"=>["strava_activity","polyline_detail"],
    ];

?>
