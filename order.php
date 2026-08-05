<?php
include_once './my_conf.php';

if (!isset($_POST['name']) || !isset($_POST['phone'])) {
    header("Location: /");
    exit();
}

$name = trim($_POST['name']);
$rawPhone = trim($_POST['phone']);

// 1. Очищаємо від усіх символів, крім цифр
$cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

// 2. Приводим до формату 380XXXXXXXXX
if (strlen($cleanPhone) === 10 && strpos($cleanPhone, '0') === 0) {
    // Якщо ввели 0971234567 -> робимо 380971234567
    $cleanPhone = '38' . $cleanPhone;
} elseif (strlen($cleanPhone) === 9) {
    // Якщо ввели 971234567 -> робимо 380971234567
    $cleanPhone = '380' . $cleanPhone;
}

// 3. Формуємо номер для Drop1 (+380XXXXXXXXX)
$phone = '+' . $cleanPhone;

$headers = array(
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
);

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
header('Location: /success.php?phone=' . urlencode($phone) . '&uid=' . urlencode($drop1_uid) . '&fbpxid=' . urlencode($fbpxidParam));
exit();
?>