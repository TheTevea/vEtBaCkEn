<?php
/**
 * AffiliatesController
 *
 * @package app
 * @subpackage app.controllers
 */
class AffiliatesController extends AppController {

    var $name = 'Affiliates';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Index');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'affiliates', $this->data['Affiliate']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $this->data['Affiliate']['created_by'] = $user['User']['id'];
            if ($this->Affiliate->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Save Add New', $this->Affiliate->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            }
            exit;
        }
        $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array(
            "conditions" => array(
                "TTransportationType.is_active = 1", 
                'TTransportationType.offline_project_id' => 1
            )
        ));
        $this->set(compact('tTransportationTypes'));
        $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Add New');
    }

    function edit($id = null) {
    $this->layout = 'ajax';
    if (!$id && empty($this->data)) {
        echo MESSAGE_DATA_INVALID;
        exit;
    }
    $user = $this->getCurrentUser();
    if (!empty($this->data)) {
        if ($this->Helper->checkDouplicateEdit('name', 'affiliates', $id, $this->data['Affiliate']['name'], "is_active = 1")) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Save Edit (Name ready existed)', $id);
            echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
            exit;
        }
        $this->data['Affiliate']['modified_by'] = $user['User']['id'];
        if ($this->Affiliate->save($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Save Edit', $id);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Save Edit (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
        }
        exit;
    }
    $this->Helper->saveUserActivity($user['User']['id'], 'Affiliate', 'Edit', $id);
    $this->data = $this->Affiliate->read(null, $id);
    $tTransportationTypes = ClassRegistry::init('TTransportationType')->find('list', array(
        "conditions" => array(
            "TTransportationType.is_active = 1", 
            'TTransportationType.offline_project_id' => 1
        )
    ));
    $this->set(compact('tTransportationTypes'));
}

}
?>