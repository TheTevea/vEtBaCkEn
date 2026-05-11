<?php
include("function.php");

// Connect To Server
connectDb(HOST_DB, USER_DB, PWD_DB, DATABASE);

$sqlReceive = mysql_query("SELECT * FROM offline_server_receives WHERE status = 1 ORDER BY id ASC");
if(mysql_num_rows($sqlReceive)){
    while($rowReceive = mysql_fetch_array($sqlReceive)){
        $sqlSer = mysql_query("SELECT * FROM offline_servers WHERE id = ".$rowReceive['offline_server_id']." LIMIT 1");
        $rowSer = mysql_fetch_array($sqlSer);
        $keyDec = md5($rowSer['key'].$rowSer['s_t']);
        // Check Exist Code
        $accessSave = true;
        $contents = $rowReceive['contents'];
        $content  = decode($contents, $keyDec);
        // Convert to Original And Excecute SQL Comment
        $dataRecords = json_decode($content, TRUE);
        $convertJson = false;
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $convertJson = true;
            break;
            case JSON_ERROR_DEPTH:
                echo ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                echo ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                echo ' - Unknown error';
            break;
        }
        if($convertJson == true){
            foreach($dataRecords AS $datas){
                $fields       = array();
                $condition    = '';
                $dbName       = '';
                $convertToSQL = 0;
                $sqlCmt  = '';
                foreach($datas AS $key => $val){
                    if($key == 'actodo' && $val == 'is'){
                        $convertToSQL = 1;
                    } else if($key == 'actodo' && $val == 'ut'){
                        $convertToSQL = 2;
                    } else if($key == 'actodo' && $val == 'dt'){
                        $convertToSQL = 3;
                    }
                    if($key == 'con' && ($convertToSQL == 2 || $convertToSQL == 3)){
                        $condition = html_entity_decode($val, ENT_QUOTES);
                    }
                    if($key == 'dbtodo'){
                        $dbName = $val;
                    }
                    if($key != 'dbtodo' && $key != 'actodo' && $key != 'con'){
                        $sqlVal = html_entity_decode($val, ENT_QUOTES);
                        $fields[$key] = $sqlVal;
                    }
                }
                if($convertToSQL == 1){
                    $sqlCmt = generateSqlInsertSync($dbName, $fields);
                } else if($convertToSQL == 2){
                    $sqlCmt = generateSqlUpdateSync($dbName, $fields, $condition, "");
                } else if($convertToSQL == 3){
                    $sqlCmt = generateSqlDeleteSync($dbName, $condition);
                }
                if($sqlCmt != ''){
                    $result = mysql_query($sqlCmt);
                } else {
                    $accessSave = false;
                }
            }

            // Update Receive
            mysql_query("UPDATE offline_server_receives SET contents = NULL, status= 2 WHERE id = ".$rowReceive['id']);
            // Move Receive
            mysql_query("INSERT INTO `offline_server_dones` (`syn_code`, `contents`, `created`, `modified`) VALUES ('".$rowReceive['syn_code']."', '".$rowReceive['contents']."', '".$rowReceive['created']."', now());");
        }
    }
}