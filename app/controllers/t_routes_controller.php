<?php

class TRoutesController extends AppController {

    var $name = 'TRoutes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'View', $id);
        $this->data = $this->TRoute->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_routes', $this->data['TRoute']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TRoute->create();
                $this->data['TRoute']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TRoute']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['TRoute']['created']  = $dateNow;
                $this->data['TRoute']['created_by'] = $user['User']['id'];
                $this->data['TRoute']['is_active']  = 1;
                if ($this->TRoute->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Save Add New', $this->TRoute->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_routes', $id, $this->data['TRoute']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TRoute']['modified'] = $dateNow;
                $this->data['TRoute']['modified_by'] = $user['User']['id'];
                if ($this->TRoute->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Edit', $id);
        $this->data = $this->TRoute->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $tTransportationType = $this->TRoute->read(null, $id);
        mysql_query("UPDATE `t_routes` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Route', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>