<?php

class SyncMonitorsController extends AppController {

    var $uses = array('User');
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'SYNC Monitor', 'Dashboard');
    }
    
    function server() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'SYNC Server Monitor', 'Dashboard');
    }
    
    function refresh() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'SYNC Server Monitor', 'Refresh');
    }
    
    function checkConnection(){
        $this->layout = 'ajax';
        $curl  = curl_init();
        curl_setopt($curl, CURLOPT_URL, LINK_SYNC_TIME);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);
        curl_exec($curl);
        $curl_errno = curl_errno($curl);
        curl_close ($curl);
        if ($curl_errno > 0) {
            $return = 0;
        } else {
            $return = 1;
        }
        echo $return;
        exit;
    }
    
    function changeSyncSetting($name, $setting){
        $this->layout = 'ajax';
        mysql_query("UPDATE offline_processes SET sync_by = ".$setting." WHERE name = '".$name."'");
        echo 'Success';
        exit;
    }
    
    function getSettingFromCloud(){
        $this->layout = 'ajax';
    }
    
    function sendTransactionToCloud($status){
        $this->layout = 'ajax';
        $result['status'] = 0;
        $result['pid'] = "";
        // Check Process
        $sqlChk = mysql_query("SELECT * FROM offline_processes WHERE name = 'Transaction' LIMIT 1;");
        if(mysql_num_rows($sqlChk)){
            $rowChk = mysql_fetch_array($sqlChk);
            $pid    = getmypid();
            $ipAddr = $this->Helper->getIpAddress();
            $sqlSer = mysql_query("SELECT s_t FROM offline_servers WHERE 1 LIMIT 1");
            $rowSer = mysql_fetch_array($sqlSer);
            $result['pid']   = $rowChk['id'];
            $result['token'] = $rowSer['s_t'];
            $result['total'] = 0;
            if($rowChk['is_processing'] == '0' || $status == 1){
                $sqlTotal = mysql_query("SELECT COUNT(id) FROM offline_server_sends WHERE status = 1;");
                $rowTotal = mysql_fetch_array($sqlTotal);
                $sqlSyn   = mysql_query("SELECT * FROM offline_server_sends WHERE status = 1 LIMIT 10;");
                if(mysql_num_rows($sqlSyn)){
                    $result['status'] = 1;
                    $i = 0;
                    while($rowSyn = mysql_fetch_array($sqlSyn)){
                        $result['data'][$i]['id'] = $rowSyn['id'];
                        $result['data'][$i]['synCode'] = $rowSyn['syn_code'];
                        $result['data'][$i]['content'] = $rowSyn['contents'];
                        $i++;
                    }
                }
                $result['total'] = $rowTotal[0];
                // Update Process
                mysql_query("UPDATE offline_processes SET is_processing = 1, pid = ".$pid.", ip = '".$ipAddr."', start = now() WHERE id = ".$rowChk['id']);
            } else {
                if(!empty($rowChk['start']) && $rowChk['start'] != '0000-00-00 00:00:00'){
                    // Compare Proccess more then 3 minute
                    $timeNow   = strtotime(date("Y-m-d H:i:s")); 
                    $timeStart = strtotime($rowChk['start']) + 300;
                    if($timeNow > $timeStart){
                        // Update Process
                        mysql_query("UPDATE offline_processes SET is_processing = 0, pid = NULL, ip = NULL, start = NULL WHERE id = ".$rowChk['id']);
                    }
                } else {
                    // Update Process
                    mysql_query("UPDATE offline_processes SET is_processing = 0, pid = NULL, ip = NULL, start = NULL WHERE id = ".$rowChk['id']);
                }
            }
        }
        echo json_encode($result);
        exit;
    }
    
    function updateEndProcess($id){
        $this->layout = 'ajax';
        // Update Process
        mysql_query("UPDATE offline_processes SET is_processing = 0, end = now() WHERE id = ".$id);
        exit;
    }
    
    function updateTransaction($id){
        $this->layout = 'ajax';
        mysql_query("UPDATE offline_server_sends SET status = 2 WHERE id = ".$id);
        // Update Send
        mysql_query("UPDATE offline_processes SET total_sent = (total_sent + 1) WHERE name = 'Transaction'");
        echo 'Success';
        exit;
    }
    
    function getSettingRequest($status){
        $this->layout = 'ajax';
        $result['request']  = "";
        $result['response'] = "";
        $result['token']    = "";
        $result['pid']      = "";
        // Check Process
        $sqlChk = mysql_query("SELECT * FROM offline_processes WHERE name = 'Setting' LIMIT 1;");
        if(mysql_num_rows($sqlChk)){
            $rowChk = mysql_fetch_array($sqlChk);
            $pid    = getmypid();
            $ipAddr = $this->Helper->getIpAddress();
            $result['pid'] = $rowChk['id'];
            if($rowChk['is_processing'] == '0' || $status == 1){
                // Update Process
                mysql_query("UPDATE offline_processes SET is_processing = 1, pid = ".$pid.", ip = '".$ipAddr."', start = now() WHERE id = ".$rowChk['id']);
                $sqlSer = mysql_query("SELECT s_t FROM offline_servers WHERE 1 LIMIT 1");
                $rowSer = mysql_fetch_array($sqlSer);
                $result['token'] = $rowSer[0];
                $sqlReq = mysql_query("SELECT * FROM offline_server_request_codes WHERE status IN (1, 2)");
                if(mysql_num_rows($sqlReq)){
                    while($rowReq = mysql_fetch_array($sqlReq)){
                        if($rowReq['status'] == 1){
                            $result['request']  = $rowReq['code'];
                        } else {
                            $result['response'] = $rowReq['code'];
                        }
                    }
                }
                if($result['request'] == ""){
                    $request = SERVER_ID.$this->Helper->generateRandomString(6);
                    mysql_query("INSERT INTO offline_server_request_codes (offline_server_id, code, created, status) VALUES (1, '".$request."', now(), 1);");
                    $result['request']  = $request;
                }
            } else {
                if(!empty($rowChk['start']) && $rowChk['start'] != '0000-00-00 00:00:00'){
                    // Compare Proccess more then 3 minute
                    $timeNow   = strtotime(date("Y-m-d H:i:s")); 
                    $timeStart = strtotime($rowChk['start']) + 300;
                    if($timeNow > $timeStart){
                        // Update Process
                        mysql_query("UPDATE offline_processes SET is_processing = 0, pid = NULL, ip = NULL, start = NULL WHERE id = ".$rowChk['id']);
                    }
                } else {
                    // Update Process
                    mysql_query("UPDATE offline_processes SET is_processing = 0, pid = NULL, ip = NULL, start = NULL WHERE id = ".$rowChk['id']);
                }
            }
        }
        echo json_encode($result);
        exit;
    }
    
    function receiveSetting(){
        $this->layout = 'ajax';
        $receive = $this->data['receive'];
        if($this->data['total'] > 30){
            $total = $this->data['total'] - 30;
        } else {
            $total = 0;
        }
        $totalGet = 0;
        $datas   = json_decode($receive, true);
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
        // Update Will Receive
        mysql_query("UPDATE `offline_processes` SET `total_will_receive` = ".$total.", total_received = (total_received + ".$totalGet.") WHERE  `name` = 'Setting';");
        // Move Receive
        shell_exec("php ".SCRIPT_PATH."move.php");
        exit;
    }
    
    function updateSettingRequest($request){
        $this->layout = 'ajax';
        mysql_query("UPDATE offline_server_request_codes SET status = 2 WHERE code = '".$request."';");
        exit;
    }
    
    function deleteSettingRequest($request){
        $this->layout = 'ajax';
        mysql_query("DELETE FROM offline_server_request_codes WHERE code = '".$request."';");
        exit;
    }

}

?>