<?php
class TDropOffsController extends AppController {

    var $name = 'TDropOffs';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'View', $id);
        $this->data = $this->TDropOff->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_drop_offs', $this->data['TDropOff']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Save Add New (Drop Off has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TDropOff->create();
                $this->data['TDropOff']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TDropOff']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['TDropOff']['created']  = $dateNow;
                $this->data['TDropOff']['created_by'] = $user['User']['id'];
                $this->data['TDropOff']['is_active'] = 1;
                if($this->TDropOff->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Add Drop Off');
        $branches  = ClassRegistry::init('Branch')->find('list',array('joins' => array( array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact('branches'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 't_drop_offs', $id, $this->data['TDropOff']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Save Edit (Drop Off has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TDropOff']['modified'] = $dateNow;
                $this->data['TDropOff']['modified_by'] = $user['User']['id'];
                if($this->TDropOff->Save($this->data)){  
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit;                                
                }else{
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Edit Drop Off', $id);
            $this->data = $this->TDropOff->read(null, $id);
            $branches  = ClassRegistry::init('Branch')->find('list',array('joins' => array( array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
            $this->set(compact('branches'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        $tDropOff = $this->TDropOff->read(null, $id);
        mysql_query("UPDATE `t_drop_offs` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Drop Off', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;   
    }

    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Export to Excel');
            $filename = "public/report/dropoff_point.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Drop Off Point' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . TABLE_TELEPHONE. "\t" . TABLE_ADDRESS. "\tLats\tLongs";
            $query = mysql_query("SELECT * FROM t_drop_offs WHERE is_active = 1 AND offline_project_id = 1 ORDER BY `name` ASC");
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t". str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['name']) . "\t" . $data['telephone']. "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['address']). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['lats']). "\t" . str_replace(array("\r\n", "\r", "\n", "\t"," "), '', $data['longs']);
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>