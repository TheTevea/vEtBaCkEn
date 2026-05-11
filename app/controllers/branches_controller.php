<?php

class BranchesController extends AppController {

    var $name = 'Branches';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'View', $id);
        $this->data = $this->Branch->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'branches', $this->data['Branch']['name'], 'is_active = 1 AND company_id ='.$this->data['Branch']['company_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $sqlBranch = mysql_query("SELECT id FROM branches WHERE company_id = ".$this->data['Branch']['company_id']." AND is_active = 1");
                if(!mysql_num_rows($sqlBranch)){
                    $this->data['Branch']['is_head'] = 1;
                }
                $company = ClassRegistry::init('Company')->read(null, $this->data['Branch']['company_id']);
                $this->Branch->create();
                $this->data['Branch']['sys_code']   = $this->Helper->generateRandomString(6);
                $this->data['Branch']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['Branch']['currency_center_id'] = $company['Company']['currency_center_id'];
                $this->data['Branch']['created']    = $dateNow;
                $this->data['Branch']['created_by'] = $user['User']['id'];
                $this->data['Branch']['is_active'] = 1;
                if ($this->Branch->save($this->data)) {
                    $lastInsertId = $this->Branch->id;
                    // User Branch
                    if(isset($this->data['Branch']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Branch']['user_id']);$i++){
                            mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$this->data['Branch']['user_id'][$i]."','".$lastInsertId."')");
                        }
                    }
                    // Destination
                    if(isset($this->data['Branch']['t_destination_id'])){
                        for($i=0;$i<sizeof($this->data['Branch']['t_destination_id']);$i++){
                            mysql_query("INSERT INTO branch_destinations (t_destination_id, branch_id) VALUES ('".$this->data['Branch']['t_destination_id'][$i]."','".$lastInsertId."')");
                        }
                    }
                    // Boarding Point
                    $this->loadModel('TBoardingPoint');
                    $this->TBoardingPoint->create();
                    $boardingPoint = array();
                    $boardingPoint['TBoardingPoint']['sys_code']   = $this->Helper->generateRandomString(6);
                    $boardingPoint['TBoardingPoint']['offline_project_id'] = $user['User']['offline_project_id'];
                    $boardingPoint['TBoardingPoint']['branch_id']  = $lastInsertId;
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
                    $dropOff['TDropOff']['branch_id']  = $lastInsertId;
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
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
        $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
        $provinces = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
        $tDestinations = ClassRegistry::init('TDestination')->find('list', array('fields' => array('TDestination.id', 'TDestination.name'), 'conditions' => array('TDestination.is_active = 1', 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $this->set(compact('countries', 'companies', 'tDestinations', 'provinces'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if ((!$id && empty($this->data))) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'branches', $id, $this->data['Branch']['name'], 'is_active = 1 AND company_id ='.$this->data['Branch']['company_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                if(!empty($this->data['Branch']['company_id'])){
                    $company = ClassRegistry::init('Company')->read(null, $this->data['Branch']['company_id']);
                    $this->data['Branch']['currency_center_id'] = $company['Company']['currency_center_id'];
                }
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Branch']['modified'] = $dateNow;
                $this->data['Branch']['modified_by'] = $user['User']['id'];
                if ($this->Branch->save($this->data)) {
                    // User Branch
                    mysql_query("DELETE FROM user_branches WHERE branch_id=".$id);
                    if(isset($this->data['Branch']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Branch']['user_id']);$i++){
                            mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$this->data['Branch']['user_id'][$i]."','".$id."')");
                        }
                    }
                    // Destination
                    mysql_query("DELETE FROM branch_destinations WHERE branch_id=".$id);
                    if(isset($this->data['Branch']['t_destination_id'])){
                        for($i=0;$i<sizeof($this->data['Branch']['t_destination_id']);$i++){
                            mysql_query("INSERT INTO branch_destinations (t_destination_id, branch_id) VALUES ('".$this->data['Branch']['t_destination_id'][$i]."','".$id."')");
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Edit', $id);
            $this->data = $this->Branch->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
            $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
            $provinces = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
            $tDestinations = ClassRegistry::init('TDestination')->find('list', array('fields' => array('TDestination.id', 'TDestination.name'), 'conditions' => array('TDestination.is_active = 1', 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
            $this->set(compact('countries', 'companies', 'tDestinations', 'provinces'));
        }
    }

    function delete($id = null) {
        $sqlSales = mysql_query("SELECT id FROM t_tickets WHERE branch_id = ".$id." AND status > 0 LIMIT 1");
        if (!$id || mysql_num_rows($sqlSales)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $branch = $this->Branch->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Delete', $id);
        mysql_query("UPDATE `branches` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>