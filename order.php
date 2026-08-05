<?php
include_once './my_conf.php';

if (!isset($_POST['name']) || !isset($_POST['phone'])) {
    header("Location: /");
    exit();
}

$name = trim($_POST['name']);
$rawPhone = trim($_POST['phone']);

// 1. Очищаємо рядок: залишаємо ТІЛЬКИ цифри (видаляємо +, пробіли, дужки)
$digits = preg_replace('/[^0-9]/', '', $rawPhone);

// 2. Приводимо до стандартного 12-значного формату (380XXXXXXXXX)
if (strlen($digits) === 10 && strpos($digits, '0') === 0) {
    // Якщо ввели 096325... -> робимо 38096325...
    $digits = '38' . $digits;
} elseif (strlen($digits) === 9) {
    // Якщо ввели 96325... -> робимо 38096325...
    $digits = '380' . $digits;
}

// 3. Формуємо номер з ОДНИМ плюсом спереду для Drop1 (+380XXXXXXXXX)
$phone = '+' . $digits;

$headers = array(
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
);

// Передаємо замовлення в Drop1
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