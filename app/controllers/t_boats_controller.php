<?php
class TBoatsController extends AppController {

    var $name = 'TBoats';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'View', $id);
        $this->data = $this->TBoat->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 't_boats', $this->data['TBoat']['code'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Save Add New (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TBoat->create();
                $this->data['TBoat']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TBoat']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['TBoat']['created']  = $dateNow;
                $this->data['TBoat']['created_by'] = $user['User']['id'];
                $this->data['TBoat']['is_active'] = 1;
                if($this->TBoat->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Save Add New (Error)');
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
            if ($this->Helper->checkDouplicateEdit('code', 't_boats', $id, $this->data['TBoat']['code'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Save Edit (Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TBoat']['modified'] = $dateNow;
                $this->data['TBoat']['modified_by'] = $user['User']['id'];
                if($this->TBoat->Save($this->data)){  
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit;                                
                }else{
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Edit', $id);
            $this->data = $this->TBoat->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $tBoat = $this->TBoat->read(null, $id);
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_boats` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Boat', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;    
    }

}

?>