<?php

class PaymentsController extends AppController {

    var $uses = 'Users';
    var $components = array('Helper', 'AutoId');
    
    function terminalPayment($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/terminalPayment/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                echo "cURL Error ($curl_errno): $curl_error\n";
            } else {
                echo $curlResp;
            }
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function terminalAbaAlipay($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function checkTerminalPaymentComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['transactionCode'] = $transactionId;
        $response['status'] = "0";
        if(empty($transactionId) || empty($token)){
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/checkTerminalPaymentComplete/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
                $response['status'] = 0;
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                    $response['status'] = 0;
                }
            }
            echo json_encode($response);
            exit;
        }
        // Process Complete
        include('includes/PayWayApiCheckout.php');
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5, 6, 7) LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk      = mysql_fetch_array($sqlChk);
                $sqlTComp    = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
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
                    'Referer: '.PAYMENT_URL_REF
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
                if ($curl_errno > 0) {
                    $response['status'] = 0;
                } else {
                    $output = json_decode($result, true);
                    if($output['status'] == 0 && $output['description'] == "approved"){
                        // Delete Token
                        $rowToken = mysql_fetch_array($sqlToken);
                        mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                        $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                        if(mysql_num_rows($sqlTmp)){
                            // Update Order
                            mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                            // Update Ticket Tmp
                            $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                // Update Ticket Tmp
                                mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Tmp to Ticket
                                $paymentId = 0;
                                if(!empty($rowChk['payment_method_id'])){
                                    $paymentId = $rowChk['payment_method_id'];
                                }
                                mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                             SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, ".$paymentId.", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Terminal', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Detail Tmp to Ticket Detail
                                mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                             SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                                // mysql_query("DELETE FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);");
                                // mysql_query("DELETE FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                                // Update Seat Status
                                mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                            }
                            // Send Email
                            if(!empty($rowChk['email'])){
                                if($rowChk['email'] != 'user@gmail.com' && $rowChk['email'] != 'minapp@gmail.com' && $rowChk['email'] != 'miniappV2.30@gmail.com'){
                                    $this->Helper->ticketSendEmail($transactionId);
                                }
                            }
                            $response['status'] = "1";
                        }
                    }
                }
            }
        }
        echo json_encode($response);
        exit;
    }

    function websiteAbaPay($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function websiteAbaPayComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['transactionCode'] = $transactionId;
        $response['status'] = "0";
        if(empty($transactionId) || empty($token)){
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/websiteAbaPayComplete/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
                $response['status'] = 0;
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                    $response['status'] = 0;
                }
            }
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5, 6, 7) LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk  = mysql_fetch_array($sqlChk);
                // Process Complete
                include('includes/PayWayApiCheckout.php');
                $apiKey       = ABA_PAYWAY_API_KEY;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
                $sqlTComp     = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                $rowTComp     = mysql_fetch_array($sqlTComp);
                if($rowTComp['company_id'] == 7 || $rowTComp['company_id'] == 12){ // VET Air Bus
                    $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;                  
                }
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
                    'Referer: '.PAYMENT_URL_REF
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
                if ($curl_errno > 0) {
                    $response['status'] = 0;
                } else {
                    $output = json_decode($result, true);
                    if($output['status'] == 0 && $output['description'] == "approved"){
                        // Delete Token
                        $rowToken = mysql_fetch_array($sqlToken);
                        mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                        $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                        if(mysql_num_rows($sqlTmp)){
                            // Update Order
                            mysql_query("UPDATE online_orders SET status = 4, `type` = 1, modified = now() WHERE id = ".$rowChk['id'].";");
                            // Update Ticket Tmp
                            $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                // Update Ticket Tmp
                                // mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Tmp to Ticket
                                mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                             SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, ".$rowChk['payment_method_id'].", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, 2, 'Website', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Detail Tmp to Ticket Detail
                                mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `markup`, `total_amount`) 
                                             SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `service_fee`, `total_amount` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                                mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                                // Update Seat Status
                                mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                            }
                            // Response Transaction
                            // $sqlPayment = mysql_query("SELECT * FROM payment_methods WHERE id = ".$rowChk['payment_method_id']);
                            // $rowPayment = mysql_fetch_array($sqlPayment);
                            // $response['transactionId']   = $rowChk['code'];
                            // $response['transactionDate'] = $rowChk['date'];
                            // $response['totalAmount']     = $rowChk['total_amount'];
                            // $response['totalDiscount']   = $rowChk['discount_amount'];
                            // $response['email']     = $rowChk['email'];
                            // $response['telephone'] = $rowChk['contact_telephone'];
                            // $response['payment']   = $rowPayment['name'];
                            // $response['created']   = $rowChk['created'];
                            // $response['paymentMethodId'] = $rowChk['payment_method_id'];
                            $response['status'] = "1";
                            // Detail
                            // $i = 0;
                            // $sqlTicket = mysql_query("SELECT
                            //                         t_tickets.id,
                            //                         t_tickets.code AS ticket_code,
                            //                         t_tickets.date AS booking_date,
                            //                         t_tickets.agt_refer_code AS reference,
                            //                         t_destinations.name AS destinationFrom,
                            //                         (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id) AS destinationTo,
                            //                         t_tickets.email,
                            //                         t_tickets.telephone,
                            //                         t_tickets.journey_date,
                            //                         t_tickets.journey_time AS departure,
                            //                         t_journeys.duration,
                            //                         t_journeys.arrival,
                            //                         IFNULL(t_boarding_points.name, '') AS boarding_point,
                            //                         IFNULL(t_boarding_points.telephone, '') AS boarding_point_telephone,
                            //                         IFNULL(t_boarding_points.address, '') AS boarding_point_address,
                            //                         IFNULL(t_boarding_points.lats, '') AS boarding_point_lats,
                            //                         IFNULL(t_boarding_points.longs, '') AS boarding_point_longs,
                            //                         IFNULL(t_drop_offs.name, '') AS drop_off_point,
                            //                         IFNULL(t_drop_offs.telephone, '') AS drop_off_point_telephone,
                            //                         IFNULL(t_drop_offs.address, '') AS drop_off_point_address,
                            //                         IFNULL(t_drop_offs.lats, '') AS drop_off_point_lats,
                            //                         IFNULL(t_drop_offs.longs, '') AS drop_off_point_longs,
                            //                         IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) AS total_amount
                            //                         FROM t_tickets
                            //                         INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                            //                         INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                            //                         LEFT JOIN t_boarding_points ON t_boarding_points.id = t_tickets.t_boarding_point_id
                            //                         LEFT JOIN t_drop_offs ON t_drop_offs.id = t_tickets.t_drop_off_id
                            //                         WHERE t_tickets.online_order_id = ".$rowChk['id']." AND t_tickets.offline_project_id = 1");
                            // while($rowTicket = mysql_fetch_array($sqlTicket)){
                            //     $response['ticket'][$i]['ticketCode']  = $rowTicket['ticket_code'];
                            //     $response['ticket'][$i]['reference']   = $rowTicket['reference'];
                            //     $response['ticket'][$i]['bookingDate'] = $rowTicket['booking_date'];
                            //     $response['ticket'][$i]['travelDate']  = $rowTicket['journey_date'];
                            //     $response['ticket'][$i]['departure']   = $rowTicket['departure'];
                            //     $response['ticket'][$i]['arrival']     = $rowTicket['arrival'];
                            //     $response['ticket'][$i]['duration']    = $rowTicket['duration'];
                            //     $response['ticket'][$i]['destinationFrom'] = $rowTicket['destinationFrom'];
                            //     $response['ticket'][$i]['destinationTo']   = $rowTicket['destinationTo'];
                            //     $response['ticket'][$i]['boardingPoint']   = $rowTicket['boarding_point'];
                            //     $response['ticket'][$i]['boardingPointTelephone'] = $rowTicket['boarding_point_telephone'];
                            //     $response['ticket'][$i]['boardingPointAddress']   = $rowTicket['boarding_point_address'];
                            //     $response['ticket'][$i]['boardingPointLats']      = $rowTicket['boarding_point_lats'];
                            //     $response['ticket'][$i]['boardingPointLongs']     = $rowTicket['boarding_point_longs'];
                            //     $response['ticket'][$i]['dropOffPoint']           = $rowTicket['drop_off_point'];
                            //     $response['ticket'][$i]['dropOffPointTelephone']  = $rowTicket['drop_off_point_telephone'];
                            //     $response['ticket'][$i]['dropOffPointAddress']    = $rowTicket['drop_off_point_address'];
                            //     $response['ticket'][$i]['dropOffPointLats']       = $rowTicket['drop_off_point_lats'];
                            //     $response['ticket'][$i]['dropOffPointLongs']      = $rowTicket['drop_off_point_longs'];
                            //     $sqlTicketDetail = mysql_query("SELECT label_number AS seat_label, IF(gender=1,'Male',IF(gender=2,'Female','N/A')) AS gender, nationally, name
                            //                                     FROM t_ticket_details
                            //                                     WHERE t_ticket_id = ".$rowTicket['id']);
                            //     $j = 0;
                            //     while($rowTicketDetail = mysql_fetch_array($sqlTicketDetail)){
                            //         $response['ticket'][$i]['seat'][$j]['seatLabel']  = $rowTicketDetail['seat_label'];
                            //         $response['ticket'][$i]['seat'][$j]['name']       = $rowTicketDetail['name'];
                            //         $response['ticket'][$i]['seat'][$j]['gender']     = $rowTicketDetail['gender'];
                            //         $response['ticket'][$i]['seat'][$j]['nationally'] = $rowTicketDetail['nationally'];
                            //         $j++;
                            //     }
                            //     $i++;
                            // }
                        }
                    }
                }
            }
        }
        echo json_encode($response);
        exit;
    }

    function checkAbaTransaction($transactionId = null){
        $this->layout = 'ajax';
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header("Allow: POST, GET, OPTIONS, PUT, DELETE");
        $response = array();
        $response['status'] = 0;
        if(empty($transactionId)){
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/checkAbaTransaction/'.rawurlencode($transactionId);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
                $response['status'] = 0;
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                    $response['status'] = 0;
                }
            }
            echo json_encode($response);
            exit;
        }
        include('includes/PayWayApiCheckout.php');
        $response['status'] = 0;
        $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND payment_method_id IN (5, 6, 7) LIMIT 1");
        if(mysql_num_rows($sqlChk)){
            $rowChk       = mysql_fetch_array($sqlChk);
            $sqlTicket    = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
            $rowTicket    = mysql_fetch_array($sqlTicket);
            // Vireak Buntham
            $apiKey       = ABA_PAYWAY_API_KEY;
            $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
            if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){ // Buva Sea
                $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
            } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;           
            }
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
                'Referer: '.PAYMENT_URL_REF
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
            if ($curl_errno > 0) {
                $response['status'] = 0;
            } else {
                $output = json_decode($result, true);
                if($output['status'] == 0 && $output['description'] == "approved"){
                    $response['status'] = 1;
                } else {
                    $response['status'] = 0;
                }
            }
        }
        echo json_encode($response);
        exit;
    }

    function abaTransactionList(){
        $this->layout = 'ajax';
        include('includes/PayWayApiCheckout.php');
        $req_time   = time();
        $machineId  = ABA_PAYWAY_MERCHANT_ID;
        $dateFrom   = "2021-12-07 00:00:00";
        $dateTo     = "2021-12-08 00:00:00";
        $status     = "APPROVED, DECLINED, PENDING";
        $hash       = base64_encode(hash_hmac('sha512', $req_time .$machineId . $dateFrom . $dateTo .$status, ABA_PAYWAY_API_KEY, true));
        $postfields = array(
            'req_time' => $req_time,
            'merchant_id' => $machineId,
            'from_date' => $dateFrom,
            'to_date' => $dateTo,
            'from_amount' => "",
            'to_amount' => "",
            'status' => $status,
            'hash' => $hash
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ABA_CHECK_TRANSACTION_LIST_API_URL);
        curl_setopt($ch, CURLOPT_PROXY, null);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
        $result = curl_exec($ch); //Result Json
        $output = json_decode($result, true);
        exit;
    }

    function abaMobilePay($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['status']  = 0;
        $response['info']    = "Your token has been expired!";
        $response['qr_code'] = "";
        $response['abapay_deeplink'] = "";
        $response['app_store']  = "";
        $response['play_store'] = "";
        $response['checkout_qr_url'] = "";
        if(empty($transactionId) || empty($token)){
            $response['info']    = "Invalid data post Transaction ID or Token";
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/abaMobilePay/'.$transactionId.'/'.$token;
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                }
            }
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 5 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowChk['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    include('includes/PayWayApiCheckout.php');
                    $sqlTicket     = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                    $rowTicket     = mysql_fetch_array($sqlTicket);
                    $req_time      = time();
                    $transactionId = $rowChk['code'];
                    $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] + $rowChk['service_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount'] - $rowChk['payment_method_discount_amount']);
                    $apiKey        = ABA_PAYWAY_API_KEY;
                    $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
                    $paymentOption = 'abapay_khqr_deeplink';
                    if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){ // Buva Sea
                        $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                        $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                    } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                        $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                        $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;           
                    }
                    $lifeTime = 10; // 10 minutes
                    // CURL
                    $url  = PayWayApiCheckout::getApiUrl();
                    $post = [
                        'hash'     => PayWayApiCheckout::getHash($req_time, $merchant_id, $transactionId, $amount, $paymentOption, $lifeTime, $apiKey),
                        'tran_id'  => $transactionId,
                        'amount'   => $amount,
                        'req_time' => $req_time,
                        'merchant_id'  => $merchant_id,
                        'payment_option'  => $paymentOption,
                        'payment_gate' => 0,
                        'lifetime' => $lifeTime
                    ];
                    $headers = array(
                        'accept: */*',
                        'Content-Type: multipart/form-data',
                        'Referer: '.PAYMENT_URL_REF
                    );
                    // CURL
                    $curl  = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    $curlResp     = curl_exec($curl);
                    $curl_errno   = curl_errno($curl);
                    $curl_error   = curl_error($curl);
                    curl_close ($curl);
                    if ($curl_errno > 0) {
                        $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $result = json_decode($curlResp, true);
                        if($result['status']['code'] == "00" && $result['status']['message'] == "Success!"){
                            $response['status']  = 1;
                            $response['info']    = "Success";
                            if(!empty($result['qr_string'])){
                                $response['qr_code'] = $result['qr_string'];
                            } else {
                                $response['qr_code'] = "payment";
                            }
                            $response['abapay_deeplink'] = $result['abapay_deeplink'];
                            if(!empty($result['app_store'])){
                                $response['app_store']  = $result['app_store'];   
                            } else {
                                $response['app_store']  = "";
                            }
                            if(!empty($result['play_store'])){
                                $response['play_store']  = $result['play_store'];   
                            } else {
                                $response['play_store']  = "";
                            }
                            if(!empty($result['checkout_qr_url'])){
                                $response['checkout_qr_url'] = $result['checkout_qr_url'];
                            } else {
                                $response['checkout_qr_url'] = "";
                            }
                            // Update Click Payment
                            mysql_query("UPDATE online_orders SET click_payment = 1 WHERE id = ".$rowChk['id']);
                        } else {
                            $response['status']  = 0;
                            if(!empty($result['description'])){
                                $response['info'] = $result['description'];
                            } else {
                                $response['info'] = "Error Access Payment";
                            }
                        }
                    }
                } else {
                    $response['info'] = "Token expired";
                }
            } else {
                $response['info'] = "Invalid Transaction";
            }
        } else {
            $response['info'] = "Invalid Token";
        }
        echo json_encode($response);
        exit;
    }

    function abaVisalPayment($transactionId = null, $token = null, $type = 1){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId', 'type'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function paymentSuccess(){
        $this->layout = 'payment';
    }

    function wingPayment($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId', 'token'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function saveWingCompleted($transactionId = null, $token = null){
        $this->layout = 'payment';
        if(empty($_GET['token']) || empty($_GET['status'])){
            echo "Invalid Transaction POST";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `created`, `status`) VALUES (NULL, '".$transactionId."', '".$_GET['token']."', now(), 3);");
            // Delete Token
            $rowToken = mysql_fetch_array($sqlToken);
            mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
            // Update Complete
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 4 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                if(mysql_num_rows($sqlTmp)){
                    // Update Order
                    mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                    // Update Ticket Tmp
                    $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                    while($rowTicket = mysql_fetch_array($sqlTicket)){
                        // Update Ticket Tmp
                        mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                        // Move Ticket Tmp to Ticket
                        mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                     SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Terminal', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                        // Move Ticket Detail Tmp to Ticket Detail
                        mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                     SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                        // mysql_query("UPDATE t_ticket_detail_api_tmps SET status = -3 WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);");
                        mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                        // Update Seat Status
                        mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                    }
                    $this->set(compact('transactionId'));
                } else {
                    echo "Invalid Booking Data";
                    exit;
                }
            } else {
                echo "Invalid Transaction ID";
                exit;
            }
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function wingCancel($transactionId = null){
        $this->layout = 'ajax';
        if(!empty($_GET['token']) && !empty($transactionId)){
            mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `created`, `status`) VALUES (NULL, '".$transactionId."', '".$_GET['token']."', now(), 2);");
        }
    }

    function wingWebsiteComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $result = array();
        $result['status'] = 0;
        if(empty($_POST['wingPayToken']) || empty($transactionId) || empty($token)){
            $result['error'] = 2;
            echo json_encode($result);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $paymentInfo = "";
            if(!empty($_POST['wingPayInfo'])){
                $paymentInfo = $_POST['wingPayInfo'];
            }
            mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `payment_info`, `created`, `status`) VALUES (NULL, '".$transactionId."', '".$_POST['wingPayToken']."', '".$paymentInfo."', now(), 3);");
            // Delete Token
            $rowToken = mysql_fetch_array($sqlToken);
            mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
            // Update Complete
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 4 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                if(mysql_num_rows($sqlTmp)){
                    // Update Order
                    mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                    // Update Ticket Tmp
                    $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                    while($rowTicket = mysql_fetch_array($sqlTicket)){
                        // Update Ticket Tmp
                        mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                        // Move Ticket Tmp to Ticket
                        mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                     SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Website', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                        // Move Ticket Detail Tmp to Ticket Detail
                        mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                     SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                        mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                        // Update Seat Status
                        mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                    }
                    // Send Email
                    if(!empty($rowChk['email'])){
                        if($rowChk['email'] != 'user@gmail.com' && $rowChk['email'] != 'minapp@gmail.com' && $rowChk['email'] != 'miniappV2.30@gmail.com'){
                            $this->Helper->ticketSendEmail($transactionId);
                        }
                    }
                    $result['status'] = 1;
                    $result['error']  = 0;
                } else {
                    $result['error'] = 1;
                }
            } else {
                $result['error'] = 3;
            }
        } else {
            $result['error'] = 4;
        }
        echo json_encode($result);
        exit;
    }

    function wingWebsiteCancel($transactionId = null, $token = null, $redirect = 0){
        $this->layout = 'ajax';
        if(!empty($token) && !empty($transactionId)){
            mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `created`, `status`) VALUES (NULL, '".$transactionId."', '".$token."', now(), 2);");
        }
        $result['status'] = 1;
        if($redirect == 1){
            // Redirect to Website Cancel
            header('Location: '.WEB_BUS_SUCCESS_PAGE."payment-success?transactionId=".$transactionId);
        } else {
            echo json_encode($result);
        }
        exit;
    }

    function checkBuvaSeaABAPayment($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['transactionCode'] = $transactionId;
        $response['status'] = "0";
        if(empty($transactionId) || empty($token)){
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/checkBuvaSeaABAPayment/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            // Process Complete
            include('includes/PayWayApiBuvasea.php');
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
                // 'Referer: http://localhost/payments/index.php'
                // 'Referer: https://qacl.udaya-tech.com/0412_VETOc_Web/payments/index.php'
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
            if ($curl_errno > 0) {
                $response['status'] = 0;
            } else {
                $output = json_decode($result, true);
                if($output['status'] == 0 && $output['description'] == "approved"){
                    $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5, 6) LIMIT 1");
                    if(mysql_num_rows($sqlChk)){
                        // Delete Token
                        $rowToken = mysql_fetch_array($sqlToken);
                        mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                        $rowChk = mysql_fetch_array($sqlChk);
                        $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." AND status > 0 LIMIT 1");
                        if(mysql_num_rows($sqlTmp)){
                            // Update Order
                            mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                            // Update Ticket Tmp
                            $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                // Update Ticket Tmp
                                mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Tmp to Ticket
                                mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                             SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, ".$rowChk['payment_method_id'].", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Website', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Detail Tmp to Ticket Detail
                                mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `markup`, `total_amount`) 
                                             SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `service_fee`, `total_amount` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                                // mysql_query("DELETE FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);");
                                // mysql_query("DELETE FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                                // Update Seat Status
                                mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                            }
                            // Response Transaction
                            $sqlPayment = mysql_query("SELECT * FROM payment_methods WHERE id = ".$rowChk['payment_method_id']);
                            $rowPayment = mysql_fetch_array($sqlPayment);
                            $response['transactionId']   = $rowChk['code'];
                            $response['transactionDate'] = $rowChk['date'];
                            $response['totalAmount']     = $rowChk['total_amount'];
                            $response['totalDiscount']   = $rowChk['discount_amount'] + $rowChk['coupon_amount'];
                            $response['email']     = $rowChk['email'];
                            $response['telephone'] = $rowChk['contact_telephone'];
                            $response['payment']   = $rowPayment['name'];
                            $response['created']   = $rowChk['created'];
                            $response['paymentMethodId'] = $rowChk['payment_method_id'];
                            $response['status'] = "1";
                            // Detail
                            $i = 0;
                            $sqlTicket = mysql_query("SELECT
                                                    t_tickets.id,
                                                    t_tickets.code AS ticket_code,
                                                    t_tickets.date AS booking_date,
                                                    t_tickets.agt_refer_code AS reference,
                                                    t_destinations.name AS destinationFrom,
                                                    (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id) AS destinationTo,
                                                    t_tickets.email,
                                                    t_tickets.telephone,
                                                    t_tickets.journey_date,
                                                    t_tickets.journey_time AS departure,
                                                    t_journeys.duration,
                                                    t_journeys.arrival,
                                                    IFNULL(t_boarding_points.name, '') AS boarding_point,
                                                    IFNULL(t_boarding_points.telephone, '') AS boarding_point_telephone,
                                                    IFNULL(t_boarding_points.address, '') AS boarding_point_address,
                                                    IFNULL(t_boarding_points.lats, '') AS boarding_point_lats,
                                                    IFNULL(t_boarding_points.longs, '') AS boarding_point_longs,
                                                    IFNULL(t_drop_offs.name, '') AS drop_off_point,
                                                    IFNULL(t_drop_offs.telephone, '') AS drop_off_point_telephone,
                                                    IFNULL(t_drop_offs.address, '') AS drop_off_point_address,
                                                    IFNULL(t_drop_offs.lats, '') AS drop_off_point_lats,
                                                    IFNULL(t_drop_offs.longs, '') AS drop_off_point_longs,
                                                    IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) AS total_amount
                                                    FROM t_tickets
                                                    INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                                    INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                                    LEFT JOIN t_boarding_points ON t_boarding_points.id = t_tickets.t_boarding_point_id
                                                    LEFT JOIN t_drop_offs ON t_drop_offs.id = t_tickets.t_drop_off_id
                                                    WHERE t_tickets.online_order_id = ".$rowChk['id']." AND t_tickets.offline_project_id = 1");
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                $response['ticket'][$i]['ticketCode']  = $rowTicket['ticket_code'];
                                $response['ticket'][$i]['reference']   = $rowTicket['reference'];
                                $response['ticket'][$i]['bookingDate'] = $rowTicket['booking_date'];
                                $response['ticket'][$i]['travelDate']  = $rowTicket['journey_date'];
                                $response['ticket'][$i]['departure']   = $rowTicket['departure'];
                                $response['ticket'][$i]['arrival']     = $rowTicket['arrival'];
                                $response['ticket'][$i]['duration']    = $rowTicket['duration'];
                                $response['ticket'][$i]['destinationFrom'] = $rowTicket['destinationFrom'];
                                $response['ticket'][$i]['destinationTo']   = $rowTicket['destinationTo'];
                                $response['ticket'][$i]['boardingPoint']   = $rowTicket['boarding_point'];
                                $response['ticket'][$i]['boardingPointTelephone'] = $rowTicket['boarding_point_telephone'];
                                $response['ticket'][$i]['boardingPointAddress']   = $rowTicket['boarding_point_address'];
                                $response['ticket'][$i]['boardingPointLats']      = $rowTicket['boarding_point_lats'];
                                $response['ticket'][$i]['boardingPointLongs']     = $rowTicket['boarding_point_longs'];
                                $response['ticket'][$i]['dropOffPoint']           = $rowTicket['drop_off_point'];
                                $response['ticket'][$i]['dropOffPointTelephone']  = $rowTicket['drop_off_point_telephone'];
                                $response['ticket'][$i]['dropOffPointAddress']    = $rowTicket['drop_off_point_address'];
                                $response['ticket'][$i]['dropOffPointLats']       = $rowTicket['drop_off_point_lats'];
                                $response['ticket'][$i]['dropOffPointLongs']      = $rowTicket['drop_off_point_longs'];
                                $sqlTicketDetail = mysql_query("SELECT label_number AS seat_label, IF(gender=1,'Male',IF(gender=2,'Female','N/A')) AS gender, nationally, name
                                                                FROM t_ticket_details
                                                                WHERE t_ticket_id = ".$rowTicket['id']);
                                $j = 0;
                                while($rowTicketDetail = mysql_fetch_array($sqlTicketDetail)){
                                    $response['ticket'][$i]['seat'][$j]['seatLabel']  = $rowTicketDetail['seat_label'];
                                    $response['ticket'][$i]['seat'][$j]['name']       = $rowTicketDetail['name'];
                                    $response['ticket'][$i]['seat'][$j]['gender']     = $rowTicketDetail['gender'];
                                    $response['ticket'][$i]['seat'][$j]['nationally'] = $rowTicketDetail['nationally'];
                                    $j++;
                                }
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        echo json_encode($response);
        exit;
    }

    function checkBuvaSeaAbaTransaction($transactionId = null){
        $this->layout = 'ajax';
        $response = array();
        if(empty($transactionId)){
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/checkBuvaSeaAbaTransaction/'.rawurlencode($transactionId);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        include('includes/PayWayApiBuvasea.php');
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
        if ($curl_errno > 0) {
            $response['status'] = 0;
        } else {
            $output = json_decode($result, true);
            if($output['status'] == 0 && $output['description'] == "approved"){
                $response['status'] = 1;
            } else {
                $response['status'] = 0;
            }
        }
        echo json_encode($response);
        exit;
    }

    function abaAlipay($transactionId = null, $token = null, $type = 1){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId', 'type'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function acledaMobilePay($transactionId = null, $token = null, $appType = 1){
        $this->layout = 'ajax';
        $response = array();
        if(empty($transactionId) || empty($token)){
            $response['status']  = 0;
            $response['info']    = "Invalid data post Transaction ID or Token";
            $response['qr_code'] = "";
            echo json_encode($response);
            exit;
        }
        $response['status']  = 0;
        $response['info']    = "Your token has been expired!";
        $response['qr_code'] = "";
        $response['abapay_deeplink'] = "";
        $response['app_store']  = "";
        $response['play_store'] = "";
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 8 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowChk['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    include('includes/AcledaCheckout.php');
                    $sqlTicket     = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                    $rowTicket     = mysql_fetch_array($sqlTicket);
                    $req_time      = time();
                    $transactionId = $rowChk['code'];
                    $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['service_fee'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount'] - $rowChk['payment_method_discount_amount']);
                    if($appType == 1){
                        $oprDevice = "android";
                    } else {
                        $oprDevice = "ios";
                    }
                    $deeplinId = 'VIREAKBT';
                    if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){ // Buva Sea
                        $deeplinId = 'BUVASEA';
                    } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                        $deeplinId = 'VETAIR';              
                    }
                    // CURL
                    $url  = ACLENDA_DEPPLINK_API_URL."/openSessionV2";
                    $post = array(
                        'loginId' => ACLENDA_DEPPLINK_LOGINID,
                        'password' => ACLENDA_DEPPLINK_PASSWORD,
                        'merchantID' => ACLENDA_DEPPLINK_MERCHANT_ID,
                        'signature' => ACLENDA_DEPPLINK_SIGNATURE,
                    );
                    $post['xpayTransaction']['txid'] = $transactionId;
                    $post['xpayTransaction']['purchaseAmount']   = $amount;
                    $post['xpayTransaction']['purchaseCurrency'] = "USD";
                    $post['xpayTransaction']['purchaseDate'] = $rowChk['date'];
                    $post['xpayTransaction']['purchaseDesc'] = "VET Express Booking Payment";
                    $post['xpayTransaction']['invoiceid']    = $transactionId;
                    $post['xpayTransaction']['item']         = "1";
                    $post['xpayTransaction']['quantity']     = "1";
                    $post['xpayTransaction']['expiryTime']   = "10";
                    $post['xpayTransaction']['oprDevice']    = $oprDevice;
                    $post['xpayTransaction']['deeplinkId']   = $deeplinId;
                    $post['xpayTransaction']['callBackUrl']  = "vetapp://payment/acleda_payment";
                    
                    $headers = array(
                        'accept: */*',
                        'Content-Type: application/json; charset=utf-8',
                        'Referer: '.PAYMENT_URL_REF
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
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                    $curlResp     = curl_exec($curl);
                    $curl_errno   = curl_errno($curl);
                    $curl_error   = curl_error($curl);
                    curl_close ($curl);
                    if ($curl_errno > 0) {
                        $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $result = json_decode($curlResp, true);
                        if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS"){
                            $response['status']  = 1;
                            $response['info']    = "Success";
                            $response['qr_code'] = "";
                            $response['abapay_deeplink'] = $result['result']['deeplinkUrl'];
                            if($appType == 1){
                                $response['app_store']  = "";
                                $response['play_store'] = $result['result']['deeplinkUrl'];
                            } else {
                                $response['app_store']  = $result['result']['deeplinkUrl'];
                                $response['play_store'] = "";
                            }
                            mysql_query("INSERT INTO `acleda_access_transactions` (`id`, `online_order_id`, `aclenda_payment_token_id`, `created`, `modified`, `status`) 
                                         VALUES (NULL, ".$rowChk['id'].", '".$result['result']['xTran']['paymentTokenid']."', now(), NULL, '1');");
                            // Update Click Payment
                            mysql_query("UPDATE online_orders SET click_payment = 1 WHERE id = ".$rowChk['id']);
                        } else {
                            $response['status']  = 0;
                            $response['info']    = $result['result']['errorDetails'];
                        }
                    }
                } else {
                    $response['info'] = "Token expired";
                }
            } else {
                $response['info'] = "Invalid Transaction";
            }
        } else {
            $response['info'] = "Invalid Token";
        }
        echo json_encode($response);
        exit;
    }

    function acledaCheckStatus($transactionId = null){
        $this->layout = 'ajax';
        $response = array();
        if(empty($transactionId)){
            $response['status']  = 0;
        } else {
            include('includes/AcledaCheckout.php');
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND payment_method_id = 8 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk   = mysql_fetch_array($sqlChk);
                $amount  = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['service_fee'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount'] - $rowChk['payment_method_discount_amount']);
                // Check Acleda Payment
                $sqlAcledaStatus = mysql_query("SELECT * FROM acleda_access_transactions WHERE online_order_id = ".$rowChk['id']." AND status > 0 ORDER BY id");
                if(mysql_num_rows($sqlAcledaStatus)){
                    $check = false;
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
                            'Referer: '.PAYMENT_URL_REF
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
                        if ($curl_errno > 0) {
                            $response['status'] = 0;
                        } else {
                            $result = json_decode($curlResp, true);
                            if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS" && $result['result']['xTran']['paymentTokenid'] == $rowAcledaStatus['aclenda_payment_token_id'] && $result['result']['xTran']['invoiceid'] == $rowChk['code'] && $result['result']['xTran']['purchaseAmount'] == $amount){
                                $check = true;
                            }
                        }
                    }
                    if($check == true){
                        $response['status'] = 1;
                    } else {
                        $response['status'] = 0;
                    }
                } else {
                    $response['status']  = 0;
                }
            } else {
                $response['status']  = 0;
            }
        }
        echo json_encode($response);
        exit;
    }

    function acledaComplete($transactionId = null, $token = null, $type = 1){
        $this->layout = 'payment';
        $response = array();
        if(empty($transactionId) || empty($token)){
            $response['status']  = 0;
            $response['info']    = "Invalid Transaction POST";
        } else {
            $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
            if(mysql_num_rows($sqlToken)){
                // Delete Token
                $rowToken = mysql_fetch_array($sqlToken);
                mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                // Update Complete
                $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 8 LIMIT 1");
                if(mysql_num_rows($sqlChk)){
                    $rowChk = mysql_fetch_array($sqlChk);
                    // Check Acleda Payment
                    $sqlAcledaStatus = mysql_query("SELECT * FROM acleda_access_transactions WHERE online_order_id = ".$rowChk['id']." AND status > 0 ORDER BY id");
                    if(mysql_num_rows($sqlAcledaStatus)){
                        include('includes/AcledaCheckout.php');
                        $req_time      = time();
                        $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['service_fee'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount'] - $rowChk['payment_method_discount_amount']);
                        $checkAcledaStatus = false;
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
                                'Referer: '.PAYMENT_URL_REF
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
                            if ($curl_errno > 0) {
                                $response['status'] = 0;
                                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
                            } else {
                                $result = json_decode($curlResp, true);
                                if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS" && $result['result']['xTran']['paymentTokenid'] == $rowAcledaStatus['aclenda_payment_token_id'] && $result['result']['xTran']['invoiceid'] == $rowChk['code'] && $result['result']['xTran']['purchaseAmount'] == $amount){
                                    $checkAcledaStatus = true;
                                }
                            }
                        }
                        if($checkAcledaStatus == true){
                            $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." AND status = 1");
                            if(mysql_num_rows($sqlTicket)){
                                // Update Order
                                mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                                // Update Ticket Tmp
                                while($rowTicket = mysql_fetch_array($sqlTicket)){
                                    // Update Ticket Tmp
                                    mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                                    // Move Ticket Tmp to Ticket
                                    mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                                 SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Terminal', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                    // Move Ticket Detail Tmp to Ticket Detail
                                    mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                                 SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                                    mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                                    // Update Seat Status
                                    mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                                }
                                // Update Aclenda Payment
                                mysql_query("UPDATE acleda_access_transactions SET modified = now(), status = 2 WHERE online_order_id = ".$rowChk['id']);
                                $response['status']  = 1;
                                $response['info']    = "Success";
                            } else {
                                $response['status']  = 0;
                                $response['info']    = "Invalid Ticket Transaction";
                            }
                        } else {
                            $response['status']  = 0;
                            $response['info']    = "Invalid Payment Status";
                        }
                    } else {
                        $response['status']  = 0;
                        $response['info']    = "Invalid Payment Transaction";
                    }
                } else {
                    $response['status']  = 0;
                    $response['info']    = "Invalid Transaction ID";
                }
            } else {
                $response['status']  = 0;
                $response['info']    = "Invalid Token";
            }
        }
        if($type == 1){ // APP Success Response
            echo json_encode($response);
            exit;
        } else { // Website Success Responnse
            //$this->set('transactionId', $transactionId);
            header('Location: '.WEB_BUS_SUCCESS_PAGE."payment-success?transactionId=".$transactionId);
        }
    }

    function acledaXpay($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        // Disable
        // echo '<b style="font-size: 14px;">This payment method is coming soon..</b>';
        // exit;
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $response['status']    = 0;
        $response['info']      = "Your token has been expired!";
        $response['sessionId'] = "";
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 8 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowChk['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    include('includes/AcledaCheckout.php');
                    $sqlTicket   = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                    $rowTicket   = mysql_fetch_array($sqlTicket);
                    $companyName = "Vireak Buntham";
                    $companyLogo = "";
                    $deeplinId   = 'VIREAKBT';
                    if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){ // Buva Sea
                        $deeplinId   = 'BUVASEA';
                        $companyName = "BUVA SEA";
                        $companyLogo = "";
                    } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                        $deeplinId   = 'VETAIR';   
                        $companyName = "VET AIR BUS";
                        $companyLogo = "";           
                    }
                    $transactionId = $rowChk['code'];
                    $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['service_fee'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount'] - $rowChk['payment_method_discount_amount']);
                    // CURL
                    $url  = ACLENDA_DEPPLINK_API_URL."/openSessionV2";
                    $post = array(
                        'loginId' => ACLENDA_DEPPLINK_LOGINID,
                        'password' => ACLENDA_DEPPLINK_PASSWORD,
                        'merchantID' => ACLENDA_DEPPLINK_MERCHANT_ID,
                        'signature' => ACLENDA_DEPPLINK_SIGNATURE
                    );
                    $post['xpayTransaction']['txid'] = $transactionId;
                    $post['xpayTransaction']['purchaseAmount']   = $amount;
                    $post['xpayTransaction']['purchaseCurrency'] = "USD";
                    $post['xpayTransaction']['purchaseDate'] = $rowChk['date'];
                    $post['xpayTransaction']['purchaseDesc'] = "VET Express Booking Payment";
                    $post['xpayTransaction']['invoiceid']    = $transactionId;
                    $post['xpayTransaction']['item']         = "1";
                    $post['xpayTransaction']['quantity']     = "1";
                    $post['xpayTransaction']['expiryTime']   = "10";
                    $post['xpayTransaction']['paymentCard']  = "1";
                    $post['xpayTransaction']['deeplinkId']   = $deeplinId;

                    $headers = array(
                        'accept: */*',
                        'Content-Type: application/json',
                        'Referer: '.PAYMENT_URL_REF
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
                    if ($curl_errno > 0) {
                        $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $result = json_decode($curlResp, true);
                        if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS"){
                            $response['status']         = 1;
                            $response['info']           = "Success";
                            $response['url']            = ACLENDA_XPAY_API_URL;
                            $response['url_success']    = ACLENDA_CALLBACK_PAYMENT_SUCCESS;
                            $response['url_cancel']     = ACLENDA_CALLBACK_PAYMENT_CANCEL;
                            $response['merchantID']     = ACLENDA_DEPPLINK_MERCHANT_ID;
                            $response['token']          = $token;
                            $response['sessionId']      = $result['result']['sessionid'];
                            $response['paymenttokenid'] = $result['result']['xTran']['paymentTokenid'];
                            $response['description']    = $post['xpayTransaction']['purchaseDesc'];
                            $response['expirytime']     = $post['xpayTransaction']['expiryTime'];
                            $response['amount']         = $post['xpayTransaction']['purchaseAmount'];
                            $response['quantity']       = $post['xpayTransaction']['quantity'];
                            $response['item']           = $post['xpayTransaction']['item'];
                            $response['invoiceid']      = $post['xpayTransaction']['invoiceid'];
                            $response['currencytype']   = $post['xpayTransaction']['purchaseCurrency'];
                            $response['transactionID']  = $post['xpayTransaction']['invoiceid'];
                            $response['companyName']    = $companyName;
                            $response['companyLogo']    = $companyLogo;
                            mysql_query("INSERT INTO `acleda_access_transactions` (`id`, `online_order_id`, `aclenda_payment_token_id`, `created`, `modified`, `status`) 
                                         VALUES (NULL, ".$rowChk['id'].", '".$result['result']['xTran']['paymentTokenid']."', now(), NULL, '1');");
                        } else {
                            $response['status']  = 0;
                            $response['info']    = $result['result']['errorDetails'];
                        }
                    }
                } else {
                    $response['info'] = "Token expired";
                }
            } else {
                $response['info'] = "Invalid Transaction";
            }
        } else {
            $response['info'] = "Invalid Token";
        }
        if($response['status'] == 0){
            echo $response['info'];
            exit;
        } else {
            $this->set('response', $response);
        }
    }

    function acledaXpayCancel($paymentTokenId = null, $transactionId = null){
        $this->layout = 'ajax';
        if(!empty($paymentTokenId)){
            mysql_query("UPDATE `acleda_access_transactions` SET status = 0, modified = now() WHERE aclenda_payment_token_id = '".$paymentTokenId."'");
        }
        $this->set('transactionId', $transactionId);
    }

    function acledaXpayComplete($transactionId = null){
        $this->layout = 'ajax';
        if(empty($transactionId)){
            echo "Invalid data post Transaction ID";
            exit;
        }
        $this->set('transactionId', $transactionId);
    }

    function busWebsitePaymentProcess($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $returnData['status'] = 0;
        $returnData['form'] = "";
        if(empty($transactionId) || empty($token)){
            echo json_encode($returnData);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/busWebsitePaymentProcess/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $returnData['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $returnData = array_merge($returnData, $remote);
                } else {
                    $returnData['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($returnData);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            // $this->set(compact('transactionId'));
            $sqlOrder = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5,6,7) LIMIT 1");
            if(mysql_num_rows($sqlOrder)){
                $rowOrder = mysql_fetch_array($sqlOrder);
                $dateCreated = strtotime($rowOrder['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    // Function
                    include('includes/PayWayApiCheckout.php');
                    $sqlTicket   = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." LIMIT 1");
                    $rowTicket   = mysql_fetch_array($sqlTicket);
                    $req_time      = time();
                    $amount        = $rowOrder['total_amount'] + $rowOrder['total_vat'] + $rowOrder['lucky_draw_fee'] + $rowOrder['service_fee'] - $rowOrder['discount_amount'];
                    $apiKey        = ABA_PAYWAY_API_KEY;
                    $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
                    $paymentOption = "";
                    $lifeTime      = 10; // 10 minute
                    if($rowOrder['payment_method_id'] == 5) {
                        $paymentOption = 'abapay_khqr';
                    } else if($rowOrder['payment_method_id'] == 6){
                        $paymentOption = 'cards';
                    } else if($rowOrder['payment_method_id'] == 7) {
                        $paymentOption = 'alipay';
                    }
                    if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                        $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                        $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;           
                    }
                    // ABA payment success route to booking complete
                    $returnUrl = base64_encode(PAYMENT_URL."busWebsiteAbaPayComplete/".$transactionId."/".$token);
                    // ABA route to payment success page
                    $continueSuccess = WEB_BUS_SUCCESS_PAGE."payment-success?transactionId=".$transactionId;
                    // Generate Hash
                    $hash = base64_encode(hash_hmac('sha512', $req_time .$merchant_id . $transactionId . $amount .$paymentOption. $returnUrl. $continueSuccess .$lifeTime, $apiKey, true));
                    $returnData['status'] = 1;
                    $returnData['form']  = '<form method="POST" target="aba_webservice" action="'.PayWayApiCheckout::getApiUrl().'" id="aba_merchant_request">';
                    $returnData['form'] .= '<input type="hidden" name="hash" value="'.$hash.'" id="hash"/>';
                    $returnData['form'] .= '<input type="hidden" name="tran_id" value="'.$transactionId.'" id="tran_id"/>';
                    $returnData['form'] .= '<input type="hidden" name="amount" value="'.$amount.'" id="amount"/>';
                    $returnData['form'] .= '<input type="hidden" name="req_time" value="'.$req_time.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="merchant_id" value="'.$merchant_id.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="payment_option" value="'.$paymentOption.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="payment_gate" value="0"/>';
                    $returnData['form'] .= '<input type="hidden" name="lifetime" value="'.$lifeTime.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="return_url" value="'.$returnUrl.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="continue_success_url" value="'.$continueSuccess.'"/>';
                    $returnData['form'] .= '</form>';
                }
            }
            echo json_encode($returnData);
            exit;
        } else {
            echo json_encode($returnData);
            exit;
        }
    }

    function busWebsiteAbaPayComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response  = array();
        $response['transactionCode'] = $transactionId;
        $response['status'] = "0";
        if(empty($transactionId) || empty($token)){
            $response['error']  = "Invalid Data";
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/busWebsiteAbaPayComplete/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5, 6, 7) LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                // Process Complete
                include('includes/PayWayApiCheckout.php');
                $rowChk       = mysql_fetch_array($sqlChk);
                $sqlTicket    = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                $rowTicket    = mysql_fetch_array($sqlTicket);
                // Vireak Buntham
                $apiKey       = ABA_PAYWAY_API_KEY;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
                if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                    $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;           
                }
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
                    'Referer: '.PAYMENT_URL_REF
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
                if ($curl_errno > 0) {
                    $response['error']  = "Check Payment";
                    $response['status'] = 0;
                } else {
                    $output = json_decode($result, true);
                    if($output['status'] == 0 && $output['description'] == "approved"){
                        // Delete Token
                        $rowToken = mysql_fetch_array($sqlToken);
                        mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                        $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                        if(mysql_num_rows($sqlTmp)){
                            // Update Order
                            mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                            // Update Ticket Tmp
                            $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                // Update Ticket Tmp
                                mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Tmp to Ticket
                                mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                             SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, ".$rowChk['payment_method_id'].", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Website', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Detail Tmp to Ticket Detail
                                mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `markup`, `total_amount`) 
                                             SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `service_fee`, `total_amount` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                                mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                                // Update Seat Status
                                mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                            }
                            // Send Email
                            if(!empty($rowChk['email'])){
                                if($rowChk['email'] != 'user@gmail.com' && $rowChk['email'] != 'minapp@gmail.com' && $rowChk['email'] != 'miniappV2.30@gmail.com'){
                                    $this->Helper->ticketSendEmail($transactionId);
                                }
                            }
                            $response['status'] = "1";
                        }
                    }
                }
            } else {
                $response['error']  = "Invalid Order ID";
            }
        } else {
            $response['error']  = "Invalid Token";
        }
        echo json_encode($response);
        exit;
    }

    function terminalAbaPhoneCall($ticketId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($ticketId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('ticketId'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function terminalAbaAlipayPhoneCall($ticketId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($ticketId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('ticketId'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }


    function saveAbaPhoneCallComplete($ticketId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['id'] = $ticketId;
        $response['status'] = "0";
        if(empty($ticketId) || empty($token)){
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/saveAbaPhoneCallComplete/'.rawurlencode($ticketId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        // Process Complete
        include('includes/PayWayApiCheckout.php');
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            // Check Payment Method
            $sqlChk = mysql_query("SELECT * FROM t_tickets WHERE id = '".$ticketId."' AND status = 1 AND offline_project_id = 1 AND api_bank_ref != '' LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk        = mysql_fetch_array($sqlChk);
                $transactionId = $rowChk['api_bank_ref'];
                $apiKey        = ABA_PAYWAY_API_KEY;
                $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
                if($rowChk['company_id'] == 6 || $rowChk['company_id'] == 17){ // Buva Sea
                    $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                } else if($rowChk['company_id'] == 7 || $rowChk['company_id'] == 12){ // VET Air Bus
                    $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;                  
                }
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
                    'Referer: '.PAYMENT_URL_REF
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
                if ($curl_errno > 0) {
                    $response['status'] = 0;
                } else {
                    $output = json_decode($result, true);
                    if($output['status'] == 0 && $output['description'] == "approved"){
                        $sqlLastPay  = mysql_query("SELECT * FROM ticket_payments WHERE t_ticket_id = ".$rowChk['id']." ORDER BY id DESC LIMIT 1");
                        $rowLastPay  = mysql_fetch_array($sqlLastPay);
                        // Update Ticket Payment Complete
                        mysql_query("UPDATE ticket_payments SET completed = now(), status = 2 WHERE t_ticket_id = ".$rowChk['id']);
                        // Delete Token
                        $rowToken = mysql_fetch_array($sqlToken);
                        mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                        // Update Ticket Phone Call
                        mysql_query("UPDATE t_tickets SET status = -2, modified = now() WHERE id = ".$rowChk['id'].";");
                        // Move Ticket Phone Call to Ticket Sold
                        mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `api_bank_ref`, `pay_date`, `agt_refer_code`, `edit_from`, `is_vat`, `status`, `coupon_id`, `coupon_amount`) 
                                     SELECT `sys_code`, `offline_project_id`, ".$rowLastPay['payment_method_id'].", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, now(), `terminal_id`, now(), `price_type`, `type`, `api_bank_ref`, DATE(now()), 'Terminal Phone Call Pay', ".$rowChk['id'].", `is_vat`, 2, `coupon_id`, `coupon_amount` FROM t_tickets AS tmp WHERE id = ".$rowChk['id'].";");
                        $newTicketId = mysql_insert_id();
                        // Move Ticket Detail Phone Call to Ticket Detail Sold
                        mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                     SELECT `sys_code`, ".$newTicketId.", `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_details AS tmp WHERE t_ticket_id = ".$rowChk['id']); 
                        // Update Seat Status
                        mysql_query("UPDATE t_seat_controls SET status = 2, t_ticket_id = ".$newTicketId." WHERE t_ticket_id = ".$rowChk['id']);
                        // General Ticket Code
                        $tmpCode = 'VETPH'.date("ym");
                        mysql_query("INSERT INTO `ticket_codes` (`offline_project_id`, `code`) VALUES (1, '".$tmpCode."');");
                        $ticketCodeId = mysql_insert_id();
                        $sqlCount = mysql_query("SELECT COUNT(id) FROM ticket_codes WHERE code LIKE '".$tmpCode."%' AND offline_project_id = 1 AND id < ".$ticketCodeId.";");
                        $rowCount = mysql_fetch_array($sqlCount);
                        $modCode  = $tmpCode.str_pad(($rowCount[0] + 1),6,"0",STR_PAD_LEFT);
                        mysql_query("UPDATE t_tickets SET code = '".$modCode."', tmp_code = '".$tmpCode."', tmp_count = ".($rowCount[0] + 1)." WHERE id = ".$newTicketId);
                        $response['status'] = "1";
                    }
                }
            }
        }
        echo json_encode($response);
        exit;
    }

    function checkAbaTransactionPhoneCall($ticketId = null){
        $this->layout = 'ajax';
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header("Allow: POST, GET, OPTIONS, PUT, DELETE");
        if(empty($ticketId)){
            exit;
        }
        $response['status'] = 0;
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/checkAbaTransactionPhoneCall/'.$ticketId;
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        include('includes/PayWayApiCheckout.php');
        $sqlChk = mysql_query("SELECT * FROM t_tickets WHERE id = '".$ticketId."' AND status = 1 AND offline_project_id = 1 AND api_bank_ref != '' LIMIT 1");
        if(mysql_num_rows($sqlChk)){
            $rowChk        = mysql_fetch_array($sqlChk);
            $transactionId = $rowChk['api_bank_ref'];
            // Vireak Buntham
            $apiKey        = ABA_PAYWAY_API_KEY;
            $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
            if($rowChk['company_id'] == 6 || $rowChk['company_id'] == 17){ // Buva Sea
                $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
            } else if($rowChk['company_id'] == 7 || $rowChk['company_id'] == 12){ // VET Air Bus
                $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;           
            }
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
                'Referer: '.PAYMENT_URL_REF
            );
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, ABA_CHECK_TRANSACTION_API_URL);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $result      = curl_exec($curl); // Result Json
            $curl_errno  = curl_errno($curl);
            $curl_error  = curl_error($curl);
            curl_close ($curl);
            $response = array();
            if ($curl_errno > 0) {
                $response['status'] = 0;
            } else {
                $output = json_decode($result, true);
                if($output['status'] == 0 && $output['description'] == "approved"){
                    $response['status'] = 1;
                } else {
                    $response['status'] = 0;
                }
            }
        }
        echo json_encode($response);
        exit;
    }

    function websiteWingRequestPay($transactionId = null, $token = null, $amount = 0){
        $this->layout = 'ajax';
        $response = array();
        $response['request'] = '';
        if(empty($transactionId) || empty($token) || empty($amount)){
            $response['error'] = 1;
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            include("includes/NewWingWebConfig.php");
            $wingPay       = new WingWebsiteSdkRequest();
            $paymentAccess = $wingPay->request($transactionId, $token, $amount);
            $response['error']   = 0;
            $response['request'] = $paymentAccess;
        } else {
            $response['error'] = 2;
        }
        echo json_encode($response);
        exit;
    }

    function wingCheckStatus($transactionId = null){
        $this->layout = 'ajax';
        $response = array();
        $response['status'] = 0;
        if(!empty($transactionId)){
            include("includes/NewWingConfig.php");
            $wingPay = new WingSdkRequest();
            $checkPayStatus = $wingPay->checkTransactionStatus($transactionId);
            if($checkPayStatus['status'] == 1){
                $response['status'] = 1;
            }
        }
        echo json_encode($response);
        exit;
    }

    function wingNewApiPayment($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId', 'token'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function wingNewApiPaymentPro($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId', 'token'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function saveNewApiWingCompleted($transactionId = null, $token = null){
        $this->layout = 'payment';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data Post";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            include("includes/NewWingConfig.php");
            $wingPay = new WingSdkRequest();
            $checkPayStatus = $wingPay->checkTransactionStatus($transactionId);
            if($checkPayStatus['status'] == 1){
                mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `payment_info`, `created`, `status`) VALUES (NULL, '".$transactionId."', '".$transactionId."', '".$checkPayStatus['info']."', now(), 3);");
                // Delete Token
                $rowToken = mysql_fetch_array($sqlToken);
                mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                // Update Complete
                $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 4 LIMIT 1");
                if(mysql_num_rows($sqlChk)){
                    $rowChk = mysql_fetch_array($sqlChk);
                    $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                    if(mysql_num_rows($sqlTmp)){
                        // Update Order
                        mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                        // Update Ticket Tmp
                        $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            // Update Ticket Tmp
                            mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                            // Move Ticket Tmp to Ticket
                            mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                         SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Terminal', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                            // Move Ticket Detail Tmp to Ticket Detail
                            mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                         SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                            // Update Ticket Tmp Status
                            mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                            // Update Seat Status
                            mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                        }
                        // Insert into wing promotion tracking
                        if($rowChk['payment_method_discount_amount'] > 0){
                            mysql_query("INSERT INTO mini_app_promotion_transactions (`online_order_id`, `telephone`, `total_amount`, `discount`, `created`, `type`) 
                                         VALUES (".$rowChk['id'].", '".$rowChk['contact_telephone']."', ".$rowChk['total_amount'].", ".$rowChk['payment_method_discount_amount'].", now(), 2);");
                        }
                        $this->set(compact('transactionId'));
                    } else {
                        echo "Invalid Booking Data";
                        exit;
                    }
                } else {
                    echo "Invalid Transaction ID";
                    exit;
                }
            } else {
                echo "Invalid Payment Status";
                exit;
            }
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function saveApiCheckTransaction(){
        $this->layout = 'ajax';
        if(!empty($_POST['response']) && !empty($_POST['trasactionNo'])){
            mysql_query("INSERT INTO `website_api_logs` (`id`, `code`, `response`, `created`) 
                         VALUES (NULL, '".$_POST['trasactionNo']."', '".$_POST['response']."', now());");
        }
        exit;
    }

    function checkOrderPayment($telephone){
        $this->layout = 'ajax';
        $returnStatus['status']  = 0;
        if(empty($telephone)){
            echo json_encode($returnStatus);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/checkOrderPayment/'.$telephone;
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $returnStatus['status'] = 0;
                $returnStatus['info']   = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $returnStatus = array_merge($returnStatus, $remote);
                } else {
                    $returnStatus['status'] = 0;
                    $returnStatus['info']   = 'Invalid remote response';
                }
            }
            echo json_encode($returnStatus);
            exit;
        }
        $sqlOrder = mysql_query("SELECT * FROM online_orders WHERE status IN (1,2) AND click_payment = 1 AND input_type = 1 AND offline_project_id = 1 AND contact_telephone = '".$telephone."';");
        while($rowOrder = mysql_fetch_array($sqlOrder)){
            $checkPayment = false;
            if($rowOrder['payment_method_id'] == 5 || $rowOrder['payment_method_id'] == 6 || $rowOrder['payment_method_id'] == 7){ // ABA
                include('includes/PayWayApiCheckout.php');
                $sqlTComp    = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." LIMIT 1");
                $rowTComp    = mysql_fetch_array($sqlTComp);
                $apiKey       = ABA_PAYWAY_API_KEY;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
                if($rowTComp['company_id'] == 6 || $rowTComp['company_id'] == 17){ // Buva Sea
                    $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                } else if($rowTComp['company_id'] == 7 || $rowTComp['company_id'] == 12){ // VET Air Bus
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
                    'Referer: '.PAYMENT_URL_REF
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
                    
                } else {
                    $output = json_decode($result, true);
                    if($output['status'] == 0 && $output['description'] == "approved"){
                        $checkPayment = true;
                    }
                }
            } else if($rowOrder['payment_method_id'] == 8){ // ACLEDA
                include('includes/AcledaCheckout.php');
                $amount  = ($rowOrder['total_amount'] + $rowOrder['total_vat'] + $rowOrder['lucky_draw_fee'] - $rowOrder['discount_amount']);
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
                            'Referer: '.PAYMENT_URL_REF
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
                        if ($curl_errno > 0) {
                            
                        } else {
                            $result = json_decode($curlResp, true);
                            if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS" && $result['result']['xTran']['paymentTokenid'] == $rowAcledaStatus['aclenda_payment_token_id'] && $result['result']['xTran']['invoiceid'] == $rowOrder['code'] && $result['result']['xTran']['purchaseAmount'] == $amount){
                                $checkPayment = true;
                            }
                        }
                    }
                }
            }
            // Complete Order
            if($checkPayment){
                $orderId = (int)$rowOrder['id'];
                $sqlTicket  = mysql_query("SELECT id FROM t_ticket_api_tmps WHERE online_order_id = ".$orderId." AND status = 1");
                if(mysql_num_rows($sqlTicket)){
                    $tmpIds = array();
                    while($rowTicket = mysql_fetch_assoc($sqlTicket)){
                        $tmpIds[] = (int)$rowTicket['id'];
                    }
                    if(!empty($tmpIds)){
                        $tmpIdList = implode(',', $tmpIds);
                        // Update Order
                        mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$orderId.";");
                        // Update Ticket Tmp
                        mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id IN (".$tmpIdList.");");
                        // Move Ticket Tmp to Ticket
                        mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                     SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Terminal', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id IN (".$tmpIdList.");");
                        // Move Ticket Detail Tmp to Ticket Detail
                        mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                     SELECT d.`sys_code`, t.`id`, d.`seat_number`, d.`label_number`, d.`gender`, d.`name`, d.`telephone`, d.`passport`, d.`dob`, d.`nationally_id`, d.`unit_price`, d.`vat_price`, d.`discount`, d.`total_amount`, d.`service_fee`, d.`nationally`
                                     FROM t_ticket_detail_api_tmps AS d
                                     INNER JOIN t_ticket_api_tmps AS tmp ON tmp.id = d.t_ticket_api_tmp_id
                                     INNER JOIN t_tickets AS t ON t.sys_code = tmp.sys_code AND t.online_order_id = tmp.online_order_id
                                     WHERE tmp.id IN (".$tmpIdList.");");
                        // Mark tmp tickets processed
                        mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id IN (".$tmpIdList.");");
                        // Update Seat Status
                        mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id IN (".$tmpIdList.");");
                    }
                }
            }
        }
        $returnStatus['status']  = 1;
        echo json_encode($returnStatus);
        exit;
    }

    function abaMobilePayPackage($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['status']  = 0;
        $response['info']    = "Your token has been expired!";
        $response['qr_code'] = "";
        $response['abapay_deeplink'] = "";
        $response['app_store']  = "";
        $response['play_store'] = "";
        $response['checkout_qr_url'] = "";
        if(empty($transactionId) || empty($token)){
            $response['info']    = "Invalid data post Transaction ID or Token";
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/abaMobilePayPackage/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                }
            }
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM travel_package_orders WHERE code = '".$transactionId."' AND status = 1 AND payment_method_id = 5 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowChk['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    include('includes/PayWayApiCheckout.php');
                    $req_time      = time();
                    $transactionId = $rowChk['code'];
                    $amount        = $rowChk['package_price'];
                    $apiKey        = ABA_PAYWAY_API_KEY;
                    $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
                    $paymentOption = 'abapay_khqr_deeplink';
                    $lifeTime      = 10; // 10 minutes
                    // ABA payment success route to booking complete
                    $returnUrl = base64_encode(PAYMENT_URL."packagePaymentComplete/".$transactionId."/".$token);
                    // ABA route to payment success page
                    $continueSuccess = "vetapp://payment/abaMobilePayPackage";
                    $hash = base64_encode(hash_hmac('sha512', $req_time .$merchant_id . $transactionId . $amount .$paymentOption. $returnUrl. $continueSuccess .$lifeTime, $apiKey, true));
                    // CURL
                    $url  = PayWayApiCheckout::getApiUrl();
                    $post = [
                        'hash'     => $hash,
                        'tran_id'  => $transactionId,
                        'amount'   => $amount,
                        'req_time' => $req_time,
                        'merchant_id'  => $merchant_id,
                        'payment_option'  => $paymentOption,
                        'payment_gate' => 0,
                        'return_url'  => $returnUrl,
                        'continue_success_url'  => $continueSuccess,
                        'lifetime' => $lifeTime
                    ];
                    $headers = array(
                        'accept: */*',
                        'Content-Type: multipart/form-data',
                        'Referer: '.PAYMENT_URL_REF
                    );
                    // CURL
                    $curl  = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    $curlResp     = curl_exec($curl);
                    $curl_errno   = curl_errno($curl);
                    $curl_error   = curl_error($curl);
                    curl_close ($curl);
                    if ($curl_errno > 0) {
                        $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $result = json_decode($curlResp, true);
                        if($result['status']['code'] == "00" && $result['status']['message'] == "Success!"){
                            $response['status']  = 1;
                            $response['info']    = "Success";
                            if(!empty($result['qr_string'])){
                                $response['qr_code'] = $result['qr_string'];
                            } else {
                                $response['qr_code'] = "payment";
                            }
                            $response['abapay_deeplink'] = $result['abapay_deeplink'];
                            if(!empty($result['app_store'])){
                                $response['app_store']  = $result['app_store'];   
                            } else {
                                $response['app_store']  = "";
                            }
                            if(!empty($result['play_store'])){
                                $response['play_store']  = $result['play_store'];   
                            } else {
                                $response['play_store']  = "";
                            }
                            if(!empty($result['checkout_qr_url'])){
                                $response['checkout_qr_url'] = $result['checkout_qr_url'];
                            } else {
                                $response['checkout_qr_url'] = "";
                            }
                            // Update Pay Date
                            mysql_query("UPDATE travel_package_orders SET pay_date = now() WHERE id = ".$rowChk['id']);
                        } else {
                            $response['status']  = 0;
                            $response['info']    = $result['description'];
                        }
                    }
                } else {
                    $response['info'] = "Token expired";
                }
            } else {
                $response['info'] = "Invalid Transaction";
            }
        } else {
            $response['info'] = "Invalid Token";
        }
        echo json_encode($response);
        exit;
    }

    function abaVisalPaymentPackage($transactionId = null, $token = null){
        $this->layout = 'ajax';
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $this->set(compact('transactionId', 'token'));
        } else {
            echo "Invalid Token";
            exit;
        }
    }

    function packagePaymentComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        $response['status'] = 0;
        if(empty($transactionId) || empty($token)){
            $response['error'] = 2;
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/packagePaymentComplete/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        // Process Complete
        include('includes/PayWayApiCheckout.php');
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
           // Check Payment Method
           $sqlChk = mysql_query("SELECT * FROM travel_package_orders WHERE code = '".$transactionId."' AND status = 1 AND payment_method_id IN (5, 6) LIMIT 1");
           if(mysql_num_rows($sqlChk)){
               $rowChk        = mysql_fetch_array($sqlChk);
               $transactionId = $rowChk['code'];
               $apiKey        = ABA_PAYWAY_API_KEY;
               $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
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
                   'Referer: '.PAYMENT_URL_REF
               );
               $curl = curl_init();
               curl_setopt($curl, CURLOPT_URL, ABA_CHECK_TRANSACTION_API_URL);
               curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
               curl_setopt($curl, CURLOPT_POST, 1);
               curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
               curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
               curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
               curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
               $result      = curl_exec($curl); // Result Json
               $curl_errno  = curl_errno($curl);
               $curl_error  = curl_error($curl);
               curl_close ($curl);
               if ($curl_errno > 0) {
                   $response['status'] = 0;
               } else {
                   $output = json_decode($result, true);
                   if($output['status'] == 0 && $output['description'] == "approved"){
                        $sqlPackage  = mysql_query("SELECT * FROM travel_packages WHERE id = ".$rowChk['travel_package_id']);
                        $rowPackage  = mysql_fetch_array($sqlPackage);
                        $dateNow     = strtotime(date("Y-m-d"));
                        $dateExpired = date("Y-m-d", strtotime("+".$rowPackage['period_expired']." month", $dateNow));
                        $codePackage = "VP_".$this->Helper->generatePackageCode(8);
                        mysql_query("UPDATE travel_package_orders SET status = 2, package_date = '".date("Y-m-d")."', package_code = '".$codePackage."', package_expired = '".$dateExpired."', modified = now() WHERE id = ".$rowChk['id']);
                        $response['status'] = "1";
                   }
               }
           }
       }
       echo json_encode($response);
       exit;
    }

    function checkTravelPackageStatus($transactionId = null){
        $this->layout = 'ajax';
        $result = array();
        $result['status'] = 0;
        if(empty($transactionId)){
            echo json_encode($result);
            exit;
        }
        $sqlPackage = mysql_query("SELECT status FROM travel_package_orders WHERE code = '".$transactionId."' LIMIT 1");
        if(mysql_num_rows($sqlPackage)){
            $rowPackage = mysql_fetch_array($sqlPackage);
            if($rowPackage['status'] == 2){
                $result['status'] = 1;
            }
        }
        echo json_encode($result);
        exit;
    }

    function wingNewWebsiteComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $result = array();
        $result['status'] = 0;
        if(empty($transactionId) || empty($token)){
            $result['error'] = 2;
            echo json_encode($result);
            exit;
        } 
        include("includes/NewWingWebConfig.php");
        $wingPay = new WingWebsiteSdkRequest();
        $checkPayStatus = $wingPay->checkTransactionStatus($transactionId);
        if($checkPayStatus['status'] == 1){
            $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
            if(mysql_num_rows($sqlToken)){
                mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `payment_info`, `created`, `status`) VALUES (NULL, '".$transactionId."', '".$transactionId."', '".$checkPayStatus['info']."', now(), 3);");
                // Delete Token
                $rowToken = mysql_fetch_array($sqlToken);
                mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                // Update Complete
                $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 4 LIMIT 1");
                if(mysql_num_rows($sqlChk)){
                    $rowChk = mysql_fetch_array($sqlChk);
                    $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                    if(mysql_num_rows($sqlTmp)){
                        // Update Order
                        mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                        // Update Ticket Tmp
                        $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            // Update Ticket Tmp
                            mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                            // Move Ticket Tmp to Ticket
                            mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                         SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, 4, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Website', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                            // Move Ticket Detail Tmp to Ticket Detail
                            mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `markup`, `nationally`) 
                                         SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `passport`, `dob`, `nationally_id`, `unit_price`, `vat_price`, `discount`, `total_amount`, `service_fee`, `nationally` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                            mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                            // Update Seat Status
                            mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                        }
                        // Insert into wing promotion tracking
                        if($rowChk['payment_method_discount_amount'] > 0){
                            mysql_query("INSERT INTO mini_app_promotion_transactions (`online_order_id`, `email`, `total_amount`, `discount`, `created`, `type`) 
                                         VALUES (".$rowChk['id'].", '".$rowChk['email']."', ".$rowChk['total_amount'].", ".$rowChk['payment_method_discount_amount'].", now(), 2);");
                        }
                        // Send Email
                        if(!empty($rowChk['email'])){
                            if($rowChk['email'] != 'user@gmail.com' && $rowChk['email'] != 'minapp@gmail.com' && $rowChk['email'] != 'miniappV2.30@gmail.com'){
                                $this->Helper->ticketSendEmail($transactionId);
                            }
                        }
                        $result['status'] = 1;
                        $result['error']  = 0;
                    } else {
                        $result['error'] = 1;
                    }
                } else {
                    $result['error'] = 3;
                }
            } else {
                $result['error'] = 4;
            }
            header('Location: '.WEB_BUS_SUCCESS_PAGE."payment-success?transactionId=".$transactionId);
        } else {
            $result['error'] = 5;
            echo json_encode($result);
        }
        exit;
    }

    function busWebsitePackagePaymentProcess($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $returnData['status'] = 0;
        $returnData['form'] = "";
        if(empty($transactionId) || empty($token)){
            echo json_encode($returnData);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/busWebsitePackagePaymentProcess/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $returnData['info'] = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $returnData = array_merge($returnData, $remote);
                } else {
                    $returnData['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($returnData);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            // $this->set(compact('transactionId'));
            $sqlOrder = mysql_query("SELECT * FROM travel_package_orders WHERE code = '".$transactionId."' AND status = 1 AND payment_method_id IN (5,6,7) LIMIT 1");
            if(mysql_num_rows($sqlOrder)){
                $rowOrder = mysql_fetch_array($sqlOrder);
                $dateCreated = strtotime($rowOrder['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    // Function
                    include('includes/PayWayApiCheckout.php');
                    $req_time      = time();
                    $amount        = $rowOrder['package_price'];
                    $apiKey        = ABA_PAYWAY_API_KEY;
                    $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
                    $paymentOption = "";
                    $lifeTime      = 10; // 10 minute
                    if($rowOrder['payment_method_id'] == 5) {
                        $paymentOption = 'abapay_khqr';
                    } else if($rowOrder['payment_method_id'] == 6){
                        $paymentOption = 'cards';
                    } else if($rowOrder['payment_method_id'] == 7) {
                        $paymentOption = 'alipay';
                    }
                    // New Website QA
                    // ABA payment success route to booking complete
                    $returnUrl = base64_encode(PAYMENT_URL."packagePaymentComplete/".$transactionId."/".$token);
                    // ABA route to payment success page
                    $continueSuccess = WEB_BUS_SUCCESS_PAGE."payment-travel-pksuccess";
                    // Generate Hash
                    $hash = base64_encode(hash_hmac('sha512', $req_time .$merchant_id . $transactionId . $amount .$paymentOption. $returnUrl. $continueSuccess .$lifeTime, $apiKey, true));
                    $returnData['status'] = 1;
                    $returnData['form']  = '<form method="POST" target="aba_webservice" action="'.PayWayApiCheckout::getApiUrl().'" id="aba_merchant_request">';
                    $returnData['form'] .= '<input type="hidden" name="hash" value="'.$hash.'" id="hash"/>';
                    $returnData['form'] .= '<input type="hidden" name="tran_id" value="'.$transactionId.'" id="tran_id"/>';
                    $returnData['form'] .= '<input type="hidden" name="amount" value="'.$amount.'" id="amount"/>';
                    $returnData['form'] .= '<input type="hidden" name="req_time" value="'.$req_time.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="merchant_id" value="'.$merchant_id.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="payment_option" value="'.$paymentOption.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="payment_gate" value="0"/>';
                    $returnData['form'] .= '<input type="hidden" name="lifetime" value="'.$lifeTime.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="return_url" value="'.$returnUrl.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="continue_success_url" value="'.$continueSuccess.'"/>';
                    $returnData['form'] .= '</form>';
                }
            }
            echo json_encode($returnData);
            exit;
        } else {
            echo json_encode($returnData);
            exit;
        }
    }

    function busWebsiteAcledaXpay($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response = array();
        if(empty($transactionId) || empty($token)){
            echo "Invalid data post Transaction ID or Token";
            exit;
        }
        $response['status']    = 0;
        $response['info']      = "Your token has been expired!";
        $response['sessionId'] = "";
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 8 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowChk['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    include('includes/AcledaCheckout.php');
                    $sqlTicket   = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                    $rowTicket   = mysql_fetch_array($sqlTicket);
                    $companyName = "Vireak Buntham";
                    $companyLogo = "";
                    $deeplinId   = 'VIREAKBT';
                    if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){ // Buva Sea
                        $deeplinId   = 'BUVASEA';
                        $companyName = "BUVA SEA";
                        $companyLogo = "";
                    } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){ // VET Air Bus
                        $deeplinId   = 'VETAIR';   
                        $companyName = "VET AIR BUS";
                        $companyLogo = "";           
                    }
                    $transactionId = $rowChk['code'];
                    $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount']);
                    // CURL
                    $url  = ACLENDA_DEPPLINK_API_URL."/openSessionV2";
                    $post = array(
                        'loginId' => ACLENDA_DEPPLINK_LOGINID,
                        'password' => ACLENDA_DEPPLINK_PASSWORD,
                        'merchantID' => ACLENDA_DEPPLINK_MERCHANT_ID,
                        'signature' => ACLENDA_DEPPLINK_SIGNATURE
                    );
                    $post['xpayTransaction']['txid'] = $transactionId;
                    $post['xpayTransaction']['purchaseAmount']   = $amount;
                    $post['xpayTransaction']['purchaseCurrency'] = "USD";
                    $post['xpayTransaction']['purchaseDate'] = $rowChk['date'];
                    $post['xpayTransaction']['purchaseDesc'] = "Vireak Buntham Website Booking Payment";
                    $post['xpayTransaction']['invoiceid']    = $transactionId;
                    $post['xpayTransaction']['item']         = "1";
                    $post['xpayTransaction']['quantity']     = "1";
                    $post['xpayTransaction']['expiryTime']   = "10";
                    $post['xpayTransaction']['paymentCard']  = "1";
                    $post['xpayTransaction']['deeplinkId']   = $deeplinId;

                    $headers = array(
                        'accept: */*',
                        'Content-Type: application/json',
                        'Referer: '.PAYMENT_URL_REF
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
                    if ($curl_errno > 0) {
                        $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $result = json_decode($curlResp, true);
                        if($result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS"){
                            $response['status']         = 1;
                            $response['info']           = "Success";
                            $response['url']            = ACLENDA_XPAY_API_URL;
                            $response['url_success']    = ACLENDA_CALLBACK_PAYMENT_SUCCESS;
                            $response['url_cancel']     = ACLENDA_CALLBACK_PAYMENT_CANCEL;
                            $response['merchantID']     = ACLENDA_DEPPLINK_MERCHANT_ID;
                            $response['token']          = $token;
                            $response['sessionId']      = $result['result']['sessionid'];
                            $response['paymenttokenid'] = $result['result']['xTran']['paymentTokenid'];
                            $response['description']    = $post['xpayTransaction']['purchaseDesc'];
                            $response['expirytime']     = $post['xpayTransaction']['expiryTime'];
                            $response['amount']         = $post['xpayTransaction']['purchaseAmount'];
                            $response['quantity']       = $post['xpayTransaction']['quantity'];
                            $response['item']           = $post['xpayTransaction']['item'];
                            $response['invoiceid']      = $post['xpayTransaction']['invoiceid'];
                            $response['currencytype']   = $post['xpayTransaction']['purchaseCurrency'];
                            $response['transactionID']  = $post['xpayTransaction']['invoiceid'];
                            $response['companyName']    = $companyName;
                            $response['companyLogo']    = $companyLogo;
                            mysql_query("INSERT INTO `acleda_access_transactions` (`id`, `online_order_id`, `aclenda_payment_token_id`, `created`, `modified`, `status`) 
                                         VALUES (NULL, ".$rowChk['id'].", '".$result['result']['xTran']['paymentTokenid']."', now(), NULL, '1');");
                        } else {
                            $response['status']  = 0;
                            $response['info']    = $result['result']['errorDetails'];
                        }
                    }
                } else {
                    $response['info'] = "Token expired";
                }
            } else {
                $response['info'] = "Invalid Transaction";
            }
        } else {
            $response['info'] = "Invalid Token";
        }
        if($response['status'] == 0){
            echo $response['info'];
            exit;
        } else {
            $this->set('response', $response);
        }
    }

    function buvaSeaWebsitePaymentProcess($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $returnData['status'] = 0;
        $returnData['form'] = "";
        if(empty($transactionId) || empty($token)){
            echo json_encode($returnData);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/buvaSeaWebsitePaymentProcess/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $returnData['info'] = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $returnData = array_merge($returnData, $remote);
                } else {
                    $returnData['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($returnData);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            // $this->set(compact('transactionId'));
            $sqlOrder = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5,6,7) LIMIT 1");
            if(mysql_num_rows($sqlOrder)){
                $rowOrder = mysql_fetch_array($sqlOrder);
                $dateCreated = strtotime($rowOrder['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    // Function
                    include('includes/PayWayApiCheckout.php');
                    $sqlTicket   = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowOrder['id']." LIMIT 1");
                    $rowTicket   = mysql_fetch_array($sqlTicket);
                    $req_time      = time();
                    $amount        = $rowOrder['total_amount'] + $rowOrder['total_vat'] + $rowOrder['lucky_draw_fee'] + $rowOrder['service_fee'] - $rowOrder['discount_amount'];
                    $apiKey        = ABA_PAYWAY_API_KEY_BUVASEA;
                    $merchant_id   = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                    $paymentOption = "";
                    $lifeTime      = 10; // 10 minute
                    if($rowOrder['payment_method_id'] == 5) {
                        $paymentOption = 'abapay_khqr';
                    } else if($rowOrder['payment_method_id'] == 6){
                        $paymentOption = 'cards';
                    } else if($rowOrder['payment_method_id'] == 7) {
                        $paymentOption = 'alipay';
                    }
                    // ABA payment success route to booking complete
                    $returnUrl = base64_encode(PAYMENT_URL."buvaSeaWebsiteAbaPayComplete/".$transactionId."/".$token);
                    // ABA route to payment success page
                    $continueSuccess = WEB_BUVA_SEA_SUCCESS_PAGE."payment-success?transactionId=".$transactionId;
                    // Generate Hash
                    $hash = base64_encode(hash_hmac('sha512', $req_time .$merchant_id . $transactionId . $amount .$paymentOption. $returnUrl. $continueSuccess .$lifeTime, $apiKey, true));
                    $returnData['status'] = 1;
                    $returnData['form']  = '<form method="POST" target="aba_webservice" action="'.PayWayApiCheckout::getApiUrl().'" id="aba_merchant_request">';
                    $returnData['form'] .= '<input type="hidden" name="hash" value="'.$hash.'" id="hash"/>';
                    $returnData['form'] .= '<input type="hidden" name="tran_id" value="'.$transactionId.'" id="tran_id"/>';
                    $returnData['form'] .= '<input type="hidden" name="amount" value="'.$amount.'" id="amount"/>';
                    $returnData['form'] .= '<input type="hidden" name="req_time" value="'.$req_time.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="merchant_id" value="'.$merchant_id.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="payment_option" value="'.$paymentOption.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="payment_gate" value="0"/>';
                    $returnData['form'] .= '<input type="hidden" name="lifetime" value="'.$lifeTime.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="return_url" value="'.$returnUrl.'"/>';
                    $returnData['form'] .= '<input type="hidden" name="continue_success_url" value="'.$continueSuccess.'"/>';
                    $returnData['form'] .= '</form>';
                }
            }
            echo json_encode($returnData);
            exit;
        } else {
            echo json_encode($returnData);
            exit;
        }
    }

    function buvaSeaWebsiteAbaPayComplete($transactionId = null, $token = null){
        $this->layout = 'ajax';
        $response  = array();
        $response['transactionCode'] = $transactionId;
        $response['status'] = "0";
        if(empty($transactionId) || empty($token)){
            $response['error']  = "Invalid Data";
            echo json_encode($response);
            exit;
        }
        $proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $currentBaseUrl = $proto.'://'.$_SERVER['HTTP_HOST'].'/';
        if($currentBaseUrl == 'https://ocvetticketn.utlog.net/'){
            $curlUrl = 'https://vetticket.utlog.net/payments/buvaSeaWebsiteAbaPayComplete/'.rawurlencode($transactionId).'/'.rawurlencode($token);
            $curl  = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResp     = curl_exec($curl);
            $curl_errno   = curl_errno($curl);
            $curl_error   = curl_error($curl);
            curl_close($curl);
            if($curl_errno > 0){
                $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
            } else {
                $remote = json_decode($curlResp, true);
                if(is_array($remote)){
                    $response = array_merge($response, $remote);
                } else {
                    $response['info'] = 'Invalid remote response';
                }
            }
            echo json_encode($response);
            exit;
        }
        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if(mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id IN (5, 6, 7) LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                // Process Complete
                include('includes/PayWayApiCheckout.php');
                $rowChk       = mysql_fetch_array($sqlChk);
                $sqlTicket    = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                $rowTicket    = mysql_fetch_array($sqlTicket);
                // Vireak Buntham
                $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
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
                    'Referer: '.PAYMENT_URL_REF
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
                if ($curl_errno > 0) {
                    $response['error']  = "Check Payment";
                    $response['status'] = 0;
                } else {
                    $output = json_decode($result, true);
                    if($output['status'] == 0 && $output['description'] == "approved"){
                        // Delete Token
                        $rowToken = mysql_fetch_array($sqlToken);
                        mysql_query("DELETE FROM payment_tokens WHERE id = ".$rowToken['id']);
                        $sqlTmp = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
                        if(mysql_num_rows($sqlTmp)){
                            // Update Order
                            mysql_query("UPDATE online_orders SET status = 4, modified = now() WHERE id = ".$rowChk['id'].";");
                            // Update Ticket Tmp
                            $sqlTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']);
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                // Update Ticket Tmp
                                mysql_query("UPDATE t_ticket_api_tmps SET status = 2 WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Tmp to Ticket
                                mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`, `coupon_id`, `coupon_amount`) 
                                             SELECT `sys_code`, `offline_project_id`, `online_order_id`, `user_logistic_id`, ".$rowChk['payment_method_id'].", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `service_fee`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, 'Website', `is_vat`, `coupon_id`, `coupon_amount` FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id'].";");
                                // Move Ticket Detail Tmp to Ticket Detail
                                mysql_query("INSERT INTO t_ticket_details (`sys_code`, `t_ticket_id`, `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `markup`, `total_amount`) 
                                             SELECT `sys_code`, (SELECT id FROM t_tickets WHERE sys_code = '".$rowTicket['sys_code']."' LIMIT 1), `seat_number`, `label_number`, `gender`, `name`, `telephone`, `nationally`, `passport`, `dob`, `nationally_id`, `unit_price`, `discount`, `service_fee`, `total_amount` FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = (SELECT id FROM t_ticket_api_tmps WHERE id = ".$rowTicket['id']." LIMIT 1);"); 
                                mysql_query("UPDATE t_ticket_api_tmps SET status = -3 WHERE id = ".$rowTicket['id'].";");
                                // Update Seat Status
                                mysql_query("UPDATE t_seat_controls SET status = 2 WHERE t_ticket_api_tmp_id = ".$rowTicket['id'].";");
                            }
                            // Send Email
                            if(!empty($rowChk['email'])){
                                if($rowChk['email'] != 'user@gmail.com' && $rowChk['email'] != 'minapp@gmail.com' && $rowChk['email'] != 'miniappV2.30@gmail.com'){
                                    $this->Helper->ticketSendEmail($transactionId);
                                }
                            }
                            $response['status'] = "1";
                        }
                    }
                }
            } else {
                $response['error']  = "Invalid Order ID";
            }
        } else {
            $response['error']  = "Invalid Token";
        }
        echo json_encode($response);
        exit;
    }

     function deeplinkAcledaPay($transactionId = null, $token = null, $oprDevice = null){
        $this->layout = 'ajax';

        $response = array(
            'status' => 0,
            'info' => "Your token has been expired!",
            'result' => array()
        );

        if(empty($transactionId) || empty($token)){
            $response['info'] = "Invalid data post Transaction ID or Token";
            echo json_encode($response);
            exit;
        }

        if(empty($oprDevice)){
            $oprDevice = "android";
        }

        $sqlToken = mysql_query("SELECT * FROM payment_tokens WHERE token = '".$token."' LIMIT 1");
        if($sqlToken && mysql_num_rows($sqlToken)){
            $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 8 LIMIT 1");
            if($sqlChk && mysql_num_rows($sqlChk)){
                $rowChk = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowChk['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s"));
                if($dateCreated > $dateNow){
                    include('includes/AcledaCheckout.php');
                    $transactionId = $rowChk['code'];
                    $amount = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount']);
                    // CURL
                    $url  = ACLENDA_DEPPLINK_API_URL."/openSessionV2";
                    $post = array(
                        'loginId' => ACLENDA_DEPPLINK_LOGINID,
                        'password' => ACLENDA_DEPPLINK_PASSWORD,
                        'merchantID' => ACLENDA_DEPPLINK_MERCHANT_ID,
                        'signature' => ACLENDA_DEPPLINK_SIGNATURE
                    );
                    $post['xpayTransaction']['txid']             = $transactionId;
                    $post['xpayTransaction']['purchaseAmount']   = $amount;
                    $post['xpayTransaction']['purchaseCurrency'] = "USD";
                    $post['xpayTransaction']['purchaseDate']     = $rowChk['date'];
                    $post['xpayTransaction']['purchaseDesc']     = "Vireak Buntham Website Booking Payment";
                    $post['xpayTransaction']['invoiceid']        = $transactionId;
                    $post['xpayTransaction']['item']             = "1";
                    $post['xpayTransaction']['quantity']         = "1";
                    $post['xpayTransaction']['expiryTime']       = "10";
                    $post['xpayTransaction']['oprDevice']        = $oprDevice;
                    $post['xpayTransaction']['callBackUrl']      = "https://qavetacledaminiapp.udaya-tech.com/#/success";
                    $post['xpayTransaction']['compName']         = "VET TICKET";

                    $headers = array(
                        'accept: */*',
                        'Content-Type: application/json; charset=utf-8',
                        'Referer: '.PAYMENT_URL_REF
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
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                    $curlResp     = curl_exec($curl);
                    $curl_errno   = curl_errno($curl);
                    $curl_error   = curl_error($curl);
                    curl_close ($curl);
                    if ($curl_errno > 0) {
                        $response['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $result = json_decode($curlResp, true);
                        if(is_array($result) && !empty($result['result']) && $result['result']['code'] == "0" && $result['result']['errorDetails'] == "SUCCESS"){
                            $response['status'] = 1;
                            $response['info'] = "Success";
                            $response['result'] = $result['result'];
                            $response['result']['sessionId'] = !empty($result['result']['sessionid']) ? $result['result']['sessionid'] : "";
                            $response['result']['deeplinkUrl'] = !empty($result['result']['deeplinkUrl']) ? $result['result']['deeplinkUrl'] : "";
                            $response['result']['paymenttokenid'] = !empty($result['result']['xTran']['paymentTokenid']) ? $result['result']['xTran']['paymentTokenid'] : "";

                            if(!empty($response['paymenttokenid'])){
                                mysql_query("INSERT INTO `acleda_access_transactions` (`id`, `online_order_id`, `aclenda_payment_token_id`, `created`, `modified`, `status`)
                                             VALUES (NULL, ".$rowChk['id'].", '".$response['paymenttokenid']."', now(), NULL, '1');");
                            }
                        } else {
                            $response['info'] = !empty($result['result']['errorDetails']) ? $result['result']['errorDetails'] : "Invalid ACLEDA response";
                        }
                    }
                } else {
                    $response['info'] = "Token expired";
                }
            } else {
                $response['info'] = "Invalid Transaction";
            }
        } else {
            $response['info'] = "Invalid Token";
        }

        echo json_encode($response);
        exit;
    }

}

?>

