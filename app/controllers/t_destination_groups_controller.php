<?php

class TDestinationGroupsController extends AppController {

    var $name = 'TDestinationGroups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'View', $id);
        $this->data = $this->TDestinationGroup->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_destination_groups', $this->data['TDestinationGroup']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->TDestinationGroup->create();
                $this->data['TDestinationGroup']['created_by'] = $user['User']['id'];
                if ($this->TDestinationGroup->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Save Add New', $this->TDestinationGroup->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_destination_groups', $id, $this->data['TDestinationGroup']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $terminal = $this->TDestinationGroup->read(null, $id);
                $this->data['TDestinationGroup']['modified_by'] = $user['User']['id'];
                if ($this->TDestinationGroup->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Edit', $id);
        $this->data = $this->TDestinationGroup->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'TDestinationGroup', 'Delete', $id);
        mysql_query("UPDATE `t_destination_groups` SET `is_active` = 2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>