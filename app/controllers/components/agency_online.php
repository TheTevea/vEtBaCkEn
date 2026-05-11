<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */
date_default_timezone_set('Asia/Phnom_Penh');

class AgencyOnlineComponent extends Object {

    function updateApiUser($post, $postType = null){
        $result['status'] = 0;
        $result['info']   = "Sync Failed";
        // Production
        // $url  = "https://tomapicaps.utlog.net/CamTicAgeAPi/auth/createApiKey";
        // Local
        // $url  = "http://localhost:8098/vetTkApiLink/auth/createApiKey";
        // QA
        $url  = OTP_API;
        $headerType = "";
        if(!empty($postType)){
            $headerType = "Content-Type: ".$postType;
        }
        $headers = array(
            'accept: */*',
            $headerType
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
            $result['status'] = 0;
            $result['info'] = "cURL Error ($curl_errno): $curl_error\n";
        } else {
            $response = json_decode($curlResp, true);
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
                if($response['header']['result'] == true){
                    $result['status'] = 1;
                    $result['info']   = $response['body'];
                }
            }
        }
        $return = $result;
        return $return;
    }

    function agencyAPiWebhook($journeyId, $action){
        $post = array();
        $post['action'] = $action;
        $journeyCode = "";
        $sqlJourney = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure, t_transportation_types.sys_code AS transportation_type_id, t_transportation_types.name AS transportation_type 
                                   FROM t_journeys 
                                   INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id
                                   INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                   WHERE t_journeys.id = ".$journeyId);
        if(mysql_num_rows($sqlJourney)){
            $rowJourney = mysql_fetch_array($sqlJourney);
            $post['schedule']['id'] = $rowJourney['sys_code'];
            $journeyCode = $rowJourney['sys_code'];
            if($action != "delete"){
                // Destination
                $destFrom = "";
                $destTo   = "";
                $sqlDest = mysql_query("SELECT * FROM t_destinations WHERE id IN (".$rowJourney['t_destination_from_id'].", ".$rowJourney['t_destination_to_id'].")");
                while($rowDest = mysql_fetch_array($sqlDest)){
                    if($rowDest == $rowJourney['t_destination_from_id']){
                        $destFrom = $rowDest['sys_code'];
                    } else {
                        $destTo = $rowDest['sys_code'];
                    }
                }
                // Boarding Point
                $sqlBoardingPoint = mysql_query("SELECT `sys_code` AS `id`, `name`, IFNULL(address, '') AS address, IFNULL(longs, '') AS longs, IFNULL(lats, '') AS lats
                                                FROM t_boarding_points
                                                WHERE id = (SELECT t_boarding_point_id FROM t_journey_boarding_points WHERE t_journey_id = (SELECT id FROM t_journeys WHERE id = ".$journeyId." LIMIT 1) LIMIT 1)");
                if(mysql_num_rows($sqlBoardingPoint)){
                    $rowBoardingPoint = mysql_fetch_array($sqlBoardingPoint);
                    $post['schedule']['boardingPointId'] = $rowBoardingPoint['id'];
                    $post['schedule']['boardingPoint']   = $rowBoardingPoint['name'];
                    $post['schedule']['boardingPointAddress'] = $rowBoardingPoint['address'];
                    $post['schedule']['boardingPointLats']    = $rowBoardingPoint['lats'];
                    $post['schedule']['boardingPointLongs']   = $rowBoardingPoint['longs'];
                } else {
                    $post['schedule']['boardingPointId'] = "";
                    $post['schedule']['boardingPoint']   = "";
                    $post['schedule']['boardingPointAddress'] = "";
                    $post['schedule']['boardingPointLats']    = "";
                    $post['schedule']['boardingPointLongs']   = "";
                }
                // Drop Off Point
                $sqlDropOff = mysql_query("SELECT `sys_code` AS `id`, `name`, IFNULL(address, '') AS address, IFNULL(longs, '') AS longs, IFNULL(lats, '') AS lats
                                           FROM t_drop_offs
                                           WHERE id = (SELECT t_drop_off_id FROM t_journey_drop_offs WHERE t_journey_id = (SELECT id FROM t_journeys WHERE id = ".$journeyId." LIMIT 1) LIMIT 1)");
                if(mysql_num_rows($sqlDropOff)){
                    $rowDropOff = mysql_fetch_array($sqlDropOff);
                    $post['schedule']['dropOffPointId'] = $rowDropOff['id'];
                    $post['schedule']['dropOffPoint']   = $rowDropOff['name'];
                    $post['schedule']['dropOffPointAddress'] = $rowDropOff['address'];
                    $post['schedule']['dropOffPointLats']    = $rowDropOff['lats'];
                    $post['schedule']['dropOffPointLongs']   = $rowDropOff['longs'];
                } else {
                    $post['schedule']['dropOffPointId'] = "";
                    $post['schedule']['dropOffPoint']   = "";
                    $post['schedule']['dropOffPointAddress'] = "";
                    $post['schedule']['dropOffPointLats']    = "";
                    $post['schedule']['dropOffPointLongs']   = "";
                }
                $post['schedule']['description'] = $rowJourney['description'];
                $post['schedule']['departure']   = $rowJourney['departure'];
                $post['schedule']['arrival']     = $rowJourney['arrival'];
                $post['schedule']['duration']    = $rowJourney['duration'];
                $post['schedule']['destinationFromId']    = $destFrom;
                $post['schedule']['destinationToId']      = $destTo;
                $post['schedule']['transportationTypeId'] = $rowJourney['transportation_type_id'];
                $post['schedule']['transportationType']   = $rowJourney['transportation_type'];
                // Check Price
                $promotionStart = "";
                $promotionEnd   = "";
                $promotionPrice = 0;
                $promotionPriceForeigner = 0;
                $price = $rowJourney['unit_price'];
                $priceForeigner = $rowJourney['foreigner_price'];
                $sqlPriceByJourney = mysql_query("SELECT `start`, `end`, price, foreigner_price, price_type AS type FROM t_journey_price_periods WHERE offline_project_id = 1 AND t_journey_id = ".$journeyId." AND '".date("Y-m-d")."' <= `start` AND status = 1 AND apply_type = 1 ORDER BY id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPriceByJourney)){
                    $rowPriceByJourney = mysql_fetch_array($sqlPriceByJourney);
                    $promotionStart = $rowPriceByJourney['start'];
                    $promotionEnd   = $rowPriceByJourney['end'];
                    $promotionPrice = $rowPriceByJourney['price'];
                    $promotionPriceForeigner = $rowPriceByJourney['foreigner_price'];
                } else {
                    $sqlPriceByDest = mysql_query("SELECT `start`, `end`, price, foreigner_price, price_type AS type FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$rowJourney['t_destination_from_id']." AND destination_to_id = ".$rowJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowJourney['t_transportation_type_id']." AND  '".date("Y-m-d")."' <= `start` AND status = 1 AND apply_type = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1;");
                    if(mysql_num_rows($sqlPriceByDest)){
                        $rowPriceByDest = mysql_fetch_array($sqlPriceByDest);
                        $promotionStart = $rowPriceByDest['start'];
                        $promotionEnd   = $rowPriceByDest['end'];
                        if($rowPriceByDest['type'] == 1){
                            $promotionPrice = $rowPriceByDest['price'];
                            $promotionPriceForeigner = $rowPriceByDest['foreigner_price'];
                        } else {
                            $promotionPrice = $price + $rowPriceByDest['price'];
                            $promotionPriceForeigner = $priceForeigner + $rowPriceByDest['foreigner_price'];
                        }
                    }
                }
                $promotionInternalStart = "";
                $promotionInternalEnd   = "";
                $promotionInternalPrice = "";
                $promotionInternalPriceForeigner = "";
                // Check Promotion Internal
                $sqlPriceByJourneyInternal = mysql_query("SELECT `start`, `end`, price, foreigner_price, price_type AS type FROM t_journey_price_periods WHERE offline_project_id = 1 AND t_journey_id = ".$journeyId." AND '".date("Y-m-d")."' <= `start` AND status = 1 AND apply_type = 2 ORDER BY id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPriceByJourneyInternal)){
                    $rowPriceByJourneyInternal = mysql_fetch_array($sqlPriceByJourneyInternal);
                    $promotionInternalStart = $rowPriceByJourneyInternal['start'];
                    $promotionInternalEnd   = $rowPriceByJourneyInternal['end'];
                    $promotionInternalPrice = $rowPriceByJourneyInternal['price'];
                    $promotionInternalPriceForeigner = $rowPriceByJourneyInternal['foreigner_price'];
                }
                // Calculage VAT
                if($rowJourney['company_id'] != 6){
                    if($price > 0 && $rowJourney['allow_price_period'] == 0){
                        // Selling Price
                        $vatPrice = ($price * 10) / 100;
                        $vatPriceForeigner = ($priceForeigner * 10) / 100;
                        $priceForeigner    = $priceForeigner + $vatPriceForeigner;
                        $price = $price + $vatPrice;
                        // Promotion Price
                        if($promotionPrice > 0){
                            $vatPromoPrice  = ($promotionPrice * 10) / 100;
                            $promotionPrice = $promotionPrice + $vatPromoPrice;
                        }
                        if($promotionPriceForeigner > 0){
                            $vatPromoPriceForeigner = ($promotionPriceForeigner * 10) / 100;
                            $promotionPriceForeigner = $promotionPriceForeigner + $vatPromoPriceForeigner;
                        }
                        // Promotion Price Internal
                        if($promotionInternalPrice > 0){
                            $vatPromoInternalPrice  = ($promotionInternalPrice * 10) / 100;
                            $promotionInternalPrice = $promotionInternalPrice + $vatPromoInternalPrice;
                        }
                        if($promotionInternalPriceForeigner > 0){
                            $vatPromoPriceInternalForeigner  = ($promotionInternalPriceForeigner * 10) / 100;
                            $promotionInternalPriceForeigner = $promotionInternalPriceForeigner + $vatPromoPriceInternalForeigner;
                        }
                    }
                }
                $post['schedule']['price']                   = floatval($price);
                $post['schedule']['priceForeigner']          = floatval($priceForeigner);
                $post['schedule']['promotionStart']          = $promotionStart;
                $post['schedule']['promotionEnd']            = $promotionEnd;
                $post['schedule']['promotionPrice']          = floatval($promotionPrice);
                $post['schedule']['promotionPriceForeigner'] = floatval($promotionPriceForeigner);
                if($rowJourney['company_id'] == 6){
                    $post['schedule']['scheduleType'] = 2;
                } else if($rowJourney['company_id'] == 7){
                    $post['schedule']['scheduleType'] = 3;
                } else {
                    $post['schedule']['scheduleType'] = 1;
                }
                if($rowJourney['allow_access'] != 1 && $rowJourney['allow_access'] != 3){ // Check Allow APi
                    $post['schedule']['status'] = 0;
                } else {
                    if($rowJourney['status'] == 1){ // Active
                        $post['schedule']['status'] = 1;
                    } else {
                        $post['schedule']['status'] = 0;
                    }
                }
            }
            // Send to Agency Api
            $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE `status` = 1 AND `type` = 3 AND webhook_url != ''");
            while($rowAgency = mysql_fetch_array($sqlAgency)){
                if($rowAgency['id'] == 47){ // VET DIGITAL (API)
                    $post['schedule']['promotionInternalStart']          = $promotionInternalStart;
                    $post['schedule']['promotionInternalEnd']            = $promotionInternalEnd;
                    $post['schedule']['promotionInternalPrice']          = floatval($promotionInternalPrice);
                    $post['schedule']['promotionInternalPriceForeigner'] = floatval($promotionInternalPriceForeigner);
                }
                $url  = $rowAgency['webhook_url'];
                $authToken = "";
                if($rowAgency['webhook_auth_type'] == 1 && !empty($rowAgency['webhook_token'])){
                    $authToken = "Authorization: Bearer ".$rowAgency['webhook_token'];
                }   
                $headerXSignature = "";
                if($rowAgency['webhook_auth_type'] == 3){ // Nham 24
                    $computedSignature = hash_hmac('sha256', json_encode($post), $rowAgency['oauth_token']);
                    $headerXSignature  = "X-Signature: ".$computedSignature;
                }
                $headers = array(
                    'accept: application/json',
                    'Content-Type: application/json',
                    $authToken,
                    $headerXSignature
                );
                // CURL
                $curl  = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                if($rowAgency['webhook_auth_type'] == 2){ // Basic Auth
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, $rowAgency['webhook_username'].":".$rowAgency['webhook_password']);
                }
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $curlResp     = curl_exec($curl);
                $curl_errno   = curl_errno($curl);
                $curl_error   = curl_error($curl);
                curl_close ($curl);
                // Save APi Logs
                $this->webhookApiSaveLogs($rowAgency['id'], $action, $journeyCode, $url, $post, $curlResp);
            }
        }
    }

    function webhookApiSaveLogs($agencyId, $name, $journeyId, $url, $post, $response){
        // Save APi Logs
        mysql_query("INSERT INTO `webhook_api_logs` (`id`, `t_agent_id`, `journey_id`, `name`, `api_url`, `request`, `response`, `created`) 
        VALUES (NULL, '".$agencyId."', '".$journeyId."', '".$name."', '".$url."', '".json_encode($post)."', '".$response."', now());");
    }

}

?>