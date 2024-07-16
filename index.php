<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *, Authorization');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');

    require_once 'db_handler.php';
    require_once 'AmoClient.php';
    
    $db = new DbHandler();
    $amo = new AmoClient($db->getAllValAmo());
    
    if (!ISSET($amo->getSettings()->access_token)) {
        $db->updateAmoVals($amo->initAmoRefCode());
    }
    else {
        // Если прошло больше времени, чем половина длительности токена
        if (time() - $amo->getSettings()->updated_at >= $amo->getSettings()->expires_in / 2) {
            $db->updateAmoVals($amo->updateAmoAccessToken());
            echo "token updated at " . date("Y-m-d H:i:s") . PHP_EOL;
        }
        else {
            echo "using current access token" . PHP_EOL;
        }
    }
    
	
    if (ISSET($_POST['leads']['status'])) { // если пришла информация о смене этапа сделки
        $data = array();
        
        foreach ($_POST['leads']['status'] as $lead) {
            if ($lead['status_id'] == 68168198) { // простое условие на определение этапа сделки
                $dat = [
                    'task_type_id' => 3454974, // идентификатор типа задачи
                    'text' => "Текст задачи",
                    'complete_till' => time() + 172800, // текущее время + время на выполнение в сек.
                    'entity_id' => (int) $lead['id'], // прикручиваем задачу к сделке, в которой обновился статус
                    'entity_type' => 'leads',
                    'responsible_user_id' => (int) $lead['responsible_user_id'], // прикручиваем задачу к текущему менеджеру сделки
                ];
                array_push($data, $dat);
            }
        }
        
        $amo->ApiRequest('/api/v4/tasks', $data);
    }

    
    http_response_code(200);
?>
