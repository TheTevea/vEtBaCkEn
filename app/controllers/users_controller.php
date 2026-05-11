<?php

class UsersController extends AppController {

    var $name = 'Users';
    var $components = array('Helper', 'AutoId');
    
    function connection(){
        $this->layout = 'ajax';
        $result    = array();
        $user      = $this->getCurrentUser();
        if($user['User']['type'] == 2){
            $sqlProvince = mysql_query("SELECT * FROM t_destinations WHERE id IN (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id'].") LIMIT 1");
            if(mysql_num_rows($sqlProvince)){
                $rowProvince = mysql_fetch_array($sqlProvince);
                $sqlAgency   = mysql_query("SELECT SUM(t_tickets.total_seat) FROM t_tickets 
                                            INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type IN (1,3) AND t_agents.id != 106 AND t_agents.id != 55
                                            INNER JOIN t_destinations ON t_destinations.id =  t_tickets.t_destination_from_id AND t_destinations.province_id = ".$rowProvince['province_id']."
                                            WHERE t_tickets.`journey_date` = '".date("Y-m-d")."' AND t_tickets.offline_project_id = 1 AND t_tickets.`status` = 2;");
                $rowAgency = mysql_fetch_array($sqlAgency);
                $result['total'] = $rowAgency[0];
                // $sqlLucky = mysql_query("SELECT SUM(total_seat) FROM t_tickets WHERE lucky_draw_fee > 0 AND t_tickets.offline_project_id = 1 AND status = 2 AND date >= '".date("Y-m-01")."' AND date <= '".date("Y-m-t")."'");
                // $rowLucky = mysql_fetch_array($sqlLucky);
                // $result['total_lucky'] = $rowLucky[0];
                $result['total_lucky'] = 0;
            } else {
                $result['total'] = 0;
                $result['total_lucky'] = 0;
            }
        } else {
            $result['total'] = 0;
        }
        echo json_encode($result);
        exit();
    }
    
    function sendSeat(){
        $this->layout = 'ajax';
        $result['status'] = 0;
        $sqlServer = mysql_query("SELECT offline_projects.code, offline_servers.s_t FROM offline_servers INNER JOIN offline_projects ON offline_projects.id = offline_servers.offline_project_id LIMIT 1;");
        $rowServer = mysql_fetch_array($sqlServer);
        $sqlSend = mysql_query("SELECT t_seat_controls.*, t_tickets.sys_code AS ticketCode, t_ticket_details.sys_code AS ticketDetailCode, t_seat_control_clouds.id AS sync_id FROM t_seat_control_clouds INNER JOIN t_seat_controls ON t_seat_controls.id = t_seat_control_clouds.t_seat_control_id LEFT JOIN t_tickets ON t_tickets.id = t_seat_controls.t_ticket_id LEFT JOIN t_ticket_details ON t_ticket_details.id = t_seat_controls.t_ticket_detail_id WHERE t_seat_control_clouds.is_sync = 1 LIMIT 30;");
        if(mysql_num_rows($sqlSend)){
            $result['status'] = 1; 
            $i = 0;
            while($rowSend = mysql_fetch_array($sqlSend)){
                $result['sync'][$i]['id'] = $rowSend['sync_id'];
                $result['seat'][$i]['sysCode'] = $rowSend['sys_code'];
                $result['seat'][$i]['journeyDate'] = $rowSend['journey_date'];
                $result['seat'][$i]['journeyTime'] = $rowSend['journey_time'];
                $result['seat'][$i]['tJourneyId'] = $rowSend['t_journey_id'];
                if($rowSend['t_journey_transit_id'] != ""){
                    $result['seat'][$i]['tJourneyTransitId'] = $rowSend['t_journey_transit_id'];
                }
                $result['seat'][$i]['tTransportationTypeId'] = $rowSend['t_transportation_type_id'];
                $result['seat'][$i]['tRouteId'] = $rowSend['t_route_id'];
                $result['seat'][$i]['seatNumber'] = $rowSend['seat_number'];
                if($rowSend['gender'] != ""){
                    $result['seat'][$i]['gender'] = $rowSend['gender'];
                }
                $result['seat'][$i]['type'] = $rowSend['type'];
                $result['seat'][$i]['created'] = $rowSend['created'];
                $result['seat'][$i]['createdBy'] = $rowSend['created_by'];
                $result['seat'][$i]['modified'] = $rowSend['modified'];
                if($rowSend['modified_by'] != ""){
                    $result['seat'][$i]['modifiedBy'] = $rowSend['modified_by'];
                }
                $result['seat'][$i]['status'] = $rowSend['status'];
                $result['seat'][$i]['reference'] = $rowSend['ticketCode'];
                $result['seat'][$i]['serverCode'] = $rowServer['s_t'];
                $result['seat'][$i]['projectCode'] = $rowServer['code'];
                $result['seat'][$i]['ticketDetailCode'] = $rowSend['ticketDetailCode'];
                // Update Is Syncing
                mysql_query("UPDATE t_seat_control_clouds SET is_sync = 2 WHERE id = ".$rowSend['sync_id']);
                $i++;
            }
        }
        echo json_encode($result);
        exit;
    }
    
    function updateSeatSync($id, $status){
        $this->layout = 'ajax';
        if($status == 1){
            mysql_query("DELETE FROM t_seat_control_clouds WHERE id = ".$id);
        } else {
            mysql_query("UPDATE t_seat_control_clouds SET is_sync = 1 WHERE id = ".$id);
        }
        exit;
    }
    
    function updateSeat(){
        $this->layout = 'ajax';
        $seats = json_decode($this->data['seat'], TRUE);
        if(!empty($seats)){
            foreach($seats AS $seat){
                $sqlChk = mysql_query("SELECT id FROM t_seat_controls WHERE sys_code = '".$seat['sysCode']."' LIMIT 1");
                $seatControll = array();
                if(!mysql_num_rows($sqlChk)){
                    // Insert
                    ClassRegistry::init('TSeatControl')->create();
                } else {
                    // Update
                    $rowChk = mysql_fetch_array($sqlChk);
                    $seatControll['TSeatControl']['id']  = $rowChk['id'];
                }
                $seatControll['TSeatControl']['sys_code']        = $seat['sysCode'];
                $seatControll['TSeatControl']['journey_date']    = $seat['journeyDate'];
                $seatControll['TSeatControl']['journey_time']    = $seat['journeyTime'];
                $seatControll['TSeatControl']['t_journey_id']    = $seat['tjourneyId'];
                $seatControll['TSeatControl']['t_journey_transit_id'] = $seat['tjourneyTransitId'];
                $seatControll['TSeatControl']['t_transportation_type_id'] = $seat['ttransportationTypeId'];
                $seatControll['TSeatControl']['t_route_id']   = $seat['trouteId'];
                $seatControll['TSeatControl']['seat_number']  = $seat['seatNumber'];
                $seatControll['TSeatControl']['gender']       = $seat['gender'];
                $seatControll['TSeatControl']['type']         = $seat['type'];
                $seatControll['TSeatControl']['status']       = $seat['status'];
                $seatControll['TSeatControl']['reference']    = $seat['reference'];
                ClassRegistry::init('TSeatControl')->save($seatControll);
            }
        }
        exit;
    }
    
    function getSeatRequest(){
        $this->layout = 'ajax';
        $result['request']  = "";
        $result['response'] = "";
        $result['server']   = "";
        $sqlSer = mysql_query("SELECT s_t FROM offline_servers WHERE 1 LIMIT 1");
        $rowSer = mysql_fetch_array($sqlSer);
        $result['server'] = $rowSer[0];
        $sqlReq = mysql_query("SELECT * FROM t_seat_control_offlines WHERE status IN (1, 2)");
        if(mysql_num_rows($sqlReq)){
            while($rowReq = mysql_fetch_array($sqlReq)){
                if($rowReq['status'] == 1){
                    $result['request']  = $rowReq['request'];
                } else {
                    $result['response'] = $rowReq['request'];
                }
            }
        }
        if($result['request'] == ""){
            $request = SERVER_ID.$this->Helper->generateRandomString(6);
            mysql_query("INSERT INTO t_seat_control_offlines (request, created, status) VALUES ('".$request."', now(), 1);");
            $result['request']  = $request;
        }
        echo json_encode($result);
        exit;
    }
    
    function updateRequest($request){
        $this->layout = 'ajax';
        mysql_query("UPDATE t_seat_control_offlines SET status = 2 WHERE request = '".$request."';");
        exit;
    }
    
    function deleteRequest($request){
        $this->layout = 'ajax';
        mysql_query("DELETE FROM t_seat_control_offlines WHERE request = '".$request."';");
        exit;
    }

    function lang($lang = 'en') {
        $this->Session->write('lang', $lang);
        $this->redirect($this->getDefaultPage());
    }

    function checkDuplicate() {
        /* RECEIVE VALUE */
        $validateValue = $_GET['fieldValue'];
        $validateId = $_GET['fieldId'];

        $strTbl = $_GET['tableName'];
        $strCol = $_GET['fieldName'];
        $strCondition = $_GET['fieldCondition'];
        $condition = "id!='" . $_GET['fieldCurrentId'] . "' AND " . ($strCondition != '' ? $strCondition : 1);

        /* RETURN VALUE */
        $arrayToJs = array();
        $arrayToJs[0] = $validateId;

        $queryUser = mysql_query("SELECT " . $strCol . " FROM " . $strTbl . " WHERE " . $condition . " AND " . $strCol . "='" . mysql_real_escape_string($validateValue) . "'");
        if (!mysql_num_rows($queryUser)) {  // validate??
            $arrayToJs[1] = true;   // RETURN TRUE
            echo json_encode($arrayToJs);   // RETURN ARRAY WITH success
        } else {
            for ($x = 0; $x < 1000000; $x++) {
                if ($x == 990000) {
                    $arrayToJs[1] = false;
                    echo json_encode($arrayToJs);  // RETURN ARRAY WITH ERROR
                }
            }
        }
        exit();
    }

    function checkDuplicate2() {
        /* RECEIVE VALUE */
        $validateValue = $_GET['fieldValue'];
        $validateId = $_GET['fieldId'];

        $strTbl = $_GET['tableName2'];
        $strCol = $_GET['fieldName2'];
        $strCondition = $_GET['fieldCondition2'];
        $condition = "id!='" . $_GET['fieldCurrentId2'] . "' AND " . ($strCondition != '' ? $strCondition : 1);

        /* RETURN VALUE */
        $arrayToJs = array();
        $arrayToJs[0] = $validateId;

        $queryUser = mysql_query("SELECT " . $strCol . " FROM " . $strTbl . " WHERE " . $condition . " AND " . $strCol . "='" . mysql_real_escape_string($validateValue) . "'");
        if (!mysql_num_rows($queryUser)) {  // validate??
            $arrayToJs[1] = true;   // RETURN TRUE
            echo json_encode($arrayToJs);   // RETURN ARRAY WITH success
        } else {
            for ($x = 0; $x < 1000000; $x++) {
                if ($x == 990000) {
                    $arrayToJs[1] = false;
                    echo json_encode($arrayToJs);  // RETURN ARRAY WITH ERROR
                }
            }
        }
        exit();
    }

    function login() {
        // Check System Config
        $access = true;
        $config = "";
        $fileConfig = "config/system_config.fg";
        if (file_exists($fileConfig)) {
            $handle   = fopen($fileConfig, "r");
            $contents = fread($handle, filesize($fileConfig));
            fclose($handle);
            $config   = $contents;
        }
        if($config == "" || $config == "{}") {
            $access = false;
        }else{
            $array = json_decode($config, true);
            if(empty($array)){
                $access = false;
            }
        }
        if($access == false){
            $this->redirect(array('controller' => 'users', 'action' => 'systemConfig'));
            exit;
        }
        // Redirect when already logged in
        if ($this->Session->check('User')) {
            $user = $this->getCurrentUser();
            if(!empty($user)){
                $query = mysql_query("SELECT session_active FROM users WHERE id=" . $user['User']['id'] . " AND session_id='".$this->Session->id(session_id())."' AND offline_project_id = 1");
                if (@mysql_num_rows($query)) {
                    $this->redirect($this->getDefaultPage($user['User']['id']));
                }
            }
        }
        $this->layout = 'login';
        if (!empty($this->data)) {
            if(empty($this->data['User']['username']) || empty($this->data['User']['password'])){
                $this->Session->setFlash(__('Invalid.', true), 'flash_failure');
            } else {
                $useragent = $_SERVER['HTTP_USER_AGENT'];
                require_once('captcha/securimage.php');
                $checkUser = $this->User->find('first', array('conditions' => array(
                            'User.username' => $this->data['User']['username'],
                            'User.is_active' => 1, 'User.offline_project_id' => 1, 'User.type IN (2,3,4)')));
                if($checkUser['User']['is_hash'] == 0){
                    $user = $this->User->find('first', array('conditions' => array(
                                'User.username' => $this->data['User']['username'],
                                'User.password' => md5(Configure::read('Security.salt') . $this->data['User']['password'] . Configure::read('Security.cipherSeed')),
                                'User.is_active' => 1, 'User.offline_project_id' => 1, 'User.type IN (2,3,4)')));
                    // Change password to Bcrypt
                    $options = array(
                        'cost' => 10,
                    );
                    $password = mysql_real_escape_string($this->data['User']['password']);
                    $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                    $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                    mysql_query("UPDATE users SET password = '".$newPassword."', is_hash = 1 WHERE id = ".$checkUser['User']['id']);
                } else {
                    // Check Login with local
                    $hash = str_replace("$2a$", "$2y$", $checkUser['User']['password']);
                    $password = mysql_real_escape_string($this->data['User']['password']);
                    if (password_verify($password, $hash)) {
                        $user = $checkUser;
                    } else {
                        $user = null;
                    }
                }
                $img = new Securimage();
                $valid = true;
                $log = $this->Session->read('log');
                if ($log >= 3) {
                    if (empty($this->data['User']['code'])) {
                        $valid = false;
                    } else {
                        $valid = $img->check($this->data['User']['code']);
                    }
                }
                if ($valid) {
                    if (!empty($user)) {
                        // User
                        $UserAct = array();
                        $UserAct['User']['id'] = $user['User']['id'];
                        $UserAct['User']['session_id'] = $this->Session->id(session_id());
                        $UserAct['User']['session_start'] = date("Y-m-d H:i:s");
                        $UserAct['User']['session_active'] = date("Y-m-d H:i:s");
                        $UserAct['User']['session_lat'] = $this->data['User']['lat'];
                        $UserAct['User']['session_long'] = $this->data['User']['long'];
                        $UserAct['User']['session_accuracy'] = $this->data['User']['accuracy'];
                        $UserAct['User']['login_attempt_remote_ip'] = $this->Helper->getIpAddress();
                        $UserAct['User']['login_attempt_http_user_agent'] = "OS: ".$this->Helper->getOS($useragent)." Browser: ".$this->Helper->getBrowser($useragent);
                        $this->User->save($UserAct);
                        // User Log
                        $UserLog = array();
                        $this->loadModel('UserLog');
                        $this->UserLog->create();
                        $UserLog['UserLog']['user_id'] = $user['User']['id'];
                        $UserLog['UserLog']['type'] = 'Login';
                        $UserLog['UserLog']['http_user_agent'] = "OS: ".$this->Helper->getOS($useragent)." Browser: ".$this->Helper->getBrowser($useragent);
                        $UserLog['UserLog']['remote_addr'] = $this->Helper->getIpAddress();
                        $UserLog['UserLog']['lat'] = $this->data['User']['lat'];
                        $UserLog['UserLog']['long'] = $this->data['User']['long'];
                        $this->UserLog->save($UserLog);
                        $logID = $this->UserLog->id;
                        // User Actvity Log
                        $useragent = $_SERVER['HTTP_USER_AGENT'];
                        $browser = $this->Helper->getBrowser($useragent);
                        $os      = $this->Helper->getOS($useragent);
                        $ipAddr  = $this->Helper->getIpAddress();
                        $this->loadModel('UserActivityLog');
                        $this->UserActivityLog->create();
                        $UserActLog = array();
                        $UserActLog['UserActivityLog']['user_id'] = $user['User']['id'];
                        $UserActLog['UserActivityLog']['type'] = 'Login';
                        $UserActLog['UserActivityLog']['tbl_from_id'] = $logID;
                        $UserActLog['UserActivityLog']['tbl_to_id'] = 0;
                        $UserActLog['UserActivityLog']['action'] = "Login";
                        $UserActLog['UserActivityLog']['browser'] = $browser;
                        $UserActLog['UserActivityLog']['operating_system'] = $os;
                        $UserActLog['UserActivityLog']['ip'] = $ipAddr;
                        $this->UserActivityLog->save($UserActLog);
                        // Set Session
                        $this->Session->delete('log');
                        $this->setCurrentUser($user);
                        // Redirect
                        $this->redirect($this->getDefaultPage($user['User']['id']));
                    } else {
                        $this->Session->write('log', $log + 1);
                        $this->Session->setFlash(__('Invalid User Name or Password.', true), 'flash_failure');
                    }
                } else {
                    $this->Session->setFlash(__('Invalid Code.', true), 'flash_failure');
                }
            }
        }
        $this->set('log', $this->Session->read('log'));
    }

    function logout() {
        $user = $this->getCurrentUser();
        // Create log
        if ($user['User']['id'] != '') {
            // Session log
            mysql_query("UPDATE users SET
                            session_id=NULL,
                            session_start=NULL,
                            session_active=NULL,
                            session_lat=NULL,
                            session_long=NULL,
                            login_lat=NULL,
                            login_long=NULL,
                            login_ip_addr=NULL,
                            login_user_agent=NULL
                        WHERE id= " . $user['User']['id']);
            // User Log
            $this->loadModel('UserLog');
            $UserLog = array();
            $useragent = $_SERVER['HTTP_USER_AGENT'];
            $this->UserLog->create();
            $UserLog['UserLog']['user_id'] = $user['User']['id'];
            $UserLog['UserLog']['type'] = 'LogOut';
            $UserLog['UserLog']['http_user_agent'] = "OS: ".$this->Helper->getOS($useragent)." Browser: ".$this->Helper->getBrowser($useragent);
            $UserLog['UserLog']['remote_addr'] = $this->Helper->getIpAddress();
            $UserLog['UserLog']['lat'] = $this->data['User']['lat'];
            $UserLog['UserLog']['long'] = $this->data['User']['long'];
            $this->UserLog->save($UserLog);
            $logID = $this->UserLog->id;
            $this->Helper->saveUserActivity($user['User']['id'], 'User', 'LogOut', $logID);
        }
        // logout
        $this->Session->destroy();
        $this->redirect('/users/login');
    }

    function profile() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $condition = "is_active = 1";
            if($user['User']['type'] != 1){
                $condition = "is_active = 1 AND offline_project_id = 1";
            }
            if ($this->Helper->checkDouplicateEdit('username', 'users', $user['User']['id'], $this->data['User']['username'], $condition)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Profile (Name ready exsited)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $queryOldPassword = mysql_query("SELECT * FROM users WHERE id=" . $user['User']['id']);
                $dataOldPassword  = mysql_fetch_array($queryOldPassword);
                if($dataOldPassword['is_hash'] == 0){
                    if ($dataOldPassword['password'] != md5(Configure::read('Security.salt') . $this->data['old_password'] . Configure::read('Security.cipherSeed'))) {
                        echo MESSAGE_DATA_INVALID;
                        exit();
                    }
                } else {
                    $hash = str_replace("$2a$", "$2y$", $dataOldPassword['password']);
                    $password = mysql_real_escape_string($this->data['old_password']);
                    if (password_verify($password, $hash)) {
                        
                    } else {
                        echo MESSAGE_DATA_INVALID;
                        exit();
                    }
                }
                $options = array(
                    'cost' => 10,
                );
                $password = mysql_real_escape_string($this->data['User']['password']);
                $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                $this->data['User']['password']    = $newPassword;
                $this->data['User']['is_hash']     = 1;
                $this->data['User']['id'] = $user['User']['id'];
                $this->data['User']['modified'] = $dateNow;
                $this->data['User']['modified_by'] = $user['User']['id'];
                if ($this->User->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Profile');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit();
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Profile (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit();
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Change Profile');
            $this->data = $this->User->read(null, $user['User']['id']);
        }
        $groups = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1'), 'order' => 'name'));
        $this->set(compact('groups', 'user'));
    }

    function index() {
        $this->layout = 'ajax';
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set('user', $this->User->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $condition = "is_active = 1";
            if($user['User']['type'] != 1){
                $condition = "is_active = 1 AND offline_project_id = ".$this->data['User']['offline_project_id'];
            }
            if ($this->Helper->checkDouplicate('username', 'users', $this->data['User']['username'], $condition)) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow = date("Y-m-d H:i:s");
                $this->User->create();
                $options = array(
                    'cost' => 10,
                );
                $password      = mysql_real_escape_string($this->data['User']['password']);
                $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                $this->data['User']['password']   = $newPassword;
                $this->data['User']['is_hash']    = 1;
                $this->data['User']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['User']['created']    = $dateNow;
                $this->data['User']['created_by'] = $user['User']['id'];
                $this->data['User']['is_active']  = 1;
                if ($this->User->save($this->data)) {
                    $lastInsertId = $this->User->getLastInsertId();
                    // User Company
                    if (isset($this->data['User']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['User']['company_id']); $i++) {
                            mysql_query("INSERT INTO user_companies (user_id, company_id) VALUES ('".$lastInsertId."','" . $this->data['User']['company_id'][$i] . "')");
                        }
                    }
                    // User Branch
                    if (isset($this->data['User']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['User']['branch_id']); $i++) {
                            mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$lastInsertId."','" . $this->data['User']['branch_id'][$i] . "')");
                        }
                    }
                    // User Report Main Branch
                    if (isset($this->data['User']['report_main_branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['User']['report_main_branch_id']); $i++) {
                            mysql_query("INSERT INTO user_report_main_branches (user_id, main_branch_id) VALUES ('".$lastInsertId."','" . $this->data['User']['report_main_branch_id'][$i] . "')");
                        }
                    }
                    // User Group
                    if($this->data['User']['type'] == 2 && $user['User']['type'] == 1){
                        mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$lastInsertId."', 2)");
                    } else {
                        for ($i = 0; $i < sizeof($this->data['User']['group_id']); $i++) {
                            mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$lastInsertId."','" . $this->data['User']['group_id'][$i] . "')");
                        }
                    }
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $sexes  = array('Male' => 'Male', 'Female' => 'Female');
        if($user['User']['type'] != 1){
            $groups = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1', "(offline_project_id = ".$user['User']['offline_project_id']." || id = 2)"), 'order' => 'name'));
            $offlineProjects = ClassRegistry::init('OfflineProject')->find('list', array('conditions' => array('is_active' => '1', 'id' => $user['User']['offline_project_id']), 'order' => 'name'));
        } else {
            $groups = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1'), 'order' => 'name'));
            $offlineProjects = ClassRegistry::init('OfflineProject')->find('list', array('conditions' => array('is_active' => '1'), 'order' => 'name'));
        }
        $mainBranches    = ClassRegistry::init('MainBranch')->find('list', array('conditions' => array('is_active' => '1', 'offline_project_id' => $user['User']['offline_project_id']), 'order' => 'name'));
        $this->set(compact('sexes', 'groups', 'mainBranches', 'offlineProjects'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['User']['modified'] = $dateNow;
            $this->data['User']['modified_by'] = $user['User']['id'];
            if ($this->User->save($this->data)) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Edit', $id, $id);
                // User Company
                mysql_query("DELETE FROM user_companies WHERE user_id=" . $id);
                if (isset($this->data['User']['company_id'])) {
                    for ($i = 0; $i < sizeof($this->data['User']['company_id']); $i++) {
                        mysql_query("INSERT INTO user_companies (user_id,company_id) VALUES ('" . $id . "','" . $this->data['User']['company_id'][$i] . "')");
                    }
                }
                // User Branch
                mysql_query("DELETE FROM user_branches WHERE user_id=" . $id);
                if (isset($this->data['User']['branch_id'])) {
                    for ($i = 0; $i < sizeof($this->data['User']['branch_id']); $i++) {
                        mysql_query("INSERT INTO user_branches (user_id,branch_id) VALUES ('" . $id . "','" . $this->data['User']['branch_id'][$i] . "')");
                    }
                }
                // User Report Location Branch
                mysql_query("DELETE FROM user_report_main_branches WHERE user_id=" . $id);
                if (isset($this->data['User']['report_main_branch_id'])) {
                    for ($i = 0; $i < sizeof($this->data['User']['report_main_branch_id']); $i++) {
                        mysql_query("INSERT INTO user_report_main_branches (user_id, main_branch_id) VALUES ('" . $id . "','" . $this->data['User']['report_main_branch_id'][$i] . "')");
                    }
                }
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Edit Error', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Edit', $id);
            $this->data = $this->User->read(null, $id);
            $mainBranches = ClassRegistry::init('MainBranch')->find('list', array('conditions' => array('is_active' => '1'), 'order' => 'name'));
            $this->set(compact('mainBranches'));
        }
    }

    function editProfile($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit();
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $condition = "is_active = 1";
            if($user['User']['type'] != 1){
                $condition = "is_active = 1 AND offline_project_id = ".$this->data['User']['offline_project_id'];
            }
            if ($this->Helper->checkDouplicateEdit('username', 'users', $id, $this->data['User']['username'], $condition)) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Edit Profile (Username has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $oldUser = $this->User->read(null, $this->data['User']['id']);
                if(!empty($this->data['User']['password'])){
                    $options = array(
                        'cost' => 10,
                    );
                    $password      = mysql_real_escape_string($this->data['User']['password']);
                    $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                    $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                    $this->data['User']['password']    = $newPassword;
                    $this->data['User']['is_hash']     = 1;
                } else {
                    $this->data['User']['password'] = $oldUser['User']['password'];
                }
                $this->data['User']['modified']    = $dateNow;
                $this->data['User']['modified_by'] = $user['User']['id'];
                if ($this->User->save($this->data)) {
                    // User Group
                    mysql_query("DELETE FROM user_groups WHERE user_id=" . $id);
                    for ($i = 0; $i < sizeof($this->data['User']['group_id']); $i++) {
                        mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('" . $id . "','" . $this->data['User']['group_id'][$i] . "')");
                    }
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Edit Profile', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit();
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Save Edit Profile (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit();
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Edit Profile', $id);
            $this->data = $this->User->read(null, $id);
            if($user['User']['type'] != 1){
                $groups = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1', "(offline_project_id = ".$user['User']['offline_project_id']." || id = 2)"), 'order' => 'name'));
                $offlineProjects = ClassRegistry::init('OfflineProject')->find('list', array('conditions' => array('is_active' => '1', 'id' => $user['User']['offline_project_id']), 'order' => 'name'));
            } else {
                $groups = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1'), 'order' => 'name'));
                $offlineProjects = ClassRegistry::init('OfflineProject')->find('list', array('conditions' => array('is_active' => '1'), 'order' => 'name'));
            }
            $this->set(compact('groups', 'offlineProjects'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if ($id != 1) {
            $dateNow  = date("Y-m-d H:i:s");
            $userDel  = $this->User->read(null, $id);
            mysql_query("UPDATE `users` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'User', 'Delete (Error)', $id);
            echo MESSAGE_ADMIN_USER_COULD_NOT_BE_DELETED;
            exit;
        }
        exit;
    }
    
    function createSysAct($mod, $act, $staus){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $bug = mysql_real_escape_string($_POST['bug']);
        $this->Helper->createSysActivity($mod, $act, $bug, $user['User']['id'], $staus);
        exit;
    }

    function vatGenerateInvoice(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
    }

    function addOnlineCustomer(){
        $this->layout = 'ajax';
        $result = array();
        $result['error'] = 1;
        if(!empty($_POST)){
            $apiKey = $_POST['api_key'];
            if($apiKey == "3178ade3iu7079X51smJvs9Wp639X"){
                $this->loadModel("OnlineCustomerTicket");
                $customerTicket = array();
                $this->OnlineCustomerTicket->create();
                $customerTicket['OnlineCustomerTicket']['id']  = $_POST['id'];
                $customerTicket['OnlineCustomerTicket']['branch_id']  = $_POST['branch_id'];
                $customerTicket['OnlineCustomerTicket']['code']       = $_POST['code'];
                $customerTicket['OnlineCustomerTicket']['number']     = $_POST['number'];
                $customerTicket['OnlineCustomerTicket']['name']       = $_POST['name'];
                $customerTicket['OnlineCustomerTicket']['telephone']  = trim($_POST['telephone']);
                $customerTicket['OnlineCustomerTicket']['type']       = $_POST['type'];
                $customerTicket['OnlineCustomerTicket']['created']    = $_POST['created'];
                $customerTicket['OnlineCustomerTicket']['created_by'] = $_POST['created_by'];
                $customerTicket['OnlineCustomerTicket']['modified']   = $_POST['modified'];
                $customerTicket['OnlineCustomerTicket']['is_active']  = $_POST['is_active'];
                if ($this->OnlineCustomerTicket->save($customerTicket)) {
                    $result['error'] = 0;
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    function updateOnlineCustomer(){
        $this->layout = 'ajax';
        $result = array();
        $result['error'] = 1;
        if(!empty($_POST)){
            $apiKey = $_POST['api_key'];
            if($apiKey == "3178ade3iu7079X51smJvs9Wp639X"){
                $this->loadModel("OnlineCustomerTicket");
                $customerTicket = array();
                $customerTicket['OnlineCustomerTicket']['id']          = $_POST['id'];
                $customerTicket['OnlineCustomerTicket']['branch_id']   = $_POST['branch_id'];
                $customerTicket['OnlineCustomerTicket']['code']        = $_POST['code'];
                $customerTicket['OnlineCustomerTicket']['number']      = $_POST['number'];
                $customerTicket['OnlineCustomerTicket']['name']        = $_POST['name'];
                $customerTicket['OnlineCustomerTicket']['telephone']   = trim($_POST['telephone']);
                $customerTicket['OnlineCustomerTicket']['type']        = $_POST['type'];
                $customerTicket['OnlineCustomerTicket']['modified']    = $_POST['modified'];
                $customerTicket['OnlineCustomerTicket']['modified_by'] = $_POST['modified_by'];
                $customerTicket['OnlineCustomerTicket']['is_active']   = $_POST['is_active'];
                if ($this->OnlineCustomerTicket->save($customerTicket)) {
                    $result['error'] = 0;
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    function updateStatusOnlineCustomer(){
        $this->layout = 'ajax';
        $result = array();
        $result['error'] = 1;
        if(!empty($_POST)){
            $apiKey = $_POST['api_key'];
            if($apiKey == "3178ade3iu7079X51smJvs9Wp639X"){
                mysql_query("UPDATE `online_customer_tickets` SET `is_active`=".$_POST['is_active'].", `modified`='".$_POST['modified']."', `modified_by`=".$_POST['modified_by']." WHERE `id`=".$_POST['id'].";");
                $result['error'] = 0;
            }
        }
        echo json_encode($result);
        exit;
    }

    function updateStatusAllOnlineCustomer(){
        $this->layout = 'ajax';
        $result = array();
        $result['error'] = 1;
        if(!empty($_POST)){
            $apiKey = $_POST['api_key'];
            if($apiKey == "3178ade3iu7079X51smJvs9Wp639X"){
                mysql_query("UPDATE `online_customer_tickets` SET `is_active` = 1, `modified`='".$_POST['modified']."', `modified_by`=".$_POST['modified_by']." WHERE `is_active` = 3;");
                $result['error'] = 0;
            }
        }
        echo json_encode($result);
        exit;
    }

    // function saveUserBranch($userId){
    //     $this->layout = 'ajax';
    //     $result = array();
    //     $result['error'] = 1;
    //     if(!empty($this->data) && !empty($userId)){
    //         // User Branch
    //         mysql_query("DELETE FROM user_branches WHERE user_id=" . $userId);
    //         if (!empty($this->data['branch_id'])) {
    //             for ($i = 0; $i < sizeof($this->data['branch_id']); $i++) {
    //                 mysql_query("INSERT INTO user_branches (user_id,branch_id) VALUES ('" . $userId . "','" . $this->data['branch_id'][$i] . "')");
    //             }
    //         }
    //     }
    //     echo json_encode($result);
    //     exit;
    // }

}

?>