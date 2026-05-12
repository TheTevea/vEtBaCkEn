<?php

class MiniAppPartnersController extends AppController {

    var $name = 'MiniAppPartners';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'View', $id);
        $this->data = $this->MiniAppPartner->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (empty($this->data['MiniAppPartner']['code']) || !$this->isMiniAppBase64Code($this->data['MiniAppPartner']['code']) || $this->Helper->checkDouplicate('code', 'mini_app_partner', $this->data['MiniAppPartner']['code'], '1=1')) {
                $this->data['MiniAppPartner']['code'] = $this->generateMiniAppCode();
            }
            $dateNow = date("Y-m-d H:i:s");
            $this->MiniAppPartner->create();
            $this->data['MiniAppPartner']['created_at'] = $dateNow;
            $this->data['MiniAppPartner']['updated_at'] = $dateNow;
            if ($this->MiniAppPartner->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Save Add New', $this->MiniAppPartner->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->data['MiniAppPartner']['code'] = $this->generateMiniAppCode();
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $id = $this->data['MiniAppPartner']['id'];
            $miniAppPartner = $this->MiniAppPartner->read(null, $id);
            $this->data['MiniAppPartner']['code'] = $miniAppPartner['MiniAppPartner']['code'];
            if ($this->Helper->checkDouplicateEdit('code', 'mini_app_partner', $id, $this->data['MiniAppPartner']['code'], '1=1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Save Edit (Code ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['MiniAppPartner']['updated_at'] = date("Y-m-d H:i:s");
                if ($this->MiniAppPartner->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Edit', $id);
        $this->data = $this->MiniAppPartner->read(null, $id);
    }

    function status($id = null, $status = 0) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $id = (int) $id;
        $status = (int) $status;
        $status = $status == 1 ? 1 : 0;
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Change Status', $id);
        mysql_query("UPDATE `mini_app_partner` SET `status`=".$status.", `updated_at`='".date("Y-m-d H:i:s")."' WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $id = (int) $id;
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App', 'Delete', $id);
        mysql_query("DELETE FROM `mini_app_partner` WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function generateMiniAppCode() {
        for ($i = 0; $i < 10; $i++) {
            $code = substr($this->Helper->Safeb64Encode(uniqid('', true).$this->Helper->generateRandomString(8)), 0, 50);
            if (!$this->Helper->checkDouplicate('code', 'mini_app_partner', $code, '1=1')) {
                return $code;
            }
        }
        return substr($this->Helper->Safeb64Encode(uniqid('', true).$this->Helper->generateRandomString(16)), 0, 50);
    }

    function isMiniAppBase64Code($code = '') {
        return preg_match('/^[A-Za-z0-9\-_]+$/', $code);
    }

}

?>
