<?php
require_once "class/Database.class.php";

try {
    $db = Database::getConnection();

    $sql = 'SELECT refresh FROM strava_authkey where user = "' . $_GET['user'] . '"';
        foreach ($db->query($sql) as $row) {
			$refresh = $row['refresh'];
        }
        
            $url = 'https://www.strava.com/oauth/token';
    $data = array('client_id' => Config::$strava_client, 'client_secret' => Config::$strava_secret, 'grant_type' => 'refresh_token', 'refresh_token' => $refresh, );
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
    var_dump($answer);

    $token = $answer["access_token"];
    
    $sql = 'UPDATE strava_authkey SET access = "'.
        $answer["access_token"] . '", refresh = "' .
        $answer["refresh_token"] . '", expire = FROM_UNIXTIME(' .
        $answer["expires_at"] . ') WHERE user = ' . $_GET['user'];
        echo $sql;
        $db->exec($sql);
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


