<?php
include_once './my_conf.php';

if (!isset($_POST['name']) || !isset($_POST['phone'])) {
    header("Location: /");
    exit();
}

$name = trim($_POST['name']);
$rawPhone = trim($_POST['phone']);

// Форматування телефону під вимоги Drop1
$cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
if (strlen($cleanPhone) === 10) {
    $cleanPhone = '38' . $cleanPhone;
}

if (strlen($cleanPhone) === 12) {
    $phone = substr($cleanPhone, 0, 2) . '(' . substr($cleanPhone, 2, 3) . ')' . substr($cleanPhone, 5, 3) . '-' . substr($cleanPhone, 8, 2) . '-' . substr($cleanPhone, 10, 2);
} else {
    $phone = $rawPhone;
}

$headers = array(
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
);

// Передаємо UID у чистому вигляді (з усіма буквами!)
$postfields = array(
    'name'  => $name,
    'phone' => $phone,
    'uid'   => trim($drop1_uid)
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

// Перенаправлення на сторінку дякуємо
$fbpxidParam = isset($fbpxid) ? $fbpxid : '';
header('Location: /success.php?phone=' . urlencode($cleanPhone) . '&uid=' . urlencode($drop1_uid) . '&fbpxid=' . urlencode($fbpxidParam));
exit();
?>
