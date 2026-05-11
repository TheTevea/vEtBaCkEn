<?php

class TDestinationsController extends AppController {

    var $name = 'TDestinations';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Dashboard');
    }

    function ajax($province = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('province'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'View', $id);
        $this->data = $this->TDestination->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 't_destinations', $this->data['TDestination']['code'], "is_active = 1 AND offline_project_id = 1")) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TDestination->create();
                $this->data['TDestination']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TDestination']['offline_project_id'] = 1;
                $this->data['TDestination']['name']       = trim($this->data['TDestination']['name']);
                $this->data['TDestination']['name_kh']    = trim($this->data['TDestination']['name_kh']);
                $this->data['TDestination']['name_cn']    = trim($this->data['TDestination']['name_cn']);
                $this->data['TDestination']['created']    = $dateNow;
                $this->data['TDestination']['created_by'] = $user['User']['id'];
                $this->data['TDestination']['is_active']  = 1;
                if ($this->TDestination->save($this->data)) {
                    $lastInsertId = $this->TDestination->id;
                    // Photo
                    if ($this->data['TDestination']['photo'] != '') {
                        $extPhoto  = explode(".", $this->data['TDestination']['photo']);
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".".$extPhoto[1];
                        rename('public/destination_photo/tmp/' . $this->data['TDestination']['photo'], 'public/destination_photo/' . $photoName);
                        mysql_query("UPDATE t_destinations SET photo='".$photoName."' WHERE id=" . $lastInsertId);
                        $this->data['TDestination']['photo'] = $photoName;
                    }
                    // Destination To
                    if(isset($this->data['TDestination']['t_destination_to_id'])){
                        for($i=0;$i<sizeof($this->data['TDestination']['t_destination_to_id']);$i++){
                            mysql_query("INSERT INTO t_destination_tos (t_destination_to_id, t_destination_from_id, created, created_by, is_active) VALUES ('".$this->data['TDestination']['t_destination_to_id'][$i]."', '".$lastInsertId."', '".$dateNow."', ".$user['User']['id'].", 1)");
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Add New');
        $provinces = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
        $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
        $tDestinationGroups = ClassRegistry::init('TDestinationGroup')->find('list', array("conditions" => array("TDestinationGroup.is_active = 1")));
        $this->set(compact('provinces', 'tDestinationGroups', 'countries'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_destinations', $id, $this->data['TDestination']['name'], "is_active = 1 AND offline_project_id = 1")) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TDestination']['name']       = trim($this->data['TDestination']['name']);
                $this->data['TDestination']['name_kh']    = trim($this->data['TDestination']['name_kh']);
                $this->data['TDestination']['name_cn']    = trim($this->data['TDestination']['name_cn']);
                $this->data['TDestination']['modified'] = $dateNow;
                $this->data['TDestination']['modified_by'] = $user['User']['id'];
                if ($this->TDestination->save($this->data)) {
                    // Company photo
                    if ($this->data['TDestination']['new_photo'] != '') {
                        $extPhoto  = explode(".", $this->data['TDestination']['new_photo']);
                        $photoName = md5($this->data['TDestination']['id'] . '_' . date("Y-m-d H:i:s")).".".$extPhoto[1];
                        rename('public/destination_photo/tmp/' . $this->data['TDestination']['new_photo'], 'public/destination_photo/' . $photoName);
                        @unlink('public/destination_photo/' . $this->data['TDestination']['old_photo']);
                        mysql_query("UPDATE t_destinations SET photo='" . $photoName . "' WHERE id=" . $this->data['TDestination']['id']);
                        $this->data['TDestination']['photo'] = $photoName;
                    }
                    // TDestination To
                    mysql_query("UPDATE t_destination_tos SET is_active = 2, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE t_destination_from_id=".$id);
                    if(isset($this->data['TDestination']['t_destination_to_id'])){
                        for($i=0;$i<sizeof($this->data['TDestination']['t_destination_to_id']);$i++){
                            mysql_query("INSERT INTO t_destination_tos (t_destination_to_id, t_destination_from_id, created, created_by, is_active) VALUES ('".$this->data['TDestination']['t_destination_to_id'][$i]."', '".$id."', '".$dateNow."', ".$user['User']['id'].", 1)");
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Edit', $id);
            $this->data = $this->TDestination->read(null, $id);
            $provinces = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
            $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
            $tDestinationGroups = ClassRegistry::init('TDestinationGroup')->find('list', array("conditions" => array("TDestinationGroup.is_active = 1")));
            $this->set(compact('provinces', 'tDestinationGroups', 'countries'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $sqlCheck = mysql_query("SELECT id FROM t_destination_tos WHERE t_destination_to_id = ".$id." LIMIT 1;");
        if(!mysql_num_rows($sqlCheck)){
            $tDestination = $this->TDestination->read(null, $id);
            mysql_query("UPDATE t_destination_tos SET is_active = 2, modified = '".date("Y-m-d H:i:s")."', modified_by = ".$user['User']['id']." WHERE t_destination_from_id=".$id);
            mysql_query("UPDATE `t_destinations` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function upload() {
        $this->layout = 'ajax';
        if ($_FILES['photo']['name'] != '') {
            $target_folder = 'public/destination_photo/tmp/';
            $ext = explode(".", $_FILES['photo']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['pos_photo']) && $_SESSION['pos_photo'] != '') {
                @unlink($target_folder . $_SESSION['pos_photo']);
            }
            echo $_SESSION['pos_photo'] = $target_name;
            exit();
        }
    }

    function updateAllowSchedule($id = null, $status = null){
        if (!$id && !$status) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $result   = array();
        $user     = $this->getCurrentUser();
        mysql_query("UPDATE `t_destinations` SET `is_allow_shedule`= ".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Destination', 'Update Allow Schedule', $id);
        $result['error'] = 0;
        echo json_encode($result);
        exit;
    }

}

?>