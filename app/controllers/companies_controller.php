<?php

class CompaniesController extends AppController {

    var $name = 'Companies';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Dashborad');
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
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'View', $id);
        $this->data = $this->Company->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'companies', $this->data['Company']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Save Add New (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Company->create();
                $this->data['Company']['sys_code']   = $this->Helper->generateRandomString(6);
                $this->data['Company']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['Company']['created']    = $dateNow;
                $this->data['Company']['created_by'] = $user['User']['id'];
                $this->data['Company']['is_active']  = 1;
                if ($this->Company->save($this->data)) {
                    $lastInsertId = $this->Company->id;
                    // Company Photo
                    $photoName = '';
                    if ($this->data['Company']['photo'] != '') {
                        $extPhoto  = explode(".", $this->data['Company']['photo']);
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".".$extPhoto[1];
                        rename('public/company_photo/tmp/' . $this->data['Company']['photo'], 'public/company_photo/' . $photoName);
                        mysql_query("UPDATE companies SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                    }
                    // User Company
                    if(isset($this->data['Company']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Company']['user_id']);$i++){
                            mysql_query("INSERT INTO user_companies (user_id, company_id) VALUES ('".$this->data['Company']['user_id'][$i]."','".$lastInsertId."')");
                        }
                    }
                    // Head Office
                    // Load Model
                    $this->loadModel('Branch');
                    $this->Branch->create();
                    $this->data['Branch']['sys_code']   = $this->Helper->generateRandomString(6);
                    $this->data['Branch']['offline_project_id'] = $user['User']['offline_project_id'];
                    $this->data['Branch']['company_id'] = $lastInsertId;
                    $this->data['Branch']['currency_center_id'] = $this->data['Company']['currency_center_id'];
                    $this->data['Branch']['created']    = $dateNow;
                    $this->data['Branch']['created_by'] = $user['User']['id'];
                    $this->data['Branch']['is_head']   = 1;
                    $this->data['Branch']['is_active'] = 1;
                    if ($this->Branch->save($this->data)) {
                        $branchId = $this->Branch->id;
                        // User Branch
                        if(isset($this->data['Company']['user_id'])){
                            for($i=0;$i<sizeof($this->data['Company']['user_id']);$i++){
                                mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$this->data['Company']['user_id'][$i]."','".$branchId."')");
                            }
                        }
                        // Destination
                        if(isset($this->data['Branch']['t_destination_id'])){
                            for($i=0;$i<sizeof($this->data['Branch']['t_destination_id']);$i++){
                                mysql_query("INSERT INTO branch_destinations (t_destination_id, branch_id) VALUES ('".$this->data['Branch']['t_destination_id'][$i]."','".$branchId."')");
                            }
                        }
                        // Boarding Point
                        $this->loadModel('TBoardingPoint');
                        $this->TBoardingPoint->create();
                        $boardingPoint = array();
                        $boardingPoint['TBoardingPoint']['sys_code']   = $this->Helper->generateRandomString(6);
                        $boardingPoint['TBoardingPoint']['offline_project_id'] = $user['User']['offline_project_id'];
                        $boardingPoint['TBoardingPoint']['branch_id']  = $branchId;
                        $boardingPoint['TBoardingPoint']['name']       = $this->data['Branch']['name'];
                        $boardingPoint['TBoardingPoint']['telephone']  = $this->data['Branch']['telephone'];
                        $boardingPoint['TBoardingPoint']['address']    = $this->data['Branch']['address'];
                        $boardingPoint['TBoardingPoint']['longs']      = $this->data['Branch']['longs'];
                        $boardingPoint['TBoardingPoint']['lats']       = $this->data['Branch']['lats'];
                        $boardingPoint['TBoardingPoint']['created']    = $dateNow;
                        $boardingPoint['TBoardingPoint']['created_by'] = $user['User']['id'];
                        $boardingPoint['TBoardingPoint']['is_default'] = 1;
                        $boardingPoint['TBoardingPoint']['is_active']  = 1;
                        $this->TBoardingPoint->save($boardingPoint);
                        $boardingPointId = $this->TBoardingPoint->id;
                        // Drop Off
                        $this->loadModel('TDropOff');
                        $this->TDropOff->create();
                        $dropOff = array();
                        $dropOff['TDropOff']['sys_code']   = $this->Helper->generateRandomString(6);
                        $dropOff['TDropOff']['offline_project_id'] = $user['User']['offline_project_id'];
                        $dropOff['TDropOff']['branch_id']  = $branchId;
                        $dropOff['TDropOff']['name']       = $this->data['Branch']['name'];
                        $dropOff['TDropOff']['telephone']  = $this->data['Branch']['telephone'];
                        $dropOff['TDropOff']['address']    = $this->data['Branch']['address'];
                        $dropOff['TDropOff']['longs']      = $this->data['Branch']['longs'];
                        $dropOff['TDropOff']['lats']       = $this->data['Branch']['lats'];
                        $dropOff['TDropOff']['created']    = $dateNow;
                        $dropOff['TDropOff']['created_by'] = $user['User']['id'];
                        $dropOff['TDropOff']['is_default'] = 1;
                        $dropOff['TDropOff']['is_active']  = 1;
                        $this->TDropOff->save($dropOff);
                        $dropOffId = $this->TDropOff->id;
                    }
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Add New');
        $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
        $currencyCenters = ClassRegistry::init('CurrencyCenter')->find('list', array('conditions' => array('CurrencyCenter.is_active = 1')));
        $tDestinations = ClassRegistry::init('TDestination')->find('list', array('fields' => array('TDestination.id', 'TDestination.name'), 'conditions' => array('TDestination.is_active = 1')));
        $this->set(compact('currencyCenters', 'countries', 'tDestinations'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'companies', $id, $this->data['Company']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Save Edit (Name has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Company']['modified'] = $dateNow;
                $this->data['Company']['modified_by'] = $user['User']['id'];
                if ($this->Company->save($this->data)) {
                    // Company photo
                    $photoName = $this->data['Company']['old_photo'];
                    if ($this->data['Company']['new_photo'] != '') {
                        $extPhoto  = explode(".", $this->data['Company']['new_photo']);
                        $photoName = md5($this->data['Company']['id'] . '_' . date("Y-m-d H:i:s")).".".$extPhoto[1];
                        rename('public/company_photo/tmp/' . $this->data['Company']['new_photo'], 'public/company_photo/' . $photoName);
                        @unlink('public/company_photo/' . $this->data['Company']['old_photo']);
                        mysql_query("UPDATE companies SET photo='" . $photoName . "' WHERE id=" . $this->data['Company']['id']);
                    }
                    // User location
                    mysql_query("DELETE FROM user_companies WHERE company_id=".$this->data['Company']['id']);
                    if(isset($this->data['Company']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Company']['user_id']);$i++){
                            mysql_query("INSERT INTO user_companies (user_id, company_id) VALUES ('".$this->data['Company']['user_id'][$i]."','".$this->data['Company']['id']."')");
                        }
                    }
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Edit', $id);
            $this->data = $this->Company->read(null, $id);
            $currencyCenters = ClassRegistry::init('CurrencyCenter')->find('list', array('conditions' => array('CurrencyCenter.is_active = 1')));
            $this->set(compact('currencyCenters'));
        }
    }

    function delete($id = null) {
        $sqlBranch = mysql_query("SELECT id FROM branches WHERE company_id = ".$id." AND is_active = 1 LIMIT 1");
        if (!$id || mysql_num_rows($sqlBranch)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $company = $this->Company->read(null, $id);
        mysql_query("UPDATE `companies` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function updateStatus($id = null, $status = null) {
        if (!$id || !$status) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $company  = $this->Company->read(null, $id);
        mysql_query("UPDATE `companies` SET `status`=".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Company', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function upload() {
        $this->layout = 'ajax';
        if ($_FILES['photo']['name'] != '') {
            $target_folder = 'public/company_photo/tmp/';
            $ext = explode(".", $_FILES['photo']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['pos_photo']) && $_SESSION['pos_photo'] != '') {
                @unlink($target_folder . $_SESSION['pos_photo']);
            }
            echo $_SESSION['pos_photo'] = $target_name;
            exit();
        }
    }

}

?>