<?php
date_default_timezone_set('Asia/Phnom_Penh');
$cn = mysql_connect('dcabticlo.cam-ticket.com', 'uCamCoCer', '28Bgvwmwq3aW62UcA5') or die(mysql_error());
mysql_select_db('CamCoCdb', $cn);
mysql_query("SET character_set_client=utf8", $cn);
mysql_query("SET character_set_connection=utf8", $cn);
mysql_query("SET NAMES 'utf8'", $cn);

include_once('/var/www/CoContorlF/app/webroot/includes/PayWayApiCheckout.php');
include_once('/var/www/CoContorlF/app/webroot/includes/AcledaCheckout.php');
include_once("/var/www/CoContorlF/app/webroot/includes/NewWingConfig.php");

$sqlOrder = mysql_query("SELECT * FROM online_orders WHERE status IN (1,2) AND input_type = 1 AND offline_project_id = 1 AND date_add(`created`, interval 11 minute) < now();");
while($rowOrder = mysql_fetch_array($sqlOrder)){
    mysql_query("INSERT INTO `crontab_logs` (`id`, `online_customer_id`, `created`, `modified`, `status`) 
                 VALUES (NULL, ".$rowOrder['id'].", now(), NULL, NULL);");
    $logId = mysql_insert_id();
    $release = true;
    $checkPayment = false;
    $bankApiResponse = "";
    if($rowOrder['payment_method_id'] == 5 || $rowOrder['payment_method_id'] == 6 || $rowOrder['payment_method_id'] == 7){ // ABA
        $sqlTComp    = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." LIMIT 1");
        $rowTComp    = mysql_fetch_array($sqlTComp);
        $apiKey       = ABA_PAYWAY_API_KEY;
        $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
        if($rowTComp['company_id'] == 6 || $rowTComp['company_id'] == 17){ // Buva Sea
            $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
            $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
        } else if($rowTComp['company_id'] == 7 || $rowTComp['company_id'] == 12 || $rowTComp['company_id'] == 13 || $rowTComp['company_id'] == 14){ // VET Air Bus
            $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
            $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;                  
        }
        $transactionId = $rowOrder['code'];
        $req_time   = time();
        $bodyReq    = $req_time.$merchant_id.$transactionId;
        $hash       = base64_encode(hash_hmac('sha512', $bodyReq, $apiKey, true));
        $postfields = array(
            'req_time' => $req_time,
            'merchant_id' => $merchant_id,
            'tran_id' => $transactionId,
            'hash' => $hash
        );
        $headers = array(
            'accept: */*',
            'Content-Type: multipart/form-data',
            'Referer: https://vetticket.utlog.net/payments/index.php'
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, ABA_CHECK_TRANSACTION_API_URL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result      = curl_exec($curl); //Result Json
        $curl_errno  = curl_errno($curl);
        $curl_error  = curl_error($curl);
        curl_close ($curl);
        $bankApiResponse = $result;
        if ($curl_errno > 0) {
            
        } else {
		    $output = json_decode($result, true);
            if($output['status'] == 0 && $output['description'] == "approved"){
                $checkPayment = true;
            }
        }
        if(!$checkPayment && (int)$rowOrder['payment_method_id'] === 7){ // Payment Failed and VISA Card
            $createdAt = isset($rowOrder['created']) ? strtotime($rowOrder['created']) : false;
            if($createdAt !== false && ($createdAt + (25 * 60)) > time()){ // Created + 25 miniute > now()
                $checkPayment = false;
                $release = false;
                mysql_query("UPDATE crontab_logs SET status = 1, modified = now() WHERE id = ".$logId.";");
            }
        }
    } else if($rowOrder['payment_method_id'] == 8){ // ACLEDA
        $amount  = ($rowOrder['total_amount'] + $rowOrder['total_vat'] + $rowOrder['lucky_draw_fee'] - $rowOrder['discount_amount']);
        $responseAcleda = array();
        // Check Acleda Payment
        $sqlAcledaStatus = mysql_query("SELECT * FROM acleda_access_transactions WHERE online_order_id = ".$rowOrder['id']." AND status > 0 ORDER BY id");
        if(mysql_num_rows($sqlAcledaStatus)){
            while($rowAcledaStatus = mysql_fetch_array($sqlAcledaStatus)){
                // CURL
                $url  = ACLENDA_DEPPLINK_API_URL."/getTxnStatus";
                $post = array(
                    'loginId' => ACLENDA_DEPPLINK_LOGINID,
                    'password' => ACLENDA_DEPPLINK_PASSWORD,
                    'merchantName' => ACLENDA_DEPPLINK_MERCHANT_NAME,
                    'signature' => ACLENDA_DEPPLINK_SIGNATURE,
                    'merchantId' => ACLENDA_DEPPLINK_MERCHANT_ID,
                    'paymentTokenid' => $rowAcledaStatus['aclenda_payment_token_id'],
                );
                $headers = array(
                    'accept: */*',
                    'Content-Type: application/json',
                    'Referer: https://vetticket.utlog.net/payments/index.php'
                );
                // CURL
                $curl  = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $curlResp     = curl_exec($curl);
                $curl_errno   = curl_errno($curl);
                $curl_error   = curl_error($curl);
                curl_close ($curl);
                $responseAcleda[] = $curlResp;
                if ($curl_errno > 0) {
                    
                } else {
                    $result = json_decode($curlResp, true);
                    if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS" && $result['result']['xTran']['paymentTokenid'] == $rowAcledaStatus['aclenda_payment_token_id'] && $result['result']['xTran']['invoiceid'] == $rowOrder['code'] && $result['result']['xTran']['purchaseAmount'] == $amount){
                        $checkPayment = true;
                    }
                }
            }
        }
        if(!empty($responseAcleda)){
            $bankApiResponse = json_encode($responseAcleda);
        }
    } else if($rowOrder['payment_method_id'] == 4){ // Wing
        $wingPay = new WingSdkRequest();
        $checkPayStatus = $wingPay->checkTransactionStatus($rowOrder['code']);
        if($checkPayStatus['status'] == 1){
            $checkPayment = true;
        }
        if(!empty($checkPayStatus['info'])){
            $bankApiResponse = $checkPayStatus['info'];
        }
    }
    // Complete Order
    if($checkPayment == true){
        $release = false;
        $sqlTicket  = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." AND status = 1");
        if(mysql_num_rows($sqlTicket)){
            // Update Order
            mysql_query("UPDATE online_orders SET status = 4, note = 'Recheck Update', modified = now() WHERE id = ".$rowOrder['id'].";");
            while($rowTicket = mysql_fetch_array($sqlTicket)){
                // Update Ticket Tmp
                mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                // Move Ticket Tmp to Ticket
                mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`) 
                             SELECT `sys_code`, `offline_project_id`, `online_order_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Terminal', `is_vat` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                // Move Ticket Detail Tmp to Ticket Detail
                mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `unit_price`, `vat_price`, `discount`, `total_amount`, `nationally`) SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `unit_price`, `vat_price`, `discount`, `total_amount`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                // Update Seat Status
                mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_id = (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1);");
            }
        }
        mysql_query("UPDATE crontab_logs SET status = 2, modified = now() WHERE id = ".$logId.";");
    }
    // Update Bank APi Response
    if(!empty($bankApiResponse)){
        mysql_query("UPDATE online_orders SET bank_api_response = '".$bankApiResponse."' WHERE id = ".$rowOrder['id'].";");
    }
    // Release Order
    if($release == true){
        // Delete Booking Agency Balance
        $sqlBooking = mysql_query("SELECT id, t_agent_id FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." AND t_agent_id IS NOT NULL LIMIT 1");
        if(mysql_num_rows($sqlBooking)){
            $rowBooking = mysql_fetch_array($sqlBooking);
            mysql_query("DELETE FROM agency_balances WHERE t_ticket_id = ".$rowBooking['id']." AND t_agency_id = ".$rowBooking['t_agent_id']);
        }
        mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_id IS NULL AND t_ticket_api_tmp_id IN (SELECT id FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id'].");");
        mysql_query("UPDATE t_ticket_api_tmps SET status = -2 WHERE online_order_id = ".$rowOrder['id']);
        mysql_query("UPDATE online_orders SET status = 0, modified = now(), note = 'Crontab Cancel' WHERE id = ".$rowOrder['id'].";");
        
        if($rowOrder['payment_method_id'] == 4){ // Wing Mini App Promotion
            $sqlCheckWingPromotion = mysql_query("SELECT id FROM mini_app_promotion_transactions WHERE online_order_id = ".$rowOrder['id']);
            if(mysql_num_rows($sqlCheckWingPromotion)){
                mysql_query("DELETE FROM mini_app_promotion_transactions WHERE online_order_id = ".$rowOrder['id']);
            }
        }
        mysql_query("UPDATE crontab_logs SET status = 1, modified = now() WHERE id = ".$logId.";");
    }
}
?>
