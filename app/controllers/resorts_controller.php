<?php

class ResortsController extends AppController {

    var $name = 'Resorts';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'View', $id);
        $this->data = $this->Resort->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'resorts', $this->data['Resort']['name'], "status = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->Resort->create();
                $this->data['Resort']['created_by']  = $user['User']['id'];
                if ($this->Resort->save($this->data)) {
                    $resortId = $this->Resort->id;
                    $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Save Add New', $resortId);
                    mysql_query("UPDATE `data_caches` SET `modified`=NOW();");
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'resorts', $this->data['Resort']['id'], $this->data['Resort']['name'], "status = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Save Edit (Name ready existed)', $this->data['Resort']['id']);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $resort = $this->Resort->read(null, $this->data['Resort']['id']);
                $this->data['Resort']['modified_by']    = $user['User']['id'];
                if ($this->Resort->save($this->data)) {
                    if($resort['Resort']['photo'] != $this->data['Resort']['photo']){
                        @unlink("public/resort/" . $resort['Resort']['photo']);
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Save Edit', $this->data['Resort']['id']);
                    mysql_query("UPDATE `data_caches` SET `modified`=NOW();");
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Save Edit (Error)', $this->data['Resort']['id']);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Edit', $id);
        $this->data = $this->Resort->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `resorts` SET `status`=0, `modified`=now(), `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Resort', 'Delete', $id);
        mysql_query("UPDATE `data_caches` SET `modified`=NOW();");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function upload() {
        $this->layout = 'ajax';
        $photoText = "photo";
        $result['img'] = "";
        if ($_FILES[$photoText]['name'] != '') {
            include("includes/function.php");
            $target_folder = 'public/resort/';
            $ext = explode(".", $_FILES[$photoText]['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            $filename    = 'resort'.rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES[$photoText]['tmp_name'], $target_folder . $target_name);
            Resize($target_folder, $target_name, $target_folder, $filename, 512, 512, 100, true);
            @unlink($target_folder . $target_name);
            $result['img'] = $filename;
        }
        echo json_encode($result);
        exit();
    }

}

?>