<?php

class TerminalsController extends AppController {

    var $name = 'Terminals';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'View', $id);
        $this->data = $this->Terminal->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'terminals', $this->data['Terminal']['name'], "status > 0 AND offline_project_id = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->Terminal->create();
                $options = array(
                    'cost' => 10,
                );
                $password      = mysql_real_escape_string($this->data['Terminal']['password']);
                $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                $this->data['Terminal']['password']   = $newPassword;
                $this->data['Terminal']['offline_project_id'] = 1;
                $this->data['Terminal']['created_by'] = $user['User']['id'];
                $this->data['Terminal']['status'] = 1;
                if ($this->Terminal->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Save Add New', $this->Terminal->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'terminals', $id, $this->data['Terminal']['name'], "status > 0 AND offline_project_id = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $terminal = $this->Terminal->read(null, $id);
                if(!empty($this->data['Terminal']['password'])){
                    $options = array(
                        'cost' => 10,
                    );
                    $password      = mysql_real_escape_string($this->data['Terminal']['password']);
                    $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                    $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                    $this->data['Terminal']['password']    = $newPassword;
                } else {
                    $this->data['Terminal']['password'] = $terminal['Terminal']['password'];
                }
                $this->data['Terminal']['offline_project_id'] = 1;
                $this->data['Terminal']['modified_by'] = $user['User']['id'];
                if ($this->Terminal->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Edit', $id);
        $this->data = $this->Terminal->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Terminal', 'Delete', $id);
        mysql_query("UPDATE `terminals` SET `status` = 0, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function updateStatus($id = null, $status = 2){
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        mysql_query("UPDATE `terminals` SET status = ".$status." WHERE id = ".$id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

}

?>