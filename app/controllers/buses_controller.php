<?php

class BusesController extends AppController {

    var $name = 'Buses';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'View', $id);
        $this->data = $this->Bus->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'buses', $this->data['Bus']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->Bus->create();
                $this->data['Bus']['created_by'] = $user['User']['id'];
                if ($this->Bus->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Save Add New', $this->Bus->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Add New');
        $busTypes = ClassRegistry::init('BusType')->find('list', array("conditions" => array("BusType.is_active = 1")));
        $this->set(compact('busTypes'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'buses', $id, $this->data['Bus']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['Bus']['modified_by'] = $user['User']['id'];
                if ($this->Bus->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Edit', $id);
            $this->data = $this->Bus->read(null, $id);
            $busTypes = ClassRegistry::init('BusType')->find('list', array("conditions" => array("BusType.is_active = 1")));
            $this->set(compact('busTypes'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Delete', $id);
        mysql_query("UPDATE `buses` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function downloadQr($id = null){
        $this->layout = 'ajax';
        $result = array();
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        include("includes/phpqrcode/qrlib.php");
        $this->data = $this->Bus->read(null, $id);
        if(!empty($this->data['Bus']['code'])){
            $filepath = "public/qr_code/".$this->data['Bus']['code'].".png";
            QRcode::png("https://vireakbuntham.com/feed-back?busId=".$this->data['Bus']['id'],$filepath , QR_ECLEVEL_H, 6);

            //Define header information
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
            header('Content-Length: ' . filesize($filepath));
            header('Pragma: public');

            //Clear system output buffer
            flush();
             
            //Read the size of the file
            readfile($filepath,true);
        } else {
            echo "File cannot download.";
        }
        exit;
    }

    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Bus', 'Export to Excel');
            $condition = "";
            if(!empty($_POST['branch_id']) && $_POST['branch_id'] != "all"){
                $condition = " AND branch_id = ".$_POST['branch_id'];
            }
            $filename = "public/report/bus_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Buses' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . MENU_BRANCH. "\t" . TABLE_CARD_ID. "\t" . TABLE_NAME. "\t" . TABLE_TELEPHONE. "\t" . TABLE_EMAIL. "\t" . TABLE_BANK_NAME. "\t" . TABLE_BANK_ACCOUNT_NAME. "\t" . TABLE_BANK_ACCOUNT_NUMBER. "\t".TABLE_TYPE. "\t".TABLE_APPLY_TO. "\tFree Delivery\tMin Fee\t".TABLE_STATUS;
            $query = mysql_query("SELECT * FROM online_customers WHERE is_active != 2".$condition." ORDER BY `number` ASC");
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t". str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $branchName) . "\t" . $data['number']. "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['name']). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['telephone']). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['email']). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $bankName). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['bank_account_name']). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['bank_account_number']). "\t" . $type ."\t". $applyTo ."\t". $isFreeDelivery ."\t". number_format($data['min_fee'], 0);
                if($data['is_active'] == 1){
                    $excelContent .= "\tActive";
                } else {
                    $excelContent .= "\tInactive";
                }
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>