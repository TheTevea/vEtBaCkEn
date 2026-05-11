<?php
$pid = getmypid();

include("function.php");

// Connect To Server
connectDb(HOST_DB, USER_DB, PWD_DB, DATABASE);

// Check Scrip Process
$sqlChk = mysql_query("SELECT is_processing, pid, start FROM `offline_processes` WHERE `name` = 'Transaction';");
$rowChk = mysql_fetch_array($sqlChk);


// Check Sync Proccess
if($rowChk['is_processing'] == 0){
    // Update Proccess Sync
    mysql_query("UPDATE `offline_processes` SET `is_processing`=1, pid = ".$pid.", `start` = '".date("Y-m-d H:i:s")."', `end` = null WHERE  `name` = 'Transaction';");
    // Send
    $sqlSer   = mysql_query("SELECT s_t FROM offline_servers WHERE 1 LIMIT 1");
    $rowSer   = mysql_fetch_array($sqlSer);
    $sqlTotal = mysql_query("SELECT COUNT(id) FROM offline_server_sends WHERE status = 1;");
    $rowTotal = mysql_fetch_array($sqlTotal);
    $sqlSend  = mysql_query("SELECT * FROM offline_server_sends WHERE status = 1 LIMIT 10;");
    if(mysql_num_rows($sqlSend)){
        $token = $rowSer['s_t'];
        while($rowSend = mysql_fetch_array($sqlSend)){
            $synCode  = $rowSend['syn_code'];
            $contents = $rowSend['contents'];
            $total    = $rowTotal[0] - 1;
            $send     = sendTransaction($token, $synCode, $contents, $total);
            if($send['status'] == 1){
                if($send['info']['body']['status'] == 1){
                    mysql_query("UPDATE offline_server_sends SET status = 2 WHERE id = ".$rowSend['id']);
                    // Update Send
                    mysql_query("UPDATE offline_processes SET total_sent = (total_sent + 1) WHERE name = 'Transaction'");
                }
            }
        }
    }
    // Update Proccess Sync
    mysql_query("UPDATE `offline_processes` SET `is_processing`=0, pid = NULL, `end` = '".date("Y-m-d H:i:s")."' WHERE  `name` = 'Transaction';");
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
                    mysql_query("UPDATE `offline_processes` SET `is_processing`=0, pid = NULL, `end` = NULL WHERE  `name` = 'Transaction';");
                }
            }
            exit;
        } else {
            // Update Proccess Sync
            mysql_query("UPDATE `offline_processes` SET `is_processing`=0, pid = NULL, `end` = NULL WHERE  `name` = 'Transaction';");
        }
    }
}
?>

