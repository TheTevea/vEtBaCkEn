<?php
class TAgentTypesController extends AppController {

    var $name = 'TAgentTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'View', $id);
        $this->data = $this->TAgentType->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_agent_types', $this->data['TAgentType']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Save Add New Agent Type (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TAgentType->create();
                $this->data['TAgentType']['sys_code']   = $this->Helper->generateRandomString(6);
                $this->data['TAgentType']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['TAgentType']['created']    = $dateNow;
                $this->data['TAgentType']['created_by'] = $user['User']['id'];
                $this->data['TAgentType']['is_active']  = 1;
                if($this->TAgentType->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Save Add New (Error)');
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
            if ($this->Helper->checkDouplicateEdit('name', 't_agent_types', $id, $this->data['TAgentType']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Save Edit Agent Type(Name has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TAgentType']['modified']    = $dateNow;
                $this->data['TAgentType']['modified_by'] = $user['User']['id'];
                if($this->TAgentType->Save($this->data)){  
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
            $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Edit Agent Type', $id);
            $this->data = $this->TAgentType->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $tAgentType = $this->TAgentType->read(null, $id);
        $this->data['TAgentType']['id'] = $id;
        $this->data['TAgentType']['modified']    = $dateNow;
        $this->data['TAgentType']['modified_by'] = $user['User']['id'];
        $this->data['TAgentType']['is_active']   = 2;
        if ($this->TAgentType->save($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Agent Type', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        } 
    }

}

?>