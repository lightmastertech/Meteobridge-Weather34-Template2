<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//script written by lightmaster
//This script is called from your Meteobridge's Services page. Set up alarms based on the values below.
//In promptu alarms are allowed and the url tag values of ?alarm=ALARM&value=VALUE will be sent to pushbullet
//with a Title of ALARM and the body text will be VALUE. The only special character I've tested and know works
//is '\n' for a new line.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once('settings1.php');

$alarm = $_GET['alarm'];
$value = $_GET['value'];
switch ($alarm) {
    case 'HeatIndex':
        $title = 'Heat Index Alert';
        if ($tempunit == 'F') {
            $body = 'Heat Index is over 100°F';
        } else {
            $body = 'Heat Index is over 38°C';
        }
        break;
    case 'Rain':
        $title = 'Rain Alert';
        $body  = 'Rain has been detected';
        break;
    case 'WindAdvisory':
        $title = 'Wind Alert';
        if ($windunit == 'mph') {
            $body = 'Wind Speed has exceeded 25mph';
        } else if ($windunit == 'km/h') {
            $body = 'Wind Speed has exceeded 40km/h';
        } else if ($windunit == 'm/s') {
            $body = 'Wind Speed has exceeded 11m/s';
        } else if ($windunit == 'kts') {
            $body = 'Wind Speed has exceeded 22kts';
        }
        break;
    case 'HighWindWarning':
        $title = 'High Wind Alert';
        if ($windunit == 'mph') {
            $body = 'Wind Speed has exceeded 39mph';
        } else if ($windunit == 'km/h') {
            $body = 'Wind Speed has exceeded 63km/h';
        } else if ($windunit == 'm/s') {
            $body = 'Wind Speed has exceeded 18m/s';
        } else if ($windunit == 'kts') {
            $body = 'Wind Speed has exceeded 34kts';
        }
        break;
    case 'ExtremeWindWarning':
        $title = 'Extreme Wind Alert';
        if ($windunit == 'mph') {
            $body = 'Wind Speed has exceeded 110mph';
        } else if ($windunit == 'km/h') {
            $body = 'Wind Speed has exceeded 177km/h';
        } else if ($windunit == 'm/s') {
            $body = 'Wind Speed has exceeded 49m/s';
        } else if ($windunit == 'kts') {
            $body = 'Wind Speed has exceeded 100kts';
        }
        break;
    case 'poweroffline':
        $title = 'Power Outage';
        $body  = 'Local power is out, running on battery power. Battery is estimated to last ' . $value . ' minutes.';
        break;
    case 'poweronline':
        $title = 'Power Restored';
        $body  = 'Local power has been restored.';
        break;
    case 'powerdown':
        $title = 'Battery Power Depleted';
        $body  = 'Local battery power has been depleted and Weather Station is shutting down.';
        break;
    case 'twitter':
        //Have not added this to files yet, so it will throw an error if you try it.
        //it's a python script and I haven't worked out a way to auto install the required twitter library.
        $command = escapeshellcmd('/usr/bin/python3 ./scripts/twitterauth.py ' . '"' . $value . '"');
        $output  = shell_exec($command);
        echo $output;
        die('Tweet has been sent.');
    default:
        $title = $alarm;
        $body  = $value;
}

ob_start();
$out = fopen('php://output', 'w');

$pburl     = 'https://api.pushbullet.com/v2/pushes';

$fields = array(
    'type' => 'note',
    'title' => $title,
    'body' => $body,
    'channel_tag' => $pbchannel
);

//url-ify the data for the POST
$fields_string = json_encode($fields);
$headers       = array();
$headers[]     = "Content-Type: application/json";

$ch = curl_init();

curl_setopt_array($ch, array(
    CURLOPT_URL => $pburl,
    CURLOPT_VERBOSE => 1,
    CURLOPT_STDERR => $out,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_USERPWD => "$pbtoken",
    CURLOPT_POST => count($fields),
    CURLOPT_POSTFIELDS => $fields_string,
    CURLOPT_HTTPHEADER => $headers
));

$response = curl_exec($ch);

fclose($out);

////////Uncomment below to see error messages and record curl to ./curl.log

//$debug = ob_get_clean();
//file_put_contents('curl.log', 'LOG: ' . date('c', time()) . PHP_EOL, FILE_APPEND);
//file_put_contents('curl.log', json_encode($headers) . PHP_EOL, FILE_APPEND);
//file_put_contents('curl.log', $fields_string . PHP_EOL, FILE_APPEND);
//file_put_contents('curl.log', $response . PHP_EOL, FILE_APPEND);
//file_put_contents('curl.log', $debug . PHP_EOL . PHP_EOL, FILE_APPEND);
//echo "<pre>". print_r($response, 1)."</pre>";
//echo "<pre>". print_r($debug, 1) . "</pre>";

//don't comment this when your done debugging
curl_close($ch);
?>