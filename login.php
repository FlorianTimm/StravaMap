<?php
require_once "class/Database.class.php";

$token = null;
$user = 0;
$db = Database::getConnection();

if (isset($_COOKIE['strava_authkey'])) {
    $login = true;
    
    try {      
        $sql = 'SELECT access, user FROM strava_authkey where code = "' . $_COOKIE['strava_authkey'] . '"';
        foreach ($db->query($sql) as $row) {
            $token = $row['access'];
			$user = $row['user'];
        }
        
        
    }
    catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>
