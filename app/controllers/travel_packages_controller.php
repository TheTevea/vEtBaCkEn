<?php

class TravelPackagesController extends AppController {

    var $name = 'TravelPackages';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'View', $id);
        $this->data = $this->TravelPackage->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'travel_packages', $this->data['TravelPackage']['name'], "status = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TravelPackage->create();
                $this->data['TravelPackage']['created']     = $dateNow;
                $this->data['TravelPackage']['created_by']  = $user['User']['id'];
                $this->data['TravelPackage']['status']      = 1;
                if ($this->TravelPackage->save($this->data)) {
                    $travelPackageId = $this->TravelPackage->id;
                    // Photo
                    if(!empty($this->data['photo_other'])){
                        $this->loadModel("TravelPackagePhoto");
                        for ($i = 0; $i < sizeof($this->data['photo_other']); $i++) {
                            $this->TravelPackagePhoto->create();
                            $transportationPhoto = array();
                            $transportationPhoto['TravelPackagePhoto']['travel_package_id']  = $travelPackageId;
                            $transportationPhoto['TravelPackagePhoto']['photo']      = $this->data['photo_other'][$i];
                            $transportationPhoto['TravelPackagePhoto']['photo_path'] = $this->data['TravelPackage']['photo_path'];
                            $this->TravelPackagePhoto->save($transportationPhoto);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Save Add New', $travelPackageId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'travel_packages', $id, $this->data['TravelPackage']['name'], "status = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $travelPackage = $this->TravelPackage->read(null, $id);
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TravelPackage']['modified'] = $dateNow;
                $this->data['TravelPackage']['modified_by'] = $user['User']['id'];
                if ($this->TravelPackage->save($this->data)) {
                    // Delete main photo
                    // if($travelPackage['TravelPackage']['photo'] != $this->data['TravelPackage']['photo']){
                    //     $filename = 'public/travel_package/'.$travelPackage['TravelPackage']['photo'];
                    //     if(file_exists($filename)){
                    //         @unlink($filename);
                    //     }
                    // }
                    // Photo
                    $sqlOtherPhoto = mysql_query("SELECT * FROM travel_package_photos WHERE travel_package_id = ".$id);
                    if(mysql_num_rows($sqlOtherPhoto)){
                        // while($rowOtherPhoto = mysql_fetch_array($sqlOtherPhoto)){
                        //     $delete = true;
                        //     if(!empty($this->data['photo_other'])){
                        //         for ($i = 0; $i < sizeof($this->data['photo_other']); $i++) {
                        //             if($rowOtherPhoto['photo'] == $this->data['photo_other'][$i]){
                        //                 $delete = false;
                        //             }
                        //         }
                        //     }
                        //     if($delete){
                        //         $filename = 'public/travel_package/'.$rowOtherPhoto['photo'];
                        //         if(file_exists($filename)){
                        //             @unlink($filename);
                        //         }
                        //     }
                        // }
                        mysql_query("DELETE FROM travel_package_photos WHERE travel_package_id = ".$id);
                    }
                    if(!empty($this->data['photo_other'])){
                        $this->loadModel("TravelPackagePhoto");
                        for ($i = 0; $i < sizeof($this->data['photo_other']); $i++) {
                            $this->TravelPackagePhoto->create();
                            $transportationPhoto = array();
                            $transportationPhoto['TravelPackagePhoto']['travel_package_id']  = $id;
                            $transportationPhoto['TravelPackagePhoto']['photo']      = $this->data['photo_other'][$i];
                            $transportationPhoto['TravelPackagePhoto']['photo_path'] = $this->data['TravelPackage']['photo_path'];
                            $this->TravelPackagePhoto->save($transportationPhoto);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Edit', $id);
        $this->data = $this->TravelPackage->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $TravelPackage = $this->TravelPackage->read(null, $id);
        mysql_query("UPDATE `travel_packages` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Travel Package', 'Delete', $id);
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
                $url       = PHOTO_PATH."uploads/uploadPhotoTravelPackage";
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