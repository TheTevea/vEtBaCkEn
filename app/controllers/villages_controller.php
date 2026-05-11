<?php

class VillagesController extends AppController {

    var $name = 'Villages';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'View', $id);
        $this->set('village', $this->Village->read(null, $id));
        ClassRegistry::init('Commune')->id = $this->Village->field('commune_id');
        ClassRegistry::init('District')->id = ClassRegistry::init('Commune')->field('district_id');
        ClassRegistry::init('Province')->id = ClassRegistry::init('District')->field('province_id');
        $this->set('commune', ClassRegistry::init('Commune')->field('name'));
        $this->set('district', ClassRegistry::init('District')->field('name'));
        $this->set('province', ClassRegistry::init('Province')->field('name'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            for ($i = 0; $i < sizeof($_POST['name']); $i++) {
                $this->Village->create();
                $village = array();
                $village['Village']['commune_id'] = $this->data['Village']['commune_id'];
                $village['Village']['name'] = $_POST['name'][$i];
                $village['Village']['created_by'] = $user['User']['id'];
                $village['Village']['is_active'] = 1;
                $this->Village->save($village);
                $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Save Add New', $this->Village->id);
            }
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Add New');
        $provinces = ClassRegistry::init('Province')->find("list", array("conditions" => array("Province.is_active != 2")));
        $districts = $this->Address->districtList();
        $communes = $this->Address->communeList();
        $this->set(compact("provinces", "districts", "communes"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->data['Village']['modified_by'] = $user['User']['id'];
            if ($this->Village->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Edit', $id);
            $provinces = ClassRegistry::init('Province')->find("list", array("conditions" => array("Province.is_active != 2")));
            $districts = $this->Address->districtList();
            $communes = $this->Address->communeList();
            $this->set(compact("provinces", "districts", "communes"));
            $this->data = $this->Village->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Village', 'Delete', $id);
        mysql_query("UPDATE `villages` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>