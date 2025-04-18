<?PHP
require 'login.php';

$url = 'https://api.strava.com/api/v3/push_subscriptions';
$data = array(
    'client_id' => Config::$strava_client,
    'client_secret' => Config::$strava_secret,
    'callback_url' => Config::$url . "webhook_callback.php",
    'verify_token' => Config::$strava_name,
);
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