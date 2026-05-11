<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */

date_default_timezone_set('Asia/Phnom_Penh');

class HelperComponent extends Object {
    var $components = array('AutoId');
    
    function getModuleCode($modCode, $modId, $field, $table, $con){
        return $this->AutoId->moduleGenerateCode($modCode, $modId, $field, $table, $con);
    }

    function checkDouplicate($strCol, $strTbl, $val, $condition="is_active = 1") {
        $exist  = false;
        $strSQL = '';
        $strSQL = "SELECT Count(" . $strCol . ") as Found FROM " . $strTbl . " WHERE " . $condition . " AND " . $strCol . "= '" . mysql_real_escape_string($val) . "' ";
        $result = mysql_query($strSQL) or die(mysql_error());
        $found = mysql_result($result, 0, 0);
        if ($found == 0):
            $exist = false;
        else:
            $exist = true;
        endif;
        return $exist;
    }

    function checkDouplicateEdit($strCol, $strTbl, $idCompare, $val, $condition="is_active = 1") {
        $exist = false;
        $strSQL = '';
        $strSQL = "SELECT Count(" . $strCol . ") as Found FROM " . $strTbl . " WHERE " . $condition . " AND id <> $idCompare AND " . $strCol . "= '" . mysql_real_escape_string($val) . "' ";
        $result = mysql_query($strSQL) or die(mysql_error());
        $found = mysql_result($result, 0, 0);
        if ($found == 0):
            $exist = false;
        else:
            $exist = true;
        endif;
        return $exist;
    }

    function genRandomString() {
        $character_set_array = array();
        $character_set_array[] = array('count' => 7, 'characters' => 'abcdefghijklmnopqrstuvwxyz');
        $character_set_array[] = array('count' => 1, 'characters' => '0123456789');
        $temp_array = array();
        foreach ($character_set_array as $character_set) {
            for ($i = 0; $i < $character_set['count']; $i++) {
                $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
            }
        }
        shuffle($temp_array);
        return implode('', $temp_array);
    }

    function switchDate($date, $format) {
        $list = explode($format, $date);
        return $list[2] . "-" . $list[1] . "-" . $list[0];
    }

    function fileRename($oldName, $newName) {
        $ext = split("\.", $oldName);
        return $newName . '.' . $ext[sizeof($ext) - 1];
    }

    function fileGetExtension($fileName) {
        $ext = split("\.", strtolower($fileName));
        return $ext[sizeof($ext) - 1];
    }

    function dateConvert($rawDate) {
        if (($rawDate == '00/00/0000 00:00:00') || ($rawDate == ''))
            return false;

        $table_date = split('/', $rawDate);
        $day = $table_date[sizeof($table_date) - 3];
        $month = $table_date[sizeof($table_date) - 2];

        $year = $table_date[sizeof($table_date) - 1];

        $str_date = $year . '-' . $month . '-' . $day;
        return ($str_date);
    }

