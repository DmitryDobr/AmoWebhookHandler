<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *, Authorization');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');

    require_once 'db_handler.php';
    require_once 'AmoClient.php';
    
    $db = new db_handler();
    $amo = new AmoClient($db->getAllValAmo());
    
    if (!ISSET($amo->getSettings()->access_token)) {
        $db->updateAmoVals($amo->initAmoRefCode());
    }
    else {
        // Если прошло больше времени, чем половина длительности токена
        if (time() - $amo->getSettings()->updated_at >= $amo->getSettings()->expires_in / 2) {
            $db->updateAmoVals($amo->updateAmoAccessToken());
            echo "update access token" . PHP_EOL;
        }
        else {
            echo "using current access token" . PHP_EOL;
        }
    }
    
    //print_r($amo->getSettings());
    print_r($amo->ApiRequest('/api/v4/account?with=task_types', null, true, false));

    
    http_response_code(200);
?>
