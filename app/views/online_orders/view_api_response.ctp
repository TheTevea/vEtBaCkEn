<?php
$sqlResp = mysql_query("SELECT * FROM online_orders WHERE id = ".$id);
if(mysql_num_rows($sqlResp)){
    $rowResp = mysql_fetch_array($sqlResp);
    if(!empty($rowResp['bank_api_response'])){
        $json = str_replace('"{', '{', $rowResp['bank_api_response']);
        $json = str_replace('}"', '}', $json);
        $result = json_decode($json, true);
        debug($result);
    }
}
?>