    function inventoryValuation() {
        $queryTrack = mysql_query("SELECT id,val FROM tracks WHERE id=1");
        $dataTrack = mysql_fetch_array($queryTrack);
        if ($dataTrack['val'] == 1) {
            mysql_query("UPDATE tracks SET val=0 WHERE id=1");
            $acc_total_cost = array();
            $acc_total_qty = array();
            $old_avg_cost = array();
            $queryPid = mysql_query("SELECT DISTINCT pid FROM inventory_valuations WHERE is_active=1");
            while ($dataPid = mysql_fetch_array($queryPid)) {
                $queryInit = mysql_query("  SELECT pid,on_hand,avg_cost,asset_value FROM inventory_valuations
                                            WHERE is_active=1
                                                AND date<(SELECT MIN(date) FROM inventory_valuations WHERE is_active=1 AND pid='" . $dataPid[0] . "' AND (DATE(created)=DATE(now()) OR (date_edited IS NOT NULL AND DATE(date_edited)=DATE(now()))))
                                                AND pid='" . $dataPid[0] . "'
                                            ORDER BY date DESC,created DESC,id DESC LIMIT 1");
                if (mysql_num_rows($queryInit)) {
                    $dataInit = mysql_fetch_array($queryInit);
                    $pid = "pid" . $dataInit['pid'];
                    $acc_total_cost[$pid] = $dataInit['asset_value'];
                    $acc_total_qty[$pid] = $dataInit['on_hand'];
                    $old_avg_cost[$pid] = $dataInit['avg_cost'];
                }
                $query = mysql_query("  SELECT * FROM inventory_valuations
                                        WHERE is_active=1
                                            AND date>=(SELECT MIN(date) FROM inventory_valuations WHERE is_active=1 AND pid='" . $dataPid[0] . "' AND (DATE(created)=DATE(now()) OR (date_edited IS NOT NULL AND DATE(date_edited)=DATE(now()))))
                                            AND pid='" . $dataPid[0] . "'
                                        ORDER BY date,created,id");
                while ($data = mysql_fetch_array($query)) {
                    $pid = "pid" . $data['pid'];
                    if (!isset($acc_total_cost[$pid])) {
                        $acc_total_cost[$pid] = 0;
                    }
                    if (!isset($acc_total_qty[$pid])) {
                        $acc_total_qty[$pid] = 0;
                    }
                    if (!isset($old_avg_cost[$pid])) {
                        $queryDefaultCost = mysql_query("SELECT default_cost FROM products WHERE id=" . $data['pid']);
                        $dataDefaultCost = mysql_fetch_array($queryDefaultCost);
                        $old_avg_cost[$pid] = $dataDefaultCost['default_cost'];
                    }
                    if ($data['is_adjust_value'] == 1) {
                        $acc_total_cost[$pid] = $data['asset_value'];
                        $acc_total_qty[$pid] += $data['qty'];
                        mysql_query("   UPDATE inventory_valuations SET
                                            on_hand='" . $acc_total_qty[$pid] . "',
                                            cost='" . @($acc_total_cost[$pid] / $acc_total_qty[$pid]) . "',
                                            avg_cost='" . @($acc_total_cost[$pid] / $acc_total_qty[$pid]) . "',
                                            asset_value='" . $acc_total_cost[$pid] . "'
                                        WHERE id=" . $data['id']) or die(mysql_error());
                    } else if ($data['is_var_cost'] == 1) {
                        $acc_total_cost[$pid] += $data['qty'] * $old_avg_cost[$pid];
                        $acc_total_qty[$pid] += $data['qty'];
                        mysql_query("   UPDATE inventory_valuations SET
                                            on_hand='" . $acc_total_qty[$pid] . "',
                                            cost='" . $old_avg_cost[$pid] . "',
                                            avg_cost='" . $old_avg_cost[$pid] . "',
                                            asset_value='" . $acc_total_cost[$pid] . "'
                                        WHERE id=" . $data['id']) or die(mysql_error());
                        mysql_query("UPDATE general_ledger_details SET credit='" . abs($data['qty'] * $old_avg_cost[$pid]) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=0 AND credit!='" . abs($data['qty'] * $old_avg_cost[$pid]) . "'") or die(mysql_error());
                        if ($data['price'] != '') {
                            $cogs = abs($data['qty'] * $data['price']) - abs($data['qty'] * $old_avg_cost[$pid]);
                            if ($cogs > 0) {
                                mysql_query("UPDATE general_ledger_details SET credit='" . abs($cogs) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND credit!='" . abs($cogs) . "'") or die(mysql_error());
                            } else if ($cogs < 0) {
                                mysql_query("UPDATE general_ledger_details SET debit='" . abs($cogs) . "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit!='" . abs($cogs) . "'") or die(mysql_error());
                            }
                        } else {
                            mysql_query("UPDATE general_ledger_details SET debit='" . abs($data['qty'] * $old_avg_cost[$pid]) . "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit!='" . abs($data['qty'] * $old_avg_cost[$pid]) . "'") or die(mysql_error());
                        }
                    } else {
                        $acc_total_cost[$pid] += $data['qty'] * $data['cost'];
                        $acc_total_qty[$pid] += $data['qty'];
                        mysql_query("   UPDATE inventory_valuations SET
                                            on_hand='" . $acc_total_qty[$pid] . "',
                                            avg_cost='" . @($acc_total_cost[$pid] / $acc_total_qty[$pid]) . "',
                                            asset_value='" . $acc_total_cost[$pid] . "'
                                        WHERE id=" . $data['id']) or die(mysql_error());
                    }
                    if ($acc_total_cost[$pid] != 0 || $acc_total_qty[$pid] != 0) {
                        $old_avg_cost[$pid] = $acc_total_cost[$pid] / $acc_total_qty[$pid];
                    }
                }
            }
        }
    }

    function inventoryValuationFull() {
        $acc_total_cost = array();
        $acc_total_qty = array();
        $old_avg_cost = array();
        $query = mysql_query("SELECT * FROM inventory_valuations WHERE is_active=1 ORDER BY pid,date,created,id");
        while ($data = mysql_fetch_array($query)) {
            $pid = "pid" . $data['pid'];
            if (!isset($acc_total_cost[$pid])) {
                $acc_total_cost[$pid] = 0;
            }
            if (!isset($acc_total_qty[$pid])) {
                $acc_total_qty[$pid] = 0;
            }
            if (!isset($old_avg_cost[$pid])) {
                $queryDefaultCost = mysql_query("SELECT default_cost FROM products WHERE id=" . $data['pid']);
                $dataDefaultCost = mysql_fetch_array($queryDefaultCost);
                $old_avg_cost[$pid] = $dataDefaultCost['default_cost'];
            }
            if ($data['is_adjust_value'] == 1) {
                $acc_total_cost[$pid] = $data['asset_value'];
                $acc_total_qty[$pid] += $data['qty'];
                mysql_query("   UPDATE inventory_valuations SET
                                    on_hand='" . $acc_total_qty[$pid] . "',
                                    cost='" . @($acc_total_cost[$pid] / $acc_total_qty[$pid]) . "',
                                    avg_cost='" . @($acc_total_cost[$pid] / $acc_total_qty[$pid]) . "',
                                    asset_value='" . $acc_total_cost[$pid] . "'
                                WHERE id=" . $data['id']) or die(mysql_error());
            } else if ($data['is_var_cost'] == 1) {
                $acc_total_cost[$pid] += $data['qty'] * $old_avg_cost[$pid];
                $acc_total_qty[$pid] += $data['qty'];
                mysql_query("   UPDATE inventory_valuations SET
                                    on_hand='" . $acc_total_qty[$pid] . "',
                                    cost='" . $old_avg_cost[$pid] . "',
                                    avg_cost='" . $old_avg_cost[$pid] . "',
                                    asset_value='" . $acc_total_cost[$pid] . "'
                                WHERE id=" . $data['id']) or die(mysql_error());
                mysql_query("UPDATE general_ledger_details SET credit='" . abs($data['qty'] * $old_avg_cost[$pid]) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=0 AND credit!='" . abs($data['qty'] * $old_avg_cost[$pid]) . "'") or die(mysql_error());
                if ($data['price'] != '') {
                    $cogs = abs($data['qty'] * $data['price']) - abs($data['qty'] * $old_avg_cost[$pid]);
                    if ($cogs > 0) {
                        mysql_query("UPDATE general_ledger_details SET credit='" . abs($cogs) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND credit!='" . abs($cogs) . "'") or die(mysql_error());
                    } else if ($cogs < 0) {
                        mysql_query("UPDATE general_ledger_details SET debit='" . abs($cogs) . "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit!='" . abs($cogs) . "'") or die(mysql_error());
                    }
                } else {
                    mysql_query("UPDATE general_ledger_details SET debit='" . abs($data['qty'] * $old_avg_cost[$pid]) . "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit!='" . abs($data['qty'] * $old_avg_cost[$pid]) . "'") or die(mysql_error());
                }
            } else {
                $acc_total_cost[$pid] += $data['qty'] * $data['cost'];
                $acc_total_qty[$pid] += $data['qty'];
                mysql_query("   UPDATE inventory_valuations SET
                                    on_hand='" . $acc_total_qty[$pid] . "',
                                    avg_cost='" . @($acc_total_cost[$pid] / $acc_total_qty[$pid]) . "',
                                    asset_value='" . $acc_total_cost[$pid] . "'
                                WHERE id=" . $data['id']) or die(mysql_error());
            }
            if ($acc_total_cost[$pid] != 0 || $acc_total_qty[$pid] != 0) {
                $old_avg_cost[$pid] = $acc_total_cost[$pid] / $acc_total_qty[$pid];
            }
        }
    }
    
    function replaceThousand($value){
        $value = str_replace(",","",$value);
        return $value;
    }
    
    function getAutoGeneratePointOfSaleCode($string = null) {
        return $this->AutoId->generateAutoCodeSale('sales_orders', 'so_code', 6, $string);
    }

    function checkWaitingNumberToday() {
        $now = date("Y-m-d");
        $query = "SELECT id FROM waiting_numbers WHERE DATE(created) = DATE('$now')";
        $result = mysql_query($query);
        if (mysql_num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    function showTotalQty($total_qty, $labelMainUom, $smallUom, $smallUomLabel) {
        $totalRemain = "";
        $totalMain = (int) ($total_qty / $smallUom);
        $checkRemain = (int) ($total_qty % $smallUom);
        if ($checkRemain > 0) {
            $totalRemain = ($total_qty - (int) ($totalMain * $smallUom)) . " " . $smallUomLabel;
        }
        return $totalMain . " " . $labelMainUom . "<br/> " . $totalRemain;
    }
    
    function checkDouplicateEditOther($strCol, $strTbl, $idCompare = "", $val = "", $condition="is_active = 1", $join = "") {
        $exist = false;
        $strSQL = '';
        $strSQL = "SELECT Count(" . $strCol . ") as Found FROM " . $strTbl . " " . $join . " WHERE " . $idCompare . " " . $strCol . "= '" . mysql_real_escape_string($val) . "' AND " . $condition;
        $result = mysql_query($strSQL) or die(mysql_error());
        $found = mysql_result($result, 0, 0);
        if ($found == 0):
            $exist = false;
        else:
            $exist = true;
        endif;
        return $exist;
    }

    function checkDouplicateSku($strCol, $strTbl, $val, $condition="is_active = 1", $join = "") {
        $exist = false;
        $strSQL = '';
        $strSQL = "SELECT Count(" . $strCol . ") as Found FROM " . $strTbl . " " . $join . " WHERE (" . $strCol . "= '" . mysql_real_escape_string($val) . "' " . $condition;
        $result = mysql_query($strSQL) or die(mysql_error());
        $found = mysql_result($result, 0, 0);
        if ($found == 0):
            $exist = false;
        else:
            $exist = true;
        endif;
        return $exist;
    }

    function setExpiredDate($table, $field, $id) {
        $sql = mysql_query("SELECT * FROM " . $table . " WHERE is_set_expired = 1 AND " . $field . " = " . $id . " ORDER BY indexs ASC");
        if (@mysql_num_rows($sql)) {
            while ($row = mysql_fetch_array($sql)) {
                $indexs = 0;
                $totalOrder = $row['qty'] * -1;                
                $sqlSumExp = mysql_query("SELECT SUM(qty) as total_qty, date_expired FROM " . $table . " WHERE indexs < " . $row['indexs'] . " AND product_id = " . $row['product_id'] . " GROUP BY product_id, date_expired ORDER BY date_expired ASC");
                if (@mysql_num_rows($sqlSumExp)) {
                    while ($r = mysql_fetch_array($sqlSumExp)) {
                        $totalQty = $r['total_qty'];
                        if ($totalQty >= $totalOrder) {
                            if ($indexs > 0) {
                                mysql_query("INSERT INTO `" . $table . "` (`indexs`, `" . $field . "`, `type`, `product_id`, `location_id`, `qty`, `unit_cost`, `date`, `date_expired`, `created`, `created_by`, `modified`) 
                                             VALUES ('" . $row['id'] . "." . $indexs . "', '" . $id . "', '" . $row['type'] . "', " . $row['product_id'] . ", " . $row['location_id'] . ", '-" . $totalOrder . "', " . $row['unit_cost'] . ", '" . $row['date'] . "', '" . $r['date_expired'] . "', '" . $row['created'] . "', " . $row['created_by'] . ", '" . date("Y-m-d H:i:s") . "');");
                                $totalOrder = 0;
                            } else {
                                mysql_query("UPDATE `" . $table . "` SET `date_expired`='" . $r['date_expired'] . "', `is_set_expired` = 0 WHERE  `id`=" . $row['id'] . ";");
                                break;
                            }
                        } else {
                            if ($indexs > 0) {
                                mysql_query("INSERT INTO `" . $table . "` (`indexs`, `" . $field . "`, `type`, `product_id`, `location_id`, `qty`, `unit_cost`, `date`, `date_expired`, `created`, `created_by`, `modified`) 
												 VALUES ('" . $row['id'] . "." . $indexs . "', '" . $id . "', '" . $row['type'] . "', " . $row['product_id'] . ", " . $row['location_id'] . ", '-" . $totalQty . "', " . $row['unit_cost'] . ", '" . $row['date'] . "', '" . $r['date_expired'] . "', '" . $row['created'] . "', " . $row['created_by'] . ", '" . date("Y-m-d H:i:s") . "');");
                            } else {
                                mysql_query("UPDATE `" . $table . "` SET `qty` = '-" . $totalQty . "',`date_expired`='" . $r['date_expired'] . "', `is_set_expired` = 0 WHERE  `id`=" . $row['id'] . ";");
                            }
                            $totalOrder = $totalOrder - $totalQty;
                        }
                        if ($totalOrder == 0) {
                            break;
                        }
                        $indexs++;
                    }
                }
            }
        }
    }
    
    function roundPrice($total_price){
        $numberCon  = ($total_price * 10);
        $number     = explode(".", $numberCon);
        if(!empty($number[1])){
            if ($number[1] <= 5 && $number[1] > 0) {
                $total_price = ($number[0] . ".5");
            } elseif ($number[1] > 5 && $number[1] <= 9) {
                $total_price = ($number[0] + 1);
            } else {
                $total_price = $number[0];
            }
            $total_price = $total_price / 10;
        }else{
            $total_price = $total_price;
        }
        $total_price = str_replace(",", "", $total_price);
        return $total_price;
    }
    
    function updateReceive($order_id) {
        $sql = mysql_query("SELECT * FROM `purchase_receives` WHERE purchase_order_id = " . $order_id . " and status = 1");
        if (@$num = mysql_num_rows($sql)) {
            return 1;
        } else {
            return 0;
        }
    }
    
    function preventInput($input){
        $result = mysql_real_escape_string(strip_tags($input));
        return $result;
    }
    
    function checkAccess($userId = null, $controller = null, $action = null) {
        if (!$controller) {
            $controller = $this->params['controller'];
        }
        if (!$action) {
            $action = $this->params['action'];
        }

        $accessRules = $_SESSION['accessRules'];
        $queryUserGroup = mysql_query("SELECT group_id FROM user_groups WHERE user_id=" . $userId);
        while ($dataUserGroup = mysql_fetch_array($queryUserGroup)) {
            if (!empty($accessRules[$dataUserGroup['group_id']][$controller]) && (is_array($accessRules[$dataUserGroup['group_id']][$controller]) && in_array($action, $accessRules[$dataUserGroup['group_id']][$controller]))) {
                return true;
            }
        }
        return false;
    }
    
    function dateShort($rawDate, $format='d/m/Y') {
        if (($rawDate == '0000-00-00 00:00:00') || ($rawDate == ''))
            return false;
        $year = substr($rawDate, 0, 4);
        $month = (int) substr($rawDate, 5, 2);
        $day = (int) substr($rawDate, 8, 2);
        $hour = (int) substr($rawDate, 11, 2);
        $minute = (int) substr($rawDate, 14, 2);
        $second = (int) substr($rawDate, 17, 2);

        if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
            return date($format, mktime($hour, $minute, $second, $month, $day, $year));
        } else {
            return ereg_replace('2037' . '$', $year, date($format, mktime($hour, $minute, $second, $month, $day, 2037)));
        }
    }
    
    function saveUserActivity($userId, $type, $action, $from = 0, $to = 0){
        $dateNow = date("Y-m-d H:i:s");
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $browser = $this->getBrowser($useragent);
        $os      = $this->getOS($useragent);
        $ipAddr  = $this->getIpAddress();
        mysql_query("INSERT INTO `user_activity_logs` (`user_id`, `type`, `tbl_from_id`, `tbl_to_id`, `action`, `browser`, `operating_system`, `ip`, `created`) "
                  . "VALUES (".$userId.", '".$type."', '".$from."', '".$to."', '".$action."', '".$browser."', '".$os."', '".$ipAddr."', '".$dateNow."');");
    }
    
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function generateRandomKey($length = 10) {
        $characters = '!@#$()abcdeGHIJKLMNmnopqrstuvwxyzABCDEFfghijklOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function sendFileToSync($inputSync, $user){
        if(!empty($inputSync) && $inputSync != ''){
            // Save Sync As File
            $dateSend = date("Y-m-d H:i:s");
            $synCode  = md5(rand().strtotime(date("Y-m-d H:i:s")));
            if(SERVER_TYPE == "1"){ // Master send to Slave
                if($user['User']['offline_project_id'] != ''){
                    $mainServerRT = "";
                    $mainKey = "";
                    $sqlServer = mysql_query("SELECT * FROM offline_servers WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status = 2 ORDER BY is_main DESC");
                    while($rowServer = mysql_fetch_array($sqlServer)){
                        if($rowServer['is_main'] == 1){
                            $mainServerRT = $rowServer['s_t'];
                            $mainKey  = $rowServer['key'];
                        } else {
                            $key      = md5($mainKey.$rowServer['s_t']);
                            $json     = json_encode($inputSync);
                            $content  = $this->encode($json, $key);
                            $contentSize = strlen($content);
                            mysql_query("INSERT INTO `offline_data_activities` (`rest_from`, `rest_to`, `syn_code`, `contents`, `created`) VALUES ('".$mainServerRT."', '".$rowServer['s_t']."', '".$synCode."', '".mysql_real_escape_string(str_replace("'", "&#39;", $content))."', '".$dateSend."');");
                            // Total Send to server
                            mysql_query("UPDATE offline_servers SET total_send = (total_send + 1) WHERE id = ".$rowServer['id']);
                        }
                    }
                }
            } else { // Slave send to Master
                $sqlServer = mysql_query("SELECT * FROM offline_servers WHERE 1 LIMIT 1;");
                $rowServer = mysql_fetch_array($sqlServer);
                $key       = md5($rowServer['key'].$rowServer['s_t']);
                $json      = json_encode($inputSync);
                $content   = $this->encode($json, $key);
                $contentSize = strlen($content);
                mysql_query("INSERT INTO `offline_server_sends` (`syn_code`, `contents`, `content_size`, `created`) VALUES ('".$synCode."', '".mysql_real_escape_string(str_replace("'", "&#39;", $content))."', '".$contentSize."', '".$dateSend."');");
                // Total Send to server
                mysql_query("UPDATE offline_processes SET total_will_send = (total_will_send + 1) WHERE name = 'Transaction'");
            }
        }
    }
    
    function convertRestToEncrypt($contents){
        $result = array();
        if(!empty($contents)){
            $i = 0;
            foreach($contents as $content){
                foreach($content as $key => $val){
                    $encrypt = $this->encryptString($key);
                    $result[$i][$encrypt] = $val;
                }
                $i++;
            }
        }
        return $result;
    }
    
    function getOS($useragent) { 
        $os_platform    =   "Unknown OS Platform";
        $os_array       =   array(
                                '/windows nt 10/i'     =>  'Windows 10',
                                '/windows nt 6.3/i'     =>  'Windows 8.1',
                                '/windows nt 6.2/i'     =>  'Windows 8',
                                '/windows nt 6.1/i'     =>  'Windows 7',
                                '/windows nt 6.0/i'     =>  'Windows Vista',
                                '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                                '/windows nt 5.1/i'     =>  'Windows XP',
                                '/windows xp/i'         =>  'Windows XP',
                                '/windows nt 5.0/i'     =>  'Windows 2000',
                                '/windows me/i'         =>  'Windows ME',
                                '/win98/i'              =>  'Windows 98',
                                '/win95/i'              =>  'Windows 95',
                                '/win16/i'              =>  'Windows 3.11',
                                '/macintosh|mac os x/i' =>  'Mac OS X',
                                '/mac_powerpc/i'        =>  'Mac OS 9',
                                '/linux/i'              =>  'Linux',
                                '/ubuntu/i'             =>  'Ubuntu',
                                '/iphone/i'             =>  'iPhone',
                                '/ipod/i'               =>  'iPod',
                                '/ipad/i'               =>  'iPad',
                                '/android/i'            =>  'Android',
                                '/blackberry/i'         =>  'BlackBerry',
                                '/webos/i'              =>  'Mobile'
                            );
        foreach ($os_array as $regex => $value) { 

            if (preg_match($regex, $useragent)) {
                $os_platform    =   $value;
            }
        }   
        return $os_platform;

    }

    function getBrowser($useragent) {
      // check for most popular browsers first
      // unfortunately, that's IE. We also ignore Opera and Netscape 8
      // because they sometimes send msie agent
      if (strpos($useragent, 'MSIE') !== FALSE && strpos($useragent, 'Opera') === FALSE && strpos($useragent, 'Netscape') === FALSE) {
        //deal with Blazer
        if (preg_match("/Blazer\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Blazer ' . $matches[1];
        }
        //deal with IE
        if (preg_match("/MSIE ([0-9]{1,2}\.[0-9]{1,2})/", $useragent, $matches)) {
          return 'Internet Explorer ' . $matches[1];
        }
      }
      elseif (strpos($useragent, 'IEMobile') !== FALSE) {
        if (preg_match("/IEMobile\/([0-9]{1,2}\.[0-9]{1,2})/", $useragent, $matches)) {
          return 'Internet Explorer Mobile ' . $matches[1];
        }
      }
      elseif (strpos($useragent, 'Gecko')) {
        //deal with Gecko based

        //if firefox
        if (preg_match("/Firefox\/([0-9]{1,2}\.[0-9]{1,2}(\.[0-9]{1,2})?)/", $useragent, $matches)) {
          return 'Mozilla Firefox ' . $matches[1];
        }

        //if Netscape (based on gecko)
        if (preg_match("/Netscape\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Netscape ' . $matches[1];
        }

        //check chrome before safari because chrome agent contains both
        if (preg_match("/Chrome\/([^\s]+)/", $useragent, $matches)) {
          return 'Google Chrome ' . $matches[1];
        }

        //if Safari (based on gecko)
        if (preg_match("/Safari\/([0-9]{2,3}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Safari ' . $matches[1];
        }

        //if Galeon (based on gecko)
        if (preg_match("/Galeon\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Galeon ' . $matches[1];
        }

        //if Konqueror (based on gecko)
        if (preg_match("/Konqueror\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Konqueror ' . $matches[1];
        }

        // if Fennec (based on gecko)
        if (preg_match("/Fennec\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Fennec' . $matches[1];
        }

        // if Maemo (based on gecko)
        if (preg_match("/Maemo\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Maemo' . $matches[1];
        }

        //no specific Gecko found
        //return generic Gecko
        return 'Gecko based';
      }
      elseif (strpos($useragent, 'Opera') !== FALSE) {
        //deal with Opera
        if (preg_match("/Opera[\/ ]([0-9]{1}\.[0-9]{1}([0-9])?)/", $useragent, $matches)) {
          return 'Opera ' . $matches[1];
        }
      }
      elseif (strpos($useragent, 'Lynx') !== FALSE) {
        //deal with Lynx
        if (preg_match("/Lynx\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Lynx ' . $matches[1];
        }
      }
      elseif (strpos($useragent, 'Netscape') !== FALSE) {
        //NN8 with IE string
        if (preg_match("/Netscape\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
          return 'Netscape ' . $matches[1];
        }
      }
      else {
        //unrecognized, this should be less than 1% of browsers (not counting bots like google etc)!
        return 'unknown';
      }
    }
    
    function getIpAddress() {

            //Just get the headers if we can or else use the SERVER global
            if ( function_exists( 'apache_request_headers' ) ) {

                    $headers = apache_request_headers();

            } else {

                    $headers = $_SERVER;

            }

            //Get the forwarded IP if it exists
            if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {

                    $the_ip = $headers['X-Forwarded-For'];

            } elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )) {

                    $the_ip = $headers['HTTP_X_FORWARDED_FOR'];

            } else {

                    $the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );

            }

            return $the_ip;

    }
    
    function createSysActivity($mod, $act, $bug, $userId, $staus){
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $browser = $this->getBrowser($useragent);
        $os      = $this->getOS($useragent);
        $ipAddr  = $this->getIpAddress();
        mysql_query("INSERT INTO `system_activities` (`module`, `act`, `bug`, `browser`, `operating_system`, `ip`, `created`, `created_by`, `status`) 
                     VALUES ('".$mod."', '".$act."', '".$bug."', '".$browser."', '".$os."', '".$ipAddr."', '".date("Y-m-d H:i:s")."', ".$userId.", ".$staus.");");
    }
    
    function getClassId($companyId, $class, $locationGroupId){
        $classId = 0;
        $classArray = array();
        if(!empty($companyId) && !empty($class) && !empty($locationGroupId)){
            $classArray = unserialize($class);
            $classId = $classArray[$companyId][$locationGroupId];
        }
        return $classId;
    }
    
    function encryptString($string){
        $convert = '';

        // Convert Number
        $convert = preg_replace('[0]', '66-', $string);
        $convert = preg_replace('[1]', '77-', $convert);
        $convert = preg_replace('[2]', '55-', $convert);
        $convert = preg_replace('[3]', '11-', $convert);
        $convert = preg_replace('[4]', '88-', $convert);
        $convert = preg_replace('[5]', '99-', $convert);
        $convert = preg_replace('[6]', '00-', $convert);
        $convert = preg_replace('[7]', '98-', $convert);
        $convert = preg_replace('[8]', '97-', $convert);
        $convert = preg_replace('[9]', '96-', $convert);

        // Convert Lowercase
        $convert = preg_replace('[a]', '21-', $convert);
        $convert = preg_replace('[b]', '22-', $convert);
        $convert = preg_replace('[c]', '23-', $convert);
        $convert = preg_replace('[d]', '31-', $convert);
        $convert = preg_replace('[e]', '32-', $convert);
        $convert = preg_replace('[f]', '33-', $convert);
        $convert = preg_replace('[g]', '41-', $convert);
        $convert = preg_replace('[h]', '42-', $convert);
        $convert = preg_replace('[i]', '43-', $convert);
        $convert = preg_replace('[j]', '51-', $convert);
        $convert = preg_replace('[k]', '52-', $convert);
        $convert = preg_replace('[l]', '53-', $convert);
        $convert = preg_replace('[m]', '61-', $convert);
        $convert = preg_replace('[n]', '62-', $convert);
        $convert = preg_replace('[o]', '63-', $convert);
        $convert = preg_replace('[p]', '71-', $convert);
        $convert = preg_replace('[q]', '72-', $convert);
        $convert = preg_replace('[r]', '73-', $convert);
        $convert = preg_replace('[s]', '74-', $convert);
        $convert = preg_replace('[t]', '81-', $convert);
        $convert = preg_replace('[u]', '82-', $convert);
        $convert = preg_replace('[v]', '83-', $convert);
        $convert = preg_replace('[w]', '91-', $convert);
        $convert = preg_replace('[x]', '92-', $convert);
        $convert = preg_replace('[y]', '93-', $convert);
        $convert = preg_replace('[z]', '94-', $convert);

        // Convert Capital Word
        $convert = preg_replace('[A]', '021-', $convert);
        $convert = preg_replace('[B]', '022-', $convert);
        $convert = preg_replace('[C]', '023-', $convert);
        $convert = preg_replace('[D]', '031-', $convert);
        $convert = preg_replace('[E]', '032-', $convert);
        $convert = preg_replace('[F]', '033-', $convert);
        $convert = preg_replace('[G]', '041-', $convert);
        $convert = preg_replace('[H]', '042-', $convert);
        $convert = preg_replace('[I]', '043-', $convert);
        $convert = preg_replace('[J]', '051-', $convert);
        $convert = preg_replace('[K]', '052-', $convert);
        $convert = preg_replace('[L]', '053-', $convert);
        $convert = preg_replace('[M]', '061-', $convert);
        $convert = preg_replace('[N]', '062-', $convert);
        $convert = preg_replace('[O]', '063-', $convert);
        $convert = preg_replace('[P]', '071-', $convert);
        $convert = preg_replace('[Q]', '072-', $convert);
        $convert = preg_replace('[R]', '073-', $convert);
        $convert = preg_replace('[S]', '074-', $convert);
        $convert = preg_replace('[T]', '081-', $convert);
        $convert = preg_replace('[U]', '082-', $convert);
        $convert = preg_replace('[V]', '083-', $convert);
        $convert = preg_replace('[W]', '091-', $convert);
        $convert = preg_replace('[X]', '092-', $convert);
        $convert = preg_replace('[Y]', '093-', $convert);
        $convert = preg_replace('[Z]', '094-', $convert);

        // Convert Under Score
        $convert = preg_replace('[_]', '100-', $convert);

        return $convert;
    }
    
    function generateSqlInsertSync($tableName, $files){
        $i = 0;
        $j = 0;
        $sql = "INSERT INTO ".$tableName." (";
        foreach($files AS $key => $value){
            if($i > 0){
                $sql .= ",";
            }
            $sql .= "`".$key."`";
            $i++;
        }
        $sql .= ") VALUES (";
        foreach($files AS $key => $value){
            if($j > 0){
                $sql .= ",";
            }
            if (strpos($value,"SELECT") != false || $value == 'null') {
                $sql .= $value;
            }else{
                $sql .= "'".$value."'";
            }
            $j++;
        }
        $sql .= ");";
        return $sql;
    }

    function generateSqlUpdateSync($tableName, $files, $conditions, $order){
        $i = 0;
        $sql = "UPDATE ".$tableName." SET ";
        foreach($files AS $key => $value){
            if($i > 0){
                $sql .= ",";
            }
            if (strpos($value,"SELECT") != false || $value == 'null') {
                $sql .= $key."=".$value;
            }else{
                $sql .= $key."="."'".$value."'";
            }
            $i++;
        }
        $sql .= " WHERE ";
        if(!empty($conditions)){
            $sql .= $conditions;
        }else{
            $sql .= "1";
        }
        if(!empty($order)){
            $sql .= " ".$order;
        }
        $sql .= ";";
        return $sql;
    }

    function generateSqlDeleteSync($tableName, $conditions){
        $sql = "DELETE FROM ".$tableName." WHERE ";
        if(!empty($conditions)){
            $sql .= $conditions;
        }else{
            $sql .= "1";
        }
        $sql .= ";";
        return $sql;
    }
    
    function convertToDataSync($records, $table = ''){
        $restCode = array();
        $fields   = array();
        // Get Filed Name From Table
        $query = "SELECT * FROM ".$table;
        $result = mysql_query($query);
        $i = 0;
        while ($i < mysql_num_fields($result)){
           $fld = mysql_fetch_field($result, $i);
           $fields[] = $fld->name;
           $i++;
        }
        foreach($records AS $key => $value){
            if(!is_array($value) && $key != 'id' && $value != '' && in_array($key, $fields)){
//                if($key == 'parent_id'){
//                    $restCode[$key] = $this->getSQLSysCode($table, $value);
//                } else {
//                    $table = $this->listField($key);
//                    if($table != '' && $value != '0' && $value != ''){
//                        $restCode[$key] = $this->getSQLSysCode($table, $value);
//                    } else {
                        $restCode[$key] = $value;
//                    }
//                }
            }
        }
        return $restCode;
    }
    
    function getSyncCode($table, $id){
        $synCode = '';
        if(!empty($table) && !empty($id)){
            $sql = mysql_query("SELECT sys_code FROM ".$table." WHERE id = ".$id);
            $row = mysql_fetch_array($sql);
            $synCode = $row[0];
        }
        return $synCode;
    }
    
    function getSQLSyncCode($table, $id){
        $sqlSync = '';
        $synCode = '';
        if(!empty($table) && !empty($id)){
            $sql = mysql_query("SELECT sys_code FROM ".$table." WHERE id = ".$id);
            $row = mysql_fetch_array($sql);
            $synCode = $row[0];
            $sqlSync = "(SELECT id FROM ".$table." WHERE sys_code = '".$synCode."' ORDER BY id DESC LIMIT 1)";
        }
        return $sqlSync;
    }
    
    function getSQLSync($table, $synCode){
        $sqlSync = "(SELECT id FROM ".$table." WHERE sys_code = '".$synCode."' ORDER BY id DESC LIMIT 1)";
        return $sqlSync;
    }
    
    function checkDateFrom($branchId, $timeNow){
        $sqlBranch  = mysql_query("SELECT HOUR(work_start) FROM branches WHERE id = ".$branchId);
        $rowBranch  = mysql_fetch_array($sqlBranch);
        $brachStart = (int) $rowBranch[0];
        $timeFrom   = array();
        for($i=$brachStart; $i < 24; $i++){
            $timeFrom[$i] = $i;
        }
        $return  = 0;
        if(array_key_exists($timeNow, $timeFrom)){
            $return  = 1;
        }
        return $return;
    }
    
    function getSQLSysCode($table, $id){
        $synCode = '';
        if($id != '' && $id > 0 && $table != ''){
            $sql = mysql_query("SELECT sys_code FROM ".$table." WHERE id = ".$id) or die("Error: SELECT sys_code FROM ".$table." WHERE id = ".$id);
            $row = mysql_fetch_array($sql);
            $synCode = $row[0];
        }
        return $synCode;
    }
    
    function listField($check){
        $field = array();
        // User
        $field['user_id']     = 'users';
        $field['created_by']  = 'users';
        $field['modified_by'] = 'users';
        // Company & Branch
        $field['company_id'] = 'companies';
        $field['branch_id']  = 'branches';
        $field['main_branch_id'] = 'main_branches';
        $field['currency_center_id']  = 'currency_centers';
        // Journey
        $field['t_journey_id']  = 't_journeys';
        $field['t_drop_off_id'] = 't_drop_offs';
        $field['t_pick_up_id']  = 't_pick_ups';
        $field['t_route_id']    = 't_routes';
        $field['coupon_id']     = 'coupons';
        $field['t_journey_transit_id']   = 't_journey_transits';
        $field['t_journey_departure_id'] = 't_journey_departures';
        $field['t_boarding_point_id']    = 't_journey_boarding_points';
        $field['t_destination_from_id']  = 't_destinations';
        $field['t_destination_to_id']    = 't_destinations';
//        $field['payment_method_id']      = 'others';
        $field['t_transportation_type_id']   = 't_transportation_types';
        // Ticket
        $field['t_ticket_id'] = 't_tickets';
        $field['t_ticket_detail_id']  = 't_ticket_details';
        $field['t_ticket_api_tmp_id'] = 't_ticket_api_tmps';
        $field['t_ticket_detail_api_tmp_id'] = 't_ticket_detail_api_tmps';
        // Seat
        $field['t_journey_seat_block_id'] = 't_journey_seat_blocks';
        // Agency
        $field['t_agent_id']      = 't_agents';
        $field['t_agent_type_id'] = 't_agent_types';
        $field['t_commision_id']  = 't_commisions';
        $result = '';
        if(array_key_exists($check, $field)){
            $result = $field[$check];
        }
        return $result;
    }
    
    function Safeb64Encode($string) {
        $data   = base64_encode($string);
        $return = str_replace(array('+','/','='),array('-','_',''),$data);
        return $return;
    }

    function encode($value, $skey){ 
        if(!$value){return false;}
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $skey, $text, MCRYPT_MODE_ECB, $iv);
        return trim($this->Safeb64Encode($crypttext)); 
    }

    function getAgencyAPiWebhook(){
        return 1;
    }

    function ticketSendEmail($transactionId){
        $result['status'] = 0;
        // QA
        $apiURL    = WEBSITE_API_URL;
        $apiKey    = WEBSITE_API_KEY;
        // Production
        // $apiURL    = "https://vetapim.utlog.net/vetTkBusWebApi";
        // $apiKey    = "11c90328-56c1-41a6-a2b2-27dd0fb57de6";
        $headers[] = 'Accept: */*';
        $headers[] = 'Authorization: Bearer '.$apiKey;
        $url       = $apiURL.'/booking/ticketSendEmail/';
        $post      = array("transactionId" => $transactionId);
        // CURL
        $curl    = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        $curl_error = curl_error($curl);
        curl_close ($curl);
        if ($curl_errno > 0) {
            $result['info']   = "cURL Error ($curl_errno): $curl_error\n";
        } else {
            $return = json_decode($result, TRUE);
            $return['info']   = $result;
            $return['status'] = 1;
        }
        return $return;
    }

    function generatePackageCode($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return "VPK".$randomString;
    }
    
}

?>