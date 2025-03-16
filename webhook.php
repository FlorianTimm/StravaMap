<?PHP
require 'login.php';

$url = 'https://api.strava.com/api/v3/push_subscriptions';
$data = array(
    'client_id' => $strava_client,
    'client_secret' => $strava_secret,
    'callback_url' => "http://dev.florian-timm.de/strava/webhook_callback.php",
    'verify_token' => 'strava2qgis');
$options = array(
        'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
var_dump($result);

?>
