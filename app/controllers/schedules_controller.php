<?php

class SchedulesController extends AppController {

    var $uses = array('TJourney');
    var $components = array('Helper');

    function viewSchedule() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Schedule', 'Dashboard');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'fields' => array('Company.id', 'Company.name'),'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
        $tDestinations = ClassRegistry::init('TDestination')->find('all', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $this->set(compact('tDestinations', 'companies', 'branches'));
    }

    function viewScheduleAjax($companyId = 'all', $branchId = 'all', $from = 'all', $to = 'all', $time = 'all', $routeCode = 'all', $date = '', $status = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'from', 'to', 'time', 'routeCode', 'date', 'status'));
    }
    
    function printSchedule($id = null, $date = ''){
        $this->layout = 'ajax';
        if(!$id){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if($date == ''){
            $date = date("Y-m-d");
        }
        $tJourney = $this->TJourney->read(null, $id);
        if($tJourney['TJourney']['type'] == 1){
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = 1 AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$id." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId = $tJourney['TJourney']['t_transportation_type_id'];
            }
            $tDepartureTime = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $tJourney['TJourney']['t_departure_time_id'])));
            $tBoat = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $trasportationId)));
            $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $tJourney['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3)')));
        } else {
            $tJourneyT = ClassRegistry::init('TJourneyTransit')->find('all', array("conditions" => array("TJourneyTransit.t_journey_id" => $tJourney['TJourney']['id'])));
            $tJourneyF = $this->TJourney->read(null, $tJourneyT[0]['TJourneyTransit']['t_journey_departure_id']);
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = 1 AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$tJourneyF['TJourney']['id']." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId = $tJourneyF['TJourney']['t_transportation_type_id'];
            }
            $tDepartureTime = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $tJourneyF['TJourney']['t_departure_time_id'])));
            $tBoat = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $trasportationId)));
            $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $tJourneyF['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3)')));
        }
        $tJourneyType = ClassRegistry::init('TJourneyType')->find('first', array("conditions" => array("TJourneyType.id" => $tJourney['TJourney']['t_journey_type_id'])));
        $this->set(compact('tJourney','tDepartureTime','tBoat', 'tSeatControlls', 'tJourneyType', 'date'));
    }
    
    function getDepartureTime($companyId = null, $branchId = null, $from = null, $to = null){
        $this->layout = 'ajax';
        $option = '<option value="all">'.TABLE_ALL.'</option>';
        if(!empty($from) && !empty($to) && !empty($companyId) && !empty($branchId)){
            $conCompany = '';
            $conBranch  = '';
            if($companyId != 'all'){
                $conCompany = 't_journeys.company_id = '.$companyId;
            }
            if($branchId != 'all'){
                $conBranch = 't_journeys.branch_id = '.$branchId;
            }
            $departures = ClassRegistry::init('TDepartureTime')->find('all', 
                            array('joins' => array(
                                array('table' => 't_journeys', 'type' => 'inner', 'conditions' => array($conCompany, $conBranch, 't_journeys.t_destination_from_id' => $from, 't_journeys.t_destination_to_id' => $to)),
                                array('table' => 't_journey_departures', 'type' => 'inner', 'conditions' => array('t_journey_departures.t_journey_id=t_journeys.id', 't_journey_departures.t_departure_time_id = TDepartureTime.id'))
                            ), 
                            'fields' => array('TDepartureTime.id', 'TDepartureTime.name'), 
                            'conditions' => array('TDepartureTime.is_active = 1', 't_journey_departures.t_journey_id=t_journeys.id'),
                            'group' => array('TDepartureTime.id'),
                            'order' => 'TDepartureTime.name'
                            ));
            foreach($departures AS $departure){
                $option .= '<option value="'.$departure['TDepartureTime']['id'].'">'.date("h:i A", strtotime($departure['TDepartureTime']['name'])).'</option>';
            }
        }
        echo $option;
        exit;
    }
    
    function viewPlanSeat($id, $date = ''){
        $this->layout = 'ajax';
        if(!$id){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if($date == ''){
            $date = date("Y-m-d");
        }
        $user = $this->getCurrentUser();
        $tJourney = $this->TJourney->read(null, $id);
        if($tJourney['TJourney']['type'] == 1){
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$id." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId = $tJourney['TJourney']['t_transportation_type_id'];
            }
            $tDepartureTime = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $tJourney['TJourney']['t_departure_time_id'])));
            $tBoat = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $trasportationId)));
            $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $tJourney['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (0, 1,2,3)')));
        } else {
            $tJourneyT = ClassRegistry::init('TJourneyTransit')->find('all', array("conditions" => array("TJourneyTransit.t_journey_id" => $tJourney['TJourney']['id'])));
            $tJourneyF = $this->TJourney->read(null, $tJourneyT[0]['TJourneyTransit']['t_journey_departure_id']);
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$tJourneyF['TJourney']['id']." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId = $tJourneyF['TJourney']['t_transportation_type_id'];
            }
            $tDepartureTime = ClassRegistry::init('TDepartureTime')->find('first', array("conditions" => array("TDepartureTime.id" => $tJourneyF['TJourney']['t_departure_time_id'])));
            $tBoat = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $trasportationId)));
            $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $tJourneyF['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (0,1,2,3)')));
            $this->set(compact('tJourneyF', 'tJourneyT'));
        }
        $this->set(compact('tJourney','tDepartureTime','tBoat', 'tSeatControlls', 'date'));
    }
    
    function blockSchedule($id, $date = ''){
        $this->layout = 'ajax';
        if(!$id){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if($date == ''){
            $date = date("Y-m-d");
        }
        $tJourney       = $this->TJourney->read(null, $id);
        if($tJourney['TJourney']['type'] == 1){
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = 1 AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$id." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId = $tJourney['TJourney']['t_transportation_type_id'];
            }
            $tBoat = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $trasportationId)));
            $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $tJourney['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3)')));
        } else {
            $tJourneyT = ClassRegistry::init('TJourneyTransit')->find('all', array("conditions" => array("TJourneyTransit.t_journey_id" => $tJourney['TJourney']['id'])));
            $tJourneyF = $this->TJourney->read(null, $tJourneyT[0]['TJourneyTransit']['t_journey_departure_id']);
            // Check Transportation Type Change
            $sqlCT = mysql_query("SELECT t_transportation_type_id FROM t_journey_change_transportations WHERE offline_project_id = 1 AND status = 1 AND start >= '".$date."' AND end <= '".$date."' AND t_journey_id = ".$tJourneyF['TJourney']['id']." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlCT)){
                $rowCT = mysql_fetch_array($sqlCT);
                $trasportationId = $rowCT['t_transportation_type_id'];
            } else {
                $trasportationId = $tJourneyF['TJourney']['t_transportation_type_id'];
            }
            $tBoat = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $trasportationId)));
            $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $trasportationId, 'TSeatControl.t_route_id' => $tJourneyF['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2,3)')));
        }
        // $tBoat          = ClassRegistry::init('TTransportationType')->find('first', array("conditions" => array("TTransportationType.id" => $tJourney['TJourney']['t_transportation_type_id'])));
        // $tSeatControlls = ClassRegistry::init('TSeatControl')->find('all', array('conditions' => array('TSeatControl.t_transportation_type_id' => $tJourney['TJourney']['t_transportation_type_id'], 'TSeatControl.t_route_id' => $tJourney['TJourney']['t_route_id'], 'TSeatControl.journey_date' => $date, 'TSeatControl.status IN (1,2)')));
        $this->set(compact('tJourney', 'tBoat', 'tSeatControlls', 'date'));
    }
    
    function saveBlockSeat(){
        $this->layout = 'ajax';
        $result = array();
        if(!empty($this->data)){
            $user = $this->getCurrentUser();
            $this->loadModel('TJourneySeatBlock');
            $blockSeat = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->TJourneySeatBlock->create();
            $blockSeat['TJourneySeatBlock']['sys_code'] = $this->Helper->generateRandomString(6);
            $blockSeat['TJourneySeatBlock']['created']  = $dateNow;
            $blockSeat['TJourneySeatBlock']['modified'] = $dateNow;
            $blockSeat['TJourneySeatBlock']['created_by'] = $user['User']['id'];
            $blockSeat['TJourneySeatBlock']['t_journey_id'] = $this->data['TJourneySeatBlock']['t_journey_id'];
            $blockSeat['TJourneySeatBlock']['t_departure_time_id'] = $this->data['TJourneySeatBlock']['t_departure_time_id'];
            $blockSeat['TJourneySeatBlock']['release_date'] = $this->data['TJourneySeatBlock']['release_date'];
            $blockSeat['TJourneySeatBlock']['start'] = $this->data['TJourneySeatBlock']['start']!=''?$this->data['TJourneySeatBlock']['start']:'0000-00-00';
            $blockSeat['TJourneySeatBlock']['end']   = $this->data['TJourneySeatBlock']['end']!=''?$this->data['TJourneySeatBlock']['end']:'0000-00-00';
            $blockSeat['TJourneySeatBlock']['type']  = $this->data['TJourneySeatBlock']['type'];
            $blockSeat['TJourneySeatBlock']['is_active'] = 1;
            if($this->TJourneySeatBlock->save($blockSeat)){
                $blockSeatId = $this->TJourneySeatBlock->id;
                $this->loadModel('TJourneySeatBlockDetail');
                for ($i = 0; $i < sizeof($this->data['TJourneySeatBlock']['seat_number']); $i++) {
                    $detail = array();
                    $this->TJourneySeatBlockDetail->create();
                    $detail['TJourneySeatBlockDetail']['t_journey_seat_block_id'] = $blockSeatId;
                    $detail['TJourneySeatBlockDetail']['seat_number'] = $this->data['TJourneySeatBlock']['seat_number'][$i];
                    $detail['TJourneySeatBlockDetail']['seat_label']  = $this->data['TJourneySeatBlock']['seat_label'][$i];
                    $detail['TJourneySeatBlockDetail']['bk_male']     = $this->data['TJourneySeatBlock']['bk_male'][$i];
                    $detail['TJourneySeatBlockDetail']['bk_female']   = $this->data['TJourneySeatBlock']['bk_female'][$i];
                    $detail['TJourneySeatBlockDetail']['bk_slave']    = $this->data['TJourneySeatBlock']['bk_slave'][$i];
                    $detail['TJourneySeatBlockDetail']['bk_api']      = $this->data['TJourneySeatBlock']['bk_api'][$i];
                    $detail['TJourneySeatBlockDetail']['bk_agency']   = $this->data['TJourneySeatBlock']['bk_agency'][$i];
                    $detail['TJourneySeatBlockDetail']['bk_eticket']  = $this->data['TJourneySeatBlock']['bk_eticket'][$i];
                    $detail['TJourneySeatBlockDetail']['created']     = $dateNow;
                    $detail['TJourneySeatBlockDetail']['created_by']  = $user['User']['id'];
                    $this->TJourneySeatBlockDetail->save($detail);
                }
                // Change Other Journey Under Route and Default Transportation Type
                $tJourney = $this->TJourney->read(null, $this->data['TJourneySeatBlock']['t_journey_id']);
                $sqlJ = mysql_query("SELECT id, t_departure_time_id FROM t_journeys WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status > 0 AND id != ".$tJourney['TJourney']['id']." AND t_route_id = ".$tJourney['TJourney']['t_route_id']." AND t_transportation_type_id = ".$tJourney['TJourney']['t_transportation_type_id']);
                while($rowT = mysql_fetch_array($sqlJ)){
                    $blockSeat = array();
                    $this->TJourneySeatBlock->create();
                    $blockSeat['TJourneySeatBlock']['sys_code'] = $this->Helper->generateRandomString(6);
                    $blockSeat['TJourneySeatBlock']['created']  = $dateNow;
                    $blockSeat['TJourneySeatBlock']['modified'] = $dateNow;
                    $blockSeat['TJourneySeatBlock']['created_by'] = $user['User']['id'];
                    $blockSeat['TJourneySeatBlock']['t_journey_id'] = $rowT['id'];
                    $blockSeat['TJourneySeatBlock']['t_departure_time_id'] = $rowT['t_departure_time_id'];
                    $blockSeat['TJourneySeatBlock']['release_date'] = $this->data['TJourneySeatBlock']['release_date'];
                    $blockSeat['TJourneySeatBlock']['start'] = $this->data['TJourneySeatBlock']['start']!=''?$this->data['TJourneySeatBlock']['start']:'0000-00-00';
                    $blockSeat['TJourneySeatBlock']['end']   = $this->data['TJourneySeatBlock']['end']!=''?$this->data['TJourneySeatBlock']['end']:'0000-00-00';
                    $blockSeat['TJourneySeatBlock']['type']  = $this->data['TJourneySeatBlock']['type'];
                    $blockSeat['TJourneySeatBlock']['is_active'] = 1;
                    if($this->TJourneySeatBlock->save($blockSeat)){
                        $blockSeatId = $this->TJourneySeatBlock->id;
                        $this->loadModel('TJourneySeatBlockDetail');
                        for ($i = 0; $i < sizeof($this->data['TJourneySeatBlock']['seat_number']); $i++) {
                            $detail = array();
                            $this->TJourneySeatBlockDetail->create();
                            $detail['TJourneySeatBlockDetail']['t_journey_seat_block_id'] = $blockSeatId;
                            $detail['TJourneySeatBlockDetail']['seat_number'] = $this->data['TJourneySeatBlock']['seat_number'][$i];
                            $detail['TJourneySeatBlockDetail']['seat_label']  = $this->data['TJourneySeatBlock']['seat_label'][$i];
                            $detail['TJourneySeatBlockDetail']['bk_male']     = $this->data['TJourneySeatBlock']['bk_male'][$i];
                            $detail['TJourneySeatBlockDetail']['bk_female']   = $this->data['TJourneySeatBlock']['bk_female'][$i];
                            $detail['TJourneySeatBlockDetail']['bk_slave']    = $this->data['TJourneySeatBlock']['bk_slave'][$i];
                            $detail['TJourneySeatBlockDetail']['bk_api']      = $this->data['TJourneySeatBlock']['bk_api'][$i];
                            $detail['TJourneySeatBlockDetail']['bk_agency']   = $this->data['TJourneySeatBlock']['bk_agency'][$i];
                            $detail['TJourneySeatBlockDetail']['bk_eticket']  = $this->data['TJourneySeatBlock']['bk_eticket'][$i];
                            $detail['TJourneySeatBlockDetail']['created']     = $dateNow;
                            $detail['TJourneySeatBlockDetail']['created_by']  = $user['User']['id'];
                            $this->TJourneySeatBlockDetail->save($detail);
                        }
                    }
                }
                $result['id'] = $blockSeatId;
                $result['error'] = 0;
            } else {
                $result['error'] = 1;
            }
        } else {
            $result['error'] = 1;
        }
        echo json_encode($result);
        exit;
    }
    
    function removeBlockSeat($id = null){
        $this->layout = 'ajax';
        $result  = array();
        if(empty($id)){
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $blockSeat = ClassRegistry::init('TJourneySeatBlock')->read(null, $id);
        $user = $this->getCurrentUser();
        $dateNow  = date("Y-m-d H:i:s");
        mysql_query("UPDATE t_journey_seat_blocks SET is_active = 2, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
        // Change Other Journey Under Route and Default Transportation Type
        $tJourney = $this->TJourney->read(null, $this->data['TJourneySeatBlock']['t_journey_id']);
        $sqlJ = mysql_query("SELECT id FROM t_journeys WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status > 0 AND id != ".$tJourney['TJourney']['id']." AND t_route_id = ".$tJourney['TJourney']['t_route_id']." AND t_transportation_type_id = ".$tJourney['TJourney']['t_transportation_type_id']);
        while($rowT = mysql_fetch_array($sqlJ)){
            $sqlJB = mysql_query("SELECT id, sys_code FROM t_journey_seat_blocks WHERE start = '".$blockSeat['TJourneySeatBlock']['start']."' AND end = '".$blockSeat['TJourneySeatBlock']['end']."' AND type = ".$blockSeat['TJourneySeatBlock']['type']." AND t_journey_id = ".$rowT['id']);
            while($rowJB = mysql_fetch_array($sqlJB)){
                mysql_query("UPDATE t_journey_seat_blocks SET is_active = 2, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$rowJB['id']);
            }
        }
        $result['error'] = 0;
        echo json_encode($result);
        exit;
    }

    function updateNote($id = null){
        $this->layout = 'ajax';
    }

    function departureSchedule() {
        $this->layout = 'display';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Schedule', 'Display');
    }

    function getScheduleDisplay(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $date = date("Y-m-d");
        if(date("H") >= 0 && date("H") <= 3){
            $date = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
        }
        $response = array();
        $response['result'] = "";
        $i = 1;
        $sqlJourney  = mysql_query("SELECT t_journeys.id, t_journeys.company_id, t_destinations.name AS destUs, t_destinations.name_kh AS destKh, bus_types.name AS busType, t_transportation_types.number_of_seat AS numOfSeat, CONCAT(buses.code, ' (', buses.name, ')') AS busName, t_departure_times.name AS departure, IFNULL(bus_schedules.delay_time, 0) AS delayTime, bus_schedules.note, bus_schedules.status, IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)) AS date
                                    FROM t_journeys 
                                    INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_to_id
                                    INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id
                                    INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                    INNER JOIN bus_schedule_details ON bus_schedule_details.t_journey_id = t_journeys.id
                                    INNER JOIN bus_schedules ON bus_schedules.id = bus_schedule_details.bus_schedule_id AND bus_schedules.status IN (1,2)
                                    INNER JOIN buses ON buses.id = bus_schedules.bus_id
                                    INNER JOIN bus_types ON bus_types.id = buses.bus_type_id
                                    WHERE t_journeys.status IN (1,2) AND t_journeys.t_destination_from_id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1) AND bus_schedules.date = '".$date."' 
                                    AND (DATE_ADD(CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure), INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) >= DATE_ADD(now(), interval -10 MINUTE) OR CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure) >= DATE_ADD(now(), interval -10 MINUTE)) 
                                    AND DATE_ADD(CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure), INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) <= DATE_ADD(now(),interval 90 MINUTE)
                                    ORDER BY bus_schedules.status ASC, DATE_ADD(CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure), INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) ASC");
        // $sqlJourney  = mysql_query("SELECT t_journeys.id, t_journeys.company_id, t_destinations.name AS destUs, t_destinations.name_kh AS destKh, bus_types.name AS busType, t_transportation_types.number_of_seat AS numOfSeat, CONCAT(buses.code, ' (', buses.name, ')') AS busName, t_departure_times.name AS departure, bus_schedules.delay_time AS delayTime, bus_schedules.note, bus_schedules.status
        //                             FROM t_journeys 
        //                             INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_to_id
        //                             INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id
        //                             INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
        //                             INNER JOIN bus_schedule_details ON bus_schedule_details.t_journey_id = t_journeys.id
        //                             INNER JOIN bus_schedules ON bus_schedules.id = bus_schedule_details.bus_schedule_id AND bus_schedules.status IN (1,2)
        //                             INNER JOIN buses ON buses.id = bus_schedules.bus_id
        //                             INNER JOIN bus_types ON bus_types.id = buses.bus_type_id
        //                             WHERE t_journeys.status IN (1,2) AND t_journeys.t_destination_from_id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1)
        //                             ORDER BY bus_schedules.status ASC, DATE_ADD(bus_schedules.departure, INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) ASC");
        while($rowJourney = mysql_fetch_array($sqlJourney)){
            // Check Time Left with 20 minutes
            $timeLeave   = date('Y-m-d H:i:s', strtotime('+'.$rowJourney['delayTime'].' minutes',strtotime($rowJourney['date']." ".$rowJourney['departure'])));
            $timeCompare = date("Y-m-d H:i:s", strtotime('+15 minutes',strtotime($timeLeave)));
            if(strtotime($timeCompare) > strtotime(date("Y-m-d H:i:s"))){
                $rowBg = "#2f2f58";
                if($i == 2){
                    $rowBg = "#463c3c";
                    $i = 1;
                } else {
                    $i++;
                }
                if($rowJourney['company_id'] == 6){ // Buva Sea
                    $comImg = "buvaSea.png";
                } else if($rowJourney['company_id'] == 7){ // Air Bus
                    $comImg = "airBus02.png";
                } else {
                    $comImg = "vetBus.png";
                }
                $delayTime = "";
                if(!empty($rowJourney['delayTime'])){
                    $delayTime = date('H:i',strtotime('+'.$rowJourney['delayTime'].' minutes',strtotime($rowJourney['departure'])));
                }
                $departure = explode(":", $rowJourney['departure']);
                $response['result'] .= '<tr class="tr_hover busScheduleList" style="background: '.$rowBg.';">';
                $response['result'] .= '<td class="bg_color_td" style="height: 55px; text-align: center; border: none;">';
                $response['result'] .= '<img style="width: 40px;" src="'.$this->webroot.'img/'.$comImg.'" alt=""/>';
                $response['result'] .= '</td>';
                $response['result'] .= '<td class="bg_color_td" style="border: none;">';
                $response['result'] .= '<div style="padding: 0px; font-size: 18px; color: #fff; font-weight: 200; font-family: KhBattambang; line-height: 25px;">'.$rowJourney['destKh'].'</div>';
                $response['result'] .= '<span class="th_color" style="font-size: 14px; font-family: \'Times New Roman\';">'.$rowJourney['destUs'].'</span>';
                $response['result'] .= '</td>';
                $response['result'] .= '<td class="bg_color_td color__fff" style="border: none;">';
                $response['result'] .= '<span class="th_color" style="text-align: center; font-size: 14px; font-family: \'Times New Roman\';">'.$rowJourney['busType'].'</span>';
                $response['result'] .= '</td>';
                $response['result'] .= '<td class="bg_color_td color__fff" style="border: none; font-size: 16px; font-family: \'Times New Roman\';">';
                $response['result'] .= $rowJourney['busName'];
                $response['result'] .= '</td>';
                $response['result'] .= '<td class="bg_color_td color__fff" style="border: none; font-size: 16px; font-family: \'Times New Roman\'; text-align: center;">';
                $response['result'] .= $departure[0].":".$departure[1];
                $response['result'] .= '</td>';
                $response['result'] .= '<td style="text-align: center;">';
                if(!empty($rowJourney['delayTime'])){
                    $response['result'] .= '<img style="width: 40px;" src="'.$this->webroot.'img/arrow_red.png" alt=""/>';
                }
                $response['result'] .= '</td>';
                $response['result'] .= '<td class="bg_color_td color__fff" style="border: none; font-size: 16px; font-family: \'Times New Roman\'; text-align: center;">';
                if($delayTime != ''){
                    if($rowJourney['status'] == 2){
                        $response['result'] .= $delayTime;
                    } else {
                        $response['result'] .= '<span style="padding: 8px; background-color: #eaff00; border-radius: 5px 5px 5px 5px; color: #000; font-size: 16px; font-family: \'Times New Roman\';">'.$delayTime.'</span>';
                    }
                }
                $response['result'] .= '</td>';
                $response['result'] .= '<td class="bg_color_td color__fff" style="border: none; font-size: 16px; font-family: \'Times New Roman\'; text-align: center;">';
                if($rowJourney['status'] == 2){
                    $response['result'] .= '<span style="color: #de5d0a; font-size: 16px; font-family: \'Times New Roman\';">បានចាកចេញ / Left</span>';
                } else if($rowJourney['status'] == 3){
                    $response['result'] .= '<span style="color: #de5d0a; font-size: 16px; font-family: \'Times New Roman\';">បិទវ៉ុល / Pending</span>';
                } else {
                    $response['result'] .= $rowJourney['note'];
                }
                $response['result'] .= '</td>';
                $response['result'] .= '</tr>';
            }
        }
        echo json_encode($response);
        exit;
    }

    function viewTravelPackage($id = null){
        $this->layout = 'ajax';
        if(empty($id)){
            echo "Invalid ID";
            exit;
        }
        $this->set(compact('id'));
    }

    function tvSchedule() {
        $this->layout = 'display';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Schedule', 'Display');
    }

    function getTvScheduleDisplay(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $date = date("Y-m-d");
        if(date("H") >= 0 && date("H") <= 3){
            $date = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
        }
        $response = array();
        $response['result'] = "";
        $i = 1;
        $sqlJourney  = mysql_query("SELECT t_journeys.id, t_journeys.company_id, t_destinations.name AS destUs, t_destinations.name_kh AS destKh, bus_types.name AS busType, t_transportation_types.number_of_seat AS numOfSeat, CONCAT(buses.code, ' (', buses.name, ')') AS busName, t_departure_times.name AS departure, IFNULL(bus_schedules.delay_time, 0) AS delayTime, bus_schedules.note, bus_schedules.status, IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)) AS date
                                    FROM t_journeys 
                                    INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_to_id
                                    INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id
                                    INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                    INNER JOIN bus_schedule_details ON bus_schedule_details.t_journey_id = t_journeys.id
                                    INNER JOIN bus_schedules ON bus_schedules.id = bus_schedule_details.bus_schedule_id AND bus_schedules.status IN (1,2)
                                    INNER JOIN buses ON buses.id = bus_schedules.bus_id
                                    INNER JOIN bus_types ON bus_types.id = buses.bus_type_id
                                    WHERE t_journeys.status IN (1,2) AND t_journeys.t_destination_from_id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1) AND bus_schedules.date = '".$date."' 
                                    AND (DATE_ADD(CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure), INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) >= DATE_ADD(now(), interval -10 MINUTE) OR CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure) >= DATE_ADD(now(), interval -10 MINUTE)) 
                                    AND DATE_ADD(CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure), INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) <= DATE_ADD(now(),interval 90 MINUTE)
                                    ORDER BY bus_schedules.status ASC, DATE_ADD(CONCAT(IF(bus_schedules.departure > '03:00:00', bus_schedules.date, DATE_ADD(bus_schedules.date, INTERVAL 1 DAY)),' ',bus_schedules.departure), INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) ASC");
        while($rowJourney = mysql_fetch_array($sqlJourney)){
            // Check Time Left with 20 minutes
            $timeLeave   = date('Y-m-d H:i:s', strtotime('+'.$rowJourney['delayTime'].' minutes',strtotime($rowJourney['date']." ".$rowJourney['departure'])));
            $timeCompare = date("Y-m-d H:i:s", strtotime('+15 minutes',strtotime($timeLeave)));
            if(strtotime($timeCompare) > strtotime(date("Y-m-d H:i:s"))){
                $rowBg = "#2f2f58";
                if($i == 2){
                    $rowBg = "#463c3c";
                    $i = 1;
                } else {
                    $i++;
                }
                if($rowJourney['company_id'] == 6){ // Buva Sea
                    $comImg = "buvaSea.png";
                } else if($rowJourney['company_id'] == 7){ // Air Bus
                    $comImg = "airBus02.png";
                } else {
                    $comImg = "vetBus.png";
                }
                $delayTime = "";
                if(!empty($rowJourney['delayTime'])){
                    $delayTime = date('H:i',strtotime('+'.$rowJourney['delayTime'].' minutes',strtotime($rowJourney['departure'])));
                }
                $departure = explode(":", $rowJourney['departure']);
                $response['result'] .= '<tr class="tr_hover busScheduleList">';
                $response['result'] .= '<td colspan="5">';
                $response['result'] .= '<table style="width: 99%; margin-left: 5px; margin-right: 5px; margin-top: 5px; border: 0px; background: #1F252E; border-radius: 20px;" border="0">';
                $response['result'] .= '<tr>';
                $response['result'] .= '<td style="border: none; font-size: 20px; font-weight: bold; font-family: \'Times New Roman\'; text-align: center; width: 100px; padding: 0px;  height: 55px;">';
                if(!empty($delayTime)){
                    $response['result'] .= '<span style="font-size: 12px; font-family: \'Times New Roman\'; display: block; text-decoration: line-through; text-decoration-thickness: 0.1em;">'.$departure[0].":".$departure[1].'</span>';
                    $response['result'] .= '<span style="font-size: 20px; font-weight: bold; font-family: \'Times New Roman\'; display: block;">'.$delayTime.'</span>';
                } else {
                    $response['result'] .= $departure[0].":".$departure[1];
                }
                $response['result'] .= '</td>';
                $response['result'] .= '<td style="border: none; width: 250px; padding: 0px;">';
                $response['result'] .= '<div class="wordSwitch" style="padding: 0px; font-size: 22px; color: #fff; font-weight: bold; font-family: KhBattambang; line-height: 25px;" kh="'.$rowJourney['destKh'].'" en="'.$rowJourney['destUs'].'" chw="'.$rowJourney['destKh'].'">'.$rowJourney['destUs'].'</div>';
                $response['result'] .= '</td>';
                $response['result'] .= '<td style="border: none; width: 180px; font-family: \'Times New Roman\'; padding: 0px;">';
                $response['result'] .= '<span style="text-align: center; font-size: 20px; font-weight: bold; font-family: \'Times New Roman\';">'.$rowJourney['busType'].'</span>';
                $response['result'] .= '</td>';
                $response['result'] .= '<td style="border: none; font-size: 20px; font-weight: bold; font-family: \'Times New Roman\'; width: 180px; padding: 0px; color: #F15A28;">';
                $response['result'] .= $rowJourney['busName'];
                $response['result'] .= '</td>';
                $response['result'] .= '<td style="border: none; font-size: 18px; font-family: \'Times New Roman\'; text-align: left; color: #F15A28;">';
                $reasonKh = "";
                $reasonEn = "";
                if($rowJourney['status'] == 2){
                    $reasonKh = "បានចាកចេញ";
                    $reasonEn = "Left";
                } else if($rowJourney['status'] == 3){
                    $reasonKh = "បិទវ៉ុល";
                    $reasonEn = "Pending";
                } else {
                    if(!empty($rowJourney['delayTime'])){
                        $reasonKh = "ពន្យាពេល ".$rowJourney['delayTime']."នាទី";
                        $reasonEn = "Delay ".$rowJourney['delayTime']."min";
                    } else {
                        $reasonKh = $rowJourney['note'];
                        $reasonEn = $rowJourney['note'];
                    }
                }
                $response['result'] .= '<div class="wordSwitch" style="padding: 0px; font-size: 18px; color: #F15A28; font-weight: bold; font-family: KhBattambang; line-height: 25px;" kh="'.$reasonKh.'" en="'.$reasonEn.'" chw="'.$reasonKh.'">'.$reasonEn.'</div>';
                $response['result'] .= '</td>';
                $response['result'] .= '</tr>';
                $response['result'] .= '</table>';
                $response['result'] .= '</td>';
                $response['result'] .= '</tr>';
            }
        }
        echo json_encode($response);
        exit;
    }

}

?>