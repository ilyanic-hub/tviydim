<?php
include_once './my_conf.php';

// Проверяем наличие данных
if (empty($_POST['name']) || empty($_POST['phone'])) {
    header("Location: /");
    exit();
}

$name = trim($_POST['name']);
$rawPhone = $_POST['phone'] ?? '';

// Логируем сырой номер из формы
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - RAW PHONE: " . $rawPhone . "\n", FILE_APPEND);

// 1. Очищаем от всех нецифровых символов (удаляем +, (), -, spaces и _)
$cleanPhone = str_replace('_', '', $rawPhone);
$digits = preg_replace('/[^0-9]/', '', $cleanPhone);

// 2. Приводим строго к 10 цифрам (формат 0XXXXXXXXX для API Drop1)
if (strlen($digits) === 12 && substr($digits, 0, 2) === '38') {
    // 380963254392 -> 0963254392
    $digits = substr($digits, 2);
} elseif (strlen($digits) === 9) {
    // 963254392 -> 0963254392
    $digits = '0' . $digits;
}

// Защита: если номер не содержит 10 цифр после очистки
if (strlen($digits) !== 10) {
    file_put_contents('log.txt', date('Y-m-d H:i:s') . " - ERROR: Invalid phone length (" . $digits . ")\n", FILE_APPEND);
    header("Location: /?error=invalid_phone");
    exit();
}

// Логируем очищенный 10-значный номер
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - CLEAN DIGITS (10): " . $digits . "\n", FILE_APPEND);

// 3. Формирование и отправка запроса к Drop1
$headers = [
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
];

$postfields = [
    'name'  => $name,
    'phone' => $digits, // Передаем ровно 10 цифр (например, 0963254392)
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

// Логируем ответ CRM
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - DROP1 RESPONSE ($httpCode): " . $result . "\n", FILE_APPEND);

// 4. Перенаправление на страницу благодарности
$fbpxidParam = $fbpxid ?? '';
header('Location: /success.php?phone=' . $digits . '&uid=' . urlencode($drop1_uid) . '&fbpxid=' . urlencode($fbpxidParam));
exit();
