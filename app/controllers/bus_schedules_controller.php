<?php

class BusSchedulesController extends AppController {

    var $name = 'BusSchedules';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Dashboard');
    }

    function ajax($from = 'all', $to = 'all', $bus = 'all', $status = 'all', $date = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('from', 'to', 'bus', 'status', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'View', $id);
        $this->data = $this->BusSchedule->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            for ($i = 0; $i < sizeof($this->data['journey']); $i++) {
                $dateNow  = date("Y-m-d H:i:s");
                $transportationId = 0;
                $sqlTransportation = mysql_query("SELECT bus_types.t_transportation_type_id 
                                                FROM buses 
                                                INNER JOIN bus_types ON bus_types.id = buses.bus_type_id 
                                                WHERE buses.id = ".$this->data['bus_id'][$i]);
                if(mysql_num_rows($sqlTransportation)){
                    $rowTransportation = mysql_fetch_array($sqlTransportation);
                    $transportationId = $rowTransportation['t_transportation_type_id'];
                }
                // $bookingTime = (int) date("H");
                // if($this->Helper->checkDateFrom(1, $bookingTime) == 0){
                //     $this->data['BusSchedule']['date'] = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
                // } else {
                //     $this->data['BusSchedule']['date'] = date("Y-m-d");
                // }
                $this->BusSchedule->create();
                $this->data['BusSchedule']['offline_project_id'] = 1;
                $this->data['BusSchedule']['t_transportation_type_id'] = $transportationId;
                $this->data['BusSchedule']['bus_id']     = $this->data['bus_id'][$i];
                $this->data['BusSchedule']['created']    = $dateNow;
                $this->data['BusSchedule']['created_by'] = $user['User']['id'];
                $this->data['BusSchedule']['status']     = 1;
                if ($this->BusSchedule->save($this->data)) {
                    $saveId = $this->BusSchedule->id;
                    $this->loadModel('BusScheduleDetail');
                    // Sub Journey
                    $indexSub = $this->data['journey'][$i]."journey";
                    if(!empty($this->data[$indexSub])){
                        for ($j = 0; $j < sizeof($this->data[$indexSub]); $j++) {
                            $this->BusScheduleDetail->create();
                            $busScheduleDetail = array();
                            $busScheduleDetail['BusScheduleDetail']['bus_schedule_id'] = $saveId;
                            $busScheduleDetail['BusScheduleDetail']['t_journey_id'] = $this->data[$indexSub][$j];
                            $this->BusScheduleDetail->save($busScheduleDetail);
                        } 
                    }
                    $this->BusScheduleDetail->create();
                    $busScheduleDetail = array();
                    $busScheduleDetail['BusScheduleDetail']['bus_schedule_id'] = $saveId;
                    $busScheduleDetail['BusScheduleDetail']['t_journey_id'] = $this->data['journey'][$i];
                    $this->BusScheduleDetail->save($busScheduleDetail);
                }   
            }
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Save Add New', 0);
            $result['error'] = "0";
            echo json_encode($result);
            exit;
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Add New');
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `bus_schedules` SET `status` = 0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function updateDelay($id = null, $delay = null) {
        if (!$id || !$delay) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `bus_schedules` SET `delay_time` = (IFNULL(delay_time, 0) + ".$delay."), `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Update Delay Time', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function updateNote($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $note = $_POST['note'];
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `bus_schedules` SET `note` = '".$note."', `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Update Note', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function updateBus($id = null, $busId = null) {
        if (!$id || !$busId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `bus_schedules` SET `bus_id` = '".$busId."', `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Update Bus', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function getJourney($fromId, $toId){
        $this->layout = 'ajax';
        $departure = $_POST['departure'];
        $date      = $_POST['date'];
        $result    = array();
        if(empty($fromId) || empty($toId) || empty($departure) || empty($date)){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $response = "";
        $index = 0;
        $transportationId = 0;
        // $sqlTransportation = mysql_query("SELECT bus_types.t_transportation_type_id 
        //                                   FROM buses 
        //                                   INNER JOIN bus_types ON bus_types.id = buses.bus_type_id 
        //                                   WHERE buses.id = ".$busId);
        // if(mysql_num_rows($sqlTransportation)){
        //     $rowTransportation = mysql_fetch_array($sqlTransportation);
        //     $transportationId = $rowTransportation['t_transportation_type_id'];
        // }
        $busOpt = "";
        $sqlBus = mysql_query("SELECT * FROM buses WHERE is_active = 1 AND id NOT IN (SELECT bus_id FROM bus_schedules WHERE t_destination_from_id = ".$fromId." AND date = '".$date."')");
        while($rowBus = mysql_fetch_array($sqlBus)){
            $busOpt .= '<option value="'.$rowBus['id'].'">'.$rowBus['code'].' ('.$rowBus['name'].')</option>'; 
        }
        $sqlJourney = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure,  t_destinations.name AS destFromName, destTo.name AS destToName
                                   FROM t_journeys 
                                   INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_from_id
                                   INNER JOIN t_destinations AS destTo ON destTo.id = t_journeys.t_destination_to_id
                                   INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                   WHERE t_journeys.offline_project_id = 1 AND t_journeys.status = 1 AND t_journeys.t_destination_from_id = ".$fromId." AND t_journeys.t_destination_to_id = ".$toId." AND t_departure_times.name = '".$departure."'");
        while($rowJourney = mysql_fetch_array($sqlJourney)){
            $sqlChk = mysql_query("SELECT bus_schedules.id 
                                   FROM bus_schedules 
                                   INNER JOIN bus_schedule_details ON bus_schedule_details.bus_schedule_id = bus_schedules.id
                                   WHERE bus_schedules.date = '".$date."' AND bus_schedules.status = 1 AND bus_schedule_details.t_journey_id = ".$rowJourney['id']." LIMIT 1");
            if(!mysql_num_rows($sqlChk)){
                $response .= '<tr>';
                $response .= '<td class="first" style="width:5%; height: 30px;">'.++$index.'</td>';
                $response .= '<td style="width:20%;"><input type="hidden" name="data[journey][]" value="'.$rowJourney['id'].'" />'.$rowJourney['description'];
                if($rowJourney['type'] == 3){
                    $response .= ' (<a href="#" ref="'.$rowJourney['id'].'" class="btnBusScheduleShowDetail">Show Detail</a><a href="#" ref="'.$rowJourney['id'].'" class="btnBusScheduleHideDetail" style="display: none;">Hide Detail</a>)';
                }
                $response .= '</td>';
                $response .= '<td style="width:20%;">'.$rowJourney['destFromName'].'</td>';
                $response .= '<td style="width:20%;">'.$rowJourney['destToName'].'</td>';
                $response .= '<td style="width:10%;">'.$rowJourney['departure'].'</td>';
                $response .= '<td style="width:10%;">'.$rowJourney['route_code'].'</td>';
                $response .= '<td style="width:15%;">';
                $response .= '<select name="data[bus_id][]" id="BusScheduleBusId'.$index.'" style="width: 200px;" class="busScheduleBusIsChosen">';
                $response .= '<option value="">'.INPUT_SELECT.'</option>';
                $response .= $busOpt;
                $response .= '</select>';
                $response .= '</td>';
                $response .= '</tr>';
                $transportationId = $rowJourney['t_transportation_type_id'];
                // Multi Route
                $subIndex = 0;
                if($rowJourney['type'] == 3){
                    $sqlTransit = mysql_query("SELECT t_journeys.id, t_journeys.t_destination_to_id
                                               FROM t_journey_transits
                                               INNER JOIN t_journeys ON t_journeys.id = t_journey_transits.t_journey_departure_id AND t_journeys.offline_project_id = 1
                                               WHERE t_journey_transits.t_journey_id = ".$rowJourney['id']." AND t_journey_transits.is_active = 1
                                               GROUP BY t_journey_transits.t_journey_departure_id
                                               ORDER BY t_journey_transits.id");
                    while($rowTransit = mysql_fetch_array($sqlTransit)){
                        // Check Other Route
                        $sqlOtherMulti = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure,  t_destinations.name AS destFromName, destTo.name AS destToName
                                                    FROM t_journeys 
                                                    INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_from_id
                                                    INNER JOIN t_destinations AS destTo ON destTo.id = t_journeys.t_destination_to_id
                                                    INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                                    WHERE t_journeys.id != ".$rowJourney['id']." AND t_journeys.offline_project_id = 1 AND t_journeys.status = 1 AND t_journeys.t_destination_from_id = ".$fromId." AND t_journeys.t_destination_to_id = ".$rowTransit['t_destination_to_id']." AND t_journeys.t_transportation_type_id = ".$transportationId." AND t_departure_times.name = '".$departure."'");
                        while($rowOther = mysql_fetch_array($sqlOtherMulti)){
                            $response .= '<tr class="journeyBusSub'.$rowJourney['id'].'" style="display: none;">';
                            $response .= '<td class="first" style="width:5%; height: 30px;">'.$index.'.'.++$subIndex.'</td>';
                            $response .= '<td style="width:20%;"><input type="hidden" name="data['.$rowJourney['id'].'journey][]" value="'.$rowOther['id'].'" />'.$rowOther['description'].'</td>';
                            $response .= '<td style="width:20%;">'.$rowOther['destFromName'].'</td>';
                            $response .= '<td style="width:20%;">'.$rowOther['destToName'].'</td>';
                            $response .= '<td style="width:10%;">'.$rowOther['departure'].'</td>';
                            $response .= '<td style="width:10%;">'.$rowOther['route_code'].'</td>';
                            $response .= '<td style="width:15%;"></td>';
                            $response .= '</tr>';
                        }
                    }
                } else if($rowJourney['type'] == 1) { // Direct
                    if($rowJourney['t_route_id'] != $rowJourney['single_route_id']){ // Route Not singer route
                        // Check Other Journey in the same route
                        $sqlOtherMulti = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure,  t_destinations.name AS destFromName, destTo.name AS destToName
                                                    FROM t_journeys 
                                                    INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_from_id
                                                    INNER JOIN t_destinations AS destTo ON destTo.id = t_journeys.t_destination_to_id
                                                    INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                                    WHERE t_journeys.id != ".$rowJourney['id']." AND t_journeys.offline_project_id = 1 AND t_journeys.status = 1 AND t_journeys.t_destination_from_id = ".$fromId." AND t_journeys.t_transportation_type_id = ".$rowJourney['t_transportation_type_id']." AND t_journeys.t_route_id = '".$rowJourney['t_route_id']."'");
                        while($rowOther = mysql_fetch_array($sqlOtherMulti)){
                            $response .= '<tr class="journeyBusSub'.$rowJourney['id'].'" style="display: none;">';
                            $response .= '<td class="first" style="width:5%; height: 30px;">'.$index.'.'.++$subIndex.'</td>';
                            $response .= '<td style="width:20%;"><input type="hidden" name="data['.$rowJourney['id'].'journey][]" value="'.$rowOther['id'].'" />'.$rowOther['description'].'</td>';
                            $response .= '<td style="width:20%;">'.$rowOther['destFromName'].'</td>';
                            $response .= '<td style="width:20%;">'.$rowOther['destToName'].'</td>';
                            $response .= '<td style="width:10%;">'.$rowOther['departure'].'</td>';
                            $response .= '<td style="width:10%;">'.$rowOther['route_code'].'</td>';
                            $response .= '<td style="width:15%;"></td>';
                            $response .= '</tr>';
                        }
                    }
                }
            }
        }
        $result['error'] = 0;
        $result['response'] = $response;
        echo json_encode($result);
        exit;
    }

    function getDeparture($fromId, $toId){
        $this->layout = 'ajax';
        $result = array();
        $response = '<option value="">'.INPUT_SELECT.'</option>';
        $sqlDeparture = mysql_query("SELECT t_departure_times.name AS departure, t_destinations.code AS destTo, t_journeys.id, t_transportation_types.name AS bus, t_routes.name AS routes, t_journeys.t_route_id, t_journeys.route_code
                                    FROM t_journeys
                                    INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                    INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_to_id
                                    INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id
                                    INNER JOIN t_routes ON t_routes.id = t_journeys.t_route_id 
                                    WHERE t_journeys.offline_project_id = 1 AND t_journeys.status = 1 AND t_journeys.t_destination_from_id = ".$fromId." AND t_journeys.t_destination_to_id = ".$toId."
                                    ORDER BY t_departure_times.name");
        while($rowDeparture = mysql_fetch_array($sqlDeparture)){
            $sqlChk = mysql_query("SELECT id FROM bus_schedules WHERE date = DATE(now()) AND t_destination_from_id = ".$fromId." AND t_destination_to_id = ".$toId." AND departure = '".$rowDeparture['departure']."' LIMIT 1");
            if(!mysql_num_rows($sqlChk)){
                $remark = "";
                if(!empty($rowDeparture['route_code'])){
                    $remark = " (".$rowDeparture['route_code'].")";
                }
                // if($rowDeparture['routes'] != 'Single Route'){
                //     $sqlChRoute = mysql_query("SELECT id FROM t_journeys WHERE status = 1 AND t_route_id = ".$rowDeparture['t_route_id']." LIMIT 1");
                //     if(mysql_num_rows($sqlChRoute)){
                //         $remark = " (".$rowDeparture['routes'].")";
                //     }
                // } else {
                //     $sqlRoute = mysql_query("SELECT t_journeys.t_destination_from_id, t_journeys.t_destination_to_id, destFrom.code AS destFrom, destTo.code AS destTo
                //                             FROM t_journey_transits 
                //                             INNER JOIN t_journeys ON t_journeys.id = t_journey_transits.t_journey_id AND t_journeys.status > 0 AND t_journeys.type != 2
                //                             INNER JOIN t_destinations AS destFrom ON destFrom.id = t_journeys.t_destination_from_id
                //                             INNER JOIN t_destinations AS destTo ON destTo.id = t_journeys.t_destination_to_id
                //                             WHERE t_journey_transits.t_journey_departure_id = ".$rowDeparture['id']);
                //     if(mysql_num_rows($sqlRoute)){
                //         $remark = " (".$rowDeparture['destTo'];
                //         while($rowRoute = mysql_fetch_array($sqlRoute)){
                //             if($rowRoute['t_destination_from_id'] != $fromId){
                //                 $remark .= ", ".$rowRoute['destFrom'];
                //             } else if($rowRoute['t_destination_to_id'] != $fromId){
                //                 $remark .= ", ".$rowRoute['destTo'];
                //             }
                //         }
                //         $remark .= ")";
                //     }
                // }
                $response .= '<option value="'.$rowDeparture['departure'].'">'.$rowDeparture['departure'].' '.$rowDeparture['bus'].$remark.'</option>';
            }
        }
        $result['error'] = 0;
        $result['response'] = $response;
        echo json_encode($result);
        exit;
    }

    function updateLeave($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `bus_schedules` SET `status` = 2, `left_date`='".$dateNow."', `left_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Leave', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function closeSchedule($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `bus_schedules` SET `status` = 3, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']."  WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Schedule', 'Close', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function updateUnscanNote($id, $seatNumber){
        $this->layout = 'ajax';
        if(empty($id) || empty($seatNumber)){
            echo "Invalid Data";
            exit;
        }
        $this->set(compact('id', 'seatNumber'));
    }

    function saveUpdateUnscanNote($id, $seatNumber){
        $this->layout = 'ajax';
        $result['error'] = 1;
        if(empty($id) || empty($seatNumber) || empty($this->data)){
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        // Update History
        mysql_query("UPDATE bus_schedule_seat_notes SET is_active = 2 WHERE bus_schedule_id = ".$id." AND seat_number = '".$seatNumber."';");
        // Save
        $this->loadModel('BusScheduleSeatNote');
        $noteUpdate = array();
        $this->BusScheduleSeatNote->create();
        $noteUpdate['BusScheduleSeatNote']['bus_schedule_id']   = $id;
        $noteUpdate['BusScheduleSeatNote']['seat_number'] = $seatNumber;
        $noteUpdate['BusScheduleSeatNote']['note']        = $this->data['note'];
        $noteUpdate['BusScheduleSeatNote']['created_by']  = $user['User']['id'];
        $this->BusScheduleSeatNote->save($noteUpdate);
        $result['error'] = 0;
        echo json_encode($result);
        exit;
    }

}

?>