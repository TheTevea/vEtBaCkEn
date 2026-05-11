<?php

class WebsitePoliciesController extends AppController {

    var $name = 'WebsitePolicies';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Policy', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Policy', 'View', $id);
        $this->data = $this->WebsitePolicy->read(null, $id);
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->data['WebsitePolicy']['modified_by'] = $user['User']['id'];
            if ($this->WebsitePolicy->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Policy', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Policy', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Policy', 'Edit', $id);
            $this->data = $this->WebsitePolicy->read(null, $id);
        }
    }

}

?>