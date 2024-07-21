<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *, Authorization');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: text/html; charset=utf-8');
    
    // на входящий запрос надо ответить в течение 2 секунд
    // иначе хук считается невалидным
    ignore_user_abort(true);
    ob_start();
    echo 'true';
    http_response_code(200);
    header('Connection: close');
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
    ob_flush();
    flush(); // отправляем буфер в ответ с кодом 200
    
    // Далее неспеша обрабатываем POST запрос
    //-----------------------------------------------------

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
    }
    

    if (ISSET($_POST['leads']['status'])) {
        
        foreach ($_POST['leads']['status'] as $lead) {
            $data = array(); // массив в котором будут указаны все задачи
            // получить из базы данных список задач на создание
            $array_task = $db->getLeadStatusAutomatization($lead['status_id'], $lead['pipeline_id']);
            
            foreach ($array_task as $task) {
                // собираем данные
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
			
            $amo->ApiRequest('/api/v4/tasks', $data);
        }
    }
    else {
        echo "no WebHook" . PHP_EOL;
    }
	
?>