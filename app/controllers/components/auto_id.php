<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */
App::import('model', 'ExtendAppModel');

class AutoIdComponent extends Object {

    function generateAutoCode($table, $field, $len, $char, $year = 1, $status = 'is_active = 1') {
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
        $con = '';
        if($year == 1){
            $year =  date('y');
        }else{
            $year = "";
        }
        if($status != ''){
            $con = ' AND '.$status;
        }
        $queryAutoId = mysql_query("SELECT COUNT(" . $field . ") FROM " . $table . " WHERE " . $field . " LIKE '" . $year . $char . "%'{$con}");
        $dataAutoId = mysql_fetch_array($queryAutoId);
        return $year . $char . str_pad($dataAutoId[0] + 1, $len, '0', STR_PAD_LEFT);
    }
    
    function moduleGenerateCode($modCode, $modId, $field, $table, $con){
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
        $total = 0;
        $queryTk = mysql_query("SELECT tmp_count FROM t_tickets WHERE id = ".$modId."");
        if(@mysql_num_rows($queryTk)){
            $dataTk = mysql_fetch_array($queryTk);
            $total  = $dataTk[0];
        }
        if($total > 0){
            $sqCode  = mysql_query("SELECT CONCAT('".$modCode."','',LPAD((".$total."),7,'0'));");
        } else {
            $sqCode  = mysql_query("SELECT CONCAT('".$modCode."','',LPAD(((SELECT count(tmp.".$field.") FROM `".$table."` as tmp WHERE tmp.id < ".$modId." AND ".$con." AND tmp.".$field." LIKE '".$modCode."%') + 1),7,'0'));");
        }
        $code   = mysql_fetch_array($sqCode);
        return $code[0];
    }

}

?>