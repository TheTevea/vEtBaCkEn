<?php
class TDepartureTimesController extends AppController {

    var $name = 'TDepartureTimes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'View', $id);
        $this->data = $this->TDepartureTime->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_departure_times', $this->data['TDepartureTime']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Save Add New (Transportation Type has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TDepartureTime->create();
                $this->data['TDepartureTime']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TDepartureTime']['created']  = $dateNow;
                $this->data['TDepartureTime']['created_by'] = $user['User']['id'];
                $this->data['TDepartureTime']['is_active'] = 1;
                if($this->TDepartureTime->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_departure_times', $id, $this->data['TDepartureTime']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Save Edit (DepartureTime has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TDepartureTime']['modified'] = $dateNow;
                $this->data['TDepartureTime']['modified_by'] = $user['User']['id'];
                if($this->TDepartureTime->Save($this->data)){  
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
            $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Edit', $id);
            $this->data = $this->TDepartureTime->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $tDepartureTime = $this->TDepartureTime->read(null, $id);
        mysql_query("UPDATE `t_departure_times` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Departure Time', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;  
    }

}

?>