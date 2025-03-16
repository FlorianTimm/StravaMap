<?php
require_once "Database.class.php";

class Login {

    private $token = null;
    private $user = 0;
    private $name = "";

    private $db = Database::getConnection();

    __construct () {
        // Neu eingeloggt
        if (isset($_GET['code'])) {

            $url = 'https://www.strava.com/oauth/token';
            $data = array('client_id' => $strava_client, 'client_secret' => $strava_secret, 'code' => $_GET['code'], 'grant_type' => 'authorization_code');
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

            $this->token = $answer["access_token"];

            try {      
                $stmt = $this->db->prepare('REPLACE INTO strava_authkey (user, code, access, refresh, expire) VALUES(:user, :code, :access, :refresh, FROM_UNIXTIME(:expire))');
                $stmt->bindParam(':user', $answer["athlete"]["id"]);
                $stmt->bindParam(':code', $_GET['code']);
                $stmt->bindParam(':acces', $answer["access_token"]);
                $stmt->bindParam(':refresh', $answer["refresh_token"]);
                $stmt->bindParam(':expire', $answer["expires_at"]);
                $db->execute();
                
                $sql = 'REPLACE INTO strava_user (id, firstname, lastname, city) VALUES(:id, :firstname, :lastname, :city)');
                $stmt->bindParam(':id', $answer["athlete"]["id"]);
                $stmt->bindParam(':firstname', $answer["athlete"]["firstname"]);
                $stmt->bindParam(':lastname', $answer["athlete"]["lastname"]);
                $stmt->bindParam(':city', $answer["athlete"]["city"]);
                $db->execute();
                
                $this->name = $answer["athlete"]["firstname"] . ' ' . $answer["athlete"]["lastname"];
                $this->user = $answer["athlete"]["id"];
            }
            catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
            setcookie("strava_authkey", $code, time()+$answer["expires_in"]);
            $login = true;
            
        // Bereits eingeloggt
        } elseif (isset($_COOKIE['strava_authkey'])) {
            $login = true;
            
            try {     
                $stmt = $this->db->prepare("SELECT firstname, lastname, user, expire, refresh, access FROM strava_authkey a left join strava_user u on a.user = u.id where code = :code LIMIT 1");
                $stmt->bindParam(':code', $_COOKIE['strava_authkey']);
                foreach ($stmt->execute() as $row) {
                    $this->token = $row['access'];
                    $this->user = $row['user'];
                    $this->name = $row["firstname"] . ' ' . $row["lastname"];
                }
                
                
            }
            catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
    }
}
?>
