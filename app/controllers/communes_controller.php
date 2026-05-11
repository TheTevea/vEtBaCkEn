<?php

class CommunesController extends AppController {

    var $name = 'Communes';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'View', $id);
        $this->set('commune', $this->Commune->read(null, $id));
        ClassRegistry::init('District')->id = $this->Commune->field('district_id');
        ClassRegistry::init('Province')->id = ClassRegistry::init('District')->field('province_id');
        $this->set('district', ClassRegistry::init('District')->field('name'));
        $this->set('province', ClassRegistry::init('Province')->field('name'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            for ($i = 0; $i < sizeof($_POST['name']); $i++) {
                $this->Commune->create();
                $commune = array();
                $commune['Commune']['district_id'] = $this->data['Commune']['district_id'];
                $commune['Commune']['name'] = $_POST['name'][$i];
                $commune['Commune']['created_by'] = $user['User']['id'];
                $commune['Commune']['is_active'] = 1;
                $this->Commune->save($commune);
                $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Save Add New', $this->Commune->id);
            }
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Add New');
        $provinces = ClassRegistry::init('Province')->find("list", array("conditions" => array("Province.is_active != 2")));
        $districts = $this->Address->districtList();
        $this->set(compact("provinces", "districts"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->data['Commune']['modified_by'] = $user['User']['id'];
            if ($this->Commune->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Edit', $id);
            $provinces = ClassRegistry::init('Province')->find("list", array("conditions" => array("Province.is_active != 2")));
            $districts = $this->Address->districtList();
            $this->set(compact("provinces", "districts"));
            $this->data = $this->Commune->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Commune', 'Delete', $id);
        mysql_query("UPDATE `communes` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>