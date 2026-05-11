<?php
class TJourneysController extends AppController {

    var $name = 'TJourneys';
    var $components = array('Helper', 'AgencyOnline');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Dashboard');
        $companies = ClassRegistry::init('Company')->find('all',array('joins' => array( array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'fields' => array('Company.id', 'Company.name'), 'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
        $tDestinations = ClassRegistry::init('TDestination')->find('all', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $this->set(compact('companies', 'branches', 'tDestinations'));
    }

    function ajax($company = 'all', $branch = 'all', $from = 'all', $to = 'all', $status = 'all', $type = 'all', $checked = 'all', $filterMarkup = 'all', $filterPricePeriod = 'all', $routeCode = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('company', 'branch', 'from', 'to', 'status', 'type', 'checked', 'filterMarkup', 'filterPricePeriod', 'routeCode'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'View', $id);
        $this->data = $this->TJourney->read(null, $id);
        $tJourneyTransits = ClassRegistry::init('TJourneyTransit')->find('all', array("conditions" => array("TJourneyTransit.is_active = 1", "TJourneyTransit.t_journey_id" => $id), "order" => "TJourneyTransit.id"));
        $this->set(compact('tJourneyTransits'));
    }

    function add($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('description', 't_journeys', $this->data['TJourney']['description'], 'status > 0 AND company_id = '.$this->data['TJourney']['company_id'].' AND branch_id = '.$this->data['TJourney']['branch_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add New Journey (Description has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $branch = ClassRegistry::init('Branch')->read(null, $this->data['TJourney']['branch_id']);
                $duration = '00:00:00';
                if (!empty($this->data['TJourney']['t_journey_duration_hour']) && !empty($this->data['TJourney']['t_journey_duration_min'])) {
                    $duration = $this->data['TJourney']['t_journey_duration_hour'].":".$this->data['TJourney']['t_journey_duration_min'].":00";
                }
                $arrivalTime = '00:00:00';
                if (!empty($this->data['TJourney']['t_journey_arrival_hour']) && !empty($this->data['TJourney']['t_journey_arrival_min'])) {
                    $arrivalTime = $this->data['TJourney']['t_journey_arrival_hour'].":".$this->data['TJourney']['t_journey_arrival_min'].":00";
                }
                // Departure Time
                $departureId = null;
                if (!empty($this->data['TJourney']['t_journey_departure_hour']) && !empty($this->data['TJourney']['t_journey_departure_min'])) {
                    // Check Departure Time 
                    $time = $this->data['TJourney']['t_journey_departure_hour'].":".$this->data['TJourney']['t_journey_departure_min'].":00";
                    $sqlT = mysql_query("SELECT id, sys_code FROM t_departure_times WHERE name = '".$time."' LIMIT 1");
                    if(mysql_num_rows($sqlT)){
                        $rowT = mysql_fetch_array($sqlT);
                        $departureId = $rowT[0];
                        $departureSyscode = $rowT[1];
                    } else {
                        $this->loadModel('TDepartureTime');
                        $departure = array();
                        $departureSyscode = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $this->TDepartureTime->create();
                        $departure['TDepartureTime']['sys_code'] = $departureSyscode;
                        $departure['TDepartureTime']['name']     = $time;
                        $departure['TDepartureTime']['created']  = $dateNow;
                        $departure['TDepartureTime']['created_by'] = $user['User']['id'];
                        $departure['TDepartureTime']['is_active']  = 1;
                        if($this->TDepartureTime->save($departure)){
                            $departureId = $this->TDepartureTime->id;
                        } else {
                            $sqlT = mysql_query("SELECT id, sys_code FROM t_departure_times WHERE name = '".$time."' LIMIT 1");
                            if(mysql_num_rows($sqlT)){
                                $rowT = mysql_fetch_array($sqlT);
                                $departureId = $rowT[0];
                            } else {
                                $departureId = null;
                            }
                        }
                    }
                }
                // Check Single Route
                if($this->data['TJourney']['t_route_id'] == ""){
                    $this->loadModel('TRoute');
                    $route = array();
                    $this->TRoute->create();
                    $route['TRoute']['sys_code'] = $this->Helper->generateRandomString(6);
                    $route['TRoute']['offline_project_id'] = $user['User']['offline_project_id'];
                    $route['TRoute']['name'] = "Single Route";
                    $route['TRoute']['type'] = 2;
                    $route['TRoute']['created_by'] = $user['User']['id'];
                    $this->TRoute->save($route);
                    $routeId = $this->TRoute->id;
                    $this->data['TJourney']['t_route_id'] = $routeId;
                    $this->data['TJourney']['single_route_id'] = $routeId;
                }
                $this->TJourney->create();
                $this->data['TJourney']['sys_code']    = $this->Helper->generateRandomString(6);
                $this->data['TJourney']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['TJourney']['description'] = $this->data['TJourney']['description'];
                $this->data['TJourney']['t_departure_time_id']    = $departureId;
                $this->data['TJourney']['duration']    = $duration;
                $this->data['TJourney']['arrival']     = $arrivalTime;
                $this->data['TJourney']['currency_center_id'] = $branch['Branch']['currency_center_id'];
                $this->data['TJourney']['created']    = $dateNow;
                $this->data['TJourney']['created_by'] = $user['User']['id'];
                $this->data['TJourney']['status']     = 1;
                if(!empty($this->data['TJourney']['reject_bf_dep_sch_walk_in'])){
                    $this->data['TJourney']['reject_bf_dep_sch_walk_in'] = 1;
                }
                if(!empty($this->data['TJourney']['reject_bf_dep_sch_online'])){
                    $this->data['TJourney']['reject_bf_dep_sch_online'] = 1;
                }
                if(!empty($this->data['TJourney']['reject_bf_dep_sch_api'])){
                    $this->data['TJourney']['reject_bf_dep_sch_api'] = 1;
                }
                if($this->TJourney->save($this->data)) {
                    $lastInsertId = $this->TJourney->getLastInsertId();
                    // Boarding Point
                    if (isset($_POST['boarding_point']) && !empty($_POST['boarding_point'])) {
                        for ($i = 0; $i < sizeof($_POST['boarding_point']); $i++) {
                            $boardingPointTime = $_POST['boarding_point_hour'][$i].":".$_POST['boarding_point_min'][$i].":00";
                            mysql_query("INSERT INTO t_journey_boarding_points (t_journey_id,t_boarding_point_id,time) VALUES ('".$lastInsertId."','".$_POST['boarding_point'][$i]."', '".$boardingPointTime."')");
                        }
                    }
                    // Drop Off
                    if (isset($_POST['drop_off']) && !empty($_POST['drop_off'])) {
                        for ($i = 0; $i < sizeof($_POST['drop_off']); $i++) {
                            $dropTime = $_POST['drop_off_hour'][$i].":".$_POST['drop_off_min'][$i].":00";
                            mysql_query("INSERT INTO t_journey_drop_offs (t_journey_id,t_drop_off_id,time) VALUES ('".$lastInsertId."','".$_POST['drop_off'][$i]."', '".$dropTime."')");
                        }
                    }
                    // Schedule
                    $monday = 0;
                    $tuesday = 0;
                    $wednesday = 0;
                    $thursday = 0;
                    $friday = 0;
                    $saturday = 0;
                    $sunday = 0;
                    if (!empty($_POST['journey_schedule'])) {
                        for ($i = 0; $i < sizeof($_POST['journey_schedule']); $i++) {
                            if($_POST['journey_schedule'][$i] == 1){
                                $monday = 1;
                            } else if($_POST['journey_schedule'][$i] == 2){
                                $tuesday = 1;
                            } else if($_POST['journey_schedule'][$i] == 3){
                                $wednesday = 1;
                            } else if($_POST['journey_schedule'][$i] == 4){
                                $thursday = 1;
                            } else if($_POST['journey_schedule'][$i] == 5){
                                $friday = 1;
                            } else if($_POST['journey_schedule'][$i] == 6){
                                $saturday = 1;
                            } else if($_POST['journey_schedule'][$i] == 7){
                                $sunday = 1;
                            }
                        }
                    }
                    mysql_query("INSERT INTO t_journey_schedules (t_journey_id, mon, tue, wed, thu, fri, sat, sun) VALUES ('".$lastInsertId."', ".$monday.", ".$tuesday.", ".$wednesday.", ".$thursday.", ".$friday.", ".$saturday.", ".$sunday.");");
                    // Price History
                    mysql_query("INSERT INTO t_journey_price_histories (t_journey_id, unit_price, created, created_by) VALUES ('".$lastInsertId."', ".$this->data['TJourney']['unit_price'].", '".$dateNow."', ".$user['User']['id'].");");
                    // Check If Transit or Direct Multi Route
                    if($this->data['TJourney']['type'] == 2 || $this->data['TJourney']['type'] == 3){
                        // Transit Jorney
                        if (!empty($this->data['journey_transit_id'])) {
                            for ($i = 0; $i < sizeof($this->data['journey_transit_id']); $i++) {
                                if(!empty($this->data['journey_transit_id'][$i])){
                                    $sysCode = $this->Helper->generateRandomString(6);
                                    $destFrId = "NULL";
                                    $sqlJuTra = mysql_query("SELECT * FROM t_journeys WHERE id = ".$this->data['journey_transit_id'][$i]);
                                    if(mysql_num_rows($sqlJuTra)){
                                        $rowJuTra = mysql_fetch_array($sqlJuTra);
                                        $destFrId = $rowJuTra['t_destination_from_id'];
                                    }
                                    mysql_query("INSERT INTO t_journey_transits (sys_code, `t_destination_from_id`, t_journey_id, t_journey_departure_id, is_next_day) 
                                                 VALUES ('".$sysCode."', ".$destFrId.", ".$lastInsertId.", " . $this->data['journey_transit_id'][$i] . ", ".$this->data['journey_transit_date'][$i].")");
                                }
                            }
                        }
                    }
                    // Price Period
                    if(!empty($this->data['TJourney']['price_period_from']) && !empty($this->data['TJourney']['price_period_to'])){
                        $periodPrice       = $this->data['TJourney']['price_period_price'];
                        $periodPriceVip    = $this->data['TJourney']['price_period_foreigner_price'];
                        $periodAgencyPrice = $this->data['TJourney']['price_period_agency_price'];
                        $periodRoundPrice       = $this->data['TJourney']['price_period_round_price'];
                        $periodRoundPriceVip    = $this->data['TJourney']['price_period_round_price_vip'];
                        $periodRoundAgencyPrice = $this->data['TJourney']['price_period_agency_round_price'];
                        mysql_query("INSERT INTO `t_journey_price_periods` (`id`, `sys_code`, `offline_project_id`, `t_journey_id`, `main_branch_id`, `name`, `destination_from_id`, `destination_to_id`, `t_transportation_type_id`, `start`, `end`, `price`, `foreigner_price`, `membership`, `agency_price`, `agency_price_foreigner`, `round_price`, `round_membership`, `round_agency_price`, `created`, `created_by`, `modified`, `modified_by`, `apply_to`, `price_type`, `apply_type`, `status`) 
                                     VALUES (NULL, NULL, '1', ".$lastInsertId.", NULL, NULL, NULL, NULL, NULL, '".$this->data['TJourney']['price_period_from']."', '".$this->data['TJourney']['price_period_to']."', ".$periodPrice.", 0, ".$periodPriceVip.", ".$periodAgencyPrice.", 0, ".$periodRoundPrice.", ".$periodRoundPriceVip.", ".$periodRoundAgencyPrice.", NOW(), '".$user['User']['id']."', NULL, NULL, '1', '1', '1', '1');");
                    }
                    // Price Period Internal
                    if(!empty($this->data['TJourney']['price_period_from_internal']) && !empty($this->data['TJourney']['price_period_to_internal'])){
                        $periodInternalPrice      = $this->data['TJourney']['price_period_price_internal'];
                        $periodInternalRoundPrice = $this->data['TJourney']['price_period_round_price_internal'];
                        mysql_query("INSERT INTO `t_journey_price_periods` (`id`, `sys_code`, `offline_project_id`, `t_journey_id`, `main_branch_id`, `name`, `destination_from_id`, `destination_to_id`, `t_transportation_type_id`, `start`, `end`, `price`, `foreigner_price`, `membership`, `agency_price`, `agency_price_foreigner`, `round_price`, `created`, `created_by`, `modified`, `modified_by`, `apply_to`, `price_type`, `apply_type`, `status`) 
                                     VALUES (NULL, NULL, '1', ".$lastInsertId.", NULL, NULL, NULL, NULL, NULL, '".$this->data['TJourney']['price_period_from_internal']."', '".$this->data['TJourney']['price_period_to_internal']."', ".$periodInternalPrice.", 0, 0, 0, 0, ".$periodInternalRoundPrice.", NOW(), '".$user['User']['id']."', NULL, NULL, '1', '1', '2', '1');");
                    }
                    // Agency APi Webhook
                    $this->AgencyOnline->agencyAPiWebhook($lastInsertId,'add');
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if($user['User']['id'] == 963){ // User KimLay Bangkok
            $companies = ClassRegistry::init('Company')->find('list',array('fields' => array('Company.id', 'Company.name'), 'conditions' => array('Company.is_active = 1', 'Company.offline_project_id = 1', 'Company.id IN (1,2)')));
        } else {
            $companies = ClassRegistry::init('Company')->find('list',array(
                        'joins' => array( array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),'fields' => array('Company.id', 'Company.name'), 'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
        }
        if($user['User']['id'] == 963){ // User KimLay Bangkok
            $branches  = ClassRegistry::init('Branch')->find('all',array('fields' => array('Branch.id', 'Branch.name', 'Branch.currency_center_id', 'CurrencyCenter.symbol', 'Branch.company_id'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id = 1', 'Branch.id IN (19, 28)')));
        } else {
            $branches  = ClassRegistry::init('Branch')->find('all',array(
                        'joins' => array( array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                        ),'fields' => array('Branch.id', 'Branch.name', 'Branch.currency_center_id', 'CurrencyCenter.symbol', 'Branch.company_id'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
        }
        $tJourneyTypes = ClassRegistry::init('TJourneyType')->find('list', array("conditions" => array("TJourneyType.is_active = 1")));
        $tDestinationFroms = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $tDestinationTos   = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array("conditions" => array("TTransportationType.is_active = 1", 'TTransportationType.offline_project_id' => $user['User']['offline_project_id'])));
        $tRoutes   = ClassRegistry::init('TRoute')->find('all', array("conditions" => array("TRoute.is_active = 1", "TRoute.type = 1", 'TRoute.offline_project_id' => $user['User']['offline_project_id'])));
        $tDropOffs = ClassRegistry::init('TDropOff')->find('all', array("conditions" => array("TDropOff.is_active = 1", 'TDropOff.offline_project_id' => $user['User']['offline_project_id'])));
        $tBoardingPoints = ClassRegistry::init('TBoardingPoint')->find('all', array("conditions" => array("TBoardingPoint.is_active = 1", 'TBoardingPoint.offline_project_id' => $user['User']['offline_project_id'])));
        $answers = array(0 => ACTION_NO, 1 => ACTION_YES);
        if(!empty($id)){
            $this->data = $this->TJourney->read(null, $id);
        }

        $this->set(compact('companies', 'branches', 'tJourneyTypes', 'tDestinationFroms', 'tDestinationTos', 'tTransportationTypes', 'tRoutes', 'answers', 'tDropOffs', 'tBoardingPoints'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('description', 't_journeys', $id, $this->data['TJourney']['description'], 'status > 0 AND company_id = '.$this->data['TJourney']['company_id'].' AND branch_id = '.$this->data['TJourney']['branch_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Edit Journey(Description has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $branch   = ClassRegistry::init('Branch')->read(null, $this->data['TJourney']['branch_id']);
                $TJourney = $this->TJourney->read(null, $id);
                $duration = '00:00:00';
                $arrivalTime = '00:00:00';
                $departureId = null;
                if (!empty($this->data['TJourney']['t_journey_duration_hour']) && !empty($this->data['TJourney']['t_journey_duration_min'])) {
                    $duration = $this->data['TJourney']['t_journey_duration_hour'].":".$this->data['TJourney']['t_journey_duration_min'].":00";
                }
                if (!empty($this->data['TJourney']['t_journey_arrival_hour']) && !empty($this->data['TJourney']['t_journey_arrival_min'])) {
                    $arrivalTime = $this->data['TJourney']['t_journey_arrival_hour'].":".$this->data['TJourney']['t_journey_arrival_min'].":00";
                }
                if($this->data['TJourney']['block_start'] != '' || $this->data['TJourney']['block_end'] != ''){
                    $this->data['TJourney']['block_created'] = $dateNow;
                    $this->data['TJourney']['block_by'] = $user['User']['id'];
                    if($this->data['TJourney']['block_start'] != '' && $this->data['TJourney']['block_end'] == ''){
                        $this->data['TJourney']['block_end'] = $this->data['TJourney']['block_start'];
                    }
                    if($this->data['TJourney']['block_end'] != '' && $this->data['TJourney']['block_start'] == ''){
                        $this->data['TJourney']['block_start'] = $this->data['TJourney']['block_end'];
                    }
                } else {
                    $this->data['TJourney']['block_start'] = null;
                    $this->data['TJourney']['block_end']   = null;
                }
                if($this->data['TJourney']['active_start'] != '' || $this->data['TJourney']['active_end'] != ''){
                    if($this->data['TJourney']['active_start'] != '' && $this->data['TJourney']['active_end'] == ''){
                        $this->data['TJourney']['active_end'] = $this->data['TJourney']['active_start'];
                    }
                    if($this->data['TJourney']['active_end'] != '' && $this->data['TJourney']['active_start'] == ''){
                        $this->data['TJourney']['active_start'] = $this->data['TJourney']['active_end'];
                    }
                } else {
                    $this->data['TJourney']['active_start'] = null;
                    $this->data['TJourney']['active_end'] = null;
                }
                // Departure Time
                if (!empty($this->data['TJourney']['t_journey_departure_hour']) && !empty($this->data['TJourney']['t_journey_departure_min'])) {
                    // Check Departure Time 
                    $time = $this->data['TJourney']['t_journey_departure_hour'].":".$this->data['TJourney']['t_journey_departure_min'].":00";
                    $sqlT = mysql_query("SELECT id, sys_code FROM t_departure_times WHERE name = '".$time."' LIMIT 1");
                    if(mysql_num_rows($sqlT)){
                        $rowT = mysql_fetch_array($sqlT);
                        $departureId = $rowT[0];
                        $departureSyscode = $rowT[1];
                    } else {
                        $this->loadModel('TDepartureTime');
                        $departure = array();
                        $departureSyscode = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $this->TDepartureTime->create();
                        $departure['TDepartureTime']['sys_code'] = $departureSyscode;
                        $departure['TDepartureTime']['name']     = $time;
                        $departure['TDepartureTime']['created']  = $dateNow;
                        $departure['TDepartureTime']['created_by'] = $user['User']['id'];
                        $departure['TDepartureTime']['is_active']  = 1;
                        if($this->TDepartureTime->save($departure)){
                            $departureId = $this->TDepartureTime->id;
                        } else {
                            $sqlT = mysql_query("SELECT id, sys_code FROM t_departure_times WHERE name = '".$time."' LIMIT 1");
                            if(mysql_num_rows($sqlT)){
                                $rowT = mysql_fetch_array($sqlT);
                                $departureId = $rowT[0];
                            } else {
                                $departureId = null;
                            }
                        }
                    }
                }
                if($this->data['TJourney']['t_route_id'] == ""){
                    if($TJourney['TJourney']['single_route_id'] == ""){
                        $this->loadModel('TRoute');
                        $route = array();
                        $this->TRoute->create();
                        $route['TRoute']['sys_code'] = $this->Helper->generateRandomString(6);
                        $route['TRoute']['offline_project_id'] = $user['User']['offline_project_id'];
                        $route['TRoute']['name'] = "Single Route";
                        $route['TRoute']['type'] = 2;
                        $route['TRoute']['created_by'] = $user['User']['id'];
                        $this->TRoute->save($route);
                        $routeId = $this->TRoute->id;
                        $this->data['TJourney']['t_route_id'] = $routeId;
                        $this->data['TJourney']['single_route_id'] = $routeId;
                    } else {
                        $this->data['TJourney']['t_route_id'] = $TJourney['TJourney']['single_route_id'];
                    }
                }
                if(!empty($this->data['TJourney']['reject_bf_dep_sch_walk_in'])){
                    $this->data['TJourney']['reject_bf_dep_sch_walk_in'] = 1;
                }
                if(!empty($this->data['TJourney']['reject_bf_dep_sch_online'])){
                    $this->data['TJourney']['reject_bf_dep_sch_online'] = 1;
                }
                if(!empty($this->data['TJourney']['reject_bf_dep_sch_api'])){
                    $this->data['TJourney']['reject_bf_dep_sch_api'] = 1;
                }
                $this->data['TJourney']['t_departure_time_id'] = $departureId;
                $this->data['TJourney']['duration'] = $duration;
                $this->data['TJourney']['arrival']  = $arrivalTime;
                $this->data['TJourney']['currency_center_id'] = $branch['Branch']['currency_center_id'];
                $this->data['TJourney']['modified'] = $dateNow;
                $this->data['TJourney']['modified_by'] = $user['User']['id'];
                // Update History Info
                // mysql_query("INSERT INTO t_journey_info_edits SELECT * FROM t_journeys WHERE id = ".$id);
                if($this->TJourney->save($this->data)){ 
                    // Update History Info
                    mysql_query("INSERT INTO t_journey_info_edit_boarding_points (t_journey_id, t_boarding_point_id, `time`, created, created_by)
                                 SELECT t_journey_id, t_boarding_point_id, `time`, now(), ".$user['User']['id']." FROM t_journey_boarding_points WHERE t_journey_id = ".$id);
                    // mysql_query("INSERT INTO t_journey_info_edit_drop_offs SELECT * FROM t_journey_drop_offs WHERE t_journey_id = ".$id);
                    // mysql_query("INSERT INTO t_journey_info_edit_schedules SELECT * FROM t_journey_schedules WHERE t_journey_id = ".$id);
                    // mysql_query("INSERT INTO t_journey_info_edit_agent_prices SELECT * FROM t_journey_agent_prices WHERE t_journey_id = ".$id);
                    // mysql_query("INSERT INTO t_journey_info_edit_transits SELECT * FROM t_journey_transits WHERE t_journey_id = ".$id);
                    // Delete
                    mysql_query("DELETE FROM t_journey_boarding_points WHERE t_journey_id = ".$id);
                    mysql_query("DELETE FROM t_journey_drop_offs WHERE t_journey_id = ".$id);
                    mysql_query("DELETE FROM t_journey_schedules WHERE t_journey_id = ".$id);
                    mysql_query("DELETE FROM t_journey_agent_prices WHERE t_journey_id = ".$id);
                    mysql_query("DELETE FROM t_journey_transits WHERE t_journey_id = ".$id);
                    // Boarding Point
                    if (isset($_POST['boarding_point']) && !empty($_POST['boarding_point'])) {
                        for ($i = 0; $i < sizeof($_POST['boarding_point']); $i++) {
                            $boardingPointTime = $_POST['boarding_point_hour'][$i].":".$_POST['boarding_point_min'][$i].":00";
                            mysql_query("INSERT INTO t_journey_boarding_points (t_journey_id,t_boarding_point_id,time) VALUES ('" . $id . "','" . $_POST['boarding_point'][$i] . "', '".$boardingPointTime."')");
                        }
                    }
                    // Drop Off
                    if (isset($_POST['drop_off']) && !empty($_POST['drop_off'])) {
                        for ($i = 0; $i < sizeof($_POST['drop_off']); $i++) {
                            $dropTime = $_POST['drop_off_hour'][$i].":".$_POST['drop_off_min'][$i].":00";
                            mysql_query("INSERT INTO t_journey_drop_offs (t_journey_id,t_drop_off_id,time) VALUES ('" . $id . "','" . $_POST['drop_off'][$i] . "', '".$dropTime."')");
                        }
                    }
                    // Schedule
                    $monday = 0;
                    $tuesday = 0;
                    $wednesday = 0;
                    $thursday = 0;
                    $friday = 0;
                    $saturday = 0;
                    $sunday = 0;
                    if (!empty($_POST['journey_schedule'])) {
                        for ($i = 0; $i < sizeof($_POST['journey_schedule']); $i++) {
                            if($_POST['journey_schedule'][$i] == 1){
                                $monday = 1;
                            } else if($_POST['journey_schedule'][$i] == 2){
                                $tuesday = 1;
                            } else if($_POST['journey_schedule'][$i] == 3){
                                $wednesday = 1;
                            } else if($_POST['journey_schedule'][$i] == 4){
                                $thursday = 1;
                            } else if($_POST['journey_schedule'][$i] == 5){
                                $friday = 1;
                            } else if($_POST['journey_schedule'][$i] == 6){
                                $saturday = 1;
                            } else if($_POST['journey_schedule'][$i] == 7){
                                $sunday = 1;
                            }
                        }
                    }
                    mysql_query("INSERT INTO t_journey_schedules (t_journey_id, mon, tue, wed, thu, fri, sat, sun) VALUES ('".$id."', ".$monday.", ".$tuesday.", ".$wednesday.", ".$thursday.", ".$friday.", ".$saturday.", ".$sunday.");");
                    // Price History
                    mysql_query("INSERT INTO t_journey_price_histories (t_journey_id, unit_price, created, created_by) VALUES ('".$id."', ".$this->data['TJourney']['unit_price'].", '".date("Y-m-d H:i:s")."', ".$user['User']['id'].");");
                    // Agent Price
                    // if (isset($_POST['agent_id'])) {
                    //     for ($i = 0; $i < sizeof($_POST['agent_id']); $i++) {
                    //         mysql_query("INSERT INTO t_journey_agent_prices (t_journey_id,t_agent_id,amount,percent,created) VALUES ('".$id."','".$_POST['agent_id'][$i]."','".$_POST['agent_amount'][$i]."','".$_POST['agent_percent'][$i]."','".date("Y-m-d H:i:s")."')");
                    //     }
                    // }
                    // Check If Transit or Direct Multi Route
                    if($this->data['TJourney']['type'] == 2 || $this->data['TJourney']['type'] == 3){
                        // Transit Journey
                        if (!empty($this->data['journey_transit_id'])) {
                            for ($i = 0; $i < sizeof($this->data['journey_transit_id']); $i++) {
                                if(!empty($this->data['journey_transit_id'][$i])){
                                    $sysCode  = $this->Helper->generateRandomString(6);
                                    $destFrId = "NULL";
                                    $sqlJuTra = mysql_query("SELECT * FROM t_journeys WHERE id = ".$this->data['journey_transit_id'][$i]);
                                    if(mysql_num_rows($sqlJuTra)){
                                        $rowJuTra = mysql_fetch_array($sqlJuTra);
                                        $destFrId = $rowJuTra['t_destination_from_id'];
                                    }
                                    mysql_query("INSERT INTO t_journey_transits (sys_code, `t_destination_from_id`, t_journey_id, t_journey_departure_id, is_next_day) VALUES ('".$sysCode."', ".$destFrId.", ".$id.", " . $this->data['journey_transit_id'][$i] . ", ".$this->data['journey_transit_date'][$i].")");
                                }
                            }
                        }
                    }
                    // Price Period
                    mysql_query("UPDATE t_journey_price_periods SET status = 0 WHERE t_journey_id = ".$id." AND apply_type = 1");
                    if(!empty($this->data['TJourney']['price_period_from']) && !empty($this->data['TJourney']['price_period_to'])){
                        $periodPrice            = $this->data['TJourney']['price_period_price'];
                        $periodForPrice         = 0;
                        $periodMembership       = $this->data['TJourney']['price_period_membership'];
                        $periodAgencyPrice      = $this->data['TJourney']['price_period_agency_price'];
                        $periodAgencyPriceFor   = 0;
                        $periodRoundPrice       = $this->data['TJourney']['price_period_round_price'];
                        $periodRoundVipPrice    = $this->data['TJourney']['price_period_round_price_vip'];
                        $periodRoundAgencyPrice = $this->data['TJourney']['price_period_agency_round_price'];
                        mysql_query("INSERT INTO `t_journey_price_periods` (`id`, `sys_code`, `offline_project_id`, `t_journey_id`, `main_branch_id`, `name`, `destination_from_id`, `destination_to_id`, `t_transportation_type_id`, `start`, `end`, `price`, `foreigner_price`, `membership`, `agency_price`, `agency_price_foreigner`, `round_price`, `round_membership`, `round_agency_price`, `created`, `created_by`, `modified`, `modified_by`, `apply_to`, `price_type`, `status`) 
                                     VALUES (NULL, NULL, '1', ".$id.", NULL, NULL, NULL, NULL, NULL, '".$this->data['TJourney']['price_period_from']."', '".$this->data['TJourney']['price_period_to']."', '".$periodPrice."', '".$periodForPrice."', '".$periodMembership."', '".$periodAgencyPrice."', '".$periodAgencyPriceFor."', '".$periodRoundPrice."', '".$periodRoundVipPrice."', '".$periodRoundAgencyPrice."', NOW(), '".$user['User']['id']."', NULL, NULL, '1', '1', '1');");
                    }
                    // Price Period Internal
                    mysql_query("UPDATE t_journey_price_periods SET status = 0 WHERE t_journey_id = ".$id." AND apply_type = 2");
                    if(!empty($this->data['TJourney']['price_period_from_internal']) && !empty($this->data['TJourney']['price_period_to_internal'])){
                        $periodInternalPrice = $this->data['TJourney']['price_period_price_internal'];
                        $periodInternalRoundPrice = $this->data['TJourney']['price_period_round_price_internal'];
                        mysql_query("INSERT INTO `t_journey_price_periods` (`id`, `sys_code`, `offline_project_id`, `t_journey_id`, `main_branch_id`, `name`, `destination_from_id`, `destination_to_id`, `t_transportation_type_id`, `start`, `end`, `price`, `foreigner_price`, `membership`, `agency_price`, `agency_price_foreigner`, `round_price`, `created`, `created_by`, `modified`, `modified_by`, `apply_to`, `price_type`, `apply_type`, `status`) 
                                     VALUES (NULL, NULL, '1', ".$id.", NULL, NULL, NULL, NULL, NULL, '".$this->data['TJourney']['price_period_from_internal']."', '".$this->data['TJourney']['price_period_to_internal']."', ".$periodInternalPrice.", 0, 0, 0, 0, ".$periodInternalRoundPrice.", NOW(), '".$user['User']['id']."', NULL, NULL, '1', '1', '2', '1');");
                    }
                    // Edited History
                    mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                                 VALUES (NULL, '1', ".$id.", 'Edit', now(), ".$user['User']['id'].");");
                    // Agency APi Webhook
                    $this->AgencyOnline->agencyAPiWebhook($id,'update');
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit();                                
                }else{
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            } 
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Edit Journey', $id);
            $this->data = $this->TJourney->read(null, $id);
            if($user['User']['id'] == 963){ // User KimLay Bangkok
                $companies = ClassRegistry::init('Company')->find('list',array('fields' => array('Company.id', 'Company.name'), 'conditions' => array('Company.is_active = 1', 'Company.offline_project_id = 1', 'Company.id IN (1,2)')));
            } else {
                $companies = ClassRegistry::init('Company')->find('list',array(
                            'joins' => array( array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                            ),'fields' => array('Company.id', 'Company.name'), 'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
            }
            if($user['User']['id'] == 963){ // User KimLay Bangkok
                $branches  = ClassRegistry::init('Branch')->find('all',array('fields' => array('Branch.id', 'Branch.name', 'Branch.currency_center_id', 'CurrencyCenter.symbol', 'Branch.company_id'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id = 1', 'Branch.id IN (19, 28)')));
            } else {
                $branches  = ClassRegistry::init('Branch')->find('all',array(
                            'joins' => array( array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),'fields' => array('Branch.id', 'Branch.name', 'Branch.currency_center_id', 'CurrencyCenter.symbol', 'Branch.company_id'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
            }
            $tJourneyTypes = ClassRegistry::init('TJourneyType')->find('list', array("conditions" => array("TJourneyType.is_active = 1")));
            $tDestinationFroms = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
            $tDestinationTos   = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
            $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array("conditions" => array("TTransportationType.is_active = 1", 'TTransportationType.offline_project_id' => $user['User']['offline_project_id'])));
            $tRoutes   = ClassRegistry::init('TRoute')->find('all', array("conditions" => array("TRoute.is_active = 1", "TRoute.type = 1", 'TRoute.offline_project_id' => $user['User']['offline_project_id'])));
            $tDropOffs = ClassRegistry::init('TDropOff')->find('all', array("conditions" => array("TDropOff.is_active = 1", 'TDropOff.offline_project_id' => $user['User']['offline_project_id'])));
            $tBoardingPoints = ClassRegistry::init('TBoardingPoint')->find('all', array("conditions" => array("TBoardingPoint.is_active = 1", 'TBoardingPoint.offline_project_id' => $user['User']['offline_project_id'])));
            $tJourneyAgentPrices = ClassRegistry::init('TJourneyAgentPrice')->find('all', array("conditions" => array("TJourneyAgentPrice.is_active = 1", "TJourneyAgentPrice.t_journey_id" => $id)));
            $tJourneyTransits    = ClassRegistry::init('TJourneyTransit')->find('all', array("conditions" => array("TJourneyTransit.is_active = 1", "TJourneyTransit.t_journey_id" => $id), "order" => "TJourneyTransit.id"));
            $answers = array(0 => ACTION_NO, 1 => ACTION_YES);
            $this->set(compact('companies', 'branches', 'tJourneyTypes', 'tDestinationFroms', 'tDestinationTos', 'tTransportationTypes', 'tRoutes', 'answers', 'tDropOffs', 'tBoardingPoints', 'tJourneyAgentPrices', 'tJourneyTransits'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $tJourney = $this->TJourney->read(null, $id);
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_journeys` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Edited History
        mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                     VALUES (NULL, '1', ".$id.", 'Delete', now(), ".$user['User']['id'].");");
        // Agency APi Webhook
        $this->AgencyOnline->agencyAPiWebhook($id,'delete');
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;    
    }
    
    function updateStatus($id = null, $status = null){
        if (!$id && !$status) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $result   = array();
        $tJourney = $this->TJourney->read(null, $id);
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_journeys` SET `status`= ".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Edited History
        if($status == 1){
            $editAct = "Update Active";
        } else {
            $editAct = "Update Inactive";
        }
        mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                     VALUES (NULL, '1', ".$id.", '".$editAct."', now(), ".$user['User']['id'].");");
        // Agency APi Webhook
        $this->AgencyOnline->agencyAPiWebhook($id,'update');
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Change Journey Status', $id);
        $result['error'] = 0;
        echo json_encode($result);
        exit;
    }
    
    function searchAgent() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $agents = ClassRegistry::init('TAgent')->find('all', array(
                    'conditions' => array('OR' => array(
                            'TAgent.code LIKE' => '%' . $this->params['url']['q'] . '%',
                            'TAgent.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'TAgent.telephone LIKE' => '%' . $this->params['url']['q'] . '%',
                        ), 'TAgent.status' => 1, 'TAgent.offline_project_id' => $user['User']['offline_project_id']
                    ),
                    'limit' => $this->params['url']['limit']
                ));
        foreach($agents AS $agent){
            echo "{$agent['TAgent']['id']}.*{$agent['TAgent']['code']}-{$agent['TAgent']['name']} ({$agent['TAgent']['telephone']})\n";
        }
        exit;
    }
    
    function addVehicle($id){
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel('TJourneyVehicle');
            $journey = $this->TJourney->read(null, $id);
            // Vehicle Detail
            mysql_query("DELETE FROM t_journey_vehicles WHERE t_journey_id = ".$id);
            for($i=0; $i < sizeof($this->data['departure']); $i++){
                $this->TJourneyVehicle->create();
                $vehicles = array();
                $vehicles['TJourneyVehicle']['t_journey_id'] = $id;
                $vehicles['TJourneyVehicle']['t_boat_id']    = $this->data['vehicle'][$i];
                $vehicles['TJourneyVehicle']['t_departure_time_id'] = $this->data['departure'][$i];
                $vehicles['TJourneyVehicle']['label']      = $this->data['label'][$i];
                $vehicles['TJourneyVehicle']['created']    = $dateNow;
                $vehicles['TJourneyVehicle']['created_by'] = $user['User']['id'];
                $this->TJourneyVehicle->save($vehicles);
            }
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add More Vehicle');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Add More Vehicle', $id);
        $tBoats = ClassRegistry::init('TBoat')->find('all', array("conditions" => array("TBoat.is_active = 1"), "fields" => array("id", "code"), "order" => "TBoat.name"));
        $tDepartureTimes = ClassRegistry::init('TDepartureTime')->find('all', array("conditions" => array("TDepartureTime.is_active = 1", "TDepartureTime.id IN (SELECT t_departure_time_id FROM t_journey_departures WHERE t_journey_id = ".$id.")")));
        $tJourneyVehicles = ClassRegistry::init('TJourneyVehicle')->find('all', array("conditions" => array("TJourneyVehicle.t_journey_id" => $id)));
        $this->set(compact('tBoats', 'tDepartureTimes', 'tJourneyVehicles'));
    }
    
    function fareEvent($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'View Fare Event', $id);
        $this->data = $this->TJourney->read(null, $id);
    }
    
    function saveFareEvent(){
        $this->layout = 'ajax';
        if(empty($this->data)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->loadModel('TJourneyFareEvent');
        $event = array();
        if($this->data['TJourneyFareEvent']['id'] != ''){
            $event['TJourneyFareEvent']['id'] = $this->data['TJourneyFareEvent']['id'];
        } else {
            $this->TJourneyFareEvent->create();
        }
        $event['TJourneyFareEvent']['t_journey_id'] = $this->data['TJourneyFareEvent']['t_journey_id'];
        $event['TJourneyFareEvent']['name']  = $this->data['TJourneyFareEvent']['name'];
        $event['TJourneyFareEvent']['start'] = $this->data['TJourneyFareEvent']['start'];
        $event['TJourneyFareEvent']['end']   = $this->data['TJourneyFareEvent']['end'];
        $event['TJourneyFareEvent']['price'] = $this->data['TJourneyFareEvent']['price'];
        $event['TJourneyFareEvent']['foreigner_price'] = $this->data['TJourneyFareEvent']['foreigner_price'];
        $event['TJourneyFareEvent']['membership'] = $this->data['TJourneyFareEvent']['membership'];
        $event['TJourneyFareEvent']['created']    = $dateNow;
        $event['TJourneyFareEvent']['created_by'] = $user['User']['id'];
        $event['TJourneyFareEvent']['is_active']  = 1;
        if($this->TJourneyFareEvent->save($event)){
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add Fare Event');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        } else {
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function deleteFareEvent($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE t_journey_fare_events SET is_active = 2, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function viewFareEvent($id){
        $this->layout = 'ajax';
        $result = array();
        if (!$id) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        include('includes/function.php');
        $fareEvent = ClassRegistry::init('TJourneyFareEvent')->find('first', array("conditions" => array("TJourneyFareEvent.id" => $id)));
        $result['error'] = 0;
        $result['id']    = $fareEvent['TJourneyFareEvent']['id'];
        $result['name']  = $fareEvent['TJourneyFareEvent']['name'];
        $result['start'] = dateShort($fareEvent['TJourneyFareEvent']['start']);
        $result['end'] = dateShort($fareEvent['TJourneyFareEvent']['end']);
        $result['price'] = number_format($fareEvent['TJourneyFareEvent']['price'], 2);
        $result['foreigner_price'] = number_format($fareEvent['TJourneyFareEvent']['foreigner_price'], 2);
        $result['membership'] = number_format($fareEvent['TJourneyFareEvent']['membership'], 2);
        echo json_encode($result);
        exit;
    }
    
    function fareEventAjax($journey_id) {
        $this->layout = 'ajax';
        $this->set(compact('journey_id'));
    }
    
    function changeTransportation($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'View Change Transportation', $id);
        $this->data = $this->TJourney->read(null, $id);
    }
    
    function saveChangeTransportation(){
        $this->layout = 'ajax';
        if(empty($this->data)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->loadModel('TJourneyChangeTransportation');
        $event = array();
        $this->TJourneyChangeTransportation->create();
        $event['TJourneyChangeTransportation']['offline_project_id'] = $user['User']['offline_project_id'];
        $event['TJourneyChangeTransportation']['t_journey_id'] = $this->data['TJourneyChangeTransportation']['t_journey_id'];
        $event['TJourneyChangeTransportation']['t_transportation_type_id']  = $this->data['TJourneyChangeTransportation']['t_transportation_type_id'];
        $event['TJourneyChangeTransportation']['start']      = $this->data['TJourneyChangeTransportation']['start'];
        $event['TJourneyChangeTransportation']['end']        = $this->data['TJourneyChangeTransportation']['end'];
        $event['TJourneyChangeTransportation']['created']    = $dateNow;
        $event['TJourneyChangeTransportation']['created_by'] = $user['User']['id'];
        $event['TJourneyChangeTransportation']['is_active']  = 1;
        if($this->TJourneyChangeTransportation->save($event)){
            $lastInsertId = $this->TJourneyChangeTransportation->id;
            // Change Ticket Book Transportation
            mysql_query("UPDATE t_seat_controls SET t_transportation_type_id = ".$this->data['TJourneyChangeTransportation']['t_transportation_type_id']." WHERE t_journey_id = ".$this->data['TJourneyChangeTransportation']['t_journey_id']." AND journey_date >= '".$this->data['TJourneyChangeTransportation']['start']."' AND journey_date <= '".$this->data['TJourneyChangeTransportation']['end']."' AND status > 0");
            mysql_query("UPDATE t_tickets SET t_transportation_type_id = ".$this->data['TJourneyChangeTransportation']['t_transportation_type_id']." WHERE t_journey_id = ".$this->data['TJourneyChangeTransportation']['t_journey_id']." AND journey_date >= '".$this->data['TJourneyChangeTransportation']['start']."' AND journey_date <= '".$this->data['TJourneyChangeTransportation']['end']."' AND status > 0");
            // Change Other Journey Under Route and Default Transportation Type
            $tJourney = $this->TJourney->read(null, $this->data['TJourneyChangeTransportation']['t_journey_id']);
            $sqlJ = mysql_query("SELECT id FROM t_journeys WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status > 0 AND id != ".$tJourney['TJourney']['id']." AND t_route_id = ".$tJourney['TJourney']['t_route_id']." AND t_transportation_type_id = ".$tJourney['TJourney']['t_transportation_type_id']);
            while($rowT = mysql_fetch_array($sqlJ)){
                $this->TJourneyChangeTransportation->create();
                $changeTransportation = array();
                $changeTransportation['TJourneyChangeTransportation']['offline_project_id'] = $user['User']['offline_project_id'];
                $changeTransportation['TJourneyChangeTransportation']['t_journey_id'] = $rowT['id'];
                $changeTransportation['TJourneyChangeTransportation']['t_transportation_type_id']  = $this->data['TJourneyChangeTransportation']['t_transportation_type_id'];
                $changeTransportation['TJourneyChangeTransportation']['start']      = $this->data['TJourneyChangeTransportation']['start'];
                $changeTransportation['TJourneyChangeTransportation']['end']        = $this->data['TJourneyChangeTransportation']['end'];
                $changeTransportation['TJourneyChangeTransportation']['created']    = $dateNow;
                $changeTransportation['TJourneyChangeTransportation']['created_by'] = $user['User']['id'];
                $changeTransportation['TJourneyChangeTransportation']['is_active']  = 1;
                if($this->TJourneyChangeTransportation->save($changeTransportation)){
                    // Change Ticket Book Transportation
                    mysql_query("UPDATE t_seat_controls SET t_transportation_type_id = ".$this->data['TJourneyChangeTransportation']['t_transportation_type_id']." WHERE t_journey_id = ".$rowT['id']." AND journey_date >= '".$this->data['TJourneyChangeTransportation']['start']."' AND journey_date <= '".$this->data['TJourneyChangeTransportation']['end']."' AND status > 0");
                    mysql_query("UPDATE t_tickets SET t_transportation_type_id = ".$this->data['TJourneyChangeTransportation']['t_transportation_type_id']." WHERE t_journey_id = ".$rowT['id']." AND journey_date >= '".$this->data['TJourneyChangeTransportation']['start']."' AND journey_date <= '".$this->data['TJourneyChangeTransportation']['end']."' AND status > 0");
                }
            }
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add Change Transportation');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        } else {
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function deleteChangeTransportation($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE t_journey_change_transportations SET status = 0, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function changeTransportationAjax($journey_id) {
        $this->layout = 'ajax';
        $this->set(compact('journey_id'));
    }
    
    function cloneJourney($routeId){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$routeId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(!empty($this->data)){
            $router = ClassRegistry::init('TRoute')->read(null, $routeId);
            $this->loadModel('TRoute');
            $this->TRoute->create();
            $route = array();
            $route['TRoute']['sys_code'] = $this->Helper->generateRandomString(6);
            $route['TRoute']['offline_project_id'] = $user['User']['offline_project_id'];
            $route['TRoute']['name']       = $router['TRoute']['name']." (".$this->Helper->generateRandomString(5).")";
            $route['TRoute']['created_by'] = $user['User']['id'];
            $route['TRoute']['is_active']  = 1;
            if ($this->TRoute->save($route)) {
                $newRouteId = $this->TRoute->id;
                $sqlJourney = mysql_query("SELECT * FROM t_journeys WHERE t_route_id = ".$router['TRoute']['id']." AND status > 0");
                while($rowJourney = mysql_fetch_array($sqlJourney)){
                    $this->loadModel('TJourney');
                    $this->TJourney->create();
                    $TJourney = array();
                    $TJourney['TJourney']['sys_code']            = $this->Helper->generateRandomString(6);
                    $TJourney['TJourney']['offline_project_id']  = $user['User']['offline_project_id'];
                    $TJourney['TJourney']['company_id']          = $rowJourney['company_id'];
                    $TJourney['TJourney']['branch_id']           = $rowJourney['branch_id'];
                    $TJourney['TJourney']['description']         = $rowJourney['description']." (C)";
                    $TJourney['TJourney']['description_kh']      = $rowJourney['description_kh']." (C)";
                    $TJourney['TJourney']['t_destination_from_id'] = $rowJourney['t_destination_from_id'];
                    $TJourney['TJourney']['t_destination_to_id']   = $rowJourney['t_destination_to_id'];
                    $TJourney['TJourney']['t_transportation_type_id']   = $rowJourney['t_transportation_type_id'];
                    $TJourney['TJourney']['t_departure_time_id']        = $rowJourney['t_departure_time_id'];
                    $TJourney['TJourney']['t_route_id']                 = $newRouteId;
                    if(!empty($rowJourney['single_route_id'])){
                        $TJourney['TJourney']['single_route_id']        = $newRouteId;
                    }
                    $TJourney['TJourney']['duration']                   = $rowJourney['duration'];
                    $TJourney['TJourney']['arrival']                    = $rowJourney['arrival'];
                    $TJourney['TJourney']['unit_price']                 = $rowJourney['unit_price'];
                    $TJourney['TJourney']['foreigner_price']            = $rowJourney['foreigner_price'];
                    $TJourney['TJourney']['membership']                 = $rowJourney['membership'];
                    // New Round Trip Price fields
                    if (isset($rowJourney['round_price'])) {
                        $TJourney['TJourney']['round_price']            = $rowJourney['round_price'];
                    }
                    if (isset($rowJourney['agent_round_price'])) {
                        $TJourney['TJourney']['agent_round_price']      = $rowJourney['agent_round_price'];
                    }
                    $TJourney['TJourney']['agent_price_amount']         = $rowJourney['agent_price_amount'];
                    $TJourney['TJourney']['agetn_price_percent']        = $rowJourney['agetn_price_percent'];
                    $TJourney['TJourney']['currency_center_id']         = $rowJourney['currency_center_id'];
                    $TJourney['TJourney']['advance_booking']            = $rowJourney['advance_booking'];
                    $TJourney['TJourney']['allow_cancellation']         = $rowJourney['allow_cancellation'];
                    $TJourney['TJourney']['reject_before_departure']    = $rowJourney['reject_before_departure'];
                    $TJourney['TJourney']['gender_require']             = $rowJourney['gender_require'];
                    $TJourney['TJourney']['vehicle_no']                 = $this->data['vehicle_no'];
                    $TJourney['TJourney']['allow_access']               = $rowJourney['allow_access'];
                    $TJourney['TJourney']['type']                       = $rowJourney['type'];
                    $TJourney['TJourney']['active_start']               = $this->data['start'];
                    $TJourney['TJourney']['active_end']                 = $this->data['end'];
                    $TJourney['TJourney']['created']                    = date("Y-m-d H:i:s");
                    $TJourney['TJourney']['created_by']                 = $user['User']['id'];
                    $TJourney['TJourney']['status']                     = 1;
                    if($this->TJourney->save($TJourney)) {
                        $lastInsertId = $this->TJourney->getLastInsertId();
                        // Boarding Point
                        mysql_query("INSERT INTO t_journey_boarding_points (t_journey_id,t_boarding_point_id,time) SELECT ".$lastInsertId.", t_boarding_point_id, time FROM t_journey_boarding_points WHERE t_journey_id = ".$rowJourney['id']);
                        // Drop Off
                        mysql_query("INSERT INTO t_journey_drop_offs (t_journey_id,t_drop_off_id,time) SELECT ".$lastInsertId.", t_drop_off_id, time FROM t_journey_drop_offs WHERE t_journey_id = ".$rowJourney['id']);
                        // Schedule
                        mysql_query("INSERT INTO t_journey_schedules (t_journey_id, mon, tue, wed, thu, fri, sat, sun) SELECT ".$lastInsertId.", mon, tue, wed, thu, fri, sat, sun FROM t_journey_schedules WHERE t_journey_id = ".$rowJourney['id']);
                        // Price History
                        mysql_query("INSERT INTO t_journey_price_histories (t_journey_id, unit_price, created, created_by) VALUES ('".$lastInsertId."', ".$TJourney['TJourney']['unit_price'].", '".date("Y-m-d H:i:s")."', ".$user['User']['id'].");");
                        // Agent Price
                        // mysql_query("INSERT INTO t_journey_agent_prices (t_journey_id,t_agent_id,amount,percent,created) SELECT ".$lastInsertId.", t_agent_id,amount,percent,'".date("Y-m-d H:i:s")."' FROM t_journey_agent_prices WHERE t_journey_id = ".$rowJourney['id']);
                    }
                }
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add New Clone Journey');
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Save Add New Clone Journey (Error Save)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'View Clone Journey');
    }
    
    function changeStatus($id){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(!empty($this->data)){
            $dateNow  = date("Y-m-d H:i:s");
            $tJourney = $this->TJourney->read(null, $id);
            $user     = $this->getCurrentUser();
            if($this->data['active_type'] == 1){
                $start = 'NULL';
                $end   = 'NULL';
            } else {
                $start = "'".$this->data['active_start']."'";
                $end   = "'".$this->data['active_end']."'";
            }
            if($this->data['apply_type'] == 2){
                // Update All Route
                mysql_query("UPDATE `t_journeys` SET `status`= 1, active_start = ".$start.", active_end = ".$end.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `t_route_id`=".$tJourney['TJourney']['t_route_id'].";");
            } else {
                // Only One
                mysql_query("UPDATE `t_journeys` SET `status`= 1, active_start = ".$start.", active_end = ".$end.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            }
            // Edited History
            $editAct = "Update Active";
            mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                        VALUES (NULL, '1', ".$id.", '".$editAct."', now(), ".$user['User']['id'].");");
            // Agency APi Webhook
            $this->AgencyOnline->agencyAPiWebhook($id,'update');
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Change Journey Status', $id);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'View Change Status');
    }

    function getJourneyByDestination($id = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result['option'] = '<option departure="" price="" vip="" foreigner="" branch="" value="">'.INPUT_SELECT.'</option>';
        if(!empty($id)){
            $sqlJ = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id WHERE t_journeys.status IN (1,2) AND t_journeys.type IN (1,3) AND t_journeys.t_destination_from_id = ".$id." AND t_journeys.offline_project_id = ".$user['User']['offline_project_id']." ORDER BY t_journeys.description ASC;");
            while($rowJ = mysql_fetch_array($sqlJ)){
                $departure = date("h:i A", strtotime($rowJ['departure']));
                $result['option'] .= '<option departure="'.$departure.'" price="'.number_format($rowJ['unit_price'], 2).'" vip="'.number_format($rowJ['membership'], 2).'" foreigner="'.number_format($rowJ['foreigner_price'], 2).'" branch="'.$rowJ['branch_id'].'" value="'.$rowJ['id'].'">'.$rowJ['description'].'</option>';
            }
        }
        echo json_encode($result);
        exit;
    }

    function saveHighlight($id = null, $highlight = 0){
        $this->layout = 'ajax';
        if(!empty($id)){
            mysql_query("UPDATE t_journeys SET is_highlight = ".$highlight." WHERE id = ".$id);
        }
        exit;
    }

    function undoVoid($id){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        // Only One
        mysql_query("UPDATE `t_journeys` SET `status`= 1, `modified`= now(), `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Edited History
        mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                     VALUES (NULL, '1', ".$id.", 'Unvoid', now(), ".$user['User']['id'].");");
        // Agency APi Webhook
        $this->AgencyOnline->agencyAPiWebhook($id,'update');
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
        mysql_query("UPDATE `t_journeys` SET `note` = '".$note."', `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Edited History
        mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                     VALUES (NULL, '1', ".$id.", 'Update Note', now(), ".$user['User']['id'].");");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Update Note', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function updatePricePeriod($id = null, $status = null){
        if (!$id && !$status) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $result   = array();
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_journeys` SET `allow_price_period`= ".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Edited History
        if($status == 1){
            $editAct = "Enable Price Period";
        } else {
            $editAct = "Disble Price Period";
        }
        mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                     VALUES (NULL, '1', ".$id.", '".$editAct."', now(), ".$user['User']['id'].");");
        // Agency APi Webhook
        $this->AgencyOnline->agencyAPiWebhook($id,'update');
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Update Price Period', $id);
        $result['error'] = 0;
        echo json_encode($result);
        exit;
    }

    function deleteForever($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $tJourney = $this->TJourney->read(null, $id);
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_journeys` SET `status` = -1, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Edited History
        mysql_query("INSERT INTO `t_journey_edit_histories` (`id`, `offline_project_id`, `t_journey_id`, `action`, `edited_date`, `edited_by`) 
                     VALUES (NULL, '1', ".$id.", 'Delete Forever', now(), ".$user['User']['id'].");");
        // Agency APi Webhook
        $this->AgencyOnline->agencyAPiWebhook($id,'delete');
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;    
    }

}

?>