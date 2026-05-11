<?php

class PromotionPackagesController extends AppController {

    var $uses = array('TravelPackages');
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Dashboard');
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
        $this->loadModel('TravelPackages');
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'View', $id);
        $this->data = $this->TravelPackages->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'travel_packages', $this->data['PromotionPackage']['name'], "status = 1 AND type = 2")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->loadModel('TravelPackages');
                $dateNow  = date("Y-m-d H:i:s");
                $this->TravelPackages->create();
                $this->data['TravelPackages']['name']           = $this->data['PromotionPackage']['name'];
                $this->data['TravelPackages']['period_expired'] = $this->data['PromotionPackage']['period_expired'];
                $this->data['TravelPackages']['buva_sea']       = $this->data['PromotionPackage']['buva_sea'];
                $this->data['TravelPackages']['international_thai']  = $this->data['PromotionPackage']['international_thai'];
                $this->data['TravelPackages']['international_viet']  = $this->data['PromotionPackage']['international_viet'];
                $this->data['TravelPackages']['international_laos']  = $this->data['PromotionPackage']['international_laos'];
                $this->data['TravelPackages']['local']               = $this->data['PromotionPackage']['local'];
                $this->data['TravelPackages']['destination_apply']   = $this->data['PromotionPackage']['destination_apply'];
                $this->data['TravelPackages']['type']        = 2;
                $this->data['TravelPackages']['created_by']  = $user['User']['id'];
                $this->data['TravelPackages']['status']      = 1;
                if ($this->TravelPackages->save($this->data)) {
                    $travelPackageId = $this->TravelPackages->id;
                    if(!empty($this->data['destination_from_id']) && !empty($this->data['destination_to_id'])){
                        $this->loadModel("TravelPackageDestination");
                        for ($i = 0; $i < sizeof($this->data['destination_from_id']); $i++) {
                            $this->TravelPackageDestination->create();
                            $travelPackageDestination = array();
                            $travelPackageDestination['TravelPackageDestination']['travel_package_id']   = $travelPackageId;
                            $travelPackageDestination['TravelPackageDestination']['destination_from_id'] = $this->data['destination_from_id'][$i];
                            $travelPackageDestination['TravelPackageDestination']['destination_to_id']   = $this->data['destination_to_id'][$i];
                            $travelPackageDestination['TravelPackageDestination']['created_by']   = $user['User']['id'];
                            $this->TravelPackageDestination->save($travelPackageDestination);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Add New', $travelPackageId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('TravelPackages');
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'travel_packages', $this->data['PromotionPackage']['id'], $this->data['PromotionPackage']['name'], "status = 1 AND type = 2")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Edit (Name ready existed)', $this->data['PromotionPackage']['id']);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TravelPackages']['id']             = $this->data['PromotionPackage']['id'];
                $this->data['TravelPackages']['name']           = $this->data['PromotionPackage']['name'];
                $this->data['TravelPackages']['period_expired'] = $this->data['PromotionPackage']['period_expired'];
                $this->data['TravelPackages']['buva_sea']       = $this->data['PromotionPackage']['buva_sea'];
                $this->data['TravelPackages']['international_thai']  = $this->data['PromotionPackage']['international_thai'];
                $this->data['TravelPackages']['international_viet']  = $this->data['PromotionPackage']['international_viet'];
                $this->data['TravelPackages']['international_laos']  = $this->data['PromotionPackage']['international_laos'];
                $this->data['TravelPackages']['local']               = $this->data['PromotionPackage']['local'];
                $this->data['TravelPackages']['destination_apply']   = $this->data['PromotionPackage']['destination_apply'];
                $this->data['TravelPackages']['modified_by']         = $user['User']['id'];
                if ($this->TravelPackages->save($this->data)) {
                    if(!empty($this->data['destination_from_id']) && !empty($this->data['destination_to_id'])){
                        $this->loadModel("TravelPackageDestination");
                        for ($i = 0; $i < sizeof($this->data['destination_from_id']); $i++) {
                            $this->TravelPackageDestination->create();
                            $travelPackageDestination = array();
                            $travelPackageDestination['TravelPackageDestination']['travel_package_id']   = $this->data['PromotionPackage']['id'];
                            $travelPackageDestination['TravelPackageDestination']['destination_from_id'] = $this->data['destination_from_id'][$i];
                            $travelPackageDestination['TravelPackageDestination']['destination_to_id']   = $this->data['destination_to_id'][$i];
                            $travelPackageDestination['TravelPackageDestination']['created_by']   = $user['User']['id'];
                            $this->TravelPackageDestination->save($travelPackageDestination);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Edit', $this->data['PromotionPackage']['id']);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Edit (Error)', $this->data['PromotionPackage']['id']);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Edit', $id);
        $this->data = $this->TravelPackages->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $this->loadModel('TravelPackages');
        $PromotionPackage = $this->TravelPackages->read(null, $id);
        mysql_query("UPDATE `travel_packages` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>