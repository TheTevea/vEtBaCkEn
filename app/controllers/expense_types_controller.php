<?php

class ExpenseTypesController extends AppController {

    var $name = 'ExpenseTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Dashborad');
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
        $this->data = $this->ExpenseType->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'View', $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            for ($i = 0; $i < sizeof($this->data['name']); $i++) {
                if ($this->Helper->checkDouplicate('name', 'expense_types', $this->data['name'][$i], 'is_active = 1')) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Add New (Error Name)');
                    echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                    exit;
                }
            }
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            for ($i = 0; $i < sizeof($this->data['name']); $i++) {
                $expenseType = array();
                $this->ExpenseType->create();
                $expenseType['ExpenseType']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $expenseType['ExpenseType']['name']     = $this->data['name'][$i];
                $expenseType['ExpenseType']['created']  = $dateNow;
                $expenseType['ExpenseType']['created_by'] = $user['User']['id'];
                $expenseType['ExpenseType']['is_active'] = 1;
                $this->ExpenseType->save($expenseType);
                $restCode[$r]['sys_code']  = $this->data['ExpenseType']['sys_code'];
                $restCode[$r]['name'] = $this->data['ExpenseType']['name'];
                $restCode[$r]['created'] = $this->data['ExpenseType']['created'];
                $restCode[$r]['created_by'] = $this->data['ExpenseType']['created_by'];
                $restCode[$r]['is_active']   = $this->data['ExpenseType']['is_active'];
                $restCode[$r]['dbtodo'] = 'expense_types';
                $restCode[$r]['actodo'] = 'is';
                // Save File Send
                $this->Helper->sendFileToSync($restCode);
                $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Save Add New', $this->ExpenseType->id);
            }
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'expense_types', $id, $this->data['ExpenseType']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Edit (Error Name)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['ExpenseType']['modified'] = $dateNow;
                $this->data['ExpenseType']['modified_by'] = $user['User']['id'];
                if ($this->ExpenseType->save($this->data)) {
                    $restCode[$r]['name'] = $this->data['ExpenseType']['name'];
                    $restCode[$r]['modified'] = $this->data['ExpenseType']['modified'];
                    $restCode[$r]['modified_by'] = $this->data['ExpenseType']['modified_by'];
                    $restCode[$r]['dbtodo'] = 'expense_types';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['ExpenseType']['sys_code']."'";
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Edit', $id);
            $this->data = $this->ExpenseType->read(null, $id);
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
        $this->data = $this->ExpenseType->read(null, $id);
        mysql_query("UPDATE `expense_types` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']  = 2;
        $restCode[$r]['modified']   = $dateNow;
        $restCode[$r]['modified_by'] = $user['User']['id'];
        $restCode[$r]['dbtodo']  = 'expense_types';
        $restCode[$r]['actodo']  = 'ut';
        $restCode[$r]['con']     = "sys_code = '".$this->data['ExpenseType']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>