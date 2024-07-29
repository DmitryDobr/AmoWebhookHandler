<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *, Authorization');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: text/html; charset=utf-8');
    
    ignore_user_abort(true);
	// файл, на который можно повесить CRON задачу по ежедневному обновлению токена
    
    function autoloder($class) {
        $file = __DIR__ . "/classes/{$class}.php";
        if(file_exists($file))
            require_once $file;
    }
    spl_autoload_register('autoloder');
    
    $db = new DbHandler();
    $amo = new AmoClient($db->getAllValAmo());
    
    if (!ISSET($amo->getSettings()->access_token)) {
        $db->updateAmoVals($amo->initAmoRefCode());
    }
    else {
        // Если прошло больше времени, чем половина длительности токена
        if (time() - $amo->getSettings()->updated_at >= $amo->getSettings()->expires_in / 2) {
            $db->updateAmoVals($amo->updateAmoAccessToken());
            $f1 = fopen("./Token.txt", 'a');
            fwrite($f1, "token updated at " . date("Y-m-d H:i:s") . PHP_EOL);
            echo "token updated at " . date("Y-m-d H:i:s") . PHP_EOL;
        }
        else {
            $f1 = fopen("./Token.txt", 'a');
            fwrite($f1, "using current token " . date("Y-m-d H:i:s") . PHP_EOL);
            echo "using current token  " . date("Y-m-d H:i:s") . PHP_EOL;
        }
    }
    
    http_response_code(200);
?>