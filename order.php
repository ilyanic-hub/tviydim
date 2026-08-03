<?php
include_once './my_conf.php';

if (!isset($_POST['name']) || !isset($_POST['phone'])) {
    die('Помилка: Не отримано дані з форми (name або phone)');
}

$name = trim($_POST['name']);
$rawPhone = trim($_POST['phone']);

// Форматування телефону
$cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
if (strlen($cleanPhone) === 10) {
    $cleanPhone = '38' . $cleanPhone;
}
if (strlen($cleanPhone) === 12) {
    $phone = substr($cleanPhone, 0, 2) . '(' . substr($cleanPhone, 2, 3) . ')' . substr($cleanPhone, 5, 3) . '-' . substr($cleanPhone, 8, 2) . '-' . substr($cleanPhone, 10, 2);
} else {
    $phone = $rawPhone;
}

$clean_uid = preg_replace('/[^0-9]/', '', $drop1_uid);

$headers = array(
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
);

$postfields = array(
    'name'  => $name,
    'phone' => $phone,
    'uid'   => $clean_uid
);

$curl = curl_init('https://drop1.top/api/orders');
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postfields));

$result = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// ВІДЛАГОДЖЕННЯ: Виводимо все на екран
echo "<h2>Результат відправки в Drop1:</h2>";
echo "<p><b>HTTP Code:</b> " . $httpCode . "</p>";
echo "<p><b>Відповідь сервера:</b> " . htmlspecialchars($result) . "</p>";
echo "<hr>";
echo "<h3>Дані, які відправлялися:</h3>";
echo "<pre>";
print_r($postfields);
echo "</pre>";
exit();
?>
