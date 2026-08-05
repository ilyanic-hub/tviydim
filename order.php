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

// 1. Полная очистка: убираем подчеркивания и всё, что не является цифрой
$cleanPhone = str_replace('_', '', $rawPhone);
$digits = preg_replace('/[^0-9]/', '', $rawPhone);

// 2. Нормализация номера под украинский формат (380XXXXXXXXX - 12 цифр)
if (strlen($digits) === 10 && strpos($digits, '0') === 0) {
    $digits = '38' . $digits;
}

// 3. Защита от отправки битого номера
// Если после очистки получилось меньше 12 цифр — возвращаем на главную
if (strlen($digits) !== 12) {
    header("Location: /?error=invalid_phone");
    exit();
}

// 4. Формирование запроса к Drop1
$headers = [
    "Authorization: Bearer $tokenauthority",
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
];

$postfields = [
    'name'  => $name,
    'phone' => $digits, // <--- ЗДЕСЬ должны быть только цифры "380963254392"
    'uid'   => trim($drop1_uid),
    // Автоматическая передача заказа в колл-центр Drop1 (CPA / Аутсорсинг)
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

// 5. Перенаправление на страницу благодарности
$fbpxidParam = $fbpxid ?? '';
header('Location: /success.php?phone=' . $digits . '&uid=' . urlencode($drop1_uid) . '&fbpxid=' . urlencode($fbpxidParam));
exit();
