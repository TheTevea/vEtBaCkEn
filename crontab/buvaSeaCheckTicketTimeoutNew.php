<?php
date_default_timezone_set('Asia/Phnom_Penh');
$cn = mysql_connect('dbremcap.utlog.lan', 'uCamCoCer', '28Bgvwmwq3aW62UcA5') or die(mysql_error());
mysql_select_db('CamCoCdb', $cn);
mysql_query("SET character_set_client=utf8", $cn);
mysql_query("SET character_set_connection=utf8", $cn);
mysql_query("SET NAMES 'utf8'", $cn);

include('/var/www/VetTicketNF/app/webroot/includes/PayWayApiBuvasea.php');
$sqlOrder = mysql_query("SELECT * FROM online_orders WHERE status IN (1,2) AND input_type = 2 AND offline_project_id = 1 AND date_add(`created`, interval 15 minute) < now();");
while($rowOrder = mysql_fetch_array($sqlOrder)){
    $release = true;
    if($rowOrder['payment_method_id'] == 5 || $rowOrder['payment_method_id'] == 6){
        $transactionId = $rowOrder['code'];
        $req_time   = time();
        $bodyReq    = $req_time.ABA_PAYWAY_MERCHANT_ID.$transactionId;
        $hash       = base64_encode(hash_hmac('sha512', $bodyReq, ABA_PAYWAY_API_KEY, true));
        $postfields = array(
            'req_time' => $req_time,
            'merchant_id' => ABA_PAYWAY_MERCHANT_ID,
            'tran_id' => $transactionId,
            'hash' => $hash
        );
        $headers = array(
            'accept: */*',
            'Content-Type: multipart/form-data',
            'Referer: https://www.buvasea.com/index.php'
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
        $response = array();
        if ($curl_errno > 0) {
            $response['status'] = 0;
        } else {
		    $output = json_decode($result, true);
            if($output['status'] == 0 && $output['description'] == "approved"){
                $release = false;
                $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." LIMIT 1");
                if(mysql_num_rows($sqlTmp)){
                    // Update Order
                    mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowOrder['id'].";");
                    // Update Ticket Tmp
                    $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']);
                    while($rowTicket = mysql_fetch_array($sqlTicket)){
                        // Update Ticket Tmp
                        mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                        // Move Ticket Tmp to Ticket
                        mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `total_amount`, `discount_amount`, `commission_percent`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`) SELECT `sys_code`, `offline_project_id`, `online_order_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `commission`, `commission_percent`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Website' FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                        // Move Ticket Detail Tmp to Ticket Detail
                        mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `unit_price`, `discount`, `total_amount`) SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `unit_price`, `discount`, `total_amount` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                        mysql_query("DELETE FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);");
                        mysql_query("DELETE FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                        // Update Seat Status
                        mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_id = (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1);");
                    }
                }
            }
        }
    }
    if($release == true){
        mysql_query("UPDATE t_seat_controls sc INNER JOIN t_ticket_api_tmps tt ON sc.t_ticket_api_tmp_id = tt.id SET sc.status = 0 WHERE sc.t_ticket_id IS NULL AND tt.online_order_id = ".$rowOrder['id'].";");
        mysql_query("UPDATE t_ticket_api_tmps SET status = -2 WHERE online_order_id = ".$rowOrder['id']);
        // Update Order
        mysql_query("UPDATE online_orders SET status = 0, modified = now(), note = 'Crontab Cancel' WHERE id = ".$rowOrder['id'].";");
        if($rowOrder['payment_method_id'] == 4){ // Wing Mini App Promotion
            $sqlCheckWingPromotion = mysql_query("SELECT id FROM mini_app_promotion_transactions WHERE online_order_id = ".$rowOrder['id']);
            if(mysql_num_rows($sqlCheckWingPromotion)){
                mysql_query("DELETE FROM mini_app_promotion_transactions WHERE online_order_id = ".$rowOrder['id']);
            }
        }
    }
}
?>
