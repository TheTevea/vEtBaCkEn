<?php

class PromotionApplyPackagesController extends AppController {

    var $uses = array('TravelPackageOrders');
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Dashboard');
    }

    function ajax($travelPackage = 'all', $status = 'all', $telephone = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('travelPackage', 'status', 'telephone'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('TravelPackageOrder');
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'View', $id);
        $this->data = $this->TravelPackageOrder->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('telephone', 'travel_package_orders', $this->data['PromotionApplyPackage']['telephone'], "status = 1 AND type = 2")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->loadModel('TravelPackageOrder');
                $sqlPackage = mysql_query("SELECT * FROM travel_packages WHERE id = ".$this->data['PromotionApplyPackage']['travel_package_id']);
                $rowPackage = mysql_fetch_array($sqlPackage);
                $date = strtotime(date("Y-m-d"));
                $userAppId     = "";
                $url           = PHOTO_PATH."uploads/checkUserApp";
                $post['telephone'] = $this->data['PromotionApplyPackage']['telephone'];
                $post['token'] = "wK4lxDowEfgnaEH2k226FppwAJSflRPG";
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $response   = curl_exec($curl);
                $curl_errno = curl_errno($curl);
                $curl_error = curl_error($curl);
                curl_close ($curl);
                if ($curl_errno > 0) {
                    
                } else {
                    $return = json_decode($response, TRUE);
                    $userAppId = $return['user'];
                }
                if(!empty($userAppId)){
                    $this->TravelPackageOrder->create();
                    $this->data['TravelPackageOrder']['code']        = $this->Helper->generatePackageCode(15);
                    $this->data['TravelPackageOrder']['name']        = $this->data['PromotionApplyPackage']['name'];
                    $this->data['TravelPackageOrder']['telephone']   = $this->data['PromotionApplyPackage']['telephone'];
                    $this->data['TravelPackageOrder']['passport']    = $this->data['PromotionApplyPackage']['passport'];
                    $this->data['TravelPackageOrder']['sex']         = $this->data['PromotionApplyPackage']['sex'];
                    $this->data['TravelPackageOrder']['nationality'] = $this->data['PromotionApplyPackage']['nationality'];
                    $this->data['TravelPackageOrder']['travel_package_id']  = $this->data['PromotionApplyPackage']['travel_package_id'];
                    $this->data['TravelPackageOrder']['photo_path']         = $this->data['PromotionApplyPackage']['photo_path'];
                    $this->data['TravelPackageOrder']['photo']              = $this->data['PromotionApplyPackage']['photo'];
                    $this->data['TravelPackageOrder']['package_date']       = date("Y-m-d");
                    $this->data['TravelPackageOrder']['package_code']       = "PRK-".$this->Helper->generatePackageCode(8);
                    $this->data['TravelPackageOrder']['package_expired']    = $this->data['PromotionApplyPackage']['package_expired'];
                    $this->data['TravelPackageOrder']['user_logistic_id']   = $userAppId;
                    $this->data['TravelPackageOrder']['type']        = 2;
                    $this->data['TravelPackageOrder']['created_by']  = $user['User']['id'];
                    $this->data['TravelPackageOrder']['status']      = 2;
                    if ($this->TravelPackageOrder->save($this->data)) {
                        $travelPackageId = $this->TravelPackageOrder->id;
                        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Save Add New', $travelPackageId);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Save Add New (Error)');
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Save Add New (Error)');
                    echo "Please register user in app first, Before you can create.";
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('TravelPackageOrder');
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('telephone', 'travel_package_orders', $this->data['PromotionApplyPackage']['id'], $this->data['PromotionApplyPackage']['telephone'], "status = 1 AND type = 2")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Save Edit (Name ready existed)', $this->data['PromotionApplyPackage']['id']);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['TravelPackageOrder']['id']          = $this->data['PromotionApplyPackage']['id'];
                $this->data['TravelPackageOrder']['name']        = $this->data['PromotionApplyPackage']['name'];
                $this->data['TravelPackageOrder']['telephone']   = $this->data['PromotionApplyPackage']['telephone'];
                $this->data['TravelPackageOrder']['passport']    = $this->data['PromotionApplyPackage']['passport'];
                $this->data['TravelPackageOrder']['sex']         = $this->data['PromotionApplyPackage']['sex'];
                $this->data['TravelPackageOrder']['nationality'] = $this->data['PromotionApplyPackage']['nationality'];
                $this->data['TravelPackageOrder']['travel_package_id']  = $this->data['PromotionApplyPackage']['travel_package_id'];
                if(!empty($this->data['PromotionApplyPackage']['photo'])){
                    $this->data['TravelPackageOrder']['photo_path']     = $this->data['PromotionApplyPackage']['photo_path'];
                    $this->data['TravelPackageOrder']['photo']          = $this->data['PromotionApplyPackage']['photo'];
                }
                $this->data['TravelPackageOrder']['modified_by'] = $user['User']['id'];
                if ($this->TravelPackageOrder->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Edit', $this->data['PromotionApplyPackage']['id']);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Package', 'Save Edit (Error)', $this->data['PromotionApplyPackage']['id']);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Edit', $id);
        $this->data = $this->TravelPackageOrder->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        mysql_query("UPDATE `travel_package_orders` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Promotion Apply Package', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function upload(){
        $photoText = "photo";
        $result['img'] = "";
        if ($_FILES[$photoText]['name'] != '') {
            $checkFormat   = true;
            // Set default file extension whitelist
            $whitelist_ext = array('jpeg','jpg','png');
            // Get filename
            $file_info = pathinfo($_FILES[$photoText]['name']);
            $name = $file_info['filename'];
            $ext  = $file_info['extension'];
            // Check file has the right extension           
            if (!in_array($ext, $whitelist_ext)) {
                $checkFormat = false;
            }
            if($checkFormat){
                $filename  = $_FILES[$photoText]['name'];
                $mineType  = "image/"+$file_info['extension'];
                $url       = PHOTO_PATH."uploads/uploadPhotoTravelPackageOrder";
                $post['photo'] = new CurlFile($_FILES[$photoText]['tmp_name'], $mineType, $filename);
                $post['token'] = "wK4lxDowEfgnaEH2k226FppwAJSflRPG";
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $response   = curl_exec($curl);
                $curl_errno = curl_errno($curl);
                $curl_error = curl_error($curl);
                curl_close ($curl);
                if ($curl_errno > 0) {
                    
                } else {
                    $return = json_decode($response, TRUE);
                    $result['img'] = $return['img'];
                }
            }
        }
        echo json_encode($result);
        exit();
    }

}

?>