<?php
require_once "lib/Mobile_Detect.php";
$params = array();
$params['isWhite'] = $isWhite;
$params['name'] = isset($_POST['name']) ? $_POST['name'] : '';
$params['phone'] = isset($_POST['phone']) ? $_POST['phone'] : '';
$params['email']  = isset($_POST['email']) ? $_POST['email'] : '';

$detect = new Mobile_Detect;
if ($detect->isMobile()) {
    $params['device'] = 'mobile';
} else {
    $params['device'] = 'desktop';
}

if(!isset($_COOKIE['user_id'])) {
    $params['user_id']=substr(md5(mt_rand()), 0, 20);
    setcookie('user_id', $params['user_id'], time() + (86400 * 60), "/"); // 86400 = 1 day
} else {
    $params['user_id']=$_COOKIE['user_id'];
}

$params['ip'] = get_client_ip();
$params['request_uri'] = $_SERVER['REQUEST_URI'];
$params['referer']=isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$params['user_agent']=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$params['accept_language']=isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
$params['http_accept']=isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : ''; 
$params['accept_charset']=isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : ''; 
$params['accept_encoding']=isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : ''; 

$file = __DIR__.'/log/people.log';
date_default_timezone_set('Europe/Rome');
$params['curr_date_time'] = date('Y-m-d H:i:s', time());
$ch = curl_init();
$content = '';
if ($ch) {
	curl_setopt_array($ch, array(
	    CURLOPT_URL => 'http://ip-api.com/json/'.$params['ip'],
//	    CURLOPT_URL => 'https://api.ipdata.co/'.$params['ip'].'/es?api-key=18d299e1455fa3a72aaa59d3a6600c5a122733d3455618fcc26b3a16',
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 30,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	));
    $content = trim(curl_exec($ch));
//var_dump($content);
    curl_close($ch);
}
$arr = json_decode($content, true);
//var_dump($arr);
//foreach ($arr as $key => $value) {
//    $params[$key] = $value;
//}
/*
$params['as'] = $arr['asn'];
$params['city'] = $arr['city'];
$params['country'] = $arr['country_name'];
$params['countryCode'] = $arr['country_code'];
$params['isp'] = $arr['organisation'];
$params['lat'] = $arr['latitude'];
$params['lon'] = $arr['longitude'];
$params['org'] = $arr['organisation'];
$params['regionName'] = $arr['region'];
*/
$params['as'] = $arr['as'];
$params['city'] = $arr['city'];
$params['country'] = $arr['country'];
$params['countryCode'] = $arr['countryCode'];
$params['isp'] = $arr['isp'];
$params['lat'] = $arr['lat'];
$params['lon'] = $arr['lon'];
$params['org'] = $arr['org'];
$params['regionName'] = $arr['regionName'];

if (isset($drop1_uid)) $params['drop1_uid'] = $drop1_uid;
$fp = fopen($file,'a');
fputcsv($fp, $params,'|','"','\\');
fclose($fp);

function get_client_ip()
{
    $ipaddress = '';
/*    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else */ if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = '8.8.8.8';
    return $ipaddress;
}

//file_put_contents($file, json_encode($params), FILE_APPEND | LOCK_EX);
//file_put_contents($file, "\r\n", FILE_APPEND);
