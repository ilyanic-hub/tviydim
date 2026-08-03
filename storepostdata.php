<?php
/* <div style="width:80%">
<pre>
<?php
var_dump($_POST);
?>
</pre>
</div>
<div style="width:80%">
<pre>
<?php
var_dump(getallheaders());
?>
</pre>
</div>
*/
?>
<?php
date_default_timezone_set('Europe/Rome');
$accepted_header=getallheaders();
$headers = array(
#    'Authorization: '.str_replace( chr( 194 ) . chr( 160 ), ' ', $accepted_header['Authorization'] ),
    'Authorization:'.chr(32).substr($accepted_header['Authorization'], 0, 6).chr(32).substr($accepted_header['Authorization'], -60),
    'Accept:'.chr(32).$accepted_header['Accept'],
    'Content-Type:'.chr(32).$accepted_header['Content-Type']
);


if ( preg_match('/^38\(([0-9]{3})\)([0-9]{3})\-([0-9]{2})\-([0-9]{2})$/', $_POST['phone']) || preg_match('/^7\(([0-9]{3})\)([0-9]{3})\-([0-9]{2})\-([0-9]{2})$/', $_POST['phone'])  ) {
	$phone = $_POST['phone'];
} elseif ( preg_match('/^\+([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/', $_POST['phone'], $matches) ) {
	$phone= $matches[1].'('.$matches[2].')'.$matches[3].'-'.$matches[4].'-'.$matches[5];
#	echo $phone;
#	echo '- UkrPhone!';
} elseif ( preg_match('/^\+([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/', $_POST['phone'], $matches) ) {
	$phone= $matches[1].'('.$matches[2].')'.$matches[3].'-'.$matches[4].'-'.$matches[5];
#	echo $phone;
#	echo '- RusPhone!';
} else {
	$phone = '38(000)000-'.substr('0'.rand(1,99), -2).'-'.substr('0'.rand(1,99), -2);
#	echo $phone;
#	echo '- Need send to comments!';
}

$postfields = array(
//    'name'  => $_POST['name'],
    'name'  => str_replace( array("_", ">", "<", "\"", "&", ":", ";"), array("", "", "", "", "", "", ""), $_POST['name'] ),
    'phone' => $phone,
    'comment' => $_POST['phone'],
    'uid'   => $_POST['uid']

);

/* ?>

<div style="width:80%">
<pre>
<?php
var_dump($headers);
?>
</pre>
</div>
<div style="width:80%">
<pre>
<?php
var_dump($postfields);
?>
</pre>
</div>


<?php
*/

 
$curl = curl_init( 'https://drop1.top/api/orders' );
curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
curl_setopt( $curl, CURLOPT_HEADER, false );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $curl, CURLOPT_POST, true );
curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $postfields ) );
$json = curl_exec( $curl );
$httpCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
curl_close( $curl );

$answer=json_decode( $json, true );
$answer['httpCode'] = $httpCode;
$answer['headers']=getallheaders();
//$answer['postdata']=$_POST;
$answer['nameedited'] = $postfields;
$out_data = json_encode( $answer, true ); 
file_put_contents('/var/www/stas_ftp/data/www/sellstuff.su/landings/ua/dont-delete-this-is-api-transformer/orders.log', $out_data." \n", FILE_APPEND);
//file_put_contents('/var/www/stas_ftp/data/www/sellstuff.su/landings/ua/dont-delete-this-is-api-transformer/orders.log'. $accepted_header['Authorization']." \n", FILE_APPEND);
echo $out_data; 


