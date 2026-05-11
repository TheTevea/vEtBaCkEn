<?php

class WebsiteGalleriesController extends AppController {

    var $name = 'WebsiteGalleries';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'View', $id);
        $this->data = $this->WebsiteGallery->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'website_galleries', $this->data['WebsiteGallery']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->WebsiteGallery->create();
                $this->data['WebsiteGallery']['created_by'] = $user['User']['id'];
                $this->data['WebsiteGallery']['is_active']  = 1;
                if ($this->WebsiteGallery->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Save Add New', $this->WebsiteGallery->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'website_galleries', $id, $this->data['WebsiteGallery']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['WebsiteGallery']['offline_project_id'] = 1;
                $this->data['WebsiteGallery']['modified_by'] = $user['User']['id'];
                if ($this->WebsiteGallery->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Edit', $id);
        $this->data = $this->WebsiteGallery->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Website Gallery', 'Delete', $id);
        mysql_query("UPDATE `website_galleries` SET `is_active` = 2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>