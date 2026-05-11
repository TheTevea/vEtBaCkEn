<?php

class WebsiteBannersController extends AppController {

    var $name = 'WebsiteBanners';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'View', $id);
        $this->data = $this->WebsiteBanner->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'website_banners', $this->data['WebsiteBanner']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->WebsiteBanner->create();
                $this->data['WebsiteBanner']['created_by'] = $user['User']['id'];
                $this->data['WebsiteBanner']['is_active']  = 1;
                if ($this->WebsiteBanner->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Save Add New', $this->WebsiteBanner->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'website_banners', $id, $this->data['WebsiteBanner']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['WebsiteBanner']['offline_project_id'] = 1;
                $this->data['WebsiteBanner']['modified_by'] = $user['User']['id'];
                if ($this->WebsiteBanner->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Edit', $id);
        $this->data = $this->WebsiteBanner->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Banner', 'Delete', $id);
        mysql_query("UPDATE `website_banners` SET `is_active` = 2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>