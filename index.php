<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *, Authorization');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');
    
    $log = true;
    if ($log) {
        $f = fopen("./logs/" . date("Y-m-d H:i:s") . ".txt", 'a');
        fwrite($f, json_encode($_POST));
        
        $f1 = fopen("./logs2/" . date("Y-m-d H:i:s") . ".txt", 'a');
        fwrite($f1, print_r($_POST, true));
        
        // ------------------------------------------------------------------
        
        if (ISSET($_POST['leads']['status'])) {
            
            $f3 = fopen("./logs3/" . date("Y-m-d H:i:s") . ".txt", 'a');
            
            foreach ($_POST['leads']['status'] as $lead) {
                fputs($f3, 'ID сделки     : ' . print_r($lead['id'], true) . PHP_EOL);
                fputs($f3, 'Имя сделки    : ' . print_r($lead['name'], true) . PHP_EOL);
                fputs($f3, 'ID воронки    : ' . print_r($lead['pipeline_id'], true) . PHP_EOL);
                fputs($f3, 'ID пред.этапа : ' . print_r($lead['old_status_id'], true) . PHP_EOL);
                fputs($f3, 'ID тек.этапа  : ' . print_r($lead['status_id'], true) . PHP_EOL);
            }
        }
        
        // file_put_contents('log.txt', print_r($_REQUEST, true));
    }
    
    //-----------------------------------------------------

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