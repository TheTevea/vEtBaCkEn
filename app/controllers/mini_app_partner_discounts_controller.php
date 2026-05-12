<?php

class MiniAppPartnerDiscountsController extends AppController {

    var $name = 'MiniAppPartnerDiscounts';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'View', $id);
        $this->data = $this->MiniAppPartnerDiscount->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow = date("Y-m-d H:i:s");
            $this->MiniAppPartnerDiscount->create();
            $this->data['MiniAppPartnerDiscount']['start_date'] = $this->formatDateTimeForSave($this->data['MiniAppPartnerDiscount']['start_date']);
            $this->data['MiniAppPartnerDiscount']['end_date'] = $this->formatDateTimeForSave($this->data['MiniAppPartnerDiscount']['end_date']);
            $this->data['MiniAppPartnerDiscount']['created_at'] = $dateNow;
            $this->data['MiniAppPartnerDiscount']['updated_at'] = $dateNow;
            if ($this->MiniAppPartnerDiscount->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Save Add New', $this->MiniAppPartnerDiscount->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Add New');
        $miniAppPartners = array();
        $sqlMiniAppPartner = mysql_query("SELECT id, name FROM mini_app_partner WHERE status >= 0 ORDER BY name");
        while ($rowMiniAppPartner = mysql_fetch_array($sqlMiniAppPartner)) {
            $miniAppPartners[$rowMiniAppPartner['id']] = $rowMiniAppPartner['name'];
        }
        $this->set(compact('miniAppPartners'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $id = $this->data['MiniAppPartnerDiscount']['id'];
            $this->data['MiniAppPartnerDiscount']['start_date'] = $this->formatDateTimeForSave($this->data['MiniAppPartnerDiscount']['start_date']);
            $this->data['MiniAppPartnerDiscount']['end_date'] = $this->formatDateTimeForSave($this->data['MiniAppPartnerDiscount']['end_date']);
            $this->data['MiniAppPartnerDiscount']['updated_at'] = date("Y-m-d H:i:s");
            if ($this->MiniAppPartnerDiscount->save($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Edit', $id);
        $this->data = $this->MiniAppPartnerDiscount->read(null, $id);
        $miniAppPartners = array();
        $sqlMiniAppPartner = mysql_query("SELECT id, name FROM mini_app_partner WHERE status >= 0 ORDER BY name");
        while ($rowMiniAppPartner = mysql_fetch_array($sqlMiniAppPartner)) {
            $miniAppPartners[$rowMiniAppPartner['id']] = $rowMiniAppPartner['name'];
        }
        $this->set(compact('miniAppPartners'));
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Change Status', $id);
        mysql_query("UPDATE `mini_app_partner_discount` SET `status`=".$status.", `updated_at`='".date("Y-m-d H:i:s")."' WHERE `id`=".$id.";");
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Mini App Discount', 'Delete', $id);
        mysql_query("DELETE FROM `mini_app_partner_discount` WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function formatDateTimeForSave($dateTime = '') {
        $dateTime = trim($dateTime);
        if ($dateTime == '') {
            return $dateTime;
        }
        if (strpos($dateTime, '/') !== false) {
            $dateParts = explode(' ', $dateTime);
            $date = $dateParts[0];
            $time = !empty($dateParts[1]) ? $dateParts[1] : '00:00:00';
            $date = explode('/', $date);
            if (count($date) == 3) {
                return $date[2].'-'.$date[1].'-'.$date[0].' '.$time;
            }
        } else if (strlen($dateTime) == 10) {
            return $dateTime.' 00:00:00';
        }
        return $dateTime;
    }

}

?>
