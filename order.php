<?php
include_once './my_conf.php';

// Проверяем наличие данных
if (empty($_POST['name']) || empty($_POST['phone'])) {
    header("Location: /");
    exit();
}

$name = trim($_POST['name']);
$rawPhone = $_POST['phone'] ?? '';

// Логируем сырой номер
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - RAW PHONE: " . $rawPhone . "\n", FILE_APPEND);

// 1. Полная очистка: убираем подчеркивания и всё, кроме цифр
$cleanPhone = str_replace('_', '', $rawPhone);
$digits = preg_replace('/[^0-9]/', '', $cleanPhone);

// 2. Приводим строго к 12 цифрам (380XXXXXXXXX)
if (strlen($digits) === 10 && strpos($digits, '0') === 0) {
    // 0963254392 -> 380963254392
    $digits = '38' . $digits;
} elseif (strlen($digits) === 9) {
    // 963254392 -> 380963254392
    $digits = '380' . $digits;
}

// Защита: номер должен содержать ровно 12 цифр
if (strlen($digits) !== 12) {
    file_put_contents('log.txt', date('Y-m-d H:i:s') . " - ERROR: Invalid phone (" . $digits . ")\n", FILE_APPEND);
    header("Location: /?error=invalid_phone");
    exit();
}

// Логируем подготовленный номер
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - SENDING DIGITS (12): " . $digits . "\n", FILE_APPEND);

// 3. Формирование запроса к API Drop1
$headers = [
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
];

$postfields = [
    'name'            => $name,
    'phone'           => $digits, // Формат 380963254392
    'uid'             => trim($drop1_uid),
    'trade_type'      => 'cpa',   // Явно указываем обработку колл-центром
    'processing_type' => 'cpa'
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

// Логируем ответ CRM
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - DROP1 RESPONSE ($httpCode): " . $result . "\n", FILE_APPEND);

// 4. Перенаправление на страницу благодарности
$fbpxidParam = $fbpxid ?? '';
header('Location: /success.php?phone=' . $digits . '&uid=' . urlencode($drop1_uid) . '&fbpxid=' . urlencode($fbpxidParam));
exit();
