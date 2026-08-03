<?php
include_once './my_conf.php';
include_once "./my_stat_0976767.php";
date_default_timezone_set('Europe/Rome');

require_once "./lib/Mobile_Detect.php";
$params=array();

function get_client_ip1()
{
	$ipaddress = '';
    if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = '8.8.8.8';
	return $ipaddress;
}


if (!isset($_POST['name']) || !isset($_POST['phone']))
    if (isset($_SERVER['HTTP_REFERER']))
        header("Location: ".$_SERVER['HTTP_REFERER']);
    else
        header("Location: /");

$logDir = './log/';
if(!is_dir($logDir)) mkdir($logDir) ;

$detect = new Mobile_Detect;
if ( $detect->isMobile() ) {
	$params['isMobile']='MobileDevice';
} else {
	$params['isMobile']='DesktopPC';
}

$params['curr_date_time'] = date('Y-m-d H:i:s', time());
$params['ip'] = get_client_ip1();
$params['name'] =  $_POST['name']; 
$params['phone'] = $_POST['phone'];
$params['drop1_uid'] = $drop1_uid;
$params['referer']=isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$params['user_agent']=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$params['accept_language']=isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
$params['http_accept']=isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : ''; 
$params['accept_charset']=isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : ''; 
$params['accept_encoding']=isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : ''; 

$ch = curl_init();
$content = '';
if ($ch) {
	curl_setopt_array($ch, array(
	    CURLOPT_URL => 'http://ip-api.com/json/'.$params['ip'],
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 30,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	));
    $content = trim(curl_exec($ch));
    curl_close($ch);
}
$arr = json_decode($content, true);
$params['as'] = $arr['as'];
$params['city'] = $arr['city'];
$params['country'] = $arr['country'];
$params['countryCode'] = $arr['countryCode'];
$params['isp'] = $arr['isp'];
$params['lat'] = $arr['lat'];
$params['lon'] = $arr['lon'];
$params['org'] = $arr['org'];
$params['regionName'] = $arr['regionName'];

 
$headers = array(
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
);




if ($geoTelMask == 'ua') preg_match('/\+([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})/', $_POST['phone'], $matches);
else preg_match('/\+([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})/', $_POST['phone'], $matches);

$phone= $matches[1].'('.$matches[2].')'.$matches[3].'-'.$matches[4].'-'.$matches[5];
$postfields = array(
    'name'  => $_POST['name'],
    'phone' => $phone,
    'uid'   => $drop1_uid
);

$curl = curl_init( 'https://drop1.top/api/orders' );
curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
curl_setopt( $curl, CURLOPT_HEADER, false );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $curl, CURLOPT_POST, true );
curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $postfields ) );
$result = curl_exec( $curl );
$res = json_decode($result,true);
if ($result === 0) {
        file_put_contents($logDir . 'orders.log', json_encode($params)."--error-ead-timeout\n", FILE_APPEND);
} else {
#	$httpCode = $res[httpCode];
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($httpCode === 200 or $httpCode === 201) {
	        file_put_contents($logDir . 'orders.log', json_encode($params)."\n", FILE_APPEND);
	} else if ($httpCode === 400 or $httpCode === 401 or $httpCode === 404 or $httpCode === 500 or $httpCode === 503) {
	        file_put_contents($logDir . 'orders.log', json_encode($params).'--error:'.$httpCode." \n", FILE_APPEND);
	} else {
	        file_put_contents($logDir . 'orders.log', json_encode($params)."--error_unknown\n", FILE_APPEND);
	}
}

header('Location: /success.php?phone='.$_POST['phone'].'&uid='.$drop1_uid.'&fbpxid='.$fbpxid);

# 200	Ok	Запрос успешно обработан.
# 201	Created	Запрос успешно выполнен и в результате был создан ресурс.
# 400	Bad Request	Некорректный запрос (сервер не понимает запрос из-за неверного синтаксиса).
# 401	Unauthorized	Не авторизовано (не указан токен или он не верный). Для получения запрашиваемого ответа нужна аутентификация.
# 404	Not Found	Сервер не может найти запрашиваемый ресурс  или к ресурсу нет доступа.
# 500	Internal Server Error	Внутренняя ошибка сервера (в этом случае нужно уведомить администратора Дропплатформы с указанием при каких условиях была получена ошибка).
# 503	Service Unavailable	Запрос сервером не обслуживается, функционал для него не реализован.

# data	Содержит фактическую информацию ответа для ресурса, к которому вы обращаетесь. Это будет либо объект JSON, либо массив JSON в зависимости от того, что возвращает ресурс.
# meta	Содержит другую дополнительную информацию об ответе. В основном, это данные пагинации массива JSON.
# message	Содержит информацию об успешном выполнении создания, обновления или удаления ресурса.
# error	Содержит информацию об ошибке в запросе.
# errors	Содержит информацию нескольких ошибок в запросе. В основном, это ошибки валидации при создании нового ресурса.

/*
{
    "data": [
        {
            // ресурс №1
        },
        {
            // ресурс №2
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 91,
        "next_page_url": "https://drop1.top/api/orders?page=2",
        "path": "https://drop1.top/api/orders",
        "per_page": "2",
        "prev_page_url": null,
        "to": 15,
        "total": 50
    },
}

Ошибки в запросе при создании ресурса:
{
    "errors":{
        "uid": [
	      "UID не найден",
        ],
        "phone": [
	      "Поле phone обязательно для заполнения",
        ],
    },
}
*/
