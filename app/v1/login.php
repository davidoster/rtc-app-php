<?php

include_once 'aliyun-php-sdk-core/Config.php';
Autoloader::addAutoloadPath("aliyun-php-sdk-rtc");
use rtc\Request\V20180111 as RTC;

class ChannelAuth
{
	public $app_id;
	public $channel_id;
	public $nonce;
	public $timestamp;
	public $channel_key;
	// Debug Request ID
	public $request_id;
}

function CreateChannel($app_id, $channel_id, $region_id, $access_key_id, $access_key_secret)
{
	$iClientProfile = DefaultProfile::getProfile($region_id, $access_key_id, $access_key_secret);
	$client = new DefaultAcsClient($iClientProfile);

	$request = new RTC\CreateChannelRequest();
	$request->setAppId($app_id);
	$request->setChannelId($channel_id);
	$response = $client->getAcsResponse($request);

	$auth = new ChannelAuth();
	$auth->app_id = $app_id;
	$auth->channel_id = $channel_id;
	$auth->channel_key = $response->ChannelKey;
	$auth->nonce = $response->Nonce;
	$auth->timestamp = $response->Timestamp;
	$auth->request_id = $response->RequestId;
	return $auth;
}

// Allow Cross-Origin Resource Sharing (CORS).
if ($_SERVER['HTTP_ORIGIN'] != '') {
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET,POST,HEAD,PUT,DELETE,OPTIONS");
	header("Access-Control-Expose-Headers: Server,Range,Content-Length,Content-Range");
	header("Access-Control-Allow-Headers: Origin,Range,Accept-Encoding,Referer,Cache-Control,X-Proxy-Authorization,X-Requested-With,Content-Type");
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	die();
}

header("Content-Type: application/json");

$channel_id = $_REQUEST['room'];
$user = $_REQUEST['user'];
$password = $_REQUEST['password'];

include_once 'Config.php';
$channel_url = $app_id . '/' . $channel_id;

function ReadObjects()
{
	$file = fopen('db.txt', 'r');
    if (!$file) {
    	die('Open file failed');
    }

    $filesize = filesize('db.txt');
    if ($filesize > 0) {
        $content = fread($file, filesize('db.txt'));
    }
    fclose($file);

    if ($filesize > 0) {
        return json_decode($content);
    }
    return (object)[];
}

function WriteObject($channels)
{
	$file = fopen('db.txt', 'w');
    if (!$file) {
    	die('Open file failed');
    }

	$content = json_encode($channels);
    fwrite($file, $content);

    fclose($file);
}

$channels = ReadObjects();
var_dump($channels);

if (!isset($channels->{$channel_url})) {
	$auth = CreateChannel($app_id, $channel_id, $region_id, $access_key_id, $access_key_secret);
	$channels->{$channel_url} = $auth;
	WriteObject($channels);
} else {
	$auth = $channels->{$channel_url};
}
?>