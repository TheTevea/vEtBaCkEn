<?php

class MainBranchesController extends AppController {

    var $name = 'MainBranches';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'View', $id);
        $this->data = $this->MainBranch->read(null, $id);
    }
    
    function add($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'main_branches', $this->data['MainBranch']['name'], "is_active = 1 AND offline_project_id = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->MainBranch->create();
                $this->data['MainBranch']['sys_code']   = $this->Helper->generateRandomString(6);
                $this->data['MainBranch']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['MainBranch']['created']    = $dateNow;
                $this->data['MainBranch']['created_by'] = $user['User']['id'];
                $this->data['MainBranch']['is_active']  = 1;
                if ($this->MainBranch->save($this->data)) {
                    $id = $this->MainBranch->id;
                    // User MainBranch
                    if(isset($this->data['MainBranch']['user_id'])){
                        for($i=0;$i<sizeof($this->data['MainBranch']['user_id']);$i++){
                            mysql_query("UPDATE users SET main_branch_id = ".$id." WHERE id = ".$this->data['MainBranch']['user_id'][$i]);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Save Add New', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Save Add New (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }   
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Add New');
            $tDestinations = ClassRegistry::init('TDestination')->find('list', array('fields' => array('TDestination.id', 'TDestination.name'), 'conditions' => array('TDestination.is_active = 1', 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
            $this->set(compact('tDestinations'));
        }
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if ((!$id && empty($this->data))) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'main_branches', $id, $this->data['MainBranch']['name'], "is_active = 1 AND offline_project_id = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['MainBranch']['modified'] = $dateNow;
                $this->data['MainBranch']['modified_by'] = $user['User']['id'];
                if ($this->MainBranch->save($this->data)) {
                    // User MainBranch
                    mysql_query("UPDATE users SET main_branch_id = NULL WHERE main_branch_id=".$id);
                    if(isset($this->data['MainBranch']['user_id'])){
                        for($i=0;$i<sizeof($this->data['MainBranch']['user_id']);$i++){
                            mysql_query("UPDATE users SET main_branch_id = ".$id." WHERE id = ".$this->data['MainBranch']['user_id'][$i]);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }   
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Edit', $id);
            $this->data = $this->MainBranch->read(null, $id);
            $tDestinations = ClassRegistry::init('TDestination')->find('list', array('fields' => array('TDestination.id', 'TDestination.name'), 'conditions' => array('TDestination.is_active = 1', 'TDestination.offline_project_id' => $user['User']['offline_project_id'])));
            $this->set(compact('tDestinations'));
        }
    }
    
    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user  = $this->getCurrentUser();
        $group = $this->MainBranch->read(null, $id);
        $this->data['MainBranch']['id'] = $id;
        $this->data['MainBranch']['modified'] = $dateNow;
        $this->data['MainBranch']['modified_by'] = $user['User']['id'];
        $this->data['MainBranch']['is_active'] = 2;
        if ($this->Group->save($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Main Branch', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

}

?>