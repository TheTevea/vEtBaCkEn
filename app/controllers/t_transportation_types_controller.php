<?php

class TTransportationTypesController extends AppController {

    var $name = 'TTransportationTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'View', $id);
        $this->data = $this->TTransportationType->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_transportation_types', $this->data['TTransportationType']['name'], "is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TTransportationType->create();
                $this->data['TTransportationType']['sys_code']   = $this->Helper->generateRandomString(6);
                $this->data['TTransportationType']['offline_project_id'] = 1;
                $this->data['TTransportationType']['created']    = $dateNow;
                $this->data['TTransportationType']['created_by'] = $user['User']['id'];
                $this->data['TTransportationType']['is_active']  = 1;
                if ($this->TTransportationType->save($this->data)) {
                    $transportationTypeId = $this->TTransportationType->id;
                    $sqlChk = mysql_query("SELECT * FROM t_transportation_seats WHERE t_transportation_type_id = ".$transportationTypeId);
                    if(!mysql_num_rows($sqlChk)){
                        $layouts = json_decode($this->data['TTransportationType']['layout'], TRUE);
                        foreach($layouts AS $layout){
                            foreach($layout['col'] AS $seatDetail){
                                if($seatDetail['value'] != "" && $seatDetail['label'] != ""){
                                    $number = filter_var($seatDetail['value'], FILTER_VALIDATE_INT);
                                    if ($number !== FALSE){
                                        mysql_query("INSERT INTO `t_transportation_seats` (`t_transportation_type_id`, `seat_number`, `seat_label`) VALUES (".$transportationTypeId.", '".$seatDetail['value']."', '".$seatDetail['label']."');");
                                    }
                                }
                            }
                        }
                    }
                    // Photo
                    if(!empty($this->data['photo_other'])){
                        $this->loadModel("TTransportationTypePhoto");
                        for ($i = 0; $i < sizeof($this->data['photo_other']); $i++) {
                            $this->TTransportationTypePhoto->create();
                            $transportationPhoto = array();
                            $transportationPhoto['TTransportationTypePhoto']['t_transportation_type_id']  = $transportationTypeId;
                            $transportationPhoto['TTransportationTypePhoto']['photo_path']  = $this->data['photo_path_other'][$i];
                            $transportationPhoto['TTransportationTypePhoto']['photo']  = $this->data['photo_other'][$i];
                            $this->TTransportationTypePhoto->save($transportationPhoto);
                        }
                    }
                    // Amenity
                    if(!empty($this->data['amenity_id'])){
                        $this->loadModel("TTransportationTypeAmenity");
                        for ($i = 0; $i < sizeof($this->data['amenity_id']); $i++) {
                            if(!empty($this->data['amenity_id'][$i])){
                                $this->TTransportationTypeAmenity->create();
                                $transportationAmenity = array();
                                $transportationAmenity['TTransportationTypeAmenity']['t_transportation_type_id']  = $transportationTypeId;
                                $transportationAmenity['TTransportationTypeAmenity']['amenity_id']  = $this->data['amenity_id'][$i];
                                $this->TTransportationTypeAmenity->save($transportationAmenity);
                            }
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Save Add New', $transportationTypeId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Add New');
        $amenities = ClassRegistry::init('Amenity')->find('list', array("conditions" => array("Amenity.is_active = 1 AND Amenity.offline_project_id = 1")));
        $this->set(compact('amenities'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_transportation_types', $id, $this->data['TTransportationType']['name'], "is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $transportType = $this->TTransportationType->read(null, $id);
                $dateNow   = date("Y-m-d H:i:s");
                if($transportType['TTransportationType']['photo'] == $this->data['TTransportationType']['photo']){
                    $this->data['TTransportationType']['photo_path'] = $transportType['TTransportationType']['photo_path'];
                }
                $this->data['TTransportationType']['modified'] = $dateNow;
                $this->data['TTransportationType']['modified_by'] = $user['User']['id'];
                if ($this->TTransportationType->save($this->data)) {
                    // Delete main photo
                    if($transportType['TTransportationType']['photo'] != $this->data['TTransportationType']['photo']){
                        $filename = 'public/transportation_type/'.$transportType['TTransportationType']['photo'];
                        if(file_exists($filename)){
                            @unlink($filename);
                        }
                    }
                    // Delete Transportation with Seat
                    mysql_query("DELETE FROM t_transportation_seats WHERE t_transportation_type_id = ".$id);
                    $layouts = json_decode($this->data['TTransportationType']['layout'], TRUE);
                    foreach($layouts AS $layout){
                        foreach($layout['col'] AS $seatDetail){
                            if($seatDetail['value'] != "" && $seatDetail['label'] != ""){
                                $number = filter_var($seatDetail['value'], FILTER_VALIDATE_INT);
                                if ($number !== FALSE){
                                    mysql_query("INSERT INTO `t_transportation_seats` (`t_transportation_type_id`, `seat_number`, `seat_label`) VALUES (".$id.", '".$seatDetail['value']."', '".$seatDetail['label']."');");
                                }
                            }
                        }
                    }
                    // Photo
                    $sqlOtherPhoto = mysql_query("SELECT * FROM t_transportation_type_photos WHERE t_transportation_type_id = ".$id);
                    if(mysql_num_rows($sqlOtherPhoto)){
                        while($rowOtherPhoto = mysql_fetch_array($sqlOtherPhoto)){
                            $delete = true;
                            if(!empty($this->data['photo_other'])){
                                for ($i = 0; $i < sizeof($this->data['photo_other']); $i++) {
                                    if($rowOtherPhoto['photo'] == $this->data['photo_other'][$i]){
                                        $delete = false;
                                    }
                                }
                            }
                            if($delete){
                                $filename = 'public/transportation_type/'.$rowOtherPhoto['photo'];
                                if(file_exists($filename)){
                                    @unlink($filename);
                                }
                            }
                        }
                        mysql_query("DELETE FROM t_transportation_type_photos WHERE t_transportation_type_id = ".$id);
                    }
                    if(!empty($this->data['photo_other'])){
                        $this->loadModel("TTransportationTypePhoto");
                        for ($i = 0; $i < sizeof($this->data['photo_other']); $i++) {
                            $this->TTransportationTypePhoto->create();
                            $transportationPhoto = array();
                            $transportationPhoto['TTransportationTypePhoto']['t_transportation_type_id']  = $id;
                            $transportationPhoto['TTransportationTypePhoto']['photo_path']  = $this->data['photo_path_other'][$i];
                            $transportationPhoto['TTransportationTypePhoto']['photo']  = $this->data['photo_other'][$i];
                            $this->TTransportationTypePhoto->save($transportationPhoto);
                        }
                    }
                    // Amenity
                    $sqlAmenity = mysql_query("SELECT * FROM t_transportation_type_amenities WHERE t_transportation_type_id = ".$id);
                    if(mysql_num_rows($sqlAmenity)){
                        mysql_query("DELETE FROM t_transportation_type_amenities WHERE t_transportation_type_id = ".$id);
                    }
                    if(!empty($this->data['amenity_id'])){
                        $this->loadModel("TTransportationTypeAmenity");
                        for ($i = 0; $i < sizeof($this->data['amenity_id']); $i++) {
                            if(!empty($this->data['amenity_id'][$i])){
                                $this->TTransportationTypeAmenity->create();
                                $transportationAmenity = array();
                                $transportationAmenity['TTransportationTypeAmenity']['t_transportation_type_id']  = $id;
                                $transportationAmenity['TTransportationTypeAmenity']['amenity_id']  = $this->data['amenity_id'][$i];
                                $this->TTransportationTypeAmenity->save($transportationAmenity);
                            }
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Edit', $id);
        $this->data = $this->TTransportationType->read(null, $id);
        $amenitySellecteds = ClassRegistry::init('TTransportationTypeAmenity')->find('list', array('fields' => array('id', 'amenity_id'), 'order' => 'id', 'conditions' => array('t_transportation_type_id' => $id)));
        $amenitySellected = array();
        foreach ($amenitySellecteds as $ps) {
            array_push($amenitySellected, $ps);
        }
        $amenities = ClassRegistry::init('Amenity')->find('list', array("conditions" => array("Amenity.is_active = 1 AND Amenity.offline_project_id = 1")));
        $this->set(compact('amenities', 'amenitySellected'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $tTransportationType = $this->TTransportationType->read(null, $id);
        mysql_query("UPDATE `t_transportation_types` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Delete Transportation with Seat
        // mysql_query("DELETE FROM t_transportation_seats WHERE t_transportation_type_id = ".$id);
        $this->Helper->saveUserActivity($user['User']['id'], 'Transportation Type', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function saveSeatProtectGender(){
        $this->layout = 'ajax';
        $result = array();
        $result['status'] = 0;
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            mysql_query("INSERT INTO `seat_protect_genders` (`id`, `t_transportation_type_id`, `seat1_number`, `seat2_number`, `seat1_lbl`, `seat2_lbl`, `created`, `created_by`) 
                         VALUES (NULL, ".$this->data['t_transportation_type_id'].", '".$this->data['seat1_number']."', '".$this->data['seat2_number']."', '".$this->data['seat1_lbl']."', '".$this->data['seat2_lbl']."', now(), ".$user['User']['id'].");");
            $result['status'] = 1;
            $result['id'] = mysql_insert_id();
        }
        echo json_encode($result);
        exit;
    }

    function deleteSeatProtectGender($id = null){
        $this->layout = 'ajax';
        $result = array();
        $result['status'] = 0;
        if (!$id) {
            echo json_encode($result);
            exit;
        } else {
            mysql_query("DELETE FROM seat_protect_genders WHERE id = ".$id);
            $result['status'] = 1;
        }
        echo json_encode($result);
        exit;
    }

    function upload() {
        $this->layout = 'ajax';
        $photoText = "photo";
        $result['img'] = "";
        if ($_FILES[$photoText]['name'] != '') {
            $target_folder = 'public/transportation_type/';
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