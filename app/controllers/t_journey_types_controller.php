<?php
class TJourneyTypesController extends AppController {

    var $name = 'TJourneyTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'View', $id);
        $this->data = $this->TJourneyType->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_journey_types', $this->data['TJourneyType']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Save Add New Journey Type (Transportation Type has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TJourneyType->create();
                $this->data['TJourneyType']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TJourneyType']['created']  = $dateNow;
                $this->data['TJourneyType']['created_by'] = $user['User']['id'];
                $this->data['TJourneyType']['is_active'] = 1;
                if($this->TJourneyType->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Save Add New (Error)');
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
            if ($this->Helper->checkDouplicateEdit('name', 't_journey_types', $id, $this->data['TJourneyType']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Save Edit Journey Type(Journey Type has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TJourneyType']['modified'] = $dateNow;
                $this->data['TJourneyType']['modified_by'] = $user['User']['id'];
                if($this->TJourneyType->Save($this->data)){  
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
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Edit Journey Type', $id);
            $this->data = $this->TJourneyType->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $tJourneyType = $this->TJourneyType->read(null, $id);
        $this->data['TJourneyType']['id'] = $id;
        $this->data['TJourneyType']['modified']    = $dateNow;
        $this->data['TJourneyType']['modified_by'] = $user['User']['id'];
        $this->data['TJourneyType']['is_active']   = 2;
        if ($this->TJourneyType->save($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Journey Type', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }   
    }

}

?>