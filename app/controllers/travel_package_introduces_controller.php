<?php

class TravelPackageIntroducesController extends AppController {

    var $name = 'TravelPackageIntroduces';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Introduce', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Introduce', 'View', $id);
        $this->data = $this->TravelPackageIntroduce->read(null, $id);
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['TravelPackageIntroduce']['modified'] = $dateNow;
            $this->data['TravelPackageIntroduce']['modified_by'] = $user['User']['id'];
            if ($this->TravelPackageIntroduce->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Introduce', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Introduce', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Introduce', 'Edit', $id);
        $this->data = $this->TravelPackageIntroduce->read(null, $id);
    }

}

?>