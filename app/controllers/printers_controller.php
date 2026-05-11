<?php

class PrintersController extends AppController {

    var $name = 'Printers';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'View', $id);
        $this->data = $this->Printer->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->Printer->create();
            $this->data['Printer']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Printer']['created']  = $dateNow;
            $this->data['Printer']['created_by'] = $user['User']['id'];
            $this->data['Printer']['is_active'] = 1;
            if ($this->Printer->save($this->data)) {
                $branchCode   = $this->Helper->getSQLSyncCode("branches", $this->data['Printer']['branch_id']);
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['Printer'], "printers");
                $restCode[$r]['branch_id'] = $branchCode;
                $restCode[$r]['modified']  = $dateNow;
                $restCode[$r]['dbtodo']    = 'printers';
                $restCode[$r]['actodo']    = 'is';
                // Save File Send
                $this->Helper->sendFileToSync($restCode);
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Add New', $this->Printer->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Add New');
        $types = array(1 => 'Print Receipt', 2 => 'Print Plan Seat');
        $silents = array(0 => 'False', 1 => 'True');
        $branches = ClassRegistry::init('Branch')->find('list', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $this->set(compact('types', 'silents', 'branches'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['Printer']['modified'] = $dateNow;
            $this->data['Printer']['modified_by'] = $user['User']['id'];
            if ($this->Printer->save($this->data)) {
                $branchCode   = $this->Helper->getSQLSyncCode("branches", $this->data['Printer']['branch_id']);
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['Printer'], "printers");
                $restCode[$r]['branch_id'] = $branchCode;
                $restCode[$r]['dbtodo']    = 'printers';
                $restCode[$r]['actodo']    = 'ut';
                $restCode[$r]['con']       = "sys_code = '".$this->data['Printer']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode);
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Edit', $id);
            $this->data = $this->Printer->read(null, $id);
            $types = array(1 => 'Print Receipt', 2 => 'Print Plan Seat');
            $silents = array(0 => 'False', 1 => 'True');
            $branches = ClassRegistry::init('Branch')->find('list', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
            $this->set(compact('types', 'silents', 'branches'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $this->data = $this->Printer->read(null, $id);
        mysql_query("UPDATE `printers` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']  = 2;
        $restCode[$r]['modified']   = $dateNow;
        $restCode[$r]['modified_by'] = $user['User']['id'];
        $restCode[$r]['dbtodo']  = 'printers';
        $restCode[$r]['actodo']  = 'ut';
        $restCode[$r]['con']     = "sys_code = '".$this->data['Printer']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode);
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>