{"hub.challenge":"<?php echo $_GET['hub_challenge']; ?>"}

<?php 
require_once "class/Database.class.php";
$file_handle = fopen('my_filename.json', 'w');
fwrite($file_handle, file_get_contents('php://input'));
fclose($file_handle);

$data = json_decode(file_get_contents('php://input'), true);
$sql = "";

if ($data['aspect_type'] == "create") {
    $sql = "INSERT INTO strava_activity (user, id) VALUES (".$data['owner_id'].",".$data['object_id'].")";
} elseif ($data['aspect_type'] == "update") {
    $sql = "UPDATE strava_activity SET ";
    if (isset($data["updates"]["type"]))
        $sql .= "type = '". $data["updates"]["type"] ."',";
    if (isset($data["updates"]["title"]))
        $sql .= "name = '". $data["updates"]["title"] ."',";
    if (isset($data["updates"]["private"]))
        $sql .= "private = '". $data["updates"]["private"] ."',";
    $sql = substr($sql, 0, -1) . " WHERE user = ".$data['owner_id']." and id = ".$data['object_id'];
} elseif ($data['aspect_type'] == "delete") {
    $sql = "DELETE FROM strava_activity WHERE user = ".$data['owner_id']." and id = ".$data['object_id'];
}

$db = Database::getConnection();
    
try {
    $db->exec($sql);
    $db = null;
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
