<?php
class TCommisionsController extends AppController {

    var $name = 'TCommisions';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'View', $id);
        $this->data = $this->TCommision->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_commisions', $this->data['TCommision']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Save Add New Commision (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TCommision->create();
                $this->data['TCommision']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['TCommision']['created']  = $dateNow;
                $this->data['TCommision']['created_by'] = $user['User']['id'];
                $this->data['TCommision']['is_active'] = 1;
                if($this->TCommision->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Save Add New (Error)');
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
            if ($this->Helper->checkDouplicateEdit('name', 't_commisions', $id, $this->data['TCommision']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Save Edit Commision(Name has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TCommision']['modified'] = $dateNow;
                $this->data['TCommision']['modified_by'] = $user['User']['id'];
                if($this->TCommision->Save($this->data)){  
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
            $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Edit Commision', $id);
            $this->data = $this->TCommision->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $tCommision = $this->TCommision->read(null, $id);
        mysql_query("UPDATE `t_commisions` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Commision', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit; 
    }

}

?>