<?php

class WebsiteDiscoversController extends AppController {

    var $name = 'WebsiteDiscovers';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'View', $id);
        $this->data = $this->WebsiteDiscover->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'website_discovers', $this->data['WebsiteDiscover']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->WebsiteDiscover->create();
                $this->data['WebsiteDiscover']['created_by'] = $user['User']['id'];
                $this->data['WebsiteDiscover']['is_active']  = 1;
                if ($this->WebsiteDiscover->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Save Add New', $this->WebsiteDiscover->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'website_discovers', $id, $this->data['WebsiteDiscover']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['WebsiteDiscover']['offline_project_id'] = 1;
                $this->data['WebsiteDiscover']['modified_by'] = $user['User']['id'];
                if ($this->WebsiteDiscover->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Edit', $id);
        $this->data = $this->WebsiteDiscover->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Discover', 'Delete', $id);
        mysql_query("UPDATE `website_discovers` SET `is_active` = 2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>