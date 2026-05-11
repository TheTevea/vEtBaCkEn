<?php

class WebsiteTermconditionsController extends AppController {

    var $name = 'WebsiteTermconditions';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Term Condition', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Term Condition', 'View', $id);
        $this->data = $this->WebsiteTermcondition->read(null, $id);
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->data['WebsiteTermcondition']['modified_by'] = $user['User']['id'];
            if ($this->WebsiteTermcondition->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Term Condition', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Term Condition', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Webiste Term Condition', 'Edit', $id);
            $this->data = $this->WebsiteTermcondition->read(null, $id);
        }
    }

}

?>