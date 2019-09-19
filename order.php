<?php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'TD.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'helpers.php');

$userId = 0;
$apiKey = 'asfsa924.............fasf8902';
$flowId = 1234;

// Собираем данные лида
$clientName = $_POST['name']; // Вместо name - подставить имя поля для имени клиента в форме
$clientPhone = $_POST['phone']; // Вместо phone - подставить имя поля для телефона клиента в форме
$clientComment = isset($_POST['comment']) ? $_POST['comment'] : null; // Комментарий к заказу

$clientReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null; // Получаем страницу откуда пришёл пользователь
$clientUseragent = $_SERVER['HTTP_USER_AGENT']; // Получаем строку юзерагента пользователя
$clientIp = getUserIP(); // Получаем IP клиента
$clientGeo = getUserCountryCode($clientIp);

// Дополнительные данные, тут в качестве примера мы будем считать, что доп данные вы передаёте через скрытые поля формы
$subId1 = isset($_POST['sub_id_1']) ? $_POST['sub_id_1'] : null;
$subId2 = isset($_POST['sub_id_2']) ? $_POST['sub_id_2'] : null;
$subId3 = isset($_POST['sub_id_3']) ? $_POST['sub_id_3'] : null;
$subId4 = isset($_POST['sub_id_4']) ? $_POST['sub_id_4'] : null;
$subId5 = isset($_POST['sub_id_5']) ? $_POST['sub_id_5'] : null;

$utmSource = isset($_POST['utm_source']) ? $_POST['utm_source'] : null;
$utmCampaign = isset($_POST['utm_campaign']) ? $_POST['utm_campaign'] : null;
$utmMedium = isset($_POST['utm_medium']) ? $_POST['utm_medium'] : null;
$utmContent = isset($_POST['utm_content']) ? $_POST['utm_content'] : null;
$utmTerm = isset($_POST['utm_term']) ? $_POST['utm_term'] : null;

$td = new TD($userId, $apiKey);

/* Вернёт массив с прототипом данных для создания лида */
$leadDataPrototype = $td->getLeadDataPrototype();

$leadDataPrototype['geo'] = $clientGeo;
$leadDataPrototype['ip'] = $clientIp;
$leadDataPrototype['name'] = $clientName;
$leadDataPrototype['phone'] = $clientPhone;
$leadDataPrototype['user_agent'] = $clientUseragent;
$leadDataPrototype['referer'] = $clientReferer;
$leadDataPrototype['comment'] = $clientComment;
$leadDataPrototype['data1'] = $subId1;
$leadDataPrototype['data2'] = $subId2;
$leadDataPrototype['data3'] = $subId3;
$leadDataPrototype['data4'] = $subId4;
$leadDataPrototype['data5'] = $subId5;
$leadDataPrototype['utm_source'] = $utmSource;
$leadDataPrototype['utm_campaign'] = $utmCampaign;
$leadDataPrototype['utm_medium'] = $utmMedium;
$leadDataPrototype['utm_content'] = $utmContent;
$leadDataPrototype['utm_term'] = $utmTerm;


/* Создаст лид в указанном потоке, вернёт id лида */
try {
    $newLeadId = $td->createLeadByFlow($flowId, $leadDataPrototype);
    // Тут у нас лид успешно создался и у нас есть его ид в $newLeadId
    // Вы можете тут добавить код для отправки лида в трекер или что-то еще
    
    header('Location: ./success.php?order=' . $newLeadId); // Перенаправление на сакцесс страницу
    exit;
} catch (\Exception $e) {
    file_put_contents(
        __DIR__ . DIRECTORY_SEPARATOR . 'log.txt',
        sprintf("[%s] Error: %s\n\tData: %s\n", date('Y-m-d H:i:s'), $e->getMessage(), print_r($leadDataPrototype, 1)),
        FILE_APPEND
    );
    // Произошла ощибка, сообщение об ошибке доступно в : $e->getMessage();
    header('Location: ./error.php'); // Перенаправление на сакцесс страницу
    exit;
}
