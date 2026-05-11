<?php

class TJourneyPriceDefaultsController extends AppController {

    var $name = 'TJourneyPriceDefaults';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Default', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Default', 'View', $id);
        $this->data = $this->TJourneyPriceDefault->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->TJourneyPriceDefault->create();
            $this->data['TJourneyPriceDefault']['sys_code']   = md5(date("Y-m-d H:i:s").rand());
            $this->data['TJourneyPriceDefault']['offline_project_id'] = $user['User']['offline_project_id'];
            $this->data['TJourneyPriceDefault']['created_by'] = $user['User']['id'];
            $this->data['TJourneyPriceDefault']['status']     = 1;
            if ($this->TJourneyPriceDefault->save($this->data)) {
                $insertId = $this->TJourneyPriceDefault->id;
                $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Default', 'Save Add New', $insertId);
                // Move Existed to History
                $condition = " AND (main_branch_id IS NULL OR main_branch_id = '')";
                if($this->data['TJourneyPriceDefault']['apply_to'] == 2 && $this->data['TJourneyPriceDefault']['main_branch_id'] != ""){
                    $condition = " AND main_branch_id = ".$this->data['TJourneyPriceDefault']['main_branch_id'];
                }
                mysql_query("INSERT INTO t_journey_price_default_histories SELECT * FROM t_journey_price_defaults WHERE id != ".$insertId." AND destination_from_id = ".$this->data['TJourneyPriceDefault']['destination_from_id']." AND destination_to_id = ".$this->data['TJourneyPriceDefault']['destination_to_id']." AND t_transportation_type_id = ".$this->data['TJourneyPriceDefault']['t_transportation_type_id'].$condition);
                mysql_query("DELETE FROM t_journey_price_defaults WHERE id != ".$insertId." AND destination_from_id = ".$this->data['TJourneyPriceDefault']['destination_from_id']." AND destination_to_id = ".$this->data['TJourneyPriceDefault']['destination_to_id']." AND t_transportation_type_id = ".$this->data['TJourneyPriceDefault']['t_transportation_type_id'].$condition);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Default', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Default', 'Add New');
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1", 'MainBranch.offline_project_id' => $user['User']['offline_project_id'])));
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Set Price Default', 'Delete', $id);
        mysql_query("UPDATE `t_journey_price_defaults` SET `status`=0, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>