<?php

class AmoClient {
    var $settings;
    
    function __construct($arr) {
        $this->settings = (object) $arr;
    }
    
    // инициировать интеграцию в первый раз по 20 минутному коду
    function initAmoRefCode() {
	    $data = [
            'client_id'     => $this->settings->client_id, // ID интеграции,
            'client_secret' => $this->settings->client_secret, // секретный ключ,
            'grant_type'    => 'authorization_code',
            'code'          => $this->settings->refresh_code, // код авторизации,
            'redirect_uri'  => $this->settings->redirect_uri,
        ];
        
        $res = $this->ApiRequest('/oauth2/access_token', $data, false);
	    if (is_array($res)) {
	        $this->settings->access_token   = $res['access_token'];
	        $this->settings->refresh_token  = $res['refresh_token'];
	        $this->settings->token_type     = $res['token_type'];
	        $this->settings->expires_in     = $res['expires_in'];
	        $this->settings->updated_at     = time();
	        $res['updated_at'] = time();
	    }
	    
	    return $res;
    }
    
    // обновление Токенов по существующему токену обновления
    function updateAmoAccessToken() {
        $data = [
            'client_id'     => $this->settings->client_id, // ID интеграции,,
            'client_secret' => $this->settings->client_secret, // секретный ключ,,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->settings->refresh_token,
            'redirect_uri'  => $this->settings->redirect_uri,
        ];
        
        $res = $this->ApiRequest('/oauth2/access_token', $data, false);
	    if (is_array($res)) {
	        $this->settings->access_token   = $res['access_token'];
	        $this->settings->refresh_token  = $res['refresh_token'];
	        $this->settings->token_type     = $res['token_type'];
	        $this->settings->expires_in     = $res['expires_in'];
	        $this->settings->updated_at     = time();
	        $res['updated_at'] = time();
	    }
	    
	    return $res;
    }
    
    // метод для совершения запросов к API
    function ApiRequest($ApiReq, $data = null, $flag = true, $toArray = true) {
        
        // $ApiReq - запрос к API после домена = '/api/v4/leads'
        // $link1 = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'
        // $link1 = 'https://' . $subdomain . '.amocrm.ru' . $ApiReq;
        
        $link = 'https://' . $this->settings->subdomain . '.amocrm.ru' . $ApiReq;
        
        $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
        /** Устанавливаем необходимые опции для сеанса cURL  */
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
        
        if ($flag) {
            $headers = [
                'Authorization: Bearer ' . $this->settings->access_token
            ];
            curl_setopt($curl,CURLOPT_HTTPHEADER, $headers); 
        }
        
        curl_setopt($curl,CURLOPT_HEADER, false);
        
        if (ISSET($data)) {
            curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        /** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];
        
        try {
            /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        }
        catch(\Exception $e) {
            $f3 = fopen("./err_logs/" . date("Y-m-d H:i:s") . ".txt", 'a');
            fwrite($f3, print_r('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode(), true) . PHP_EOL);
            fwrite($f3, print_r($out, true));
            
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }
        
        /**
         * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
         * нам придётся перевести ответ в формат, понятный PHP
         */
        if ($toArray)
            $out = json_decode($out, true);
        
        return $out;
    }

    // геттер настроек
    function getSettings(){
        return $this->settings;
    }
}

?>
