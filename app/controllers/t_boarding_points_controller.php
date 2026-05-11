<?php
class TBoardingPointsController extends AppController {

    var $name = 'TBoardingPoints';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Dashboard');
        $tDestinations = ClassRegistry::init('TDestination')->find('all', array('fields' => array('TDestination.id', 'TDestination.name'), 'conditions' => array('TDestination.is_active = 1', 'TDestination.offline_project_id' => 1)));
        $branches = ClassRegistry::init('Branch')->find('all', array('fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => 1)));
        $this->set(compact('tDestinations', 'branches'));
    }

    function ajax($origin = "all", $branch = "all") {
        $this->layout = 'ajax';
        $this->set(compact('origin', 'branch'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'View', $id);
        $this->data = $this->TBoardingPoint->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 't_boarding_points', $this->data['TBoardingPoint']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Save Add New (Boarding Point has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TBoardingPoint->create();
                $this->data['TBoardingPoint']['sys_code'] = $this->Helper->generateRandomString(6);
                $this->data['TBoardingPoint']['offline_project_id'] = $user['User']['offline_project_id'];
                $this->data['TBoardingPoint']['created']  = $dateNow;
                $this->data['TBoardingPoint']['created_by'] = $user['User']['id'];
                $this->data['TBoardingPoint']['is_active'] = 1;
                if($this->TBoardingPoint->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Add Boarding Point');
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
            if ($this->Helper->checkDouplicateEdit('name', 't_boarding_points', $id, $this->data['TBoardingPoint']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Save Edit (Boarding Point has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TBoardingPoint']['modified'] = $dateNow;
                $this->data['TBoardingPoint']['modified_by'] = $user['User']['id'];
                if($this->TBoardingPoint->Save($this->data)){  
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit;                                
                }else{
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Edit Boarding Point', $id);
            $this->data = $this->TBoardingPoint->read(null, $id);
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
        $tBoardingPoint = $this->TBoardingPoint->read(null, $id);
        mysql_query("UPDATE `t_boarding_points` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit; 
    }

    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Boarding Point', 'Export to Excel');
            $filename = "public/report/boarding_point.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Boarding Point' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . TABLE_TELEPHONE. "\t" . TABLE_ADDRESS. "\tLats\tLongs";
            $query = mysql_query("SELECT * FROM t_boarding_points WHERE is_active = 1 AND offline_project_id = 1 ORDER BY `name` ASC");
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