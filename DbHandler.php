<?php

class DbHandler {
    
    var $con;
    
    const TASK_TYPES_TBL_NAME = 'amo_account_task_types';
    const PIPELINES_TBL_NAME = 'amo_account_pipelines';
    const LEAD_STATUS_TBL_NAME = 'amo_account_lead_status';
    
    function __construct() {
        $host = "localhost";
        $user = "id22368519_admin";
        $pass = "Admin001$";
        $name = "id22368519_amosettings";
    
        $this->con = mysqli_connect($host,$user,$pass,$name);
    }
    
    function getAllValAmo() {
	    $ret = Array();
	    $result = mysqli_query($this->con, 'SELECT * FROM amo_settings');

	    while($row3 = mysqli_fetch_array($result))
	    {
		    $ret[$row3['komm']] = $row3['val'];
	    }
	    return $ret;
    }
    
    function updateAmoVals($arr) {
        if (!is_array($arr))
            return;
            
        foreach ($arr as $key => $value)
        {
            $query = 'UPDATE amo_settings SET val = ? WHERE komm = ?'; 
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('ss', $value , $key);
            $stmt->execute();
        }
    }
    
    function insertAmoAccountParams($array, $table) {
        // (
        // функция для переброса входящего массива сущностей Amo аккаунта в БД
        // task_type, pipelines, lead_status
        //
        // ПРИМЕР:
        // [task_types] => Array
        //        (
        //            [0] => Array
        //                (
        //                    [id] => 1
        //                    [name] => Связаться
        //                    [color] => 
        //                    [icon_id] => 
        //                    [code] => FOLLOW_UP
        //                )
        //            [1] => ...
        //        )
        // В БД заранее заготовлена таблица amo_account_task_types
        // с столбцами идентичными названию полей сущности
        // функция проверяет существование значения поля сущности среди столбцов таблицы
        // и если значение имеется, то добавляет в INSERT запрос соответствующую информацию
        // поля, значений которых нет замещаются автозначениями указанными в БД
        // )
        
        $result = mysqli_query($this->con, 'SHOW COLUMNS FROM ' . $table);
        $ret = array();
        
        while($row3 = mysqli_fetch_array($result)) {
		    array_push($ret, $row3[0]);
	    }
	    
	    $flag = true;
	    foreach ($array as $array_elem) // элемент массива - сущность Amo с параметрами
	    {
	        // SQL запрос по частям
	        $string = 'INSERT INTO ' . $table;
    	    $columns = ' (';
    	    $values = ' VALUES (';
    	    
	        foreach ($array_elem as $key => $value) { // для каждого значения входящей сущности
    	        if ($value && in_array($key,$ret)) // если значение есть и в таблице есть столбец под ключ значения
    	        {
    	            $columns = $columns . $key . ','; // добавить в строку запроса
    	            $values = $values . "'" . $value . "',"; // добавить в строку значений
    	        }
	        }
	        
	        $columns = substr($columns, 0, -1) . ')'; // удалить последнюю запятую
	        $values = substr($values, 0, -1) . ');'; // и закрыть скобку
	        
	        $string = $string . $columns . $values; // соединить в одну строку запрос
	        // INSERT INTO amo_account_task_types (id, name, code) VALUES ('1', 'Связаться', 'FOLLOW_UP');
	        
	        $result = mysqli_query($this->con, $string);
	        $flag = $flag && $result;
	    }
        
        return $flag;
    }
}

?>
