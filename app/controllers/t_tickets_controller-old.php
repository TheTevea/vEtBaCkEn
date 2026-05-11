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

    function ajax($isOpen = 'all', $status = 'all', $show = 1, $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('isOpen', 'status', 'show', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'View', $id);
        $this->data = $this->TTicket->read(null, $id);
    }

    function add($journeyId, $departureId, $date, $editId = 0) {
        $this->layout = 'ajax';
        $result = array();
        if(empty($journeyId) || empty($departureId) || empty($date) || sizeof($this->data['seat_number']) == 0){
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
            $markup = $this->data['TTicket']['total_markup'];
            for ($i = 0; $i < sizeof($this->data['price']); $i++) {
                $totalAmount += $this->data['price'][$i] - $this->data['TTicket']['total_markup'];
                $totalMarkup += $markup;
                $totalDiscount += $this->data['discount'][$i];
            }
            $this->data['TTicket']['total_amount'] = $totalAmount;
            $this->data['TTicket']['total_markup'] = $totalMarkup;
            $this->data['TTicket']['discount_amount'] = $totalDiscount;
            // Check Agency
            $balanceId  = 0;
            $checkBalance = true;
            if(!empty($this->data['TTicket']['t_agent_id'])){
                $sqlAgency = mysql_query("SELECT commission, commission_type, payment, max_balance FROM t_agents WHERE id = ".$this->data['TTicket']['t_agent_id']);
                if(mysql_num_rows($sqlAgency)){
                    $rowAgency = mysql_fetch_array($sqlAgency);
                    // Calculate Commission (Default Agency Price No Commission)
                    if($rowAgency['commission_type'] != 2){ // != Default Agency Price
                        if($rowAgency['commission_type'] == 1){ // Commission (%)
                            if($rowAgency['commission'] > 0 && $this->data['TTicket']['total_amount'] > 0){
                                $totalCommission = ($this->data['TTicket']['total_amount'] * $rowAgency['commission']) / 100;
                                $this->data['TTicket']['commission'] = $totalCommission;
                                $this->data['TTicket']['commission_percent'] = $rowAgency['commission'];
                            }
                        } else { // Fixed Amount
                            $totalCommission  = $rowAgency['commission'];
                            $this->data['TTicket']['commission'] = $totalCommission * sizeof($this->data['seat_number']);
                            $this->data['TTicket']['commission_percent'] = 0;
                        }
                    }
                    // Check Balance
                    if($rowAgency['payment'] == 1 || $rowAgency['payment'] == 2){ // Prepaid or Postpaid
                        // Check Foriegner Price
                        if($this->data['TTicket']['price_type'] == 3){ // Foriegner
                            $totalAgencyNetPrice = $TJourney['TJourney']['agetn_price_percent'] * sizeof($this->data['seat_number']);
                        } else {
                            $totalAgencyNetPrice = $TJourney['TJourney']['agent_price_amount'] * sizeof($this->data['seat_number']);
                        }
                        mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                     VALUES (".$this->data['TTicket']['t_agent_id'].", ".$totalAgencyNetPrice.", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
                        $balanceId  = mysql_insert_id();
                        if($rowAgency['payment'] == 1){
                            $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$this->data['TTicket']['t_agent_id']); 
                            $rowBalance = mysql_fetch_array($sqlBalance);
                            if($rowBalance['balance'] <= 0){
                                $checkBalance = false;
                            }
                        }
                    } else { // Postpaid
                        $sqlTkSell = mysql_query("SELECT IFNULL((SELECT SUM(total_amount) AS balance FROM t_tickets WHERE status = 2 AND t_agent_id = ".$this->data['TTicket']['t_agent_id']." AND MONTH(date) = '".date('m')."' AND YEAR(date) = '".date('Y')."'), 0) AS total_balance;");
                        $rowTkSell = mysql_fetch_array($sqlTkSell);
                        if(($rowTkSell['total_balance'] + $this->data['TTicket']['total_amount']) > $rowAgency['max_balance']){
                            $checkBalance = false;
                        }
                    }
                }
            }
            // Check Seat Available
            $avaiable = true;
            if($checkBalance == true){
                for ($i = 0; $i < sizeof($this->data['seat_number']); $i++) {
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
                                $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data['seat_number'][$i]."' AND t_transportation_type_id = ".$rowTransit['t_transportation_type_id']." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$travelDate."' AND status IN (1,2,3) AND id < ".$tmpSeatId.$checkSeatCon.";");
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
                            $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data['seat_number'][$i]."' AND t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$TJourney['TJourney']['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3) AND id < ".$tmpSeatId.$checkSeatCon.";");
                            if(mysql_num_rows($sqlCheck)){
                                $avaiable = false;
                            }
                        } else {
                            $avaiable = false;
                        }
                    }
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
                    if(!empty($tTicket)){
                        if($tTicket['TTicket']['is_open_date'] == 1){
                            $created   = $TTicket['TTicket']['created'];
                            $createdBy = $TTicket['TTicket']['created_by'];
                            $this->data['TTicket']['code']     = $TTicket['TTicket']['code'];
                            $this->data['TTicket']['date']     = $TTicket['TTicket']['date'];
                            $this->data['TTicket']['modified']    = $dateNow;
                            $this->data['TTicket']['modified_by'] = $user['User']['id'];
                            $checkCodeGen = false;
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
                $this->data['TTicket']['company_id']   = $TJourney['TJourney']['company_id'];
                $this->data['TTicket']['branch_id']    = $TJourney['TJourney']['branch_id'];
                $this->data['TTicket']['main_branch_id'] = $user['User']['main_branch_id'];
                $this->data['TTicket']['journey_date']   = $date;
                $this->data['TTicket']['journey_time']   = $departure['TDepartureTime']['name'];
                $this->data['TTicket']['t_journey_id']   = $TJourney['TJourney']['id'];
                $this->data['TTicket']['t_transportation_type_id']   = $trasportationId;
                $this->data['TTicket']['t_journey_departure_id'] = $departureId;
                $this->data['TTicket']['t_destination_from_id']  = $TJourney['TJourney']['t_destination_from_id'];
                $this->data['TTicket']['t_destination_to_id']    = $TJourney['TJourney']['t_destination_to_id'];
                $this->data['TTicket']['t_route_id']  = $TJourney['TJourney']['t_route_id'];
                $this->data['TTicket']['reject_time'] = $TJourney['TJourney']['reject_before_departure'];
                $this->data['TTicket']['currency_center_id'] = $TJourney['TJourney']['currency_center_id'];
                $this->data['TTicket']['created']     = $created;
                $this->data['TTicket']['created_by']  = $createdBy;
                $this->data['TTicket']['status']      = 1;
                $this->data['TTicket']['is_open_date']  = 0;
                $this->data['TTicket']['balance']     = 0;
                $this->data['TTicket']['journey_type'] = $this->data['TTicket']['round_trip'];
                if($this->data['TTicket']['type'] == 2){
                    $this->data['TTicket']['balance'] = $this->data['TTicket']['total_amount'];
                } else if($this->data['TTicket']['type'] == 1 || $this->data['TTicket']['type'] == 3) {
                    $this->data['TTicket']['status'] = 2;
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
                if($this->TTicket->save($this->data)) {
                    $ticketId = $this->TTicket->id;
                    // Seat
                    $this->loadModel('TTicketDetail');
                    $this->loadModel('TSeatControl');
                    $this->loadModel('TSeatControlCloud');
                    for ($i = 0; $i < sizeof($this->data['seat_number']); $i++) {
                        $this->TTicketDetail->create();
                        $seat = array();
                        $seat['TTicketDetail']['sys_code']     = SERVER_ID."S".$this->Helper->generateRandomString(8);
                        $seat['TTicketDetail']['t_ticket_id']  = $ticketId;
                        $seat['TTicketDetail']['seat_number']  = $this->data['seat_number'][$i];
                        $seat['TTicketDetail']['label_number'] = $this->data['label_number'][$i];
                        $seat['TTicketDetail']['is_special']   = $this->data['is_special'][$i];
                        $seat['TTicketDetail']['gender']       = $this->data['gender'][$i];
                        $seat['TTicketDetail']['unit_price']   = $this->data['price'][$i] - $markup;
                        $seat['TTicketDetail']['markup']       = $markup;
                        $seat['TTicketDetail']['discount']     = $this->data['discount'][$i];
                        $seat['TTicketDetail']['total_amount'] = $this->data['price'][$i]; // Include Markup
                        $this->TTicketDetail->save($seat);
                        $ticketDetailId = $this->TTicketDetail->id;
                        $status = 2;
                        if($this->data['TTicket']['type'] == 2){
                            $status = 1;
                        }
                        // Update Seat Information
                        mysql_query("UPDATE t_seat_controls SET t_ticket_id = ".$ticketId.", t_ticket_detail_id = ".$ticketDetailId.", type = 1, status = ".$status." WHERE sys_code = '".$this->data['sys_code'][$i]."' AND seat_number = '".$this->data['seat_number'][$i]."'");
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
                                mysql_query("UPDATE t_tickets SET code = '".$modCode."', tmp_code = '".$tmpCode."', tmp_count = ".($rowCount[0] + 1)." WHERE id = ".$ticketId);
                            }
                        } else {
                            mysql_query("INSERT INTO `ticket_codes` (`offline_project_id`, `code`) VALUES (".$user['User']['offline_project_id'].", '".$ticketCode."');");
                            $ticketCodeId = mysql_insert_id();
                            $sqlCount = mysql_query("SELECT COUNT(id) FROM ticket_codes WHERE code LIKE '".$ticketCode."%' AND offline_project_id = ".$user['User']['offline_project_id']." AND id < ".$ticketCodeId.";");
                            $rowCount = mysql_fetch_array($sqlCount);
                            // Get Module Code
                            $modCode  = $ticketCode.str_pad(($rowCount[0] + 1),6,"0",STR_PAD_LEFT);
                            // Updaet Module Code
                            mysql_query("UPDATE t_tickets SET code = '".$modCode."', tmp_count = ".($rowCount[0] + 1)." WHERE id = ".$ticketId);
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
                    // Balance
                    if(!empty($balanceId)){
                        mysql_query("UPDATE agency_balances SET t_ticket_id = ".$ticketId.", reference = '".$modCode."' WHERE id = ".$balanceId);
                        mysql_query("UPDATE t_agents SET balance = (balance - ".$this->data['TTicket']['total_amount'].") WHERE id = ".$this->data['TTicket']['t_agent_id']);
                    }
                    $result['error']   = 0;
                    $result['id']      = $ticketId;
                    $result['company'] = $company['Company']['name'];
                    $result['website'] = $company['Company']['website'];
                    if($company['Company']['id'] == 6){
                        $result['company_type'] = 2;
                    } else {
                        $result['company_type'] = 1;
                    }
                    $result['dest_from_id'] = $destFrom['TDestination']['id'];
                    $result['dest_to_id']   = $destTo['TDestination']['id'];
                    $result['dest_from']    = $destFrom['TDestination']['name'];
                    $result['dest_to']      = $destTo['TDestination']['name'];
                    $result['dest_from_code'] = $destFrom['TDestination']['code'];
                    $result['dest_to_code']   = $destTo['TDestination']['code'];
                    $result['booking_date']   = $this->Helper->dateShort($this->data['TTicket']['date']);
                    $result['travel_date']    = $travelDate;
                    $result['created_by']     = $user['User']['first_name']." ".$user['User']['last_name'];
                    $result['agency_ref']     = $this->data['TTicket']['agt_refer_code'];
                    if($this->data['TTicket']['type'] == 3 || $this->data['TTicket']['type'] == 4){
                        $result['ticket_type']    = "A";
                    } else {
                        $result['ticket_type']    = $code;
                    }
                    $result['ticket_code']    = $modCode;
                    $result['trans_type']     = $transportaionType;
                    $result['branch_from']      = $branchFrom;
                    $result['branch_from_tel']  = $branchTel;
                    $result['branch_to']      = $rowDropOff['name'];
                    $result['branch_to_tel']  = $rowDropOff['telephone'];
                    $result['print_date']     = date("d/m/Y H:i:s");
                    $result['sys_code']       = $this->data['TTicket']['sys_code'];
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
                                $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data[$journeyIndex]['seat_number'][$i]."' AND t_transportation_type_id = ".$rowTransit['t_transportation_type_id']." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$travelDate."' AND status IN (1,2,3) AND id < ".$tmpSeatId.$checkSeatCon.";");
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
                            $sqlCheck  = mysql_query("SELECT id FROM t_seat_controls WHERE seat_number = '".$this->data[$journeyIndex]['seat_number'][$i]."' AND t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$journey['t_route_id']." AND journey_date = '".$travelDate."' AND status IN (1,2,3) AND id < ".$tmpSeatId.$checkSeatCon.";");
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
        $totalPrice = $price * $TTicket['TTicket']['total_seat'];
        $totalDisc  = 0;
        if($TTicket['TTicket']['discount_percent'] > 0){
            $totalDisc = $this->Helper->replaceThousand(number_format(($totalPrice * $TTicket['TTicket']['discount_percent']) / 100, 2));
        }
        $amountPaid = $totalPrice - $totalDisc;
        // Check Agency
        $balanceId  = 0;
        $checkBalance = true;
        if(!empty($TTicket['TTicket']['t_agent_id'])){
            $sqlAgency = mysql_query("SELECT commission, commission_type, payment FROM t_agents WHERE id = ".$TTicket['TTicket']['t_agent_id']);
            if(mysql_num_rows($sqlAgency)){
                $rowAgency = mysql_fetch_array($sqlAgency);
                // Calculate Commission (Default Agency Price No Commission)
                if($rowAgency['commission_type'] != 2){ // != Default Agency Price
                    if($rowAgency['commission_type'] == 1){ // Commission (%)
                        if($rowAgency['commission'] > 0 && $this->data['TTicket']['total_amount'] > 0){
                            $totalCommission = ($this->data['TTicket']['total_amount'] * $rowAgency['commission']) / 100;
                            $this->data['TTicket']['commission'] = $totalCommission;
                            $this->data['TTicket']['commission_percent'] = $rowAgency['commission'];
                        }
                    } else { // Fixed Amount
                        $totalCommission  = $rowAgency['commission'];
                        $this->data['TTicket']['commission'] = $totalCommission * sizeof($this->data['seat_number']);
                        $this->data['TTicket']['commission_percent'] = 0;
                    }
                }
                if($rowAgency['payment'] == 1 || $rowAgency['payment'] == 2){ // Prepaid
                    if(!empty($TTicket['TTicket']['t_agent_id'])){
                        mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                                     VALUES (".$TTicket['TTicket']['t_agent_id'].", ".$amountPaid.", 0, 1, 'Ticket Booking', now(), ".$user['User']['id'].");");
                        $balanceId  = mysql_insert_id();
                        $sqlBalance = mysql_query("SELECT SUM(IFNULL(credit, 0) - IFNULL(debit, 0)) AS balance FROM agency_balances WHERE t_agency_id = ".$TTicket['TTicket']['t_agent_id']); 
                        $rowBalance = mysql_fetch_array($sqlBalance);
                        if($rowBalance['balance'] <= 0){
                            $checkBalance = false;
                        }
                    }
                }
            }
        }
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
                $result['booking_date']     = $this->Helper->dateShort($this->data['TTicket']['date']);
                $result['travel_date']      = 'Open Date';
                $result['total_seat']       = $this->data['TTicket']['total_seat'];
                $result['created_by']       = $user['User']['first_name']." ".$user['User']['last_name'];
                $result['agency_ref']       = $this->data['TTicket']['agt_refer_code'];
                $result['price_type']       = $this->data['TTicket']['price_type'];
                $result['price']            = number_format($this->data['TTicket']['price'], 2);
                $result['ticket_type']      = 'R';
                $result['ticket_code']      = $modCode;
                $result['branch_from']      = $branch['Branch']['name'];
                $result['branch_from_tel']  = $branch['Branch']['telephone'];
                $result['branch_to']        = $rowBranchTo['name'];
                $result['branch_to_tel']    = $rowBranchTo['telephone'];
                $result['print_date']       = date("d/m/Y H:i:s");
                $result['seat']             = "";
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
        // Update Ticket
        mysql_query("UPDATE t_tickets SET status = -1, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
        mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_id = ".$id);
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
        // Update Ticket
        mysql_query("UPDATE t_tickets SET status = 0, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
        mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_id = ".$id);
        // Check Agency Balance
        $sqlChk = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$id." AND module = 'Ticket Booking'");
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
        $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $journey['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3)')));
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
        $journeys = ClassRegistry::init('TJourney')->find('all', array('conditions' => array('TJourney.t_destination_from_id' => $desFrom, 'TJourney.t_destination_to_id' => $desTo, 'TJourney.status' => 1, 'TJourney.offline_project_id' => $user['User']['offline_project_id'], $condition), "order" => array("TDepartureTime.name", "TTransportationType.name")));
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
                $sqlChk = mysql_query("SELECT id FROM t_journeys WHERE t_destination_from_id = ".$destFromId." AND t_destination_to_id = ".$rowDesTo['id']." AND offline_project_id = 1 AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") AND status > 0 LIMIT 1");
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
        $sqlSeat = mysql_query("SELECT * FROM t_ticket_details WHERE id = ".$id." AND is_active = 1");
        if(mysql_num_rows($sqlSeat)){
            $rowSeat = mysql_fetch_array($sqlSeat);
            mysql_query("UPDATE t_ticket_details SET is_active = 2, modified = now(), modified_by = ".$user['User']['id']." WHERE id = ".$id);
            mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_detail_id = ".$rowSeat['id']);
            $sqlTicket = mysql_query("SELECT * FROM t_tickets WHERE id = ".$rowSeat['t_ticket_id']);
            $rowTicket = mysql_fetch_array($sqlTicket);
            // SUM Price
            $totalPrice = 0;
            $sqlDetail = mysql_query("SELECT * FROM t_ticket_details WHERE t_ticket_id = ".$rowSeat['t_ticket_id']." AND is_active = 1");
            while($rowDetail = mysql_fetch_array($sqlDetail)){
                $totalPrice += $rowDetail['total_amount'];
            }
            if($rowTicket['balance'] > 0){
                $balance = $totalPrice;
            } else {
                $balance = 0;
            }
            if($totalPrice == 0){
                mysql_query("UPDATE t_tickets SET total_amount = ".$totalPrice.", balance = ".$balance.", status = 0 WHERE id = ".$rowSeat['t_ticket_id']);
            } else {
                mysql_query("UPDATE t_tickets SET total_amount = ".$totalPrice.", balance = ".$balance." WHERE id = ".$rowSeat['t_ticket_id']);
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
        // Check Ticket Detail
        $sqlSeat = mysql_query("SELECT * FROM t_ticket_details WHERE id = ".$id." AND is_active = 1 AND is_sync = 0");
        if(mysql_num_rows($sqlSeat)){
            $rowSeat = mysql_fetch_array($sqlSeat);
            mysql_query("UPDATE t_seat_controls SET status = 0 WHERE t_ticket_detail_id = ".$id);
            // Update Ticket Detail
            mysql_query("UPDATE t_ticket_details SET release_date = now(), release_by = ".$user['User']['id'].", is_sync = 1 WHERE id = ".$id);
            // Update History
            mysql_query("INSERT INTO `release_seats` (`id`, `t_ticket_id`, `t_ticket_detail_id`, `created`, `created_by`) 
                         VALUES (NULL, ".$rowSeat['t_ticket_id'].", ".$id.", now(), ".$user['User']['id'].");");
        }
        // Save to Activity
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
        mysql_query('UPDATE t_tickets SET note="' . mysql_real_escape_string($note) . '" WHERE id=' . $id);
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

}

?>