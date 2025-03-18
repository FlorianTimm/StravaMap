{"hub.challenge":"<?php echo $_GET['hub_challenge']; ?>"}

<?php
require_once "class/Database.class.php";
$file_handle = fopen('temp.json', 'w');
fwrite($file_handle, file_get_contents('php://input'));
fclose($file_handle);

$data = json_decode(file_get_contents('php://input'), true);

try {
    $db = Database::getInstance();

    if ($data['aspect_type'] == "create") {
        $sql = "INSERT INTO strava_activity (user, id) VALUES (:user, :id)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user', $data['owner_id']);
        $stmt->bindParam(':id', $data['object_id']);
        $stmt->execute();
    } elseif ($data['aspect_type'] == "update") {
        $sql = "UPDATE strava_activity SET ";
        if (isset($data["updates"]["type"]))
            $sql .= "type = :type,";
        if (isset($data["updates"]["title"]))
            $sql .= "name = :name,";
        if (isset($data["updates"]["private"]))
            $sql .= "private = :private,";
        $sql = substr($sql, 0, -1) . " WHERE user = user = :user and id = :id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user', $data['owner_id']);
        $stmt->bindParam(':id', $data['object_id']);
        if (isset($data["updates"]["type"]))
            $stmt->bindParam(':type', $data["updates"]["type"]);
        if (isset($data["updates"]["title"]))
            $stmt->bindParam(':name', $data["updates"]["title"]);
        if (isset($data["updates"]["private"]))
            $stmt->bindParam(':private', $data["updates"]["private"]);
        $stmt->execute();
    } elseif ($data['aspect_type'] == "delete") {
        $sql = "DELETE FROM strava_activity WHERE user = :user and id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user', $data['owner_id']);
        $stmt->bindParam(':id', $data['object_id']);
        $stmt->execute();
    }

    $db = null;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>