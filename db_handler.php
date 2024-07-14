<?php

class db_handler {
    
    var $con;
    
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
}

?>