<?php

class AmenitiesController extends AppController {

    var $name = 'Amenities';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'View', $id);
        $this->data = $this->Amenity->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'amenities', $this->data['Amenity']['name'], "is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Amenity->create();
                $this->data['Amenity']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['Amenity']['created']    = $dateNow;
                $this->data['Amenity']['created_by'] = $user['User']['id'];
                $this->data['Amenity']['is_active']  = 1;
                if ($this->Amenity->save($this->data)) {
                    $id = $this->Amenity->id;
                    $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Save Add New', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'amenities', $id, $this->data['Amenity']['name'], "is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $amenity = $this->Amenity->read(null, $id);
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Amenity']['modified'] = $dateNow;
                $this->data['Amenity']['modified_by'] = $user['User']['id'];
                if ($this->Amenity->save($this->data)) {
                    // Delete photo
                    if($amenity['Amenity']['photo'] != $this->data['Amenity']['photo']){
                        $filename = 'public/amenities/'.$amenity['Amenity']['photo'];
                        if(file_exists($filename)){
                            @unlink($filename);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Edit', $id);
        $this->data = $this->Amenity->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        mysql_query("UPDATE `amenities` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Amenity', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function upload() {
        $this->layout = 'ajax';
        $photoText = "photo";
        $result['img'] = "";
        if ($_FILES[$photoText]['name'] != '') {
            $target_folder = 'public/amenities/';
            $ext = explode(".", $_FILES[$photoText]['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES[$photoText]['tmp_name'], $target_folder . $target_name);
            $result['img'] = $target_name;
        }
        echo json_encode($result);
        exit();
    }

}

?>