<?php

class BusTypesController extends AppController {

    var $name = 'BusTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'View', $id);
        $this->data = $this->BusType->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'bus_types', $this->data['BusType']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'BusType', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->BusType->create();
                $this->data['BusType']['created_by'] = $user['User']['id'];
                if ($this->BusType->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Save Add New', $this->BusType->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array("conditions" => array("TTransportationType.is_active = 1", 'TTransportationType.offline_project_id' => 1)));
        $this->set(compact('tTransportationTypes'));
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Add New');
    }

    function edit($id = null) {
        // Set layout
        $this->layout = 'ajax';
        
        // Validate input
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }

        // Fetch the current user
        $user = $this->getCurrentUser();
        
        // Handle form submission
        if (!empty($this->data)) {
            // Check for duplicate filed
            if ($this->Helper->checkDouplicateEdit('name', 'bus_types', $id, $this->data['BusType']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            // Save the data
            $this->data['BusType']['modified_by'] = $user['User']['id'];
            if ($this->BusType->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            }
            exit;
        }
    
        // Save user activity for viewing the edit form
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Edit', $id);
        // Fetch the existing data for the bus type
        $this->data = $this->BusType->read(null, $id);
        // Model data for the dropdown
        $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array(
            "conditions" => array(
                "TTransportationType.is_active = 1", 
                'TTransportationType.offline_project_id' => 1
            )
        ));
        // Set the data for the view
        $this->set(compact('tTransportationTypes'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Type', 'Delete', $id);
        mysql_query("UPDATE `bus_types` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>