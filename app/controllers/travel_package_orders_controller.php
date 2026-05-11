<?php

class TravelPackageOrdersController extends AppController {

    var $name = 'TravelPackageOrders';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Order', 'Dashboard');
    }

    function ajax($travelPackage = 'all', $status = 'all', $telephone = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('travelPackage', 'status', 'telephone', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Order', 'View', $id);
        $this->data = $this->TravelPackageOrder->read(null, $id);
    }

    function delete($id = null){
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `travel_package_orders` SET status = 3, disabled_date = now(), disabled_by = ".$user['User']['id']." WHERE id = ".$id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
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
            if(!empty($this->data['TravelPackageOrderEdit']['photo'])){
                $this->data['TravelPackageOrder']['photo_path']     = $this->data['TravelPackageOrderEdit']['photo_path'];
                $this->data['TravelPackageOrder']['photo']          = $this->data['TravelPackageOrderEdit']['photo'];
            }
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package Order', 'Edit', $id);
        $this->data = $this->TravelPackageOrder->read(null, $id);
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