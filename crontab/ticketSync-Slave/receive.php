<?php
$pid = getmypid();

include("function.php");

// Connect To Server
connectDb(HOST_DB, USER_DB, PWD_DB, DATABASE);

// Check Scrip Process
$sqlChk = mysql_query("SELECT is_processing, pid, start FROM `offline_processes` WHERE `name` = 'Setting';");
$rowChk = mysql_fetch_array($sqlChk);


// Check Sync Proccess
if($rowChk['is_processing'] == 0){
    // Update Proccess Sync
    mysql_query("UPDATE `offline_processes` SET `is_processing`=1, pid = ".$pid.", `start` = '".date("Y-m-d H:i:s")."', `end` = null WHERE  `name` = 'Setting';");
    $request  = "";
    $response = "NULL";
    $sqlSer = mysql_query("SELECT s_t FROM offline_servers WHERE 1 LIMIT 1");
    $rowSer = mysql_fetch_array($sqlSer);
    $token  = $rowSer[0];
    $sqlReq = mysql_query("SELECT * FROM offline_server_request_codes WHERE status IN (1, 2)");
    if(mysql_num_rows($sqlReq)){
        while($rowReq = mysql_fetch_array($sqlReq)){
            if($rowReq['status'] == 1){
                $request  = $rowReq['code'];
            } else {
                $response = $rowReq['code'];
            }
        }
    }
    if($request == ""){
        $requestCode = SERVER_ID.generateRandomString(6);
        mysql_query("INSERT INTO offline_server_request_codes (offline_server_id, code, created, status) VALUES (1, '".$requestCode."', now(), 1);");
        $request  = $requestCode;
    }
    // Get Setting from API
    $getSetting = receiveSetting($token, $request, $response);
    if($getSetting['status'] == 1){
        if($getSetting['info']['header']['result'] == true){
            if($getSetting['info']['body']['status'] == 1){
                if($getSetting['info']['body']['total'] > 30){
                    $willReceive = $getSetting['info']['body']['total'] - 30;
                } else {
                    $willReceive = 0;
                }
                $datas = $getSetting['info']['body']['settings'];
                $totalGet = 0;
                foreach($datas AS $data){
                    $synCode = $data['synCode'];
                    $content = $data['contents'];
                    // Check Existed
                    $sqlExt = mysql_query("SELECT id FROM offline_server_receives WHERE syn_code = '".$synCode."' LIMIT 1");
                    if(!mysql_num_rows($sqlExt)){
                        mysql_query("INSERT INTO `offline_server_receives` (`offline_server_id`, `syn_code`, `contents`, `created`, `status`) 
                                     VALUES (1, '".$synCode."', '".$content."', now(), 1);");
                        $totalGet++;
                    }
                }
                mysql_query("UPDATE `offline_processes` SET `total_will_receive`=".$willReceive.", total_received = (total_received + ".$totalGet.") WHERE  `name` = 'Setting';");
            }
            // Update Request Code
            mysql_query("UPDATE offline_server_request_codes SET status = 2 WHERE code = '".$request."';");
            // Delete Response
            if($response != '' && $response != 'NULL'){
                mysql_query("DELETE FROM offline_server_request_codes WHERE code = '".$response."';");
            }
        }
    }
    // Update Proccess Sync
    mysql_query("UPDATE `offline_processes` SET `is_processing`=0, pid = NULL, `end` = '".date("Y-m-d H:i:s")."' WHERE  `name` = 'Setting';");
} else {
    if(!empty($rowChk['start'])){
        exec("ps -p ".$rowChk['pid'], $output);
        if (count($output) > 1) {
            if(!empty($rowChk['start']) && $rowChk['start'] != '0000-00-00 00:00:00'){
                // Compare Proccess more then 5 minutes
                $timeNow   = strtotime(date("Y-m-d H:i:s")); 
                $timeStart = strtotime($rowChk['start']) + 500;
                if($timeNow > $timeStart){
                    exec("kill -9 ".$rowChk['pid']);
                    // Update Proccess Sync
                    mysql_query("UPDATE `offline_processes` SET `is_processing`=0, pid = NULL, `end` = NULL WHERE  `name` = 'Setting';");
                }
            }
            exit;
        } else {
            // Update Proccess Sync
            mysql_query("UPDATE `offline_processes` SET `is_processing`=0, pid = NULL, `end` = NULL WHERE  `name` = 'Setting';");
        }
    }
}
?>

