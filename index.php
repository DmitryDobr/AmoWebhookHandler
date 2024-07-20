<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *, Authorization');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');
    
    require_once 'DbHandler.php';
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
    
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        
        if (ISSET($_POST['leads']['status'])) {
            $data = array();
            
            foreach ($_POST['leads']['status'] as $lead) {
                
                $array_task = $db->getLeadStatusAutomatization($lead['status_id'], $lead['pipeline_id']);
                
                foreach ($array_task as $task) {
                   
                    $dat = [
                        'task_type_id' => (int) $task['task_type_id'],
                        'text' => $task['task_text'],
                        'complete_till' => time() + (int) $task['complete_till'],
                        'entity_id' => (int) $lead['id'],
                        'entity_type' => 'leads',
                        'responsible_user_id' => (int) $lead['responsible_user_id'],
                    ];
                    array_push($data, $dat);
                }
            }
            
            $amo->ApiRequest('/api/v4/tasks', $data);
        }
    }
    else {
        echo "GET request is no WebHook" . PHP_EOL;
    }
    
    
    http_response_code(200);
?>