<?php
/**
 *
 * @author WitER <dimka.witer@yandex.ru>
 *
 */
/**
 * Class LeadR
 * Реализация API методов Lead-R CPA для вебмастеров
 */
class TD
{
    /**
     * @var string API-ключ
     */
    private $apiKey;
    /**
     * @var integer User-ID
     */
    private $userId;

    private $leadCreateApiUrl = 'http://trustlandings.teledirekt.ru/order/api.php';
    private $apiUrl = 'http://cpa.teledirekt.ru/api3/';

    public function __construct($userId, $apiKey)
    {
        $this->userId = $userId;
        $this->apiKey = $apiKey;
    }
    /**
     * Получить список офферов
     * @return array
     */
    public function getOffers()
    {
        $response =  $this->request('get_offers');
        return $response->response;
    }
    /**
     * Получить список лидов
     * @param array $leadsId По ID лидов - [1, 2, 3, 4]
     * @param bool $dateFrom Начиная с даты
     * @param bool $dateTo Заканчивая датой
     * @return array
     */
    public function getLeads($leadsId = array(), $dateFrom = false, $dateTo = false)
    {
        if (!$dateFrom) {
            $dateFrom = date('Y-m-d', strtotime('-7 days'));
        }
        if (!$dateTo) {
            $dateTo = date('Y-m-d');
        }
        $payload = array(
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        );
        if (!empty($leadsId)) {
            $payload['ids'] = implode(',', $leadsId);
            unset($payload['date_from'], $payload['date_to']);
        }
        $response = $this->request('get_leads', $payload);
        return $response->response;
    }
    /**
     * Возвращает подготовленный массив с полями, передающимися по API
     * @return array
     */
    public function getLeadDataPrototype()
    {
        return array(
            'geo' => null,
            'ip' => null,
            'name' => null,
            'phone' => null,
            'web_id' => null,
            'user_agent' => null,
            'referer' => null,
            'timezone' => null,
            'comment' => null,
            'data1' => null,
            'data2' => null,
            'data3' => null,
            'data4' => null,
            'data5' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_content' => null,
            'utm_term' => null,
        );
    }
    /**
     * Создаёт лид по ID потока
     * @param integer $flowId ID потока
     * @param array $payload Массив данных (пример - @getLeadDataPrototype)
     * @return integer ID созданного лида
     */
    public function createLeadByFlow($flowId, $payload)
    {
        $payload['flow_id'] = $flowId;
        
        $payload = array_filter($payload);
        $response = $this->request('add_lead', $payload);
        return $response->response->id;
    }
    /**
     * Создаёт лид по ID оффера
     * @param integer $offerId ID оффера
     * @param array $payload Массив данных (пример - @getLeadDataPrototype)
     * @return integer ID созданного лида
     */
    public function createLeadByOffer($offerId, $payload)
    {
        $payload['offer_id'] = $offerId;
        $payload = array_filter($payload);
        $response = $this->request('add_lead', $payload);
        return $response->response->id;
    }
    private function request($method, $payload = array())
    {
        $payload['user_id'] = $this->userId;
        $payload['api_key'] = $this->apiKey;
        $url = $this->apiUrl . $method;
        if ($method == 'add_lead') {
            $url = $this->leadCreateApiUrl;
        }
        $payload = http_build_query($payload);
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HEADER => FALSE,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $payload,
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        if (!$result || empty($result)) {
            throw new \Exception('Request filed');
        }
        if ($result->status_code != 200) {
            throw new \Exception($result->status_text, $result->status_code);
        }
        return $result;
    }
}
