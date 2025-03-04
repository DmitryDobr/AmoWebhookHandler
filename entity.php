<?php

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
    }

    // перенести из Amo идентификаторы, названия и т.д. всех типов задач в БД
    $result = $amo->ApiRequest('/api/v4/account?with=task_types', null, true, false);
    $db->insertAmoAccountParams($result['_embedded']['task_types'], db_handler::TASK_TYPES_TBL_NAME);

    // перенести из Amo идентификаторы, названия и т.д. всех этапов сделок во всех воронках в БД
    $result = $amo->ApiRequest('/api/v4/leads/pipelines');
    foreach ($result['_embedded']['pipelines'] as $pipeline) {
        $db->insertAmoAccountParams($pipeline['_embedded']['statuses'], DbHandler::LEAD_STATUS_TBL_NAME);
    }
    echo "registered";

?>
