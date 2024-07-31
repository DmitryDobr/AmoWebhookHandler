<?php
    // переправка информации в БД об автоматизациях (тест)
    
    require_once 'DbHandler.php';
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (ISSET($input['automatization']['leads']['status']))
    {
       $db = new DbHandler();
       $db->insertAmoAccountParams($input['automatization']['leads']['status'], DbHandler::LEAD_STATUS_AUTO_TBL_NAME);
       
       echo "updated";
    }
    
    // входные данные - данные об автозадачах, которые надо поставить
	// в зависимости от воронки и этапа
	// ответственный (в реализации)
	// responsible_user - CURRENT (текущий в сделке)
	//					- PIPELINERESPONSIBLE (главный по воронке)
    // {
    //   "automatization": {
    //       "leads": {
    //         "status": [
    //           {
    //             "id": 68168194,
    //             "pipeline_id": 8367578,
    //             "task_type_id": 3455098,
    //             "task_text": "текст задачи",
    //             "complete_till": 900,
    //             "responsible_user": "CURRENT"
    //           },
    //           ...
    //         ],
    //         "responsible": [...]
    //       }
    //     } 
    //   ]
    // }
?>
