<?php

class TJourneyPricePeriodsController extends AppController {

    var $name = 'TJourneyPricePeriods';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'View', $id);
        $this->data = $this->TJourneyPricePeriod->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $transporationType = $this->data['TJourneyPricePeriod']['t_transportation_type_id'];
            $name = $this->data['TJourneyPricePeriod']['name'];
            for ($i = 0; $i < sizeof($transporationType); $i++) {
                $tTransportationTypes = ClassRegistry::init('TTransportationType')->read(null, $transporationType[$i]);
                $this->data['TJourneyPricePeriod']['name'] = $name." (".$tTransportationTypes['TTransportationType']['name'].")";
                if (!$this->Helper->checkDouplicate('name', 't_journey_price_periods', $this->data['TJourneyPricePeriod']['name'], 'status > 0 AND offline_project_id = '.$user['User']['offline_project_id'])) {
                    $this->TJourneyPricePeriod->create();
                    $this->data['TJourneyPricePeriod']['offline_project_id'] = $user['User']['offline_project_id'];
                    $this->data['TJourneyPricePeriod']['t_transportation_type_id'] = $transporationType[$i];
                    $this->data['TJourneyPricePeriod']['created_by'] = $user['User']['id'];
                    $this->data['TJourneyPricePeriod']['status']     = 1;
                    $this->TJourneyPricePeriod->save($this->data);
                }   
            }
            $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Save Add New', "0");
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Add New');
        $mainBranches     = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1", 'MainBranch.offline_project_id' => $user['User']['offline_project_id'])));
        $destinationFroms = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $destinationTos   = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array("conditions" => array("TTransportationType.is_active = 1", 'TTransportationType.offline_project_id' => $user['User']['offline_project_id'])));
        $this->set(compact('destinationFroms', 'destinationTos', 'tTransportationTypes', 'mainBranches'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_journey_price_periods', $id, $this->data['TJourneyPricePeriod']['name'], 'status > 0 AND offline_project_id = '.$user['User']['offline_project_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['TJourneyPricePeriod']['modified_by'] = $user['User']['id'];
                if ($this->TJourneyPricePeriod->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Edit', $id);
        $this->data = $this->TJourneyPricePeriod->read(null, $id);
        $mainBranches     = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1", 'MainBranch.offline_project_id' => $user['User']['offline_project_id'])));
        $destinationFroms = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $destinationTos   = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1", 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
        $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array("conditions" => array("TTransportationType.is_active = 1", 'TTransportationType.offline_project_id' => $user['User']['offline_project_id'])));
        $this->set(compact('destinationFroms', 'destinationTos', 'tTransportationTypes', 'mainBranches'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Delete', $id);
        mysql_query("UPDATE `t_journey_price_periods` SET `status`=0, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function approve($id = null, $status = 3) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Period', 'Approve', $id);
        mysql_query("UPDATE `t_journey_price_periods` SET `status`=".$status.", `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

}

?>