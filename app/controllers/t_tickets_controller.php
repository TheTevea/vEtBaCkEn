<?php

class TTicketsController extends AppController {

    var $name = 'TTickets';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Dashboard');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'fields' => array('Company.id', 'Company.name'),'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
        $tDestinationFroms = ClassRegistry::init('TDestination')->find('all', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'], "TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))")));
        $tDepartureTimes = ClassRegistry::init('TDepartureTime')->find('all', array("conditions" => array("TDepartureTime.is_active = 1")));
        $this->set(compact('tDestinationFroms', 'companies', 'branches', 'tDepartureTimes'));
    }
    
    function viewTicket() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Dashboard');
    }

    function ajax($isOpen = 'all', $status = 'all', $show = 1, $date = 'all', $search = '') {
        $this->layout = 'ajax';
        $this->set(compact('isOpen', 'status', 'show', 'date', 'search'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'View', $id);
        $this->set(compact('id'));
    }

    function add($journeyId, $departureId, $date, $editId = 0) {
        $this->layout = 'ajax';
        $result = array();
        if(empty($journeyId) || empty($departureId) || empty($date) || sizeof($this->data['seat_number']) == 0){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        if(strtotime($date) > strtotime(date("2030-01-01"))){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data) && !empty($journeyId) && !empty($departureId) && !empty($date)) {
            $checkSeatCon = '';
            if($editId > 0){
                $TTicket = $this->TTicket->read(null, $editId);
                if(!empty($TTicket)){
                    if($TTicket['TTicket']['status'] == 1 && $TTicket['TTicket']['type'] == 2){
                        $checkSeatCon = ' AND t_ticket_id != '.$TTicket['TTicket']['id'];
                    }
                }
            }
            // Journey
            $TJourney  = ClassRegistry::init('TJourney')->find('first', array("conditions" => array("TJourney.id" => $journeyId)));
            $departure = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $departureId)));
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id FROM t_journey_change_transportations WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$TJourney['TJourney']['id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId   = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId   = $TJourney['TJourney']['t_transportation_type_id'];
            }
            // Get Total Amount
            $totalAmount   = 0;
            $totalMarkup   = 0;
            $totalDiscount = 0;
            $unitPrice     = 0;
            $markup        = $this->data['TTicket']['total_markup'];
            $luckyTicket   = $this->data['TTicket']['lucky_draw_fee'];
            $totalSeat = 0;
            for ($i = 0; $i < sizeof($this->data['price']); $i++) {
                $unitPrice   = $this->data['price'][$i] - $this->data['TTicket']['total_markup'];
                $totalAmount += $this->data['price'][$i] - $this->data['TTicket']['total_markup'];
                $totalMarkup += $markup;
                $totalSeat++;
            }
            $agencyMarkupDis = $this->data['TTicket']['discount_amount'];
            $unitVatPrice = $this->data['TTicket']['total_vat'] / $totalSeat; 
            $this->data['TTicket']['price'] = $unitPrice + $markup;
            $this->data['TTicket']['total_amount'] = $totalAmount;
            $this->data['TTicket']['total_markup'] = $totalMarkup;
            // $this->data['TTicket']['discount_amount'] = $totalDiscount + $agencyMarkupDis;
            // Check Agency
            $balanceId    = 0;
            $checkBalance = true;
            $totalBonus   = 0;
            $agentBalance = 0;
            if(!empty($this->data['TTicket']['t_agent_id'])){
                $sqlAgency = mysql_query("SELECT commission, commission_type, payment, max_balance, `type`, apply_bonus, bonus, commission_buva_sea_type, commission_buva_sea FROM t_agents WHERE id = ".$this->data['TTicket']['t_agent_id']);
                if(mysql_num_rows($sqlAgency)){
                    $rowAgency = mysql_fetch_array($sqlAgency);
                    $totalCommission  = 0;
                    if($rowAgency['type'] != 3){ // != API
                        if($TJourney['Company']['type'] == 1){ // Bus
                            $agentComType = $rowAgency['commission_type'];
                            $agentCom = $rowAgency['commission'];
                        } else { // Boat
                            $agentComType = $rowAgency['commission_buva_sea_type'];
                            $agentCom = $rowAgency['commission_buva_sea'];
                        }
                    } else {
                        $agentComType = $rowAgency['commission_type'];
                        $agentCom = $rowAgency['commission'];
                    }
                    // Calculate Commission (Default Agency Price No Commission)
                    if($agentComType != 2){ // != Default Agency Price
                        if($agentComType == 1){ // Commission (%)
                            if($agentCom > 0 && $this->data['TTicket']['total_amount'] > 0){
                                $totalCommission = (($this->data['TTicket']['total_amount'] + $this->data['TTicket']['total_vat']) * $agentCom) / 100;
                                $this->data['TTicket']['commission'] = $this->Helper->replaceThousand(number_format($totalCommission, 2));
                                $this->data['TTicket']['commission_percent'] = $agentCom;
                            }
                        } else { // Fixed Amount
                            $totalCommission  = $agentCom * sizeof($this->data['seat_number']);
                            $this->data['TTicket']['commission'] = $this->Helper->replaceThousand(number_format($totalCommission, 2));
                            $this->data['TTicket']['commission_percent'] = 0;
                        }
                    }
                    if($rowAgency['type'] == 1 || $rowAgency['type'] == 2){ // Agency Type (Offline or Online or APi)
                        // Check Balance
                        if($rowAgency['payment'] == 1 || $rowAgency['payment'] == 2){ // Prepaid or Postpaid
                            if($agentComType == 2){ // Agency Price
                                $totalAgencyPrice = $TJourney['TJourney']['agent_price_amount'] * sizeof($this->data['seat_number']);
                                if($this->data['TTicket']['round_trip'] == 2 && $TJourney['TJourney']['agent_round_price'] > 0){ // Round Trip
                                    $totalAgencyPrice = $TJourney['TJourney']['agent_round_price'] * sizeof($this->data['seat_number']);
                                }
                                // Check Price in Period
                                // By Journey
                                $sqlPJ = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND t_journey_id = ".$TJourney['TJourney']['id']." AND apply_type = 1 ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPJ)){
                                    $rowPJ = mysql_fetch_array($sqlPJ);
                                    $totalAgencyPrice  = $rowPJ['agency_price'] * sizeof($this->data['seat_number']);
                                    if($this->data['TTicket']['round_trip'] == 2 && $rowPJ['round_agency_price'] > 0){ // Round Trip
                                        $totalAgencyPrice  = $rowPJ['round_agency_price'] * sizeof($this->data['seat_number']);
                                    }
                                } else { // By Destination
                                    $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$TJourney['TJourney']['t_destination_from_id']." AND destination_to_id = ".$TJourney['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$TJourney['TJourney']['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND apply_type = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                    if(mysql_num_rows($sqlPA)){
                                        $rowPAPrice = mysql_fetch_array($sqlPA);
                                        if($rowPAPrice['price_type'] == 1){
                                            $totalAgencyPrice  = $rowPAPrice['agency_price'] * sizeof($this->data['seat_number']);
                                            if($this->data['TTicket']['round_trip'] == 2 && $rowPAPrice['round_agency_price'] > 0){ // Round Trip
                                                $totalAgencyPrice  = $rowPAPrice['round_agency_price'] * sizeof($this->data['seat_number']);
                                            }
                                        } else {
                                            $totalAgencyPrice  = $totalAgencyPrice + ($rowPAPrice['agency_price'] * sizeof($this->data['seat_number']));   
                                            if($this->data['TTicket']['round_trip'] == 2 && $rowPAPrice['round_agency_price'] > 0){ // Round Trip
                                                $totalAgencyPrice  = $totalAgencyPrice + ($rowPAPrice['round_agency_price'] * sizeof($this->data['seat_number']));
                                            }
                                        }
                                    }
                                }
                                $totalAgencyVatPrice = $this->data['TTicket']['total_vat'] - $agencyMarkupDis;
                                $totalAgencyNetPrice = $totalAgencyPrice + $totalAgencyVatPrice;
                            } else { // Commission (%) and Fixed Amount
                                $totalAgencyVatPrice = 0;
                                $totalAgencyPrice    = ($this->data['TTicket']['total_amount'] + $this->data['TTicket']['total_vat']) - $this->Helper->replaceThousand(number_format($totalCommission, 2));
                                $totalAgencyNetPrice = $totalAgencyPrice;
                            }
                            $bunusAgency = 0;
                            // Agency Online type prepaid check bonus
                            if($rowAgency['type'] == 1 && $rowAgency['payment'] == 1){
                                if($rowAgency['apply_bonus'] == 1 && $rowAgency['bonus'] > 0){
                                    $bunusAgency = $rowAgency['bonus'] * sizeof($this->data['seat_number']);
                                }
                            }
                            $totalBonus   = $bunusAgency;
                            $agentBalance = $totalAgencyNetPrice + $luckyTicket - $bunusAgency;
                            mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `net_price`, `vat_price`, `bonus`, `lucky_ticket`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                         VALUES (".$this->data['TTicket']['t_agent_id'].", ".$totalAgencyPrice.", ".$totalAgencyVatPrice.", ".$bunusAgency.", ".$luckyTicket.", ".$agentBalance.", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
                            $balanceId  = mysql_insert_id();
                            if($rowAgency['payment'] == 1){ // Prepaid
                                $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$this->data['TTicket']['t_agent_id']); 
                                $rowBalance = mysql_fetch_array($sqlBalance);
                                if($rowBalance['balance'] <= 0){
                                    $checkBalance = false;
                                }
                            } else { // PostPaid (Online / Offline)
                                $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$this->data['TTicket']['t_agent_id']); 
                                $rowBalance = mysql_fetch_array($sqlBalance);
                                if(($rowBalance['balance'] * -1) > $rowAgency['max_balance']){
                                    $checkBalance = false;
                                }
                            }
                        }
                    } else if($rowAgency['type'] == 3){ // APi
                        if($rowAgency['payment'] == 1){ // Prepaid
                            $totalAgencyVatPrice = 0;
                            $bunusAgency         = 0;
                            $totalAgencyPrice    = ($this->data['TTicket']['total_amount'] + $this->data['TTicket']['total_vat']) - $this->Helper->replaceThousand(number_format($totalCommission, 2));
                            $totalAgencyNetPrice = $totalAgencyPrice;
                            $agentBalance        = $totalAgencyNetPrice + $luckyTicket - $bunusAgency;
                            mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `net_price`, `vat_price`, `bonus`, `lucky_ticket`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                        VALUES (".$this->data['TTicket']['t_agent_id'].", ".$totalAgencyPrice.", ".$totalAgencyVatPrice.", ".$bunusAgency.", ".$luckyTicket.", ".$agentBalance.", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
                            $balanceId  = mysql_insert_id();
                            $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$this->data['TTicket']['t_agent_id']); 
                            $rowBalance = mysql_fetch_array($sqlBalance);
                            if($rowBalance['balance'] <= 0){
                                $checkBalance = false;
                            }
                        }
                    } else {
                        mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `net_price`, `vat_price`, `bonus`, `lucky_ticket`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                     VALUES (".$this->data['TTicket']['t_agent_id'].", ".$totalAmount.", ".$this->data['TTicket']['total_vat'].", 0, ".$luckyTicket.", ".($totalAmount + $this->data['TTicket']['total_vat'] + $luckyTicket).", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
                        $balanceId  = mysql_insert_id();
                        $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$this->data['TTicket']['t_agent_id']); 
                        $rowBalance = mysql_fetch_array($sqlBalance);
                        if($rowAgency['payment'] == 1){ // Prepaid
                            if($rowBalance['balance'] <= 0){
                                $checkBalance = false;
                            }
                        } else { // Postpaid Check Max Balance
                            if(($rowBalance['balance'] * -1) > $rowAgency['max_balance']){
                                $checkBalance = false;
                            }
                        }
                    }
                }
            }
            // Check Seat Available
            $avaiable  = true;
            $changeShiftId = 0;
            $checkPromotion = array(); 
            if($checkBalance == true){
                for ($i = 0; $i < sizeof($this->data['seat_number']); $i++) {
                    if(!empty($this->data['change_ticket_id'][$i])){
                        $changeShiftId = $this->data['change_ticket_id'][$i];
                    }
                    if($TJourney['TJourney']['type'] == 3){
                        $sqlTransit = mysql_query("SELECT t_journeys.t_transportation_type_id, t_journeys.t_route_id, t_journeys.id AS journey_id, t_journeys.t_departure_time_id, t_journey_transits.is_next_day
                                                   FROM t_journeys
                                                   INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id
                                                   WHERE t_journey_transits.t_journey_id = ".$TJourney['TJourney']['id']." 
                                                   GROUP BY t_journey_transits.t_journey_departure_id");
                        while($rowTransit = mysql_fetch_array($sqlTransit)){
                            $sqlDeparture = mysql_query("SELECT * FROM t_departure_times WHERE id = ".$rowTransit['t_departure_time_id']);
                            $rowDeparture = mysql_fetch_array($sqlDeparture);
                            $travelDate   = $date;
                            if($rowTransit['is_next_day'] == 1){
                                $travelDate = date("Y-m-d", strtotime("+1 day", strtotime($date)));
                            }
                            // Insert to Seat Control Tmp
                            mysql_query("INSERT INTO `t_seat_controls` (`sys_code`, `journey_date`, `journey_time`, `t_journey_id`, `t_transportation_type_id`, `t_route_id`, `seat_number`, `gender`, `created`, `created_by`, `modified`, `status`)
                                         VALUES ('".$this->data['sys_code'][$i]."', '".$travelDate."', '".$rowDeparture['name']."', ".$rowTransit['journey_id'].", ".$rowTransit['t_transportation_type_id'].", ".$rowTransit['t_route_id'].", '".$this->data['seat_number'][$i]."', '".$this->data['gender'][$i]."', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."', 2);");
                            $tmpSeatId = mysql_insert_id();
                            if(!empty($tmpSeatId)){
                                $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data['seat_number'][$i]."' AND t_transportation_type_id = ".$rowTransit['t_transportation_type_id']." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$travelDate."' AND status > 0 AND status < 5 AND id < ".$tmpSeatId.$checkSeatCon.";");
                                if(mysql_num_rows($sqlCheck)){
                                    $avaiable = false;
                                    break;
                                }
                            } else {
                                $avaiable = false;
                                break;
                            }
                        }
                    } else {
                        // Insert to Seat Control Tmp
                        mysql_query("INSERT INTO `t_seat_controls` (`sys_code`, `journey_date`, `journey_time`, `t_journey_id`, `t_transportation_type_id`, `t_route_id`, `seat_number`, `gender`, `created`, `created_by`, `modified`, `status`)
                                     VALUES ('".$this->data['sys_code'][$i]."', '".$date."', '".$departure['TDepartureTime']['name']."', ".$TJourney['TJourney']['id'].", ".$trasportationId.", ".$TJourney['TJourney']['t_route_id'].", '".$this->data['seat_number'][$i]."', '".$this->data['gender'][$i]."', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."', 2);");
                        $tmpSeatId = mysql_insert_id();
                        if(!empty($tmpSeatId)){
                            $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data['seat_number'][$i]."' AND t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$TJourney['TJourney']['t_route_id']." AND journey_date = '".$date."' AND status > 0 AND status < 5 AND id < ".$tmpSeatId.$checkSeatCon.";");
                            if(mysql_num_rows($sqlCheck)){
                                $avaiable = false;
                            }
                        } else {
                            $avaiable = false;
                        }
                    }
                    // VIP 10 Free 1
                    // if(empty($this->data['TTicket']['t_agent_id']) && !empty($this->data['TTicket']['telephone']) && $this->data['TTicket']['type'] != 2){
                    //     // Check Destination != (Bangkok, Vietnam)
                    //     if($TJourney['TJourney']['t_destination_from_id'] != 4 && $TJourney['TJourney']['t_destination_from_id'] != 13 && $TJourney['TJourney']['t_destination_to_id'] != 4 && $TJourney['TJourney']['t_destination_to_id'] != 13){
                    //         // Check VIP 10 Free 1
                    //         $sqlCusVip = mysql_query("SELECT * FROM online_customer_tickets WHERE telephone = '".$this->data['TTicket']['telephone']."' AND is_active = 1");
                    //         if(mysql_num_rows($sqlCusVip)){
                    //             $condition = "";
                    //             $filterId  = "";
                    //             if(!empty($checkPromotion)){
                    //                 foreach($checkPromotion AS $val){
                    //                     if(!empty($val['promotion_id'])){
                    //                         if(!empty($filterId)){
                    //                             $filterId .= ",";
                    //                         }
                    //                         $filterId .= $val['promotion_id'];
                    //                     }
                    //                 }
                    //                 if(!empty($filterId)){
                    //                     $condition = " AND id NOT IN (".$filterId.")";
                    //                 }
                    //             }
                    //             mysql_query("INSERT INTO `online_customer_promotions` (`telephone`, `created`) 
                    //                          VALUES ('".$this->data['TTicket']['telephone']."', now());");
                    //             $promotionId = mysql_insert_id();
                    //             $checkPromotion[$promotionId]['promotion_id'] = "";
                    //             $checkPromotion[$promotionId]['seat_num']     = $this->data['seat_number'][$i];
                    //             $sqlTotal = mysql_query("SELECT count(id) AS total FROM online_customer_promotions WHERE id < ".$promotionId." AND `status` = 1".$condition); 
                    //             $rowTotal = mysql_fetch_array($sqlTotal);
                    //             if($rowTotal[0] >= 10){
                    //                 // Update Ticket Free Block
                    //                 mysql_query("UPDATE online_customer_promotions SET status = 3 WHERE id < ".$promotionId." AND status = 1 LIMIT 10");
                    //                 mysql_query("UPDATE online_customer_promotions SET status = 3 WHERE id = ".$promotionId);
                    //                 // Set Param Free
                    //                 $checkPromotion[$promotionId]['promotion_id'] = $promotionId;
                    //             }
                    //         }
                    //     }
                    // }
                }
            }
            if($avaiable == true && $checkBalance == true){
                $dateNow     = date("Y-m-d H:i:s");
                $created     = $dateNow;
                $createdBy   = $user['User']['id'];
                $bookingTime = (int) date("H");
                $checkCodeGen = true;
                if($this->Helper->checkDateFrom($TJourney['TJourney']['branch_id'], $bookingTime) == 0){
                    $this->data['TTicket']['date'] = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
                } else {
                    $this->data['TTicket']['date'] = date("Y-m-d");
                }
                if($editId > 0){
                    if(!empty($TTicket)){
                        if($TTicket['TTicket']['is_open_date'] == 1){
                            $created   = $TTicket['TTicket']['created'];
                            $createdBy = $TTicket['TTicket']['created_by'];
                            $this->data['TTicket']['code']     = $TTicket['TTicket']['code'];
                            $this->data['TTicket']['date']     = $TTicket['TTicket']['date'];
                            $this->data['TTicket']['modified']    = $dateNow;
                            $this->data['TTicket']['modified_by'] = $user['User']['id'];
                            $checkCodeGen = false;
                        }
                        // Delete Agency Balance
                        if($TTicket['TTicket']['t_agent_id']){
                            mysql_query("DELETE FROM agency_balances WHERE t_ticket_id = ".$TTicket['TTicket']['id']);
                        }
                    }
                    $this->data['TTicket']['online_order_id'] = $TTicket['TTicket']['online_order_id'];
                    $this->data['TTicket']['t_journey_transit_id'] = $TTicket['TTicket']['t_journey_transit_id'];
                    $this->data['TTicket']['edit_from'] = $editId;
                    // Update Status Edit
                    mysql_query("UPDATE t_tickets SET status = -2, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$editId);
                    // Delete Seat Control
                    mysql_query("DELETE FROM t_seat_controls WHERE t_ticket_id = ".$editId);
                }
                $company   = ClassRegistry::init('Company')->find('first', array("conditions" => array("Company.id" => $TJourney['TJourney']['company_id'])));
                $branch    = ClassRegistry::init('Branch')->find('first', array("conditions" => array("Branch.id" => $TJourney['TJourney']['branch_id'])));
                $destFrom  = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $TJourney['TJourney']['t_destination_from_id'])));
                $destTo    = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $TJourney['TJourney']['t_destination_to_id'])));
                $this->TTicket->create();
                $this->data['TTicket']['company_id']     = $TJourney['TJourney']['company_id'];
                $this->data['TTicket']['branch_id']      = $TJourney['TJourney']['branch_id'];
                $this->data['TTicket']['main_branch_id'] = $user['User']['main_branch_id'];
                $this->data['TTicket']['journey_date']   = $date;
                $this->data['TTicket']['journey_time']   = $departure['TDepartureTime']['name'];
                $this->data['TTicket']['t_journey_id']   = $TJourney['TJourney']['id'];
                $this->data['TTicket']['t_transportation_type_id'] = $trasportationId;
                $this->data['TTicket']['t_journey_departure_id']   = $departureId;
                $this->data['TTicket']['t_destination_from_id']    = $TJourney['TJourney']['t_destination_from_id'];
                $this->data['TTicket']['t_destination_to_id']      = $TJourney['TJourney']['t_destination_to_id'];
                $this->data['TTicket']['t_route_id']   = $TJourney['TJourney']['t_route_id'];
                $this->data['TTicket']['reject_time']  = $TJourney['TJourney']['reject_before_departure'];
                $this->data['TTicket']['currency_center_id'] = $TJourney['TJourney']['currency_center_id'];
                $this->data['TTicket']['total_bonus']  = $totalBonus;
                $this->data['TTicket']['total_seat']   = $totalSeat;
                $this->data['TTicket']['created']      = $created;
                $this->data['TTicket']['created_by']   = $createdBy;
                $this->data['TTicket']['status']       = 1;
                $this->data['TTicket']['is_open_date'] = 0;
                $this->data['TTicket']['balance']      = 0;
                $this->data['TTicket']['journey_type'] = $this->data['TTicket']['round_trip'];
                $this->data['TTicket']['total_print_lucky'] = 1;
                if($this->data['TTicket']['type'] == 2){
                    $this->data['TTicket']['balance'] = $this->data['TTicket']['total_amount'];
                } else if($this->data['TTicket']['type'] == 1 || $this->data['TTicket']['type'] == 3) {
                    $this->data['TTicket']['status'] = 2;
                }
                $this->data['TTicket']['coupon_amount'] = 0;
                if($this->data['TTicket']['round_trip'] == 2){ // Round Trip
                    $this->data['TTicket']['is_round_trip'] = 1;
                }
                // Check Coupon
                $useCoupon = false;
                if(!empty($this->data['TTicket']['coupon_code'])){
                    $sqlCoupon = mysql_query("SELECT * FROM coupons WHERE code = '".$this->data['TTicket']['coupon_code']."' AND status = 2");
                    if(mysql_num_rows($sqlCoupon)){
                        $rowCoupon = mysql_fetch_array($sqlCoupon);
                        $this->data['TTicket']['coupon_id'] = $rowCoupon['id'];
                        $this->data['TTicket']['coupon_amount'] = $this->data['TTicket']['total_amount'] - $this->data['TTicket']['discount_amount'] + $this->data['TTicket']['total_vat'];
                        $useCoupon = true;
                    }
                }
                if($checkCodeGen == true){
                    // Code
                    if($this->data['TTicket']['type'] == 1){ // Walk In
                        $code = 'T';
                        if($this->data['TTicket']['round_trip'] == 2 && $this->data['TTicket']['return'] == 0){
                            $code = 'TR';
                        } else if($this->data['TTicket']['round_trip'] == 1 && $this->data['TTicket']['return'] == 1){
                            $code = 'R';
                        }
                    } else if($this->data['TTicket']['type'] == 2){ // Phone Call
                        $code = 'P';
                    } else { // Agency
                        if(!empty($this->data['TTicket']['t_agent_id'])){
                            $code = 'A';
                        }
                    }
                    $ticketCode = $branch['Branch']['code']."-".$code.date("ym");
                    $this->data['TTicket']['tmp_code'] = $ticketCode;
                } else {
                    $code = 'R';
                }
                // Remove all whitespace from telephone before saving
                if(!empty($this->data['TTicket']['telephone'])){
                    $this->data['TTicket']['telephone'] = preg_replace('/\s+/', '', $this->data['TTicket']['telephone']);
                }
                if($this->TTicket->save($this->data)) {
                    $ticketId = $this->TTicket->id;
                    // Update Coupon Transaction
                    if(!empty($this->data['TTicket']['coupon_id'])){
                        mysql_query("INSERT INTO `coupon_transactions` (`id`, `coupon_id`, `amount`, `t_ticket_id`, `created`) 
                                     VALUES (NULL, ".$this->data['TTicket']['coupon_id'].", ".$this->data['TTicket']['coupon_amount'].", ".$ticketId.", now());");
                    }
                    // Seat
                    $this->loadModel('TTicketDetail');
                    $this->loadModel('TSeatControl');
                    $this->loadModel('TSeatControlCloud');
                    $checkSeatLocked = true;
                    $printItemLabel  = "";
                    for ($i = 0; $i < sizeof($this->data['seat_number']); $i++) {
                        $this->TTicketDetail->create();
                        $seat = array();
                        $promotionCut = "";
                        $promotionId  = "";
                        if($i > 0){
                            $printItemLabel .= ",";
                        }
                        $printItemLabel .= $this->data['label_number'][$i];
                        if(!empty($checkPromotion)){
                            foreach($checkPromotion AS $key => $val){
                                // If Seat Promotion = Seat (Set Free Id)
                                if($val['seat_num'] == $this->data['seat_number'][$i]){
                                    $promotionId = $key;
                                    if(!empty($val['promotion_id'])){
                                        $this->data['discount'][$i] = $this->data['price'][$i] + $unitVatPrice;
                                        $promotionCut = $val['promotion_id'];
                                    }
                                }   
                            }
                            
                            // Check Free
                            if(!empty($promotionCut)){
                                $seat['TTicketDetail']['is_free'] = 1;
                                $printItemLabel .= "(Free)";
                            }
                        }
                        $seat['TTicketDetail']['sys_code']     = SERVER_ID."S".$this->Helper->generateRandomString(8);
                        $seat['TTicketDetail']['t_ticket_id']  = $ticketId;
                        $seat['TTicketDetail']['seat_number']  = $this->data['seat_number'][$i];
                        $seat['TTicketDetail']['label_number'] = $this->data['label_number'][$i];
                        $seat['TTicketDetail']['is_special']   = $this->data['is_special'][$i];
                        $seat['TTicketDetail']['gender']       = $this->data['gender'][$i];
                        $seat['TTicketDetail']['unit_price']   = $this->data['price'][$i] - $markup;
                        $seat['TTicketDetail']['vat_price']    = $unitVatPrice;
                        $seat['TTicketDetail']['markup']       = $markup;
                        $seat['TTicketDetail']['total_amount'] = $this->data['price'][$i]; // Include Markup
                        if(!empty($this->data['change_ticket_id'][$i])){
                            $seat['TTicketDetail']['change_reference']  = $this->data['change_ticket_id'][$i];
                            $seat['TTicketDetail']['reference']         = $this->data['change_detail_id'][$i];
                            $seat['TTicketDetail']['total_amt_change']  = $this->data['amt_change'][$i];
                            $seat['TTicketDetail']['total_change']      = 1;
                            $seat['TTicketDetail']['change_date']       = date("Y-m-d H:i:s");
                            $seat['TTicketDetail']['change_by']         = $user['User']['id'];
                        }
                        if(!empty($this->data['name'][$i])){
                            $seat['TTicketDetail']['name'] = $this->data['name'][$i];
                        }
                        if(!empty($this->data['dob'][$i])){
                            $seat['TTicketDetail']['dob'] = $this->data['dob'][$i];
                        }
                        if(!empty($this->data['passprot'][$i])){
                            $seat['TTicketDetail']['passport'] = $this->data['passprot'][$i];
                        }
                        if($useCoupon){
                            $seat['TTicketDetail']['discount'] = $seat['TTicketDetail']['total_amount'] + $seat['TTicketDetail']['vat_price'];
                        } else {
                            $seat['TTicketDetail']['discount'] = $this->data['discount'][$i];
                            $totalDiscount += $seat['TTicketDetail']['discount'];
                        }
                        $this->TTicketDetail->save($seat);
                        $ticketDetailId = $this->TTicketDetail->id;
                        $status = 2;
                        if($this->data['TTicket']['type'] == 2){
                            $status = 1;
                        }
                        if(!empty($this->data['change_ticket_id'][$i])){
                            // Update Seat Change
                            mysql_query("UPDATE t_ticket_details SET change_reference = ".$ticketId.", reference = ".$ticketDetailId.", is_change = 1, change_date = now(), change_by = ".$user['User']['id']." WHERE id = ".$this->data['change_detail_id'][$i]);
                            // Update Seat Control
                            mysql_query("UPDATE t_seat_controls SET status = 5 WHERE t_ticket_detail_id = ".$this->data['change_detail_id'][$i]);
                            // Update Seat Control
                            mysql_query("UPDATE t_tickets SET is_change = 1 WHERE id = ".$this->data['change_ticket_id'][$i]);
                        }
                        if(!empty($this->data['sys_code'][$i])){
                            $sqlChkSeat = mysql_query("SELECT id FROM t_seat_controls WHERE sys_code = '".$this->data['sys_code'][$i]."' AND seat_number = '".$this->data['seat_number'][$i]."'");
                            if(mysql_num_rows($sqlChkSeat)){
                                // Update Seat Information
                                mysql_query("UPDATE t_seat_controls SET t_ticket_id = ".$ticketId.", t_ticket_detail_id = ".$ticketDetailId.", type = 1, status = ".$status." WHERE sys_code = '".$this->data['sys_code'][$i]."' AND seat_number = '".$this->data['seat_number'][$i]."'");
                            } else {
                                $checkSeatLocked = false;
                            }
                        } else {
                            $checkSeatLocked = false;
                        }
                        // Update Promotion 10 Free 1
                        if(!empty($promotionCut)){
                            mysql_query("UPDATE online_customer_promotions SET status = 2 WHERE id <= ".$promotionCut." AND status = 3 LIMIT 11");
                        }
                        if(!empty($promotionId)){
                            mysql_query("UPDATE online_customer_promotions SET t_ticket_id = ".$ticketId.", t_ticket_detail_id = ".$ticketDetailId." WHERE id = ".$promotionId);
                        }
                        
                    }
                    $ticketDiscount = $totalDiscount + $agencyMarkupDis + $this->data['TTicket']['total_change'];
                    // Check Seat Lock
                    if($checkSeatLocked == false){
                        if(!empty($balanceId)){
                            mysql_query("DELETE FROM agency_balances WHERE id = ".$balanceId);
                        }
                        // Delete Seat Book Tmp
                        if(!empty($this->data['sys_code'])){
                            for ($i = 0; $i < sizeof($this->data['seat_number']); $i++) {
                                mysql_query("DELETE FROM t_seat_controls WHERE sys_code = '".$this->data['sys_code'][$i]."'");
                            }
                        }
                        // User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Error Seat Not Lock)');
                        $result['error'] = 1;
                        echo json_encode($result);
                        exit;
                    }
                    if($checkCodeGen == true){
                        if($user['User']['type'] != 2){ // Agency
                            if($user['User']['type'] == 3 || $user['User']['type'] == 4){
                                $tmpCode = 'VETA'.date("ym");
                                mysql_query("INSERT INTO `ticket_codes` (`offline_project_id`, `code`) VALUES (".$user['User']['offline_project_id'].", '".$tmpCode."');");
                                $ticketCodeId = mysql_insert_id();
                                $sqlCount = mysql_query("SELECT COUNT(id) FROM ticket_codes WHERE code LIKE '".$tmpCode."%' AND offline_project_id = ".$user['User']['offline_project_id']." AND id < ".$ticketCodeId.";");
                                $rowCount = mysql_fetch_array($sqlCount);
                                $modCode  = $tmpCode.str_pad(($rowCount[0] + 1),6,"0",STR_PAD_LEFT);
                                mysql_query("UPDATE t_tickets SET code = '".$modCode."', tmp_code = '".$tmpCode."', tmp_count = ".($rowCount[0] + 1).", discount_amount = ".$ticketDiscount." WHERE id = ".$ticketId);
                            }
                        } else {
                            $checkShiftCode = "";
                            if($changeShiftId > 0){
                                $sqlTicketShift = mysql_query("SELECT code FROM t_tickets WHERE id = ".$changeShiftId);
                                if(mysql_num_rows($sqlTicketShift)){
                                    $rowTicketShift = mysql_fetch_array($sqlTicketShift);
                                    $checkShiftCode = $rowTicketShift['code'];
                                }
                            }
                            if(!empty($checkShiftCode)){
                                // Updaet Module Code
                                $modCode = $checkShiftCode."(1)";
                                mysql_query("UPDATE t_tickets SET code = '".$modCode."', tmp_count = 1, discount_amount = ".$ticketDiscount." WHERE id = ".$ticketId);
                            } else {
                                mysql_query("INSERT INTO `ticket_codes` (`offline_project_id`, `code`) VALUES (".$user['User']['offline_project_id'].", '".$ticketCode."');");
                                $ticketCodeId = mysql_insert_id();
                                $sqlCount = mysql_query("SELECT COUNT(id) FROM ticket_codes WHERE code LIKE '".$ticketCode."%' AND offline_project_id = ".$user['User']['offline_project_id']." AND id < ".$ticketCodeId.";");
                                $rowCount = mysql_fetch_array($sqlCount);
                                // Get Module Code
                                $modCode  = $ticketCode.str_pad(($rowCount[0] + 1),6,"0",STR_PAD_LEFT);
                                // Updaet Module Code
                                mysql_query("UPDATE t_tickets SET code = '".$modCode."', tmp_count = ".($rowCount[0] + 1).", discount_amount = ".$ticketDiscount." WHERE id = ".$ticketId);
                            }
                        }
                    } else {
                        $modCode = $this->data['TTicket']['code'];
                    }
                    // Update Seat Control reference
                    mysql_query("UPDATE t_seat_controls SET reference = '".$modCode."' WHERE t_ticket_id = ".$ticketId);
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New');
                    $depare  = explode(":", $this->data['TTicket']['journey_time']);
                    $depareureTime = (int) $depare[0];
                    if(strtotime($this->data['TTicket']['journey_date']) >= strtotime("2021-12-07")){
                        $travelDate = $this->Helper->dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                    } else {
                        if($this->Helper->checkDateFrom($this->data['TTicket']['branch_id'], $depareureTime) == 1){
                            $travelDate = $this->Helper->dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                        } else {
                            $travelDate = date("d/m/Y", strtotime("+1 day", strtotime($this->data['TTicket']['journey_date'])))." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                        }
                    }
                    // Transportaion Type
                    $transportaionType = '';
                    if($this->data['TTicket']['company_id'] != 1 && !empty($this->data['TTicket']['t_boat_id'])){
                        $sqlType = mysql_query("SELECT name FROM t_transportation_types WHERE id = (SELECT t_transportation_type_id FROM t_boats WHERE id = ".$this->data['TTicket']['t_boat_id'].")");
                        $rowType = mysql_fetch_array($sqlType);
                        $transportaionType = $rowType[0];
                    }
                    // Boarding Point
                    $branchFrom = "";
                    $branchTel  = "";
                    $boardingPointTime = "";
                    $dropOffTime = "";
                    if(!empty($this->data['TTicket']['t_boarding_point_id'])){
                        $sqlBoarding = mysql_query("SELECT name, telephone FROM t_boarding_points WHERE id = ".$this->data['TTicket']['t_boarding_point_id']);
                        $rowBoarding = mysql_fetch_array($sqlBoarding);
                        $branchFrom = $rowBoarding['name'];
                        $branchTel  = $rowBoarding['telephone'];
                        
                    } else {
                        $branchFrom = $branch['Branch']['name'];
                        $branchTel  = $branch['Branch']['telephone'];
                    }
                    // Branch To
                    $sqlDropOff = mysql_query("SELECT name, telephone FROM t_drop_offs WHERE id = ".$this->data['TTicket']['t_drop_off_id']);
                    $rowDropOff = mysql_fetch_array($sqlDropOff);
                    $sqlDropOffTime = mysql_query("SELECT time FROM t_journey_drop_offs WHERE t_journey_id = ".$this->data['TTicket']['t_journey_id']." AND t_drop_off_id = ".$this->data['TTicket']['t_drop_off_id']);
                    if(mysql_num_rows($sqlDropOffTime)){
                        $rowDropOffTime = mysql_fetch_array($sqlDropOffTime);
                        $dropOffTime    = date('h:i A', strtotime(date("Y-m-d")." ".$rowDropOffTime['time']));
                        // $expTime = explode(":",$rowDropOffTime['time']);
                        // $dropOffTime = $expTime[0].":".$expTime[1];
                    }
                    // Dropoff Point
                    $sqlBoarding = mysql_query("SELECT name, telephone FROM t_boarding_points WHERE id = ".$this->data['TTicket']['t_boarding_point_id']);
                    $rowBoarding = mysql_fetch_array($sqlBoarding);
                    $sqlBoardingTime = mysql_query("SELECT time FROM t_journey_boarding_points WHERE t_journey_id = ".$this->data['TTicket']['t_journey_id']." AND t_boarding_point_id = ".$this->data['TTicket']['t_boarding_point_id']);
                    if(mysql_num_rows($sqlBoardingTime)){
                        $rowBoardingTime   = mysql_fetch_array($sqlBoardingTime);
                        $boardingPointTime = date('h:i A', strtotime(date("Y-m-d")." ".$rowBoardingTime['time']));
                        // $expTime = explode(":",$rowBoardingTime['time']);
                        // $boardingPointTime = $expTime[0].":".$expTime[1];
                    }
                    // Balance
                    if(!empty($balanceId)){
                        mysql_query("UPDATE agency_balances SET t_ticket_id = ".$ticketId.", reference = '".$modCode."' WHERE id = ".$balanceId);
                        mysql_query("UPDATE t_agents SET balance = (balance - ".$agentBalance.") WHERE id = ".$this->data['TTicket']['t_agent_id']);
                    }
                    $result['error']   = 0;
                    $result['id']      = $ticketId;
                    $result['company'] = $company['Company']['name'];
                    $result['website'] = $company['Company']['website'];
                    if($company['Company']['id'] != 6){
                        $result['company_type'] = 1;
                    } else {
                        $result['company_type'] = 2;
                    }
                    $result['dest_from_id'] = $destFrom['TDestination']['id'];
                    $result['dest_to_id']   = $destTo['TDestination']['id'];
                    $result['dest_from']    = $destFrom['TDestination']['name'];
                    $result['dest_to']      = $destTo['TDestination']['name'];
                    $result['dest_from_code'] = $destFrom['TDestination']['code'];
                    $result['dest_to_code']   = $destTo['TDestination']['code'];
                    // if(!empty($this->data['TTicket']['lucky_draw_fee'])){

                    // } else {
                    //     $this->data['TTicket']['lucky_draw_fee'] = 0;
                        
                    // }
                    $result['booking_date']   = $this->Helper->dateShort($this->data['TTicket']['date']);
                    $result['travel_date']    = $travelDate;
                    $result['created_by']     = $user['User']['first_name']." ".$user['User']['last_name'];
                    $result['agency_ref']     = $this->data['TTicket']['agt_refer_code'];
                    if($this->data['TTicket']['type'] == 3 || $this->data['TTicket']['type'] == 4){
                        $result['ticket_type']    = "A";
                    } else {
                        $result['ticket_type']    = $code;
                    }
                    $result['ticket_code']      = $modCode;
                    $result['trans_type']       = $transportaionType;
                    $result['branch_from']      = $branchFrom;
                    $result['branch_from_tel']  = $branchTel;
                    $result['branch_to']        = $rowDropOff['name'];
                    $result['branch_to_tel']    = $rowDropOff['telephone'];
                    $result['print_date']       = date("d/m/Y H:i:s");
                    $result['sys_code']         = $this->data['TTicket']['sys_code'];
                    $result['boarding_point']   = $rowBoarding['name']." (".$boardingPointTime.")";
                    $result['dropoff_point']    = $rowDropOff['name']." (".$dropOffTime.")";
                    $result['total_seat']       = $totalSeat;
                    $result['unit_price']       = number_format($this->data['TTicket']['price'] + $unitVatPrice, 2);
                    $result['total_vat']        = $this->data['TTicket']['total_vat'];
                    $result['extra_price']      = number_format($this->data['TTicket']['lucky_draw_fee'], 2);
                    $result['total_dis']        = number_format($ticketDiscount + $this->data['TTicket']['coupon_amount'], 2);
                    $result['total_amount']     = number_format($this->data['TTicket']['total_amount'] + $this->data['TTicket']['total_markup'] + $this->data['TTicket']['total_vat'], 2);
                    $result['total_usd']        = number_format($result['total_amount'] - $ticketDiscount + $this->data['TTicket']['lucky_draw_fee'] - $this->data['TTicket']['coupon_amount'], 2);
                    $result['total_riel']       = number_format($result['total_usd'] * 4100, 0);
                    $result['item_label']       = $printItemLabel;
                    // Get Printer Name 
                    $printerName = '';
                    $printSilent = 0;
                    $result['printer_name']   = $printerName;
                    $result['printer_silent'] = $printSilent;
                    echo json_encode($result);
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                // Delete Agency Balance History
                if(!empty($balanceId)){
                    mysql_query("DELETE FROM agency_balances WHERE id = ".$balanceId);
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Error)');
                    $result['error'] = 3;
                    echo json_encode($result);
                    exit;
                } else {
                    // Delete Seat Book Tmp
                    for ($i = 0; $i < sizeof($this->data['seat_number']); $i++) {
                        mysql_query("DELETE FROM t_seat_controls WHERE sys_code = '".$this->data['sys_code'][$i]."'");
                    }
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Some Seat Not Avaiable)');
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }
        }
    }
    
    function addTransit($transitId, $date, $editId = 0) {
        $this->layout = 'ajax';
        $result = array();
        if(empty($transitId) || empty($date)){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        if(strtotime($date) > strtotime(date("2025-01-01"))){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data) && !empty($transitId) && !empty($date)) {
            // Journey Transit
            $totalAmtJourney = array();
            $index = 0;
            $ticketJourney = array();
            $transit = ClassRegistry::init('TJourney')->find('first', array("conditions" => array("TJourney.id" => $transitId)));
            $TJourneyTransits = ClassRegistry::init('TJourneyTransit')->find('all', array("conditions" => array("TJourneyTransit.t_journey_id" => $transitId, "TJourneyTransit.is_active" => 1), "order" => "TJourneyTransit.id"));
            foreach($TJourneyTransits AS $TJourneyTransit){
                $TJourney  = ClassRegistry::init('TJourney')->find('first', array("conditions" => array("TJourney.id" => $TJourneyTransit['TJourneyTransit']['t_journey_departure_id'])));
                $ticketJourney[$index]['sys_code']       = $TJourney['TJourney']['sys_code'];
                $ticketJourney[$index]['t_journey_id']   = $TJourney['TJourney']['id'];
                $ticketJourney[$index]['t_journey_type'] = $TJourney['TJourney']['type'];
                $ticketJourney[$index]['company_id']     = $TJourney['TJourney']['company_id'];
                $ticketJourney[$index]['branch_id']      = $TJourney['TJourney']['branch_id'];
                $ticketJourney[$index]['arrival']        = $TJourney['TJourney']['arrival'];
                $ticketJourney[$index]['departure_id']   = $TJourney['TJourney']['t_departure_time_id'];
                $ticketJourney[$index]['departure_time'] = $TJourney['TDepartureTime']['name'];
                $ticketJourney[$index]['t_route_id']     = $TJourney['TJourney']['t_route_id'];
                $ticketJourney[$index]['t_transportation_type_id'] = $TJourney['TJourney']['t_transportation_type_id'];
                $ticketJourney[$index]['t_destination_from_id']    = $TJourney['TJourney']['t_destination_from_id'];
                $ticketJourney[$index]['t_destination_to_id']      = $TJourney['TJourney']['t_destination_to_id'];
                $ticketJourney[$index]['reject_before_departure']  = $TJourney['TJourney']['reject_before_departure'];
                $ticketJourney[$index]['currency_center_id']       = $TJourney['TJourney']['currency_center_id'];
                $ticketJourney[$index]['duration']                 = $TJourney['TJourney']['duration'];
                $ticketJourney[$index]['journey_syscode']          = $TJourney['TJourney']['sys_code'];
                $ticketJourney[$index]['transportation_type_syscode']  = $TJourney['TTransportationType']['sys_code'];
                $ticketJourney[$index]['route_syscode']            = $TJourney['TRoute']['sys_code'];
                $ticketJourney[$index]['agent_price_amount']       = $TJourney['TJourney']['agent_price_amount'];
                $ticketJourney[$index]['agetn_price_percent']      = $TJourney['TJourney']['agetn_price_percent'];
                $index++;
            }
            // Check Seat Available
            $checkSeatCon = "";
            if($editId > 0){
                $TTicket = $this->TTicket->read(null, $editId);
                if(!empty($TTicket)){
                    $checkSeatCon = " AND t_ticket_id != ".$editId;
                }
            }
            $avaiable  = true;
            $totalPaid = 0;
            foreach($ticketJourney AS $journey){
                $departure    = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $journey['departure_id'])));
                $journeyIndex = $journey['t_journey_id'];
                if(strtotime($journey['departure_time']) < strtotime($transit['TDepartureTime']['name'])){
                    $travelDate = date('Y-m-d', strtotime($date . ' +1 day'));
                } else {
                    $travelDate = $date;
                }
                // Check Transportation Type Change
                $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id FROM t_journey_change_transportations WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$journey['t_journey_id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
                if(mysql_num_rows($sqlCT)){
                    $rowCT = mysql_fetch_array($sqlCT);
                    $trasportationId   = $rowCT['t_transportation_type_id'];
                } else {
                    $trasportationId   = $journey['t_transportation_type_id'];
                }
                for ($i = 0; $i < sizeof($this->data[$journeyIndex]['seat_number']); $i++) {
                    if (array_key_exists($journeyIndex, $totalAmtJourney)){
                        $totalAmtJourney[$journeyIndex]['total'] += $this->data[$journeyIndex]['total'][$i];
                    } else {
                        $totalAmtJourney[$journeyIndex]['total'] = $this->data[$journeyIndex]['total'][$i];
                    }
                    $discount = 0;
                    if($this->data['discount_percent'] > 0){
                        $discount = $this->Helper->replaceThousand(number_format(($this->data[$journeyIndex]['total'][$i] * $this->data['discount_percent']) / 100, 2));
                    }
                    $totalPaid = $this->data[$journeyIndex]['total'][$i] - $discount;
                    if($journey['t_journey_type'] == 3){ // Multi Route
                        $sqlTransit = mysql_query("SELECT t_journeys.t_transportation_type_id, t_journeys.t_route_id, t_journeys.id AS journey_id, t_journeys.t_departure_time_id, t_journey_transits.is_next_day  
                                                   FROM t_journeys 
                                                   INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id
                                                   WHERE t_journey_transits.t_journey_id = ".$journey['t_journey_id']." 
                                                   GROUP BY t_journey_departure_id");
                        while($rowTransit = mysql_fetch_array($sqlTransit)){
                            $sqlDeparture = mysql_query("SELECT * FROM t_departure_times WHERE id = ".$rowTransit['t_departure_time_id']);
                            $rowDeparture = mysql_fetch_array($sqlDeparture);
                            $transitDate  = $travelDate;
                            if($rowTransit['is_next_day'] == 1){
                                $transitDate = date("Y-m-d", strtotime("+1 day", strtotime($travelDate)));
                            }
                            // Insert to Seat Control Tmp
                            mysql_query("INSERT INTO `t_seat_controls` (`sys_code`, `journey_date`, `journey_time`, `t_journey_id`, `t_transportation_type_id`, `t_route_id`, `seat_number`, `gender`, `created`, `created_by`, `modified`, `status`)
                                         VALUES ('".$this->data[$journeyIndex]['sys_code'][$i]."', '".$transitDate."', '".$rowDeparture['name']."', ".$rowTransit['journey_id'].", ".$rowTransit['t_transportation_type_id'].", ".$rowTransit['t_route_id'].", '".$this->data[$journeyIndex]['seat_number'][$i]."', '".$this->data[$journeyIndex]['gender'][$i]."', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."', 2);");
                            $tmpSeatId = mysql_insert_id();
                            if(!empty($tmpSeatId)){
                                $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data[$journeyIndex]['seat_number'][$i]."' AND t_transportation_type_id = ".$rowTransit['t_transportation_type_id']." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$travelDate."' AND status > 0 AND status < 5 AND id < ".$tmpSeatId.$checkSeatCon.";");
                                if(mysql_num_rows($sqlCheck)){
                                    $avaiable = false;
                                    break;
                                }
                            } else {
                                $avaiable = false;
                                break;
                            }
                        }
                    } else {
                        // Insert to Seat Control Tmp
                        mysql_query("INSERT INTO `t_seat_controls` (`sys_code`, `journey_date`, `journey_time`, `t_journey_id`, `t_transportation_type_id`, `t_route_id`, `seat_number`, `gender`, `created`, `created_by`, `modified`, `status`)
                                     VALUES ('".$this->data[$journeyIndex]['sys_code'][$i]."', '".$travelDate."', '".$departure['TDepartureTime']['name']."', ".$journey['t_journey_id'].", ".$trasportationId.", ".$journey['t_route_id'].", '".$this->data[$journeyIndex]['seat_number'][$i]."', '".$this->data[$journeyIndex]['gender'][$i]."', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."', 2);");
                        $tmpSeatId = mysql_insert_id();
                        if(!empty($tmpSeatId)){
                            $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data[$journeyIndex]['seat_number'][$i]."' AND t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$journey['t_route_id']." AND journey_date = '".$travelDate."' AND status > 0 AND status < 5 AND id < ".$tmpSeatId.$checkSeatCon.";");
                            if(mysql_num_rows($sqlCheck)){
                                $avaiable = false;
                                break;
                            }
                        } else {
                            $avaiable = false;
                            break;
                        }   
                    }
                }
            }
            // Check Agency
            $balanceId  = 0;
            $checkBalance = true;
            if(!empty($this->data['TTicket']['t_agent_id'])){
                $sqlAgency = mysql_query("SELECT commission, commission_type, payment FROM t_agents WHERE id = ".$this->data['TTicket']['t_agent_id']);
                if(mysql_num_rows($sqlAgency)){
                    $rowAgency = mysql_fetch_array($sqlAgency);
                    if($rowAgency['payment'] == 1 || $rowAgency['payment'] == 2){ // Prepaid
                        if(!empty($this->data['TTicket']['t_agent_id'])){
                            mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                        VALUES (".$this->data['TTicket']['t_agent_id'].", ".$totalPaid.", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
                            $balanceId  = mysql_insert_id();
                            $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$this->data['TTicket']['t_agent_id']); 
                            $rowBalance = mysql_fetch_array($sqlBalance);
                            if($rowBalance['balance'] <= 0){
                                $checkBalance = false;
                            }
                        }
                    }
                }
            }
            if($avaiable == true && $checkBalance == true){
                $dateNow   = date("Y-m-d H:i:s");
                $tckCode   = "";
                $tckId     = "";
                // Check Edit
                if($editId > 0){
                    $TTicket = $this->TTicket->read(null, $editId);
                    $created = $TTicket['TTicket']['created'];
                    $createdBy = $TTicket['TTicket']['created_by'];
                    $this->data['TTicket']['modified']    = $dateNow;
                    $this->data['TTicket']['created_by']  = $user['User']['id'];
                    $this->data['TTicket']['edit_from']   = $editId;
                    $this->data['TTicket']['code']     = $TTicket['TTicket']['code'];
                    // Update Status Edit
                    mysql_query("UPDATE t_tickets SET status = -2, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$editId);
                    // Delete Seat Control
                    mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_id = ".$editId);
                } else {
                    $created = $dateNow;
                    $createdBy = $user['User']['id'];
                }
                foreach($ticketJourney AS $journey){
                    $company   = ClassRegistry::init('Company')->find('first', array("conditions" => array("Company.id" => $journey['company_id'])));
                    $branch    = ClassRegistry::init('Branch')->find('first', array("conditions" => array("Branch.id" => $journey['branch_id'])));
                    $departure = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $journey['departure_id'])));
                    $destFrom  = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $journey['t_destination_from_id'])));
                    $destTo    = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $journey['t_destination_to_id'])));
                    $journeyIndex = $journey['t_journey_id'];
                    // Check Transportation Type Change
                    $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id FROM t_journey_change_transportations WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$journey['t_journey_id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
                    if(mysql_num_rows($sqlCT)){
                        $rowCT = mysql_fetch_array($sqlCT);
                        $trasportationId   = $rowCT['t_transportation_type_id'];
                    } else {
                        $trasportationId   = $journey['t_transportation_type_id'];
                    }
                    $this->TTicket->create();
                    if($editId > 0){
                        $this->data['TTicket']['date']     = $TTicket['TTicket']['date'];
                    } else {
                        $bookingTime = (int) date("H");
                        if($this->Helper->checkDateFrom($journey['branch_id'], $bookingTime) == 0){
                            $this->data['TTicket']['date'] = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
                        } else {
                            $this->data['TTicket']['date'] = date("Y-m-d");
                        }
                    }
                    if(strtotime($date) >= strtotime("2021-12-07")){
                        $travelDate = $date;
                    } else {
                        if(strtotime($journey['departure_time']) < strtotime($transit['TDepartureTime']['name'])){
                            $travelDate = date('Y-m-d', strtotime($date . ' +1 day'));
                        } else {
                            $travelDate = $date;
                        }
                    }
                    $this->data['TTicket']['sys_code']     = $this->data[$journeyIndex]['t_sys_code'];
                    $this->data['TTicket']['company_id']   = $journey['company_id'];
                    $this->data['TTicket']['branch_id']    = $journey['branch_id'];
                    $this->data['TTicket']['main_branch_id'] = $user['User']['main_branch_id'];
                    $this->data['TTicket']['journey_date'] = $travelDate;
                    $this->data['TTicket']['journey_time'] = $departure['TDepartureTime']['name'];
                    $this->data['TTicket']['t_journey_id'] = $journey['t_journey_id'];
                    $this->data['TTicket']['t_transportation_type_id']   = $trasportationId;
                    $this->data['TTicket']['t_journey_transit_id']   = $transitId;
                    $this->data['TTicket']['t_journey_departure_id'] = $journey['departure_id'];
                    $this->data['TTicket']['t_destination_from_id']  = $journey['t_destination_from_id'];
                    $this->data['TTicket']['t_destination_to_id']    = $journey['t_destination_to_id'];
                    $this->data['TTicket']['t_route_id']          = $journey['t_route_id'];
                    $this->data['TTicket']['reject_time']         = $journey['reject_before_departure'];
                    $this->data['TTicket']['currency_center_id']  = $journey['currency_center_id'];
                    $this->data['TTicket']['t_boarding_point_id'] = $this->data[$journeyIndex]['t_boarding_point_id'];
                    $this->data['TTicket']['t_drop_off_id'] = $this->data[$journeyIndex]['t_drop_off_id'];
                    $this->data['TTicket']['pick_id']       = $this->data[$journeyIndex]['t_pick_up_id'];
                    $this->data['TTicket']['customer_name'] = $this->data[$journeyIndex]['customer_name'];
                    $this->data['TTicket']['telephone']   = $this->data[$journeyIndex]['telephone'];
                    $this->data['TTicket']['email']       = $this->data[$journeyIndex]['email'];
                    $this->data['TTicket']['price']       = $this->data[$journeyIndex]['t_price'];
                    $this->data['TTicket']['total_seat']  = $this->data[$journeyIndex]['total_seat'];
                    $this->data['TTicket']['created']     = $created;
                    $this->data['TTicket']['created_by']  = $createdBy;
                    $this->data['TTicket']['status']      = 1;
                    $this->data['TTicket']['balance']     = 0;
                    $this->data['TTicket']['journey_type'] = $this->data['TTicket']['round_trip'];
                    // Calculate Total Discount
                    $totalDiscount = 0;
                    if($this->data['discount_percent'] > 0){
                        $totalDiscount = $this->Helper->replaceThousand(number_format(($totalAmtJourney[$journeyIndex]['total'] * $this->data['discount_percent']) / 100, 2));
                    }
                    $this->data['TTicket']['discount_amount']  = $totalDiscount;
                    $this->data['TTicket']['discount_percent'] = $this->data['discount_percent'];
                    $this->data['TTicket']['total_amount']     = $totalAmtJourney[$journeyIndex]['total'];
                    if($this->data['TTicket']['type'] == 2){
                        $this->data['TTicket']['balance'] = $this->data['TTicket']['total_amount'];
                    } else if($this->data['TTicket']['type'] == 1 || $this->data['TTicket']['type'] == 3) {
                        $this->data['TTicket']['status'] = 2;
                    }
                    // Check Agent Commission
                    if(!empty($this->data['TTicket']['t_agent_id'])){
                        $sqlCom = mysql_query("SELECT * FROM t_journey_agent_prices WHERE t_journey_id = ".$journey['t_journey_id']." AND t_agent_id = ".$this->data['TTicket']['t_agent_id']." AND is_active = 1");
                        if(mysql_num_rows($sqlCom)){
                            $rowCom = mysql_fetch_array($sqlCom);
                            if($rowCom['percent'] > 0){
                                $totalCommission = ($this->data['TTicket']['total_amount'] * $rowCom['percent']) / 100;
                            } else {
                                $totalCommission = $rowCom['amount'];
                            }
                            $this->data['TTicket']['commission'] = $totalCommission;
                            $this->data['TTicket']['commission_percent'] = $rowCom['percent'];
                        } else {
                            if($journey['agetn_price_percent'] > 0){
                                $totalCommission = ($this->data['TTicket']['total_amount'] * $journey['agetn_price_percent']) / 100;
                            } else {
                                $totalCommission = $journey['agent_price_amount'];
                            }
                            $this->data['TTicket']['commission'] = $totalCommission;
                            $this->data['TTicket']['commission_percent'] = $journey['agetn_price_percent'];
                        }
                    }
                    if($editId == 0){
                        // Code
                        if($this->data['TTicket']['type'] == 1){ // Walk In
                            $code = 'T';
                            if($this->data['TTicket']['round_trip'] == 2 && $this->data['TTicket']['return'] == 0){
                                $code = 'TR';
                            } else if($this->data['TTicket']['round_trip'] == 1 && $this->data['TTicket']['return'] == 1){
                                $code = 'R';
                            }
                        } else if($this->data['TTicket']['type'] == 2){ // Phone Call
                            $code = 'P';
                        } else { // Agency
                            $code = 'A';
                        }
                        $ticketCode = $branch['Branch']['code']."-".SERVER_ID."-".$code;
                        $this->data['TTicket']['tmp_code'] = $ticketCode;
                    } else {
                        $code = 'R';
                    }
                    if($this->TTicket->save($this->data)) {
                        $ticketId = $this->TTicket->id;
                        $tckId    = $ticketId;
                        if($editId == 0){
                            // Get Module Code
                            $modCode    = $this->Helper->getModuleCode($ticketCode, $ticketId, 'code', 't_tickets', 'status >= 0 AND branch_id = '.$this->data['TTicket']['branch_id']);
                            // Updaet Module Code
                            $this->data['TTicket']['code'] = $modCode;
                            mysql_query("UPDATE t_tickets SET code = '".$this->data['TTicket']['code']."' WHERE id = ".$ticketId);
                        } else {
                            $modCode = $this->data['TTicket']['code'];
                        }
                        $tckCode = $modCode;
                        // Seat
                        $this->loadModel('TTicketDetail');
                        $this->loadModel('TSeatControl');
                        $this->loadModel('TSeatControlCloud');
                        for ($i = 0; $i < sizeof($this->data[$journeyIndex]['seat_number']); $i++) {
                            $this->TTicketDetail->create();
                            $seat = array();
                            $seat['TTicketDetail']['sys_code']     = SERVER_ID."S".$this->Helper->generateRandomString(8);
                            $seat['TTicketDetail']['t_ticket_id']  = $ticketId;
                            $seat['TTicketDetail']['seat_number']  = $this->data[$journeyIndex]['seat_number'][$i];
                            $seat['TTicketDetail']['label_number'] = $this->data[$journeyIndex]['label_number'][$i];
                            $seat['TTicketDetail']['is_special']   = $this->data[$journeyIndex]['is_special'][$i];
                            $seat['TTicketDetail']['gender']       = $this->data[$journeyIndex]['gender'][$i];
                            $seat['TTicketDetail']['unit_price']   = $this->data[$journeyIndex]['price'][$i];
                            $seat['TTicketDetail']['discount']     = $this->data[$journeyIndex]['discount'][$i];
                            $seat['TTicketDetail']['total_amount'] = $this->data[$journeyIndex]['price'][$i];
                            $this->TTicketDetail->save($seat);
                            $ticketDetailId = $this->TTicketDetail->id;
                            $status = 2;
                            if($this->data['TTicket']['type'] == 2){
                                $status = 1;
                            }
                            // Update Seat Information
                            mysql_query("UPDATE t_seat_controls SET t_ticket_id = ".$ticketId.", t_ticket_detail_id = ".$ticketDetailId.", type = 1, status = ".$status." WHERE sys_code = '".$this->data[$journeyIndex]['sys_code'][$i]."' AND seat_number = '".$this->data[$journeyIndex]['seat_number'][$i]."'");
                        }
                        if($editId == 0){
                            // Get Module Code
                            $modCode    = $this->Helper->getModuleCode($ticketCode, $ticketId, 'code', 't_tickets', 'status >= 0 AND branch_id = '.$this->data['TTicket']['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE t_tickets SET code = '".$modCode."' WHERE id = ".$ticketId);
                        } else {
                            $modCode = $this->data['TTicket']['code'];
                        }
                        // Update Seat Control reference
                        mysql_query("UPDATE t_seat_controls SET reference = '".$modCode."' WHERE t_ticket_id = ".$ticketId);
                        $depare  = explode(":", $this->data['TTicket']['journey_time']);
                        $depareureTime = (int) $depare[0];
                        if($this->Helper->checkDateFrom($this->data['TTicket']['branch_id'], $depareureTime) == 1){
                            $travelDate = $this->Helper->dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                        } else {
                            $travelDate = date("d/m/Y", strtotime("+1 day", strtotime($this->data['TTicket']['journey_date'])))." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                        }
                        // Transportaion Type
                        $transportaionType = '';
                        if($this->data['TTicket']['company_id'] != 1 && !empty($this->data['TTicket']['t_boat_id'])){
                            $sqlType = mysql_query("SELECT name FROM t_transportation_types WHERE id = (SELECT t_transportation_type_id FROM t_boats WHERE id = ".$this->data['TTicket']['t_boat_id'].")");
                            $rowType = mysql_fetch_array($sqlType);
                            $transportaionType = $rowType[0];
                        }
                        // Branch To
                        $sqlBranchTo  = mysql_query("SELECT name, telephone FROM branches WHERE id IN (SELECT branch_id FROM branch_destinations WHERE t_destination_id = ".$this->data['TTicket']['t_destination_to_id'].") AND company_id = ".$this->data['TTicket']['company_id']);
                        $rowBranchTo  = mysql_fetch_array($sqlBranchTo);
                        $result[$journeyIndex]['id']      = $ticketId;
                        $result[$journeyIndex]['company'] = $company['Company']['name'];
                        $result[$journeyIndex]['website'] = $company['Company']['website'];
                        $result[$journeyIndex]['company_type'] = $company['Company']['type'];
                        $result[$journeyIndex]['dest_from'] = $destFrom['TDestination']['name'];
                        $result[$journeyIndex]['dest_to']   = $destTo['TDestination']['name'];
                        $result[$journeyIndex]['dest_from_code'] = $destFrom['TDestination']['code'];
                        $result[$journeyIndex]['dest_to_code']   = $destTo['TDestination']['code'];
                        $result[$journeyIndex]['booking_date']   = $this->Helper->dateShort($this->data['TTicket']['date']);
                        $result[$journeyIndex]['travel_date']    = $travelDate;
                        $result[$journeyIndex]['created_by']     = $user['User']['first_name']." ".$user['User']['last_name'];
                        $result[$journeyIndex]['agency_ref']     = $this->data['TTicket']['agt_refer_code'];
                        $result[$journeyIndex]['ticket_type']    = $code;
                        $result[$journeyIndex]['ticket_code']    = $modCode;
                        $result[$journeyIndex]['trans_type']     = $transportaionType;
                        $result[$journeyIndex]['branch_from']      = $branch['Branch']['name'];
                        $result[$journeyIndex]['branch_from_tel']  = $branch['Branch']['telephone'];
                        $result[$journeyIndex]['branch_to']      = $rowBranchTo['name'];
                        $result[$journeyIndex]['branch_to_tel']  = $rowBranchTo['telephone'];
                        $result[$journeyIndex]['sys_code']       = $this->data['TTicket']['sys_code'];
                    }
                }
                // Balance
                if(!empty($balanceId)){
                    mysql_query("UPDATE agency_balances SET t_ticket_id = '".$tckId."', reference = '".$tckCode."' WHERE id = ".$balanceId);
                }
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New');
                $TJourneyTransit  = ClassRegistry::init('TJourney')->find('first', array("conditions" => array("TJourney.id" => $transitId)));
                $destFrom  = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $TJourneyTransit['TJourney']['t_destination_from_id'])));
                $destTo    = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $TJourneyTransit['TJourney']['t_destination_to_id'])));
                // Get Printer Name 
                $printerName = '';
                $printSilent = 0;
                $sqlPrinter  = mysql_query("SELECT printer_name, silent FROM printers WHERE type_id = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPrinter)){
                    $rowPrinter  = mysql_fetch_array($sqlPrinter);
                    $printerName = $rowPrinter[0]; 
                    $printSilent = $rowPrinter[1];
                }
                $result['error'] = 0;
                $result['print_date']     = date("d/m/Y H:i:s");
                $result['printer_name']   = $printerName;
                $result['printer_silent'] = $printSilent;
                $result['dest_from_id']   = $destFrom['TDestination']['id'];
                $result['dest_to_id']     = $destTo['TDestination']['id'];
                echo json_encode($result);
                exit;
            } else {
                if(!empty($balanceId)){
                    mysql_query("DELETE FROM agency_balances WHERE id = ".$balanceId);
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Error)');
                    $result['error'] = 3;
                    echo json_encode($result);
                    exit;
                } else {
                    // Delete Seat Tmp
                    foreach($ticketJourney AS $journey){
                        $journeyIndex = $journey['t_journey_id'];
                        for ($i = 0; $i < sizeof($this->data[$journeyIndex]['seat_number']); $i++) {
                            mysql_query("DELETE FROM t_seat_controls WHERE sys_code = '".$this->data[$journeyIndex]['sys_code'][$i]."'");
                        }
                    }
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Some Seat Not Avaiable)');
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }
        } else {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Some Seat Not Avaiable)');
            $result['error'] = 3;
            echo json_encode($result);
            exit;
        }
    }
    
    function addReturnOpen($from, $to, $referenceId){
        $this->layout = 'ajax';
        $result = array();
        if(empty($from) || empty($to) || empty($referenceId)){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $user     = $this->getCurrentUser();
        $TTicket  = $this->TTicket->read(null, $referenceId);
        $TJourney = ClassRegistry::init('TJourney')->find('first', array("conditions" => array("TJourney.t_destination_from_id" => $from, "TJourney.t_destination_to_id" => $to, "TJourney.company_id" => $TTicket['TTicket']['company_id'], "TJourney.status" => 1)));
        if($TTicket['TTicket']['price_type'] == 2){
            $price = $TJourney['TJourney']['membership'];
        } else if($TTicket['TTicket']['price_type'] == 3){
            $price = $TJourney['TJourney']['foreigner_price'];
        } else {
            $price = $TJourney['TJourney']['unit_price'];
        }
        // Check Price Period
        $sqlPriceJourney = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND start <= '".date("Y-m-d")."' AND end >= '".date("Y-m-d")."' AND status = 1 AND t_journey_id = ".$TJourney['TJourney']['id']." AND apply_type = 1 ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlPriceJourney)){
            $rowPriceJourney = mysql_fetch_array($sqlPriceJourney);
            if($TTicket['TTicket']['price_type'] == 2){
                $price = $rowPriceJourney['membership'];
            } else if($TTicket['TTicket']['price_type'] == 3){
                $price = $rowPriceJourney['foreigner_price'];
            } else {
                $price = $rowPriceJourney['price'];
            }
        } else {
            // By Destination
            $sqlPrice = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$TJourney['TJourney']['t_destination_from_id']." AND destination_to_id = ".$TJourney['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$TJourney['TJourney']['t_transportation_type_id']." AND start <= '".date("Y-m-d")."' AND end >= '".date("Y-m-d")."' AND status = 1 AND apply_type = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlPrice)){
                $rowPrice = mysql_fetch_array($sqlPrice);
                if($rowPrice['price_type'] == 1){
                    if($TTicket['TTicket']['price_type'] == 2){
                        $price = $rowPrice['membership'];
                    } else if($TTicket['TTicket']['price_type'] == 3){
                        $price = $rowPrice['foreigner_price'];
                    } else {
                        $price = $rowPrice['price'];
                    }
                } else {
                    if($TTicket['TTicket']['price_type'] == 2){
                        $price = $price + $rowPrice['membership'];
                    } else if($TTicket['TTicket']['price_type'] == 3){
                        $price = $price + $rowPrice['foreigner_price'];
                    } else {
                        $price = $price + $rowPrice['price'];
                    }
                }
            } else {
                $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$TJourney['TJourney']['t_destination_from_id']." AND destination_to_id = ".$TJourney['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$TJourney['TJourney']['t_transportation_type_id']." AND start <= '".date("Y-m-d")."' AND end >= '".date("Y-m-d")."' AND status = 1 AND apply_type = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                if(mysql_num_rows($sqlPA)){
                    $rowPAPrice = mysql_fetch_array($sqlPA);
                    if($rowPAPrice['price_type'] == 1){
                        if($TTicket['TTicket']['price_type'] == 2){
                            $price = $rowPAPrice['membership'];
                        } else if($TTicket['TTicket']['price_type'] == 3){
                            $price = $rowPAPrice['foreigner_price'];
                        } else {
                            $price = $rowPAPrice['price'];
                        }
                    } else {
                        if($TTicket['TTicket']['price_type'] == 2){
                            $price = $price + $rowPAPrice['membership'];
                        } else if($TTicket['TTicket']['price_type'] == 3){
                            $price = $price + $rowPAPrice['foreigner_price'];
                        } else {
                            $price = $price + $rowPAPrice['price'];
                        }
                    }
                }
            }
        }
        $totalPrice = $price * $TTicket['TTicket']['total_seat'];
        $totalDisc  = 0;
        $totalVat   = 0;
        $unitVatPrice = 0;
        if($TTicket['TTicket']['discount_percent'] > 0){
            $totalDisc = $this->Helper->replaceThousand(number_format(($totalPrice * $TTicket['TTicket']['discount_percent']) / 100, 2));
        }
        if($TJourney['TJourney']['allow_price_period'] == 0){ // VAT Disabled
            $totalVat = ($totalPrice * 10) / 100;
        }
        if($totalVat > 0){
            $unitVatPrice = $totalVat / $TTicket['TTicket']['total_seat'];
        }
        $amountPaid = $totalPrice - $totalDisc;
        // Check Agency
        $balanceId  = 0;
        $checkBalance = true;
        // if(!empty($TTicket['TTicket']['t_agent_id'])){
        //     $sqlAgency = mysql_query("SELECT commission, commission_type, payment FROM t_agents WHERE id = ".$TTicket['TTicket']['t_agent_id']);
        //     if(mysql_num_rows($sqlAgency)){
        //         $rowAgency = mysql_fetch_array($sqlAgency);
        //         // Calculate Commission (Default Agency Price No Commission)
        //         if($rowAgency['commission_type'] != 2){ // != Default Agency Price
        //             if($rowAgency['commission_type'] == 1){ // Commission (%)
        //                 if($rowAgency['commission'] > 0 && $this->data['TTicket']['total_amount'] > 0){
        //                     $totalCommission = ($this->data['TTicket']['total_amount'] * $rowAgency['commission']) / 100;
        //                     $this->data['TTicket']['commission'] = $totalCommission;
        //                     $this->data['TTicket']['commission_percent'] = $rowAgency['commission'];
        //                 }
        //             } else { // Fixed Amount
        //                 $totalCommission  = $rowAgency['commission'];
        //                 $this->data['TTicket']['commission'] = $totalCommission * sizeof($this->data['seat_number']);
        //                 $this->data['TTicket']['commission_percent'] = 0;
        //             }
        //         }
        //         if($rowAgency['payment'] == 1 || $rowAgency['payment'] == 2){ // Prepaid
        //             if(!empty($TTicket['TTicket']['t_agent_id'])){
        //                 mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
        //                              VALUES (".$TTicket['TTicket']['t_agent_id'].", ".$amountPaid.", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
        //                 $balanceId  = mysql_insert_id();
        //                 $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$TTicket['TTicket']['t_agent_id']); 
        //                 $rowBalance = mysql_fetch_array($sqlBalance);
        //                 if($rowAgency['payment'] == 1){
        //                     if($rowBalance['balance'] <= 0){
        //                         $checkBalance = false;
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
        if($checkBalance == true){
            $dateNow  = date("Y-m-d H:i:s");
            $company  = ClassRegistry::init('Company')->find('first', array("conditions" => array("Company.id" => $TJourney['TJourney']['company_id'])));
            $branch   = ClassRegistry::init('Branch')->find('first', array("conditions" => array("Branch.id" => $TJourney['TJourney']['branch_id'])));
            $destFrom = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $from)));
            $destTo   = ClassRegistry::init('TDestination')->find('first', array("conditions" => array("TDestination.id" => $to)));
            $this->TTicket->create();
            $this->data['TTicket']['sys_code']       = SERVER_ID.$this->Helper->generateRandomString(8);
            $this->data['TTicket']['date']           = $TTicket['TTicket']['date'];
            $this->data['TTicket']['company_id']     = $TJourney['TJourney']['company_id'];
            $this->data['TTicket']['branch_id']      = $TJourney['TJourney']['branch_id'];
            $this->data['TTicket']['main_branch_id'] = $user['User']['main_branch_id'];
            $this->data['TTicket']['t_journey_id']   = $TJourney['TJourney']['id'];
            $this->data['TTicket']['journey_date']   = '0000-00-00';
            $this->data['TTicket']['journey_time']   = '00:00:00';
            $this->data['TTicket']['t_destination_from_id'] = $from;
            $this->data['TTicket']['t_destination_to_id']   = $to;
            $this->data['TTicket']['t_agent_id']       = $TTicket['TTicket']['t_agent_id'];
            $this->data['TTicket']['agt_refer_code']   = $TTicket['TTicket']['agt_refer_code'];
            $this->data['TTicket']['telephone']        = $TTicket['TTicket']['telephone'];
            $this->data['TTicket']['price']            = $price;
            $this->data['TTicket']['discount_amount']  = $totalDisc;
            $this->data['TTicket']['discount_percent'] = $TTicket['TTicket']['discount_percent'];
            $this->data['TTicket']['total_amount']     = $totalPrice;
            $this->data['TTicket']['total_vat']        = $totalVat;
            $this->data['TTicket']['lucky_draw_fee']   = $TTicket['TTicket']['lucky_draw_fee'];
            $this->data['TTicket']['balance']          = 0;
            $this->data['TTicket']['currency_center_id']  = $TJourney['TJourney']['currency_center_id'];
            $this->data['TTicket']['total_seat']       = $TTicket['TTicket']['total_seat'];
            $this->data['TTicket']['ticket_reference'] = $referenceId;
            $this->data['TTicket']['is_open_date']     = 1;
            $this->data['TTicket']['journey_type']     = 1;
            $this->data['TTicket']['price_type']       = $TTicket['TTicket']['price_type'];
            $this->data['TTicket']['type']             = $TTicket['TTicket']['type'];
            $this->data['TTicket']['reject_time']      = $TJourney['TJourney']['reject_before_departure'];
            $this->data['TTicket']['created']          = $dateNow;
            $this->data['TTicket']['created_by']       = $user['User']['id'];
            $this->data['TTicket']['status']           = 2;
            $ticketCode  = $branch['Branch']['code']."-".SERVER_ID."-R";
            $this->data['TTicket']['tmp_code'] = $ticketCode;
            if($this->TTicket->save($this->data)) {
                $ticketId = $this->TTicket->id;
                // Get Module Code
                $modCode     = $this->Helper->getModuleCode($ticketCode, $ticketId, 'code', 't_tickets', 'status >= 0 AND branch_id = '.$this->data['TTicket']['branch_id']);
                // Updaet Module Code
                $this->data['TTicket']['code'] = $modCode;
                mysql_query("UPDATE t_tickets SET code = '".$this->data['TTicket']['code']."' WHERE id = ".$ticketId);
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New');
                // Branch To
                $sqlBranchTo = mysql_query("SELECT name, telephone FROM branches WHERE id IN (SELECT branch_id FROM branch_destinations WHERE t_destination_id = ".$this->data['TTicket']['t_destination_to_id'].") AND company_id = ".$this->data['TTicket']['company_id']);
                $rowBranchTo = mysql_fetch_array($sqlBranchTo);
                // Balance
                if(!empty($balanceId)){
                    mysql_query("UPDATE agency_balances SET t_ticket_id = ".$ticketId.", reference = '".$modCode."' WHERE id = ".$balanceId);
                    mysql_query("UPDATE t_agents SET balance = (balance - ".$amountPaid.") WHERE id = ".$this->data['TTicket']['t_agent_id']);
                }
                $result['error']   = 0;
                $result['id']      = $ticketId;
                $result['company'] = $company['Company']['name'];
                $result['website'] = $company['Company']['website'];
                $result['company_type'] = $company['Company']['type'];
                $result['dest_from_id'] = $destFrom['TDestination']['id'];
                $result['dest_to_id']   = $destTo['TDestination']['id'];
                $result['dest_from']    = $destFrom['TDestination']['name'];
                $result['dest_to']      = $destTo['TDestination']['name'];
                if(!empty($this->data['TTicket']['lucky_draw_fee'])){
                    if($this->data['TTicket']['lucky_draw_fee'] > 0){
                        $result['booking_date']   = $this->Helper->dateShort($this->data['TTicket']['date'])." (<b>Lucky Draw</b>)";
                    }
                } else {
                    $this->data['TTicket']['lucky_draw_fee'] = 0;
                    $result['booking_date']   = $this->Helper->dateShort($this->data['TTicket']['date']);
                }
                $result['travel_date']      = 'Open Date';
                $result['total_seat']       = $this->data['TTicket']['total_seat'];
                $result['created_by']       = $user['User']['first_name']." ".$user['User']['last_name'];
                $result['agency_ref']       = $this->data['TTicket']['agt_refer_code'];
                $result['price_type']       = $this->data['TTicket']['price_type'];
                $result['price']            = number_format($this->data['TTicket']['price'] + $unitVatPrice, 2);
                $result['ticket_type']      = 'R';
                $result['ticket_code']      = $modCode;
                $result['branch_from']      = $branch['Branch']['name'];
                $result['branch_from_tel']  = $branch['Branch']['telephone'];
                $result['branch_to']        = $rowBranchTo['name'];
                $result['branch_to_tel']    = $rowBranchTo['telephone'];
                $result['print_date']       = date("d/m/Y H:i:s");
                $result['seat']             = "";
                $result['unit_price']       = $this->data['TTicket']['price'] + $unitVatPrice;
                $result['total_vat']        = number_format($this->data['TTicket']['total_vat'], 2);
                $result['extra_price']      = number_format($this->data['TTicket']['lucky_draw_fee'], 2);
                $result['total_dis']        = number_format($this->data['TTicket']['discount_amount'], 2);
                $result['total_amount']     = number_format($this->data['TTicket']['total_amount'] + $this->data['TTicket']['total_vat'], 2);
                $result['total_usd']        = number_format($this->data['TTicket']['total_amount'] - $this->data['TTicket']['discount_amount'] + $this->data['TTicket']['total_vat'] + $this->data['TTicket']['lucky_draw_fee'], 2);
                $result['total_riel']       = number_format(($this->data['TTicket']['total_amount'] - $this->data['TTicket']['discount_amount'] + $this->data['TTicket']['total_vat'] + $this->data['TTicket']['lucky_draw_fee']) * 4150, 0);
                echo json_encode($result);
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Error)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
        } else {
            if(!empty($balanceId)){
                mysql_query("DELETE FROM agency_balances WHERE id = ".$balanceId);
            }
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Add New (Error)');
            $result['error'] = 3;
            echo json_encode($result);
            exit;
        }
    }

    function editOpen($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $TTicket = $this->TTicket->read(null, $id);
        $TDestinations = ClassRegistry::init('TDestination')->find('all', array('conditions' => array('TDestination.id IN ('.$TTicket['TTicket']['t_destination_from_id'].', '.$TTicket['TTicket']['t_destination_to_id'].')')));
        $this->set(compact('TTicket', 'TDestinations'));
    }
    
    function printAward($id=null){
        $this->layout = 'ajax';
        if(!$id){
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Print Award', $id);
        $this->data = $this->TTicket->read(null, $id);
    }

    function printVatInvoice($id=null){
        $this->layout = 'ajax';
        if(!$id){
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Print VAT Invoice', $id);
        $this->data = $this->TTicket->read(null, $id);
    }

    function printLucky($id=null){
        $this->layout = 'ajax';
        if(!$id){
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Print Lucky Ticket', $id);
        $this->data = $this->TTicket->read(null, $id);
    }
    
    function findBooks(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Find Booking');
    }
    
    function findBooksAjax(){
        $this->layout = 'ajax';
    }
    
    function cancelTicket($id = null){
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $reason   = $_POST['reason'];
        $sqlTicket = mysql_query("SELECT *, '1' AS tbl_type FROM t_tickets WHERE id = ".$id."
                                  UNION ALL
                                  SELECT *, '2' AS tbl_type FROM t_ticket_3months WHERE id = ".$id);
        if(mysql_num_rows($sqlTicket)){
            $rowTicket = mysql_fetch_array($sqlTicket);
            $tblTicket = 't_tickets';
            if($rowTicket['tbl_type'] == 2){
                $tblTicket = 't_ticket_3months';
            }
            // Update Ticket
            mysql_query("UPDATE ".$tblTicket." SET status = -1, note = '".$reason."', modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
            mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_id = ".$id);
            // Delete Agency Balance
            if($rowTicket['t_agent_id']){
                mysql_query("DELETE FROM agency_balances WHERE t_ticket_id = ".$id);
            }
        }
        // Save to Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Cancel Ticket');
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $sqlTicket = mysql_query("SELECT t_tickets.*, '1' AS tbl_type FROM t_tickets WHERE id = ".$id."
                                  UNION ALL
                                  SELECT t_ticket_3months.*, '2' AS tbl_type FROM t_ticket_3months WHERE id = ".$id);
        if(mysql_num_rows($sqlTicket)){
            $rowTicket = mysql_fetch_array($sqlTicket);
            $tblTicket = 't_tickets';
            $tblTicketDetail = 't_ticket_details';
            if($rowTicket['tbl_type'] == 2){
                $tblTicket = 't_ticket_3months';
                $tblTicketDetail = 't_ticket_detail_3months';
            }
            // Update Ticket
            mysql_query("UPDATE ".$tblTicket." SET status = 0, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
            mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_id = ".$id);
            // Check Agency Balance
            $ticketId = $id;
            if($rowTicket['type'] == 7){ // Agency APi
                $sqlTmpTicket = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowTicket['online_order_id']);
                $rowTmpTicket = mysql_fetch_array($sqlTmpTicket);
                $ticketId = $rowTmpTicket['id'];
            }
            $sqlChk = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$ticketId." AND module = 'Ticket Booking'");
            if(mysql_num_rows($sqlChk)){
                $agencyId = 0;
                $balance  = 0;
                while($rowBalance = mysql_fetch_array($sqlChk)){
                    mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `t_ticket_id`, `reference`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                VALUES (".$rowBalance['t_agency_id'].", ".$rowBalance['t_ticket_id'].", '".$rowBalance['reference']."', 0, ".$rowBalance['debit'].", 2, 'Void Ticket Booking', now(), ".$user['User']['id'].");");
                    $agencyId = $rowBalance['t_agency_id'];
                    $balance += $rowBalance['debit'];
                }
                mysql_query("UPDATE t_agents SET balance = (balance + ".$balance.") WHERE id = ".$agencyId);
            }
            // Check 10 Free 1
            $sqlDetail = mysql_query("SELECT * FROM ".$tblTicketDetail." WHERE t_ticket_id = ".$id." AND is_free = 1 AND is_active = 1");
            while($rowDetail = mysql_fetch_array($sqlDetail)){
                $sqlPromotion = mysql_query("SELECT id FROM online_customer_promotions WHERE t_ticket_detail_id = ".$rowDetail['id']);
                if(mysql_num_rows($sqlPromotion)){
                    $rowPromotion = mysql_fetch_array($sqlPromotion);
                    mysql_query("UPDATE online_customer_promotions SET status = 1 WHERE id < ".$rowPromotion['id']." AND status = 2 LIMIT 10");
                }
            }
            // Update Ticket Status 10 Free 1
            mysql_query("UPDATE online_customer_promotions SET status = 0 WHERE t_ticket_id = ".$id);
            // Update Ticket Detail
            mysql_query("UPDATE ".$tblTicketDetail." SET is_active = 2 WHERE t_ticket_id = ".$id);
        }
        // Save to Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Void Ticket');
        echo MESSAGE_DATA_HAS_BEEN_VOID;
        exit;    
    }
    
    function booking($id, $departureId, $date, $isReturn, $editId = 0){
        $this->layout  = 'ajax';
        if (!$id || !$departureId || !$date) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $tTicket   = ClassRegistry::init('TTicket')->find('first', array('conditions' => array('TTicket.id' => $editId)));
        $journey   = ClassRegistry::init('TJourney')->find('first', array('conditions' => array('TJourney.id' => $id)));
        $departure = ClassRegistry::init('TDepartureTime')->find('first', array('conditions' => array('TDepartureTime.id' => $departureId)));
        // Check Transportation Type Change
        $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$id." ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlCT)){
            $rowCT = mysql_fetch_array($sqlCT);
            $trasportationId = $rowCT['t_transportation_type_id'];
        } else {
            $trasportationId = $journey['TJourney']['t_transportation_type_id'];
        }
        $tTransportaion = ClassRegistry::init('TTransportationType')->find('first', array('conditions' => array('TTransportationType.id' => $trasportationId)));
        $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $journey['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3,4)')));
        $sysCode = SERVER_ID.$this->Helper->generateRandomString(8);
        $this->set(compact('sysCode', 'journey', 'departure', 'tSeatControlls', 'tTransportaion', 'date', 'isReturn', 'tTicket', 'editId', 'trasportationId'));
    }
    
    function bookingTransit($id, $departureId, $date, $isReturn){
        $this->layout  = 'ajax';
        if (!$id || !$departureId || !$date) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $transits  = ClassRegistry::init('TJourneyTransit')->find('all', array('conditions' => array('TJourneyTransit.t_journey_id' => $id, 'is_active' => 1), "order" => "TJourneyTransit.id"));
        $journey   = ClassRegistry::init('TJourney')->find('first', array('conditions' => array('TJourney.id' => $id)));
        $departure = ClassRegistry::init('TDepartureTime')->find('first', array('conditions' => array('TDepartureTime.id' => $departureId)));
        $tAgents   = ClassRegistry::init('TAgent')->find('all', 
                    array('joins' => array(array('table' => 't_agent_companies', 'type' => 'inner', 'conditions' => array('t_agent_companies.t_agent_id=TAgent.id'))),
                          'fields' => array('TAgent.id', 'TAgent.name'),
                          'conditions' => array('TAgent.status = 1', 't_agent_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'),
                          'group' => 'TAgent.id'));
        $this->set(compact('transits', 'journey', 'departure', 'date', 'tAgents', 'isReturn', 'sysCode'));
    }
    
    function editBooking($id, $departureId, $date, $isReturn, $editId){
        $this->layout  = 'ajax';
        if (!$id || !$departureId || !$date) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $transits  = ClassRegistry::init('TJourneyTransit')->find('all', array('conditions' => array('TJourneyTransit.t_journey_id' => $id, 'is_active' => 1), "order" => "TJourneyTransit.id"));
        $journey   = ClassRegistry::init('TJourney')->find('first', array('conditions' => array('TJourney.id' => $id)));
        $departure = ClassRegistry::init('TDepartureTime')->find('first', array('conditions' => array('TDepartureTime.id' => $departureId)));
        $tAgents   = ClassRegistry::init('TAgent')->find('all', 
                    array('joins' => array(array('table' => 't_agent_companies', 'type' => 'inner', 'conditions' => array('t_agent_companies.t_agent_id=TAgent.id'))),
                          'fields' => array('TAgent.id', 'TAgent.name'),
                          'conditions' => array('TAgent.status = 1', 't_agent_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'),
                          'group' => 'TAgent.id'));
        $this->set(compact('transits', 'journey', 'departure', 'date', 'tAgents', 'isReturn', 'editId'));
    }
    
    function transitDetail($id, $departureId, $date){
        $this->layout  = 'ajax';
        if (!$id || !$departureId || !$date) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $journey   = ClassRegistry::init('TJourney')->find('first', array('conditions' => array('TJourney.id' => $id)));
        $departure = ClassRegistry::init('TDepartureTime')->find('first', array('conditions' => array('TDepartureTime.id' => $departureId)));
        // Check Transportation Type Change
        $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$id." ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlCT)){
            $rowCT = mysql_fetch_array($sqlCT);
            $trasportationId = $rowCT['t_transportation_type_id'];
        } else {
            $trasportationId = $journey['TJourney']['t_transportation_type_id'];
        }
        $tTransportaion = ClassRegistry::init('TTransportationType')->find('first', array('conditions' => array('TTransportationType.id' => $trasportationId)));
        $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $journey['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3)')));
        $sysCode = SERVER_ID.$this->Helper->generateRandomString(8);
        $this->set(compact('journey', 'departure', 'tSeatControlls', 'tTransportaion', 'date', 'travelDate', 'sysCode', 'trasportationId'));
    }
    
    function viewSchedule($desFrom, $desTo, $date, $isReturn, $editId = 0){
        $this->layout  = 'ajax';
        $user = $this->getCurrentUser();
        if (!$desFrom || !$desTo || !$date) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $condition = '';
        if(!empty($_GET['company'])){
            if($condition != ''){
                $condition .= ' AND';
            }
            $condition .= ' TJourney.company_id = '.$_GET['company'];
        }
        if(!empty($_GET['branch'])){
            if($condition != ''){
                $condition .= ' AND';
            }
            $condition .= ' TJourney.branch_id = '.$_GET['branch'];
        } else {
            if($condition != ''){
                $condition .= ' AND';
            }
            $condition .= ' TJourney.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if(!empty($_GET['departure'])){
            if($condition != ''){
                $condition .= ' AND';
            }
            $condition .= ' TJourney.t_departure_time_id = '.$_GET['departure'];
        }
        // Filter for Agency User
        $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlAgency)){
            $rowAgency = mysql_fetch_array($sqlAgency);
            if($rowAgency['type'] == 3){ // Agency APi
                if($condition != ''){
                    $condition .= ' AND';
                }
                $condition .= ' TJourney.allow_access IN (1,3)';
            }
        }
        // $journeys = ClassRegistry::init('TJourney')->find('all', array('conditions' => array('TJourney.t_destination_from_id' => $desFrom, 'TJourney.t_destination_to_id' => $desTo, 'TJourney.status' => 1, 'TJourney.offline_project_id' => $user['User']['offline_project_id'], $condition), "order" => array("TDepartureTime.name", "TTransportationType.name")));
        $journeys = ClassRegistry::init('TJourney')->find('all', array('conditions' => array('TDestination.t_destination_group_id IN (SELECT t_destination_group_id FROM t_destinations WHERE id = '.$desFrom.')' , 'TJourney.t_destination_to_id' => $desTo, 'TJourney.status' => 1, 'TJourney.offline_project_id' => $user['User']['offline_project_id'], $condition), "order" => array("TDepartureTime.name", "TTransportationType.name")));
        $destFrom = ClassRegistry::init('TDestination')->find('first', array('conditions' => array('TDestination.id' => $desFrom)));
        $destTo   = ClassRegistry::init('TDestination')->find('first', array('conditions' => array('TDestination.id' => $desTo)));
        $this->set(compact('journeys', 'destFrom', 'destTo', 'date', 'isReturn', 'editId'));
    }
    
    function getDestinationTo($destFromId = null){
        $this->layout  = 'ajax';
        $result = '<option value="">'.REPORT_TO.'</option>';
        if(!empty($destFromId)){
            $user = $this->getCurrentUser();
            $sqlDestTo = mysql_query("SELECT * FROM t_destinations WHERE is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id']." AND id IN (SELECT t_destination_to_id FROM t_destination_tos WHERE t_destination_from_id = ".$destFromId.")");
            while($rowDesTo = mysql_fetch_array($sqlDestTo)){
                // Check Destination with Journey
                $sqlChk = mysql_query("SELECT id FROM t_journeys WHERE t_destination_from_id = ".$destFromId." AND t_destination_to_id = ".$rowDesTo['id']." AND offline_project_id = 1 AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") AND status IN (1,2,3) LIMIT 1");
                if(mysql_num_rows($sqlChk)){
                    $result .= '<option value="'.$rowDesTo['id'].'">'.$rowDesTo['name'].'</option>';
                }
            }
        }
        echo $result;
        exit;
    }

    function voidSeat($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        // Check Ticket Detail
        $sqlSeat = mysql_query("SELECT *, '1' AS tbl_type FROM t_ticket_details WHERE id = ".$id." AND is_active = 1
                                UNION ALL
                                SELECT *, '2' AS tbl_type FROM t_ticket_detail_3months WHERE id = ".$id." AND is_active = 1");
        if(mysql_num_rows($sqlSeat)){
            $rowSeat = mysql_fetch_array($sqlSeat);
            $tblTicketDetail = 't_ticket_details';
            $tblTicket = 't_tickets';
            if($rowSeat['tbl_type'] == 2){
                $tblTicketDetail = 't_ticket_detail_3months';
                $tblTicket = 't_ticket_3months';
            }
            mysql_query("UPDATE ".$tblTicketDetail." SET is_active = 2, modified = now(), modified_by = ".$user['User']['id']." WHERE id = ".$id);
            mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_detail_id = ".$rowSeat['id']);
            $sqlTicket = mysql_query("SELECT * FROM ".$tblTicket." WHERE id = ".$rowSeat['t_ticket_id']);
            $rowTicket = mysql_fetch_array($sqlTicket);
            // SUM Price
            $totalPrice = 0;
            $sqlDetail = mysql_query("SELECT * FROM ".$tblTicketDetail." WHERE t_ticket_id = ".$rowSeat['t_ticket_id']." AND is_active = 1");
            while($rowDetail = mysql_fetch_array($sqlDetail)){
                $totalPrice += $rowDetail['total_amount'];
            }
            if($rowTicket['balance'] > 0){
                $balance = $totalPrice;
            } else {
                $balance = 0;
            }
            if($totalPrice == 0){
                mysql_query("UPDATE ".$tblTicket." SET total_amount = ".$totalPrice.", balance = ".$balance.", status = 0 WHERE id = ".$rowSeat['t_ticket_id']);
            } else {
                mysql_query("UPDATE ".$tblTicket." SET total_amount = ".$totalPrice.", balance = ".$balance." WHERE id = ".$rowSeat['t_ticket_id']);
            }
            // Check Agency Balance
            $sqlChk = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$id." AND module = 'Ticket Booking' LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowAgBalance = mysql_fetch_array($sqlChk);
                mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `t_ticket_id`, `reference`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                             VALUES (".$rowAgBalance['t_agency_id'].", ".$rowAgBalance['t_ticket_id'].", '".$rowAgBalance['reference']."', 0, ".$rowSeat['total_amount'].", 2, 'Void Seat Ticket', now(), ".$user['User']['id'].");");
                $agencyId = 0;
                $balance  = 0;
                $sqlBalance = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$id." AND module = 'Ticket Booking'");
                while($rowBalance = mysql_fetch_array($sqlBalance)){
                    $agencyId = $rowBalance['t_agency_id'];
                    $balance += $rowBalance['debit'];
                }
                mysql_query("UPDATE t_agents SET balance = (balance + ".$balance.") WHERE id = ".$agencyId);
            }
            // Check Free
            if($rowSeat['is_free'] == 1){
                $sqlPromotion = mysql_query("SELECT id FROM online_customer_promotions WHERE t_ticket_detail_id = ".$rowSeat['id']);
                if(mysql_num_rows($sqlPromotion)){
                    $rowPromotion = mysql_fetch_array($sqlPromotion);
                    mysql_query("UPDATE online_customer_promotions SET status = 1 WHERE id < ".$rowPromotion['id']." AND status = 2 LIMIT 10");
                }
            }
        }
        // Save to Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Void Seat Ticket');
        echo MESSAGE_DATA_HAS_BEEN_VOID;
        exit;    
    }

    function releaseSeat($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        // Optional note from UI
        $note = isset($_GET['note']) ? trim($_GET['note']) : '';
        // Require note
        if($note === ''){
            echo 'Note is required';
            exit;
        }
        // Check Ticket Detail
        $sqlSeat = mysql_query("SELECT *, '1' AS tbl_type FROM t_ticket_details WHERE id = ".$id." AND is_active = 1 AND is_sync = 0
                                UNION ALL
                                SELECT *, '2' AS tbl_type FROM t_ticket_detail_3months WHERE id = ".$id." AND is_active = 1 AND is_sync = 0");
        if(mysql_num_rows($sqlSeat)){
            $rowSeat = mysql_fetch_array($sqlSeat);
            $tblTicketDetail = 't_ticket_details';
            if($rowSeat['tbl_type'] == 2){
                $tblTicketDetail = 't_ticket_detail_3months';
            }
            mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_detail_id = ".$id);
            // Update Ticket Detail
            mysql_query("UPDATE ".$tblTicketDetail." SET release_date = now(), release_by = ".$user['User']['id'].", is_sync = 1, `note` = '".mysql_real_escape_string($note)."' WHERE id = ".$id);
            // Update History
            mysql_query("INSERT INTO `release_seats` (`id`, `t_ticket_id`, `t_ticket_detail_id`, `note`, `created`, `created_by`) 
                         VALUES (NULL, ".$rowSeat['t_ticket_id'].", ".$id.", '".mysql_real_escape_string($note)."', now(), ".$user['User']['id'].");");
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Release Seat Ticket');
        echo MESSAGE_DATA_HAS_BEEN_VOID;
        exit;    
    }

    function saveNote($id = null, $note = '') {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $sqlTicket = mysql_query("SELECT *, '1' AS tbl_type FROM t_tickets WHERE id = ".$id."
                                  UNION ALL
                                  SELECT *, '2' AS tbl_type FROM t_ticket_3months WHERE id = ".$id);
        if(mysql_num_rows($sqlTicket)){
            $rowTicket = mysql_fetch_array($sqlTicket);
            $tblTicket = 't_tickets';
            if($rowTicket['tbl_type'] == 2){
                $tblTicket = 't_ticket_3months';
            }
            mysql_query('UPDATE '.$tblTicket.' SET note="' . mysql_real_escape_string($note) . '" WHERE id=' . $id);
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Save Note');
        echo MESSAGE_DATA_HAS_BEEN_VOID;
        exit();
    }

    function viewApiResult() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Dashboard');
    }

    function apiAjax() {
        $this->layout = 'ajax';
    }

    function agencyApiBooked(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Dashboard');
        $condition = "";
        $agencyBooked = array();
        $sqlTicketBooked = mysql_query("SELECT t_agents.id
                                        FROM t_tickets 
                                        INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type IN (1,3) AND t_agents.id != 106 AND t_agents.id != 55
                                        WHERE t_tickets.`journey_date` = '".date("Y-m-d")."' AND t_tickets.offline_project_id = 1 AND t_tickets.`status` = 2
                                        GROUP BY t_tickets.t_agent_id;");
        while($rowBooked = mysql_fetch_array($sqlTicketBooked)){
            $agencyBooked[] = $rowBooked['id'];
        }
        if(!empty($agencyBooked)){
            $condition = " AND TAgent.id IN (".implode(",", $agencyBooked).")";
        }
        $agencyApis = ClassRegistry::init('TAgent')->find('all', array("conditions" => array("TAgent.status = 1 AND TAgent.type IN (1,3)".$condition)));
        $this->set(compact('agencyApis'));
    }

    function agencyApiBookedResult($agency = 'all'){
        $this->layout = 'ajax';
        $this->set(compact('agency'));
    }

    function agencyApiBookedDetail($agency = 'all'){
        $this->layout = 'ajax';
        $this->set(compact('agency'));
    }

    function changeShift(){
        $this->layout = 'ajax';
    }

    function checkTicketChangeShift(){
        $this->layout = 'ajax';
        $result['status'] = 0;
        $result['response'] = "";
        if(!empty($this->data['code'])){
            $result['status'] = 1;
            // Internal Ticket (Walk-In, App, Website, Terminal, Mini App) (30 Minutes)
            $sqlTicket = mysql_query("SELECT t_tickets.id, t_tickets.code, t_tickets.telephone, t_ticket_details.id AS detail_id, t_ticket_details.label_number, (t_ticket_details.total_amount + t_ticket_details.vat_price - t_ticket_details.discount) AS amount 
                                      FROM t_tickets 
                                      INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 AND t_ticket_details.is_change = 0 AND t_ticket_details.total_change = 0
                                      WHERE t_tickets.code = '".$this->data['code']."' AND t_tickets.status = 2 AND t_tickets.type IN (1, 5, 10, 11) AND DATE_ADD(CONCAT(t_tickets.journey_date,' ',t_tickets.journey_time), INTERVAL 30 MINUTE) > now()
                                      UNION ALL
                                      SELECT t_tickets.id, t_tickets.code, t_tickets.telephone, t_ticket_details.id AS detail_id, t_ticket_details.label_number, (t_ticket_details.total_amount + t_ticket_details.vat_price - t_ticket_details.discount) AS amount 
                                      FROM t_ticket_3months AS t_tickets 
                                      INNER JOIN t_ticket_detail_3months AS t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 AND t_ticket_details.is_change = 0 AND t_ticket_details.total_change = 0
                                      WHERE t_tickets.code = '".$this->data['code']."' AND t_tickets.status = 2 AND t_tickets.type IN (1, 5, 10, 11) AND DATE_ADD(CONCAT(t_tickets.journey_date,' ',t_tickets.journey_time), INTERVAL 30 MINUTE) > now()");
            while($rowTicket = mysql_fetch_array($sqlTicket)){
                $result['response'] .= '<tr>';
                $result['response'] .= '<td class="first">'.$rowTicket['code'].'</td>';
                $result['response'] .= '<td>'.$rowTicket['telephone'].'</td>';
                $result['response'] .= '<td>'.$rowTicket['label_number'].'</td>';
                $result['response'] .= '<td><input type="checkbox" class="chkSeatChangeShift" tel="'.$rowTicket['telephone'].'" t-id="'.$rowTicket['id'].'" amt="'.$rowTicket['amount'].'" rel="'.$rowTicket['label_number'].'" data="'.$rowTicket['detail_id'].'" /></td>';
                $result['response'] .= '</tr>';
            }
            // VET APP (Digital) (4 Hour)
            $sqlTicket = mysql_query("SELECT t_tickets.id, t_tickets.code, t_tickets.telephone, t_ticket_details.id AS detail_id, t_ticket_details.label_number, (t_ticket_details.total_amount + t_ticket_details.vat_price - t_ticket_details.discount) AS amount 
                                      FROM t_tickets 
                                      INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 AND t_ticket_details.is_change = 0 AND t_ticket_details.total_change = 0
                                      WHERE t_tickets.code = '".$this->data['code']."' AND t_tickets.status = 2 AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 2 AND DATE_ADD(CONCAT(t_tickets.journey_date,' ',t_tickets.journey_time), INTERVAL 4 HOUR) > now()
                                      UNION ALL
                                      SELECT t_tickets.id, t_tickets.code, t_tickets.telephone, t_ticket_details.id AS detail_id, t_ticket_details.label_number, (t_ticket_details.total_amount + t_ticket_details.vat_price - t_ticket_details.discount) AS amount 
                                      FROM t_ticket_3months AS t_tickets 
                                      INNER JOIN t_ticket_detail_3months AS t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 AND t_ticket_details.is_change = 0 AND t_ticket_details.total_change = 0
                                      WHERE t_tickets.code = '".$this->data['code']."' AND t_tickets.status = 2 AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 2 AND DATE_ADD(CONCAT(t_tickets.journey_date,' ',t_tickets.journey_time), INTERVAL 4 HOUR) > now()
                                      UNION ALL
                                      SELECT t_tickets.id, t_tickets.code, t_tickets.telephone, t_ticket_details.id AS detail_id, t_ticket_details.label_number, (t_ticket_details.total_amount + t_ticket_details.vat_price - t_ticket_details.discount) AS amount 
                                      FROM t_tickets 
                                      INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 AND t_ticket_details.is_change = 0 AND t_ticket_details.total_change = 0
                                      WHERE t_tickets.code = '".$this->data['code']."' AND t_tickets.status = 2 AND t_tickets.t_agent_id = 91 AND t_tickets.online_order_id IS NULL AND DATE_ADD(CONCAT(t_tickets.journey_date,' ',t_tickets.journey_time), INTERVAL 4 HOUR) > now()
                                      UNION ALL
                                      SELECT t_tickets.id, t_tickets.code, t_tickets.telephone, t_ticket_details.id AS detail_id, t_ticket_details.label_number, (t_ticket_details.total_amount + t_ticket_details.vat_price - t_ticket_details.discount) AS amount 
                                      FROM t_ticket_3months AS t_tickets 
                                      INNER JOIN t_ticket_detail_3months AS t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 AND t_ticket_details.is_change = 0 AND t_ticket_details.total_change = 0
                                      WHERE t_tickets.code = '".$this->data['code']."' AND t_tickets.status = 2 AND t_tickets.t_agent_id = 91 AND t_tickets.online_order_id IS NULL AND DATE_ADD(CONCAT(t_tickets.journey_date,' ',t_tickets.journey_time), INTERVAL 4 HOUR) > now()");
            while($rowTicket = mysql_fetch_array($sqlTicket)){
                $result['response'] .= '<tr>';
                $result['response'] .= '<td class="first">'.$rowTicket['code'].'</td>';
                $result['response'] .= '<td>'.$rowTicket['telephone'].'</td>';
                $result['response'] .= '<td>'.$rowTicket['label_number'].'</td>';
                $result['response'] .= '<td><input type="checkbox" class="chkSeatChangeShift" tel="'.$rowTicket['telephone'].'" t-id="'.$rowTicket['id'].'" amt="'.$rowTicket['amount'].'" rel="'.$rowTicket['label_number'].'" data="'.$rowTicket['detail_id'].'" /></td>';
                $result['response'] .= '</tr>';
            }
        }
        echo json_encode($result);
        exit;
    }

    function addLuckyTicket($id = null){
        $this->layout = 'ajax';
        $result = array();
        $result['error'] = 1;
        if (empty($id)) {
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Add Lucky Ticket');
        $TTicket  = $this->TTicket->read(null, $id);
        if(!empty($TTicket)){
            if($TTicket['TTicket']['status'] == 2 && $TTicket['TTicket']['lucky_draw_fee'] == 0){
                $totalLucky = 0.25 * $TTicket['TTicket']['total_seat'];
                mysql_query("UPDATE t_tickets SET lucky_draw_fee = ".$totalLucky." WHERE id = ".$id);
                mysql_query("INSERT INTO `lucky_tickets` (`id`, `main_branch_id`, `t_ticket_id`, `created`, `created_by`) 
                             VALUES (NULL, '".$user['User']['main_branch_id']."', '".$id."', now(), '".$user['User']['id']."');");
                $result['error'] = 0;
            }
        }
        echo json_encode($result);
        exit;
    }

    function applyCoupon(){
        $this->layout = 'ajax';
        $result = array();
        $result['error'] = 1;
        $coupon      = isset($this->data['coupon']) ? $this->data['coupon'] : '';
        $travelDate  = isset($this->data['date']) ? $this->data['date'] : '';
        $totalAmount = isset($this->data['amount']) ? $this->data['amount'] : null;
        if($coupon === '' || $travelDate === '' || $totalAmount === null){
            echo json_encode($result);
            exit;
        }
        $sqlCoupon = mysql_query("SELECT * FROM coupons WHERE code = '".$coupon."' AND `start` <= '".$travelDate."' AND `end` >= '".$travelDate."' AND status = 2");
        if(mysql_num_rows($sqlCoupon)){
            $rowCoupon = mysql_fetch_array($sqlCoupon);
            $couponAmt = $rowCoupon['amount'];
            $hasError  = false;
            $sqlUsed   = mysql_query("SELECT COUNT(id) AS total_used, SUM(amount) AS total_amount FROM coupon_transactions WHERE coupon_id = ".$rowCoupon['id']);
            if(mysql_num_rows($sqlUsed)){
                $rowUsed = mysql_fetch_array($sqlUsed);
                if(((int)$rowUsed['total_used']) >= ((int)$rowCoupon['total_time_use'])){
                    $result['error'] = 2;
                    $hasError = true;
                }
                $couponAmt = $rowCoupon['amount'] -  $rowUsed['total_amount'];
            }
            if($hasError == false){
                if(((float)$totalAmount) > ((float)$couponAmt)){
                    $result['error'] = 3;
                    $hasError = true;
                }
            }
            if($hasError == false){
                $result['error'] = 0;
            }
        }
        echo json_encode($result);
        exit;
    }

}

?>
