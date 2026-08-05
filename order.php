<?php
include_once './my_conf.php';

// Проверяем наличие данных
if (empty($_POST['name']) || empty($_POST['phone'])) {
    header("Location: /");
    exit();
}

$name = trim($_POST['name']);
$rawPhone = $_POST['phone'] ?? '';
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - RAW PHONE: " . $_POST['phone'] . "\n", FILE_APPEND);

// 1. Очищаем от всего, кроме цифр
$cleanPhone = str_replace('_', '', $rawPhone);
$digits = preg_replace('/[^0-9]/', '', $cleanPhone);

// 2. Приводим к 12 цифрам (380963254392)
if (strlen($digits) === 10 && strpos($digits, '0') === 0) {
    $digits = '38' . $digits;
} elseif (strlen($digits) === 9) {
    $digits = '380' . $digits;
}

// ЛОГИРУЕМ ОЧИЩЕННЫЙ НОМЕР
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - CLEAN DIGITS: " . $digits . "\n", FILE_APPEND);

$headers = [
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
];

$postfields = [
    'name'  => $name,
    'phone' => $digits,
    'uid'   => trim($drop1_uid)
];

$curl = curl_init('https://drop1.top/api/orders');
curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_HEADER         => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postfields),
    CURLOPT_TIMEOUT        => 10
]);

$result = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// ЛОГИРУЕМ ОТВЕТ ОТ DROP1
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - DROP1 RESPONSE ($httpCode): " . $result . "\n", FILE_APPEND);

// 5. Перенаправление на страницу благодарности
$fbpxidParam = $fbpxid ?? '';
header('Location: /success.php?phone=' . $digits . '&uid=' . urlencode($drop1_uid) . '&fbpxid=' . urlencode($fbpxidParam));
exit();
